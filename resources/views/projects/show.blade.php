@extends(Auth::user()->role === 'admin' ? 'layouts.admin' : 'layouts.app')

@section('title', 'Project Details')

@section('content')
{{-- 
    HALAMAN SHOW PROJECT - MODERN DASHBOARD STYLE
    ==============================================
    Styling yang digunakan:
    - Dashboard-style layout dengan cards dan statistics
    - Modern project header dengan status indicators
    - Team members display dengan avatar dan role badges
    - Boards/tasks overview dengan progress visualization
    - Action buttons untuk edit, delete, dan manage
    - Responsive grid layout untuk all sections
    - Interactive elements dengan hover effects
    - Color-coded sections untuk visual hierarchy
--}}

<div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-indigo-50 py-6 px-4">
    {{-- 
        Background gradient neutral untuk view mode:
        - from-gray-50: abu terang untuk professional look
        - via-blue-50: biru terang untuk trust dan stability
        - to-indigo-50: indigo terang untuk premium feel
    --}}
    
    <!-- Floating Background Elements -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-20 right-10 w-40 h-40 bg-blue-200/10 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute bottom-40 left-20 w-60 h-60 bg-indigo-200/10 rounded-full blur-3xl animate-pulse" style="animation-delay: 2s;"></div>
        <div class="absolute top-1/2 right-1/3 w-32 h-32 bg-gray-200/15 rounded-full blur-2xl animate-pulse" style="animation-delay: 1s;"></div>
    </div>
    
    <!-- Main Container -->
    <div class="relative z-10 max-w-7xl mx-auto">
        {{-- max-w-7xl untuk dashboard yang lebih luas --}}
        
        <!-- Project Header Section -->
        <div class="mb-8" 
             x-data 
             x-init="$el.style.opacity = '0'; $el.style.transform = 'translateY(-20px)'; 
                    setTimeout(() => { 
                        $el.style.transition = 'all 0.8s ease-out'; 
                        $el.style.opacity = '1'; 
                        $el.style.transform = 'translateY(0)'; 
                    }, 100)">
            
            <!-- Breadcrumb -->
            <nav class="mb-6">
                <ol class="flex items-center space-x-2 text-sm text-gray-500">
                    <li><a href="{{ route('dashboard') }}" class="hover:text-blue-600 transition-colors duration-200">Dashboard</a></li>
                    <li><span class="mx-2 text-gray-400">→</span></li>
                    <li><a href="{{ route('projects.index') }}" class="hover:text-blue-600 transition-colors duration-200">Projects</a></li>
                    <li><span class="mx-2 text-gray-400">→</span></li>
                    <li class="text-blue-600 font-medium">{{ isset($project) ? $project->project_name ?? 'Project Details' : 'Project Details' }}</li>
                    {{-- <li><span class="mx-2 text-gray-400">→</span></li> --}}
                    {{-- <li><span class="mx-2 text-red-400">
                            //@if (session('error'))
                                <div class="alert alert-danger">
                                    {{ session('error') }}
                                </div>
                            //@endif
                    </span></li> --}}

                </ol>
            </nav>
            
            <!-- Project Title & Status Card -->
            <div class="bg-white/60 backdrop-blur-xl rounded-2xl shadow-xl border border-white/40 p-8">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    
                    <!-- Project Info -->
                    <div class="flex-1">
                        <div class="flex items-start gap-4">
                            <!-- Project Icon -->
                            <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg flex-shrink-0">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                            
                            <!-- Project Details -->
                            <div class="flex-1 min-w-0">
                                <h1 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-2">
                                    {{ isset($project) ? $project->project_name ?? 'Project Name' : 'Project Name' }}
                                </h1>
                                
                                <!-- Status Badges -->
                                <div class="flex flex-wrap items-center gap-3 mb-4">
                                    <!-- Deadline Status -->
                                    @if(isset($project) && isset($project->deadline))
                                        @php
                                            $deadline = \Carbon\Carbon::parse($project->deadline);
                                            $now = \Carbon\Carbon::now();
                                            $daysLeft = round($now->diffInDays($deadline, false));
                                        @endphp
                                        
                                        @if($daysLeft < 0)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800 border border-red-200">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                Overdue ({{ abs($daysLeft) }} days)
                                            </span>
                                        @elseif($daysLeft <= 7)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800 border border-yellow-200">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                Due Soon ({{ $daysLeft }} days)
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 border border-green-200">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                On Track ({{ $daysLeft }} days left)
                                            </span>
                                        @endif
                                    @endif
                                    
                                    <!-- Team Size Badge -->
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 border border-blue-200">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-.5a4 4 0 110 5.292"/>
                                        </svg>
                                        {{ isset($statistics) ? $statistics['total_members'] ?? '0' : '0' }} Members
                                    </span>
                                    
                                    <!-- Progress Badge -->
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800 border border-purple-200">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                        </svg>
                                        {{ isset($statistics) ? $statistics['total_boards'] ?? '0' : '0' }} Boards
                                    </span>
                                </div>
                                
                                <!-- Project Meta Info -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        <span>Created by: <strong>{{ isset($project) && isset($project->creator) ? $project->creator->full_name ?? 'Unknown' : 'Unknown' }}</strong></span>
                                    </div>
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <span>Deadline: <strong>{{ isset($project) && $project->deadline ? \Carbon\Carbon::parse($project->deadline)->format('M d, Y') : 'Not set' }}</strong></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row gap-3 lg:flex-col xl:flex-row">
                        @if(isset($userRole) && ($userRole === 'admin'))
                            <!-- Edit Button -->
                            <a href="{{ route('projects.edit', $project ?? 1) }}" 
                               class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Edit Project
                            </a>
                        @endif
                        
                        <!-- Manage Members Button - Admin Only -->
                        @if(Auth::user()->role === 'admin')
                            <a href="{{ route('project-members.index', ['project' => $project->slug]) }}" class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-.5a4 4 0 110 5.292"/>
                                </svg>
                                Manage Team
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Project Description Section -->
        @if(isset($project) && $project->description)
        <div class="mb-8" 
             x-data 
             x-init="$el.style.opacity = '0'; $el.style.transform = 'translateY(20px)'; 
                    setTimeout(() => { 
                        $el.style.transition = 'all 0.8s ease-out'; 
                        $el.style.opacity = '1'; 
                        $el.style.transform = 'translateY(0)'; 
                    }, 200)">
            
            <div class="bg-white/60 backdrop-blur-xl rounded-2xl shadow-xl border border-white/40 p-6">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-gradient-to-r from-gray-500 to-gray-600 rounded-xl flex items-center justify-center mr-4">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-semibold text-gray-800">Project Description</h2>
                </div>
                <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ $project->description }}</p>
            </div>
        </div>
        @endif
        
        <!-- Statistics Dashboard -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8" 
             x-data 
             x-init="$el.style.opacity = '0'; $el.style.transform = 'translateY(30px)'; 
                    setTimeout(() => { 
                        $el.style.transition = 'all 0.8s ease-out'; 
                        $el.style.opacity = '1'; 
                        $el.style.transform = 'translateY(0)'; 
                    }, 300)">
                    
                    
                    
                    
            
            <!-- Total Boards Stat -->
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 backdrop-blur-xl rounded-2xl shadow-lg border border-blue-200/50 p-6 hover:shadow-xl transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-600 text-sm font-medium">Total Boards</p>
                        <p class="text-3xl font-bold text-blue-800">{{ isset($statistics) ? $statistics['total_boards'] ?? '0' : '0' }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-500 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 0a2 2 0 012 2v6a2 2 0 01-2 2m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v8a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                </div>
            </div>
            
            
            
            
            
            <!-- Total Cards Stat -->
            <div class="bg-gradient-to-br from-green-50 to-green-100 backdrop-blur-xl rounded-2xl shadow-lg border border-green-200/50 p-6 hover:shadow-xl transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-600 text-sm font-medium">Total Cards</p>
                        <p class="text-3xl font-bold text-green-800">{{ isset($statistics) ? $statistics['total_cards'] ?? '0' : '0' }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-500 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                </div>
            </div>
            
            
            
            
            <!-- Team Members Stat -->
            <div class="bg-gradient-to-br from-purple-50 to-purple-100 backdrop-blur-xl rounded-2xl shadow-lg border border-purple-200/50 p-6 hover:shadow-xl transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-600 text-sm font-medium">Team Members</p>
                        <p class="text-3xl font-bold text-purple-800">{{ isset($statistics) ? $statistics['total_members'] ?? '0' : '0' }}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-500 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-.5a4 4 0 110 5.292"/>
                        </svg>
                    </div>
                </div>
            </div>
            
            
            
            
            
            
            <!-- Days Remaining Stat -->
            <div class="bg-gradient-to-br from-orange-50 to-orange-100 backdrop-blur-xl rounded-2xl shadow-lg border border-orange-200/50 p-6 hover:shadow-xl transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-orange-600 text-sm font-medium">Days Remaining</p>
                        <p class="text-3xl font-bold text-orange-800">
                            @if(isset($statistics) && isset($statistics['days_remaining']))
                                {{ $statistics['days_remaining'] >= 0 ? $statistics['days_remaining'] : 'Overdue' }}
                            @else
                                N/A
                            @endif
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-orange-500 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        
        
        
        
        
        
        
        
        
        
        
        
        
        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8" 
             x-data 
             x-init="$el.style.opacity = '0'; $el.style.transform = 'translateY(40px)'; 
                    setTimeout(() => { 
                        $el.style.transition = 'all 0.8s ease-out'; 
                        $el.style.opacity = '1'; 
                        $el.style.transform = 'translateY(0)'; 
                    }, 400)">
                    
                    
                    
                    
            
            <!-- Team Members Card -->
            <div class="xl:col-span-1 space-y-6">
                <div class="bg-white/60 backdrop-blur-xl rounded-2xl shadow-xl border border-white/40 p-6 h-fit">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-gradient-to-r from-purple-500 to-pink-600 rounded-xl flex items-center justify-center mr-4">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-.5a4 4 0 110 5.292"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-800">Team Members</h3>
                        </div>
                        
                    </div>
                    
                    <!-- Members List -->
                    <div class="space-y-4">
                        @if(isset($project) && $project->members->isNotEmpty())
                            @foreach($project->members->take(5) as $index => $member)
                                @php
                                    // Generate initials from full name
                                    $initials = collect(explode(' ', $member->user->full_name))
                                        ->map(fn($word) => strtoupper(substr($word, 0, 1)))
                                        ->take(2)
                                        ->join('');
                                    
                                    // Role colors
                                    $roleColors = [
                                        'team lead' => 'bg-red-100 text-red-800',
                                        'developer' => 'bg-blue-100 text-blue-800',
                                        'designer' => 'bg-green-100 text-green-800',
                                    ];
                                    
                                    // Avatar gradients
                                    $gradients = [
                                        'from-purple-500 to-pink-600',
                                        'from-blue-500 to-indigo-600',
                                        'from-green-500 to-teal-600',
                                        'from-orange-500 to-red-600',
                                        'from-yellow-500 to-orange-600',
                                    ];
                                @endphp
                                
                                <div class="flex items-center p-4 bg-{{ $member->role === 'team lead' ? 'purple' : 'gray' }}-50/60 backdrop-blur-sm rounded-xl border border-{{ $member->role === 'team lead' ? 'purple' : 'gray' }}-200/50 hover:bg-{{ $member->role === 'team lead' ? 'purple' : 'gray' }}-100/60 transition-all duration-200">
                                    <div class="w-12 h-12 bg-gradient-to-r {{ $gradients[$index % 5] }} rounded-full flex items-center justify-center text-white font-semibold mr-4">
                                        {{ $initials }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-gray-900">{{ $member->user->full_name }}</p>
                                        <p class="text-sm text-gray-500 truncate">{{ $member->user->email }}</p>
                                    </div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $roleColors[$member->role] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst($member->role) }}
                                    </span>
                                </div>
                            @endforeach
                            
                            @if($project->members->count() > 5)
                                <div class="text-center pt-2">
                                    <a href="{{ route('project-members.index', ['project' => $project->slug]) }}" 
                                       class="text-sm text-purple-600 hover:text-purple-800 font-medium">
                                        + {{ $project->members->count() - 5 }} more members
                                    </a>
                                </div>
                            @endif
                        @else
                            <div class="text-center py-8 text-gray-500">
                                <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <p class="text-sm">No team members yet</p>
                                <a href="{{ route('project-members.index', ['project' => $project->slug]) }}" 
                                   class="text-sm text-purple-600 hover:text-purple-800 font-medium mt-2 inline-block">
                                    Add members
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
                
                
                

                
                <!-- Additional Team Info Card -->
                <div class="bg-white/60 backdrop-blur-xl rounded-2xl shadow-xl border border-white/40 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-xl flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2-2v6a2 2 0 002 2h2a2 2 0 002 2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-800">Project Stats</h3>
                        </div>
                    </div>
                    
                    @php
                        $totalCards = $statistics['total_cards'] ?? 0;
                        $completedCards = $project->boards->sum(function($board) {
                            return $board->cards->where('status', 'done')->count();
                        });
                        $inProgressCards = $project->boards->sum(function($board) {
                            return $board->cards->where('status', 'in progress')->count();
                        });
                        $todoCards = $project->boards->sum(function($board) {
                            return $board->cards->where('status', 'todo')->count();
                        });
                        $reviewCards = $project->boards->sum(function($board) {
                            return $board->cards->where('status', 'review')->count();
                        });
                    @endphp
                    
                    <div class="space-y-3">
                        <div class="flex items-center text-sm text-gray-600">
                            <div class="w-2 h-2 bg-green-500 rounded-full mr-3"></div>
                            <span>{{ $completedCards }} {{ Str::plural('task', $completedCards) }} completed</span>
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <div class="w-2 h-2 bg-blue-500 rounded-full mr-3"></div>
                            <span>{{ $inProgressCards }} {{ Str::plural('task', $inProgressCards) }} in progress</span>
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <div class="w-2 h-2 bg-yellow-500 rounded-full mr-3"></div>
                            <span>{{ $reviewCards }} {{ Str::plural('task', $reviewCards) }} in review</span>
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <div class="w-2 h-2 bg-gray-400 rounded-full mr-3"></div>
                            <span>{{ $todoCards }} {{ Str::plural('task', $todoCards) }} to do</span>
                        </div>
                        
                        @if($totalCards > 0)
                            <div class="pt-3 border-t border-gray-200">
                                <div class="flex justify-between text-xs text-gray-500 mb-1">
                                    <span>Overall Progress</span>
                                    <span>{{ round(($completedCards / $totalCards) * 100) }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-gradient-to-r from-green-500 to-emerald-600 h-2 rounded-full transition-all duration-300" 
                                         style="width: {{ ($completedCards / $totalCards) * 100 }}%"></div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            
            
            
            
            <!-- Project Boards Card -->
            <div class="xl:col-span-2">
                <div class="bg-white/60 backdrop-blur-xl rounded-2xl shadow-xl border border-white/40 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center mr-4">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 0a2 2 0 012 2v6a2 2 0 01-2 2m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v8a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-800">Task Overview by Status</h3>
                        </div>
                    </div>
                    
                    @php
                        $statusGroups = [
                            'todo' => ['label' => 'To Do', 'color' => 'gray', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                            'in progress' => ['label' => 'In Progress', 'color' => 'blue', 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z'],
                            'review' => ['label' => 'Review', 'color' => 'yellow', 'icon' => 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z'],
                            'done' => ['label' => 'Done', 'color' => 'green', 'icon' => 'M5 13l4 4L19 7'],
                        ];
                    @endphp
                    
                    <!-- Boards Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($statusGroups as $status => $config)
                            @php
                                // Filter cards berdasarkan role
                                $cards = $project->boards->flatMap(function($board) use ($status) {
                                    return $board->cards->where('status', $status);
                                })->take(3);
                                
                                $totalCount = $project->boards->sum(function($board) use ($status) {
                                    return $board->cards->where('status', $status)->count();
                                });
                            @endphp
                            
                            <div class="bg-{{ $config['color'] }}-50/50 backdrop-blur-sm rounded-xl border border-{{ $config['color'] }}-200/50 p-4">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-{{ $config['color'] }}-500 rounded-lg flex items-center justify-center mr-2">
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $config['icon'] }}"/>
                                            </svg>
                                        </div>
                                        <h4 class="font-semibold text-{{ $config['color'] }}-800">{{ $config['label'] }}</h4>
                                    </div>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-{{ $config['color'] }}-100 text-{{ $config['color'] }}-800">
                                        {{ $totalCount }}
                                    </span>
                                </div>
                                
                                <div class="space-y-2">
                                    @forelse($cards as $card)
                                        <a href="{{ route('cards.show', $card) }}" class="bg-white/80 backdrop-blur-sm rounded-lg p-3 hover:shadow-md transition-all duration-200 cursor-pointer">
                                            <p class="text-sm font-medium text-gray-900 line-clamp-1">{{ $card->card_title }}</p>
                                            @if($card->description)
                                                <p class="text-xs text-gray-500 mt-1 line-clamp-2">{{ $card->description }}</p>
                                            @endif
                                            <div class="flex items-center mt-2 text-xs text-gray-400">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                                                </svg>
                                                {{ $card->comments_count ?? 0 }}
                                            </div>
                                        </a>
                                    @empty
                                        <p class="text-center text-{{ $config['color'] }}-400 text-xs py-4">
                                            @if(Auth::user()->role === 'member')
                                                No tasks assigned
                                            @else
                                                No tasks
                                            @endif
                                        </p>
                                    @endforelse
                                    
                                    @if($totalCount > 3)
                                        <p class="text-center text-xs text-{{ $config['color'] }}-600 pt-2">
                                            + {{ $totalCount - 3 }} more
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>



        
        
        


        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8 mt-10" 
             x-data 
             x-init="$el.style.opacity = '0'; $el.style.transform = 'translateY(40px)'; 
                    setTimeout(() => { 
                        $el.style.transition = 'all 0.8s ease-out'; 
                        $el.style.opacity = '1'; 
                        $el.style.transform = 'translateY(0)'; 
                    }, 400)">
                    
                    
                    
                    

                    
                    {{-- board task --}}
                    <div class="xl:col-span-full">
                        
                        @if(Auth::user()->role === 'member')
                            <!-- Member Notice -->
                            <div class="mb-4 bg-blue-50/60 backdrop-blur-sm border border-blue-200 rounded-xl p-4">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <p class="text-sm text-blue-800">
                                        <strong>Member View:</strong> You are viewing only boards and tasks assigned to you.
                                    </p>
                                </div>
                            </div>
                        @endif


                        <x-ui.board.board-container :userRole="$userRole ?? null">
            



                            <!-- Boards Grid -->
                            <div class="grid grid-cols-1 md:grid-cols-3  gap-4">
                            @if( isset($project) && $project->boards->isNotEmpty() )
                                @foreach ($project->boards as $board)

                                    <div class="relative">
                                        <x-ui.board.board-card 
                                            :name="$board->board_name" 
                                            :description="$board->description" 
                                            :totalCards="$board->cards_count"
                                            :board="$board"
                                            :boardUrl="route('boards.show', $board->id)" >
                                
                                        @if($board->cards->isNotEmpty())
                                            @foreach ($board->cards as $card)
                                                <x-ui.task-board-list 
                                                    :taskName="$card->card_title" 
                                                    :description="$card->description" 
                                                    :taskStatus="$card->status" />
                                            @endforeach
                                        @else
                                            <p class="text-gray-500 text-center text-sm">No cards available in this board.</p>
                                        @endif
                                        
                                        </x-ui.board.board-card>
                                        
                                        {{-- Delete Confirmation Modal untuk setiap board --}}
                                        @if(Auth::user()->role === 'admin' || Auth::id() === $project->created_by)
                                            <x-ui.board.delete-board-modal :board="$board" />
                                        @endif
                                    </div>
                                
                                @endforeach                    
                                
                                
                                @else
                                    <div class="col-span-3">
                                        @if(Auth::user()->role === 'member')
                                            <div class="text-center py-12">
                                                <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                                </svg>
                                                <p class="text-gray-600 font-medium mb-2">No tasks assigned to you yet</p>
                                                <p class="text-sm text-gray-500">Contact your project manager to get assigned to tasks</p>
                                            </div>
                                        @else
                                            <p class="text-center text-gray-500">No boards available. Please add a board to get started.</p>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </x-ui.board.board-container>
                    </div>

                    
                    
                    
                    
                </div>
            </div>
            <x-ui.board.add-board-modal :project="$project" :nextPosition="$statistics['total_boards'] + 1" />
</div>







{{-- JavaScript untuk Interactive Elements --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Smooth scroll untuk anchor links jika ada
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Tooltip functionality untuk badges jika diperlukan
    const badges = document.querySelectorAll('[data-tooltip]');
    badges.forEach(badge => {
        badge.addEventListener('mouseenter', function() {
            // Add tooltip logic here if needed
        });
    });
});
</script>
@endsection