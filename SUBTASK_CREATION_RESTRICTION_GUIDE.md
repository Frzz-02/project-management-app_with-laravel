# 🔐 Subtask Creation Restriction Guide

## 📚 Overview

Fitur ini mengatur **akses untuk membuat subtask** berdasarkan **role** user dan **status time tracking**. Tujuannya adalah memastikan Designer/Developer hanya bisa membuat subtask **setelah mulai mengerjakan task** (start time tracking).

---

## 🎯 Business Rules

### Rule 1: Admin & Team Lead
- ❌ **Tidak bisa melihat** button "Create Subtasks"
- 🎯 **Alasan:** Admin dan Team Lead fokus pada project management, bukan eksekusi task detail
- ✅ **Alternatif:** Bisa create subtasks via card detail page langsung (jika diperlukan)

### Rule 2: Designer & Developer (Assigned + Tracking)
- ✅ **Button VISIBLE & ENABLED**
- ⏱️ User sudah start time tracking
- 🎯 User di-assign ke card tersebut
- � Bisa membuat subtasks untuk breakdown task

### Rule 3: Designer & Developer (Assigned + NOT Tracking)
- 👁️ **Button VISIBLE tapi DISABLED** (opacity 60%)
- 🔒 User belum start time tracking
- 💬 Tooltip: "Start time tracking first to create subtasks"
- ⚠️ Harus klik "Start Task" terlebih dahulu

### Rule 4: Designer & Developer (NOT Assigned)
- ✅ **Button VISIBLE & ENABLED**
- 📝 User bisa view card meskipun tidak di-assign
- 🔐 Backend authorization akan handle access control

---

## 🏗️ Implementation Details

### 1️⃣ Data Flow

#### A. card-item.blade.php (Pass Time Tracking Data)

```php
@php
    // Check if current user has active time tracking for this card
    $currentUser = Auth::user();
    $activeTimeLog = null;
    if ($card->timeLogs) {
        $activeTimeLog = $card->timeLogs->where('user_id', $currentUser->id)
            ->whereNull('end_time')
            ->whereNull('subtask_id')
            ->first();
    }
    $hasActiveTracking = $activeTimeLog !== null;
    
    // Check if user is assigned to this card
    $isUserAssigned = $card->assignments?->contains('user_id', $currentUser->id) ?? false;
    
    // Check user role in project
    $projectMember = $board->project->members->where('user_id', $currentUser->id)->first();
    $userRole = $projectMember?->role ?? 'member';
    $isDesignerOrDeveloper = in_array($userRole, ['designer', 'developer']);

    $cardData = [
        // ... existing fields ...
        
        // NEW: Time tracking info for Create Subtasks button
        'has_active_tracking' => $hasActiveTracking,
        'is_user_assigned' => $isUserAssigned,
        'user_role' => $userRole,
        'is_designer_or_developer' => $isDesignerOrDeveloper,
    ];
@endphp
```

**Key Variables:**
- `has_active_tracking`: User sedang tracking card ini (time_logs dengan `end_time = NULL` dan `subtask_id = NULL`)
- `is_user_assigned`: User ada di `card_assignments` untuk card ini
- `user_role`: Role user di project (`team lead`, `designer`, `developer`)
- `is_designer_or_developer`: Helper boolean untuk check role

---

#### B. card-detail-modal.blade.php (Conditional Button Rendering)

```blade
<!-- Button ONLY VISIBLE for Designer/Developer -->
<template x-if="selectedCard?.is_designer_or_developer">
    <div x-data="{ 
        shouldDisable() {
            // Disable if:
            // - User is assigned to card (assignment check)
            // - Time tracking NOT started (tracking check)
            const isAssigned = this.selectedCard?.is_user_assigned || false;
            const hasTracking = this.selectedCard?.has_active_tracking || false;
            
            const shouldDisable = isAssigned && !hasTracking;
            
            return shouldDisable;
        }
    }">
        <template x-if="shouldDisable()">
            <!-- Disabled Button with Tooltip -->
            <button disabled class="opacity-60 cursor-not-allowed ...">
                Create Subtasks 🔒
            </button>
            
            <!-- Tooltip -->
            <div class="tooltip">
                🔒 Start time tracking first to create subtasks
            </div>
        </template>
        
        <template x-if="!shouldDisable()">
            <!-- Active Button -->
            <a :href="'{{ url("cards") }}/' + (selectedCard?.id || '') + '#subtasks-section'">
                Create Subtasks
            </a>
        </template>
    </div>
</template>
```

**Key Changes:**
- **Outer `x-if`**: Check `is_designer_or_developer` → Button ONLY visible untuk Designer/Developer
- **Inner logic**: Check `isAssigned` dan `hasTracking` → Conditional disable
- **Result**: Admin & Team Lead tidak melihat button sama sekali

---

### 2️⃣ UI States

#### State 1: Admin / Team Lead
```
Button: ❌ NOT VISIBLE
Reason: Admin/Team Lead tidak perlu create subtasks via modal
Alternative: Bisa create subtasks via card detail page jika diperlukan
```

#### State 2: Designer/Developer (Assigned + NOT tracking)
```
Button: 🔒 VISIBLE tapi DISABLED (bg-indigo-400, opacity-60)
Label: "Create Subtasks 🔒"
Tooltip: "🔒 Start time tracking first to create subtasks"
Behavior: No action on click, cursor: not-allowed
```

#### State 3: Designer/Developer (Assigned + TRACKING)
```
Button: ✅ VISIBLE & ENABLED (bg-indigo-600)
Label: "Create Subtasks"
Behavior: Click → Navigate to card detail page dengan anchor #subtasks-section
```

#### State 4: Designer/Developer (NOT Assigned)
```
Button: ✅ VISIBLE & ENABLED (bg-indigo-600)
Label: "Create Subtasks"
Note: Backend authorization akan handle access control
```
Label: "Create Subtasks"
Note: Backend authorization will handle access control
```

---

## 🎨 UI Design Specs

### Disabled Button Style
```css
/* Tailwind Classes */
bg-indigo-400      /* Lighter blue (not too dim) */
opacity-60         /* Reduced opacity (60% = not too redup) */
cursor-not-allowed /* Show restricted cursor */
text-white         /* White text */
rounded-lg         /* Rounded corners */
```

### Tooltip Style
```css
/* Tooltip positioning */
position: absolute
bottom: full (above button)
left: 50% (centered)
transform: translateX(-50%)

/* Tooltip appearance */
bg-gray-900        /* Dark background */
text-white         /* White text */
text-xs            /* Small text */
rounded-lg         /* Rounded corners */
px-3 py-2          /* Padding */
whitespace-nowrap  /* Single line */

/* Tooltip arrow */
border-4 border-transparent border-t-gray-900
```

### Hover Effect
```css
/* Only on disabled button */
group-hover:opacity-100  /* Show tooltip on hover */
transition-opacity       /* Smooth fade in/out */
```

---

## 🔍 Debugging Guide

### Debug Console Logs

Saat membuka card detail modal, check console untuk:

```javascript
🎯 Card Detail Event Received
  ⏱️ Has Active Tracking: true/false
  👤 Is User Assigned: true/false
  🎭 User Role: "designer" / "developer" / "team lead"
  🔧 Is Designer/Developer: true/false

🔐 Create Subtasks Button Check:
  isDesignerOrDev: true
  isAssigned: true
  hasTracking: false
  shouldDisable: true ← This determines button state
```

### Database Query for Debugging

```sql
-- Check active time tracking for user
SELECT * FROM time_logs
WHERE user_id = [USER_ID]
  AND card_id = [CARD_ID]
  AND end_time IS NULL
  AND subtask_id IS NULL;

-- Check card assignment
SELECT * FROM card_assignments
WHERE user_id = [USER_ID]
  AND card_id = [CARD_ID];

-- Check user role in project
SELECT pm.role FROM project_members pm
JOIN boards b ON b.project_id = pm.project_id
JOIN cards c ON c.board_id = b.id
WHERE pm.user_id = [USER_ID]
  AND c.id = [CARD_ID];
```

### Common Issues

#### Issue 1: Button Always Enabled
**Symptom:** Designer/Developer bisa klik button meskipun belum tracking

**Debug Steps:**
1. Check console log untuk `shouldDisable: false`
2. Verify `has_active_tracking` value
3. Query database untuk check time_logs
4. Ensure `timeLogs` relationship di-load di Controller

**Solution:**
```php
// In BoardController::show()
$board->load([
    'cards.timeLogs' => function($query) {
        $query->where('user_id', Auth::id())
              ->whereNull('end_time')
              ->whereNull('subtask_id');
    }
]);
```

#### Issue 2: Button Always Disabled
**Symptom:** Button disabled meskipun sudah start tracking

**Debug Steps:**
1. Check console log untuk `hasTracking: false`
2. Verify time_logs ada di database dengan `end_time = NULL`
3. Check `subtask_id = NULL` (not tracking subtask)
4. Ensure user_id match

**Solution:**
Hard refresh browser (Ctrl + Shift + F5) untuk reload data terbaru

#### Issue 3: Tooltip Not Showing
**Symptom:** Hover di button disabled tapi tooltip tidak muncul

**Debug Steps:**
1. Check CSS class `group-hover:opacity-100`
2. Verify tooltip parent has class `group`
3. Check z-index tidak tertutup element lain

**Solution:**
```blade
<div class="relative group">  <!-- Add 'group' class -->
    <button disabled>...</button>
    <div class="... opacity-0 group-hover:opacity-100">Tooltip</div>
</div>
```

---

## 📊 User Flow Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                    USER OPENS CARD DETAIL                    │
└────────────────────────────┬────────────────────────────────┘
                             │
                ┌────────────┴────────────┐
                │   Check User Role       │
                └────────────┬────────────┘
                             │
        ┌────────────────────┼────────────────────┐
        │                    │                    │
        ▼                    ▼                    ▼
  ┌─────────┐         ┌──────────┐        ┌───────────┐
  │  Admin  │         │Team Lead │        │Designer/  │
  │         │         │          │        │Developer  │
  └────┬────┘         └────┬─────┘        └─────┬─────┘
       │                   │                     │
       │                   │              ┌──────┴──────┐
       │                   │              │ Assigned?   │
       │                   │              └──────┬──────┘
       │                   │                     │
       │                   │              ┌──────┴──────┐
       │                   │              │    YES      │
       │                   │              └──────┬──────┘
       │                   │                     │
       │                   │              ┌──────┴──────┐
       │                   │              │ Tracking?   │
       │                   │              └──────┬──────┘
       │                   │                     │
       │                   │         ┌───────────┴────────────┐
       │                   │         │                        │
       ▼                   ▼         ▼                        ▼
  ┌─────────────────────────────┐  ┌──────────────┐  ┌──────────────┐
  │   BUTTON ENABLED            │  │   DISABLED   │  │   ENABLED    │
  │   (Always allowed)          │  │   🔒         │  │   ✅         │
  └─────────────────────────────┘  └──────────────┘  └──────────────┘
           │                               │                 │
           │                               │                 │
           ▼                               ▼                 ▼
    Click → Navigate            Hover → Show Tooltip  Click → Navigate
    to card detail              "Start tracking      to card detail
    #subtasks-section           first"               #subtasks-section
```

---

## 🧪 Test Scenarios

### Scenario 1: Admin User
```
Given: User dengan role 'admin'
When: User membuka card detail modal
Then: Button "Create Subtasks" TIDAK TERLIHAT (❌)
Reason: Admin fokus pada project management, bukan task detail
```

### Scenario 2: Team Lead
```
Given: User dengan role 'team lead' di project
When: User membuka card detail modal
Then: Button "Create Subtasks" TIDAK TERLIHAT (❌)
Reason: Team Lead fokus pada task assignment dan review
```

### Scenario 3: Designer (Not Tracking)
```
Given: User dengan role 'designer'
And: User di-assign ke card tersebut
And: User BELUM start time tracking
When: User membuka card detail modal
Then: Button "Create Subtasks" TERLIHAT tapi DISABLED (🔒)
And: Button opacity 60%
And: Hover button menampilkan tooltip "Start time tracking first"
And: Cursor menampilkan 'not-allowed'
```

### Scenario 4: Designer (Tracking)
```
Given: User dengan role 'designer'
And: User di-assign ke card tersebut
And: User SUDAH start time tracking (time_logs.end_time = NULL)
When: User membuka card detail modal
Then: Button "Create Subtasks" TERLIHAT & ENABLED (✅)
And: Button opacity 100%
And: Click button navigate ke card detail page
```

### Scenario 5: Developer (Not Assigned)
```
Given: User dengan role 'developer'
And: User TIDAK di-assign ke card tersebut
When: User membuka card detail modal
Then: Button "Create Subtasks" TERLIHAT & ENABLED (✅)
Note: Backend authorization akan handle access control
```

---

## 🔧 Backend Authorization (Future Enhancement)

Untuk keamanan maksimal, tambahkan server-side validation di `SubtaskController::store()`:

```php
public function store(Request $request)
{
    $user = Auth::user();
    $card = Card::findOrFail($request->card_id);
    
    // Get user role in project
    $projectMember = $card->board->project->members()
        ->where('user_id', $user->id)
        ->first();
    
    $userRole = $projectMember?->role;
    
    // Check if user is Designer/Developer
    if (in_array($userRole, ['designer', 'developer'])) {
        // Check if assigned
        $isAssigned = $card->assignments()
            ->where('user_id', $user->id)
            ->exists();
        
        if ($isAssigned) {
            // Check if has active time tracking
            $hasActiveTracking = $card->timeLogs()
                ->where('user_id', $user->id)
                ->whereNull('end_time')
                ->whereNull('subtask_id')
                ->exists();
            
            if (!$hasActiveTracking) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must start time tracking before creating subtasks'
                ], 403);
            }
        }
    }
    
    // ... proceed with subtask creation
}
```

---

## 📝 Notes

### Why Opacity 60% (Not Too Dim)?
- `opacity-60` = 60% visibility
- Masih jelas terlihat tapi clearly disabled
- Better UX daripada `opacity-40` atau `opacity-50` yang terlalu redup
- User masih bisa baca text "Create Subtasks"

### Why Show Tooltip?
- Provides **clear feedback** kenapa button disabled
- **Educates user** tentang workflow yang benar
- **Reduces confusion** dan support tickets
- Better UX than silent disabled button

### Why Check `subtask_id = NULL`?
- Ensure user tracking **card**, bukan subtask
- User bisa concurrent track card + subtasks
- Hanya card tracking yang di-check untuk create subtasks

---

## 🚀 Future Enhancements

1. **Real-time Button Update**
   - Saat user klik "Start Task" di card-item
   - Button "Create Subtasks" langsung enabled tanpa reload

2. **Progressive Disclosure**
   - Show "Start Tracking" button di modal
   - Jika user belum tracking dan button disabled

3. **Visual Indicator**
   - Badge "Tracking Active" di modal header
   - Show timer di button area

4. **Batch Operations**
   - Allow create multiple subtasks sekaligus
   - Pre-fill subtask template berdasarkan card description

---

**Created by:** AI Assistant  
**Date:** November 9, 2025  
**Version:** 1.0  
**Related Features:** Time Tracking, Subtasks, Authorization
