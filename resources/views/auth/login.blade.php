@extends('layouts.guest')

@section('title', 'Login')

@section('content')
{{-- 
    HALAMAN LOGIN MODERN & ESTETIK
    ==============================
    Halaman ini menggunakan desain split-screen dengan:
    - Sisi kiri: Ilustrasi/branding 
    - Sisi kanan: Form login
    - Animasi entrance yang smooth
    - Gradien background yang menarik
--}}

<!-- Container Utama - Membagi layar jadi 2 bagian -->
<div class="min-h-screen flex">
    
    {{-- SISI KIRI: BRANDING & ILUSTRASI --}}
    <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-blue-600 via-purple-600 to-indigo-700 relative overflow-hidden">
        {{-- 
            Background Gradient Explanation:
            - bg-gradient-to-br = gradient dari top-left ke bottom-right
            - from-blue-600 = mulai dari biru
            - via-purple-600 = lewat ungu di tengah  
            - to-indigo-700 = berakhir di indigo gelap
            - relative = positioning untuk elemen decorative
            - overflow-hidden = sembunyikan elemen yang keluar container
        --}}
        
        <!-- Elemen Dekoratif - Bulatan-bulatan cantik di background -->
        <div class="absolute inset-0">
            {{-- Bulatan besar di pojok kanan atas --}}
            <div class="absolute -top-20 -right-20 w-80 h-80 bg-white/10 rounded-full"></div>
            {{-- bg-white/10 = warna putih dengan opacity 10% (transparan) --}}
            
            {{-- Bulatan sedang di tengah kiri --}}
            <div class="absolute top-1/3 -left-16 w-64 h-64 bg-white/5 rounded-full"></div>
            
            {{-- Bulatan kecil di bawah kanan --}}
            <div class="absolute -bottom-10 right-20 w-40 h-40 bg-white/15 rounded-full"></div>
        </div>
        
        <!-- Konten Utama Sisi Kiri -->
        <div class="relative z-10 flex flex-col justify-center items-center text-white p-12">
            {{-- 
                relative z-10 = berada di atas elemen dekoratif
                flex flex-col = susun vertikal
                justify-center = rata tengah vertikal
                items-center = rata tengah horizontal  
                text-white = teks warna putih
                p-12 = padding 48px semua sisi
            --}}
            
            <!-- Logo/Brand -->
            <div class="text-center mb-8" 
                 x-data 
                 x-init="$el.style.opacity = '0'; $el.style.transform = 'translateY(30px)'; 
                        setTimeout(() => { 
                            $el.style.transition = 'all 0.8s ease-out'; 
                            $el.style.opacity = '1'; 
                            $el.style.transform = 'translateY(0)'; 
                        }, 200)">
                {{-- 
                    Animasi Entrance Explanation:
                    - Mulai dengan opacity 0 (transparan) dan posisi 30px ke bawah
                    - Setelah 200ms, perlahan muncul dan slide up ke posisi normal
                    - Durasi animasi 0.8 detik dengan easing smooth
                --}}
                
                <!-- Icon Brand -->
                <div class="w-20 h-20 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center mb-6 mx-auto">
                    {{-- 
                        backdrop-blur-sm = efek blur transparan
                        rounded-2xl = sudut membulat besar
                        mx-auto = margin horizontal auto (center)
                    --}}
                    <svg class="w-12 h-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                
                <h1 class="text-4xl font-bold mb-3"> TaskFlow</h1>
                {{-- text-4xl = ukuran text extra large --}}
                
                <p class="text-xl text-blue-100 font-light">
                    {{-- text-blue-100 = warna biru sangat terang --}}
                    {{-- font-light = ketebalan font tipis --}}
                    Manage your projects with style
                </p>
            </div>
            
            <!-- Features List -->
            <div class="space-y-4 max-w-md" 
                 x-data 
                 x-init="$el.style.opacity = '0'; $el.style.transform = 'translateX(-30px)'; 
                        setTimeout(() => { 
                            $el.style.transition = 'all 0.8s ease-out'; 
                            $el.style.opacity = '1'; 
                            $el.style.transform = 'translateX(0)'; 
                        }, 600)">
                {{-- 
                    space-y-4 = jarak vertikal 16px antar elemen
                    max-w-md = lebar maksimum medium
                    Animasi slide dari kiri setelah 600ms
                --}}
                
                <!-- Feature 1 -->
                <div class="flex items-center space-x-3">
                    {{-- flex items-center = horizontal alignment center --}}
                    {{-- space-x-3 = jarak horizontal 12px antar elemen --}}
                    
                    <div class="w-8 h-8 bg-green-400 rounded-full flex items-center justify-center flex-shrink-0">
                        {{-- 
                            flex-shrink-0 = tidak mengecil saat space terbatas
                            bg-green-400 = background hijau terang
                        --}}
                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <span class="text-lg">Real-time collaboration</span>
                </div>
                
                <!-- Feature 2 -->
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-blue-400 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <span class="text-lg">Advanced analytics</span>
                </div>
                
                <!-- Feature 3 -->
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-purple-400 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <span class="text-lg">Enterprise security</span>
                </div>
            </div>
            
            <!-- Bottom Quote -->
            <div class="mt-12 text-center" 
                 x-data 
                 x-init="$el.style.opacity = '0'; 
                        setTimeout(() => { 
                            $el.style.transition = 'opacity 1s ease-out'; 
                            $el.style.opacity = '1'; 
                        }, 1000)">
                {{-- Quote muncul paling terakhir setelah 1 detik --}}
                
                {{-- <blockquote class="text-lg text-blue-100 italic font-light"> --}}
                    {{-- 
                        blockquote = elemen HTML untuk kutipan
                        italic = teks miring
                        font-light = ketebalan font tipis
                    --}}
                    {{-- "The best project management tool we've ever used."
                </blockquote>
                <cite class="text-sm text-blue-200 mt-2 block"> --}}
                    {{-- 
                        cite = elemen HTML untuk sumber kutipan
                        text-blue-200 = warna biru lebih terang
                        block = tampilkan sebagai block element (new line)
                    --}}
                    {{-- — Sarah Johnson, Tech Lead at InnovateCorp
                </cite> --}}
            </div>
        </div>
    </div>
    
    {{-- SISI KANAN: FORM LOGIN --}}
    <div class="flex-1 flex flex-col justify-center py-12 px-4 sm:px-6 lg:px-20 xl:px-24">
        {{-- 
            flex-1 = ambil sisa ruang yang tersedia
            justify-center = rata tengah vertikal
            py-12 = padding vertikal 48px
            px-4 = padding horizontal 16px di mobile
            sm:px-6 = padding horizontal 24px di screen small
            lg:px-20 = padding horizontal 80px di screen large
            xl:px-24 = padding horizontal 96px di screen extra large
        --}}
        
        <div class="mx-auto w-full max-w-sm">
            {{-- 
                mx-auto = margin horizontal auto (center)
                max-w-sm = lebar maksimum small (384px)
            --}}
            
            <!-- Header Form -->
            <div class="text-center mb-8" 
                 x-data 
                 x-init="$el.style.opacity = '0'; $el.style.transform = 'translateY(20px)'; 
                        setTimeout(() => { 
                            $el.style.transition = 'all 0.6s ease-out'; 
                            $el.style.opacity = '1'; 
                            $el.style.transform = 'translateY(0)'; 
                        }, 300)">
                {{-- Header muncul dengan animasi slide up --}}
                
                <h2 class="text-3xl font-bold text-gray-900 mb-2">Welcome back!</h2>
                {{-- text-3xl = ukuran text large --}}
                {{-- font-bold = ketebalan font tebal --}}
                {{-- text-gray-900 = warna abu-abu sangat gelap (hampir hitam) --}}
                
                <p class="text-gray-600">
                    {{-- text-gray-600 = warna abu-abu medium --}}
                    Sign in to your account to continue
                </p>
            </div>
            
            <!-- Social Login Buttons -->
            <div class="space-y-3 mb-6" 
                 x-data 
                 x-init="$el.style.opacity = '0'; $el.style.transform = 'translateY(20px)'; 
                        setTimeout(() => { 
                            $el.style.transition = 'all 0.6s ease-out'; 
                            $el.style.opacity = '1'; 
                            $el.style.transform = 'translateY(0)'; 
                        }, 500)">
                {{-- Social buttons muncul setelah 500ms --}}
                
                <!-- Google Login -->
                <button type="button" class="w-full flex justify-center items-center px-4 py-3 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                    {{-- 
                        w-full = lebar penuh container
                        flex justify-center items-center = konten di tengah horizontal & vertikal
                        border-gray-300 = border abu-abu terang
                        rounded-lg = sudut membulat
                        shadow-sm = bayangan tipis
                        hover:bg-gray-50 = background abu-abu terang saat di-hover
                        focus:ring-2 = ring 2px saat di-focus
                        focus:ring-offset-2 = jarak ring dari elemen
                        focus:ring-blue-500 = warna ring biru
                        transition-colors = animasi perubahan warna
                        duration-200 = durasi animasi 200ms
                    --}}
                    
                    <svg class="w-5 h-5 mr-3" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    Continue with Google
                </button>
                
                <!-- GitHub Login -->
                <button type="button" class="w-full flex justify-center items-center px-4 py-3 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                    <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 0C4.477 0 0 4.484 0 10.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0110 4.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.203 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.942.359.31.678.921.678 1.856 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0020 10.017C20 4.484 15.522 0 10 0z" clip-rule="evenodd"/>
                    </svg>
                    Continue with GitHub
                </button>
            </div>
            
            <!-- Divider -->
            <div class="relative mb-6" 
                 x-data 
                 x-init="$el.style.opacity = '0'; 
                        setTimeout(() => { 
                            $el.style.transition = 'opacity 0.6s ease-out'; 
                            $el.style.opacity = '1'; 
                        }, 700)">
                {{-- Divider muncul setelah 700ms --}}
                
                <div class="absolute inset-0 flex items-center">
                    {{-- 
                        absolute inset-0 = posisi absolute menutupi seluruh parent
                        flex items-center = konten di tengah vertikal
                    --}}
                    <div class="w-full border-t border-gray-300"></div>
                    {{-- border-t = border hanya di atas --}}
                </div>
                <div class="relative flex justify-center text-sm">
                    {{-- relative = positioning relatif terhadap flow normal --}}
                    <span class="px-2 bg-white text-gray-500">Or continue with email</span>
                    {{-- px-2 = padding horizontal 8px --}}
                    {{-- bg-white = background putih (menutupi garis border) --}}
                </div>
            </div>
            
            
            
            
            
            
            
            
            
            
            
            
            <!-- Login Form -->
            <form method="POST" action="{{ route('login.attempt') }}" class="space-y-6" 
                  x-data 
                  x-init="$el.style.opacity = '0'; $el.style.transform = 'translateY(20px)'; 
                         setTimeout(() => { 
                             $el.style.transition = 'all 0.6s ease-out'; 
                             $el.style.opacity = '1'; 
                             $el.style.transform = 'translateY(0)'; 
                         }, 900)">
                {{-- Form muncul terakhir setelah 900ms --}}



                
                @csrf
                <!-- Error Message -->
                @error('errorLogin')
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">Error!</strong>
                    <span class="block sm:inline">{{ $message }}</span>
                    <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                        <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.03a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                    </span>
                </div>
            @enderror
                
                
                
                
                
                <!-- Email Field -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        {{-- 
                            block = tampilkan sebagai block element
                            text-sm = ukuran text small
                            font-medium = ketebalan font medium
                            mb-2 = margin bottom 8px
                        --}}
                        Email address
                    </label>
                    <input id="email" 
                           name="email" 
                           type="email" 
                           required 
                           value="{{ old('email') }}"
                           class="appearance-none relative block w-full px-4 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:z-10 transition-colors duration-200" 
                           placeholder="Enter your email">
                    {{-- 
                        appearance-none = hapus styling default browser
                        relative = positioning relatif
                        px-4 = padding horizontal 16px
                        py-3 = padding vertikal 12px
                        placeholder-gray-500 = warna placeholder abu-abu medium
                        focus:outline-none = hapus outline default saat focus
                        focus:z-10 = z-index 10 saat focus (di atas elemen lain)
                    --}}
                </div>
                
                <!-- Password Field -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Password
                    </label>
                    <div class="relative">
                        {{-- Container untuk input + icon toggle password --}}
                        <input id="password" 
                               name="password" 
                               type="password" 
                               required 
                               value="{{ old('password') }}"
                               class="appearance-none relative block w-full px-4 py-3 pr-12 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:z-10 transition-colors duration-200" 
                               placeholder="Enter your password">
                        {{-- pr-12 = padding right 48px (untuk kasih ruang icon) --}}
                        
                        <!-- Toggle Password Visibility -->
                        <button type="button" 
                                class="absolute inset-y-0 right-0 pr-4 flex items-center"
                                @click="$refs.passwordInput.type = $refs.passwordInput.type === 'password' ? 'text' : 'password'">
                            {{-- 
                                absolute inset-y-0 right-0 = posisi absolute di sisi kanan
                                pr-4 = padding right 16px
                                @click = event handler Alpine.js untuk klik
                                Toggle antara type 'password' dan 'text'
                            --}}
                            <svg class="h-5 w-5 text-gray-400 hover:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember_me" 
                               name="remember_me" 
                               type="checkbox" 
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        {{-- 
                            h-4 w-4 = ukuran 16x16px
                            text-blue-600 = warna centang biru
                            focus:ring-blue-500 = ring biru saat focus
                            rounded = sudut sedikit membulat
                        --}}
                        <label for="remember_me" class="ml-2 block text-sm text-gray-900">
                            Remember me
                        </label>
                    </div>
                    
                    <div class="text-sm">
                        <a href="{{ route('dashboard') }}" class="font-medium text-blue-600 hover:text-blue-500 transition-colors duration-200">
                            {{-- 
                                font-medium = ketebalan font medium
                                text-blue-600 = warna biru
                                hover:text-blue-500 = warna biru lebih terang saat hover
                            --}}
                            Forgot your password?
                        </a>
                    </div>
                </div>
                
                <!-- Submit Button -->
                <div>
                    <button type="submit" 
                            class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transform hover:scale-105 transition-all duration-200 shadow-lg">
                        {{-- 
                            group = untuk styling grup (parent-child interaction)
                            border-transparent = border transparan
                            bg-gradient-to-r = gradient horizontal
                            from-blue-600 to-purple-600 = gradient biru ke ungu
                            hover:from-blue-700 = warna mulai lebih gelap saat hover
                            transform hover:scale-105 = sedikit membesar saat hover (105%)
                            shadow-lg = bayangan besar
                        --}}
                        
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            {{-- Icon di dalam button --}}
                            <svg class="h-5 w-5 text-white group-hover:text-blue-100 transition-colors duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                {{-- group-hover:text-blue-100 = warna berubah saat parent di-hover --}}
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </span>
                        Sign in to your account
                    </button>
                </div>
            </form>
            
            <!-- Sign Up Link -->
            <div class="mt-6 text-center" 
                 x-data 
                 x-init="$el.style.opacity = '0'; 
                        setTimeout(() => { 
                            $el.style.transition = 'opacity 0.6s ease-out'; 
                            $el.style.opacity = '1'; 
                        }, 1100)">
                {{-- Sign up link muncul paling terakhir setelah 1.1 detik --}}
                
                <p class="text-sm text-gray-600">
                    Don't have an account? 
                    <a href="{{ route('register') }}" class="font-medium text-blue-600 hover:text-blue-500 transition-colors duration-200">
                        Sign up for free
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection