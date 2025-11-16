@extends('layouts.admin')

@section('title', 'Statistics & Analytics')

@section('content')
<div class="p-4 sm:p-6 lg:p-8">
    <div class="max-w-7xl mx-auto">
        
        <!-- Header -->
        <div class="mb-6 sm:mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">Statistics & Analytics</h1>
                <p class="text-gray-600">Comprehensive system performance metrics</p>
            </div>

            <!-- Time Range Filter -->
            <form method="GET" class="flex items-center gap-2">
                <select name="range" onchange="this.form.submit()" 
                        class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                    <option value="7" {{ $range == 7 ? 'selected' : '' }}>Last 7 days</option>
                    <option value="30" {{ $range == 30 ? 'selected' : '' }}>Last 30 days</option>
                    <option value="90" {{ $range == 90 ? 'selected' : '' }}>Last 90 days</option>
                    <option value="365" {{ $range == 365 ? 'selected' : '' }}>Last year</option>
                </select>
            </form>
        </div>

        <!-- Overall Statistics Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <!-- Total Projects -->
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-medium opacity-90">Total Projects</h3>
                    <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                    </svg>
                </div>
                <p class="text-3xl font-bold">{{ $overallStats['total_projects'] }}</p>
                <p class="text-sm mt-2 opacity-80">{{ $overallStats['active_projects'] }} active ({{ $overallStats['project_activity_rate'] }}%)</p>
            </div>

            <!-- Total Users -->
            <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-medium opacity-90">Total Users</h3>
                    <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
                <p class="text-3xl font-bold">{{ $overallStats['total_users'] }}</p>
                <p class="text-sm mt-2 opacity-80">{{ $overallStats['total_admins'] }} admins</p>
            </div>

            <!-- Total Tasks -->
            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-medium opacity-90">Total Tasks</h3>
                    <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <p class="text-3xl font-bold">{{ $overallStats['total_tasks'] }}</p>
                <p class="text-sm mt-2 opacity-80">{{ $overallStats['completed_tasks'] }} completed ({{ $overallStats['task_completion_rate'] }}%)</p>
            </div>

            <!-- Time Tracked -->
            <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl shadow-lg p-6 text-white">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-medium opacity-90">Time Tracked</h3>
                    <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <p class="text-3xl font-bold">{{ $timeStats['total_hours'] }}h</p>
                <p class="text-sm mt-2 opacity-80">{{ $timeStats['avg_per_task'] }} min avg/task</p>
            </div>
        </div>

        <!-- Charts Row 1 -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Tasks by Status -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Tasks by Status</h3>
                <div class="space-y-4">
                    @php
                        $statusColors = [
                            'todo' => ['bg' => 'bg-gray-500', 'text' => 'text-gray-700'],
                            'in progress' => ['bg' => 'bg-blue-500', 'text' => 'text-blue-700'],
                            'review' => ['bg' => 'bg-yellow-500', 'text' => 'text-yellow-700'],
                            'done' => ['bg' => 'bg-green-500', 'text' => 'text-green-700'],
                        ];
                        $totalTasks = array_sum($tasksByStatus);
                    @endphp

                    @foreach($tasksByStatus as $status => $count)
                        @php
                            $percentage = $totalTasks > 0 ? round(($count / $totalTasks) * 100, 1) : 0;
                            $color = $statusColors[$status] ?? ['bg' => 'bg-gray-500', 'text' => 'text-gray-700'];
                        @endphp
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium {{ $color['text'] }} capitalize">{{ $status }}</span>
                                <span class="text-sm text-gray-600">{{ $count }} ({{ $percentage }}%)</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="{{ $color['bg'] }} h-2 rounded-full transition-all" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Tasks by Priority -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Tasks by Priority</h3>
                <div class="space-y-4">
                    @php
                        $priorityColors = [
                            'low' => ['bg' => 'bg-green-500', 'text' => 'text-green-700'],
                            'medium' => ['bg' => 'bg-yellow-500', 'text' => 'text-yellow-700'],
                            'high' => ['bg' => 'bg-red-500', 'text' => 'text-red-700'],
                        ];
                        $totalPriority = array_sum($tasksByPriority);
                    @endphp

                    @foreach($tasksByPriority as $priority => $count)
                        @php
                            $percentage = $totalPriority > 0 ? round(($count / $totalPriority) * 100, 1) : 0;
                            $color = $priorityColors[$priority] ?? ['bg' => 'bg-gray-500', 'text' => 'text-gray-700'];
                        @endphp
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium {{ $color['text'] }} capitalize">{{ $priority }}</span>
                                <span class="text-sm text-gray-600">{{ $count }} ({{ $percentage }}%)</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="{{ $color['bg'] }} h-2 rounded-full transition-all" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Top Users & Projects -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Top Active Users -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Top Active Users</h3>
                    <p class="text-sm text-gray-600 mt-1">Most tasks created in selected period</p>
                </div>
                <div class="divide-y divide-gray-200">
                    @forelse($topUsers as $index => $user)
                        <div class="p-4 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center gap-4">
                                <div class="flex-shrink-0 text-gray-400 font-bold text-sm w-6">
                                    #{{ $index + 1 }}
                                </div>
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold flex-shrink-0">
                                    {{ $user['avatar'] }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $user['name'] }}</p>
                                    <p class="text-xs text-gray-500 truncate">{{ $user['email'] }}</p>
                                </div>
                                <div class="flex-shrink-0">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $user['tasks_created'] }} tasks
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-gray-500">
                            No user data available
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Most Active Projects -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Most Active Projects</h3>
                    <p class="text-sm text-gray-600 mt-1">Projects with most tasks</p>
                </div>
                <div class="divide-y divide-gray-200">
                    @forelse($topProjects as $index => $project)
                        <div class="p-4 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center gap-4">
                                <div class="flex-shrink-0 text-gray-400 font-bold text-sm w-6">
                                    #{{ $index + 1 }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $project['name'] }}</p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ $project['members'] }} members • 
                                        <span class="capitalize {{ \Carbon\Carbon::parse($project['deadline'])->isFuture() ? 'text-green-600' : 'text-red-600' }}">
                                            Deadline: {{ \Carbon\Carbon::parse($project['deadline'])->format('d M Y') }}
                                        </span>
                                    </p>
                                </div>
                                <div class="flex-shrink-0">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ $project['tasks'] }} tasks
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-gray-500">
                            No project data available
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Additional Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Comments Stats -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Comments</h3>
                    <svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                </div>
                <p class="text-3xl font-bold text-gray-900 mb-2">{{ $commentsStats['total'] }}</p>
                <p class="text-sm text-gray-600">{{ $commentsStats['recent'] }} in period</p>
                <p class="text-sm text-gray-600">{{ $commentsStats['avg_per_task'] }} avg per task</p>
            </div>

            <!-- Time Tracking Details -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Time Tracking</h3>
                    <svg class="w-8 h-8 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <p class="text-3xl font-bold text-gray-900 mb-2">{{ $timeStats['total_hours'] }}h</p>
                <p class="text-sm text-gray-600">Total logged</p>
                @if($timeStats['most_tracked_user'])
                    <p class="text-sm text-gray-600 mt-2">
                        Top: {{ $timeStats['most_tracked_user']->user?->full_name ?? 'N/A' }}
                    </p>
                @endif
            </div>

            <!-- Task Completion -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Completion Rate</h3>
                    <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <p class="text-3xl font-bold text-gray-900 mb-2">{{ $overallStats['task_completion_rate'] }}%</p>
                <p class="text-sm text-gray-600">{{ $overallStats['completed_tasks'] }} of {{ $overallStats['total_tasks'] }} tasks</p>
                <p class="text-sm text-gray-600">{{ $overallStats['in_progress_tasks'] }} in progress</p>
            </div>
        </div>

    </div>
</div>
@endsection
