
<div class="backdrop-blur-xl bg-white/70 rounded-2xl border border-white/20 shadow-lg">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">My Projects</h2>
    </div>
    
    <div class="p-6 space-y-4">
        @foreach($myProjects as $project)
        <div class="p-4 bg-gradient-to-br from-white to-gray-50 rounded-xl border border-gray-200 hover:shadow-md transition-all">
            <h3 class="font-semibold text-gray-900">{{ $project->project_name }}</h3>
            <div class="mt-2">
                <div class="flex justify-between text-sm text-gray-600 mb-1">
                    <span>Progress</span>
                    <span>{{ $project->completion_rate }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-gradient-to-r from-blue-500 to-purple-500 h-2 rounded-full" 
                         style="width: {{ $project->completion_rate }}%"></div>
                </div>
            </div>
            <div class="mt-3 flex items-center justify-between text-xs text-gray-600">
                <span>{{ $project->team_count }} members</span>
                <span class="px-2 py-1 rounded-full 
                    @if($project->deadline_status === 'overdue') bg-red-100 text-red-800
                    @elseif($project->deadline_status === 'urgent') bg-orange-100 text-orange-800
                    @else bg-blue-100 text-blue-800 @endif">
                    @if($project->days_remaining !== null)
                        {{ abs($project->days_remaining) }} days {{ $project->days_remaining < 0 ? 'overdue' : 'left' }}
                    @else
                        No deadline
                    @endif
                </span>
            </div>
        </div>
        @endforeach
    </div>
</div>
```
