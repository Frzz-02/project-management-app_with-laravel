<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\Comment;
use App\Models\Subtask;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;



/**
 * ====================================================================================================
 * CommentController API
 * ====================================================================================================
 * 
 * API Controller untuk mengelola comments pada cards dan subtasks.
 * Selalu return JSON response untuk API consumption.
 * API Controller untuk mengelola comments pada cards dan subtasks.
 * Selalu return JSON response untuk API consumption.
 * 
 * Endpoints:
 * - GET /api/comments - List all comments (with filtering)
 * - POST /api/comments - Create new comment
 * - GET /api/comments/{id} - Get specific comment
 * - PUT /api/comments/{id} - Update comment
 * - DELETE /api/comments/{id} - Delete comment
 * - GET /api/cards/{card}/comments - Get comments by card
 * 
 * Features:
 * - Support comments on both cards and subtasks
 * - Authorization checking (user must have access to parent card/project)
 * - Nested comment structure support
 * - Real-time updates compatible with Alpine.js frontend
 * 
 * ====================================================================================================
 */
class CommentController extends Controller
{



    /**
     * ====================================================================================================
     * INDEX - Display a listing of comments
     * ====================================================================================================
     * 
     * Query parameters:
     * - card_id: Filter by card ID
     * - subtask_id: Filter by subtask ID
     * - limit: Limit results (default: 50)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Comment::query()
                ->with(['user', 'card.board.project', 'subtask.card.board.project']);

            // Filter by card ID
            if ($request->has('card_id')) {
                $query->where('card_id', $request->card_id);
            }

            // Filter by subtask ID
            if ($request->has('subtask_id')) {
                $query->where('subtask_id', $request->subtask_id);
            }

            // Apply authorization filter
            $query->where(function ($q) {
                // Comments on cards
                $q->whereHas('card.board.project', function ($projectQuery) {
                    $projectQuery->where('created_by', Auth::id())
                        ->orWhereHas('members', function ($memberQuery) {
                            $memberQuery->where('user_id', Auth::id());
                        });
                })
                // Comments on subtasks
                ->orWhereHas('subtask.card.board.project', function ($projectQuery) {
                    $projectQuery->where('created_by', Auth::id())
                        ->orWhereHas('members', function ($memberQuery) {
                            $memberQuery->where('user_id', Auth::id());
                        });
                });
            });

            // Order by creation date (newest first)
            $query->orderBy('created_at', 'desc');

            // Limit results
            $limit = min($request->get('limit', 50), 100);
            $comments = $query->limit($limit)->get();

            return response()->json([
                'success' => true,
                'data' => $comments,
                'message' => 'Comments retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve comments',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }



    /**
     * ====================================================================================================
     * STORE - Store a newly created comment
     * ====================================================================================================
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Validate request data
            $validated = $request->validate([
                'card_id' => 'required_without:subtask_id|exists:cards,id',
                'subtask_id' => 'required_without:card_id|exists:subtasks,id',
                'content' => 'required|string|max:2000'
            ]);

            // Get the target (card or subtask) and check authorization
            $card = null;
            if (isset($validated['card_id'])) {
                $card = Card::with(['board.project', 'board.project.members'])->findOrFail($validated['card_id']);
            } elseif (isset($validated['subtask_id'])) {
                $subtask = Subtask::with(['card.board.project', 'card.board.project.members'])->findOrFail($validated['subtask_id']);
                $card = $subtask->card;
            }

            // Check if user has access to this card's project
            if (!$this->hasProjectAccess($card)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to this resource'
                ], 403);
            }

            // Create comment with transaction
            DB::beginTransaction();
            
            $comment = Comment::create([
                'card_id' => $validated['card_id'] ?? null,
                'subtask_id' => $validated['subtask_id'] ?? null,
                'content' => $validated['content'],
                'user_id' => Auth::id()
            ]);

            // Load relationships for response
            $comment->load(['user', 'card.board.project', 'subtask.card.board.project']);
            
            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $comment,
                'message' => 'Comment created successfully'
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create comment',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }



    /**
     * ====================================================================================================
     * SHOW - Display the specified comment
     * ====================================================================================================
     */
    public function show(Comment $comment): JsonResponse
    {
        try {
            // Load relationships
            $comment->load(['user', 'card.board.project', 'subtask.card.board.project']);

            // Check authorization
            $card = $comment->card ?: $comment->subtask->card;
            if (!$this->hasProjectAccess($card)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to this comment'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $comment,
                'message' => 'Comment retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve comment',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }



    /**
     * ====================================================================================================
     * UPDATE - Update the specified comment
     * ====================================================================================================
     */
    public function update(Request $request, Comment $comment): JsonResponse
    {
        try {
            // Load relationships
            $comment->load(['user', 'card.board.project', 'subtask.card.board.project']);

            // Check authorization (only comment author or project owner can edit)
            $card = $comment->card ?: $comment->subtask->card;
            if (!$this->hasProjectAccess($card) || ($comment->user_id !== Auth::id() && $card->board->project->created_by !== Auth::id())) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to this comment'
                ], 403);
            }

            // Validate request data
            $validated = $request->validate([
                'content' => 'required|string|max:2000'
            ]);

            // Update comment with transaction
            DB::beginTransaction();
            
            $comment->update($validated);
            
            // Reload with fresh relationships
            $comment->load(['user', 'card.board.project', 'subtask.card.board.project']);
            
            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $comment,
                'message' => 'Comment updated successfully'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update comment',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }



    /**
     * ====================================================================================================
     * DESTROY - Remove the specified comment
     * ====================================================================================================
     */
    public function destroy(Comment $comment): JsonResponse
    {
        try {
            // Load relationships
            $comment->load(['user', 'card.board.project', 'subtask.card.board.project']);

            // Check authorization (only comment author or project owner can delete)
            $card = $comment->card ?: $comment->subtask->card;
            if (!$this->hasProjectAccess($card) || ($comment->user_id !== Auth::id() && $card->board->project->created_by !== Auth::id())) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to this comment'
                ], 403);
            }

            // Delete comment with transaction
            DB::beginTransaction();
            
            $comment->delete();
            
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Comment deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete comment',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }



    /**
     * ====================================================================================================
     * BY CARD - Get comments by card
     * ====================================================================================================
     * 
     * Special endpoint to get all comments for a specific card
     */
    public function byCard(Card $card): JsonResponse
    {
        try {
            // Load relationships
            $card->load(['board.project', 'board.project.members']);

            // Check authorization
            if (!$this->hasProjectAccess($card)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to this card'
                ], 403);
            }

            // Get comments for this card (both direct card comments and subtask comments)
            $comments = Comment::with(['user'])
                ->where(function ($query) use ($card) {
                    $query->where('card_id', $card->id)
                          ->orWhereHas('subtask', function ($subtaskQuery) use ($card) {
                              $subtaskQuery->where('card_id', $card->id);
                          });
                })
                ->orderBy('created_at', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $comments,
                'message' => 'Card comments retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve card comments',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }



    /**
     * ====================================================================================================
     * HELPER METHOD - Check if current user has access to the project
     * ====================================================================================================
     * 
     * Check if current user has access to the project containing this card
     * 
     * @param Card $card
     * @return bool
     */
    private function hasProjectAccess(Card $card): bool
    {
        $project = $card->board->project;
        
        // Check if user is creator
        if ($project->created_by === Auth::id()) {
            return true;
        }

        // Check if user is project member
        return $project->members()->where('user_id', Auth::id())->exists();
    }
}
