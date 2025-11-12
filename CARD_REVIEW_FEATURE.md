# Card Review Feature - Documentation

## 📋 Overview

Fitur **Card Review** memungkinkan Team Lead untuk melakukan approve atau reject terhadap task/card yang dikerjakan team member. Setiap review disimpan sebagai history dengan keterangan opsional.

---

## 🗄️ Database Structure

### Tabel: `card_reviews`

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint (PK) | Primary key |
| `card_id` | bigint (FK) | Foreign key ke `cards.id` (cascade delete) |
| `reviewed_by` | bigint (FK) | Foreign key ke `users.id` (user yang melakukan review) |
| `status` | enum | Status review: `approved` atau `rejected` |
| `notes` | text (nullable) | Keterangan/catatan dari reviewer (opsional) |
| `reviewed_at` | timestamp | Waktu review dilakukan |

**Indexes:**
- `(card_id, reviewed_at)` - Untuk query history berdasarkan card dan waktu
- `reviewed_by` - Untuk query berdasarkan reviewer

---

## 📦 Model: CardReview

### Location
`app/Models/CardReview.php`

### Relationships

#### BelongsTo Relations
```php
// Relasi ke Card
$cardReview->card; // Card yang direview

// Relasi ke User (reviewer)
$cardReview->reviewer; // User yang melakukan review
```

### Scopes

```php
// Filter berdasarkan status
CardReview::status('approved')->get();
CardReview::approved()->get();
CardReview::rejected()->get();

// Filter berdasarkan card
CardReview::forCard($cardId)->get();

// Filter berdasarkan reviewer
CardReview::byReviewer($userId)->get();

// Urutan terbaru
CardReview::latestReviews()->get();
```

### Helper Methods

```php
// Cek status
$cardReview->isApproved(); // boolean
$cardReview->isRejected(); // boolean
$cardReview->hasNotes(); // boolean

// Accessor attributes
$cardReview->status_badge_color; // 'bg-green-100 text-green-800' atau 'bg-red-100 text-red-800'
$cardReview->status_text; // 'Approved' atau 'Rejected'
$cardReview->reviewed_at_formatted; // '11 Nov 2025, 14:30'
```

---

## 🔐 Authorization (Policy)

### Location
`app/Policies/CardReviewPolicy.php`

### Authorization Rules

#### 1. **viewAny** - Lihat Daftar Reviews
- ✅ Admin: Bisa lihat semua reviews
- ✅ Team Lead: Bisa lihat reviews di project mereka

#### 2. **view** - Lihat Detail Review
- ✅ Admin: Bisa lihat semua reviews
- ✅ Reviewer: Bisa lihat review yang mereka buat
- ✅ Team Lead: Bisa lihat reviews di project yang sama

#### 3. **create** - Buat Review (Approve/Reject)
- ✅ Admin: Bisa review semua cards
- ✅ Team Lead: Bisa review cards di project mereka

#### 4. **reviewCard** - Cek Apakah Bisa Review Card Tertentu
```php
// Usage di controller
$this->authorize('reviewCard', [CardReview::class, $card]);
```

#### 5. **update** - Update Review
- ✅ Admin: Bisa update semua reviews
- ✅ Reviewer: Bisa update review mereka sendiri

#### 6. **delete** - Delete Review
- ✅ Admin only: Hanya admin yang bisa delete history

---

## 🏭 Factory

### Location
`database/factories/CardReviewFactory.php`

### Usage

```php
// Create random review
CardReview::factory()->create();

// Create approved review
CardReview::factory()->approved()->create();

// Create rejected review (always with notes)
CardReview::factory()->rejected()->create();

// Create review dengan notes
CardReview::factory()->withNotes()->create();

// Create review tanpa notes
CardReview::factory()->withoutNotes()->create();

// Create review untuk card tertentu
CardReview::factory()->create([
    'card_id' => $card->id,
    'reviewed_by' => $teamLeadId,
]);
```

---

## 🔗 Model Relationships Update

### Card Model (`app/Models/Card.php`)

Tambahan relasi:

```php
// Mendapatkan semua review history dari card
$card->reviews; // Collection of CardReview

// Mendapatkan review terbaru
$card->reviews()->latest('reviewed_at')->first();

// Cek apakah card pernah di-approve
$card->reviews()->approved()->exists();

// Cek apakah card pernah di-reject
$card->reviews()->rejected()->exists();

// Hitung jumlah approve
$card->reviews()->approved()->count();
```

---

## 💻 Implementation Guide

### 1. Controller untuk Review Card

```php
// app/Http/Controllers/CardReviewController.php

public function store(Request $request, Card $card)
{
    // Authorization check
    $this->authorize('reviewCard', [CardReview::class, $card]);
    
    // Validation
    $validated = $request->validate([
        'status' => 'required|in:approved,rejected',
        'notes' => 'nullable|string|max:1000',
    ]);
    
    DB::beginTransaction();
    try {
        // Create review
        $review = CardReview::create([
            'card_id' => $card->id,
            'reviewed_by' => Auth::id(),
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
            'reviewed_at' => now(),
        ]);
        
        // Optional: Update card status based on review
        if ($validated['status'] === 'approved') {
            $card->update(['status' => 'done']);
        } elseif ($validated['status'] === 'rejected') {
            $card->update(['status' => 'todo']); // Back to todo
        }
        
        DB::commit();
        
        return redirect()->back()->with('success', 'Card reviewed successfully!');
    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->with('error', 'Failed to review card: ' . $e->getMessage());
    }
}
```

### 2. Blade Component untuk Review Modal

```blade
<!-- resources/views/components/card-review-modal.blade.php -->

<div x-data="{ showModal: false, status: '', notes: '' }">
    <!-- Trigger Button (hanya tampil untuk Team Lead) -->
    @can('reviewCard', [App\Models\CardReview::class, $card])
        <button @click="showModal = true" 
                class="px-4 py-2 bg-blue-600 text-white rounded-lg">
            Review Task
        </button>
    @endcan
    
    <!-- Modal -->
    <div x-show="showModal" 
         x-transition
         class="fixed inset-0 z-50 overflow-y-auto">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-50"></div>
        
        <!-- Modal Content -->
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                <h3 class="text-xl font-semibold mb-4">Review Task: {{ $card->card_title }}</h3>
                
                <form action="{{ route('card-reviews.store', $card) }}" method="POST">
                    @csrf
                    
                    <!-- Status Selection -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Decision <span class="text-red-500">*</span>
                        </label>
                        <div class="flex gap-4">
                            <label class="flex items-center">
                                <input type="radio" name="status" value="approved" required
                                       class="mr-2">
                                <span class="text-green-600 font-medium">✓ Approve</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="status" value="rejected" required
                                       class="mr-2">
                                <span class="text-red-600 font-medium">✗ Reject</span>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Notes (Optional) -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Notes (Optional)
                        </label>
                        <textarea name="notes" rows="4"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                  placeholder="Add feedback or reason..."></textarea>
                    </div>
                    
                    <!-- Actions -->
                    <div class="flex justify-end gap-3">
                        <button type="button" @click="showModal = false"
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg">
                            Submit Review
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
```

### 3. Display Review History

```blade
<!-- Display review history in card detail -->

<div class="mt-6">
    <h4 class="text-lg font-semibold mb-4">Review History</h4>
    
    @forelse($card->reviews()->latest('reviewed_at')->get() as $review)
        <div class="border border-gray-200 rounded-lg p-4 mb-3">
            <!-- Review Header -->
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-3">
                    <!-- Reviewer Avatar -->
                    <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-semibold">
                        {{ substr($review->reviewer->full_name, 0, 2) }}
                    </div>
                    <div>
                        <p class="font-medium">{{ $review->reviewer->full_name }}</p>
                        <p class="text-sm text-gray-500">{{ $review->reviewed_at_formatted }}</p>
                    </div>
                </div>
                
                <!-- Status Badge -->
                <span class="px-3 py-1 rounded-full text-sm font-medium {{ $review->status_badge_color }}">
                    {{ $review->status_text }}
                </span>
            </div>
            
            <!-- Review Notes -->
            @if($review->hasNotes())
                <div class="mt-3 p-3 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-700">{{ $review->notes }}</p>
                </div>
            @endif
        </div>
    @empty
        <p class="text-gray-500 text-center py-4">No review history yet.</p>
    @endforelse
</div>
```

---

## 📝 Routes

```php
// routes/web.php

// Card Review Routes (hanya untuk authenticated users)
Route::middleware(['auth'])->group(function () {
    // Create review (approve/reject)
    Route::post('/cards/{card}/reviews', [CardReviewController::class, 'store'])
        ->name('card-reviews.store');
    
    // View review history
    Route::get('/cards/{card}/reviews', [CardReviewController::class, 'index'])
        ->name('card-reviews.index');
    
    // Update review (jika diperlukan)
    Route::put('/card-reviews/{cardReview}', [CardReviewController::class, 'update'])
        ->name('card-reviews.update');
    
    // Delete review (admin only)
    Route::delete('/card-reviews/{cardReview}', [CardReviewController::class, 'destroy'])
        ->name('card-reviews.destroy');
});
```

---

## 🧪 Testing Examples

```php
// tests/Feature/CardReviewTest.php

// Test: Team Lead dapat approve card
public function test_team_lead_can_approve_card()
{
    $teamLead = User::factory()->create(['role' => 'user']);
    $project = Project::factory()->create();
    ProjectMember::factory()->create([
        'project_id' => $project->id,
        'user_id' => $teamLead->id,
        'role' => 'team lead'
    ]);
    
    $board = Board::factory()->create(['project_id' => $project->id]);
    $card = Card::factory()->create(['board_id' => $board->id]);
    
    $response = $this->actingAs($teamLead)->post(route('card-reviews.store', $card), [
        'status' => 'approved',
        'notes' => 'Great work!'
    ]);
    
    $response->assertRedirect();
    $this->assertDatabaseHas('card_reviews', [
        'card_id' => $card->id,
        'reviewed_by' => $teamLead->id,
        'status' => 'approved'
    ]);
}

// Test: Regular member tidak bisa review
public function test_regular_member_cannot_review_card()
{
    $developer = User::factory()->create(['role' => 'user']);
    $card = Card::factory()->create();
    
    $response = $this->actingAs($developer)->post(route('card-reviews.store', $card), [
        'status' => 'approved'
    ]);
    
    $response->assertForbidden();
}
```

---

## 📊 Query Examples

```php
// Mendapatkan semua reviews dari card
$reviews = $card->reviews;

// Mendapatkan review terbaru
$latestReview = $card->reviews()->latest('reviewed_at')->first();

// Cek apakah card sudah pernah di-approve
$isApproved = $card->reviews()->approved()->exists();

// Mendapatkan semua cards yang di-approve oleh user tertentu
$approvedCards = Card::whereHas('reviews', function($q) use ($userId) {
    $q->where('reviewed_by', $userId)
      ->where('status', 'approved');
})->get();

// Mendapatkan cards yang belum pernah direview
$unreviewedCards = Card::whereDoesntHave('reviews')->get();

// Statistik review per project
$project->boards->flatMap->cards->flatMap->reviews->groupBy('status')->map->count();
```

---

## 🎨 UI/UX Recommendations

### 1. Review Button Placement
- Di card detail page (paling prominent)
- Di kanban board (quick action)
- Di card hover menu

### 2. Review Indicators
- Badge di card: "Pending Review", "Approved", "Rejected"
- Color coding: Green (approved), Red (rejected), Yellow (pending)
- Review count badge

### 3. Notification
- Email notification ke card creator setelah review
- In-app notification
- Activity log

### 4. Review Workflow
```
Card Status: Review → Team Lead Reviews
    ↓ Approved → Status: Done
    ↓ Rejected → Status: Todo (back to work)
```

---

## 🔄 Migration Command

```bash
# Jalankan migration
php artisan migrate

# Rollback migration
php artisan migrate:rollback

# Check migration status
php artisan migrate:status
```

---

## 📌 Best Practices

1. **Always use authorization checks**
   ```php
   $this->authorize('reviewCard', [CardReview::class, $card]);
   ```

2. **Use database transactions**
   ```php
   DB::beginTransaction();
   // ... operations
   DB::commit();
   ```

3. **Validate notes for rejected status**
   ```php
   $request->validate([
       'status' => 'required|in:approved,rejected',
       'notes' => 'required_if:status,rejected|nullable|string|max:1000'
   ]);
   ```

4. **Log review activities**
   ```php
   Log::info("Card {$card->id} reviewed by {$user->id}", [
       'status' => $review->status,
       'has_notes' => $review->hasNotes()
   ]);
   ```

5. **Consider soft deletes for reviews**
   - Jika ingin keep history bahkan setelah delete

---

## 📚 Related Documentation

- [BoardPolicy.php](app/Policies/BoardPolicy.php) - Pattern authorization yang sama
- [Card Model](app/Models/Card.php) - Parent model
- [Project Structure](.github/copilot-instructions.md) - Overall architecture

---

## ✅ Feature Checklist

- [x] Migration created
- [x] Model created with relationships
- [x] Factory created with states
- [x] Policy created with authorization rules
- [x] Card model updated with reviews relationship
- [ ] Controller created (next step)
- [ ] Routes defined (next step)
- [ ] Blade components created (next step)
- [ ] Tests written (next step)

---

## 🚀 Next Steps

1. **Create CardReviewController**
   ```bash
   php artisan make:controller CardReviewController --resource
   ```

2. **Create Request Validation**
   ```bash
   php artisan make:request StoreCardReviewRequest
   php artisan make:request UpdateCardReviewRequest
   ```

3. **Create Blade Components**
   - Card review modal
   - Review history list
   - Review badge indicator

4. **Add to Seeder** (optional)
   ```php
   // database/seeders/CardReviewSeeder.php
   CardReview::factory()->count(50)->create();
   ```

---

**Created:** 11 November 2025  
**Author:** GitHub Copilot  
**Version:** 1.0
