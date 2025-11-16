
<div class="backdrop-blur-xl bg-white/70 rounded-2xl border border-white/20 shadow-lg">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">Tasks Requiring Review</h2>
        <p class="text-sm text-gray-600">Review and approve/reject submitted tasks</p>
    </div>
    
    <div class="p-6">
        @forelse($tasksRequiringReview as $task)
        <div class="mb-4 last:mb-0 p-4 bg-gradient-to-br from-white to-gray-50 rounded-xl border border-gray-200 hover:shadow-md transition-all">
            {{-- Header: Task Title & Priority Badge --}}
            <div class="flex items-start justify-between mb-3">
                <div class="flex-1">
                    <h3 class="text-base font-semibold text-gray-900 mb-1">
                        {{ $task->card_title }}
                    </h3>
                    <div class="flex items-center gap-2 text-xs text-gray-600">
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                            </svg>
                            {{ $task->board->project->project_name }}
                        </span>
                        <span class="text-gray-400">•</span>
                        <span>{{ $task->board->board_name }}</span>
                    </div>
                </div>
                
                {{-- Priority Badge --}}
                <span class="ml-3 px-3 py-1 rounded-full text-xs font-semibold
                    @if($task->priority === 'high') bg-red-100 text-red-800
                    @elseif($task->priority === 'medium') bg-yellow-100 text-yellow-800
                    @else bg-green-100 text-green-800 @endif">
                    {{ ucfirst($task->priority) }}
                </span>
            </div>
            
            {{-- Task Details --}}
            <div class="flex flex-wrap items-center gap-3 mb-3 text-sm">
                {{-- Created By --}}
                <div class="flex items-center gap-1 text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span>{{ $task->creator->full_name }}</span>
                </div>
                
                {{-- Assigned Members --}}
                @if($task->assignments->count() > 0)
                <div class="flex items-center gap-1 text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span>{{ $task->assignments->count() }} assigned</span>
                </div>
                @endif
                
                {{-- Due Date with Urgency --}}
                @if($task->due_date)
                <div class="flex items-center gap-1 font-medium
                    @if($task->urgency_color === 'red') text-red-600
                    @elseif($task->urgency_color === 'orange') text-orange-600
                    @elseif($task->urgency_color === 'yellow') text-yellow-600
                    @else text-blue-600 @endif">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    @if($task->days_until_due < 0)
                        <span>{{ abs($task->days_until_due) }} days overdue</span>
                    @elseif($task->days_until_due === 0)
                        <span>Due today!</span>
                    @else
                        <span>{{ $task->days_until_due }} days left</span>
                    @endif
                </div>
                @endif
            </div>
            
            {{-- Description Preview --}}
            @if($task->description)
            <p class="text-sm text-gray-600 mb-3 line-clamp-2">
                {{ $task->description }}
            </p>
            @endif
            
            {{-- Action Buttons --}}
            <div class="flex items-center justify-between pt-3 border-t border-gray-200">
                <a href="{{ route('boards.show', ['board' => $task->board->id]) }}" 
                   class="text-sm text-blue-600 hover:text-blue-700 font-medium flex items-center gap-1 hover:underline">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    View Details
                </a>
                
                <div class="flex items-center gap-2">
                    {{-- Reject Button --}}
                    {{-- <button onclick="showReviewModal({{ $task->id }}, 'rejected')"
                            class="px-3 py-1.5 bg-white hover:bg-red-50 text-red-600 border border-red-200 hover:border-red-300 rounded-lg text-sm font-medium transition-all flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Reject
                    </button> --}}
                    
                    {{-- Approve Button --}}
                    {{-- <button onclick="showReviewModal({{ $task->id }}, 'approved')"
                            class="px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium transition-all flex items-center gap-1.5 shadow-sm hover:shadow-md">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Approve
                    </button> --}}
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
            <h3 class="text-lg font-semibold text-gray-900 mb-2">All Caught Up!</h3>
            <p class="text-sm text-gray-600">No tasks requiring review at the moment.</p>
        </div>
        @endforelse
    </div>
</div>