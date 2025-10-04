<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserDetailResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'title' => 'user data',
            'data' => UserResource::collection(User::all())
            // UserResource::collection(User::all())
        ]);
    }



    /**
     * Store a newly created resource in storage.
     */
    // public function store(Request $request)
    // {
        
    // }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user_detail = User::findOrFail($id);
        return new UserDetailResource($user_detail);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);
        $user->update($request->all());

        return response()->json([
            'message' => 'User updated'
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user=User::findOrFail($id);
        $user->delete();
        return response()->json([
            'message' => 'User deleted'
        ], 200);
    }
}
