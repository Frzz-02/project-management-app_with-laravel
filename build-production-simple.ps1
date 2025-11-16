# =====================================================
# PRODUCTION BUILD SCRIPT - SIMPLE VERSION
# Jalankan script ini sebelum upload ke cPanel
# =====================================================

Write-Host "`n==================================================" -ForegroundColor Cyan
Write-Host "      PRODUCTION BUILD SCRIPT" -ForegroundColor Cyan
Write-Host "==================================================" -ForegroundColor Cyan
Write-Host ""

# Step 1: Install production dependencies
Write-Host "[1/5] Installing production dependencies..." -ForegroundColor Yellow
try {
    composer install --no-dev --optimize-autoloader --no-interaction
    Write-Host "[OK] Dependencies installed successfully" -ForegroundColor Green
} catch {
    Write-Host "[ERROR] Failed to install dependencies: $_" -ForegroundColor Red
    exit 1
}

Write-Host ""

# Step 2: Build frontend assets
Write-Host "[2/5] Building frontend assets (Tailwind + Alpine)..." -ForegroundColor Yellow
try {
    npm run build
    Write-Host "[OK] Assets built successfully" -ForegroundColor Green
} catch {
    Write-Host "[ERROR] Failed to build assets: $_" -ForegroundColor Red
    exit 1
}

Write-Host ""

# Step 3: Clear all cache
Write-Host "[3/5] Clearing Laravel cache..." -ForegroundColor Yellow
try {
    php artisan config:clear
    php artisan cache:clear
    php artisan route:clear
    php artisan view:clear
    Write-Host "[OK] Cache cleared successfully" -ForegroundColor Green
} catch {
    Write-Host "[WARNING] Some cache clear failed, continuing..." -ForegroundColor Yellow
}

Write-Host ""

# Step 4: Optimize for production
Write-Host "[4/5] Optimizing for production..." -ForegroundColor Yellow
try {
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    Write-Host "[OK] Optimization completed" -ForegroundColor Green
} catch {
    Write-Host "[WARNING] Some optimization failed, continuing..." -ForegroundColor Yellow
}

Write-Host ""

# Step 5: Create .env.production template
Write-Host "[5/5] Creating .env.production template..." -ForegroundColor Yellow
$envContent = @"
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
"@

try {
    $envContent | Out-File -FilePath ".env.production" -Encoding UTF8 -Force
    Write-Host "[OK] .env.production created" -ForegroundColor Green
} catch {
    Write-Host "[ERROR] Failed to create .env.production: $_" -ForegroundColor Red
}

Write-Host ""
Write-Host "==================================================" -ForegroundColor Green
Write-Host "      BUILD COMPLETED SUCCESSFULLY!" -ForegroundColor Green
Write-Host "==================================================" -ForegroundColor Green
Write-Host ""

# Display next steps
Write-Host "NEXT STEPS:" -ForegroundColor Cyan
Write-Host "---------------------------------------------------" -ForegroundColor Gray
Write-Host ""
Write-Host " 1. Generate APP_KEY:" -ForegroundColor White
Write-Host "    php artisan key:generate --show" -ForegroundColor Gray
Write-Host ""
Write-Host " 2. Edit .env.production:" -ForegroundColor White
Write-Host "    - Paste APP_KEY dari step 1" -ForegroundColor Gray
Write-Host "    - Update APP_URL dengan domain production" -ForegroundColor Gray
Write-Host ""
Write-Host " 3. Compress project to .zip" -ForegroundColor White
Write-Host "    EXCLUDE: node_modules, .git, .env, tests" -ForegroundColor Gray
Write-Host "    INCLUDE: vendor, public/build/" -ForegroundColor Gray
Write-Host ""
Write-Host " 4. Upload to cPanel:" -ForegroundColor White
Write-Host "    - Upload .zip to folder 'laravel'" -ForegroundColor Gray
Write-Host "    - Extract files" -ForegroundColor Gray
Write-Host "    - Copy public/* to public_html/" -ForegroundColor Gray
Write-Host ""
Write-Host " 5. Setup database:" -ForegroundColor White
Write-Host "    - Create database in cPanel" -ForegroundColor Gray
Write-Host "    - Import: database/deploy/production.sql" -ForegroundColor Gray
Write-Host ""
Write-Host " 6. Configure .env:" -ForegroundColor White
Write-Host "    - Rename .env.production to .env" -ForegroundColor Gray
Write-Host "    - Update database credentials" -ForegroundColor Gray
Write-Host ""
Write-Host " 7. Set permissions:" -ForegroundColor White
Write-Host "    - storage/ -> 775 (recursive)" -ForegroundColor Gray
Write-Host "    - bootstrap/cache/ -> 775 (recursive)" -ForegroundColor Gray
Write-Host ""
Write-Host "---------------------------------------------------" -ForegroundColor Gray
Write-Host ""
Write-Host "For detailed instructions, see: DEPLOY_CPANEL.md" -ForegroundColor Cyan
Write-Host ""
