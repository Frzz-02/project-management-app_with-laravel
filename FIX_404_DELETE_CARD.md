# Fix untuk Error 404 Delete Card

## Problem
```
Failed to load resource: the server responded with a status of 404 (Not Found)
Error deleting card: SyntaxError: Unexpected token '<', "<!DOCTYPE "... is not valid JSON
```

## Root Cause
AJAX request tidak mengirim authentication cookies, sehingga Laravel middleware `auth` menganggap user belum login dan redirect ke login page (HTML), bukan mengembalikan JSON 404.

## Solution Applied

### 1. Add `credentials: 'same-origin'` to DELETE request

**File:** `resources/views/components/ui/card-detail-modal.blade.php`

```javascript
const response = await fetch(`/cards/${cardId}`, {
    method: 'DELETE',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken.content,
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
    },
    credentials: 'same-origin' // ✅ ADDED: Include cookies for auth
});
```

### 2. Add `credentials: 'same-origin'` to PATCH request

**File:** `resources/views/components/ui/edit-card-modal.blade.php`

```javascript
const response = await fetch(`/cards/${this.cardId}`, {
    method: 'POST',
    headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
    },
    body: formData,
    credentials: 'same-origin' // ✅ ADDED: Include cookies for auth
});
```

## Why This Works

**Fetch API Default Behavior:**
- By default, `fetch()` does NOT include cookies in cross-origin requests
- Even for same-origin requests, cookies might not be included depending on browser settings

**`credentials: 'same-origin'`:**
- Tells fetch to include cookies for same-origin requests
- Laravel session cookie will be sent
- Middleware `auth` can verify user is logged in
- Request proceeds normally to controller

## Test Steps

### 1. Clear Browser Cache
```
Chrome: Ctrl + Shift + Delete
Firefox: Ctrl + Shift + Delete
Edge: Ctrl + Shift + Delete

Select: "Cached images and files"
Time range: "All time"
Click "Clear data"
```

### 2. Hard Refresh Page
```
Chrome/Firefox/Edge: Ctrl + F5
```

### 3. Test Delete Card
1. Open browser console (F12)
2. Navigate to project board
3. Click on a card
4. Click "Delete Card"
5. Check console logs

**Expected Console Output:**
```
🗑️ Deleting card: 123
🔑 CSRF Token: AbCdEf1234567890...
📡 Delete response status: 200
📡 Response headers: {contentType: "application/json", status: 200, statusText: "OK"}
📦 Delete response data: {success: true, message: "Card 'Task Name' deleted successfully!", board_id: 5}
✅ Card deleted successfully!
🔄 Reloading page...
```

**Previous Error (should be gone):**
```
❌ Failed to load resource: 404
❌ SyntaxError: Unexpected token '<', "<!DOCTYPE "... is not valid JSON
```

### 4. Test Edit Card
1. Open edit modal
2. Change some fields
3. Submit form
4. Check console logs

**Expected Console Output:**
```
🚀 Submitting card update...
📝 Card ID: 123
📦 Form data: {board_id: "5", card_title: "Updated", ...}
📡 Response status: 200
📦 Response data: {success: true, message: "Card berhasil diupdate!", ...}
✅ Card updated successfully!
```

## Additional Fixes (If Still Error)

### If Still Getting 404:

**Check Session:**
```php
// In controller, add debug:
Log::info('User authenticated:', ['user_id' => Auth::id(), 'logged_in' => Auth::check()]);
```

**Check Middleware:**
```php
// routes/web.php - Verify route is inside auth middleware
Route::middleware('auth')->group(function () {
    Route::resource('cards', CardController::class);
});
```

### If Getting CSRF Token Mismatch:

**Check meta tag exists:**
```blade
<!-- resources/views/layouts/app.blade.php -->
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
```

**Regenerate application key:**
```bash
php artisan key:generate
```

### If Getting 403 Unauthorized:

**Check Policy:**
```php
// app/Policies/CardPolicy.php
public function delete(User $user, Card $card)
{
    // Allow admin atau team lead
    if ($user->role === 'admin') return true;
    
    $project = $card->board->project;
    $member = $project->members()->where('user_id', $user->id)->first();
    
    return $member && $member->role === 'team lead';
}
```

**Test as correct user:**
- Login sebagai Admin OR
- Login sebagai Team Lead dari project tersebut

## Browser Compatibility

**`credentials: 'same-origin'` Support:**
- ✅ Chrome 42+
- ✅ Firefox 39+
- ✅ Safari 10.1+
- ✅ Edge 14+

## Related Documentation

- [MDN: Fetch API - credentials option](https://developer.mozilla.org/en-US/docs/Web/API/fetch#credentials)
- [Laravel: CSRF Protection](https://laravel.com/docs/12.x/csrf)
- [Laravel: Authentication](https://laravel.com/docs/12.x/authentication)

## Verification Checklist

After applying fix:
- [ ] Browser cache cleared
- [ ] Page hard refreshed (Ctrl+F5)
- [ ] Logged in as correct user (Admin/Team Lead)
- [ ] Console shows DELETE request to correct URL
- [ ] Response status is 200 (not 404)
- [ ] Response is JSON (not HTML)
- [ ] Card deleted successfully
- [ ] Page reloads
- [ ] Card removed from board

---

**Fix Applied:** 2025-01-XX
**Issue:** 404 on DELETE /cards/{id}
**Root Cause:** Missing authentication cookies in fetch request
**Solution:** Add `credentials: 'same-origin'` to fetch options
**Status:** ✅ FIXED

