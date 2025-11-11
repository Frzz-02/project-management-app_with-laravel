# Start Task Feature - Dokumentasi

## 📋 Overview
Fitur "Start Task" memungkinkan Designer/Developer untuk langsung memulai tracking waktu kerja dari card Kanban board.

## ✨ Fitur Utama

### 1️⃣ Button "Start Task"
- **Lokasi**: Muncul di card item dalam Kanban board
- **Kondisi Tampil**:
  - ✅ Card status = `todo`
  - ✅ User adalah Designer ATAU Developer
  - ✅ User sudah di-assign ke card tersebut
  - ❌ Team Lead **TIDAK** melihat button ini

### 2️⃣ Auto-Update Status
Saat button "Start Task" diklik:
1. **Time tracking dimulai** (create record di `time_logs` table)
2. **Card status otomatis berubah** dari `todo` → `in progress`
3. **Card assignment updated**: `started_at` diisi, `assignment_status` → `in progress`
4. **Redirect** kembali ke board dengan success message

### 3️⃣ Button Dihilangkan
- ❌ **"Submit for Review"** button sudah dihilangkan
- ✅ Designer/Developer mengerjakan task sampai selesai
- ✅ Team Lead yang review dan approve

## 🎯 User Flow

### Designer/Developer Flow:
```
1. Login sebagai Designer/Developer
2. Buka board project
3. Card dengan status "TODO" yang sudah di-assign akan tampilkan button "Start Task"
4. Klik "Start Task"
   ├─ Time tracking mulai
   ├─ Card status → "In Progress"
   └─ Success message tampil
5. Kerjakan task
6. Stop tracking di card detail page
7. Tunggu Team Lead review
```

### Team Lead Flow:
```
1. Login sebagai Team Lead
2. Card status "Review" tampilkan button "Approve" / "Request Changes"
3. Review hasil kerja Developer/Designer
4. Klik "Approve" → Status jadi "Done"
   ATAU
   Klik "Request Changes" → Status kembali "In Progress"
```

## 🔧 Technical Implementation

### Frontend (card-item.blade.php)

```blade
{{-- Start Task Button: Only for Designer/Developer --}}
@if($card->status === 'todo' && $isAssigned && in_array($userRole, ['designer', 'developer']))
    <form action="{{ route('time-logs.start') }}" method="POST" @click.stop>
        @csrf
        <input type="hidden" name="card_id" value="{{ $card->id }}">
        <button type="submit" class="...">
            <svg>⏱</svg> Start Task
        </button>
    </form>
@endif
```

**Key Points:**
- Form POST ke route `time-logs.start`
- `@click.stop` prevent card detail modal dari terbuka
- Hidden input `card_id` untuk identify card

### Backend (TimeLogController.php)

```php
public function startTracking(Request $request)
{
    // 1. Validate input
    $validatedData = $request->validate([
        'card_id' => 'nullable|exists:cards,id',
        'subtask_id' => 'nullable|exists:subtasks,id',
        'description' => 'nullable|string|max:1000'
    ]);
    
    // 2. Check concurrent tracking rules
    // - Cannot track same card/subtask twice
    // - Cannot track 2 different cards simultaneously
    // - Can track 1 card + multiple subtasks concurrently
    
    // 3. Authorization check
    // - User must be project member
    
    // 4. Create time log
    $timeLog = TimeLog::create([
        'card_id' => $validatedData['card_id'],
        'user_id' => Auth::id(),
        'start_time' => Carbon::now('Asia/Jakarta'),
        'end_time' => null,
        'duration_minutes' => 0
    ]);
    
    // 5. Auto-update card status
    if ($card->status !== 'done' && $card->status !== 'review') {
        $card->update(['status' => 'in progress']);
    }
    
    // 6. Update card assignment
    $cardAssignment->update([
        'started_at' => Carbon::now(),
        'assignment_status' => 'in progress'
    ]);
    
    return redirect()->back()->with('success', 'Time tracking dimulai!');
}
```

## 🗄️ Database Changes

### time_logs Table
```sql
INSERT INTO time_logs (card_id, user_id, start_time, end_time, duration_minutes)
VALUES (123, 456, '2025-11-08 10:00:00', NULL, 0);
```

### cards Table
```sql
UPDATE cards 
SET status = 'in progress', updated_at = NOW()
WHERE id = 123 AND status = 'todo';
```

### card_assignments Table
```sql
UPDATE card_assignments
SET started_at = '2025-11-08 10:00:00',
    assignment_status = 'in progress',
    updated_at = NOW()
WHERE card_id = 123 AND user_id = 456;
```

## 🎨 UI/UX Details

### Button Styling
- **Background**: `bg-blue-50` (light blue)
- **Text**: `text-blue-700` (dark blue)
- **Hover**: `hover:bg-blue-100` (slightly darker)
- **Icon**: Clock icon (⏱)
- **Width**: Full width (`w-full`)

### Visibility Rules

| User Role     | Card Status | Is Assigned | Button Visible? |
|---------------|-------------|-------------|-----------------|
| Designer      | TODO        | ✅ Yes      | ✅ Yes          |
| Developer     | TODO        | ✅ Yes      | ✅ Yes          |
| Team Lead     | TODO        | ✅ Yes      | ❌ No           |
| Designer      | TODO        | ❌ No       | ❌ No           |
| Designer      | In Progress | ✅ Yes      | ❌ No           |

## 🔒 Security & Authorization

### Authorization Layers:
1. **Blade Template Level**: `@if($isAssigned && in_array($userRole, ['designer', 'developer']))`
2. **Controller Level**: Project membership check
3. **Database Level**: Foreign key constraints

### Protection Against:
- ✅ Unauthorized users starting tracking
- ✅ Team Lead accidentally clicking "Start Task"
- ✅ Non-assigned members starting tracking
- ✅ Duplicate tracking (same card twice)
- ✅ Concurrent tracking of different cards

## 📊 Testing Scenarios

### Test 1: Designer Starts Task
```
Given: Designer "Alex" assigned to card "Design Login Page" (status: TODO)
When: Alex clicks "Start Task" button
Then:
  ✅ Time log created with start_time = now
  ✅ Card status changed to "In Progress"
  ✅ Card assignment started_at = now
  ✅ Success message: "Time tracking dimulai!"
  ✅ Button disappears from card
```

### Test 2: Team Lead Doesn't See Button
```
Given: Team Lead "Sarah" assigned to card "Review API" (status: TODO)
When: Sarah views the board
Then:
  ✅ "Start Task" button NOT visible
  ❌ Team Lead uses other methods to change status
```

### Test 3: Unassigned Member Cannot Start
```
Given: Developer "Bob" NOT assigned to card "Fix Bug" (status: TODO)
When: Bob views the board
Then:
  ✅ "Start Task" button NOT visible
  ❌ Bob cannot start tracking on this card
```

### Test 4: Already In Progress
```
Given: Card "Code Feature X" already has status "In Progress"
When: Designer views the card
Then:
  ✅ "Start Task" button NOT visible (card not in TODO anymore)
  ✅ Designer continues work or stops tracking
```

## 🐛 Troubleshooting

### Button Tidak Muncul
**Checklist:**
- [ ] User login sebagai Designer/Developer (bukan Team Lead)?
- [ ] Card status = `todo`?
- [ ] User sudah di-assign ke card?
- [ ] Refresh halaman (Ctrl+F5)?

### Error "Gagal memulai time tracking"
**Possible Causes:**
- Database connection issue
- Card ID tidak valid
- User bukan member project
- Already has active tracking on another card

**Solution:**
```bash
# Check logs
php artisan pail --filter=error

# Check active tracking
SELECT * FROM time_logs 
WHERE user_id = X AND end_time IS NULL;
```

### Status Tidak Berubah
**Checklist:**
- [ ] Card status sebelumnya = `todo`?
- [ ] Card tidak dalam status `done` atau `review`?
- [ ] Database connection aktif?

**Debug:**
```sql
-- Check card status
SELECT id, card_title, status, updated_at 
FROM cards WHERE id = 123;

-- Check time logs
SELECT * FROM time_logs 
WHERE card_id = 123 
ORDER BY created_at DESC LIMIT 5;
```

## 🚀 Future Enhancements

### Possible Improvements:
1. **AJAX Start Task**: No page reload, instant feedback
2. **Timer Display**: Show running timer on card
3. **Notification**: Email/push notification when task started
4. **Analytics**: Track average time to start tasks
5. **Bulk Start**: Start multiple tasks at once
6. **Quick Stop**: Stop button on card item (not just detail page)

### Example AJAX Implementation:
```javascript
async function startTask(cardId) {
    const response = await fetch('/time-logs/start', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ card_id: cardId })
    });
    
    if (response.ok) {
        // Update UI without reload
        updateCardStatus(cardId, 'in progress');
        showToast('Time tracking started!');
    }
}
```

## 📝 Summary

### What Changed:
✅ Added "Start Task" button for Designer/Developer  
✅ Removed "Submit for Review" button  
✅ Auto-update card status on start tracking  
✅ Role-based visibility (only Designer/Developer)  
✅ Integration with existing time tracking system  

### What Stayed:
✅ Team Lead approve/reject flow (unchanged)  
✅ Time tracking stop functionality (unchanged)  
✅ Card assignment system (unchanged)  
✅ Project member authorization (unchanged)  

### Benefits:
🎯 Faster workflow for Designer/Developer  
🎯 Clear role separation (work vs review)  
🎯 Automatic status tracking  
🎯 Better time tracking adoption  
🎯 Simplified UI (fewer buttons)  
