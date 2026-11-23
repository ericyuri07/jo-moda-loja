# Gera o Flutter Web apontando para a API do servidor e copia para deploy/front.
#
# Uso (PowerShell, no Windows):
#   .\deploy\build-front.ps1 -SiteUrl http://123.45.67.89
#   .\deploy\build-front.ps1 -SiteUrl https://jomodas.com.br -Whatsapp 5542999145187
param(
    [Parameter(Mandatory = $true)] [string] $SiteUrl,
    [string] $Whatsapp = "",
    [string] $FrontPath = "$env:USERPROFILE\Downloads\jo_modas"
)

# "Continue": o Flutter escreve avisos no stderr, e o PowerShell 5.1 trataria isso como erro.
$ErrorActionPreference = "Continue"
$SiteUrl = $SiteUrl.TrimEnd("/")
$dest = Join-Path $PSScriptRoot "front"

$defines = @("--dart-define=API_URL=$SiteUrl/api")
if ($Whatsapp) { $defines += "--dart-define=STORE_WHATSAPP=$Whatsapp" }

Push-Location $FrontPath
try {
    flutter pub get
    if ($LASTEXITCODE -ne 0) { throw "flutter pub get falhou" }
    flutter build web --release --no-wasm-dry-run @defines
    if ($LASTEXITCODE -ne 0) { throw "flutter build web falhou" }
} finally {
    Pop-Location
}

$ErrorActionPreference = "Stop"
Get-ChildItem $dest -Exclude ".gitkeep" | Remove-Item -Recurse -Force
Copy-Item -Path (Join-Path $FrontPath "build\web\*") -Destination $dest -Recurse -Force
# Lixo que o Windows cria ao copiar para o WSL (flutter.js:Zone.Identifier etc.)
Get-ChildItem $dest -Recurse -Filter "*Zone.Identifier" | Remove-Item -Force
Write-Host "Pronto: site gerado em $dest (API: $SiteUrl/api)"
