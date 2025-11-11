{{--
/**
 * Card Detail Modal Component
 * 
 * Modal lengkap untuk melihat detail card dengan:
 * - Card information display
 * - Subtasks todolist dengan CRUD functionality
 * - Comments system
 * - File attachments
 * - Activity timeline
 * - Quick edit actions
 * 
 * Modal ini menggunakan Alpine.js untuk state management dan real-time updates
 */
--}}

<div x-show="$store.cardModal.detail" 
     x-transition:enter="ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;">
     
    <!-- Modal Background Overlay -->
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
             @click="$store.cardModal.closeAll()"></div>

        <!-- Modal Container -->
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-5xl sm:w-full max-h-[90vh] overflow-y-auto"
             x-data="cardDetailModal()" 
             @card-updated.window="handleCardUpdate($event.detail)">
             
            <div class="bg-white">
                
                <!-- ============================================================================ -->
                <!-- MODAL HEADER SECTION                                                        -->
                <!-- Header dengan title, status, close button, dan quick actions                -->
                <!-- ============================================================================ -->
                
                <div class="bg-gradient-to-r from-indigo-50 to-purple-50 px-6 py-4 border-b border-gray-200">
                    <div class="flex items-start justify-between">
                        <div class="flex-1 min-w-0">
                            <!-- Card Title -->
                            <h2 x-text="card?.title || 'Loading...'" 
                                class="text-2xl font-bold text-gray-900 truncate"></h2>
                            
                            <!-- Card Meta Info -->
                            <div class="flex items-center space-x-4 mt-2 text-sm text-gray-600">
                                <span class="flex items-center space-x-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                    <span x-text="card?.board?.project?.name || ''"></span>
                                </span>
                                
                                <span>•</span>
                                
                                <span class="flex items-center space-x-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2h2a2 2 0 002-2z"></path>
                                    </svg>
                                    <span x-text="card?.board?.name || ''"></span>
                                </span>
                                
                                <span>•</span>
                                
                                <span x-text="card?.created_at ? 'Created ' + new Date(card.created_at).toLocaleDateString() : ''"></span>
                            </div>
                        </div>

                        <!-- Header Actions -->
                        <div class="flex items-center space-x-2 ml-4">
                            <button @click="$dispatch('show-edit-card', card)"
                                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Edit
                            </button>
                            
                            <button @click="$store.cardModal.closeAll()"
                                    class="rounded-md text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>


                <!-- ============================================================================ -->
                <!-- MODAL CONTENT SECTION                                                       -->
                <!-- Main content dengan 2 column layout: left content, right sidebar           -->
                <!-- ============================================================================ -->
                
                <div class="flex flex-col lg:flex-row">
                    
                    <!-- ======================================================================== -->
                    <!-- LEFT COLUMN - MAIN CONTENT                                              -->
                    <!-- Description, subtasks, comments                                         -->
                    <!-- ======================================================================== -->
                    
                    <div class="flex-1 p-6 space-y-6">
                        
                        <!-- Card Description -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-3">Description</h3>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p x-text="card?.description || 'No description provided.'" 
                                   class="text-gray-700 whitespace-pre-wrap"></p>
                            </div>
                        </div>


                        <!-- ================================================================ -->
                        <!-- SUBTASKS TODOLIST SECTION                                       -->
                        <!-- Interactive todolist dengan CRUD functionality                  -->
                        <!-- ================================================================ -->
                        
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-gray-900 flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                    </svg>
                                    Subtasks
                                    <span x-show="subtasks.length > 0" 
                                          class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800"
                                          x-text="`${getCompletedSubtasks()} of ${subtasks.length} completed`"></span>
                                </h3>
                                
                                <button @click="showAddSubtask = !showAddSubtask"
                                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Add Subtask
                                </button>
                            </div>

                            <!-- Add New Subtask Form -->
                            <div x-show="showAddSubtask" 
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 transform scale-95"
                                 x-transition:enter-end="opacity-100 transform scale-100"
                                 class="mb-4 bg-blue-50 rounded-lg p-4 border border-blue-200">
                                 
                                <form @submit.prevent="addSubtask()">
                                    <div class="flex space-x-3">
                                        <div class="flex-1">
                                            <input type="text" 
                                                   x-model="newSubtaskTitle"
                                                   placeholder="Enter subtask title..."
                                                   class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                                   required>
                                        </div>
                                        
                                        <button type="submit"
                                                :disabled="!newSubtaskTitle.trim()"
                                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                            Add
                                        </button>
                                        
                                        <button type="button" 
                                                @click="showAddSubtask = false; newSubtaskTitle = ''"
                                                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            Cancel
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Subtasks List -->
                            <div class="space-y-2">
                                <template x-for="subtask in subtasks" :key="subtask.id">
                                    <div class="group flex items-center space-x-3 p-3 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                        
                                        <!-- Checkbox -->
                                        <div class="flex-shrink-0">
                                            <button @click="toggleSubtask(subtask)"
                                                    class="flex items-center justify-center w-5 h-5 border-2 rounded transition-all duration-200"
                                                    :class="subtask.is_completed ? 'bg-green-500 border-green-500 text-white' : 'border-gray-300 hover:border-green-400'">
                                                <svg x-show="subtask.is_completed" class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                            </button>
                                        </div>

                                        <!-- Subtask Content -->
                                        <div class="flex-1 min-w-0">
                                            <!-- View Mode -->
                                            <div x-show="!subtask.editing" class="flex items-center justify-between">
                                                <span class="text-sm text-gray-900 truncate"
                                                      :class="subtask.is_completed ? 'line-through text-gray-500' : ''"
                                                      x-text="subtask.title"></span>
                                                      
                                                <div class="flex items-center space-x-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                    <button @click="editSubtask(subtask)"
                                                            class="p-1 text-gray-400 hover:text-indigo-600 transition-colors">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                        </svg>
                                                    </button>
                                                    
                                                    <button @click="deleteSubtask(subtask)"
                                                            class="p-1 text-gray-400 hover:text-red-600 transition-colors">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Edit Mode -->
                                            <div x-show="subtask.editing" class="flex space-x-2">
                                                <input type="text" 
                                                       x-model="subtask.editTitle"
                                                       @keydown.enter="saveSubtask(subtask)"
                                                       @keydown.escape="cancelEditSubtask(subtask)"
                                                       class="flex-1 text-sm border-gray-300 rounded focus:ring-indigo-500 focus:border-indigo-500">
                                                       
                                                <button @click="saveSubtask(subtask)"
                                                        class="px-2 py-1 text-xs bg-green-600 text-white rounded hover:bg-green-700">
                                                    Save
                                                </button>
                                                
                                                <button @click="cancelEditSubtask(subtask)"
                                                        class="px-2 py-1 text-xs bg-gray-500 text-white rounded hover:bg-gray-600">
                                                    Cancel
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <!-- Empty State for Subtasks -->
                                <div x-show="subtasks.length === 0" class="text-center py-8 text-gray-500">
                                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                    <p class="text-sm">No subtasks yet. Add one to break down this card into smaller tasks.</p>
                                </div>
                            </div>
                        </div>


                        <!-- ================================================================ -->
                        <!-- COMMENTS SECTION                                                 -->
                        <!-- Comments dengan threaded replies dan real-time updates          -->
                        <!-- ================================================================ -->
                        
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-gray-900 flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-3.582 8-8 8a9.863 9.863 0 01-4.255-.949L5 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 3.582-8 8-8s8 3.582 8 8z"></path>
                                    </svg>
                                    Comments
                                    <span x-show="comments.length > 0" 
                                          class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                                          x-text="`${comments.length} comment${comments.length !== 1 ? 's' : ''}`"></span>
                                </h3>
                            </div>

                            <!-- Add Comment Form -->
                            <div class="mb-6 bg-gray-50 rounded-lg p-4">
                                <form @submit.prevent="addComment()">
                                    <div class="space-y-3">
                                        <textarea x-model="newComment"
                                                  placeholder="Write a comment..."
                                                  rows="3"
                                                  class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm resize-none"
                                                  required></textarea>
                                                  
                                        <div class="flex justify-end space-x-2">
                                            <button type="button"
                                                    @click="newComment = ''"
                                                    class="px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                                Clear
                                            </button>
                                            
                                            <button type="submit"
                                                    :disabled="!newComment.trim()"
                                                    class="px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                                Add Comment
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <!-- Comments List -->
                            <div class="space-y-4">
                                <template x-for="comment in comments" :key="comment.id">
                                    <div class="bg-white border border-gray-200 rounded-lg p-4">
                                        <div class="flex items-start space-x-3">
                                            <!-- Avatar -->
                                            <div class="flex-shrink-0">
                                                <div class="w-8 h-8 bg-gradient-to-r from-indigo-400 to-purple-500 rounded-full flex items-center justify-center text-white text-sm font-medium">
                                                    <span x-text="comment.user?.full_name ? comment.user.full_name.charAt(0).toUpperCase() : 'U'"></span>
                                                </div>
                                            </div>

                                            <!-- Comment Content -->
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center space-x-2 mb-1">
                                                    <span class="text-sm font-medium text-gray-900" x-text="comment.user?.full_name || 'Unknown User'"></span>
                                                    <span class="text-xs text-gray-500" x-text="comment.created_at ? new Date(comment.created_at).toLocaleString() : ''"></span>
                                                </div>
                                                
                                                <p class="text-sm text-gray-700 whitespace-pre-wrap" x-text="comment.content"></p>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <!-- Empty State for Comments -->
                                <div x-show="comments.length === 0" class="text-center py-8 text-gray-500">
                                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-3.582 8-8 8a9.863 9.863 0 01-4.255-.949L5 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 3.582-8 8-8s8 3.582 8 8z"></path>
                                    </svg>
                                    <p class="text-sm">No comments yet. Be the first to add a comment!</p>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- ======================================================================== -->
                    <!-- RIGHT SIDEBAR - CARD PROPERTIES                                         -->
                    <!-- Status, priority, assignees, dates, dll                                 -->
                    <!-- ======================================================================== -->
                    
                    <div class="w-full lg:w-80 bg-gray-50 border-t lg:border-t-0 lg:border-l border-gray-200 p-6 space-y-6">
                        
                        <!-- Status -->
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 mb-2">Status</h4>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                  :class="{
                                      'bg-gray-100 text-gray-800': card?.status === 'todo',
                                      'bg-blue-100 text-blue-800': card?.status === 'in progress',
                                      'bg-yellow-100 text-yellow-800': card?.status === 'review',
                                      'bg-green-100 text-green-800': card?.status === 'done'
                                  }"
                                  x-text="card?.status ? card.status.charAt(0).toUpperCase() + card.status.slice(1).replace('_', ' ') : ''"></span>
                        </div>

                        <!-- Priority -->
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 mb-2">Priority</h4>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                  :class="{
                                      'bg-green-100 text-green-800': card?.priority === 'low',
                                      'bg-orange-100 text-orange-800': card?.priority === 'medium',
                                      'bg-red-100 text-red-800': card?.priority === 'high'
                                  }"
                                  x-text="card?.priority ? card.priority.charAt(0).toUpperCase() + card.priority.slice(1) : ''"></span>
                        </div>

                        <!-- Due Date -->
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 mb-2">Due Date</h4>
                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span x-text="card?.due_date ? new Date(card.due_date).toLocaleDateString() : 'No due date'"></span>
                            </div>
                        </div>

                        <!-- Assignees -->
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 mb-2">Assignees</h4>
                            <div x-show="card?.assignments?.length > 0" class="space-y-2">
                                <template x-for="assignment in card?.assignments || []" :key="assignment.user.id">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-6 h-6 bg-gradient-to-r from-indigo-400 to-purple-500 rounded-full flex items-center justify-center text-white text-xs font-medium">
                                            <span x-text="assignment.user?.full_name ? assignment.user.full_name.charAt(0).toUpperCase() : 'U'"></span>
                                        </div>
                                        <span class="text-sm text-gray-700" x-text="assignment.user?.full_name || 'Unknown User'"></span>
                                    </div>
                                </template>
                            </div>
                            <div x-show="!card?.assignments || card.assignments.length === 0" class="text-sm text-gray-500">
                                No assignees
                            </div>
                        </div>

                        <!-- Progress -->
                        <div x-show="subtasks.length > 0">
                            <h4 class="text-sm font-medium text-gray-900 mb-2">Progress</h4>
                            <div class="space-y-2">
                                <div class="flex justify-between text-sm text-gray-600">
                                    <span>Subtasks completed</span>
                                    <span x-text="`${getCompletedSubtasks()} of ${subtasks.length}`"></span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-gradient-to-r from-indigo-500 to-purple-600 h-2 rounded-full transition-all duration-300" 
                                         :style="`width: ${subtasks.length > 0 ? (getCompletedSubtasks() / subtasks.length) * 100 : 0}%`"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Created Info -->
                        <div class="pt-4 border-t border-gray-200">
                            <div class="text-xs text-gray-500 space-y-1">
                                <div x-text="card?.created_at ? 'Created ' + new Date(card.created_at).toLocaleDateString() : ''"></div>
                                <div x-text="card?.updated_at ? 'Updated ' + new Date(card.updated_at).toLocaleDateString() : ''"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- ============================================================================ -->
<!-- JAVASCRIPT COMPONENT                                                        -->
<!-- Alpine.js component untuk managing modal state dan CRUD operations         -->
<!-- ============================================================================ -->

<script>
/**
 * Card Detail Modal Component
 * 
 * Handles:
 * - Modal state management
 * - Subtasks CRUD operations
 * - Comments CRUD operations
 * - Real-time updates
 * - Optimistic UI updates
 */
function cardDetailModal() {
    return {
        // Component state
        card: null,
        subtasks: [],
        comments: [],
        
        // Form states
        showAddSubtask: false,
        newSubtaskTitle: '',
        newComment: '',

        /**
         * Initialize component when modal opens
         */
        init() {
            // Watch for card changes from the store
            this.$watch('$store.cardModal.selectedCard', (card) => {
                if (card) {
                    this.loadCardData(card);
                }
            });
        },

        /**
         * Load card data including subtasks and comments
         */
        loadCardData(cardData) {
            this.card = cardData;
            this.subtasks = cardData.subtasks || [];
            this.comments = cardData.comments || [];
        },

        /**
         * Add new subtask
         */
        async addSubtask() {
            if (!this.newSubtaskTitle.trim()) return;

            try {
                const response = await fetch('/api/subtasks', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        card_id: this.card.id,
                        title: this.newSubtaskTitle,
                        is_completed: false
                    })
                });

                if (response.ok) {
                    const newSubtask = await response.json();
                    this.subtasks.push(newSubtask);
                    this.newSubtaskTitle = '';
                    this.showAddSubtask = false;
                    
                    // Show success notification
                    window.showSuccess('Subtask added successfully!');
                }
            } catch (error) {
                console.error('Error adding subtask:', error);
                window.showError('Failed to add subtask. Please try again.');
            }
        },

        /**
         * Toggle subtask completion status
         */
        async toggleSubtask(subtask) {
            const originalStatus = subtask.is_completed;
            
            // Optimistic update
            subtask.is_completed = !subtask.is_completed;

            try {
                const response = await fetch(`/api/subtasks/${subtask.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        is_completed: subtask.is_completed
                    })
                });

                if (!response.ok) {
                    // Revert on failure
                    subtask.is_completed = originalStatus;
                    throw new Error('Failed to update subtask');
                }
            } catch (error) {
                console.error('Error toggling subtask:', error);
                subtask.is_completed = originalStatus;
                
                window.showError('Failed to update subtask. Please try again.');
            }
        },

        /**
         * Edit subtask
         */
        editSubtask(subtask) {
            subtask.editing = true;
            subtask.editTitle = subtask.title;
        },

        /**
         * Save subtask changes
         */
        async saveSubtask(subtask) {
            if (!subtask.editTitle.trim()) return;

            try {
                const response = await fetch(`/api/subtasks/${subtask.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        title: subtask.editTitle
                    })
                });

                if (response.ok) {
                    subtask.title = subtask.editTitle;
                    subtask.editing = false;
                    
                    window.showSuccess('Subtask updated successfully!');
                }
            } catch (error) {
                console.error('Error saving subtask:', error);
                window.showError('Failed to update subtask. Please try again.');
            }
        },

        /**
         * Cancel subtask edit
         */
        cancelEditSubtask(subtask) {
            subtask.editing = false;
            subtask.editTitle = '';
        },

        /**
         * Delete subtask
         */
        async deleteSubtask(subtask) {
            if (!confirm('Are you sure you want to delete this subtask?')) return;

            try {
                const response = await fetch(`/api/subtasks/${subtask.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (response.ok) {
                    this.subtasks = this.subtasks.filter(s => s.id !== subtask.id);
                    
                    window.showSuccess('Subtask deleted successfully!');
                }
            } catch (error) {
                console.error('Error deleting subtask:', error);
                window.showError('Failed to delete subtask. Please try again.');
            }
        },

        /**
         * Add new comment
         */
        async addComment() {
            if (!this.newComment.trim()) return;

            try {
                const response = await fetch('/api/comments', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        card_id: this.card.id,
                        content: this.newComment
                    })
                });

                if (response.ok) {
                    const newComment = await response.json();
                    this.comments.push(newComment);
                    this.newComment = '';
                    
                    window.showSuccess('Comment added successfully!');
                }
            } catch (error) {
                console.error('Error adding comment:', error);
                window.showError('Failed to add comment. Please try again.');
            }
        },

        /**
         * Get count of completed subtasks
         */
        getCompletedSubtasks() {
            return this.subtasks.filter(s => s.is_completed).length;
        },

        /**
         * Handle card update events
         */
        handleCardUpdate(updatedCard) {
            if (this.card && this.card.id === updatedCard.id) {
                this.card = { ...this.card, ...updatedCard };
            }
        }
    }
}
</script>