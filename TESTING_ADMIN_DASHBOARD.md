# 🧪 TESTING ADMIN DASHBOARD - LOCAL

## ✅ PERSIAPAN

### 1. Cache Sudah Di-clear
```bash
php artisan config:clear
php artisan route:clear  
php artisan view:clear
php artisan cache:clear
```

### 2. Server Running
```bash
php artisan serve
# Akses: http://localhost:8000
```

### 3. Admin User Available
**Email:** `admin@test.com`
**Password:** `password` (default)
**Role:** `admin`

---

## 🔍 TEST SCENARIOS

### Test 1: Login Sebagai Non-Admin
**Expected:** Redirect ke `/dashboard` dengan error message

1. Buka browser: `http://localhost:8000/login`
2. Login dengan:
   - Email: `feri@gmail.com`
   - Password: `password`
3. Akses: `http://localhost:8000/admin/dashboard`
4. **EXPECTED RESULT:**
   - ✅ Redirect ke `http://localhost:8000/dashboard`
   - ✅ Muncul flash message: "Akses ditolak. Halaman ini hanya untuk Admin."
   - ❌ TIDAK redirect ke `/api/notifications/unread-count`

### Test 2: Login Sebagai Admin
**Expected:** Tampil dashboard admin

1. Logout dulu
2. Login dengan:
   - Email: `admin@test.com`
   - Password: `password`
3. Akses: `http://localhost:8000/admin/dashboard`
4. **EXPECTED RESULT:**
   - ✅ Tampil halaman "Admin Dashboard"
   - ✅ Muncul statistik, charts, dll
   - ❌ TIDAK ada error 500
   - ❌ TIDAK redirect ke route lain

### Test 3: Akses Admin Dashboard Tanpa Login
**Expected:** Redirect ke login

1. Logout atau buka incognito
2. Akses langsung: `http://localhost:8000/admin/dashboard`
3. **EXPECTED RESULT:**
   - ✅ Redirect ke `http://localhost:8000/login`
   - ✅ Muncul flash message: "Silakan login terlebih dahulu."

---

## 🐛 TROUBLESHOOTING

### Issue: Masih redirect ke `/api/notifications/unread-count`

**Possible Causes:**
1. ❌ Route cache belum di-clear
2. ❌ Browser cache masih menyimpan old JavaScript
3. ❌ AdminDashboardController masih ada `dd()`

**Solutions:**
```bash
# 1. Clear Laravel cache
php artisan optimize:clear

# 2. Clear browser cache
# Tekan: Ctrl + Shift + Delete
# Atau: Buka incognito mode (Ctrl + Shift + N)

# 3. Hard refresh
# Tekan: Ctrl + Shift + R (Chrome)
# Tekan: Ctrl + F5 (Firefox)

# 4. Check controller
# Pastikan tidak ada dd() atau dump() di AdminDashboardController
```

### Issue: Error 500 saat akses `/admin/dashboard`

**Check:**
1. `storage/logs/laravel.log` - Lihat error message
2. AdminDashboardController - Pastikan query tidak error
3. View file - `resources/views/admin/dashboard.blade.php` harus ada

**Quick fix:**
```bash
# Comment dd() di controller
# File: app/Http/Controllers/web/AdminDashboardController.php
# Line 64: dd($viewData); → // dd($viewData);
```

### Issue: "Class not found" error

**Solution:**
```bash
composer dump-autoload
php artisan clear-compiled
```

---

## 📸 EXPECTED SCREENSHOTS

### ✅ Success: Admin Dashboard
```
URL: http://localhost:8000/admin/dashboard
Header: "📊 Admin Dashboard"
Content: Stats cards, charts, recent activities
No errors, no redirects
```

### ✅ Success: Non-Admin Blocked
```
URL Requested: http://localhost:8000/admin/dashboard
URL Result: http://localhost:8000/dashboard (redirected)
Flash Message: "Akses ditolak. Halaman ini hanya untuk Admin."
```

### ✅ Success: Not Logged In
```
URL Requested: http://localhost:8000/admin/dashboard
URL Result: http://localhost:8000/login (redirected)
Flash Message: "Silakan login terlebih dahulu."
```

---

## 🔑 QUICK LOGIN CREDENTIALS

### Admin User
```
Email: admin@test.com
Password: password
Role: admin
Access: ✅ Can access /admin/dashboard
```

### Regular User (untuk test blocked access)
```
Email: feri@gmail.com
Password: password
Role: member
Access: ❌ Cannot access /admin/dashboard (will redirect)
```

---

## ✅ CHECKLIST

Setelah test, verify:

- [ ] Non-admin user **TIDAK bisa** akses `/admin/dashboard`
- [ ] Non-admin user di-redirect ke `/dashboard` dengan error message
- [ ] Admin user **BISA** akses `/admin/dashboard`
- [ ] Admin dashboard menampilkan data dengan benar
- [ ] Tidak ada `dd()` yang blocking view
- [ ] Tidak ada redirect loop ke `/api/notifications/unread-count`
- [ ] Flash message muncul saat akses ditolak

---

## 🚀 NEXT STEPS

Jika semua test ✅ PASS di local:

1. **Build production assets:**
   ```bash
   npm run build
   ```

2. **Upload files ke cPanel:**
   - `app/Http/Middleware/isAdmin.php`
   - `app/Http/Controllers/web/AdminDashboardController.php`
   - `public/build/` (entire folder)

3. **Clear cache di production:**
   - Delete: `bootstrap/cache/*.php`
   - Delete: `storage/framework/views/*`

4. **Test di production**

---

## 📞 NEED HELP?

Jika masih ada issue, **kirim screenshot** dari:
1. Browser URL bar (showing current URL)
2. Error message (jika ada)
3. Laravel log (`storage/logs/laravel.log`) - last 20 lines
