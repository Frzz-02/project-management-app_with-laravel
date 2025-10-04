<!-- Board 1 -->
<div class="bg-gradient-to-br {{ $cardColorStyle }} rounded-xl p-6 border border-gray-200/50 hover:shadow-lg transition-all duration-300 cursor-pointer">
    <div class="flex items-start justify-between mb-4">
        <h4 class="font-semibold text-gray-800 text-lg">{{ $name }}</h4>
        <span class="text-xs {{ $badgeColorStyle }} px-2 py-1 rounded-full">{{ $totalCards }} cards</span>
    </div>
    
    <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ $description }}</p>
    
    <!-- Sample Cards -->
    <div class="space-y-2 mb-4">

        
        {{-- card --}}
        {{ $slot }}
    </div>
    
</div>