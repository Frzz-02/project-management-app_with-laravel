<?php

namespace App\Http\Controllers\web;

use App\Events\CardReviewed;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCardReviewRequest;
use App\Models\Card;
use App\Models\CardAssignment;
use App\Models\CardReview;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * CardReviewController
 * 
 * Controller untuk handle approve/reject card oleh Team Lead
 * - Menyimpan history review ke tabel card_reviews
 * - Update status card menjadi done (approved) atau todo (rejected)
 * - Update card_assignments (completed_at, assignment_status)
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
            
            DB::commit();
            
            // 4. Broadcast event untuk realtime notification
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
}
