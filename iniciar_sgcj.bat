@echo off
title Iniciando SGCJ - Entorno de Desarrollo
color 0B

echo ===================================================
echo    SISTEMA DE GESTION DE CONSULTORIA JURIDICA
echo ===================================================
echo.

:: 1. Verificacion de Variables de Entorno (.env)
if not exist .env (
    echo [!] No se encontro el archivo .env. Clonando .env.example...
    copy .env.example .env
    echo [!] Generando clave de aplicacion...
    php artisan key:generate
) else (
    echo [OK] Archivo .env detectado.
)

:: 2. Estructura de Carpetas (Previene errores de permisos/escritura)
echo [OK] Verificando estructura de almacenamiento...
if not exist storage\app\public mkdir storage\app\public
if not exist storage\framework\cache mkdir storage\framework\cache
if not exist storage\framework\sessions mkdir storage\framework\sessions
if not exist storage\framework\views mkdir storage\framework\views

:: 3. Limpieza profunda
echo [OK] Limpiando cache del sistema...
call php artisan optimize:clear

:: 4. Enlace simbolico de imagenes (Storage Link)
if not exist public\storage (
    echo [!] Creando acceso directo publico para los archivos...
    call php artisan storage:link
)

:: 5. Levantando los servidores ESCALONADAMENTE
echo.
echo ===================================================
echo   Levantando motores... Abriendo terminales
echo ===================================================
echo.

:: 5.1 Abre una terminal nueva para el Servidor PHP
echo [Iniciando Servidor PHP...]
start "SGCJ - Servidor PHP" cmd /k "php artisan serve --host=127.0.0.1 --port=8000"

:: PAUSA VITAL 1: Espera 3 segundos para que 'serve' asiente la cache sin estorbar
timeout /t 3 /nobreak > NUL

:: 5.2 Abre una terminal nueva para compilar el Frontend (Vite)
echo [Iniciando Vite Frontend...]
start "SGCJ - Vite (Frontend)" cmd /k "npm run dev"

:: PAUSA VITAL 2: Breve respiro para la CPU
timeout /t 2 /nobreak > NUL

:: 5.3 Abre una terminal nueva para WebSockets (Notificaciones Reverb)
echo [Iniciando Reverb WebSockets...]
start "SGCJ - Reverb (WebSockets)" cmd /k "php artisan reverb:start --debug"

:: PAUSA VITAL 3: Evita colision con las colas
timeout /t 2 /nobreak > NUL

:: 5.4 Abre una terminal nueva para Procesamiento en Segundo Plano (Colas)
echo [Iniciando Procesador de Colas...]
start "SGCJ - Queue Worker" cmd /k "php artisan queue:work"

:: PAUSA VITAL 4: Sincronizacion final para el motor de tareas
timeout /t 2 /nobreak > NUL

:: 5.5 Abre una terminal nueva para el Motor de Tareas Programadas (Cron de Windows)
echo [Iniciando Programador de Tareas (Scheduler)...]
start "SGCJ - Task Scheduler" cmd /k "php artisan schedule:work"

echo.
echo Listo! Todos los microservicios estan en linea sin colisiones.
pause