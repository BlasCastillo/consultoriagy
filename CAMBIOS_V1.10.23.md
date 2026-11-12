# Cambios de la versión v1.10.23 — SGCJ

**Proyecto:** Sistema de Gestión de Consultoría Jurídica (SGCJ)  
**Repositorio:** `consultoriagy`  
**Rama de trabajo:** `version-1.10.22`  
**Tag de referencia:** `v1.10.23` (HEAD = `05c3d3f`)  
**Fecha de este documento:** 12/11/2026  
**Autor del trabajo:** Equipo de desarrollo SGCJ

---

## 1. Resumen ejecutivo

La versión **v1.10.23** agrupa dos momentos de trabajo:

1. **Commit `05c3d3f`** — correcciones de PDF, títulos de actos, carga masiva de PDFs y normalización del año político 2026.
2. **Cambios aún sin commitear en el árbol de trabajo** — implantación en red local, corrección de los límites de carga, editor de recorte de PDF por acto administrativo y eliminación de la doble portada en gacetas históricas del módulo Archivo.

> **Importante para el colega:** los cambios del segundo bloque **no están commiteados**. Antes de compartir la rama o desplegar en otra máquina hay que revisarlos y commitearlos (o generar un parche). El comando `git status --short` muestra el detalle exacto.

### Problemas que se resolvieron

| # | Problema | Solución aplicada |
|---|---|---|
| 1 | La vista previa del PDF fallaba en equipos sin enlace `public/storage`. | El PDF se sirve por controlador (`gacetas.pdf`, `gacetas.actos.pdf.ver`). |
| 2 | El título del acto y el encabezado de página tapaban contenido del PDF. | Se reserva una banda superior y se escala la hoja original. |
| 3 | El año político 2026 estaba guardado como `168`/`CLXVIII`. | Migración que lo normaliza a `CXVIII` y ayudas en los formularios. |
| 4 | Subir los PDFs de los actos uno por uno era lento. | Carga masiva con un solo formulario. |
| 5 | Las gacetas históricas de ~120 MB no subían por el tope de 20 MB. | Topes centralizados en `config/sgcj.php` y `php.ini` ajustado. |
| 6 | No se podían quitar hojas o zonas con datos de un acto ya digitalizado. | Editor visual de recorte con PDF.js + FPDI, con respaldo del original. |
| 7 | Las gacetas históricas mostraban doble portada. | Se omite la portada HTML y la regeneración del PDF para `es_retroactiva = true`. |
| 8 | El sistema solo era accesible desde el propio equipo. | Servidor en `0.0.0.0`, Reverb en 8081 y reglas de firewall restringidas por subred. |

---

## 2. Estado de la rama y línea de tiempo

```text
4d0bb0a  (tag v1.10.22)  Release v1.10.22
   │
   └── 05c3d3f  (tag v1.10.23, HEAD)  Corrección de error en PDF, títulos de actos
                                        y conversión de número a romano
          │
          └── Cambios sin commitear (12/11/2026)
              ├── Implantación / red local
              ├── Límites de carga
              ├── Recorte de PDF por acto
              └── Doble portada en gacetas históricas
```

### 2.1. Commit ya registrado

- **Hash:** `05c3d3f`
- **Mensaje:** `Correccion de error en PDF, titulos de actos y conversion de numero a romano`
- **Fecha:** 09/11/2026 15:20:55 (-04:00)
- **Archivos:** 12 archivos, 525 inserciones, 89 eliminaciones.

### 2.2. Cambios pendientes de commit

Archivos modificados:

```text
.env.example
app/Http/Controllers/ArchivoController.php
app/Http/Controllers/EvidenciaController.php
app/Http/Controllers/FichaActividadController.php
app/Http/Controllers/GacetaController.php
app/Http/Controllers/SolicitudController.php
app/Services/GacetaPdfService.php
iniciar_sgcj.bat
resources/views/archivo/retroactiva.blade.php
resources/views/gacetas/partials/preview-pdf.blade.php
resources/views/gacetas/show.blade.php
resources/views/gacetas/upload_pdf.blade.php
resources/views/poa/fichas/create.blade.php
routes/web.php
```

Archivos nuevos:

```text
abrir_firewall_sgcj.bat
app/Services/RecortePdfService.php
config/sgcj.php
detener_sgcj.bat
resources/views/gacetas/recortar_pdf.blade.php
```

---

## 3. Bloque A — Correcciones de PDF, títulos y año político (commit `05c3d3f`)

### A.1. Vista previa y descarga del PDF por controlador

**¿Qué se modificó?**

- `app/Http/Controllers/GacetaController.php`
- `app/Services/GacetaPdfService.php`
- `routes/web.php`
- `resources/views/gacetas/show.blade.php`
- `resources/views/gacetas/revision.blade.php`
- `resources/views/gacetas/partials/preview-pdf.blade.php`

**¿Cómo se modificó?**

1. Se agregaron dos acciones nuevas en `GacetaController`:
   - `verPdfActo($gaceta, $acto)`: entrega el PDF de un acto con `response()->file()`.
   - `verPdf($gaceta)`: entrega el PDF consolidado o el mejor PDF disponible.
2. Se agregaron las rutas:
   - `GET gacetas/{gaceta}/actos/{acto}/pdf/ver` → `gacetas.actos.pdf.ver`.
   - `GET gacetas/{gaceta}/pdf` → `gacetas.pdf`, protegida con `permission:leer Gaceta|actualizar Gaceta|crear Gaceta`.
3. `GacetaPdfService` incorporó:
   - `tienePdfDisponible(Gaceta $gaceta): bool`: comprueba en disco cuál PDF existe realmente.
   - `obtenerRutaVistaPrevia(Gaceta $gaceta): ?string`: devuelve la mejor ruta disponible.
4. Las vistas dejaron de construir enlaces con `asset('storage/...')` y ahora usan `route('gacetas.pdf', ...)` o `route('gacetas.actos.pdf.ver', ...)`.

**¿Por qué se modificó?**

Porque la vista previa dependía del enlace simbólico `public/storage`, que no siempre existe en todos los equipos. Además, cuando la base de datos tenía una ruta registrada pero el archivo no existía en el disco, la vista mostraba un error genérico o una hoja en blanco.

**Orden de preferencia de `obtenerRutaVistaPrevia()`:**

1. PDF consolidado de los actos (`archivo_pdf_completo`).
2. PDF final publicado (`pdf_final`).
3. Unión al vuelo de los PDFs de los actos (si todos existen).
4. Escaneo físico heredado (`ruta_archivo`).
5. `null` si no hay ningún archivo legible.

**Resultado alcanzado:**

- La vista previa funciona con o sin `storage:link`.
- Si un archivo no existe, la interfaz muestra un mensaje claro en lugar de fallar en silencio.
- No se generan PDFs duplicados si ya hay un consolidado disponible.

---

### A.2. Carga masiva de PDFs por acto administrativo

**¿Qué se modificó?**

- `app/Http/Controllers/GacetaController.php` → `subirPdfsActos()`.
- `routes/web.php` → `POST gacetas/{gaceta}/actos/pdf` (`gacetas.actos.pdf.bulk`).
- `resources/views/gacetas/upload_pdf.blade.php`.

**¿Cómo se modificó?**

1. Se creó un formulario oculto único (`#bulk-upload-form`) que envía todos los archivos juntos.
2. Cada acto tiene un input con nombre `archivos[{acto_id}]`.
3. El controlador:
   - Valida que haya al menos un archivo.
   - Valida que todos sean PDF.
   - Reemplaza el PDF anterior si existía.
   - Cuenta cuántos se subieron y registra la actividad.
   - Invalida el PDF consolidado.
4. La vista muestra una confirmación con SweetAlert antes de subir y el estado de cada acto:
   - `PDF` si existe en disco.
   - `Falta archivo` si está registrado en BD pero no en disco.
   - `Pendiente` si no tiene archivo.
5. Se eliminó el campo “PDF Maestro” del formulario de envío a revisión. Ahora el flujo obliga a cargar el PDF de cada acto.

**¿Por qué se modificó?**

El digitalizador tenía que repetir el proceso de selección y envío por cada acto. En gacetas con muchos actos esto era lento y propenso a errores. Además, el flujo anterior permitía enviar a revisión con archivos registrados pero inexistentes.

**Resultado alcanzado:**

- Se pueden subir varios PDFs en una sola operación.
- El envío a revisión solo pasa si cada acto tiene su PDF **y el archivo existe en el disco**.
- Los errores se muestran por acto, sin bloquear el resto de la carga.

---

### A.3. Invalidación automática del PDF consolidado

**¿Qué se modificó?**

- `app/Http/Controllers/GacetaController.php` → `invalidarPdfConsolidado()`.

**¿Cómo se modificó?**

Se eliminó el archivo `archivo_pdf_completo` y se limpió el campo en la base de datos cada vez que:

- Se sube un PDF individual a un acto.
- Se suben PDFs en lote.
- Se elimina el PDF de un acto.
- Se aplica un recorte a un acto.

La invalidación **no se ejecuta** si la gaceta ya está publicada, para no alterar un documento oficial.

**¿Por qué se modificó?**

Porque el consolidado se generaba una vez y quedaba “congelado”. Si luego se reemplazaba el PDF de un acto, la vista previa seguía mostrando el archivo viejo.

**Resultado alcanzado:**

- La próxima vista previa refleja siempre los PDFs actuales.
- Las gacetas publicadas conservan su PDF oficial sin cambios.

---

### A.4. Títulos de actos y encabezados sin tapar contenido

**¿Qué se modificó?**

- `app/Services/GacetaPdfService.php`.

**¿Cómo se modificó?**

1. Se agregó la constante `ALTO_BANDA_ENCABEZADO = 12.0` (mm).
2. Se creó `agregarPaginaEscalada(Fpdi $pdf, $tplId, float $bandaSuperior)`:
   - Calcula el tamaño original de la hoja.
   - Reserva una banda superior.
   - Escala el contenido proporcionalmente para que quepa debajo de la banda.
   - Centra horizontalmente el contenido.
3. `altoBandaTitulo()` reserva 24 mm si el título tiene dos líneas y 18 mm si tiene una.
4. `estamparTituloActo()` centra verticalmente el texto dentro de la banda.
5. `mergeActPdfs()` y `generarPdfFinal()` ahora usan `agregarPaginaEscalada()` en lugar de dibujar la hoja a tamaño completo y escribir encima.

**¿Por qué se modificó?**

Antes el título institucional y el encabezado “PAGINA N° X / GACETA OFICIAL DEL ESTADO YARACUY” se dibujaban sobre la hoja original. En actos con contenido en el borde superior, el texto tapaba parte del documento.

**Resultado alcanzado:**

- El título y el encabezado quedan en una banda propia.
- El contenido del acto nunca se cubre.
- El PDF conserva su contenido vectorial (no se convierte a imagen).

---

### A.5. Año político 2026 = CXVIII

**¿Qué se modificó?**

- Nueva migración: `database/migrations/2026_11_09_000000_corregir_ano_politico_gacetas_2026.php`.
- Formularios: `gacetas/create.blade.php`, `gacetas/edit.blade.php`, `gacetas/checklist.blade.php`, `archivo/retroactiva.blade.php`.

**¿Cómo se modificó?**

1. La migración actualiza los registros de gacetas del año 2026 que tenían:
   - `168`
   - `168°`
   - `168º`
   - `CLXVIII`
   
   y los deja en `CXVIII`.
2. Los formularios cambiaron el ejemplo de `168°` a `118°` y agregaron el texto:
   > “Escriba el año en números (ej. 118); se guardará como CXVIII.”
3. El formulario de edición aclara que se puede escribir en números (`118`) o en romano (`CXVIII`).

**¿Por qué se modificó?**

El formulario mostraba `168°` como ejemplo y algunos usuarios lo copiaron como si fuera el año político correcto. El año 2026 corresponde al **CXVIII (118)**, no al CLXVIII (168).

La conversión de números arábigos a romanos ya existía en `app/Models/Gaceta.php` mediante `numeroARomano()` y los mutadores `setAnoPoliticoAttribute()` / `setMesPoliticoAttribute()`. En este bloque se corrigieron los datos históricos y se mejoraron las ayudas visuales.

**Resultado alcanzado:**

- Los registros de 2026 quedaron normalizados.
- Los nuevos registros se guardan directamente en romano.
- El usuario entiende qué debe escribir.

> La migración **no se revierte** en `down()` porque `CXVIII` es el valor correcto para 2026.

---

### A.6. Validación de archivos realmente existentes

**¿Qué se modificó?**

- `GacetaController::enviarARevision()`.
- `GacetaController::publicarGaceta()`.
- `GacetaController::uploadPdf()` (vista).
- `GacetaController::show()` y `revisarPdfFinal()`.

**¿Cómo se modificó?**

Se cambió la comprobación de “el campo tiene valor” por “el campo tiene valor **y** el archivo existe en el disco”:

```php
empty($acto->archivo_pdf) || !$storage->exists($acto->archivo_pdf)
```

También se agregó `$pdfDisponible` a las vistas para distinguir entre:

- “El digitalizador aún no ha subido el documento”.
- “El archivo fue registrado pero no existe en el almacenamiento”.

**¿Por qué se modificó?**

Una gaceta podía quedar en un estado inconsistente: la base de datos decía que había PDF, pero el archivo se había borrado o nunca se copió. El sistema intentaba consolidar y fallaba.

**Resultado alcanzado:**

- No se puede enviar a revisión ni publicar con archivos faltantes.
- La interfaz explica exactamente qué hacer para recuperar el flujo.

---

### A.7. Resultados del Bloque A

- Vista previa y descarga funcionando sin depender del enlace simbólico.
- Carga de actos en lote.
- Consolidado siempre coherente con los archivos reales.
- Títulos y encabezados sin superposición.
- Año político 2026 corregido.
- Mensajes de error claros para archivos faltantes.

---

## 4. Bloque B — Implantación en red local

Este bloque corresponde a los ajustes necesarios para que el sistema funcione desde otras computadoras de la red, sin cambiar el perfil de red de Windows ni afectar las carpetas compartidas.

### B.1. `iniciar_sgcj.bat`

**¿Qué se modificó?**

Se reescribió el arranque para trabajar en red local:

| Antes | Ahora |
|---|---|
| `php artisan serve --host=127.0.0.1 --port=8000` | `php artisan serve --host=0.0.0.0 --port=8000` |
| `npm run dev` en una terminal | Se usan los assets compilados de `public/build` |
| Reverb en el puerto por defecto | Reverb en `0.0.0.0:8081` |
| Sin información de red | Mensajes con IP local y URL LAN |

**Variables del script:**

```bat
set "SGCJ_LAN_IP=172.16.0.108"
set "SGCJ_HTTP_PORT=8000"
set "SGCJ_REVERB_PORT=8081"
```

**¿Por qué se modificó?**

- `127.0.0.1` solo escucha en el propio equipo; otras PC no pueden entrar.
- `npm run dev` levanta un servidor en `localhost:5173` que no existe desde otra máquina.
- El puerto 8080 estaba ocupado por otro servicio (`AgentService.exe`), por eso Reverb se movió a **8081**.

**Resultado alcanzado:**

- El servidor Laravel escucha en todas las interfaces.
- Las otras PC consumen los assets compilados, sin depender de Vite.
- Reverb funciona en 8081.
- El script muestra las URLs de acceso local y LAN.

---

### B.2. `abrir_firewall_sgcj.bat` (nuevo)

**¿Qué hace?**

1. Verifica que se ejecute como administrador.
2. Elimina las reglas anteriores de SGCJ, si existen.
3. Crea dos reglas de entrada:
   - `SGCJ Laravel 8000` → TCP 8000.
   - `SGCJ Reverb 8081` → TCP 8081.
4. Aplica las reglas a **cualquier perfil** (`profile=any`), pero restringe los clientes a las subredes:

```text
172.16.0.0/16,192.168.137.0/24
```

5. Muestra el estado de los perfiles de red, **sin modificarlos**.

**¿Por qué se hizo así?**

Cambiar el perfil de red de “Pública” a “Privada” puede alterar el comportamiento de las carpetas compartidas y de otras reglas del equipo. La solución más segura fue dejar el perfil como está y crear reglas que solo acepten tráfico desde las subredes autorizadas.

**Resultado alcanzado:**

- Otras PC de la red acceden a `http://172.16.0.108:8000`.
- Las notificaciones en tiempo real funcionan por `ws://172.16.0.108:8081`.
- El perfil de red y las carpetas compartidas no se tocan.

> **Pendiente operativo:** este script debe ejecutarse **como administrador**. Si no se ejecuta, el firewall seguirá bloqueando el acceso desde otras máquinas.

---

### B.3. `detener_sgcj.bat` (nuevo)

**¿Qué hace?**

Detiene únicamente los procesos de SGCJ:

```text
artisan serve
artisan reverb:start
artisan queue:work
artisan schedule:work
```

Luego verifica los puertos 8000 y 8081.

**¿Por qué se creó?**

Al reiniciar el servidor quedaban procesos hijos ocupando el puerto 8000. El script evita tener que buscar los PID manualmente y no afecta otros procesos de PHP ajenos al proyecto.

**Resultado alcanzado:**

- Reinicio limpio de servicios.
- Menos errores de “puerto en uso”.

---

### B.4. Variables de entorno para la LAN

En `.env` (y documentado en `.env.example`):

```env
APP_URL=http://172.16.0.108:8000

REVERB_HOST="127.0.0.1"
REVERB_PORT=8081
REVERB_SERVER_HOST=0.0.0.0
REVERB_SERVER_PORT=8081

VITE_REVERB_HOST="172.16.0.108"
```

**¿Por qué?**

- `APP_URL` debe ser la URL que usan las otras PC.
- `REVERB_SERVER_HOST=0.0.0.0` permite que Reverb acepte conexiones de la red.
- `VITE_REVERB_HOST` debe apuntar a la IP del servidor y requiere **recompilar** con `npm run build`.

**Si cambia la IP del servidor:**

1. Actualizar `APP_URL` y `VITE_REVERB_HOST` en `.env`.
2. Actualizar `SGCJ_LAN_IP` en `iniciar_sgcj.bat`.
3. Actualizar `SGCJ_REDES` en `abrir_firewall_sgcj.bat`.
4. Ejecutar `npm run build` y `php artisan optimize:clear`.

---

### B.5. Resultados del Bloque B

- Acceso desde otras PC de la red.
- Notificaciones en tiempo real por Reverb en 8081.
- Firewall restringido por subred, sin cambiar el perfil de red.
- Arranque y parada controlados con scripts.

---

## 5. Bloque C — Corrección de los límites de carga

### C.1. Configuración centralizada: `config/sgcj.php` (nuevo)

```php
'archivos' => [
    'max_pdf_kb'    => (int) env('SGCJ_MAX_PDF_KB', 512000), // 500 MB
    'max_imagen_kb' => (int) env('SGCJ_MAX_IMAGEN_KB', 10240), // 10 MB
],
```

**¿Por qué se creó?**

Los topes estaban repetidos y hardcodeados en varios controladores (`20480`, `51200`, `10240`, `5120`). Eso hacía que un cambio requiriera editar muchos archivos y era fácil dejar un módulo con un límite distinto.

**Resultado alcanzado:**

- Un solo lugar define los topes.
- Se pueden cambiar por `.env` sin tocar código.
- Todos los módulos usan el mismo valor.

---

### C.2. `php.ini`

Archivo: `C:\xampp\php\php.ini`

| Directiva | Valor |
|---|---|
| `upload_max_filesize` | `512M` |
| `post_max_size` | `1024M` |
| `memory_limit` | `1024M` |
| `max_execution_time` | `600` |
| `max_input_time` | `600` |
| `max_file_uploads` | `60` |

**¿Por qué?**

Laravel valida con `max:512000` (500 MB), pero PHP corta la subida antes si `upload_max_filesize` o `post_max_size` son menores. Con los valores anteriores (20 MB / 8 MB) una gaceta de 120 MB nunca llegaba a Laravel.

**Resultado alcanzado:**

- Un PDF de 120 MB pasa la validación y se sube correctamente.
- Hay margen para archivos de hasta 500 MB.
- Se permiten hasta 60 archivos por petición (carga masiva).

> **Nota:** PHP CLI puede reportar `max_execution_time=0` y `max_input_time=-1` porque la SAPI CLI no aplica esos límites. El servidor web sí toma los valores de `php.ini`.

---

### C.3. Controladores actualizados

| Archivo | Regla anterior | Regla nueva |
|---|---|---|
| `ArchivoController::guardarRetroactiva()` | `max:20480` | `max:` + `config('sgcj.archivos.max_pdf_kb')` |
| `GacetaController::subirPdfActo()` | `max:20480` | `max:` + `config(...max_pdf_kb)` |
| `GacetaController::subirPdfsActos()` | `max:20480` y mensaje “20 MB” | `max:` + config y mensaje dinámico |
| `GacetaController::savePdf()` | `max:10240` | `max:` + `config(...max_pdf_kb)` |
| `GacetaController::enviarARevision()` | `max:20480` | `max:` + `config(...max_pdf_kb)` |
| `SolicitudController::store()` | `max:51200` | `max:` + `config(...max_pdf_kb)` |
| `SolicitudController::update()` | `max:51200` | `max:` + `config(...max_pdf_kb)` |
| `EvidenciaController::store()` | `max:5120` | `max:` + `config(...max_pdf_kb)` |
| `FichaActividadController::store()` | `max:5120` | `max:` + `config(...max_imagen_kb)` |

**¿Por qué?**

Para que el histórico de gacetas y todos los módulos que suben archivos acepten los tamaños reales del trabajo diario, no solo el módulo de Archivo.

**Resultado alcanzado:**

- Todos los módulos comparten el mismo tope de PDF.
- Las imágenes de fichas usan el tope de imágenes (10 MB).
- Los mensajes de error muestran el límite correcto en MB.

---

### C.4. Vistas actualizadas

| Vista | Cambio |
|---|---|
| `resources/views/archivo/retroactiva.blade.php` | Etiqueta dinámica: “Máx. {{ intdiv(config(...max_pdf_kb), 1024) }} MB”. |
| `resources/views/poa/fichas/create.blade.php` | “PNG, JPG, GIF hasta {{ intdiv(config(...max_imagen_kb), 1024) }}MB”. |

**¿Por qué?**

Para que el usuario vea el límite real y no un texto fijo que ya no coincide con la validación.

---

### C.5. `.env.example`

Se agregaron:

```env
SGCJ_MAX_PDF_KB=512000
SGCJ_MAX_IMAGEN_KB=10240
```

También se actualizaron las variables de Reverb a 8081 y se documentó que `VITE_REVERB_HOST` debe cambiarse a la IP del servidor en red local.

**Resultado alcanzado:**

- Un entorno nuevo conoce los topes desde el primer arranque.
- Se corrigió el salto de línea final del archivo.

---

### C.6. Resultados del Bloque C

- Subida de PDFs de hasta ~500 MB.
- Prueba realizada con un PDF de **120 MB**: pasó `required|mimes:pdf|max:512000`.
- `php_upload_max_filesize=512M` y `php_post_max_size=1024M` verificados.
- Sin coincidencias de topes antiguos (`20480`, `51200`, `20 MB`, `max:5120`, `max:10240`) en `app`, `resources`, `routes` y `config`.

---

## 6. Bloque D — Editor de recorte de PDF por acto administrativo

### D.1. Objetivo

Permitir que el digitalizador, desde `upload_pdf`, pueda:

- Quitar hojas completas del PDF de un acto.
- Recortar una zona de una hoja (por ejemplo, un sello o dato que no debe publicarse).
- Conservar el resto del documento sin convertirlo a imagen.

### D.2. Servicio de recorte: `app/Services/RecortePdfService.php` (nuevo)

**¿Cómo funciona?**

1. Recibe:
   - Ruta del PDF original.
   - Lista de páginas a conservar.
   - Ruta de destino.
2. Ordena las páginas por número.
3. Para cada página:
   - Si no tiene recorte, la copia completa.
   - Si tiene recorte, calcula el área normalizada (`x`, `y`, `w`, `h`) y genera una página del tamaño del recorte.
   - Dibuja el template desplazado hacia arriba/izquierda; FPDF recorta automáticamente lo que queda fuera.
4. Ignora páginas duplicadas o fuera de rango.
5. Exige al menos una página y un recorte mínimo del **5%** de la hoja.
6. Usa `FPDI`, la misma librería de `GacetaPdfService`, para conservar el contenido vectorial.

**¿Por qué FPDI?**

Porque convierte el PDF a imágenes sería más pesado, perdería calidad y dificultaría la lectura del documento oficial.

**Resultado alcanzado:**

- Se pueden eliminar hojas y recortar zonas sin perder resolución.
- El archivo resultante sigue siendo un PDF vectorial.

---

### D.3. Controlador

**Métodos nuevos en `GacetaController`:**

#### `mostrarRecortePdf($gaceta, $acto)`

- Verifica que el acto exista y pertenezca a la gaceta.
- Verifica que el PDF exista en el disco.
- Envía a la vista:
  - Gaceta.
  - Acto.
  - URL del PDF (`gacetas.actos.pdf.ver`).
  - Tamaño actual en MB.

#### `guardarRecortePdf(Request, $gaceta, $acto, RecortePdfService)`

1. Valida que llegue el campo `paginas` (JSON).
2. Decodifica la lista de páginas y recortes.
3. Verifica que el PDF original siga existiendo.
4. Genera el PDF nuevo en un archivo temporal.
5. **Respalda el original** en:
   ```text
   storage/app/public/gacetas/actos/{gaceta_id}/respaldos/
   ```
   con nombre:
   ```text
   {nombre_original}_{Ymd_His}.pdf
   ```
6. Elimina el original y mueve el temporal a la misma ruta registrada en la BD.
7. Si el reemplazo falla:
   - Restaura el original desde el respaldo.
   - Borra el temporal.
   - Devuelve un mensaje de error.
8. Invalida el PDF consolidado.
9. Registra la actividad en el log `BPM_Gacetas`.
10. Redirige a `upload_pdf` con un mensaje de éxito que indica cuántas hojas se conservaron.

**¿Por qué se respalda el original?**

Porque el recorte es destructivo: reemplaza el archivo que ya está asociado al acto. El respaldo permite recuperar el documento si el recorte fue incorrecto.

**Resultado alcanzado:**

- El recorte se aplica de forma segura.
- Si algo falla, el original se restaura.
- Queda trazabilidad en el log de actividad.

---

### D.4. Rutas

Dentro del grupo del **Digitalizador** (`permission:crear Gaceta`):

```php
Route::get('gacetas/{gaceta}/actos/{acto}/recortar',
    [GacetaController::class, 'mostrarRecortePdf'])->name('gacetas.actos.recortar');

Route::post('gacetas/{gaceta}/actos/{acto}/recortar',
    [GacetaController::class, 'guardarRecortePdf'])->name('gacetas.actos.recortar.save');
```

**¿Por qué solo el digitalizador?**

Porque el recorte forma parte del proceso de digitalización y debe ejecutarlo quien sube los actos. No se otorgó el permiso a otros roles para evitar cambios accidentales en documentos ya revisados.

---

### D.5. Vista: `resources/views/gacetas/recortar_pdf.blade.php` (nueva)

**Funcionalidad:**

- Visor con PDF.js que renderiza cada hoja como una tarjeta.
- Checkbox **“Conservar hoja”** por página.
- Botón **“Recortar zona”** para dibujar un rectángulo sobre la hoja.
- Botón **“Quitar recorte”** para eliminar la selección.
- Contadores:
  - Hojas totales.
  - Hojas a conservar.
- Botones **“Conservar todas”** y **“Excluir todas”**.
- Al guardar:
  - Se construye un JSON con las páginas y recortes.
  - Se envía al controlador.
  - SweetAlert pide confirmación.
- Si no se conserva ninguna hoja, el botón no permite guardar.

**¿Por qué se hizo en el navegador?**

Porque el digitalizador necesita ver exactamente qué hoja y qué zona está quitando antes de aplicar el cambio. Hacerlo a ciegas desde un formulario de texto sería inviable.

**Resultado alcanzado:**

- Editor visual e intuitivo.
- Confirmación antes de aplicar un cambio destructivo.
- Contador en tiempo real de las hojas que quedarán.

---

### D.6. Botón en `upload_pdf`

Junto a **“Ver PDF del acto”** se agregó:

```blade
<x-action-button :href="route('gacetas.actos.recortar', [$gaceta->id, $acto->id])"
    action="Recortar acto"
    icon="fa-solid fa-scissors"
    tone="edit" />
```

**¿Por qué?**

Para que el digitalizador encuentre la acción en el mismo lugar donde sube y revisa el PDF del acto, sin tener que navegar a otra pantalla.

**Resultado alcanzado:**

- La acción está disponible solo si el acto tiene un PDF existente.
- Se muestra con icono de tijera y tono de edición.

---

### D.7. Prueba funcional realizada

Se creó una gaceta y un acto temporales con un PDF de 4 hojas:

1. Se excluyó la hoja 2.
2. Se recortó la hoja 4.
3. Se guardó el recorte.

**Resultado:**

- PDF resultante con **3 hojas**.
- **1 respaldo** creado en `respaldos/`.
- Redirección correcta a `upload_pdf`.
- Mensaje flash de éxito.
- Limpieza total de los datos temporales:
  - `gacetas = 0`
  - `solicitudes = 0`
  - `actos = 0`

También se verificó el render de la vista:

```text
render_ok bytes=59357
tiene_boton=SI
tiene_js_pdf=SI
```

---

## 7. Bloque E — Evitar la doble portada en gacetas históricas (módulo Archivo)

### E.1. Causa

Las gacetas históricas que se suben desde **Archivo → Subir Gaceta Histórica** ya fueron publicadas y su PDF incluye la portada original. El sistema, sin embargo:

1. Mostraba la portada HTML + sumario en `gacetas/show.blade.php`.
2. Al descargar, regeneraba el PDF anteponiendo un sumario DomPDF al archivo subido.
3. En la vista previa, dibujaba el encabezado HTML “GACETA OFICIAL DEL ESTADO YARACUY” sobre cada página.

Resultado: **doble portada** y maquetación alterada.

### E.2. Cambios aplicados

#### `app/Services/GacetaPdfService.php`

- `generarPdfFinal()`:
  - Si `es_retroactiva` es verdadero y existe `archivo_pdf_completo`, devuelve ese archivo tal cual.
  - Si no, devuelve `pdf_final` si existe.
  - No antepone sumario ni estampa encabezados.
- `obtenerRutaDescarga()`:
  - Si `es_retroactiva` y existe `archivo_pdf_completo`, devuelve la ruta del original.

#### `resources/views/gacetas/show.blade.php`

```blade
@php
    $esHistorica = (bool) $gaceta->es_retroactiva;
@endphp

@unless($esHistorica)
    @include('gacetas.partials.portada')
@endunless
```

Y la vista previa se incluye con:

```blade
@include('gacetas.partials.preview-pdf', [
    'pdfUrl' => $pdfPreviewUrl,
    'incluirPortada' => !$esHistorica,
    'mostrarEncabezado' => !$esHistorica,
])
```

#### `resources/views/gacetas/partials/preview-pdf.blade.php`

Se agregaron las opciones:

- `$incluirPortada` (por defecto `true`): si la portada HTML ocupa la página 1.
- `$mostrarEncabezado` (por defecto `true`): si se dibuja el encabezado sobre cada página.

Para históricas:

- `incluirPortada = false` → la numeración empieza en la página 1 real del PDF.
- `mostrarEncabezado = false` → no se dibuja el encabezado HTML.

#### `resources/views/archivo/retroactiva.blade.php`

Se agregó la nota:

> “Suba la gaceta completa tal como fue publicada, con su portada original. El sistema no generará una portada adicional.”

**¿Por qué?**

Para dejar claro al usuario que no debe quitar la portada antes de subir el archivo y que el sistema respetará el documento original.

### E.3. Verificación realizada

Se creó una gaceta histórica temporal con un PDF de 2 páginas y se ejecutó:

```text
descarga_es_original=OK
final_es_original=OK
no_genero_archivo_nuevo=OK
historica_sin_portada_html=OK
historica_pagina_base_0=OK
historica_sin_encabezado=OK
normal_con_portada_html=OK
normal_pagina_base_1=OK
normal_con_encabezado=OK
limpieza=OK
```

**Resultado alcanzado:**

- Las gacetas históricas se muestran y descargan exactamente como se subieron.
- Las gacetas normales siguen generando su portada y sumario como antes.
- El cambio solo aplica cuando `es_retroactiva = true`.

---

## 8. Verificaciones generales ejecutadas

| Verificación | Comando | Resultado |
|---|---|---|
| Sintaxis PHP de los archivos tocados | `php -l app/...` | Sin errores |
| Compilación de vistas Blade | `php artisan view:cache` | Correcta |
| Rutas registradas | `php artisan route:list --name=gacetas` | Rutas nuevas visibles |
| Migraciones pendientes | `php artisan migrate --force` | `2026_11_09_000000...` ejecutada |
| Permisos RBAC | `php artisan db:seed --class=RolesAndPermissionsSeeder --force` | Correcto |
| Limpieza de caché | `php artisan optimize:clear` | Correcto |
| Servidor web | `GET /login` | HTTP 200 |
| PDF de 120 MB | Validación `max:512000` | Aprobada |
| Recorte E2E | Gaceta/acto temporales | 3 hojas, 1 respaldo |
| Gaceta histórica | Prueba de servicio y render | Todos los checks en OK |

### 8.1. Estado de los servicios al cierre

```text
Laravel: 0.0.0.0:8000
Reverb:  0.0.0.0:8081
```

También quedaron activos:

```text
php artisan queue:work
php artisan schedule:work
```

---

## 9. Guía paso a paso para el colega

### 9.1. Requisitos previos

- PHP 8.2 o superior.
- Composer.
- Node.js + npm.
- PostgreSQL 17 (base `sgc_db`).
- XAMPP en `C:\xampp` (para el entorno actual).
- Acceso de administrador para el firewall.

### 9.2. Preparar el proyecto

```powershell
cd C:\xampp\htdocs\proyectos\consultoriagy

# 1. Verificar rama y estado
git status
git log --oneline -5

# 2. Instalar dependencias PHP
composer install

# 3. Instalar dependencias JS
npm ci

# 4. Copiar variables de entorno (si es un entorno nuevo)
copy .env.example .env
php artisan key:generate
```

### 9.3. Configurar `.env`

Revisar como mínimo:

```env
APP_URL=http://IP_DEL_SERVIDOR:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=sgc_db
DB_USERNAME=postgres
DB_PASSWORD=...

REVERB_HOST="127.0.0.1"
REVERB_PORT=8081
REVERB_SERVER_HOST=0.0.0.0
REVERB_SERVER_PORT=8081
VITE_REVERB_HOST="IP_DEL_SERVIDOR"

SGCJ_MAX_PDF_KB=512000
SGCJ_MAX_IMAGEN_KB=10240
```

### 9.4. Preparar almacenamiento

```powershell
php artisan storage:link

# Directorio requerido por el servicio de PDFs
mkdir -Force storage\app\SGCJ
```

### 9.5. Base de datos y permisos

```powershell
php artisan migrate --force
php artisan db:seed --class=RolesAndPermissionsSeeder --force
```

> La migración `2026_11_09_000000_corregir_ano_politico_gacetas_2026` ya está aplicada en el entorno actual. En un entorno nuevo se ejecutará con el resto de migraciones.

### 9.6. Compilar el frontend

```powershell
npm run build
```

> No usar `npm run dev` para el despliegue en red: las otras PC no pueden acceder a `localhost:5173`.

### 9.7. Ajustar `php.ini`

Editar `C:\xampp\php\php.ini`:

```ini
upload_max_filesize=512M
post_max_size=1024M
memory_limit=1024M
max_execution_time=600
max_input_time=600
max_file_uploads=60
```

Guardar y **reiniciar Apache/PHP** (o el servidor que corresponda).

### 9.8. Limpiar caché

```powershell
php artisan optimize:clear
php artisan view:cache
```

### 9.9. Arrancar los servicios

```powershell
.\iniciar_sgcj.bat
```

Verificar:

```powershell
Get-NetTCPConnection -State Listen |
  Where-Object { $_.LocalPort -in 8000,8081 } |
  Select-Object LocalAddress, LocalPort, OwningProcess
```

Debe aparecer:

```text
0.0.0.0  8000  <PID>
0.0.0.0  8081  <PID>
```

### 9.10. Abrir el firewall (una vez, como administrador)

1. Clic derecho sobre `abrir_firewall_sgcj.bat`.
2. **Ejecutar como administrador**.
3. Verificar que se crearon las reglas:
   - `SGCJ Laravel 8000`
   - `SGCJ Reverb 8081`

Si no se ejecuta como administrador, el script se detiene y muestra el aviso.

### 9.11. Probar desde otra PC

```text
http://IP_DEL_SERVIDOR:8000
```

Si no carga:

1. Verificar que el servidor escuche en `0.0.0.0` (no `127.0.0.1`).
2. Verificar las reglas del firewall.
3. Verificar que la PC cliente esté en la subred autorizada.
4. Verificar que no haya otra instancia de `php artisan serve` ocupando el puerto.

### 9.12. Pruebas de humo recomendadas

1. **Subida grande:** subir una gaceta histórica de ~120 MB.
2. **Carga masiva:** subir varios PDFs de actos en una sola operación.
3. **Vista previa:** verificar que las páginas aparecen y que el título no tapa contenido.
4. **Recorte:** entrar a “Recortar acto”, excluir una hoja, recortar una zona, guardar y verificar:
   - PDF resultante.
   - Respaldo en `storage/app/public/gacetas/actos/{id}/respaldos/`.
   - Consolidado invalidado.
5. **Gaceta histórica:** abrir la vista legal y descargar; no debe aparecer doble portada.
6. **Gaceta normal:** verificar que sigue mostrando portada y sumario.
7. **Red:** abrir desde otra PC y comprobar notificaciones en tiempo real.

---

## 10. Archivos modificados y nuevos

### 10.1. Commit `05c3d3f`

| Archivo | Tipo de cambio |
|---|---|
| `app/Http/Controllers/GacetaController.php` | Rutas de vista previa, carga masiva, invalidación de consolidado, validación de archivos. |
| `app/Services/GacetaPdfService.php` | Bandas de encabezado, escalado de páginas, `tienePdfDisponible()`, `obtenerRutaVistaPrevia()`. |
| `database/migrations/2026_11_09_000000_corregir_ano_politico_gacetas_2026.php` | Normalización de año político 2026 a `CXVIII`. |
| `routes/web.php` | Rutas `gacetas.pdf`, `gacetas.actos.pdf.bulk`, `gacetas.actos.pdf.ver`. |
| `resources/views/gacetas/upload_pdf.blade.php` | Carga masiva, estados por acto, eliminación del PDF maestro. |
| `resources/views/gacetas/revision.blade.php` | Uso de `$pdfDisponible` y mensajes de archivo faltante. |
| `resources/views/gacetas/show.blade.php` | Uso de `$pdfDisponible` y mensajes de archivo faltante. |
| `resources/views/gacetas/partials/preview-pdf.blade.php` | Protección si PDF.js no carga y mejores mensajes. |
| `resources/views/gacetas/create.blade.php` | Ejemplo `118°` y ayuda de conversión. |
| `resources/views/gacetas/edit.blade.php` | Ejemplo `118°` y ayuda de conversión. |
| `resources/views/gacetas/checklist.blade.php` | Ejemplo `118°` y ayuda de conversión. |
| `resources/views/archivo/retroactiva.blade.php` | Ejemplo `118°` y ayuda de conversión. |

### 10.2. Cambios pendientes de commit

| Archivo | Tipo | Descripción |
|---|---|---|
| `config/sgcj.php` | Nuevo | Topes de PDF e imágenes. |
| `app/Services/RecortePdfService.php` | Nuevo | Recorte de hojas y zonas con FPDI. |
| `resources/views/gacetas/recortar_pdf.blade.php` | Nuevo | Editor visual de recorte. |
| `abrir_firewall_sgcj.bat` | Nuevo | Reglas de firewall restringidas por subred. |
| `detener_sgcj.bat` | Nuevo | Detiene solo los servicios de SGCJ. |
| `app/Http/Controllers/GacetaController.php` | Modificado | Recorte, límites dinámicos, doble portada. |
| `app/Services/GacetaPdfService.php` | Modificado | PDF original para gacetas históricas. |
| `app/Http/Controllers/ArchivoController.php` | Modificado | Límite de PDF dinámico. |
| `app/Http/Controllers/SolicitudController.php` | Modificado | Límite de PDF dinámico. |
| `app/Http/Controllers/EvidenciaController.php` | Modificado | Límite dinámico. |
| `app/Http/Controllers/FichaActividadController.php` | Modificado | Límite de imágenes dinámico. |
| `routes/web.php` | Modificado | Rutas de recorte. |
| `resources/views/gacetas/upload_pdf.blade.php` | Modificado | Botón “Recortar acto”. |
| `resources/views/gacetas/show.blade.php` | Modificado | Omite portada HTML en históricas. |
| `resources/views/gacetas/partials/preview-pdf.blade.php` | Modificado | Opciones de portada/encabezado. |
| `resources/views/archivo/retroactiva.blade.php` | Modificado | Nota sobre portada original y límite dinámico. |
| `resources/views/poa/fichas/create.blade.php` | Modificado | Límite de imágenes dinámico. |
| `.env.example` | Modificado | Variables SGCJ y Reverb. |
| `iniciar_sgcj.bat` | Modificado | Arranque en red local. |

---

## 11. Observaciones y pendientes

1. **Cambios sin commitear:** el trabajo de los bloques B, C, D y E está en el árbol de trabajo. Antes de entregar la rama:
   ```powershell
   git add -A
   git commit -m "v1.10.23: límites de carga, recorte de PDF, LAN y doble portada histórica"
   ```
2. **Nombre de rama:** la rama se llama `version-1.10.22`, pero el tag de HEAD es `v1.10.23`. Conviene decidir si se renombra la rama o se crea una nueva `version-1.10.23` antes de publicar.
3. **Firewall:** `abrir_firewall_sgcj.bat` requiere ejecución como administrador. En el entorno actual quedó pendiente esa ejecución manual.
4. **`EvidenciaController`:** usa `max_pdf_kb` para PDFs e imágenes. Si se desea un tope distinto para imágenes, habría que separar la regla por MIME o usar `max_imagen_kb` para imágenes y otra validación para PDFs.
5. **`php artisan serve` es monohilo:** una subida de 120 MB bloquea otras peticiones mientras se procesa. Para varios usuarios simultáneos se recomienda Apache con un virtual host apuntando a `public/`.
6. **PostgreSQL:** escucha en `0.0.0.0:5432`. Conviene restringirlo a `localhost` si la base no necesita acceso remoto (requiere administrador y reinicio del servicio).
7. **Directorios huérfanos:** existen `storage/app/public/gacetas/actos/42` y `/43` sin registros en la BD. No se eliminaron por si contienen respaldos útiles.
8. **Copia anidada:** existe `consultoriagy\consultoriagy\` (24 MB, con `.git`) sin seguimiento. No se eliminó porque no fue confirmado por el usuario.

---

## 12. Cómo revertir cambios

### 12.1. Revertir el commit `05c3d3f`

```powershell
git revert 05c3d3f
```

> La migración de año político no se revierte automáticamente (`down()` es intencionalmente vacío). Si se necesita volver atrás, actualizar manualmente los registros.

### 12.2. Revertir cambios sin commitear

Revisar primero:

```powershell
git diff
git status
```

Luego, para descartar cambios de un archivo específico:

```powershell
git restore ruta\del\archivo
```

Para eliminar archivos nuevos no deseados:

```powershell
git clean -n   # ver qué se eliminaría
git clean -f   # eliminar (¡cuidado!)
```

### 12.3. Recuperar un PDF recortado

Los originales están en:

```text
storage/app/public/gacetas/actos/{gaceta_id}/respaldos/
```

Copiar el respaldo sobre el archivo actual del acto y luego invalidar el consolidado (o volver a guardar cualquier PDF del acto desde la interfaz).

---

## 13. Conclusión

La versión **v1.10.23** deja el flujo de gacetas más robusto y usable:

- El PDF se sirve sin depender de enlaces simbólicos.
- Los títulos y encabezados no interfieren con el contenido.
- El año político está normalizado.
- Los actos se cargan en lote.
- Los archivos grandes ya no chocan con el límite de 20 MB.
- El digitalizador puede recortar hojas y zonas con respaldo automático.
- Las gacetas históricas conservan su portada original sin duplicados.
- El sistema es accesible desde la red local con reglas de firewall seguras y acotadas.

Con los pasos de la sección 9, cualquier compañero puede reproducir el entorno y validar los cambios.
