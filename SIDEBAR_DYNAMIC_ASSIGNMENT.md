# Sidebar Dynamic Assignment Feature

## Overview
Fitur ini memungkinkan sidebar navigation menu **otomatis berubah** ketika member yang tadinya unassigned (belum direkrut) kemudian ditambahkan ke project oleh admin.

## Cara Kerja

### 1. Deteksi Status Member
Sistem mengecek status member saat setiap page load:

```php
// Check if user has any project assignment
$hasAssignment = DB::table('project_members')
    ->where('user_id', Auth::id())
    ->exists();

if ($hasAssignment) {
    // User is assigned to at least one project
    $isMember = DB::table('project_members')
        ->where('user_id', Auth::id())
        ->whereIn('role', ['developer', 'designer', 'team lead'])
        ->exists();
} else {
    // User has no project assignments
    $isUnassigned = true;
}
```

### 2. Kondisi Sidebar

#### **Unassigned Member (Belum Direkrut)**
Sidebar menampilkan menu terbatas:
- ✅ Dashboard (Unassigned)
- ✅ Profil Saya

**Screenshot Sidebar:**
```
┌─────────────────────┐
│  Dashboard          │
│  Profil Saya        │
└─────────────────────┘
```

#### **Assigned Member (Sudah Direkrut)**
Sidebar menampilkan menu lengkap:
- ✅ Dashboard Saya
- ✅ Proyek Saya
- ✅ Riwayat Review
- ✅ Profil Saya

**Screenshot Sidebar:**
```
┌─────────────────────────┐
│  🎉 Selamat!            │
│  Anda telah ditambahkan │
│  ke project             │
├─────────────────────────┤
│  Dashboard Saya   [NEW] │
│  Proyek Saya      [NEW] │
│  Riwayat Review         │
│  Profil Saya            │
└─────────────────────────┘
```

### 3. Badge "NEW" untuk Member Baru

Member yang baru direkrut dalam **7 hari terakhir** akan melihat:
- 🎉 **Info banner** di atas menu (warna hijau)
- 🟢 **Badge "NEW"** di menu Dashboard dan Proyek

```php
// Check jika baru direkrut dalam 7 hari terakhir
$latestAssignment = DB::table('project_members')
    ->where('user_id', Auth::id())
    ->orderBy('joined_at', 'desc')
    ->first();

if ($latestAssignment && $latestAssignment->joined_at) {
    $joinedDate = \Carbon\Carbon::parse($latestAssignment->joined_at);
    $isNewlyAssigned = $joinedDate->diffInDays(now()) <= 7;
}
```

## Workflow Lengkap

### Skenario: Member A Direkrut oleh Admin

1. **Initial State (Unassigned)**
   - Member A login → Sidebar hanya 2 menu
   - Redirect ke `unassigned.dashboard`

2. **Admin Action**
   - Admin membuka project
   - Admin tambahkan Member A ke project
   - Data tersimpan di tabel `project_members`

3. **Member A Refresh**
   - Member A refresh halaman (F5)
   - Sistem query ulang tabel `project_members`
   - Deteksi: `$hasAssignment = true`
   - Sidebar berubah otomatis jadi 4 menu

4. **Visual Indicator (7 hari)**
   - Info banner hijau muncul: "Selamat! Anda telah ditambahkan ke project"
   - Badge "NEW" di menu Dashboard dan Proyek
   - Setelah 7 hari → Badge hilang otomatis

## Database Schema

### Tabel: `project_members`
```sql
project_id      INT
user_id         INT
role            ENUM('team lead', 'developer', 'designer')
joined_at       TIMESTAMP  -- Untuk deteksi "newly assigned"
```

## File yang Terlibat

### 1. `resources/views/layouts/app.blade.php`
File utama untuk sidebar navigation (desktop & mobile).

**Key Sections:**
- **Lines 42-90**: Desktop sidebar logic & rendering
- **Lines 200-280**: Mobile sidebar logic & rendering

### 2. Routes
- `route('unassigned.dashboard')` → Dashboard untuk unassigned member
- `route('member.dashboard')` → Dashboard untuk assigned member
- `route('projects.my-active-project')` → Proyek member

## Testing

### Test Case 1: Unassigned → Assigned
```
1. Login sebagai member yang belum direkrut
2. Verify sidebar hanya 2 menu
3. Admin tambahkan member ke project
4. Member refresh halaman
5. Verify sidebar berubah jadi 4 menu
6. Verify badge "NEW" muncul
```

### Test Case 2: Badge Expiration
```
1. Login sebagai member yang baru direkrut
2. Verify badge "NEW" muncul
3. Ubah joined_at di database menjadi 8 hari lalu
4. Refresh halaman
5. Verify badge "NEW" hilang
```

### Test Case 3: Multiple Projects
```
1. Member A sudah di Project X (joined 10 hari lalu)
2. Admin tambahkan Member A ke Project Y (joined hari ini)
3. Member A refresh
4. Verify badge "NEW" muncul (karena joined_at terakhir < 7 hari)
```

## Performance Considerations

### Query Optimization
```php
// Query di-execute setiap page load
// Untuk optimasi, bisa di-cache:
$hasAssignment = Cache::remember('user_assignment_' . Auth::id(), 300, function () {
    return DB::table('project_members')
        ->where('user_id', Auth::id())
        ->exists();
});
```

### Recommendations
- ✅ Query sudah minimal (hanya check existence)
- ✅ Menggunakan database index pada `user_id`
- ⚠️ Bisa ditambahkan cache 5 menit untuk high-traffic
- ⚠️ Badge "NEW" bisa disimpan di session untuk performa lebih baik

## Limitations

1. **Tidak Realtime**
   - Member harus refresh manual untuk melihat perubahan
   - Alternatif: Gunakan WebSocket/Pusher untuk realtime update

2. **Badge Duration**
   - Badge "NEW" hilang setelah 7 hari
   - Bisa dikustomisasi di variable `$joinedDate->diffInDays(now()) <= 7`

3. **Multiple Projects**
   - Badge ditampilkan berdasarkan project terakhir yang diikuti
   - Jika member di-assign ke multiple projects, hanya satu badge yang muncul

## Customization

### Mengubah Durasi Badge
```php
// Dari 7 hari ke 14 hari
$isNewlyAssigned = $joinedDate->diffInDays(now()) <= 14;
```

### Mengubah Warna Badge
```blade
<!-- Hijau (default) -->
<span class="bg-green-100 text-green-800">NEW</span>

<!-- Biru -->
<span class="bg-blue-100 text-blue-800">NEW</span>

<!-- Ungu -->
<span class="bg-purple-100 text-purple-800">NEW</span>
```

### Menambahkan Notifikasi
```php
// Di ProjectMemberController saat assign member
Notification::create([
    'user_id' => $newMemberId,
    'type' => 'project_assignment',
    'title' => 'Anda ditambahkan ke project baru!',
    'message' => "Selamat! Anda telah ditambahkan ke project {$project->project_name}",
]);
```

## Troubleshooting

### Sidebar Tidak Berubah Setelah Refresh
1. **Check Database**
   ```sql
   SELECT * FROM project_members WHERE user_id = ?;
   ```
2. **Clear Cache**
   ```bash
   php artisan cache:clear
   php artisan view:clear
   ```
3. **Check Session**
   - Pastikan user sudah login
   - Cek `Auth::id()` return value yang benar

### Badge "NEW" Tidak Muncul
1. **Check joined_at Column**
   ```sql
   SELECT joined_at FROM project_members WHERE user_id = ? ORDER BY joined_at DESC LIMIT 1;
   ```
2. **Verify Calculation**
   ```php
   // Debug
   dd($latestAssignment, $joinedDate, $joinedDate->diffInDays(now()));
   ```

## Future Enhancements

### Realtime Update dengan Pusher
```javascript
// Listen for project assignment
Echo.private(`user.${userId}`)
    .listen('ProjectAssigned', (e) => {
        // Reload sidebar
        location.reload();
        // Or update DOM directly
    });
```

### Notification Integration
- Kirim email saat member di-assign
- Push notification browser
- In-app notification di navbar

### Analytics
- Track berapa lama member baru mulai aktif
- Metric: Time to First Task Completion

## Related Documentation
- [AUTHORIZATION_GUIDE.md](./AUTHORIZATION_GUIDE.md) - Role-based access
- [ONE_PROJECT_PER_USER_IMPLEMENTATION.md](./ONE_PROJECT_PER_USER_IMPLEMENTATION.md) - Business rules
- [SIDEBAR_MEMBER_UPDATE.md](./SIDEBAR_MEMBER_UPDATE.md) - Previous sidebar updates
