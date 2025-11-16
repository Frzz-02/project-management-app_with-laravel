@extends('layouts.guest')

@section('title', 'Register')

@section('content')
{{-- 
    HALAMAN REGISTER GLASSMORPHISM & 3D ISOMETRIC
    =============================================
    Desain ini menggunakan:
    - Background gradient cyan ke purple
    - Glassmorphism card effect (transparan dengan blur)
    - Ilustrasi 3D isometric di sisi kanan
    - Animasi entrance yang bertahap
    - Form styling yang modern
--}}

<!-- Success Modal - Tidak Dapat Ditutup -->
@if(session('success'))
<div id="successModal" 
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm"
     x-data="{ show: false }"
     x-init="setTimeout(() => show = true, 100)"
     x-show="show"
     x-transition:enter="transition ease-out duration-500"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100">
    
    <!-- Modal Content -->
    <div class="relative bg-white/15 backdrop-blur-xl border border-white/30 rounded-3xl p-8 mx-4 max-w-md w-full shadow-2xl"
         x-show="show"
         x-transition:enter="transition ease-out duration-700 delay-200"
         x-transition:enter-start="opacity-0 scale-90 translate-y-8"
         x-transition:enter-end="opacity-1 scale-100 translate-y-0">
        
        <!-- Success Icon dengan Animasi -->
        <div class="text-center mb-6">
            <div class="relative inline-flex items-center justify-center w-20 h-20 mx-auto mb-4">
                <!-- Background Circle dengan Pulse Effect -->
                <div class="absolute inset-0 bg-gradient-to-r from-green-400 to-emerald-500 rounded-full animate-pulse opacity-20"></div>
                <div class="absolute inset-2 bg-gradient-to-r from-green-400 to-emerald-500 rounded-full animate-ping opacity-30"></div>
                
                <!-- Main Circle -->
                <div class="relative w-16 h-16 bg-gradient-to-r from-green-400 to-emerald-500 rounded-full flex items-center justify-center shadow-lg">
                    <!-- Checkmark Icon -->
                    <svg class="w-8 h-8 text-white animate-bounce" 
                         style="animation-delay: 0.5s; animation-duration: 1s; animation-iteration-count: 3;"
                         fill="none" 
                         stroke="currentColor" 
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" 
                              stroke-linejoin="round" 
                              stroke-width="3" 
                              d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                
                <!-- Floating Particles -->
                <div class="absolute -top-2 -right-2 w-3 h-3 bg-yellow-400 rounded-full animate-bounce opacity-80" style="animation-delay: 0.8s;"></div>
                <div class="absolute -bottom-1 -left-2 w-2 h-2 bg-blue-400 rounded-full animate-bounce opacity-70" style="animation-delay: 1.2s;"></div>
                <div class="absolute top-1 -right-4 w-1.5 h-1.5 bg-purple-400 rounded-full animate-bounce opacity-60" style="animation-delay: 1.5s;"></div>
            </div>
        </div>
        
        <!-- Success Message -->
        <div class="text-center mb-8">
            <h3 class="text-2xl font-bold text-white mb-3">
                Registrasi Berhasil! 🎉
            </h3>
            <p class="text-white/90 text-base leading-relaxed">
                {{ session('success') }}
            </p>
        </div>
        
        <!-- Decorative Elements -->
        <div class="absolute top-4 left-4 w-8 h-8 border-2 border-white/30 rounded-full animate-spin opacity-50" style="animation-duration: 8s;"></div>
        <div class="absolute bottom-4 right-4 w-6 h-6 border-2 border-white/20 rounded-full animate-spin opacity-40" style="animation-duration: 6s; animation-direction: reverse;"></div>
        
        <!-- Progress Bar -->
        <div class="mb-8">
            <div class="bg-white/20 rounded-full h-2 overflow-hidden">
                <div class="h-full bg-gradient-to-r from-green-400 to-emerald-500 rounded-full animate-pulse"
                     x-data="{ width: 0 }"
                     x-init="setTimeout(() => { 
                         $el.style.transition = 'width 3s ease-out'; 
                         width = 100; 
                         $el.style.width = width + '%'; 
                     }, 800)">
                </div>
            </div>
            <p class="text-white/70 text-xs text-center mt-2">Mengalihkan ke halaman login...</p>
        </div>
        
        <!-- Action Button -->
        <div class="text-center">
            <button onclick="window.location.href='{{ route('login') }}'"
                    class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-semibold rounded-2xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-green-300/50">
                
                <!-- Button Icon -->
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                </svg>
                
                Lanjut ke Login
            </button>
        </div>
        
        <!-- Auto Redirect Script -->
        <script>
            // Auto redirect setelah 5 detik
            setTimeout(function() {
                window.location.href = '{{ route('login') }}';
            }, 5000);
        </script>
    </div>
</div>
@endif

<!-- Container Utama dengan Background Gradient -->
<div class="min-h-screen bg-gradient-to-br from-cyan-400 via-blue-500 to-purple-600 relative overflow-hidden flex items-center justify-center p-4">
    {{-- 
        Background Gradient Explanation:
        - bg-gradient-to-br = gradient diagonal dari top-left ke bottom-right
        - from-cyan-400 = mulai dari cyan terang
        - via-blue-500 = lewat biru di tengah
        - to-purple-600 = berakhir di ungu
        - relative = untuk positioning elemen dekoratif
        - overflow-hidden = sembunyikan elemen yang keluar
        - flex items-center justify-center = center semua konten
        - p-4 = padding 16px untuk mobile
    --}}
    
    <!-- Elemen Dekoratif Background -->
    <div class="absolute inset-0 overflow-hidden">
        {{-- Bulatan gradient besar di kiri atas --}}
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-gradient-to-r from-white/20 to-transparent rounded-full blur-3xl"></div>
        {{-- 
            blur-3xl = blur effect sangat besar untuk soft glow
            from-white/20 to-transparent = gradient dari putih transparan ke transparan total
        --}}
        
        {{-- Bulatan medium di kanan bawah --}}
        <div class="absolute -bottom-32 -right-32 w-80 h-80 bg-gradient-to-l from-purple-300/30 to-transparent rounded-full blur-2xl"></div>
        
        {{-- Bulatan kecil di tengah kiri --}}
        <div class="absolute top-1/3 -left-20 w-60 h-60 bg-gradient-to-r from-cyan-300/25 to-transparent rounded-full blur-xl"></div>
    </div>
    
    <!-- Main Content Container -->
    <div class="relative z-10 w-full max-w-6xl mx-auto">
        {{-- 
            relative z-10 = berada di atas elemen dekoratif
            max-w-6xl = lebar maksimum extra large (1152px)
            mx-auto = center horizontal
        --}}
        
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            {{-- 
                grid lg:grid-cols-2 = grid 2 kolom di layar large
                gap-12 = jarak 48px antar kolom
                items-center = align vertical center
            --}}
            
            {{-- KOLOM KIRI: GLASSMORPHISM CARD REGISTER --}}
            <div class="order-2 lg:order-1" 
                 x-data 
                 x-init="$el.style.opacity = '0'; $el.style.transform = 'translateX(-50px)'; 
                        setTimeout(() => { 
                            $el.style.transition = 'all 1s ease-out'; 
                            $el.style.opacity = '1'; 
                            $el.style.transform = 'translateX(0)'; 
                        }, 300)">
                {{-- 
                    order-2 lg:order-1 = di mobile card muncul kedua, di desktop pertama
                    Animasi slide dari kiri setelah 300ms
                --}}
                
                <!-- Glassmorphism Card -->
                <div class="bg-white/20 backdrop-blur-xl border border-white/30 rounded-3xl p-8 shadow-2xl">
                    {{-- 
                        bg-white/20 = background putih 20% opacity (transparan)
                        backdrop-blur-xl = blur background di belakang card (glassmorphism effect)
                        border-white/30 = border putih 30% opacity
                        rounded-3xl = sudut membulat extra besar
                        shadow-2xl = bayangan besar untuk depth
                    --}}
                    
                    <!-- Header Card -->
                    <div class="text-center mb-8" 
                         x-data 
                         x-init="$el.style.opacity = '0'; $el.style.transform = 'translateY(30px)'; 
                                setTimeout(() => { 
                                    $el.style.transition = 'all 0.8s ease-out'; 
                                    $el.style.opacity = '1'; 
                                    $el.style.transform = 'translateY(0)'; 
                                }, 600)">
                        {{-- Header muncul dengan slide up setelah 600ms --}}
                        
                        <h1 class="text-4xl font-bold text-white mb-3">Register</h1>
                        {{-- text-4xl = ukuran text extra large --}}
                        {{-- font-bold = ketebalan font tebal --}}
                        {{-- text-white = teks warna putih --}}
                        
                        <p class="text-white/80 text-lg">
                            {{-- text-white/80 = putih dengan 80% opacity (sedikit transparan) --}}
                            Silahkan isi data diri anda di bawah ini
                        </p>
                    </div>

                    
                    
                    
                    
                    
                    
                    
                    <!-- Register Form -->
                    <form method="POST" action="{{ route('register.store') }}" class="space-y-6" 
                          x-data 
                          x-init="$el.style.opacity = '0'; $el.style.transform = 'translateY(30px)'; 
                                 setTimeout(() => { 
                                     $el.style.transition = 'all 0.8s ease-out'; 
                                     $el.style.opacity = '1'; 
                                     $el.style.transform = 'translateY(0)'; 
                                 }, 900)">
                        {{-- space-y-6 = jarak vertikal 24px antar elemen form --}}
                        {{-- Form muncul setelah 900ms --}}

                        @csrf
                        {{-- Token CSRF untuk keamanan form --}}


                        @if($errors->any())
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                                <strong class="font-bold">Error!</strong>
                                <span class="block sm:inline">{{ $errors->first() }}</span>
                                <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                                    <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.03a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                                </span>
                            </div>
                        @endif

                        <!-- Username Field -->
                        <div class="relative">
                            <input type="text" 
                                   name="username" 
                                   required 
                                   placeholder="Username"
                                   class="w-full px-6 py-4 bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-white/40 transition-all duration-300">
                            <p class="mt-2 text-xs text-white/70 px-2">
                                <span class="inline-flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                    Username harus unik, maksimal 255 karakter
                                </span>
                            </p>
                        </div>
                        
                        
                        
                        
                        <!-- Full Name Field -->
                        <div class="relative">
                            {{-- Container untuk input dengan styling khusus --}}
                            <input type="text" 
                                   name="full_name" 
                                   required 
                                   placeholder="Full Name"
                                   class="w-full px-6 py-4 bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-white/40 transition-all duration-300">
                            {{-- 
                                w-full = lebar penuh
                                px-6 py-4 = padding horizontal 24px, vertikal 16px
                                bg-white/10 = background putih 10% opacity
                                backdrop-blur-sm = blur kecil untuk glassmorphism
                                border-white/20 = border putih 20% opacity
                                rounded-2xl = sudut membulat besar
                                placeholder-white/60 = placeholder putih 60% opacity
                                focus:ring-2 = ring 2px saat focus
                                focus:ring-white/50 = ring putih 50% opacity
                                transition-all duration-300 = animasi smooth 300ms
                            --}}
                            <p class="mt-2 text-xs text-white/70 px-2">
                                <span class="inline-flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                    Nama lengkap maksimal 255 karakter
                                </span>
                            </p>
                        </div>

                        
                        
                        
                        <!-- Email Field -->
                        <div class="relative">
                            <input type="email" 
                                   name="email" 
                                   required 
                                   placeholder="Email Address"
                                   class="w-full px-6 py-4 bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-white/40 transition-all duration-300">
                            <p class="mt-2 text-xs text-white/70 px-2">
                                <span class="inline-flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                    Email harus valid dan unik, maksimal 255 karakter
                                </span>
                            </p>
                        </div>
                        
                        
                        
                        
                        
                        <!-- Password Field -->
                        <div class="relative">
                            <input type="password" 
                                   name="password" 
                                   required 
                                   placeholder="Password"
                                   class="w-full px-6 py-4 bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-white/40 transition-all duration-300">
                            <div class="mt-2 text-xs text-white/70 px-2 space-y-1">
                                <p class="inline-flex items-center">
                                    <svg class="w-3 h-3 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>Minimal 10 karakter, maksimal 255 karakter</span>
                                </p>
                                <p class="text-white/60 pl-4">
                                    Harus mengandung: huruf besar, huruf kecil, angka, dan simbol
                                </p>
                            </div>
                        </div>
                        
                        
                        
                        
                      
                        
                        <!-- Register Button -->
                        <button type="submit" 
                                class="w-full py-4 px-6 bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-600 hover:to-blue-700 text-white font-semibold rounded-2xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-white/50">
                            {{-- 
                                bg-gradient-to-r = gradient horizontal
                                from-cyan-500 to-blue-600 = gradient cyan ke biru
                                hover:from-cyan-600 hover:to-blue-700 = gradient lebih gelap saat hover
                                font-semibold = ketebalan font semi-bold
                                shadow-lg = bayangan besar
                                hover:shadow-xl = bayangan extra besar saat hover
                                transform hover:scale-105 = membesar 105% saat hover
                            --}}
                            Register
                        </button>
                    </form>

                    
                    
                    
                    
                    
                    
                    
                    <!-- Login Link -->
                    <div class="mt-6 text-center" 
                         x-data 
                         x-init="$el.style.opacity = '0'; 
                                setTimeout(() => { 
                                    $el.style.transition = 'opacity 0.8s ease-out'; 
                                    $el.style.opacity = '1'; 
                                }, 1200)">
                        {{-- Login link muncul terakhir setelah 1.2 detik --}}
                        
                        <p class="text-white/80">
                            Sudah punya akun? 
                            <a href="{{ route('login') }}" class="text-white font-medium hover:text-cyan-200 transition-colors duration-200 underline decoration-white/50 hover:decoration-cyan-200">
                                {{-- 
                                    font-medium = ketebalan font medium
                                    hover:text-cyan-200 = warna cyan terang saat hover
                                    underline = garis bawah
                                    decoration-white/50 = warna underline putih 50% opacity
                                    hover:decoration-cyan-200 = underline cyan saat hover
                                --}}
                                Masuk disini
                            </a>
                        </p>
                    </div>
                </div>
            </div>
            
            {{-- KOLOM KANAN: WELCOME MESSAGE & SIMPLE ANIMATION --}}
            <div class="order-1 lg:order-2 text-center lg:text-left flex flex-col justify-center" 
                 x-data 
                 x-init="$el.style.opacity = '0'; $el.style.transform = 'translateX(50px)'; 
                        setTimeout(() => { 
                            $el.style.transition = 'all 1s ease-out'; 
                            $el.style.opacity = '1'; 
                            $el.style.transform = 'translateX(0)'; 
                        }, 500)">
                {{-- 
                    Menambahkan flex flex-col justify-center untuk center vertikal
                    Agar tinggi konten sejajar dengan form di sebelah kiri
                --}}
                
                <!-- Welcome Message -->
                <div class="mb-12" 
                     x-data 
                     x-init="$el.style.opacity = '0'; $el.style.transform = 'translateY(-30px)'; 
                            setTimeout(() => { 
                                $el.style.transition = 'all 0.8s ease-out'; 
                                $el.style.opacity = '1'; 
                                $el.style.transform = 'translateY(0)'; 
                            }, 800)">
                    
                    <h2 class="text-4xl lg:text-5xl font-bold text-white mb-6 leading-tight">
                        {{-- Mengurangi ukuran dari text-6xl ke text-5xl agar tidak terlalu besar --}}
                        Selamat Datang 👋
                    </h2>
                    
                    <p class="text-lg lg:text-xl text-white/90 font-light leading-relaxed">
                        {{-- Mengurangi ukuran dari text-2xl ke text-xl --}}
                        Bergabunglah dengan ribuan pengguna yang sudah merasakan kemudahan mengelola proyek dengan 
                        <span class="text-cyan-200 font-semibold">TaskFlow</span>
                    </p>
                </div>
                
                <!-- Simple Animated Illustration -->
                <div class="relative flex justify-center lg:justify-start mb-12" 
                     x-data 
                     x-init="$el.style.opacity = '0'; $el.style.transform = 'scale(0.9)'; 
                            setTimeout(() => { 
                                $el.style.transition = 'all 1s ease-out'; 
                                $el.style.opacity = '1'; 
                                $el.style.transform = 'scale(1)'; 
                            }, 1000)">
                    
                    <!-- Simple Dashboard Mockup -->
                    <div class="relative w-72 h-48">
                        
                        <!-- Main Dashboard Card -->
                        <div class="absolute inset-0 bg-white/15 backdrop-blur-sm rounded-2xl border border-white/20 p-6 shadow-xl">
                            
                            <!-- Header Bar -->
                            <div class="flex items-center justify-between mb-6">
                                <div class="flex items-center space-x-2">
                                    <div class="w-3 h-3 bg-red-400 rounded-full animate-pulse"></div>
                                    <div class="w-3 h-3 bg-yellow-400 rounded-full animate-pulse" style="animation-delay: 0.2s;"></div>
                                    <div class="w-3 h-3 bg-green-400 rounded-full animate-pulse" style="animation-delay: 0.4s;"></div>
                                </div>
                                <div class="text-white/60 text-xs font-medium">TaskFlow Dashboard</div>
                            </div>
                            
                            <!-- Stats Cards -->
                            <div class="grid grid-cols-3 gap-3 mb-4">
                                <div class="bg-gradient-to-br from-blue-400/30 to-blue-600/30 rounded-lg p-3 backdrop-blur-sm">
                                    <div class="w-full h-1 bg-blue-300/50 rounded mb-2"></div>
                                    <div class="w-3/4 h-1 bg-blue-300/30 rounded"></div>
                                </div>
                                <div class="bg-gradient-to-br from-purple-400/30 to-purple-600/30 rounded-lg p-3 backdrop-blur-sm">
                                    <div class="w-full h-1 bg-purple-300/50 rounded mb-2"></div>
                                    <div class="w-2/3 h-1 bg-purple-300/30 rounded"></div>
                                </div>
                                <div class="bg-gradient-to-br from-cyan-400/30 to-cyan-600/30 rounded-lg p-3 backdrop-blur-sm">
                                    <div class="w-full h-1 bg-cyan-300/50 rounded mb-2"></div>
                                    <div class="w-4/5 h-1 bg-cyan-300/30 rounded"></div>
                                </div>
                            </div>
                            
                            <!-- Progress Bars -->
                            <div class="space-y-2">
                                <!-- Progress 1 -->
                                <div class="bg-white/10 rounded-full h-1.5 overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-green-400 to-emerald-500 rounded-full animate-pulse" 
                                         style="width: 75%; animation-duration: 2s;"></div>
                                </div>
                                
                                <!-- Progress 2 -->
                                <div class="bg-white/10 rounded-full h-1.5 overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-blue-400 to-cyan-500 rounded-full animate-pulse" 
                                         style="width: 60%; animation-duration: 2.5s; animation-delay: 0.5s;"></div>
                                </div>
                                
                                <!-- Progress 3 -->
                                <div class="bg-white/10 rounded-full h-1.5 overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-purple-400 to-pink-500 rounded-full animate-pulse" 
                                         style="width: 45%; animation-duration: 3s; animation-delay: 1s;"></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Floating Elements -->
                        <div class="absolute -top-4 -right-4 w-12 h-12 bg-gradient-to-r from-cyan-400 to-blue-500 rounded-full flex items-center justify-center shadow-lg animate-bounce">
                            <span class="text-white text-lg">📊</span>
                        </div>
                        
                        <div class="absolute -bottom-4 -left-4 w-10 h-10 bg-gradient-to-r from-purple-400 to-pink-500 rounded-full flex items-center justify-center shadow-lg animate-bounce" style="animation-delay: 1s;">
                            <span class="text-white text-sm">✨</span>
                        </div>
                        
                        <div class="absolute top-1/2 -right-6 w-8 h-8 bg-gradient-to-r from-green-400 to-emerald-500 rounded-full flex items-center justify-center shadow-lg animate-bounce" style="animation-delay: 0.5s;">
                            <span class="text-white text-xs">🚀</span>
                        </div>
                    </div>
                </div>
                
                <!-- Features Highlight - Compact Version -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4" 
                     x-data 
                     x-init="$el.style.opacity = '0'; $el.style.transform = 'translateY(20px)'; 
                            setTimeout(() => { 
                                $el.style.transition = 'all 0.8s ease-out'; 
                                $el.style.opacity = '1'; 
                                $el.style.transform = 'translateY(0)'; 
                            }, 1400)">
                    
                    <!-- Feature 1: Easy Setup -->
                    <div class="text-center">
                        <div class="w-10 h-10 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center mx-auto mb-2 border border-white/30">
                            <span class="text-lg text-white">✓</span>
                        </div>
                        <h4 class="text-white font-medium mb-1 text-sm">Easy Setup</h4>
                        <p class="text-white/70 text-xs">Get started in minutes</p>
                    </div>
                    
                    <!-- Feature 2: Fast & Secure -->
                    <div class="text-center">
                        <div class="w-10 h-10 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center mx-auto mb-2 border border-white/30">
                            <span class="text-lg text-white">⚡</span>
                        </div>
                        <h4 class="text-white font-medium mb-1 text-sm">Fast & Secure</h4>
                        <p class="text-white/70 text-xs">Enterprise-grade security</p>
                    </div>
                    
                    <!-- Feature 3: Team Ready -->
                    <div class="text-center">
                        <div class="w-10 h-10 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center mx-auto mb-2 border border-white/30">
                            <span class="text-lg text-white">👥</span>
                        </div>
                        <h4 class="text-white font-medium mb-1 text-sm">Team Ready</h4>
                        <p class="text-white/70 text-xs">Collaborate seamlessly</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection