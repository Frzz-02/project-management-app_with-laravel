# 🚀 QUICK DEPLOY GUIDE - cPanel

## 📦 **DI LOCAL (5 Menit)**

```powershell
# 1. Build assets
npm run build

# 2. Install production dependencies
composer install --no-dev --optimize-autoloader

# 3. Cache everything
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 4. Generate APP_KEY
php artisan key:generate --show
# Copy output!

# 5. Compress project
# Exclude: node_modules, .git, .env
# Include: vendor, public/build, .env.production
```

---

## 🌐 **DI CPANEL (10 Menit)**

### 1. Upload & Extract
- Upload `.zip` ke folder `laravel` (bukan public_html)
- Extract

### 2. Setup Public
- Copy isi `laravel/public/` ke `public_html/`
- Edit `public_html/index.php`:
  ```php
  require __DIR__.'/../laravel/bootstrap/app.php';
  ```

### 3. Database
- Buat database & user di cPanel
- Import `laravel/database/deploy/production.sql` via phpMyAdmin

### 4. Setup .env
- Rename `laravel/.env.production` → `.env`
- Edit DB credentials:
  ```
  DB_DATABASE=your_database
  DB_USERNAME=your_user
  DB_PASSWORD=your_password
  ```
- Paste APP_KEY dari step 4 di local

### 5. Permissions
- Set `laravel/storage/` → **775** (recurse)
- Set `laravel/bootstrap/cache/` → **775** (recurse)

### 6. Test
- Akses domain
- Login: `admin@example.com` / `password`

---

## ✅ **DONE!**

**⚠️ Security:**
- Set `APP_DEBUG=false`
- Delete `generate-key.php`
- Change admin password

**📝 Full guide:** Lihat `DEPLOY_CPANEL.md`
