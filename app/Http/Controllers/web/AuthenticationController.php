<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

// use Illuminate\Http\Request;

class AuthenticationController extends Controller
{
    public function login(Request $request)
    { 
        $rules = [
            'email' => 'required',
            'password' => 'required'
        ];

        $validatedData = $request->validate($rules, [
            'email.required' => 'Email atau username tolong diisi',
            'password.required' => 'Password is required'
        ]);

        // Support login with email or username
        $loginField = filter_var($validatedData['email'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $credentials = [
            $loginField => $validatedData['email'],
            'password' => $validatedData['password']
        ];

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate(); 
            return redirect()->intended('/dashboard'); 
        }

        return back()->withErrors([
            'errorLogin' => 'Login gagal, Email atau password salah!'
        ]);
    }



    public function register(Request $request)
    {
        // dd($request->all());
        $validateData = $request->validate([
            'full_name' => 'required|max:255',
            'username' => 'unique:users|max:255',
            'email' => 'required|email:dns|unique:users|max:255',
            'password' => 'required|min:10|max:255|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z0-9]).+$/',
            // maksud dari regex disitu : 
                // ^(?=.*[a-z])         // ada huruf kecil
                // (?=.*[A-Z])          // ada huruf besar
                // (?=.*\d)             // ada angka
                // (?=.*[^a-zA-Z0-9])   // ada simbol
                // .+$                  // minimal 1 karakter (tapi sudah dibatasi min:8 juga)
        ]);

        $validateData['password'] = Hash::make($validateData['password']);

        User::create($validateData);
        return back()->with('success', 'Registrasi berhasil, silahkan untuk login terlebih dahulu !');
    }
}
