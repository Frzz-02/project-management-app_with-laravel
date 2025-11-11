# 🔒 Subtask Tracking Prerequisite - Implementation Guide

## 📋 Overview

**New Business Rule**: Subtask tracking hanya bisa di-start jika card-nya sudah di-tracking terlebih dahulu.

### Why This Rule?
✅ **Logical Work Flow**: Card adalah parent task, subtask adalah child  
✅ **Context Requirement**: Subtask work harus dalam context card work  
✅ **Data Consistency**: Mencegah orphaned subtask tracking  
✅ **Better UX**: User dipandu untuk follow proper workflow  

## 🎯 Business Rule

### Rule: Card Tracking First
```
BEFORE starting subtask tracking:
1. User MUST start card tracking first
2. Card tracking MUST be active (ongoing)
3. THEN user can start subtask tracking

IF card tracking NOT active:
❌ Subtask tracking will be blocked
📢 Error message shown to user
```

### Visual Flow
```
┌─────────────────────────────────────┐
│ User wants to track Subtask #1      │
└──────────────┬──────────────────────┘
               │
               ▼
      ┌────────────────────┐
      │ Check: Card         │
      │ tracking active?    │
      └────────┬────────────┘
               │
        ┌──────┴──────┐
        │             │
       YES           NO
        │             │
        ▼             ▼
   ┌────────┐    ┌────────────────────┐
   │ ALLOW  │    │ BLOCK with error   │
   │ Start  │    │ "Must start card   │
   │ Subtask│    │  tracking first"   │
   └────────┘    └────────────────────┘
```

## 💻 Implementation

### Backend Validation (TimeLogController.php)

**Location**: `app/Http/Controllers/web/TimeLogController.php` (lines ~152-176)

```php
// SUBTASK TRACKING PREREQUISITE CHECK
if (!empty($validatedData['subtask_id'])) {
    // User mau start subtask tracking
    // Cek apakah card ini sedang di-tracking oleh user yang sama
    $cardTracking = TimeLog::where('card_id', $validatedData['card_id'])
        ->where('user_id', $currentUser->id)
        ->whereNull('subtask_id')  // Hanya card tracking (bukan subtask)
        ->whereNull('end_time')     // Yang masih ongoing
        ->first();

    if (!$cardTracking) {
        return redirect()->back()->with('error', 
            'Anda harus memulai tracking card terlebih dahulu sebelum tracking subtask. Silakan start tracking pada card ini dulu.');
    }

    Log::info('Subtask tracking allowed - card tracking active', [
        'card_id' => $validatedData['card_id'],
        'subtask_id' => $validatedData['subtask_id'],
        'card_tracking_id' => $cardTracking->id
    ]);
}
```

### Key Points:
1. **Check timing**: After authorization, before creating TimeLog
2. **Check condition**: `whereNull('subtask_id')` ensures we check card tracking, not another subtask
3. **Check scope**: Only check current user's tracking (`where('user_id', $currentUser->id)`)
4. **Error message**: Clear, actionable instruction for user
5. **Logging**: Debug info when validation passes

## 🎨 User Experience

### Scenario 1: Correct Flow (Happy Path)
```
Step 1: User opens Card detail page
        Status: No tracking active
        
Step 2: User clicks "Start Work" → "Entire Card"
        Action: POST /time-logs/start (card_id=1, subtask_id=null)
        Result: ✅ Card tracking started
        Display: 🟢 Green timer "Card Tracking"

Step 3: User scrolls to Subtasks
        Status: Card tracking ACTIVE
        
Step 4: User clicks Subtask #1 → "Start Tracking"
        Check: Card tracking active? ✅ YES
        Action: POST /time-logs/start (card_id=1, subtask_id=1)
        Result: ✅ Subtask tracking started
        Display: 🔵 Blue timer "Subtask Tracking"

Final State:
📋 Card tracking: 00:15:30
🎯 Subtask #1 tracking: 00:05:20
```

### Scenario 2: Blocked Flow (Validation Error)
```
Step 1: User opens Card detail page
        Status: No tracking active
        
Step 2: User scrolls to Subtasks (skips card tracking)
        
Step 3: User clicks Subtask #1 → "Start Tracking"
        Check: Card tracking active? ❌ NO
        Action: POST /time-logs/start (card_id=1, subtask_id=1)
        Validation: ❌ FAILED
        Response: Redirect with error message
        
Step 4: User sees error banner
        📢 "Anda harus memulai tracking card terlebih dahulu 
            sebelum tracking subtask. Silakan start tracking 
            pada card ini dulu."
            
Step 5: User clicks "Start Work" → "Entire Card"
        Result: ✅ Card tracking started
        
Step 6: User tries Subtask #1 again
        Check: Card tracking active? ✅ YES
        Result: ✅ Subtask tracking started
```

## 🧪 Testing Guide

### Test Case 1: Basic Prerequisite Check
```
✅ Setup:
- Login as Developer/Designer
- Navigate to card with subtasks
- Ensure no tracking active

✅ Test Steps:
1. Direct to subtask modal
2. Click "Start Tracking" on subtask
   Expected: ❌ Error "Anda harus memulai tracking card..."
3. Close modal, start card tracking
   Expected: ✅ Success
4. Open subtask modal again
5. Click "Start Tracking" on subtask
   Expected: ✅ Success

✅ Verification:
SELECT * FROM time_logs 
WHERE user_id = ? AND card_id = ? AND end_time IS NULL;

-- Should show 2 rows after step 5:
-- Row 1: subtask_id = NULL (card tracking)
-- Row 2: subtask_id = X (subtask tracking)
```

### Test Case 2: Different User Context
```
✅ Setup:
- User A starts Card #1 tracking
- User B tries to start Subtask #1 of Card #1

✅ Test Steps:
1. User A: Start Card #1 tracking
   Expected: ✅ Success
2. User B: Try to start Subtask #1 tracking
   Check: Card tracking active FOR USER B? ❌ NO
   Expected: ❌ Error (User B must start card tracking first)
3. User B: Start Card #1 tracking
   Expected: ✅ Success
4. User B: Start Subtask #1 tracking
   Expected: ✅ Success

✅ Verification:
-- User A has only card tracking
SELECT * FROM time_logs WHERE user_id = ? AND card_id = ? AND end_time IS NULL;
-- Should show 1 row (card only)

-- User B has card + subtask tracking
SELECT * FROM time_logs WHERE user_id = ? AND card_id = ? AND end_time IS NULL;
-- Should show 2 rows (card + subtask)
```

### Test Case 3: Card Stopped, Try Start Subtask
```
✅ Setup:
- Card tracking was active, then stopped

✅ Test Steps:
1. Start Card tracking
   Expected: ✅ Success
2. Stop Card tracking
   Expected: ✅ Success
3. Try to start Subtask tracking
   Check: Card tracking active? ❌ NO (stopped)
   Expected: ❌ Error

✅ Verification:
SELECT * FROM time_logs 
WHERE user_id = ? AND card_id = ?
ORDER BY created_at DESC LIMIT 2;

-- Latest card tracking should have end_time NOT NULL (stopped)
```

## 🐛 Debugging

### Issue: Error shown when card IS tracking
**Symptoms**: User gets error even though card timer is visible

**Debug Query**:
```sql
-- Check active card tracking
SELECT 
    tl.*,
    tl.subtask_id IS NULL as is_card_tracking,
    tl.end_time IS NULL as is_ongoing
FROM time_logs tl
WHERE tl.user_id = ?
  AND tl.card_id = ?
  AND tl.end_time IS NULL
ORDER BY tl.created_at DESC;
```

**Check Points**:
1. Is `subtask_id` NULL for card tracking?
2. Is `end_time` NULL (ongoing)?
3. Is `user_id` matching current user?
4. Is `card_id` matching the card?

### Issue: Can start subtask without card tracking
**Symptoms**: Validation not working, subtask starts without card

**Debug**:
```php
// Add to startTracking() before validation
\Log::debug('Subtask prerequisite check', [
    'has_subtask_id' => !empty($validatedData['subtask_id']),
    'card_id' => $validatedData['card_id'],
    'user_id' => $currentUser->id,
]);

// After query
\Log::debug('Card tracking found', [
    'card_tracking' => $cardTracking ? $cardTracking->id : null,
]);
```

**Possible Causes**:
- Code not deployed (still using old version)
- Cache issue (clear with `php artisan cache:clear`)
- Query condition wrong

## 📊 Impact Analysis

### Before This Rule:
```
❌ User could start subtask tracking anytime
❌ Orphaned subtask tracking (no parent card tracking)
❌ Inconsistent work flow
❌ Confusing for users
```

### After This Rule:
```
✅ Enforced logical work flow
✅ All subtask tracking has parent card tracking
✅ Consistent data structure
✅ Clear user guidance
✅ Better time tracking reports
```

### Database Impact:
```sql
-- Query untuk check compliance
-- Seharusnya return 0 rows (no orphaned subtask tracking)
SELECT st.* 
FROM time_logs st
WHERE st.subtask_id IS NOT NULL
  AND st.end_time IS NULL
  AND NOT EXISTS (
      SELECT 1 
      FROM time_logs ct
      WHERE ct.card_id = st.card_id
        AND ct.user_id = st.user_id
        AND ct.subtask_id IS NULL
        AND ct.end_time IS NULL
  );
```

## 🎯 Best Practices

### For Users:
1. ✅ Always start card tracking first
2. ✅ Then start specific subtask tracking
3. ✅ Stop subtask when done (card keeps running)
4. ✅ Stop card when all work done (cascade stop)

### For Developers:
1. ✅ Validate prerequisite BEFORE creating record
2. ✅ Provide clear, actionable error messages
3. ✅ Log validation passes for debugging
4. ✅ Test with multiple users
5. ✅ Consider UI hints (disable subtask button if no card tracking)

## 🚀 Future Enhancements

### UI Improvements:
- [ ] Disable "Start Tracking" button on subtasks if no card tracking
- [ ] Show tooltip: "Start card tracking first"
- [ ] Auto-scroll to "Start Work" button when validation fails
- [ ] Badge indicator: "Card tracking required"

### Backend Improvements:
- [ ] API endpoint: Check if subtask can be started
- [ ] Notification: Remind user to start card tracking
- [ ] Analytics: Track how often users hit this validation

### Possible Relaxation:
- [ ] Allow subtask tracking if card was tracked before (hasBeenTracked)
- [ ] Config setting: Enable/disable this rule per project

---

**Implemented**: 2025-11-08  
**Version**: 1.0  
**Status**: ✅ Active  
**Documentation**: CONCURRENT_TIME_TRACKING.md (updated)
