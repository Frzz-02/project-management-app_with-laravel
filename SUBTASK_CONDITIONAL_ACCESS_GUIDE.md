# Fitur Subtask Conditional Access - Implementation Guide

## 📝 Overview

Implementasi fitur dimana:
1. **Subtask CRUD & tracking** hanya bisa diakses **SETELAH card sudah di-start tracking**
2. **Setelah card di-stop**, subtask tidak bisa diakses lagi
3. **Auto-stop cascade**: Ketika stop card tracking dengan subtask tracking masih jalan, otomatis stop keduanya

---

## ✅ Changes Made

### 1. **Web TimeLogController** - Auto-Stop Cascade

#### File: `app/Http/Controllers/web/TimeLogController.php`

**Added Logic in `stopTracking()` method:**

```php
// AUTO-STOP SUBTASK TRACKING (CASCADE)
// Jika ada subtask tracking yang masih berjalan untuk card ini,
// stop otomatis sebelum stop card tracking
$ongoingSubtaskTimeLogs = TimeLog::where('card_id', $timeLog->card_id)
    ->whereNotNull('subtask_id')  // Hanya subtask tracking
    ->whereNull('end_time')        // Yang masih berjalan
    ->where('user_id', $currentUser->id)
    ->get();

$stoppedSubtasksCount = 0;
$endTime = Carbon::now('Asia/Jakarta');

foreach ($ongoingSubtaskTimeLogs as $subtaskLog) {
    // Stop subtask tracking
    $subtaskDuration = $subtaskLog->start_time->diffInMinutes($endTime);
    
    $subtaskLog->update([
        'end_time' => $endTime,
        'duration_minutes' => $subtaskDuration,
    ]);

    $stoppedSubtasksCount++;
}

// Success message dengan info subtask yang di-stop (jika ada)
$successMessage = "Time tracking dihentikan! Durasi: {$formattedDuration}";
if ($stoppedSubtasksCount > 0) {
    $successMessage .= " (Otomatis menghentikan {$stoppedSubtasksCount} subtask tracking)";
}
```

**What happens:**
- Sebelum stop card tracking, cari semua subtask tracking yang masih jalan
- Stop semua subtask tracking dengan `end_time` yang sama
- Calculate duration untuk setiap subtask
- Tampilkan info berapa subtask yang di-stop di success message

---

### 2. **Card Model** - Helper Methods

#### File: `app/Models/Card.php`

**Added 3 helper methods:**

```php
/**
 * Cek apakah card ini sudah pernah di-start tracking (punya completed time log)
 * 
 * @return bool
 */
public function hasBeenTracked(): bool
{
    return $this->timeLogs()->whereNotNull('end_time')->exists();
}

/**
 * Cek apakah card ini sedang di-track (punya ongoing time log)
 * 
 * @return bool
 */
public function isBeingTracked(): bool
{
    return $this->timeLogs()->whereNull('end_time')->exists();
}

/**
 * Get ongoing time log untuk card ini
 * 
 * @return \App\Models\TimeLog|null
 */
public function getOngoingTimeLog()
{
    return $this->timeLogs()->whereNull('end_time')->first();
}
```

**Usage:**
```php
// Di Blade view
@if($card->isBeingTracked())
    // Card sedang di-track, tampilkan subtask features
@elseif($card->hasBeenTracked())
    // Card pernah di-track tapi sudah stop, disable subtask features
@else
    // Card belum pernah di-track
@endif
```

---

### 3. **Blade View** - Conditional Access Implementation

#### File: `resources/views/cards/show.blade.php`

**Implementation Steps:**

#### A. Wrap Subtask Section dengan Conditional

```blade
{{-- SUBTASK SECTION - Hanya tampil jika card sedang di-track --}}
@if($card->isBeingTracked())
    {{-- Subtask CRUD & Tracking Features --}}
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">Subtasks</h2>
        
        {{-- Add Subtask Button --}}
        <button type="button" @click="showAddSubtaskForm = true">
            Add Subtask
        </button>
        
        {{-- Subtask List dengan Edit/Delete/Track --}}
        @foreach($card->subtasks as $subtask)
            <div class="subtask-item">
                {{-- Subtask content --}}
                {{-- Edit, Delete, Start Tracking buttons --}}
            </div>
        @endforeach
    </div>

@elseif($card->hasBeenTracked())
    {{-- Card sudah pernah di-track tapi sudah stop --}}
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
        <div class="flex items-center">
            <svg class="w-5 h-5 text-yellow-600 mr-2">...</svg>
            <p class="text-sm text-yellow-800">
                <strong>Subtask features disabled.</strong> 
                Card tracking telah dihentikan. Start tracking card lagi untuk mengakses subtask.
            </p>
        </div>
    </div>
    
    {{-- Tampilkan read-only subtask list --}}
    @if($card->subtasks->count() > 0)
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Subtasks (Read-Only)</h2>
            @foreach($card->subtasks as $subtask)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg mb-2">
                    <span class="text-gray-700">{{ $subtask->subtask_name }}</span>
                    <span class="text-sm px-2 py-1 rounded {{ $subtask->status === 'done' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                        {{ $subtask->status }}
                    </span>
                </div>
            @endforeach
        </div>
    @endif

@else
    {{-- Card belum pernah di-track --}}
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <div class="flex items-center">
            <svg class="w-5 h-5 text-blue-600 mr-2">...</svg>
            <p class="text-sm text-blue-800">
                <strong>Start card tracking first.</strong> 
                Subtask features akan tersedia setelah Anda memulai tracking untuk card ini.
            </p>
        </div>
    </div>
@endif
```

#### B. Update Alpine.js Data

```blade
<div x-data="{
    // Existing data
    showAddSubtaskForm: {{ $card->isBeingTracked() ? 'false' : 'false' }},
    editingSubtask: null,
    
    // Helper untuk cek akses
    canAccessSubtasks: {{ $card->isBeingTracked() ? 'true' : 'false' }},
    
    // Method untuk prevent action jika disabled
    checkSubtaskAccess() {
        if (!this.canAccessSubtasks) {
            alert('Please start card tracking first to access subtask features.');
            return false;
        }
        return true;
    }
}">
```

#### C. Update Start Tracking Form - Disable Subtask Option

```blade
{{-- Start Tracking Form --}}
<form method="POST" action="{{ route('time-logs.start') }}">
    @csrf
    <input type="hidden" name="card_id" value="{{ $card->id }}">
    
    {{-- Tracking Type Selection --}}
    <div class="mb-4">
        <label class="flex items-center mb-2">
            <input type="radio" name="tracking_type" value="card" checked>
            <span class="ml-2">Track Entire Card</span>
        </label>
        
        {{-- Subtask option - Only show if card is being tracked AND has subtasks --}}
        @if($card->isBeingTracked() && $card->subtasks->count() > 0)
            <label class="flex items-center">
                <input type="radio" name="tracking_type" value="subtask">
                <span class="ml-2">Track Specific Subtask</span>
            </label>
            
            {{-- Subtask dropdown --}}
            <select name="subtask_id" x-show="trackingType === 'subtask'">
                <option value="">Select Subtask</option>
                @foreach($card->subtasks as $subtask)
                    <option value="{{ $subtask->id }}">{{ $subtask->subtask_name }}</option>
                @endforeach
            </select>
        @endif
    </div>
    
    <button type="submit">Start Tracking</button>
</form>
```

---

## 🔄 Complete Flow Examples

### Scenario 1: First Time User Visits Card

**State:** Card belum pernah di-track

**UI Display:**
```
┌─────────────────────────────────────────┐
│ ℹ️  Start card tracking first.         │
│ Subtask features akan tersedia          │
│ setelah Anda memulai tracking.          │
└─────────────────────────────────────────┘

[Start Tracking Button]  ← Only card tracking available
```

**Available Actions:**
- ✅ Start card tracking
- ❌ Add subtask (disabled)
- ❌ Edit subtask (disabled)
- ❌ Delete subtask (disabled)
- ❌ Start subtask tracking (disabled)

---

### Scenario 2: Card Tracking Started

**State:** `$card->isBeingTracked() === true`

**User Action:**
1. Click "Start Tracking"
2. Card tracking dimulai

**UI Display:**
```
⏱️ Timer Running: 00:15:30
[Stop Tracking Button]

┌─────────────────────────────────────────┐
│ Subtasks                          [+Add]│
├─────────────────────────────────────────┤
│ ☐ Setup database         [Edit][Delete] │
│   [Start Tracking]                       │
│                                          │
│ ☐ Create API endpoints   [Edit][Delete] │
│   [Start Tracking]                       │
└─────────────────────────────────────────┘
```

**Available Actions:**
- ✅ Stop card tracking
- ✅ Add subtask
- ✅ Edit subtask
- ✅ Delete subtask
- ✅ Start subtask tracking
- ✅ Start tracking untuk subtask tertentu

---

### Scenario 3: Card Tracking Stopped (Subtask Still Running)

**State:** User stop card tracking, tapi ada subtask tracking yang masih jalan

**User Action:**
1. Click "Stop Tracking" pada card
2. **AUTO-CASCADE** terjadi

**Backend Process:**
```php
// 1. Find ongoing subtask tracking
$ongoingSubtaskTimeLogs = TimeLog::where('card_id', $card_id)
    ->whereNotNull('subtask_id')
    ->whereNull('end_time')
    ->get();

// 2. Stop all ongoing subtask tracking
foreach ($ongoingSubtaskTimeLogs as $subtaskLog) {
    $subtaskLog->update([
        'end_time' => now(),
        'duration_minutes' => calculated
    ]);
}

// 3. Stop card tracking
$cardTimeLog->update([
    'end_time' => now(),
    'duration_minutes' => calculated
]);
```

**Success Message:**
```
✅ Time tracking dihentikan! Durasi: 2 jam 30 menit
   (Otomatis menghentikan 1 subtask tracking)
```

**UI Display After:**
```
⚠️ Subtask features disabled.
   Card tracking telah dihentikan.
   Start tracking card lagi untuk mengakses subtask.

┌─────────────────────────────────────────┐
│ Subtasks (Read-Only)                    │
├─────────────────────────────────────────┤
│ ✓ Setup database          [done]        │
│ ☐ Create API endpoints    [to do]       │
└─────────────────────────────────────────┘

[Start Tracking Again]  ← Can restart tracking
```

**Available Actions:**
- ✅ Start card tracking again
- ❌ Add subtask (disabled)
- ❌ Edit subtask (disabled)
- ❌ Delete subtask (disabled)
- ❌ Start subtask tracking (disabled)

---

### Scenario 4: Restart Card Tracking

**State:** Card pernah di-track, lalu di-stop, sekarang mau start lagi

**User Action:**
1. Click "Start Tracking" lagi

**UI Display:**
```
⏱️ Timer Running: 00:00:10  ← New session
[Stop Tracking Button]

┌─────────────────────────────────────────┐
│ Subtasks                          [+Add]│ ← Features enabled again!
├─────────────────────────────────────────┤
│ ✓ Setup database          [Edit][Delete]│
│   Status: done                           │
│                                          │
│ ☐ Create API endpoints    [Edit][Delete]│
│   [Start Tracking]                       │
└─────────────────────────────────────────┘
```

**Available Actions:**
- ✅ All subtask features enabled again
- ✅ Can add/edit/delete/track subtasks

---

## 📊 Database Impact

### On Card Tracking Start:

```sql
-- time_logs
INSERT INTO time_logs 
VALUES (card_id = 5, subtask_id = NULL, start_time = now(), end_time = NULL);

-- card_assignments (if exists)
UPDATE card_assignments 
SET started_at = now(), assignment_status = 'in progress'
WHERE card_id = 5 AND started_at IS NULL;

-- cards
UPDATE cards 
SET status = 'in progress' 
WHERE id = 5;
```

### On Card Tracking Stop (with ongoing subtask):

```sql
-- 1. Stop ongoing subtask tracking first (CASCADE)
UPDATE time_logs 
SET end_time = now(), duration_minutes = 45
WHERE card_id = 5 AND subtask_id = 2 AND end_time IS NULL;

-- 2. Stop card tracking
UPDATE time_logs 
SET end_time = now(), duration_minutes = 150
WHERE id = 1;

-- 3. Update actual_hours di card
UPDATE cards 
SET actual_hours = (150 + 45) / 60  -- 3.25 hours
WHERE id = 5;

-- 4. Update card_assignments
UPDATE card_assignments 
SET completed_at = now(), assignment_status = 'completed'
WHERE card_id = 5;
```

---

## 🎨 UI/UX States

### State 1: Not Tracked Yet
```
┌───────────────────────────────────────┐
│ ℹ️ Info Box                            │
│ "Start card tracking first"           │
└───────────────────────────────────────┘
[Start Tracking Button]
```

### State 2: Currently Tracking
```
┌───────────────────────────────────────┐
│ ⏱️ Timer: 00:15:30                     │
│ [Stop Tracking]                        │
└───────────────────────────────────────┘

┌───────────────────────────────────────┐
│ Subtasks                        [+Add]│
│ • Subtask 1  [Edit][Delete][Track]    │
│ • Subtask 2  [Edit][Delete][Track]    │
└───────────────────────────────────────┘
```

### State 3: Stopped (Previously Tracked)
```
┌───────────────────────────────────────┐
│ ⚠️ Warning Box                         │
│ "Subtask features disabled"           │
│ "Start tracking again to enable"      │
└───────────────────────────────────────┘

┌───────────────────────────────────────┐
│ Subtasks (Read-Only)                  │
│ • Subtask 1  [done]   👁️              │
│ • Subtask 2  [to do]  👁️              │
└───────────────────────────────────────┘

[Start Tracking Again]
```

---

## 🧪 Testing Checklist

### Test 1: Initial State
- [ ] Visit card yang belum pernah di-track
- [ ] Info box "Start card tracking first" muncul
- [ ] Subtask features tidak tampil
- [ ] Hanya tombol "Start Tracking" (card) yang available

### Test 2: Start Card Tracking
- [ ] Click "Start Tracking" untuk card
- [ ] Timer mulai jalan
- [ ] Subtask section muncul dengan full features
- [ ] Button "Add Subtask" enabled
- [ ] Bisa add/edit/delete subtask

### Test 3: Start Subtask Tracking While Card Tracking
- [ ] Card tracking sedang jalan
- [ ] Add subtask baru
- [ ] Click "Start Tracking" pada subtask
- [ ] Subtask timer mulai jalan
- [ ] Both timers (card & subtask) jalan bersamaan

### Test 4: Stop Card (dengan subtask tracking jalan)
- [ ] Card tracking jalan + 1 subtask tracking jalan
- [ ] Click "Stop Tracking" pada card
- [ ] **Check:** Subtask tracking otomatis stop juga
- [ ] Success message menyebutkan "Otomatis menghentikan 1 subtask tracking"
- [ ] Check database: both time_logs punya `end_time` yang sama

### Test 5: After Stop - Read-Only Mode
- [ ] Subtask features disabled
- [ ] Warning box muncul
- [ ] Subtask list tampil dalam read-only mode
- [ ] No add/edit/delete/track buttons
- [ ] Tombol "Start Tracking Again" available

### Test 6: Restart Tracking
- [ ] Click "Start Tracking" lagi (setelah stop)
- [ ] Timer mulai dari 0
- [ ] Subtask features enabled kembali
- [ ] Check: `started_at` di card_assignments tidak berubah (tetap first time)

### Test 7: Multiple Subtask Stop
- [ ] Start card tracking
- [ ] Start tracking pada 3 subtasks
- [ ] Stop card tracking
- [ ] **Check:** Semua 3 subtask tracking stop otomatis
- [ ] Success message: "Otomatis menghentikan 3 subtask tracking"

### Test 8: actual_hours Calculation
- [ ] Start & stop card tracking (duration: 120 min)
- [ ] Start & stop subtask tracking (duration: 30 min)
- [ ] Stop card
- [ ] Check database: `actual_hours` = (120 + 30) / 60 = 2.5

---

## 🔍 SQL Queries for Debugging

### Check Card Tracking State

```sql
-- Cek apakah card sedang di-track
SELECT 
    c.id,
    c.card_title,
    CASE 
        WHEN EXISTS(
            SELECT 1 FROM time_logs 
            WHERE card_id = c.id 
            AND subtask_id IS NULL 
            AND end_time IS NULL
        ) THEN 'Currently Tracking'
        WHEN EXISTS(
            SELECT 1 FROM time_logs 
            WHERE card_id = c.id 
            AND end_time IS NOT NULL
        ) THEN 'Previously Tracked'
        ELSE 'Never Tracked'
    END as tracking_state
FROM cards c
WHERE c.id = 5;
```

### Check Ongoing Subtask Tracking

```sql
-- Cek subtask tracking yang masih jalan
SELECT 
    tl.id,
    tl.card_id,
    tl.subtask_id,
    s.subtask_name,
    tl.start_time,
    TIMESTAMPDIFF(MINUTE, tl.start_time, NOW()) as elapsed_minutes
FROM time_logs tl
JOIN subtasks s ON tl.subtask_id = s.id
WHERE tl.card_id = 5
AND tl.end_time IS NULL
AND tl.subtask_id IS NOT NULL;
```

### Check CASCADE Stop Result

```sql
-- Verify semua time logs stopped dengan end_time yang sama
SELECT 
    id,
    card_id,
    subtask_id,
    start_time,
    end_time,
    duration_minutes,
    CASE 
        WHEN subtask_id IS NULL THEN 'Card Tracking'
        ELSE 'Subtask Tracking'
    END as tracking_type
FROM time_logs
WHERE card_id = 5
ORDER BY end_time DESC
LIMIT 5;
```

---

## 📚 Helper Methods Reference

### Card Model Methods

```php
// Check if card has any completed time log
$card->hasBeenTracked();  // true/false

// Check if card currently being tracked
$card->isBeingTracked();  // true/false

// Get ongoing time log
$ongoingLog = $card->getOngoingTimeLog();  // TimeLog|null
```

### Blade View Usage

```blade
@if($card->isBeingTracked())
    {{-- Full subtask features --}}
@elseif($card->hasBeenTracked())
    {{-- Read-only subtask list --}}
@else
    {{-- Info message --}}
@endif
```

---

## 🚨 Important Notes

1. **CASCADE Stop is Automatic**
   - User tidak perlu stop subtask tracking manual
   - System otomatis stop semua subtask tracking saat stop card

2. **Read-Only Mode Purpose**
   - Protect data integrity
   - Prevent changes saat card tidak di-track
   - User bisa restart tracking untuk enable features lagi

3. **Multiple Sessions Support**
   - User bisa start-stop-start lagi
   - Setiap session punya time log terpisah
   - `actual_hours` accumulate dari semua sessions

4. **Performance Consideration**
   - Helper methods use `exists()` untuk efficiency
   - Eager load `timeLogs` relation di controller untuk avoid N+1

5. **UI Feedback**
   - Clear info boxes untuk setiap state
   - Success message include subtask stop count
   - Disable vs Hide: Subtask list tetap visible (read-only)

---

## 🤝 Support

Jika ada pertanyaan atau issue:
1. Check helper methods di Card model
2. Verify cascade logic di TimeLogController
3. Check conditional rendering di Blade view
4. Use SQL queries di atas untuk debugging

---

**Status**: ✅ **Ready for Implementation**

Backend logic sudah complete. Tinggal implement conditional rendering di Blade view sesuai panduan di atas.
