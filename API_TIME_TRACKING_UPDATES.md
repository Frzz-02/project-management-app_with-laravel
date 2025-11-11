# Time Tracking API - Auto-Update Features

## 📝 Overview

Time Tracking API sekarang memiliki fitur auto-update untuk mengelola `card_assignments` table dan `actual_hours` di cards.

---

## ✨ New Features

### 1. **Start Tracking** - Auto-Update

Ketika user **pertama kali** memulai time tracking pada sebuah card:

#### What Gets Updated:

| Table | Field | Action | Condition |
|-------|-------|--------|-----------|
| `cards` | `status` | → "in progress" | Jika hanya card_id (no subtask) |
| `subtasks` | `status` | → "in progress" | Jika ada subtask_id |
| `card_assignments` | `started_at` | Set to current time | **Hanya jika NULL** (first time) |
| `card_assignments` | `assignment_status` | → "in progress" | When started_at is updated |

#### Logic Flow:

```
1. User clicks "Start Timer" di Flutter app
   ↓
2. POST /api/v1/time-logs/start dengan card_id
   ↓
3. Check: Apakah user sudah pernah start card ini?
   ├─ NO (started_at = NULL)
   │  └─ Update started_at = now()
   │  └─ Update assignment_status = "in progress"
   │
   └─ YES (started_at already set)
      └─ Skip update (tidak overwrite started_at)
```

---

### 2. **Stop Tracking** - Auto-Update

Ketika user menghentikan time tracking:

#### What Gets Updated:

| Table | Field | Action | Notes |
|-------|-------|--------|-------|
| `time_logs` | `end_time` | Set to current time | - |
| `time_logs` | `duration_minutes` | Calculate (end - start) | In minutes |
| `cards` | `actual_hours` | Sum all completed time logs | Total dari SEMUA time logs (hours) |
| `card_assignments` | `completed_at` | Set to current time | Always updated |
| `card_assignments` | `assignment_status` | → "completed" | Always updated |

#### Logic Flow:

```
1. User clicks "Stop Timer" di Flutter app
   ↓
2. POST /api/v1/time-logs/{id}/stop
   ↓
3. Calculate duration (end_time - start_time)
   ↓
4. Update time_logs:
   - end_time = now()
   - duration_minutes = calculated
   ↓
5. Update cards:
   - actual_hours = SUM(all time_logs.duration_minutes) / 60
   ↓
6. Update card_assignments:
   - completed_at = now()
   - assignment_status = "completed"
```

---

## 📊 Database Schema Reference

### `card_assignments` Table

```sql
CREATE TABLE card_assignments (
    id BIGINT PRIMARY KEY,
    card_id BIGINT,                -- FK to cards
    user_id BIGINT,                -- FK to users
    assigned_at TIMESTAMP,         -- Waktu di-assign
    assignment_status ENUM,        -- 'assigned', 'in progress', 'completed'
    started_at TIMESTAMP NULL,     -- ✨ Updated on FIRST start tracking
    completed_at TIMESTAMP NULL    -- ✨ Updated on stop tracking
);
```

### `cards` Table

```sql
CREATE TABLE cards (
    id BIGINT PRIMARY KEY,
    board_id BIGINT,
    card_title VARCHAR,
    status ENUM,                   -- 'todo', 'in progress', 'review', 'done'
    estimated_hours DECIMAL,       -- Estimasi jam
    actual_hours DECIMAL,          -- ✨ Updated on stop tracking (total hours)
    -- ... other fields
);
```

---

## 🔄 Complete Example Flow

### Scenario: User mengerjakan card "Login Feature"

#### 1️⃣ **First Time Start** (started_at = NULL)

**Request:**
```bash
POST /api/v1/time-logs/start
{
  "card_id": 5,
  "description": "Starting login feature"
}
```

**Database Changes:**
```sql
-- time_logs
INSERT: start_time = '2024-11-05 10:00:00'

-- card_assignments
UPDATE: started_at = '2024-11-05 10:00:00'
UPDATE: assignment_status = 'in progress'

-- cards
UPDATE: status = 'in progress'
```

---

#### 2️⃣ **First Time Stop**

**Request:**
```bash
POST /api/v1/time-logs/15/stop
{
  "description": "Completed login authentication"
}
```

**Database Changes:**
```sql
-- time_logs
UPDATE: end_time = '2024-11-05 12:30:00'
UPDATE: duration_minutes = 150  -- 2.5 hours

-- cards
UPDATE: actual_hours = 2.5  -- 150 minutes / 60

-- card_assignments
UPDATE: completed_at = '2024-11-05 12:30:00'
UPDATE: assignment_status = 'completed'
```

---

#### 3️⃣ **Second Time Start** (started_at already set)

**Request:**
```bash
POST /api/v1/time-logs/start
{
  "card_id": 5,
  "description": "Continue working on login"
}
```

**Database Changes:**
```sql
-- time_logs
INSERT: start_time = '2024-11-05 14:00:00'

-- card_assignments
-- ⚠️ NO UPDATE to started_at (already set from first time)
-- started_at TETAP '2024-11-05 10:00:00'

-- cards
-- status already "in progress", no change needed
```

---

#### 4️⃣ **Second Time Stop**

**Request:**
```bash
POST /api/v1/time-logs/18/stop
```

**Database Changes:**
```sql
-- time_logs
UPDATE: end_time = '2024-11-05 15:45:00'
UPDATE: duration_minutes = 105  -- 1.75 hours

-- cards
UPDATE: actual_hours = 4.25  -- (150 + 105) / 60 = total dari 2 time logs

-- card_assignments
UPDATE: completed_at = '2024-11-05 15:45:00'  -- Updated to latest
UPDATE: assignment_status = 'completed'
```

---

## ⚠️ Important Notes

### 1. **started_at Only Set Once**
- `started_at` hanya di-set pada **pertama kali** user mulai tracking
- Jika user start-stop-start lagi, `started_at` **TIDAK** di-overwrite
- Ini berguna untuk tracking "kapan pertama kali mulai mengerjakan task"

### 2. **completed_at Always Updated**
- `completed_at` **selalu** di-update setiap kali stop tracking
- Ini berguna untuk tracking "kapan terakhir kali selesai work session"

### 3. **actual_hours is Cumulative**
- `actual_hours` adalah **total** dari semua time logs yang sudah selesai
- Formula: `SUM(duration_minutes) / 60`
- Setiap kali stop tracking, nilai ini akan bertambah

### 4. **Status NOT Auto-Updated on Stop**
- Ketika stop tracking, status card/subtask **TETAP "in progress"**
- User harus manual ubah status via endpoint lain:
  - `PATCH /api/v1/time-logs/subtask/status` untuk subtask
  - `PATCH /api/v1/time-logs/card/status` untuk card (dengan validasi)

---

## 🧪 Testing Checklist

### Test Case 1: First Time Start
- [ ] `started_at` di `card_assignments` ter-set
- [ ] `assignment_status` menjadi "in progress"
- [ ] Status card/subtask menjadi "in progress"

### Test Case 2: Stop Tracking
- [ ] `end_time` dan `duration_minutes` ter-set di `time_logs`
- [ ] `actual_hours` di `cards` ter-update (total semua logs)
- [ ] `completed_at` ter-set di `card_assignments`
- [ ] `assignment_status` menjadi "completed"

### Test Case 3: Start Again (Second Time)
- [ ] `started_at` di `card_assignments` **TIDAK berubah**
- [ ] New time log created dengan start_time baru
- [ ] Status tetap "in progress"

### Test Case 4: Stop Again (Second Time)
- [ ] `actual_hours` bertambah (sum dari 2 time logs)
- [ ] `completed_at` ter-update ke waktu terbaru

### Test Case 5: No Card Assignment
- [ ] Tidak error jika card_assignments tidak ada
- [ ] Time tracking tetap jalan normal
- [ ] Hanya time_logs dan cards yang ter-update

---

## 📱 Flutter Integration Example

### Start Tracking with Assignment Check

```dart
Future<void> startTimeTracking(int cardId) async {
  try {
    final response = await dio.post(
      '/time-logs/start',
      data: {
        'card_id': cardId,
        'description': 'Working on task',
      },
    );

    if (response.data['success']) {
      print('✅ Timer started');
      print('📝 Assignment status updated automatically');
      
      // Update UI
      setState(() {
        isTracking = true;
        currentTimerData = response.data['data'];
      });
    }
  } catch (e) {
    print('Error: $e');
  }
}
```

### Stop Tracking with Results

```dart
Future<void> stopTimeTracking(int timeLogId) async {
  try {
    final response = await dio.post(
      '/time-logs/$timeLogId/stop',
      data: {
        'description': 'Completed work session',
      },
    );

    if (response.data['success']) {
      final data = response.data['data'];
      
      print('✅ Timer stopped');
      print('⏱️ Duration: ${data['duration_formatted']}');
      print('📊 Card actual_hours updated');
      print('✔️ Assignment completed_at updated');
      
      // Show summary
      showDialog(
        context: context,
        builder: (context) => AlertDialog(
          title: Text('Work Session Completed'),
          content: Text(
            'Total time: ${data['duration_formatted']}\n'
            'Card: ${data['card_title']}'
          ),
        ),
      );
      
      // Update UI
      setState(() {
        isTracking = false;
        totalHoursWorked += data['duration_minutes'] / 60;
      });
    }
  } catch (e) {
    print('Error: $e');
  }
}
```

---

## 🔍 SQL Queries for Debugging

### Check First Start Status

```sql
-- Cek apakah user sudah pernah start card ini
SELECT 
    ca.id,
    ca.card_id,
    ca.user_id,
    ca.started_at,
    ca.assignment_status,
    CASE 
        WHEN ca.started_at IS NULL THEN 'FIRST TIME'
        ELSE 'ALREADY STARTED'
    END as start_status
FROM card_assignments ca
WHERE ca.card_id = 5 AND ca.user_id = 1;
```

### Check Total Hours

```sql
-- Cek total hours dari semua time logs
SELECT 
    c.id as card_id,
    c.card_title,
    c.estimated_hours,
    c.actual_hours,
    COUNT(tl.id) as total_sessions,
    SUM(tl.duration_minutes) as total_minutes,
    SUM(tl.duration_minutes) / 60 as calculated_hours
FROM cards c
LEFT JOIN time_logs tl ON c.id = tl.card_id AND tl.end_time IS NOT NULL
WHERE c.id = 5
GROUP BY c.id;
```

### Check Assignment History

```sql
-- Cek history assignment
SELECT 
    ca.id,
    ca.card_id,
    c.card_title,
    ca.user_id,
    u.username,
    ca.assigned_at,
    ca.started_at,
    ca.completed_at,
    ca.assignment_status,
    TIMESTAMPDIFF(HOUR, ca.started_at, ca.completed_at) as hours_worked
FROM card_assignments ca
JOIN cards c ON ca.card_id = c.id
JOIN users u ON ca.user_id = u.id
WHERE ca.card_id = 5;
```

---

## 📚 Related Documentation

- [API_TIME_TRACKING_DOCUMENTATION.md](./API_TIME_TRACKING_DOCUMENTATION.md) - Complete API reference
- [API_AUTO_UPDATE_STATUS_TEST.md](./API_AUTO_UPDATE_STATUS_TEST.md) - Status update test scenarios

---

## 🤝 Support

Jika ada pertanyaan atau issue, silakan buka issue di repository atau hubungi tim development.
