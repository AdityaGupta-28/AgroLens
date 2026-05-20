# AgroLens - start the Laravel web server
Set-Location $PSScriptRoot

Write-Host ""
Write-Host "  AgroLens - Land Insights Platform" -ForegroundColor Green
Write-Host "  --------------------------------" -ForegroundColor Green
Write-Host ""
Write-Host "  1. Open this URL in your browser:" -ForegroundColor Yellow
Write-Host "     http://127.0.0.1:8000" -ForegroundColor Cyan
Write-Host ""
Write-Host "  2. Log in with:" -ForegroundColor Yellow
Write-Host "     Email:    officer@agrolens.gov.in" -ForegroundColor White
Write-Host "     Password: password" -ForegroundColor White
Write-Host ""
Write-Host "  Do NOT use http://localhost:5173 (that is Vite only)." -ForegroundColor DarkGray
Write-Host "  Keep this window open while using the app." -ForegroundColor DarkGray
Write-Host ""

php artisan config:clear | Out-Null
php artisan serve --host=127.0.0.1 --port=8000
