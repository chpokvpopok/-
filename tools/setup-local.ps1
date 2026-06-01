$tools = Join-Path $PSScriptRoot '.'
if (-not (Test-Path $tools)) {
    New-Item -Path $tools -ItemType Directory | Out-Null
}
$zipUrl = 'https://windows.php.net/downloads/releases/php-8.2.31-Win32-vs16-x64.zip'
$zipPath = Join-Path $tools 'php-8.2.31.zip'
$phpDir = Join-Path $tools 'php-8.2.31'
if (-not (Test-Path $zipPath)) {
    Write-Host "Downloading PHP from $zipUrl..."
    Invoke-WebRequest -Uri $zipUrl -OutFile $zipPath -UseBasicParsing
}
if (-not (Test-Path $phpDir)) {
    Write-Host "Extracting PHP to $phpDir..."
    Expand-Archive -LiteralPath $zipPath -DestinationPath $phpDir
}
$phpExe = Join-Path $phpDir 'php.exe'
if (-not (Test-Path $phpExe)) {
    throw "php.exe not found at $phpExe"
}
$iniSource = Join-Path $phpDir 'php.ini-production'
$iniTarget = Join-Path $phpDir 'php.ini'
if (-not (Test-Path $iniTarget)) {
    Copy-Item -Path $iniSource -Destination $iniTarget
}
$iniText = Get-Content -Path $iniTarget
if ($iniText -notmatch '^[\s;]*extension=pdo_mysql') {
    $iniText = $iniText -replace '^[\s;]*;?extension=pdo_(mysql|mysqli|pdo_mysql).*', 'extension=pdo_mysql'
    if ($iniText -notmatch '^[\s;]*extension=pdo_mysql') {
        $iniText += "`r`nextension=pdo_mysql"
    }
    Set-Content -Path $iniTarget -Value $iniText -Encoding UTF8
}
Write-Host "PHP binary: $phpExe"
Get-Service -Name 'MySQL*' -ErrorAction SilentlyContinue | Select-Object Name, Status, DisplayName | Format-Table
Write-Host `"Use this PHP binary for local startup: $phpExe`"