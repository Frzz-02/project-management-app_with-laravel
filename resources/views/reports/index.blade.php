@extends('layouts.admin')

@section('title', 'System Reports')

@section('content')
<div x-data="reportData()" x-init="loadReportData()" class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- Header -->
    <div class="mb-8 print:mb-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 print:text-2xl">📊 System Reports</h1>
                <p class="mt-2 text-sm text-gray-600 print:text-xs">Comprehensive analytics and statistics</p>
            </div>
            <div class="flex items-center space-x-3 no-print">
                <button @click="window.print()" 
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    Print Report
                </button>
                <button @click="loadReportData()" 
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Refresh Data
                </button>
            </div>
        </div>
        <div class="mt-2 text-xs text-gray-500" x-show="generatedAt">
            Generated at: <span x-text="generatedAt"></span>
        </div>
    </div>

    <!-- Loading State -->
    <div x-show="loading" class="flex justify-center items-center py-20">
        <div class="text-center">
            <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-blue-600 mx-auto"></div>
            <p class="mt-4 text-gray-600">Loading report data...</p>
        </div>
    </div>

    <!-- Main Content -->
    <div x-show="!loading" class="space-y-6 print:space-y-4">
        
        <!-- Overview Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 print:gap-3 print:grid-cols-4">
            <!-- Total Users -->
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white print:p-3 print:shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm print:text-xs">Total Users</p>
                        <p class="text-3xl font-bold mt-2 print:text-xl print:mt-1" x-text="data.overview.total_users"></p>
                    </div>
                    <div class="bg-blue-400 bg-opacity-30 rounded-full p-3 print:p-2">
                        <svg class="w-8 h-8 print:w-6 print:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Projects -->
            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white print:p-3 print:shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-sm print:text-xs">Total Projects</p>
                        <p class="text-3xl font-bold mt-2 print:text-xl print:mt-1" x-text="data.overview.total_projects"></p>
                    </div>
                    <div class="bg-green-400 bg-opacity-30 rounded-full p-3 print:p-2">
                        <svg class="w-8 h-8 print:w-6 print:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Cards -->
            <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white print:p-3 print:shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-100 text-sm print:text-xs">Total Cards</p>
                        <p class="text-3xl font-bold mt-2 print:text-xl print:mt-1" x-text="data.overview.total_cards"></p>
                    </div>
                    <div class="bg-purple-400 bg-opacity-30 rounded-full p-3 print:p-2">
                        <svg class="w-8 h-8 print:w-6 print:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Completed Cards -->
            <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl shadow-lg p-6 text-white print:p-3 print:shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-orange-100 text-sm print:text-xs">Completed</p>
                        <p class="text-3xl font-bold mt-2 print:text-xl print:mt-1" x-text="data.overview.completed_cards"></p>
                    </div>
                    <div class="bg-orange-400 bg-opacity-30 rounded-full p-3 print:p-2">
                        <svg class="w-8 h-8 print:w-6 print:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row 1 -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 print:gap-3 print:grid-cols-2 print:page-break-after">
            <!-- Card Status Chart -->
            <div class="bg-white rounded-xl shadow-lg p-6 print:p-3 print:shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 print:text-sm print:mb-2">Card Status Distribution</h3>
                <div class="h-64 print:h-48">
                    <canvas id="cardStatusChart"></canvas>
                </div>
            </div>

            <!-- Card Priority Chart -->
            <div class="bg-white rounded-xl shadow-lg p-6 print:p-3 print:shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 print:text-sm print:mb-2">Card Priority Distribution</h3>
                <div class="h-64 print:h-48">
                    <canvas id="cardPriorityChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Charts Row 2 -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 print:gap-3 print:grid-cols-2">
            <!-- User Role Chart -->
            <div class="bg-white rounded-xl shadow-lg p-6 print:p-3 print:shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 print:text-sm print:mb-2">User Roles</h3>
                <div class="h-64 print:h-48">
                    <canvas id="userRoleChart"></canvas>
                </div>
            </div>

            <!-- Top Active Users Chart -->
            <div class="bg-white rounded-xl shadow-lg p-6 print:p-3 print:shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 print:text-sm print:mb-2">Top Active Users (by Assigned Cards)</h3>
                <div class="h-64 print:h-48">
                    <canvas id="topUsersChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Monthly Trend Chart -->
        <div class="bg-white rounded-xl shadow-lg p-6 print:p-3 print:shadow-sm print:page-break-before">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 print:text-sm print:mb-2">Card Creation Trend (Last 6 Months)</h3>
            <div class="h-80 print:h-48">
                <canvas id="monthlyTrendChart"></canvas>
            </div>
        </div>

        <!-- Detailed Statistics Tables -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 print:gap-3 print:grid-cols-2 print:page-break-before">
            <!-- User Statistics -->
            <div class="bg-white rounded-xl shadow-lg p-6 print:p-3 print:shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 print:text-sm print:mb-2">👥 User Statistics</h3>
                <div class="space-y-3 print:space-y-1">
                    <div class="flex justify-between items-center py-2 border-b print:py-1 print:text-xs">
                        <span class="text-gray-600">Admin Users</span>
                        <span class="font-semibold text-gray-900" x-text="data.users.admin_count"></span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b print:py-1 print:text-xs">
                        <span class="text-gray-600">Member Users</span>
                        <span class="font-semibold text-gray-900" x-text="data.users.member_count"></span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b print:py-1 print:text-xs">
                        <span class="text-gray-600">Working Status</span>
                        <span class="font-semibold text-green-600" x-text="data.users.working_users"></span>
                    </div>
                    <div class="flex justify-between items-center py-2 print:py-1 print:text-xs">
                        <span class="text-gray-600">Idle Status</span>
                        <span class="font-semibold text-gray-500" x-text="data.users.idle_users"></span>
                    </div>
                </div>
            </div>

            <!-- Project Statistics -->
            <div class="bg-white rounded-xl shadow-lg p-6 print:p-3 print:shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 print:text-sm print:mb-2">📁 Project Statistics</h3>
                <div class="space-y-3 print:space-y-1">
                    <div class="flex justify-between items-center py-2 border-b print:py-1 print:text-xs">
                        <span class="text-gray-600">Total Projects</span>
                        <span class="font-semibold text-gray-900" x-text="data.projects.total"></span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b print:py-1 print:text-xs">
                        <span class="text-gray-600">Overdue Projects</span>
                        <span class="font-semibold text-red-600" x-text="data.projects.overdue"></span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b print:py-1 print:text-xs">
                        <span class="text-gray-600">Due Soon (7 days)</span>
                        <span class="font-semibold text-orange-600" x-text="data.projects.due_soon"></span>
                    </div>
                    <div class="flex justify-between items-center py-2 print:py-1 print:text-xs">
                        <span class="text-gray-600">Total Boards</span>
                        <span class="font-semibold text-gray-900" x-text="data.boards.total_boards"></span>
                    </div>
                </div>
            </div>

            <!-- Assignment Statistics -->
            <div class="bg-white rounded-xl shadow-lg p-6 print:p-3 print:shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 print:text-sm print:mb-2">📌 Assignment Statistics</h3>
                <div class="space-y-3 print:space-y-1">
                    <div class="flex justify-between items-center py-2 border-b print:py-1 print:text-xs">
                        <span class="text-gray-600">Total Assignments</span>
                        <span class="font-semibold text-gray-900" x-text="data.assignments.total_assignments"></span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b print:py-1 print:text-xs">
                        <span class="text-gray-600">Completed</span>
                        <span class="font-semibold text-green-600" x-text="data.assignments.completed_assignments"></span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b print:py-1 print:text-xs">
                        <span class="text-gray-600">In Progress</span>
                        <span class="font-semibold text-blue-600" x-text="data.assignments.in_progress_assignments"></span>
                    </div>
                    <div class="flex justify-between items-center py-2 print:py-1 print:text-xs">
                        <span class="text-gray-600">Assigned Only</span>
                        <span class="font-semibold text-gray-500" x-text="data.assignments.assigned_only"></span>
                    </div>
                </div>
            </div>

            <!-- Additional Statistics -->
            <div class="bg-white rounded-xl shadow-lg p-6 print:p-3 print:shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 print:text-sm print:mb-2">📊 Other Statistics</h3>
                <div class="space-y-3 print:space-y-1">
                    <div class="flex justify-between items-center py-2 border-b print:py-1 print:text-xs">
                        <span class="text-gray-600">Total Subtasks</span>
                        <span class="font-semibold text-gray-900" x-text="data.subtasks.total_subtasks"></span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b print:py-1 print:text-xs">
                        <span class="text-gray-600">Completed Subtasks</span>
                        <span class="font-semibold text-green-600" x-text="data.subtasks.completed_subtasks"></span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b print:py-1 print:text-xs">
                        <span class="text-gray-600">Total Comments</span>
                        <span class="font-semibold text-gray-900" x-text="data.comments.total_comments"></span>
                    </div>
                    <div class="flex justify-between items-center py-2 print:py-1 print:text-xs">
                        <span class="text-gray-600">Total Time Logs</span>
                        <span class="font-semibold text-gray-900" x-text="data.time_logs.total_logs"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Projects Table -->
        <div class="bg-white rounded-xl shadow-lg p-6 print:p-3 print:shadow-sm print:page-break-before">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 print:text-sm print:mb-2">🆕 Recent Projects</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 print:text-xs">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider print:px-2 print:py-1">Project Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider print:px-2 print:py-1">Creator</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider print:px-2 print:py-1">Deadline</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider print:px-2 print:py-1">Created</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <template x-for="project in data.recent_projects" :key="project.id">
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 print:px-2 print:py-1" x-text="project.project_name"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 print:px-2 print:py-1" x-text="project.creator"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 print:px-2 print:py-1" x-text="project.deadline"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 print:px-2 print:py-1" x-text="project.created_at"></td>
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

<!-- Alpine.js Report Data Component -->
<script>
    function reportData() {
        return {
            loading: true,
            generatedAt: '',
            data: {
                overview: {},
                users: {},
                projects: {},
                cards: { by_status: {}, by_priority: {} },
                assignments: {},
                time_logs: {},
                subtasks: {},
                comments: {},
                project_members: {},
                monthly_trend: [],
                boards: {},
                top_users: [],
                recent_projects: []
            },
            charts: {},
            
            async loadReportData() {
                this.loading = true;
                try {
                    const response = await fetch('/api/reports/data', {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    
                    if (!response.ok) {
                        throw new Error('Failed to load report data');
                    }
                    
                    const result = await response.json();
                    this.data = result.data;
                    this.generatedAt = result.generated_at;
                    
                    // Wait for DOM to update
                    await this.$nextTick();
                    
                    // Initialize charts
                    this.initializeCharts();
                } catch (error) {
                    console.error('Error loading report:', error);
                    alert('Failed to load report data. Please try again.');
                } finally {
                    this.loading = false;
                }
            },
            
            initializeCharts() {
                // Destroy existing charts
                Object.values(this.charts).forEach(chart => chart.destroy());
                this.charts = {};
                
                // Card Status Pie Chart
                const statusCtx = document.getElementById('cardStatusChart');
                if (statusCtx) {
                    this.charts.status = new Chart(statusCtx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Todo', 'In Progress', 'Review', 'Done'],
                            datasets: [{
                                data: [
                                    this.data.overview.todo_cards || 0,
                                    this.data.overview.in_progress_cards || 0,
                                    this.data.overview.review_cards || 0,
                                    this.data.overview.completed_cards || 0
                                ],
                                backgroundColor: ['#94a3b8', '#3b82f6', '#f59e0b', '#10b981'],
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
                
                // Card Priority Bar Chart
                const priorityCtx = document.getElementById('cardPriorityChart');
                if (priorityCtx) {
                    this.charts.priority = new Chart(priorityCtx, {
                        type: 'bar',
                        data: {
                            labels: ['Low', 'Medium', 'High'],
                            datasets: [{
                                label: 'Cards by Priority',
                                data: [
                                    this.data.cards.by_priority.low || 0,
                                    this.data.cards.by_priority.medium || 0,
                                    this.data.cards.by_priority.high || 0
                                ],
                                backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                                borderRadius: 8
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false }
                            },
                            scales: {
                                y: { beginAtZero: true }
                            }
                        }
                    });
                }
                
                // User Role Doughnut Chart
                const userRoleCtx = document.getElementById('userRoleChart');
                if (userRoleCtx) {
                    this.charts.userRole = new Chart(userRoleCtx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Admin', 'Member'],
                            datasets: [{
                                data: [
                                    this.data.users.admin_count || 0,
                                    this.data.users.member_count || 0
                                ],
                                backgroundColor: ['#8b5cf6', '#3b82f6'],
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
                
                // Top Users Horizontal Bar Chart
                const topUsersCtx = document.getElementById('topUsersChart');
                if (topUsersCtx && this.data.top_users.length > 0) {
                    this.charts.topUsers = new Chart(topUsersCtx, {
                        type: 'bar',
                        data: {
                            labels: this.data.top_users.map(u => u.full_name),
                            datasets: [{
                                label: 'Assigned Cards',
                                data: this.data.top_users.map(u => u.card_count),
                                backgroundColor: '#3b82f6',
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
                                x: { beginAtZero: true }
                            }
                        }
                    });
                }
                
                // Monthly Trend Line Chart
                const trendCtx = document.getElementById('monthlyTrendChart');
                if (trendCtx && this.data.monthly_trend.length > 0) {
                    this.charts.trend = new Chart(trendCtx, {
                        type: 'line',
                        data: {
                            labels: this.data.monthly_trend.map(m => m.month),
                            datasets: [{
                                label: 'Cards Created',
                                data: this.data.monthly_trend.map(m => m.count),
                                borderColor: '#3b82f6',
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                tension: 0.4,
                                fill: true,
                                borderWidth: 3
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false }
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
    /* Hide elements that shouldn't be printed */
    .no-print,
    nav,
    aside,
    .sidebar,
    button:not(.print-button),
    [x-cloak] {
        display: none !important;
    }
    
    /* Optimize page layout */
    body {
        background: white !important;
        color: black !important;
        font-size: 10pt;
        line-height: 1.3;
    }
    
    /* Remove shadows and backgrounds */
    .shadow-lg,
    .shadow-xl {
        box-shadow: none !important;
        border: 1px solid #e5e7eb;
    }
    
    .rounded-xl {
        border-radius: 8px !important;
    }
    
    /* Page breaks */
    .print\:page-break-before {
        page-break-before: always;
    }
    
    .print\:page-break-after {
        page-break-after: always;
    }
    
    /* Avoid breaks inside elements */
    .bg-white {
        page-break-inside: avoid;
    }
    
    /* Optimize chart sizes */
    canvas {
        max-width: 100% !important;
        height: auto !important;
    }
    
    /* Table optimization */
    table {
        page-break-inside: auto;
    }
    
    tr {
        page-break-inside: avoid;
        page-break-after: auto;
    }
    
    /* Margins */
    @page {
        margin: 1.5cm;
        size: A4;
    }
    
    /* Hide gradient backgrounds */
    .bg-gradient-to-br {
        background: white !important;
        border: 2px solid currentColor !important;
    }
    
    /* Ensure text is visible */
    .text-white {
        color: black !important;
    }
    
    /* Grid adjustments */
    .grid {
        display: block !important;
    }
    
    .grid > div {
        margin-bottom: 0.5cm;
        width: 100% !important;
    }
}
</style>
@endsection
