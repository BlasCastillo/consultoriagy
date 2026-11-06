---
name: estandar-laravel-sgcj
description: Estándar de arquitectura Laravel 12, nomenclatura 100% en español y sincronización RBAC para SGCJ.
---

# Skill: Estándar Laravel 12 y Unificación en Español (SGCJ)

## 1. Modelos y Base de Datos (`app/Models/*.php`)
- Definir siempre `protected $table` explícito coincidiendo con la tabla en PostgreSQL (`sgc_db`).
- Mantener `$fillable` sincronizado con las columnas reales de la tabla.
- En modelos con columnas heredadas (como `Jefatura`), mantener sincronizadas las columnas en el modelo/controlador o limpiarlas mediante migración segura.

## 2. Permisos y RBAC (`ModelPermissionAuditor` y `RolesAndPermissionsSeeder`)
- Acciones estándar en español: `crear`, `leer`, `actualizar`, `eliminar`.
- Módulos en español singular claro: `Usuario`, `Rol`, `Permiso`, `Institucion`, `TipoInstitucion`, `Jefatura`, `Gobernador`, `Titulo`, `Solicitud`, `Gaceta`, `ActoAdministrativo`, `PrestamoArchivo`, `InventarioArchivo`, `Sede`, `Estante`, `Gaveta`, `Poa`, `ActividadPoa`, `MetaTrimestral`, `Replanificacion`, `FichaActividad`, `ImagenFicha`, `Evidencia`, `Atencion`, `Bitacora`, `Respaldo`.
- Proteger el rol `Super Admin` / `Super Administrador` en `Gate::before` y en `RoleController@destroy`.

## 3. Vistas y Rutas en Español
- Directorios de vistas en español (`instituciones`, `bitacora`, `respaldos`, etc.).
- Ningún texto de interfaz debe quedar en inglés (`Profile`, `Log Out`, `Update Password`, etc.).
