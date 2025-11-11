# Time Tracking API Documentation

API untuk fitur time tracking yang bisa digunakan di aplikasi Flutter/Mobile.

## Base URL
```
http://localhost:8000/api/v1
```

## Authentication
Semua endpoint memerlukan Bearer Token (Laravel Sanctum).

```
Authorization: Bearer {your_token}
```

---

## 📋 Endpoints

### 1. Get All Time Logs

**Mendapatkan semua time logs milik user yang sedang login.**

```http
GET /time-logs
```

#### Query Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| status | string | No | Filter by status: `ongoing` atau `completed` |
| card_id | integer | No | Filter by card ID |
| subtask_id | integer | No | Filter by subtask ID |
| per_page | integer | No | Jumlah data per halaman (default: 20) |
| page | integer | No | Nomor halaman untuk pagination |

#### Example Request

```bash
curl -X GET "http://localhost:8000/api/v1/time-logs?status=ongoing&per_page=10" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

#### Example Response

```json
{
  "success": true,
  "message": "Time logs berhasil diambil",
  "data": [
    {
      "id": 1,
      "card_id": 5,
      "card_title": "Implement Login Feature",
      "board_name": "Sprint 1",
      "subtask_id": null,
      "subtask_name": null,
      "user_id": 1,
      "user_name": "john_doe",
      "start_time": "2025-10-31T08:30:00+07:00",
      "end_time": "2025-10-31T10:45:00+07:00",
      "duration_minutes": 135,
      "duration_formatted": "2 jam 15 menit",
      "description": "Working on login authentication",
      "is_ongoing": false,
      "created_at": "2025-10-31T08:30:00+07:00",
      "updated_at": "2025-10-31T10:45:00+07:00"
    }
  ],
  "meta": {
    "total": 10,
    "ongoing_count": 1,
    "completed_count": 9,
    "current_page": 1,
    "last_page": 1,
    "per_page": 20
  }
}
```

---

### 2. Get Ongoing Timer

**Cek apakah ada timer yang sedang berjalan untuk user.**

```http
GET /time-logs/ongoing
```

#### Example Request

```bash
curl -X GET "http://localhost:8000/api/v1/time-logs/ongoing" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

#### Example Response (Timer Active)

```json
{
  "success": true,
  "message": "Ongoing timer ditemukan",
  "data": {
    "id": 15,
    "card_id": 5,
    "card_title": "Implement Login Feature",
    "board_name": "Sprint 1",
    "subtask_id": null,
    "subtask_name": null,
    "start_time": "2025-10-31T14:30:00+07:00",
    "elapsed_minutes": 45,
    "elapsed_formatted": "45 menit",
    "description": "Working on login UI"
  }
}
```

#### Example Response (No Timer)

```json
{
  "success": true,
  "message": "Tidak ada timer yang sedang berjalan",
  "data": null
}
```

---

### 3. Start Time Tracking

**Memulai tracking waktu untuk card atau subtask.**

**Auto-Update Features:**
- ✅ Update status card/subtask ke "in progress"
- ✅ Update `started_at` di `card_assignments` (jika pertama kali start)
- ✅ Update `assignment_status` ke "in progress" di `card_assignments`

```http
POST /time-logs/start
```

#### Request Body

```json
{
  "card_id": 5,                              // Required jika tidak ada subtask_id
  "subtask_id": 2,                           // Optional
  "description": "Working on login feature"  // Optional
}
```

#### What Happens When You Start Tracking:

1. **Timer Started**: Creates new time log with start_time
2. **Status Updated**: 
   - If only card_id → card status becomes "in progress"
   - If card_id + subtask_id → subtask status becomes "in progress"
3. **Card Assignment Updated** (if exists):
   - `started_at` set to current time (only if null - first time)
   - `assignment_status` set to "in progress"

#### Example Request

```bash
curl -X POST "http://localhost:8000/api/v1/time-logs/start" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "card_id": 5,
    "description": "Starting work on login feature"
  }'
```

#### Example Response (Success)

```json
{
  "success": true,
  "message": "Time tracking dimulai",
  "data": {
    "id": 20,
    "card_id": 5,
    "card_title": "Implement Login Feature",
    "board_name": "Sprint 1",
    "subtask_id": null,
    "subtask_name": null,
    "start_time": "2025-10-31T15:30:00+07:00",
    "description": "Starting work on login feature"
  }
}
```

#### Example Response (Error - Already Running)

```json
{
  "success": false,
  "message": "Anda masih memiliki timer yang sedang berjalan",
  "data": {
    "ongoing_timer_id": 15,
    "card_title": "Previous Task"
  }
}
```

---

### 4. Stop Time Tracking

**Menghentikan timer yang sedang berjalan.**

**Auto-Update Features:**
- ✅ Calculate and save duration (end_time - start_time)
- ✅ Update `actual_hours` di table `cards` (total semua time logs)
- ✅ Update `completed_at` di `card_assignments`
- ✅ Update `assignment_status` ke "completed" di `card_assignments`
- ⚠️ **Status card/subtask tetap "in progress"** (tidak auto-update)

```http
POST /time-logs/{id}/stop
```

#### Request Body

```json
{
  "description": "Completed login feature"  // Optional
}
```

#### What Happens When You Stop Tracking:

1. **Timer Stopped**: Sets end_time and calculates duration
2. **Card Updated**:
   - `actual_hours` updated with total time from all completed time logs
3. **Card Assignment Updated** (if exists):
   - `completed_at` set to current time
   - `assignment_status` set to "completed"
4. **Status NOT Changed**: Card/subtask status remains "in progress"
   - Use separate endpoints to update status to "done" or "review"

#### Example Request

```bash
curl -X POST "http://localhost:8000/api/v1/time-logs/15/stop" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "description": "Completed login authentication"
  }'
```

#### Example Response

```json
{
  "success": true,
  "message": "Time tracking dihentikan",
  "data": {
    "id": 15,
    "card_id": 5,
    "card_title": "Implement Login Feature",
    "board_name": "Sprint 1",
    "subtask_id": null,
    "subtask_name": null,
    "start_time": "2025-10-31T14:30:00+07:00",
    "end_time": "2025-10-31T16:45:00+07:00",
    "duration_minutes": 135,
    "duration_formatted": "2 jam 15 menit",
    "description": "Completed login authentication"
  }
}
```

---

### 5. Update Time Log

**Update description dari time log yang sudah selesai.**

```http
PUT /time-logs/{id}
```

#### Request Body

```json
{
  "description": "Updated description here"
}
```

#### Example Request

```bash
curl -X PUT "http://localhost:8000/api/v1/time-logs/15" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "description": "Updated work description"
  }'
```

#### Example Response

```json
{
  "success": true,
  "message": "Time log berhasil diupdate",
  "data": {
    "id": 15,
    "description": "Updated work description"
  }
}
```

---

### 6. Delete Time Log

**Menghapus time log.**

```http
DELETE /time-logs/{id}
```

#### Example Request

```bash
curl -X DELETE "http://localhost:8000/api/v1/time-logs/15" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

#### Example Response

```json
{
  "success": true,
  "message": "Time log berhasil dihapus"
}
```

---

### 7. Get Total Time by Card

**Menghitung total waktu yang dihabiskan untuk satu card.**

```http
GET /time-logs/card/{cardId}/total
```

#### Example Request

```bash
curl -X GET "http://localhost:8000/api/v1/time-logs/card/5/total" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

#### Example Response

```json
{
  "success": true,
  "message": "Total waktu berhasil dihitung",
  "data": {
    "card_id": 5,
    "card_title": "Implement Login Feature",
    "total_minutes": 450,
    "total_hours": 7.5,
    "formatted": "7 jam 30 menit",
    "logs_count": 5
  }
}
```

---

### 8. Get Total Time by Subtask

**Menghitung total waktu yang dihabiskan untuk satu subtask.**

```http
GET /time-logs/subtask/{subtaskId}/total
```

#### Example Request

```bash
curl -X GET "http://localhost:8000/api/v1/time-logs/subtask/2/total" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

#### Example Response

```json
{
  "success": true,
  "message": "Total waktu berhasil dihitung",
  "data": {
    "subtask_id": 2,
    "subtask_name": "Create login form UI",
    "total_minutes": 180,
    "total_hours": 3,
    "formatted": "3 jam",
    "logs_count": 3
  }
}
```

---

### 9. Update Subtask Status

**Mengubah status subtask (to do → in progress → done).**

Endpoint ini bisa dipanggil langsung untuk mengubah status subtask tanpa validasi khusus.

```http
PATCH /time-logs/subtask/status
```

#### Request Body

```json
{
  "subtask_id": 2,
  "status": "done"  // Options: "to do", "in progress", "done"
}
```

#### Example Request

```bash
curl -X PATCH "http://localhost:8000/api/v1/time-logs/subtask/status" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "subtask_id": 2,
    "status": "done"
  }'
```

#### Example Response

```json
{
  "success": true,
  "message": "Status subtask berhasil diupdate ke done",
  "data": {
    "subtask_id": 2,
    "subtask_name": "Create login form UI",
    "status": "done",
    "card_id": 5,
    "card_title": "Implement Login Feature"
  }
}
```

---

### 10. Update Card Status

**Mengubah status card dengan validasi khusus.**

**⚠️ PENTING - Validasi Khusus:**
- Untuk mengubah status ke **"review"** atau **"done"**: SEMUA subtask harus berstatus "done" terlebih dahulu
- Jika masih ada subtask yang belum "done", request akan ditolak dengan error 400

```http
PATCH /time-logs/card/status
```

#### Request Body

```json
{
  "card_id": 5,
  "status": "review"  // Options: "todo", "in progress", "review", "done"
}
```

#### Example Request

```bash
curl -X PATCH "http://localhost:8000/api/v1/time-logs/card/status" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "card_id": 5,
    "status": "review"
  }'
```

#### Example Response (Success - All Subtasks Done)

```json
{
  "success": true,
  "message": "Status card berhasil diupdate ke review",
  "data": {
    "card_id": 5,
    "card_title": "Implement Login Feature",
    "status": "review",
    "all_subtasks_done": true,
    "total_subtasks": 3
  }
}
```

#### Example Response (Error - Subtasks Not Done)

```json
{
  "success": false,
  "message": "Tidak bisa mengubah status card ke \"review\" karena masih ada subtask yang belum selesai",
  "data": {
    "unfinished_subtasks_count": 2,
    "total_subtasks": 3,
    "unfinished_subtasks": [
      {
        "id": 1,
        "name": "Create login form UI",
        "status": "in progress"
      },
      {
        "id": 3,
        "name": "Add validation",
        "status": "to do"
      }
    ]
  }
}
```

---

## 🔥 Error Responses

### Validation Error (422)

```json
{
  "success": false,
  "message": "Validasi gagal",
  "errors": {
    "card_id": [
      "The card_id field is required when subtask_id is not present."
    ]
  }
}
```

### Unauthorized (401)

```json
{
  "message": "Unauthenticated."
}
```

### Forbidden (403)

```json
{
  "success": false,
  "message": "Anda tidak memiliki izin untuk tracking waktu di card ini"
}
```

### Not Found (404)

```json
{
  "success": false,
  "message": "Resource not found"
}
```

### Server Error (500)

```json
{
  "success": false,
  "message": "Gagal memulai time tracking: Internal server error"
}
```

---

## 💡 Usage Flow untuk Flutter

### 🔄 Auto-Update Status Logic

**Saat START Tracking:**
1. **Jika hanya `card_id` (tanpa subtask)**: 
   - Card status auto-update ke **"in progress"**
   
2. **Jika `card_id` + `subtask_id` (ada subtask)**:
   - Subtask status auto-update ke **"in progress"**
   - Card status TIDAK berubah

**Saat STOP Tracking:**
- Status card/subtask **TETAP "in progress"** (tidak berubah otomatis)
- User harus manual update status menggunakan endpoint terpisah:

**Update Subtask Status (Langsung):**
```dart
// Ubah subtask ke "done" (tanpa validasi)
await updateSubtaskStatus(subtaskId: 2, status: 'done');
```

**Update Card Status (Dengan Validasi):**
```dart
// Ubah card ke "review" (validasi: semua subtask harus done)
await updateCardStatus(cardId: 5, status: 'review');
// Jika ada subtask belum done, akan return error
```

---

### 1. Cek Ongoing Timer saat App Start

```dart
// Cek apakah ada timer yang sedang berjalan
final response = await http.get(
  Uri.parse('$baseUrl/time-logs/ongoing'),
  headers: {'Authorization': 'Bearer $token'},
);

if (response.data != null) {
  // Ada timer aktif, tampilkan di UI
  startRealtimeCounter(response.data['start_time']);
}
```

### 2. Start Tracking

```dart
// Start timer untuk card
final response = await http.post(
  Uri.parse('$baseUrl/time-logs/start'),
  headers: {
    'Authorization': 'Bearer $token',
    'Content-Type': 'application/json',
  },
  body: jsonEncode({
    'card_id': cardId,
    'description': 'Working on this task',
  }),
);
```

### 3. Stop Tracking

```dart
// Stop timer
final response = await http.post(
  Uri.parse('$baseUrl/time-logs/$timeLogId/stop'),
  headers: {
    'Authorization': 'Bearer $token',
    'Content-Type': 'application/json',
  },
  body: jsonEncode({
    'description': 'Completed the task',
  }),
);
```

### 4. Get History dengan Pagination

```dart
// Get time logs dengan pagination
final response = await http.get(
  Uri.parse('$baseUrl/time-logs?page=$currentPage&per_page=20&status=completed'),
  headers: {'Authorization': 'Bearer $token'},
);
```

---

## 🎯 Flutter Implementation Tips

1. **Real-time Timer**: Gunakan `Timer.periodic()` untuk update elapsed time di UI
2. **State Management**: Simpan `ongoing_timer_id` di provider/bloc untuk tracking state
3. **Offline Support**: Simpan start time di local storage, sync saat online
4. **Background Service**: Gunakan `flutter_background_service` untuk tracking saat app di background
5. **Notifications**: Tampilkan notification saat timer berjalan

---

## 📞 Support

Jika ada pertanyaan atau issue, silakan hubungi backend team.

**Created**: October 31, 2025  
**Version**: 1.0.0
