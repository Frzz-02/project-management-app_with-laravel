<div class="bg-white rounded-lg p-3 border border-gray-200 shadow-sm">
    <div class="flex items-center justify-between mb-2">
        <span class="text-sm font-medium text-gray-800">{{ $taskName }}</span>
        <span class="text-xs {{ $badgeColorStyle }} px-2 py-1 rounded-full">{{ $taskStatus }}</span>
    </div>
    <p class="text-xs text-gray-500">{{ $description }}</p>
</div>