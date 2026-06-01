# Запуск локального dev-сервера (только PowerShell, без cmd/bash)
# Запуск: из корня репозитория (shop):  .\start-dev.ps1

$ErrorActionPreference = 'Stop'
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8

$projectRoot = $PSScriptRoot
if (-not (Test-Path (Join-Path $projectRoot 'router.php'))) {
    Write-Host 'ОШИБКА: запусти скрипт из корня проекта (где лежит router.php).' -ForegroundColor Red
    exit 1
}

# Локальные настройки БД (не в git): скопируй db-local.ps1.example → db-local.ps1
$dbLocalPath = Join-Path $projectRoot 'db-local.ps1'
$dbLocalLoaded = $false
if (Test-Path $dbLocalPath) {
    Write-Host 'Загружаю db-local.ps1' -ForegroundColor Cyan
    . $dbLocalPath
    $dbLocalLoaded = $true
}

function Find-Executable {
    param(
        [string]$Name,
        [string[]]$ExtraPaths
    )
    $cmd = Get-Command $Name -ErrorAction SilentlyContinue
    if ($cmd) { return $cmd.Source }

    foreach ($p in $ExtraPaths) {
        if ($p -and (Test-Path $p)) { return $p }
    }
    return $null
}

function Find-MysqlClient {
    $fromPath = Find-Executable 'mysql.exe' @()
    if ($fromPath) { return $fromPath }

    $base = 'C:\Program Files\MySQL'
    if (Test-Path $base) {
        $dirs = Get-ChildItem -Path $base -Directory -ErrorAction SilentlyContinue |
            Where-Object { $_.Name -like 'MySQL Server *' } |
            Sort-Object Name -Descending
        foreach ($d in $dirs) {
            $exe = Join-Path $d.FullName 'bin\mysql.exe'
            if (Test-Path $exe) { return $exe }
        }
    }

    return Find-Executable 'mysql.exe' @(
        'C:\xampp\mysql\bin\mysql.exe'
    )
}

function Find-PhpExecutable {
    $candidates = @()

    $fromPath = Get-Command php.exe -All -ErrorAction SilentlyContinue
    if ($fromPath) {
        $candidates += $fromPath | ForEach-Object { $_.Source }
    }

    $candidates += @(
        'C:\xampp\php\php.exe',
        'C:\php\php.exe',
        'C:\Program Files\PHP\php.exe'
    )

    $wingetPhp = Join-Path $env:LOCALAPPDATA 'Microsoft\WinGet\Packages'
    if (Test-Path $wingetPhp) {
        $candidates += Get-ChildItem -Path $wingetPhp -Recurse -Filter 'php.exe' -ErrorAction SilentlyContinue |
            Select-Object -ExpandProperty FullName
    }

    $best = $null
    $bestVer = [version]'0.0'

    foreach ($exe in ($candidates | Select-Object -Unique)) {
        if (-not (Test-Path $exe)) { continue }
        try {
            $verLine = & $exe -r 2>$null
            if (-not $verLine) { continue }
            $m = [regex]::Match($verLine, '(\d+\.\d+\.\d+)')
            if (-not $m.Success) { continue }
            $ver = [version]$m.Groups[1].Value
            if ($ver -ge [version]'8.2.0' -and $ver -gt $bestVer) {
                $best = $exe
                $bestVer = $ver
            }
        } catch { }
    }

    if ($best) { return $best }

    # Любой php из PATH, даже если версию не распарсили
    return Find-Executable 'php.exe' @('C:\xampp\php\php.exe')
}

function Test-PhpPdoMysql {
    param([string]$PhpExe)
    $mods = & $PhpExe -m 2>$null
    return ($mods -match 'pdo_mysql')
}

# mysql: те же аргументы, что при ручном запуске; таймаут чтобы не зависать
$script:LastMySqlOutput = ''

function Get-MySqlClientArgs {
    $clientArgs = @(
        '-h', $env:DB_HOST,
        '-P', $env:DB_PORT,
        '-u', $env:DB_USER,
        '--default-character-set=utf8mb4',
        '--connect-timeout=3',
        '--batch'
    )
    if ($env:DB_PASSWORD) {
        $clientArgs += "-p$($env:DB_PASSWORD)"
    }
    return $clientArgs
}

function Get-MySqlServiceStatus {
    foreach ($name in @('MySQL80', 'MySQL84', 'MySQL')) {
        $svc = Get-Service -Name $name -ErrorAction SilentlyContinue
        if ($svc) { return $svc }
    }
    return $null
}

function Invoke-MySql {
    param(
        [Parameter(Mandatory)][string]$MySqlExe,
        [Parameter(Mandatory)][string[]]$Arguments,
        [string]$InputText,
        [int]$TimeoutSec = 8
    )
    $job = Start-Job -ArgumentList @($MySqlExe, $Arguments, $InputText) -ScriptBlock {
        param($exe, $mysqlCliArgs, $inputText)
        $ErrorActionPreference = 'Continue'
        try {
            if ($null -ne $inputText -and $inputText -ne '') {
                $output = @($inputText | & $exe @mysqlCliArgs 2>&1)
            } else {
                $output = @(& $exe @mysqlCliArgs 2>&1)
            }
            return @{
                Code = [int]$LASTEXITCODE
                Out  = (($output | ForEach-Object { "$_" }) -join [Environment]::NewLine)
            }
        } catch {
            return @{ Code = 1; Out = $_.Exception.Message }
        }
    }

    $done = Wait-Job -Job $job -Timeout $TimeoutSec
    if (-not $done) {
        Stop-Job -Job $job -Force
        Remove-Job -Job $job -Force
        $script:LastMySqlOutput = "Таймаут ${TimeoutSec}s - проверь DB_PORT и DB_PASSWORD в db-local.ps1"
        return 124
    }

    $result = Receive-Job -Job $job
    Remove-Job -Job $job -Force
    if ([int]$result.Code -ne 0) {
        $script:LastMySqlOutput = [string]$result.Out
    }
    return [int]$result.Code
}

function Test-MySqlReady {
    param([string]$MySqlExe)
    Write-Host "  mysql $($env:DB_USER)@$($env:DB_HOST):$($env:DB_PORT) ..." -ForegroundColor DarkGray
    $mysqlCliArgs = (Get-MySqlClientArgs) + @('-e', 'SELECT 1')
    return (Invoke-MySql -MySqlExe $MySqlExe -Arguments $mysqlCliArgs) -eq 0
}

function Start-MysqlServiceIfNeeded {
    $services = Get-Service -ErrorAction SilentlyContinue |
        Where-Object { $_.Name -match 'mysql' -and $_.Status -ne 'Running' }
    foreach ($s in $services) {
        try {
            Write-Host "Запуск службы $($s.Name)..."
            Start-Service $s.Name -ErrorAction Stop
            Start-Sleep -Seconds 2
            return
        } catch { }
    }
    # Типичные имена
    foreach ($name in @('MySQL84', 'MySQL80', 'MySQL')) {
        $svc = Get-Service -Name $name -ErrorAction SilentlyContinue
        if ($svc -and $svc.Status -ne 'Running') {
            try {
                Write-Host "Запуск службы $name..."
                Start-Service $name -ErrorAction Stop
                Start-Sleep -Seconds 2
                return
            } catch { }
        }
    }
}

Write-Host "Корень проекта: $projectRoot" -ForegroundColor Cyan
Write-Host "Ищу PHP..."
$php = Find-PhpExecutable
if (-not $php) {
    Write-Host 'ОШИБКА: PHP 8.2+ не найден. Установи: winget install PHP.PHP' -ForegroundColor Red
    exit 1
}

$phpVer = & $php -v 2>$null | Select-Object -First 1
Write-Host "PHP: $php" -ForegroundColor Green
Write-Host "     $phpVer"

if (-not (Test-PhpPdoMysql -PhpExe $php)) {
    Write-Host 'ОШИБКА: расширение pdo_mysql не загружено.' -ForegroundColor Red
    Write-Host 'Открой php.ini (команда: php --ini) и раскомментируй:' -ForegroundColor Yellow
    Write-Host '  extension=pdo_mysql' -ForegroundColor Yellow
    Write-Host '  extension=mysqli' -ForegroundColor Yellow
    exit 1
}
Write-Host 'pdo_mysql: OK' -ForegroundColor Green

Write-Host 'Ищу MySQL...'
$mysql = Find-MysqlClient
if (-not $mysql) {
    Write-Host 'ОШИБКА: mysql.exe не найден (проверь MySQL 8.x в Program Files).' -ForegroundColor Red
    exit 1
}
Write-Host "MySQL: $mysql" -ForegroundColor Green

$env:APP_DEBUG = 'true'
$env:APP_URL = 'http://localhost:8080'
$env:SESSION_SECURE = 'false'
$env:DB_HOST = if ($env:DB_HOST) { $env:DB_HOST } else { '127.0.0.1' }
$env:DB_PORT = if ($env:DB_PORT) { $env:DB_PORT } else { '3306' }
$env:DB_NAME = if ($env:DB_NAME) { $env:DB_NAME } else { 'furniture_platform' }
$env:DB_USER = if ($env:DB_USER) { $env:DB_USER } else { 'root' }
if (-not $env:DB_PASSWORD) { $env:DB_PASSWORD = '' }

Write-Host "MySQL: $env:DB_USER@$env:DB_HOST`:$env:DB_PORT, база $env:DB_NAME$(if ($env:DB_PASSWORD) { ', пароль задан' } else { ', пароль пустой' })" -ForegroundColor Cyan

Write-Host 'Проверка MySQL...'
$mysqlSvc = Get-MySqlServiceStatus
if ($mysqlSvc -and $mysqlSvc.Status -ne 'Running') {
    Write-Host "Служба $($mysqlSvc.Name) остановлена, запускаю..." -ForegroundColor Yellow
    Start-MysqlServiceIfNeeded
    Start-Sleep -Seconds 2
}

$mysqlOk = Test-MySqlReady -MySqlExe $mysql
if (-not $mysqlOk -and -not $dbLocalLoaded -and $env:DB_PORT -eq '3306') {
    Write-Host 'Пробую порт 3307...' -ForegroundColor Yellow
    $env:DB_PORT = '3307'
    $mysqlOk = Test-MySqlReady -MySqlExe $mysql
}

if (-not $mysqlOk) {
    Write-Host ''
    Write-Host 'ОШИБКА: не удалось подключиться к MySQL.' -ForegroundColor Red
    Write-Host "  Хост:   $env:DB_HOST`:$env:DB_PORT" -ForegroundColor Yellow
    Write-Host "  Пользователь: $env:DB_USER" -ForegroundColor Yellow
    if ($script:LastMySqlOutput) {
        Write-Host ''
        Write-Host '  Ответ mysql:' -ForegroundColor Yellow
        Write-Host $script:LastMySqlOutput -ForegroundColor DarkYellow
    }
    if (-not $dbLocalLoaded) {
        Write-Host ''
        Write-Host '  Создай db-local.ps1 (см. db-local.ps1.example):' -ForegroundColor Yellow
        Write-Host '    copy db-local.ps1.example db-local.ps1' -ForegroundColor Yellow
        Write-Host '    notepad db-local.ps1   # пароль и порт 3307' -ForegroundColor Yellow
    } else {
        Write-Host '  db-local.ps1 загружен - проверь DB_PORT и DB_PASSWORD в файле.' -ForegroundColor Yellow
    }
    exit 1
}

$mysqlArgs = Get-MySqlClientArgs

Write-Host "Проверка базы $env:DB_NAME..."
if ((Invoke-MySql -MySqlExe $mysql -Arguments ($mysqlArgs + @('-e', "USE $env:DB_NAME"))) -ne 0) {
    Write-Host 'Создание базы из sql\schema.sql...' -ForegroundColor Yellow
    $schemaPath = Join-Path $projectRoot 'sql\schema.sql'
    if (-not (Test-Path $schemaPath)) {
        Write-Host 'ОШИБКА: не найден sql\schema.sql' -ForegroundColor Red
        exit 1
    }
    $schemaSql = Get-Content -Path $schemaPath -Raw -Encoding UTF8
    if ((Invoke-MySql -MySqlExe $mysql -Arguments $mysqlArgs -InputText $schemaSql) -ne 0) {
        Write-Host 'ОШИБКА: не удалось применить schema.sql' -ForegroundColor Red
        exit 1
    }
    Write-Host 'База создана.' -ForegroundColor Green
}

$migrationsDir = Join-Path $projectRoot 'sql\migrations'
if (Test-Path $migrationsDir) {
    Write-Host 'Применение миграций...'
    Get-ChildItem -Path $migrationsDir -Filter '*.sql' | Sort-Object Name | ForEach-Object {
        Write-Host "  $($_.Name)"
        $migrationSql = Get-Content -Path $_.FullName -Raw -Encoding UTF8
        if ((Invoke-MySql -MySqlExe $mysql -Arguments $mysqlArgs -InputText $migrationSql) -ne 0) {
            Write-Host "ОШИБКА: миграция $($_.Name) не применилась." -ForegroundColor Red
            exit 1
        }
    }
    Write-Host 'Миграции применены.' -ForegroundColor Green
}

Write-Host ''
Write-Host '========================================' -ForegroundColor Cyan
Write-Host 'Сайт: http://localhost:8080' -ForegroundColor Yellow
Write-Host 'Конфигуратор: http://localhost:8080/product/1' -ForegroundColor Yellow
Write-Host 'Остановка: Ctrl+C' -ForegroundColor Cyan
Write-Host '========================================' -ForegroundColor Cyan
Write-Host ''

Set-Location $projectRoot
& $php -S localhost:8080 router.php
