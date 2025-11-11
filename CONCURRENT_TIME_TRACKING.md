# 🕐 Concurrent Time Tracking - Implementation Guide

## 📋 Overview

Feature **Concurrent Time Tracking** memungkinkan user untuk melakukan time tracking pada:
- **1 Card** (main task)
- **Multiple Subtasks** dari card yang sama
- **Secara bersamaan** (concurrent/parallel)

### Key Benefits
✅ Lebih fleksibel - bisa track card general + specific subtasks  
✅ Detail tracking - track waktu per subtask dengan presisi  
✅ Auto-cascade - stop card otomatis stop semua subtask  
✅ Visual indicator - jelas melihat semua active timers  

## 🎯 Business Rules

### Rule 1: Tidak Boleh Duplicate Tracking
- ❌ User **TIDAK BISA** start tracking card yang sama 2x
- ❌ User **TIDAK BISA** start tracking subtask yang sama 2x
- ✅ User **BISA** start tracking 1 card + multiple different subtasks

**Example**:
```
User A sedang tracking Card #1
❌ User A tidak bisa start tracking Card #1 lagi
✅ User A bisa start tracking Subtask #1 dari Card #1
✅ User A bisa start tracking Subtask #2 dari Card #1
```

### Rule 2: Single Card Context
- ❌ User **TIDAK BISA** tracking 2 cards berbeda bersamaan
- ✅ User **BISA** tracking 1 card + subtasks dari card yang sama

**Example**:
```
User A sedang tracking Card #1
❌ User A tidak bisa start tracking Card #2 (harus stop Card #1 dulu)
✅ User A bisa start tracking subtasks dari Card #1
```

### Rule 3: Subtask Tracking Prerequisite
- ❌ User **TIDAK BISA** start subtask tracking jika card belum di-tracking
- ✅ User **HARUS** start card tracking terlebih dahulu
- ✅ Setelah card tracking aktif, baru bisa start subtask tracking

**Example**:
```
Scenario A: Card tidak sedang tracking
Action: User tries to start Subtask #1 tracking
Result: ❌ Error "Anda harus memulai tracking card terlebih dahulu sebelum tracking subtask..."

Scenario B: Card sedang tracking
Step 1: User start Card #1 tracking ✅
Step 2: User start Subtask #1 tracking ✅
Result: Both timers running
```

**Rationale**: 
- Subtask adalah bagian dari card, jadi card harus aktif dulu
- Mencegah tracking subtask tanpa context card
- Memastikan work flow yang logis dan terstruktur

### Rule 4: Stop Behavior
**Stop Subtask**:
- Hanya stop subtask itu saja
- Card tracking tetap berjalan
- Subtask lain tetap berjalan

**Stop Card**:
- Stop card tracking
- **CASCADE**: Auto-stop SEMUA subtask tracking dari card tersebut
- Durasi subtask dihitung sampai waktu yang sama dengan card

**Example**:
```
Active: Card #1, Subtask #1, Subtask #2

User stop Subtask #1:
  ✅ Subtask #1 stopped
  ✅ Card #1 masih jalan
  ✅ Subtask #2 masih jalan

User stop Card #1:
  ✅ Card #1 stopped
  ✅ Subtask #2 auto-stopped (cascade)
  ⚠️ Success message: "... (Otomatis menghentikan 1 subtask tracking)"
```

## 🔄 Implementation Flow

### Flow 1: Start Card + Multiple Subtasks
```
1. User navigates to Card detail page
2. Click "Start Work" → Select "Entire Card"
   ✅ Card tracking started (green indicator)
   
3. Scroll to Subtasks section
4. Click subtask #1 → Click "Start Tracking"
   ✅ Check: Card tracking active? YES
   ✅ Subtask #1 tracking started (blue indicator)
   
5. Click subtask #2 → Click "Start Tracking"
   ✅ Check: Card tracking active? YES
   ✅ Subtask #2 tracking started (blue indicator)

Result:
📋 Card tracking: 01:30:00
🎯 Subtask #1 tracking: 00:45:00
🎯 Subtask #2 tracking: 00:30:00
👉 Total: 3 Active Timers
```

### Flow 1B: Try Start Subtask WITHOUT Card Tracking (ERROR)
```
1. User navigates to Card detail page
2. No card tracking active
3. Click subtask #1 → Click "Start Tracking"
   ❌ Check: Card tracking active? NO
   ❌ Error: "Anda harus memulai tracking card terlebih dahulu sebelum tracking subtask..."
   
Result:
⚠️ Subtask tracking not started
💡 User must start card tracking first
```

### Flow 2: Stop Individual Subtask
```
Starting state:
📋 Card tracking: 01:00:00
🎯 Subtask #1 tracking: 00:30:00
🎯 Subtask #2 tracking: 00:20:00

User clicks "Stop Subtask" on Subtask #1:
✅ Subtask #1 stopped (saved 30 minutes)

Current state:
📋 Card tracking: 01:00:00 (masih jalan)
🎯 Subtask #2 tracking: 00:20:00 (masih jalan)
👉 Total: 2 Active Timers
```

### Flow 3: Stop Card (Cascade Stop)
```
Starting state:
📋 Card tracking: 02:00:00
🎯 Subtask #1 tracking: 01:00:00
🎯 Subtask #2 tracking: 00:45:00

User clicks "Stop Card":
✅ Card stopped (saved 2 hours)
✅ Subtask #1 auto-stopped (saved 1 hour)
✅ Subtask #2 auto-stopped (saved 45 minutes)
📝 Message: "Time tracking dihentikan! Durasi: 2 jam 0 menit (Otomatis menghentikan 2 subtask tracking)"

Current state:
👉 Total: 0 Active Timers
📊 All durations saved to database
```

### Flow 4: Start Tracking dengan Existing Timer
```
Scenario A: Try to start duplicate card tracking
Current: Card #1 tracking active
Action: User tries to start Card #1 tracking again
Result: ❌ Error "Anda sudah memiliki timer yang sedang berjalan untuk card ini."

Scenario B: Try to start different card
Current: Card #1 tracking active
Action: User tries to start Card #2 tracking
Result: ❌ Error "Anda masih memiliki timer card lain yang sedang berjalan: "Card #1 Title""

Scenario C: Try to start duplicate subtask
Current: Subtask #1 tracking active
Action: User tries to start Subtask #1 tracking again
Result: ❌ Error "Anda sudah memiliki timer yang sedang berjalan untuk subtask ini."

Scenario D: Start subtask while card tracking
Current: Card #1 tracking active
Action: User starts Subtask #1 tracking
Result: ✅ Success! Both timers running

Scenario E: Start subtask WITHOUT card tracking (NEW)
Current: No card tracking active
Action: User tries to start Subtask #1 tracking
Result: ❌ Error "Anda harus memulai tracking card terlebih dahulu sebelum tracking subtask..."
```

## 💻 Backend Implementation

### TimeLogController - startTracking()

**Validation Logic**:
```php
// Rule 1: Check duplicate tracking (same card/subtask)
$duplicateTracking = TimeLog::where('user_id', $currentUser->id)
    ->whereNull('end_time')
    ->where(function($query) use ($validatedData) {
        if (!empty($validatedData['card_id']) && empty($validatedData['subtask_id'])) {
            // Tracking card, cek apakah card ini sudah ditracking
            $query->where('card_id', $validatedData['card_id'])
                  ->whereNull('subtask_id');
        } elseif (!empty($validatedData['subtask_id'])) {
            // Tracking subtask, cek apakah subtask ini sudah ditracking
            $query->where('subtask_id', $validatedData['subtask_id']);
        }
    })
    ->first();

if ($duplicateTracking) {
    return redirect()->back()->with('error', '...');
}

// Rule 2: Check different card tracking
if (empty($validatedData['subtask_id'])) {
    $otherCardTracking = TimeLog::where('user_id', $currentUser->id)
        ->whereNull('end_time')
        ->whereNull('subtask_id')
        ->where('card_id', '!=', $validatedData['card_id'])
        ->first();
    
    if ($otherCardTracking) {
        return redirect()->back()->with('error', '...');
    }
}

// Rule 3: Subtask tracking prerequisite - Card MUST be tracking
if (!empty($validatedData['subtask_id'])) {
    // User mau start subtask tracking
    // Cek apakah card ini sedang di-tracking oleh user yang sama
    $cardTracking = TimeLog::where('card_id', $validatedData['card_id'])
        ->where('user_id', $currentUser->id)
        ->whereNull('subtask_id')  // Hanya card tracking
        ->whereNull('end_time')     // Yang masih ongoing
        ->first();

    if (!$cardTracking) {
        return redirect()->back()->with('error', 
            'Anda harus memulai tracking card terlebih dahulu sebelum tracking subtask...');
    }
}
```

### TimeLogController - stopTracking()

**Cascade Logic**:
```php
// Only cascade if stopping CARD tracking (not subtask)
if (is_null($timeLog->subtask_id)) {
    // This is CARD tracking
    // Find all ongoing subtask tracking for this card
    $ongoingSubtaskTimeLogs = TimeLog::where('card_id', $timeLog->card_id)
        ->whereNotNull('subtask_id')
        ->whereNull('end_time')
        ->where('user_id', $currentUser->id)
        ->get();

    foreach ($ongoingSubtaskTimeLogs as $subtaskLog) {
        $subtaskLog->update([
            'end_time' => $endTime,  // Same end_time as card
            'duration_minutes' => $calculated
        ]);
        $stoppedSubtasksCount++;
    }
} else {
    // This is SUBTASK tracking - NO CASCADE
    Log::info('Stopping subtask tracking (no cascade)');
}
```

### CardController - show()

**Data Preparation**:
```php
public function show(Card $card)
{
    $currentUserId = Auth::id();
    
    // Get ongoing card tracking
    $ongoingCardTracking = $card->timeLogs()
        ->where('user_id', $currentUserId)
        ->whereNull('subtask_id')
        ->whereNull('end_time')
        ->first();

    // Get ongoing subtask trackings
    $ongoingSubtaskTrackings = TimeLog::where('card_id', $card->id)
        ->where('user_id', $currentUserId)
        ->whereNotNull('subtask_id')
        ->whereNull('end_time')
        ->with('subtask')
        ->get();

    // Total active timers
    $activeTimersCount = ($ongoingCardTracking ? 1 : 0) + $ongoingSubtaskTrackings->count();

    return view('cards.show', compact(
        'card',
        'ongoingCardTracking',
        'ongoingSubtaskTrackings',
        'activeTimersCount'
    ));
}
```

## 🎨 Frontend Implementation

### Visual Indicators

**Active Timers Badge**:
```blade
@if($activeTimersCount > 0)
    <div class="flex items-center space-x-2 px-3 py-1 bg-green-100 border border-green-200 rounded-lg">
        <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
        <span class="text-xs font-medium text-green-700">
            {{ $activeTimersCount }} Active Timer{{ $activeTimersCount > 1 ? 's' : '' }}
        </span>
    </div>
@endif
```

**Card Tracking Display**:
```blade
@if($ongoingCardTracking && $ongoingCardTracking->card_id === $card->id)
    <div class="bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-green-200 rounded-lg p-4">
        <!-- Animated pulse icon (green) -->
        <!-- Live timer -->
        <!-- Stop button with cascade warning -->
        <button onclick="return confirm('Stop card tracking? (This will also stop all related subtask tracking)')">
            Stop Card
        </button>
    </div>
@endif
```

**Subtask Tracking Display**:
```blade
@foreach($ongoingSubtaskTrackings as $subtaskTracking)
    @if($subtaskTracking->card_id === $card->id)
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-lg p-4">
            <!-- Animated pulse icon (blue) -->
            <!-- Live timer -->
            <!-- Stop button (no cascade) -->
            <button onclick="return confirm('Stop subtask tracking?')">
                Stop Subtask
            </button>
        </div>
    @endif
@endforeach
```

### Color Scheme
- **Card tracking**: 🟢 Green (primary task)
- **Subtask tracking**: 🔵 Blue (detailed work)
- **Badge**: 🟢 Green with pulse animation

## 🧪 Testing Scenarios

### Test 1: Basic Concurrent Tracking
```
✅ Prerequisites:
- Logged in as Developer/Designer
- Navigate to card with 2+ subtasks

✅ Steps:
1. Start card tracking
   Expected: Green timer appears
2. Open subtask #1 → Start tracking
   Expected: Blue timer appears
3. Open subtask #2 → Start tracking
   Expected: Another blue timer appears
4. Check badge
   Expected: "3 Active Timers"

✅ Verification:
SELECT * FROM time_logs 
WHERE user_id = ? AND end_time IS NULL;
-- Should show 3 rows (1 card + 2 subtasks)
```

### Test 2: Duplicate Prevention
```
✅ Prerequisites:
- Card tracking active

✅ Steps:
1. Try to start card tracking again
   Expected: Error "Anda sudah memiliki timer yang sedang berjalan untuk card ini."
2. Try to start different card
   Expected: Error "Anda masih memiliki timer card lain yang sedang berjalan..."

✅ Verification:
- No new time_logs created
- Error message displayed
```

### Test 2B: Subtask Prerequisite Validation (NEW)
```
✅ Prerequisites:
- No card tracking active
- Card has subtasks

✅ Steps:
1. Navigate to card detail
2. Click subtask → Click "Start Tracking"
   Expected: Error "Anda harus memulai tracking card terlebih dahulu..."
3. Start card tracking
   Expected: Success
4. Click subtask → Click "Start Tracking"
   Expected: Success! Subtask timer started

✅ Verification:
SELECT * FROM time_logs 
WHERE user_id = ? AND end_time IS NULL;
-- Should show 2 rows (1 card + 1 subtask)
-- card_id same for both rows
-- subtask_id NULL for card, NOT NULL for subtask
```

### Test 3: Individual Subtask Stop
```
✅ Prerequisites:
- Card tracking: 01:00:00
- Subtask #1 tracking: 00:30:00
- Subtask #2 tracking: 00:20:00

✅ Steps:
1. Stop Subtask #1
   Expected: Subtask #1 timer removed
2. Check remaining timers
   Expected: Card + Subtask #2 still running

✅ Verification:
SELECT * FROM time_logs WHERE id = ?;
-- Subtask #1: end_time NOT NULL, duration_minutes ~30
-- Card: end_time IS NULL
-- Subtask #2: end_time IS NULL
```

### Test 4: Cascade Stop
```
✅ Prerequisites:
- Card tracking: 02:00:00
- Subtask #1 tracking: 01:00:00
- Subtask #2 tracking: 00:45:00

✅ Steps:
1. Stop card tracking
   Expected: Confirm dialog mentions cascade
2. Confirm stop
   Expected: All timers removed
3. Check success message
   Expected: "... (Otomatis menghentikan 2 subtask tracking)"

✅ Verification:
SELECT * FROM time_logs 
WHERE card_id = ? AND user_id = ?
ORDER BY end_time DESC LIMIT 3;
-- All 3 rows: end_time NOT NULL
-- All 3 rows: end_time within 1 second (same stop time)
```

### Test 5: Database Consistency
```
✅ After all tests, verify:

-- No orphaned ongoing timers
SELECT * FROM time_logs 
WHERE end_time IS NULL;
-- Should be 0 or only expected active timers

-- Duration calculated correctly
SELECT 
    id,
    TIMESTAMPDIFF(MINUTE, start_time, end_time) as calculated,
    duration_minutes as stored
FROM time_logs
WHERE end_time IS NOT NULL
HAVING calculated != stored;
-- Should be empty (all matches)

-- Cascade stop consistency
SELECT card_id, COUNT(*) as count
FROM time_logs
WHERE user_id = ?
  AND end_time IS NOT NULL
  AND DATE(end_time) = CURDATE()
GROUP BY card_id, end_time
HAVING count > 1;
-- Shows cards with multiple simultaneous stops (cascade events)
```

## 🐛 Debugging Guide

### Issue: Timer tidak bisa di-start
**Symptoms**: Error "Anda masih memiliki timer yang sedang berjalan..."

**Debug**:
```sql
-- Check ongoing timers
SELECT 
    tl.*,
    c.card_title,
    s.subtask_name
FROM time_logs tl
LEFT JOIN cards c ON tl.card_id = c.id
LEFT JOIN subtasks s ON tl.subtask_id = s.id
WHERE tl.user_id = ?
  AND tl.end_time IS NULL;
```

**Solution**:
- Stop timer yang masih aktif via UI
- Atau force stop via SQL (development only):
```sql
UPDATE time_logs
SET end_time = NOW(),
    duration_minutes = TIMESTAMPDIFF(MINUTE, start_time, NOW())
WHERE user_id = ? AND end_time IS NULL;
```

### Issue: Cascade stop tidak bekerja
**Symptoms**: Stop card tapi subtask masih tracking

**Debug**:
```php
// Check TimeLogController stopTracking() line ~280
Log::info('Cascade check', [
    'timeLog_subtask_id' => $timeLog->subtask_id,
    'is_card_tracking' => is_null($timeLog->subtask_id),
    'ongoing_subtasks_count' => $ongoingSubtaskTimeLogs->count()
]);
```

**Verification**:
```sql
-- Check if cascade executed
SELECT * FROM time_logs
WHERE card_id = ?
  AND user_id = ?
  AND ABS(TIMESTAMPDIFF(SECOND, 
      (SELECT end_time FROM time_logs WHERE id = ? LIMIT 1),
      end_time
  )) < 2;
-- Should show card + all subtasks with same end_time
```

### Issue: UI tidak menampilkan concurrent timers
**Symptoms**: Badge shows wrong count atau timers tidak muncul

**Debug**:
```blade
{{-- Add to view for debugging --}}
<div class="bg-yellow-100 p-4">
    <p>Debug Info:</p>
    <p>Card tracking: {{ $ongoingCardTracking ? 'YES' : 'NO' }}</p>
    <p>Subtask trackings: {{ $ongoingSubtaskTrackings->count() }}</p>
    <p>Active timers count: {{ $activeTimersCount }}</p>
</div>
```

**Check controller**:
```php
// In CardController show(), add:
\Log::debug('Card show data', [
    'card_id' => $card->id,
    'ongoing_card' => $ongoingCardTracking ? $ongoingCardTracking->id : null,
    'ongoing_subtasks' => $ongoingSubtaskTrackings->pluck('id'),
    'active_count' => $activeTimersCount
]);
```

## 📊 Database Schema

### time_logs Table
```sql
CREATE TABLE time_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    card_id BIGINT NOT NULL,          -- Always present (even for subtask tracking)
    subtask_id BIGINT NULL,            -- NULL = card tracking, NOT NULL = subtask tracking
    user_id BIGINT NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NULL,            -- NULL = still ongoing
    duration_minutes INT DEFAULT 0,
    description TEXT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (card_id) REFERENCES cards(id) ON DELETE CASCADE,
    FOREIGN KEY (subtask_id) REFERENCES subtasks(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    INDEX idx_ongoing (user_id, end_time),
    INDEX idx_card_tracking (card_id, user_id, end_time)
);
```

### Query Patterns

**Get all active timers for user**:
```sql
SELECT 
    tl.*,
    c.card_title,
    s.subtask_name,
    CASE 
        WHEN tl.subtask_id IS NULL THEN 'card'
        ELSE 'subtask'
    END as tracking_type
FROM time_logs tl
JOIN cards c ON tl.card_id = c.id
LEFT JOIN subtasks s ON tl.subtask_id = s.id
WHERE tl.user_id = ?
  AND tl.end_time IS NULL
ORDER BY tl.start_time ASC;
```

**Get active timers for specific card**:
```sql
SELECT *
FROM time_logs
WHERE card_id = ?
  AND user_id = ?
  AND end_time IS NULL
ORDER BY 
    CASE WHEN subtask_id IS NULL THEN 0 ELSE 1 END,  -- Card first, then subtasks
    start_time ASC;
```

## 📝 Files Modified

### Backend
1. **app/Http/Controllers/web/TimeLogController.php**
   - `startTracking()`: Added concurrent validation (lines ~80-140)
   - `stopTracking()`: Added conditional cascade logic (lines ~280-325)

2. **app/Http/Controllers/web/CardController.php**
   - `show()`: Added concurrent tracking data preparation (lines ~262-300)

### Frontend
3. **resources/views/cards/show.blade.php**
   - Added concurrent tracking indicators (lines ~230-240)
   - Updated timer display for card tracking (lines ~245-330)
   - Added subtask tracking display loop (lines ~335-425)
   - Updated PHP data preparation block (lines ~190-230)

## 🎯 Best Practices

### For Users
1. ✅ Start card tracking untuk general work
2. ✅ Start subtask tracking untuk detailed tasks
3. ✅ Stop subtask saat selesai (card tetap jalan)
4. ✅ Stop card saat semua work selesai (auto-cascade)

### For Developers
1. ✅ Always check `subtask_id IS NULL` untuk identify card tracking
2. ✅ Use consistent `end_time` untuk cascade stop
3. ✅ Log cascade events untuk audit trail
4. ✅ Test dengan multiple concurrent timers
5. ✅ Handle edge cases (orphaned timers, duplicate starts)

## 🚀 Future Enhancements

### Possible Features
- [ ] Pause/Resume functionality
- [ ] Timer sync across devices (WebSocket)
- [ ] Time tracking reports per day/week
- [ ] Notification when timer exceeds estimated hours
- [ ] Export time logs to CSV/PDF
- [ ] Team time tracking dashboard

---

**Last Updated**: 2025-11-08  
**Version**: 1.0  
**Status**: ✅ Implemented & Documented
