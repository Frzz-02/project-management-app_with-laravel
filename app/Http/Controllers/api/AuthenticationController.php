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
    
    
    
    
    /**
     * ====================================
     * UPDATE PROFILE
     * ====================================
     * 
     * Method untuk update profile user yang sedang login.
     * 
     * Field yang bisa di-edit:
     * - username (unique)
     * - full_name
     * - password (optional)
     * - current_task_status (optional)
     * - role (optional)
     * 
     * Field yang TIDAK bisa di-edit:
     * - email (tidak bisa diubah)
     * 
     * Request Body:
     * {
     *   "username": "new_username",         // Optional
     *   "full_name": "New Full Name",       // Optional
     *   "password": "newpassword123",       // Optional (min 6 karakter)
     *   "current_task_status": "Working",   // Optional
     *   "role": "admin"                     // Optional
     * }
     * 
     * Response Format:
     * {
     *   "success": true,
     *   "message": "Profile berhasil diupdate",
     *   "data": {
     *     "id": 1,
     *     "username": "new_username",
     *     "full_name": "New Full Name",
     *     "email": "user@example.com",
     *     "role": "admin",
     *     "current_task_status": "Working"
     *   }
     * }
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(Request $request)
    {
        try {
            $user = $request->user();
            
            // Validasi input
            $rules = [
                'username' => 'sometimes|string|max:255|unique:users,username,' . $user->id,
                'full_name' => 'sometimes|string|max:255',
                'password' => 'sometimes|string|min:6',
                'current_task_status' => 'sometimes|string|max:255',
                'role' => 'sometimes|string|in:admin,user'
            ];
            
            $validatedData = $request->validate($rules, [
                'username.unique' => 'Username sudah digunakan oleh user lain',
                'username.max' => 'Username maksimal 255 karakter',
                'full_name.max' => 'Full name maksimal 255 karakter',
                'password.min' => 'Password minimal 6 karakter',
                'role.in' => 'Role harus admin atau user'
            ]);
            
            
            
            // Jika ada password, hash terlebih dahulu
            if (isset($validatedData['password'])) {
                $validatedData['password'] = Hash::make($validatedData['password']);
            }
            
            
            
            // Update user data
            $user->update($validatedData);
            
            
            
            // Load relasi user setelah update
            $user->load([
                'createdProjects',
                'projectMemberships.project',
                'createdCards.board',
                'comments.card',
                'timeLogs.card',
            ]);
            
            
            
            return response()->json([
                'success' => true,
                'message' => 'Profile berhasil diupdate',
                'data' => new UserDetailResource($user)
            ], 200);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate profile: ' . $e->getMessage()
            ], 500);
        }
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
