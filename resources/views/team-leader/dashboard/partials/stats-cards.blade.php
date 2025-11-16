
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
    {{-- Total Projects --}}
    <div class="backdrop-blur-xl bg-white/70 rounded-2xl p-6 border border-white/20 shadow-lg hover:shadow-xl transition-all">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Total Projects</p>
                <p class="text-3xl font-bold text-gray-900">{{ $overviewStats['total_projects'] }}</p>
            </div>
            <div class="h-12 w-12 bg-blue-100 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- Total Tasks --}}
    {{-- Similar structure with different icons/colors --}}
    
    {{-- Continue for: tasks_need_review, overdue_tasks, active_members --}}
</div>