---
name: qa-verificacion
description: Protocolo de pruebas de calidad (QA) automatizadas sobre PHP, Laravel y PostgreSQL después de cada bloque de cambios.
---

# Skill: Protocolo de Verificación y QA (SGCJ)

Después de aplicar cambios en cualquier módulo:
1. **Verificación de Sintaxis PHP:** Ejecutar `php -l` sobre todos los archivos `.php` modificados.
2. **Verificación de Rutas y Contenedor Laravel:** Ejecutar `php artisan route:list` y limpiar cachés (`php artisan optimize:clear`).
3. **Verificación de Migraciones y Permisos en PostgreSQL (`sgc_db`):** Ejecutar `php artisan migrate --force` y `php artisan db:seed --class=RolesAndPermissionsSeeder --force`, comprobando que no existan permisos huérfanos ni errores SQL.
