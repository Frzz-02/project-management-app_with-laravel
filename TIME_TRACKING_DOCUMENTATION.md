# Time Tracking Feature - Dokumentasi Lengkap

## 📋 Daftar Isi
1. [Overview](#overview)
2. [Struktur Database](#struktur-database)
3. [Controller Methods](#controller-methods)
4. [Routes](#routes)
5. [Model & Relationships](#model--relationships)
6. [View Components](#view-components)
7. [Alur Kerja](#alur-kerja)
8. [Contoh Penggunaan](#contoh-penggunaan)

---

## 🎯 Overview

Fitur **Time Tracking** memungkinkan user untuk mencatat waktu kerja pada **Card** atau **Subtask** dengan sistem seperti Jira.

### Fitur Utama:
- ✅ **Start/Stop Timer**: Mulai dan hentikan tracking waktu dengan satu klik
- ✅ **Real-time Timer**: Tampilan timer berjalan secara real-time dengan JavaScript
- ✅ **Duration Auto-Calculate**: Durasi dihitung otomatis berdasarkan start_time dan end_time
- ✅ **Description Support**: User bisa menambahkan deskripsi aktivitas
- ✅ **Card & Subtask Level**: Tracking bisa dilakukan di level card atau subtask spesifik
- ✅ **User Authorization**: Hanya user yang login yang bisa tracking waktu
- ✅ **History Logs**: Semua catatan waktu tersimpan dengan lengkap
- ✅ **Total Time Display**: Menampilkan total waktu yang sudah dilog

---

## 🗄️ Struktur Database

### Tabel: `time_logs`

| Field | Type | Constraint | Deskripsi |
|-------|------|------------|-----------|
| `id` | bigint | PRIMARY KEY | ID unik time log (auto increment) |
| `card_id` | bigint | FOREIGN KEY → cards.id | ID card yang di-track |
| `subtask_id` | bigint | NULLABLE, FOREIGN KEY → subtasks.id | ID subtask (opsional) |
| `user_id` | bigint | FOREIGN KEY → users.id | ID user yang melakukan tracking |
| `start_time` | datetime | NOT NULL | Waktu mulai tracking (timezone Asia/Jakarta) |
| `end_time` | datetime | NULLABLE | Waktu selesai tracking (null = masih berjalan) |
| `duration_minutes` | integer | DEFAULT 0 | Durasi dalam menit (auto-calculated) |
| `description` | text | NULLABLE | Deskripsi aktivitas yang dikerjakan |
| `created_at` | timestamp | AUTO | Waktu record dibuat |
| `updated_at` | timestamp | AUTO | Waktu record terakhir diupdate |

### Relasi:
- `time_logs.card_id` → `cards.id` (Many to One)
- `time_logs.subtask_id` → `subtasks.id` (Many to One)
- `time_logs.user_id` → `users.id` (Many to One)

---

## 🎮 Controller Methods

File: `app/Http/Controllers/web/TimeLogController.php`

### 1. **startTracking(Request $request)**

**Method**: POST  
**Route**: `/time-logs/start`  
**Purpose**: Memulai tracking waktu kerja

**Request Parameters**:
```php
[
    'card_id' => 'nullable|exists:cards,id',           // ID card (required jika subtask_id null)
    'subtask_id' => 'nullable|exists:subtasks,id',     // ID subtask (optional)
    'description' => 'nullable|string|max:1000'        // Deskripsi (optional)
]
```

**Validasi**:
- ✅ Minimal ada `card_id` ATAU `subtask_id`
- ✅ User tidak boleh punya timer yang sedang berjalan
- ✅ User harus member dari project

**Flow**:
1. Validasi input
2. Cek apakah user sudah punya timer ongoing → reject jika ada
3. Authorization check (user harus member project)
4. Buat record baru dengan `start_time = NOW()`, `end_time = NULL`
5. Redirect back dengan success message

**Return**: Redirect back dengan flash message

---

### 2. **stopTracking(Request $request, TimeLog $timeLog)**

**Method**: POST  
**Route**: `/time-logs/{timeLog}/stop`  
**Purpose**: Menghentikan timer dan menghitung durasi

**Request Parameters**:
```php
[
    'description' => 'nullable|string|max:1000'  // Update description (optional)
]
```

**Validasi**:
- ✅ Hanya owner (user_id) yang bisa stop timer miliknya
- ✅ Timer harus masih berjalan (`end_time = NULL`)
- ✅ `end_time` tidak boleh lebih awal dari `start_time`

**Flow**:
1. Authorization check (user_id match)
2. Validasi timer masih ongoing
3. Set `end_time = NOW()`
4. Hitung `duration_minutes = end_time - start_time` (dalam menit)
5. Update description jika ada
6. Redirect back dengan durasi formatted (e.g., "2 jam 30 menit")

**Return**: Redirect back dengan flash message + durasi

---

### 3. **update(Request $request, TimeLog $timeLog)**

**Method**: PUT  
**Route**: `/time-logs/{timeLog}`  
**Purpose**: Update description time log yang sudah selesai

**Request Parameters**:
```php
[
    'description' => 'nullable|string|max:1000'
]
```

**Validasi**:
- ✅ Hanya owner yang bisa update
- ✅ Time log harus sudah selesai (end_time NOT NULL)

**Flow**:
1. Authorization check
2. Validasi time log sudah completed
3. Update description
4. Redirect back dengan success message

**Return**: Redirect back dengan flash message

---

### 4. **destroy(TimeLog $timeLog)**

**Method**: DELETE  
**Route**: `/time-logs/{timeLog}`  
**Purpose**: Hapus time log

**Validasi**:
- ✅ Hanya owner yang bisa delete

**Flow**:
1. Authorization check
2. Delete record
3. Redirect back

**Return**: Redirect back dengan flash message

---

### 5. **getTotalTimeByCard($cardId)**

**Method**: GET  
**Route**: `/time-logs/card/{cardId}`  
**Purpose**: Hitung total waktu untuk satu card (API endpoint)

**Authorization**: User harus member project

**Return** (JSON):
```json
{
    "success": true,
    "total_minutes": 150,
    "total_hours": 2.5,
    "formatted": "2 jam 30 menit",
    "logs_count": 5
}
```

**Catatan**: Hanya menghitung time log yang sudah selesai (`end_time NOT NULL`)

---

### 6. **getTotalTimeBySubtask($subtaskId)**

**Method**: GET  
**Route**: `/time-logs/subtask/{subtaskId}`  
**Purpose**: Hitung total waktu untuk satu subtask (API endpoint)

**Authorization**: User harus member project

**Return**: Sama dengan `getTotalTimeByCard`

---

## 🛣️ Routes

File: `routes/web.php`

```php
use App\Http\Controllers\web\TimeLogController;

Route::middleware('auth')->group(function () {
    
    // Start tracking
    Route::post('time-logs/start', [TimeLogController::class, 'startTracking'])
        ->name('time-logs.start');
    
    // Stop tracking
    Route::post('time-logs/{timeLog}/stop', [TimeLogController::class, 'stopTracking'])
        ->name('time-logs.stop');
    
    // Update description
    Route::put('time-logs/{timeLog}', [TimeLogController::class, 'update'])
        ->name('time-logs.update');
    
    // Delete time log
    Route::delete('time-logs/{timeLog}', [TimeLogController::class, 'destroy'])
        ->name('time-logs.destroy');
    
    // Get total time (JSON API)
    Route::get('time-logs/card/{cardId}', [TimeLogController::class, 'getTotalTimeByCard'])
        ->name('time-logs.total-card');
    
    Route::get('time-logs/subtask/{subtaskId}', [TimeLogController::class, 'getTotalTimeBySubtask'])
        ->name('time-logs.total-subtask');
});
```

---

## 🔗 Model & Relationships

File: `app/Models/TimeLog.php`

### Relationships

```php
// TimeLog belongs to Card
public function card(): BelongsTo
{
    return $this->belongsTo(Card::class);
}

// TimeLog belongs to Subtask (optional)
public function subtask(): BelongsTo
{
    return $this->belongsTo(Subtask::class);
}

// TimeLog belongs to User
public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}
```

### Scopes

```php
// Time logs yang masih berjalan
TimeLog::ongoing()->get();  // WHERE end_time IS NULL

// Time logs yang sudah selesai
TimeLog::completed()->get();  // WHERE end_time IS NOT NULL

// Filter by date
TimeLog::onDate('2024-01-15')->get();

// Filter by user
TimeLog::byUser($userId)->get();
```

### Helper Methods

```php
// Cek apakah masih berjalan
$timeLog->isOngoing();  // return bool

// Stop timer dan hitung durasi
$timeLog->stop();  // return bool

// Get formatted duration
$timeLog->formatted_duration;  // "2:30" (jam:menit)

// Get duration in hours
$timeLog->duration_in_hours;  // 2.5
```

### Auto-Calculate Duration

Model menggunakan `boot()` method untuk auto-calculate `duration_minutes`:

```php
protected static function boot()
{
    parent::boot();
    
    static::saving(function ($timeLog) {
        if (is_null($timeLog->duration_minutes) && !is_null($timeLog->end_time)) {
            $timeLog->duration_minutes = Carbon::parse($timeLog->start_time)
                ->diffInMinutes(Carbon::parse($timeLog->end_time));
        }
    });
}
```

---

## 🎨 View Components

File: `resources/views/cards/show.blade.php`

### 1. **Time Tracking Section**

Section utama yang menampilkan:
- Header dengan total logged time
- Ongoing timer display (jika ada)
- Start timer form (jika tidak ada timer ongoing)
- History time logs

### 2. **Ongoing Timer Display**

Fitur:
- ✅ Real-time counter menggunakan Alpine.js `setInterval()`
- ✅ Animated pulse indicator (hijau)
- ✅ Informasi card/subtask yang sedang di-track
- ✅ Button Stop dengan optional description input
- ✅ Format display: `HH:MM:SS`

Alpine.js Code:
```javascript
x-data="{ 
    startTime: new Date('{{ $ongoingTimer->start_time->toIso8601String() }}'),
    elapsed: '00:00:00',
    updateTimer() {
        const now = new Date();
        const diff = Math.floor((now - this.startTime) / 1000);
        const hours = Math.floor(diff / 3600);
        const minutes = Math.floor((diff % 3600) / 60);
        const seconds = diff % 60;
        this.elapsed = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    }
}"
x-init="updateTimer(); setInterval(() => updateTimer(), 1000)"
```

### 3. **Start Timer Form**

Form collapsible dengan Alpine.js untuk:
- Pilih track untuk: Entire Card / Specific Subtask
- Select dropdown subtask (jika pilih subtask)
- Input description (optional)
- Button Start Tracking

### 4. **Time Logs History**

Menampilkan list semua time logs dengan:
- User name + badge "You" jika milik user login
- Subtask name (jika ada)
- Description
- Start time dan end time
- Formatted duration
- Actions: Edit description, Delete (hanya untuk owner)

### 5. **Edit Time Log Modal**

Modal untuk edit description time log yang sudah selesai.
Trigger: `@click="$dispatch('edit-time-log-modal', {...})"`

---

## 🔄 Alur Kerja

### **Scenario 1: Start Tracking pada Card**

```
1. User klik "Start Work"
2. Form expand (Alpine.js: showForm = true)
3. User pilih "Entire Card"
4. User input description (optional)
5. User klik "Start Tracking"
6. POST /time-logs/start dengan card_id
7. Controller validasi dan buat record baru
8. Redirect back dengan success message
9. View menampilkan ongoing timer dengan real-time counter
```

### **Scenario 2: Start Tracking pada Subtask**

```
1. User klik "Start Work"
2. Form expand
3. User pilih "Specific Subtask"
4. Dropdown subtask muncul (Alpine.js: forSubtask = true)
5. User pilih subtask dari dropdown
6. User input description (optional)
7. User klik "Start Tracking"
8. POST /time-logs/start dengan card_id + subtask_id
9. Controller validasi dan buat record baru
10. View menampilkan ongoing timer
```

### **Scenario 3: Stop Tracking**

```
1. User klik icon edit (optional) untuk tambah description
2. User klik "Stop" button
3. Confirm dialog muncul
4. POST /time-logs/{id}/stop
5. Controller:
   - Set end_time = NOW()
   - Hitung duration_minutes
   - Update description (jika ada)
6. Redirect back dengan message "Durasi: 2 jam 30 menit"
7. View update: timer hilang, history logs bertambah
```

### **Scenario 4: Edit Description**

```
1. User klik icon edit pada history log
2. Event dispatch: @edit-time-log-modal
3. Modal muncul dengan form
4. User edit description
5. User klik "Save Changes"
6. PUT /time-logs/{id}
7. Controller update description
8. Redirect back dengan success message
```

### **Scenario 5: Delete Time Log**

```
1. User klik icon delete pada history log
2. Confirm dialog muncul
3. DELETE /time-logs/{id}
4. Controller delete record
5. Redirect back dengan success message
6. View update: history log berkurang
```

---

## 💡 Contoh Penggunaan

### **1. Query Time Logs untuk Card**

```php
// Di Controller
$card = Card::findOrFail($cardId);
$timeLogs = $card->timeLogs()
    ->with(['user', 'subtask'])
    ->orderBy('created_at', 'desc')
    ->get();

// Total time (completed only)
$totalMinutes = $card->timeLogs()
    ->whereNotNull('end_time')
    ->sum('duration_minutes');
```

### **2. Get Ongoing Timer untuk User**

```php
$ongoingTimer = TimeLog::where('user_id', Auth::id())
    ->whereNull('end_time')
    ->with(['card', 'subtask'])
    ->first();

if ($ongoingTimer) {
    echo "Timer berjalan pada: " . $ongoingTimer->card->card_title;
}
```

### **3. Format Duration di Blade**

```blade
<!-- Formatted duration -->
{{ $timeLog->formatted_duration }}  <!-- Output: "2:30" -->

<!-- Duration in hours -->
{{ $timeLog->duration_in_hours }}h  <!-- Output: "2.5h" -->

<!-- Manual format -->
@php
    $hours = intval($timeLog->duration_minutes / 60);
    $minutes = $timeLog->duration_minutes % 60;
@endphp
{{ $hours }} jam {{ $minutes }} menit
```

### **4. Cek Permission**

```php
// Di Controller
$currentUser = Auth::user();
$project = $card->board->project;
$projectMember = $project->members->where('user_id', $currentUser->id)->first();

if (!$projectMember && $project->created_by !== $currentUser->id) {
    abort(403, 'Tidak ada akses');
}
```

### **5. JavaScript Fetch Total Time (AJAX)**

```javascript
// Fetch total time untuk card
fetch(`/time-logs/card/{{ $card->id }}`)
    .then(response => response.json())
    .then(data => {
        console.log('Total hours:', data.total_hours);
        console.log('Formatted:', data.formatted);
        console.log('Logs count:', data.logs_count);
    });
```

---

## 🎯 Best Practices

### **1. Timezone Consistency**
Selalu gunakan timezone `Asia/Jakarta`:
```php
Carbon::now('Asia/Jakarta');
```

### **2. Validation**
Selalu validasi di controller:
- User ownership check
- Timer status check
- End time >= start time

### **3. Authorization**
Gunakan pattern yang konsisten:
```php
if ($timeLog->user_id !== Auth::id()) {
    abort(403);
}
```

### **4. User Experience**
- Tampilkan live timer agar user tahu timer masih berjalan
- Berikan confirm dialog sebelum stop/delete
- Tampilkan durasi formatted yang mudah dibaca

### **5. Performance**
- Eager load relationships: `with(['user', 'subtask'])`
- Index pada `user_id`, `card_id`, `subtask_id`, `end_time`
- Limit history logs jika terlalu banyak (pagination)

---

## 📝 Notes Penting

1. **One Timer Per User**: User hanya bisa punya 1 timer yang berjalan pada satu waktu
2. **Auto-Calculate**: Duration dihitung otomatis via model boot event
3. **Timezone**: Semua datetime menggunakan timezone Asia/Jakarta
4. **Cascade Delete**: Ketika card/subtask dihapus, time logs juga ikut terhapus (pastikan ada cascade di migration)
5. **Authorization**: Hanya member project yang bisa tracking waktu
6. **Real-time Timer**: Menggunakan Alpine.js `setInterval()` untuk update setiap detik

---

## 🚀 Testing Checklist

- [ ] Start timer pada card
- [ ] Start timer pada subtask
- [ ] Stop timer dan cek durasi
- [ ] Edit description time log
- [ ] Delete time log
- [ ] Cek total time display
- [ ] Cek timer tidak bisa start jika sudah ada ongoing
- [ ] Cek authorization (non-member tidak bisa tracking)
- [ ] Cek real-time timer berjalan
- [ ] Cek format durasi sesuai

---

## 📚 Resources

- Laravel Documentation: https://laravel.com/docs
- Carbon Documentation: https://carbon.nesbot.com/docs/
- Alpine.js Documentation: https://alpinejs.dev/
- Tailwind CSS Documentation: https://tailwindcss.com/docs

---

**Created by**: GitHub Copilot  
**Date**: 2025  
**Laravel Version**: 12.x  
**PHP Version**: 8.3+
