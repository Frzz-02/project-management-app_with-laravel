# Team Leader Dashboard Implementation Guide

## 🎯 Status Implementation

### ✅ SELESAI (Completed):
1. **TeamLeaderDashboardController** - Fully functional dengan:
   - `index()` - Main dashboard dengan caching
   - `getOverviewStats()` - Statistics (projects, tasks, reviews, overdue, active members)
   - `getMyProjects()` - Projects dengan completion rate & deadline status
   - `getTasksRequiringReview()` - Tasks perlu review dengan urgency indicator
   - `getTeamActivity()` - Team members activity hari ini
   - `getRecentComments()` - Timeline comments
   - `getTaskStatusChart()` - API untuk donut chart
   - `getTeamWorkloadChart()` - API untuk bar chart
   - `clearCache()` - Clear cache method

2. **TeamLeaderMiddleware** - Validation middleware:
   - Check role dari `project_members` table
   - Admin override support
   - Friendly error messages

3. **Model Relationships**:
   - `User::ledProjects()` - Projects yang di-lead
   - `Project::teamLeader()` - Get team leader

---

## 📋 NEXT STEPS (Implementasi Sisanya):

Karena scope ini SANGAT BESAR (7 partials + main view + JS + routes), berikut adalah structure lengkap yang perlu dibuat:

### 1. Main Dashboard View
**File**: `resources/views/team-leader/dashboard.blade.php`

```blade
@extends('layouts.app')

@section('title', 'Team Leader Dashboard')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 py-6 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Team Leader Dashboard</h1>
                <p class="mt-1 text-sm text-gray-600">Manage your projects and team effectively</p>
            </div>
            <div class="mt-4 sm:mt-0 flex gap-3">
                <a href="{{ route('projects.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                    </svg>
                    My Projects
                </a>
                <a href="{{ route('cards.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 rounded-lg transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    All Tasks
                </a>
            </div>
        </div>

        {{-- Stats Cards --}}
        @include('team-leader.dashboard.partials.stats-cards')

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
            {{-- Tasks Requiring Review (2/3 width) --}}
            <div class="xl:col-span-2">
                @include('team-leader.dashboard.partials.tasks-review')
            </div>

            {{-- My Projects (1/3 width) --}}
            <div class="xl:col-span-1">
                @include('team-leader.dashboard.partials.my-projects')
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-6">
            {{-- Team Activity --}}
            <div>
                @include('team-leader.dashboard.partials.team-activity')
            </div>

            {{-- Recent Comments --}}
            <div>
                @include('team-leader.dashboard.partials.recent-comments')
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            {{-- Task Status Chart --}}
            <div>
                @include('team-leader.dashboard.partials.task-status-chart')
            </div>

            {{-- Team Workload Chart --}}
            <div>
                @include('team-leader.dashboard.partials.team-workload-chart')
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="{{ asset('js/team-leader/dashboard.js') }}"></script>
@endpush
```

---

### 2. Partials Structure

#### A. stats-cards.blade.php
```blade
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
    {{-- Total Projects --}}
    <div class="backdrop-blur-xl bg-white/70 rounded-2xl p-6 border border-white/20 shadow-lg hover:shadow-xl transition-all">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Total Projects</p>
                <p class="text-3xl font-bold text-gray-900">{{ $overviewStats['total_projects'] }}</p>
            </div>
            <div class="h-12 w-12 bg-blue-100 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- Total Tasks --}}
    {{-- Similar structure with different icons/colors --}}
    
    {{-- Continue for: tasks_need_review, overdue_tasks, active_members --}}
</div>
```

#### B. tasks-review.blade.php
```blade
<div class="backdrop-blur-xl bg-white/70 rounded-2xl border border-white/20 shadow-lg">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">Tasks Requiring Review</h2>
        <p class="text-sm text-gray-600">Review and approve/reject submitted tasks</p>
    </div>
    
    <div class="p-6">
        @forelse($tasksRequiringReview as $task)
        <div class="mb-4 last:mb-0 p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors">
            {{-- Task title, project, priority, due date --}}
            {{-- Approve/Reject buttons --}}
        </div>
        @empty
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="mt-2 text-sm text-gray-600">No tasks requiring review</p>
        </div>
        @endforelse
    </div>
</div>
```

#### C. my-projects.blade.php
```blade
<div class="backdrop-blur-xl bg-white/70 rounded-2xl border border-white/20 shadow-lg">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">My Projects</h2>
    </div>
    
    <div class="p-6 space-y-4">
        @foreach($myProjects as $project)
        <div class="p-4 bg-gradient-to-br from-white to-gray-50 rounded-xl border border-gray-200 hover:shadow-md transition-all">
            <h3 class="font-semibold text-gray-900">{{ $project->project_name }}</h3>
            <div class="mt-2">
                <div class="flex justify-between text-sm text-gray-600 mb-1">
                    <span>Progress</span>
                    <span>{{ $project->completion_rate }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-gradient-to-r from-blue-500 to-purple-500 h-2 rounded-full" 
                         style="width: {{ $project->completion_rate }}%"></div>
                </div>
            </div>
            <div class="mt-3 flex items-center justify-between text-xs text-gray-600">
                <span>{{ $project->team_count }} members</span>
                <span class="px-2 py-1 rounded-full 
                    @if($project->deadline_status === 'overdue') bg-red-100 text-red-800
                    @elseif($project->deadline_status === 'urgent') bg-orange-100 text-orange-800
                    @else bg-blue-100 text-blue-800 @endif">
                    @if($project->days_remaining !== null)
                        {{ abs($project->days_remaining) }} days {{ $project->days_remaining < 0 ? 'overdue' : 'left' }}
                    @else
                        No deadline
                    @endif
                </span>
            </div>
        </div>
        @endforeach
    </div>
</div>
```

#### D. team-activity.blade.php
```blade
<div class="backdrop-blur-xl bg-white/70 rounded-2xl border border-white/20 shadow-lg">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">Team Activity Today</h2>
    </div>
    
    <div class="p-6 space-y-4">
        @forelse($teamActivity as $member)
        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
            <div class="flex items-center space-x-3">
                <div class="h-10 w-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-semibold">
                    {{ strtoupper(substr($member->full_name, 0, 1)) }}
                </div>
                <div>
                    <p class="font-medium text-gray-900">{{ $member->full_name }}</p>
                    <p class="text-sm text-gray-600">
                        @if($member->current_tasks->count() > 0)
                            Working on: {{ $member->current_tasks->first()['title'] }}
                        @else
                            No active tasks
                        @endif
                    </p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-lg font-semibold text-gray-900">{{ $member->hours_today }}h</p>
                <p class="text-xs text-gray-600">today</p>
            </div>
        </div>
        @empty
        <p class="text-center text-sm text-gray-600 py-8">No activity today</p>
        @endforelse
    </div>
</div>
```

#### E. recent-comments.blade.php
```blade
<div class="backdrop-blur-xl bg-white/70 rounded-2xl border border-white/20 shadow-lg">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">Recent Comments</h2>
    </div>
    
    <div class="p-6 space-y-4 max-h-96 overflow-y-auto">
        @foreach($recentComments as $comment)
        <div class="flex items-start space-x-3">
            <div class="h-8 w-8 bg-gradient-to-br from-purple-400 to-pink-400 rounded-full flex items-center justify-center text-white text-sm font-semibold flex-shrink-0">
                {{ strtoupper(substr($comment->user->full_name, 0, 1)) }}
            </div>
            <div class="flex-1">
                <p class="text-sm text-gray-900">
                    <span class="font-medium">{{ $comment->user->full_name }}</span> commented on 
                    <span class="font-medium">{{ $comment->context }}</span>
                </p>
                <p class="text-sm text-gray-600 mt-1 line-clamp-2">{{ $comment->comment_text }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ $comment->time_ago }}</p>
            </div>
        </div>
        @endforeach
    </div>
</div>
```

#### F. task-status-chart.blade.php
```blade
<div class="backdrop-blur-xl bg-white/70 rounded-2xl border border-white/20 shadow-lg">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">Task Status Distribution</h2>
    </div>
    
    <div class="p-6">
        <canvas id="taskStatusChart" height="300"></canvas>
    </div>
</div>
```

#### G. team-workload-chart.blade.php
```blade
<div class="backdrop-blur-xl bg-white/70 rounded-2xl border border-white/20 shadow-lg">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">Team Workload</h2>
    </div>
    
    <div class="p-6">
        <canvas id="teamWorkloadChart" height="300"></canvas>
    </div>
</div>
```

---

### 3. JavaScript for Charts
**File**: `public/js/team-leader/dashboard.js`

```javascript
// Team Leader Dashboard Charts
document.addEventListener('DOMContentLoaded', function() {
    
    // Task Status Donut Chart
    fetch('/team-leader/dashboard/chart/task-status')
        .then(res => res.json())
        .then(data => {
            const ctx = document.getElementById('taskStatusChart').getContext('2d');
            
            const colors = {
                'todo': '#94a3b8',      // Gray
                'in progress': '#3b82f6', // Blue
                'review': '#f59e0b',    // Orange
                'done': '#10b981'       // Green
            };
            
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: data.data.map(d => d.label),
                    datasets: [{
                        data: data.data.map(d => d.value),
                        backgroundColor: data.data.map(d => colors[d.status]),
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        });
    
    // Team Workload Bar Chart
    fetch('/team-leader/dashboard/chart/team-workload')
        .then(res => res.json())
        .then(data => {
            const ctx = document.getElementById('teamWorkloadChart').getContext('2d');
            
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.data.map(d => d.name),
                    datasets: [
                        {
                            label: 'Completed',
                            data: data.data.map(d => d.completed),
                            backgroundColor: '#10b981'
                        },
                        {
                            label: 'In Progress',
                            data: data.data.map(d => d.in_progress),
                            backgroundColor: '#3b82f6'
                        },
                        {
                            label: 'Pending',
                            data: data.data.map(d => d.pending),
                            backgroundColor: '#94a3b8'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { stacked: true },
                        y: { stacked: true }
                    },
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        });
});
```

---

### 4. Routes
**File**: `routes/web.php`

```php
// Team Leader Dashboard Routes
Route::middleware(['auth', 'team.leader'])->prefix('team-leader')->name('team-leader.')->group(function () {
    // Main dashboard
    Route::get('/dashboard', [TeamLeaderDashboardController::class, 'index'])
        ->name('dashboard');
    
    // Chart APIs
    Route::get('/dashboard/chart/task-status', [TeamLeaderDashboardController::class, 'getTaskStatusChart'])
        ->name('dashboard.chart.task-status');
    
    Route::get('/dashboard/chart/team-workload', [TeamLeaderDashboardController::class, 'getTeamWorkloadChart'])
        ->name('dashboard.chart.team-workload');
    
    // Cache management
    Route::post('/dashboard/clear-cache', [TeamLeaderDashboardController::class, 'clearCache'])
        ->name('dashboard.clear-cache');
});
```

**Register Middleware di** `bootstrap/app.php`:
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'admin' => \App\Http\Middleware\isAdmin::class,
        'team.leader' => \App\Http\Middleware\TeamLeaderMiddleware::class, // ADD THIS
    ]);
})
```

---

### 5. Navigation Menu Update
**File**: `resources/views/layouts/app.blade.php`

Tambahkan menu item untuk Team Leader (setelah Reports menu):

```blade
@php
    $isTeamLead = false;
    if(Auth::check()) {
        $isTeamLead = DB::table('project_members')
            ->where('user_id', Auth::id())
            ->where('role', 'team lead')
            ->exists();
    }
@endphp

@if($isTeamLead || (Auth::check() && Auth::user()->role === 'admin'))
<a href="{{ route('team-leader.dashboard') }}" class="text-gray-600 hover:bg-gray-50 hover:text-gray-900 group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors">
    <svg class="text-gray-400 group-hover:text-gray-500 mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
    </svg>
    Team Leader
</a>
@endif
```

---

## 🎯 Implementation Priority

**Estimated Time: 4-6 hours total**

1. ✅ Controller (DONE) - 1 hour
2. ✅ Middleware (DONE) - 15 minutes
3. ✅ Model Relationships (DONE) - 15 minutes
4. ⏳ Main View - 30 minutes
5. ⏳ 7 Partials - 2 hours
6. ⏳ JavaScript - 45 minutes
7. ⏳ Routes + Middleware Registration - 15 minutes
8. ⏳ Navigation Menu - 15 minutes
9. ⏳ Testing - 1 hour

---

## 🧪 Testing Checklist

### Functional Tests:
- [ ] Team Leader dapat akses dashboard
- [ ] Developer/Designer tidak bisa akses (redirect dengan error)
- [ ] Admin bisa akses sebagai override
- [ ] Stats cards menampilkan angka yang benar
- [ ] My Projects list muncul dengan completion rate
- [ ] Tasks requiring review tampil dengan prioritas correct
- [ ] Approve/Reject buttons berfungsi
- [ ] Team activity menampilkan hours today
- [ ] Comments timeline update real-time
- [ ] Task status chart render correctly
- [ ] Team workload chart render correctly
- [ ] Cache berfungsi (5 menit)
- [ ] Responsive design di mobile/tablet/desktop

### Performance Tests:
- [ ] Dashboard load < 2 detik
- [ ] No N+1 queries (check Laravel Debugbar)
- [ ] Caching berfungsi proper
- [ ] Charts load smooth tanpa lag

---

## 📚 Additional Features (Future Enhancements)

1. **Real-time Notifications**: WebSocket untuk new comments/reviews
2. **Export Reports**: PDF/Excel export untuk statistics
3. **Date Range Filter**: Custom range untuk activity tracking
4. **Project Filter**: Dropdown untuk filter by project
5. **Quick Actions**: Modal untuk quick approve/reject dari dashboard
6. **Time Tracking Widget**: Visual time tracker untuk each member
7. **Deadline Calendar**: Calendar view untuk upcoming deadlines
8. **Performance Metrics**: Team velocity, completion trends

---

**Status**: Foundation complete (Controller, Middleware, Models).
**Next**: Create views and partials following structure above.

**Created**: {{ now()->format('d M Y, H:i') }}
**Laravel Version**: 12.27.1
**PHP Version**: 8.3.10
