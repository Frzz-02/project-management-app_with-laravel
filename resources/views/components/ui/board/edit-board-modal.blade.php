{{--
    EDIT BOARD MODAL COMPONENT
    ==========================
    Modal untuk mengedit board name dan description
    
    Props:
    - $board: Board model instance
--}}

@props(['board'])

<div x-data="{ 
        showModal: false, 
        boardName: '{{ $board->board_name }}', 
        description: '{{ $board->description ?? '' }}',
        isLoading: false
     }"
     x-on:edit-board-modal.window="showModal = true; $nextTick(() => { $refs.boardNameInput?.focus(); })"
     x-on:keydown.escape.window="if (showModal && !isLoading) showModal = false"
     class="relative z-50">

    {{-- Modal Backdrop --}}
    <div x-show="showModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/50 backdrop-blur-sm"
         x-on:click="if (!isLoading) showModal = false"></div>

    {{-- Modal Container --}}
    <div x-show="showModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95 translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-4"
         class="fixed inset-0 z-[80] flex items-center justify-center p-4 sm:p-6">

        {{-- Modal Card --}}
        <div class="w-full max-w-lg bg-white/95 backdrop-blur-xl rounded-2xl shadow-2xl border border-white/60 overflow-hidden">
            
            {{-- Modal Header --}}
            <div class="relative px-6 py-5 bg-gradient-to-r from-indigo-600 to-purple-600 border-b border-indigo-500/20">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        {{-- Board Icon --}}
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </div>
                        
                        {{-- Modal Title --}}
                        <div>
                            <h2 class="text-xl font-semibold text-white">Edit Board</h2>
                            <p class="text-indigo-100 text-sm mt-0.5">Update board name and description</p>
                        </div>
                    </div>
                    
                    {{-- Close Button --}}
                    <button type="button" 
                            x-bind:disabled="isLoading"
                            x-on:click="if (!isLoading) { showModal = false; }"
                            class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/10 hover:bg-white/20 disabled:bg-white/5 text-white/70 hover:text-white disabled:text-white/30 transition-all duration-200 disabled:cursor-not-allowed">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Form Body --}}
            <form action="{{ route('boards.update', $board->id) }}" 
                  method="POST" 
                  class="p-6"
                  x-on:submit.prevent="
                    if (!boardName.trim()) {
                        return;
                    }
                    isLoading = true;
                    $el.submit();
                  ">
                @csrf
                @method('PUT')
                
                {{-- Board Name Field --}}
                <div class="mb-6">
                    <label for="board_name_edit" class="block text-sm font-semibold text-gray-700 mb-2">
                        Board Name <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="text" 
                               id="board_name_edit"
                               name="board_name"
                               x-ref="boardNameInput"
                               x-model="boardName"
                               x-bind:disabled="isLoading"
                               placeholder="e.g., Sprint Planning, Design Tasks"
                               class="w-full px-4 py-3 bg-gray-50/80 border-2 border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-0 transition-all duration-200 pr-12 disabled:bg-gray-100 disabled:cursor-not-allowed @error('board_name') border-red-500 bg-red-50/50 @enderror"
                               required
                               maxlength="255">
                        
                        {{-- Input Icon --}}
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
                    
                    {{-- Error Display --}}
                    @error('board_name')
                        <div class="mt-2 flex items-center text-sm text-red-600 bg-red-50/80 rounded-lg px-3 py-2">
                            <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ $message }}
                        </div>
                    @enderror
                    
                    {{-- Character Counter --}}
                    <div class="mt-2 text-xs text-right" 
                         x-bind:class="boardName.length > 240 ? 'text-red-500' : boardName.length > 200 ? 'text-yellow-600' : 'text-gray-500'">
                        <span x-text="boardName.length"></span>/255 characters
                    </div>
                </div>

                {{-- Description Field --}}
                <div class="mb-6">
                    <label for="description_edit" class="block text-sm font-semibold text-gray-700 mb-2">
                        Description <span class="text-gray-400 text-xs font-normal">(Optional)</span>
                    </label>
                    <div class="relative">
                        <textarea id="description_edit"
                                  name="description"
                                  x-model="description"
                                  x-bind:disabled="isLoading"
                                  rows="3"
                                  placeholder="Describe the purpose of this board..."
                                  class="w-full px-4 py-3 bg-gray-50/80 border-2 border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-0 transition-all duration-200 resize-none pr-12 disabled:bg-gray-100 disabled:cursor-not-allowed @error('description') border-red-500 bg-red-50/50 @enderror"></textarea>
                        
                        {{-- Textarea Icon --}}
                        <div class="absolute top-3 right-3 pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                    </div>
                    
                    {{-- Error Message --}}
                    @error('description')
                        <div class="mt-2 flex items-center text-sm text-red-600 bg-red-50/80 rounded-lg px-3 py-2">
                            <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- Form Actions --}}
                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100">
                    {{-- Cancel Button --}}
                    <button type="button" 
                            x-bind:disabled="isLoading"
                            x-on:click="if (!isLoading) { showModal = false; }"
                            class="px-6 py-3 bg-gray-100 hover:bg-gray-200 disabled:bg-gray-50 text-gray-700 disabled:text-gray-400 font-medium rounded-xl transition-all duration-200 hover:scale-105 disabled:scale-100 disabled:cursor-not-allowed">
                        Cancel
                    </button>
                    
                    {{-- Submit Button --}}
                    <button type="submit"
                            x-bind:disabled="!boardName.trim() || isLoading"
                            class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 disabled:from-gray-400 disabled:to-gray-500 text-white font-medium rounded-xl shadow-lg hover:shadow-xl disabled:shadow-none transition-all duration-200 hover:scale-105 disabled:scale-100 disabled:cursor-not-allowed flex items-center space-x-2">
                        
                        {{-- Loading Spinner --}}
                        <svg x-show="isLoading" 
                             class="w-4 h-4 animate-spin" 
                             fill="none" 
                             stroke="currentColor" 
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        
                        {{-- Dynamic Button Text --}}
                        <span x-text="isLoading ? 'Updating...' : 'Update Board'"></span>
                        
                        {{-- Edit Icon --}}
                        <svg x-show="!isLoading" 
                             class="w-4 h-4" 
                             fill="none" 
                             stroke="currentColor" 
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
