# Test Edit & Delete Card Functionality

## Quick Test Checklist

### Persiapan Test
- [ ] Run server: `php artisan serve`
- [ ] Open browser: http://localhost:8000
- [ ] Login sebagai user yang memiliki permission (Team Lead atau Admin)
- [ ] Buka Chrome DevTools (F12) → Console tab
- [ ] Navigate ke project board yang memiliki cards

---

## Test 1: Edit Card (Same Board)

**Steps:**
1. Click pada card untuk buka detail modal
2. Click tombol "Edit Card" (indigo button)
3. Verify modal edit terbuka
4. Check console untuk log: `📝 Loading card data for edit:`

**Expected Results:**
- ✅ Edit modal terbuka
- ✅ Current board ter-select di dropdown
- ✅ Selected board info card muncul (blue gradient)
- ✅ All fields terisi dengan data card
- ✅ Console log: `✅ Form loaded: {board_id: "...", ...}`

**Test Edit:**
1. Ubah card title
2. Ubah description
3. Ubah priority
4. Click "Update Card"

**Expected Console Logs:**
```
🚀 Submitting card update...
📝 Card ID: 123
📦 Form data: {_token: "...", _method: "PATCH", board_id: "5", card_title: "Updated Title", ...}
📡 Response status: 200
📦 Response data: {success: true, message: "Card berhasil diupdate!", ...}
✅ Card updated successfully!
```

**Expected Outcome:**
- ✅ Modal closes
- ✅ Page reload
- ✅ Card updated dengan data baru
- ✅ Card masih di board yang sama

---

## Test 2: Edit Card (Move to Different Board)

**Steps:**
1. Click pada card untuk buka detail modal
2. Click "Edit Card"
3. Change board dari dropdown
4. Verify selected board info card update
5. Edit card title juga
6. Click "Update Card"

**Expected Console Logs:**
```
🚀 Submitting card update...
📦 Form data: {board_id: "7", card_title: "Moved Card", ...}
📡 Response status: 200
✅ Card updated successfully!
```

**Expected Outcome:**
- ✅ Card pindah ke board baru
- ✅ Card data updated
- ✅ Redirect to new board

**Possible Errors to Check:**
- ❌ Error 403: "Anda tidak memiliki akses ke board tujuan"
  - **Solution:** Pilih board dari project yang accessible

---

## Test 3: Delete Card (Success)

**Steps:**
1. Click pada card untuk buka detail modal
2. Scroll ke sidebar kanan
3. Click tombol "Delete Card" (red button)
4. Check console BEFORE clicking confirm

**Expected Console Logs (Before Confirm):**
```
🗑️ Deleting card: 123
🔑 CSRF Token: AbCdEf1234567890...
```

5. Click "OK" di confirmation dialog

**Expected Console Logs (After Confirm):**
```
📡 Delete response status: 200
📡 Response headers: {contentType: "application/json", status: 200, statusText: "OK"}
📦 Delete response data: {success: true, message: "Card 'Task Name' deleted successfully!", board_id: 5}
✅ Card deleted successfully!
🔄 Reloading page...
```

**Expected Outcome:**
- ✅ Confirmation dialog muncul
- ✅ After confirm, card terhapus
- ✅ Modal closes
- ✅ Page reload
- ✅ Card tidak ada lagi di board

---

## Test 4: Delete Card (Cancel)

**Steps:**
1. Click card → "Delete Card"
2. Click "Cancel" di confirmation dialog

**Expected Console Logs:**
```
🚫 Delete cancelled by user
```

**Expected Outcome:**
- ✅ Card TIDAK terhapus
- ✅ Modal tetap terbuka
- ✅ No server request sent

---

## Test 5: Error Handling - No CSRF Token

**Manual Test (Advanced):**
1. Open DevTools Console
2. Run: `document.querySelector('meta[name="csrf-token"]').remove()`
3. Try delete card

**Expected Console Logs:**
```
❌ CSRF token tidak ditemukan
```

**Expected Alert:**
"Error: Security token tidak ditemukan. Silakan refresh halaman."

**Expected Outcome:**
- ✅ Error message shown
- ✅ No server request sent
- ✅ Card NOT deleted

---

## Test 6: Error Handling - Unauthorized Delete

**Test as Non-Admin User:**
1. Login sebagai Designer atau Developer (bukan Team Lead)
2. Open card detail
3. Check sidebar - Delete button should NOT appear

**Expected UI:**
- ✅ "Edit & Delete Restricted" message shown
- ✅ Lock icon displayed
- ✅ Message: "Only Admin or Team Lead can edit/delete cards"

**If Try to Force Request:**
- Response: 403 Forbidden
- Message: "Unauthorized. Only Admin or Team Lead can delete cards."

---

## Test 7: Edit Form Validation

**Test Required Fields:**
1. Open edit modal
2. Clear card title
3. Submit form

**Expected:**
- ❌ Validation error: "The card_title field is required."
- ✅ Error displayed below field
- ✅ Form does not close

**Test Board Required:**
1. Open edit modal
2. Set board dropdown to "Pilih board..."
3. Submit form

**Expected:**
- ❌ Validation error: "The board_id field is required."
- ✅ Error displayed
- ✅ Form does not close

---

## Common Errors & Solutions

### Error 1: "CSRF token tidak ditemukan"
**Cause:** Meta tag missing in layout
**Solution:** 
```blade
<!-- Add to resources/views/layouts/app.blade.php -->
<meta name="csrf-token" content="{{ csrf_token() }}">
```

### Error 2: "Server tidak mengembalikan response JSON yang valid"
**Cause:** Laravel returning HTML error page
**Solution:** Check Laravel logs: `storage/logs/laravel.log`
- Check for 500 errors
- Check for database connection issues

### Error 3: Delete button not visible
**Cause:** User tidak memiliki permission
**Solution:** 
- Login sebagai Admin atau Team Lead
- Check CardPolicy rules

### Error 4: "Anda tidak memiliki akses ke board tujuan"
**Cause:** Moving card to board user tidak punya akses
**Solution:** 
- Pilih board dari project yang accessible
- Check project_members table

### Error 5: Modal tidak menutup setelah delete
**Cause:** Alpine.store('modal') tidak initialized
**Solution:** Check layouts/app.blade.php:
```javascript
Alpine.store('modal', {
    cardDetail: false,
    editCard: false,
    addCard: false,
    close() { /* ... */ }
});
```

### Error 6: Page tidak reload setelah delete
**Cause:** JavaScript error sebelum `window.location.reload()`
**Solution:** Check console untuk error lain

---

## Browser Compatibility Test

Test di berbagai browser:
- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Edge (latest)
- [ ] Safari (if available)

---

## Performance Check

**Network Tab Monitoring:**
1. Open DevTools → Network tab
2. Filter: XHR/Fetch
3. Delete card
4. Check request:
   - Method: DELETE
   - URL: `/cards/{id}`
   - Status: 200
   - Response Time: < 500ms
   - Content-Type: application/json

---

## Database Verification

**After Delete Test:**
```sql
-- Check card terhapus
SELECT * FROM cards WHERE id = 123;
-- Should return 0 rows

-- Check cascade delete worked
SELECT * FROM card_assignments WHERE card_id = 123;
-- Should return 0 rows

SELECT * FROM subtasks WHERE card_id = 123;
-- Should return 0 rows

SELECT * FROM comments WHERE card_id = 123;
-- Should return 0 rows

SELECT * FROM time_logs WHERE card_id = 123;
-- Should return 0 rows
```

**After Edit Test (Move Board):**
```sql
-- Check card pindah board
SELECT id, card_title, board_id FROM cards WHERE id = 123;
-- board_id should be new board_id
```

---

## Success Criteria

### Edit Card
✅ Form loads with current data
✅ Board pre-selected correctly
✅ Can edit all fields
✅ Can move to different board (with access)
✅ Validation works
✅ Success message shown
✅ Page reloads
✅ Data persisted correctly

### Delete Card
✅ Confirmation dialog works
✅ Can cancel delete
✅ CSRF token validation works
✅ Authorization check works
✅ Card deleted from database
✅ Cascade delete works (assignments, subtasks, comments, time logs)
✅ Success message shown
✅ Page reloads
✅ Card removed from UI

### Error Handling
✅ Network errors caught
✅ Validation errors displayed
✅ Authorization errors handled
✅ User-friendly error messages (Indonesian)
✅ Console logging for debugging

---

## Report Format

**Test Passed:**
```
✅ Test 1: Edit Card (Same Board) - PASSED
✅ Test 2: Edit Card (Move Board) - PASSED
✅ Test 3: Delete Card - PASSED
```

**Test Failed:**
```
❌ Test 3: Delete Card - FAILED
Error: "TypeError: Cannot read property 'content' of null"
Location: card-detail-modal.blade.php, line 866
Screenshot: [attach screenshot]
```

---

**Testing Date:** _________________
**Tested By:** _________________
**Laravel Version:** 12.27.1
**Browser:** Chrome/Firefox/Edge
**Test Result:** PASS / FAIL

