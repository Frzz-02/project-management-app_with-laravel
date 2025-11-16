<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Profile Controller
 * 
 * Controller untuk mengelola profile user.
 * User bisa update:
 * - Full Name
 * - Profile Picture (image upload)
 * - Phone
 * - Bio
 * - Password (optional)
 * 
 * Field yang TIDAK bisa diubah:
 * - Username (unique identifier)
 * - Email (authentication)
 * - Role (authorization)
 * - is_admin (security)
 * 
 * @author System
 * @package App\Http\Controllers\web
 */
class ProfileController extends Controller
{
    /**
     * Show profile edit form
     * 
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        $user = Auth::user();
        
        return view('profile.edit', compact('user'));
    }

    /**
     * Update user profile
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        // Validasi input
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'bio' => ['nullable', 'string', 'max:500'],
            'profile_picture' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'], // Max 2MB
            'current_password' => ['nullable', 'required_with:new_password', 'string'],
            'new_password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ], [
            'full_name.required' => 'Nama lengkap wajib diisi.',
            'full_name.max' => 'Nama lengkap maksimal 255 karakter.',
            'phone.max' => 'Nomor telepon maksimal 20 karakter.',
            'bio.max' => 'Bio maksimal 500 karakter.',
            'profile_picture.image' => 'File harus berupa gambar.',
            'profile_picture.mimes' => 'Format gambar harus: jpeg, png, jpg, atau gif.',
            'profile_picture.max' => 'Ukuran gambar maksimal 2MB.',
            'current_password.required_with' => 'Password lama wajib diisi jika ingin mengubah password.',
            'new_password.min' => 'Password baru minimal 8 karakter.',
            'new_password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        try {
            // Update full name
            $user->full_name = $validated['full_name'];
            
            // Update phone (optional)
            if ($request->has('phone')) {
                $user->phone = $validated['phone'];
            }
            
            // Update bio (optional)
            if ($request->has('bio')) {
                $user->bio = $validated['bio'];
            }

            // Handle profile picture upload
            if ($request->hasFile('profile_picture')) {
                // Delete old profile picture if exists
                if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                    Storage::disk('public')->delete($user->profile_picture);
                }

                // Store new profile picture
                $path = $request->file('profile_picture')->store('profile_pictures', 'public');
                $user->profile_picture = $path;
            }

            // Handle password change (optional)
            if ($request->filled('new_password')) {
                // Verify current password
                if (!Hash::check($request->current_password, $user->password)) {
                    return back()->withErrors([
                        'current_password' => 'Password lama tidak sesuai.'
                    ])->withInput();
                }

                // Update password
                $user->password = Hash::make($validated['new_password']);
            }

            // Save changes
            $user->save();

            return back()->with('success', 'Profile berhasil diperbarui! ✅');

        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Gagal memperbarui profile: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Delete profile picture
     * 
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteProfilePicture()
    {
        $user = Auth::user();

        try {
            // Delete profile picture file
            if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                Storage::disk('public')->delete($user->profile_picture);
            }

            // Remove from database
            $user->profile_picture = null;
            $user->save();

            return back()->with('success', 'Foto profile berhasil dihapus.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal menghapus foto profile: ' . $e->getMessage()]);
        }
    }
}
