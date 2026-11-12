@echo off
title SGCJ - Detener Servicios
color 0C

:: ============================================================
::  Detiene unicamente los procesos de SGCJ:
::    artisan serve / reverb:start / queue:work / schedule:work
::  Util cuando el puerto 8000 u 8081 aparece ocupado.
:: ============================================================

echo Deteniendo servicios de SGCJ...
echo.

powershell -NoProfile -Command "Get-CimInstance Win32_Process | Where-Object { $_.CommandLine -match 'artisan (serve|reverb:start|queue:work|schedule:work)' } | ForEach-Object { Write-Host ('  Cerrando PID ' + $_.ProcessId + ' -> ' + $_.CommandLine); Stop-Process -Id $_.ProcessId -Force -ErrorAction SilentlyContinue }"

echo.
echo Verificando puertos 8000 y 8081...
netsh interface ipv4 show tcpconnections | findstr ":8000 :8081"
echo.
echo Listo. Si no aparece nada arriba, los puertos quedaron libres.
pause
