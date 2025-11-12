@props(['board'])

<!-- Card Detail Modal -->
<div x-show="$store.modal.cardDetail" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 overflow-y-auto"
     @keydown.escape="$store.modal.close()"
     style="display: none;">
    
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm transition-opacity"></div>

    <!-- Modal Content -->
    <div class="flex items-start justify-center min-h-screen px-4 py-6">
        <div x-show="$store.modal.cardDetail" 
             x-data="cardDetailData()"
             @card-detail-modal.window="handleCardDetailEvent($event.detail)"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-95"
             @click.away="$store.modal.close()"
             class="bg-white rounded-xl shadow-2xl border border-white/20 backdrop-blur-xl w-full max-w-4xl mt-12">
            
            <div>
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 x-text="selectedCard?.title || 'Card Details'" class="text-lg font-semibold text-gray-900"></h3>
                            <p class="text-sm text-gray-500">Task details and activity</p>
                        </div>
                    </div>
                    <button @click="$store.modal.close()" 
                            class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 p-6">
                    
                    <!-- Main Content (Left Side) -->
                    <div class="lg:col-span-2 space-y-6">
                        
                        <!-- Card Description -->
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Description</h4>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p x-text="selectedCard?.description || 'No description provided.'" 
                                   class="text-gray-700 whitespace-pre-wrap"></p>
                            </div>
                        </div>

                        

                <!-- Assigned Members - ONLY VISIBLE FOR TEAM LEAD -->
                @php
                    // Authorization check: Hanya team lead atau card creator yang bisa assign
                    $currentUserMember = $board->project->members->where('user_id', Auth::id())->first();
                    $isTeamLead = $currentUserMember && $currentUserMember->role === 'team lead';
                @endphp
                
                @if($isTeamLead)
                <div x-data="{
                    isUserAlreadyAssigned(userId) {
                        return this.selectedCard?.assignments?.some(a => a.user_id === userId) || false;
                    }
                }">
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        Assign Members
                        <span class="text-xs text-gray-500">(Team Lead Only)</span>
                    </label>

                    @if($board->project->members->count() > 0)
                        @php
                            // Count assignable members (exclude Team Lead and current user)
                            $assignableMembers = $board->project->members->filter(function($m) {
                                return $m->role !== 'team lead' && $m->user_id !== Auth::id();
                            });
                        @endphp
                        
                        @if($assignableMembers->count() > 0)
                            <div class="space-y-2 max-h-32 overflow-y-auto mb-4">
                                @foreach($board->project->members as $member)
                                    {{-- Filter: Jangan tampilkan Team Lead dan user yang login --}}
                                    @if($member->role !== 'team lead' && $member->user_id !== Auth::id())
                                    <label class="flex items-center justify-between p-3 bg-gray-50 rounded-lg transition-colors"
                                           :class="isUserAlreadyAssigned({{ $member->user->id }}) ? 'opacity-75 cursor-not-allowed' : 'hover:bg-gray-100 cursor-pointer'">
                                        <div class="flex items-center space-x-3 flex-1">
                                            <!-- Checkbox: Hidden untuk member yang sudah assigned -->
                                            <div x-show="!isUserAlreadyAssigned({{ $member->user->id }})">
                                                <input type="checkbox" 
                                                    value="{{ $member->user->id }}"
                                                    @click="toggleUser({{ $member->user->id }})"
                                                    :checked="selectedUsers.includes({{ $member->user->id }})"
                                                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                            </div>
                                            
                                            <!-- Empty space untuk member yang sudah assigned (untuk alignment) -->
                                            <div x-show="isUserAlreadyAssigned({{ $member->user->id }})" class="w-4"></div>
                                                
                                            <div class="flex items-center space-x-3 flex-1">
                                                <div class="w-8 h-8 bg-gradient-to-br from-indigo-400 to-purple-500 rounded-full flex items-center justify-center text-white text-sm font-medium">
                                                    {{ substr($member->user->username, 0, 1) }}
                                                </div>
                                                <div class="flex-1">
                                                    <div class="text-sm font-medium text-gray-900">{{ $member->user->username }}</div>
                                                    <div class="text-xs text-gray-500">{{ $member->user->email }} • {{ ucfirst($member->role) }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Badge "Already Assigned" -->
                                        <div x-show="isUserAlreadyAssigned({{ $member->user->id }})"
                                             x-transition:enter="transition ease-out duration-200"
                                             x-transition:enter-start="opacity-0 scale-95"
                                             x-transition:enter-end="opacity-100 scale-100"
                                             class="ml-2 flex-shrink-0">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                </svg>
                                                Already Assigned
                                            </span>
                                        </div>
                                    </label>
                                @endif
                            @endforeach
                        </div>
                        @else
                            <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg text-center">
                                <svg class="w-8 h-8 mx-auto mb-2 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                <p class="text-sm text-yellow-800 font-medium">No assignable members</p>
                                <p class="text-xs text-yellow-600 mt-1">Only Designer and Developer roles can be assigned to tasks</p>
                            </div>
                        @endif
                        
                        <!-- Assign Button - Shows when there are changes -->
                        <div x-show="hasChanges" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100"
                             class="mt-4">
                            <button @click="assignMembers()"
                                    :disabled="assignLoading || selectedUsers.length === 0"
                                    class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center space-x-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                <span x-show="!assignLoading">Assign Selected Members (<span x-text="selectedUsers.length"></span>)</span>
                                <span x-show="assignLoading">Assigning...</span>
                            </button>
                        </div>
                    @else
                        <div class="p-3 bg-gray-50 rounded-lg text-center">
                            <span class="text-sm text-gray-500">No members in project</span>
                        </div>
                    @endif
                </div>
                @else
                    <!-- Non-Team Lead (Designer/Developer): Show assigned members (read-only) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            Assigned Members
                            <span class="text-xs text-gray-500">(Read Only)</span>
                        </label>
                        
                        {{-- Tampilkan semua member yang di-assign ke task ini --}}
                        <div class="space-y-2 max-h-48 overflow-y-auto">
                            <template x-for="assignment in selectedCard?.assignments" :key="assignment.id">
                                <div class="flex items-center justify-between p-3 bg-gradient-to-r from-indigo-50 to-purple-50 rounded-lg border border-indigo-100">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-gradient-to-br from-indigo-400 to-purple-500 rounded-full flex items-center justify-center text-white text-sm font-medium shadow-md">
                                            <span x-text="(assignment.user_name || assignment.user?.username || 'U').charAt(0).toUpperCase()"></span>
                                        </div>
                                        <div>
                                            <div class="text-sm font-semibold text-gray-900" x-text="(assignment.user_name || assignment.user?.username || 'Unknown') == @js( Auth::user()->username ) ? 'You' : (assignment.user_name || assignment.user?.username || 'Unknown')"></div>
                                            <div class="text-xs text-gray-600" x-text="assignment.user_email || assignment.user?.email || 'No email'"></div>
                                        </div>
                                    </div>
                                    
                                    {{-- Badge "Assigned" dengan icon --}}
                                    <div class="flex-shrink-0">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                            Assigned
                                        </span>
                                    </div>
                                </div>
                            </template>
                            
                            {{-- Empty state jika belum ada yang di-assign --}}
                            <div x-show="!selectedCard?.assignments || selectedCard.assignments.length === 0" 
                                 class="p-6 bg-gray-50 rounded-lg text-center border-2 border-dashed border-gray-200">
                                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                                <p class="text-sm font-medium text-gray-600">No members assigned yet</p>
                                <p class="text-xs text-gray-500 mt-1">Team Lead will assign members to this task</p>
                            </div>
                        </div>
                        
                        {{-- Info banner untuk Designer/Developer --}}
                        <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                            <div class="flex items-start space-x-2">
                                <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                </svg>
                                <div class="flex-1">
                                    <p class="text-xs text-blue-800 font-medium">Assignment Information</p>
                                    <p class="text-xs text-blue-600 mt-0.5">Only Team Lead can assign or remove members from tasks. You can view who's assigned here.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                        
                        

                        <!-- Comments Section -->
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-sm font-medium text-gray-700">
                                    Comments (<span x-text="comments.length">0</span>)
                                </h4>
                                <button @click="showAddComment = !showAddComment"
                                        class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                                    Add Comment
                                </button>
                            </div>

                            <!-- Add Comment Form -->
                            <div x-show="showAddComment" 
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 class="bg-gray-50 rounded-lg p-4 mb-4">
                                <form @submit.prevent="addComment()">
                                    <textarea x-model="newComment"
                                              rows="3"
                                              placeholder="Add a comment..."
                                              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none"
                                              required></textarea>
                                    <div class="flex items-center justify-end space-x-2 mt-3">
                                        <button type="button" 
                                                @click="showAddComment = false; newComment = ''"
                                                class="px-3 py-1 text-sm text-gray-600 hover:text-gray-700">
                                            Cancel
                                        </button>
                                        <button type="submit"
                                                :disabled="commentLoading"
                                                class="px-4 py-1 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700 disabled:opacity-50">
                                            <span x-show="!commentLoading">Comment</span>
                                            <span x-show="commentLoading">Posting...</span>
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Comments List -->
                            <div class="space-y-4 max-h-64 overflow-y-auto">
                                <template x-for="comment in comments" :key="comment.id">
                                    <div class="flex space-x-3 p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors group">
                                        <div class="w-8 h-8 bg-gradient-to-br from-blue-400 to-indigo-500 rounded-full flex items-center justify-center text-white text-sm font-medium flex-shrink-0">
                                            <span x-text="comment.user_name?.charAt(0) || 'U'"></span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center space-x-2">
                                                    <span x-text="(comment.user_name) == @js( Auth::user()->username ) ? 'you' : comment.user_name" class="text-sm font-medium text-gray-900"></span>
                                                    <span x-text="formatDate(comment.created_at)" class="text-xs text-gray-500"></span>
                                                </div>
                                                
                                                <!-- Edit/Delete buttons (only for comment owner) -->
                                                <div x-show="comment.user_id === {{ Auth::id() }}" 
                                                     class="flex items-center space-x-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                    <button @click="editComment(comment)"
                                                            class="p-1 text-gray-400 hover:text-indigo-600 transition-colors">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                        </svg>
                                                    </button>
                                                    <button @click="deleteComment(comment.id)"
                                                            class="p-1 text-gray-400 hover:text-red-600 transition-colors">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            <!-- Comment text or edit form -->
                                            <div x-show="editingCommentId !== comment.id">
                                                <p x-text="comment.comment_text" class="mt-1 text-sm text-gray-700 whitespace-pre-wrap"></p>
                                            </div>
                                            
                                            <!-- Edit form -->
                                            <div x-show="editingCommentId === comment.id" 
                                                 x-transition:enter="transition ease-out duration-200"
                                                 x-transition:enter-start="opacity-0 scale-95"
                                                 x-transition:enter-end="opacity-100 scale-100"
                                                 class="mt-2">
                                                <form @submit.prevent="updateComment(comment.id)">
                                                    <textarea x-model="editingCommentText"
                                                              rows="3"
                                                              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none text-sm"
                                                              required></textarea>
                                                    <div class="flex items-center justify-end space-x-2 mt-2">
                                                        <button type="button" 
                                                                @click="cancelEdit()"
                                                                class="px-3 py-1 text-xs text-gray-600 hover:text-gray-700">
                                                            Cancel
                                                        </button>
                                                        <button type="submit"
                                                                :disabled="commentLoading"
                                                                class="px-3 py-1 bg-indigo-600 text-white text-xs rounded-md hover:bg-indigo-700 disabled:opacity-50">
                                                            <span x-show="!commentLoading">Save</span>
                                                            <span x-show="commentLoading">Saving...</span>
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                
                                <!-- Empty State -->
                                <div x-show="comments.length === 0" class="text-center py-8 text-gray-500">
                                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                    </svg>
                                    <p class="text-sm">No comments yet. Be the first to comment!</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Sidebar -->
                    <div class="space-y-6">
                        
                        <!-- Status & Priority -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-3">Status & Priority</h4>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Status:</span>
                                    <span x-text="formatStatus(selectedCard?.status)" 
                                          :class="getStatusColor(selectedCard?.status)"
                                          class="px-2 py-1 rounded-full text-xs font-medium"></span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Priority:</span>
                                    <span x-text="formatPriority(selectedCard?.priority)" 
                                          :class="getPriorityColor(selectedCard?.priority)"
                                          class="px-2 py-1 rounded-full text-xs font-medium"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Dates & Time -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-3">Timeline</h4>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Due Date:</span>
                                    <span x-text="formatDate(selectedCard?.due_date) || 'Not set'" 
                                          class="text-sm text-gray-900"></span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Created:</span>
                                    <span x-text="formatDate(selectedCard?.created_at)" 
                                          class="text-sm text-gray-900"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Work Hours -->
                        <div x-show="selectedCard?.estimated_hours || selectedCard?.actual_hours" 
                             class="bg-gray-50 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-3">Work Hours</h4>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Estimated:</span>
                                    <span x-text="(selectedCard?.estimated_hours || 0) + 'h'" 
                                          class="text-sm text-gray-900"></span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Actual:</span>
                                    <span x-text="(selectedCard?.actual_hours || 0) + 'h'" 
                                          class="text-sm text-gray-900"></span>
                                </div>
                                <template x-if="selectedCard?.estimated_hours">
                                    <div class="w-full">
                                        <div class="flex justify-between text-xs text-gray-500 mb-1">
                                            <span>Progress</span>
                                            <span x-text="Math.round((selectedCard.actual_hours / selectedCard.estimated_hours) * 100) + '%'"></span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div :style="`width: ${Math.min(100, (selectedCard.actual_hours / selectedCard.estimated_hours) * 100)}%`"
                                                 :class="(selectedCard.actual_hours / selectedCard.estimated_hours) > 1 ? 'bg-red-500' : 'bg-blue-500'"
                                                 class="h-2 rounded-full transition-all"></div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Creator -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-3">Created By</h4>
                            <div class="flex items-center space-x-2">
                                <div class="w-8 h-8 bg-gradient-to-br from-indigo-400 to-purple-500 rounded-full flex items-center justify-center text-white text-sm font-medium">
                                    <span x-text="selectedCard?.creator_name?.charAt(0) || 'U'"></span>
                                </div>
                                <span x-text="selectedCard?.creator_name || 'Unknown'" 
                                      class="text-sm text-gray-900"></span>
                            </div>
                        </div>
                        


                        <!-- Create Subtasks Button - ONLY FOR DESIGNER/DEVELOPER -->
                        <template x-if="selectedCard?.is_designer_or_developer">
                            <div x-data="{ 
                                shouldDisable() {
                                    // Disable if:
                                    // - User is assigned to card (assignment check)
                                    // - Time tracking NOT started (tracking check)
                                    const isAssigned = this.selectedCard?.is_user_assigned || false;
                                    const hasTracking = this.selectedCard?.has_active_tracking || false;
                                    
                                    const shouldDisable = isAssigned && !hasTracking;
                                    
                                    console.log('🔐 Create Subtasks Button Check:', {
                                        isDesignerOrDev: true,
                                        isAssigned,
                                        hasTracking,
                                        shouldDisable
                                    });
                                    
                                    return shouldDisable;
                                }
                            }">
                                <template x-if="shouldDisable()">
                                    <!-- Disabled Button with Tooltip -->
                                    <div class="relative group">
                                        <button disabled
                                                class="w-full px-4 py-2 bg-indigo-400 text-white rounded-lg cursor-not-allowed transition-colors text-sm font-medium flex items-center justify-center space-x-2 opacity-60">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"  d="M6 6.878V6a2.25 2.25 0 0 1 2.25-2.25h7.5A2.25 2.25 0 0 1 18 6v.878m-12 0c.235-.083.487-.128.75-.128h10.5c.263 0 .515.045.75.128m-12 0A2.25 2.25 0 0 0 4.5 9v.878m13.5-3A2.25 2.25 0 0 1 19.5 9v.878m0 0a2.246 2.246 0 0 0-.75-.128H5.25c-.263 0-.515.045-.75.128m15 0A2.25 2.25 0 0 1 21 12v6a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18v-6c0-.98.626-1.813 1.5-2.122" />
                                            </svg>
                                            <span>Create Subtasks</span>
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                            </svg>
                                        </button>
                                        
                                        <!-- Tooltip -->
                                        <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-900 text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none z-50">
                                            🔒 Start time tracking first to create subtasks
                                            <div class="absolute top-full left-1/2 transform -translate-x-1/2 -mt-1">
                                                <div class="border-4 border-transparent border-t-gray-900"></div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                
                                <template x-if="!shouldDisable()">
                                    <!-- Active Button -->
                                    <a :href="'{{ url("cards") }}/' + (selectedCard?.id || '') + '#subtasks-section'"
                                       class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm font-medium flex items-center justify-center space-x-2">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"  d="M6 6.878V6a2.25 2.25 0 0 1 2.25-2.25h7.5A2.25 2.25 0 0 1 18 6v.878m-12 0c.235-.083.487-.128.75-.128h10.5c.263 0 .515.045.75.128m-12 0A2.25 2.25 0 0 0 4.5 9v.878m13.5-3A2.25 2.25 0 0 1 19.5 9v.878m0 0a2.246 2.246 0 0 0-.75-.128H5.25c-.263 0-.515.045-.75.128m15 0A2.25 2.25 0 0 1 21 12v6a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18v-6c0-.98.626-1.813 1.5-2.122" />
                                        </svg>
                                        <span>Create Subtasks</span>
                                    </a>
                                </template>
                            </div>
                        </template>
                        
                        
                        <!-- Card Review Actions - ONLY FOR TEAM LEAD OR ADMIN -->
                        @php
                            $currentUserMember = $board->project->members->where('user_id', Auth::id())->first();
                            $isTeamLeadReviewer = Auth::user()->role === 'admin' || ($currentUserMember && $currentUserMember->role === 'team lead');
                        @endphp
                        
                        @if($isTeamLeadReviewer)
                        <div x-show="selectedCard?.status === 'review'" class="space-y-3">
                            <h4 class="text-sm font-medium text-gray-700 flex items-center">
                                <svg class="w-4 h-4 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Review Card
                            </h4>
                            
                            <!-- Notes Input (Optional) -->
                            <div>
                                <label class="block text-xs text-gray-600 mb-2">Notes (Optional)</label>
                                <textarea x-model="reviewNotes" 
                                          placeholder="Keterangan untuk developer (opsional)..."
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent resize-none"
                                          rows="3"></textarea>
                                <p class="text-xs text-gray-500 mt-1">
                                    <span x-text="reviewNotes.length"></span>/2000 karakter
                                </p>
                            </div>
                            
                            <!-- Review Buttons -->
                            <div class="grid grid-cols-2 gap-2">
                                <!-- Approve Button -->
                                <button @click="handleReview('approved')"
                                        :disabled="isReviewing"
                                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm font-medium flex items-center justify-center space-x-2 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <svg x-show="!isReviewing" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <svg x-show="isReviewing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span x-text="isReviewing ? 'Processing...' : 'Approve'"></span>
                                </button>
                                
                                <!-- Request Change Button -->
                                <button @click="handleReview('rejected')"
                                        :disabled="isReviewing"
                                        class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors text-sm font-medium flex items-center justify-center space-x-2 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <svg x-show="!isReviewing" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                    <svg x-show="isReviewing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span x-text="isReviewing ? 'Processing...' : 'Request Change'"></span>
                                </button>
                            </div>
                            
                            <p class="text-xs text-gray-500 text-center">
                                💡 Tip: Provide notes to help developers understand your feedback
                            </p>
                        </div>
                        @endif
                        
                        <!-- Actions - Authorization via Policy -->
                        @can('update', $board->cards->first())
                        <div class="space-y-2">
                            <button @click="$dispatch('edit-card-modal', selectedCard); $store.modal.close()"
                                    class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm font-medium flex items-center justify-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                <span>Edit Card</span>
                            </button>
                            
                            <!-- DELETE BUTTON - Modal Confirmation -->
                            <button @click="showDeleteConfirm = true"
                                    class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm font-medium flex items-center justify-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                <span>Delete Card</span>
                            </button>
                        </div>
                        @else
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 text-center">
                            <svg class="w-8 h-8 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            <p class="text-xs text-gray-600 font-medium">Edit & Delete Restricted</p>
                            <p class="text-xs text-gray-500 mt-1">Only Admin or Team Lead can edit/delete cards</p>
                        </div>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- DELETE CONFIRMATION MODAL -->
    <div x-show="showDeleteConfirm" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[60] overflow-y-auto"
         style="display: none;">
        
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-75 backdrop-blur-sm"></div>

        <!-- Modal Content -->
        <div class="flex items-center justify-center min-h-screen px-4">
            <div x-show="showDeleteConfirm"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95"
                 @click.away="showDeleteConfirm = false"
                 class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
                
                <!-- Icon Warning -->
                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full mb-4">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                
                <!-- Title -->
                <h3 class="text-lg font-semibold text-gray-900 text-center mb-2">
                    Hapus Card?
                </h3>
                
                <!-- Message -->
                <p class="text-sm text-gray-600 text-center mb-6">
                    Apakah Anda yakin ingin menghapus card "<span class="font-semibold" x-text="selectedCard?.title"></span>"?
                    <br><br>
                    <span class="text-red-600 font-medium">Tindakan ini tidak dapat dibatalkan!</span>
                    <br>
                    Semua data terkait (subtasks, comments, time logs) akan ikut terhapus.
                </p>
                
                <!-- Actions -->
                <div class="flex space-x-3">
                    <button @click="showDeleteConfirm = false"
                            class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium">
                        Batal
                    </button>
                    <form :action="'/cards/' + (selectedCard?.id || '')" method="POST" class="flex-1">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium">
                            Ya, Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Inline script untuk component - @push tidak bekerja di component --}}
<script>
function cardDetailData() {
    return {
        selectedCard: null,
        comments: [],
        showAddComment: false,
        newComment: '',
        commentLoading: false,
        editingCommentId: null,
        editingCommentText: '',
        showDeleteConfirm: false, // State untuk delete confirmation modal
        
        // Assignment data
        selectedUsers: [],
        hasChanges: false,
        assignLoading: false,
        
        // Review data
        reviewNotes: '',
        isReviewing: false,

        init() {
            console.log('✅ cardDetailData() initialized');
        },

        handleCardDetailEvent(cardData) {
            // Handle event dari card click
            console.group('🎯 Card Detail Event Received');
            console.log('📦 Raw Event Data:', cardData);
            console.log('📋 Title:', cardData?.title);
            console.log('⚡ Status:', cardData?.status);
            console.log('🎯 Priority:', cardData?.priority);
            console.log('📅 Due Date:', cardData?.due_date);
            console.log('👥 Assignments:', cardData?.assignments);
            console.log('👥 Assignments Count:', cardData?.assignments?.length || 0);
            console.log('👥 First Assignment:', cardData?.assignments?.[0]);
            
            // Time Tracking Info for Create Subtasks button
            console.log('⏱️ Has Active Tracking:', cardData?.has_active_tracking);
            console.log('👤 Is User Assigned:', cardData?.is_user_assigned);
            console.log('🎭 User Role:', cardData?.user_role);
            console.log('🔧 Is Designer/Developer:', cardData?.is_designer_or_developer);
            console.groupEnd();

            this.selectedCard = cardData;
            this.loadComments();
            
            // Initialize selected users from assignments
            this.selectedUsers = cardData?.assignments?.map(a => a.user_id) || [];
            this.hasChanges = false;
            console.log('🔄 Initialized selectedUsers:', this.selectedUsers);

            // Debug lagi setelah set
            console.log('✅ selectedCard updated:', this.selectedCard);
            console.log('✅ selectedCard.assignments:', this.selectedCard?.assignments);
        },




        // Memuat komentar dari server untuk card yang dipilih
        // Menggunakan AJAX untuk mendapatkan data realtime dari database
        async loadComments() {
            if (!this.selectedCard?.id) {
                this.comments = [];
                return;
            }

            try {
                const response = await fetch(`/comments/card/${this.selectedCard.id}`);
                if (!response.ok) throw new Error('Failed to load comments');
                
                const data = await response.json();
                this.comments = data.comments || [];
                
                console.log('✅ Comments loaded:', this.comments.length);
            } catch (error) {
                console.error('❌ Error loading comments:', error);
                this.comments = [];
            }
        },




        // Menambahkan komentar baru untuk card
        // Menggunakan AJAX POST untuk menyimpan ke database melalui CommentController
        async addComment() {
            if (!this.newComment.trim() || !this.selectedCard?.id) return;

            this.commentLoading = true;
            try {
                const response = await fetch('/comments', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        card_id: this.selectedCard.id,
                        comment_text: this.newComment,
                        comment_type: 'card'
                    })
                });

                const data = await response.json();
                
                if (!response.ok) {
                    throw new Error(data.message || 'Failed to add comment');
                }

                // Tampilkan komentar baru dengan animasi
                this.comments.push(data.comment);
                this.newComment = '';
                this.showAddComment = false;

                console.log('✅ Comment added successfully');

            } catch (error) {
                console.error('❌ Error adding comment:', error);
                alert(error.message || 'Failed to add comment. Please try again.');
            } finally {
                this.commentLoading = false;
            }
        },



        // Mengedit komentar (tampilkan form edit)
        // Set state untuk menampilkan textarea edit dengan Alpine.js animation
        editComment(comment) {
            this.editingCommentId = comment.id;
            this.editingCommentText = comment.comment_text;
        },



        // Membatalkan edit komentar
        // Reset state editing untuk kembali ke mode tampilan biasa
        cancelEdit() {
            this.editingCommentId = null;
            this.editingCommentText = '';
        },



        // Menyimpan perubahan komentar ke server
        // Menggunakan AJAX PUT untuk update melalui CommentController
        async updateComment(commentId) {
            if (!this.editingCommentText.trim()) return;

            this.commentLoading = true;
            try {
                const response = await fetch(`/comments/${commentId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        comment_text: this.editingCommentText
                    })
                });

                const data = await response.json();
                
                if (!response.ok) {
                    throw new Error(data.message || 'Failed to update comment');
                }

                // Update komentar di array dengan data baru
                const index = this.comments.findIndex(c => c.id === commentId);
                if (index !== -1) {
                    this.comments[index] = data.comment;
                }

                // Reset editing state
                this.cancelEdit();

                console.log('✅ Comment updated successfully');

            } catch (error) {
                console.error('❌ Error updating comment:', error);
                alert(error.message || 'Failed to update comment. Please try again.');
            } finally {
                this.commentLoading = false;
            }
        },



        // Menghapus komentar dari database
        // Menggunakan AJAX DELETE dengan konfirmasi user terlebih dahulu
        async deleteComment(commentId) {
            if (!confirm('Are you sure you want to delete this comment?')) return;

            try {
                const response = await fetch(`/comments/${commentId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();
                
                if (!response.ok) {
                    throw new Error(data.message || 'Failed to delete comment');
                }

                // Hapus dari array dengan animasi
                this.comments = this.comments.filter(c => c.id !== commentId);

                console.log('✅ Comment deleted successfully');

            } catch (error) {
                console.error('❌ Error deleting comment:', error);
                alert(error.message || 'Failed to delete comment. Please try again.');
            }
        },

        toggleAssignment(userId) {
            console.log('🔄 Toggle assignment for user:', userId);
            // In real implementation, call API to toggle assignment
            // For now just log the action
        },
        
        // Toggle user selection untuk assignment
        toggleUser(userId) {
            const index = this.selectedUsers.indexOf(userId);
            if (index > -1) {
                this.selectedUsers.splice(index, 1);
            } else {
                this.selectedUsers.push(userId);
            }
            this.hasChanges = true;
            console.log('✅ Selected users:', this.selectedUsers);
        },
        
        // Assign members ke card via AJAX
        async assignMembers() {
            if (this.selectedUsers.length === 0) {
                alert('Pilih minimal 1 member untuk di-assign.');
                return;
            }
            
            this.assignLoading = true;
            
            try {
                const response = await fetch('{{ route('card-assignments.assign') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        card_id: this.selectedCard?.id,
                        assigned_users: this.selectedUsers
                    })
                });
                
                const data = await response.json();
                
                if (!response.ok) {
                    throw new Error(data.message || 'Gagal assign members');
                }
                
                alert(data.message);
                this.hasChanges = false;
                
                // Reload page untuk update UI
                window.location.reload();
                
            } catch (error) {
                console.error('❌ Error assigning members:', error);
                alert(error.message || 'Gagal assign members. Silakan coba lagi.');
            } finally {
                this.assignLoading = false;
            }
        },

        /**
         * Handle Card Review (Approve/Reject)
         * 
         * @param {string} status - 'approved' atau 'rejected'
         */
        async handleReview(status) {
            if (this.isReviewing) return;
            
            // Validation
            if (!this.selectedCard?.id) {
                alert('Card tidak ditemukan');
                return;
            }
            
            // Confirm action
            const confirmMessage = status === 'approved' 
                ? 'Apakah Anda yakin ingin approve card ini?\n\nCard akan dipindahkan ke status "Done" dan semua assignments akan di-mark sebagai completed.'
                : 'Apakah Anda yakin ingin request perubahan?\n\nCard akan dikembalikan ke status "Todo" untuk dikerjakan ulang.';
            
            if (!confirm(confirmMessage)) {
                return;
            }
            
            this.isReviewing = true;
            
            try {
                const formData = new FormData();
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                formData.append('status', status);
                
                if (this.reviewNotes.trim()) {
                    formData.append('notes', this.reviewNotes.trim());
                }
                
                const response = await fetch(`/cards/${this.selectedCard.id}/reviews`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                
                const result = await response.json();
                
                if (response.ok && result.success) {
                    // Success notification
                    alert(result.message || (status === 'approved' ? 'Card berhasil di-approve!' : 'Perubahan diminta!'));
                    
                    // Reset review notes
                    this.reviewNotes = '';
                    
                    // Close modal and reload page to reflect changes
                    this.$store.modal.close();
                    
                    // Dispatch event untuk update UI
                    document.dispatchEvent(new CustomEvent('card-reviewed', {
                        detail: {
                            cardId: this.selectedCard.id,
                            status: status,
                            newCardStatus: result.card.status
                        }
                    }));
                    
                    // Reload page to see updated card
                    window.location.reload();
                } else {
                    alert(result.message || 'Gagal memproses review. Silakan coba lagi.');
                }
                
            } catch (error) {
                console.error('❌ Error reviewing card:', error);
                alert('Terjadi kesalahan saat memproses review. Silakan coba lagi.');
            } finally {
                this.isReviewing = false;
            }
        },

        formatStatus(status) {
            if (!status) return 'Unknown';
            // Handle multi-word status like "in progress"
            return status.split(' ')
                .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                .join(' ');
        },

        formatPriority(priority) {
            if (!priority) return 'Unknown';
            return priority.charAt(0).toUpperCase() + priority.slice(1);
        },

        getStatusColor(status) {
            const colors = {
                'todo': 'bg-gray-100 text-gray-800',
                'in progress': 'bg-blue-100 text-blue-800',
                'review': 'bg-yellow-100 text-yellow-800',
                'done': 'bg-green-100 text-green-800'
            };
            return colors[status] || 'bg-gray-100 text-gray-800';
        },

        getPriorityColor(priority) {
            const colors = {
                'low': 'bg-green-100 text-green-800',
                'medium': 'bg-yellow-100 text-yellow-800', 
                'high': 'bg-red-100 text-red-800'
            };
            return colors[priority] || 'bg-gray-100 text-gray-800';
        },

        formatDate(dateString) {
            if (!dateString) return '';
            return new Date(dateString).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    }
}
</script>