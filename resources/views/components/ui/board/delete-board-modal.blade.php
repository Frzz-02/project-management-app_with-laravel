{{--
    DELETE BOARD CONFIRMATION MODAL
    ================================
    Modal konfirmasi untuk menghapus board dengan warning yang jelas.
    
    Props:
    - $board: Board model instance
    
    Fitur:
    - Confirmation dialog dengan warning message
    - Menampilkan jumlah cards yang akan ikut terhapus
    - Loading state saat proses delete
    - Keyboard shortcuts (Escape untuk cancel)
    - Smooth animations
--}}

@props(['board'])

<div x-data="{ 
        showDeleteModal: false,
        isDeleting: false,
        boardToDelete: null
     }"
     x-on:delete-board-{{ $board->id }}.window="showDeleteModal = true; boardToDelete = {{ $board->id }}"
     x-on:keydown.escape.window="if (showDeleteModal && !isDeleting) showDeleteModal = false"
     class="relative z-50">

    {{-- Modal Backdrop dengan Danger Red Tint --}}
    <div x-show="showDeleteModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-red-900/20 backdrop-blur-sm"
         x-on:click="if (!isDeleting) showDeleteModal = false"></div>

    {{-- Modal Container --}}
    <div x-show="showDeleteModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95 translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-4"
         class="fixed inset-0 z-50 flex items-center justify-center p-4">

        {{-- Modal Card --}}
        <div class="w-full max-w-md bg-white/95 backdrop-blur-xl rounded-2xl shadow-2xl border border-red-200/50 overflow-hidden">
            
            {{-- Danger Header dengan Red Gradient --}}
            <div class="relative px-6 py-5 bg-gradient-to-r from-red-600 to-red-700 border-b border-red-500/20">
                <div class="flex items-center space-x-3">
                    {{-- Warning Icon dengan Pulse Animation --}}
                    <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center animate-pulse">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    
                    {{-- Modal Title --}}
                    <div>
                        <h2 class="text-xl font-semibold text-white">Delete Board?</h2>
                        <p class="text-red-100 text-sm mt-0.5">This action cannot be undone</p>
                    </div>
                </div>
            </div>

            {{-- Modal Body --}}
            <div class="p-6">
                {{-- Warning Message dengan Board Info --}}
                <div class="mb-6 p-4 bg-red-50/80 border-l-4 border-red-500 rounded-r-lg">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-red-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold text-red-800 mb-2">You are about to delete:</p>
                            <p class="text-base font-bold text-red-900 mb-3">"{{ $board->board_name }}"</p>
                            
                            {{-- Data yang akan terhapus --}}
                            <div class="space-y-1.5 text-sm text-red-700">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                    </svg>
                                    <span><strong>{{ $board->cards->count() }}</strong> cards will be deleted</span>
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                    </svg>
                                    <span>All comments and subtasks</span>
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>All time logs and assignments</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Confirmation Text --}}
                <p class="text-gray-700 text-sm mb-6">
                    This will permanently delete the board and all its associated data. 
                    Team members will lose access to all cards and information within this board.
                </p>

                {{-- Action Buttons --}}
                <div class="flex items-center justify-end space-x-3">
                    {{-- Cancel Button --}}
                    <button type="button" 
                            x-bind:disabled="isDeleting"
                            x-on:click="if (!isDeleting) { showDeleteModal = false; boardToDelete = null; }"
                            class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 disabled:bg-gray-50 text-gray-700 disabled:text-gray-400 font-medium rounded-xl transition-all duration-200 hover:scale-105 disabled:scale-100 disabled:cursor-not-allowed">
                        Cancel
                    </button>
                    
                    {{-- Delete Form dengan Manual Submission --}}
                    <form action="{{ route('boards.destroy', $board->id) }}" 
                          method="POST" 
                          x-ref="deleteForm"
                          class="inline">
                        @csrf
                        @method('DELETE')
                        
                        <button type="button"
                                x-bind:disabled="isDeleting"
                                x-on:click="
                                    if (!isDeleting) {
                                        isDeleting = true;
                                        $refs.deleteForm.submit();
                                    }
                                "
                                class="px-5 py-2.5 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 disabled:from-gray-400 disabled:to-gray-500 text-white font-medium rounded-xl shadow-lg hover:shadow-xl disabled:shadow-none transition-all duration-200 hover:scale-105 disabled:scale-100 disabled:cursor-not-allowed flex items-center space-x-2">
                            
                            {{-- Loading Spinner --}}
                            <svg x-show="isDeleting" 
                                 class="w-4 h-4 animate-spin" 
                                 fill="none" 
                                 stroke="currentColor" 
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            
                            {{-- Dynamic Button Text --}}
                            <span x-text="isDeleting ? 'Deleting...' : 'Yes, Delete Board'"></span>
                            
                            {{-- Trash Icon --}}
                            <svg x-show="!isDeleting" 
                                 class="w-4 h-4" 
                                 fill="none" 
                                 stroke="currentColor" 
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
