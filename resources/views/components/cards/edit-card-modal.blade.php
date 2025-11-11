{{--
/**
 * Edit Card Modal Component
 * 
 * Modal untuk mengedit card existing dengan:
 * - Pre-filled form data
 * - Form validation
 * - Status and priority selection
 * - Assignee selection
 * - Due date picker
 * - Real-time updates
 */
--}}

<div x-show="$store.cardModal.edit" 
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
             x-data="editCardModal()" 
             @keydown.escape="$store.cardModal.closeAll()">
             
            <form @submit.prevent="submitForm()">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-medium text-gray-900">Edit Card</h3>
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
                            <label for="edit_title" class="block text-sm font-medium text-gray-700 mb-2">
                                Card Title <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="edit_title"
                                   x-model="form.title"
                                   placeholder="Enter card title..."
                                   class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                   :class="errors.title ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''"
                                   required>
                            <p x-show="errors.title" x-text="errors.title" class="mt-1 text-sm text-red-600"></p>
                        </div>

                        <!-- Card Description -->
                        <div>
                            <label for="edit_description" class="block text-sm font-medium text-gray-700 mb-2">
                                Description
                            </label>
                            <textarea id="edit_description"
                                      x-model="form.description"
                                      rows="4"
                                      placeholder="Enter card description..."
                                      class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm resize-none"
                                      :class="errors.description ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''"></textarea>
                            <p x-show="errors.description" x-text="errors.description" class="mt-1 text-sm text-red-600"></p>
                        </div>

                        <!-- Two Column Layout -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <!-- Status -->
                            <div>
                                <label for="edit_status" class="block text-sm font-medium text-gray-700 mb-2">
                                    Status
                                </label>
                                <select id="edit_status"
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
                                <label for="edit_priority" class="block text-sm font-medium text-gray-700 mb-2">
                                    Priority
                                </label>
                                <select id="edit_priority"
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
                            <label for="edit_due_date" class="block text-sm font-medium text-gray-700 mb-2">
                                Due Date
                            </label>
                            <input type="date" 
                                   id="edit_due_date"
                                   x-model="form.due_date"
                                   class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                   :class="errors.due_date ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''">
                            <p x-show="errors.due_date" x-text="errors.due_date" class="mt-1 text-sm text-red-600"></p>
                        </div>

                        <!-- Project & Board Info (Read-only) -->
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <div class="flex items-center space-x-4 text-sm text-gray-600">
                                <div class="flex items-center space-x-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                    <span>Project: <span x-text="card?.board?.project?.name || 'Unknown'"></span></span>
                                </div>
                                
                                <span>•</span>
                                
                                <div class="flex items-center space-x-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2h2a2 2 0 002-2z"></path>
                                    </svg>
                                    <span>Board: <span x-text="card?.board?.name || 'Unknown'"></span></span>
                                </div>
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
                        <span x-text="submitting ? 'Updating...' : 'Update Card'"></span>
                    </button>
                    
                    <button type="button" 
                            @click="deleteCard()"
                            :disabled="submitting"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-red-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-red-700 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                        Delete Card
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
 * Edit Card Modal Component
 * 
 * Handles editing existing cards, including form validation and deletion
 */
function editCardModal() {
    return {
        // Current card being edited
        card: null,

        // Form data
        form: {
            title: '',
            description: '',
            status: 'todo',
            priority: 'medium',
            due_date: ''
        },

        // Form state
        submitting: false,
        errors: {},

        /**
         * Initialize component
         */
        init() {
            // Watch for selected card changes
            this.$watch('$store.cardModal.selectedCard', (card) => {
                if (card && this.$store.cardModal.edit) {
                    this.loadCardData(card);
                }
            });

            // Watch for modal opening
            this.$watch('$store.cardModal.edit', (isOpen) => {
                if (isOpen && this.$store.cardModal.selectedCard) {
                    this.loadCardData(this.$store.cardModal.selectedCard);
                }
            });
        },

        /**
         * Load card data into form
         */
        loadCardData(cardData) {
            this.card = cardData;
            this.form = {
                title: cardData.title || '',
                description: cardData.description || '',
                status: cardData.status || 'todo',
                priority: cardData.priority || 'medium',
                due_date: cardData.due_date || ''
            };
            this.errors = {};
        },

        /**
         * Submit the form
         */
        async submitForm() {
            if (this.submitting || !this.card) return;

            this.submitting = true;
            this.errors = {};

            try {
                const response = await fetch(`/cards/${this.card.id}`, {
                    method: 'PUT',
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
                    
                    window.showSuccess('Card updated successfully!');

                    // Emit event for card update
                    this.$dispatch('card-updated', data);

                    // Refresh the page to show changes
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                    
                } else {
                    // Handle validation errors
                    if (data.errors) {
                        this.errors = data.errors;
                    } else {
                        window.showError(data.message || 'Failed to update card. Please try again.');
                    }
                }
            } catch (error) {
                console.error('Error updating card:', error);
                window.showError('An error occurred. Please try again.');
            } finally {
                this.submitting = false;
            }
        },

        /**
         * Delete the card
         */
        async deleteCard() {
            if (this.submitting || !this.card) return;

            if (!confirm('Are you sure you want to delete this card? This action cannot be undone.')) {
                return;
            }

            this.submitting = true;

            try {
                const response = await fetch(`/cards/${this.card.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (response.ok) {
                    // Success - close modal and refresh page
                    this.$store.cardModal.closeAll();
                    
                    window.showSuccess('Card deleted successfully!');

                    // Refresh the page to remove card
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                    
                } else {
                    const data = await response.json();
                    window.showError(data.message || 'Failed to delete card. Please try again.');
                }
            } catch (error) {
                console.error('Error deleting card:', error);
                window.showError('An error occurred. Please try again.');
            } finally {
                this.submitting = false;
            }
        }
    }
}
</script>