# Диагностика окружения Windows (только PowerShell)
# Запуск из корня репозитория (shop):  .\scripts\check-env.ps1

$ErrorActionPreference = 'Continue'
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8

Write-Host '=== Проверка окружения (Windows) ===' -ForegroundColor Cyan
Write-Host "Shell: $($PSVersionTable.PSEdition) $($PSVersionTable.PSVersion)"
Write-Host "PWD:   $(Get-Location)"
Write-Host ''

# PHP
Write-Host '--- PHP ---' -ForegroundColor Yellow
$phpList = Get-Command php.exe -All -ErrorAction SilentlyContinue
if ($phpList) {
    foreach ($p in $phpList) {
        $v = & $p.Source -v 2>$null | Select-Object -First 1
        Write-Host "  $($p.Source)"
        Write-Host "    $v"
    }
} else {
    Write-Host '  php.exe не в PATH' -ForegroundColor Red
}

$wingetPhp = Join-Path $env:LOCALAPPDATA 'Microsoft\WinGet\Packages'
if (Test-Path $wingetPhp) {
    $found = Get-ChildItem -Path $wingetPhp -Recurse -Filter 'php.exe' -ErrorAction SilentlyContinue
    foreach ($f in $found) {
        Write-Host "  WinGet: $($f.FullName)"
        $v = & $f.FullName -v 2>$null | Select-Object -First 1
        Write-Host "    $v"
    }
}

$php = Get-Command php.exe -ErrorAction SilentlyContinue | Select-Object -First 1
if ($php) {
    Write-Host ''
    Write-Host 'php --ini:'
    & $php.Source --ini
    Write-Host ''
    Write-Host 'Модули (pdo / mysql):'
    & $php.Source -m | Select-String -Pattern 'pdo|mysql'
    $hasPdo = (& $php.Source -m) -match 'pdo_mysql'
    if ($hasPdo) {
        Write-Host 'pdo_mysql: OK' -ForegroundColor Green
    } else {
        Write-Host 'pdo_mysql: НЕТ — включи в php.ini' -ForegroundColor Red
    }
}

Write-Host ''
Write-Host '--- MySQL ---' -ForegroundColor Yellow
$mysql = Get-Command mysql.exe -ErrorAction SilentlyContinue
if ($mysql) {
    Write-Host "  PATH: $($mysql.Source)"
    & $mysql.Source --version
} else {
    Write-Host '  mysql.exe не в PATH' -ForegroundColor Red
}

$mysqlRoot = 'C:\Program Files\MySQL'
if (Test-Path $mysqlRoot) {
    Get-ChildItem $mysqlRoot -Directory | ForEach-Object {
        $exe = Join-Path $_.FullName 'bin\mysql.exe'
        if (Test-Path $exe) { Write-Host "  Найден: $exe" }
    }
}

Write-Host ''
Write-Host '--- Службы MySQL ---' -ForegroundColor Yellow
Get-Service -ErrorAction SilentlyContinue | Where-Object { $_.Name -match 'mysql' } |
    Format-Table Name, Status, DisplayName -AutoSize

Write-Host ''
Write-Host '--- Проект ---' -ForegroundColor Yellow
$root = Split-Path -Parent $PSScriptRoot
$checks = @('router.php', 'index.php', 'sql\schema.sql')
foreach ($c in $checks) {
    $p = Join-Path $root $c
    if (Test-Path $p) { Write-Host "  OK  $c" -ForegroundColor Green }
    else { Write-Host "  НЕТ $c" -ForegroundColor Red }
}

Write-Host ''
Write-Host 'Дальше: из корня проекта выполни  .\start-dev.ps1' -ForegroundColor Cyan
