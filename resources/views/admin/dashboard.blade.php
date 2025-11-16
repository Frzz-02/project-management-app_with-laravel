@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50">
    <div class="max-w-[1920px] mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Header Section -->
        <div class="mb-8 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h1 class="text-4xl font-bold text-gray-900 flex items-center gap-3">
                    <span class="text-5xl">📊</span>
                    <span>Dashboard Admin</span>
                </h1>
                <p class="mt-2 text-sm text-gray-600">
                    Selamat datang kembali! Berikut adalah yang terjadi dengan proyek Anda hari ini.
                </p>
            </div>
            
            <!-- Quick Actions -->
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('projects.create') }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors shadow-md hover:shadow-lg">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Buat Proyek
                </a>
                <a href="{{ route('reports.index') }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors shadow-sm">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    Lihat Laporan
                </a>
                <button onclick="window.print()" 
                        class="inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors shadow-sm">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Ekspor
                </button>
            </div>
        </div>

        @isset($stats)
        <!-- 1️⃣ Stats Cards Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6 mb-8">
            
            <!-- Total Projects Card -->
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-transform">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="text-blue-100 text-xs uppercase tracking-wide font-semibold">Total Projects</p>
                        <p class="text-4xl font-bold mt-2">{{ $stats['total_projects'] ?? 0 }}</p>
                        @if(isset($stats['project_growth']) && $stats['project_growth'] > 0)
                        <p class="text-blue-100 text-xs mt-2 flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"></path>
                            </svg>
                            +{{ $stats['project_growth'] }}% this month
                        </p>
                        @endif
                    </div>
                    <div class="bg-blue-400 bg-opacity-30 rounded-full p-3">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Active Users Card -->
            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-transform">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="text-green-100 text-xs uppercase tracking-wide font-semibold">Active Users</p>
                        <p class="text-4xl font-bold mt-2">{{ $stats['active_users'] ?? 0 }}</p>
                        <p class="text-green-100 text-xs mt-2">
                            {{ $stats['user_activity_rate'] ?? 0 }}% activity rate
                        </p>
                    </div>
                    <div class="bg-green-400 bg-opacity-30 rounded-full p-3">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Tasks Completed Card -->
            <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-transform">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="text-purple-100 text-xs uppercase tracking-wide font-semibold">Tasks Done</p>
                        <p class="text-4xl font-bold mt-2">{{ $stats['done_tasks'] ?? 0 }}</p>
                        <p class="text-purple-100 text-xs mt-2">
                            {{ $stats['completion_rate'] ?? 0 }}% completion rate
                        </p>
                    </div>
                    <div class="bg-purple-400 bg-opacity-30 rounded-full p-3">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Overdue Tasks Card -->
            <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-transform">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="text-red-100 text-xs uppercase tracking-wide font-semibold">Overdue Tasks</p>
                        <p class="text-4xl font-bold mt-2">{{ $stats['overdue_tasks'] ?? 0 }}</p>
                        <p class="text-red-100 text-xs mt-2">
                            {{ $stats['due_soon_tasks'] ?? 0 }} due soon (7 days)
                        </p>
                    </div>
                    <div class="bg-red-400 bg-opacity-30 rounded-full p-3">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Work Hours Card -->
            <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-transform">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="text-orange-100 text-xs uppercase tracking-wide font-semibold">Work Hours</p>
                        <p class="text-4xl font-bold mt-2">{{ $stats['work_hours_this_week'] ?? 0 }}h</p>
                        <p class="text-orange-100 text-xs mt-2">
                            This week
                        </p>
                    </div>
                    <div class="bg-orange-400 bg-opacity-30 rounded-full p-3">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Project Health Card -->
            <div class="bg-gradient-to-br from-teal-500 to-teal-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-transform">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="text-teal-100 text-xs uppercase tracking-wide font-semibold">Project Health</p>
                        <p class="text-4xl font-bold mt-2">{{ $stats['project_health'] ?? 0 }}%</p>
                        <p class="text-teal-100 text-xs mt-2">
                            {{ $stats['healthy_projects'] ?? 0 }}/{{ $stats['total_projects'] ?? 0 }} on track
                        </p>
                    </div>
                    <div class="bg-teal-400 bg-opacity-30 rounded-full p-3">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        @endisset

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8 mb-8">
            
            <!-- Left Column (2/3 width) -->
            <div class="xl:col-span-2 space-y-8">
                
                @isset($projectsAtRisk)
                <!-- 2️⃣ Projects at Risk Section -->
                <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                            <span>🚨</span> Projects at Risk
                        </h2>
                        <span class="px-3 py-1 bg-red-100 text-red-800 text-sm font-medium rounded-full">
                            {{ count($projectsAtRisk) }} projects
                        </span>
                    </div>
                    
                    @if(count($projectsAtRisk) > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deadline</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Overdue Tasks</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($projectsAtRisk as $project)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ $project['name'] }}</div>
                                                <div class="text-xs text-gray-500">by {{ $project['creator'] }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($project['risk_level'] === 'critical')
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                🔴 Critical
                                            </span>
                                        @elseif($project['risk_level'] === 'high')
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800">
                                                🟠 High Risk
                                            </span>
                                        @else
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                🟡 Warning
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-full bg-gray-200 rounded-full h-2 mr-2">
                                                <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $project['progress'] }}%"></div>
                                            </div>
                                            <span class="text-sm font-medium text-gray-900">{{ $project['progress'] }}%</span>
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">{{ $project['done_cards'] }}/{{ $project['total_cards'] }} tasks</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $project['deadline'] }}</div>
                                        @if(isset($project['days_overdue']))
                                            <div class="text-xs text-red-600 font-medium">{{ $project['days_overdue'] }} days overdue</div>
                                        @elseif(isset($project['days_until_deadline']))
                                            <div class="text-xs text-orange-600 font-medium">{{ $project['days_until_deadline'] }} days left</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if($project['overdue_cards'] > 0)
                                            <span class="px-2 py-1 text-xs font-bold text-red-600 bg-red-50 rounded-full">
                                                {{ $project['overdue_cards'] }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h3 class="mt-4 text-lg font-medium text-gray-900">All Projects On Track! 🎉</h3>
                        <p class="mt-2 text-sm text-gray-500">No projects at risk currently.</p>
                    </div>
                    @endif
                </div>
                @endisset

                @isset($charts)
                <!-- 4️⃣ Charts Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    
                    <!-- Task Status Distribution Chart -->
                    <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            <span>📊</span> Task Status Distribution
                        </h3>
                        <div class="h-64">
                            <canvas id="taskStatusChart"></canvas>
                        </div>
                    </div>

                    <!-- Team Workload Chart -->
                    <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            <span>👥</span> Team Workload
                        </h3>
                        <div class="h-64">
                            <canvas id="teamWorkloadChart"></canvas>
                        </div>
                    </div>

                    <!-- Daily Activity Trend Chart (Full Width) -->
                    <div class="lg:col-span-2 bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            <span>📈</span> Daily Activity Trend (Last 7 Days)
                        </h3>
                        <div class="h-80">
                            <canvas id="activityTrendChart"></canvas>
                        </div>
                    </div>

                    <!-- Project Progress Chart -->
                    <div class="lg:col-span-2 bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            <span>🎯</span> Top 10 Projects by Progress
                        </h3>
                        <div class="h-96">
                            <canvas id="projectProgressChart"></canvas>
                        </div>
                    </div>
                </div>
                @endisset

            </div>

            <!-- Right Column (1/3 width) -->
            <div class="space-y-8">
                
                @isset($activities)
                <!-- 3️⃣ Recent Activities Timeline -->
                <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
                    <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                        <span>🕐</span> Recent Activities
                    </h2>
                    
                    @if(count($activities) > 0)
                    <div class="space-y-4 max-h-[600px] overflow-y-auto pr-2">
                        @foreach(array_slice($activities, 0, 10) as $activity)
                        <div class="flex items-start space-x-3 p-3 rounded-lg hover:bg-gray-50 transition-colors">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center text-white text-lg
                                    @if($activity['type'] === 'project_created') bg-blue-500
                                    @elseif($activity['type'] === 'card_created') bg-green-500
                                    @elseif($activity['type'] === 'comment_added') bg-purple-500
                                    @elseif($activity['type'] === 'task_completed') bg-emerald-500
                                    @else bg-gray-500
                                    @endif">
                                    {{ $activity['icon'] ?? '📌' }}
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900">{{ $activity['title'] }}</p>
                                <p class="text-sm text-gray-600 truncate">{{ $activity['description'] }}</p>
                                <div class="mt-1 flex items-center text-xs text-gray-500">
                                    <span>{{ $activity['user'] }}</span>
                                    <span class="mx-1">•</span>
                                    <span>{{ \Carbon\Carbon::parse($activity['time'])->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-8 text-gray-500">
                        <p>No recent activities</p>
                    </div>
                    @endif
                </div>
                @endisset

                @isset($upcomingDeadlines)
                <!-- 6️⃣ Upcoming Deadlines -->
                <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
                    <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                        <span>⏰</span> Upcoming Deadlines
                    </h2>
                    
                    @if(count($upcomingDeadlines) > 0)
                    <div class="space-y-3 max-h-[500px] overflow-y-auto pr-2">
                        @foreach(array_slice($upcomingDeadlines, 0, 10) as $deadline)
                        <div class="p-4 rounded-lg border-l-4 
                            @if($deadline['priority'] === 'high') border-red-500 bg-red-50
                            @elseif($deadline['priority'] === 'medium') border-yellow-500 bg-yellow-50
                            @else border-green-500 bg-green-50
                            @endif
                            hover:shadow-md transition-shadow">
                            
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h4 class="text-sm font-semibold text-gray-900">{{ $deadline['title'] }}</h4>
                                    <p class="text-xs text-gray-600 mt-1">{{ $deadline['project'] }}</p>
                                </div>
                                <span class="px-2 py-1 text-xs font-bold rounded-full
                                    @if($deadline['priority'] === 'high') bg-red-200 text-red-800
                                    @elseif($deadline['priority'] === 'medium') bg-yellow-200 text-yellow-800
                                    @else bg-green-200 text-green-800
                                    @endif">
                                    @if($deadline['priority'] === 'high') 🔴
                                    @elseif($deadline['priority'] === 'medium') 🟡
                                    @else 🟢
                                    @endif
                                    {{ ucfirst($deadline['priority']) }}
                                </span>
                            </div>
                            
                            <div class="mt-3 flex items-center justify-between text-xs">
                                <span class="font-medium 
                                    @if($deadline['urgency_color'] === 'red') text-red-600
                                    @elseif($deadline['urgency_color'] === 'orange') text-orange-600
                                    @elseif($deadline['urgency_color'] === 'yellow') text-yellow-600
                                    @else text-blue-600
                                    @endif">
                                    {{ $deadline['urgency_label'] }}
                                </span>
                                <span class="text-gray-500">{{ $deadline['due_date'] }}</span>
                            </div>
                            
                            @if(isset($deadline['assigned_to']) && $deadline['assigned_to'] !== 'Belum ditugaskan')
                            <div class="mt-2 text-xs text-gray-600">
                                <span class="font-medium">Assigned to:</span> {{ $deadline['assigned_to'] }}
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-8 text-gray-500">
                        <p>No upcoming deadlines</p>
                    </div>
                    @endif
                </div>
                @endisset

                @isset($systemStats)
                <!-- 7️⃣ System Statistics -->
                <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
                    <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                        <span>📊</span> Weekly Stats
                    </h2>
                    
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                            <div class="flex items-center">
                                <span class="text-2xl mr-3">⏱️</span>
                                <div>
                                    <p class="text-xs text-gray-600">Total Work Hours</p>
                                    <p class="text-lg font-bold text-gray-900">{{ $systemStats['total_work_hours'] ?? 0 }}h</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between p-3 bg-purple-50 rounded-lg">
                            <div class="flex items-center">
                                <span class="text-2xl mr-3">💬</span>
                                <div>
                                    <p class="text-xs text-gray-600">New Comments</p>
                                    <p class="text-lg font-bold text-gray-900">{{ $systemStats['new_comments'] ?? 0 }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                            <div class="flex items-center">
                                <span class="text-2xl mr-3">✅</span>
                                <div>
                                    <p class="text-xs text-gray-600">Subtask Completion</p>
                                    <p class="text-lg font-bold text-gray-900">{{ $systemStats['subtask_completion_rate'] ?? 0 }}%</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between p-3 bg-orange-50 rounded-lg">
                            <div class="flex items-center">
                                <span class="text-2xl mr-3">🎯</span>
                                <div>
                                    <p class="text-xs text-gray-600">Tasks Completed</p>
                                    <p class="text-lg font-bold text-gray-900">{{ $systemStats['tasks_completed'] ?? 0 }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between p-3 bg-teal-50 rounded-lg">
                            <div class="flex items-center">
                                <span class="text-2xl mr-3">⏰</span>
                                <div>
                                    <p class="text-xs text-gray-600">Avg Completion Time</p>
                                    <p class="text-lg font-bold text-gray-900">{{ $systemStats['avg_completion_hours'] ?? 0 }}h</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endisset

            </div>
        </div>

        @isset($teamStatus)
        <!-- 5️⃣ Team Status Section -->
        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                <span>👥</span> Team Status
            </h2>
            
            @if(count($teamStatus) > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach($teamStatus as $member)
                <div class="p-4 rounded-lg border border-gray-200 hover:shadow-md transition-shadow
                    {{ $member['status'] === 'working' ? 'bg-green-50 border-green-300' : 'bg-gray-50' }}">
                    
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-bold">
                                {{ strtoupper(substr($member['name'], 0, 1)) }}
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-semibold text-gray-900">{{ $member['name'] }}</h4>
                                <p class="text-xs text-gray-500">{{ $member['email'] }}</p>
                            </div>
                        </div>
                        <div class="flex-shrink-0">
                            @if($member['status'] === 'working')
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    🟢 Working
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-200 text-gray-600">
                                    ⚪ Idle
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Active Tasks</span>
                            <span class="font-semibold text-blue-600">{{ $member['active_tasks'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Completed</span>
                            <span class="font-semibold text-green-600">{{ $member['completed_tasks'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Total Assigned</span>
                            <span class="font-semibold text-gray-900">{{ $member['total_assigned'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Hours Today</span>
                            <span class="font-semibold text-orange-600">{{ $member['work_hours_today'] }}h</span>
                        </div>
                    </div>
                    
                    @if(isset($member['current_task']) && $member['current_task'])
                    <div class="mt-3 pt-3 border-t border-gray-200">
                        <p class="text-xs text-gray-600">Current Task:</p>
                        <p class="text-xs font-medium text-gray-900 truncate">{{ $member['current_task']['title'] }}</p>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-8 text-gray-500">
                <p>No team members found</p>
            </div>
            @endif
        </div>
        @endisset

    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

@isset($charts)
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Task Status Distribution (Donut Chart)
    const taskStatusCtx = document.getElementById('taskStatusChart');
    if (taskStatusCtx) {
        const taskStatusData = @json($charts['task_status_distribution'] ?? []);
        
        new Chart(taskStatusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Todo', 'In Progress', 'Review', 'Done'],
                datasets: [{
                    data: [
                        taskStatusData.todo?.count || 0,
                        taskStatusData['in progress']?.count || 0,
                        taskStatusData.review?.count || 0,
                        taskStatusData.done?.count || 0
                    ],
                    backgroundColor: ['#94a3b8', '#3b82f6', '#f59e0b', '#10b981'],
                    borderWidth: 3,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            font: { size: 12 }
                        }
                    }
                }
            }
        });
    }

    // 2. Team Workload (Stacked Bar Chart)
    const teamWorkloadCtx = document.getElementById('teamWorkloadChart');
    if (teamWorkloadCtx) {
        const workloadData = @json($charts['team_workload'] ?? []);
        
        new Chart(teamWorkloadCtx, {
            type: 'bar',
            data: {
                labels: workloadData.slice(0, 8).map(item => item.user_name),
                datasets: [
                    {
                        label: 'Assigned',
                        data: workloadData.slice(0, 8).map(item => item.assigned),
                        backgroundColor: '#94a3b8',
                        borderRadius: 6
                    },
                    {
                        label: 'In Progress',
                        data: workloadData.slice(0, 8).map(item => item.in_progress),
                        backgroundColor: '#3b82f6',
                        borderRadius: 6
                    },
                    {
                        label: 'Completed',
                        data: workloadData.slice(0, 8).map(item => item.completed),
                        backgroundColor: '#10b981',
                        borderRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { stacked: true },
                    y: { stacked: true, beginAtZero: true }
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 10,
                            font: { size: 11 }
                        }
                    }
                }
            }
        });
    }

    // 3. Daily Activity Trend (Line Chart)
    const activityTrendCtx = document.getElementById('activityTrendChart');
    if (activityTrendCtx) {
        const activityData = @json($charts['daily_activity'] ?? []);
        
        new Chart(activityTrendCtx, {
            type: 'line',
            data: {
                labels: activityData.map(item => item.day_name),
                datasets: [
                    {
                        label: 'Cards Created',
                        data: activityData.map(item => item.cards_created),
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 3
                    },
                    {
                        label: 'Comments',
                        data: activityData.map(item => item.comments_added),
                        borderColor: '#8b5cf6',
                        backgroundColor: 'rgba(139, 92, 246, 0.1)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 3
                    },
                    {
                        label: 'Tasks Completed',
                        data: activityData.map(item => item.tasks_completed),
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            font: { size: 12 }
                        }
                    }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }

    // 4. Project Progress (Horizontal Bar Chart)
    const projectProgressCtx = document.getElementById('projectProgressChart');
    if (projectProgressCtx) {
        const projectData = @json($charts['project_progress'] ?? []);
        
        new Chart(projectProgressCtx, {
            type: 'bar',
            data: {
                labels: projectData.map(item => item.project_name),
                datasets: [{
                    label: 'Completion %',
                    data: projectData.map(item => item.progress),
                    backgroundColor: projectData.map(item => {
                        if (item.progress >= 80) return '#10b981';
                        if (item.progress >= 50) return '#3b82f6';
                        if (item.progress >= 30) return '#f59e0b';
                        return '#ef4444';
                    }),
                    borderRadius: 8,
                    borderWidth: 0
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: { 
                        beginAtZero: true, 
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
@endisset

@endsection
