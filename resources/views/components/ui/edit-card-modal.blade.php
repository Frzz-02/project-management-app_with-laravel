@props(['boards' => null])

@php
    // Ambil boards dari projects yang accessible oleh user
    if (!$boards) {
        $userId = Auth::id();
        $boards = \App\Models\Board::with('project')
            ->whereHas('project', function($query) use ($userId) {
                $query->where('created_by', $userId)
                      ->orWhereHas('members', function($q) use ($userId) {
                          $q->where('user_id', $userId);
                      });
            })
            ->orderBy('board_name')
            ->get();
    }
@endphp

@push('styles')
<style>
    /* Custom Scrollbar for Edit Modal */
    .edit-modal-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    
    .edit-modal-scrollbar::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    
    .edit-modal-scrollbar::-webkit-scrollbar-thumb {
        background: linear-gradient(180deg, #3b82f6 0%, #6366f1 100%);
        border-radius: 10px;
    }
    
    .edit-modal-scrollbar::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(180deg, #2563eb 0%, #4f46e5 100%);
    }
</style>
@endpush

<!-- Edit Card Modal -->
<div x-show="$store.modal.editCard" 
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
    <div class="flex items-center justify-center min-h-screen px-4 py-6">
        <div x-show="$store.modal.editCard" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-95"
             @click.away="$store.modal.close()"
             class="bg-white rounded-xl shadow-2xl border border-white/20 backdrop-blur-xl w-full max-w-2xl">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Edit Card</h3>
                        <p class="text-sm text-gray-500">Update task details</p>
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
            <form x-data="editCardForm()" @submit.prevent="submitForm" class="p-6 space-y-6">
                @csrf
                @method('PATCH')

                <!-- Board Selection -->
                <div>
                    <label for="edit_board_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Board <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <select id="edit_board_id" 
                                name="board_id"
                                x-model="form.board_id"
                                required
                                class="w-full px-4 py-3 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 appearance-none bg-white">
                            <option value="">Choose a board...</option>
                            @foreach($boards as $board)
                                <option value="{{ $board->id }}" 
                                        data-project-id="{{ $board->project_id }}"
                                        data-project-name="{{ $board->project->project_name }}">
                                    {{ $board->board_name }} • {{ $board->project->project_name }}
                                </option>
                            @endforeach
                        </select>
                        <!-- Custom dropdown icon -->
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </div>
                    <div x-show="errors.board_id" x-text="errors.board_id" class="mt-1 text-sm text-red-600"></div>
                    
                    <!-- Selected Board Info -->
                    <div x-show="form.board_id" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 transform -translate-y-2"
                         x-transition:enter-end="opacity-100 transform translate-y-0"
                         class="mt-3 p-3 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-100">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 0a2 2 0 012 2v8a2 2 0 01-2 2m-6 0h6"></path>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900" x-text="selectedBoardName"></p>
                                <p class="text-xs text-gray-600" x-text="'Project: ' + selectedProjectName"></p>
                            </div>
                            <div class="flex-shrink-0">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    Selected
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card Title -->
                <div>
                    <label for="edit_card_title" class="block text-sm font-medium text-gray-700 mb-2">
                        Card Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="edit_card_title" 
                           name="card_title"
                           x-model="form.card_title"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200"
                           placeholder="Enter card title..."
                           required>
                    <div x-show="errors.card_title" x-text="errors.card_title" class="mt-1 text-sm text-red-600"></div>
                </div>

                <!-- Card Description -->
                <div>
                    <label for="edit_description" class="block text-sm font-medium text-gray-700 mb-2">
                        Description
                    </label>
                    <textarea id="edit_description" 
                              name="description"
                              x-model="form.description"
                              rows="4"
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200 resize-none"
                              placeholder="Describe the task..."></textarea>
                    <div x-show="errors.description" x-text="errors.description" class="mt-1 text-sm text-red-600"></div>
                </div>

                <!-- Row 1: Priority & Due Date -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Priority -->
                    <div>
                        <label for="edit_priority" class="block text-sm font-medium text-gray-700 mb-2">
                            Priority <span class="text-red-500">*</span>
                        </label>
                        <select id="edit_priority" 
                                name="priority"
                                x-model="form.priority"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                        </select>
                        <div x-show="errors.priority" x-text="errors.priority" class="mt-1 text-sm text-red-600"></div>
                    </div>

                    <!-- Due Date -->
                    <div>
                        <label for="edit_due_date" class="block text-sm font-medium text-gray-700 mb-2">
                            Due Date
                        </label>
                        <input type="date" 
                               id="edit_due_date" 
                               name="due_date"
                               x-model="form.due_date"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200">
                        <div x-show="errors.due_date" x-text="errors.due_date" class="mt-1 text-sm text-red-600"></div>
                    </div>
                </div>

                <!-- Estimated Hours -->
                <div>
                    <label for="edit_estimated_hours" class="block text-sm font-medium text-gray-700 mb-2">
                        Estimated Hours
                    </label>
                    <input type="number" 
                           id="edit_estimated_hours" 
                           name="estimated_hours"
                           x-model="form.estimated_hours"
                           step="0.5"
                           min="0"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200"
                           placeholder="Enter estimated hours...">
                    <div x-show="errors.estimated_hours" x-text="errors.estimated_hours" class="mt-1 text-sm text-red-600"></div>
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                    <button type="button" 
                            @click="$store.modal.close()"
                            class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" 
                            :disabled="loading"
                            :class="loading ? 'opacity-50 cursor-not-allowed' : ''"
                            class="px-6 py-2 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-lg hover:from-blue-600 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 shadow-lg">
                        <span x-show="!loading">Update Card</span>
                        <span x-show="loading" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Updating...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function editCardForm() {
    return {
        loading: false,
        errors: {},
        cardId: null,
        selectedBoardName: '',
        selectedProjectName: '',
        form: {
            board_id: '',
            card_title: '',
            description: '',
            priority: 'medium',
            due_date: '',
            estimated_hours: ''
        },

        init() {
            // Listen for edit card modal events
            document.addEventListener('edit-card-modal', (e) => {
                if (e.detail) {
                    this.loadCardData(e.detail);
                }
            });
            
            // Watch for board selection changes
            this.$watch('form.board_id', (value) => {
                if (value) {
                    this.updateBoardInfo();
                }
            });
        },

        loadCardData(card) {
            console.log('📝 Loading card data for edit:', card);
            this.cardId = card.id;
            this.form = {
                board_id: card.board_id || '',
                card_title: card.title || card.card_title || '',
                description: card.description || '',
                priority: card.priority || 'medium',
                due_date: card.due_date ? card.due_date.split(' ')[0] : '',
                estimated_hours: card.estimated_hours || ''
            };
            this.errors = {};
            
            // Set initial board info
            if (this.form.board_id) {
                this.$nextTick(() => {
                    this.updateBoardInfo();
                });
            }
            
            console.log('✅ Form loaded:', this.form);
        },

        updateBoardInfo() {
            const selectElement = document.getElementById('edit_board_id');
            if (selectElement && selectElement.selectedIndex > 0) {
                const selectedOption = selectElement.options[selectElement.selectedIndex];
                this.selectedBoardName = selectedOption.text.split(' • ')[0];
                this.selectedProjectName = selectedOption.dataset.projectName;
            }
        },

        async submitForm() {
            if (!this.cardId) {
                alert('Error: Card ID not found');
                return;
            }

            this.loading = true;
            this.errors = {};

            try {
                const formData = new FormData();
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                formData.append('_method', 'PATCH');
                
                // Add form fields
                Object.keys(this.form).forEach(key => {
                    if (this.form[key] !== null && this.form[key] !== '') {
                        formData.append(key, this.form[key]);
                    }
                });

                console.log('🚀 Submitting card update...');
                console.log('📝 Card ID:', this.cardId);
                console.log('📦 Form data:', Object.fromEntries(formData));

                const response = await fetch(`/cards/${this.cardId}`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData,
                    credentials: 'same-origin' // IMPORTANT: Include cookies for auth
                });

                console.log('📡 Response status:', response.status);

                const result = await response.json();
                console.log('📦 Response data:', result);

                if (response.ok) {
                    console.log('✅ Card updated successfully!');
                    Alpine.store('modal').close();
                    // Reload page to show updated card
                    window.location.reload();
                } else {
                    console.error('❌ Update failed:', result);
                    // Handle validation errors or authorization errors
                    if (result.errors) {
                        this.errors = result.errors;
                    }
                    if (result.message) {
                        alert(result.message);
                    }
                }
            } catch (error) {
                console.error('❌ Error updating card:', error);
                console.error('Error stack:', error.stack);
                alert('Terjadi kesalahan saat mengupdate card: ' + error.message);
            } finally {
                this.loading = false;
            }
        }
    }
}

// Update modal store
document.addEventListener('alpine:init', () => {
    if (!Alpine.store('modal')) {
        Alpine.store('modal', {
            addCard: false,
            editCard: false,
            cardDetail: false,
            
            open(modalName, data = null) {
                this[modalName] = true;
                if (data) {
                    document.dispatchEvent(new CustomEvent(modalName + '-modal', { detail: data }));
                }
            },
            
            close() {
                this.addCard = false;
                this.editCard = false;
                this.cardDetail = false;
            }
        });
    } else {
        Alpine.store('modal').editCard = false;
    }
});
</script>
@endpush