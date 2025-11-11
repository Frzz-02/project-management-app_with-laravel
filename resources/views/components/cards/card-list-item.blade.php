{{--
/**
 * Card List Item Component
 * 
 * Component untuk menampilkan card dalam list/table view dengan:
 * - Horizontal layout dengan semua info visible
 * - Status dan priority indicators
 * - Subtasks progress inline
 * - Quick actions
 * - Compact design for density
 * 
 * @param Card $card - The card model instance dengan relationships
 */
--}}

@props(['card'])

<div class="bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden group cursor-pointer"
     @click="$dispatch('show-card-detail', {{ json_encode($card) }})">
     
    <div class="p-4">
        <div class="grid grid-cols-12 gap-4 items-center">
            
            <!-- Card Title & Description (Col 1-4) -->
            <div class="col-span-12 md:col-span-4">
                <div class="space-y-2">
                    <h3 class="text-lg font-semibold text-gray-900 group-hover:text-indigo-600 transition-colors line-clamp-1">
                        {{ $card->card_title }}
                    </h3>
                    
                    @if($card->description)
                        <p class="text-sm text-gray-600 line-clamp-2">
                            {{ Str::limit($card->description, 100) }}
                        </p>
                    @endif
                    
                    <!-- Project & Board Info (mobile) -->
                    <div class="flex items-center space-x-2 text-xs text-gray-400 md:hidden">
                        <span class="flex items-center space-x-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            <span>{{ $card->board->project->project_name }}</span>
                        </span>
                        <span>•</span>
                        <span class="flex items-center space-x-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2h2a2 2 0 002-2z"></path>
                            </svg>
                            <span>{{ $card->board->board_name }}</span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Status & Priority (Col 5-6) -->
            <div class="col-span-6 md:col-span-2">
                <div class="space-y-2">
                    <!-- Status Badge -->
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                        {{ $card->status === 'done' ? 'bg-green-100 text-green-800' : 
                           ($card->status === 'in progress' ? 'bg-blue-100 text-blue-800' : 
                           ($card->status === 'review' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800')) }}">
                        @if($card->status === 'done')
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        @elseif($card->status === 'in progress')
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        @elseif($card->status === 'review')
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                            </svg>
                        @else
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        @endif
                        {{ ucfirst(str_replace('_', ' ', $card->status)) }}
                    </span>

                    <!-- Priority Badge -->
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                        {{ $card->priority === 'high' ? 'bg-red-100 text-red-800' : 
                           ($card->priority === 'medium' ? 'bg-orange-100 text-orange-800' : 'bg-green-100 text-green-800') }}">
                        @if($card->priority === 'high')
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.293l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        @elseif($card->priority === 'medium')
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"></path>
                            </svg>
                        @else
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.293l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        @endif
                        {{ ucfirst($card->priority) }}
                    </span>
                </div>
            </div>

            <!-- Subtasks Progress (Col 7-8) -->
            <div class="col-span-6 md:col-span-2">
                @if($card->subtasks->count() > 0)
                    @php
                        $completedSubtasks = $card->subtasks->where('is_completed', true)->count();
                        $totalSubtasks = $card->subtasks->count();
                        $progressPercentage = $totalSubtasks > 0 ? ($completedSubtasks / $totalSubtasks) * 100 : 0;
                    @endphp
                    
                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                </svg>
                                Tasks
                            </span>
                            <span class="font-medium text-gray-900">{{ $completedSubtasks }}/{{ $totalSubtasks }}</span>
                        </div>
                        
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 h-2 rounded-full transition-all duration-300" 
                                 style="width: {{ $progressPercentage }}%"></div>
                        </div>
                    </div>
                @else
                    <div class="text-sm text-gray-400 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        No subtasks
                    </div>
                @endif
            </div>

            <!-- Assignees & Due Date (Col 9-10) -->
            <div class="col-span-6 md:col-span-2 space-y-2">
                <!-- Assignees -->
                @if($card->assignments->count() > 0)
                    <div class="flex items-center space-x-1">
                        <div class="flex -space-x-1 overflow-hidden">
                            @foreach($card->assignments->take(2) as $assignment)
                                <div class="flex h-6 w-6 rounded-full ring-1 ring-white bg-gradient-to-r from-indigo-500 to-purple-600 items-center justify-center text-white text-xs font-medium">
                                    {{ strtoupper(substr($assignment->user->full_name ?? 'U', 0, 1)) }}
                                </div>
                            @endforeach
                            
                            @if($card->assignments->count() > 2)
                                <div class="flex h-6 w-6 rounded-full ring-1 ring-white bg-gray-200 items-center justify-center text-gray-600 text-xs font-medium">
                                    +{{ $card->assignments->count() - 2 }}
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="text-xs text-gray-400">Unassigned</div>
                @endif

                <!-- Due Date -->
                <div class="text-xs">
                    @if($card->due_date)
                        <div class="flex items-center space-x-1 {{ \Carbon\Carbon::parse($card->due_date)->isPast() && $card->status !== 'done' ? 'text-red-600' : 'text-gray-600' }}">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span>{{ \Carbon\Carbon::parse($card->due_date)->format('M j') }}</span>
                        </div>
                    @else
                        <span class="text-gray-400">No due date</span>
                    @endif
                </div>
            </div>

            <!-- Project/Board & Actions (Col 11-12) -->
            <div class="col-span-6 md:col-span-2">
                <div class="flex items-center justify-between">
                    
                    <!-- Project & Board (desktop only) -->
                    <div class="hidden md:block space-y-1">
                        <div class="text-xs text-gray-600 flex items-center space-x-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            <span class="truncate max-w-20" title="{{ $card->board->project->project_name }}">{{ $card->board->project->project_name }}</span>
                        </div>
                        
                        <div class="text-xs text-gray-400 flex items-center space-x-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2h2a2 2 0 002-2z"></path>
                            </svg>
                            <span class="truncate max-w-20" title="{{ $card->board->board_name }}">{{ $card->board->board_name }}</span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex space-x-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button @click.stop="$dispatch('show-card-detail', {{ json_encode($card) }})"
                                class="p-1 text-gray-400 hover:text-indigo-600 transition-colors"
                                title="View Details">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                        
                        <button @click.stop="$dispatch('show-edit-card', {{ json_encode($card) }})"
                                class="p-1 text-gray-400 hover:text-indigo-600 transition-colors"
                                title="Edit Card">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Comments count -->
                <div class="flex items-center space-x-1 text-xs text-gray-400 mt-2">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-3.582 8-8 8a9.863 9.863 0 01-4.255-.949L5 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 3.582-8 8-8s8 3.582 8 8z"></path>
                    </svg>
                    <span>{{ $card->comments->count() }} comments</span>
                </div>
            </div>
        </div>
    </div>
</div>