@extends('layouts.admin')

@section('title', 'Activity Logs')

@section('content')
<div class="p-4 sm:p-6 lg:p-8">
    <div class="max-w-7xl mx-auto">
        
        <!-- Header -->
        <div class="mb-6 sm:mb-8">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">Activity Logs</h1>
            <p class="text-gray-600">Monitor all system activities and user actions</p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Total</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total_activities'] }}</p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Today</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['today_activities'] }}</p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">This Week</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['this_week'] }}</p>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">This Month</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['this_month'] }}</p>
                    </div>
                    <div class="bg-orange-100 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6 mb-6">
            <form method="GET" action="{{ route('admin.activity-logs') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Search -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                        <input type="text" name="search" value="{{ $filters['search'] }}" 
                               placeholder="Search activities..." 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <!-- Activity Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Activity Type</label>
                        <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="all" {{ $filters['type'] === 'all' ? 'selected' : '' }}>All Types</option>
                            <option value="project_created" {{ $filters['type'] === 'project_created' ? 'selected' : '' }}>Projects</option>
                            <option value="card_created" {{ $filters['type'] === 'card_created' ? 'selected' : '' }}>Tasks</option>
                            <option value="comment_added" {{ $filters['type'] === 'comment_added' ? 'selected' : '' }}>Comments</option>
                            <option value="user_registered" {{ $filters['type'] === 'user_registered' ? 'selected' : '' }}>Users</option>
                        </select>
                    </div>

                    <!-- User Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">User</label>
                        <select name="user" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">All Users</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ $filters['user'] == $user->id ? 'selected' : '' }}>
                                    {{ $user->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Date Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                        <input type="date" name="date" value="{{ $filters['date'] }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                        Apply Filters
                    </button>
                    <a href="{{ route('admin.activity-logs') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Activity Timeline -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-4 sm:p-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Activity Timeline</h2>
            </div>

            <div class="divide-y divide-gray-200">
                @forelse($activities as $activity)
                    <div class="p-4 sm:p-6 hover:bg-gray-50 transition-colors">
                        <div class="flex gap-4">
                            <!-- Icon -->
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 rounded-full bg-{{ $activity['color'] }}-100 flex items-center justify-center">
                                    @if($activity['icon'] === 'folder-plus')
                                        <svg class="w-5 h-5 text-{{ $activity['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path>
                                        </svg>
                                    @elseif($activity['icon'] === 'clipboard-list')
                                        <svg class="w-5 h-5 text-{{ $activity['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                        </svg>
                                    @elseif($activity['icon'] === 'chat-alt')
                                        <svg class="w-5 h-5 text-{{ $activity['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5 text-{{ $activity['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                        </svg>
                                    @endif
                                </div>
                            </div>

                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1">
                                        <p class="text-sm font-semibold text-gray-900">{{ $activity['title'] }}</p>
                                        <p class="text-sm text-gray-600 mt-1">{{ $activity['description'] }}</p>
                                        
                                        <!-- User Info -->
                                        <div class="flex items-center gap-2 mt-2">
                                            <div class="w-6 h-6 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold">
                                                {{ substr($activity['user']?->full_name ?? 'System', 0, 1) }}
                                            </div>
                                            <span class="text-xs text-gray-500">{{ $activity['user']?->full_name ?? 'System' }}</span>
                                        </div>

                                        <!-- Metadata -->
                                        @if(isset($activity['metadata']['project']))
                                            <p class="text-xs text-gray-500 mt-1">
                                                Project: <span class="font-medium">{{ $activity['metadata']['project'] }}</span>
                                            </p>
                                        @endif
                                    </div>

                                    <!-- Timestamp -->
                                    <div class="flex-shrink-0 text-right">
                                        <p class="text-xs text-gray-500">{{ $activity['timestamp']->diffForHumans() }}</p>
                                        <p class="text-xs text-gray-400 mt-1">{{ $activity['timestamp']->format('M d, Y H:i') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-12 text-center">
                        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="text-gray-500 text-lg font-medium">No activities found</p>
                        <p class="text-gray-400 text-sm mt-1">Try adjusting your filters</p>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($pagination['last_page'] > 1)
                <div class="p-4 sm:p-6 border-t border-gray-200 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-600">
                            Showing {{ ($pagination['current_page'] - 1) * $pagination['per_page'] + 1 }} 
                            to {{ min($pagination['current_page'] * $pagination['per_page'], $pagination['total']) }} 
                            of {{ $pagination['total'] }} activities
                        </div>

                        <div class="flex gap-2">
                            @if($pagination['current_page'] > 1)
                                <a href="?page={{ $pagination['current_page'] - 1 }}&type={{ $filters['type'] }}&user={{ $filters['user'] }}&date={{ $filters['date'] }}&search={{ $filters['search'] }}" 
                                   class="px-3 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm font-medium">
                                    Previous
                                </a>
                            @endif

                            @if($pagination['current_page'] < $pagination['last_page'])
                                <a href="?page={{ $pagination['current_page'] + 1 }}&type={{ $filters['type'] }}&user={{ $filters['user'] }}&date={{ $filters['date'] }}&search={{ $filters['search'] }}" 
                                   class="px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                                    Next
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
