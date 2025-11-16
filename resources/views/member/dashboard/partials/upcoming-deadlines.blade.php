<div class="backdrop-blur-xl bg-white/70 rounded-2xl border border-white/20 shadow-lg h-full">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
            <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Upcoming Deadlines
        </h2>
        <p class="text-sm text-gray-600">Tasks due within 7 days</p>
    </div>

    <div class="p-6">
        @forelse($upcomingDeadlines as $card)
        <div class="mb-4 last:mb-0 p-4 bg-gradient-to-br from-white to-gray-50 rounded-xl border-l-4
            @if($card->urgency_color === 'red') border-red-500
            @elseif($card->urgency_color === 'orange') border-orange-500
            @elseif($card->urgency_color === 'yellow') border-yellow-500
            @else border-blue-500 @endif">
            
            {{-- Task Title --}}
            <h3 class="text-sm font-semibold text-gray-900 mb-1">
                {{ $card->card_title }}
            </h3>

            {{-- Project Info --}}
            <p class="text-xs text-gray-600 mb-2">
                {{ $card->board->project->project_name }}
            </p>

            {{-- Deadline Info --}}
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium
                    @if($card->urgency_color === 'red') text-red-600
                    @elseif($card->urgency_color === 'orange') text-orange-600
                    @elseif($card->urgency_color === 'yellow') text-yellow-600
                    @else text-blue-600 @endif">
                    @if($card->is_overdue)
                        🔴 {{ abs($card->days_until_due) }} days overdue
                    @elseif($card->is_today)
                        ⚠️ Due today!
                    @else
                        📅 {{ $card->days_until_due }} days left
                    @endif
                </span>

                {{-- Priority Badge --}}
                <span class="px-2 py-0.5 rounded text-xs font-semibold
                    @if($card->priority === 'high') bg-red-100 text-red-700
                    @elseif($card->priority === 'medium') bg-yellow-100 text-yellow-700
                    @else bg-green-100 text-green-700 @endif">
                    {{ ucfirst($card->priority) }}
                </span>
            </div>
        </div>
        @empty
        <div class="text-center py-8">
            <div class="inline-flex items-center justify-center w-12 h-12 bg-green-100 rounded-full mb-3">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <p class="text-sm text-gray-600">No upcoming deadlines</p>
        </div>
        @endforelse
    </div>
</div>
