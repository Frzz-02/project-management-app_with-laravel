# Database Migration & File Storage - Complete Guide

## ✅ Migration Status: FIXED & COMPLETED

Semua migration sudah berhasil dijalankan! Database sudah siap.

---

## 📊 Database Structure

### Users Table - Final Structure

```sql
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL UNIQUE,
  `full_name` varchar(255) DEFAULT NULL,
  `current_task_status` varchar(50) DEFAULT NULL,
  `email` varchar(255) NOT NULL UNIQUE,
  `phone` varchar(20) DEFAULT NULL,              -- ✅ ADDED
  `role` varchar(50) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,   -- ✅ ADDED (stores PATH only)
  `bio` text DEFAULT NULL,                       -- ✅ ADDED
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
);
```

### ⚠️ IMPORTANT: Profile Picture Column

**Column Type**: `VARCHAR(255)` (NOT BLOB/LONGTEXT)  
**Stores**: File path only (e.g., `profile_pictures/abc123.jpg`)  
**Does NOT Store**: Image binary data

---

## 🗂️ File Storage System

### Storage Architecture

```
project-management/
├── storage/
│   ├── app/
│   │   └── public/              ← Actual file storage
│   │       └── profile_pictures/  ← Profile images stored here
│   │           ├── abc123.jpg
│   │           ├── def456.png
│   │           └── ...
│   └── logs/
└── public/
    └── storage/                 ← Symbolic link to storage/app/public
        └── profile_pictures/      (accessible via URL)
```

### How It Works

1. **Upload**: User uploads image via `/profile/edit`
2. **Storage**: File saved to `storage/app/public/profile_pictures/`
3. **Database**: Only the **path** saved to DB:
   ```php
   // Example database value:
   profile_picture = "profile_pictures/rX8mK3nP2qA1bC5dE7fG.jpg"
   ```
4. **Access**: File accessible via URL through symbolic link:
   ```
   http://localhost:8000/storage/profile_pictures/rX8mK3nP2qA1bC5dE7fG.jpg
   ```

---

## 🔧 Migration Details

### Migration File
**File**: `database/migrations/2025_11_16_010257_add_phone_and_bio_to_users_table.php`

**What It Does**:
- ✅ Checks if `profile_picture` column exists, adds if not
- ✅ Checks if `bio` column exists, adds if not
- ✅ Skips `phone` (already exists)
- ✅ Safe to run multiple times (idempotent)

**Code**:
```php
public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        if (!Schema::hasColumn('users', 'profile_picture')) {
            $table->string('profile_picture')->nullable()->after('password');
        }
        if (!Schema::hasColumn('users', 'bio')) {
            $table->text('bio')->nullable()->after('profile_picture');
        }
    });
}
```

### Migration Status

```bash
✅ 2025_11_12_144427_create_notifications_table ............... Ran
✅ 2025_11_16_010257_add_phone_and_bio_to_users_table ......... Ran
```

**All migrations completed successfully!**

---

## 💾 Database vs Storage: What Goes Where?

### Stored in DATABASE (users table)

| Column | Type | Example | Purpose |
|--------|------|---------|---------|
| `id` | BIGINT | `8` | User ID |
| `username` | VARCHAR | `john_doe` | Login identifier |
| `email` | VARCHAR | `john@example.com` | Contact/login |
| `full_name` | VARCHAR | `John Doe` | Display name |
| `phone` | VARCHAR | `+62 812-3456-7890` | Contact number |
| `bio` | TEXT | `I'm a developer...` | User description |
| **`profile_picture`** | **VARCHAR** | **`profile_pictures/abc.jpg`** | **Path to file** ✅ |

### Stored in FILESYSTEM (storage/app/public/)

| File | Location | Size |
|------|----------|------|
| `abc123.jpg` | `storage/app/public/profile_pictures/` | ~500 KB |
| `def456.png` | `storage/app/public/profile_pictures/` | ~1.2 MB |
| `ghi789.gif` | `storage/app/public/profile_pictures/` | ~800 KB |

---

## 📝 ProfileController Implementation

### Upload Logic (Correct Implementation ✅)

```php
// Handle profile picture upload
if ($request->hasFile('profile_picture')) {
    // 1. Delete old file from storage
    if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
        Storage::disk('public')->delete($user->profile_picture);
    }

    // 2. Store new file in storage/app/public/profile_pictures/
    $path = $request->file('profile_picture')->store('profile_pictures', 'public');
    
    // 3. Save ONLY the path to database (NOT the file content!)
    $user->profile_picture = $path;  // e.g., "profile_pictures/abc123.jpg"
}

// 4. Save user record
$user->save();
```

### What Gets Saved to Database

```php
// CORRECT ✅
$user->profile_picture = "profile_pictures/rX8mK3nP2qA1bC5dE7fG.jpg";

// WRONG ❌ (Don't do this!)
$user->profile_picture = file_get_contents($file);  // Binary data
$user->profile_picture = base64_encode($file);      // Encoded binary
```

### Display Image in View

```blade
{{-- CORRECT ✅ --}}
@if($user->profile_picture)
    <img src="{{ asset('storage/' . $user->profile_picture) }}" 
         alt="Profile">
    {{-- Results in: /storage/profile_pictures/abc123.jpg --}}
@endif

{{-- WRONG ❌ --}}
<img src="data:image/jpeg;base64,{{ $user->profile_picture }}">
```

---

## 🔍 Verification Commands

### Check Table Structure
```bash
php artisan tinker
Schema::getColumnListing('users')
# Should show: [..., 'phone', 'profile_picture', 'bio', ...]
```

### Check Column Type
```bash
php artisan tinker
DB::select("DESCRIBE users WHERE Field = 'profile_picture'")
# Type should be: varchar(255)
```

### Check Storage Directory
```bash
# PowerShell
Get-ChildItem storage\app\public\profile_pictures

# Bash
ls -la storage/app/public/profile_pictures/
```

### Check Symbolic Link
```bash
# PowerShell
Get-Item public\storage | Select-Object Target

# Should point to: ..\storage\app\public
```

### Test Upload
```bash
# 1. Go to http://localhost:8000/profile/edit
# 2. Upload an image
# 3. Check file was created:
Get-ChildItem storage\app\public\profile_pictures

# 4. Check database only has path:
php artisan tinker
User::find(8)->profile_picture
# Should return: "profile_pictures/abc123.jpg" (NOT binary data)
```

---

## ⚡ Performance Benefits

### Using Filesystem Storage (Our Implementation ✅)

**Pros**:
- ✅ **Fast**: No database overhead for large files
- ✅ **Scalable**: Can use CDN, separate file server
- ✅ **Efficient**: Database stays small and fast
- ✅ **Cacheable**: Files can be cached by browser/CDN
- ✅ **Maintainable**: Easy to backup, migrate, or delete files

**Example**:
```
Database size: 50 MB (just paths)
Storage size: 5 GB (actual images)
Query time: 10ms (fast, no large blobs)
Image serve: Direct file read (fast)
```

### Using Database Storage (NOT Our Implementation ❌)

**Cons**:
- ❌ **Slow**: Large database, slow queries
- ❌ **Bloated**: Database grows huge
- ❌ **Inefficient**: Must read through PHP/SQL
- ❌ **Not cacheable**: Hard to cache binary data
- ❌ **Difficult**: Backup, migration, scaling issues

**Example**:
```
Database size: 5 GB (includes images)
Storage size: 0 MB
Query time: 500ms+ (slow, reading blobs)
Image serve: Through PHP (slow, memory-intensive)
```

---

## 🛠️ Maintenance Commands

### Clear Old Orphaned Files
```php
// Find files not in database
$dbPaths = User::whereNotNull('profile_picture')
    ->pluck('profile_picture')
    ->toArray();

$allFiles = Storage::disk('public')->files('profile_pictures');

foreach ($allFiles as $file) {
    if (!in_array($file, $dbPaths)) {
        Storage::disk('public')->delete($file);
        echo "Deleted orphaned file: $file\n";
    }
}
```

### Backup Profile Pictures
```bash
# PowerShell
Compress-Archive -Path storage\app\public\profile_pictures -DestinationPath backups\profiles_$(Get-Date -Format 'yyyyMMdd').zip

# Bash
tar -czf backups/profiles_$(date +%Y%m%d).tar.gz storage/app/public/profile_pictures/
```

### Migrate Files to New Server
```bash
# Copy storage directory
rsync -av storage/app/public/profile_pictures/ user@newserver:/path/to/storage/app/public/profile_pictures/

# Or using ZIP
# On old server:
zip -r profile_pictures.zip storage/app/public/profile_pictures/

# On new server:
unzip profile_pictures.zip -d /path/to/
```

---

## 🚨 Common Issues & Solutions

### Issue 1: Image not displaying after upload
**Symptom**: Image uploaded but shows broken image icon

**Cause**: Symbolic link missing

**Solution**:
```bash
php artisan storage:link
```

**Verify**:
```bash
# Should exist: public/storage → ../storage/app/public
Get-Item public\storage
```

---

### Issue 2: Permission denied when uploading
**Symptom**: "Failed to store file" error

**Cause**: Storage directory not writable

**Solution**:
```bash
# Windows (PowerShell as Admin)
icacls storage\app\public /grant Users:F /T

# Linux/Mac
chmod -R 775 storage/app/public
chown -R www-data:www-data storage/
```

---

### Issue 3: File upload fails silently
**Symptom**: No error, but file not saved

**Cause**: PHP upload size limits

**Solution**: Check `php.ini`:
```ini
upload_max_filesize = 2M
post_max_size = 8M
max_execution_time = 60
```

**Verify**:
```bash
php -i | grep upload_max_filesize
php -i | grep post_max_size
```

---

### Issue 4: Old images not deleted
**Symptom**: Storage fills with old profile pictures

**Cause**: Delete logic not working

**Debug**:
```php
// In ProfileController
\Log::info('Deleting old file: ' . $user->profile_picture);

if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
    Storage::disk('public')->delete($user->profile_picture);
    \Log::info('File deleted successfully');
} else {
    \Log::warning('File not found or path empty');
}
```

---

### Issue 5: Database shows binary data
**Symptom**: profile_picture column contains gibberish

**Cause**: Someone stored file content instead of path

**Fix**:
```sql
-- Clear all binary data
UPDATE users 
SET profile_picture = NULL 
WHERE profile_picture LIKE '%\xFF\xD8%';  -- JPEG signature
```

**Then**: Re-upload images properly through the form

---

## 📊 Storage Statistics

### Check Storage Usage
```php
// Total size of profile pictures
$totalSize = 0;
$files = Storage::disk('public')->allFiles('profile_pictures');

foreach ($files as $file) {
    $totalSize += Storage::disk('public')->size($file);
}

echo "Total storage: " . round($totalSize / 1024 / 1024, 2) . " MB\n";
echo "Total files: " . count($files) . "\n";
echo "Average file size: " . round($totalSize / count($files) / 1024, 2) . " KB\n";
```

### Database Size Check
```sql
SELECT 
    COUNT(*) as total_users,
    COUNT(profile_picture) as users_with_picture,
    AVG(LENGTH(profile_picture)) as avg_path_length
FROM users;
```

**Expected Results**:
```
total_users: 10
users_with_picture: 7
avg_path_length: 45-50 characters  (just the path, NOT megabytes!)
```

---

## ✅ Final Verification Checklist

- [x] Migration ran successfully
- [x] `profile_picture` column type is VARCHAR (not BLOB)
- [x] `bio` column added to users table
- [x] `phone` column exists in users table
- [x] Storage directory exists: `storage/app/public/profile_pictures/`
- [x] Symbolic link exists: `public/storage → storage/app/public`
- [x] Controller saves PATH only to database
- [x] Controller deletes old files before uploading new
- [x] View uses `asset('storage/' . $path)` to display images
- [x] File upload limit is 2MB
- [x] Allowed formats: JPEG, PNG, JPG, GIF

---

## 🎯 Summary

### What We Did Right ✅

1. **Database Structure**:
   - ✅ `profile_picture` is VARCHAR (stores path only)
   - ✅ `bio` is TEXT (for long descriptions)
   - ✅ `phone` is VARCHAR (for contact)

2. **Storage System**:
   - ✅ Files stored in `storage/app/public/profile_pictures/`
   - ✅ Database stores only file paths
   - ✅ Symbolic link created for public access
   - ✅ Old files deleted when uploading new

3. **Controller Logic**:
   - ✅ Validates file type and size
   - ✅ Stores file to filesystem using `store()`
   - ✅ Saves only path to database
   - ✅ Deletes old file before new upload
   - ✅ Handles errors gracefully

4. **Security**:
   - ✅ File type validation (images only)
   - ✅ File size limit (2MB)
   - ✅ Stored outside web root (storage/app/public)
   - ✅ Access controlled via symbolic link

---

**Migration COMPLETE! Storage system correctly implemented!** ✅

Database hanya menyimpan **path** ke gambar, bukan gambar itu sendiri. Gambar disimpan di folder `storage/app/public/profile_pictures/`.

**Test sekarang**:
```bash
# 1. Go to http://localhost:8000/profile/edit
# 2. Upload gambar
# 3. Check database: hanya path yang disimpan
# 4. Check storage: file gambar ada disana
```
