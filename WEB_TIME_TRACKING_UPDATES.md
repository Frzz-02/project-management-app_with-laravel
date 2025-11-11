# Web TimeLogController - Auto-Update Features

## 📝 Ringkasan Perubahan

Logika dari API TimeLogController untuk auto-update `card_assignments` dan `actual_hours` telah diterapkan ke Web TimeLogController.

---

## ✅ Fitur yang Ditambahkan

### 1. **startTracking() Method**

Ketika user **pertama kali** memulai time tracking:

#### Auto-Update Features:
- ✅ **Status Card/Subtask** → "in progress"
  - Jika hanya `card_id` (no subtask) → update card status
  - Jika `card_id` + `subtask_id` → update subtask status saja
  
- ✅ **Card Assignment** (jika ada):
  - `started_at` → set ke waktu sekarang (hanya jika NULL)
  - `assignment_status` → "in progress"

#### Logika:
```php
// 1. Update status ke "in progress"
if (!empty($validatedData['subtask_id'])) {
    // Update subtask status
    $subtask->update(['status' => 'in progress']);
} elseif (!empty($validatedData['card_id'])) {
    // Update card status
    $card->update(['status' => 'in progress']);
}

// 2. Update card_assignments (hanya jika pertama kali)
$cardAssignment = CardAssignment::where('card_id', $card_id)
    ->where('user_id', $user_id)
    ->first();

if ($cardAssignment && is_null($cardAssignment->started_at)) {
    $cardAssignment->update([
        'started_at' => now(),
        'assignment_status' => 'in progress'
    ]);
}
```

---

### 2. **stopTracking() Method**

Ketika user menghentikan time tracking:

#### Auto-Update Features:
- ✅ **Time Log**:
  - `end_time` → waktu sekarang
  - `duration_minutes` → calculate (end_time - start_time)

- ✅ **Card**:
  - `actual_hours` → SUM semua time logs yang selesai / 60

- ✅ **Card Assignment**:
  - `completed_at` → waktu sekarang
  - `assignment_status` → "completed"

- ⚠️ **Status card/subtask TETAP "in progress"** (tidak auto-update)

#### Logika:
```php
// 1. Update time log
$timeLog->update([
    'end_time' => now(),
    'duration_minutes' => $calculated_duration
]);

// 2. Update actual_hours di card
$totalMinutes = TimeLog::where('card_id', $card_id)
    ->whereNotNull('end_time')
    ->sum('duration_minutes');

$card->update([
    'actual_hours' => round($totalMinutes / 60, 2)
]);

// 3. Update card_assignments
$cardAssignment->update([
    'completed_at' => now(),
    'assignment_status' => 'completed'
]);
```

---

## 📊 Database Changes Overview

### On START Tracking (First Time):

| Table | Field | Action | Condition |
|-------|-------|--------|-----------|
| `cards` | `status` | → "in progress" | If card-only (no subtask) |
| `subtasks` | `status` | → "in progress" | If has subtask |
| `card_assignments` | `started_at` | Set to NOW | **Only if NULL** |
| `card_assignments` | `assignment_status` | → "in progress" | When started_at updated |

### On STOP Tracking:

| Table | Field | Action | Value |
|-------|-------|--------|-------|
| `time_logs` | `end_time` | Set to NOW | Current timestamp |
| `time_logs` | `duration_minutes` | Calculate | end_time - start_time |
| `cards` | `actual_hours` | Update | SUM(all time_logs) / 60 |
| `card_assignments` | `completed_at` | Set to NOW | Current timestamp |
| `card_assignments` | `assignment_status` | → "completed" | Always |

---

## 🔄 Complete Flow Example

### Scenario: User mengerjakan card "Login Feature"

#### 1️⃣ **First Start** (Pertama kali mulai)

**Action:** User klik tombol "Start Timer" di web app

**Request:**
```php
POST /time-logs/start
{
    "card_id": 5,
    "description": "Working on login feature"
}
```

**Database Changes:**
```sql
-- time_logs
INSERT INTO time_logs 
VALUES (start_time = '2024-11-07 10:00:00', end_time = NULL);

-- cards
UPDATE cards 
SET status = 'in progress' 
WHERE id = 5;

-- card_assignments
UPDATE card_assignments 
SET started_at = '2024-11-07 10:00:00',
    assignment_status = 'in progress'
WHERE card_id = 5 AND user_id = 1 AND started_at IS NULL;
```

**Response:**
```
✅ Redirect back with message: "Time tracking dimulai!"
```

---

#### 2️⃣ **First Stop**

**Action:** User klik tombol "Stop Timer"

**Request:**
```php
POST /time-logs/{id}/stop
{
    "description": "Completed login authentication"
}
```

**Database Changes:**
```sql
-- time_logs
UPDATE time_logs 
SET end_time = '2024-11-07 12:30:00',
    duration_minutes = 150
WHERE id = 1;

-- cards (calculate total from all time logs)
UPDATE cards 
SET actual_hours = 2.5  -- 150 minutes / 60
WHERE id = 5;

-- card_assignments
UPDATE card_assignments 
SET completed_at = '2024-11-07 12:30:00',
    assignment_status = 'completed'
WHERE card_id = 5 AND user_id = 1;
```

**Response:**
```
✅ Redirect back with message: "Time tracking dihentikan! Durasi: 2 jam 30 menit"
```

---

#### 3️⃣ **Second Start** (Mulai lagi setelah stop)

**Action:** User mulai tracking lagi untuk card yang sama

**Request:**
```php
POST /time-logs/start
{
    "card_id": 5,
    "description": "Continue working"
}
```

**Database Changes:**
```sql
-- time_logs
INSERT INTO time_logs 
VALUES (start_time = '2024-11-07 14:00:00', end_time = NULL);

-- cards
-- Status already "in progress", no change

-- card_assignments
-- ⚠️ started_at NOT NULL anymore, so NO UPDATE
-- started_at tetap '2024-11-07 10:00:00' (first time)
```

**Response:**
```
✅ Redirect back with message: "Time tracking dimulai!"
```

---

#### 4️⃣ **Second Stop**

**Action:** Stop tracking untuk kedua kalinya

**Database Changes:**
```sql
-- time_logs
UPDATE time_logs 
SET end_time = '2024-11-07 15:45:00',
    duration_minutes = 105
WHERE id = 2;

-- cards (SUM dari 2 time logs)
UPDATE cards 
SET actual_hours = 4.25  -- (150 + 105) / 60
WHERE id = 5;

-- card_assignments
UPDATE card_assignments 
SET completed_at = '2024-11-07 15:45:00',  -- Updated to latest
    assignment_status = 'completed'
WHERE card_id = 5 AND user_id = 1;
```

**Response:**
```
✅ Redirect back with message: "Time tracking dihentikan! Durasi: 1 jam 45 menit"
```

---

## ⚠️ Important Notes

### 1. **started_at Behavior**
- `started_at` hanya di-set **SEKALI** (pertama kali start)
- Tidak di-overwrite pada start kedua/ketiga
- Berguna untuk track "kapan pertama kali mulai mengerjakan"

### 2. **completed_at Behavior**
- `completed_at` **SELALU** di-update setiap stop tracking
- Berguna untuk track "kapan terakhir kali selesai work session"

### 3. **actual_hours is Cumulative**
- `actual_hours` = **TOTAL** dari semua time logs (completed)
- Bertambah setiap kali stop tracking
- Formula: `SUM(duration_minutes) / 60`

### 4. **Status NOT Auto-Updated on Stop**
- Status tetap "in progress" setelah stop
- User harus manual ubah status jika task selesai
- Untuk ubah status, gunakan fitur edit card/subtask

### 5. **No Error if Assignment Doesn't Exist**
- Jika tidak ada `card_assignments`, tidak error
- Hanya time tracking yang jalan
- Features lain tetap berfungsi normal

---

## 🧪 Testing Checklist

### Web Application Testing:

#### Test 1: First Time Start
- [ ] Klik "Start Timer" pada card
- [ ] Cek `time_logs` → start_time ter-set
- [ ] Cek `cards` → status jadi "in progress"
- [ ] Cek `card_assignments` → started_at ter-set (jika ada)
- [ ] Cek `card_assignments` → assignment_status jadi "in progress"

#### Test 2: Stop Timer
- [ ] Klik "Stop Timer"
- [ ] Cek `time_logs` → end_time dan duration_minutes ter-set
- [ ] Cek `cards` → actual_hours ter-update
- [ ] Cek `card_assignments` → completed_at ter-set
- [ ] Cek `card_assignments` → assignment_status jadi "completed"
- [ ] Lihat flash message dengan durasi

#### Test 3: Start Again (Second Time)
- [ ] Klik "Start Timer" lagi pada card yang sama
- [ ] Cek `time_logs` → new record created
- [ ] Cek `card_assignments` → started_at **TIDAK berubah**
- [ ] Cek `card_assignments` → assignment_status tetap "completed" atau "in progress"

#### Test 4: Stop Again (Second Time)
- [ ] Klik "Stop Timer" untuk kedua kali
- [ ] Cek `cards` → actual_hours bertambah (sum dari 2 time logs)
- [ ] Cek `card_assignments` → completed_at ter-update ke waktu terbaru

#### Test 5: Card Without Assignment
- [ ] Start timer pada card yang tidak punya card_assignment
- [ ] Pastikan tidak error
- [ ] Time tracking tetap jalan normal

#### Test 6: Subtask Tracking
- [ ] Start timer pada subtask
- [ ] Cek `subtasks` → status jadi "in progress"
- [ ] Cek `cards` → status **TIDAK** berubah
- [ ] Stop timer dan cek actual_hours ter-update

---

## 🎯 Key Differences: API vs Web

| Feature | API Controller | Web Controller |
|---------|---------------|----------------|
| **Response** | JSON | Redirect with flash message |
| **Error Handling** | JSON with status code | Redirect with error message |
| **Success Message** | JSON response | Flash session message |
| **Logic** | ✅ Same | ✅ Same |
| **Auto-Updates** | ✅ Same | ✅ Same |

---

## 📚 Related Files

- **API Controller**: `app/Http/Controllers/api/TimeLogController.php`
- **Web Controller**: `app/Http/Controllers/web/TimeLogController.php`
- **Model**: `app/Models/TimeLog.php`
- **Model**: `app/Models/CardAssignment.php`
- **Migration**: `database/migrations/2025_09_03_011702_create_card_assignments_table.php`

---

## 🤝 Support

Jika ada pertanyaan tentang implementasi ini, silakan:
1. Cek file `API_TIME_TRACKING_UPDATES.md` untuk dokumentasi lengkap API
2. Test semua scenario di checklist
3. Gunakan `Log::info()` untuk debugging jika perlu

---

**Status**: ✅ **Completed - Ready for Testing**

Semua logika dari API TimeLogController sudah berhasil diterapkan ke Web TimeLogController dengan format response yang disesuaikan untuk web (redirect + flash message).
