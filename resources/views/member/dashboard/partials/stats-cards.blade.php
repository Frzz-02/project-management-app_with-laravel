<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    {{-- Assigned Tasks --}}
    <div class="backdrop-blur-xl bg-white/70 rounded-2xl border border-white/20 shadow-lg p-6">
        <div class="flex items-center justify-between mb-2">
            <div class="p-3 bg-blue-100 rounded-xl">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
        </div>
        <div class="text-3xl font-bold text-gray-900">
            {{ $overviewStats['assigned_tasks'] }}
        </div>
        <div class="text-sm text-gray-600 mt-1">Assigned Tasks</div>
    </div>

    {{-- In Progress --}}
    <div class="backdrop-blur-xl bg-white/70 rounded-2xl border border-white/20 shadow-lg p-6">
        <div class="flex items-center justify-between mb-2">
            <div class="p-3 bg-yellow-100 rounded-xl">
                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
        </div>
        <div class="text-3xl font-bold text-gray-900">
            {{ $overviewStats['in_progress_tasks'] }}
        </div>
        <div class="text-sm text-gray-600 mt-1">In Progress</div>
    </div>

    {{-- Completed This Week --}}
    <div class="backdrop-blur-xl bg-white/70 rounded-2xl border border-white/20 shadow-lg p-6">
        <div class="flex items-center justify-between mb-2">
            <div class="p-3 bg-green-100 rounded-xl">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <div class="text-3xl font-bold text-gray-900">
            {{ $overviewStats['completed_this_week'] }}
        </div>
        <div class="text-sm text-gray-600 mt-1">Completed This Week</div>
    </div>

    {{-- Hours Today --}}
    <div class="backdrop-blur-xl bg-white/70 rounded-2xl border border-white/20 shadow-lg p-6">
        <div class="flex items-center justify-between mb-2">
            <div class="p-3 bg-purple-100 rounded-xl">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <div class="text-3xl font-bold text-gray-900">
            {{ number_format($overviewStats['hours_today'], 1) }}h
        </div>
        <div class="text-sm text-gray-600 mt-1">Hours Today</div>
    </div>
</div>
