@extends('layouts.app')

@section('title', 'Cards Management')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-indigo-50 via-blue-50 to-cyan-50" x-data="cardsPageData()">
    <!-- Header Section - Responsive -->
    <div class="sticky top-0 z-30 backdrop-blur-xl bg-white/80 border-b border-white/20 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 lg:py-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
                <div class="flex-1 min-w-0">
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 truncate">Cards Management</h1>
                    <p class="text-sm sm:text-base text-gray-600 mt-1">Manage and track all your tasks across projects</p>
                </div>
                
                <div class="flex items-center gap-2 sm:gap-3 flex-shrink-0">
                    @if(Auth::user()->role !== 'member')
                        <a href="{{ route('cards.create') }}" 
                           class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-gradient-to-r from-indigo-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-indigo-600 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl flex-1 sm:flex-initial">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            <span class="hidden sm:inline">New Card</span>
                            <span class="sm:hidden">New</span>
                        </a>
                    @endif
                    
                    <button @click="toggleView()" 
                            class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-white text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors text-sm flex-1 sm:flex-initial">
                        <svg x-show="currentView === 'grid'" class="w-4 h-4 sm:w-5 sm:h-5 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                        </svg>
                        <svg x-show="currentView === 'list'" class="w-4 h-4 sm:w-5 sm:h-5 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        <span class="hidden sm:inline" x-text="currentView === 'grid' ? 'List' : 'Grid'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6">
    <!-- Member View Notice - Responsive -->
    @if(Auth::user()->role === 'member')
        <div class="mb-4 sm:mb-6 bg-gradient-to-r from-blue-50 to-indigo-50 backdrop-blur-sm rounded-xl border border-blue-200/50 shadow-lg p-4 sm:p-5">
            <div class="flex items-start sm:items-center space-x-3">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-blue-500 flex items-center justify-center">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm sm:text-base font-semibold text-blue-900">Member View</h3>
                    <p class="text-xs sm:text-sm text-blue-700 mt-0.5">You are viewing only cards that are assigned to you.</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Project Selection - Responsive -->
    @if($availableProjects->count() > 0)
        <div class="bg-white/70 backdrop-blur-sm rounded-xl border border-white/20 shadow-lg p-4 sm:p-6 mb-4 sm:mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
                <div class="flex-1 min-w-0">
                    <h2 class="text-lg sm:text-xl font-semibold mb-1 sm:mb-2">Current Project</h2>
                    <p class="text-sm sm:text-base text-gray-600 truncate">Showing cards from: 
                        <span class="font-semibold text-indigo-600">{{ $selectedProject ? $selectedProject->project_name : 'No Project Selected' }}</span>
                    </p>
                </div>
                <div class="flex-shrink-0">
                    <form method="GET" action="{{ route('cards.index') }}">
                        <select name="project_id" onchange="this.form.submit()" 
                                class="w-full sm:w-auto px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                            @foreach($availableProjects as $project)
                                <option value="{{ $project->id }}" {{ $selectedProjectId == $project->id ? 'selected' : '' }}>
                                    {{ $project->project_name }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Enhanced Statistics Dashboard - Responsive -->
    <div class="bg-white/70 backdrop-blur-sm rounded-xl border border-white/20 shadow-lg p-4 sm:p-6 mb-4 sm:mb-6">
        <h2 class="text-lg sm:text-xl font-semibold mb-3 sm:mb-4">Project Dashboard Statistics</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-8 gap-3 sm:gap-4">
            <!-- Total Cards -->
            <div class="bg-white/90 backdrop-blur-sm rounded-xl p-3 sm:p-4 border border-white/20 shadow-sm hover:shadow-lg transition-all hover:-translate-y-1 duration-200">
                <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-0">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center mx-auto sm:mx-0 flex-shrink-0">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                    <div class="sm:ml-3 text-center sm:text-left">
                        <p class="text-xs sm:text-sm font-medium text-gray-600">Total</p>
                        <p class="text-xl sm:text-2xl font-bold text-gray-900">{{ $cards->total() }}</p>
                    </div>
                </div>
            </div>
            
            <!-- Todo Cards -->
            <div class="bg-white/90 backdrop-blur-sm rounded-xl p-3 sm:p-4 border border-white/20 shadow-sm hover:shadow-lg transition-all hover:-translate-y-1 duration-200">
                <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-0">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-r from-gray-400 to-gray-600 rounded-lg flex items-center justify-center mx-auto sm:mx-0 flex-shrink-0">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <div class="sm:ml-3 text-center sm:text-left">
                        <p class="text-xs sm:text-sm font-medium text-gray-600">Todo</p>
                        <p class="text-xl sm:text-2xl font-bold text-gray-900">{{ $cards->where('status', 'todo')->count() }}</p>
                    </div>
                </div>
            </div>
            
            <!-- In Progress -->
            <div class="bg-white/90 backdrop-blur-sm rounded-xl p-3 sm:p-4 border border-white/20 shadow-sm hover:shadow-lg transition-all hover:-translate-y-1 duration-200">
                <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-0">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-r from-blue-400 to-blue-600 rounded-lg flex items-center justify-center mx-auto sm:mx-0 flex-shrink-0">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="sm:ml-3 text-center sm:text-left">
                        <p class="text-xs sm:text-sm font-medium text-gray-600">Progress</p>
                        <p class="text-xl sm:text-2xl font-bold text-blue-600">{{ $cards->where('status', 'in progress')->count() }}</p>
                    </div>
                </div>
            </div>
            
            <!-- Review -->
            <div class="bg-white/90 backdrop-blur-sm rounded-xl p-3 sm:p-4 border border-white/20 shadow-sm hover:shadow-lg transition-all hover:-translate-y-1 duration-200">
                <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-0">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-lg flex items-center justify-center mx-auto sm:mx-0 flex-shrink-0">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                    </div>
                    <div class="sm:ml-3 text-center sm:text-left">
                        <p class="text-xs sm:text-sm font-medium text-gray-600">Review</p>
                        <p class="text-xl sm:text-2xl font-bold text-yellow-600">{{ $cards->where('status', 'review')->count() }}</p>
                    </div>
                </div>
            </div>
            
            <!-- Done -->
            <div class="bg-white/90 backdrop-blur-sm rounded-xl p-3 sm:p-4 border border-white/20 shadow-sm hover:shadow-lg transition-all hover:-translate-y-1 duration-200">
                <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-0">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-r from-green-400 to-green-600 rounded-lg flex items-center justify-center mx-auto sm:mx-0 flex-shrink-0">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <div class="sm:ml-3 text-center sm:text-left">
                        <p class="text-xs sm:text-sm font-medium text-gray-600">Done</p>
                        <p class="text-xl sm:text-2xl font-bold text-green-600">{{ $cards->where('status', 'done')->count() }}</p>
                    </div>
                </div>
            </div>
            
            <!-- High Priority -->
            <div class="bg-white/90 backdrop-blur-sm rounded-xl p-3 sm:p-4 border border-white/20 shadow-sm hover:shadow-lg transition-all hover:-translate-y-1 duration-200">
                <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-0">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-r from-red-400 to-red-600 rounded-lg flex items-center justify-center mx-auto sm:mx-0 flex-shrink-0">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.35 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div class="sm:ml-3 text-center sm:text-left">
                        <p class="text-xs sm:text-sm font-medium text-gray-600">High</p>
                        <p class="text-xl sm:text-2xl font-bold text-red-600">{{ $cards->where('priority', 'high')->count() }}</p>
                    </div>
                </div>
            </div>
            
            <!-- Medium Priority -->
            <div class="bg-white/90 backdrop-blur-sm rounded-xl p-3 sm:p-4 border border-white/20 shadow-sm hover:shadow-lg transition-all hover:-translate-y-1 duration-200">
                <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-0">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-r from-yellow-400 to-yellow-600 rounded-lg flex items-center justify-center mx-auto sm:mx-0 flex-shrink-0">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <div class="sm:ml-3 text-center sm:text-left">
                        <p class="text-xs sm:text-sm font-medium text-gray-600">Medium</p>
                        <p class="text-xl sm:text-2xl font-bold text-yellow-600">{{ $cards->where('priority', 'medium')->count() }}</p>
                    </div>
                </div>
            </div>
            
            <!-- Low Priority -->
            <div class="bg-white/90 backdrop-blur-sm rounded-xl p-3 sm:p-4 border border-white/20 shadow-sm hover:shadow-lg transition-all hover:-translate-y-1 duration-200">
                <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-0">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-r from-green-400 to-green-600 rounded-lg flex items-center justify-center mx-auto sm:mx-0 flex-shrink-0">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <div class="sm:ml-3 text-center sm:text-left">
                        <p class="text-xs sm:text-sm font-medium text-gray-600">Low</p>
                        <p class="text-xl sm:text-2xl font-bold text-green-600">{{ $cards->where('priority', 'low')->count() }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section - Responsive -->
    <div class="bg-white/70 backdrop-blur-sm rounded-xl border border-white/20 shadow-lg p-4 sm:p-6 mb-4 sm:mb-6">
        <form method="GET" action="{{ route('cards.index') }}" class="space-y-4">
            <!-- Hidden input untuk preserve project_id -->
            <input type="hidden" name="project_id" value="{{ $selectedProjectId }}">
            
            <!-- Search Bar - Full Width on Mobile -->
            <div class="flex flex-col sm:flex-row gap-3 sm:gap-4">
                <div class="flex-1">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search Cards</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input type="text" 
                               name="search" 
                               id="search"
                               value="{{ request('search') }}"
                               placeholder="Search by title or description..."
                               class="block w-full pl-10 pr-3 py-2.5 sm:py-3 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200">
                    </div>
                </div>
                <div class="flex items-end">
                    <button type="submit" 
                            class="w-full sm:w-auto px-6 py-2.5 sm:py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-sm sm:text-base rounded-lg hover:from-indigo-700 hover:to-purple-700 transition-all shadow-md hover:shadow-lg">
                        <span class="hidden sm:inline">Search</span>
                        <svg class="w-5 h-5 sm:hidden mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Filter Dropdowns - Responsive Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" id="status" 
                            class="block w-full px-3 py-2.5 sm:py-3 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white">
                        <option value="">All Status</option>
                        <option value="todo" {{ request('status') === 'todo' ? 'selected' : '' }}>Todo</option>
                        <option value="in progress" {{ request('status') === 'in progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="review" {{ request('status') === 'review' ? 'selected' : '' }}>Review</option>
                        <option value="done" {{ request('status') === 'done' ? 'selected' : '' }}>Done</option>
                    </select>
                </div>

                <div>
                    <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                    <select name="priority" id="priority" 
                            class="block w-full px-3 py-2.5 sm:py-3 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white">
                        <option value="">All Priority</option>
                        <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                        <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                    </select>
                </div>

                <div>
                    <label for="board_id" class="block text-sm font-medium text-gray-700 mb-2">Board</label>
                    <select name="board_id" id="board_id" 
                            class="block w-full px-3 py-2.5 sm:py-3 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white">
                        <option value="">All Boards</option>
                        @foreach($availableBoards as $board)
                            <option value="{{ $board->id }}" {{ request('board_id') == $board->id ? 'selected' : '' }}>
                                {{ $board->board_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="sort" class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
                    <select name="sort" id="sort" 
                            class="block w-full px-3 py-2.5 sm:py-3 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white">
                        <option value="created_at" {{ request('sort') === 'created_at' ? 'selected' : '' }}>Created Date</option>
                        <option value="due_date" {{ request('sort') === 'due_date' ? 'selected' : '' }}>Due Date</option>
                        <option value="priority" {{ request('sort') === 'priority' ? 'selected' : '' }}>Priority</option>
                        <option value="status" {{ request('sort') === 'status' ? 'selected' : '' }}>Status</option>
                    </select>
                </div>
            </div>

            <!-- Action Buttons - Responsive -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-4 border-t border-gray-200">
                <div>
                    <a href="{{ route('cards.index') }}" 
                       class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800 font-medium transition-colors">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Clear All Filters
                    </a>
                </div>
                <div class="flex gap-2 sm:gap-3">
                    <button type="submit" 
                            class="flex-1 sm:flex-initial px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-indigo-700 hover:to-purple-700 transition-all shadow-md hover:shadow-lg">
                        Apply Filters
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Cards Container -->
    <div class="bg-white/70 backdrop-blur-sm rounded-xl border border-white/20 shadow-lg overflow-hidden">
        <!-- Header -->
        <div class="px-4 sm:px-6 py-4 border-b border-gray-200/50 bg-gradient-to-r from-gray-50/50 to-white/50">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <h3 class="text-base sm:text-lg font-semibold text-gray-900">
                    Cards <span class="text-indigo-600">({{ $cards->total() }} total)</span>
                </h3>
                <div class="text-xs sm:text-sm text-gray-600">
                    Showing {{ $cards->firstItem() ?? 0 }} - {{ $cards->lastItem() ?? 0 }} of {{ $cards->total() }} results
                </div>
            </div>
        </div>
        
        <div class="p-4 sm:p-6">
            @if($cards->count() > 0)
                <!-- Grid View - Responsive -->
                <div x-show="currentView === 'grid'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6" x-transition>
                    @foreach($cards as $card)
                        <div class="group relative bg-white rounded-2xl border border-gray-200/60 shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden backdrop-blur-sm hover:border-indigo-200 hover:scale-[1.02] hover:-translate-y-1">
                            <!-- Priority Stripe -->
                            <div class="absolute top-0 left-0 right-0 h-1 
                                @if($card->priority === 'high') bg-gradient-to-r from-red-500 to-red-600
                                @elseif($card->priority === 'medium') bg-gradient-to-r from-yellow-500 to-orange-500  
                                @else bg-gradient-to-r from-green-500 to-emerald-500 @endif">
                            </div>
                            
                            <!-- Card Header -->
                            <div class="p-6 pb-4">
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex-1 pr-3">
                                        <h3 class="text-xl font-bold text-gray-900 leading-tight mb-2 group-hover:text-indigo-900 transition-colors">
                                            {{ $card->card_title }}
                                        </h3>
                                        
                                        <!-- Status and Priority Badges -->
                                        <div class="flex flex-wrap items-center gap-2">
                                            <!-- Status Badge with Enhanced Design -->
                                            @if($card->status === 'done')
                                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-gradient-to-r from-green-100 to-emerald-100 text-green-800 border border-green-200/50 shadow-sm">
                                                    <svg class="w-3 h-3 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    Completed
                                                </span>
                                            @elseif($card->status === 'in progress')
                                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-gradient-to-r from-blue-100 to-indigo-100 text-blue-800 border border-blue-200/50 shadow-sm">
                                                    <svg class="w-3 h-3 mr-1.5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                                    </svg>
                                                    In Progress
                                                </span>
                                            @elseif($card->status === 'review')
                                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-gradient-to-r from-yellow-100 to-orange-100 text-orange-800 border border-orange-200/50 shadow-sm">
                                                    <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    Review
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-gradient-to-r from-gray-100 to-slate-100 text-gray-700 border border-gray-200/50 shadow-sm">
                                                    <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                                    </svg>
                                                    To Do
                                                </span>
                                            @endif

                                            <!-- Priority Badge with Icon -->
                                            @if($card->priority === 'high')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-gradient-to-r from-red-500 to-red-600 text-white shadow-md">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    HIGH
                                                </span>
                                            @elseif($card->priority === 'medium')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-gradient-to-r from-yellow-500 to-yellow-600 text-white  shadow-md">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    MED
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-gradient-to-r from-green-500 to-emerald-500 text-white shadow-md">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M5.293 7.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L10 4.414 6.707 7.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    LOW
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Description -->
                                @if($card->description)
                                    <div class="mb-4">
                                        <p class="text-gray-600 text-sm leading-relaxed line-clamp-3">
                                            {{ $card->description }}
                                        </p>
                                    </div>
                                @endif
                                
                                <!-- Card Metadata -->
                                <div class="space-y-3">
                                    @if($card->due_date)
                                        <div class="flex items-center space-x-2 text-sm">
                                            <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gradient-to-r from-indigo-100 to-purple-100">
                                                <svg class="w-4 h-4 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-xs font-medium text-gray-500">Due Date</p>
                                                <p class="font-semibold text-gray-900">{{ $card->due_date->format('d M Y') }}</p>
                                            </div>
                                        </div>
                                    @endif

                                    @if($card->creator)
                                        <div class="flex items-center space-x-2 text-sm">
                                            <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gradient-to-r from-emerald-100 to-teal-100">
                                                <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-xs font-medium text-gray-500">Created by</p>
                                                <p class="font-semibold text-gray-900">{{ $card->creator->full_name ?? 'Unknown' }}</p>
                                            </div>
                                        </div>
                                    @endif

                                    @if($card->board)
                                        <div class="flex items-center space-x-2 text-sm">
                                            <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gradient-to-r from-blue-100 to-cyan-100">
                                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-xs font-medium text-gray-500">Board</p>
                                                <p class="font-semibold text-gray-900">{{ $card->board->board_name ?? 'No Board' }}</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Card Footer with Enhanced Actions -->
                            <div class="px-6 py-4 bg-gradient-to-r from-gray-50/80 to-slate-50/80 border-t border-gray-100/50 backdrop-blur-sm">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-1">
                                        <a href="{{ route('cards.show', $card) }}"
                                           class="inline-flex items-center px-3 py-2 text-xs font-semibold text-blue-700 bg-blue-50 rounded-lg hover:bg-blue-100 hover:text-blue-800 transition-all duration-200 border border-blue-200/50">
                                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                            View
                                        </a>
                                        <a href="{{ route('cards.edit', $card) }}"
                                           class="inline-flex items-center px-3 py-2 text-xs font-semibold text-emerald-700 bg-emerald-50 rounded-lg hover:bg-emerald-100 hover:text-emerald-800 transition-all duration-200 border border-emerald-200/50">
                                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                            Edit
                                        </a>
                                    </div>

                                    <form action="{{ route('cards.destroy', $card) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                onclick="return confirm('Are you sure you want to delete this card?')"
                                                class="inline-flex items-center px-3 py-2 text-xs font-semibold text-red-700 bg-red-50 rounded-lg hover:bg-red-100 hover:text-red-800 transition-all duration-200 border border-red-200/50 group">
                                            <svg class="w-3.5 h-3.5 mr-1.5 group-hover:animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- List View - Professional Table Design -->
                <div x-show="currentView === 'list'" class="space-y-3" x-transition>
                    @foreach($cards as $card)
                        <div class="group bg-white rounded-xl border border-gray-200/60 shadow-md hover:shadow-lg transition-all duration-300 overflow-hidden backdrop-blur-sm hover:border-indigo-200">
                            <!-- Priority Stripe -->
                            <div class="h-1 
                                @if($card->priority === 'high') bg-gradient-to-r from-red-500 to-red-600
                                @elseif($card->priority === 'medium') bg-gradient-to-r from-yellow-500 to-orange-500  
                                @else bg-gradient-to-r from-green-500 to-emerald-500 @endif">
                            </div>
                            
                            <div class="p-6">
                                <div class="flex items-start justify-between">
                                    <!-- Main Content -->
                                    <div class="flex-1 pr-6">
                                        <!-- Header Row -->
                                        <div class="flex items-start justify-between mb-3">
                                            <div class="flex-1">
                                                <h3 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-indigo-900 transition-colors">
                                                    {{ $card->card_title }}
                                                </h3>
                                                
                                                <!-- Badges Row -->
                                                <div class="flex flex-wrap items-center gap-2 mb-3">
                                                    <!-- Status Badge -->
                                                    @if($card->status === 'done')
                                                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-gradient-to-r from-green-100 to-emerald-100 text-green-800 border border-green-200/50">
                                                            <svg class="w-3 h-3 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                            </svg>
                                                            Completed
                                                        </span>
                                                    @elseif($card->status === 'in progress')
                                                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-gradient-to-r from-blue-100 to-indigo-100 text-blue-800 border border-blue-200/50">
                                                            <svg class="w-3 h-3 mr-1.5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                                            </svg>
                                                            In Progress
                                                        </span>
                                                    @elseif($card->status === 'review')
                                                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-gradient-to-r from-yellow-100 to-orange-100 text-orange-800 border border-orange-200/50">
                                                            <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                            </svg>
                                                            Review
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-gradient-to-r from-gray-100 to-slate-100 text-gray-700 border border-gray-200/50">
                                                            <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                                            </svg>
                                                            To Do
                                                        </span>
                                                    @endif

                                                    <!-- Priority Badge -->
                                                    @if($card->priority === 'high')
                                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-gradient-to-r from-red-500 to-red-600 text-white">
                                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                                            </svg>
                                                            HIGH PRIORITY
                                                        </span>
                                                    @elseif($card->priority === 'medium')
                                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-gradient-to-r from-yellow-500 to-orange-500 text-white">
                                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                                                            </svg>
                                                            MEDIUM PRIORITY
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-gradient-to-r from-green-500 to-emerald-500 text-white">
                                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M5.293 7.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L10 4.414 6.707 7.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                                            </svg>
                                                            LOW PRIORITY
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Description -->
                                        @if($card->description)
                                            <div class="mb-4">
                                                <p class="text-gray-600 text-sm leading-relaxed line-clamp-2">
                                                    {{ $card->description }}
                                                </p>
                                            </div>
                                        @endif
                                        
                                        <!-- Metadata Row -->
                                        <div class="flex flex-wrap items-center gap-4 text-sm">
                                            @if($card->due_date)
                                                <div class="flex items-center space-x-2">
                                                    <div class="flex items-center justify-center w-6 h-6 rounded-md bg-gradient-to-r from-indigo-100 to-purple-100">
                                                        <svg class="w-3 h-3 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                                                        </svg>
                                                    </div>
                                                    <span class="font-semibold text-gray-900">Due: {{ $card->due_date->format('d M Y') }}</span>
                                                </div>
                                            @endif

                                            @if($card->creator)
                                                <div class="flex items-center space-x-2">
                                                    <div class="flex items-center justify-center w-6 h-6 rounded-md bg-gradient-to-r from-emerald-100 to-teal-100">
                                                        <svg class="w-3 h-3 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                                        </svg>
                                                    </div>
                                                    <span class="text-gray-700">Created by: <span class="font-semibold text-gray-900">{{ $card->creator->full_name ?? 'Unknown' }}</span></span>
                                                </div>
                                            @endif

                                            @if($card->board)
                                                <div class="flex items-center space-x-2">
                                                    <div class="flex items-center justify-center w-6 h-6 rounded-md bg-gradient-to-r from-blue-100 to-cyan-100">
                                                        <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                                        </svg>
                                                    </div>
                                                    <span class="text-gray-700">Board: <span class="font-semibold text-gray-900">{{ $card->board->board_name ?? 'No Board' }}</span></span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="flex items-start space-x-2 ml-4">
                                        <a href="{{ route('cards.show', $card) }}"
                                           class="inline-flex items-center px-4 py-2.5 text-sm font-semibold text-blue-700 bg-blue-50 rounded-xl hover:bg-blue-100 hover:text-blue-800 transition-all duration-200 border border-blue-200/50 group">
                                            <svg class="w-4 h-4 mr-2 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                            View Details
                                        </a>
                                        
                                        <a href="{{ route('cards.edit', $card) }}"
                                           class="inline-flex items-center px-4 py-2.5 text-sm font-semibold text-emerald-700 bg-emerald-50 rounded-xl hover:bg-emerald-100 hover:text-emerald-800 transition-all duration-200 border border-emerald-200/50 group">
                                            <svg class="w-4 h-4 mr-2 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                            Edit Card
                                        </a>
                                        
                                        <form action="{{ route('cards.destroy', $card) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    onclick="return confirm('Are you sure you want to delete this card?')"
                                                    class="inline-flex items-center px-4 py-2.5 text-sm font-semibold text-red-700 bg-red-50 rounded-xl hover:bg-red-100 hover:text-red-800 transition-all duration-200 border border-red-200/50 group">
                                                <svg class="w-4 h-4 mr-2 group-hover:animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">
                        @if(Auth::user()->role === 'member')
                            No assigned cards found
                        @else
                            No cards found
                        @endif
                    </h3>
                    <p class="text-gray-600 mb-6">
                        @if(Auth::user()->role === 'member')
                            You don't have any cards assigned to you yet, or try adjusting your search or filter criteria.
                        @else
                            Try adjusting your search or filter criteria.
                        @endif
                    </p>
                    @if(Auth::user()->role !== 'member')
                        <a href="{{ route('cards.create') }}" 
                           class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Create First Card
                        </a>
                    @endif
                </div>
            @endif
        </div>
        
        <!-- Custom Pagination -->
        @if($cards->hasPages())
            <div class="px-4 sm:px-6 py-4 sm:py-5 border-t border-gray-200/50 bg-gradient-to-r from-gray-50/50 to-white/50">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <!-- Pagination Info -->
                    <div class="text-xs sm:text-sm text-gray-600 order-2 sm:order-1">
                        Showing <span class="font-semibold text-gray-900">{{ $cards->firstItem() ?? 0 }}</span> to 
                        <span class="font-semibold text-gray-900">{{ $cards->lastItem() ?? 0 }}</span> of 
                        <span class="font-semibold text-gray-900">{{ $cards->total() }}</span> results
                    </div>
                    
                    <!-- Pagination Links -->
                    <nav class="inline-flex items-center space-x-1 sm:space-x-2 order-1 sm:order-2" aria-label="Pagination">
                        {{-- Previous Button --}}
                        @if ($cards->onFirstPage())
                            <span class="px-2 sm:px-3 py-2 text-xs sm:text-sm font-medium text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </span>
                        @else
                            <a href="{{ $cards->appends(request()->query())->previousPageUrl() }}" 
                               class="px-2 sm:px-3 py-2 text-xs sm:text-sm font-medium text-indigo-600 bg-white border border-gray-300 rounded-lg hover:bg-indigo-50 hover:border-indigo-300 transition-all duration-200 shadow-sm hover:shadow">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </a>
                        @endif

                        {{-- Page Numbers --}}
                        @php
                            $start = max($cards->currentPage() - 1, 1);
                            $end = min($start + 2, $cards->lastPage());
                            $start = max($end - 2, 1);
                        @endphp

                        @if($start > 1)
                            <a href="{{ $cards->appends(request()->query())->url(1) }}" 
                               class="hidden sm:inline-flex px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-indigo-50 hover:border-indigo-300 hover:text-indigo-600 transition-all duration-200 shadow-sm hover:shadow">
                                1
                            </a>
                            @if($start > 2)
                                <span class="hidden sm:inline-flex px-2 py-2 text-sm text-gray-500">...</span>
                            @endif
                        @endif

                        @for ($page = $start; $page <= $end; $page++)
                            @if ($page == $cards->currentPage())
                                <span class="px-3 py-2 text-xs sm:text-sm font-bold text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg shadow-md">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $cards->appends(request()->query())->url($page) }}" 
                                   class="hidden sm:inline-flex px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-indigo-50 hover:border-indigo-300 hover:text-indigo-600 transition-all duration-200 shadow-sm hover:shadow">
                                    {{ $page }}
                                </a>
                            @endif
                        @endfor

                        @if($end < $cards->lastPage())
                            @if($end < $cards->lastPage() - 1)
                                <span class="hidden sm:inline-flex px-2 py-2 text-sm text-gray-500">...</span>
                            @endif
                            <a href="{{ $cards->appends(request()->query())->url($cards->lastPage()) }}" 
                               class="hidden sm:inline-flex px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-indigo-50 hover:border-indigo-300 hover:text-indigo-600 transition-all duration-200 shadow-sm hover:shadow">
                                {{ $cards->lastPage() }}
                            </a>
                        @endif

                        {{-- Next Button --}}
                        @if ($cards->hasMorePages())
                            <a href="{{ $cards->appends(request()->query())->nextPageUrl() }}" 
                               class="px-2 sm:px-3 py-2 text-xs sm:text-sm font-medium text-indigo-600 bg-white border border-gray-300 rounded-lg hover:bg-indigo-50 hover:border-indigo-300 transition-all duration-200 shadow-sm hover:shadow">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        @else
                            <span class="px-2 sm:px-3 py-2 text-xs sm:text-sm font-medium text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </span>
                        @endif

                        {{-- Mobile: Show current page info --}}
                        <span class="sm:hidden px-3 py-2 text-xs font-medium text-gray-600 bg-gray-100 rounded-lg">
                            {{ $cards->currentPage() }} / {{ $cards->lastPage() }}
                        </span>
                    </nav>
                </div>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function cardsPageData() {
    return {
        currentView: 'grid',
        selectedCard: null,
        editingCard: null,
        showCreateModal: false,

        init() {
            this.currentView = localStorage.getItem('cardsView') || 'grid';
        },

        toggleView() {
            this.currentView = this.currentView === 'grid' ? 'list' : 'grid';
            localStorage.setItem('cardsView', this.currentView);
        },

        closeModals() {
            this.selectedCard = null;
            this.editingCard = null;
            this.showCreateModal = false;
        },

        refreshPage() {
            window.location.reload();
        }
    }
}
</script>
@endpush

@endsection