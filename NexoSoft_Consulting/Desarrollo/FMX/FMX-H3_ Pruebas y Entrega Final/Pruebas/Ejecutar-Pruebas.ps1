param(
    [ValidateSet('Integracion', 'Regresion')]
    [string]$Suite = 'Integracion',
    [string]$Php = 'php'
)

$ErrorActionPreference = 'Stop'
$appPath = [IO.Path]::GetFullPath((Join-Path $PSScriptRoot '../../FMX-H2_ Diseño y Desarrollo Core/App-ferremax'))
$phpunitPath = Join-Path $appPath 'vendor/phpunit/phpunit/phpunit'
if (!(Test-Path -LiteralPath $phpunitPath)) {
    throw 'Faltan dependencias. Ejecute composer install dentro de App-ferremax del Hito 2.'
}
# No ejecutar con una configuración cacheada que pudiera apuntar a una base real.
if (Test-Path -LiteralPath (Join-Path $appPath 'bootstrap/cache/config.php')) {
    throw 'Existe configuración cacheada. Use una copia de pruebas sin bootstrap/cache/config.php antes de ejecutar.'
}
$evidencePath = Join-Path $PSScriptRoot 'Evidencias'
New-Item -ItemType Directory -Force -Path $evidencePath | Out-Null
$runId = Get-Date -Format 'yyyyMMdd-HHmmss-fff'
$prefix = Join-Path $evidencePath "$runId-$Suite"
$configPath = if ($Suite -eq 'Integracion') { Join-Path $PSScriptRoot 'phpunit.xml' } else { Join-Path $appPath 'phpunit.xml' }

Push-Location $appPath
try {
    $revision = (& git rev-parse HEAD | Out-String).Trim()
    $branch = (& git branch --show-current | Out-String).Trim()
    $phpVersion = (& $Php -v | Out-String).Trim()
    $dirtyFiles = (& git status --short | Out-String).Trim()
    $started = (Get-Date).ToString('o')
    & $Php $phpunitPath --configuration $configPath --testdox --display-deprecations --colors=never --do-not-cache-result --log-junit "$prefix.junit.xml" 2>&1 |
        Tee-Object -FilePath "$prefix.log"
    $testExitCode = $LASTEXITCODE
    [ordered]@{
        inicio = $started
        fin = (Get-Date).ToString('o')
        suite = $Suite
        revision_base = $revision
        rama = $branch
        cambios_sin_commit = $dirtyFiles
        php = $phpVersion
        configuracion = $configPath
        base_de_pruebas = 'SQLite :memory:'
        codigo_salida = $testExitCode
        nota = 'La revisión identifica la base. Los cambios sin commit también forman parte de lo probado. Las fechas son las del equipo ejecutor.'
    } | ConvertTo-Json | Set-Content -LiteralPath "$prefix.entorno.json" -Encoding utf8
} finally {
    Pop-Location
}
exit $testExitCode
