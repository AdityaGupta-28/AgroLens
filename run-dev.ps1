# AgroLens - Laravel server + Vite (for UI development)
Set-Location $PSScriptRoot

Write-Host "Starting AgroLens (server + Vite)..." -ForegroundColor Green
Write-Host "Open: http://127.0.0.1:8000" -ForegroundColor Cyan
Write-Host ""

php artisan config:clear | Out-Null
composer run dev
