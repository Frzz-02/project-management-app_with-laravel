# Card Approve/Reject Feature - Implementation Complete! ✅

## 📋 Overview

Fitur **Approve** dan **Request Change (Reject)** memungkinkan Team Lead untuk mereview dan memberikan feedback terhadap card/task yang telah dikerjakan developer. Fitur ini dilengkapi dengan:

- ✅ **Realtime Broadcast** untuk notifikasi instant
- ✅ **Notes/Keterangan** opsional dari reviewer
- ✅ **Automatic Status Update** untuk card dan assignments
- ✅ **History Tracking** semua review activity
- ✅ **Authorization** hanya Team Lead atau Admin

---

## 🎯 Workflow

```
Developer → Set card status "Review" → Team Lead Review:

┌──────────────────────────────────────┐
│  APPROVE                             │
│  ├─ Status card → "Done"             │
│  ├─ All assignments → "Completed"    │
│  ├─ Set completed_at timestamp       │
│  └─ Save to card_reviews history     │
└──────────────────────────────────────┘

┌──────────────────────────────────────┐
│  REJECT (Request Change)             │
│  ├─ Status card → "Todo"             │
│  ├─ Assignments tetap unchanged      │
│  └─ Save to card_reviews with notes  │
└──────────────────────────────────────┘
```

---

## 🔐 Authorization

### **Team Lead atau Admin ONLY**

```php
// Di Controller
$isAdmin = $user->role === 'admin';
$isTeamLead = $card->board->project->members()
    ->where('user_id', $user->id)
    ->where('role', 'team lead')
    ->exists();

if (!$isAdmin && !$isTeamLead) {
    return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
}
```

### **Di Blade**

```blade
@php
    $currentUserMember = $board->project->members->where('user_id', Auth::id())->first();
    $isTeamLeadReviewer = Auth::user()->role === 'admin' || 
                          ($currentUserMember && $currentUserMember->role === 'team lead');
@endphp

@if($isTeamLeadReviewer)
    <!-- Review buttons only visible for Team Lead/Admin -->
@endif
```

---

## 📁 Files Created/Modified

### **1. Request Validation** ✅
**File:** `app/Http/Requests/StoreCardReviewRequest.php`

```php
public function rules(): array
{
    return [
        'status' => ['required', 'in:approved,rejected'],
        'notes' => ['nullable', 'string', 'max:2000'],
    ];
}
```

### **2. Event for Broadcasting** ✅
**File:** `app/Events/CardReviewed.php`

```php
class CardReviewed implements ShouldBroadcastNow
{
    public function broadcastOn(): array
    {
        return [
            new Channel('projects.' . $this->card->board->project_id),
        ];
    }
    
    public function broadcastAs(): string
    {
        return 'card.reviewed';
    }
}
```

**Broadcast Data:**
- review (id, card_id, reviewed_by, reviewer_name, status, notes, reviewed_at)
- card (id, card_title, status, board_id, project_id)
- message (user-friendly notification message)

### **3. Controller** ✅
**File:** `app/Http/Controllers/web/CardReviewController.php`

**Methods:**
- `store(StoreCardReviewRequest $request, Card $card)` - Create review
- `index(Card $card)` - Get review history

**Features:**
- ✅ Authorization check (Admin OR Team Lead)
- ✅ Database transaction
- ✅ Update card status
- ✅ Update card_assignments (completed_at, assignment_status)
- ✅ Save to card_reviews table
- ✅ Broadcast realtime event
- ✅ JSON response for AJAX

### **4. Routes** ✅
**File:** `routes/web.php`

```php
Route::post('cards/{card}/reviews', [CardReviewController::class, 'store'])
    ->name('cards.reviews.store');
    
Route::get('cards/{card}/reviews', [CardReviewController::class, 'index'])
    ->name('cards.reviews.index');
```

### **5. Blade Component** ✅
**File:** `resources/views/components/ui/card-detail-modal.blade.php`

**Added:**
- Review section (hanya tampil untuk Team Lead/Admin)
- Conditional display (hanya untuk card dengan status "review")
- Notes textarea (opsional, max 2000 karakter)
- Approve button (green)
- Request Change button (yellow)
- Loading states
- JavaScript handler `handleReview(status)`

---

## 🎨 UI Components

### **Review Section in Card Detail Modal**

```blade
<!-- Only visible if card status is "review" AND user is Team Lead/Admin -->
<div x-show="selectedCard?.status === 'review'">
    <h4>Review Card</h4>
    
    <!-- Optional Notes Input -->
    <textarea x-model="reviewNotes" 
              placeholder="Keterangan untuk developer (opsional)..."
              rows="3"></textarea>
    
    <!-- Approve Button -->
    <button @click="handleReview('approved')">
        ✓ Approve
    </button>
    
    <!-- Request Change Button -->
    <button @click="handleReview('rejected')">
        ⚠ Request Change
    </button>
</div>
```

### **Color Coding**
- **Approve Button**: Green (`bg-green-600`)
- **Request Change Button**: Yellow (`bg-yellow-600`)
- **Loading State**: Spinner animation

---

## 💻 JavaScript Implementation

### **Alpine.js Data**

```javascript
{
    // Review state
    reviewNotes: '',
    isReviewing: false,
    
    // Handle review
    async handleReview(status) {
        // 1. Validation
        // 2. Confirmation dialog
        // 3. Send AJAX request
        // 4. Handle response
        // 5. Reload page or update UI
        // 6. Dispatch event for realtime updates
    }
}
```

### **AJAX Request**

```javascript
const formData = new FormData();
formData.append('_token', csrfToken);
formData.append('status', 'approved'); // or 'rejected'
formData.append('notes', reviewNotes);

const response = await fetch(`/cards/${cardId}/reviews`, {
    method: 'POST',
    body: formData,
    headers: { 'Accept': 'application/json' }
});
```

---

## 📊 Database Operations

### **When APPROVED:**

```php
// 1. Create review record
CardReview::create([
    'card_id' => $card->id,
    'reviewed_by' => $user->id,
    'status' => 'approved',
    'notes' => $notes,
    'reviewed_at' => now(),
]);

// 2. Update card status
$card->update(['status' => 'done']);

// 3. Update assignments
CardAssignment::where('card_id', $card->id)
    ->where('assignment_status', '!=', 'completed')
    ->update([
        'assignment_status' => 'completed',
        'completed_at' => now(),
    ]);
```

### **When REJECTED:**

```php
// 1. Create review record
CardReview::create([
    'card_id' => $card->id,
    'reviewed_by' => $user->id,
    'status' => 'rejected',
    'notes' => $notes, // Usually contains reason
    'reviewed_at' => now(),
]);

// 2. Update card status back to todo
$card->update(['status' => 'todo']);

// 3. Assignments remain unchanged
```

---

## 📡 Realtime Broadcasting

### **Event Dispatched:**

```php
event(new CardReviewed($cardReview, $card->fresh()));
```

### **Channel:**
```
Channel: 'projects.{project_id}'
Event Name: 'card.reviewed'
```

### **Broadcast Data:**

```json
{
    "review": {
        "id": 1,
        "card_id": 123,
        "reviewed_by": 5,
        "reviewer_name": "John Doe",
        "status": "approved",
        "notes": "Great work!",
        "reviewed_at": "2025-11-11 14:30:00"
    },
    "card": {
        "id": 123,
        "card_title": "Implement Login Feature",
        "status": "done",
        "board_id": 10,
        "project_id": 3
    },
    "message": "Card 'Implement Login Feature' telah di-approve!"
}
```

### **Frontend Listener (Laravel Echo):**

```javascript
// In your main JS file
Echo.channel('projects.' + PROJECT_ID)
    .listen('.card.reviewed', (e) => {
        console.log('Card reviewed:', e);
        
        // Show notification
        showNotification(e.message);
        
        // Update card in UI
        updateCardInUI(e.card);
        
        // Optionally refresh board
        if (e.card.board_id === currentBoardId) {
            refreshBoard();
        }
    });
```

---

## 🧪 Testing

### **Manual Testing Steps:**

1. **Login as Developer:**
   - Create/edit a card
   - Set status to "Review"
   - Verify buttons NOT visible in card detail modal

2. **Login as Team Lead:**
   - Open card with status "Review"
   - Verify review buttons visible
   - Test Approve:
     - Click "Approve"
     - Verify confirmation dialog
     - Verify card status → "Done"
     - Verify assignments → "Completed"
     - Verify completed_at filled
   - Test Request Change:
     - Set card to "Review" again
     - Enter notes
     - Click "Request Change"
     - Verify card status → "Todo"
     - Verify notes saved

3. **Login as Admin:**
   - Verify can review any card
   - Same tests as Team Lead

### **API Testing (Postman/Insomnia):**

```bash
POST /cards/{card_id}/reviews
Headers:
  Accept: application/json
  X-CSRF-TOKEN: {token}
Body (form-data):
  status: approved
  notes: Optional feedback text

Expected Response (200):
{
    "success": true,
    "message": "Card berhasil di-approve! Status diubah menjadi Done.",
    "review": {
        "id": 1,
        "status": "approved",
        "notes": "Great work!",
        "reviewed_by": "John Doe",
        "reviewed_at": "11 Nov 2025, 14:30"
    },
    "card": {
        "id": 123,
        "status": "done"
    }
}
```

---

## 🔄 Integration with Existing Features

### **1. Card Status Flow:**
```
Todo → In Progress → Review → (Approve) → Done
                           → (Reject) → Todo (loop back)
```

### **2. Card Assignments:**
- Approve: All assignments marked "completed"
- Reject: Assignments unchanged

### **3. Time Tracking:**
- Not affected by review
- Time logs remain unchanged

### **4. Comments:**
- Developer can add comments during review
- Team Lead can add notes via review system

---

## 📚 Related Models & Relationships

### **Card Model:**
```php
// Get review history
$card->reviews; // HasMany relationship

// Latest review
$card->reviews()->latest('reviewed_at')->first();

// Check if approved
$card->reviews()->approved()->exists();
```

### **CardReview Model:**
```php
// Relationships
$cardReview->card; // BelongsTo
$cardReview->reviewer; // BelongsTo User

// Helpers
$cardReview->isApproved(); // boolean
$cardReview->isRejected(); // boolean
$cardReview->status_badge_color; // Tailwind classes
```

---

## 🎯 Next Steps (Optional Enhancements)

### **1. Review History Display**
```blade
<!-- In card detail modal -->
<div class="review-history">
    <h4>Review History</h4>
    @foreach($card->reviews as $review)
        <div class="review-item">
            <span>{{ $review->status_text }}</span>
            <span>by {{ $review->reviewer->full_name }}</span>
            <span>{{ $review->reviewed_at_formatted }}</span>
            @if($review->notes)
                <p>{{ $review->notes }}</p>
            @endif
        </div>
    @endforeach
</div>
```

### **2. Email Notification**
```php
// After review
Mail::to($card->creator->email)
    ->send(new CardReviewedMail($cardReview, $card));
```

### **3. In-App Notification**
```php
// Create notification record
Notification::create([
    'user_id' => $card->created_by,
    'type' => 'card_reviewed',
    'data' => json_encode([
        'card_id' => $card->id,
        'status' => $cardReview->status,
        'message' => $message,
    ]),
]);
```

### **4. Statistics Dashboard**
```php
// Review statistics
$approvedCount = $card->reviews()->approved()->count();
$rejectedCount = $card->reviews()->rejected()->count();
$averageReviewTime = // Calculate from reviews
```

---

## 🐛 Troubleshooting

### **Issue: Buttons not visible**
- Check user role: `Auth::user()->role`
- Check project membership: `$board->project->members`
- Check card status: Should be "review"

### **Issue: 403 Unauthorized**
- Verify user is Team Lead in the project
- Check BoardController loaded project with members
- Verify `$card->board->project->members` relationship

### **Issue: Broadcast not working**
- Check `.env`: `BROADCAST_DRIVER=pusher` (or redis)
- Verify Laravel Echo configured
- Check browser console for Echo errors
- Test with: `php artisan queue:work` if using queue

### **Issue: Assignments not updating**
- Check migration: `completed_at` column exists
- Verify CardAssignment model
- Check database transaction committed

---

## ✅ Feature Checklist

- [x] Request validation created
- [x] Event for broadcasting created
- [x] Controller with store/index methods
- [x] Routes registered
- [x] Blade component updated
- [x] JavaScript handler implemented
- [x] Authorization checks (Team Lead + Admin)
- [x] Card status update (approved → done, rejected → todo)
- [x] Card assignments update (completed_at, assignment_status)
- [x] Review history saved to card_reviews
- [x] Realtime broadcast event
- [x] Loading states
- [x] Error handling
- [x] User feedback (alerts/messages)
- [x] Documentation complete

---

## 📞 Support

For issues or questions about this feature:
1. Check console logs in browser (F12)
2. Check Laravel logs: `storage/logs/laravel.log`
3. Verify database transactions committed
4. Test with `php artisan tinker`

```php
// Test in tinker
$card = Card::find(123);
$user = User::find(5);
$review = CardReview::create([
    'card_id' => $card->id,
    'reviewed_by' => $user->id,
    'status' => 'approved',
    'notes' => 'Test',
    'reviewed_at' => now(),
]);
```

---

**Created:** 11 November 2025  
**Status:** ✅ **READY FOR PRODUCTION**  
**Version:** 1.0
