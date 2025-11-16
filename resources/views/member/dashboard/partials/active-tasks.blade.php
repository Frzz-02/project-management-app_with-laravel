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
                <div class="flex lg:flex-col items-center gap-2 lg:min-w-[140px]">
                    @if($assignment->assignment_status === 'in progress')
                        {{-- Active Timer Display --}}
                        @if($assignment->card->active_timer)
                        {{-- <div class="px-3 py-2 bg-green-50 border border-green-200 rounded-lg text-center mb-2 w-full">
                            <div class="text-xs text-green-600 font-medium mb-1">Timer Running</div>
                            <div class="text-lg font-mono font-bold text-green-700" id="timer-{{ $assignment->card->id }}">
                                {{ gmdate('H:i:s', now()->diffInSeconds($assignment->card->active_timer->start_time)) }}
                            </div>
                        </div> --}}
                        @endif

                        {{-- Pause Button --}}
                        {{-- <button onclick="pauseTask({{ $assignment->card->id }})"
                                class="px-4 py-2 bg-red-100 hover:bg-red-200 text-red-700 rounded-lg text-sm font-medium transition-all flex items-center gap-2 w-full justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Pause
                        </button> --}}
                    @else
                        {{-- Start Button --}}
                        {{-- <button onclick="startTask({{ $assignment->card->id }})"
                                class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium transition-all flex items-center gap-2 w-full justify-center shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Start Task
                        </button> --}}
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
