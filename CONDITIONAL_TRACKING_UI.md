# Conditional Time Tracking UI - Documentation

## Overview
Implementasi UI yang conditional untuk time tracking berdasarkan state tracking yang sedang berjalan. Ini mencegah user dari membuat tracking yang invalid dan meningkatkan UX dengan visual guidance yang jelas.

## Business Rules

### 1. Radio Button "Entire Card" 
**Conditional Display**: Hanya tampil jika TIDAK ada subtask tracking yang aktif

**Rationale**:
- Jika ada subtask sedang di-track, card tidak boleh di-track (aturan concurrent tracking)
- Mencegah user mencoba start card tracking yang akan di-reject oleh backend

**Behavior**:
- ✅ **SHOW**: Jika `$ongoingSubtaskTrackings->where('card_id', $card->id)->count() === 0`
- ❌ **HIDE**: Jika ada subtask tracking aktif untuk card ini
- **Replacement**: Tampilkan pesan informasi: _"Card tracking unavailable (subtask tracking active)"_

### 2. Radio Button "Specific Subtask"
**Conditional Display**: Hanya tampil jika card SUDAH di-track

**Rationale**:
- Subtask tracking requires active card tracking (prerequisite rule)
- Jika card belum di-track, subtask tidak bisa di-track
- Mencegah user mencoba start subtask tracking yang akan error

**Behavior**:
- ✅ **SHOW**: Jika `$ongoingCardTracking && $ongoingCardTracking->card_id === $card->id`
- ❌ **HIDE**: Jika card belum di-track
- **Replacement**: Tampilkan warning message dengan amber styling: _"Start card tracking first to enable subtask tracking"_

### 3. Button "Start Tracking" di Modal Subtask
**New Feature**: Quick action untuk start tracking subtask langsung dari detail modal

**Conditional Enable/Disable**:
- ✅ **ENABLED**: Card sedang tracking + Subtask belum di-track
- ❌ **DISABLED**: Card belum tracking ATAU Subtask sudah di-track

**Tooltip Messages**:
- "Start card tracking first" - Jika card belum di-track
- "Already tracking this subtask" - Jika subtask sudah di-track
- "Start tracking this subtask" - Jika bisa di-track

---

## Implementation Details

### 1. Start Timer Form - Conditional Radio Buttons

**File**: `resources/views/cards/show.blade.php`

**Location**: Time Tracking Section → Start Timer Form

```blade
<!-- Track For Option -->
<div>
    <label class="block text-sm font-medium text-gray-700 mb-2">
        Track time for:
    </label>
    <div class="flex items-center space-x-4">
        <!-- Entire Card Option - Hide jika ada subtask tracking -->
        @php
            $hasSubtaskTracking = $ongoingSubtaskTrackings->where('card_id', $card->id)->count() > 0;
        @endphp
        
        @if(!$hasSubtaskTracking)
            <label class="flex items-center cursor-pointer">
                <input type="radio" 
                       name="track_type" 
                       value="card"
                       @click="forSubtask = false; selectedSubtask = null"
                       checked
                       class="mr-2 text-green-600 focus:ring-green-500">
                <span class="text-sm text-gray-700">Entire Card</span>
            </label>
        @else
            <div class="flex items-center space-x-2 text-sm text-gray-500">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
                <span>Card tracking unavailable (subtask tracking active)</span>
            </div>
        @endif
        
        <!-- Specific Subtask Option - Hide jika card belum tracking -->
        @if($card->subtasks->count() > 0)
            @php
                $hasCardTracking = $ongoingCardTracking && $ongoingCardTracking->card_id === $card->id;
            @endphp
            
            @if($hasCardTracking)
                <label class="flex items-center cursor-pointer">
                    <input type="radio" 
                           name="track_type" 
                           value="subtask"
                           @click="forSubtask = true"
                           {{ $hasSubtaskTracking ? 'checked' : '' }}
                           class="mr-2 text-green-600 focus:ring-green-500">
                    <span class="text-sm text-gray-700">Specific Subtask</span>
                </label>
            @else
                <div class="flex items-center space-x-2 text-sm text-amber-600 bg-amber-50 px-3 py-2 rounded-lg">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="font-medium">Start card tracking first to enable subtask tracking</span>
                </div>
            @endif
        @endif
    </div>
</div>
```

**Key Points**:
- **PHP Variables**: `$hasSubtaskTracking`, `$hasCardTracking` untuk conditional logic
- **Visual Feedback**: Info icon + gray text untuk unavailable card tracking
- **Warning Badge**: Amber badge dengan warning icon untuk prerequisite message
- **Auto-select**: Jika ada subtask tracking, auto-select "Specific Subtask" radio

---

### 2. Alpine.js State Initialization

**Auto-set `forSubtask` based on current tracking state**:

```blade
<div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6" 
     x-data="{ 
         showForm: false, 
         forSubtask: {{ $ongoingSubtaskTrackings->where('card_id', $card->id)->count() > 0 ? 'true' : 'false' }}, 
         selectedSubtask: null 
     }">
```

**Logic**:
- Jika ada ongoing subtask tracking untuk card ini → `forSubtask = true`
- Jika tidak → `forSubtask = false` (default)
- Ini memastikan form state konsisten dengan display state

---

### 3. Start Tracking Button di Modal Subtask

**File**: `resources/views/cards/show.blade.php`

**Location**: Subtask Detail Modal → Footer Section

```blade
<!-- Left side: Start Tracking Button -->
<div x-data="{ 
    canStartTracking() {
        // Check if card is being tracked
        const hasCardTracking = {{ $hasCardTracking ? 'true' : 'false' }};
        // Check if this subtask is already being tracked
        const isTracking = {{ json_encode($ongoingSubtaskTrackings->pluck('subtask_id')->toArray()) }}.includes(this.subtask?.id);
        return hasCardTracking && !isTracking;
    },
    getTooltip() {
        const hasCardTracking = {{ $hasCardTracking ? 'true' : 'false' }};
        const isTracking = {{ json_encode($ongoingSubtaskTrackings->pluck('subtask_id')->toArray()) }}.includes(this.subtask?.id);
        
        if (isTracking) return 'Already tracking this subtask';
        if (!hasCardTracking) return 'Start card tracking first';
        return 'Start tracking this subtask';
    }
}">
    <form :action="`{{ route('time-logs.start') }}`" method="POST">
        @csrf
        <input type="hidden" name="card_id" :value="subtask?.card_id">
        <input type="hidden" name="subtask_id" :value="subtask?.id">
        
        <button type="submit"
                x-bind:disabled="!canStartTracking()"
                x-bind:title="getTooltip()"
                class="px-4 py-2 rounded-lg transition-colors flex items-center space-x-2 group relative"
                :class="canStartTracking() ? 'bg-green-600 text-white hover:bg-green-700' : 'bg-gray-300 text-gray-500 cursor-not-allowed'">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"></path>
            </svg>
            <span>Start Tracking</span>
            
            <!-- Tooltip on hover -->
            <div x-show="!canStartTracking()" 
                 class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-900 text-white text-xs rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                <span x-text="getTooltip()"></span>
                <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
            </div>
        </button>
    </form>
</div>
```

**Alpine.js Methods**:

1. **`canStartTracking()`**: 
   - Returns `true` jika card tracking + subtask belum tracking
   - Returns `false` jika card tidak tracking ATAU subtask sudah tracking

2. **`getTooltip()`**: 
   - Returns appropriate message based on state
   - "Already tracking" / "Start card first" / "Start tracking this"

**Button States**:
- **Enabled**: Green button, hover effect, cursor pointer
- **Disabled**: Gray button, no hover, cursor not-allowed, tooltip on hover

**Form Submission**:
- POST to `time-logs.start` route
- Hidden inputs: `card_id`, `subtask_id`
- Uses Alpine.js `:value` binding untuk dynamic subtask data

---

## User Experience Flows

### Flow 1: Start Card Tracking (Normal)
1. User buka card detail
2. No tracking active → Form shows "Entire Card" radio (checked by default)
3. User click "Start Work" → Form expands
4. User bisa langsung submit (card tracking) atau pilih subtask (DISABLED dengan warning)
5. Submit → Card tracking starts

### Flow 2: Start Subtask Tracking (After Card Started)
1. Card tracking already active (green timer visible)
2. User click "Start Work" → Form expands
3. "Entire Card" option HIDDEN dengan info message
4. "Specific Subtask" radio visible dan auto-selected
5. Dropdown subtask selector visible
6. User pilih subtask → Submit → Subtask tracking starts

### Flow 3: Start Subtask from Modal (Quick Action)
1. Card tracking active
2. User click subtask → Modal detail opens
3. Footer shows "Start Tracking" button (ENABLED, green)
4. User click button → Langsung submit form → Subtask tracking starts
5. Modal closes, subtask timer appears

### Flow 4: Try Start Subtask Without Card (Blocked - Form)
1. No card tracking
2. User click "Start Work"
3. "Entire Card" radio visible
4. "Specific Subtask" HIDDEN, replaced with amber warning badge: _"Start card tracking first to enable subtask tracking"_
5. User dipaksa start card dulu

### Flow 5: Try Start Subtask Without Card (Blocked - Modal)
1. No card tracking
2. User click subtask → Modal opens
3. "Start Tracking" button DISABLED (gray, cursor not-allowed)
4. Hover button → Tooltip shows: _"Start card tracking first"_
5. User cannot submit

### Flow 6: Try Start Already-Tracked Subtask (Blocked - Modal)
1. Subtask already tracking (blue timer visible)
2. User click same subtask → Modal opens
3. "Start Tracking" button DISABLED (gray)
4. Hover → Tooltip: _"Already tracking this subtask"_
5. Cannot submit

---

## Visual Design

### Color Coding
- **Green**: Card tracking, enabled buttons
- **Blue**: Subtask tracking
- **Gray**: Disabled/unavailable options
- **Amber**: Warning messages (prerequisite not met)

### Icons
- ℹ️ Info icon (gray circle with 'i') - Unavailable card option
- ⚠️ Warning icon (amber triangle) - Prerequisite warning
- ▶️ Play icon - Start tracking button

### Typography
- **Font size**: `text-sm` untuk form labels dan messages
- **Font weight**: `font-medium` untuk warnings, `font-bold` untuk headings
- **Color**: 
  - Gray-700 untuk normal text
  - Gray-500 untuk disabled text
  - Amber-600 untuk warnings
  - Green-600 untuk enabled actions

---

## Testing Scenarios

### Test 1: Conditional Radio Button Display

**Scenario 1A**: No tracking active
```
✅ Expected:
- "Entire Card" radio visible dan checked
- "Specific Subtask" hidden dengan amber warning
```

**Scenario 1B**: Card tracking active
```
✅ Expected:
- "Entire Card" radio visible
- "Specific Subtask" radio visible
```

**Scenario 1C**: Subtask tracking active
```
✅ Expected:
- "Entire Card" hidden dengan gray info message
- "Specific Subtask" radio visible dan auto-checked
```

**SQL Verification**:
```sql
-- Check ongoing tracking
SELECT 
    tl.id,
    tl.card_id,
    tl.subtask_id,
    tl.user_id,
    CASE 
        WHEN tl.subtask_id IS NULL THEN 'CARD'
        ELSE 'SUBTASK'
    END as tracking_type,
    c.card_title,
    s.subtask_name
FROM time_logs tl
LEFT JOIN cards c ON c.id = tl.card_id
LEFT JOIN subtasks s ON s.id = tl.subtask_id
WHERE tl.user_id = ? -- Current user ID
  AND tl.end_time IS NULL
ORDER BY tl.start_time DESC;
```

### Test 2: Start Tracking Button in Modal

**Scenario 2A**: Card tracking + Subtask not tracked
```
✅ Expected:
- Button ENABLED (green)
- Hover: "Start tracking this subtask"
- Click: Form submits → Subtask tracking starts
```

**Scenario 2B**: No card tracking
```
✅ Expected:
- Button DISABLED (gray)
- Hover tooltip: "Start card tracking first"
- Click: No action
```

**Scenario 2C**: Subtask already tracked
```
✅ Expected:
- Button DISABLED (gray)
- Hover tooltip: "Already tracking this subtask"
- Click: No action
```

**Manual Test**:
1. Start card tracking
2. Open subtask modal
3. Verify button is green and enabled
4. Click button
5. Verify subtask tracking starts
6. Re-open same subtask modal
7. Verify button is now gray/disabled with "already tracking" tooltip

### Test 3: Form State Consistency

**Verify Alpine.js `forSubtask` initialization**:

```javascript
// Browser console
const formElement = document.querySelector('[x-data*="forSubtask"]');
const alpineData = Alpine.$data(formElement);
console.log('forSubtask:', alpineData.forSubtask);
console.log('selectedSubtask:', alpineData.selectedSubtask);
```

**Expected**:
- If subtask tracking active: `forSubtask = true`
- If no tracking or only card tracking: `forSubtask = false`

---

## Error Handling

### Backend Validation Still Active
Meskipun UI mencegah invalid submissions, backend validation tetap enforces rules:

1. **Duplicate tracking check** (TimeLogController.php ~lines 80-120)
2. **Multiple card tracking check** (TimeLogController.php ~lines 122-145)
3. **Subtask prerequisite check** (TimeLogController.php ~lines 152-176)

**Rationale**: Defense in depth - UI adalah first line of defense, backend adalah final authority

### User Tampering Prevention
Jika user bypass UI (e.g., browser console manipulation):
- Backend akan reject dengan error message
- Flash error message akan tampil di UI
- User kembali ke form dengan validation errors visible

---

## Browser Compatibility

### Alpine.js Requirements
- **Minimum Alpine.js version**: 3.x
- **Required features**: 
  - `x-data` component state
  - `x-bind` dynamic attributes
  - `x-show` conditional rendering
  - `x-text` text binding
  - Event binding (`@click`, `@submit`)

### Browser Support
- ✅ Chrome/Edge 90+
- ✅ Firefox 88+
- ✅ Safari 14+
- ⚠️ IE11: NOT SUPPORTED (Alpine.js requires modern JS)

---

## Performance Considerations

### Server-Side Rendering
- **PHP checks performed once** during blade compilation
- **No N+1 queries**: Use eager loading di controller
  ```php
  $ongoingCardTracking = TimeLog::where('user_id', Auth::id())
      ->whereNull('end_time')
      ->whereNull('subtask_id')
      ->first();
  
  $ongoingSubtaskTrackings = TimeLog::where('user_id', Auth::id())
      ->whereNull('end_time')
      ->whereNotNull('subtask_id')
      ->get();
  ```

### Client-Side Reactivity
- **Alpine.js state**: Lightweight, reactive
- **No external API calls**: All data from blade variables
- **Minimal JavaScript**: Only form toggle and button state logic

---

## Accessibility (a11y)

### Keyboard Navigation
- ✅ All buttons are keyboard accessible (`tab` to navigate)
- ✅ Radio buttons support arrow key navigation
- ✅ Form submission works with `Enter` key

### Screen Reader Support
- ⚠️ **Improvement needed**: Add `aria-label` to disabled states
  ```blade
  <div aria-label="Card tracking unavailable because subtask tracking is active">
      Card tracking unavailable (subtask tracking active)
  </div>
  ```

### Visual Indicators
- ✅ Color + icon kombinasi (not color-only)
- ✅ Clear text messages untuk semua states
- ✅ Hover tooltips untuk context

---

## Future Enhancements

### 1. Real-time UI Updates
**Problem**: Jika user start tracking di tab A, tab B tidak auto-update

**Solution**: 
- Implement Laravel Echo + Pusher untuk real-time broadcasts
- Listen to `TimeLogStarted`, `TimeLogStopped` events
- Update Alpine.js state via websocket

### 2. Loading States
**Problem**: Button tidak show loading saat form submit

**Solution**:
```blade
<button type="submit"
        x-data="{ loading: false }"
        @click="loading = true"
        :disabled="loading || !canStartTracking()"
        class="...">
    <span x-show="!loading">Start Tracking</span>
    <span x-show="loading">Starting...</span>
</button>
```

### 3. Keyboard Shortcuts
**Enhancement**: Quick actions via keyboard

**Example**:
- `Ctrl+Shift+T`: Toggle start/stop card tracking
- `Ctrl+Shift+S`: Open subtask selector modal

### 4. Undo/Redo
**Enhancement**: Accidental stop tracking recovery

**Implementation**:
- Store last stopped TimeLog in session
- Show "Undo stop" toast notification for 10 seconds
- Click undo → Delete TimeLog record → Recreate with same start_time

---

## Related Documentation
- [CONCURRENT_TIME_TRACKING.md](./CONCURRENT_TIME_TRACKING.md) - Backend validation rules
- [SUBTASK_PREREQUISITE.md](./SUBTASK_PREREQUISITE.md) - Prerequisite validation logic
- [SUBTASK_COMMENT_FIX.md](./SUBTASK_COMMENT_FIX.md) - Alpine.js event system

---

## Changelog

### 2025-01-08 (Latest)
- ✅ Implemented conditional radio button display
- ✅ Added "Start Tracking" button to subtask modal
- ✅ Auto-initialized `forSubtask` state based on tracking status
- ✅ Added visual feedback (info/warning messages) for disabled options
- ✅ Implemented tooltip system for button states
- ✅ Created comprehensive documentation

---

## Summary
Conditional UI implementation ini significantly improves UX dengan:
1. **Preventing invalid actions** sebelum user submit
2. **Clear visual guidance** tentang kenapa option unavailable
3. **Quick actions** via modal untuk efficiency
4. **Consistent state** antara backend validation dan frontend display
5. **Intuitive flow** yang guide user ke correct tracking sequence

**Key principle**: UI should guide, backend should enforce.
