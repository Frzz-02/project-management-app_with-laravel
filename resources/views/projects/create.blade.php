@extends('layouts.app')

@section('title', 'Create New Project')

@section('content')
{{-- 
    HALAMAN CREATE PROJECT - MODERN & AESTHETIC
    ==========================================
    Styling yang digunakan:
    - Glassmorphism card dengan backdrop blur effect
    - Gradient background dengan floating elements dekoratif
    - Form sections dengan visual hierarchy yang jelas
    - Interactive hover effects dan smooth focus states
    - Color-coded sections untuk UX yang lebih baik
    - Responsive grid layout untuk semua device
    - Smooth animations dan transitions pada semua elemen
    - Modern button styling dengan gradient dan shadows
--}}

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 py-8 px-4">
    {{-- 
        Background gradient yang soft dan professional:
        - from-slate-50: mulai dari abu-abu super terang
        - via-blue-50: lewat biru super terang di tengah
        - to-indigo-100: berakhir di indigo terang
        - py-8: padding vertikal 32px
        - px-4: padding horizontal 16px untuk mobile
    --}}
    
    <!-- Floating Background Decorative Elements -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        {{-- 
            pointer-events-none: elemen dekoratif tidak mengganggu interaksi user
            fixed inset-0: menutupi seluruh viewport
            overflow-hidden: sembunyikan bagian yang keluar
        --}}
        <div class="absolute top-20 left-10 w-32 h-32 bg-blue-200/20 rounded-full blur-xl animate-pulse"></div>
        <div class="absolute top-40 right-20 w-48 h-48 bg-indigo-200/15 rounded-full blur-2xl animate-pulse" style="animation-delay: 1s;"></div>
        <div class="absolute bottom-20 left-20 w-40 h-40 bg-purple-200/20 rounded-full blur-xl animate-pulse" style="animation-delay: 2s;"></div>
        <div class="absolute bottom-40 right-10 w-24 h-24 bg-cyan-200/25 rounded-full blur-lg animate-pulse" style="animation-delay: 0.5s;"></div>
        {{-- 
            Elemen dekoratif dengan:
            - bg-color/opacity: warna dengan transparansi untuk subtle effect
            - blur-xl/2xl/lg: berbagai tingkat blur untuk depth
            - animate-pulse: animasi berkedip halus
            - animation-delay: timing berbeda untuk organic feeling
        --}}
    </div>
    
    <!-- Main Container -->
    <div class="relative z-10 max-w-4xl mx-auto">
        {{-- 
            relative z-10: berada di atas elemen dekoratif
            max-w-4xl: lebar maksimum 896px untuk readability optimal
            mx-auto: center horizontal
        --}}
        
        <!-- Header Section dengan Animasi -->
        <div class="mb-8 text-center" 
             x-data 
             x-init="$el.style.opacity = '0'; $el.style.transform = 'translateY(-20px)'; 
                    setTimeout(() => { 
                        $el.style.transition = 'all 0.6s ease-out'; 
                        $el.style.opacity = '1'; 
                        $el.style.transform = 'translateY(0)'; 
                    }, 100)">
            {{-- Alpine.js animasi slide down untuk header --}}
            
            <h1 class="text-4xl lg:text-5xl font-bold bg-gradient-to-r from-gray-900 via-blue-800 to-indigo-900 bg-clip-text text-transparent mb-4">
                {{-- 
                    Gradient text effect:
                    - bg-gradient-to-r: gradient horizontal
                    - from-gray-900 via-blue-800 to-indigo-900: gradient gelap ke biru
                    - bg-clip-text: clip gradient ke text
                    - text-transparent: buat text transparan agar gradient terlihat
                --}}
                Create New Project
            </h1>
            
            <p class="text-lg text-gray-600 mb-6 max-w-2xl mx-auto">
                {{-- max-w-2xl mx-auto: batasi lebar dan center untuk readability --}}
                Start building something amazing with your team. Define your project goals and bring your vision to life.
            </p>
            
            <!-- Animated Breadcrumb -->
            <nav class="flex justify-center">
                <ol class="flex items-center space-x-2 text-sm text-gray-500 bg-white/30 backdrop-blur-sm rounded-full px-4 py-2 border border-white/40">
                    {{-- 
                        Glassmorphism breadcrumb:
                        - bg-white/30: background putih 30% opacity
                        - backdrop-blur-sm: blur background di belakang
                        - rounded-full: sudut bulat penuh
                        - border-white/40: border putih transparan
                    --}}
                    <li><a href="{{ route('dashboard') }}" class="hover:text-blue-600 transition-colors duration-200">Dashboard</a></li>
                    <li><span class="mx-2 text-gray-400">→</span></li>
                    <li><a href="{{ route('projects.index') }}" class="hover:text-blue-600 transition-colors duration-200">Projects</a></li>
                    <li><span class="mx-2 text-gray-400">→</span></li>
                    <li class="text-blue-600 font-medium">Create</li>
                </ol>
            </nav>
        </div>
        
        <!-- Main Glassmorphism Form Card -->
        <div class="bg-white/40 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/30 p-8 lg:p-12" 
             x-data 
             x-init="$el.style.opacity = '0'; $el.style.transform = 'translateY(30px)'; 
                    setTimeout(() => { 
                        $el.style.transition = 'all 0.8s cubic-bezier(0.4, 0, 0.2, 1)'; 
                        $el.style.opacity = '1'; 
                        $el.style.transform = 'translateY(0)'; 
                    }, 300)">
            {{-- 
                Glassmorphism card premium:
                - bg-white/40: background putih 40% opacity
                - backdrop-blur-xl: blur ekstrem untuk glass effect
                - rounded-3xl: sudut melengkung besar (24px)
                - shadow-2xl: bayangan besar untuk depth
                - border-white/30: border putih transparan
                - cubic-bezier: easing curve yang smooth
            --}}
            
            <form method="POST" action="{{ route('projects.store') }}" class="space-y-8">
                @csrf
                {{-- space-y-8: jarak vertikal 32px antar section --}}
                
                <!-- Project Information Section -->
                <div class="bg-gradient-to-r from-blue-50/50 to-indigo-50/50 rounded-2xl p-6 border border-blue-100/50 backdrop-blur-sm">
                    {{-- 
                        Section dengan background gradient subtle:
                        - from-blue-50/50 to-indigo-50/50: gradient biru dengan opacity 50%
                        - rounded-2xl: sudut melengkung sedang
                        - border-blue-100/50: border biru dengan opacity
                        - backdrop-blur-sm: blur tambahan untuk layering
                    --}}
                    
                    <!-- Section Header dengan Icon -->
                    <div class="flex items-center mb-6">
                        <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center mr-4 shadow-lg">
                            {{-- 
                                Icon container dengan gradient dan shadow:
                                - w-12 h-12: ukuran 48x48px
                                - rounded-xl: sudut melengkung besar
                                - shadow-lg: bayangan untuk depth
                            --}}
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-gray-800">Project Details</h3>
                            <p class="text-sm text-gray-600">Basic information about your project</p>
                        </div>
                    </div>
                    
                    <!-- Form Fields Grid -->
                    <div class="grid md:grid-cols-2 gap-6">
                        {{-- 
                            Responsive grid:
                            - md:grid-cols-2: 2 kolom di medium screen ke atas
                            - gap-6: jarak 24px antar grid items
                        --}}
                        
                        <!-- Project Name Field -->
                        <div class="md:col-span-2">
                            {{-- md:col-span-2: span 2 kolom untuk field yang penting --}}
                            
                            <label for="project_name" class="block text-sm font-semibold text-gray-700 mb-3">
                                <span class="flex items-center">
                                    Project Name 
                                    <span class="text-red-500 ml-1">*</span>
                                    <svg class="w-4 h-4 text-gray-400 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </span>
                            </label>
                            
                            <input type="text" 
                                   id="project_name" 
                                   name="project_name" 
                                   value="{{ old('project_name') }}"
                                   required
                                   class="w-full px-5 py-4 bg-white/60 backdrop-blur-sm border border-gray-200/60 rounded-xl text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-300 hover:bg-white/80 hover:border-gray-300/60 shadow-sm"
                                   placeholder="Enter your awesome project name">
                            {{-- 
                                Premium input styling:
                                - bg-white/60: background putih 60% opacity
                                - backdrop-blur-sm: blur untuk glassmorphism
                                - border-gray-200/60: border abu dengan opacity
                                - rounded-xl: sudut melengkung besar
                                - focus:ring-2: ring 2px saat focus
                                - focus:ring-blue-500/50: ring biru dengan opacity
                                - transition-all duration-300: animasi smooth 300ms
                                - hover:bg-white/80: background lebih solid saat hover
                                - shadow-sm: bayangan tipis
                            --}}
                            
                            @error('project_name')
                            <div class="mt-2 flex items-center text-sm text-red-600 bg-red-50/50 rounded-lg p-2 border border-red-200/50">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ $message }}
                            </div>
                            {{-- Error styling dengan icon dan background --}}
                            @enderror
                        </div>
                        
                        <!-- Team Lead Field -->
                        <div>
                            <label for="teamlead_id" class="block text-sm font-semibold text-gray-700 mb-3">
                                <span class="flex items-center">
                                    Team Lead 
                                    <span class="text-red-500 ml-1">*</span>
                                    <svg class="w-4 h-4 text-gray-400 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </span>
                            </label>
                            
                            <select id="teamlead_id" 
                                    name="teamlead_id" 
                                    required
                                    class="w-full px-5 py-4 bg-white/60 backdrop-blur-sm border border-gray-200/60 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-300 hover:bg-white/80 hover:border-gray-300/60 shadow-sm">
                                <option value="">Choose Team Lead</option>
                                {{-- Nanti akan diisi dengan data dari controller --}}
                                @if(isset($users))
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('teamlead_id') == $user->id ? 'selected' : '' }}>
                                            {{-- {{ $user->id }} --}}
                                            {{ $user->full_name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            
                            @error('teamlead_id')
                            <div class="mt-2 flex items-center text-sm text-red-600 bg-red-50/50 rounded-lg p-2 border border-red-200/50">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                        
                        <!-- Deadline Field -->
                        <div>
                            <label for="deadline" class="block text-sm font-semibold text-gray-700 mb-3">
                                <span class="flex items-center">
                                    Project Deadline 
                                    <span class="text-red-500 ml-1">*</span>
                                    <svg class="w-4 h-4 text-gray-400 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </span>
                            </label>
                            
                            <input type="date" 
                                   id="deadline" 
                                   name="deadline" 
                                   value="{{ old('deadline') }}"
                                   required
                                   min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                   class="w-full px-5 py-4 bg-white/60 backdrop-blur-sm border border-gray-200/60 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-300 hover:bg-white/80 hover:border-gray-300/60 shadow-sm">
                            {{-- min: set minimum date ke besok untuk mencegah tanggal masa lalu --}}
                            
                            @error('deadline')
                            <div class="mt-2 flex items-center text-sm text-red-600 bg-red-50/50 rounded-lg p-2 border border-red-200/50">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <!-- Project Description Section -->
                <div class="bg-gradient-to-r from-purple-50/50 to-pink-50/50 rounded-2xl p-6 border border-purple-100/50 backdrop-blur-sm">
                    {{-- Section dengan gradient purple untuk visual variety --}}
                    
                    <!-- Section Header dengan Icon -->
                    <div class="flex items-center mb-6">
                        <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-pink-600 rounded-xl flex items-center justify-center mr-4 shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-gray-800">Project Description</h3>
                            <p class="text-sm text-gray-600">Describe your project goals and objectives</p>
                        </div>
                    </div>
                    
                    <div>
                        <label for="description" class="block text-sm font-semibold text-gray-700 mb-3">
                            <span class="flex items-center">
                                Description
                                <span class="text-gray-400 ml-2 text-xs">(Optional)</span>
                                <svg class="w-4 h-4 text-gray-400 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </span>
                        </label>
                        
                        <textarea id="description" 
                                  name="description" 
                                  rows="6"
                                  class="w-full px-5 py-4 bg-white/60 backdrop-blur-sm border border-gray-200/60 rounded-xl text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-purple-500/50 focus:border-purple-500/50 transition-all duration-300 hover:bg-white/80 hover:border-gray-300/60 resize-none shadow-sm"
                                  placeholder="Describe your project goals, key features, target audience, and success criteria. What problem does this project solve?">{{ old('description') }}</textarea>
                        {{-- 
                            Textarea premium styling:
                            - rows="6": tinggi 6 baris
                            - resize-none: tidak bisa di-resize manual untuk konsistensi
                            - focus:ring-purple-500/50: ring purple untuk section ini
                        --}}
                        
                        @error('description')
                        <div class="mt-2 flex items-center text-sm text-red-600 bg-red-50/50 rounded-lg p-2 border border-red-200/50">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ $message }}
                        </div>
                        @enderror
                        
                        <!-- Character Counter -->
                        <div class="mt-3 flex justify-between items-center text-sm">
                            <span class="text-gray-500">💡 Tip: A good description helps team members understand the project vision</span>
                            <span class="text-gray-400" id="char-counter">0 characters</span>
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons Section -->
                <div class="flex flex-col sm:flex-row gap-4 pt-8 border-t border-gray-200/50">
                    {{-- 
                        Button section dengan border separator:
                        - flex-col sm:flex-row: vertikal di mobile, horizontal di small+
                        - gap-4: jarak 16px antar button
                        - pt-8: padding top untuk spacing
                        - border-t border-gray-200/50: garis atas transparan
                    --}}
                    
                    <!-- Cancel Button -->
                    <button type="button" 
                            onclick="window.history.back()" 
                            class="flex-1 px-6 py-4 bg-gray-100/80 hover:bg-gray-200/80 text-gray-700 font-semibold rounded-xl transition-all duration-300 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-gray-500/50 focus:ring-offset-2 shadow-sm hover:shadow-md backdrop-blur-sm border border-gray-200/50">
                        {{-- 
                            Cancel button dengan subtle styling:
                            - flex-1: ambil space yang sama dengan button lain
                            - bg-gray-100/80: background abu dengan opacity
                            - hover:scale-105: sedikit membesar saat hover (5%)
                            - focus:ring-offset-2: jarak ring dari button
                            - hover:shadow-md: bayangan lebih besar saat hover
                        --}}
                        <span class="flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Cancel
                        </span>
                    </button>
                    
                    <!-- Save as Draft Button (Optional Future Feature) -->
                    <button type="button" 
                            class="flex-1 px-6 py-4 bg-yellow-100/80 hover:bg-yellow-200/80 text-yellow-800 font-semibold rounded-xl transition-all duration-300 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-yellow-500/50 focus:ring-offset-2 shadow-sm hover:shadow-md backdrop-blur-sm border border-yellow-200/50">
                        <span class="flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                            </svg>
                            Save Draft
                        </span>
                    </button>
                    
                    <!-- Create Project Button -->
                    <button type="submit"
                            class="flex-1 px-6 py-4 bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:ring-offset-2 backdrop-blur-sm">
                        {{-- 
                            Primary button dengan gradient premium:
                            - bg-gradient-to-r: gradient horizontal
                            - from-blue-600 to-indigo-700: gradient biru ke indigo
                            - hover:from-blue-700: gradient lebih gelap saat hover
                            - shadow-lg hover:shadow-xl: bayangan dinamis
                            - transform hover:scale-105: scale effect
                        --}}
                        <span class="flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2 group-hover:rotate-12 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Create Project
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- JavaScript untuk Character Counter dan Enhancements --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Character counter untuk textarea
    const textarea = document.getElementById('description');
    const counter = document.getElementById('char-counter');
    
    if (textarea && counter) {
        textarea.addEventListener('input', function() {
            const count = this.value.length;
            counter.textContent = count + ' characters';
            
            // Change color based on length
            if (count > 500) {
                counter.classList.add('text-yellow-500');
            } else if (count > 800) {
                counter.classList.add('text-red-500');
            } else {
                counter.classList.remove('text-yellow-500', 'text-red-500');
            }
        });
    }
    
    // Auto-focus pada field pertama
    const firstInput = document.getElementById('project_name');
    if (firstInput) {
        setTimeout(() => firstInput.focus(), 500);
    }
});
</script>
@endsection
