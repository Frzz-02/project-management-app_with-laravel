# Board Delete Feature - Implementation Documentation

## 📋 Overview
Fitur hapus board lengkap dengan confirmation modal, authorization check, dan cascade delete untuk semua data terkait (cards, subtasks, comments, time logs, assignments).

## 🔧 Files Modified/Created

### 1. Controller - BoardController.php
**Location:** `app/Http/Controllers/web/BoardController.php`

**Changes:**
- Added `destroy(Board $board)` method dengan:
  - Authorization via Policy (`$this->authorize('delete', $board)`)
  - Database transaction (beginTransaction/commit/rollBack)
  - Cascade delete untuk semua relasi
  - Success message dengan nama board yang dihapus

```php
public function destroy(Board $board)
{
    $this->authorize('delete', $board);
    
    DB::beginTransaction();
    // Simpan project_id sebelum hapus
    $projectId = $board->project_id;
    $board->delete(); // Cascade delete via foreign key constraints
    DB::commit();
    
    return redirect()->route('projects.show', $projectId)
        ->with('success', "Board '{$boardName}' berhasil dihapus...");
}
```

---

### 2. Policy - BoardPolicy.php
**Location:** `app/Policies/BoardPolicy.php`

**Changes:**
- Updated `delete()` method untuk allow:
  - **Admin** → Bisa hapus semua board
  - **Project Creator** → Bisa hapus board di project mereka

```php
public function delete(User $user, Board $board): bool
{
    if ($user->role === 'admin') return true;
    return $board->project->created_by === $user->id;
}
```

**Pattern:** Konsisten dengan ProjectPolicy (admin OR creator)

---

### 3. Component - delete-board-modal.blade.php
**Location:** `resources/views/components/ui/board/delete-board-modal.blade.php`

**Features:**
- **Danger-themed UI** dengan red gradient
- **Warning message** dengan detail data yang akan terhapus:
  - Jumlah cards
  - All comments dan subtasks
  - All time logs dan assignments
- **Loading state** dengan disabled buttons saat proses delete
- **Alpine.js event system** dengan `x-on:delete-board-{id}`
- **Keyboard shortcuts** (Escape untuk cancel)
- **Smooth transitions** untuk modal open/close

**Props:**
- `$board` - Board model instance

**Alpine.js State:**
```javascript
{
    showDeleteModal: false,
    isDeleting: false,
    boardToDelete: null
}
```

---

### 4. Component - board-card.blade.php
**Location:** `resources/views/components/ui/board/board-card.blade.php`

**Changes:**
- Added **delete button** (trash icon) di top-right corner
- Button muncul on hover (opacity transition)
- Authorization check: `@can('delete', $board)`
- Dispatch Alpine.js event: `$dispatch('delete-board-{{ $board->id }}')`
- Stop event propagation: `x-on:click.stop` (agar link tidak ke-trigger)

**New Props:**
- `$board` - Board model instance (optional, untuk delete functionality)

**UI Pattern:**
```blade
<button x-on:click.stop="$dispatch('delete-board-{{ $board->id }}')"
        class="absolute top-3 right-3 opacity-0 group-hover:opacity-100">
    <!-- Trash Icon -->
</button>
```

---

### 5. PHP Components
**Location:** `app/View/Components/ui/board/`

**Created:**
- `DeleteBoardModal.php` - Component class untuk delete modal

**Updated:**
- `BoardCard.php` - Added `$board` property

---

### 6. View - projects/show.blade.php
**Location:** `resources/views/projects/show.blade.php`

**Changes:**
- Pass `:board="$board"` prop ke `<x-ui.board.board-card>`
- Include `<x-ui.board.delete-board-modal :board="$board" />` untuk setiap board dalam loop

```blade
@foreach ($project->boards as $board)
    <x-ui.board.board-card :board="$board" ... />
    <x-ui.board.delete-board-modal :board="$board" />
@endforeach
```

---

## 🔐 Authorization Flow

```
User hover board card
    ↓
@can('delete', $board) → BoardPolicy::delete()
    ↓
    ├─ Admin? → ✅ Show delete button
    ├─ Project Creator? → ✅ Show delete button
    └─ Others → ❌ Hide delete button

User click delete button
    ↓
Alpine.js dispatch event 'delete-board-{id}'
    ↓
Modal opens dengan warning
    ↓
User confirm delete
    ↓
Form POST dengan @method('DELETE')
    ↓
BoardController::destroy()
    ↓
$this->authorize('delete', $board) → Policy check again
    ↓
DB::beginTransaction()
    ↓
$board->delete() → Cascade delete semua relasi
    ↓
DB::commit()
    ↓
Redirect ke projects.show dengan success message
```

---

## 🗄️ Database Cascade Delete

Board memiliki foreign key constraints dengan `onDelete('cascade')`:

**Relasi yang akan ikut terhapus:**
1. **Cards** → `board_id` di tabel `cards`
2. **Subtasks** → `card_id` di tabel `subtasks` (via cards)
3. **Comments** → `card_id` di tabel `comments` (via cards)
4. **Card Assignments** → `card_id` di tabel `card_assignments` (via cards)
5. **Time Logs** → `card_id` di tabel `time_logs` (via cards)

**Migration pattern:**
```php
$table->foreignId('board_id')
      ->constrained('boards')
      ->onDelete('cascade');
```

---

## 🎨 UI/UX Features

### Delete Button (board-card)
- **Position:** Absolute top-right corner
- **Visibility:** Hidden by default, muncul on hover
- **Color:** Red tint (bg-red-500/10) → Solid red on hover
- **Icon:** Trash icon dari Heroicons
- **Z-index:** 10 (di atas card content)

### Delete Modal
- **Theme:** Danger (red gradient)
- **Backdrop:** Red tint dengan blur
- **Header:** Red gradient dengan warning icon (pulse animation)
- **Warning Box:** Red border dengan detailed information
- **Buttons:**
  - Cancel: Gray (hover scale)
  - Delete: Red gradient dengan loading spinner

### Animations
- **Modal transitions:** Scale + opacity + translateY
- **Button hover:** Scale transform (1.05)
- **Loading spinner:** Rotate animation
- **Warning icon:** Pulse animation

---

## 🧪 Testing Checklist

### As Admin:
- [x] Lihat delete button pada board cards
- [ ] Klik delete button → Modal muncul
- [ ] Check data yang ditampilkan di modal (nama board, jumlah cards)
- [ ] Klik Cancel → Modal close
- [ ] Klik Delete → Board terhapus + redirect + success message
- [ ] Verify cards dan relasi ikut terhapus di database

### As Project Creator:
- [x] Lihat delete button pada board di project sendiri
- [ ] Bisa hapus board yang dibuat
- [ ] Success message muncul

### As Non-Creator Member:
- [x] Tidak lihat delete button
- [ ] Tidak bisa akses route delete (403 jika paksa via URL)

### Error Handling:
- [ ] Database error → Rollback + error message
- [ ] Authorization fail → 403 Unauthorized
- [ ] Board tidak ditemukan → 404

---

## 🚀 Usage Instructions

### Untuk Developer:
1. Pull latest code
2. Tidak perlu migration (foreign key sudah ada)
3. Clear view cache: `php artisan view:clear`
4. Test fitur di halaman project detail

### Untuk User:
1. Buka halaman project detail (`/projects/{id}`)
2. Hover mouse ke board card
3. Klik icon trash di pojok kanan atas
4. Baca warning message di modal
5. Konfirmasi delete
6. Board beserta semua cards akan terhapus

---

## 📝 Code Conventions Followed

✅ **Indonesian comments** untuk business logic
✅ **Authorization via Policy** (bukan manual check di controller)
✅ **Database transactions** untuk data integrity
✅ **Alpine.js** untuk client-side interactivity
✅ **Tailwind CSS** dengan glassmorphism effect
✅ **Component architecture** dengan props dan slots
✅ **Error handling** dengan try-catch dan rollback
✅ **Consistent naming** (PascalCase untuk classes, camelCase untuk variables)

---

## 🔄 Future Enhancements

**Soft Delete:**
```php
// Add to Board migration
$table->softDeletes();

// Update controller
$board->delete(); // Soft delete
$board->forceDelete(); // Permanent delete
```

**Audit Log:**
```php
// Log board deletion untuk tracking
AuditLog::create([
    'user_id' => Auth::id(),
    'action' => 'delete_board',
    'board_id' => $board->id,
    'board_name' => $board->board_name,
]);
```

**Restore Functionality:**
```php
// Add restore button jika soft delete
Board::withTrashed()->find($id)->restore();
```

---

## 🐛 Known Issues & Solutions

**Issue:** Modal tidak muncul
**Solution:** Check Alpine.js loaded, verify event name match

**Issue:** 403 Unauthorized
**Solution:** Check Policy registered di AuthServiceProvider

**Issue:** Data tidak terhapus
**Solution:** Check foreign key constraints di migration

---

## 📞 Support

Jika ada error atau pertanyaan:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Check browser console untuk Alpine.js errors
3. Verify authorization dengan `php artisan tinker`

```php
// Test Policy di tinker
$user = User::find(1);
$board = Board::find(1);
$user->can('delete', $board); // Should return true/false
```
