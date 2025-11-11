{{--
/**
 * Card Grid Item Component
 * 
 * Component untuk menampilkan card dalam grid view dengan:
 * - Status dan priority badges  
 * - Progress bar untuk subtasks
 * - Assignee avatars
 * - Quick actions
 * - Hover interactions
 * 
 * @param Card $card - The card model instance dengan relationships
 */
--}}

@props(['card'])

<div class="group relative bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden cursor-pointer"
     @click="$dispatch('show-card-detail', {{ json_encode($card) }})"
     x-data="{ showQuickActions: false }">
     
    <!-- Card Header dengan Status Badge -->
    <div class="p-4 border-b border-gray-100 bg-gradient-to-r {{ $card->status === 'done' ? 'from-green-50 to-emerald-50' : ($card->status === 'in progress' ? 'from-blue-50 to-indigo-50' : ($card->status === 'review' ? 'from-yellow-50 to-orange-50' : 'from-gray-50 to-slate-50')) }}">
        <div class="flex items-center justify-between mb-2">
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

        <!-- Card Title -->
        <h3 class="text-lg font-semibold text-gray-900 group-hover:text-indigo-600 transition-colors line-clamp-2">
            {{ $card->card_title }}
        </h3>
    </div>


    <!-- Card Content -->
    <div class="p-4 space-y-4">
        
        <!-- Card Description Preview -->
        @if($card->description)
            <p class="text-sm text-gray-600 line-clamp-3">
                {{ Str::limit($card->description, 120) }}
            </p>
        @endif


        <!-- Subtasks Progress (jika ada subtasks) -->
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
                        Subtasks
                    </span>
                    <span class="font-medium text-gray-900">{{ $completedSubtasks }}/{{ $totalSubtasks }}</span>
                </div>
                
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-gradient-to-r from-indigo-500 to-purple-600 h-2 rounded-full transition-all duration-300" 
                         style="width: {{ $progressPercentage }}%"></div>
                </div>
            </div>
        @endif


        <!-- Card Meta Information -->
        <div class="flex items-center justify-between text-sm text-gray-500">
            
            <!-- Due Date -->
            @if($card->due_date)
                <div class="flex items-center space-x-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span class="{{ \Carbon\Carbon::parse($card->due_date)->isPast() && $card->status !== 'done' ? 'text-red-600 font-medium' : '' }}">
                        {{ \Carbon\Carbon::parse($card->due_date)->format('M j, Y') }}
                    </span>
                </div>
            @else
                <span>No due date</span>
            @endif

            <!-- Comments Count -->
            <div class="flex items-center space-x-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-3.582 8-8 8a9.863 9.863 0 01-4.255-.949L5 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 3.582-8 8-8s8 3.582 8 8z"></path>
                </svg>
                <span>{{ $card->comments->count() }}</span>
            </div>
        </div>


        <!-- Assignees Avatars -->
        @if($card->assignments->count() > 0)
            <div class="flex items-center space-x-2">
                <span class="text-sm text-gray-600">Assigned to:</span>
                <div class="flex -space-x-2 overflow-hidden">
                    @foreach($card->assignments->take(3) as $assignment)
                        <div class="flex h-8 w-8 rounded-full ring-2 ring-white bg-gradient-to-r from-indigo-500 to-purple-600 items-center justify-center text-white text-xs font-medium">
                            {{ strtoupper(substr($assignment->user->full_name ?? 'U', 0, 1)) }}
                        </div>
                    @endforeach
                    
                    @if($card->assignments->count() > 3)
                        <div class="flex h-8 w-8 rounded-full ring-2 ring-white bg-gray-200 items-center justify-center text-gray-600 text-xs font-medium">
                            +{{ $card->assignments->count() - 3 }}
                        </div>
                    @endif
                </div>
            </div>
        @endif


        <!-- Project and Board Info -->
        <div class="flex items-center justify-between text-xs text-gray-400 pt-2 border-t border-gray-100">
            <div class="flex items-center space-x-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                <span>{{ $card->board->project->project_name }}</span>
            </div>
            
            <div class="flex items-center space-x-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2h2a2 2 0 002-2z"></path>
                </svg>
                <span>{{ $card->board->board_name }}</span>
            </div>
        </div>
    </div>


    <!-- Quick Actions Overlay (muncul saat hover) -->
    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-all duration-200 flex items-center justify-center opacity-0 group-hover:opacity-100">
        <div class="flex space-x-2">
            <button @click.stop="$dispatch('show-card-detail', {{ json_encode($card) }})"
                    class="px-3 py-2 bg-white text-gray-700 rounded-lg shadow-lg hover:bg-gray-50 transition-colors text-sm font-medium">
                View Details
            </button>
            
            <button @click.stop="$dispatch('show-edit-card', {{ json_encode($card) }})"
                    class="px-3 py-2 bg-indigo-600 text-white rounded-lg shadow-lg hover:bg-indigo-700 transition-colors text-sm font-medium">
                Edit
            </button>
        </div>
    </div>
</div>