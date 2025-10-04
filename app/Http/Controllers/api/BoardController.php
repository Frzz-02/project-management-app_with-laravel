<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Http\Requests\StoreBoardRequest;
use App\Http\Requests\UpdateBoardRequest;
use App\Http\Resources\BoardResource;

class BoardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $boards = Board::all();
        // return response()->json([
        //     'title' => 'List of Boards',
        //     'data' => $boards
        // ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBoardRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $Board = Board::with('cards:id,board_id,card_title,description,position,status,due_date')->find($id);
        
        if (!$Board) {
            return response()->json([
                'message' => 'Board not found'
            ], 400);
        }
        
        return response()->json([
            'title' => 'Detail of Board',
            'data' => new BoardResource($Board)
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Board $board)
    {
        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBoardRequest $request, Board $board)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Board $board)
    {
        //
    }
}
