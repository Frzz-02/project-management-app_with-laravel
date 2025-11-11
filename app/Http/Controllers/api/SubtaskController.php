<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\Subtask;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * SubtaskController API
 * 
 * API Controller untuk mengelola subtasks yang digunakan dalam todolist functionality.
 * 
 * Endpoints:
 * - GET /api/subtasks - List all subtasks (with filtering)
 * - POST /api/subtasks - Create new subtask
 * - GET /api/subtasks/{id} - Get specific subtask
 * - PUT /api/subtasks/{id} - Update subtask
 * - DELETE /api/subtasks/{id} - Delete subtask  
 * - PATCH /api/subtasks/{id}/toggle - Toggle subtask completion status
 * 
 * Features:
 * - Authorization checking (user must have access to parent card)
 * - Validation of input data
 * - Optimistic UI support with proper error handling
 * - Real-time updates compatible with Alpine.js frontend
 */
class SubtaskController extends Controller
{
    /**
     * Display a listing of subtasks
     * 
     * Query parameters:
     * - card_id: Filter by card ID
     * - is_completed: Filter by completion status (true/false)
     * - limit: Limit results (default: 50)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Subtask::query()
                ->with(['card.board.project', 'card.board.project.members']);

            // Filter by card ID
            if ($request->has('card_id')) {
                $query->where('card_id', $request->card_id);
            }

            // Filter by completion status
            if ($request->has('is_completed')) {
                $query->where('is_completed', $request->boolean('is_completed'));
            }

            // Apply authorization filter
            $query->whereHas('card.board.project', function ($q) {
                $q->where('created_by', Auth::id())
                  ->orWhereHas('members', function ($memberQuery) {
                      $memberQuery->where('user_id', Auth::id());
                  });
            });

            // Limit results
            $limit = min($request->get('limit', 50), 100);
            $subtasks = $query->limit($limit)->get();

            return response()->json([
                'success' => true,
                'data' => $subtasks,
                'message' => 'Subtasks retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve subtasks',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Store a newly created subtask
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Validate request data
            $validated = $request->validate([
                'card_id' => 'required|exists:cards,id',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'is_completed' => 'boolean'
            ]);

            // Get the card and check authorization
            $card = Card::with(['board.project', 'board.project.members'])->findOrFail($validated['card_id']);
            
            // Check if user has access to this card's project
            if (!$this->hasProjectAccess($card)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to this card'
                ], 403);
            }

            // Create subtask with transaction
            DB::beginTransaction();
            
            $subtask = Subtask::create([
                'card_id' => $validated['card_id'],
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'is_completed' => $validated['is_completed'] ?? false,
                'created_by' => Auth::id()
            ]);

            // Load relationships for response
            $subtask->load(['card.board.project']);
            
            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $subtask,
                'message' => 'Subtask created successfully'
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
                'message' => 'Failed to create subtask',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Display the specified subtask
     */
    public function show(Subtask $subtask): JsonResponse
    {
        try {
            // Load relationships
            $subtask->load(['card.board.project', 'card.board.project.members']);

            // Check authorization
            if (!$this->hasProjectAccess($subtask->card)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to this subtask'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $subtask,
                'message' => 'Subtask retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve subtask',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Update the specified subtask
     */
    public function update(Request $request, Subtask $subtask): JsonResponse
    {
        try {
            // Load card with relationships
            $subtask->load(['card.board.project', 'card.board.project.members']);

            // Check authorization
            if (!$this->hasProjectAccess($subtask->card)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to this subtask'
                ], 403);
            }

            // Validate request data
            $validated = $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string|max:1000', 
                'is_completed' => 'sometimes|boolean'
            ]);

            // Update subtask with transaction
            DB::beginTransaction();
            
            $subtask->update($validated);
            
            // Reload with fresh relationships
            $subtask->load(['card.board.project']);
            
            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $subtask,
                'message' => 'Subtask updated successfully'
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
                'message' => 'Failed to update subtask',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Remove the specified subtask
     */
    public function destroy(Subtask $subtask): JsonResponse
    {
        try {
            // Load card with relationships
            $subtask->load(['card.board.project', 'card.board.project.members']);

            // Check authorization
            if (!$this->hasProjectAccess($subtask->card)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to this subtask'
                ], 403);
            }

            // Delete subtask with transaction
            DB::beginTransaction();
            
            $subtask->delete();
            
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Subtask deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete subtask',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Toggle subtask completion status
     * 
     * Special endpoint for quick toggling of completion status,
     * optimized for todolist UI interactions
     */
    public function toggle(Request $request, Subtask $subtask): JsonResponse
    {
        try {
            // Load card with relationships
            $subtask->load(['card.board.project', 'card.board.project.members']);

            // Check authorization
            if (!$this->hasProjectAccess($subtask->card)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to this subtask'
                ], 403);
            }

            // Toggle completion status with transaction
            DB::beginTransaction();
            
            $subtask->update([
                'is_completed' => !$subtask->is_completed
            ]);
            
            // Reload with fresh relationships
            $subtask->load(['card.board.project']);
            
            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $subtask,
                'message' => $subtask->is_completed ? 'Subtask marked as completed' : 'Subtask marked as incomplete'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle subtask status',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
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