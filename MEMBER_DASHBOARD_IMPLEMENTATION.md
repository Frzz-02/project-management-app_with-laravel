# Member Dashboard Implementation Guide

## 📋 Implementation Status

### Backend (✅ COMPLETED)
- ✅ **MemberDashboardController** - Full controller with 9 methods
- ✅ **MemberMiddleware** - Role validation for developer/designer
- ✅ **Routes** - Registered with middleware
- ✅ **Middleware Registration** - Added to bootstrap/app.php

### Frontend (❌ TODO - Follow this guide)
- ❌ Main dashboard view
- ❌ 5 Dashboard partials (stats-cards, active-tasks, upcoming-deadlines, work-summary, recent-feedback, my-projects)
- ❌ JavaScript for interactions
- ❌ Navigation menu update

---

## 🎯 Member Dashboard Overview

Dashboard untuk **Developer/Designer (Member)** yang berisi:

### Features
1. **Overview Stats** - 4 stat cards (assigned tasks, in progress, completed this week, hours today)
2. **Active Tasks** - Tasks dengan Start/Pause buttons, progress tracking, timer display
3. **Upcoming Deadlines** - Tasks dengan deadline ≤7 days, sorted by urgency
4. **Today's Work Summary** - Time logs, completed subtasks, active timer
5. **Recent Feedback** - Comments dari team leader
6. **My Projects** - Projects yang di-join dengan task count

### Key Capabilities
- ⏱️ **Time Tracking** - Start/pause timer untuk tasks
- 📊 **Progress Tracking** - Subtask completion percentage
- 🚨 **Deadline Alerts** - Visual urgency indicators
- 💬 **Feedback System** - View comments dari team leaders
- 📈 **Productivity Stats** - Hours worked, tasks completed

---

## 📂 File Structure

```
resources/views/member/
├── dashboard.blade.php                    # Main dashboard view
└── dashboard/
    └── partials/
        ├── stats-cards.blade.php          # Overview statistics
        ├── active-tasks.blade.php         # Tasks dengan Start/Pause
        ├── upcoming-deadlines.blade.php   # Deadline alerts
        ├── work-summary.blade.php         # Today's work
        ├── recent-feedback.blade.php      # Team leader comments
        └── my-projects.blade.php          # Project list

public/js/member/
└── dashboard.js                           # Interactive JavaScript
```

---

## 🔧 Implementation Steps

### Step 1: Create Main Dashboard View

Create file: `resources/views/member/dashboard.blade.php`

```blade
@extends('layouts.app')

@section('title', 'My Dashboard')

@section('content')
<div class="container mx-auto px-4 py-6">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">
                Welcome back, {{ auth()->user()->full_name }}! 👋
            </h1>
            <p class="text-gray-600 mt-1">Here's what you need to focus on today</p>
        </div>
        <div class="flex items-center gap-3 mt-4 md:mt-0">
            <button onclick="location.reload()" 
                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Refresh
            </button>
        </div>
    </div>

    {{-- Overview Stats Cards --}}
    @include('member.dashboard.partials.stats-cards')

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
        {{-- Active Tasks (Takes 2 columns) --}}
        <div class="xl:col-span-2">
            @include('member.dashboard.partials.active-tasks')
        </div>

        {{-- Upcoming Deadlines (Takes 1 column) --}}
        <div class="xl:col-span-1">
            @include('member.dashboard.partials.upcoming-deadlines')
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Today's Work Summary --}}
        <div>
            @include('member.dashboard.partials.work-summary')
        </div>

        {{-- Recent Feedback --}}
        <div>
            @include('member.dashboard.partials.recent-feedback')
        </div>
    </div>

    {{-- My Projects --}}
    @include('member.dashboard.partials.my-projects')
</div>

{{-- Include JavaScript --}}
<script src="{{ asset('js/member/dashboard.js') }}"></script>
@endsection
```

---

### Step 2: Create Stats Cards Partial

Create file: `resources/views/member/dashboard/partials/stats-cards.blade.php`

```blade
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    {{-- Assigned Tasks --}}
    <div class="backdrop-blur-xl bg-white/70 rounded-2xl border border-white/20 shadow-lg p-6">
        <div class="flex items-center justify-between mb-2">
            <div class="p-3 bg-blue-100 rounded-xl">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
        </div>
        <div class="text-3xl font-bold text-gray-900">
            {{ $overviewStats['assigned_tasks'] }}
        </div>
        <div class="text-sm text-gray-600 mt-1">Assigned Tasks</div>
    </div>

    {{-- In Progress --}}
    <div class="backdrop-blur-xl bg-white/70 rounded-2xl border border-white/20 shadow-lg p-6">
        <div class="flex items-center justify-between mb-2">
            <div class="p-3 bg-yellow-100 rounded-xl">
                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
        </div>
        <div class="text-3xl font-bold text-gray-900">
            {{ $overviewStats['in_progress_tasks'] }}
        </div>
        <div class="text-sm text-gray-600 mt-1">In Progress</div>
    </div>

    {{-- Completed This Week --}}
    <div class="backdrop-blur-xl bg-white/70 rounded-2xl border border-white/20 shadow-lg p-6">
        <div class="flex items-center justify-between mb-2">
            <div class="p-3 bg-green-100 rounded-xl">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <div class="text-3xl font-bold text-gray-900">
            {{ $overviewStats['completed_this_week'] }}
        </div>
        <div class="text-sm text-gray-600 mt-1">Completed This Week</div>
    </div>

    {{-- Hours Today --}}
    <div class="backdrop-blur-xl bg-white/70 rounded-2xl border border-white/20 shadow-lg p-6">
        <div class="flex items-center justify-between mb-2">
            <div class="p-3 bg-purple-100 rounded-xl">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <div class="text-3xl font-bold text-gray-900">
            {{ number_format($overviewStats['hours_today'], 1) }}h
        </div>
        <div class="text-sm text-gray-600 mt-1">Hours Today</div>
    </div>
</div>
```

---

### Step 3: Create Active Tasks Partial

Create file: `resources/views/member/dashboard/partials/active-tasks.blade.php`

```blade
<div class="backdrop-blur-xl bg-white/70 rounded-2xl border border-white/20 shadow-lg">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
            My Active Tasks
        </h2>
        <p class="text-sm text-gray-600">Tasks currently assigned to you</p>
    </div>

    <div class="p-6 space-y-4">
        @forelse($myActiveTasks as $assignment)
        <div class="p-4 bg-gradient-to-br from-white to-gray-50 rounded-xl border border-gray-200 hover:shadow-md transition-all">
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                {{-- Task Info --}}
                <div class="flex-1">
                    {{-- Title & Status Badge --}}
                    <div class="flex items-start gap-3 mb-2">
                        <h3 class="text-base font-semibold text-gray-900 flex-1">
                            {{ $assignment->card->card_title }}
                        </h3>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold
                            @if($assignment->assignment_status === 'in progress') bg-yellow-100 text-yellow-800
                            @else bg-gray-100 text-gray-700 @endif">
                            {{ ucwords($assignment->assignment_status) }}
                        </span>
                    </div>

                    {{-- Project Info --}}
                    <div class="flex items-center gap-2 text-xs text-gray-600 mb-3">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                        </svg>
                        <span>{{ $assignment->card->board->project->project_name }}</span>
                        <span class="text-gray-400">•</span>
                        <span>{{ $assignment->card->board->board_name }}</span>
                    </div>

                    {{-- Progress Bar --}}
                    @if($assignment->card->subtasks_total > 0)
                    <div class="mb-3">
                        <div class="flex items-center justify-between text-xs text-gray-600 mb-1">
                            <span>Subtasks Progress</span>
                            <span class="font-medium">{{ $assignment->card->subtasks_completed }}/{{ $assignment->card->subtasks_total }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full transition-all" 
                                 style="width: {{ $assignment->card->progress_percentage }}%"></div>
                        </div>
                    </div>
                    @endif

                    {{-- Meta Info --}}
                    <div class="flex flex-wrap items-center gap-3 text-xs text-gray-600">
                        {{-- Priority --}}
                        <span class="flex items-center gap-1 px-2 py-1 rounded-md
                            @if($assignment->card->priority === 'high') bg-red-100 text-red-700
                            @elseif($assignment->card->priority === 'medium') bg-yellow-100 text-yellow-700
                            @else bg-green-100 text-green-700 @endif">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 2L8.5 5H5l3 3-1 4 3-2 3 2-1-4 3-3h-3.5L10 2z"/>
                            </svg>
                            {{ ucfirst($assignment->card->priority) }}
                        </span>

                        {{-- Time Spent --}}
                        @if($assignment->card->time_spent > 0)
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ number_format($assignment->card->time_spent, 1) }}h logged
                        </span>
                        @endif

                        {{-- Deadline --}}
                        @if($assignment->card->due_date)
                        <span class="flex items-center gap-1 font-medium
                            @if($assignment->card->is_overdue) text-red-600
                            @elseif($assignment->card->is_urgent) text-orange-600
                            @else text-gray-600 @endif">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            @if($assignment->card->is_overdue)
                                {{ abs($assignment->card->days_until_due) }} days overdue!
                            @elseif($assignment->card->days_until_due === 0)
                                Due today!
                            @else
                                {{ $assignment->card->days_until_due }} days left
                            @endif
                        </span>
                        @endif
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex lg:flex-col items-center gap-2">
                    @if($assignment->assignment_status === 'in progress')
                        {{-- Active Timer Display --}}
                        @if($assignment->card->active_timer)
                        <div class="px-3 py-2 bg-green-50 border border-green-200 rounded-lg text-center mb-2">
                            <div class="text-xs text-green-600 font-medium mb-1">Timer Running</div>
                            <div class="text-lg font-mono font-bold text-green-700" id="timer-{{ $assignment->card->id }}">
                                {{ gmdate('H:i:s', now()->diffInSeconds($assignment->card->active_timer->start_time)) }}
                            </div>
                        </div>
                        @endif

                        {{-- Pause Button --}}
                        <button onclick="pauseTask({{ $assignment->card->id }})"
                                class="px-4 py-2 bg-red-100 hover:bg-red-200 text-red-700 rounded-lg text-sm font-medium transition-all flex items-center gap-2 w-full justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Pause
                        </button>
                    @else
                        {{-- Start Button --}}
                        <button onclick="startTask({{ $assignment->card->id }})"
                                class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium transition-all flex items-center gap-2 w-full justify-center shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Start Task
                        </button>
                    @endif

                    {{-- View Details Link --}}
                    <a href="{{ route('boards.show', ['board' => $assignment->card->board->id]) }}" 
                       class="px-4 py-2 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-lg text-sm font-medium transition-all w-full text-center">
                        View Details
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-12">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-green-100 to-emerald-100 rounded-full mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">No Active Tasks</h3>
            <p class="text-sm text-gray-600">You're all caught up! Great job! 🎉</p>
        </div>
        @endforelse
    </div>
</div>
```

---

### Step 4: Create Upcoming Deadlines Partial

Create file: `resources/views/member/dashboard/partials/upcoming-deadlines.blade.php`

```blade
<div class="backdrop-blur-xl bg-white/70 rounded-2xl border border-white/20 shadow-lg h-full">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
            <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Upcoming Deadlines
        </h2>
        <p class="text-sm text-gray-600">Tasks due within 7 days</p>
    </div>

    <div class="p-6">
        @forelse($upcomingDeadlines as $card)
        <div class="mb-4 last:mb-0 p-4 bg-gradient-to-br from-white to-gray-50 rounded-xl border-l-4
            @if($card->urgency_color === 'red') border-red-500
            @elseif($card->urgency_color === 'orange') border-orange-500
            @elseif($card->urgency_color === 'yellow') border-yellow-500
            @else border-blue-500 @endif">
            
            {{-- Task Title --}}
            <h3 class="text-sm font-semibold text-gray-900 mb-1">
                {{ $card->card_title }}
            </h3>

            {{-- Project Info --}}
            <p class="text-xs text-gray-600 mb-2">
                {{ $card->board->project->project_name }}
            </p>

            {{-- Deadline Info --}}
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium
                    @if($card->urgency_color === 'red') text-red-600
                    @elseif($card->urgency_color === 'orange') text-orange-600
                    @elseif($card->urgency_color === 'yellow') text-yellow-600
                    @else text-blue-600 @endif">
                    @if($card->is_overdue)
                        🔴 {{ abs($card->days_until_due) }} days overdue
                    @elseif($card->is_today)
                        ⚠️ Due today!
                    @else
                        📅 {{ $card->days_until_due }} days left
                    @endif
                </span>

                {{-- Priority Badge --}}
                <span class="px-2 py-0.5 rounded text-xs font-semibold
                    @if($card->priority === 'high') bg-red-100 text-red-700
                    @elseif($card->priority === 'medium') bg-yellow-100 text-yellow-700
                    @else bg-green-100 text-green-700 @endif">
                    {{ ucfirst($card->priority) }}
                </span>
            </div>
        </div>
        @empty
        <div class="text-center py-8">
            <div class="inline-flex items-center justify-center w-12 h-12 bg-green-100 rounded-full mb-3">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <p class="text-sm text-gray-600">No upcoming deadlines</p>
        </div>
        @endforelse
    </div>
</div>
```

---

### Step 5: Create Work Summary Partial

Create file: `resources/views/member/dashboard/partials/work-summary.blade.php`

```blade
<div class="backdrop-blur-xl bg-white/70 rounded-2xl border border-white/20 shadow-lg h-full">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            Today's Work Summary
        </h2>
        <p class="text-sm text-gray-600">Your productivity today</p>
    </div>

    <div class="p-6">
        {{-- Summary Stats --}}
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="text-center">
                <div class="text-2xl font-bold text-purple-600">
                    {{ number_format($todayWorkSummary['total_hours'], 1) }}h
                </div>
                <div class="text-xs text-gray-600 mt-1">Total Hours</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-blue-600">
                    {{ $todayWorkSummary['sessions_count'] }}
                </div>
                <div class="text-xs text-gray-600 mt-1">Sessions</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-green-600">
                    {{ $todayWorkSummary['completed_subtasks'] }}
                </div>
                <div class="text-xs text-gray-600 mt-1">Subtasks Done</div>
            </div>
        </div>

        {{-- Active Timer --}}
        @if($todayWorkSummary['active_timer'])
        <div class="mb-6 p-4 bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-xl">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs text-green-600 font-medium mb-1">Currently Working On</div>
                    <div class="text-sm font-semibold text-gray-900">
                        {{ $todayWorkSummary['active_timer']->card->card_title }}
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-xs text-green-600 mb-1">Timer</div>
                    <div class="text-xl font-mono font-bold text-green-700" id="active-timer">
                        {{ gmdate('H:i:s', now()->diffInSeconds($todayWorkSummary['active_timer']->start_time)) }}
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Recent Time Logs --}}
        <div class="space-y-2">
            <h3 class="text-xs font-semibold text-gray-700 uppercase mb-3">Recent Sessions</h3>
            @forelse($todayWorkSummary['time_logs']->take(5) as $log)
            <div class="flex items-center justify-between text-sm p-2 hover:bg-gray-50 rounded-lg transition-colors">
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 bg-purple-500 rounded-full"></div>
                    <span class="text-gray-700 text-xs">{{ Str::limit($log->card->card_title, 30) }}</span>
                </div>
                <span class="text-xs font-medium text-gray-600">
                    {{ number_format($log->duration_minutes / 60, 1) }}h
                </span>
            </div>
            @empty
            <p class="text-sm text-gray-500 text-center py-4">No work sessions yet today</p>
            @endforelse
        </div>
    </div>
</div>
```

---

### Step 6: Create Recent Feedback Partial

Create file: `resources/views/member/dashboard/partials/recent-feedback.blade.php`

```blade
<div class="backdrop-blur-xl bg-white/70 rounded-2xl border border-white/20 shadow-lg h-full">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            Recent Feedback
        </h2>
        <p class="text-sm text-gray-600">Comments from team leaders</p>
    </div>

    <div class="p-6 space-y-4 max-h-96 overflow-y-auto">
        @forelse($recentFeedback as $comment)
        <div class="p-4 bg-gradient-to-br from-white to-blue-50/30 rounded-xl border border-gray-200">
            {{-- Comment Header --}}
            <div class="flex items-start gap-3 mb-2">
                <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white text-sm font-semibold flex-shrink-0">
                    {{ substr($comment->user->full_name, 0, 1) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-sm font-semibold text-gray-900">
                            {{ $comment->user->full_name }}
                        </span>
                        <span class="px-2 py-0.5 bg-blue-100 text-blue-700 text-xs font-medium rounded">
                            Team Lead
                        </span>
                    </div>
                    <p class="text-xs text-gray-600">
                        {{ $comment->time_ago }} • {{ $comment->card->card_title }}
                    </p>
                </div>
            </div>

            {{-- Comment Content --}}
            <p class="text-sm text-gray-700 leading-relaxed ml-11">
                {{ $comment->comment }}
            </p>

            {{-- Project Context --}}
            <div class="mt-2 ml-11 text-xs text-gray-500">
                📁 {{ $comment->card->board->project->project_name }}
            </div>
        </div>
        @empty
        <div class="text-center py-8">
            <div class="inline-flex items-center justify-center w-12 h-12 bg-blue-100 rounded-full mb-3">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
            </div>
            <p class="text-sm text-gray-600">No feedback yet</p>
        </div>
        @endforelse
    </div>
</div>
```

---

### Step 7: Create My Projects Partial

Create file: `resources/views/member/dashboard/partials/my-projects.blade.php`

```blade
<div class="backdrop-blur-xl bg-white/70 rounded-2xl border border-white/20 shadow-lg">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
            </svg>
            My Projects
        </h2>
        <p class="text-sm text-gray-600">Projects you're working on</p>
    </div>

    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($myProjects as $membership)
            <div class="p-5 bg-gradient-to-br from-white to-gray-50 rounded-xl border border-gray-200 hover:shadow-md transition-all">
                {{-- Project Header --}}
                <div class="flex items-start justify-between mb-3">
                    <h3 class="text-base font-semibold text-gray-900 flex-1">
                        {{ $membership->project->project_name }}
                    </h3>
                    <span class="px-2 py-1 rounded-md text-xs font-semibold
                        @if($membership->role === 'developer') bg-blue-100 text-blue-700
                        @else bg-purple-100 text-purple-700 @endif">
                        {{ ucfirst($membership->role) }}
                    </span>
                </div>

                {{-- Project Creator --}}
                <p class="text-xs text-gray-600 mb-3 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    by {{ $membership->project->creator->full_name }}
                </p>

                {{-- Stats --}}
                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div class="text-center p-2 bg-white rounded-lg border border-gray-200">
                        <div class="text-lg font-bold text-gray-900">
                            {{ $membership->my_tasks_count }}
                        </div>
                        <div class="text-xs text-gray-600">My Tasks</div>
                    </div>
                    <div class="text-center p-2 bg-white rounded-lg border border-gray-200">
                        <div class="text-lg font-bold text-blue-600">
                            {{ $membership->active_tasks_count }}
                        </div>
                        <div class="text-xs text-gray-600">Active</div>
                    </div>
                </div>

                {{-- Boards Count --}}
                <div class="flex items-center justify-between text-xs text-gray-600">
                    <span>{{ $membership->project->boards->count() }} boards</span>
                    <a href="{{ route('projects.show', $membership->project) }}" 
                       class="text-blue-600 hover:text-blue-700 font-medium">
                        View →
                    </a>
                </div>
            </div>
            @empty
            <div class="col-span-full text-center py-12">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">No Projects Yet</h3>
                <p class="text-sm text-gray-600">You haven't been assigned to any projects</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
```

---

### Step 8: Create JavaScript for Dashboard Interactions

Create file: `public/js/member/dashboard.js`

```javascript
/**
 * Member Dashboard JavaScript
 * Handles Start/Pause task actions dan real-time timer updates
 */

// Start task - begin working and start timer
function startTask(cardId) {
    if (confirm('Start working on this task? Timer will begin tracking your time.')) {
        fetch(`/member/tasks/${cardId}/start`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            // Show success message
            showNotification('Task started! Timer is running. ⏱️', 'success');
            
            // Reload page after 1 second
            setTimeout(() => {
                location.reload();
            }, 1000);
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Failed to start task. Please try again.', 'error');
        });
    }
}

// Pause task - stop timer
function pauseTask(cardId) {
    if (confirm('Pause this task? Your time will be saved.')) {
        fetch(`/member/tasks/${cardId}/pause`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            showNotification('Task paused. Take a break! ☕', 'success');
            
            setTimeout(() => {
                location.reload();
            }, 1000);
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Failed to pause task. Please try again.', 'error');
        });
    }
}

// Show notification helper
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg transition-all transform translate-x-0 ${
        type === 'success' ? 'bg-green-500' :
        type === 'error' ? 'bg-red-500' :
        'bg-blue-500'
    } text-white font-medium`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        notification.style.transform = 'translateX(120%)';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Update all timers every second
function updateTimers() {
    // Update individual task timers
    document.querySelectorAll('[id^="timer-"]').forEach(timerElement => {
        const startTime = timerElement.dataset.startTime;
        if (startTime) {
            const elapsed = Math.floor((Date.now() - new Date(startTime)) / 1000);
            const hours = Math.floor(elapsed / 3600).toString().padStart(2, '0');
            const minutes = Math.floor((elapsed % 3600) / 60).toString().padStart(2, '0');
            const seconds = (elapsed % 60).toString().padStart(2, '0');
            timerElement.textContent = `${hours}:${minutes}:${seconds}`;
        }
    });
    
    // Update active timer in work summary
    const activeTimer = document.getElementById('active-timer');
    if (activeTimer && activeTimer.dataset.startTime) {
        const startTime = activeTimer.dataset.startTime;
        const elapsed = Math.floor((Date.now() - new Date(startTime)) / 1000);
        const hours = Math.floor(elapsed / 3600).toString().padStart(2, '0');
        const minutes = Math.floor((elapsed % 3600) / 60).toString().padStart(2, '0');
        const seconds = (elapsed % 60).toString().padStart(2, '0');
        activeTimer.textContent = `${hours}:${minutes}:${seconds}`;
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Set start time data attributes for timers
    document.querySelectorAll('[id^="timer-"]').forEach(timerElement => {
        const timeText = timerElement.textContent.trim();
        if (timeText) {
            // Parse H:i:s format and calculate start time
            const [hours, minutes, seconds] = timeText.split(':').map(Number);
            const elapsedMs = (hours * 3600 + minutes * 60 + seconds) * 1000;
            const startTime = new Date(Date.now() - elapsedMs);
            timerElement.dataset.startTime = startTime.toISOString();
        }
    });
    
    const activeTimer = document.getElementById('active-timer');
    if (activeTimer) {
        const timeText = activeTimer.textContent.trim();
        if (timeText) {
            const [hours, minutes, seconds] = timeText.split(':').map(Number);
            const elapsedMs = (hours * 3600 + minutes * 60 + seconds) * 1000;
            const startTime = new Date(Date.now() - elapsedMs);
            activeTimer.dataset.startTime = startTime.toISOString();
        }
    }
    
    // Update timers every second
    setInterval(updateTimers, 1000);
    
    console.log('Member Dashboard initialized');
});
```

---

### Step 9: Update Navigation Menu (Optional)

Add link to member dashboard in your main navigation. Update `resources/views/layouts/app.blade.php`:

```blade
{{-- Add this in the navigation section --}}
@if(auth()->check())
    @php
        $isMember = \App\Models\ProjectMember::where('user_id', auth()->id())
            ->whereIn('role', ['developer', 'designer'])
            ->exists();
    @endphp
    
    @if($isMember)
    <a href="{{ route('member.dashboard') }}" 
       class="{{ request()->routeIs('member.*') ? 'active' : '' }}">
        <svg><!-- Dashboard icon --></svg>
        My Dashboard
    </a>
    @endif
@endif
```

---

## 🧪 Testing Checklist

After implementing all views:

### 1. Access & Authorization
- [ ] Member can access `/member/dashboard`
- [ ] Non-member gets redirected with error message
- [ ] Admin can access (bypass)

### 2. Overview Stats
- [ ] Assigned tasks count correct
- [ ] In progress tasks count correct
- [ ] Completed this week accurate
- [ ] Hours today calculated correctly

### 3. Active Tasks
- [ ] All assigned/in progress tasks visible
- [ ] Start button works → status changes to "in progress"
- [ ] Pause button works → timer stops, duration saved
- [ ] Progress bars show correct percentages
- [ ] Priority badges display correctly
- [ ] Deadline warnings show with correct colors

### 4. Timer Functionality
- [ ] Timer starts when clicking "Start Task"
- [ ] Timer displays and updates every second
- [ ] Timer pauses correctly
- [ ] Duration saved to database
- [ ] Active timer shown in work summary

### 5. Upcoming Deadlines
- [ ] Tasks due within 7 days shown
- [ ] Overdue tasks marked red
- [ ] Critical tasks (≤2 days) marked orange
- [ ] Sorted by due date and priority

### 6. Work Summary
- [ ] Total hours calculated correctly
- [ ] Sessions count accurate
- [ ] Completed subtasks count correct
- [ ] Recent sessions listed
- [ ] Active timer displayed if working

### 7. Recent Feedback
- [ ] Comments from team leaders shown
- [ ] Own comments excluded
- [ ] Time ago formatted correctly
- [ ] Task context displayed

### 8. My Projects
- [ ] All joined projects listed
- [ ] Task counts correct
- [ ] Role badges displayed
- [ ] Links work correctly

### 9. Responsive Design
- [ ] Mobile view (320px - 768px)
- [ ] Tablet view (769px - 1024px)
- [ ] Desktop view (1025px+)
- [ ] All buttons clickable on mobile

### 10. Performance
- [ ] Dashboard loads within 2 seconds
- [ ] Cache working (5 minute TTL)
- [ ] No N+1 queries
- [ ] Timers update smoothly

---

## 🚀 Testing Instructions

### 1. Assign Member Role to Test User

Run this script to assign member role:

```php
<?php
// assign_member.php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Project;
use App\Models\ProjectMember;

$user = User::first(); // Or find specific user
$project = Project::first(); // Or find specific project

ProjectMember::updateOrCreate(
    [
        'project_id' => $project->id,
        'user_id' => $user->id,
    ],
    [
        'role' => 'developer', // atau 'designer'
        'joined_at' => now(),
    ]
);

echo "✓ {$user->full_name} assigned as Developer in {$project->project_name}\n";
echo "Login as: {$user->email}\n";
echo "Access: /member/dashboard\n";
```

Run: `php assign_member.php`

### 2. Create Test Data

Assign some cards to the member and set various statuses and deadlines for testing.

---

## 🎨 Design Highlights

- **Glassmorphism UI** - `backdrop-blur-xl` with `bg-white/70`
- **Color-coded priorities** - Red (high), Yellow (medium), Green (low)
- **Urgency indicators** - Visual cues for deadlines
- **Real-time timers** - Updates every second using JavaScript
- **Smooth transitions** - `transition-all` for hover effects
- **Responsive grid** - Adapts to all screen sizes
- **Empty states** - Friendly messages when no data

---

## 🔮 Future Enhancements

1. **Notifications** - Real-time notifications for new assignments, feedback, deadlines
2. **Task Notes** - Quick notes feature while working
3. **Daily Goals** - Set and track daily task goals
4. **Productivity Charts** - Weekly/monthly productivity trends
5. **Collaboration** - See who else is working (live status)
6. **Pomodoro Timer** - Built-in focus timer
7. **Task Recommendations** - AI suggests next task based on priority
8. **Mobile App** - PWA for mobile devices

---

## 📚 Related Documentation

- Team Leader Dashboard: `TEAM_LEADER_DASHBOARD_IMPLEMENTATION.md`
- API Documentation: `API_TIME_TRACKING_DOCUMENTATION.md`
- Time Tracking Updates: `API_TIME_TRACKING_UPDATES.md`
- Authorization Guide: `AUTHORIZATION_GUIDE.md`

---

## ⚠️ Important Notes

1. **Cache Management**: Dashboard data cached for 5 minutes. Clear with:
   ```php
   Cache::forget('member_dashboard_' . auth()->id());
   ```

2. **Timer Accuracy**: JavaScript timers update every second. For production, consider websockets for real-time sync.

3. **Database Queries**: Controller uses eager loading to prevent N+1 queries. Don't modify query structure without testing performance.

4. **Authorization**: Middleware checks `project_members` table. If user has no memberships, they can't access dashboard.

5. **Browser Compatibility**: Tested on Chrome, Firefox, Safari. IE11 not supported (uses modern CSS features).

---

**Implementation Time Estimate**: 2-3 hours for all views + testing

**Priority**: HIGH - Core member functionality

**Dependencies**: Requires `CardAssignment`, `TimeLog`, `Subtask`, `Comment` models functioning correctly

---

Happy coding! 🚀
