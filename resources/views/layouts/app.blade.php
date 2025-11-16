<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Project Management - @yield('title', 'Dashboard')</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/taskflow_logo.png') }}">
    
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    
    
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <!-- Alpine.js x-cloak style -->
    <style>
        [x-cloak] { display: none !important; }
    </style>
    
    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 font-inter" x-data="appData()" x-init="init()">

    <!-- Sidebar untuk Desktop -->
    <div class="hidden lg:fixed lg:inset-y-0 lg:flex lg:w-64 lg:flex-col">
        <div class="flex min-h-0 flex-1 flex-col bg-white border-r border-gray-200">
            <!-- Logo -->
            <div class="flex h-16 flex-shrink-0 items-center px-6 border-b border-gray-200">
                <div class="flex items-center">
                    <div class="h-8 w-8 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg flex items-center justify-center">
                        <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <h1 class="ml-3 text-xl font-bold text-gray-900">TaskFlow</h1>
                </div>
            </div>
            
            <!-- Navigation Menu -->
            <nav class="flex-1 px-4 pb-4 space-y-1 mt-6">
                {{-- Dashboard - Untuk Member (Developer/Designer/Team Lead) dan Unassigned --}}
                @php
                    $isMember = false;
                    $isUnassigned = false;
                    $isNewlyAssigned = false; // Baru direkrut dalam 7 hari terakhir
                    $currentRoute = request()->route()->getName();
                    
                    if(Auth::check()) {
                        // Check if user has any project assignment
                        $hasAssignment = DB::table('project_members')
                            ->where('user_id', Auth::id())
                            ->exists();
                        
                        if ($hasAssignment) {
                            // User is assigned to at least one project
                            $isMember = DB::table('project_members')
                                ->where('user_id', Auth::id())
                                ->whereIn('role', ['developer', 'designer', 'team lead'])
                                ->exists();
                            $isGeneralMember = DB::table('project_members')
                                ->where('user_id', Auth::id())
                                ->whereIn('role', ['developer', 'designer'])
                                ->exists();
                            $isTeamLead = DB::table('project_members')
                                ->where('user_id', Auth::id())
                                ->whereIn('role', ['team lead'])
                                ->exists();
                            
                            // Check jika baru direkrut dalam 7 hari terakhir
                            $latestAssignment = DB::table('project_members')
                                ->where('user_id', Auth::id())
                                ->orderBy('joined_at', 'desc')
                                ->first();
                            
                            if ($latestAssignment && $latestAssignment->joined_at) {
                                $joinedDate = \Carbon\Carbon::parse($latestAssignment->joined_at);
                                $isNewlyAssigned = $joinedDate->diffInDays(now()) <= 7;
                            }
                        } else {
                            // User has no project assignments
                            $isUnassigned = true;
                        }
                    }
                @endphp

                @if($isMember)
                    {{-- Info Badge untuk Member Baru --}}
                    @if($isNewlyAssigned)
                        <div class="mb-4 bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-lg p-3">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-xs font-medium text-green-800">Selamat! 🎉</p>
                                    <p class="text-xs text-green-700 mt-1">Anda telah ditambahkan ke project</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Dashboard Member --}}
                    <a href="{{ $isTeamLead ? route('team-leader.dashboard') : route('member.dashboard') }}" 
                       class="{{ str_starts_with($currentRoute, 'member.dashboard') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors">
                        <svg class="{{ str_starts_with($currentRoute, 'member.dashboard') ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500' }} mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Dashboard Saya
                        @if($isNewlyAssigned)
                            <span class="ml-auto inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                NEW
                            </span>
                        @endif
                    </a>
                    
                    {{-- My Projects --}}
                    <a href="{{ route('projects.index') }}" 
                       class="{{ str_starts_with($currentRoute, 'projects.index') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors">
                        <svg class="{{ str_starts_with($currentRoute, 'projects.index') ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500' }} mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        Proyek Saya
                        @if($isNewlyAssigned)
                            <span class="ml-auto inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                NEW
                            </span>
                        @endif
                    </a>
                    
                    {{-- Review History --}}
                    <a href="{{ route('card-reviews.my-reviews') }}" 
                       class="{{ str_starts_with($currentRoute, 'card-reviews.my-reviews') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors">
                        <svg class="{{ str_starts_with($currentRoute, 'card-reviews.my-reviews') ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500' }} mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Riwayat Review
                    </a>

                    {{-- Profile --}}
                    <a href="{{ route('profile.edit') }}" 
                       class="{{ str_starts_with($currentRoute, 'profile.') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors">
                        <svg class="{{ str_starts_with($currentRoute, 'profile.') ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500' }} mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Profil Saya
                    </a>
                @elseif($isUnassigned)
                    {{-- Dashboard untuk Unassigned Member --}}
                    <a href="{{ route('unassigned.dashboard') }}" 
                       class="{{ str_starts_with($currentRoute, 'unassigned.dashboard') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors">
                        <svg class="{{ str_starts_with($currentRoute, 'unassigned.dashboard') ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500' }} mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Dashboard
                    </a>

                    {{-- Profile --}}
                    <a href="{{ route('profile.edit') }}" 
                       class="{{ str_starts_with($currentRoute, 'profile.') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors">
                        <svg class="{{ str_starts_with($currentRoute, 'profile.') ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500' }} mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Profil Saya
                    </a>
                @endif
            </nav>
            
            <!-- User Profile -->
            <div class="flex-shrink-0 border-t border-gray-200 p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center min-w-0 flex-1">
                        <div class="h-10 w-10 bg-gradient-to-r from-purple-400 to-pink-400 rounded-full flex items-center justify-center flex-shrink-0">
                            <span class="text-white font-medium text-sm">
                                {{ strtoupper(substr(Auth::user()->full_name ?? 'U', 0, 2)) }}
                            </span>
                        </div>
                        <div class="ml-3 min-w-0 flex-1">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ Auth::user()->full_name ?? 'User' }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
                        </div>
                    </div>
                    
                    <!-- Logout Button -->
                    <form method="POST" action="{{ route('logout') }}" class="ml-2">
                        @csrf
                        <button type="submit" 
                                class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors group"
                                title="Logout">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Sidebar -->
    <div class="lg:hidden">
        <!-- Overlay Background -->
        <div x-show="sidebarOpen" 
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false"
             class="fixed inset-0 z-40 bg-gray-900/50 backdrop-blur-sm"
             style="display: none;">
        </div>

        <!-- Sidebar Panel -->
        <div x-show="sidebarOpen"
             x-transition:enter="transition ease-in-out duration-300 transform"
             x-transition:enter-start="-translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in-out duration-300 transform"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="-translate-x-full"
             class="fixed inset-y-0 left-0 z-50 w-72 bg-white shadow-2xl"
             style="display: none;">
            
            <div class="flex h-full flex-col">
                <!-- Logo -->
                <div class="flex h-16 flex-shrink-0 items-center justify-between px-6 border-b border-gray-200">
                    <div class="flex items-center">
                        <div class="h-8 w-8 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg flex items-center justify-center">
                            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <h1 class="ml-3 text-xl font-bold text-gray-900">TaskFlow</h1>
                    </div>
                    <!-- Close Button -->
                    <button @click="sidebarOpen = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Navigation Menu -->
                <nav class="flex-1 px-4 pb-4 space-y-1 mt-6 overflow-y-auto">
                    {{-- Dashboard - Untuk Member (Developer/Designer/Team Lead) dan Unassigned - Mobile --}}
                    @php
                        $isMobileMember = false;
                        $isMobileUnassigned = false;
                        $isMobileNewlyAssigned = false; // Baru direkrut dalam 7 hari terakhir
                        $currentMobileRoute = request()->route()->getName();
                        
                        if(Auth::check()) {
                            // Check if user has any project assignment
                            $hasMobileAssignment = DB::table('project_members')
                                ->where('user_id', Auth::id())
                                ->exists();
                            
                            if ($hasMobileAssignment) {
                                // User is assigned to at least one project
                                $isMobileMember = DB::table('project_members')
                                    ->where('user_id', Auth::id())
                                    ->whereIn('role', ['developer', 'designer', 'team lead'])
                                    ->exists();
                                
                                // Check jika baru direkrut dalam 7 hari terakhir
                                $latestMobileAssignment = DB::table('project_members')
                                    ->where('user_id', Auth::id())
                                    ->orderBy('joined_at', 'desc')
                                    ->first();
                                
                                if ($latestMobileAssignment && $latestMobileAssignment->joined_at) {
                                    $joinedDate = \Carbon\Carbon::parse($latestMobileAssignment->joined_at);
                                    $isMobileNewlyAssigned = $joinedDate->diffInDays(now()) <= 7;
                                }
                            } else {
                                // User has no project assignments
                                $isMobileUnassigned = true;
                            }
                        }
                    @endphp

                    @if($isMobileMember)
                        {{-- Info Badge untuk Member Baru - Mobile --}}
                        @if($isMobileNewlyAssigned)
                            <div class="mb-4 bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-lg p-3">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-xs font-medium text-green-800">Selamat! 🎉</p>
                                        <p class="text-xs text-green-700 mt-1">Anda telah ditambahkan ke project</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Dashboard Member --}}
                        <a href="{{ route('member.dashboard') }}" 
                           class="{{ str_starts_with($currentMobileRoute, 'member.dashboard') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors">
                            <svg class="{{ str_starts_with($currentMobileRoute, 'member.dashboard') ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500' }} mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            Dashboard Saya
                            @if($isMobileNewlyAssigned)
                                <span class="ml-auto inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                    NEW
                                </span>
                            @endif
                        </a>
                        
                        {{-- My Projects --}}
                        <a href="{{ route('projects.my-active-project') }}" 
                           class="{{ str_starts_with($currentMobileRoute, 'projects.show') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors">
                            <svg class="{{ str_starts_with($currentMobileRoute, 'projects.show') ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500' }} mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            Proyek Saya
                            @if($isMobileNewlyAssigned)
                                <span class="ml-auto inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                    NEW
                                </span>
                            @endif
                        </a>
                        
                        {{-- Review History --}}
                        <a href="{{ route('card-reviews.my-reviews') }}" 
                           class="{{ str_starts_with($currentMobileRoute, 'card-reviews.my-reviews') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors">
                            <svg class="{{ str_starts_with($currentMobileRoute, 'card-reviews.my-reviews') ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500' }} mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Riwayat Review
                        </a>

                        {{-- Profile --}}
                        <a href="{{ route('profile.edit') }}" 
                           class="{{ str_starts_with($currentMobileRoute, 'profile.') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors">
                            <svg class="{{ str_starts_with($currentMobileRoute, 'profile.') ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500' }} mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Profil Saya
                        </a>
                    @elseif($isMobileUnassigned)
                        {{-- Dashboard untuk Unassigned Member --}}
                        <a href="{{ route('unassigned.dashboard') }}" 
                           class="{{ str_starts_with($currentMobileRoute, 'unassigned.dashboard') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors">
                            <svg class="{{ str_starts_with($currentMobileRoute, 'unassigned.dashboard') ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500' }} mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            Dashboard
                        </a>

                        {{-- Profile --}}
                        <a href="{{ route('profile.edit') }}" 
                           class="{{ str_starts_with($currentMobileRoute, 'profile.') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors">
                            <svg class="{{ str_starts_with($currentMobileRoute, 'profile.') ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500' }} mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Profil Saya
                        </a>
                    @endif
                </nav>

                <!-- User Profile -->
                <div class="flex-shrink-0 border-t border-gray-200 p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center min-w-0 flex-1">
                            <div class="h-10 w-10 bg-gradient-to-r from-purple-400 to-pink-400 rounded-full flex items-center justify-center flex-shrink-0">
                                <span class="text-white font-medium text-sm">
                                    {{ strtoupper(substr(Auth::user()->full_name ?? 'U', 0, 2)) }}
                                </span>
                            </div>
                            <div class="ml-3 min-w-0 flex-1">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ Auth::user()->full_name ?? 'User' }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
                            </div>
                        </div>
                        
                        <!-- Logout Button -->
                        <form method="POST" action="{{ route('logout') }}" class="ml-2">
                            @csrf
                            <button type="submit" 
                                    class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors group"
                                    title="Logout">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="lg:pl-64">
        <!-- Top Header -->
        <div class="sticky top-0 z-40 bg-white border-b border-gray-200 pl-1 pr-4 sm:pl-3 sm:pr-6 lg:pr-8">
            <div class="flex h-16 items-center justify-between">
                <!-- Mobile menu button -->
                <button @click="sidebarOpen = true" 
                        class="lg:hidden -ml-0.5 -mt-0.5 inline-flex h-12 w-12 items-center justify-center rounded-md text-gray-500 hover:text-gray-900 focus:outline-none">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <!-- Search Bar -->
                <div class="flex flex-1 justify-center px-2 lg:ml-6 lg:justify-start">
                    <div class="w-full max-w-lg lg:max-w-xs">
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input class="block w-full rounded-lg border-gray-300 bg-gray-50 pl-10 pr-3 py-2 text-sm placeholder-gray-500 focus:border-blue-500 focus:bg-white focus:ring-blue-500" 
                                   placeholder="Search projects, tasks..." type="search">
                        </div>
                    </div>
                </div>

                <!-- Right side buttons -->
                <div class="flex items-center space-x-4">
                    <!-- Quick Add Button -->
                    {{-- <button class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Create
                    </button> --}}
                    
                    <!-- Notifications -->
                    <div x-data="{ open: false }" class="relative z-50">
                        <button @click="open = !open; notificationDropdownOpen = !notificationDropdownOpen" 
                                class="relative p-2 text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-lg">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-3.393-3.768a8.5 8.5 0 01-2.607-5.732V8a3 3 0 00-6 0v-.5c0 2.09-.753 4.034-2.01 5.732L5 17h5m5 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            <span x-show="unreadCount > 0" 
                                  x-text="unreadCount"
                                  class="absolute -top-1 -right-1 h-5 w-5 rounded-full bg-red-500 flex items-center justify-center text-xs text-white font-medium"></span>
                        </button>
                        
                        <!-- Notification Dropdown -->
                        <div x-show="open"
                             x-cloak
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             @click.away="open = false"
                             class="absolute right-0 z-[60] mt-2 w-80 origin-top-right rounded-lg bg-white shadow-xl border border-gray-200 focus:outline-none"
                             style="display: none;">
                            <div class="p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="text-sm font-semibold text-gray-900">Notifications</h3>
                                    <button @click="open = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                                <div class="space-y-2 max-h-96 overflow-y-auto">
                                    <!-- Empty state -->
                                    <template x-if="notifications.length === 0">
                                        <div class="text-center py-8">
                                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-3.393-3.768a8.5 8.5 0 01-2.607-5.732V8a3 3 0 00-6 0v-.5c0 2.09-.753 4.034-2.01 5.732L5 17h5m5 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                            </svg>
                                            <p class="mt-2 text-sm text-gray-500">No notifications yet</p>
                                        </div>
                                    </template>
                                    
                                    <!-- Notification list -->
                                    <template x-for="notification in notifications" :key="notification.id">
                                        <div @click="handleNotificationClick(notification)" 
                                             class="flex items-start space-x-3 p-3 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors border border-transparent hover:border-gray-200"
                                             :class="{ 'bg-blue-50/50': !notification.is_read }">
                                            <div class="flex-shrink-0">
                                                <div class="h-8 w-8 bg-gradient-to-br rounded-full flex items-center justify-center text-lg"
                                                     :class="notification.color_class">
                                                    <span x-text="notification.icon"></span>
                                                </div>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900" 
                                                   :class="{ 'font-semibold': !notification.is_read }"
                                                   x-text="notification.title"></p>
                                                <p class="text-sm text-gray-500 line-clamp-2 mt-0.5" x-text="notification.message"></p>
                                                <p class="text-xs text-gray-400 mt-1" x-text="notification.time_ago"></p>
                                            </div>
                                            <div x-show="!notification.is_read" class="flex-shrink-0">
                                                <span class="inline-block w-2 h-2 bg-blue-600 rounded-full"></span>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                                <div class="mt-3 pt-3 border-t border-gray-200">
                                    <a href="/notifications" 
                                       class="block w-full text-center text-sm font-medium text-blue-600 hover:text-blue-700 transition-colors py-2">
                                        View all notifications
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page Content -->
        <main class="flex-1">
            @yield('content')
        </main>
    </div>
    
    <!-- Toast Notifications Component -->
    <x-toast-notifications />

    <!-- Additional Scripts -->
    @stack('scripts')
    
    <!-- Alpine.js Notification Data -->
    <script>
        function appData() {
            return {
                sidebarOpen: false,
                isDarkMode: false,
                notifications: [],
                unreadCount: 0,
                notificationDropdownOpen: false,
                
                init() {
                    @auth
                    // TEMPORARILY DISABLED FOR DEBUGGING
                    // this.loadNotifications();
                    // this.loadUnreadCount();
                    // this.startPolling();
                    console.log('Notification polling disabled for debugging');
                    @endauth
                },
                
                async loadNotifications() {
                    try {
                        const response = await fetch('/api/notifications/recent');
                        const data = await response.json();
                        if (data.success) {
                            this.notifications = data.notifications;
                        }
                    } catch (error) {
                        console.error('Error loading notifications:', error);
                    }
                },
                
                async loadUnreadCount() {
                    try {
                        const response = await fetch('/api/notifications/unread-count');
                        const data = await response.json();
                        if (data.success) {
                            this.unreadCount = data.unread_count;
                        }
                    } catch (error) {
                        console.error('Error loading unread count:', error);
                    }
                },
                
                startPolling() {
                    setInterval(() => {
                        this.loadNotifications();
                        this.loadUnreadCount();
                    }, 30000);
                },
                
                async markAsRead(notificationId) {
                    try {
                        const response = await fetch(`/api/notifications/${notificationId}/read`, {
                            method: 'PATCH',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            }
                        });
                        
                        if (response.ok) {
                            await this.loadNotifications();
                            await this.loadUnreadCount();
                        }
                    } catch (error) {
                        console.error('Error marking as read:', error);
                    }
                },
                
                handleNotificationClick(notification) {
                    if (!notification.is_read) {
                        this.markAsRead(notification.id);
                    }
                    
                    // Navigate ke board page
                    if (notification.data && notification.data.board_id) {
                        window.location.href = `/boards/${notification.data.board_id}`;
                    }
                }
            }
        }
    </script>
</body>
</html>