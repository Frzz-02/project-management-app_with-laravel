# Test Auto-Update Status Time Tracking

## Scenario Test Flow

### 1. Start Tracking pada Card (tanpa subtask)
**Expected**: Card status auto-update ke "in progress"

```http
POST http://localhost:8000/api/v1/time-logs/start
Authorization: Bearer YOUR_TOKEN
Content-Type: application/json

{
  "card_id": 1,
  "description": "Working on card without subtask"
}
```

**Check**: 
- Card ID 1 status sekarang "in progress" ✅

---

### 2. Start Tracking pada Subtask
**Expected**: Subtask status auto-update ke "in progress", card status TIDAK berubah

```http
POST http://localhost:8000/api/v1/time-logs/start
Authorization: Bearer YOUR_TOKEN
Content-Type: application/json

{
  "card_id": 2,
  "subtask_id": 5,
  "description": "Working on subtask"
}
```

**Check**: 
- Subtask ID 5 status sekarang "in progress" ✅
- Card ID 2 status TIDAK berubah ✅

---

### 3. Stop Tracking
**Expected**: Status card/subtask TETAP "in progress" (tidak berubah)

```http
POST http://localhost:8000/api/v1/time-logs/15/stop
Authorization: Bearer YOUR_TOKEN
Content-Type: application/json

{
  "description": "Completed work"
}
```

**Check**:
- Card/Subtask status masih "in progress" ✅

---

### 4. Update Subtask Status ke Done (Langsung)
**Expected**: Subtask bisa langsung diubah ke "done"

```http
PATCH http://localhost:8000/api/v1/time-logs/subtask/status
Authorization: Bearer YOUR_TOKEN
Content-Type: application/json

{
  "subtask_id": 5,
  "status": "done"
}
```

**Check**:
- Subtask ID 5 status sekarang "done" ✅

---

### 5. Update Card Status ke Review (GAGAL - Ada Subtask Belum Done)
**Expected**: Error 400 karena masih ada subtask yang belum "done"

```http
PATCH http://localhost:8000/api/v1/time-logs/card/status
Authorization: Bearer YOUR_TOKEN
Content-Type: application/json

{
  "card_id": 2,
  "status": "review"
}
```

**Response Expected**:
```json
{
  "success": false,
  "message": "Tidak bisa mengubah status card ke \"review\" karena masih ada subtask yang belum selesai",
  "data": {
    "unfinished_subtasks_count": 2,
    "total_subtasks": 3,
    "unfinished_subtasks": [...]
  }
}
```

---

### 6. Update Semua Subtask ke Done
**Expected**: Semua subtask berhasil diubah ke "done"

```http
PATCH http://localhost:8000/api/v1/time-logs/subtask/status
Authorization: Bearer YOUR_TOKEN
Content-Type: application/json

{
  "subtask_id": 6,
  "status": "done"
}
```

```http
PATCH http://localhost:8000/api/v1/time-logs/subtask/status
Authorization: Bearer YOUR_TOKEN
Content-Type: application/json

{
  "subtask_id": 7,
  "status": "done"
}
```

---

### 7. Update Card Status ke Review (BERHASIL - Semua Subtask Done)
**Expected**: Card berhasil diubah ke "review"

```http
PATCH http://localhost:8000/api/v1/time-logs/card/status
Authorization: Bearer YOUR_TOKEN
Content-Type: application/json

{
  "card_id": 2,
  "status": "review"
}
```

**Response Expected**:
```json
{
  "success": true,
  "message": "Status card berhasil diupdate ke review",
  "data": {
    "card_id": 2,
    "card_title": "Example Card",
    "status": "review",
    "all_subtasks_done": true,
    "total_subtasks": 3
  }
}
```

---

## Summary Test Results

| No | Action | Expected Behavior | Status |
|----|--------|-------------------|--------|
| 1 | Start tracking card tanpa subtask | Card → "in progress" | ✅ |
| 2 | Start tracking subtask | Subtask → "in progress" | ✅ |
| 3 | Stop tracking | Status tetap "in progress" | ✅ |
| 4 | Update subtask → done | Subtask → "done" | ✅ |
| 5 | Update card → review (belum semua done) | Error 400 | ✅ |
| 6 | Update semua subtask → done | All subtasks → "done" | ✅ |
| 7 | Update card → review (semua done) | Card → "review" | ✅ |

---

## Business Logic Validation

✅ **Start Tracking Logic**:
- Card only → update card status
- Card + Subtask → update subtask status only

✅ **Stop Tracking Logic**:
- Status tetap "in progress"
- User harus manual update

✅ **Update Subtask Status**:
- Bisa langsung ubah ke done
- No special validation

✅ **Update Card Status**:
- Ke "review"/"done" → harus semua subtask done
- Return error dengan list subtask belum done
- Ke "todo"/"in progress" → no validation
