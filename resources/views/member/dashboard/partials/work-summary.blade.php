<div class="backdrop-blur-xl bg-white/70 rounded-2xl border border-white/20 shadow-lg h-full">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            Today's Work Summary
        </h2>
        <p class="text-sm text-gray-600">Your productivity today</p>
    </div>

    <div class="p-6">
        {{-- Summary Stats --}}
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="text-center">
                <div class="text-2xl font-bold text-purple-600">
                    {{ number_format($todayWorkSummary['total_hours'], 1) }}h
                </div>
                <div class="text-xs text-gray-600 mt-1">Total Hours</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-blue-600">
                    {{ $todayWorkSummary['sessions_count'] }}
                </div>
                <div class="text-xs text-gray-600 mt-1">Sessions</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-green-600">
                    {{ $todayWorkSummary['completed_subtasks'] }}
                </div>
                <div class="text-xs text-gray-600 mt-1">Subtasks Done</div>
            </div>
        </div>

        {{-- Active Timer --}}
        @if($todayWorkSummary['active_timer'])
        <div class="mb-6 p-4 bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-xl">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs text-green-600 font-medium mb-1">Currently Working On</div>
                    <div class="text-sm font-semibold text-gray-900">
                        {{ $todayWorkSummary['active_timer']->card->card_title }}
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-xs text-green-600 mb-1">Timer</div>
                    <div class="text-xl font-mono font-bold text-green-700" id="active-timer">
                        {{ gmdate('H:i:s', now()->diffInSeconds($todayWorkSummary['active_timer']->start_time)) }}
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Recent Time Logs --}}
        <div class="space-y-2">
            <h3 class="text-xs font-semibold text-gray-700 uppercase mb-3">Recent Sessions</h3>
            @forelse($todayWorkSummary['time_logs']->take(5) as $log)
            <div class="flex items-center justify-between text-sm p-2 hover:bg-gray-50 rounded-lg transition-colors">
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 bg-purple-500 rounded-full"></div>
                    <span class="text-gray-700 text-xs">{{ Str::limit($log->card->card_title, 30) }}</span>
                </div>
                <span class="text-xs font-medium text-gray-600">
                    {{ number_format($log->duration_minutes / 60, 1) }}h
                </span>
            </div>
            @empty
            <p class="text-sm text-gray-500 text-center py-4">No work sessions yet today</p>
            @endforelse
        </div>
    </div>
</div>
