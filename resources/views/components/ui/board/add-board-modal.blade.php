{{--
    ADD BOARD MODAL COMPONENT - PERBAIKAN FORM SUBMISSION
    =====================================================
    Modal untuk menambahkan board baru dengan proper form handling
    mengikuti pattern Laravel + Alpine.js yang digunakan di project management app ini.
    
    Perbaikan kunci:
    - Sinkronisasi Alpine.js x-model dengan HTML form values
    - Form submission pattern yang konsisten dengan project architecture
    - Loading state management yang tidak mengganggu form processing
    - Authorization dan validation display sesuai Laravel pattern
--}}

<div x-data="{ 
        showModal: false, 
        boardName: '', 
        description: '',
        position: {{ $nextPosition ?? 1 }},
        isLoading: false,
        formSubmitted: false
     }"
     x-on:add-board-modal.window="showModal = true; $nextTick(() => { $refs.boardNameInput?.focus(); boardName = ''; description = ''; })"
     x-on:keydown.escape.window="if (showModal && !isLoading) showModal = false"
     class="relative z-50">

    {{-- Modal Backdrop dengan Glassmorphism Effect --}}
    <div x-show="showModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/50 backdrop-blur-sm"
         x-on:click="if (!isLoading) showModal = false"></div>

    {{-- Modal Container dengan Professional Transitions --}}
    <div x-show="showModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95 translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-4"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6">

        {{-- Modal Card dengan Backdrop Blur --}}
        <div class="w-full max-w-lg bg-white/95 backdrop-blur-xl rounded-2xl shadow-2xl border border-white/60 overflow-hidden">
            
            {{-- Modal Header dengan Gradient Background --}}
            <div class="relative px-6 py-5 bg-gradient-to-r from-blue-600 to-indigo-600 border-b border-blue-500/20">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        {{-- Board Icon dengan Animated Pulse --}}
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 0a2 2 0 012 2v6a2 2 0 01-2 2m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v8a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        
                        {{-- Modal Title dan Subtitle --}}
                        <div>
                            <h2 class="text-xl font-semibold text-white">Create New Board</h2>
                            <p class="text-blue-100 text-sm mt-0.5">Add a new kanban board to organize project tasks</p>
                        </div>
                    </div>
                    
                    {{-- Close Button dengan Disabled State saat Loading --}}
                    <button type="button" 
                            x-bind:disabled="isLoading"
                            x-on:click="if (!isLoading) { showModal = false; boardName = ''; description = ''; isLoading = false; }"
                            class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/10 hover:bg-white/20 disabled:bg-white/5 text-white/70 hover:text-white disabled:text-white/30 transition-all duration-200 disabled:cursor-not-allowed">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Form Body dengan Proper Event Handling dan Sinkronisasi Values --}}
            <form action="{{ route('boards.store') }}" 
                  method="POST" 
                  class="p-6"
                  x-on:submit.prevent="
                    if (!boardName.trim()) {
                        return;
                    }
                    isLoading = true;
                    formSubmitted = true;
                    
                    // Sinkronisasi Alpine.js values ke form inputs
                    $refs.hiddenBoardName.value = boardName;
                    $refs.hiddenDescription.value = description;
                    $refs.hiddenPosition.value = position;
                    
                    // Submit form secara manual setelah sinkronisasi
                    $el.submit();
                  ">
                @csrf
                
                {{-- Hidden Project ID untuk Database Relationship --}}
                <input type="hidden" name="project_id" value="{{ $project->id }}">

                {{-- Hidden inputs untuk sinkronisasi Alpine.js dengan Laravel form --}}
                <input type="hidden" name="board_name" x-ref="hiddenBoardName" value="">
                <input type="hidden" name="description" x-ref="hiddenDescription" value="">
                <input type="hidden" name="position" x-ref="hiddenPosition" x-bind:value="position">

                {{-- Board Name Field dengan Real-time Validation --}}
                <div class="mb-6">
                    <label for="board_name_display" class="block text-sm font-semibold text-gray-700 mb-2">
                        Board Name <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="text" 
                               id="board_name_display"
                               x-ref="boardNameInput"
                               x-model="boardName"
                               x-bind:disabled="isLoading"
                               placeholder="e.g., Sprint Planning, Design Tasks, Bug Fixes, Testing"
                               class="w-full px-4 py-3 bg-gray-50/80 border-2 border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-0 transition-all duration-200 pr-12 disabled:bg-gray-100 disabled:cursor-not-allowed @error('board_name') border-red-500 bg-red-50/50 @enderror"
                               required
                               maxlength="255">
                        
                        {{-- Input Icon dengan Status Indicator --}}
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5" 
                                 x-bind:class="boardName.trim() ? 'text-green-500' : 'text-gray-400'" 
                                 fill="none" 
                                 stroke="currentColor" 
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                        </div>
                    </div>
                    
                    {{-- Laravel Blade Error Display Pattern --}}
                    @error('board_name')
                        <div class="mt-2 flex items-center text-sm text-red-600 bg-red-50/80 rounded-lg px-3 py-2">
                            <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ $message }}
                        </div>
                    @enderror
                    
                    {{-- Character Counter dengan Dynamic Styling --}}
                    <div class="mt-2 text-xs text-right" 
                         x-bind:class="boardName.length > 240 ? 'text-red-500' : boardName.length > 200 ? 'text-yellow-600' : 'text-gray-500'">
                        <span x-text="boardName.length"></span>/255 characters
                    </div>
                </div>

                {{-- Description Field dengan Enhanced UX --}}
                <div class="mb-6">
                    <label for="description_display" class="block text-sm font-semibold text-gray-700 mb-2">
                        Description <span class="text-gray-400 text-xs font-normal">(Optional)</span>
                    </label>
                    <div class="relative">
                        <textarea id="description_display"
                                  x-model="description"
                                  x-bind:disabled="isLoading"
                                  rows="3"
                                  placeholder="Describe the purpose of this board and what types of tasks it will contain..."
                                  class="w-full px-4 py-3 bg-gray-50/80 border-2 border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-0 transition-all duration-200 resize-none pr-12 disabled:bg-gray-100 disabled:cursor-not-allowed @error('description') border-red-500 bg-red-50/50 @enderror"></textarea>
                        
                        {{-- Textarea Icon --}}
                        <div class="absolute top-3 right-3 pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                    </div>
                    
                    {{-- Error Message untuk Description --}}
                    @error('description')
                        <div class="mt-2 flex items-center text-sm text-red-600 bg-red-50/80 rounded-lg px-3 py-2">
                            <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- Form Actions dengan Proper State Management --}}
                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                    {{-- Cancel Button dengan Loading State --}}
                    <button type="button" 
                            x-bind:disabled="isLoading"
                            x-on:click="if (!isLoading) { showModal = false; boardName = ''; description = ''; isLoading = false; }"
                            class="px-6 py-3 bg-gray-100 hover:bg-gray-200 disabled:bg-gray-50 text-gray-700 disabled:text-gray-400 font-medium rounded-xl transition-all duration-200 hover:scale-105 disabled:scale-100 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-2">
                        Cancel
                    </button>
                    
                    {{-- Submit Button dengan Proper Form Submission --}}
                    <button type="submit"
                            x-bind:disabled="!boardName.trim() || isLoading"
                            class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 disabled:from-gray-400 disabled:to-gray-500 text-white font-medium rounded-xl shadow-lg hover:shadow-xl disabled:shadow-none transition-all duration-200 hover:scale-105 disabled:scale-100 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-blue-300 focus:ring-offset-2 flex items-center space-x-2">
                        
                        {{-- Loading Spinner --}}
                        <svg x-show="isLoading" 
                             class="w-4 h-4 animate-spin" 
                             fill="none" 
                             stroke="currentColor" 
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        
                        {{-- Dynamic Button Text --}}
                        <span x-text="isLoading ? 'Creating Board...' : 'Create Board'"></span>
                        
                        {{-- Plus Icon (hidden saat loading) --}}
                        <svg x-show="!isLoading" 
                             class="w-4 h-4" 
                             fill="none" 
                             stroke="currentColor" 
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{--
    SCRIPT UNTUK MODAL RESET DAN ERROR HANDLING
    ===========================================
    JavaScript tambahan untuk handling modal state reset
    dan error message display sesuai Laravel pattern
--}}
<script>
document.addEventListener('alpine:init', () => {
    // Reset modal state setelah successful submission
    window.addEventListener('board-created', () => {
        // Event ini akan di-dispatch dari controller setelah successful create
        console.log('Board created successfully, resetting modal state');
    });
    
    // Global keyboard shortcut untuk modal
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + Shift + B untuk membuka add board modal
        if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'B') {
            e.preventDefault();
            window.dispatchEvent(new CustomEvent('add-board-modal'));
        }
    });
});
</script>