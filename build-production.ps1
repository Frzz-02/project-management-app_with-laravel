# =====================================================
# PRODUCTION BUILD SCRIPT (Windows PowerShell)
# Jalankan script ini sebelum upload ke cPanel
# =====================================================

Write-Host "Starting production build..." -ForegroundColor Green

# 1. Install dependencies (production only)
Write-Host "Installing production dependencies..." -ForegroundColor Yellow
composer install --no-dev --optimize-autoloader

# 2. Build frontend assets
Write-Host "Building frontend assets..." -ForegroundColor Yellow
npm run build

# 3. Clear all cache
Write-Host "Clearing cache..." -ForegroundColor Yellow
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 4. Optimize for production
Write-Host "Optimizing for production..." -ForegroundColor Yellow
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Create production env file template
Write-Host "Creating .env.production template..." -ForegroundColor Yellow
@"
APP_NAME="Project Management"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://yourdomain.com

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

BROADCAST_DRIVER=log
CACHE_DRIVER=database
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
SESSION_DRIVER=database
SESSION_LIFETIME=120

VITE_APP_NAME="`${APP_NAME}"
"@ | Out-File -FilePath ".env.production" -Encoding UTF8

Write-Host ""
Write-Host "Production build completed!" -ForegroundColor Green
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Cyan
Write-Host "1. Generate APP_KEY dengan: php artisan key:generate --show" -ForegroundColor White
Write-Host "2. Copy APP_KEY ke .env.production" -ForegroundColor White
Write-Host "3. Compress project ke .zip (exclude: node_modules, .git, .env)" -ForegroundColor White
Write-Host "4. Upload .zip ke cPanel File Manager" -ForegroundColor White
Write-Host "5. Extract di cPanel" -ForegroundColor White
Write-Host "6. Rename .env.production menjadi .env" -ForegroundColor White
Write-Host "7. Import database/deploy/production.sql ke phpMyAdmin" -ForegroundColor White
Write-Host "8. Setup public folder sesuai panduan" -ForegroundColor White
