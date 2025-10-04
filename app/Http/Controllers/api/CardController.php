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
        return response()->json([
            'title' => 'card assignment project',
            'data' => CardResource::collection(\App\Models\Card::with('user')->get()),
            // 'message' => 'Card index'
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
            'status' => 'required|in:todo,in_progress,done',
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
