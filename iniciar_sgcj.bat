@echo off
title Iniciando SGCJ - Entorno de Desarrollo
color 0B

:: ============================================================
::  IPs DEL SERVIDOR (se detectan automaticamente al iniciar)
::    LAN / cable ...... IP del adaptador con puerta de enlace
::    Hotspot Wi-Fi .... IP del adaptador Wi-Fi Direct (192.168.137.x)
::
::  Las URLs que se muestran abajo son las que deben usar los demas
::  equipos. No hace falta editar .env ni recompilar: los WebSockets
::  usan el mismo host desde el que se abre la pagina.
:: ============================================================
set "SGCJ_HTTP_PORT=8000"
set "SGCJ_REVERB_PORT=8081"
set "SGCJ_LAN_IP="
set "SGCJ_HOTSPOT_IP="

for /f "tokens=1,2" %%a in ('powershell -NoProfile -Command "$lan=''; try { $r=Get-NetRoute -DestinationPrefix '0.0.0.0/0' -ErrorAction Stop | Sort-Object RouteMetric | Select-Object -First 1; if($r){ $lan=@(Get-NetIPAddress -InterfaceIndex $r.InterfaceIndex -AddressFamily IPv4 -ErrorAction Stop)[0].IPAddress } } catch {}; $hot=(Get-NetIPAddress -AddressFamily IPv4 -ErrorAction SilentlyContinue | Where-Object { $_.IPAddress -like '192.168.137.*' } | Select-Object -First 1).IPAddress; if(-not $lan){$lan='172.16.0.108'}; if(-not $hot){$hot='192.168.137.1'}; Write-Output ($lan + ' ' + $hot)"') do (
    set "SGCJ_LAN_IP=%%a"
    set "SGCJ_HOTSPOT_IP=%%b"
)
if not defined SGCJ_LAN_IP set "SGCJ_LAN_IP=172.16.0.108"
if not defined SGCJ_HOTSPOT_IP set "SGCJ_HOTSPOT_IP=192.168.137.1"

echo ===================================================
echo    SISTEMA DE GESTION DE CONSULTORIA JURIDICA
echo ===================================================
echo   En este equipo   : http://127.0.0.1:%SGCJ_HTTP_PORT%
echo   En la red LAN    : http://%SGCJ_LAN_IP%:%SGCJ_HTTP_PORT%
echo   Por hotspot WiFi : http://%SGCJ_HOTSPOT_IP%:%SGCJ_HTTP_PORT%
echo   WebSockets LAN   : ws://%SGCJ_LAN_IP%:%SGCJ_REVERB_PORT%
echo   WebSockets WiFi  : ws://%SGCJ_HOTSPOT_IP%:%SGCJ_REVERB_PORT%
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

:: 3. Limpieza profunda y Reconstruccion Segura
echo [OK] Limpiando cache del sistema...
call php artisan optimize:clear
echo [OK] Descubriendo paquetes para evitar colisiones...
call php artisan package:discover

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

:: 5.1 Servidor PHP en TODAS las interfaces (0.0.0.0 = accesible por LAN)
echo [Iniciando Servidor PHP...]
start "SGCJ - Servidor PHP" cmd /k "php artisan serve --host=0.0.0.0 --port=%SGCJ_HTTP_PORT%"

:: PAUSA VITAL 1: Espera 3 segundos para que 'serve' asiente la cache sin estorbar
timeout /t 3 /nobreak > NUL

:: 5.2 Frontend: se sirven los assets ya compilados (public\build).
::     NO se usa "npm run dev": en otras PC "localhost:5173" no existe.
::     Se recompila si el manifest no existe o si el codigo fuente es mas nuevo.
set "SGCJ_NECESITA_BUILD=0"
if not exist public\build\manifest.json (
    set "SGCJ_NECESITA_BUILD=1"
) else (
    for /f %%f in ('powershell -NoProfile -Command "if((Get-ChildItem resources\js,resources\css -Recurse -File -ErrorAction SilentlyContinue | Sort-Object LastWriteTime -Descending | Select-Object -First 1).LastWriteTime -gt (Get-Item public\build\manifest.json).LastWriteTime){1}else{0}"') do set "SGCJ_NECESITA_BUILD=%%f"
)
if "%SGCJ_NECESITA_BUILD%"=="1" (
    echo [!] Assets del frontend desactualizados. Ejecutando npm run build...
    call npm run build
) else (
    echo [OK] Assets del frontend ya compilados ^(npm run build^).
)

:: PAUSA VITAL 2: Breve respiro para la CPU
timeout /t 2 /nobreak > NUL

:: 5.3 WebSockets (Notificaciones Reverb) en 8081 (8000 = Laravel, 8080 = ocupado)
echo [Iniciando Reverb WebSockets...]
start "SGCJ - Reverb (WebSockets)" cmd /k "php artisan reverb:start --host=0.0.0.0 --port=%SGCJ_REVERB_PORT% --debug"

:: PAUSA VITAL 3: Evita colision con las colas
timeout /t 2 /nobreak > NUL

:: 5.4 Procesamiento en Segundo Plano (Colas)
echo [Iniciando Procesador de Colas...]
start "SGCJ - Queue Worker" cmd /k "php artisan queue:work"

:: PAUSA VITAL 4: Sincronizacion final para el motor de tareas
timeout /t 2 /nobreak > NUL

:: 5.5 Motor de Tareas Programadas (Cron de Windows)
echo [Iniciando Programador de Tareas (Scheduler)...]
start "SGCJ - Task Scheduler" cmd /k "php artisan schedule:work"

echo.
echo Listo! Todos los microservicios estan en linea sin colisiones.
echo.
echo   Desde este equipo    : http://127.0.0.1:%SGCJ_HTTP_PORT%
echo   Desde la red LAN     : http://%SGCJ_LAN_IP%:%SGCJ_HTTP_PORT%
echo   Desde el hotspot WiFi: http://%SGCJ_HOTSPOT_IP%:%SGCJ_HTTP_PORT%
echo.
echo   Si otra PC no puede entrar, ejecuta como administrador:
echo      abrir_firewall_sgcj.bat
echo.
pause
