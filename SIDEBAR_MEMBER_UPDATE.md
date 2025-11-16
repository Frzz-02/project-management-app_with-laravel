# Sidebar Update untuk Role Member

## 📋 Overview
Update sidebar navigation untuk role **Member** (Developer/Designer) dengan menu yang relevan dan route yang tepat.

---

## ✨ Perubahan yang Dilakukan

### **File yang Dimodifikasi**
📄 `resources/views/layouts/app.blade.php`

### **Lokasi Perubahan**
1. **Desktop Sidebar**: Lines ~46-108 (Navigation Menu section)
2. **Mobile Sidebar**: Lines ~227-289 (Navigation Menu section)

---

## 🎯 Menu Sidebar untuk Member

### **Menu yang Ditampilkan**
Sidebar hanya muncul untuk user dengan role **Developer** atau **Designer** (member):

| No | Menu | Icon | Route | Deskripsi |
|----|------|------|-------|-----------|
| 1 | **Dashboard Saya** | 🏠 Home | `member.dashboard` | Dashboard utama member dengan stats, active tasks, deadlines |
| 2 | **Proyek Saya** | 📦 Projects | `projects.joined-projects` | Daftar proyek yang di-join sebagai member |
| 3 | **Riwayat Review** | ⏱️ Clock | `card-reviews.my-reviews` | History review yang diterima dari team leader |
| 4 | **Profil Saya** | 👤 User | `profile.edit` | Edit profil (nama, email, phone, bio, foto) |

### **Menu yang DIHILANGKAN**
Menu berikut TIDAK ditampilkan untuk member:
- ❌ **Projects** (general) - Diganti dengan "Proyek Saya"
- ❌ **Tasks** (general) - Sudah ada di Dashboard
- ❌ **Team** (general) - Member tidak perlu lihat semua team
- ❌ **Reports** - Hanya untuk Admin
- ❌ **Team Leader Dashboard** - Hanya untuk Team Lead

---

## 🔍 Logic & Implementasi

### **1. Deteksi Role Member**

**Desktop Sidebar**:
```php
@php
    $isMember = false;
    $currentRoute = request()->route()->getName();
    if(Auth::check()) {
        $isMember = DB::table('project_members')
            ->where('user_id', Auth::id())
            ->whereIn('role', ['developer', 'designer'])
            ->exists();
    }
@endphp
```

**Mobile Sidebar**:
```php
@php
    $isMobileMember = false;
    $currentMobileRoute = request()->route()->getName();
    if(Auth::check()) {
        $isMobileMember = DB::table('project_members')
            ->where('user_id', Auth::id())
            ->whereIn('role', ['developer', 'designer'])
            ->exists();
    }
@endphp
```

**Penjelasan**:
- Query database `project_members` untuk check role user
- Role yang valid: `developer` atau `designer`
- Menggunakan `DB::table()` untuk performa optimal
- Variable berbeda untuk desktop (`$isMember`) dan mobile (`$isMobileMember`)

---

### **2. Active State Highlighting**

**Active Menu Detection**:
```php
{{ str_starts_with($currentRoute, 'member.dashboard') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}
```

**Icon Color**:
```php
{{ str_starts_with($currentRoute, 'member.dashboard') ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500' }}
```

**Behavior**:
- Menu aktif: Background biru muda, teks biru
- Menu inactive: Gray, hover effect
- Icon ikut berubah warna sesuai state

---

### **3. Menu Structure**

**Desktop Sidebar** (Lines 46-108):
```blade
<nav class="flex-1 px-4 pb-4 space-y-1 mt-6">
    @if($isMember)
        {{-- 1. Dashboard Saya --}}
        <a href="{{ route('member.dashboard') }}" class="...">
            <svg>...</svg>
            Dashboard Saya
        </a>
        
        {{-- 2. Proyek Saya --}}
        <a href="{{ route('projects.joined-projects') }}" class="...">
            <svg>...</svg>
            Proyek Saya
        </a>
        
        {{-- 3. Riwayat Review --}}
        <a href="{{ route('card-reviews.my-reviews') }}" class="...">
            <svg>...</svg>
            Riwayat Review
        </a>

        {{-- 4. Profil Saya --}}
        <a href="{{ route('profile.edit') }}" class="...">
            <svg>...</svg>
            Profil Saya
        </a>
    @endif
</nav>
```

**Mobile Sidebar** (Lines 227-289):
- Struktur identik dengan desktop
- Menggunakan variable `$isMobileMember` dan `$currentMobileRoute`
- Responsive design dengan scroll support

---

## 🛣️ Route Mapping

### **Route Details**

| Menu | Route Name | Controller | Method | Middleware |
|------|-----------|------------|--------|------------|
| Dashboard Saya | `member.dashboard` | `MemberDashboardController` | `index` | `auth`, `member` |
| Proyek Saya | `projects.joined-projects` | `ProjectController` | `joinedProjects` | `auth` |
| Riwayat Review | `card-reviews.my-reviews` | `CardReviewController` | `myReviews` | `auth` |
| Profil Saya | `profile.edit` | `ProfileController` | `edit` | `auth` |

### **Route Definitions** (dari `routes/web.php`)

**1. Member Dashboard**:
```php
Route::middleware('member')->prefix('member')->name('member.')->group(function () {
    Route::get('/dashboard', [MemberDashboardController::class, 'index'])
        ->name('dashboard'); // member.dashboard
});
```

**2. Proyek Saya**:
```php
Route::middleware('auth')->group(function () {
    Route::get('/joined-projects', [ProjectController::class, 'joinedProjects'])
        ->name('projects.joined-projects');
});
```

**3. Riwayat Review**:
```php
Route::middleware('auth')->prefix('card-reviews')->name('card-reviews.')->group(function () {
    Route::get('/my-reviews', [CardReviewController::class, 'myReviews'])
        ->name('my-reviews'); // card-reviews.my-reviews
});
```

**4. Profil Saya**:
```php
Route::middleware('auth')->group(function () {
    Route::get('/profile/edit', [ProfileController::class, 'edit'])
        ->name('profile.edit');
});
```

---

## 🎨 UI/UX Design

### **Active State**
- **Background**: `bg-blue-50` (Light blue)
- **Text**: `text-blue-700` (Dark blue)
- **Icon**: `text-blue-500` (Medium blue)

### **Inactive State**
- **Background**: Transparent
- **Text**: `text-gray-600`
- **Icon**: `text-gray-400`

### **Hover State**
- **Background**: `hover:bg-gray-50` (Very light gray)
- **Text**: `hover:text-gray-900` (Almost black)
- **Icon**: `group-hover:text-gray-500` (Medium gray)

### **Transitions**
- All states use `transition-colors` for smooth animation
- Duration: Default (150ms)

---

## 📱 Responsive Behavior

### **Desktop (lg:)**
- Sidebar fixed di sebelah kiri
- Width: `w-64` (256px)
- Always visible
- No overlay

### **Mobile (< lg)**
- Sidebar sebagai slide-in panel
- Overlay background dengan blur
- Close button di header
- Touch-friendly spacing
- Scroll support untuk banyak menu

---

## ✅ Testing Checklist

### **1. Role Detection**
- [ ] Login sebagai Developer → Sidebar muncul dengan 4 menu
- [ ] Login sebagai Designer → Sidebar muncul dengan 4 menu
- [ ] Login sebagai Team Lead → Sidebar TIDAK muncul (member logic)
- [ ] Login sebagai Admin → Sidebar TIDAK muncul (member logic)

### **2. Navigation**
- [ ] Klik "Dashboard Saya" → Redirect ke `/member/dashboard`
- [ ] Klik "Proyek Saya" → Redirect ke `/joined-projects`
- [ ] Klik "Riwayat Review" → Redirect ke `/card-reviews/my-reviews`
- [ ] Klik "Profil Saya" → Redirect ke `/profile/edit`

### **3. Active State**
- [ ] Di `/member/dashboard` → "Dashboard Saya" highlighted (biru)
- [ ] Di `/joined-projects` → "Proyek Saya" highlighted
- [ ] Di `/card-reviews/my-reviews` → "Riwayat Review" highlighted
- [ ] Di `/profile/edit` → "Profil Saya" highlighted

### **4. Mobile**
- [ ] Hamburger button berfungsi (sidebar slide-in)
- [ ] Close button (X) menutup sidebar
- [ ] Click overlay menutup sidebar
- [ ] All 4 menu muncul dan berfungsi
- [ ] Smooth animation (slide & fade)

### **5. User Profile Section**
- [ ] Avatar menampilkan inisial nama (2 huruf)
- [ ] Nama lengkap tampil dengan benar
- [ ] Email tampil dengan benar
- [ ] Logout button berfungsi (redirect ke login)

---

## 🐛 Troubleshooting

### **Issue 1: Sidebar Kosong**
**Symptom**: Sidebar tidak menampilkan menu apapun

**Possible Causes**:
1. User belum di-assign ke project manapun
2. Role bukan `developer` atau `designer`
3. Database `project_members` kosong

**Solution**:
```sql
-- Check user role in project_members
SELECT * FROM project_members WHERE user_id = YOUR_USER_ID;

-- Assign user sebagai developer
INSERT INTO project_members (project_id, user_id, role, joined_at)
VALUES (1, YOUR_USER_ID, 'developer', NOW());
```

### **Issue 2: Menu Tidak Highlighted**
**Symptom**: Menu tidak berubah warna saat diklik

**Possible Causes**:
1. Route name tidak match dengan pattern
2. `str_starts_with()` pattern salah

**Solution**:
```php
// Check current route name
{{ request()->route()->getName() }}

// Adjust pattern di blade
{{ str_starts_with($currentRoute, 'member.') ? 'active' : 'inactive' }}
```

### **Issue 3: Mobile Sidebar Tidak Muncul**
**Symptom**: Hamburger button tidak membuka sidebar

**Possible Causes**:
1. Alpine.js belum load
2. Variable `sidebarOpen` tidak ter-define
3. `x-show` directive error

**Solution**:
```javascript
// Check Alpine.js di console
Alpine.version

// Check variable di Alpine DevTools
sidebarOpen
```

---

## 📚 Related Files

### **Controllers**
- `app/Http/Controllers/web/MemberDashboardController.php` - Dashboard member
- `app/Http/Controllers/web/ProjectController.php` - Joined projects
- `app/Http/Controllers/web/CardReviewController.php` - Review history
- `app/Http/Controllers/web/ProfileController.php` - Profile management

### **Views**
- `resources/views/member/dashboard.blade.php` - Member dashboard
- `resources/views/projects/joined.blade.php` - Joined projects list
- `resources/views/card-reviews/my-reviews.blade.php` - Review history
- `resources/views/profile/edit.blade.php` - Profile edit form

### **Middleware**
- `app/Http/Middleware/MemberMiddleware.php` - Validate member role

### **Routes**
- `routes/web.php` - All route definitions

---

## 🚀 Next Steps

### **To-Do: Sidebar untuk Role Lain**

**1. Team Leader Sidebar**:
- [ ] Dashboard Ketua Tim
- [ ] Proyek yang Dipimpin
- [ ] Review Tasks
- [ ] Tim Saya
- [ ] Laporan Tim

**2. Admin Sidebar**:
- [ ] Admin Dashboard
- [ ] Semua Proyek
- [ ] Manajemen User
- [ ] Laporan Sistem
- [ ] Pengaturan

**3. Unassigned Member Sidebar**:
- [ ] Welcome Dashboard
- [ ] Profil Saya
- [ ] FAQ / Help

---

## 📝 Code Snippets

### **Quick Copy: Desktop Sidebar**
```blade
<nav class="flex-1 px-4 pb-4 space-y-1 mt-6">
    @php
        $isMember = false;
        $currentRoute = request()->route()->getName();
        if(Auth::check()) {
            $isMember = DB::table('project_members')
                ->where('user_id', Auth::id())
                ->whereIn('role', ['developer', 'designer'])
                ->exists();
        }
    @endphp

    @if($isMember)
        <a href="{{ route('member.dashboard') }}" 
           class="{{ str_starts_with($currentRoute, 'member.dashboard') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors">
            <svg class="...">...</svg>
            Dashboard Saya
        </a>
        <!-- More menu items -->
    @endif
</nav>
```

### **Quick Copy: Mobile Sidebar**
```blade
<nav class="flex-1 px-4 pb-4 space-y-1 mt-6 overflow-y-auto">
    @php
        $isMobileMember = false;
        $currentMobileRoute = request()->route()->getName();
        if(Auth::check()) {
            $isMobileMember = DB::table('project_members')
                ->where('user_id', Auth::id())
                ->whereIn('role', ['developer', 'designer'])
                ->exists();
        }
    @endphp

    @if($isMobileMember)
        <!-- Same menu structure as desktop -->
    @endif
</nav>
```

---

## ✅ Completion Status

**Date**: November 16, 2025  
**Status**: ✅ **COMPLETED** untuk Role Member

**Implemented**:
- ✅ Desktop sidebar dengan 4 menu
- ✅ Mobile sidebar dengan responsive design
- ✅ Active state highlighting
- ✅ Route integration
- ✅ User profile section
- ✅ Logout functionality
- ✅ Hover effects & transitions
- ✅ Role detection logic

**Next**: Sidebar untuk Team Leader, Admin, dan Unassigned Member

---

## 🎉 Summary

Sidebar untuk **Member** (Developer/Designer) telah berhasil diimplementasikan dengan:
- 4 menu utama yang relevan
- Route yang tepat dan berfungsi
- UI/UX yang clean dan responsive
- Active state yang jelas
- Mobile-friendly dengan smooth animations

**Ready for Testing!** 🚀
