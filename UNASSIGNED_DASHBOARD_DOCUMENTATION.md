# Unassigned Member Dashboard Documentation

## Overview
Dashboard khusus untuk user yang belum di-assign ke project manapun. Menampilkan welcome screen dengan:
- Profile completion tracker dengan circular progress
- Getting started guide dengan timeline steps
- System statistics (projects, members, tasks)
- FAQ accordion section
- Auto-refresh untuk deteksi assignment setiap 60 detik

## Architecture

### Controller: UnassignedMemberDashboardController

**Location**: `app/Http/Controllers/web/UnassignedMemberDashboardController.php`

**Methods**:

1. **index()** - Main dashboard view
   - Checks if user has project assignments
   - Redirects to member dashboard if assigned
   - Returns unassigned dashboard view with data

2. **calculateProfileCompletion()** - Private helper
   - Checks 5 profile fields: full_name, email_verified_at, username, profile_picture, created_at
   - Returns array: `['completed' => int, 'total' => int, 'percentage' => float]`

3. **getTutorialSteps()** - Private helper
   - Returns 4 tutorial steps with completion status
   - Steps: Complete Profile → Wait for Assignment → Start Tasks → Collaborate

4. **getSystemStats()** - Private helper
   - Returns statistics: total_projects, active_members, total_tasks_completed

5. **checkAssignment()** - API endpoint
   - Returns JSON: `{has_assignment: bool, redirect_url: string|null}`
   - Used by JavaScript for auto-detection

### View: unassigned-dashboard.blade.php

**Location**: `resources/views/member/unassigned-dashboard.blade.php`

**Sections**:

1. **Welcome Banner** (Gradient card)
   - User greeting with full_name or username
   - Welcoming message

2. **Status Alert** (Yellow alert box)
   - Hourglass icon
   - "Waiting for Project Assignment" message

3. **Profile Completion Card** (Left column)
   - Circular SVG progress indicator
   - 5-item checklist with checkmarks
   - "Complete Your Profile" button (if < 100%)

4. **Getting Started Guide** (Right column)
   - Timeline with 4 steps
   - Each step: icon, title, description, action button
   - Completed steps: green marker and background

5. **System Statistics** (3 cards)
   - Total Projects (blue gradient)
   - Active Members (green gradient)
   - Tasks Completed (purple gradient)

6. **FAQ Accordion** (4 questions)
   - How long does it take?
   - Can I choose projects?
   - What to do while waiting?
   - Will I be notified?

7. **Contact Support Card** (Purple gradient)
   - Email link to admin

**Custom CSS** (in @push('styles')):
- `.circular-progress` - SVG circular progress bar
- `.timeline` - Vertical timeline with gradient line
- `.timeline-item` - Timeline step container
- `.timeline-marker` - Circular icon badges
- `.faq-toggle` - FAQ accordion trigger

### JavaScript: dashboard.js

**Location**: `public/js/unassigned/dashboard.js`

**Functions**:

1. **checkAssignment()** - AJAX call to `/api/check-assignment`
   - Fetches assignment status
   - If assigned: shows notification, redirects after 2s

2. **showAssignmentNotification()** - Toast notification
   - Green success notification
   - "You've been assigned to a project!" message

3. **initFAQAccordion()** - FAQ collapse/expand
   - Toggles FAQ content display
   - Rotates arrow icon

**Auto-Check**:
- Runs immediately on page load
- Then runs every 60 seconds (setInterval)
- Console logs for debugging

### Routes

**Location**: `routes/web.php`

**Added Routes**:

```php
// Unassigned dashboard
Route::middleware('auth')->prefix('unassigned')->name('unassigned.')->group(function () {
    Route::get('/dashboard', [UnassignedMemberDashboardController::class, 'index'])
        ->name('dashboard');
});

// API endpoint
Route::middleware('auth')->get('/api/check-assignment', [UnassignedMemberDashboardController::class, 'checkAssignment'])
    ->name('api.check-assignment');
```

**Updated Main Dashboard Route** (`/dashboard`):
- Smart routing logic:
  1. Admin → `admin.dashboard`
  2. Team Lead → `team-leader.dashboard`
  3. Member with projects → `member.dashboard`
  4. Member without projects → `unassigned.dashboard`

## User Flow

### 1. New User Registration
```
Register → Email Verification → Login → /dashboard redirect
```

### 2. First Dashboard Visit (Unassigned)
```
/dashboard → Check roles/assignments → Redirect to /unassigned/dashboard
```

### 3. Unassigned Dashboard
```
View welcome screen
→ Complete profile (click button → /profile/edit)
→ Read FAQ, view stats
→ JavaScript checks assignment every 60s
```

### 4. Admin Assigns User
```
Admin adds user to project with role (developer/designer)
→ ProjectMember record created
```

### 5. Auto-Detection
```
JavaScript checkAssignment() → API returns has_assignment: true
→ Show notification: "You've been assigned!"
→ Wait 2 seconds → Redirect to /dashboard
```

### 6. Smart Routing
```
/dashboard → Check ProjectMember → Redirect to /member/dashboard
```

### 7. Member Dashboard
```
User now sees full member dashboard with tasks, time tracking, etc.
```

## Testing Guide

### Test Scenario 1: New Unassigned User

1. Create new user:
```php
php artisan tinker
$user = User::create([
    'username' => 'newbie',
    'email' => 'newbie@example.com',
    'password' => Hash::make('password'),
    'full_name' => 'New User',
    'is_admin' => false
]);
```

2. Login as newbie@example.com
3. Should redirect to `/unassigned/dashboard`
4. Verify:
   - Welcome banner shows "New User"
   - Profile completion < 100% (no profile picture)
   - 4 timeline steps visible
   - Only step 1 partially completed
   - FAQ accordion works
   - System stats display correctly

### Test Scenario 2: Profile Completion

1. Click "Complete Your Profile"
2. Upload profile picture
3. Return to dashboard
4. Verify:
   - Profile completion increases to 100%
   - Profile picture checkmark turns green
   - "Complete Your Profile" button disappears

### Test Scenario 3: Auto-Detection

1. Open unassigned dashboard
2. Open browser console (F12)
3. Should see: "Unassigned Dashboard initialized", "Auto-check running every 60 seconds"
4. In another tab/terminal, assign user to project:
```php
php artisan tinker
ProjectMember::create([
    'project_id' => 1,
    'user_id' => User::where('email', 'newbie@example.com')->first()->id,
    'role' => 'developer'
]);
```
5. Wait up to 60 seconds
6. Should see green notification: "Great News! You've been assigned to a project!"
7. Automatically redirects to `/member/dashboard`

### Test Scenario 4: FAQ Accordion

1. Click first FAQ question
2. Content should expand, arrow rotates 180°
3. Click another question
4. Previous FAQ closes, new one opens
5. Click same question again
6. FAQ should close

### Test Scenario 5: Manual Assignment Check

1. Open unassigned dashboard
2. Open browser console
3. Run: `checkAssignment()`
4. Check Network tab for API call to `/api/check-assignment`
5. Response should be: `{has_assignment: false, redirect_url: null}`

### Test Scenario 6: Direct Route Access

1. Try accessing `/unassigned/dashboard` after being assigned
2. Should redirect to `/member/dashboard`

### Test Scenario 7: API Endpoint

1. Test via curl:
```bash
curl -X GET http://localhost:8000/api/check-assignment \
  -H "Accept: application/json" \
  -H "Cookie: laravel_session=YOUR_SESSION_COOKIE"
```
2. Should return JSON with assignment status

## Troubleshooting

### Issue: Dashboard not loading
**Solution**: Check if Font Awesome CDN is loaded (needed for timeline icons)

### Issue: Auto-check not working
**Solution**: 
1. Check browser console for errors
2. Verify `/api/check-assignment` route is registered
3. Check CSRF token is valid

### Issue: Profile completion stuck at 80%
**Solution**: User needs to upload profile picture (last 20%)

### Issue: FAQ accordion not working
**Solution**: 
1. Check JavaScript console for errors
2. Verify `initFAQAccordion()` is called on DOMContentLoaded

### Issue: Redirect loop
**Solution**: 
1. Check MemberMiddleware logic
2. Verify user has correct role in ProjectMember table

### Issue: Timeline not displaying correctly
**Solution**: Check if custom CSS in @push('styles') is loaded

## Database Queries

### Check if user has assignments:
```sql
SELECT * FROM project_members WHERE user_id = ?;
```

### Get user's role:
```sql
SELECT role FROM project_members WHERE user_id = ? LIMIT 1;
```

### System statistics queries:
```sql
-- Total projects
SELECT COUNT(*) FROM projects;

-- Active members (currently working)
SELECT COUNT(*) FROM users WHERE current_task_status = 'working';

-- Completed tasks
SELECT COUNT(*) FROM cards WHERE status = 'done';
```

## Future Enhancements

### 1. Email Notifications
- Send welcome email on registration
- Notify when assigned to project
- Reminder email if profile incomplete after 3 days

### 2. Enhanced Profile Completion
- Add more fields: bio, skills, timezone
- Show progress breakdown by category
- Tips for completing profile

### 3. Tutorial Videos
- Embed YouTube tutorials
- Step-by-step walkthrough
- Project role explanations

### 4. Admin Notification
- Alert admin when new member registers
- Show pending assignments in admin dashboard
- Quick-assign button from admin panel

### 5. Skills Matching
- User enters skills in profile
- System suggests suitable projects
- Admin sees skill match percentage

### 6. Faster Auto-Check
- Use WebSockets or Server-Sent Events
- Instant notification without 60s delay
- Push notification support

### 7. Onboarding Checklist
- More granular steps
- Mark tasks as complete manually
- Gamification with badges

### 8. Project Preview
- Show available projects (public info only)
- Team size, tech stack, duration
- "Express Interest" button

## API Documentation

### GET /api/check-assignment

**Description**: Check if authenticated user has been assigned to any project

**Authentication**: Required (session-based)

**Request**:
```http
GET /api/check-assignment HTTP/1.1
Host: localhost:8000
Accept: application/json
X-Requested-With: XMLHttpRequest
Cookie: laravel_session=...
```

**Response (Not Assigned)**:
```json
{
  "has_assignment": false,
  "redirect_url": null
}
```

**Response (Assigned)**:
```json
{
  "has_assignment": true,
  "redirect_url": "http://localhost:8000/dashboard"
}
```

**HTTP Status Codes**:
- 200: Success
- 401: Unauthenticated
- 500: Server error

**Rate Limiting**: Not implemented (consider adding: `throttle:60,1`)

## Configuration

### Auto-Check Interval
Change in `public/js/unassigned/dashboard.js`:
```javascript
// Default: 60 seconds
setInterval(checkAssignment, 60000);

// Change to 30 seconds:
setInterval(checkAssignment, 30000);
```

### Profile Completion Fields
Modify in `UnassignedMemberDashboardController::calculateProfileCompletion()`:
```php
// Add more fields:
if ($user->bio) $completed++;
if ($user->phone) $completed++;
$total = 7; // Update total
```

### Tutorial Steps
Edit in `UnassignedMemberDashboardController::getTutorialSteps()`:
```php
// Add new step:
[
    'icon' => 'fas fa-book',
    'title' => 'Read Documentation',
    'description' => 'Learn about the system',
    'completed' => false,
    'action_url' => '/docs',
    'action_text' => 'View Docs'
]
```

## Security Considerations

1. **Authentication**: All routes use `auth` middleware
2. **Authorization**: No special permission needed (all authenticated users can access)
3. **CSRF Protection**: API endpoint should include CSRF token for POST requests
4. **Rate Limiting**: Consider adding throttle middleware to API endpoint
5. **Input Validation**: No user input accepted (read-only dashboard)
6. **XSS Protection**: Blade escapes all user data automatically

## Performance Optimization

1. **Caching**: System stats could be cached for 5 minutes
2. **Eager Loading**: Not needed (simple queries)
3. **Database Indexing**: Ensure index on `project_members.user_id`
4. **JavaScript**: Auto-check only runs when user active (page visible)
5. **CDN**: Font Awesome loaded from CDN (cache-friendly)

## Maintenance

### Clear Cache
```bash
php artisan cache:clear
php artisan route:clear
php artisan config:clear
```

### Update Font Awesome
Replace CDN link in `layouts/app.blade.php` with latest version

### Monitor Auto-Check
Check server logs for `/api/check-assignment` request frequency

### Database Cleanup
No cleanup needed (read-only dashboard, no data created)

---

**Last Updated**: 2024
**Version**: 1.0.0
**Maintainer**: Development Team
