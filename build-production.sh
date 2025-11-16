#!/bin/bash
# =====================================================
# PRODUCTION BUILD SCRIPT
# Jalankan script ini sebelum upload ke cPanel
# =====================================================

echo "🚀 Starting production build..."

# 1. Install dependencies (production only)
echo "📦 Installing production dependencies..."
composer install --no-dev --optimize-autoloader

# 2. Build frontend assets
echo "🎨 Building frontend assets..."
npm run build

# 3. Clear all cache
echo "🧹 Clearing cache..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 4. Optimize for production
echo "⚡ Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Generate production .env file template
echo "📝 Creating .env.production template..."
cat > .env.production << 'EOF'
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

VITE_APP_NAME="${APP_NAME}"
EOF

echo "✅ Production build completed!"
echo ""
echo "📋 Next steps:"
echo "1. Copy semua file ke folder local untuk upload"
echo "2. Edit .env.production dengan database credentials cPanel"
echo "3. Rename .env.production menjadi .env di cPanel"
echo "4. Upload production.sql ke phpMyAdmin di cPanel"
echo "5. Setup public folder di cPanel"
