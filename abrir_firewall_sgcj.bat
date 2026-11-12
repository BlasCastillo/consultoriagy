@echo off
title SGCJ - Abrir Firewall para la Red Local
color 0E

:: ============================================================
::  Abre los puertos que usa SGCJ para las demas maquinas de la LAN.
::    8000 -> Laravel (pagina web)
::    8081 -> Reverb  (notificaciones en tiempo real)
::
::  IMPORTANTE:
::   - NO cambia el perfil de red (Publica/Privada). Las carpetas
::     compartidas siguen funcionando exactamente igual.
::   - Las reglas se aplican en CUALQUIER perfil, pero solo aceptan
::     clientes de las subredes indicadas en SGCJ_REDES.
::   - Ejecutar con clic derecho > "Ejecutar como administrador"
::
::  Si cambia la IP del servidor, ajusta SGCJ_REDES aqui abajo.
:: ============================================================
set "SGCJ_REDES=172.16.0.0/16,192.168.137.0/24"

net session >nul 2>&1
if errorlevel 1 (
    echo.
    echo [!] Este script necesita permisos de Administrador.
    echo.
    echo     Cierra esta ventana, haz clic derecho sobre el archivo
    echo     "abrir_firewall_sgcj.bat" y elige:
    echo         "Ejecutar como administrador"
    echo.
    pause
    exit /b 1
)

echo [OK] Permisos de administrador detectados.
echo.
echo Redes autorizadas: %SGCJ_REDES%
echo.

echo [1/3] Eliminando reglas anteriores (si existen)...
netsh advfirewall firewall delete rule name="SGCJ Laravel 8000" >nul 2>&1
netsh advfirewall firewall delete rule name="SGCJ Reverb 8081" >nul 2>&1

echo [2/3] Creando reglas de entrada restringidas por subred...
netsh advfirewall firewall add rule name="SGCJ Laravel 8000" dir=in action=allow protocol=TCP localport=8000 profile=any remoteip=%SGCJ_REDES% >nul
if errorlevel 1 (echo   [X] Error creando la regla del puerto 8000) else (echo   [OK] Puerto 8000 permitido a: %SGCJ_REDES%)
netsh advfirewall firewall add rule name="SGCJ Reverb 8081" dir=in action=allow protocol=TCP localport=8081 profile=any remoteip=%SGCJ_REDES% >nul
if errorlevel 1 (echo   [X] Error creando la regla del puerto 8081) else (echo   [OK] Puerto 8081 permitido a: %SGCJ_REDES%)

echo [3/3] Estado de los perfiles de red (solo informativo, NO se modifican)...
echo.
powershell -NoProfile -Command "Get-NetConnectionProfile | Select-Object InterfaceAlias, NetworkCategory | Format-Table -AutoSize"
echo.
echo [i] Las reglas de arriba funcionan tanto en red Publica como Privada,
echo     por lo que NO hace falta cambiar el tipo de perfil del equipo.
echo     El uso de carpetas compartidas queda igual que antes.
echo.
echo ===================================================
echo   Reglas creadas:
echo ===================================================
netsh advfirewall firewall show rule name="SGCJ Laravel 8000" | findstr /i "Rule Regla Enabled Habilitada Direction Direct Port Puerto Profile Perfil RemoteIP"
netsh advfirewall firewall show rule name="SGCJ Reverb 8081" | findstr /i "Rule Regla Enabled Habilitada Direction Direct Port Puerto Profile Perfil RemoteIP"
echo.
echo Prueba desde otra maquina de la misma red:
echo     http://IP_DEL_SERVIDOR:8000
echo.
echo Para revertir todo:
echo     netsh advfirewall firewall delete rule name="SGCJ Laravel 8000"
echo     netsh advfirewall firewall delete rule name="SGCJ Reverb 8081"
echo.
pause
