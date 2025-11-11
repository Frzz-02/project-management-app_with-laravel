# Start Task with Real-Time Timer - Update Documentation

## 🎯 Overview
Update fitur "Start Task" dengan real-time timer yang menampilkan waktu tracking berjalan dan button "Stop" untuk menghentikan tracking.

## ✨ Fitur Baru

### 1️⃣ **Real-Time Timer Display**
Saat tracking aktif, card menampilkan:
- ⏱️ **Timer berjalan** dalam format `HH:MM:SS`
- 🟢 **Animated indicator** (pulsing green dot + rotating clock)
- 🎨 **Gradient background** (green to emerald)
- ⏹️ **Stop button** untuk menghentikan tracking

### 2️⃣ **Dynamic State Management**
Card secara otomatis mendeteksi:
- ✅ **Tidak ada tracking**: Tampilkan button "Start Task"
- ✅ **Tracking aktif**: Tampilkan timer + button "Stop"
- ✅ **Status bukan TODO**: Sembunyikan button "Start Task"

### 3️⃣ **Alpine.js Timer**
Timer menggunakan Alpine.js untuk update real-time tanpa refresh:
```javascript
x-data="{ 
    elapsed: {{ initialSeconds }},
    interval: null,
    mounted() {
        this.interval = setInterval(() => {
            this.elapsed++;
        }, 1000);
    },
    formatTime() {
        const hours = Math.floor(this.elapsed / 3600);
        const minutes = Math.floor((this.elapsed % 3600) / 60);
        const seconds = this.elapsed % 60;
        return `${hours.padStart(2, '0')}:${minutes.padStart(2, '0')}:${seconds.padStart(2, '0')}`;
    }
}"
```

## 🎨 UI/UX Design

### State 1: No Tracking (TODO Status)
```
┌─────────────────────────────────────────┐
│  [Card Content]                         │
│                                         │
│  ┌────────────────────────────────┐    │
│  │  ⏱️ Start Task                 │    │
│  └────────────────────────────────┘    │
└─────────────────────────────────────────┘
```

### State 2: Tracking Active
```
┌─────────────────────────────────────────┐
│  [Card Content]                         │
│                                         │
│  ┌────────────────────────────────┐    │
│  │ 🟢 Tracking Active    [Stop]   │    │
│  │    01:23:45                     │    │
│  └────────────────────────────────┘    │
└─────────────────────────────────────────┘
```

### Visual Details:

**Tracking Display:**
- Background: Gradient `from-green-50 to-emerald-50`
- Border: `border-green-200`
- Icon: Animated pulsing green dot + rotating clock
- Text: "Tracking Active" (green-700)
- Timer: Large mono font (green-800)
- Stop Button: Red background with hover effect

**Animations:**
- ✨ Pulsing dot: `animate-pulse`
- ✨ Rotating clock: `animate-spin` (2s duration)
- ✨ Timer updates every second

## 🔧 Technical Implementation

### Backend Changes

#### 1. BoardController.php - Eager Loading
```php
'cards.timeLogs' => function ($query) {
    $query->where('user_id', Auth::id())
          ->whereNull('end_time')
          ->whereNull('subtask_id');
}
```

**Purpose:**
- Load active time logs untuk current user
- Filter: Only ongoing tracking (end_time = NULL)
- Filter: Only card-level tracking (subtask_id = NULL)
- Prevent N+1 query problem

### Frontend Changes

#### 2. card-item.blade.php - Dynamic Timer Display

**PHP Logic:**
```php
@php
    // Get active time log for current user
    $activeTimeLog = $card->timeLogs()
        ->where('user_id', $currentUser->id)
        ->whereNull('end_time')
        ->whereNull('subtask_id')
        ->first();
    
    $hasActiveTracking = $activeTimeLog !== null;
    
    // Calculate elapsed time
    $elapsedSeconds = 0;
    if ($hasActiveTracking && $activeTimeLog->start_time) {
        $elapsedSeconds = now()->diffInSeconds($activeTimeLog->start_time);
    }
@endphp
```

**Conditional Rendering:**
```blade
@if($isAssigned && in_array($userRole, ['designer', 'developer']))
    @if($hasActiveTracking)
        {{-- Show Timer + Stop Button --}}
    @elseif($card->status === 'todo')
        {{-- Show Start Task Button --}}
    @endif
@endif
```

#### 3. Timer Component Structure

```blade
<div x-data="timerData({{ $elapsedSeconds }})" x-init="mounted()" @click.stop>
    <div class="flex items-center justify-between">
        <!-- Left: Icon + Timer -->
        <div class="flex items-center space-x-2">
            <div class="relative">
                <!-- Pulsing dot -->
                <svg class="animate-pulse">...</svg>
                <!-- Rotating clock -->
                <svg class="animate-spin">...</svg>
            </div>
            <div>
                <div class="text-xs">Tracking Active</div>
                <div class="text-lg font-mono" x-text="formatTime()">00:00:00</div>
            </div>
        </div>
        
        <!-- Right: Stop Button -->
        <form action="{{ route('time-logs.stop', $activeTimeLog->id) }}" method="POST">
            @csrf
            <button>Stop</button>
        </form>
    </div>
</div>
```

## 🔄 User Flow

### Start Task Flow:
```
1. Designer/Developer login
2. View board with TODO card (assigned)
3. See "Start Task" button (blue)
4. Click "Start Task"
   ├─ POST /time-logs/start
   ├─ Create time_log record
   ├─ Update card status → "in progress"
   └─ Redirect back to board
5. Page reload
6. Card now shows timer (green background)
7. Timer starts counting from 00:00:00
```

### Stop Task Flow:
```
1. While tracking active
2. Timer displays elapsed time (real-time)
3. Click "Stop" button (red)
   ├─ POST /time-logs/{id}/stop
   ├─ Update time_log: end_time = now
   ├─ Calculate duration_minutes
   └─ Redirect back to board
4. Page reload
5. Timer disappears
6. Button returns to "Start Task" (if TODO)
```

## 📊 State Transition Diagram

```
TODO Card (No Tracking)
         │
         │ [Click "Start Task"]
         ▼
   Create TimeLog
   Status → In Progress
         │
         ▼
 Active Tracking State
 (Timer Running)
         │
         │ [Click "Stop"]
         ▼
    End TimeLog
    Timer Stops
         │
         ▼
  In Progress Card
  (No Tracking)
```

## 🎭 Role-Based Visibility

| User Role  | Card Status | Assigned | Has Tracking | Displays           |
|------------|-------------|----------|--------------|-------------------|
| Designer   | TODO        | ✅ Yes   | ❌ No        | "Start Task"      |
| Designer   | TODO        | ✅ Yes   | ✅ Yes       | Timer + Stop      |
| Designer   | In Progress | ✅ Yes   | ✅ Yes       | Timer + Stop      |
| Designer   | In Progress | ✅ Yes   | ❌ No        | Nothing           |
| Developer  | TODO        | ✅ Yes   | ❌ No        | "Start Task"      |
| Team Lead  | TODO        | ✅ Yes   | ❌ No        | Nothing           |
| Designer   | TODO        | ❌ No    | ❌ No        | Nothing           |

## 🧪 Testing Scenarios

### Test 1: Start Task & See Timer
```
GIVEN: Designer "Alex" with TODO card "Design Homepage"
WHEN:  Alex clicks "Start Task"
THEN:
  ✅ Page reloads
  ✅ Card shows green gradient background
  ✅ Timer displays "00:00:00" and starts counting
  ✅ Pulsing dot animates
  ✅ Clock icon rotates
  ✅ "Stop" button appears (red)
  ✅ "Start Task" button disappears
```

### Test 2: Timer Counts Correctly
```
GIVEN: Active tracking for 5 minutes 30 seconds
WHEN:  User views the board
THEN:
  ✅ Timer shows "00:05:30"
  ✅ Timer increments every second
  ✅ After 30 seconds: "00:06:00"
  ✅ Timer continues counting indefinitely
```

### Test 3: Stop Tracking
```
GIVEN: Active tracking showing "01:23:45"
WHEN:  User clicks "Stop" button
THEN:
  ✅ POST request to /time-logs/{id}/stop
  ✅ Database: end_time updated
  ✅ Database: duration_minutes calculated
  ✅ Page reloads
  ✅ Timer disappears
  ✅ Card shows normal state (no buttons if in progress)
```

### Test 4: Multiple Cards Tracking
```
GIVEN: User tracking Card A for 10 minutes
WHEN:  User tries to start Card B
THEN:
  ❌ Error: "Anda masih memiliki timer card lain yang sedang berjalan"
  ✅ User must stop Card A first
  ✅ Only one card can be tracked at a time
```

### Test 5: Page Refresh Persistence
```
GIVEN: Active tracking showing "00:15:30"
WHEN:  User refreshes page (F5)
THEN:
  ✅ Timer still displays
  ✅ Timer continues from correct elapsed time
  ✅ No data loss
  ✅ Stop button still functional
```

## 🐛 Troubleshooting

### Timer Tidak Muncul Setelah Start
**Checklist:**
- [ ] Route `time-logs.start` terdaftar?
- [ ] TimeLog berhasil dibuat di database?
- [ ] BoardController eager load `cards.timeLogs`?
- [ ] Card model punya relationship `timeLogs()`?
- [ ] User ID match dengan yang login?

**Debug:**
```sql
-- Check active time logs
SELECT * FROM time_logs 
WHERE user_id = X 
  AND card_id = Y 
  AND end_time IS NULL 
  AND subtask_id IS NULL;
```

### Timer Tidak Count Up
**Checklist:**
- [ ] JavaScript console errors?
- [ ] Alpine.js loaded di layout?
- [ ] `x-init="mounted()"` dipanggil?
- [ ] `setInterval` berjalan?

**Debug:**
```javascript
// Add to timer component
mounted() {
    console.log('Timer mounted, initial elapsed:', this.elapsed);
    this.interval = setInterval(() => {
        this.elapsed++;
        console.log('Timer tick:', this.elapsed);
    }, 1000);
}
```

### Stop Button Tidak Bekerja
**Checklist:**
- [ ] Route `time-logs.stop` terdaftar?
- [ ] Route parameter `{timeLog}` match dengan ID?
- [ ] CSRF token ada di form?
- [ ] User authorized untuk stop?

**Debug:**
```php
// In TimeLogController::stopTracking
Log::info('Stop tracking request', [
    'time_log_id' => $timeLog->id,
    'user_id' => Auth::id(),
    'elapsed' => $timeLog->start_time->diffInMinutes(now())
]);
```

### Timer Restart dari 0 Setelah Refresh
**Problem:** `$elapsedSeconds` calculation salah

**Fix:**
```php
// Pastikan timezone konsisten
$elapsedSeconds = now()->diffInSeconds($activeTimeLog->start_time);

// BUKAN:
$elapsedSeconds = Carbon::now()->diffInSeconds($activeTimeLog->start_time);
```

## 📈 Performance Considerations

### Eager Loading Benefits:
```php
// GOOD ✅ (1 query for all cards + 1 for timeLogs)
$board->with(['cards.timeLogs'])->get();

// BAD ❌ (N+1 queries: 1 + 50 cards)
$cards->each(function($card) {
    $card->timeLogs()->where(...)->first();
});
```

### Timer JavaScript:
- ✅ Uses `setInterval` (efficient)
- ✅ Only increments number (no DOM manipulation)
- ✅ Formatted on display (`x-text`)
- ⚠️ Clears interval on component destroy

### Database Queries:
```sql
-- Single query for all active timeLogs
SELECT * FROM time_logs 
WHERE card_id IN (1,2,3,4,5...) 
  AND user_id = X 
  AND end_time IS NULL 
  AND subtask_id IS NULL;
```

## 🚀 Future Enhancements

### 1. AJAX Stop (No Page Reload)
```javascript
async function stopTracking(timeLogId) {
    await fetch(`/time-logs/${timeLogId}/stop`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': token }
    });
    
    // Update UI without reload
    hideTimer();
    showStartButton();
}
```

### 2. Pause/Resume Timer
```javascript
// Add pause button
<button @click="pauseTimer()">⏸ Pause</button>

pauseTimer() {
    clearInterval(this.interval);
    this.paused = true;
    // Save pause state to backend
}
```

### 3. Timer Notifications
```javascript
// Alert after certain duration
if (this.elapsed === 3600) { // 1 hour
    new Notification('Time tracking reminder', {
        body: 'You have been working for 1 hour!'
    });
}
```

### 4. Multiple Timer Display
Show all active timers in sidebar:
```
🟢 Design Homepage    01:23:45 [Stop]
🟢 API Integration    00:45:12 [Stop]
```

## 📝 Summary

### What Changed:
✅ Added real-time timer display with Alpine.js  
✅ Stop button for active tracking  
✅ Animated visual indicators (pulsing dot, rotating clock)  
✅ Eager loading for timeLogs in BoardController  
✅ Dynamic state: Start button ↔ Timer display  

### What Works:
✅ Timer counts up every second  
✅ Correct elapsed time calculation on page load  
✅ Stop button ends tracking and updates database  
✅ Role-based visibility (Designer/Developer only)  
✅ Persists across page refreshes  

### Benefits:
🎯 Real-time feedback for users  
🎯 No need to open card detail to see timer  
🎯 Quick stop access from board view  
🎯 Visual clarity with animations  
🎯 Better time tracking adoption  
