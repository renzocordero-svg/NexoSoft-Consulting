param([int]$Puerto = 8000)
$ErrorActionPreference = 'Stop'
Set-Location -LiteralPath $PSScriptRoot
$phpExe = (Get-Command php -ErrorAction SilentlyContinue).Source
if (!$phpExe -and (Test-Path 'C:\xampp\php\php.exe')) { $phpExe = 'C:\xampp\php\php.exe' }
if (!$phpExe) { throw 'Instala PHP 8.3 con pdo_sqlite y zip.' }
if (!(Test-Path 'vendor/autoload.php')) { throw 'Ejecuta composer install en esta carpeta primero.' }
if (!(Test-Path '.env')) { Copy-Item '.env.example' '.env' }
if ((Get-Content '.env' -Raw) -match '(?m)^APP_KEY=\s*$') {
    & $phpExe artisan key:generate
    if ($LASTEXITCODE -ne 0) { throw 'No se pudo generar la clave.' }
}
if (!(Test-Path 'database/database.sqlite')) { New-Item -ItemType File 'database/database.sqlite' | Out-Null }
& $phpExe artisan migrate --seed
if ($LASTEXITCODE -ne 0) { throw 'No se pudo preparar la base de datos.' }
& $phpExe artisan serve --host=127.0.0.1 --port=$Puerto
