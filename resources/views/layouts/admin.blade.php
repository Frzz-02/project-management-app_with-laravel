<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin Dashboard') - {{ config('app.name', 'Laravel') }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/taskflow_logo.png') }}">


    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800,900&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .font-inter { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 font-inter antialiased" x-data="{ 
    sidebarOpen: window.innerWidth >= 1024,
    init() {
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) {
                this.sidebarOpen = true;
            }
        });
    }
}" x-init="init()">
    
    <!-- Top Navigation Bar - Fixed & Responsive -->
    <nav class="bg-white shadow-sm border-b border-gray-200 fixed top-0 left-0 right-0 z-30">
        <div class="px-3 sm:px-4 lg:px-6">
            <div class="flex justify-between items-center h-14 sm:h-16">
                <!-- Left: Toggle + Logo -->
                <div class="flex items-center space-x-2 sm:space-x-4 min-w-0 flex-1">
                    <button @click="sidebarOpen = !sidebarOpen" class="text-gray-600 hover:text-gray-900 p-2 rounded-lg hover:bg-gray-100 transition-colors flex-shrink-0">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>

                    <a href="{{ route('admin.dashboard') }}" class="flex items-center min-w-0">
                        <span class="text-base sm:text-lg lg:text-xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent truncate">
                            {{ config('app.name', 'PM App') }}
                        </span>
                        <span class="ml-1.5 sm:ml-2 px-1.5 sm:px-2 py-0.5 text-[10px] sm:text-xs font-semibold bg-red-100 text-red-800 rounded-full flex-shrink-0">
                            ADMIN
                        </span>
                    </a>
                </div>

                <!-- Right: User + Logout -->
                <div class="flex items-center space-x-2 sm:space-x-4">
                    <!-- User Info - Desktop Only -->
                    <div class="text-right hidden md:block">
                        <div class="text-sm font-medium text-gray-900 truncate max-w-[120px] xl:max-w-none">{{ Auth::user()->full_name }}</div>
                        <div class="text-xs text-gray-500">{{ ucfirst(Auth::user()->role) }}</div>
                    </div>

                    <!-- Logout -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="bg-red-500 text-white px-2.5 py-1.5 sm:px-4 sm:py-2 rounded-lg text-xs sm:text-sm font-medium hover:bg-red-600 transition-colors flex items-center">
                            <span class="hidden sm:inline">Logout</span>
                            <svg class="w-4 h-4 sm:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Mobile Overlay -->
    <div 
        x-show="sidebarOpen && window.innerWidth < 1024" 
        @click="sidebarOpen = false"
        x-transition:enter="transition-opacity ease-linear duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-300"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-10 lg:hidden"
    ></div>

    <!-- Sidebar - Responsive -->
    <aside 
        x-show="sidebarOpen"
        x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="-translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-300 transform"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        @click.away="if (window.innerWidth < 1024) sidebarOpen = false"
        class="fixed left-0 top-14 sm:top-16 bottom-0 w-64 sm:w-72 lg:w-64 bg-white border-r border-gray-200 shadow-2xl lg:shadow-lg z-20 overflow-y-auto"
    >
        <nav class="p-3 sm:p-4 space-y-1">
            <!-- Overview Section -->
            <div class="mb-4 sm:mb-6">
                <h3 class="px-3 text-[10px] sm:text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">
                    Overview
                </h3>
                
                <a href="{{ route('admin.dashboard') }}" 
                   class="flex items-center px-3 py-2 sm:py-2.5 text-sm font-medium rounded-lg transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 text-blue-700 shadow-sm' : 'text-gray-700 hover:bg-gray-50' }}">
                    <svg class="w-5 h-5 mr-2 sm:mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    <span class="truncate">Dashboard</span>
                </a>
            </div>

            <!-- Management Section -->
            <div class="mb-4 sm:mb-6">
                <h3 class="px-3 text-[10px] sm:text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">
                    Management
                </h3>
                
                <a href="{{ route('projects.index') }}" 
                   class="flex items-center px-3 py-2 sm:py-2.5 text-sm font-medium rounded-lg transition-all {{ request()->routeIs('projects.*') ? 'bg-blue-50 text-blue-700 shadow-sm' : 'text-gray-700 hover:bg-gray-50' }}">
                    <svg class="w-5 h-5 mr-2 sm:mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span class="truncate">Projects</span>
                </a>

                <a href="{{ route('reports.index') }}" 
                   class="flex items-center px-3 py-2 sm:py-2.5 text-sm font-medium rounded-lg transition-all {{ request()->routeIs('reports.*') ? 'bg-blue-50 text-blue-700 shadow-sm' : 'text-gray-700 hover:bg-gray-50' }}">
                    <svg class="w-5 h-5 mr-2 sm:mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    <span class="truncate">Reports</span>
                </a>

                <a href="{{ route('admin.activity-logs') }}" 
                   class="flex items-center px-3 py-2 sm:py-2.5 text-sm font-medium rounded-lg transition-all {{ request()->routeIs('admin.activity-logs') ? 'bg-blue-50 text-blue-700 shadow-sm' : 'text-gray-700 hover:bg-gray-50' }}">
                    <svg class="w-5 h-5 mr-2 sm:mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="truncate">Activity Logs</span>
                </a>

                <a href="{{ route('admin.statistics') }}" 
                   class="flex items-center px-3 py-2 sm:py-2.5 text-sm font-medium rounded-lg transition-all {{ request()->routeIs('admin.statistics') ? 'bg-blue-50 text-blue-700 shadow-sm' : 'text-gray-700 hover:bg-gray-50' }}">
                    <svg class="w-5 h-5 mr-2 sm:mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span class="truncate">Statistics</span>
                </a>

            </div>

            




            <!-- Settings Section -->
            <div class="mb-4 sm:mb-6 pb-4">
                <h3 class="px-3 text-[10px] sm:text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">
                    Settings
                </h3>
                
                <a href="{{ route('admin.settings') }}" 
                   class="flex items-center px-3 py-2 sm:py-2.5 text-sm font-medium rounded-lg transition-all {{ request()->routeIs('admin.settings') ? 'bg-blue-50 text-blue-700 shadow-sm' : 'text-gray-700 hover:bg-gray-50' }}">
                    <svg class="w-5 h-5 mr-2 sm:mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span class="truncate">System Settings</span>
                </a>
            </div>
        </nav>
    </aside>

    <!-- Main Content Area - Responsive Margin -->
    <div class="transition-all duration-300 pt-14 sm:pt-16 min-h-screen" 
         :class="{ 'lg:ml-64': sidebarOpen && window.innerWidth >= 1024 }">
        
        <!-- Flash Messages -->
        @if (session('success'))
            <div class="px-3 sm:px-4 lg:px-6 pt-4">
                <div class="max-w-7xl mx-auto bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg relative shadow-sm" x-data="{ show: true }" x-show="show">
                    <span class="block sm:inline pr-8">{{ session('success') }}</span>
                    <button @click="show = false" class="absolute top-0 right-0 px-4 py-3 hover:text-green-900">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="px-3 sm:px-4 lg:px-6 pt-4">
                <div class="max-w-7xl mx-auto bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg relative shadow-sm" x-data="{ show: true }" x-show="show">
                    <span class="block sm:inline pr-8">{{ session('error') }}</span>
                    <button @click="show = false" class="absolute top-0 right-0 px-4 py-3 hover:text-red-900">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
            </div>
        @endif

        <!-- Main Content -->
        <main>
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 mt-8 sm:mt-12">
            <div class="px-3 sm:px-4 lg:px-6 py-4 sm:py-6">
                <div class="max-w-7xl mx-auto text-center text-xs sm:text-sm text-gray-500">
                    &copy; {{ date('Y') }} {{ config('app.name') }}. Admin Panel - All Rights Reserved.
                </div>
            </div>
        </footer>
    </div>

    <!-- Scripts -->
    <script>
        // Auto-hide flash messages after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('[x-data*="show"]');
            alerts.forEach(alert => {
                try {
                    const alpineData = Alpine.$data(alert);
                    if (alpineData && alpineData.show !== undefined) {
                        alpineData.show = false;
                    }
                } catch (e) {
                    console.log('Flash message auto-hide:', e);
                }
            });
        }, 5000);
    </script>
</body>
</html>
