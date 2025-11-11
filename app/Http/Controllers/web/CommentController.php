<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Card;
use App\Models\Subtask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;



/**
 * ====================================================================================================
 * CommentController - Web Routes
 * ====================================================================================================
 * 
 * Controller ini mengelola fitur komentar untuk card dan subtask dengan dual response handling:
 * - AJAX requests: Return JSON untuk real-time updates
 * - Form submissions: Return redirect dengan flash messages
 * 
 * Mendukung progressive enhancement untuk kompatibilitas browser dan accessibility.
 * Mendukung progressive enhancement untuk kompatibilitas browser dan accessibility.
 * 
 * Fitur utama:
 * - CRUD komentar pada card (di modal detail card)
 * - CRUD komentar pada subtask (di modal detail subtask)
 * - Real-time comment display dengan Alpine.js
 * - Authorization berbasis role untuk setiap operasi
 * 
 * Role Access:
 * - Team Lead: bisa comment di card yang dibuat/ditugaskan
 * - Developer/Designer: bisa comment di card & subtask yang accessible
 * 
 * ====================================================================================================
 */
class CommentController extends Controller
{



    /**
     * ====================================================================================================
     * STORE COMMENT (CREATE)
     * ====================================================================================================
     * 
     * Method ini digunakan untuk menambahkan komentar baru pada card atau subtask.
     * 
     * Alur kerja:
     * 1. Validasi input (card_id/subtask_id, comment_text, comment_type)
     * 2. Cek authorization berdasarkan role dan akses project
     * 3. Simpan komentar ke database
     * 4. Return JSON response untuk AJAX request
     * 
     * Authorization:
     * - User harus member dari project
     * - Team Lead: hanya bisa comment di card yang dibuat/ditugaskan
     * - Developer/Designer: bisa comment di semua card & subtask yang accessible
     * 
     * @param Request $request - HTTP request dengan data comment
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validasi input
        $validatedData = $request->validate([
            'card_id' => 'nullable|exists:cards,id',
            'subtask_id' => 'nullable|exists:subtasks,id',
            'comment_text' => 'required|string|max:5000',
            'comment_type' => 'required|in:card,subtask'
        ]);



        // Pastikan ada card_id atau subtask_id sesuai dengan comment_type
        if ($validatedData['comment_type'] === 'card' && empty($validatedData['card_id'])) {
            return response()->json([
                'success' => false,
                'message' => 'Card ID required for card comment.'
            ], 422);
        }



        if ($validatedData['comment_type'] === 'subtask' && empty($validatedData['subtask_id'])) {
            return response()->json([
                'success' => false,
                'message' => 'Subtask ID required for subtask comment.'
            ], 422);
        }



        try {
            $currentUser = Auth::user();



            // Authorization check berdasarkan comment type
            if ($validatedData['comment_type'] === 'card') {
                $card = Card::findOrFail($validatedData['card_id']);
                $project = $card->board->project;
                
                // Cek akses ke project
                $projectMember = $project->members->where('user_id', $currentUser->id)->first();
                $isCreator = $project->created_by === $currentUser->id;
                
                if (!$projectMember && !$isCreator) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda tidak memiliki akses ke project ini.'
                    ], 403);
                }



                // Jika team lead, cek apakah card dibuat atau ditugaskan ke dia
                if ($projectMember && $projectMember->role === 'team lead') {
                    $isAssigned = $card->assignments()->where('user_id', $currentUser->id)->exists();
                    $isCardCreator = $card->created_by === $currentUser->id;
                    
                    if (!$isAssigned && !$isCardCreator) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Team Lead hanya bisa comment di card yang dibuat atau ditugaskan ke Anda.'
                        ], 403);
                    }
                }
            } 
            
            
            
            else {
                // Comment untuk subtask
                $subtask = Subtask::findOrFail($validatedData['subtask_id']);
                $card = $subtask->card;
                $project = $card->board->project;
                
                // Cek akses ke project
                $projectMember = $project->members->where('user_id', $currentUser->id)->first();
                $isCreator = $project->created_by === $currentUser->id;
                
                if (!$projectMember && !$isCreator) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda tidak memiliki akses ke project ini.'
                    ], 403);
                }



                // Hanya developer/designer yang bisa comment di subtask
                if ($projectMember && !in_array($projectMember->role, ['developer', 'designer'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Hanya Developer dan Designer yang bisa comment di subtask.'
                    ], 403);
                }
                
                // Set card_id untuk subtask comment
                $validatedData['card_id'] = $card->id;
            }



            // Buat comment baru
            $comment = Comment::create([
                'card_id' => $validatedData['card_id'] ?? null,
                'subtask_id' => $validatedData['subtask_id'] ?? null,
                'user_id' => $currentUser->id,
                'comment_text' => $validatedData['comment_text'],
                'comment_type' => $validatedData['comment_type']
            ]);



            // Load relationship untuk response
            $comment->load('user');



            // Log untuk debugging
            Log::info('Comment created', [
                'comment_id' => $comment->id,
                'comment_type' => $comment->comment_type,
                'card_id' => $comment->card_id,
                'subtask_id' => $comment->subtask_id,
                'user_id' => $comment->user_id
            ]);



            // Check if AJAX request
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Komentar berhasil ditambahkan!',
                    'comment' => [
                        'id' => $comment->id,
                        'comment_text' => $comment->comment_text,
                        'user_name' => $comment->user->username,
                        'user_id' => $comment->user_id,
                        'created_at' => $comment->created_at->toIso8601String(),
                        'created_at_human' => $comment->created_at->diffForHumans()
                    ]
                ], 201);
            }
            
            // Regular form submission - redirect back with success message
            return redirect()->back()->with('success', 'Komentar berhasil ditambahkan!');

        } catch (\Exception $e) {
            Log::error('Failed to create comment', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Check if AJAX request
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menambahkan komentar: ' . $e->getMessage()
                ], 500);
            }
            
            // Regular form submission - redirect back with error message
            return redirect()->back()->with('error', 'Gagal menambahkan komentar: ' . $e->getMessage());
        }
    }



    /**
     * ====================================================================================================
     * UPDATE COMMENT
     * ====================================================================================================
     * 
     * Method ini digunakan untuk mengupdate komentar yang sudah ada.
     * 
     * Alur kerja:
     * 1. Validasi input (comment_text)
     * 2. Cek authorization (hanya owner yang bisa edit)
     * 3. Update comment text
     * 4. Return JSON response
     * 
     * Authorization:
     * - Hanya user yang membuat comment yang bisa edit
     * 
     * @param Request $request - HTTP request dengan data update
     * @param Comment $comment - Instance comment yang akan diupdate
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Comment $comment)
    {
        // Validasi input
        $validatedData = $request->validate([
            'comment_text' => 'required|string|max:5000'
        ]);



        try {
            $currentUser = Auth::user();



            // Authorization check - hanya owner yang bisa edit
            if ($comment->user_id !== $currentUser->id) {
                // Check if AJAX request
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda tidak memiliki izin untuk mengedit komentar ini.'
                    ], 403);
                }
                
                // Regular form submission
                return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk mengedit komentar ini.');
            }



            // Update comment
            $comment->update([
                'comment_text' => $validatedData['comment_text']
            ]);



            // Check if AJAX request
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Komentar berhasil diupdate!',
                    'comment' => [
                        'id' => $comment->id,
                        'comment_text' => $comment->comment_text,
                        'updated' => true
                    ]
                ], 200);
            }
            
            // Regular form submission - redirect back with success message
            return redirect()->back()->with('success', 'Komentar berhasil diupdate!');

        } catch (\Exception $e) {
            // Check if AJAX request
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengupdate komentar: ' . $e->getMessage()
                ], 500);
            }
            
            // Regular form submission - redirect back with error message
            return redirect()->back()->with('error', 'Gagal mengupdate komentar: ' . $e->getMessage());
        }
    }



    /**
     * ====================================================================================================
     * DELETE COMMENT
     * ====================================================================================================
     * 
     * Method ini digunakan untuk menghapus komentar.
     * 
     * Alur kerja:
     * 1. Cek authorization (hanya owner yang bisa delete)
     * 2. Hapus comment dari database
     * 3. Return JSON response
     * 
     * Authorization:
     * - Hanya user yang membuat comment yang bisa delete
     * 
     * @param Comment $comment - Instance comment yang akan dihapus
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, Comment $comment)
    {
        try {
            $currentUser = Auth::user();



            // Authorization check - hanya owner yang bisa delete
            if ($comment->user_id !== $currentUser->id) {
                // Check if AJAX request
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda tidak memiliki izin untuk menghapus komentar ini.'
                    ], 403);
                }
                
                // Regular form submission
                return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk menghapus komentar ini.');
            }



            // Hapus comment
            $comment->delete();



            // Check if AJAX request
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Komentar berhasil dihapus!'
                ], 200);
            }
            
            // Regular form submission - redirect back with success message
            return redirect()->back()->with('success', 'Komentar berhasil dihapus!');

        } catch (\Exception $e) {
            // Check if AJAX request
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus komentar: ' . $e->getMessage()
                ], 500);
            }
            
            // Regular form submission - redirect back with error message
            return redirect()->back()->with('error', 'Gagal menghapus komentar: ' . $e->getMessage());
        }
    }



    /**
     * ====================================================================================================
     * GET COMMENTS FOR CARD
     * ====================================================================================================
     * 
     * Method ini mengambil semua komentar untuk satu card.
     * Digunakan untuk load comments saat modal dibuka.
     * 
     * Return format:
     * {
     *   "success": true,
     *   "comments": [...]
     * }
     * 
     * @param int $cardId - ID card
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCommentsForCard($cardId)
    {
        try {
            $card = Card::findOrFail($cardId);
            $currentUser = Auth::user();



            // Authorization check
            $project = $card->board->project;
            $projectMember = $project->members->where('user_id', $currentUser->id)->first();
            $isCreator = $project->created_by === $currentUser->id;
            
            if (!$projectMember && !$isCreator) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke project ini.'
                ], 403);
            }



            // Get comments
            $comments = Comment::where('card_id', $cardId)
                ->where('comment_type', 'card')
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($comment) use ($currentUser) {
                    return [
                        'id' => $comment->id,
                        'comment_text' => $comment->comment_text,
                        'user_name' => $comment->user->username,
                        'user_id' => $comment->user_id,
                        'is_owner' => $comment->user_id === $currentUser->id,
                        'created_at' => $comment->created_at->toIso8601String(),
                        'created_at_human' => $comment->created_at->diffForHumans()
                    ];
                });



            return response()->json([
                'success' => true,
                'comments' => $comments
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil komentar: ' . $e->getMessage()
            ], 500);
        }
    }



    /**
     * ====================================================================================================
     * GET COMMENTS FOR SUBTASK
     * ====================================================================================================
     * 
     * Method ini mengambil semua komentar untuk satu subtask.
     * 
     * Authorization:
     * - Hanya developer/designer yang bisa lihat comments subtask
     * 
     * @param int $subtaskId - ID subtask
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCommentsForSubtask($subtaskId)
    {
        try {
            $subtask = Subtask::findOrFail($subtaskId);
            $currentUser = Auth::user();



            // Authorization check
            $card = $subtask->card;
            $project = $card->board->project;
            $projectMember = $project->members->where('user_id', $currentUser->id)->first();
            $isCreator = $project->created_by === $currentUser->id;
            
            if (!$projectMember && !$isCreator) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke project ini.'
                ], 403);
            }



            // Hanya developer/designer yang bisa lihat comments subtask
            if ($projectMember && !in_array($projectMember->role, ['developer', 'designer'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya Developer dan Designer yang bisa melihat comments subtask.'
                ], 403);
            }



            // Get comments
            $comments = Comment::where('subtask_id', $subtaskId)
                ->where('comment_type', 'subtask')
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($comment) use ($currentUser) {
                    return [
                        'id' => $comment->id,
                        'comment_text' => $comment->comment_text,
                        'user_name' => $comment->user->full_name,
                        'user_id' => $comment->user_id,
                        'is_owner' => $comment->user_id === $currentUser->id,
                        'created_at' => $comment->created_at->toIso8601String(),
                        'created_at_human' => $comment->created_at->diffForHumans()
                    ];
                });



            return response()->json([
                'success' => true,
                'comments' => $comments
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil komentar: ' . $e->getMessage()
            ], 500);
        }
    }
}
