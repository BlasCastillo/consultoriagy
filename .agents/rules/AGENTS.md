# Reglas Maestras del Ecosistema SGCJ (Consultoría Jurídica)

Estas reglas aplican de forma obligatoria a todos los agentes y subagentes que operan en el workspace `consultoriagy` (Laravel 12 + PostgreSQL 17 + TailwindCSS + Blade + Spatie Permission):

1. **Idioma y Nomenclatura Obligatoria en Español:**
   - Todas las respuestas, planes, comentarios de código, mensajes de validación, vistas Blade, rutas y nombres de permisos deben estar exclusivamente en **español**.
   - Se mantiene el estándar de nombres en español para vistas (`instituciones`, `bitacora`, `respaldos`, `gacetas`, `solicitudes`, `atenciones`, `archivo`, `poa`, etc.) y para el catálogo centralizado de permisos y roles.
2. **Arquitectura Limpia Laravel 12 (Controladores, Modelos y Servicios):**
   - Prohibido duplicar lógica de negocio entre `Observers` y `Services` (ej. el conteo de metas del POA debe tener una única fuente de ejecución).
   - Todo modelo Eloquent debe definir explícitamente su `$table` y `$fillable` alineados con el esquema real de PostgreSQL (`sgc_db`), sin referenciar columnas obsoletas o inexistentes.
3. **Validación Dual Obligatoria (Frontend + Backend):**
   - **Frontend (`resources/views/**/*.blade.php`):** Todo formulario `<form>` debe incluir atributos HTML5 (`required`, `maxlength`, `min`, `max`, `accept`), clases del motor `public/js/global-validation.js` (`.val-numerico`, `.val-texto-limpio`, `.val-solo-letras`, `.val-fecha-futura`, `.val-fecha-rango`), preservación de datos con `old()` y visualización clara de errores (`@error` y/o bloque `$errors->any()`).
   - **Backend (`app/Http/Controllers/` y `app/Http/Requests/`):** Todo endpoint `POST`, `PUT`, `PATCH` debe validar rigurosamente tipos, longitudes máximas, formatos (`regex`), llaves foráneas (`exists`) y reglas de negocio antes de tocar la base de datos.
4. **Seguridad RBAC Unificada (Spatie Permission):**
   - El catálogo de módulos y permisos en `App\Services\ModelPermissionAuditor` y `Database\Seeders\RolesAndPermissionsSeeder` es la **única fuente de verdad** para `routes/web.php`, `resources/views/layouts/navigation.blade.php`, controladores y vistas.
   - El rol maestro del sistema se denomina de forma unificada (`Super Administrador` / `Super Admin` en `Gate::before`) y está protegido contra eliminación o desactivación.
5. **Seguridad de Datos en el Chatbot (Gemini RAG + PDF):**
   - El Chatbot (`ChatbotController`) tiene prohibido exponer credenciales, correos o datos de usuarios (`users`).
   - El acceso a solicitudes en trámite debe filtrarse estrictamente según la institución del usuario autenticado (`institucion_id`), mientras que las gacetas publicadas (`Publicada`), sus actos administrativos (`acto_administrativos`), gobernadores, instituciones y PDFs públicos están disponibles para consulta jurídica e institucional.
6. **Ejecución Paso a Paso con Verificación QA:**
   - Todo archivo PHP modificado debe verificarse con `php -l`, comprobando rutas (`php artisan route:list`), migraciones (`php artisan migrate:status`) y consistencia con PostgreSQL (`sgc_db`).
