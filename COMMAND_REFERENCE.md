# ⚡ QUICK COMMAND REFERENCE

## 🏠 **LOCAL DEVELOPMENT**

### Start Development
```powershell
# Start all services (jika ada composer script 'dev')
composer dev

# Atau manual:
php artisan serve              # Laravel server
npm run dev                    # Vite dev server
php artisan queue:listen       # Queue worker (optional)
```

### Database
```powershell
php artisan migrate            # Run migrations
php artisan migrate:fresh      # Fresh migrations
php artisan db:seed            # Seed database
php artisan migrate:fresh --seed  # Fresh + seed
```

### Clear Cache
```powershell
php artisan optimize:clear     # Clear all cache
php artisan config:clear       # Clear config
php artisan cache:clear        # Clear app cache
php artisan route:clear        # Clear routes
php artisan view:clear         # Clear views
```

---

## 🚀 **PRODUCTION BUILD**

### Complete Build Script
```powershell
# Method 1: Use script (RECOMMENDED)
.\build-production.ps1

# Method 2: Manual commands
npm run build
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Generate APP_KEY
```powershell
php artisan key:generate --show
# Copy output and paste to .env
```

### Verify Build
```powershell
# Check if build folder exists
Test-Path public/build

# List build files
Get-ChildItem public/build -Recurse
```

---

## 🗜️ **COMPRESS PROJECT**

### PowerShell (Windows)
```powershell
# Using Compress-Archive
$exclude = @('node_modules', '.git', '.env', 'storage/logs/*.log', 'tests')
$files = Get-ChildItem -Path . -Recurse -Exclude $exclude
Compress-Archive -Path $files -DestinationPath project-deploy.zip -Force
```

### Manual (Recommended)
```
1. Install 7-Zip or WinRAR
2. Right-click project folder
3. Add to archive
4. EXCLUDE:
   - node_modules/
   - .git/
   - .env
   - storage/logs/*.log
   - tests/
```

---

## 💾 **DATABASE OPERATIONS**

### Export Database
```powershell
# Via Laravel
php artisan schema:dump --path=database/deploy/schema.sql

# Via MySQL command (if available)
mysqldump -u root -p database_name > database/deploy/production.sql
```

### Backup Database
```powershell
# Production database backup
php artisan db:backup  # If you have backup package

# Manual via phpMyAdmin:
# 1. Select database
# 2. Click "Export"
# 3. Choose "SQL" format
# 4. Click "Go"
```

---

## 🔑 **GENERATE KEYS & SECRETS**

### APP_KEY
```powershell
# Method 1: Artisan command
php artisan key:generate --show

# Method 2: Online (if no CLI in cPanel)
# Upload generate-key.php to cPanel
# Access: https://yourdomain.com/generate-key.php
```

### Generate Random Password
```powershell
# PowerShell random password
-join ((48..57) + (65..90) + (97..122) | Get-Random -Count 16 | % {[char]$_})
```

---

## 📦 **COMPOSER OPERATIONS**

### Production Install
```powershell
composer install --no-dev --optimize-autoloader
```

### Development Install
```powershell
composer install
```

### Update Dependencies
```powershell
composer update              # Update all
composer update vendor/package  # Update specific
```

### Clear Composer Cache
```powershell
composer clear-cache
composer dump-autoload
```

---

## 🎨 **NPM/VITE OPERATIONS**

### Development
```powershell
npm install                  # Install dependencies
npm run dev                  # Start dev server
```

### Production
```powershell
npm run build                # Build for production
npm run preview              # Preview production build
```

### Check Build Output
```powershell
# Verify manifest exists
Get-Content public/build/manifest.json

# Check assets size
Get-ChildItem public/build/assets | Measure-Object -Property Length -Sum
```

---

## 🧪 **TESTING**

### Run Tests
```powershell
php artisan test             # Run all tests
php artisan test --filter TestName  # Run specific test
composer test                # If you have test script
```

### Code Quality
```powershell
# Laravel Pint (code formatter)
./vendor/bin/pint

# Static analysis (if PHPStan installed)
./vendor/bin/phpstan analyse
```

---

## 🔍 **DEBUGGING**

### Check Logs
```powershell
# View latest log
Get-Content storage/logs/laravel.log -Tail 50

# Clear logs
Remove-Item storage/logs/*.log
```

### Check Routes
```powershell
php artisan route:list       # List all routes
php artisan route:list --name=project  # Filter by name
```

### Check Config
```powershell
php artisan config:show      # Show all config
php artisan config:show database  # Show specific config
```

### Application Info
```powershell
php artisan about            # Show app info
php artisan env              # Show environment
```

---

## 🛠️ **MAINTENANCE**

### Maintenance Mode
```powershell
php artisan down             # Enable maintenance
php artisan down --secret=secret-token  # With bypass token
php artisan up               # Disable maintenance
```

### Storage Link
```powershell
php artisan storage:link     # Create storage symlink
```

### Clear Everything
```powershell
php artisan optimize:clear   # Clear all cache
composer dump-autoload       # Regenerate autoload
```

---

## 🔒 **SECURITY**

### Generate New Secret
```powershell
# New APP_KEY
php artisan key:generate

# New encryption key (if exists)
php artisan encryption:generate
```

### Check Security
```powershell
# Check outdated dependencies
composer outdated

# Security audit (if available)
composer audit
```

---

## 📊 **OPTIMIZATION**

### Cache Everything
```powershell
php artisan optimize         # Cache config + routes
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### Autoloader Optimization
```powershell
composer dump-autoload --optimize
composer dump-autoload --classmap-authoritative  # Even more optimized
```

---

## 🚨 **EMERGENCY COMMANDS**

### Complete Reset
```powershell
# WARNING: This will delete all data!
php artisan migrate:fresh    # Reset database
php artisan optimize:clear   # Clear all cache
composer dump-autoload       # Regenerate autoload
npm run build                # Rebuild assets
```

### Fix Permissions (Linux/Mac)
```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### Fix Permissions (cPanel)
```
Via File Manager:
1. Select storage folder
2. Change Permissions → 775
3. Check "Recurse into subdirectories"
4. Apply
```

---

## 📝 **USEFUL CHECKS**

### Verify Production Ready
```powershell
# Check .env
Get-Content .env | Select-String "APP_"

# Check build folder
Test-Path public/build/manifest.json

# Check vendor folder
Test-Path vendor/autoload.php

# Check cache
Test-Path bootstrap/cache/config.php
```

### File Sizes
```powershell
# Project size (exclude node_modules)
Get-ChildItem -Exclude node_modules | Measure-Object -Property Length -Sum

# Build size
Get-ChildItem public/build | Measure-Object -Property Length -Sum
```

---

## 💡 **QUICK TIPS**

```powershell
# Create new controller
php artisan make:controller ControllerName

# Create new model
php artisan make:model ModelName -m  # with migration

# Create new migration
php artisan make:migration create_table_name

# Create new request
php artisan make:request StoreRequestName

# Create new policy
php artisan make:policy PolicyName --model=ModelName
```

---

## 🔗 **USEFUL ALIASES** (Add to PowerShell Profile)

```powershell
# Open profile
notepad $PROFILE

# Add these aliases:
function pa { php artisan $args }
function pas { php artisan serve }
function pam { php artisan migrate }
function pac { php artisan optimize:clear }
function ci { composer install }
function cu { composer update }
function nr { npm run $args }
function nrd { npm run dev }
function nrb { npm run build }
```

---

## 📚 **DOCUMENTATION LINKS**

- Laravel Docs: https://laravel.com/docs
- Tailwind Docs: https://tailwindcss.com/docs
- Alpine.js Docs: https://alpinejs.dev
- Vite Docs: https://vitejs.dev

---

**Save this file for quick reference! 📌**
