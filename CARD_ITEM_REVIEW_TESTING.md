# 🧪 Testing Guide: Card Review Feature (Card-Item Component)

## Quick Test Steps

### 1️⃣ **Setup Test Environment**
```bash
# Clear caches
php artisan view:clear
php artisan cache:clear
php artisan config:clear

# Start development server
composer dev
```

### 2️⃣ **Login sebagai Team Lead**
- Navigate to: `http://localhost:8000/login`
- Login dengan user yang memiliki role `team lead` di salah satu project

### 3️⃣ **Buat/Edit Card dengan Status "Review"**

**Option A - Via UI:**
1. Buka project board: `http://localhost:8000/boards/{board_id}`
2. Klik card atau create new card
3. Set status = "Review"
4. Save

**Option B - Via Tinker:**
```php
php artisan tinker

// Get a card and set to review status
$card = App\Models\Card::first();
$card->update(['status' => 'review']);

// Verify
$card->status; // Should output: "review"
```

### 4️⃣ **Visual Verification**

✅ **Expected UI di Kanban Board:**
```
┌─────────────────────────────────────┐
│  Card Title                      ⋮  │
│  Description...                     │
│                                     │
│  ⚠ Medium  📅 Nov 12                │
│  Progress: 2h / 5h                  │
│  ━━━━━━━━━━░░░░ 40%                 │
│                                     │
│  👤 JD  💬 3  ☑ 2/5                 │
│                                     │
│  ┌─────────────┬─────────────────┐  │
│  │ ✓ Approve   │ ✗ Request Change│  │
│  └─────────────┴─────────────────┘  │
└─────────────────────────────────────┘
```

**Check:**
- [ ] Button "Approve" dengan icon ✓ (hijau)
- [ ] Button "Request Changes" dengan icon ✗ (merah)
- [ ] Kedua button terlihat jelas dengan icon DAN text

### 5️⃣ **Test Approve Flow**

**Steps:**
1. Klik button **"Approve"**
2. Modal popup muncul dengan:
   - Title: "✅ Approve Card"
   - Card title: "{Card Title}"
   - Textarea untuk notes (opsional)
   - Character counter: "0/2000 karakter"
   - Button "Cancel" dan "Approve" (hijau)

3. **Test Case A - Without Notes:**
   - Klik langsung "Approve" tanpa isi notes
   - Confirm dialog muncul: "Approve card ini? Status akan diubah ke Done..."
   - Klik OK
   - Success alert: "Card berhasil di-approve!"
   - Page reload
   - Card berpindah ke kolom "Done"

4. **Test Case B - With Notes:**
   - Klik "Approve" lagi (dengan card review lain)
   - Type di textarea: "Great work! Design looks clean."
   - Character counter update: "27/2000 karakter"
   - Klik button "Approve" (hijau)
   - Confirm → OK
   - Success alert
   - Page reload

### 6️⃣ **Test Request Changes Flow**

**Steps:**
1. Klik button **"Request Changes"**
2. Modal popup dengan:
   - Title: "🔄 Request Changes"
   - Card title: "{Card Title}"
   - Textarea notes
   - Button "Cancel" dan "Request Changes" (merah)

3. **Type notes:**
   ```
   Please update the button alignment. 
   Also, change the color scheme to match brand guidelines.
   ```

4. Klik "Request Changes" (button merah)
5. Confirm dialog: "Request perubahan? Card akan dikembalikan ke Todo..."
6. OK → Success alert → Page reload
7. Card berpindah ke kolom "Todo"

### 7️⃣ **Test Event Isolation**

**Test klik behavior:**

| Action | Expected Behavior | ✓/✗ |
|--------|------------------|-----|
| Klik di card body (title/description) | Card detail modal terbuka | |
| Klik button "Approve" | Notes modal terbuka, card detail TIDAK terbuka | |
| Klik button "Request Changes" | Notes modal terbuka, card detail TIDAK terbuka | |
| Klik backdrop (area hitam) modal | Modal close | |
| Klik textarea di modal | Modal tetap terbuka, cursor di textarea | |
| Klik button "Cancel" di modal | Modal close tanpa submit | |

### 8️⃣ **Database Verification**

**A. Check card_reviews table:**
```sql
SELECT 
    cr.id,
    cr.card_id,
    cr.status,
    cr.notes,
    cr.reviewed_at,
    u.username as reviewed_by_name
FROM card_reviews cr
LEFT JOIN users u ON cr.reviewed_by = u.id
ORDER BY cr.reviewed_at DESC
LIMIT 5;
```

**Expected Output:**
```
id | card_id | status   | notes                    | reviewed_at         | reviewed_by_name
---|---------|----------|--------------------------|---------------------|------------------
5  | 12      | rejected | Please update button...  | 2025-11-12 10:30:00 | john_team_lead
4  | 11      | approved | Great work!              | 2025-11-12 10:25:00 | john_team_lead
```

**B. Check cards table:**
```sql
SELECT id, card_title, status 
FROM cards 
WHERE id IN (11, 12);
```

**Expected:**
```
id | card_title        | status
---|-------------------|-------
11 | UI Design Task    | done      (was 'review', approved)
12 | API Integration   | todo      (was 'review', rejected)
```

**C. Check card_assignments (untuk approved card):**
```sql
SELECT 
    ca.id,
    ca.card_id,
    ca.assignment_status,
    ca.completed_at,
    u.username
FROM card_assignments ca
LEFT JOIN users u ON ca.user_id = u.id
WHERE ca.card_id = 11;
```

**Expected (jika card di-approve):**
```
id | card_id | assignment_status | completed_at        | username
---|---------|-------------------|---------------------|----------
20 | 11      | completed         | 2025-11-12 10:25:00 | alice
21 | 11      | completed         | 2025-11-12 10:25:00 | bob
```

### 9️⃣ **Browser Console Check**

**Open DevTools (F12) → Console Tab**

**Expected Logs:**
```javascript
✅ Alpine.js initialized
✅ Card component loaded
// When click Approve/Reject button:
✅ Modal opened for status: approved
// When submit:
✅ Fetching: POST /cards/12/reviews
✅ Response: {success: true, message: "Card berhasil di-approve!"}
```

**⚠️ No Errors Expected:**
- ❌ `handleQuickReview is not defined` → SHOULD NOT APPEAR
- ❌ `Cannot read property 'length' of undefined`
- ❌ `404 Not Found: /cards/12/reviews`

**Network Tab Check:**
- Request: `POST /cards/{id}/reviews`
- Status: `200 OK`
- Response:
  ```json
  {
    "success": true,
    "message": "Card berhasil di-approve!",
    "data": {
      "review": {...},
      "card": {...}
    }
  }
  ```

### 🔟 **Authorization Test**

**Test dengan different roles:**

| User Role | Project Member? | Status="Review"? | Buttons Visible? |
|-----------|----------------|------------------|------------------|
| Admin | - | Yes | ✅ Yes |
| Team Lead | Yes | Yes | ✅ Yes |
| Designer | Yes | Yes | ❌ No |
| Developer | Yes | Yes | ❌ No |
| Guest | No | Yes | ❌ No |

**How to test:**
```php
php artisan tinker

// Create test users
$admin = User::where('role', 'admin')->first();
$teamLead = User::find(2); // Adjust ID
$developer = User::find(3);

// Login as different users and check UI
Auth::login($admin);     // Should see buttons
Auth::login($teamLead);  // Should see buttons
Auth::login($developer); // Should NOT see buttons
```

---

## 🐛 Common Issues & Solutions

### **Issue 1: Button Text Hilang (Hanya Icon)**
**Symptom:** Button hanya menampilkan icon ✓ atau ✗, text "Approve" / "Request Changes" tidak terlihat

**Diagnosis:**
- Inspect element di browser
- Check apakah `<span>` dengan text ada di DOM

**Solution:**
✅ **Already Fixed** in latest update:
```blade
<button class="... flex items-center justify-center">
    <svg class="w-4 h-4 mr-1">...</svg>
    <span>Approve</span>  <!-- Static text, no x-text -->
</button>
```

---

### **Issue 2: Error "handleQuickReview is not defined"**
**Symptom:** Console error saat klik button

**Diagnosis:**
```javascript
// Check di console:
document.querySelectorAll('[x-data]').forEach(el => {
    console.log('Alpine scope:', el.__x);
});
```

**Solution:**
✅ **Already Fixed** - Function renamed dan moved ke proper scope:
- OLD: `handleQuickReview(cardId, status)` (global scope, not defined)
- NEW: `openNotesModal(status)` (defined in x-data component)

---

### **Issue 3: Modal Tidak Muncul**
**Symptom:** Klik button tidak ada reaksi

**Diagnosis:**
1. Check Alpine.js loaded:
   ```javascript
   // In console:
   typeof Alpine !== 'undefined' // Should return true
   ```

2. Check x-cloak style:
   ```html
   <!-- In <head>: -->
   <style>[x-cloak] { display: none !important; }</style>
   ```

3. Check state:
   ```javascript
   // In console, after click:
   $el.__x.$data.showNotesModal // Should be true
   ```

**Solution:**
- Clear view cache: `php artisan view:clear`
- Hard refresh browser: `Ctrl + Shift + R`
- Check `x-show="showNotesModal"` directive

---

### **Issue 4: Review Tidak Tersimpan ke Database**
**Symptom:** Success alert muncul tapi data tidak masuk DB

**Diagnosis:**
1. Check route registered:
   ```bash
   php artisan route:list --name=reviews
   ```
   Should output:
   ```
   POST   cards/{card}/reviews  › CardReviewController@store
   GET    cards/{card}/reviews  › CardReviewController@index
   ```

2. Check authorization:
   ```php
   // In CardReviewController
   $this->authorize('reviewCard', $card);
   ```

3. Check validation:
   ```php
   // In StoreCardReviewRequest
   'status' => 'required|in:approved,rejected',
   'notes' => 'nullable|string|max:2000',
   ```

**Solution:**
- Verify user role: Team Lead atau Admin
- Check CSRF token valid
- Check database connection
- Check logs: `tail -f storage/logs/laravel.log`

---

## 📊 Success Criteria

✅ **All tests pass if:**

1. **Visual:**
   - [ ] Button icons + text terlihat jelas
   - [ ] Warna sesuai (green untuk approve, red untuk reject)
   - [ ] Modal popup design clean dan responsive

2. **Functional:**
   - [ ] Button trigger modal notes (bukan card detail)
   - [ ] Textarea bisa diisi atau kosong (opsional)
   - [ ] Character counter update realtime
   - [ ] Submit berhasil dengan/tanpa notes
   - [ ] Success alert muncul
   - [ ] Page reload dan card berpindah status

3. **Database:**
   - [ ] Record baru di `card_reviews` table
   - [ ] Card status update (`review` → `done`/`todo`)
   - [ ] Assignments completed (jika approve)

4. **Authorization:**
   - [ ] Team Lead: dapat akses
   - [ ] Designer/Developer: tidak dapat akses
   - [ ] Admin: dapat akses

5. **Performance:**
   - [ ] No JavaScript errors di console
   - [ ] AJAX request <500ms
   - [ ] Page reload smooth
   - [ ] No memory leaks (close modal properly)

---

## 🎯 Final Checklist

- [ ] Code changes implemented (`card-item.blade.php`)
- [ ] x-cloak style added (`layouts/app.blade.php`)
- [ ] View cache cleared
- [ ] Browser cache cleared (hard refresh)
- [ ] Tested as Team Lead
- [ ] Tested Approve flow (with and without notes)
- [ ] Tested Request Changes flow (with notes)
- [ ] Verified database updates
- [ ] Checked browser console (no errors)
- [ ] Tested authorization (other roles)
- [ ] Documentation updated (this file + CARD_ITEM_REVIEW_FIX.md)

---

**When ALL checkboxes are ✅, feature is READY for production!**

---

**Last Updated:** November 12, 2025
**Test Duration:** ~15 minutes
**Test Environment:** Laravel 12 + SQLite + Alpine.js 3.x
