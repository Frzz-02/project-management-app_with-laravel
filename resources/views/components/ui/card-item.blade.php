@props(['card', 'board'])

@php
    // Check if current user has active time tracking for this card
    $currentUser = Auth::user();
    $activeTimeLog = null;
    if ($card->timeLogs) {
        $activeTimeLog = $card->timeLogs->where('user_id', $currentUser->id)
            ->whereNull('end_time')
            ->whereNull('subtask_id')
            ->first();
    }
    $hasActiveTracking = $activeTimeLog !== null;
    
    // Check if user is assigned to this card
    $isUserAssigned = $card->assignments?->contains('user_id', $currentUser->id) ?? false;
    
    // Check user role in project
    $projectMember = $board->project->members->where('user_id', $currentUser->id)->first();
    $userRole = $projectMember?->role ?? 'member';
    $isDesignerOrDeveloper = in_array($userRole, ['designer', 'developer']);

    $cardData = [
        'id' => $card->id,
        'title' => $card->card_title,
        'description' => $card->description,
        'status' => $card->status,
        'priority' => $card->priority,
        'due_date' => $card->due_date?->format('Y-m-d H:i:s'),
        'estimated_hours' => $card->estimated_hours,
        'actual_hours' => $card->actual_hours,
        'created_at' => $card->created_at?->format('Y-m-d H:i:s'),
        'creator_name' => $card->creator?->username ?? 'Unknown',
        'assignments' => $card->assignments->map(fn($assignment) => [
            'id' => $assignment->id,
            'user_id' => $assignment->user_id,
            'user_name' => $assignment->user->username ?? 'Unknown',
            'user_email' => $assignment->user->email ?? ''
        ])->toArray(),
        'comments_count' => $card->comments->count(),
        'subtasks_count' => $card->subtasks->count(),
        // Time tracking info for Create Subtasks button
        'has_active_tracking' => $hasActiveTracking,
        'is_user_assigned' => $isUserAssigned,
        'user_role' => $userRole,
        'is_designer_or_developer' => $isDesignerOrDeveloper,
    ];
@endphp

<div class="bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-all duration-200 cursor-pointer group"
     @click="
        console.log('🚀 Card clicked:', {{ \Illuminate\Support\Js::from($cardData) }}); 
        $dispatch('card-detail-modal', {{ \Illuminate\Support\Js::from($cardData) }}); 
        $event.stopPropagation()
     ">
    
    <!-- Card Header -->
    <div class="p-4 pb-2">
        <div class="flex items-start justify-between mb-2">
            <h4 class="font-medium text-gray-900 text-sm leading-5 group-hover:text-indigo-600 transition-colors">
                {{ $card->card_title }}
            </h4>
            
            <!-- Card Actions Dropdown -->
            @can('update', $card)
                
                <div class="relative" x-data="{ open: false }">
                    <button @click.stop="open = !open" class="opacity-0 group-hover:opacity-100 p-1 text-gray-400 hover:text-gray-600 rounded-md transition-all">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                        </svg>
                    </button>
                    
                    <div x-show="open" @click.away="open = false" 
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="transform opacity-0 scale-95"
                        x-transition:enter-end="transform opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="transform opacity-100 scale-100"
                        x-transition:leave-end="transform opacity-0 scale-95"
                        class="absolute right-0 top-8 w-48 bg-white rounded-md shadow-lg border border-gray-200 z-10">
                        <div class="py-1">
                            <button @click.stop="$dispatch('edit-card-modal', {{ \Illuminate\Support\Js::from($card) }}); open = false" 
                                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Edit Card
                            </button>
                            <button @click.stop="
                                        console.log('🗑️ Delete button clicked for card:', {{ $card->id }}); 
                                        window.dispatchEvent(new CustomEvent('show-delete-modal', { 
                                            detail: { 
                                                cardId: {{ $card->id }}, 
                                                cardTitle: '{{ addslashes($card->card_title) }}' 
                                            } 
                                        })); 
                                        console.log('📤 Event dispatched');
                                        open = false
                                    " 
                                    class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Delete Card
                            </button>
                        </div>
                    </div>
                </div>

            @endcan
        </div>

        <!-- Card Description -->
        @if($card->description)
            <p class="text-gray-600 text-xs leading-4 mb-3 line-clamp-2">
                {{ Str::limit($card->description, 80) }}
            </p>
        @endif
    </div>

    <!-- Card Body -->
    <div class="px-4 pb-2">
        <!-- Priority & Status Badges -->
        <div class="flex items-center gap-2 mb-3">
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $card->priority_badge_color }}">
                {{ ucfirst($card->priority) }}
            </span>
            @if($card->due_date)
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium 
                    {{ $card->due_date->isPast() && $card->status !== 'done' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-600' }}">
                    {{ $card->due_date->format('M d') }}
                </span>
            @endif
        </div>

        <!-- Progress Info -->
        @if($card->estimated_hours || $card->actual_hours)
            <div class="mb-3">
                <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                    <span>Progress</span>
                    <span>
                        {{ $card->actual_hours ?? 0 }}h 
                        @if($card->estimated_hours)
                            / {{ $card->estimated_hours }}h
                        @endif
                    </span>
                </div>
                @if($card->estimated_hours)
                    @php
                        $percentage = min(100, ($card->actual_hours / $card->estimated_hours) * 100);
                    @endphp
                    <div class="w-full bg-gray-200 rounded-full h-1.5">
                        <div class="h-1.5 rounded-full {{ $percentage > 100 ? 'bg-red-500' : 'bg-blue-500' }}" 
                             style="width: {{ $percentage }}%"></div>
                    </div>
                @endif
            </div>
        @endif

        <!-- Card Footer -->
        <div class="flex items-center justify-between">
            <!-- Assignees/Creator -->
            <div class="flex items-center space-x-2">
                @if($card->creator)
                    <div class="flex items-center">
                        <div class="w-6 h-6 bg-gradient-to-br from-indigo-400 to-purple-500 rounded-full flex items-center justify-center text-white text-xs font-medium">
                            {{ substr($card->creator->username, 0, 1) }}
                        </div>
                    </div>
                @endif

                <!-- Show assignments if available -->
                @if($card->assignments && $card->assignments->count() > 0)
                    <div class="flex -space-x-1">
                        @foreach($card->assignments->take(3) as $assignment)
                            <div class="w-6 h-6 bg-gradient-to-br from-blue-400 to-indigo-500 rounded-full border-2 border-white flex items-center justify-center text-white text-xs font-medium"
                                 title="{{ $assignment->user->username }}">
                                {{ substr($assignment->user->username, 0, 1) }}
                            </div>
                        @endforeach
                        @if($card->assignments->count() > 3)
                            <div class="w-6 h-6 bg-gray-300 rounded-full border-2 border-white flex items-center justify-center text-gray-600 text-xs font-medium">
                                +{{ $card->assignments->count() - 3 }}
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Card Metrics -->
            <div class="flex items-center space-x-3 text-gray-400">
                <!-- Comments -->
                @if($card->comments->count() > 0)
                    <div class="flex items-center space-x-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                        <span class="text-xs">{{ $card->comments->count() }}</span>
                    </div>
                @endif

                <!-- Subtasks -->
                @if($card->subtasks->count() > 0)
                    <div class="flex items-center space-x-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                        </svg>
                        <span class="text-xs">{{ $card->subtasks->where('is_completed', true)->count() }}/{{ $card->subtasks->count() }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Status Change Buttons for Different Roles -->
    <div class="px-4 pb-3">
        @php
            $currentUser = Auth::user();
            $projectMember = $board->project->members->where('user_id', $currentUser->id)->first();
            $userRole = $projectMember?->role ?? 'member';
            $isAssigned = $card->assignments?->contains('user_id', $currentUser->id) ?? false;
            
            // Debug: Check if timeLogs relationship is loaded
            $timeLogsCount = $card->timeLogs ? $card->timeLogs->count() : 0;
            
            // Check if card has active tracking (use collection, not query builder)
            $activeTimeLog = null;
            if ($card->timeLogs) {
                $activeTimeLog = $card->timeLogs->where('user_id', $currentUser->id)
                    ->whereNull('end_time')
                    ->whereNull('subtask_id')
                    ->first();
            }
            
            $hasActiveTracking = $activeTimeLog !== null;
            
            // Calculate elapsed time if tracking
            $elapsedSeconds = 0;
            if ($hasActiveTracking && $activeTimeLog->start_time) {
                $elapsedSeconds = now()->diffInSeconds($activeTimeLog->start_time);
            }
        @endphp
        
        {{-- Debug info (remove after testing) --}}
        @if($isAssigned && in_array($userRole, ['designer', 'developer']))
            <!-- Debug: TimeLogs Count: {{ $timeLogsCount }}, Has Active: {{ $hasActiveTracking ? 'YES' : 'NO' }}, Elapsed: {{ $elapsedSeconds }}s -->
        @endif

        {{-- Start Task / Timer Display: For Designer/Developer with assigned card --}}
        @if($isAssigned && in_array($userRole, ['designer', 'developer']))
            @if($hasActiveTracking)
                {{-- Timer Display with Stop Button --}}
                <div class="w-full bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-md px-3 py-2"
                     x-data="{ 
                         elapsed: {{ $elapsedSeconds }},
                         interval: null,
                         init() {
                             console.log('⏱️ Timer initialized with elapsed:', this.elapsed, 'seconds');
                             this.interval = setInterval(() => {
                                 this.elapsed++;
                             }, 1000);
                         },
                         formatTime() {
                             const hours = Math.floor(this.elapsed / 3600);
                             const minutes = Math.floor((this.elapsed % 3600) / 60);
                             const seconds = this.elapsed % 60;
                             
                             const hh = String(hours).padStart(2, '0');
                             const mm = String(minutes).padStart(2, '0');
                             const ss = String(seconds).padStart(2, '0');
                             
                             return hh + ':' + mm + ':' + ss;
                         }
                     }"
                     @click.stop>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <div class="relative">
                                <svg class="w-5 h-5 text-green-600 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                                    <circle cx="10" cy="10" r="4"></circle>
                                </svg>
                                <svg class="w-5 h-5 text-green-600 absolute top-0 left-0 animate-spin" style="animation-duration: 2s;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <div class="text-xs font-medium text-green-700">Tracking Active</div>
                                <div class="text-lg font-bold text-green-800 font-mono" x-text="formatTime()">00:00:00</div>
                            </div>
                        </div>
                        <form action="{{ route('time-logs.stop', $activeTimeLog->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit"
                                    class="px-3 py-1.5 bg-red-500 text-white hover:bg-red-600 rounded-md text-xs font-medium transition-colors flex items-center space-x-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                <span>Stop</span>
                            </button>
                        </form>
                    </div>
                </div>
            @elseif($card->status === 'todo' || $card->status === 'in progress')
                {{-- Start Task Button: Only show in TODO status --}}
                <form action="{{ route('time-logs.start') }}" method="POST" @click.stop>
                    @csrf
                    <input type="hidden" name="card_id" value="{{ $card->id }}">
                    <button type="submit"
                            class="w-full bg-blue-50 text-blue-700 hover:bg-blue-100 px-3 py-2 rounded-md text-sm font-medium transition-colors flex items-center justify-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Start Task
                    </button>
                </form>
            @endif
        @endif

        {{-- Approve/Request Changes: Only for Team Lead on Review status --}}
        @if($card->status === 'review' && $userRole === 'team lead')
            <div class="flex space-x-2">
                <button @click.stop="updateCardStatus({{ $card->id }}, 'done')"
                        class="flex-1 bg-green-50 text-green-700 hover:bg-green-100 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Approve
                </button>
                <button @click.stop="updateCardStatus({{ $card->id }}, 'in progress')"
                        class="flex-1 bg-red-50 text-red-700 hover:bg-red-100 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Request Changes
                </button>
            </div>
        @endif
    </div>
</div>