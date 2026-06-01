# Local development startup script for the shop project
# Run with PowerShell: .\start-dev.ps1

$ErrorActionPreference = 'Stop'
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8

$projectRoot = $PSScriptRoot
if (-not (Test-Path (Join-Path $projectRoot 'router.php'))) {
    Write-Host 'ERROR: router.php not found in project root.' -ForegroundColor Red
    exit 1
}

function Find-Executable {
    param(
        [string]$Name,
        [string[]]$ExtraPaths
    )
    $cmd = Get-Command $Name -ErrorAction SilentlyContinue
    if ($cmd) { return $cmd.Source }
    foreach ($path in $ExtraPaths) {
        if ($path -and (Test-Path $path)) { return $path }
    }
    return $null
}

function Find-PhpExecutable {
    $candidates = @(
        'php.exe',
        'C:\php\php.exe',
        'C:\xampp\php\php.exe',
        'C:\Program Files\PHP\php.exe'
    )
    foreach ($candidate in $candidates) {
        $found = Find-Executable $candidate @()
        if ($found) { return $found }
    }
    return $null
}

function Find-MysqlExecutable {
    $candidates = @(
        'mysql.exe',
        'C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe',
        'C:\xampp\mysql\bin\mysql.exe'
    )
    foreach ($candidate in $candidates) {
        $found = Find-Executable $candidate @()
        if ($found) { return $found }
    }
    return $null
}

function Get-MySqlConfigPath {
    $paths = @(
        'C:\ProgramData\MySQL\MySQL Server 8.0\my.ini',
        'C:\Program Files\MySQL\MySQL Server 8.0\my.ini',
        'C:\xampp\mysql\bin\my.ini'
    )
    foreach ($path in $paths) {
        if (Test-Path $path) { return $path }
    }
    return $null
}

function Get-MySqlPortFromConfig {
    $configPath = Get-MySqlConfigPath
    if (-not $configPath) { return $null }
    $lines = Get-Content -Path $configPath -ErrorAction SilentlyContinue
    foreach ($line in $lines) {
        $trim = $line.Trim()
        if ($trim -match '^port\s*=\s*(\d+)') {
            return $matches[1]
        }
    }
    return $null
}

function Start-MySqlServiceIfNeeded {
    $serviceNames = @('MySQL80','MySQL','MySQL57','MySQL56')
    foreach ($name in $serviceNames) {
        $svc = Get-Service -Name $name -ErrorAction SilentlyContinue
        if ($svc -and $svc.Status -ne 'Running') {
            try {
                Write-Host "Starting service $name..." -ForegroundColor Yellow
                Start-Service $svc.Name -ErrorAction Stop
                Start-Sleep -Seconds 3
                return
            } catch { }
        }
    }
}

function Build-MySqlArgs {
    param(
        [string]$User,
        [string]$Password
    )
    $args = @('--host', $env:DB_HOST, '--port', $env:DB_PORT, '--user', $User)
    if ($Password -ne '') {
        $args += "--password=$Password"
    }
    return $args
}

function Test-MySqlConnection {
    param(
        [string]$MySqlExe,
        [string[]]$Args
    )
    try {
        & $MySqlExe @Args -e 'SELECT 1' > $null 2>&1
        return $LASTEXITCODE -eq 0
    } catch {
        return $false
    }
}

function Prompt-ForMySqlPassword {
    param([string]$User)
    for ($attempt = 1; $attempt -le 2; $attempt++) {
        $secure = Read-Host "Enter MySQL password for user '$User' (press Enter for no password)" -AsSecureString
        $password = [Runtime.InteropServices.Marshal]::PtrToStringAuto([Runtime.InteropServices.Marshal]::SecureStringToBSTR($secure))
        $args = Build-MySqlArgs -User $User -Password $password
        if (Test-MySqlConnection -MySqlExe $mysql -Args $args) {
            $env:DB_PASSWORD = $password
            return $args
        }
        Write-Host 'MySQL connection failed. Try again.' -ForegroundColor Yellow
    }
    return Build-MySqlArgs -User $User -Password $password
}

Write-Host "Project root: $projectRoot" -ForegroundColor Cyan

Write-Host 'Looking for PHP...'
$php = Find-PhpExecutable
if (-not $php) {
    Write-Host 'ERROR: PHP not found. Install PHP 8.2+ or add it to PATH.' -ForegroundColor Red
    exit 1
}
Write-Host "PHP: $php" -ForegroundColor Green

$phpVersion = & $php -v 2>$null | Select-Object -First 1
Write-Host "    $phpVersion"

$phpModules = & $php -m 2>$null
if (-not ($phpModules -match 'pdo_mysql')) {
    Write-Host 'ERROR: PHP extension pdo_mysql is missing.' -ForegroundColor Red
    exit 1
}
Write-Host 'pdo_mysql: OK' -ForegroundColor Green

Write-Host 'Looking for MySQL client...'
$mysql = Find-MysqlExecutable
if (-not $mysql) {
    Write-Host 'ERROR: mysql.exe not found. Install MySQL client or XAMPP.' -ForegroundColor Red
    exit 1
}
Write-Host "MySQL client: $mysql" -ForegroundColor Green

$env:APP_DEBUG = 'true'
$env:APP_URL = 'http://localhost:8080'
$env:SESSION_SECURE = 'false'
$env:DB_HOST = '127.0.0.1'
$env:DB_PORT = '3306'
$env:DB_NAME = 'furniture_platform'
$env:DB_USER = 'root'
if (-not $env:DB_PASSWORD) { $env:DB_PASSWORD = '' }

$portFromConfig = Get-MySqlPortFromConfig
if ($portFromConfig) {
    Write-Host "Detected MySQL port from config: $portFromConfig" -ForegroundColor Yellow
    $env:DB_PORT = $portFromConfig
}

$mysqlArgs = Build-MySqlArgs -User $env:DB_USER -Password $env:DB_PASSWORD
if (-not (Test-MySqlConnection -MySqlExe $mysql -Args $mysqlArgs)) {
    Write-Host 'MySQL connection failed with current credentials. Trying to start service...' -ForegroundColor Yellow
    Start-MySqlServiceIfNeeded
    if (-not (Test-MySqlConnection -MySqlExe $mysql -Args $mysqlArgs)) {
        $mysqlArgs = Prompt-ForMySqlPassword -User $env:DB_USER
        if (-not (Test-MySqlConnection -MySqlExe $mysql -Args $mysqlArgs)) {
            Write-Host 'ERROR: Cannot connect to MySQL. Check DB_USER and DB_PASSWORD.' -ForegroundColor Red
            exit 1
        }
    }
}

Write-Host 'Checking database furniture_platform...'
& $mysql @mysqlArgs -e 'USE furniture_platform' > $null 2>&1
if ($LASTEXITCODE -ne 0) {
    Write-Host 'Database not found. Importing sql/schema.sql...' -ForegroundColor Yellow
    $schemaPath = Join-Path $projectRoot 'sql\schema.sql'
    if (-not (Test-Path $schemaPath)) {
        Write-Host 'ERROR: schema.sql not found.' -ForegroundColor Red
        exit 1
    }
    Get-Content -Path $schemaPath -Raw -Encoding UTF8 | & $mysql @mysqlArgs 2>$null
    if ($LASTEXITCODE -ne 0) {
        Write-Host 'ERROR: Failed to import schema.sql.' -ForegroundColor Red
        exit 1
    }
}

Write-Host ''
Write-Host '========================================' -ForegroundColor Cyan
Write-Host 'Site: http://localhost:8080' -ForegroundColor Yellow
Write-Host 'Catalog: http://localhost:8080/catalog' -ForegroundColor Yellow
Write-Host 'Product: http://localhost:8080/product/1' -ForegroundColor Yellow
Write-Host 'Stop: Ctrl+C' -ForegroundColor Cyan
Write-Host '========================================' -ForegroundColor Cyan
Write-Host ''

Set-Location $projectRoot
$router = Join-Path $projectRoot 'router.php'
& $php -S localhost:8080 -t $projectRoot $router
