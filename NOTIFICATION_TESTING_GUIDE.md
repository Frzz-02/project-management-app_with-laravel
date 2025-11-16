# Notification System Testing Guide

## Overview
Panduan lengkap untuk test notification system yang baru saja dibuat. System ini akan mengirim notifikasi real-time ke developer/designer ketika card mereka di-review oleh team lead.

## Prerequisites
1. ✅ Migration notifications table sudah di-run
2. ✅ NotificationController sudah dibuat
3. ✅ Routes sudah registered
4. ✅ Layout dropdown sudah updated
5. ✅ Halaman notifications sudah dibuat

## Testing Flow

### 1. Setup Test Data

#### A. Check Existing Users
```bash
php artisan tinker
```

```php
// Get users
$users = App\Models\User::all();
foreach($users as $user) {
    echo "ID: {$user->id} - {$user->name} ({$user->email})\n";
}
```

#### B. Get Project with Team Lead and Members
```sql
-- Via tinker
$project = App\Models\Project::with('creator', 'members.user')->first();
echo "Project: {$project->name}\n";
echo "Creator (Team Lead): {$project->creator->name}\n";
foreach($project->members as $member) {
    echo "Member ({$member->role}): {$member->user->name}\n";
}
```

#### C. Get Card in Review Status
```php
$card = App\Models\Card::with('assignments.user')->where('status', 'review')->first();
if($card) {
    echo "Card: {$card->title}\n";
    echo "Status: {$card->status}\n";
    echo "Assigned users:\n";
    foreach($card->assignments as $assignment) {
        echo "- {$assignment->user->name} (Role: {$assignment->role})\n";
    }
}
```

#### D. Create Test Card (if needed)
```php
$board = App\Models\Board::first();
$developer = App\Models\User::where('email', 'developer@example.com')->first();

$card = App\Models\Card::create([
    'board_id' => $board->id,
    'title' => 'Test Notification Card',
    'description' => 'Testing notification system',
    'status' => 'review',
    'priority' => 'high',
    'position' => 1,
    'created_by' => $developer->id
]);

// Assign to developer
App\Models\CardAssignment::create([
    'card_id' => $card->id,
    'user_id' => $developer->id,
    'role' => 'developer'
]);

echo "Card created with ID: {$card->id}\n";
```

### 2. Test Notification Creation (Backend)

#### A. Via Tinker (Direct Test)
```bash
php artisan tinker
```

```php
// Get card in review
$card = App\Models\Card::with('assignments.user')->where('status', 'review')->first();

// Get team lead
$teamLead = App\Models\User::find(1); // Adjust ID

// Simulate notification creation
foreach($card->assignments as $assignment) {
    $notification = App\Models\Notification::create([
        'user_id' => $assignment->user_id,
        'type' => 'card_reviewed',
        'title' => '✅ Card Approved',
        'message' => "Your card '{$card->title}' has been approved by {$teamLead->name}",
        'data' => [
            'card_id' => $card->id,
            'card_title' => $card->title,
            'review_status' => 'approved',
            'review_notes' => 'Test notification',
            'reviewed_by' => $teamLead->name,
            'project_id' => $card->board->project_id,
            'board_id' => $card->board_id
        ]
    ]);
    
    echo "Notification created for {$assignment->user->name} (ID: {$notification->id})\n";
}
```

#### B. Via Browser (Actual Review Flow)
1. Login sebagai **Team Lead**
2. Navigate ke board dengan card status "review"
3. Click tombol **Approve** atau **Reject** pada card
4. Lihat console browser untuk response notification creation

### 3. Test Notification Display (Frontend)

#### A. Check Database
```sql
-- Via tinker
$notifications = App\Models\Notification::with('user')->latest()->get();
foreach($notifications as $notif) {
    echo "ID: {$notif->id}\n";
    echo "User: {$notif->user->name}\n";
    echo "Type: {$notif->type}\n";
    echo "Title: {$notif->title}\n";
    echo "Is Read: " . ($notif->is_read ? 'Yes' : 'No') . "\n";
    echo "Created: {$notif->created_at->diffForHumans()}\n\n";
}
```

#### B. Check API Endpoints
```bash
# Get recent notifications (requires authentication)
curl -X GET http://localhost:8000/api/notifications/recent \
  -H "Accept: application/json" \
  -H "Cookie: laravel_session=YOUR_SESSION_COOKIE"

# Get unread count
curl -X GET http://localhost:8000/api/notifications/unread-count \
  -H "Accept: application/json" \
  -H "Cookie: laravel_session=YOUR_SESSION_COOKIE"
```

#### C. Test Notification Bell Badge
1. Login sebagai **Developer/Designer** yang di-assign ke card
2. Check notification bell di header
3. Badge harus show jumlah unread notifications (merah)
4. Jika tidak ada unread, badge hidden

#### D. Test Notification Dropdown
1. Click notification bell icon
2. Dropdown harus show 10 latest notifications
3. Check elements:
   - ✅ Icon emoji sesuai type
   - ✅ Title bold untuk unread
   - ✅ Message
   - ✅ Time ago (e.g., "5 minutes ago")
   - ✅ Blue dot untuk unread notifications
4. Click notification → harus navigate ke board

#### E. Test "View All Notifications" Link
1. Click "View all notifications" di dropdown
2. Harus navigate ke `/notifications` page
3. Page harus show semua notifications dengan pagination

### 4. Test Notification Page Features

#### A. Filter Tabs
1. Click **All** → Show all notifications
2. Click **Unread** → Show only unread
3. Click **Read** → Show only read
4. Count di tab "All" harus sesuai total notifications

#### B. Mark as Read
1. Click notification item (unread)
2. Should navigate to board
3. Notification should be marked as read
4. Badge count should decrement
5. Blue left border should disappear

#### C. Mark All as Read
1. Click "Mark All as Read" button
2. Confirm dialog
3. All notifications should be marked as read
4. Badge should disappear
5. Filter should update

#### D. Delete Notification
1. Click trash icon on notification
2. Confirm dialog
3. Notification should be removed from list
4. Count should update

#### E. Delete All Read
1. Mark some notifications as read
2. Click "Delete All Read" button
3. Confirm dialog
4. Only read notifications should be deleted
5. Unread notifications remain

#### F. Pagination
1. If > 20 notifications exist
2. Pagination controls should appear
3. Click page numbers → load that page
4. Click "Previous"/"Next" → navigate pages

### 5. Test Real-Time Polling (30s Interval)

#### A. Setup
1. Login sebagai Developer in one browser tab
2. Login sebagai Team Lead in another tab/incognito

#### B. Test Flow
1. **Tab 1 (Developer)**: Stay on any page
2. **Tab 2 (Team Lead)**: Review a card assigned to developer
3. **Tab 1 (Developer)**: Wait max 30 seconds
4. Badge count should update automatically
5. Open dropdown → new notification should appear

### 6. Test Notification Types

#### A. Card Reviewed - Approved
```php
// Expected notification
[
    'type' => 'card_reviewed',
    'title' => '✅ Card Approved',
    'message' => "Your card 'Card Title' has been approved by Team Lead Name",
    'icon' => '✅',
    'color_class' => 'from-green-100 to-green-200'
]
```

#### B. Card Reviewed - Rejected
```php
// Expected notification
[
    'type' => 'card_reviewed',
    'title' => '🔄 Changes Requested',
    'message' => "Your card 'Card Title' needs changes. Team Lead Name said: 'Review notes here'",
    'icon' => '✅',
    'color_class' => 'from-green-100 to-green-200'
]
```

### 7. Test Navigation

#### A. From Dropdown
1. Click notification in dropdown
2. Should call `handleNotificationClick(notification)`
3. Should make PATCH request to mark as read
4. Should navigate to: `/projects/{project_id}/boards/{board_id}`

#### B. From Notifications Page
1. Click notification item
2. Should mark as read
3. Should navigate to board
4. Notification should update UI (remove blue border, show "Read" badge)

### 8. Test Error Handling

#### A. Network Error
1. Disconnect internet
2. Try mark as read → should log error in console
3. Try delete → should log error
4. Reconnect → polling should resume

#### B. Unauthorized Access
1. Logout
2. Navigate to `/notifications`
3. Should redirect to login
4. API calls should return 401

### 9. Test Performance

#### A. Many Notifications (100+)
```php
// Create 100 test notifications
$user = App\Models\User::find(1);
for($i = 1; $i <= 100; $i++) {
    App\Models\Notification::create([
        'user_id' => $user->id,
        'type' => 'card_reviewed',
        'title' => "Test Notification {$i}",
        'message' => "This is test notification number {$i}",
        'data' => ['test' => true]
    ]);
}
```

1. Check page load time
2. Check pagination works correctly
3. Check filtering doesn't lag
4. Check dropdown only loads 10 (not all 100)

#### B. Polling Performance
1. Open browser dev tools → Network tab
2. Wait 30 seconds
3. Should see 2 API calls:
   - `/api/notifications/recent`
   - `/api/notifications/unread-count`
4. Calls should be < 100ms response time

### 10. Database Verification

#### A. Check Notification Records
```sql
SELECT * FROM notifications ORDER BY created_at DESC LIMIT 10;
```

Expected columns:
- `id`
- `user_id` (FK to users)
- `type` (card_reviewed, card_assigned, etc)
- `title`
- `message`
- `data` (JSON)
- `is_read` (0 or 1)
- `read_at` (timestamp or NULL)
- `created_at`
- `updated_at`

#### B. Check Indexes
```sql
PRAGMA index_list('notifications');
```

Expected indexes:
- `notifications_user_id_is_read_index`
- `notifications_user_id_created_at_index`

#### C. Check Foreign Keys
```sql
PRAGMA foreign_key_list('notifications');
```

Expected:
- `user_id` → `users.id` ON DELETE CASCADE

## Expected Results Summary

### ✅ Backend
- [x] Notifications created when card reviewed
- [x] One notification per assigned user
- [x] Data JSON contains card_id, project_id, board_id
- [x] Type is 'card_reviewed'
- [x] Title shows approve/reject status
- [x] Message includes card title and reviewer name

### ✅ Frontend - Layout
- [x] Badge shows unread count
- [x] Badge hidden when count = 0
- [x] Dropdown shows 10 latest notifications
- [x] Dropdown items show icon, title, message, time
- [x] Blue dot for unread notifications
- [x] Click notification → marks read + navigates
- [x] "View all" link goes to /notifications

### ✅ Frontend - Notifications Page
- [x] Shows all notifications with pagination
- [x] Filter tabs work (all/unread/read)
- [x] Mark as read works
- [x] Mark all as read works
- [x] Delete notification works
- [x] Delete all read works
- [x] Pagination works
- [x] Click notification navigates to board

### ✅ Real-Time
- [x] Polling every 30 seconds
- [x] Badge updates automatically
- [x] Dropdown refreshes automatically

### ✅ Database
- [x] Migration ran successfully
- [x] Indexes created
- [x] Foreign keys work
- [x] Cascade delete works

## Troubleshooting

### Issue: Badge not showing
**Check:**
1. `/api/notifications/unread-count` returns correct count
2. Alpine.js `unreadCount` variable updates
3. Browser console for errors
4. CSRF token in meta tag

**Fix:**
```blade
<!-- Ensure this is in <head> -->
<meta name="csrf-token" content="{{ csrf_token() }}">
```

### Issue: Dropdown empty
**Check:**
1. `/api/notifications/recent` returns data
2. `notifications` array in Alpine.js
3. Database has notifications for logged-in user

**Fix:**
```php
// Create test notification
App\Models\Notification::create([
    'user_id' => auth()->id(),
    'type' => 'card_reviewed',
    'title' => 'Test',
    'message' => 'Test notification',
    'data' => ['test' => true]
]);
```

### Issue: Click notification doesn't navigate
**Check:**
1. `handleNotificationClick()` function exists
2. `notification.data` contains `project_id` and `board_id`
3. Browser console for JavaScript errors

**Fix:**
```javascript
// Check notification.data structure
console.log(notification.data);
// Should contain: {card_id, project_id, board_id, ...}
```

### Issue: Polling not working
**Check:**
1. `startPolling()` called in `init()`
2. `setInterval` syntax correct
3. Browser console for errors

**Fix:**
```javascript
// Check Alpine.js init
init() {
    this.loadNotifications();
    this.loadUnreadCount();
    this.startPolling(); // Must be called
}
```

### Issue: Mark as read not working
**Check:**
1. CSRF token included in request
2. `/api/notifications/{id}/read` route exists
3. User authorized (notification.user_id === auth.id)

**Fix:**
```javascript
// Check CSRF token
const token = document.querySelector('meta[name="csrf-token"]').content;
console.log('CSRF Token:', token);
```

## Next Steps

### Optional Enhancements
1. **Laravel Echo + Pusher**: Replace polling with WebSocket for true real-time
2. **Email Notifications**: Send email for important notifications
3. **Push Notifications**: Browser push notifications
4. **Notification Settings**: User preferences for notification types
5. **Notification Groups**: Group related notifications
6. **Rich Notifications**: Images, buttons, actions in notifications
7. **Desktop Notifications**: Browser desktop notifications API

### Performance Optimization
1. Cache unread count in Redis
2. Add database indexes for common queries
3. Implement notification archiving (delete old read notifications)
4. Add notification rate limiting

## Testing Checklist

- [ ] Notifications created when card reviewed
- [ ] Badge shows correct unread count
- [ ] Badge hidden when no unread
- [ ] Dropdown shows 10 latest notifications
- [ ] Click notification marks as read
- [ ] Click notification navigates to board
- [ ] "View all" link works
- [ ] Notifications page loads
- [ ] Filter tabs work (all/unread/read)
- [ ] Mark as read button works
- [ ] Mark all as read works
- [ ] Delete notification works
- [ ] Delete all read works
- [ ] Pagination works (if > 20 notifications)
- [ ] Polling updates badge every 30s
- [ ] Polling updates dropdown every 30s
- [ ] Icons show correctly (emoji)
- [ ] Time ago shows correctly
- [ ] Blue dot shows for unread
- [ ] Navigation to board works
- [ ] Database records correct
- [ ] Foreign keys work
- [ ] Indexes exist

## Summary

System notification sudah complete dengan fitur:
1. ✅ Backend notification creation saat card review
2. ✅ Real-time polling (30s) untuk update otomatis
3. ✅ Notification bell dengan badge count
4. ✅ Dropdown dengan 10 latest notifications
5. ✅ Halaman notifications lengkap dengan filter & pagination
6. ✅ Mark as read (single & bulk)
7. ✅ Delete notifications (single & bulk)
8. ✅ Navigation ke board dari notification
9. ✅ Database migration dengan indexes
10. ✅ API endpoints lengkap (8 endpoints)

**Ready for production testing!** 🚀
