@extends('layouts.app')

@section('title', $board->board_name . ' - Board')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50" x-data="boardData()">
    
    <!-- Flash Messages -->
    @if(session('success'))
    <div x-data="{ show: true }" 
         x-show="show" 
         x-init="setTimeout(() => show = false, 5000)"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed top-4 right-4 z-50 max-w-md">
        <div class="backdrop-blur-xl bg-green-500/90 text-white px-6 py-4 rounded-lg shadow-2xl border border-green-400/20 flex items-center space-x-3">
            <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="font-medium">{{ session('success') }}</p>
            <button @click="show = false" class="ml-4 hover:bg-green-600/50 rounded p-1 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div x-data="{ show: true }" 
         x-show="show" 
         x-init="setTimeout(() => show = false, 5000)"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed top-4 right-4 z-50 max-w-md">
        <div class="backdrop-blur-xl bg-red-500/90 text-white px-6 py-4 rounded-lg shadow-2xl border border-red-400/20 flex items-center space-x-3">
            <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="font-medium">{{ session('error') }}</p>
            <button @click="show = false" class="ml-4 hover:bg-red-600/50 rounded p-1 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </div>
    @endif

<!-- Header Board -->

    <div class="sticky top-0 z-30 backdrop-blur-xl bg-white/60 border-b border-white/20 shadow-lg">
        <div class="max-w-full mx-auto px-4 sm:px-6 py-3 sm:py-4">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 sm:gap-4">
                


<!-- Breadcrumb -->

                <div class="flex flex-col gap-2 min-w-0 flex-1">
                    <div class="flex items-center flex-wrap gap-x-2 gap-y-1 text-xs sm:text-sm text-gray-600">
                        <a href="{{ route('projects.index') }}" class="hover:text-indigo-600 transition-colors whitespace-nowrap">Projects</a>
                        <svg class="w-3 h-3 sm:w-4 sm:h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 111.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <a href="{{ route('projects.show', $board->project) }}" class="hover:text-indigo-600 transition-colors truncate max-w-[120px] sm:max-w-[200px]" title="{{ $board->project->project_name }}">{{ $board->project->project_name }}</a>
                        <svg class="w-3 h-3 sm:w-4 sm:h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 111.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-indigo-600 font-medium truncate" title="{{ $board->board_name }}">{{ $board->board_name }}</span>
                    </div>
                    
                    {{-- Member Notice Badge --}}
                    @if(Auth::user()->role === 'member' && !$isTeamLead && !$isProjectCreator)
                        <div class="inline-flex items-center space-x-2 px-3 py-1.5 bg-gradient-to-r from-blue-500/10 to-indigo-500/10 border border-blue-300/30 rounded-lg backdrop-blur-sm max-w-fit">
                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="text-xs font-medium text-blue-700">Anda hanya melihat cards yang ditugaskan kepada Anda</span>
                        </div>
                    @endif
                </div>

                


                <!-- Board Actions -->
                @if($isAdmin || $isProjectCreator || $isTeamLead)
                    <div class="flex items-center gap-2 sm:gap-3 flex-shrink-0">
                        {{-- Edit Board Button - Only for creator and admin --}}
                        @if($isAdmin || $isProjectCreator)
                            @can('update', $board)
                                <button 
                                    @click="$dispatch('edit-board-modal')"
                                    class="inline-flex items-center px-3 py-1.5 sm:px-4 sm:py-2 bg-gradient-to-r from-gray-500 to-gray-600 text-white text-xs sm:text-sm font-medium rounded-lg hover:from-gray-600 hover:to-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200 shadow-lg whitespace-nowrap"
                                >
                                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    <span class="hidden sm:inline">Edit Board</span>
                                </button>
                            @endcan
                        @endif
                        
                        {{-- Add Card Button - For team lead, creator and admin --}}
                        @can('create', App\Models\Card::class)
                            <button 
                                @click="$dispatch('add-card-modal')"
                                class="inline-flex items-center px-3 py-1.5 sm:px-4 sm:py-2 bg-gradient-to-r from-indigo-500 to-purple-600 text-white text-xs sm:text-sm font-medium rounded-lg hover:from-indigo-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 shadow-lg whitespace-nowrap"
                            >
                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                <span>Add Card</span>
                            </button>
                        @endcan
                    </div>
                @endif
            </div>

            

            


<!-- Board Info -->

            <div class="mt-3 sm:mt-4 flex flex-col gap-3 sm:gap-4">
                <div class="min-w-0">
                    <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900 truncate" title="{{ $board->board_name }}">{{ $board->board_name }}</h1>
                    @if($board->description)
                        <p class="mt-1 text-sm sm:text-base text-gray-600 line-clamp-2">{{ $board->description }}</p>
                    @endif
                </div>

                


<!-- Statistics Cards -->

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:flex lg:flex-wrap gap-2 sm:gap-3">
                    <div class="bg-white/70 backdrop-blur-sm rounded-lg px-3 py-2 border border-white/20 shadow-sm">
                        <div class="text-xs text-gray-500 truncate">Total Cards</div>
                        <div class="text-base sm:text-lg font-semibold text-gray-900">{{ $stats['total_cards'] }}</div>
                    </div>
                    <div class="bg-white/70 backdrop-blur-sm rounded-lg px-3 py-2 border border-white/20 shadow-sm">
                        <div class="text-xs text-gray-500 truncate">In Progress</div>
                        <div class="text-base sm:text-lg font-semibold text-blue-600">{{ $stats['in_progress_cards'] }}</div>
                    </div>
                    <div class="bg-white/70 backdrop-blur-sm rounded-lg px-3 py-2 border border-white/20 shadow-sm">
                        <div class="text-xs text-gray-500 truncate">Review</div>
                        <div class="text-base sm:text-lg font-semibold text-yellow-600">{{ $stats['review_cards'] }}</div>
                    </div>
                    <div class="bg-white/70 backdrop-blur-sm rounded-lg px-3 py-2 border border-white/20 shadow-sm">
                        <div class="text-xs text-gray-500 truncate">Completed</div>
                        <div class="text-base sm:text-lg font-semibold text-green-600">{{ $stats['done_cards'] }}</div>
                    </div>
                    @if($stats['overdue_cards'] > 0)
                    <div class="bg-red-50/70 backdrop-blur-sm rounded-lg px-3 py-2 border border-red-200/50 shadow-sm col-span-2 sm:col-span-1">
                        <div class="text-xs text-red-500 truncate">Overdue</div>
                        <div class="text-base sm:text-lg font-semibold text-red-600">{{ $stats['overdue_cards'] }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    


<!-- Kanban Board -->

    <div class="p-3 sm:p-4 lg:p-6">
        <div class="flex gap-3 sm:gap-4 lg:gap-6 overflow-x-auto pb-4 snap-x snap-mandatory scrollbar-thin scrollbar-thumb-indigo-500 scrollbar-track-gray-200">
            
            


<!-- Todo Column -->

            <div class="kanban-column snap-start flex-shrink-0" data-status="todo">
                <div class="bg-white/70 backdrop-blur-xl rounded-xl border border-white/20 shadow-lg w-72 sm:w-80">
                    


<!-- Column Header -->

                    <div class="p-3 sm:p-4 border-b border-gray-200/50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <div class="w-3 h-3 bg-gray-400 rounded-full flex-shrink-0"></div>
                                <h3 class="font-semibold text-gray-900 text-sm sm:text-base">To Do</h3>
                                <span class="bg-gray-100 text-gray-600 px-2 py-0.5 sm:py-1 rounded-full text-xs font-medium">
                                    {{ $cardsByStatus->get('todo', collect())->count() }}
                                </span>
                            </div>
                        </div>
                    </div>

                    


<!-- Cards Container -->

                    <div class="p-3 sm:p-4 space-y-3 min-h-[20rem] max-h-[calc(100vh-20rem)] overflow-y-auto">
                        @foreach($cardsByStatus->get('todo', collect()) as $card)
                            <x-ui.card-item :card="$card" :board="$board" />
                        @endforeach
                    </div>
                </div>
            </div>

            


<!-- In Progress Column -->

            <div class="kanban-column snap-start flex-shrink-0" data-status="in progress">
                <div class="bg-white/70 backdrop-blur-xl rounded-xl border border-white/20 shadow-lg w-72 sm:w-80">
                    


<!-- Column Header -->

                    <div class="p-3 sm:p-4 border-b border-gray-200/50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <div class="w-3 h-3 bg-blue-500 rounded-full flex-shrink-0"></div>
                                <h3 class="font-semibold text-gray-900 text-sm sm:text-base">In Progress</h3>
                                <span class="bg-blue-100 text-blue-600 px-2 py-0.5 sm:py-1 rounded-full text-xs font-medium">
                                    {{ $cardsByStatus->get('in progress', collect())->count() }}
                                </span>
                            </div>
                            
                        </div>
                    </div>

                    


<!-- Cards Container -->

                    <div class="p-3 sm:p-4 space-y-3 min-h-[20rem] max-h-[calc(100vh-20rem)] overflow-y-auto">
                        @foreach($cardsByStatus->get('in progress', collect()) as $card)
                            <x-ui.card-item :card="$card" :board="$board" />
                        @endforeach
                    </div>
                </div>
            </div>

            


<!-- Review Column -->

            <div class="kanban-column snap-start flex-shrink-0" data-status="review">
                <div class="bg-white/70 backdrop-blur-xl rounded-xl border border-white/20 shadow-lg w-72 sm:w-80">
                    
                    

<!-- Column Header -->

                    <div class="p-3 sm:p-4 border-b border-gray-200/50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <div class="w-3 h-3 bg-yellow-500 rounded-full flex-shrink-0"></div>
                                <h3 class="font-semibold text-gray-900 text-sm sm:text-base">Review</h3>
                                <span class="bg-yellow-100 text-yellow-600 px-2 py-0.5 sm:py-1 rounded-full text-xs font-medium">
                                    {{ $cardsByStatus->get('review', collect())->count() }}
                                </span>
                            </div>
                        </div>
                    </div>

                    


<!-- Cards Container -->

                    <div class="p-3 sm:p-4 space-y-3 min-h-[20rem] max-h-[calc(100vh-20rem)] overflow-y-auto">
                        @foreach($cardsByStatus->get('review', collect()) as $card)
                            <x-ui.card-item :card="$card" :board="$board" />
                        @endforeach
                    </div>
                </div>
            </div>

            


<!-- Done Column -->

            <div class="kanban-column snap-start flex-shrink-0" data-status="done">
                <div class="bg-white/70 backdrop-blur-xl rounded-xl border border-white/20 shadow-lg w-72 sm:w-80">
                    


<!-- Column Header -->

                    <div class="p-3 sm:p-4 border-b border-gray-200/50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <div class="w-3 h-3 bg-green-500 rounded-full flex-shrink-0"></div>
                                <h3 class="font-semibold text-gray-900 text-sm sm:text-base">Done</h3>
                                <span class="bg-green-100 text-green-600 px-2 py-0.5 sm:py-1 rounded-full text-xs font-medium">
                                    {{ $cardsByStatus->get('done', collect())->count() }}
                                </span>
                            </div>
                        </div>
                    </div>

                    


<!-- Cards Container -->

                    <div class="p-3 sm:p-4 space-y-3 min-h-[20rem] max-h-[calc(100vh-20rem)] overflow-y-auto">
                        @foreach($cardsByStatus->get('done', collect()) as $card)
                            <x-ui.card-item :card="$card" :board="$board" />
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    


<!-- Add Card Modal -->

    <x-ui.add-card-modal :board="$board" />

    


<!-- Edit Card Modal -->

    <x-ui.edit-card-modal :board="$board" />

    


<!-- Edit Board Modal -->

    <x-ui.board.edit-board-modal :board="$board" />

    


<!-- Card Detail Modal -->

        <!-- Card Detail Modal -->
    <x-ui.card-detail-modal :board="$board" />
    
    <!-- Global Delete Confirmation Modal -->
    <div x-show="showDeleteModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[100] overflow-y-auto"
         style="display: none;"
         @keydown.escape.window="showDeleteModal = false">
        
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-75 backdrop-blur-sm z-[100]"></div>

        <!-- Modal Content Container -->
        <div class="flex items-center justify-center min-h-screen px-4 py-6 relative z-[101]">
            <div x-show="showDeleteModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95"
                 @click.away="showDeleteModal = false"
                 class="bg-white rounded-xl shadow-2xl max-w-md w-full p-4 sm:p-6 relative">
                
                <!-- Icon Warning -->
                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full mb-4">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                
                <!-- Title -->
                <h3 class="text-base sm:text-lg font-semibold text-gray-900 text-center mb-2">
                    Hapus Card?
                </h3>
                
                <!-- Message -->
                <p class="text-xs sm:text-sm text-gray-600 text-center mb-4 sm:mb-6 px-2">
                    Apakah Anda yakin ingin menghapus card "<span class="font-semibold" x-text="deleteCardTitle"></span>"?
                    <br><br>
                    <span class="text-red-600 font-medium">Tindakan ini tidak dapat dibatalkan!</span>
                    <br>
                    Semua data terkait (subtasks, comments, time logs) akan ikut terhapus.
                </p>
                
                <!-- Actions -->
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3">
                    <button @click="showDeleteModal = false"
                            class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium text-sm order-2 sm:order-1">
                        Batal
                    </button>
                    <form :action="'/cards/' + deleteCardId" method="POST" class="flex-1 order-1 sm:order-2">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium text-sm">
                            Ya, Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

@push('scripts')
<script>
function boardData() {
    return {
        loading: false,
        showDeleteModal: false,
        deleteCardId: null,
        deleteCardTitle: '',

        init() {
            console.log('✅ boardData() initialized');
            
            // Register event listener INSIDE component
            window.addEventListener('show-delete-modal', (e) => {
                console.log('🗑️ Delete modal triggered (inside component):', e.detail);
                this.deleteCardId = e.detail.cardId;
                this.deleteCardTitle = e.detail.cardTitle;
                this.showDeleteModal = true;
                console.log('✅ Modal state updated:', this.showDeleteModal);
            });
        },

        // Show delete confirmation modal
        confirmDelete(cardId, cardTitle) {
            this.deleteCardId = cardId;
            this.deleteCardTitle = cardTitle;
            this.showDeleteModal = true;
        },

        // Update card status
        async updateCardStatus(cardId, newStatus) {
            if (this.loading) return;

            this.loading = true;
            try {
                const formData = new FormData();
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                formData.append('_method', 'PATCH'); 
                formData.append('status', newStatus);

                const response = await fetch(`/cards/${cardId}/status`, {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (response.ok) {
                    // Success - reload page to reflect changes
                    window.location.reload();
                } else {
                    alert(result.message || 'Gagal mengupdate status card');
                }
            } catch (error) {
                console.error('Error updating card status:', error);
                alert('Terjadi kesalahan saat mengupdate status');
            } finally {
                this.loading = false;
            }
        }
    }
}

// Initialize Alpine stores and event listeners
document.addEventListener('alpine:init', () => {
    // Global modal store
    Alpine.store('modal', {
        addCard: false,
        editCard: false,
        cardDetail: false,
        
        close() {
            this.addCard = false;
            this.editCard = false;
            this.cardDetail = false;
        }
    });
});

// Listen for add card modal trigger
document.addEventListener('add-card-modal', (e) => {
    Alpine.store('modal').addCard = true;
});

// Listen for edit card modal trigger
document.addEventListener('edit-card-modal', (e) => {
    Alpine.store('modal').editCard = true;
    // Re-dispatch for modal component to receive data
    document.dispatchEvent(new CustomEvent('edit-card-modal', { detail: e.detail }));
});

// Listen for card detail modal trigger  
document.addEventListener('card-detail-modal', (e) => {
    console.log('📡 show.blade.php received card-detail-modal event:', e.detail);
    Alpine.store('modal').cardDetail = true;
    // Event already dispatched by card-item, no need to re-dispatch
});
</script>
@endpush

@endsection