# Запуск локального сервера PHP
# Очень простая версия без проблем с кодировкой

[Console]::OutputEncoding = [System.Text.Encoding]::UTF8

# Ищем PHP
Write-Host "Ищу PHP..."
$php = $null

if (Test-Path "C:\xampp\php\php.exe") { $php = "C:\xampp\php\php.exe" }
elseif (Test-Path "C:\Program Files\PHP\php.exe") { $php = "C:\Program Files\PHP\php.exe" }
elseif (Test-Path "C:\php\php.exe") { $php = "C:\php\php.exe" }
else {
    $cmd = Get-Command php.exe -ErrorAction SilentlyContinue
    if ($cmd) { $php = $cmd.Source }
}

if (-not $php) {
    Write-Host "ОШИБКА: PHP не найден!" -ForegroundColor Red
    Read-Host "Нажмите Enter"
    exit
}

Write-Host "PHP: $php" -ForegroundColor Green

# Ищем MySQL
Write-Host "Ищу MySQL..."
$mysql = $null

if (Test-Path "C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe") { 
    $mysql = "C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe" 
}
elseif (Test-Path "C:\xampp\mysql\bin\mysql.exe") { 
    $mysql = "C:\xampp\mysql\bin\mysql.exe" 
}
else {
    $cmd = Get-Command mysql.exe -ErrorAction SilentlyContinue
    if ($cmd) { $mysql = $cmd.Source }
}

if (-not $mysql) {
    Write-Host "ОШИБКА: MySQL не найден!" -ForegroundColor Red
    Read-Host "Нажмите Enter"
    exit
}

Write-Host "MySQL: $mysql" -ForegroundColor Green

# Переменные окружения
$env:APP_DEBUG = "true"
$env:APP_URL = "http://localhost:8080"
$env:SESSION_SECURE = "false"
$env:DB_HOST = "127.0.0.1"
$env:DB_PORT = "3306"
$env:DB_NAME = "furniture_platform"
$env:DB_USER = "root"
$env:DB_PASSWORD = ""

# Путь к проекту
$projectRoot = Split-Path -Parent $PSScriptRoot

# Проверяем MySQL
Write-Host "Проверка MySQL..."
& $mysql -u root -e "SELECT 1" 2>$null
if ($LASTEXITCODE -ne 0) {
    Write-Host "Запуск MySQL сервиса..."
    net start MySQL80 2>$null
    Start-Sleep -Seconds 3
}

# Проверяем БД
Write-Host "Проверка базы данных..."
& $mysql -u root -e "USE furniture_platform" 2>$null
if ($LASTEXITCODE -ne 0) {
    Write-Host "Создание базы..."
    $schemaPath = Join-Path $projectRoot "sql\schema.sql"
    $sql = Get-Content $schemaPath -Raw -Encoding UTF8
    $sql | & $mysql -u root 2>$null
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Готово! Сайт запущен:" -ForegroundColor Green
Write-Host "http://localhost:8080" -ForegroundColor Yellow
Write-Host "Нажмите Ctrl+C для остановки" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Запуск сервера
Push-Location $projectRoot
& $php -S localhost:8080 router.php
Pop-Location
