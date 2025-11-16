# 📋 DEPLOY CHECKLIST

## ✅ PERSIAPAN DI LOCAL

### Build Production
- [ ] Jalankan `npm run build` untuk compile Tailwind + Alpine
- [ ] Verify folder `public/build/` terisi dengan assets
- [ ] Jalankan `composer install --no-dev --optimize-autoloader`
- [ ] Jalankan `php artisan config:cache`
- [ ] Jalankan `php artisan route:cache`
- [ ] Jalankan `php artisan view:cache`

### Generate Keys & Configs
- [ ] Generate APP_KEY: `php artisan key:generate --show`
- [ ] Copy APP_KEY hasil generate
- [ ] Buat/edit file `.env.production`
- [ ] Paste APP_KEY ke `.env.production`
- [ ] Set `APP_DEBUG=false` di `.env.production`
- [ ] Set `APP_ENV=production` di `.env.production`
- [ ] Set `APP_URL` dengan domain production

### Database
- [ ] Export database schema: `database/deploy/production.sql`
- [ ] Verify SQL file bisa diimport (cek syntax error)
- [ ] Backup SQL file di safe place

### Compress Project
- [ ] **EXCLUDE** folder `node_modules/` (WAJIB!)
- [ ] **EXCLUDE** folder `.git/`
- [ ] **EXCLUDE** file `.env` (local)
- [ ] **INCLUDE** folder `vendor/` (production dependencies)
- [ ] **INCLUDE** folder `public/build/` (compiled assets)
- [ ] **INCLUDE** file `.env.production`
- [ ] **INCLUDE** file `generate-key.php`
- [ ] **INCLUDE** file `database/deploy/production.sql`
- [ ] Compress ke format `.zip`
- [ ] Verify ukuran file .zip (seharusnya < 50MB tanpa node_modules)

---

## 🌐 DEPLOY DI CPANEL

### Upload Files
- [ ] Login ke cPanel
- [ ] Buka File Manager
- [ ] Navigate ke home directory (bukan public_html)
- [ ] Buat folder `laravel`
- [ ] Upload file `.zip` ke folder `laravel`
- [ ] Extract file `.zip`
- [ ] Verify semua folder/file ter-extract dengan benar
- [ ] Delete file `.zip` setelah extract

### Setup Public Folder
- [ ] Navigate ke folder `laravel/public/`
- [ ] Copy semua isi folder `public/` ke `public_html/`
- [ ] Edit `public_html/index.php`
- [ ] Ubah path bootstrap: `require __DIR__.'/../laravel/bootstrap/app.php';`
- [ ] Verify file `.htaccess` ada di `public_html/`
- [ ] Verify folder `public_html/build/` terisi

### Setup Database
- [ ] Buka "MySQL Databases" di cPanel
- [ ] Buat database baru (catat nama database)
- [ ] Buat user database (catat username & password)
- [ ] Add user ke database dengan "All Privileges"
- [ ] Buka phpMyAdmin
- [ ] Select database yang baru dibuat
- [ ] Import file `laravel/database/deploy/production.sql`
- [ ] Verify import sukses (check tabel users ada)

### Setup Environment File
- [ ] Navigate ke folder `laravel/`
- [ ] Rename `.env.production` menjadi `.env`
- [ ] Edit file `.env`
- [ ] Set `DB_HOST=localhost`
- [ ] Set `DB_DATABASE=` (nama database yang dibuat)
- [ ] Set `DB_USERNAME=` (username database yang dibuat)
- [ ] Set `DB_PASSWORD=` (password database yang dibuat)
- [ ] Save file `.env`

### Setup Permissions
- [ ] Folder `laravel/storage/` → Set permission **775**
- [ ] ✅ Check "Recurse into subdirectories"
- [ ] Folder `laravel/bootstrap/cache/` → Set permission **775**
- [ ] ✅ Check "Recurse into subdirectories"

### Verify APP_KEY (Jika Belum Ada)
- [ ] Akses `https://yourdomain.com/generate-key.php`
- [ ] Copy APP_KEY yang di-generate
- [ ] Edit `.env` dan paste APP_KEY
- [ ] **DELETE** file `generate-key.php` (PENTING!)

---

## 🧪 TESTING

### Basic Testing
- [ ] Akses domain: `https://yourdomain.com`
- [ ] Verify homepage load tanpa error
- [ ] Check CSS load dengan benar (Tailwind)
- [ ] Check JS berfungsi (Alpine.js)
- [ ] Test halaman login
- [ ] Test halaman register

### Database Connection
- [ ] Login dengan admin default:
  - Email: `admin@example.com`
  - Password: `password`
- [ ] Verify bisa login
- [ ] Test create project (jika admin)
- [ ] Test logout

### Error Check
- [ ] Check browser console (F12) - tidak ada error JS
- [ ] Check Network tab - semua assets load (200 status)
- [ ] Test navigasi antar halaman
- [ ] Test form submission

---

## 🔒 SECURITY

### Final Security Checks
- [ ] Set `APP_DEBUG=false` di `.env` (WAJIB!)
- [ ] Delete file `generate-key.php`
- [ ] Verify `.env` tidak bisa diakses dari browser
- [ ] Verify folder `storage/` tidak bisa diakses dari browser
- [ ] Change default admin password
- [ ] Remove/disable test accounts (jika ada)

### Optional Security
- [ ] Enable SSL/HTTPS (recommended)
- [ ] Setup firewall rules (jika ada)
- [ ] Enable fail2ban (jika tersedia)
- [ ] Setup backup schedule di cPanel

---

## 📝 POST-DEPLOY

### Documentation
- [ ] Update README dengan production URL
- [ ] Document admin credentials (save securely)
- [ ] Document database credentials (save securely)
- [ ] Create backup of `.env` file (save securely)

### Monitoring
- [ ] Check storage logs: `laravel/storage/logs/laravel.log`
- [ ] Monitor cPanel error logs
- [ ] Setup uptime monitoring (optional)
- [ ] Test all major features

### Backup
- [ ] Backup database via phpMyAdmin
- [ ] Download backup `.env` file
- [ ] Create restore plan/documentation

---

## 🚨 TROUBLESHOOTING CHECKLIST

Jika ada error, check:
- [ ] Storage permissions (775)
- [ ] `.env` file configured correctly
- [ ] Database credentials correct
- [ ] `public_html/index.php` path correct
- [ ] `.htaccess` file exists
- [ ] `APP_KEY` set in `.env`
- [ ] Error logs: `storage/logs/laravel.log`
- [ ] cPanel error logs

---

## ✅ DEPLOYMENT COMPLETE!

Jika semua checklist sudah ✅, deployment sukses!

**Next Steps:**
1. Change default passwords
2. Setup regular backups
3. Monitor application performance
4. Update documentation

**Congratulations! 🎉**
