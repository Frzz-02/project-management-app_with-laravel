@extends('layouts.admin')

@section('title', 'Admin Reports - Comprehensive Analytics')

@section('content')
<div x-data="adminReportData()" x-init="init()" class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- Header with Filters and Actions -->
    <div class="mb-8 no-print">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h1 class="text-4xl font-bold text-gray-900 flex items-center gap-3">
                    <span class="text-5xl">📊</span>
                    <span>Admin Dashboard</span>
                </h1>
                <p class="mt-2 text-sm text-gray-600">Comprehensive project management analytics and insights</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-3">
                <!-- Refresh Button -->
                <button @click="refreshAllData()" 
                        :disabled="loading"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-5 h-5 mr-2" :class="{'animate-spin': loading}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    <span x-text="loading ? 'Loading...' : 'Refresh Data'"></span>
                </button>
                
                <!-- Export Excel Button -->
                <a href="{{ route('admin.reports.export.excel') }}" 
                   class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Export Excel
                </a>
                
                <!-- Export PDF Button -->
                <a href="{{ route('admin.reports.export.pdf') }}" 
                   class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                    Export PDF
                </a>
                
                <!-- Print Button -->
                <button @click="window.print()" 
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    Print
                </button>
            </div>
        </div>
        
        <!-- Filters Section -->
        <div class="mt-6 bg-white rounded-xl shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">🔍 Filters</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Date Range Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                    <input type="date" 
                           x-model="filters.date_from" 
                           @change="applyFilters()"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                    <input type="date" 
                           x-model="filters.date_to" 
                           @change="applyFilters()"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <!-- Clear Filters Button -->
                <div class="flex items-end">
                    <button @click="clearFilters()" 
                            class="w-full px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200 transition-colors">
                        Clear Filters
                    </button>
                </div>
            </div>
            
            <div class="mt-2 text-xs text-gray-500" x-show="generatedAt">
                Last updated: <span x-text="generatedAt"></span>
            </div>
        </div>
    </div>

    <!-- Loading State -->
    <div x-show="loading" class="flex justify-center items-center py-20">
        <div class="text-center">
            <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-blue-600 mx-auto"></div>
            <p class="mt-4 text-gray-600 font-medium">Loading comprehensive analytics...</p>
        </div>
    </div>

    <!-- Main Content -->
    <div x-show="!loading" class="space-y-8">
        
        <!-- SECTION 1: Overview Statistics Cards (4 cards) -->
        <div>
            <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                <span>📈</span> Overview Statistics
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Total Projects Card -->
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-transform">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm uppercase tracking-wide">Total Projects</p>
                            <p class="text-4xl font-bold mt-2" x-text="overview.total_projects || 0"></p>
                            <p class="text-blue-100 text-xs mt-1">
                                <span x-text="overview.active_projects || 0"></span> active
                            </p>
                        </div>
                        <div class="bg-blue-400 bg-opacity-30 rounded-full p-4">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Active Users Card -->
                <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-transform">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm uppercase tracking-wide">Active Users</p>
                            <p class="text-4xl font-bold mt-2" x-text="overview.active_users || 0"></p>
                            <p class="text-green-100 text-xs mt-1">
                                of <span x-text="overview.total_users || 0"></span> total
                            </p>
                        </div>
                        <div class="bg-green-400 bg-opacity-30 rounded-full p-4">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Total Tasks Card -->
                <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-transform">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-100 text-sm uppercase tracking-wide">Total Tasks</p>
                            <p class="text-4xl font-bold mt-2" x-text="overview.total_cards || 0"></p>
                            <p class="text-purple-100 text-xs mt-1">
                                <span x-text="overview.overdue_cards || 0"></span> overdue
                            </p>
                        </div>
                        <div class="bg-purple-400 bg-opacity-30 rounded-full p-4">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Completion Rate Card -->
                <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-transform">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-orange-100 text-sm uppercase tracking-wide">Completion Rate</p>
                            <p class="text-4xl font-bold mt-2">
                                <span x-text="overview.completion_rate || 0"></span>%
                            </p>
                            <p class="text-orange-100 text-xs mt-1">
                                <span x-text="overview.completed_cards || 0"></span> completed
                            </p>
                        </div>
                        <div class="bg-orange-400 bg-opacity-30 rounded-full p-4">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 2: Charts Row 1 - Pie & Donut Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Project Status Distribution (Pie Chart) -->
            <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <span>🎯</span> Project Status Distribution
                </h3>
                <div class="h-80">
                    <canvas id="projectStatusChart"></canvas>
                </div>
            </div>

            <!-- Task Status Distribution (Donut Chart) -->
            <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <span>📋</span> Task Status Distribution
                </h3>
                <div class="h-80">
                    <canvas id="taskStatusChart"></canvas>
                </div>
            </div>
        </div>

        <!-- SECTION 3: Project Timeline (Horizontal Bar Chart) -->
        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <span>📅</span> Project Completion Timeline
            </h3>
            <div class="h-96">
                <canvas id="projectTimelineChart"></canvas>
            </div>
        </div>

        <!-- SECTION 4: Charts Row 2 - Bar & Line Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Team Workload Distribution (Vertical Bar Chart) -->
            <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <span>👥</span> Team Workload Distribution
                </h3>
                <div class="h-80">
                    <canvas id="teamWorkloadChart"></canvas>
                </div>
            </div>

            <!-- Estimated vs Actual Hours (Line Chart) -->
            <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <span>⏱️</span> Estimated vs Actual Hours (6 Months)
                </h3>
                <div class="h-80">
                    <canvas id="hoursComparisonChart"></canvas>
                </div>
            </div>
        </div>

        <!-- SECTION 5: Critical Alert - Overdue Tasks Table -->
        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                    <span>🚨</span> Critical Alerts - Overdue Tasks
                </h3>
                <span class="px-3 py-1 bg-red-100 text-red-800 text-sm font-medium rounded-full" 
                      x-text="`${overdueTasks.length} tasks`"></span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Task</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Project</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Due Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Days Overdue</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Priority</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <template x-for="task in overdueTasks.slice(0, 10)" :key="task.id">
                            <tr class="hover:bg-red-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900" x-text="task.card_title"></div>
                                    <div class="text-xs text-gray-500" x-text="task.creator"></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="task.project_name"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="task.due_date"></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full" 
                                          x-text="`${task.days_overdue} days`"></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full"
                                          :class="{
                                              'bg-red-100 text-red-800': task.priority === 'high',
                                              'bg-yellow-100 text-yellow-800': task.priority === 'medium',
                                              'bg-green-100 text-green-800': task.priority === 'low'
                                          }"
                                          x-text="task.priority"></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full" 
                                          x-text="task.status"></span>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="overdueTasks.length === 0">
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-12 h-12 text-green-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <p class="font-medium">No overdue tasks! 🎉</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- SECTION 6: Project Health Score Table -->
        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <span>💚</span> Project Health Score
            </h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Project</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Health Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Completion</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Team Size</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tasks</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Overdue</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Days Left</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <template x-for="project in projectPerformance" :key="project.id">
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900" x-text="project.project_name"></div>
                                    <div class="text-xs text-gray-500" x-text="project.creator"></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 text-xs font-bold rounded-full"
                                          :class="{
                                              'bg-red-100 text-red-800': project.health_status === 'Overdue',
                                              'bg-orange-100 text-orange-800': project.health_status === 'At Risk',
                                              'bg-yellow-100 text-yellow-800': project.health_status === 'Needs Attention',
                                              'bg-green-100 text-green-800': project.health_status === 'On Track'
                                          }"
                                          x-text="project.health_status"></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                            <div class="bg-blue-600 h-2 rounded-full" 
                                                 :style="`width: ${project.completion_percentage}%`"></div>
                                        </div>
                                        <span class="text-sm text-gray-600" x-text="`${project.completion_percentage}%`"></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="project.team_size"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span x-text="project.completed_tasks"></span>/<span x-text="project.total_tasks"></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-medium" 
                                          :class="project.overdue_tasks > 0 ? 'text-red-600' : 'text-gray-500'"
                                          x-text="project.overdue_tasks"></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-medium"
                                          :class="{
                                              'text-red-600': project.days_remaining < 0,
                                              'text-orange-600': project.days_remaining >= 0 && project.days_remaining <= 7,
                                              'text-green-600': project.days_remaining > 7
                                          }"
                                          x-text="project.days_remaining < 0 ? `${Math.abs(project.days_remaining)} days late` : `${project.days_remaining} days`"></span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- SECTION 7: Team Performance Table -->
        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <span>🏆</span> Team Performance Leaderboard
            </h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rank</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Member</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Assigned</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">In Progress</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Completed</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Completion Rate</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hours</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <template x-for="member in teamPerformance" :key="member.id">
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-2xl" x-text="member.badge || member.rank"></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900" x-text="member.full_name"></div>
                                    <div class="text-xs text-gray-500" x-text="`@${member.username}`"></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full"
                                          :class="{
                                              'bg-green-100 text-green-800': member.current_status === 'working',
                                              'bg-gray-100 text-gray-800': member.current_status === 'idle'
                                          }"
                                          x-text="member.current_status"></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="member.assigned_cards"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 font-medium" x-text="member.in_progress_cards"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-medium" x-text="member.completed_cards"></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                            <div class="bg-green-600 h-2 rounded-full" 
                                                 :style="`width: ${member.completion_rate}%`"></div>
                                        </div>
                                        <span class="text-sm text-gray-600" x-text="`${member.completion_rate}%`"></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900" x-text="`${member.total_actual_hours}h`"></div>
                                    <div class="text-xs text-gray-500" x-text="`Est: ${member.total_estimated_hours}h`"></div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<!-- Alpine.js Admin Report Component -->
<script>
    function adminReportData() {
        return {
            loading: true,
            generatedAt: '',
            filters: {
                date_from: '',
                date_to: '',
                project_id: '',
                user_id: ''
            },
            overview: {},
            projectPerformance: [],
            teamPerformance: [],
            taskAnalytics: {},
            overdueTasks: [],
            charts: {},
            
            async init() {
                await this.loadAllData();
            },
            
            async loadAllData() {
                this.loading = true;
                try {
                    await Promise.all([
                        this.loadOverviewStats(),
                        this.loadProjectPerformance(),
                        this.loadTeamPerformance(),
                        this.loadTaskAnalytics(),
                        this.loadOverdueTasks()
                    ]);
                    
                    await this.$nextTick();
                    this.initializeCharts();
                } catch (error) {
                    console.error('Error loading data:', error);
                    alert('Failed to load report data. Please try again.');
                } finally {
                    this.loading = false;
                }
            },
            
            async loadOverviewStats() {
                const params = new URLSearchParams(this.filters);
                const response = await fetch(`/admin/reports/overview-stats?${params}`);
                const result = await response.json();
                this.overview = result.data;
                this.generatedAt = result.generated_at;
            },
            
            async loadProjectPerformance() {
                const params = new URLSearchParams(this.filters);
                const response = await fetch(`/admin/reports/project-performance?${params}`);
                const result = await response.json();
                this.projectPerformance = result.data;
            },
            
            async loadTeamPerformance() {
                const params = new URLSearchParams(this.filters);
                const response = await fetch(`/admin/reports/team-performance?${params}`);
                const result = await response.json();
                this.teamPerformance = result.data;
            },
            
            async loadTaskAnalytics() {
                const response = await fetch('/admin/reports/task-analytics');
                const result = await response.json();
                this.taskAnalytics = result.data;
            },
            
            async loadOverdueTasks() {
                const response = await fetch('/admin/reports/overdue-tasks');
                const result = await response.json();
                this.overdueTasks = result.data;
            },
            
            async refreshAllData() {
                await this.loadAllData();
            },
            
            async applyFilters() {
                await this.loadAllData();
            },
            
            clearFilters() {
                this.filters = {
                    date_from: '',
                    date_to: '',
                    project_id: '',
                    user_id: ''
                };
                this.loadAllData();
            },
            
            initializeCharts() {
                // Destroy existing charts
                Object.values(this.charts).forEach(chart => chart.destroy());
                this.charts = {};
                
                // 1. Project Status Pie Chart
                const projectStatusCtx = document.getElementById('projectStatusChart');
                if (projectStatusCtx) {
                    this.charts.projectStatus = new Chart(projectStatusCtx, {
                        type: 'pie',
                        data: {
                            labels: ['On Track', 'Due Soon', 'Overdue'],
                            datasets: [{
                                data: [
                                    this.overview.project_status_distribution?.on_track || 0,
                                    this.overview.project_status_distribution?.due_soon || 0,
                                    this.overview.project_status_distribution?.overdue || 0
                                ],
                                backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                                borderWidth: 2,
                                borderColor: '#fff'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { position: 'bottom' }
                            }
                        }
                    });
                }
                
                // 2. Task Status Donut Chart
                const taskStatusCtx = document.getElementById('taskStatusChart');
                if (taskStatusCtx) {
                    const statusDist = this.overview.task_status_distribution || {};
                    this.charts.taskStatus = new Chart(taskStatusCtx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Todo', 'In Progress', 'Review', 'Done'],
                            datasets: [{
                                data: [
                                    statusDist.todo || 0,
                                    statusDist['in progress'] || 0,
                                    statusDist.review || 0,
                                    statusDist.done || 0
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
                                legend: { position: 'bottom' }
                            }
                        }
                    });
                }
                
                // 3. Project Timeline Horizontal Bar Chart
                const timelineCtx = document.getElementById('projectTimelineChart');
                if (timelineCtx && this.projectPerformance.length > 0) {
                    this.charts.timeline = new Chart(timelineCtx, {
                        type: 'bar',
                        data: {
                            labels: this.projectPerformance.map(p => p.project_name),
                            datasets: [{
                                label: 'Completion %',
                                data: this.projectPerformance.map(p => p.completion_percentage),
                                backgroundColor: this.projectPerformance.map(p => {
                                    if (p.health_status === 'On Track') return '#10b981';
                                    if (p.health_status === 'Needs Attention') return '#f59e0b';
                                    if (p.health_status === 'At Risk') return '#f97316';
                                    return '#ef4444';
                                }),
                                borderRadius: 8
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
                                    max: 100
                                }
                            }
                        }
                    });
                }
                
                // 4. Team Workload Vertical Bar Chart
                const workloadCtx = document.getElementById('teamWorkloadChart');
                if (workloadCtx && this.teamPerformance.length > 0) {
                    const topTeam = this.teamPerformance.slice(0, 10);
                    this.charts.workload = new Chart(workloadCtx, {
                        type: 'bar',
                        data: {
                            labels: topTeam.map(m => m.full_name),
                            datasets: [
                                {
                                    label: 'Completed',
                                    data: topTeam.map(m => m.completed_cards),
                                    backgroundColor: '#10b981',
                                    borderRadius: 8
                                },
                                {
                                    label: 'In Progress',
                                    data: topTeam.map(m => m.in_progress_cards),
                                    backgroundColor: '#3b82f6',
                                    borderRadius: 8
                                },
                                {
                                    label: 'Assigned',
                                    data: topTeam.map(m => m.assigned_cards - m.completed_cards - m.in_progress_cards),
                                    backgroundColor: '#94a3b8',
                                    borderRadius: 8
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { position: 'bottom' }
                            },
                            scales: {
                                x: { 
                                    stacked: true,
                                },
                                y: { 
                                    stacked: true,
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                }
                
                // 5. Hours Comparison Line Chart
                const hoursCtx = document.getElementById('hoursComparisonChart');
                if (hoursCtx && this.taskAnalytics.hours_comparison) {
                    this.charts.hours = new Chart(hoursCtx, {
                        type: 'line',
                        data: {
                            labels: this.taskAnalytics.hours_comparison.map(h => h.month),
                            datasets: [
                                {
                                    label: 'Estimated Hours',
                                    data: this.taskAnalytics.hours_comparison.map(h => h.estimated),
                                    borderColor: '#3b82f6',
                                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                    tension: 0.4,
                                    fill: true,
                                    borderWidth: 3
                                },
                                {
                                    label: 'Actual Hours',
                                    data: this.taskAnalytics.hours_comparison.map(h => h.actual),
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
                                legend: { position: 'bottom' }
                            },
                            scales: {
                                y: { beginAtZero: true }
                            }
                        }
                    });
                }
            }
        }
    }
</script>

<!-- Print-specific CSS -->
<style>
@media print {
    .no-print {
        display: none !important;
    }
    
    body {
        background: white !important;
        color: black !important;
        font-size: 10pt;
    }
    
    .shadow-lg,
    .shadow-xl {
        box-shadow: none !important;
        border: 1px solid #e5e7eb;
    }
    
    .bg-gradient-to-br {
        background: white !important;
        border: 2px solid currentColor !important;
    }
    
    .text-white {
        color: black !important;
    }
    
    @page {
        margin: 1.5cm;
        size: A4 landscape;
    }
}
</style>
@endsection
