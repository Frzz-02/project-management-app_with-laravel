<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\CardAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * CardAssignmentController
 * 
 * Controller untuk mengelola assignment user ke card.
 * 
 * Authorization:
 * - Hanya Team Lead atau Card Creator yang bisa assign members
 * - Hanya Team Lead yang bisa melihat section assignment
 * 
 * @package App\Http\Controllers\web
 */
class CardAssignmentController extends Controller
{
    /**
     * Assign selected users to a card.
     * 
     * Flow:
     * 1. Validasi input (card_id, user_ids array)
     * 2. Cek authorization (team lead atau creator)
     * 3. Sync assignments (delete existing, create new)
     * 4. Return JSON response untuk AJAX
     * 
     * @param Request $request HTTP request dengan card_id dan assigned_users[]
     * @return \Illuminate\Http\JsonResponse JSON response dengan status dan data
     */
    public function assign(Request $request)
    {
        // Validasi input
        $validatedData = $request->validate([
            'card_id' => 'required|exists:cards,id',
            'assigned_users' => 'required|array',
            'assigned_users.*' => 'exists:users,id'
        ]);

        try {
            // Ambil card dengan relasi yang diperlukan
            $card = Card::with(['board.project.members', 'creator'])->findOrFail($validatedData['card_id']);
            
            // Authorization check
            $currentUser = Auth::user();
            $projectMember = $card->board->project->members->where('user_id', $currentUser->id)->first();
            
            // Cek apakah user adalah team lead ATAU creator card
            $isTeamLead = $projectMember && $projectMember->role === 'team lead';
            $isCreator = $card->created_by_id === $currentUser->id;
            
            if (!$isTeamLead && !$isCreator) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk assign members. Hanya Team Lead atau Card Creator yang bisa assign.'
                ], 403);
            }

            // Gunakan database transaction untuk consistency
            DB::beginTransaction();
            
            try {
                // Hapus semua assignment lama untuk card ini
                CardAssignment::where('card_id', $card->id)->delete();
                
                // Buat assignment baru untuk setiap user yang dipilih
                $assignments = [];
                foreach ($validatedData['assigned_users'] as $userId) {
                    $assignment = CardAssignment::create([
                        'card_id' => $card->id,
                        'user_id' => $userId,
                        'assignment_status' => 'assigned', // Default status
                        'started_at' => null,
                        'completed_at' => null
                    ]);
                    
                    // Load relasi user untuk response
                    $assignment->load('user');
                    $assignments[] = $assignment;
                }
                
                DB::commit();
                
                return response()->json([
                    'success' => true,
                    'message' => count($assignments) . ' member(s) berhasil di-assign ke card.',
                    'assignments' => $assignments
                ]);
                
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal assign members: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove assignment (unassign user from card).
     * 
     * @param Request $request HTTP request dengan card_id dan user_id
     * @return \Illuminate\Http\JsonResponse JSON response
     */
    public function unassign(Request $request)
    {
        $validatedData = $request->validate([
            'card_id' => 'required|exists:cards,id',
            'user_id' => 'required|exists:users,id'
        ]);

        try {
            $card = Card::with(['board.project.members', 'creator'])->findOrFail($validatedData['card_id']);
            
            // Authorization check
            $currentUser = Auth::user();
            $projectMember = $card->board->project->members->where('user_id', $currentUser->id)->first();
            
            $isTeamLead = $projectMember && $projectMember->role === 'team lead';
            $isCreator = $card->created_by_id === $currentUser->id;
            
            if (!$isTeamLead && !$isCreator) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk unassign members.'
                ], 403);
            }

            // Hapus assignment
            $deleted = CardAssignment::where('card_id', $card->id)
                ->where('user_id', $validatedData['user_id'])
                ->delete();

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Member berhasil di-unassign dari card.'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Assignment tidak ditemukan.'
                ], 404);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal unassign member: ' . $e->getMessage()
            ], 500);
        }
    }
}
