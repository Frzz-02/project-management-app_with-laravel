{{--
    Add Board Modal Component
    
    Component ini adalah modal untuk menambahkan board baru dalam sebuah project.
    Modal ini menggunakan Alpine.js untuk interaktivitas dan Tailwind CSS for styling.
    
    Props yang dibutuhkan:
    - $projectId: ID project tempat board akan ditambahkan
    - $existingBoardsCount: Jumlah board yang sudah ada (untuk auto-increment position)
    
    Usage:
    <x-modals.add-board-modal 
        :project-id="$project->id" 
        :existing-boards-count="$project->boards()->count()" 
    />
    
    Field yang digunakan berdasarkan Board Model:
    - project_id: Hidden field, diambil dari props
    - board_name: Text input, required
    - description: Textarea, optional
    - position: Hidden field, auto calculated
--}}

@props([
    'projectId',
    'existingBoardsCount' => 0
])

<!-- Modal Backdrop - menggunakan Alpine.js untuk state management -->
<div 
    x-data="{ 
        open: false,
        formData: {
            project_id: {{ $projectId }},
            board_name: '',
            description: '',
            position: {{ $existingBoardsCount + 1 }}
        },
        errors: {},
        isLoading: false,
        
        // Method untuk reset form
        resetForm() {
            this.formData = {
                project_id: {{ $projectId }},
                board_name: '',
                description: '',
                position: {{ $existingBoardsCount + 1 }}
            };
            this.errors = {};
        },
        
        // Method untuk close modal
        closeModal() {
            this.open = false;
            setTimeout(() => this.resetForm(), 300); // Delay untuk smooth transition
        },
        
        // Method untuk handle form submission
        submitForm() {
            // Validasi client-side sederhana
            this.errors = {};
            
            if (!this.formData.board_name.trim()) {
                this.errors.board_name = 'Board name is required';
                return;
            }
            
            if (this.formData.board_name.length > 100) {
                this.errors.board_name = 'Board name must not exceed 100 characters';
                return;
            }
            
            // Set loading state
            this.isLoading = true;
            
            // Submit form menggunakan native form submission
            this.$refs.boardForm.submit();
        }
    }"
    x-show="open"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 overflow-y-auto"
    style="display: none;"
    @add-board-modal.window="open = true"
    @keydown.escape.window="closeModal()"
>
    <!-- Background Overlay dengan blur effect -->
    <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div 
            class="fixed inset-0 transition-opacity bg-gray-900/50 backdrop-blur-sm"
            @click="closeModal()"
        ></div>

        <!-- Modal Content Container -->
        <div 
            x-show="open"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white/90 backdrop-blur-xl shadow-2xl rounded-2xl border border-white/40"
        >
            <!-- Modal Header -->
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center">
                    <!-- Icon Board dengan gradient -->
                    <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-xl flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2h2a2 2 0 002-2z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800">Add New Board</h3>
                </div>
                
                <!-- Close Button -->
                <button 
                    @click="closeModal()"
                    class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors duration-200"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Form Content -->
            <form 
                x-ref="boardForm"
                action="{{ route('boards.store') }}" 
                method="POST"
                class="space-y-6"
                @submit.prevent="submitForm()"
            >
                @csrf
                
                <!-- Hidden Fields -->
                <!-- Project ID - diambil dari props component -->
                <input type="hidden" name="project_id" x-model="formData.project_id">
                
                <!-- Position - auto calculated berdasarkan existing boards count -->
                <input type="hidden" name="position" x-model="formData.position">

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
                            x-model="formData.board_name"
                            placeholder="Enter board name (e.g., To Do, In Progress, Done)"
                            maxlength="100"
                            class="w-full px-4 py-3 text-gray-900 placeholder-gray-500 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                            :class="{ 'border-red-300 focus:ring-red-500': errors.board_name }"
                            required
                        >
                        <!-- Character Counter -->
                        <div class="absolute right-3 top-3 text-xs text-gray-400">
                            <span x-text="formData.board_name.length"></span>/100
                        </div>
                    </div>
                    <!-- Error Message untuk Board Name -->
                    <p x-show="errors.board_name" x-text="errors.board_name" class="text-sm text-red-600"></p>
                </div>

                <!-- Description Field (Optional) -->
                <div class="space-y-2">
                    <label for="description" class="block text-sm font-medium text-gray-700">
                        Description
                        <span class="text-gray-400 text-xs">(optional)</span>
                    </label>
                    <textarea 
                        id="description"
                        name="description"
                        x-model="formData.description"
                        rows="3"
                        placeholder="Describe what this board is for..."
                        class="w-full px-4 py-3 text-gray-900 placeholder-gray-500 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 resize-none"
                    ></textarea>
                </div>

                <!-- Position Info (Read-only display) -->
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-sm text-blue-700">
                            This board will be positioned at <strong>position <span x-text="formData.position"></span></strong>
                        </span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
                    <!-- Cancel Button -->
                    <button 
                        type="button"
                        @click="closeModal()"
                        class="px-6 py-3 text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200 font-medium"
                        :disabled="isLoading"
                    >
                        Cancel
                    </button>
                    
                    <!-- Submit Button -->
                    <button 
                        type="submit"
                        class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-medium rounded-xl shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
                        :disabled="isLoading || !formData.board_name.trim()"
                    >
                        <!-- Loading Spinner -->
                        <svg 
                            x-show="isLoading" 
                            class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" 
                            fill="none" 
                            viewBox="0 0 24 24"
                        >
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        
                        <!-- Button Text -->
                        <span x-show="!isLoading">Create Board</span>
                        <span x-show="isLoading">Creating...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 
    Script untuk trigger modal dari luar component.
    Gunakan Alpine.js event: $dispatch('add-board-modal')
    
    Contoh penggunaan di tombol:
    <button @click="$dispatch('add-board-modal')">Add Board</button>
--}}
<script>
document.addEventListener('alpine:init', () => {
    // Global function untuk membuka modal dari anywhere
    window.openAddBoardModal = function() {
        window.dispatchEvent(new CustomEvent('add-board-modal'));
    }
});
</script>