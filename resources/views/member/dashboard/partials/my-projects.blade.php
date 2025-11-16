<div class="backdrop-blur-xl bg-white/70 rounded-2xl border border-white/20 shadow-lg">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
            </svg>
            My Projects
        </h2>
        <p class="text-sm text-gray-600">Projects you're working on</p>
    </div>

    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($myProjects as $membership)
            <div class="p-5 bg-gradient-to-br from-white to-gray-50 rounded-xl border border-gray-200 hover:shadow-md transition-all">
                {{-- Project Header --}}
                <div class="flex items-start justify-between mb-3">
                    <h3 class="text-base font-semibold text-gray-900 flex-1">
                        {{ $membership->project->project_name }}
                    </h3>
                    <span class="px-2 py-1 rounded-md text-xs font-semibold
                        @if($membership->role === 'developer') bg-blue-100 text-blue-700
                        @else bg-purple-100 text-purple-700 @endif">
                        {{ ucfirst($membership->role) }}
                    </span>
                </div>

                {{-- Project Creator --}}
                <p class="text-xs text-gray-600 mb-3 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    by {{ $membership->project->creator->full_name }}
                </p>

                {{-- Stats --}}
                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div class="text-center p-2 bg-white rounded-lg border border-gray-200">
                        <div class="text-lg font-bold text-gray-900">
                            {{ $membership->my_tasks_count }}
                        </div>
                        <div class="text-xs text-gray-600">My Tasks</div>
                    </div>
                    <div class="text-center p-2 bg-white rounded-lg border border-gray-200">
                        <div class="text-lg font-bold text-blue-600">
                            {{ $membership->active_tasks_count }}
                        </div>
                        <div class="text-xs text-gray-600">Active</div>
                    </div>
                </div>

                {{-- Boards Count --}}
                <div class="flex items-center justify-between text-xs text-gray-600">
                    <span>{{ $membership->project->boards->count() }} boards</span>
                    <a href="{{ route('projects.show', $membership->project) }}" 
                       class="text-blue-600 hover:text-blue-700 font-medium">
                        View →
                    </a>
                </div>
            </div>
            @empty
            <div class="col-span-full text-center py-12">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">No Projects Yet</h3>
                <p class="text-sm text-gray-600">You haven't been assigned to any projects</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
