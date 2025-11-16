<?php

namespace App\Http\Controllers\web;

use App\Events\CardReviewed;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCardReviewRequest;
use App\Models\Card;
use App\Models\CardAssignment;
use App\Models\CardReview;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * CardReviewController
 * 
 * Controller untuk handle approve/reject card oleh Team Lead
 * - Menyimpan history review ke tabel card_reviews
 * - Update status card menjadi done (approved) atau todo (rejected)
 * - Update card_assignments (completed_at, assignment_status)
 * - Create notifications untuk assigned developers/designers
 * - Broadcast realtime event untuk notifikasi
 */
class CardReviewController extends Controller
{
    /**
     * Store card review (approve/reject)
     * 
     * Authorization: Hanya Admin atau Team Lead dari project terkait
     */
    public function store(StoreCardReviewRequest $request, Card $card)
    {
        $user = Auth::user();
        
        // Load relasi yang dibutuhkan
        $card->load('board.project');
        
        // Authorization check: Admin OR Team Lead di project ini
        $isAdmin = $user->role === 'admin';
        $isTeamLead = $card->board->project->members()
            ->where('user_id', $user->id)
            ->where('role', 'team lead')
            ->exists();
        
        if (!$isAdmin && !$isTeamLead) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk mereview card ini. Hanya Team Lead atau Admin yang dapat melakukan review.',
            ], 403);
        }
        
        $validated = $request->validated();
        
        DB::beginTransaction();
        try {
            // 1. Create review history
            $cardReview = CardReview::create([
                'card_id' => $card->id,
                'reviewed_by' => $user->id,
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
                'reviewed_at' => now(),
            ]);
            
            // 2. Update card status berdasarkan review
            if ($validated['status'] === 'approved') {
                // Approved: set status done
                $card->update(['status' => 'done']);
                
                // 3. Update semua assignments untuk card ini
                CardAssignment::where('card_id', $card->id)
                    ->where('assignment_status', '!=', 'completed')
                    ->update([
                        'assignment_status' => 'completed',
                        'completed_at' => now(),
                    ]);
                
                $message = 'Card berhasil di-approve! Status diubah menjadi Done.';
            } else {
                // Rejected: kembalikan ke todo untuk dikerjakan ulang
                $card->update(['status' => 'todo']);
                
                $message = 'Perubahan diminta. Card dikembalikan ke status Todo.';
            }
            
            // 4. Create notifications untuk semua assigned developers/designers
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
            
            DB::commit();
            
            // 5. Broadcast event untuk realtime notification
            event(new CardReviewed($cardReview, $card->fresh()));
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'review' => [
                    'id' => $cardReview->id,
                    'status' => $cardReview->status,
                    'notes' => $cardReview->notes,
                    'reviewed_by' => $user->full_name,
                    'reviewed_at' => $cardReview->reviewed_at->format('d M Y, H:i'),
                ],
                'card' => [
                    'id' => $card->id,
                    'status' => $card->status,
                ],
            ], 200);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses review: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Get review history for a card
     */
    public function index(Card $card)
    {
        $reviews = $card->reviews()
            ->with('reviewer:id,full_name,email')
            ->orderBy('reviewed_at', 'desc')
            ->get()
            ->map(function ($review) {
                return [
                    'id' => $review->id,
                    'status' => $review->status,
                    'status_text' => $review->status_text,
                    'notes' => $review->notes,
                    'reviewer' => [
                        'name' => $review->reviewer->full_name,
                        'email' => $review->reviewer->email,
                    ],
                    'reviewed_at' => $review->reviewed_at_formatted,
                ];
            });
        
        return response()->json([
            'success' => true,
            'reviews' => $reviews,
        ]);
    }
    
    /**
     * Show review history page untuk Developer/Designer
     * 
     * Menampilkan semua review dari card yang di-assign ke user yang login
     * Hanya untuk user dengan role 'developer' atau 'designer' di project_members
     */
    public function myReviews()
    {
        $user = Auth::user();
        
        // Get semua card yang assigned ke user ini
        $assignedCardIds = CardAssignment::where('user_id', $user->id)
            ->pluck('card_id')
            ->unique();
        
        // Check apakah user punya role developer/designer di project manapun
        $isDeveloperOrDesigner = DB::table('project_members')
            ->where('user_id', $user->id)
            ->whereIn('role', ['developer', 'designer'])
            ->exists();
        
        // Jika bukan developer/designer, redirect dengan pesan
        if (!$isDeveloperOrDesigner && $user->role !== 'admin') {
            return redirect()->route('dashboard')
                ->with('error', 'Halaman ini hanya untuk Developer dan Designer.');
        }
        
        // Get filter dari request
        $statusFilter = request('status'); // all, approved, rejected
        $search = request('search');
        
        // Query reviews untuk card yang assigned ke user
        $reviewsQuery = CardReview::with([
            'card.board.project',
            'reviewer:id,full_name,username,email'
        ])
        ->whereIn('card_id', $assignedCardIds)
        ->orderBy('reviewed_at', 'desc');
        
        // Apply status filter
        if ($statusFilter && $statusFilter !== 'all') {
            $reviewsQuery->where('status', $statusFilter);
        }
        
        // Apply search (cari di card title, reviewer name, notes)
        if ($search) {
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
        }
        
        $reviews = $reviewsQuery->paginate(15);
        
        // Group by date untuk timeline view
        $reviewsByDate = $reviews->groupBy(function ($review) {
            return $review->reviewed_at->format('Y-m-d');
        });
        
        // Statistics
        $stats = [
            'total' => CardReview::whereIn('card_id', $assignedCardIds)->count(),
            'approved' => CardReview::whereIn('card_id', $assignedCardIds)->where('status', 'approved')->count(),
            'rejected' => CardReview::whereIn('card_id', $assignedCardIds)->where('status', 'rejected')->count(),
        ];
        
        return view('card-reviews.my-reviews', compact('reviews', 'reviewsByDate', 'stats', 'statusFilter', 'search'));
    }
}
