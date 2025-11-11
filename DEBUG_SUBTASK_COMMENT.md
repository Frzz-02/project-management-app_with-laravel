# 🐛 Debug Guide - Subtask Comment Issue

## Langkah-langkah Debug

### 1. Buka Browser Console (F12)
Sebelum test, buka Console untuk monitoring logs

### 2. Login & Akses Card
```
1. Login sebagai user dengan role Developer atau Designer
2. Navigate ke Projects
3. Pilih salah satu project
4. Klik salah satu card
```

### 3. Buka Subtask Modal
```
1. Scroll ke section "Subtasks"
2. Klik salah satu subtask
3. Modal akan muncul
```

### 4. Check Console Log - Initialization
Harus ada log seperti ini:
```javascript
✅ Subtask Comment Data initialized for subtask: 123 card: 456
✅ Subtask comments loaded: 0
```

**JIKA TIDAK ADA LOG INI**:
- Alpine.js tidak ter-initialize
- Check apakah Alpine.js loaded: ketik `Alpine` di console, harus ada object

**JIKA cardId = undefined**:
- Data tidak terpassing dari event dispatch
- Check line 683 di show.blade.php

### 5. Test Add Comment
```
1. Klik "Add Comment" button
2. Ketik: "Test comment debug"
3. Klik "Comment" button
```

### 6. Check Console Log - Submit
Harus ada log seperti ini:
```javascript
📤 Sending comment: {
    card_id: 456,
    subtask_id: 123,
    comment_text: "Test comment debug",
    comment_type: "subtask"
}
```

**JIKA LOG INI TIDAK ADA**:
- Function tidak terpanggil
- Check apakah button @click="addComment()" terhubung

**JIKA ADA ERROR LOG**:
```javascript
❌ Missing required fields: { subtaskId: ..., cardId: ..., comment: ... }
```
→ Ada field yang undefined/empty

### 7. Check Network Tab
```
1. Buka Network tab di DevTools
2. Filter: XHR/Fetch
3. Cari request ke: /comments
4. Klik request untuk detail
```

**Check Payload**:
```json
{
    "card_id": 456,
    "subtask_id": 123,
    "comment_text": "Test comment debug",
    "comment_type": "subtask"
}
```

**Check Response**:

✅ **Success (201 Created)**:
```json
{
    "success": true,
    "message": "Komentar berhasil ditambahkan!",
    "comment": {
        "id": 789,
        "comment_text": "Test comment debug",
        "user_name": "John Doe",
        ...
    }
}
```

❌ **Validation Error (422)**:
```json
{
    "message": "The card id field is required.",
    "errors": {
        "card_id": ["The card id field is required."]
    }
}
```
→ Backend tidak menerima card_id

❌ **Authorization Error (403)**:
```json
{
    "success": false,
    "message": "Hanya Developer dan Designer yang bisa comment di subtask."
}
```
→ User role tidak sesuai

❌ **Server Error (500)**:
```json
{
    "success": false,
    "message": "Gagal menambahkan komentar: ..."
}
```
→ Check Laravel logs

### 8. Check Laravel Logs (Jika Error 500)
```bash
# Windows PowerShell
Get-Content storage/logs/laravel.log -Tail 50

# Atau buka file langsung
# storage/logs/laravel.log
```

Look for:
```
[2025-11-08 10:30:00] local.ERROR: Failed to create comment
```

### 9. Check Database
```bash
# Buka SQLite database
sqlite3 database/database.sqlite

# Check comments table
SELECT * FROM comments 
WHERE comment_type = 'subtask' 
ORDER BY created_at DESC 
LIMIT 5;

# Check jika ada constraint errors
.schema comments
```

## Common Issues & Solutions

### Issue 1: "cardId is undefined"
**Console Log**:
```javascript
✅ Subtask Comment Data initialized for subtask: 123 card: undefined
```

**Cause**: Event dispatch tidak include card_id

**Solution**: Check line 683 di show.blade.php:
```blade
'card_id' => $card->id,  // ← Harus ada ini
```

### Issue 2: "Validation Error: card_id required"
**Network Response**: 422

**Cause**: AJAX request tidak mengirim card_id

**Solution**: Check addComment() function:
```javascript
body: JSON.stringify({
    card_id: this.cardId,  // ← Harus ada ini
    subtask_id: this.subtaskId,
    ...
})
```

### Issue 3: "Authorization Error: Hanya Developer..."
**Network Response**: 403

**Cause**: User role bukan Developer/Designer

**Solution**: 
1. Check user role di database
2. Login dengan user yang benar
```sql
SELECT users.name, users.email, project_members.role 
FROM users
JOIN project_members ON users.id = project_members.user_id
WHERE project_members.project_id = ?;
```

### Issue 4: "Comment tidak muncul di list"
**Network Response**: 201 (Success)
**Console Log**: ✅ Subtask comment added successfully

**Cause**: Frontend tidak update UI

**Debug**:
```javascript
// Di console, after submit, check:
this.comments  // Harus ada comment baru

// Atau check Alpine data:
$el.__x.$data.comments  // Dari element dengan x-data
```

**Solution**: Check line ~1493:
```javascript
this.comments.push(data.comment);  // ← Harus ada ini
```

### Issue 5: "Alpine.js not loaded"
**Console**: `Alpine is not defined`

**Solution**: Check layout (app.blade.php):
```blade
@vite(['resources/css/app.css', 'resources/js/app.js'])

<!-- Alpine.js MUST be included -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
```

## Quick Test Script

Paste di Browser Console untuk test manual:
```javascript
// Test 1: Check Alpine loaded
console.log('Alpine loaded:', typeof Alpine !== 'undefined');

// Test 2: Check CSRF token
console.log('CSRF token:', document.querySelector('meta[name="csrf-token"]')?.content);

// Test 3: Manual API call (ganti IDs sesuai data kamu)
async function testAddComment(cardId, subtaskId) {
    try {
        const response = await fetch('/comments', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                card_id: cardId,           // Ganti dengan card ID real
                subtask_id: subtaskId,     // Ganti dengan subtask ID real
                comment_text: 'Manual test comment',
                comment_type: 'subtask'
            })
        });
        
        const data = await response.json();
        console.log('Response:', data);
        return data;
    } catch (error) {
        console.error('Error:', error);
    }
}

// Run test (ganti 1 dan 2 dengan ID yang valid)
testAddComment(1, 2);
```

## Expected Full Flow (Success Case)

```
1. User clicks subtask
   ✅ Modal opens
   ✅ Console: "Subtask Comment Data initialized for subtask: X card: Y"
   ✅ Console: "Subtask comments loaded: N"

2. User clicks "Add Comment"
   ✅ Form appears (x-show="showAddComment")

3. User types text + submits
   ✅ Console: "📤 Sending comment: { card_id: Y, ... }"
   ✅ Network: POST /comments (Status 201)
   ✅ Console: "✅ Subtask comment added successfully"
   ✅ UI: Comment appears in list
   ✅ UI: Form closes + textarea cleared
```

## Checklist Sebelum Report Issue

- [ ] Browser console tidak ada error
- [ ] Alpine.js loaded (cek dengan ketik `Alpine` di console)
- [ ] CSRF token exists (check meta tag)
- [ ] User login sebagai Developer/Designer
- [ ] Card ID dan Subtask ID valid (exists di database)
- [ ] Network request sukses (201 response)
- [ ] Laravel logs tidak ada error
- [ ] Database table structure correct

## File Locations

- **Frontend**: `resources/views/cards/show.blade.php`
  - Line 683: Event dispatch (card_id added)
  - Line 1137: Component init
  - Line 1403: Function definition
  - Line 1445: AJAX request

- **Backend**: `app/Http/Controllers/web/CommentController.php`
  - store() method: Line 40
  - Validation: Line 42-47
  - Authorization: Line 97-129

- **Model**: `app/Models/Comment.php`
  - Fillable: Line 28-34

- **Routes**: `routes/web.php`
  - POST /comments
  - GET /comments/subtask/{subtaskId}

---

**Jika semua check passed tapi masih error, share:**
1. Console logs (screenshot)
2. Network tab response (screenshot)
3. User role dari database
4. Laravel log error (jika ada)
