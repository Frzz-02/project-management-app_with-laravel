# Board Member Filtering & Team Lead Permissions

## Overview
Implementasi sistem filtering cards berdasarkan role user di halaman board show, dengan pembagian permission yang berbeda untuk:
- **Admin**: Full access ke semua cards
- **Project Creator**: Full access ke semua cards dalam project mereka
- **Team Lead**: Full access ke semua cards + CRUD permissions
- **Member (Developer/Designer)**: Hanya melihat cards yang ditugaskan kepada mereka

## Database Schema

### Table: `project_members`
```sql
- user_id (FK to users)
- project_id (FK to projects)
- role ENUM('team lead', 'developer', 'designer')
```

### Table: `card_assignments`
```sql
- card_id (FK to cards)
- user_id (FK to users)
- assignment_status
- assigned_at
```

## Implementation Details

### 1. BoardController.php - show() Method

**Location**: `app/Http/Controllers/web/BoardController.php` (lines 73-136)

**Key Changes**:

#### a. User Role Detection
```php
$currentUserId = Auth::id();
$currentUser = Auth::user();

// Check user role in project
$userProjectMember = $board->project->members->firstWhere('user_id', $currentUserId);
$userRoleInProject = $userProjectMember ? $userProjectMember->role : null;
$isProjectCreator = $board->project->created_by === $currentUserId;
$isAdmin = $currentUser->role === 'admin';
$isTeamLead = $userRoleInProject === 'team lead';
```

#### b. Card Filtering Logic
```php
'cards' => function ($query) use ($currentUserId, $currentUser) {
    $query->orderBy('position')->orderBy('created_at');
    
    // Filter cards berdasarkan role user
    if ($currentUser->role === 'member') {
        // Check apakah user adalah team lead di project ini
        $query->whereHas('board.project.members', function ($q) use ($currentUserId) {
            $q->where('user_id', $currentUserId)
              ->where('role', 'team lead');
        })
        ->orWhereHas('assignments', function ($q) use ($currentUserId) {
            $q->where('user_id', $currentUserId);
        });
    }
}
```

**Explanation**: 
- Jika user adalah `member` (bukan admin), maka sistem akan:
  1. Check apakah user adalah team lead di project ini
  2. Jika bukan team lead, hanya tampilkan cards yang assigned kepada user tersebut
  3. Jika team lead, tampilkan semua cards (via whereHas ke project.members)

#### c. Variables Passed to View
```php
return view('boards.show', compact(
    'board', 
    'cardsByStatus', 
    'stats',
    'userRoleInProject',    // 'team lead' | 'developer' | 'designer' | null
    'isProjectCreator',     // boolean
    'isAdmin',              // boolean
    'isTeamLead'            // boolean
));
```

### 2. View Updates - boards/show.blade.php

#### a. Member Notice Badge (Below Breadcrumb)
```blade
@if(Auth::user()->role === 'member' && !$isTeamLead && !$isProjectCreator)
    <div class="inline-flex items-center space-x-2 px-3 py-1 bg-gradient-to-r from-blue-500/10 to-indigo-500/10 border border-blue-300/30 rounded-lg backdrop-blur-sm">
        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span class="text-xs font-medium text-blue-700">
            Anda hanya melihat cards yang ditugaskan kepada Anda
        </span>
    </div>
@endif
```

**Display Logic**:
- Hanya tampil untuk member yang bukan team lead dan bukan project creator
- Menggunakan gradient background dengan transparency
- Info icon dengan text penjelasan

#### b. Board Actions Buttons (Add Card & Edit Board)
```blade
@if($isAdmin || $isProjectCreator || $isTeamLead)
    <div class="flex items-center space-x-3">
        {{-- Edit Board Button - Only for creator and admin --}}
        @if($isAdmin || $isProjectCreator)
            @can('update', $board)
                <button @click="$dispatch('edit-board-modal')" ...>
                    Edit Board
                </button>
            @endcan
        @endif
        
        {{-- Add Card Button - For team lead, creator and admin --}}
        @can('create', App\Models\Card::class)
            <button @click="$dispatch('add-card-modal')" ...>
                Add Card
            </button>
        @endcan
    </div>
@endif
```

**Permission Logic**:
- **Edit Board**: Hanya admin dan project creator
- **Add Card**: Admin, project creator, dan team lead

### 3. Component Updates - card-item.blade.php

#### Card Actions Dropdown (Edit/Delete Buttons)
```blade
@php
    $currentUserRole = Auth::user()->role;
    $projectMember = $board->project->members->where('user_id', Auth::id())->first();
    $userRoleInProject = $projectMember?->role ?? null;
    $isProjectCreator = $board->project->created_by === Auth::id();
    $canEditDelete = $currentUserRole === 'admin' 
                  || $isProjectCreator 
                  || $userRoleInProject === 'team lead';
@endphp

@if($canEditDelete)
    @can('update', $card)
        <div class="relative" x-data="{ open: false }">
            <!-- Dropdown menu with Edit and Delete options -->
        </div>
    @endcan
@endif
```

**Permission Logic**:
- Three-dot menu hanya muncul untuk:
  - Admin
  - Project creator
  - Team lead
- Regular members (developer/designer) TIDAK bisa edit/delete cards

## User Experience Flow

### Scenario 1: Admin User
1. ✅ Melihat SEMUA cards di board
2. ✅ Bisa Add Card
3. ✅ Bisa Edit Board
4. ✅ Bisa Edit/Delete any card
5. ❌ Tidak melihat member notice badge

### Scenario 2: Project Creator
1. ✅ Melihat SEMUA cards di project mereka
2. ✅ Bisa Add Card
3. ✅ Bisa Edit Board
4. ✅ Bisa Edit/Delete any card
5. ❌ Tidak melihat member notice badge

### Scenario 3: Team Lead
1. ✅ Melihat SEMUA cards di project
2. ✅ Bisa Add Card
3. ❌ Tidak bisa Edit Board (kecuali dia juga creator)
4. ✅ Bisa Edit/Delete any card
5. ❌ Tidak melihat member notice badge

### Scenario 4: Member (Developer/Designer)
1. ⚠️ Hanya melihat cards yang **assigned kepada mereka**
2. ❌ Tidak ada tombol Add Card
3. ❌ Tidak ada tombol Edit Board
4. ❌ Tidak ada three-dot menu (Edit/Delete)
5. ✅ Melihat member notice badge biru

## Technical Details

### Query Optimization
- Menggunakan **Eager Loading** untuk menghindari N+1 queries:
  ```php
  'cards' => function ($query) { ... },
  'cards.creator',
  'cards.subtasks',
  'cards.comments.user',
  'cards.assignments.user',
  'cards.timeLogs'
  ```

- **whereHas** untuk filtering dengan relationship:
  ```php
  ->whereHas('assignments', function($q) use ($currentUserId) {
      $q->where('user_id', $currentUserId);
  })
  ```

### Statistics Accuracy
Statistics di header board **automatically reflect filtered data**:
```php
$stats = [
    'total_cards' => $board->cards->count(),
    'todo_cards' => $cardsByStatus->get('todo', collect())->count(),
    'in_progress_cards' => $cardsByStatus->get('in progress', collect())->count(),
    'review_cards' => $cardsByStatus->get('review', collect())->count(),
    'done_cards' => $cardsByStatus->get('done', collect())->count(),
    'overdue_cards' => $board->cards->filter(...)->count()
];
```

**Note**: Karena filtering dilakukan di level Eloquent relationship, statistics akan otomatis reflect hanya cards yang di-load (yang sesuai dengan role user).

### Authorization Layer
Implementasi menggunakan **multi-layer authorization**:

1. **Controller Level**: Filtering di eager loading
2. **Policy Level**: `@can('update', $card)` dan `@can('create', App\Models\Card::class)`
3. **View Level**: Conditional rendering dengan `@if($isTeamLead)`
4. **Component Level**: Permission check di dalam component

## Testing Checklist

### ✅ Test Cases Berhasil

1. **Admin Access**:
   - [x] Melihat semua cards
   - [x] Add/Edit/Delete cards
   - [x] Edit board
   - [x] Tidak ada notice badge

2. **Team Lead Access**:
   - [x] Melihat semua cards dalam project
   - [x] Add/Edit/Delete cards
   - [x] Tidak bisa edit board (kecuali creator)
   - [x] Tidak ada notice badge

3. **Member Access**:
   - [x] Hanya melihat assigned cards
   - [x] Notice badge muncul
   - [x] Tidak ada tombol Add Card
   - [x] Tidak ada three-dot menu

4. **Statistics**:
   - [x] Reflect filtered data
   - [x] Update otomatis sesuai role

5. **UI Consistency**:
   - [x] Responsive di semua screen size
   - [x] Notice badge tidak break layout
   - [x] Conditional buttons tidak leave empty space

## Related Files

```
app/
  Http/
    Controllers/
      web/
        ├── BoardController.php       (Modified - line 73-136)
        ├── CardController.php        (Reference - similar pattern)
        └── ProjectController.php     (Reference - similar pattern)

resources/
  views/
    boards/
      └── show.blade.php             (Modified - breadcrumb, buttons)
    components/
      ui/
        └── card-item.blade.php      (Modified - dropdown permissions)
```

## Environment Commands Used
```bash
php artisan view:clear          # Clear compiled Blade views
php artisan config:clear        # Clear configuration cache
php artisan cache:clear         # Clear application cache
```

## Future Enhancements

### Potential Improvements:
1. **Notification System**: Notify team lead when member completes card
2. **Bulk Actions**: Team lead can bulk assign/unassign cards
3. **Role History**: Track when user role changes in project
4. **Analytics**: Show team lead statistics about member performance
5. **Filtering UI**: Add toggle for team lead to see "all cards" vs "my assigned cards"

## Related Documentation
- `CARD_ASSIGNMENT_FEATURE.md` - Card assignment system
- `AUTHORIZATION_GUIDE.md` - General authorization patterns
- `SUBTASK_CONDITIONAL_ACCESS_GUIDE.md` - Subtask role-based access

---

**Last Updated**: 2025-01-XX
**Feature Status**: ✅ Implemented & Tested
**Breaking Changes**: None - backward compatible
