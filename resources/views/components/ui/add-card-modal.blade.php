@props(['boards' => null])

@php
    // Ambil boards dari projects yang accessible oleh user
    if (!$boards) {
        $userId = Auth::id();
        $userRole = Auth::user()->role;

        // Admin dapat akses SEMUA boards tanpa filter
        if ($userRole === 'admin') {
            $boards = \App\Models\Board::with('project')
                ->orderBy('board_name')
                ->get();
        } else {
            // Non-admin: Filter berdasarkan project ownership atau team lead role
            $boards = \App\Models\Board::with('project')
                ->whereHas('project', function($query) use ($userId) {
                    // Option 1: User adalah creator project
                    $query->where('created_by', $userId)
                        // Option 2: User adalah team lead di project
                        ->orWhereHas('members', function($q) use ($userId) {
                            $q->where('user_id', $userId)
                              ->where('role', 'team lead');
                        });
                })
                ->orderBy('board_name')
                ->get();
        }
    }
@endphp

@push('styles')
<style>
    /* Custom Scrollbar */
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: linear-gradient(180deg, #6366f1 0%, #8b5cf6 100%);
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(180deg, #4f46e5 0%, #7c3aed 100%);
    }
</style>
@endpush

<!-- Add Card Modal -->
<div x-show="$store.modal.addCard" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-[80] overflow-y-auto"
     @keydown.escape="$store.modal.close()"
     style="display: none;">
    
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm transition-opacity"></div>

    <!-- Modal Content -->
    <div class="flex items-center justify-center min-h-screen px-4 py-6">
        <div x-show="$store.modal.addCard" 
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
                    <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Add New Card</h3>
                        <p class="text-sm text-gray-500">Create a new task card</p>
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
            <form x-data="addCardForm()" @submit.prevent="submitForm" class="p-6 space-y-6">
                @csrf

                <!-- Board Selection -->
                <div>
                    <label for="board_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Select Board <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <select id="board_id" 
                                name="board_id"
                                x-model="form.board_id"
                                @change="loadBoardMembers()"
                                required
                                class="w-full px-4 py-3 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200 appearance-none bg-white">
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
                         class="mt-3 p-3 bg-gradient-to-r from-indigo-50 to-purple-50 rounded-lg border border-indigo-100">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 0a2 2 0 012 2v8a2 2 0 01-2 2m-6 0h6"></path>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900" x-text="selectedBoardName"></p>
                                <p class="text-xs text-gray-600" x-text="'Project: ' + selectedProjectName"></p>
                            </div>
                            <div class="flex-shrink-0">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                    Selected
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card Title -->
                <div>
                    <label for="card_title" class="block text-sm font-medium text-gray-700 mb-2">
                        Card Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="card_title" 
                           name="card_title"
                           x-model="form.card_title"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200"
                           placeholder="Enter card title..."
                           required>
                    <div x-show="errors.card_title" x-text="errors.card_title" class="mt-1 text-sm text-red-600"></div>
                </div>

                <!-- Card Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Description
                    </label>
                    <textarea id="description" 
                              name="description"
                              x-model="form.description"
                              rows="4"
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200 resize-none"
                              placeholder="Describe the task..."></textarea>
                    <div x-show="errors.description" x-text="errors.description" class="mt-1 text-sm text-red-600"></div>
                </div>


                <!-- Row 1: Due Date & Estimated Hours -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Due Date -->
                    <div>
                        <label for="due_date" class="block text-sm font-medium text-gray-700 mb-2">
                            Due Date
                        </label>
                        <input type="date" 
                               id="due_date" 
                               name="due_date"
                               x-model="form.due_date"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200">
                        <div x-show="errors.due_date" x-text="errors.due_date" class="mt-1 text-sm text-red-600"></div>
                    </div>

                    <!-- Estimated Hours -->
                    <div>
                        <label for="estimated_hours" class="block text-sm font-medium text-gray-700 mb-2">
                            Estimated Hours
                        </label>
                        <input type="number" 
                               id="estimated_hours" 
                               name="estimated_hours"
                               x-model="form.estimated_hours"
                               step="0.5"
                               min="0"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200"
                               placeholder="Hours...">
                        <div x-show="errors.estimated_hours" x-text="errors.estimated_hours" class="mt-1 text-sm text-red-600"></div>
                    </div>
                </div>


                
                <!-- Row 2: Status & Priority -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- Priority -->
                    <div>
                        <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">
                            Priority <span class="text-red-500">*</span>
                        </label>
                        <select id="priority" 
                                name="priority"
                                x-model="form.priority"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                        </select>
                        <div x-show="errors.priority" x-text="errors.priority" class="mt-1 text-sm text-red-600"></div>
                    </div>
                </div>
                
                
                <!-- Assignees (if available) -->
                <div x-show="form.board_id && projectMembers.length > 0"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 transform -translate-y-2"
                     x-transition:enter-end="opacity-100 transform translate-y-0">
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        Assign Members
                        <span class="text-xs text-gray-500 font-normal ml-1">(<span x-text="projectMembers.length"></span> available)</span>
                    </label>
                    <div class="space-y-2 max-h-48 overflow-y-auto pr-2 custom-scrollbar">
                        <template x-for="member in projectMembers" :key="member.id">
                            <label class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-indigo-50 transition-all duration-200 cursor-pointer border border-transparent hover:border-indigo-200">
                                <input type="checkbox" 
                                       :name="'assigned_users[]'"
                                       :value="member.user_id"
                                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 focus:ring-2">
                                <div class="ml-3 flex items-center space-x-3 flex-1">
                                    <div class="w-9 h-9 bg-gradient-to-br from-indigo-400 to-purple-500 rounded-full flex items-center justify-center text-white text-sm font-semibold shadow-sm"
                                         x-text="member.user_initial">
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm font-medium text-gray-900" x-text="member.user_name"></div>
                                        <div class="text-xs text-gray-500 flex items-center space-x-2">
                                            <span x-text="member.user_email"></span>
                                            <span>•</span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                                  :class="{
                                                      'bg-blue-100 text-blue-800': member.role === 'team lead',
                                                      'bg-green-100 text-green-800': member.role === 'developer',
                                                      'bg-purple-100 text-purple-800': member.role === 'designer'
                                                  }"
                                                  x-text="member.role_display"></span>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        </template>
                    </div>
                </div>

                <!-- Empty State for Members -->
                <div x-show="form.board_id && projectMembers.length === 0"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     class="text-center py-8 bg-gray-50 rounded-lg border-2 border-dashed border-gray-200">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <p class="mt-2 text-sm text-gray-600">No project members available</p>
                    <p class="text-xs text-gray-500">Select a board first to see available members</p>
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
                            class="px-6 py-2 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-lg hover:from-indigo-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 shadow-lg">
                        <span x-show="!loading">Create Card</span>
                        <span x-show="loading" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Creating...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function addCardForm() {
    return {
        loading: false,
        errors: {},
        projectMembers: [],
        selectedBoardName: '',
        selectedProjectName: '',
        form: {
            board_id: '',
            card_title: '',
            description: '',
            status: 'todo',
            priority: 'medium',
            due_date: '',
            estimated_hours: ''
        },

        init() {
            // Listen for modal events to set default status
            this.$nextTick(() => {
                document.addEventListener('add-card-modal', (e) => {
                    if (e.detail && e.detail.status) {
                        this.form.status = e.detail.status;
                    }
                    if (e.detail && e.detail.board_id) {
                        this.form.board_id = e.detail.board_id;
                        this.loadBoardMembers();
                    }
                });
            });
        },

        async loadBoardMembers() {
            if (!this.form.board_id) {
                this.projectMembers = [];
                this.selectedBoardName = '';
                this.selectedProjectName = '';
                return;
            }

            // Update selected board info
            const selectElement = document.getElementById('board_id');
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            this.selectedBoardName = selectedOption.text.split(' • ')[0];
            this.selectedProjectName = selectedOption.dataset.projectName;

            try {
                const response = await fetch(`/boards/${this.form.board_id}/members`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    this.projectMembers = data.members.map(member => ({
                        id: member.id,
                        user_id: member.user_id,
                        user_name: member.user?.username || member.user?.full_name || 'Unknown',
                        user_email: member.user?.email || '',
                        user_initial: (member.user?.username || member.user?.full_name || 'U').charAt(0).toUpperCase(),
                        role: member.role,
                        role_display: this.formatRole(member.role)
                    })).filter(member => member.role !== 'team lead'); // Exclude team lead
                } else {
                    console.error('Failed to load members');
                    this.projectMembers = [];
                }
            } catch (error) {
                console.error('Error loading members:', error);
                this.projectMembers = [];
            }
        },

        formatRole(role) {
            return role.split(' ').map(word => 
                word.charAt(0).toUpperCase() + word.slice(1)
            ).join(' ');
        },

        async submitForm() {
            this.loading = true;
            this.errors = {};

            try {
                const formData = new FormData();
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                
                // Add form fields
                Object.keys(this.form).forEach(key => {
                    if (this.form[key] !== null && this.form[key] !== '') {
                        formData.append(key, this.form[key]);
                    }
                });

                // Add assigned users
                const assignedUsers = document.querySelectorAll('input[name="assigned_users[]"]:checked');
                assignedUsers.forEach(checkbox => {
                    formData.append('assigned_users[]', checkbox.value);
                });

                console.log('🚀 Submitting card creation...');
                console.log('📝 Form data:', Object.fromEntries(formData));

                const response = await fetch('{{ route("cards.store") }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });

                console.log('📡 Response status:', response.status);

                const result = await response.json();
                console.log('📦 Response data:', result);

                if (response.ok) {
                    console.log('✅ Card created successfully!');
                    // Success - reload page to show new card
                    window.location.reload();
                } else {
                    console.error('❌ Validation errors:', result.errors);
                    // Handle validation errors
                    this.errors = result.errors || {};
                    
                    if (result.message) {
                        alert(result.message);
                    }
                }
            } catch (error) {
                console.error('❌ Error creating card:', error);
                console.error('Error stack:', error.stack);
                alert('Terjadi kesalahan saat membuat card: ' + error.message);
            } finally {
                this.loading = false;
            }
        },

        resetForm() {
            this.form = {
                board_id: '',
                card_title: '',
                description: '',
                status: 'todo',
                priority: 'medium',
                due_date: '',
                estimated_hours: ''
            };
            this.errors = {};
            this.projectMembers = [];
            this.selectedBoardName = '';
            this.selectedProjectName = '';
            
            // Uncheck all assigned users
            document.querySelectorAll('input[name="assigned_users[]"]').forEach(checkbox => {
                checkbox.checked = false;
            });
        }
    }
}

// Global Alpine store for modal management
document.addEventListener('alpine:init', () => {
    Alpine.store('modal', {
        addCard: false,
        
        open(modalName, data = null) {
            this[modalName] = true;
            if (data) {
                document.dispatchEvent(new CustomEvent(modalName + '-modal', { detail: data }));
            }
        },
        
        close() {
            this.addCard = false;
        }
    });
});

// Listen for add card modal trigger
document.addEventListener('add-card-modal', (e) => {
    Alpine.store('modal').open('addCard', e.detail);
});
</script>
@endpush