# Card Review History untuk Developer/Designer

Fitur untuk menampilkan history approve/reject dari semua card yang di-assign kepada developer/designer.

## 📋 Overview

Halaman ini menampilkan timeline review history dari card yang di-assign ke user yang login, khusus untuk role **developer** dan **designer** (berdasarkan tabel `project_members`).

### Fitur Utama
- ✅ Timeline view dengan grouping by date
- 🔍 Search functionality (card title, reviewer name, notes)
- 🎯 Filter status (All / Approved / Rejected)
- 📊 Statistics cards (Total, Approved, Rejected)
- 📱 Responsive design (mobile-first)
- 🎨 Glassmorphism UI dengan Tailwind
- 🔐 Role-based access (hanya developer/designer)

---

## 🗂️ File Structure

### 1. Controller
**File**: `app/Http/Controllers/web/CardReviewController.php`

**Method**: `myReviews()`

```php
/**
 * Show review history page untuk Developer/Designer
 * 
 * Menampilkan semua review dari card yang di-assign ke user yang login
 * Hanya untuk user dengan role 'developer' atau 'designer' di project_members
 */
public function myReviews()
```

#### Authorization Logic:
```php
// Get semua card yang assigned ke user
$assignedCardIds = CardAssignment::where('user_id', $user->id)
    ->pluck('card_id')
    ->unique();

// Check role developer/designer dari project_members
$isDeveloperOrDesigner = DB::table('project_members')
    ->where('user_id', $user->id)
    ->whereIn('role', ['developer', 'designer'])
    ->exists();

// Redirect jika bukan developer/designer (kecuali admin)
if (!$isDeveloperOrDesigner && $user->role !== 'admin') {
    return redirect()->route('dashboard')
        ->with('error', 'Halaman ini hanya untuk Developer dan Designer.');
}
```

#### Query Features:
- **Eager Loading**: `card.board.project`, `reviewer`
- **Filtering**: Status (all/approved/rejected)
- **Search**: Card title, reviewer name, notes
- **Pagination**: 15 items per page
- **Grouping**: By date untuk timeline view

#### Statistics Calculation:
```php
$stats = [
    'total' => CardReview::whereIn('card_id', $assignedCardIds)->count(),
    'approved' => CardReview::whereIn('card_id', $assignedCardIds)
        ->where('status', 'approved')->count(),
    'rejected' => CardReview::whereIn('card_id', $assignedCardIds)
        ->where('status', 'rejected')->count(),
];
```

---

### 2. View
**File**: `resources/views/card-reviews/my-reviews.blade.php`

#### Component Breakdown:

**A. Header Section**
- Page title dengan emoji icon 📋
- Description text
- Statistics cards (Total, Approved, Rejected) dengan glassmorphism

**B. Filters & Search Section**
```blade
<div x-data="{ 
    statusFilter: '{{ $statusFilter ?? 'all' }}',
    search: '{{ $search ?? '' }}'
}">
```

Features:
- Filter buttons: All / Approved / Rejected
- Alpine.js reactive state
- Search input dengan icon
- Form submission on filter change

**C. Timeline Content**

Grouped by date dengan struktur:
```blade
@foreach($reviewsByDate as $date => $dateReviews)
    <!-- Date Header (sticky) -->
    <div class="sticky top-20 z-20">
        {{ \Carbon\Carbon::parse($date)->format('d M Y') }}
    </div>
    
    <!-- Reviews for this date -->
    @foreach($dateReviews as $review)
        <!-- Review Card -->
    @endforeach
@endforeach
```

**D. Review Card Components**

Setiap card menampilkan:
1. **Card Info**: Title + breadcrumb (Board > Project)
2. **Status Badge**: 
   - ✅ Approved (gradient green, shadow)
   - ❌ Changes Requested (gradient red, shadow)
3. **Reviewer Info**: Avatar, name, timestamp
4. **Notes** (optional): Displayed in grey box dengan icon

**E. Empty State**
- Icon dengan background gradient
- Contextual message (based on filters)
- Reset filter button (jika ada filter aktif)

---

### 3. Route
**File**: `routes/web.php`

```php
Route::get('/my-card-reviews', [CardReviewController::class, 'myReviews'])
    ->name('card-reviews.my-reviews');
```

Berada di dalam `Route::middleware('auth')->group()`

---

### 4. Navigation Menu
**File**: `resources/views/layouts/app.blade.php`

#### Desktop Sidebar & Mobile Sidebar:

```blade
@php
    $isDeveloperOrDesigner = false;
    if(Auth::check()) {
        $isDeveloperOrDesigner = DB::table('project_members')
            ->where('user_id', Auth::id())
            ->whereIn('role', ['developer', 'designer'])
            ->exists();
    }
@endphp

@if($isDeveloperOrDesigner)
<a href="{{ route('card-reviews.my-reviews') }}" class="...">
    <svg><!-- Clock icon --></svg>
    Review History
</a>
@endif
```

**Icon**: Clock icon (⏰ History)
**Conditional**: Hanya tampil jika user adalah developer/designer di project manapun

---

## 🎨 UI Design

### Color Scheme
- **Approved**: Green gradient (`from-green-500 to-emerald-600`)
- **Rejected**: Red gradient (`from-red-500 to-rose-600`)
- **Background**: Gradient (`from-blue-50 via-white to-purple-50`)
- **Cards**: Glassmorphism (`backdrop-blur-xl bg-white/70`)

### Responsive Breakpoints
- **Mobile**: Single column, stack elements
- **SM (640px+)**: Improved spacing, inline stats
- **MD (768px+)**: Better grid layouts
- **LG (1024px+)**: Desktop sidebar visible

### Z-Index Hierarchy
- **Date Header**: `z-20` (sticky top-20)
- **Navigation**: `z-30`
- **Content**: `z-10`

---

## 🔍 Features Detail

### 1. Filter System

**Status Filter**: 3 tombol dengan Alpine.js reactivity
```blade
@click="statusFilter = 'all'; $el.closest('form').submit()"
```

States:
- `all` (default) - Blue button active
- `approved` - Green button active
- `rejected` - Red button active

### 2. Search Functionality

**Search Fields**:
- Card title (`card.card_title`)
- Reviewer name (`reviewer.full_name`, `reviewer.username`)
- Review notes (`notes`)

**Query Logic**:
```php
$reviewsQuery->where(function ($query) use ($search) {
    $query->whereHas('card', function ($q) use ($search) {
        $q->where('card_title', 'like', "%{$search}%");
    })
    ->orWhereHas('reviewer', function ($q) use ($search) {
        $q->where('full_name', 'like', "%{$search}%")
          ->orWhere('username', 'like', "%{$search}%");
    })
    ->orWhere('notes', 'like', "%{$search}%");
});
```

### 3. Date Grouping

Controller mengirim 2 data:
```php
$reviews = $reviewsQuery->paginate(15); // For pagination
$reviewsByDate = $reviews->groupBy(function ($review) {
    return $review->reviewed_at->format('Y-m-d');
}); // For timeline grouping
```

View loop:
```blade
@foreach($reviewsByDate as $date => $dateReviews)
    <!-- Sticky date header -->
    {{ \Carbon\Carbon::parse($date)->format('d M Y') }}
    
    <!-- Reviews for this date -->
    @foreach($dateReviews as $review)
        <!-- Review card -->
    @endforeach
@endforeach
```

### 4. Statistics

**Real-time counts** dari database:
```php
'total' => CardReview::whereIn('card_id', $assignedCardIds)->count(),
'approved' => CardReview::whereIn('card_id', $assignedCardIds)
    ->where('status', 'approved')->count(),
'rejected' => CardReview::whereIn('card_id', $assignedCardIds)
    ->where('status', 'rejected')->count(),
```

Displayed di header dengan color-coded cards:
- Total: White/gray
- Approved: Green background
- Rejected: Red background

---

## 🔐 Authorization Flow

### Access Control

**Diagram Flow**:
```
User Login
    ↓
Check project_members table
    ↓
role IN ('developer', 'designer') ?
    ↓ YES                    ↓ NO
Show menu item          Hide menu item
    ↓
Access /my-card-reviews
    ↓
Controller checks role again
    ↓ NOT dev/designer
Return 403 or redirect to dashboard
```

**Menu Visibility**:
```php
$isDeveloperOrDesigner = DB::table('project_members')
    ->where('user_id', Auth::id())
    ->whereIn('role', ['developer', 'designer'])
    ->exists();
```

**Controller Authorization**:
```php
if (!$isDeveloperOrDesigner && $user->role !== 'admin') {
    return redirect()->route('dashboard')
        ->with('error', 'Halaman ini hanya untuk Developer dan Designer.');
}
```

**Admin Override**: Admin bisa akses walaupun bukan developer/designer

---

## 📊 Database Queries

### Main Query (Optimized)

```php
CardReview::with([
    'card.board.project',       // Eager load untuk breadcrumb
    'reviewer:id,full_name,username,email'  // Limited columns
])
->whereIn('card_id', $assignedCardIds)  // Only assigned cards
->orderBy('reviewed_at', 'desc')        // Newest first
```

### Performance Considerations:
- ✅ Eager loading prevents N+1 queries
- ✅ Specific column selection (`select`) untuk reviewer
- ✅ Index on `card_id` (FK dari migration)
- ✅ Index on `reviewed_at` (composite: `[card_id, reviewed_at]`)
- ✅ Pagination (15 per page)

### Index dari Migration:
```php
$table->index(['card_id', 'reviewed_at']);
$table->index('reviewed_by');
```

---

## 🧪 Testing Checklist

### Functional Testing
- [ ] Menu hanya tampil untuk developer/designer
- [ ] Non-dev/designer tidak bisa akses via direct URL
- [ ] Empty state muncul jika belum ada review
- [ ] Filter "All" menampilkan semua review
- [ ] Filter "Approved" hanya approved
- [ ] Filter "Rejected" hanya rejected
- [ ] Search by card title works
- [ ] Search by reviewer name works
- [ ] Search by notes works
- [ ] Pagination berfungsi dengan filter aktif
- [ ] Date grouping bekerja correct
- [ ] Statistics count akurat

### UI/UX Testing
- [ ] Responsive dari 320px - 4K
- [ ] Mobile: Stack layout, touch-friendly
- [ ] Tablet: Grid optimized
- [ ] Desktop: Full sidebar visible
- [ ] Sticky date headers work
- [ ] Glassmorphism effect terlihat
- [ ] Status badges color correct
- [ ] Empty state centered & clear
- [ ] Loading states (pagination)

### Performance Testing
- [ ] Page load < 2 detik
- [ ] No N+1 queries (check Laravel Debugbar)
- [ ] Search tidak lag dengan 100+ reviews
- [ ] Filter switch instant
- [ ] Pagination smooth

---

## 🚀 Usage

### Sebagai Developer/Designer:

1. **Login** dengan akun yang memiliki role developer/designer di project_members
2. **Lihat menu** "Review History" muncul di sidebar (dengan icon clock ⏰)
3. **Klik menu** untuk akses halaman
4. **Lihat statistics** di header (Total/Approved/Rejected)
5. **Filter by status**:
   - Klik "Semua" untuk semua review
   - Klik "✅ Approved" untuk yang approved saja
   - Klik "❌ Rejected" untuk yang rejected saja
6. **Search**: Ketik di search box untuk cari card, reviewer, atau notes
7. **Scroll timeline**: Lihat reviews grouped by date
8. **Read details**: Card title, board, project, reviewer, timestamp, notes

### Query String Parameters:
- `?status=approved` - Filter approved only
- `?status=rejected` - Filter rejected only
- `?search=bug+fix` - Search "bug fix"
- `?status=approved&search=homepage` - Combined filters

---

## 🔄 Integration Points

### Existing Features:
1. **Card Reviews Table** (`card_reviews`):
   - Populated by `CardReviewController@store` (Team Lead approve/reject)
   - Contains: card_id, reviewed_by, status, notes, reviewed_at

2. **Card Assignments** (`card_assignments`):
   - Used to find assigned cards untuk user
   - Filter: `user_id` = current user

3. **Project Members** (`project_members`):
   - Used for role checking
   - Filter: `user_id` + `role IN ('developer', 'designer')`

4. **Notifications**:
   - Team Lead review creates notification
   - Developer/Designer sees notification
   - Click notification → Card detail
   - Can also check Review History page untuk overview

---

## 🛠️ Future Enhancements

### Potential Features:
1. **Export to PDF/Excel**: Download review history report
2. **Real-time updates**: WebSocket untuk live review notifications
3. **Charts & Analytics**: Graph untuk approved vs rejected trends
4. **Email notifications**: Daily/weekly digest of reviews
5. **Filtering by project**: Multi-select project filter
6. **Filtering by board**: Board-level filtering
7. **Date range picker**: Custom date range filtering
8. **Bulk actions**: Mark multiple as read/archived
9. **Comments on reviews**: Reply to reviewer notes
10. **Review acknowledgment**: Developer confirm they've read review

### Performance Optimizations:
1. **Caching**: Cache statistics for 5 minutes
2. **Lazy loading**: Infinite scroll instead of pagination
3. **Indexed search**: Elasticsearch integration
4. **Database optimization**: Materialized views untuk statistics

---

## 📝 Related Files

### Models:
- `app/Models/CardReview.php` - Review model dengan relationships & scopes
- `app/Models/Card.php` - Card model dengan assignments
- `app/Models/CardAssignment.php` - Assignment pivot

### Migrations:
- `database/migrations/2025_11_11_064658_create_card_reviews_table.php`
- Indexes: `[card_id, reviewed_at]`, `[reviewed_by]`

### Routes:
- `routes/web.php` - Line ~152 (Card Review Routes section)

### Views:
- `resources/views/card-reviews/my-reviews.blade.php` - Main view
- `resources/views/layouts/app.blade.php` - Navigation menu

### Controllers:
- `app/Http/Controllers/web/CardReviewController.php`
  - `store()` - Create review (existing)
  - `index(Card $card)` - Get reviews for specific card (existing)
  - `myReviews()` - Review history page (NEW)

---

## ✅ Implementation Summary

### What Was Added:

1. **Controller Method**: `CardReviewController@myReviews()`
   - Role-based authorization
   - Query dengan eager loading
   - Filter & search functionality
   - Statistics calculation
   - Pagination & date grouping

2. **View File**: `card-reviews/my-reviews.blade.php`
   - Responsive timeline layout
   - Filter buttons dengan Alpine.js
   - Search box with icon
   - Statistics cards
   - Date-grouped reviews
   - Status badges
   - Empty state

3. **Route**: `GET /my-card-reviews`
   - Named route: `card-reviews.my-reviews`
   - Auth middleware

4. **Navigation Menu**: 
   - Desktop sidebar item
   - Mobile sidebar item
   - Clock icon (⏰)
   - Conditional display (developer/designer only)

### Infrastructure Already Existed:
- ✅ `card_reviews` table (migration dari 2025-11-11)
- ✅ `CardReview` model dengan full features
- ✅ `CardReviewController` dengan `store()` dan `index()` methods
- ✅ Relationships: Card, CardReview, User, CardAssignment
- ✅ Project members role system

---

## 📞 Support

**Documentation**: CARD_REVIEW_HISTORY.md (this file)

**Related Docs**:
- `CARD_REVIEW_FEATURE.md` - Original approve/reject feature
- `AUTHORIZATION_GUIDE.md` - Role-based access patterns
- `BOARD_RESPONSIVE_FIX.md` - Responsive design patterns

**Key Concepts**:
- Role checking dari `project_members` table (bukan `users.role`)
- Timeline view dengan date grouping
- Glassmorphism UI dengan Tailwind
- Alpine.js untuk reactive filters
- Eager loading untuk performance

---

**Dibuat**: {{ now()->format('d M Y, H:i') }}
**Versi Laravel**: 12.27.1
**Versi PHP**: 8.3.10
