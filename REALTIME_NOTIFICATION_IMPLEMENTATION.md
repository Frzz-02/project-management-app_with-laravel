# 🔔 Realtime Notification System - Complete Implementation Guide

## Overview
Sistem notifikasi realtime untuk developer/designer ketika card mereka di-review oleh team lead. Menggunakan Laravel Broadcasting, Alpine.js, dan Pusher/Laravel Echo untuk realtime updates.

---

## 📋 Table of Contents
1. [Database Setup](#database-setup)
2. [Backend Implementation](#backend-implementation)
3. [Frontend Implementation](#frontend-implementation)
4. [Routes Configuration](#routes-configuration)
5. [Testing Guide](#testing-guide)
6. [Troubleshooting](#troubleshooting)

---

## 1. Database Setup ✅

### Migration: `create_notifications_table.php`
```php
Schema::create('notifications', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('type'); // card_reviewed, card_assigned, etc
    $table->string('title');
    $table->text('message');
    $table->json('data')->nullable(); // card_id, project_id, review_status, dll
    $table->boolean('is_read')->default(false);
    $table->timestamp('read_at')->nullable();
    $table->timestamps();
    
    $table->index(['user_id', 'is_read']);
    $table->index(['user_id', 'created_at']);
});
```

**Run migration:**
```bash
php artisan migrate
```

✅ **Status: COMPLETED**

---

## 2. Backend Implementation

### A. Notification Model ✅

**File:** `app/Models/Notification.php`

**Key Features:**
- Type constants untuk different notification types
- Scopes: `unread()`, `read()`, `recent()`
- Helper methods: `markAsRead()`, `markAsUnread()`
- Accessors: `time_ago`, `icon`, `color_class`

**Notification Types:**
```php
const TYPE_CARD_REVIEWED = 'card_reviewed';       // ✅ Card approved/rejected
const TYPE_CARD_ASSIGNED = 'card_assigned';       // 📋 Assigned to task
const TYPE_DEADLINE_REMINDER = 'deadline_reminder'; // ⏰ Deadline approaching
const TYPE_COMMENT_ADDED = 'comment_added';       // 💬 New comment
```

---

### B. CardReviewController Updates ✅

**File:** `app/Http/Controllers/web/CardReviewController.php`

**Added Logic:**
```php
// Step 4: Create notifications untuk semua assigned developers/designers
$assignedUsers = $card->assignments()->with('user')->get();

foreach ($assignedUsers as $assignment) {
    $notificationTitle = $validated['status'] === 'approved' 
        ? '✅ Card Approved' 
        : '🔄 Changes Requested';
    
    $notificationMessage = $validated['status'] === 'approved'
        ? "Your card \"{$card->card_title}\" has been approved by {$user->username}."
        : "Changes requested for your card \"{$card->card_title}\" by {$user->username}.";
    
    if (!empty($validated['notes'])) {
        $notificationMessage .= " Notes: " . $validated['notes'];
    }
    
    Notification::create([
        'user_id' => $assignment->user_id,
        'type' => Notification::TYPE_CARD_REVIEWED,
        'title' => $notificationTitle,
        'message' => $notificationMessage,
        'data' => [
            'card_id' => $card->id,
            'card_title' => $card->card_title,
            'review_status' => $validated['status'],
            'review_notes' => $validated['notes'] ?? null,
            'reviewed_by' => $user->username,
            'project_id' => $card->board->project_id,
            'board_id' => $card->board_id,
        ],
    ]);
}
```

**Flow:**
1. Team Lead approve/reject card
2. Loop semua assigned developers/designers
3. Create notification record di database
4. Broadcast event `CardReviewed` (already implemented)

---

### C. NotificationController ✅

**File:** `app/Http/Controllers/NotificationController.php`

**Endpoints:**

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/notifications` | Get all notifications (paginated) |
| GET | `/api/notifications/recent` | Get 10 recent for dropdown |
| GET | `/api/notifications/unread-count` | Get unread count |
| PATCH | `/api/notifications/{id}/read` | Mark single as read |
| POST | `/api/notifications/mark-all-read` | Mark all as read |
| DELETE | `/api/notifications/{id}` | Delete single notification |
| DELETE | `/api/notifications/delete-all-read` | Delete all read notifications |

**Key Features:**
- Authorization check (user can only access own notifications)
- Pagination support
- Filter support (all/unread/read)
- Bulk operations (mark all as read, delete all read)

---

## 3. Frontend Implementation

### A. Update Layout Notification Dropdown

**File:** `resources/views/layouts/app.blade.php`

**Current Implementation:**
```blade
<body x-data="{ 
    notifications: [
      { id: 1, title: 'New task assigned', ... } // Static dummy data
    ]
}">
```

**Need to Update to:**
```blade
<body x-data="{
    notifications: [],
    unreadCount: 0,
    notificationDropdownOpen: false,
    
    init() {
        this.loadNotifications();
        this.startPolling();
    },
    
    async loadNotifications() {
        try {
            const response = await fetch('/api/notifications/recent');
            const data = await response.json();
            if (data.success) {
                this.notifications = data.notifications;
            }
        } catch (error) {
            console.error('Error loading notifications:', error);
        }
    },
    
    async loadUnreadCount() {
        try {
            const response = await fetch('/api/notifications/unread-count');
            const data = await response.json();
            if (data.success) {
                this.unreadCount = data.unread_count;
            }
        } catch (error) {
            console.error('Error loading unread count:', error);
        }
    },
    
    startPolling() {
        // Poll every 30 seconds
        setInterval(() => {
            this.loadNotifications();
            this.loadUnreadCount();
        }, 30000);
    },
    
    async markAsRead(notificationId) {
        try {
            const response = await fetch(`/api/notifications/${notificationId}/read`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                }
            });
            
            if (response.ok) {
                this.loadNotifications();
                this.loadUnreadCount();
            }
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    }
}">
```

**Update Button Badge:**
```blade
<span class="absolute -top-1 -right-1 h-5 w-5 rounded-full bg-red-500 flex items-center justify-center text-xs text-white font-medium" 
      x-show="unreadCount > 0"
      x-text="unreadCount"></span>
```

**Update "View All" Button:**
```blade
<a href="/notifications" class="w-full text-center text-sm font-medium text-blue-600 hover:text-blue-700 transition-colors">
    View all notifications
</a>
```

---

### B. Create Notifications Page

**File:** `resources/views/notifications/index.blade.php`

Create new directory dan file dengan struktur lengkap halaman notifications.

---

## 4. Routes Configuration

**File:** `routes/web.php`

**Add these routes:**

```php
use App\Http\Controllers\NotificationController;

// Notification Routes (requires auth)
Route::middleware(['auth'])->group(function () {
    // Web route - notifications page
    Route::get('/notifications', [NotificationController::class, 'page'])->name('notifications.index');
    
    // API routes for AJAX
    Route::prefix('api/notifications')->name('notifications.')->group(function () {
        Route::get('/recent', [NotificationController::class, 'recent'])->name('recent');
        Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
        Route::get('/', [NotificationController::class, 'index'])->name('api.index');
        Route::patch('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy');
        Route::delete('/read/all', [NotificationController::class, 'deleteAllRead'])->name('delete-all-read');
    });
});
```

---

## 5. Realtime Broadcasting Setup (Optional)

### For TRUE Realtime (without polling):

**Step 1: Install Laravel Echo & Pusher**
```bash
npm install --save laravel-echo pusher-js
```

**Step 2: Configure `.env`**
```env
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=ap1
```

**Step 3: Update `resources/js/bootstrap.js`**
```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true
});
```

**Step 4: Listen to Events in Alpine.js**
```javascript
init() {
    this.loadNotifications();
    
    // Listen untuk CardReviewed event
    window.Echo.private(`user.${userId}`)
        .listen('CardReviewed', (e) => {
            console.log('New review notification:', e);
            this.loadNotifications();
            this.loadUnreadCount();
            
            // Optional: Show toast notification
            this.showToast(e.review.status === 'approved' 
                ? '✅ Your card was approved!' 
                : '🔄 Changes requested for your card');
        });
}
```

---

## 6. Testing Guide

### A. Manual Testing Flow

**Test Scenario: Developer receives notification when card reviewed**

1. **Setup:**
   - Login sebagai Admin/Team Lead (User A)
   - Ada card dengan status "review" yang di-assign ke Developer (User B)

2. **Create Review:**
   ```
   User A (Team Lead):
   1. Open board dengan card status "review"
   2. Click "Approve" atau "Request Changes"
   3. Add notes (optional): "Great work!" atau "Please fix button alignment"
   4. Submit review
   ```

3. **Check Database:**
   ```sql
   -- Check card_reviews table
   SELECT * FROM card_reviews WHERE card_id = X ORDER BY created_at DESC LIMIT 1;
   
   -- Check notifications table  
   SELECT * FROM notifications WHERE user_id = {developer_user_id} ORDER BY created_at DESC LIMIT 1;
   ```
   
   **Expected:**
   - New record in `card_reviews`
   - New record in `notifications` untuk setiap assigned developer
   - `type` = 'card_reviewed'
   - `data` JSON contains card_id, review_status, notes

4. **Check Frontend (User B - Developer):**
   ```
   Login sebagai Developer (User B):
   1. Open any page
   2. Look at notification bell icon → Badge should show "1"
   3. Click bell → Dropdown shows new notification
   4. Notification title: "✅ Card Approved" atau "🔄 Changes Requested"
   5. Notification message: includes card title dan notes
   6. Click notification → should mark as read (badge decrements)
   7. Click "View all notifications" → redirect to /notifications page
   ```

---

### B. API Testing (Postman/Thunder Client)

**1. Get Recent Notifications**
```http
GET /api/notifications/recent
Authorization: Bearer {token}

Response:
{
    "success": true,
    "notifications": [
        {
            "id": 1,
            "type": "card_reviewed",
            "title": "✅ Card Approved",
            "message": "Your card \"UI Design Task\" has been approved by john_team_lead. Notes: Great work!",
            "data": {
                "card_id": 12,
                "card_title": "UI Design Task",
                "review_status": "approved",
                "review_notes": "Great work!",
                "reviewed_by": "john_team_lead",
                "project_id": 5,
                "board_id": 8
            },
            "is_read": false,
            "time_ago": "2 minutes ago",
            "icon": "✅",
            "color_class": "bg-green-100 text-green-600"
        }
    ]
}
```

**2. Get Unread Count**
```http
GET /api/notifications/unread-count

Response:
{
    "success": true,
    "unread_count": 3
}
```

**3. Mark as Read**
```http
PATCH /api/notifications/1/read

Response:
{
    "success": true,
    "message": "Notification marked as read"
}
```

---

## 7. Troubleshooting

### Issue 1: Notifications Not Created

**Symptom:** Card review berhasil tapi tidak ada notification di database

**Check:**
```php
// In CardReviewController, add debug log
\Log::info('Creating notifications for card', [
    'card_id' => $card->id,
    'assigned_users_count' => $assignedUsers->count(),
]);

foreach ($assignedUsers as $assignment) {
    \Log::info('Creating notification', [
        'user_id' => $assignment->user_id,
        'card_title' => $card->card_title,
    ]);
    
    Notification::create([...]);
}
```

**Check logs:**
```bash
tail -f storage/logs/laravel.log
```

**Common Causes:**
- Card tidak memiliki assignments (check `card_assignments` table)
- Transaction rollback karena error lain
- Notification model fillable tidak lengkap

---

### Issue 2: Frontend Not Showing Notifications

**Symptom:** Notification ada di database tapi tidak muncul di dropdown

**Check:**
1. Browser console untuk errors
2. Network tab untuk API call `/api/notifications/recent`
3. Response dari API

**Debug:**
```javascript
// Add to Alpine init()
console.log('Loading notifications...');
const response = await fetch('/api/notifications/recent');
console.log('Response:', response);
const data = await response.json();
console.log('Data:', data);
```

---

### Issue 3: Unread Count Not Updating

**Symptom:** Badge masih show 0 padahal ada unread notifications

**Check:**
```sql
SELECT COUNT(*) FROM notifications 
WHERE user_id = X AND is_read = false;
```

**Fix:**
- Pastikan polling interval berjalan
- Check API `/api/notifications/unread-count` return correct count
- Verify Alpine.js `unreadCount` reactive variable updated

---

## 8. Next Steps & Enhancements

### Phase 1: Basic (CURRENT) ✅
- [x] Database schema
- [x] Notification model
- [x] Create notifications on card review
- [x] API endpoints
- [ ] Update layout dropdown
- [ ] Create notifications page
- [ ] Add routes

### Phase 2: Realtime
- [ ] Setup Laravel Echo + Pusher
- [ ] Listen to CardReviewed event
- [ ] Update UI without polling

### Phase 3: Additional Features
- [ ] Email notifications
- [ ] Slack/Discord integration
- [ ] Push notifications (PWA)
- [ ] Notification preferences (user settings)
- [ ] Mute/snooze notifications
- [ ] Rich notifications (dengan gambar/preview)

---

## 9. File Checklist

| File | Status | Description |
|------|--------|-------------|
| `database/migrations/2025_11_12_144427_create_notifications_table.php` | ✅ | Notifications table schema |
| `app/Models/Notification.php` | ✅ | Notification model |
| `app/Http/Controllers/NotificationController.php` | ✅ | API endpoints |
| `app/Http/Controllers/web/CardReviewController.php` | ✅ | Updated with notification logic |
| `routes/web.php` | ⏳ | Need to add notification routes |
| `resources/views/layouts/app.blade.php` | ⏳ | Need to update dropdown |
| `resources/views/notifications/index.blade.php` | ⏳ | Need to create |

---

**Last Updated:** November 12, 2025
**Author:** GitHub Copilot
**Status:** Backend ✅ | Frontend ⏳ | Testing ⏳
