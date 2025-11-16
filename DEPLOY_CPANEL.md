# 📚 PANDUAN DEPLOY KE CPANEL
## Laravel Project Management Application

---

## 🎯 **PERSIAPAN DI LOCAL (WAJIB!)**

### **1. Build Production Assets**
```powershell
# Di PowerShell, jalankan script build
.\build-production.ps1

# Atau manual:
composer install --no-dev --optimize-autoloader
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### **2. Generate Application Key**
```powershell
php artisan key:generate --show
# Copy output: base64:xxxxxxxxxxxxxxxxxxxxx
```

### **3. Edit .env.production**
Edit file `.env.production` yang sudah dibuat:
- Paste `APP_KEY` dari langkah sebelumnya
- Ganti `APP_URL` dengan domain Anda
- **JANGAN** isi DB credentials dulu (nanti di cPanel)

### **4. Compress Project**
**EXCLUDE folder/file berikut:**
- ✅ `node_modules/` - **WAJIB EXCLUDE** (ukuran besar)
- ✅ `.git/` - Tidak perlu di production
- ✅ `.env` - File local, pakai .env.production
- ✅ `storage/logs/*.log` - Log file local
- ✅ `tests/` - Tidak perlu testing di production

**Yang WAJIB di-include:**
- ✅ `vendor/` - Dependencies PHP (sudah production-only)
- ✅ `public/build/` - Compiled assets (Tailwind + Alpine)
- ✅ `bootstrap/cache/` - Cache files
- ✅ `.env.production` - Template env production
- ✅ `database/deploy/production.sql` - Database schema

**Cara compress:**
```powershell
# Dengan 7-Zip atau WinRAR
# Klik kanan folder project > Add to archive
# Exclude folder di atas
```

---

## 🌐 **DEPLOY DI CPANEL**

### **STEP 1: Upload Files**

1. **Login cPanel**
2. **Buka File Manager**
3. **Navigate ke `public_html` atau `www`**
4. **Upload file .zip**
5. **Extract file .zip**
6. **Delete file .zip setelah extract**

### **STEP 2: Setup Folder Structure**

**Struktur cPanel yang benar:**
```
/home/username/
├── public_html/              ← Document root cPanel
│   ├── index.php            ← Akan kita buat redirect
│   ├── .htaccess           ← Akan kita buat redirect
│   └── (file static lainnya jika ada)
├── laravel/                  ← Upload project Laravel disini
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── public/              ← Folder public Laravel
│   │   ├── index.php       ← Entry point Laravel
│   │   ├── .htaccess
│   │   └── build/          ← Assets compiled
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   ├── vendor/
│   ├── .env.production
│   └── artisan
```

**Cara setup:**
1. Buat folder `laravel` di root (bukan di public_html)
2. Upload semua file project ke folder `laravel`
3. Setup symlink dari `public_html` ke `laravel/public`

### **STEP 3: Setup Public Folder (Symlink)**

**Opsi A: Via cPanel Terminal (jika tersedia)**
```bash
cd /home/username/public_html
# Backup public_html asli jika ada isi
mv index.html index.html.backup

# Buat symlink ke Laravel public folder
ln -s /home/username/laravel/public/* .

# ATAU copy semua isi folder public
cp -R /home/username/laravel/public/* /home/username/public_html/
```

**Opsi B: Via File Manager (jika tidak ada terminal)**
1. Copy semua isi dari `laravel/public/` 
2. Paste ke `public_html/`
3. **PENTING:** Edit `public_html/index.php`

**Edit `public_html/index.php`:**
```php
<?php
// Ubah path ini untuk point ke folder laravel
require __DIR__.'/../laravel/bootstrap/app.php';

// Bukan:
// require __DIR__.'/../bootstrap/app.php';
```

**Buat/Edit `public_html/.htaccess`:**
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
    
    # Redirect Trailing Slashes If Not A Folder
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]
    
    # Send Requests To Front Controller
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

### **STEP 4: Setup Database**

1. **Buka phpMyAdmin di cPanel**
2. **Buat database baru:**
   - Database name: `username_projectmanagement`
   - User: `username_dbuser`
   - Password: (strong password)
3. **Import SQL file:**
   - Upload file: `database/deploy/production.sql`
   - Klik "Import"
   - Wait until success

### **STEP 5: Setup Environment (.env)**

1. **Di File Manager, navigate ke folder `laravel`**
2. **Rename `.env.production` menjadi `.env`**
3. **Edit `.env` dengan credentials cPanel:**

```env
APP_NAME="Project Management"
APP_ENV=production
APP_KEY=base64:xxxxxxxxxxxxxxxxxxxxx  ← Dari php artisan key:generate
APP_DEBUG=false  ← WAJIB false di production!
APP_URL=https://yourdomain.com  ← Domain Anda

DB_CONNECTION=mysql
DB_HOST=localhost  ← Biasanya localhost di cPanel
DB_PORT=3306
DB_DATABASE=username_projectmanagement  ← Database yang dibuat
DB_USERNAME=username_dbuser  ← User yang dibuat
DB_PASSWORD=your_strong_password  ← Password database
```

### **STEP 6: Setup Permissions (Folder Storage)**

**Via cPanel File Manager:**
1. Navigate ke `laravel/storage`
2. Klik kanan > Change Permissions
3. Set ke **775** atau **755**
4. ✅ Check "Recurse into subdirectories"
5. Apply

**Folder yang perlu permission write:**
- `laravel/storage/` → 775
- `laravel/storage/logs/` → 775
- `laravel/storage/framework/` → 775
- `laravel/bootstrap/cache/` → 775

### **STEP 7: Verify Installation**

1. **Akses domain Anda:** `https://yourdomain.com`
2. **Test halaman login**
3. **Test database connection**
4. **Login dengan admin:**
   - Email: `admin@example.com`
   - Password: `password`

---

## 🔧 **TROUBLESHOOTING**

### **Error: 500 Internal Server Error**
**Solusi:**
1. Check storage permissions (harus 775)
2. Check .htaccess file di public_html
3. Enable error display di .env: `APP_DEBUG=true` (sementara)
4. Check error logs di cPanel

### **Error: No input file specified**
**Solusi:**
1. Check index.php path di public_html
2. Pastikan `.htaccess` sudah benar
3. Pastikan mod_rewrite enabled di cPanel

### **Error: Database connection failed**
**Solusi:**
1. Check DB credentials di .env
2. Pastikan database user punya privileges
3. Test connection via phpMyAdmin

### **Assets (CSS/JS) tidak load**
**Solusi:**
1. Check folder `public_html/build/` ada isinya
2. Check file `public_html/build/manifest.json`
3. Pastikan `APP_URL` di .env sudah benar
4. Clear browser cache

### **Error: Class not found**
**Solusi:**
```bash
# Via cPanel Terminal (jika ada)
cd /home/username/laravel
composer dump-autoload --optimize

# Via PHP Selector (jika ada)
# Pilih PHP version yang sesuai (8.1+)
```

---

## 📝 **CHECKLIST DEPLOY**

### **Pre-Upload:**
- [ ] Build assets dengan `npm run build`
- [ ] Install dependencies dengan `--no-dev`
- [ ] Cache config, routes, views
- [ ] Generate APP_KEY
- [ ] Edit .env.production
- [ ] Compress project (exclude node_modules, .git)

### **Di cPanel:**
- [ ] Upload & extract project
- [ ] Setup folder structure
- [ ] Copy/symlink public folder ke public_html
- [ ] Edit public_html/index.php path
- [ ] Buat database & user
- [ ] Import production.sql
- [ ] Setup .env dengan DB credentials
- [ ] Set storage permissions ke 775
- [ ] Test akses website
- [ ] Test login admin

---

## 🚀 **UPDATE/DEPLOY ULANG**

Jika ada update di local:

1. **Build ulang di local:**
   ```powershell
   npm run build
   php artisan config:cache
   php artisan route:cache
   ```

2. **Upload hanya file yang berubah:**
   - `public/build/` → Jika ada perubahan CSS/JS
   - `app/`, `routes/`, `resources/` → Jika ada perubahan code
   - `config/` → Jika ada perubahan config

3. **Clear cache di cPanel:**
   - Delete `bootstrap/cache/config.php`
   - Delete `bootstrap/cache/routes.php`

---

## 💡 **TIPS PRODUCTION**

1. **Selalu set `APP_DEBUG=false` di production!**
2. **Gunakan HTTPS (SSL) untuk keamanan**
3. **Backup database secara berkala**
4. **Monitor error logs di `storage/logs/`**
5. **Gunakan strong password untuk admin**
6. **Enable cache untuk performa:**
   - Config cache ✅
   - Route cache ✅
   - View cache ✅

---

## 📞 **SUPPORT**

Jika ada masalah:
1. Check error logs: `storage/logs/laravel.log`
2. Check cPanel error logs
3. Enable debug mode sementara untuk lihat error
4. Hubungi hosting support jika masalah server

**Good luck! 🚀**
