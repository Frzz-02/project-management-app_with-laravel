@props(['name', 'description', 'totalCards', 'board' => null, 'boardUrl' => null, 'cardColorStyle' => 'from-blue-50 to-indigo-100', 'badgeColorStyle' => 'bg-blue-100 text-blue-700'])

<div class="bg-gradient-to-br {{ $cardColorStyle }} rounded-xl p-6 border border-gray-200/50 hover:shadow-xl hover:scale-105 transition-all duration-300 group relative cursor-pointer" onclick="window.location.href='{{ $boardUrl }}'">
    
    @if($board && auth()->user()->can('delete', $board))
    <button type="button" x-on:click.stop="$dispatch('delete-board-{{ $board->id }}')" class="absolute top-3 right-3 w-8 h-8 bg-red-500/10 hover:bg-red-500 text-red-600 hover:text-white rounded-lg flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-200 z-10" title="Delete Board">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
    </button>
    @endif
    
    <div class="flex items-start justify-between mb-4">
        <h4 class="font-semibold text-gray-800 text-lg group-hover:text-indigo-600 transition-colors pr-10">{{ $name }}</h4>
        <span class="text-xs {{ $badgeColorStyle }} px-2 py-1 rounded-full group-hover:bg-indigo-100 group-hover:text-indigo-700 transition-colors whitespace-nowrap">{{ $totalCards }} cards</span>
    </div>
    
    <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ $description }}</p>
    
    <div class="space-y-2 mb-4">{{ $slot }}</div>
</div>