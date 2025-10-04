{{--
/**
 * Add Board Modal Component View
 * 
 * Modal form untuk menambah board baru dengan fitur:
 * - Alpine.js untuk state management dan validasi
 * - Tailwind CSS untuk styling modern
 * - Backdrop blur effect dan smooth animations
 * - Form validation real-time
 * - Responsive design
 * 
 * PROPS YANG DITERIMA:
 * --------------------
 * - $projectId: ID project tempat board akan ditambahkan
 * - $modalId: ID unik untuk modal element
 * - $actionUrl: URL untuk submit form
 * - $method: HTTP method untuk form
 */
--}}

<!-- Modal Backdrop - Background blur dengan opacity -->
<div 
    x-data="{ 
        open: false, 
        boardName: '', 
        description: '', 
        errors: {},
        
        // Method untuk validasi form
        validateForm() {
            this.errors = {};
            
            // Validasi board name (required, max 255 chars)
            if (!this.boardName.trim()) {
                this.errors.board_name = 'Nama board wajib diisi';
            } else if (this.boardName.length > 255) {
                this.errors.board_name = 'Nama board tidak boleh lebih dari 255 karakter';
            }
            
            return Object.keys(this.errors).length === 0;
        },
        
        // Method untuk submit form
        submitForm() {
            if (this.validateForm()) {
                $refs.boardForm.submit();
            }
        },
        
        // Method untuk reset form
        resetForm() {
            this.boardName = '';
            this.description = '';
            this.errors = {};
        },
        
        // Method untuk close modal dengan reset
        closeModal() {
            this.open = false;
            this.resetForm();
        }
    }"
    x-show="open"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
    id="{{ $modalId }}"
    @keydown.escape.window="closeModal()"
    style="display: none;">
    
    <!-- Background Overlay dengan blur effect -->
    <div class="flex items-center justify-center min-h-screen px-4 pt-6 pb-20 text-center sm:block sm:p-0">
        <!-- Background backdrop -->
        <div 
            class="fixed inset-0 transition-opacity bg-gray-900/75 backdrop-blur-sm"
            @click="closeModal()">
        </div>

        <!-- Modal positioning helper -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <!-- Modal Content Container -->
        <div 
            x-show="open"
            x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white/95 backdrop-blur-xl shadow-2xl rounded-2xl border border-white/50 sm:align-middle">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-4 mb-6 border-b border-gray-200/50">
                <div class="flex items-center">
                    <!-- Icon Board -->
                    <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-xl flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 0a2 2 0 012 2v6a2 2 0 01-2 2m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v8a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Add New Board</h3>
                        <p class="text-sm text-gray-500">Create a new kanban board for your project</p>
                    </div>
                </div>
                
                <!-- Close Button -->
                <button 
                    @click="closeModal()"
                    class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Form Content -->
            <form 
                x-ref="boardForm"
                action="{{ $actionUrl }}" 
                method="{{ $method }}"
                class="space-y-6"
                @submit.prevent="submitForm()">
                
                @csrf
                
                <!-- Hidden Project ID -->
                <input type="hidden" name="project_id" value="{{ $projectId }}">
                
                <!-- Board Name Field -->
                <div class="space-y-2">
                    <label for="board_name" class="block text-sm font-medium text-gray-700">
                        Board Name
                        <span class="text-red-500">*</span>
                    </label>
                    
                    <div class="relative">
                        <input 
                            type="text" 
                            id="board_name"
                            name="board_name"
                            x-model="boardName"
                            @input="errors.board_name = ''"
                            placeholder="Enter board name (e.g., To Do, In Progress, Done)"
                            class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 bg-white/50 backdrop-blur-sm"
                            :class="errors.board_name ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : ''"
                            maxlength="255"
                            required>
                            
                        <!-- Character counter -->
                        <div class="absolute right-3 top-3 text-xs text-gray-400">
                            <span x-text="boardName.length"></span>/255
                        </div>
                    </div>
                    
                    <!-- Error message untuk board name -->
                    <div x-show="errors.board_name" class="text-sm text-red-600" x-text="errors.board_name"></div>
                </div>

                <!-- Description Field -->
                <div class="space-y-2">
                    <label for="description" class="block text-sm font-medium text-gray-700">
                        Description
                        <span class="text-gray-400 text-xs">(Optional)</span>
                    </label>
                    
                    <textarea 
                        id="description"
                        name="description"
                        x-model="description"
                        rows="3"
                        placeholder="Describe what this board is for..."
                        class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 bg-white/50 backdrop-blur-sm resize-none"></textarea>
                    
                    <!-- Helper text -->
                    <p class="text-xs text-gray-500">Help your team understand the purpose of this board.</p>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200/50">
                    <!-- Cancel Button -->
                    <button 
                        type="button"
                        @click="closeModal()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors duration-200">
                        Cancel
                    </button>
                    
                    <!-- Submit Button -->
                    <button 
                        type="submit"
                        class="px-6 py-2 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 shadow-md hover:shadow-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="!boardName.trim()">
                        
                        <!-- Loading state dengan Alpine.js -->
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Create Board
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 
JavaScript Helper untuk membuka modal dari luar component.
Usage: openModal('{{ $modalId }}')
--}}
<script>
    function openModal(modalId) {
        // Get modal component by ID dan trigger open
        const modalElement = document.getElementById(modalId);
        if (modalElement) {
            Alpine.evaluate(modalElement, 'open = true');
        }
    }
</script>