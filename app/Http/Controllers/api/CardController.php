<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CardResource;
use Illuminate\Http\Request;

class CardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Ambil user ID yang sedang login dari token authentication
    $userId = auth()->user()->id;


    // Query cards dengan 2 kondisi:
    // 1. Cards yang dibuat oleh user (created_by)
    // 2. Cards yang ditugaskan ke user (via card_assignments relationship)
    $cards = \App\Models\Card::with([
            'creator',           // Eager load creator untuk menghindari N+1
            'board.project',     // Eager load board dan project
            'assignments.user',  // Eager load assignments dengan user data
            'subtasks',          // Eager load subtasks
            'comments'           // Eager load comments
        ])
        ->where(function($query) use ($userId) {
            // Cards yang dibuat oleh user
            $query->whereHas('assignments', function($q) use ($userId) {
                $q->where('user_id', $userId);
            });
                  // ATAU cards yang ditugaskan ke user via card_assignments
                //   ->orWhereHas('assignments', function($q) use ($userId) {
                //       $q->where('user_id', $userId);
                //   });
        })
        ->orderBy('created_at', 'desc')
        ->get();


    return response()->json([
        'title' => 'My Cards',
        // 'data' => CardResource::collection($cards),
        'data' => $cards,
        'meta' => [
            'total' => $cards->count(),
            'user_id' => $userId
        ]
    ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            'board_id' => 'required|exists:boards,id',
            'position' => 'required|integer',
            'priority' => 'required|in:low,medium,high',
            'card_title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'created_by' => 'required|exists:users,id',
            'due_date' => 'nullable|date',
            'estimated_hours' => 'nullable|integer',
        ];

        $validatedData = $request->validate($rules);
        $card = \App\Models\Card::create($validatedData);

        return response()->json([
            'message' => 'Card created',
            'data' => new CardResource($card),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
