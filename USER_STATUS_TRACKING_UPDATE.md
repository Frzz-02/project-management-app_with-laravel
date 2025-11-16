# User Status Tracking Update

## 📋 Overview
Fitur baru untuk auto-update `current_task_status` di tabel `users` saat user start/stop time tracking pada card.

## 🎯 Tujuan
Memberikan indikator real-time status user apakah sedang bekerja (`working`) atau tidak (`idle`) berdasarkan time tracking yang aktif.

---

## ✨ Fitur yang Ditambahkan

### 1. **Auto-Update Status ke "WORKING"**
**Location**: `TimeLogController@startTracking()`

**Trigger**: Saat user START tracking CARD (bukan subtask)

**Logic**:
```php
if (empty($validatedData['subtask_id'])) {
    // User start tracking card (bukan subtask)
    DB::table('users')
        ->where('id', $currentUser->id)
        ->update(['current_task_status' => 'working']);
}
```

**Behavior**:
- ✅ Start tracking **CARD** → Status jadi `working`
- ❌ Start tracking **SUBTASK** → Status TIDAK berubah (tetap `working` dari card)
- 📝 Logging aktivitas ke Laravel Log

**Contoh Skenario**:
```
1. User idle (current_task_status = 'idle')
2. User klik "Start Task" pada Card #123
3. ✅ Time tracking dimulai
4. ✅ Status user otomatis jadi 'working'
5. User bisa start subtask tracking (status tetap 'working')
```

---

### 2. **Auto-Update Status ke "IDLE"**
**Location**: `TimeLogController@stopTracking()`

**Trigger**: Saat user STOP tracking CARD (bukan subtask)

**Logic dengan Validasi**:
```php
if (is_null($timeLog->subtask_id)) {
    // Cek apakah user masih punya card lain yang sedang di-track
    $otherOngoingCardTracking = TimeLog::where('user_id', $currentUser->id)
        ->whereNull('end_time')           // Masih ongoing
        ->whereNull('subtask_id')         // Card tracking (bukan subtask)
        ->where('id', '!=', $timeLog->id) // Selain yang baru di-stop
        ->exists();

    if (!$otherOngoingCardTracking) {
        // Tidak ada card lain yang di-track, set ke idle
        DB::table('users')
            ->where('id', $currentUser->id)
            ->update(['current_task_status' => 'idle']);
    }
}
```

**Behavior**:
- ✅ Stop tracking **CARD** + tidak ada card lain yang aktif → Status jadi `idle`
- ⚠️ Stop tracking **CARD** + masih ada card lain yang aktif → Status tetap `working`
- ❌ Stop tracking **SUBTASK** → Status TIDAK berubah
- 📝 Logging aktivitas ke Laravel Log

**Contoh Skenario A** (Single Card):
```
1. User sedang tracking Card #123 (current_task_status = 'working')
2. User klik "Pause Task" pada Card #123
3. ✅ Time tracking dihentikan
4. ✅ Cek: tidak ada card lain yang aktif
5. ✅ Status user otomatis jadi 'idle'
```

**Contoh Skenario B** (Multiple Cards):
```
1. User tracking Card #123 (current_task_status = 'working')
2. User juga tracking Card #456 (concurrent tracking diperbolehkan)
3. User klik "Pause Task" pada Card #123
4. ✅ Time tracking Card #123 dihentikan
5. ⚠️ Cek: masih ada Card #456 yang aktif
6. ✅ Status user TETAP 'working' (tidak jadi idle)
```

---

## 🔍 Technical Details

### Database Column
**Tabel**: `users`  
**Column**: `current_task_status`  
**Type**: `ENUM('idle', 'working')`  
**Default**: `'idle'`

### Update Method
Menggunakan **DB::table()** instead of **Eloquent update()** untuk menghindari masalah dengan Auth::user() proxy:

```php
// ✅ CORRECT (menggunakan DB::table)
DB::table('users')
    ->where('id', $currentUser->id)
    ->update(['current_task_status' => 'working']);

// ❌ WRONG (error: Undefined method 'update')
$currentUser->update([
    'current_task_status' => 'working'
]);
```

### Logging
Semua update status user di-log menggunakan `Log::info()`:

**Start Tracking**:
```php
Log::info('User status updated to working', [
    'user_id' => $currentUser->id,
    'card_id' => $validatedData['card_id']
]);
```

**Stop Tracking**:
```php
Log::info('User status updated to idle', [
    'user_id' => $currentUser->id,
    'stopped_card_id' => $timeLog->card_id
]);

// Atau jika masih ada card lain:
Log::info('User status remains working - other card tracking ongoing', [
    'user_id' => $currentUser->id,
    'stopped_card_id' => $timeLog->card_id
]);
```

---

## 📊 Use Cases

### 1. **Admin Dashboard - Team Status**
Admin bisa melihat member mana saja yang sedang `working`:

```php
$workingMembers = User::where('current_task_status', 'working')->get();
```

### 2. **Team Leader - Monitor Activity**
Team Leader bisa track tim yang sedang aktif:

```php
$activeTeam = $project->members()
    ->where('current_task_status', 'working')
    ->with('currentTimeLogs')
    ->get();
```

### 3. **Member Dashboard - Show Status Badge**
Member bisa lihat status mereka sendiri:

```blade
@if(auth()->user()->current_task_status === 'working')
    <span class="badge badge-success">🟢 Working</span>
@else
    <span class="badge badge-secondary">⚪ Idle</span>
@endif
```

### 4. **Real-time Status Updates**
Bisa diintegrasikan dengan Laravel Echo/Pusher untuk live updates:

```javascript
Echo.channel('team-status')
    .listen('UserStatusChanged', (e) => {
        updateUserStatusBadge(e.userId, e.status);
    });
```

---

## ⚡ Edge Cases Handled

### 1. **Concurrent Card Tracking**
✅ **Scenario**: User tracking 2 cards bersamaan  
✅ **Behavior**: Status tetap `working` sampai SEMUA card di-stop

### 2. **Subtask Tracking Only**
✅ **Scenario**: User start/stop subtask tracking  
✅ **Behavior**: Status user TIDAK berubah (tetap mengikuti card tracking)

### 3. **Mixed Card + Subtask Tracking**
✅ **Scenario**: User tracking Card A + Subtask B (dari Card A)  
✅ **Behavior**: 
- Start Card A → `working`
- Start Subtask B → tetap `working`
- Stop Subtask B → tetap `working`
- Stop Card A → `idle` (jika tidak ada card lain)

### 4. **System Crash/Force Stop**
⚠️ **Scenario**: Server crash saat user sedang tracking  
⚠️ **Issue**: Status user bisa stuck di `working`  
✅ **Solution**: Perlu cron job untuk auto-reset status idle jika time log > 12 jam tanpa update

**Suggested Cron Job**:
```php
// app/Console/Commands/ResetStuckWorkingStatus.php
TimeLog::whereNull('end_time')
    ->where('start_time', '<', now()->subHours(12))
    ->get()
    ->each(function($log) {
        DB::table('users')
            ->where('id', $log->user_id)
            ->update(['current_task_status' => 'idle']);
        
        Log::warning('Force reset user to idle - stuck tracking', [
            'user_id' => $log->user_id,
            'time_log_id' => $log->id
        ]);
    });
```

---

## 🧪 Testing Checklist

### Manual Testing

**Test 1: Basic Start/Stop Card**
- [ ] User idle → Start tracking card → Status jadi `working`
- [ ] User working → Stop tracking card → Status jadi `idle`

**Test 2: Subtask Tracking**
- [ ] User idle → Start card → Status `working`
- [ ] Start subtask → Status tetap `working`
- [ ] Stop subtask → Status tetap `working`
- [ ] Stop card → Status jadi `idle`

**Test 3: Concurrent Cards**
- [ ] Start Card A → Status `working`
- [ ] Start Card B → Status tetap `working`
- [ ] Stop Card A → Status tetap `working` (Card B masih aktif)
- [ ] Stop Card B → Status jadi `idle`

**Test 4: Multiple Users**
- [ ] User A start tracking → User A status `working`
- [ ] User B start tracking → User B status `working`
- [ ] User A stop → User A status `idle`, User B tetap `working`

### Database Validation

```sql
-- Check user status distribution
SELECT current_task_status, COUNT(*) as count
FROM users
GROUP BY current_task_status;

-- Find users with active tracking but status idle (inconsistency)
SELECT u.id, u.username, u.current_task_status, COUNT(tl.id) as active_logs
FROM users u
LEFT JOIN time_logs tl ON tl.user_id = u.id AND tl.end_time IS NULL
WHERE u.current_task_status = 'idle'
GROUP BY u.id
HAVING active_logs > 0;
```

---

## 📝 Code Changes Summary

### Modified Files
1. **TimeLogController.php** (2 methods updated)
   - `startTracking()` - Added user status update to "working"
   - `stopTracking()` - Added user status update to "idle" with validation

### Lines Changed
- **startTracking()**: ~15 lines added (after line 235)
- **stopTracking()**: ~30 lines added (after line 440)

### Dependencies
- ✅ No new dependencies required
- ✅ Uses existing `DB` facade (already imported)
- ✅ Uses existing `Log` facade (already imported)

---

## 🚀 Deployment Notes

### Pre-Deployment
1. ✅ Verify `current_task_status` column exists in `users` table
2. ✅ Set default value to `'idle'` for existing users:
   ```sql
   UPDATE users SET current_task_status = 'idle' WHERE current_task_status IS NULL;
   ```

### Post-Deployment
1. ✅ Test dengan 2-3 user berbeda
2. ✅ Monitor Laravel logs untuk "User status updated" messages
3. ✅ Check database untuk inconsistencies (query di atas)
4. ✅ Setup cron job untuk reset stuck status (optional but recommended)

### Rollback Plan
Jika ada masalah, cukup comment out bagian update status:

```php
// ROLLBACK: Comment this block if needed
// DB::table('users')
//     ->where('id', $currentUser->id)
//     ->update(['current_task_status' => 'working']);
```

---

## 📚 Related Documentation
- [API_TIME_TRACKING_DOCUMENTATION.md](./API_TIME_TRACKING_DOCUMENTATION.md)
- [CONCURRENT_TIME_TRACKING.md](./CONCURRENT_TIME_TRACKING.md)
- [CONDITIONAL_TRACKING_UI.md](./CONDITIONAL_TRACKING_UI.md)

---

## ✅ Completion Status

**Date**: November 16, 2025  
**Developer**: GitHub Copilot  
**Status**: ✅ **COMPLETED**

**Changes**:
- ✅ Start tracking → Update user status to "working"
- ✅ Stop tracking → Update user status to "idle" (with validation)
- ✅ Subtask tracking → No status change (follows card)
- ✅ Concurrent tracking → Status validation implemented
- ✅ Logging → All status changes logged
- ✅ Edge cases → Handled properly
- ✅ Documentation → Complete

**Ready for Testing**: YES 🚀
