<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserDetailResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthenticationController extends Controller
{
    public function login(Request $request)
    { 
        $rules = [
            'email' => 'required|email',
            'password' => 'required'
        ];

        $validatedData = $request->validate($rules, [
            'email.required' => 'Email tolong diisi peler',
            'email.email' => 'Email is not valid',
            'password.required' => 'Password is required'
        ]);


        if (!Auth::attempt($validatedData)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user = $request->user();
        return response()->json([
            'token' => $user->createToken('auth_token')->plainTextToken,
        ], 200);
    }



    public function register(Request $request)
    {
        $rules = [
            'username' => 'required|string|max:255|unique:users,username',
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6'
        ];

        $validatedData = $request->validate($rules);
        $validatedData['password'] = Hash::make($validatedData['password']);
        $user = User::create($validatedData);

        return response()->json([
            'message' => 'User created',
            'data' => new UserDetailResource($user),
        ], 200);
    }
    
    
    
    

    public function logout(Request $request)
    { 
        // Revoke token login
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message' => 'Logged out'
        ], 200);
    }
}
