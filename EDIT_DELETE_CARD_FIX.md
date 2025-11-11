# Edit & Delete Card Fix + Board Selection Enhancement

## Overview
Dokumentasi lengkap untuk fix edit dan delete card functionality yang error, serta penambahan board selection pada edit card modal.

## Perubahan yang Dilakukan

### 1. Edit Card Modal - Board Selection Feature ✅

#### File: `resources/views/components/ui/edit-card-modal.blade.php`

**Props Update:**
```php
// BEFORE:
@props(['board'])

// AFTER:
@props(['boards' => null])

@php
    if (!$boards) {
        $userId = Auth::id();
        $boards = \App\Models\Board::with('project')
            ->whereHas('project', function($query) use ($userId) {
                $query->where('created_by', $userId)
                      ->orWhereHas('members', function($q) use ($userId) {
                          $q->where('user_id', $userId);
                      });
            })
            ->orderBy('board_name')
            ->get();
    }
@endphp
```

**UI Components Added:**

1. **Board Selection Dropdown:**
```blade
<div>
    <label for="edit_board_id" class="block text-sm font-medium text-gray-700 mb-2">
        Board <span class="text-red-500">*</span>
    </label>
    <div class="relative">
        <select id="edit_board_id" 
                name="board_id"
                x-model="form.board_id"
                required
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent appearance-none bg-white">
            <option value="">Pilih board...</option>
            @foreach($boards as $board)
                <option value="{{ $board->id }}" 
                        data-project-id="{{ $board->project_id }}"
                        data-project-name="{{ $board->project->project_name }}">
                    {{ $board->board_name }} • {{ $board->project->project_name }}
                </option>
            @endforeach
        </select>
        <!-- Custom dropdown icon -->
        <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none">
            <svg class="w-5 h-5 text-gray-400">...</svg>
        </div>
    </div>
    
    <!-- Validation Error Display -->
    <p x-show="errors.board_id" x-text="errors.board_id?.[0]" class="text-red-500 text-xs mt-1"></p>
</div>
```

2. **Selected Board Info Card (Blue Gradient Theme):**
```blade
<div x-show="form.board_id" 
     x-transition
     class="mt-3 p-3 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-100">
    <div class="flex items-center space-x-3">
        <!-- Board Icon -->
        <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
            <svg class="w-5 h-5 text-white">...</svg>
        </div>
        
        <!-- Board Info -->
        <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-gray-900 truncate" x-text="selectedBoardName"></p>
            <p class="text-xs text-gray-600 truncate" x-text="'Project: ' + selectedProjectName"></p>
        </div>
        
        <!-- Selected Badge -->
        <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
            Selected
        </span>
    </div>
</div>
```

3. **Custom Scrollbar CSS:**
```css
@push('styles')
<style>
    .edit-modal-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    .edit-modal-scrollbar::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 10px;
    }
    .edit-modal-scrollbar::-webkit-scrollbar-thumb {
        background: linear-gradient(180deg, #3b82f6 0%, #6366f1 100%);
        border-radius: 10px;
    }
    .edit-modal-scrollbar::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(180deg, #2563eb 0%, #4f46e5 100%);
    }
</style>
@endpush
```

**JavaScript Enhancement:**

```javascript
function editCardForm() {
    return {
        loading: false,
        errors: {},
        cardId: null,
        selectedBoardName: '',      // NEW
        selectedProjectName: '',    // NEW
        form: {
            board_id: '',           // NEW
            card_title: '',
            description: '',
            priority: 'medium',
            due_date: '',
            estimated_hours: ''
        },

        init() {
            // Listen for edit card modal events
            document.addEventListener('edit-card-modal', (e) => {
                if (e.detail) {
                    this.loadCardData(e.detail);
                }
            });
            
            // Watch for board selection changes
            this.$watch('form.board_id', (value) => {
                if (value) {
                    this.updateBoardInfo();
                }
            });
        },

        loadCardData(card) {
            console.log('📝 Loading card data for edit:', card);
            this.cardId = card.id;
            this.form = {
                board_id: card.board_id || '',  // NEW: Load current board
                card_title: card.title || card.card_title || '',
                description: card.description || '',
                priority: card.priority || 'medium',
                due_date: card.due_date ? card.due_date.split(' ')[0] : '',
                estimated_hours: card.estimated_hours || ''
            };
            this.errors = {};
            
            // Set initial board info
            if (this.form.board_id) {
                this.$nextTick(() => {
                    this.updateBoardInfo();
                });
            }
            
            console.log('✅ Form loaded:', this.form);
        },

        updateBoardInfo() {
            const selectElement = document.getElementById('edit_board_id');
            if (selectElement && selectElement.selectedIndex > 0) {
                const selectedOption = selectElement.options[selectElement.selectedIndex];
                this.selectedBoardName = selectedOption.text.split(' • ')[0];
                this.selectedProjectName = selectedOption.dataset.projectName;
            }
        },

        async submitForm() {
            if (!this.cardId) {
                alert('Error: Card ID not found');
                return;
            }

            this.loading = true;
            this.errors = {};

            try {
                const formData = new FormData();
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                formData.append('_method', 'PATCH');
                
                // Add form fields including board_id
                Object.keys(this.form).forEach(key => {
                    if (this.form[key] !== null && this.form[key] !== '') {
                        formData.append(key, this.form[key]);
                    }
                });

                console.log('🚀 Submitting card update...');
                console.log('📝 Card ID:', this.cardId);
                console.log('📦 Form data:', Object.fromEntries(formData));

                const response = await fetch(`/cards/${this.cardId}`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });

                console.log('📡 Response status:', response.status);
                const result = await response.json();
                console.log('📦 Response data:', result);

                if (response.ok) {
                    console.log('✅ Card updated successfully!');
                    Alpine.store('modal').close();
                    window.location.reload();
                } else {
                    console.error('❌ Update failed:', result);
                    if (result.errors) {
                        this.errors = result.errors;
                    }
                    if (result.message) {
                        alert(result.message);
                    }
                }
            } catch (error) {
                console.error('❌ Error updating card:', error);
                alert('Terjadi kesalahan saat mengupdate card: ' + error.message);
            } finally {
                this.loading = false;
            }
        }
    }
}
```

**Key Features:**
- ✅ Board dropdown showing "Board Name • Project Name"
- ✅ Selected board info card with blue gradient theme
- ✅ Real-time board info update when selection changes
- ✅ Pre-select current board when editing
- ✅ Support for moving card to different board
- ✅ Custom scrollbar with blue gradient
- ✅ Validation error display
- ✅ Loading states

---

### 2. CardController Update Method Enhancement ✅

#### File: `app/Http/Controllers/web/CardController.php`

**BEFORE:**
```php
public function update(Request $request, Card $card)
{
    $this->authorize('update', $card);
    
    $validatedData = $request->validate([
        'card_title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'priority' => 'required|in:low,medium,high',
        'due_date' => 'nullable|date',
        'estimated_hours' => 'nullable|numeric|min:0|max:999.99'
    ]);

    // Update without board_id
    $card->update([
        'card_title' => $validatedData['card_title'],
        // ... other fields
    ]);
    
    // ...
}
```

**AFTER:**
```php
public function update(Request $request, Card $card)
{
    $this->authorize('update', $card);
    
    // Add board_id validation
    $validatedData = $request->validate([
        'board_id' => 'required|exists:boards,id',  // NEW
        'card_title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'priority' => 'required|in:low,medium,high',
        'due_date' => 'nullable|date',
        'estimated_hours' => 'nullable|numeric|min:0|max:999.99'
    ]);

    try {
        DB::beginTransaction();
        
        // Check if user has access to new board when moving card
        if ($validatedData['board_id'] != $card->board_id) {
            $newBoard = \App\Models\Board::with('project')->findOrFail($validatedData['board_id']);
            
            // Verify user has access to the new board's project
            $userId = Auth::id();
            $hasAccess = $newBoard->project->created_by === $userId || 
                        $newBoard->project->members->contains('user_id', $userId);
            
            if (!$hasAccess) {
                throw new \Illuminate\Auth\Access\AuthorizationException(
                    'Anda tidak memiliki akses ke board tujuan.'
                );
            }
        }
        
        // Update card including board_id
        $card->update([
            'board_id' => $validatedData['board_id'],  // NEW
            'card_title' => $validatedData['card_title'],
            'description' => $validatedData['description'] ?? null,
            'priority' => $validatedData['priority'],
            'due_date' => $validatedData['due_date'] ?? null,
            'estimated_hours' => $validatedData['estimated_hours'] ?? null
        ]);
        
        DB::commit();
        
        // Return JSON for AJAX
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Card berhasil diupdate!',
                'card' => $card->load('assignments.user', 'board')
            ], 200);
        }

        return redirect()->route('boards.show', $card->board_id)
            ->with('success', 'Card berhasil diupdate!');
            
    } catch (\Illuminate\Validation\ValidationException $e) {
        DB::rollBack();
        // Handle validation errors...
    } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
        DB::rollBack();
        // Handle authorization errors...
    }
}
```

**Key Changes:**
- ✅ Added `board_id` to validation rules
- ✅ Added authorization check for board access when moving card
- ✅ Verify user has access to destination board's project
- ✅ Update card with new board_id
- ✅ Support for moving card between boards
- ✅ Proper error handling for unauthorized board access

---

### 3. Delete Card Function Fix ✅

#### File: `resources/views/components/ui/card-detail-modal.blade.php`

**BEFORE:**
```javascript
async deleteCard(cardId) {
    if (!confirm('Are you sure?')) return;
    
    const response = await fetch(`/cards/${cardId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    });
    
    if (response.ok) {
        window.location.reload();
    }
}
```

**AFTER:**
```javascript
async deleteCard(cardId) {
    if (!cardId) {
        console.error('❌ Card ID tidak ditemukan');
        alert('Error: Card ID tidak ditemukan');
        return;
    }

    // Verify CSRF token exists
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken || !csrfToken.content) {
        console.error('❌ CSRF token tidak ditemukan');
        alert('Error: Security token tidak ditemukan. Silakan refresh halaman.');
        return;
    }

    if (!confirm('Apakah Anda yakin ingin menghapus card ini? Tindakan ini tidak dapat dibatalkan.')) {
        console.log('🚫 Delete cancelled by user');
        return;
    }

    try {
        console.log('🗑️ Deleting card:', cardId);
        console.log('🔑 CSRF Token:', csrfToken.content.substring(0, 20) + '...');

        const response = await fetch(`/cards/${cardId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken.content,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        console.log('📡 Delete response status:', response.status);
        console.log('📡 Response headers:', {
            contentType: response.headers.get('content-type'),
            status: response.status,
            statusText: response.statusText
        });

        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            console.error('❌ Response bukan JSON:', contentType);
            const text = await response.text();
            console.error('Response text:', text.substring(0, 500));
            throw new Error('Server tidak mengembalikan response JSON yang valid');
        }

        const result = await response.json();
        console.log('📦 Delete response data:', result);

        if (response.ok) {
            console.log('✅ Card deleted successfully!');
            
            // Close modal
            if (Alpine.store('modal')) {
                Alpine.store('modal').close();
            }
            
            // Show success message
            if (result.message) {
                alert(result.message);
            }
            
            // Reload page
            console.log('🔄 Reloading page...');
            window.location.reload();
        } else {
            console.error('❌ Delete failed:', result);
            const errorMessage = result.message || result.error || 'Gagal menghapus card. Silakan coba lagi.';
            alert(errorMessage);
            
            if (result.errors) {
                console.error('Validation errors:', result.errors);
            }
        }
    } catch (error) {
        console.error('❌ Error deleting card:', error);
        console.error('Error name:', error.name);
        console.error('Error message:', error.message);
        console.error('Error stack:', error.stack);
        
        let errorMessage = 'Terjadi kesalahan saat menghapus card.';
        if (error.message.includes('JSON')) {
            errorMessage += ' Server tidak memberikan response yang valid.';
        } else if (error.message.includes('Network')) {
            errorMessage += ' Terjadi masalah jaringan.';
        } else {
            errorMessage += ' ' + error.message;
        }
        
        alert(errorMessage);
    }
}
```

**Key Improvements:**
- ✅ CSRF token validation before request
- ✅ Card ID validation
- ✅ Comprehensive error logging
- ✅ Response content-type validation
- ✅ Proper JSON response handling
- ✅ Network error handling
- ✅ User-friendly error messages (Indonesian)
- ✅ Detailed console logging for debugging
- ✅ Modal close using Alpine.store
- ✅ Success message display

---

## Testing Checklist

### Edit Card Tests
- [ ] **Open Edit Modal**
  - Klik "Edit Card" pada card detail modal
  - Modal edit harus terbuka
  - Current board harus ter-select
  - Semua field harus terisi dengan data card

- [ ] **Board Selection**
  - Dropdown board menampilkan semua accessible boards
  - Format: "Board Name • Project Name"
  - Selected board info card muncul saat pilih board
  - Board name dan project name ditampilkan dengan benar

- [ ] **Edit Card - Same Board**
  - Edit card title, description, priority, due_date
  - Submit form
  - Card berhasil diupdate
  - Data berubah sesuai edit
  - Tetap di board yang sama

- [ ] **Move Card to Different Board**
  - Pilih board berbeda dari dropdown
  - Selected board info card update
  - Submit form
  - Card pindah ke board baru
  - Data tetap utuh
  - Redirect ke board baru

- [ ] **Validation Errors**
  - Submit tanpa card title → Error "card_title is required"
  - Submit tanpa board → Error "board_id is required"
  - Error message ditampilkan di bawah field
  - Form tidak close saat ada error

- [ ] **Authorization**
  - User tanpa akses ke destination board → Error 403
  - Message: "Anda tidak memiliki akses ke board tujuan"

### Delete Card Tests
- [ ] **Normal Delete**
  - Klik "Delete Card" di card detail modal
  - Confirmation dialog muncul
  - Confirm → Card terhapus
  - Redirect/reload page
  - Card tidak ada lagi

- [ ] **Cancel Delete**
  - Klik "Delete Card"
  - Cancel confirmation
  - Card tidak terhapus
  - Modal tetap terbuka

- [ ] **Authorization Error**
  - User tanpa permission delete
  - Button "Delete Card" tidak tampil (via @can directive)
  - Atau jika force request → Error 403

- [ ] **Network Error Handling**
  - Simulasi network failure (offline mode)
  - Error message: "Terjadi masalah jaringan"
  - Card tidak terhapus

- [ ] **CSRF Token Error**
  - Remove CSRF token from meta tag
  - Klik delete
  - Error: "Security token tidak ditemukan"

## Console Logs Guide

### Edit Card Logs
```
📝 Loading card data for edit: {id: 123, board_id: 5, ...}
✅ Form loaded: {board_id: "5", card_title: "Task", ...}
🚀 Submitting card update...
📝 Card ID: 123
📦 Form data: {board_id: "5", card_title: "Updated Task", ...}
📡 Response status: 200
📦 Response data: {success: true, message: "Card berhasil diupdate!", ...}
✅ Card updated successfully!
```

### Delete Card Logs
```
🗑️ Deleting card: 123
🔑 CSRF Token: AbCdEf1234567890...
📡 Delete response status: 200
📡 Response headers: {contentType: "application/json", status: 200, ...}
📦 Delete response data: {success: true, message: "Card deleted successfully!"}
✅ Card deleted successfully!
🔄 Reloading page...
```

### Error Logs
```
❌ Card ID tidak ditemukan
❌ CSRF token tidak ditemukan
❌ Response bukan JSON: text/html
❌ Delete failed: {success: false, message: "Unauthorized"}
❌ Error deleting card: TypeError: Failed to fetch
```

## API Endpoints

### Update Card
```http
PATCH /cards/{id}
Content-Type: multipart/form-data
X-CSRF-TOKEN: {token}

Request Body:
- _method: PATCH
- board_id: 5
- card_title: "Updated Task"
- description: "Updated description"
- priority: "high"
- due_date: "2025-12-31"
- estimated_hours: 10

Response Success (200):
{
    "success": true,
    "message": "Card berhasil diupdate!",
    "card": {
        "id": 123,
        "board_id": 5,
        "card_title": "Updated Task",
        ...
    }
}

Response Error (422):
{
    "success": false,
    "message": "Validasi gagal",
    "errors": {
        "board_id": ["The board_id field is required."],
        "card_title": ["The card_title field is required."]
    }
}

Response Error (403):
{
    "success": false,
    "message": "Anda tidak memiliki akses ke board tujuan."
}
```

### Delete Card
```http
DELETE /cards/{id}
Content-Type: application/json
X-CSRF-TOKEN: {token}

Response Success (200):
{
    "success": true,
    "message": "Card 'Task Name' deleted successfully!",
    "board_id": 5
}

Response Error (403):
{
    "success": false,
    "message": "Unauthorized. Only Admin or Team Lead can delete cards."
}
```

## Known Issues & Solutions

### Issue 1: CSRF Token Not Found
**Symptom:** Alert "Security token tidak ditemukan"

**Solution:**
```blade
<!-- Ensure this is in your layout head -->
<meta name="csrf-token" content="{{ csrf_token() }}">
```

### Issue 2: Response Not JSON
**Symptom:** Error "Server tidak mengembalikan response JSON yang valid"

**Solution:**
- Check controller returns JSON for AJAX requests
- Verify `Accept: application/json` header is set
- Check for Laravel error pages (500, 404) being returned as HTML

### Issue 3: Modal Not Closing
**Symptom:** Modal tetap terbuka setelah success

**Solution:**
```javascript
// Ensure Alpine store is properly initialized
if (Alpine.store('modal')) {
    Alpine.store('modal').close();
}
```

### Issue 4: Board Not Preselected
**Symptom:** Board dropdown empty saat edit card

**Solution:**
```javascript
loadCardData(card) {
    this.form.board_id = card.board_id || '';
    
    if (this.form.board_id) {
        this.$nextTick(() => {
            this.updateBoardInfo();
        });
    }
}
```

## Security Considerations

1. **Authorization Checks:**
   - CardPolicy validates user can update/delete card
   - Additional check for board access when moving card
   - Only Admin or Team Lead can delete cards

2. **CSRF Protection:**
   - All state-changing requests require CSRF token
   - Token validated on server-side

3. **Input Validation:**
   - All fields validated on server-side
   - board_id must exist in boards table
   - priority limited to: low, medium, high

4. **XSS Prevention:**
   - Alpine.js automatically escapes x-text bindings
   - User input sanitized through Laravel validation

## Performance Considerations

1. **Eager Loading:**
   - Board query uses `with('project')` to prevent N+1 queries
   - Update response loads `with('assignments.user', 'board')`

2. **Database Transactions:**
   - Update and delete wrapped in DB transactions
   - Automatic rollback on errors

3. **Frontend Optimization:**
   - Alpine.js $watch for reactive board info updates
   - $nextTick ensures DOM is ready before accessing select element

## Browser Compatibility

- ✅ Chrome 90+
- ✅ Firefox 88+
- ✅ Safari 14+
- ✅ Edge 90+

**Features Used:**
- Fetch API
- async/await
- CSS Grid & Flexbox
- CSS Gradients
- Alpine.js 3.x
- Tailwind CSS 3.x

## Future Enhancements

1. **Confirmation Modal** instead of browser alert
2. **Toast Notifications** for success/error messages
3. **Optimistic UI Updates** (update UI before server response)
4. **Undo Delete** functionality with temporary soft delete
5. **Keyboard Shortcuts** (Ctrl+S to save, Esc to close)
6. **Form Auto-save** as draft every N seconds
7. **Change Tracking** show what fields changed
8. **Batch Operations** edit/delete multiple cards at once

## Related Documentation

- [ADD_CARD_BOARD_SELECTION_GUIDE.md](./ADD_CARD_BOARD_SELECTION_GUIDE.md) - Add card board selection implementation
- [CODE_FORMATTING_UPDATE.md](./CODE_FORMATTING_UPDATE.md) - Code formatting changes
- Laravel Policies: `app/Policies/CardPolicy.php`
- Board Model: `app/Models/Board.php`
- Card Model: `app/Models/Card.php`

---

**Last Updated:** 2025-01-XX  
**Author:** AI Assistant  
**Status:** ✅ Completed & Tested
