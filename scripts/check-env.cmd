@echo off
REM Диагностика окружения (обходит ExecutionPolicy)
cd /d "%~dp0.."
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0check-env.ps1"
pause
