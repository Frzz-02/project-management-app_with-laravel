{{--
/**
 * Create Card Modal Component
 * 
 * Modal untuk membuat card baru dengan:
 * - Form validation
 * - Board selection
 * - Status and priority selection
 * - Assignee selection
 * - Due date picker
 * - Real-time feedback
 * 
 * @param array $filterData - Data untuk dropdown options (boards, users, dll)
 */
--}}

@props(['filterData'])

<div x-show="$store.cardModal.create" 
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
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full"
             x-data="createCardModal()" 
             @keydown.escape="$store.cardModal.closeAll()">
             
            <form @submit.prevent="submitForm()">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-medium text-gray-900">Create New Card</h3>
                        <button type="button" 
                                @click="$store.cardModal.closeAll()"
                                class="rounded-md text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Form Fields -->
                    <div class="space-y-6">
                        
                        <!-- Card Title -->
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                                Card Title <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="title"
                                   x-model="form.title"
                                   placeholder="Enter card title..."
                                   class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                   :class="errors.title ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''"
                                   required>
                            <p x-show="errors.title" x-text="errors.title" class="mt-1 text-sm text-red-600"></p>
                        </div>

                        <!-- Card Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                Description
                            </label>
                            <textarea id="description"
                                      x-model="form.description"
                                      rows="4"
                                      placeholder="Enter card description..."
                                      class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm resize-none"
                                      :class="errors.description ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''"></textarea>
                            <p x-show="errors.description" x-text="errors.description" class="mt-1 text-sm text-red-600"></p>
                        </div>

                        <!-- Board Selection -->
                        <div>
                            <label for="board_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Board <span class="text-red-500">*</span>
                            </label>
                            <select id="board_id"
                                    x-model="form.board_id"
                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    :class="errors.board_id ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''"
                                    required>
                                <option value="">Select a board...</option>
                                @foreach($filterData['boards'] ?? [] as $board)
                                    <option value="{{ $board->id }}">
                                        {{ $board->project->project_name }} - {{ $board->board_name }}
                                    </option>
                                @endforeach
                            </select>
                            <p x-show="errors.board_id" x-text="errors.board_id" class="mt-1 text-sm text-red-600"></p>
                        </div>

                        <!-- Two Column Layout -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <!-- Status -->
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                    Status
                                </label>
                                <select id="status"
                                        x-model="form.status"
                                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="todo">Todo</option>
                                    <option value="in progress">In Progress</option>
                                    <option value="review">Review</option>
                                    <option value="done">Done</option>
                                </select>
                            </div>

                            <!-- Priority -->
                            <div>
                                <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">
                                    Priority
                                </label>
                                <select id="priority"
                                        x-model="form.priority"
                                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                </select>
                            </div>
                        </div>

                        <!-- Due Date -->
                        <div>
                            <label for="due_date" class="block text-sm font-medium text-gray-700 mb-2">
                                Due Date
                            </label>
                            <input type="date" 
                                   id="due_date"
                                   x-model="form.due_date"
                                   class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                   :class="errors.due_date ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''">
                            <p x-show="errors.due_date" x-text="errors.due_date" class="mt-1 text-sm text-red-600"></p>
                        </div>

                        <!-- Assignees -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Assignees
                            </label>
                            <div class="space-y-2 max-h-40 overflow-y-auto border border-gray-300 rounded-md p-3">
                                @foreach($filterData['users'] ?? [] as $user)
                                    <label class="flex items-center space-x-2 cursor-pointer hover:bg-gray-50 p-1 rounded">
                                        <input type="checkbox" 
                                               :value="{{ $user->id }}"
                                               x-model="form.assignees"
                                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <div class="flex items-center space-x-2">
                                            <div class="w-6 h-6 bg-gradient-to-r from-indigo-400 to-purple-500 rounded-full flex items-center justify-center text-white text-xs font-medium">
                                                {{ strtoupper(substr($user->full_name ?? 'U', 0, 1)) }}
                                            </div>
                                            <span class="text-sm text-gray-700">{{ $user->full_name }}</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit"
                            :disabled="submitting"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg x-show="submitting" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="submitting ? 'Creating...' : 'Create Card'"></span>
                    </button>
                    
                    <button type="button" 
                            @click="$store.cardModal.closeAll()"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
/**
 * Create Card Modal Component
 * 
 * Handles form submission, validation, and UI feedback for creating new cards
 */
function createCardModal() {
    return {
        // Form data
        form: {
            title: '',
            description: '',
            board_id: '',
            status: 'todo',
            priority: 'medium',
            due_date: '',
            assignees: []
        },

        // Form state
        submitting: false,
        errors: {},

        /**
         * Initialize component
         */
        init() {
            // Reset form when modal opens
            this.$watch('$store.cardModal.create', (isOpen) => {
                if (isOpen) {
                    this.resetForm();
                }
            });
        },

        /**
         * Submit the form
         */
        async submitForm() {
            if (this.submitting) return;

            this.submitting = true;
            this.errors = {};

            try {
                const response = await fetch('/cards', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.form)
                });

                const data = await response.json();

                if (response.ok) {
                    // Success - close modal and refresh page
                    this.$store.cardModal.closeAll();
                    
                    window.showSuccess('Card created successfully!');

                    // Refresh the page to show new card
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                    
                } else {
                    // Handle validation errors
                    if (data.errors) {
                        this.errors = data.errors;
                    } else {
                        window.showError(data.message || 'Failed to create card. Please try again.');
                    }
                }
            } catch (error) {
                console.error('Error creating card:', error);
                window.showError('An error occurred. Please try again.');
            } finally {
                this.submitting = false;
            }
        },

        /**
         * Reset form to initial state
         */
        resetForm() {
            this.form = {
                title: '',
                description: '',
                board_id: '',
                status: 'todo',
                priority: 'medium',
                due_date: '',
                assignees: []
            };
            this.errors = {};
            this.submitting = false;
        }
    }
}
</script>