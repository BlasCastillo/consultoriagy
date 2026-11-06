# Auditoría y sincronización de `database/` con la base real `sgc_db`

**Fecha:** 2026-11-05
**Motor:** PostgreSQL 15+ (`127.0.0.1:5432`, base `sgc_db`)
**Framework:** Laravel 12.55.1
**Alcance:** `database/migrations/`, `database/seeders/` y `database/factories/`
**Base de validación:** `sgc_audit2` (creada desde cero con `migrate:fresh --seed`)

---

## 1. Resumen ejecutivo

| Métrica | Antes | Después |
|---|---:|---:|
| Archivos de migración | 65 | 59 |
| Seeders | 22 | 9 |
| Tablas en `sgc_db` | 38 | 38 |
| `migrate:fresh` | ❌ fallaba | ✅ funciona |
| `db:seed` | ❌ fallaba | ✅ funciona e idempotente |
| Esquema fresh vs `sgc_db` | — | ✅ idéntico (columnas, índices, FKs) |

Resultados de la comparación estructural entre `sgc_db` y `sgc_audit2`:

- **Columnas:** idénticas.
- **Índices:** idénticos.
- **Foreign keys:** idénticas.
- **CHECK constraints:** idénticos en semántica; solo cambia el formato textual que PostgreSQL genera para `poas_estado_check` y `prestamos_archivos_estado_prestamo_check`.
- **Datos de catálogo/RBAC seedeados:** idénticos (roles, permisos, usuarios, instituciones, jefaturas, títulos, gobernadores y archivo físico).

> `sgc_db` **no fue modificada**. Toda la validación se hizo en `sgc_audit2`. Los comandos para aplicar los cambios en `sgc_db` están en la sección 7.

---

## 2. Metodología

1. Introspección de `sgc_db` con `psql` (`information_schema`, `pg_constraint`, `pg_indexes`).
2. Inventario de archivos de `database/migrations` y `database/seeders`.
3. Comparación de la tabla `migrations` contra el filesystem (huérfanos y pendientes).
4. Creación de `sgc_audit2`, ejecución de `migrate:fresh` y `db:seed`.
5. Comparación automática `sgc_db` vs `sgc_audit2` de esquema y datos.
6. Segunda ejecución de `db:seed` para comprobar idempotencia.

---

## 3. Hallazgos de migraciones

### 3.1 Fallo crítico de `migrate:fresh`

`2026_07_27_061944_refactorizar_campos_en_atenciones_table.php` ejecutaba:

```php
$table->dropColumn('datos_persona');
```

pero `datos_persona` **nunca fue creada** por `2026_07_27_053027_create_atencions_table.php`. En una base limpia la migración abortaba con:

```
Undefined column: datos_persona
```

**Acción aplicada:** la migración ahora elimina `datos_persona` solo si existe y agrega cada columna de forma condicional (`Schema::hasColumn`). Sigue siendo segura sobre bases parcialmente actualizadas y ya no rompe `migrate:fresh`.

### 3.2 Migraciones eliminadas por consolidación (7)

Se eliminaron pares `create` + `drop` que no dejan ningún objeto en el esquema final, y la migración que dependía de una tabla eliminada:

| Archivo eliminado | Motivo |
|---|---|
| `2026_04_29_041257_create_institutions_table.php` | La tabla `institutions` fue eliminada del esquema real. |
| `2026_04_29_041258_add_institution_id_to_users_table.php` | `institution_id` ya no existe en `users` (la reemplazó `institucion_id`). |
| `2026_05_26_135218_create_sumario_gacetas_table.php` | `sumarios_gacetas` no existe en la base real. |
| `2026_05_29_032050_drop_tipo_acto_check_constraint_from_sumarios_gacetas.php` | Dependía de `sumarios_gacetas`. |
| `2026_06_12_002950_drop_sumario_gacetas_table.php` | Su par `create` ya no existe. |
| `2026_06_12_014500_drop_institutions_table.php` | Su par `create` ya no existe. |
| `2026_07_24_190941_create_ubicacion_fisicas_table.php` | `ubicaciones_fisicas` fue reemplazada por `sedes`/`estantes`/`gavetas`. |

### 3.3 Migraciones modificadas por dependencia (3)

| Archivo | Cambio |
|---|---|
| `2026_07_24_190942_create_inventario_archivos_table.php` | Se quitó la FK `ubicacion_fisica_id` (la tabla ya no se crea). |
| `2026_07_24_204000_refactor_inventario_kardex_to_3nf.php` | Se quitó el `dropForeign`/`dropColumn` de `ubicacion_fisica_id` y el `dropIfExists('ubicaciones_fisicas')`. Se mantiene la creación de `sedes`, `estantes`, `gavetas` y sus FKs. |
| `2026_07_27_061944_refactorizar_campos_en_atenciones_table.php` | Se hizo idempotente (ver 3.1). |

### 3.4 Registros huérfanos en la tabla `migrations`

En `sgc_db` existen registros sin archivo en el repositorio:

```
2026_05_27_140628_create_titulos_table
2026_05_27_140629_create_gobernadors_table
2026_05_27_140630_add_gobernador_id_to_gacetas_table
```

Los reemplazos reales son los archivos `2026_05_27_180655`, `180656` y `180657`, que sí se ejecutaron.

**Acción aplicada:** se agregó la migración `2026_11_05_000000_prune_obsolete_migration_records.php`, que elimina esos 3 registros huérfanos y los 7 registros de las migraciones consolidadas. Es idempotente y no toca datos ni esquema.

---

## 4. Hallazgos de seeders

### 4.1 Seeders eliminados (13)

| Seeder | Motivo |
|---|---|
| `InstitutionSeeder.php` | Usa `App\Models\Institution` (no existe) y columnas `name/type/status` (no existen). |
| `GacetasTestSeeder.php` | Usa `Institution`, `institution_id`, `type`, `status` y datos de prueba obsoletos. |
| `UbicacionFisicaSeeder.php` | Usa `App\Models\UbicacionFisica` y la tabla eliminada `ubicacion_fisicas`. |
| `ArchivoRoleSeeder.php` | Crea el rol `Jefe de Archivo`, que no existe en `sgc_db`. |
| `AsignacionAbsolutaSeeder.php` | Crea roles `Super Admin`/`Súper Administrador` con nombres y asignaciones que no reflejan la base real. |
| `FixRolesYPermisosSeeder.php` | Referencia permisos obsoletos (`SumarioGaceta`, `Institution`, etc.). |
| `FixSeguridadSeeder.php` | Hace `syncPermissions` destructivo sobre `Digitalizador` y crea permisos inexistentes. |
| `SaneamientoRBACSeeder.php` | Script de saneamiento destructivo, no apto como seeder base. |
| `SaneamientoPermisosSeeder.php` | Migración de nombres de permisos ya ejecutada (one-off). |
| `RestaurarMenusSeeder.php` | Crea permisos con nombres que no existen en la base real (`read Solicitud`, `ver gacetas`, etc.). |
| `PermisosFaltantesSeeder.php` | Sus permisos ya están integrados en `RolesAndPermissionsSeeder`. |
| `UpdateRolesNameSeeder.php` | Renombrado ya aplicado (`Institucion` → `Representante Institucional`). |
| `CleanZombieSolicitudesSeeder.php` | Borrado masivo de solicitudes (one-off destructivo). |

### 4.2 Seeders reescritos para reflejar `sgc_db`

| Seeder | Contenido |
|---|---|
| `RolesAndPermissionsSeeder.php` | 110 permisos + 12 roles reales con su matriz exacta de permisos. No crea usuarios. |
| `UserSeeder.php` | 14 usuarios reales con nombre, estado, institución, jefatura y rol. Sin factories. |
| `JefaturaSeeder.php` | 10 jefaturas reales, incluyendo `nivel`/`parent_id` y las columnas heredadas `nivel_jerarquico`/`dependencia_id`. |
| `EntidadesBaseSeeder.php` | Solo las 4 instituciones base (se le quitó la creación de jefaturas obsoletas). |
| `InstitucionesAdicionalesSeeder.php` | 24 instituciones adicionales; corregidas siglas (`IABOY`) y nombre real de PROSALUD. |
| `ClasificarInstitucionesSeeder.php` | Clasificación de los 7 tipos reproduciendo el estado real (incluye CLEY sin tipo y PROSALUD como Corporación). |
| `GobernadorSeeder.php` | 6 títulos con descripción y los 2 gobernadores reales. |

### 4.3 Seeder nuevo

| Seeder | Contenido |
|---|---|
| `ArchivoFisicoSeeder.php` | 2 sedes, 2 estantes y 2 gavetas reales. Reemplaza funcionalmente a `UbicacionFisicaSeeder`. |

### 4.4 `DatabaseSeeder` final

```php
public function run(): void
{
    $this->call([
        // Catálogos base
        EntidadesBaseSeeder::class,
        InstitucionesAdicionalesSeeder::class,
        ClasificarInstitucionesSeeder::class,
        JefaturaSeeder::class,
        GobernadorSeeder::class,
        ArchivoFisicoSeeder::class,

        // Seguridad
        RolesAndPermissionsSeeder::class,
        UserSeeder::class,
    ]);
}
```

Se eliminó la creación de `test@example.com` con `User::factory()`; ahora ese usuario lo crea `UserSeeder` con los datos reales.

### 4.5 Seeders vigentes (9)

```
ArchivoFisicoSeeder.php
ClasificarInstitucionesSeeder.php
DatabaseSeeder.php
EntidadesBaseSeeder.php
GobernadorSeeder.php
InstitucionesAdicionalesSeeder.php
JefaturaSeeder.php
RolesAndPermissionsSeeder.php
UserSeeder.php
```

---

## 5. Desalineaciones detectadas que NO se corrigieron en `sgc_db`

Estas son inconsistencias de datos/modelo que conviene revisar por separado:

1. **`jefaturas` tiene columnas duplicadas.** Conviven `nivel`/`parent_id` (usadas por el modelo `Jefatura`) y `nivel_jerarquico`/`dependencia_id` (legado). El seeder rellena ambas para reproducir la base real, pero lo recomendable es consolidarlas en una migración futura y actualizar el modelo.
2. **`Super Admin` no tiene los 14 permisos de módulo.** En `sgc_db` tiene 96 permisos (los generados por modelo), no los 110. El acceso total funciona por el `Gate::before` de `AppServiceProvider`; el seeder replica ese estado.
3. **`superadmin@yaracuy.gob.ve` no tiene rol asignado.** Se replicó el estado real, pero es un riesgo operativo: conviene asignarle `Super Admin`.
4. **Usuarios inactivos:** `test@example.com`, `director@example.com` y `usuario@example.com` tienen `status = false`.
5. **`Consejo Legislativo del estado Yaracuy` (CLEY) no tiene `tipo_institucion_id`.** Se replicó el estado real.
6. **Nombre de rol corrupto en `app/Providers/AppServiceProvider.php`:** el string `Sǧper Administrador` tiene un carácter inválido; el rol real es `Super Admin`. El código funciona por el segundo elemento del array, pero conviene corregir la cadena.
7. **Dos CHECK constraints** (`poas_estado_check`, `prestamos_archivos_estado_prestamo_check`) difieren solo en el formato textual que genera PostgreSQL. Son equivalentes.
8. **La tabla `migrations` de `sgc_db`** aún contiene los 10 registros obsoletos hasta que se ejecute la migración de poda (sección 7).

---

## 6. Validación ejecutada

```powershell
# 1. Base limpia
psql -c "DROP DATABASE IF EXISTS sgc_audit2;" -c "CREATE DATABASE sgc_audit2;"

# 2. Migraciones desde cero
$env:DB_DATABASE='sgc_audit2'
php artisan migrate:fresh --force      # ✅ 59 migraciones OK

# 3. Seeders
php artisan db:seed --force            # ✅ 9 seeders OK

# 4. Idempotencia
php artisan db:seed --force            # ✅ sin duplicados
```

Conteos obtenidos tras el doble seed:

| Entidad | Filas |
|---|---:|
| roles | 12 |
| permissions | 110 |
| users | 14 |
| instituciones | 28 |
| jefaturas | 10 |
| tipo_instituciones | 7 |
| titulos | 6 |
| gobernadores | 2 |
| sedes / estantes / gavetas | 2 / 2 / 2 |

La comparación automática `sgc_db` vs `sgc_audit2` devolvió **OK** para columnas, índices, FKs, roles, permisos, usuarios, instituciones, jefaturas, títulos, gobernadores y archivo físico.

---

## 7. Cómo aplicar en `sgc_db` (entorno real)

> ⚠️ Hacer respaldo antes de cualquier cambio.

```powershell
# 1. Respaldo lógico
$env:PGPASSWORD='123456'
& "C:\pgsql\bin\pg_dump.exe" -h 127.0.0.1 -p 5432 -U postgres -Fc -d sgc_db -f "C:\backups\sgc_db_2026-11-05.dump"

# 2. Aplicar la poda de registros obsoletos de la tabla migrations
php artisan migrate --force

# 3. (Opcional) Re-sembrar catálogos/seguridad.
#    Los seeders son idempotentes y replican el estado actual, por lo que
#    no deberían introducir cambios; aun así, revisar el diff de datos.
php artisan db:seed --force
```

### Verificación posterior

```sql
-- 10 registros obsoletos eliminados
SELECT migration FROM migrations
WHERE migration IN (
  '2026_04_29_041257_create_institutions_table',
  '2026_04_29_041258_add_institution_id_to_users_table',
  '2026_05_26_135218_create_sumario_gacetas_table',
  '2026_05_29_032050_drop_tipo_acto_check_constraint_from_sumarios_gacetas',
  '2026_06_12_002950_drop_sumario_gacetas_table',
  '2026_06_12_014500_drop_institutions_table',
  '2026_07_24_190941_create_ubicacion_fisicas_table',
  '2026_05_27_140628_create_titulos_table',
  '2026_05_27_140629_create_gobernadors_table',
  '2026_05_27_140630_add_gobernador_id_to_gacetas_table'
);

-- Debe devolver 0 filas.
```

### Reversión

Los archivos eliminados y modificados están versionados en Git; si hiciera falta:

```powershell
git checkout -- database/migrations database/seeders
```

La poda de `migrations` no borra datos ni esquema; no requiere reversión funcional.

---

## 8. Notas finales

- `resources/views/jefaturas/pdf.blade.php` **no fue tocado** (trabajo ajeno en curso).
- Los cambios visuales previos (paleta, `action-button`, submenú, `routes/web.php`) siguen **sin commit** y son independientes de esta auditoría.
- Se recomienda commitear esta limpieza en dos bloques: `database/migrations` y `database/seeders`, para facilitar la revisión.
