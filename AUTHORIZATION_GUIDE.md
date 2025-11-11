# 🔐 Authorization Guide - Laravel Policy Pattern

## 📚 Overview

Project ini menggunakan **Laravel Policy** untuk centralized authorization. Policy sudah diimplementasikan untuk `Card` resource dan bisa digunakan sebagai template untuk resource lain.

---

## 🎯 Authorization Hierarchy

### Role Structure:
1. **Admin** (`users.role = 'admin'`)
   - Full access ke semua resource
   - Bisa CRUD semua Project, Board, Card
   - Super user yang bypass semua permission check

2. **Team Lead** (`project_members.role = 'team lead'`)
   - Manager level dalam project
   - Bisa CRUD Card/Task dalam project mereka
   - Bisa assign task ke Designer/Developer
   - Bisa manage project members

3. **Designer / Developer** (`project_members.role = 'designer|developer'`)
   - Executor level
   - Read-only access untuk Card
   - Bisa update status card yang di-assign ke mereka
   - Bisa track time (time logs)
   - Bisa comment dan create subtasks

---

## 📝 Cara Pakai Policy (CardPolicy as Example)

### 1️⃣ **Di Controller**

```php
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CardController extends Controller
{
    use AuthorizesRequests; // Required trait
    
    public function update(Request $request, Card $card)
    {
        // Authorization check - throws 403 if unauthorized
        $this->authorize('update', $card);
        
        // ... update logic
    }
    
    public function destroy(Request $request, Card $card)
    {
        // Authorization check
        $this->authorize('delete', $card);
        
        // ... delete logic
    }
}
```

**Benefits:**
- ✅ Clean code (1 line untuk authorization)
- ✅ Automatic 403 response jika unauthorized
- ✅ Consistent error handling

---

### 2️⃣ **Di Blade Views**

```blade
{{-- Show/Hide button berdasarkan permission --}}
@can('update', $card)
    <button>Edit Card</button>
@endcan

@can('delete', $card)
    <button>Delete Card</button>
@endcan

{{-- Show alternative UI jika tidak ada permission --}}
@cannot('update', $card)
    <div class="text-gray-500">
        You don't have permission to edit this card
    </div>
@endcannot

{{-- Check multiple permissions --}}
@canany(['update', 'delete'], $card)
    <div class="action-buttons">
        @can('update', $card)
            <button>Edit</button>
        @endcan
        
        @can('delete', $card)
            <button>Delete</button>
        @endcan
    </div>
@endcanany
```

**Benefits:**
- ✅ Declarative syntax (easy to read)
- ✅ UI automatically adapts to user permissions
- ✅ No manual if-else checks

---

### 3️⃣ **Di Alpine.js / JavaScript**

Untuk dynamic authorization check di Alpine.js, pass authorization result dari Blade:

```blade
<div x-data="{ 
    canEdit: {{ auth()->user()->can('update', $card) ? 'true' : 'false' }},
    canDelete: {{ auth()->user()->can('delete', $card) ? 'true' : 'false' }}
}">
    <button x-show="canEdit" @click="editCard()">Edit</button>
    <button x-show="canDelete" @click="deleteCard()">Delete</button>
</div>
```

---

## 🏗️ Struktur CardPolicy

### Helper Methods (Private)

```php
/**
 * Check if user is Admin
 */
private function isAdmin(User $user): bool
{
    return $user->role === 'admin';
}

/**
 * Check if user is Team Lead in specific project
 */
private function isTeamLeadInProject(User $user, Card $card): bool
{
    return $card->board->project->members()
        ->where('user_id', $user->id)
        ->where('role', 'team lead')
        ->exists();
}

/**
 * Check if user is Member in specific project
 */
private function isMemberInProject(User $user, Card $card): bool
{
    return $card->board->project->members()
        ->where('user_id', $user->id)
        ->exists();
}
```

### Public Methods (Authorization Rules)

```php
/**
 * Determine if user can view any cards
 */
public function viewAny(User $user): bool
{
    if ($this->isAdmin($user)) return true;
    return $user->projectMemberships()->exists();
}

/**
 * Determine if user can view specific card
 */
public function view(User $user, Card $card): bool
{
    if ($this->isAdmin($user)) return true;
    return $this->isMemberInProject($user, $card);
}

/**
 * Determine if user can create cards
 */
public function create(User $user): bool
{
    if ($this->isAdmin($user)) return true;
    return $user->projectMemberships()
        ->where('role', 'team lead')
        ->exists();
}

/**
 * Determine if user can update card
 */
public function update(User $user, Card $card): bool
{
    if ($this->isAdmin($user)) return true;
    return $this->isTeamLeadInProject($user, $card);
}

/**
 * Determine if user can delete card
 */
public function delete(User $user, Card $card): bool
{
    if ($this->isAdmin($user)) return true;
    return $this->isTeamLeadInProject($user, $card);
}
```

---

## 🚀 Cara Buat Policy untuk Resource Lain

### Step 1: Generate Policy

```bash
php artisan make:policy ProjectPolicy --model=Project
php artisan make:policy BoardPolicy --model=Board
php artisan make:policy SubtaskPolicy --model=Subtask
```

### Step 2: Implement Authorization Logic

Copy pattern dari `CardPolicy.php`:

```php
<?php

namespace App\Policies;

use App\Models\User;
use App\Models\YourModel;

class YourModelPolicy
{
    // 1. Helper method untuk cek Admin
    private function isAdmin(User $user): bool
    {
        return $user->role === 'admin';
    }
    
    // 2. Helper method untuk cek role specific
    private function isTeamLeadInProject(User $user, YourModel $model): bool
    {
        // Implement based on your model relationships
        return $model->project->members()
            ->where('user_id', $user->id)
            ->where('role', 'team lead')
            ->exists();
    }
    
    // 3. Public methods untuk authorization rules
    public function viewAny(User $user): bool
    {
        if ($this->isAdmin($user)) return true;
        // ... your logic
    }
    
    public function view(User $user, YourModel $model): bool
    {
        if ($this->isAdmin($user)) return true;
        // ... your logic
    }
    
    public function create(User $user): bool
    {
        if ($this->isAdmin($user)) return true;
        // ... your logic
    }
    
    public function update(User $user, YourModel $model): bool
    {
        if ($this->isAdmin($user)) return true;
        // ... your logic
    }
    
    public function delete(User $user, YourModel $model): bool
    {
        if ($this->isAdmin($user)) return true;
        // ... your logic
    }
}
```

### Step 3: Register Policy (Optional)

Laravel auto-discovers policies by convention:
- `App\Models\Card` → `App\Policies\CardPolicy`
- `App\Models\Project` → `App\Policies\ProjectPolicy`

Jika tidak mengikuti convention, register manual di `AuthServiceProvider`:

```php
// app/Providers/AuthServiceProvider.php

protected $policies = [
    Card::class => CardPolicy::class,
    Project::class => ProjectPolicy::class,
];
```

---

## 📋 Example Use Cases

### Use Case 1: Board Management

**Requirement:**
- Team Lead bisa CRUD boards
- Designer/Developer bisa view boards
- Admin bisa CRUD semua boards

**Implementation:**

```php
// app/Policies/BoardPolicy.php
class BoardPolicy
{
    public function update(User $user, Board $board): bool
    {
        if ($user->role === 'admin') return true;
        
        return $board->project->members()
            ->where('user_id', $user->id)
            ->where('role', 'team lead')
            ->exists();
    }
}

// Controller
public function update(Request $request, Board $board)
{
    $this->authorize('update', $board);
    // ... update logic
}

// Blade
@can('update', $board)
    <button>Edit Board</button>
@endcan
```

---

### Use Case 2: Comment on Card

**Requirement:**
- Semua project member bisa comment
- Admin bisa comment di semua card
- User hanya bisa edit/delete comment mereka sendiri

**Implementation:**

```php
// app/Policies/CommentPolicy.php
class CommentPolicy
{
    public function create(User $user, Card $card): bool
    {
        if ($user->role === 'admin') return true;
        
        // Check if user is member of the project
        return $card->board->project->members()
            ->where('user_id', $user->id)
            ->exists();
    }
    
    public function update(User $user, Comment $comment): bool
    {
        if ($user->role === 'admin') return true;
        
        // User can only edit their own comment
        return $comment->user_id === $user->id;
    }
    
    public function delete(User $user, Comment $comment): bool
    {
        if ($user->role === 'admin') return true;
        
        // User can delete own comment, or Team Lead can delete any comment in their project
        if ($comment->user_id === $user->id) return true;
        
        return $comment->card->board->project->members()
            ->where('user_id', $user->id)
            ->where('role', 'team lead')
            ->exists();
    }
}

// Blade
@can('create', $card)
    <button>Add Comment</button>
@endcan

@can('update', $comment)
    <button>Edit</button>
@endcan

@can('delete', $comment)
    <button>Delete</button>
@endcan
```

---

### Use Case 3: Assign User to Card

**Requirement:**
- Hanya Team Lead yang bisa assign users
- Admin bisa assign di semua card

**Implementation:**

```php
// app/Policies/CardPolicy.php
class CardPolicy
{
    public function assign(User $user, Card $card): bool
    {
        if ($user->role === 'admin') return true;
        
        return $card->board->project->members()
            ->where('user_id', $user->id)
            ->where('role', 'team lead')
            ->exists();
    }
}

// Controller
public function assignUser(Request $request, Card $card)
{
    $this->authorize('assign', $card);
    // ... assignment logic
}

// Blade
@can('assign', $card)
    <div class="assign-members-section">
        <!-- Assignment UI here -->
    </div>
@endcan
```

---

## 🔍 Debugging Policy

### Check Permission di Tinker

```bash
php artisan tinker
```

```php
$user = User::find(1);
$card = Card::find(1);

// Check single permission
$user->can('update', $card); // true/false

// Check multiple permissions
$user->canAny(['update', 'delete'], $card);

// Get all abilities for a user
Gate::abilities(); // List semua registered gates
```

### Log Policy Decisions

Add debug logging di Policy:

```php
public function update(User $user, Card $card): bool
{
    $isAdmin = $user->role === 'admin';
    $isTeamLead = $this->isTeamLeadInProject($user, $card);
    
    \Log::info('CardPolicy::update', [
        'user_id' => $user->id,
        'card_id' => $card->id,
        'is_admin' => $isAdmin,
        'is_team_lead' => $isTeamLead,
        'result' => $isAdmin || $isTeamLead
    ]);
    
    return $isAdmin || $isTeamLead;
}
```

---

## ⚠️ Common Pitfalls

### 1. Missing Model Binding in @can

```blade
{{-- ❌ WRONG - Missing $card parameter --}}
@can('update')
    <button>Edit</button>
@endcan

{{-- ✅ CORRECT --}}
@can('update', $card)
    <button>Edit</button>
@endcan
```

### 2. Using @can in Loop Without Proper Eager Loading

```blade
{{-- ❌ WRONG - N+1 Query Problem --}}
@foreach($cards as $card)
    @can('update', $card)
        <button>Edit</button>
    @endcan
@endforeach

{{-- ✅ CORRECT - Eager load relationships --}}
@php
    // Di Controller:
    $cards = Card::with('board.project.members')->get();
@endphp
```

### 3. Forgetting to Use AuthorizesRequests Trait

```php
// ❌ WRONG
class CardController extends Controller
{
    public function update(Request $request, Card $card)
    {
        $this->authorize('update', $card); // Error: Method not found
    }
}

// ✅ CORRECT
class CardController extends Controller
{
    use AuthorizesRequests; // Add this trait
    
    public function update(Request $request, Card $card)
    {
        $this->authorize('update', $card); // Works!
    }
}
```

---

## 📊 Authorization Matrix (Card Resource)

| Action | Admin | Team Lead (in project) | Designer/Developer |
|--------|-------|------------------------|-------------------|
| View List | ✅ | ✅ | ✅ |
| View Detail | ✅ | ✅ | ✅ |
| Create | ✅ | ✅ | ❌ |
| Update | ✅ | ✅ | ❌ |
| Delete | ✅ | ✅ | ❌ |
| Assign Users | ✅ | ✅ | ❌ |
| Update Status | ✅ | ✅ | ✅ (own assigned cards) |
| Comment | ✅ | ✅ | ✅ |
| Track Time | ✅ | ✅ | ✅ |

---

## 🎓 Best Practices

1. **Always check Admin first**
   ```php
   if ($this->isAdmin($user)) return true;
   // ... other checks
   ```

2. **Use descriptive helper methods**
   ```php
   private function isTeamLeadInProject(User $user, Card $card): bool
   private function isMemberInProject(User $user, Card $card): bool
   ```

3. **Eager load relationships in Policy queries**
   ```php
   // Load relationships di Controller
   $card->load('board.project.members');
   
   // Policy tidak perlu load lagi
   ```

4. **Handle edge cases**
   ```php
   // Check if relationships exist
   if (!$card->board || !$card->board->project) {
       return false;
   }
   ```

5. **Use Policy for BOTH frontend and backend**
   - Frontend: `@can` directive
   - Backend: `$this->authorize()`
   - Consistent authorization logic!

---

## 📚 Further Reading

- [Laravel Policy Documentation](https://laravel.com/docs/11.x/authorization#creating-policies)
- [Authorization Best Practices](https://laravel.com/docs/11.x/authorization#via-controller-helpers)
- [Gate vs Policy](https://laravel.com/docs/11.x/authorization#gates-vs-policies)

---

**Created by:** AI Assistant  
**Date:** November 9, 2025  
**Version:** 1.0
