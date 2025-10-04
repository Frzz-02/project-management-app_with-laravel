@extends('layouts.app')

@section('title', 'All Projects')

@section('content')
{{-- 
    HALAMAN INDEX PROJECTS - ULTRA MODERN DESIGN
    ==========================================
    Features:
    - Glassmorphism dengan backdrop effects
    - Advanced filtering dan search system
    - Card-based layout dengan hover animations
    - Interactive status indicators
    - Modern grid system dengan responsive design
    - Beautiful loading states dan micro-interactions
    - Color-coded project categories
    - Advanced analytics dashboard
--}}

<!-- Background Elements -->
<div class="fixed inset-0 overflow-hidden pointer-events-none z-0">
    <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-purple-400/20 to-pink-400/20 rounded-full blur-3xl animate-pulse"></div>
    <div class="absolute top-1/3 -left-40 w-96 h-96 bg-gradient-to-br from-blue-400/20 to-cyan-400/20 rounded-full blur-3xl animate-pulse" style="animation-delay: 2s;"></div>
    <div class="absolute bottom-20 right-1/3 w-72 h-72 bg-gradient-to-br from-emerald-400/20 to-teal-400/20 rounded-full blur-3xl animate-pulse" style="animation-delay: 4s;"></div>
</div>

<div class="relative z-10 min-h-screen bg-gradient-to-br from-slate-50 via-blue-50/50 to-indigo-50/50 py-8 px-4">
    <div class="max-w-7xl mx-auto">
        
        <!-- Header Section -->
        <div class="mb-12" x-data="{ mounted: false }" x-init="setTimeout(() => mounted = true, 100)">
            <div class="text-center mb-8" 
                 :class="mounted ? 'opacity-100 translate-y-0' : 'opacity-0 -translate-y-8'"
                 class="transition-all duration-1000 ease-out">
                
                <!-- Main Title -->
                <h1 class="text-6xl lg:text-7xl font-black bg-gradient-to-r from-gray-900 via-blue-800 to-purple-800 bg-clip-text text-transparent mb-4">
                    Project Hub
                </h1>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed mb-8">
                    Your central command center for managing all projects. Track progress, collaborate with teams, and deliver exceptional results.
                </p>
                
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
                    <!-- Total Projects -->
                    <div class="bg-white/70 backdrop-blur-xl rounded-2xl p-6 border border-white/50 shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105 group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-3xl font-bold text-gray-900 group-hover:text-blue-600 transition-colors">{{ $totalProjects ?? 0 }}</p>
                                <p class="text-sm font-medium text-gray-600">Total Projects</p>
                            </div>
                            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Active Projects -->
                    <div class="bg-white/70 backdrop-blur-xl rounded-2xl p-6 border border-white/50 shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105 group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-3xl font-bold text-gray-900 group-hover:text-emerald-600 transition-colors">{{ $activeProjects ?? 0 }}</p>
                                <p class="text-sm font-medium text-gray-600">Active Projects</p>
                            </div>
                            <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Team Members -->
                    <div class="bg-white/70 backdrop-blur-xl rounded-2xl p-6 border border-white/50 shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105 group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-3xl font-bold text-gray-900 group-hover:text-purple-600 transition-colors">{{ $totalMembers ?? 0 }}</p>
                                <p class="text-sm font-medium text-gray-600">Team Members</p>
                            </div>
                            <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-.5a4 4 0 110 5.292"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Completion Rate -->
                    <div class="bg-white/70 backdrop-blur-xl rounded-2xl p-6 border border-white/50 shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105 group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-3xl font-bold text-gray-900 group-hover:text-orange-600 transition-colors">{{ $completionRate ?? 0 }}%</p>
                                <p class="text-sm font-medium text-gray-600">Completion Rate</p>
                            </div>
                            <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2-2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search & Filter Section -->
        <div class="bg-white/60 backdrop-blur-2xl rounded-3xl shadow-2xl border border-white/60 p-8 mb-12" 
             x-data="{ 
                showAdvancedFilters: false,
                searchQuery: '',
                selectedStatus: '',
                selectedPriority: '',
                sortBy: 'newest'
             }">
            
            <!-- Main Search Bar -->
            <div class="flex flex-col lg:flex-row gap-6 items-center mb-6">
                <div class="flex-1 w-full">
                    <div class="relative group">
                        <input type="text" 
                               x-model="searchQuery"
                               placeholder="Search projects by name, description, or team member..."
                               class="w-full pl-14 pr-6 py-5 bg-white/80 backdrop-blur-sm border-2 border-gray-200/60 rounded-2xl text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500/60 transition-all duration-300 hover:bg-white hover:border-gray-300/60 text-lg group-hover:shadow-lg">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-5">
                            <svg class="w-6 h-6 text-gray-400 group-hover:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="flex items-center space-x-4">
                    <!-- Advanced Filters Toggle -->
                    <button @click="showAdvancedFilters = !showAdvancedFilters"
                            class="px-6 py-3 bg-gray-100/80 hover:bg-gray-200/80 text-gray-700 font-semibold rounded-xl transition-all duration-300 hover:scale-105 focus:outline-none focus:ring-4 focus:ring-gray-500/20 shadow-md hover:shadow-lg backdrop-blur-sm">
                        <span class="flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"/>
                            </svg>
                            <span>Filters</span>
                        </span>
                    </button>
                    
                    <!-- Create New Project -->
                    @can('create', App\Models\Project::class)
                    <a href="{{ route('projects.create') }}"
                       class="px-8 py-3 bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-600 hover:from-blue-700 hover:via-purple-700 hover:to-indigo-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-blue-500/20">
                        <span class="flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            <span>New Project</span>
                        </span>
                    </a>
                    @endcan
                </div>
            </div>
            
            <!-- Advanced Filters -->
            <div x-show="showAdvancedFilters" 
                 x-collapse
                 class="grid grid-cols-1 md:grid-cols-4 gap-6 pt-6 border-t border-gray-200/60">
                
                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-3">Status</label>
                    <select x-model="selectedStatus" 
                            class="w-full px-4 py-3 bg-white/80 backdrop-blur-sm border-2 border-gray-200/60 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/60 transition-all duration-300 hover:bg-white hover:border-gray-300/60">
                        <option value="">All Status</option>
                        <option value="planning">Planning</option>
                        <option value="active">Active</option>
                        <option value="on-hold">On Hold</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                
                <!-- Priority Filter -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-3">Priority</label>
                    <select x-model="selectedPriority" 
                            class="w-full px-4 py-3 bg-white/80 backdrop-blur-sm border-2 border-gray-200/60 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/60 transition-all duration-300 hover:bg-white hover:border-gray-300/60">
                        <option value="">All Priorities</option>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
                
                <!-- Date Range -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-3">Deadline</label>
                    <select class="w-full px-4 py-3 bg-white/80 backdrop-blur-sm border-2 border-gray-200/60 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/60 transition-all duration-300 hover:bg-white hover:border-gray-300/60">
                        <option value="">All Deadlines</option>
                        <option value="overdue">Overdue</option>
                        <option value="this-week">This Week</option>
                        <option value="this-month">This Month</option>
                        <option value="next-month">Next Month</option>
                    </select>
                </div>
                
                <!-- Sort Options -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-3">Sort By</label>
                    <select x-model="sortBy" 
                            class="w-full px-4 py-3 bg-white/80 backdrop-blur-sm border-2 border-gray-200/60 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/60 transition-all duration-300 hover:bg-white hover:border-gray-300/60">
                        <option value="newest">Newest First</option>
                        <option value="oldest">Oldest First</option>
                        <option value="deadline">Deadline</option>
                        <option value="name">Name A-Z</option>
                        <option value="priority">Priority</option>
                        <option value="progress">Progress</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Projects Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8" id="projectsGrid">
            @forelse($projects ?? [] as $index => $project)
                <div class="project-card group relative" 
                     style="animation: fadeInUp 0.6s ease-out {{ $index * 0.1 }}s both">
                    
                    <!-- Main Card -->
                    <div class="bg-white/70 backdrop-blur-2xl rounded-3xl shadow-xl border border-white/60 overflow-hidden hover:shadow-2xl hover:scale-[1.02] transition-all duration-500 group-hover:bg-white/80">
                        
                        <!-- Card Header with Gradient -->
                        <div class="relative h-32 bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 overflow-hidden">
                            <!-- Animated Background Pattern -->
                            <div class="absolute inset-0 opacity-20">
                                <div class="absolute top-0 left-0 w-full h-full">
                                    <div class="absolute top-4 left-4 w-8 h-8 bg-white/30 rounded-full animate-pulse"></div>
                                    <div class="absolute top-8 right-8 w-6 h-6 bg-white/20 rounded-full animate-pulse" style="animation-delay: 1s;"></div>
                                    <div class="absolute bottom-6 left-8 w-4 h-4 bg-white/25 rounded-full animate-pulse" style="animation-delay: 2s;"></div>
                                </div>
                            </div>
                            
                            <!-- Status Badge -->
                            <div class="absolute top-4 right-4">
                                @php
                                    $statusConfig = [
                                        'planning' => ['bg' => 'bg-yellow-500', 'text' => 'Planning'],
                                        'active' => ['bg' => 'bg-green-500', 'text' => 'Active'],
                                        'on-hold' => ['bg' => 'bg-orange-500', 'text' => 'On Hold'],
                                        'completed' => ['bg' => 'bg-blue-500', 'text' => 'Completed'],
                                        'cancelled' => ['bg' => 'bg-red-500', 'text' => 'Cancelled']
                                    ];
                                    $status = $project->status ?? 'active';
                                    $config = $statusConfig[$status] ?? $statusConfig['active'];
                                @endphp
                                <span class="px-3 py-1 {{ $config['bg'] }} text-white text-xs font-bold rounded-full shadow-lg backdrop-blur-sm">
                                    {{ $config['text'] }}
                                </span>
                            </div>
                            
                            <!-- Priority Indicator -->
                            <div class="absolute top-4 left-4">
                                @php
                                    $priorityConfig = [
                                        'low' => ['color' => 'text-green-400', 'icon' => '●'],
                                        'medium' => ['color' => 'text-yellow-400', 'icon' => '●●'],
                                        'high' => ['color' => 'text-orange-400', 'icon' => '●●●'],
                                        'urgent' => ['color' => 'text-red-400', 'icon' => '🔥']
                                    ];
                                    $priority = $project->priority ?? 'medium';
                                    $priorityStyle = $priorityConfig[$priority] ?? $priorityConfig['medium'];
                                @endphp
                                <span class="text-white font-bold text-lg {{ $priorityStyle['color'] }}" title="Priority: {{ ucfirst($priority) }}">
                                    {{ $priorityStyle['icon'] }}
                                </span>
                            </div>
                            
                            <!-- Project Title -->
                            <div class="absolute bottom-4 left-4 right-4">
                                <h3 class="text-xl font-bold text-white truncate mb-1">{{ $project->project_name ?? 'Untitled Project' }}</h3>
                                <p class="text-white/80 text-sm">{{ $project->team_lead_name ?? 'No team lead assigned' }}</p>
                            </div>
                        </div>
                        
                        <!-- Card Body -->
                        <div class="p-6">
                            <!-- Description -->
                            <p class="text-gray-600 text-sm mb-6 line-clamp-3 leading-relaxed">
                                {{ $project->description ?: 'No description provided for this project. Click to view more details and add a description.' }}
                            </p>
                            
                            <!-- Progress Section -->
                            <div class="mb-6">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-semibold text-gray-700">Progress</span>
                                    <span class="text-sm font-bold text-gray-900">{{ $project->progress ?? 0 }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                                    <div class="bg-gradient-to-r from-blue-500 to-purple-500 h-3 rounded-full transition-all duration-1000 ease-out" 
                                         style="width: {{ $project->progress ?? 0 }}%"></div>
                                </div>
                            </div>
                            
                            <!-- Stats Grid -->
                            <div class="grid grid-cols-3 gap-4 mb-6">
                                <div class="text-center p-3 bg-blue-50 rounded-xl">
                                    <p class="text-2xl font-bold text-blue-600">{{ $project->tasks_count ?? 0 }}</p>
                                    <p class="text-xs font-medium text-blue-600">Tasks</p>
                                </div>
                                <div class="text-center p-3 bg-purple-50 rounded-xl">
                                    <p class="text-2xl font-bold text-purple-600">{{ $project->members_count ?? 0 }}</p>
                                    <p class="text-xs font-medium text-purple-600">Members</p>
                                </div>
                                <div class="text-center p-3 bg-emerald-50 rounded-xl">
                                    <p class="text-2xl font-bold text-emerald-600">{{ $project->days_left ?? 0 }}</p>
                                    <p class="text-xs font-medium text-emerald-600">Days Left</p>
                                </div>
                            </div>
                            
                            <!-- Deadline Info -->
                            @if($project->deadline ?? false)
                                <div class="mb-6 p-4 bg-gray-50 rounded-xl border border-gray-100">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-2">
                                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            <span class="text-sm font-medium text-gray-700">Deadline</span>
                                        </div>
                                        <span class="text-sm font-bold text-gray-900">{{ date('M d, Y', strtotime($project->deadline)) }}</span>
                                    </div>
                                </div>
                            @endif
                            
                            <!-- Team Members Preview -->
                            <div class="mb-6">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-sm font-semibold text-gray-700">Team</span>
                                    <span class="text-xs text-gray-500">{{ $project->members_count ?? 0 }} members</span>
                                </div>
                                <div class="flex -space-x-2">
                                    @for($i = 0; $i < min(4, $project->members_count ?? 0); $i++)
                                        <div class="w-8 h-8 bg-gradient-to-br from-blue-400 to-purple-500 rounded-full border-2 border-white flex items-center justify-center text-white text-xs font-bold">
                                            {{ chr(65 + $i) }}
                                        </div>
                                    @endfor
                                    @if(($project->members_count ?? 0) > 4)
                                        <div class="w-8 h-8 bg-gray-400 rounded-full border-2 border-white flex items-center justify-center text-white text-xs font-bold">
                                            +{{ ($project->members_count ?? 0) - 4 }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="flex items-center space-x-3">
                                <!-- View Details -->
                                <a href="{{ route('projects.show', $project->id ?? 1) }}" 
                                   class="flex-1 px-4 py-3 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-semibold rounded-xl transition-all duration-300 hover:scale-105 focus:outline-none focus:ring-4 focus:ring-blue-500/20 text-center text-sm shadow-lg">
                                    View Details
                                </a>
                                
                                <!-- Edit Button -->
                                @can('update', $project ?? new stdClass())
                                <a href="{{ route('projects.edit', $project->id ?? 1) }}" 
                                   class="p-3 bg-emerald-100 hover:bg-emerald-200 text-emerald-700 rounded-xl transition-all duration-300 hover:scale-110 focus:outline-none focus:ring-4 focus:ring-emerald-500/20 shadow-md">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                @endcan
                                
                                <!-- Delete Button -->
                                @can('delete', $project ?? new stdClass())
                                <button onclick="confirmDelete({{ $project->id ?? 1 }})" 
                                        class="p-3 bg-red-100 hover:bg-red-200 text-red-700 rounded-xl transition-all duration-300 hover:scale-110 focus:outline-none focus:ring-4 focus:ring-red-500/20 shadow-md">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <!-- Empty State -->
                <div class="col-span-full flex flex-col items-center justify-center py-20">
                    <div class="relative">
                        <!-- Animated Background Circle -->
                        <div class="w-64 h-64 bg-gradient-to-br from-blue-100 via-purple-50 to-pink-100 rounded-full flex items-center justify-center mb-8 shadow-2xl">
                            <div class="w-48 h-48 bg-white/80 backdrop-blur-xl rounded-full flex items-center justify-center border border-white/60">
                                <svg class="w-20 h-20 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                            </div>
                        </div>
                        
                        <!-- Floating Elements -->
                        <div class="absolute top-8 left-8 w-4 h-4 bg-blue-400 rounded-full animate-bounce"></div>
                        <div class="absolute top-16 right-12 w-3 h-3 bg-purple-400 rounded-full animate-bounce" style="animation-delay: 0.5s;"></div>
                        <div class="absolute bottom-12 left-16 w-5 h-5 bg-pink-400 rounded-full animate-bounce" style="animation-delay: 1s;"></div>
                    </div>
                    
                    <div class="text-center max-w-lg">
                        <h3 class="text-3xl font-bold text-gray-800 mb-4">No Projects Yet</h3>
                        <p class="text-gray-600 mb-8 leading-relaxed text-lg">
                            Ready to bring your ideas to life? Create your first project and start building something amazing with your team.
                        </p>
                        
                        @can('create', App\Models\Project::class)
                        <a href="{{ route('projects.create') }}"
                           class="inline-flex items-center space-x-3 px-8 py-4 bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-600 hover:from-blue-700 hover:via-purple-700 hover:to-indigo-700 text-white font-bold rounded-2xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-blue-500/20">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            <span>Create Your First Project</span>
                        </a>
                        @endcan
                    </div>
                </div>
            @endforelse
        </div>
        
        <!-- Pagination -->
        @if(method_exists($projects ?? collect(), 'hasPages') && $projects->hasPages())
        <div class="flex justify-center mt-12">
            <div class="bg-white/60 backdrop-blur-2xl rounded-2xl shadow-xl border border-white/60 p-6">
                {{ $projects->links() }}
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="bg-white/90 backdrop-blur-2xl rounded-3xl shadow-2xl max-w-md w-full mx-4 overflow-hidden border border-white/60">
        <div class="bg-gradient-to-r from-red-500 to-pink-500 px-6 py-4">
            <h3 class="text-xl font-bold text-white flex items-center space-x-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.664-.833-2.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
                <span>Confirm Deletion</span>
            </h3>
        </div>
        <div class="p-6">
            <p class="text-gray-600 mb-6 leading-relaxed">Are you sure you want to delete this project? This action cannot be undone and will permanently remove all project data, tasks, and team assignments.</p>
            <div class="flex space-x-4">
                <button onclick="closeDeleteModal()" 
                        class="flex-1 px-4 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl transition-all duration-300 hover:scale-105 focus:outline-none focus:ring-4 focus:ring-gray-500/20">
                    Cancel
                </button>
                <form id="deleteForm" method="POST" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="w-full px-4 py-3 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-semibold rounded-xl transition-all duration-300 hover:scale-105 focus:outline-none focus:ring-4 focus:ring-red-500/20 shadow-lg">
                        Delete Project
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Custom CSS & JavaScript --}}
<style>
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.project-card:hover .bg-gradient-to-br {
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.9), rgba(168, 85, 247, 0.9), rgba(236, 72, 153, 0.9));
}

/* Scrollbar Styling */
::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: rgba(0, 0, 0, 0.1);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb {
    background: linear-gradient(to bottom, #3B82F6, #8B5CF6);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(to bottom, #2563EB, #7C3AED);
}

/* Advanced Animation Effects */
.project-card {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.project-card:hover {
    transform: translateY(-8px) scale(1.02);
}

/* Glassmorphism Enhancement */
.backdrop-blur-2xl {
    backdrop-filter: blur(40px);
}

/* Custom Gradient Text */
.gradient-text {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animationPlayState = 'running';
            }
        });
    }, observerOptions);

    document.querySelectorAll('.project-card').forEach(card => {
        observer.observe(card);
    });

    // Search functionality
    const searchInput = document.querySelector('input[x-model="searchQuery"]');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(function() {
            filterProjects();
        }, 300));
    }

    // Debounce function
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Filter projects function
    function filterProjects() {
        const searchTerm = searchInput.value.toLowerCase();
        const projectCards = document.querySelectorAll('.project-card');
        
        projectCards.forEach(card => {
            const projectName = card.querySelector('h3').textContent.toLowerCase();
            const projectDescription = card.querySelector('p').textContent.toLowerCase();
            
            if (projectName.includes(searchTerm) || projectDescription.includes(searchTerm)) {
                card.style.display = 'block';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            } else {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    if (card.style.opacity === '0') {
                        card.style.display = 'none';
                    }
                }, 300);
            }
        });
    }
});

// Delete modal functions
function confirmDelete(projectId) {
    const modal = document.getElementById('deleteModal');
    const form = document.getElementById('deleteForm');
    form.action = `/projects/${projectId}`;
    modal.classList.remove('hidden');
    
    // Add entrance animation
    setTimeout(() => {
        modal.querySelector('.bg-white\\/90').style.transform = 'scale(1)';
        modal.querySelector('.bg-white\\/90').style.opacity = '1';
    }, 10);
}

function closeDeleteModal() {
    const modal = document.getElementById('deleteModal');
    const modalContent = modal.querySelector('.bg-white\\/90');
    
    modalContent.style.transform = 'scale(0.95)';
    modalContent.style.opacity = '0';
    
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 200);
}

// Close modal on backdrop click
document.getElementById('deleteModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDeleteModal();
    }
    
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        document.querySelector('input[x-model="searchQuery"]')?.focus();
    }
});

// Add smooth scrolling
document.documentElement.style.scrollBehavior = 'smooth';

// Progress bar animation on scroll
const progressBars = document.querySelectorAll('.bg-gradient-to-r.from-blue-500.to-purple-500');
const progressObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const bar = entry.target;
            const width = bar.style.width;
            bar.style.width = '0%';
            setTimeout(() => {
                bar.style.width = width;
            }, 200);
        }
    });
}, { threshold: 0.5 });

progressBars.forEach(bar => {
    progressObserver.observe(bar);
});
</script>
@endsection