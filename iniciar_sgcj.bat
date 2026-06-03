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

:: 5. Levantando los servidores en paralelo
echo.
echo ===================================================
echo   Levantando motores... Abriendo terminales
echo ===================================================
echo.

:: Abre una terminal nueva para el Servidor PHP
start "SGCJ - Servidor PHP" cmd /k "php artisan serve"

:: Abre una terminal nueva para compilar el Frontend (Vite)
start "SGCJ - Vite (Frontend)" cmd /k "npm run dev"

:: Abre una terminal nueva para WebSockets (Notificaciones Reverb)
start "SGCJ - Reverb (WebSockets)" cmd /k "php artisan reverb:start --debug"

:: Abre una terminal nueva para Procesamiento en Segundo Plano (Colas)
start "SGCJ - Queue Worker" cmd /k "php artisan queue:work"

echo Listo! Puedes minimizar esta ventana.
pause