@echo off
REM Запуск dev-сервера (обходит ExecutionPolicy)
cd /d "%~dp0"
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0start-dev.ps1"
pause
