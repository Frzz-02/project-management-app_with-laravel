
<div class="backdrop-blur-xl bg-white/70 rounded-2xl border border-white/20 shadow-lg">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">Team Activity Today</h2>
    </div>
    
    <div class="p-6 space-y-4">
        @forelse($teamActivity as $member)
        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
            <div class="flex items-center space-x-3">
                <div class="h-10 w-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-semibold">
                    {{ strtoupper(substr($member->full_name, 0, 1)) }}
                </div>
                <div>
                    <p class="font-medium text-gray-900">{{ $member->full_name }}</p>
                    <p class="text-sm text-gray-600">
                        @if($member->current_tasks->count() > 0)
                            Working on: {{ $member->current_tasks->first()['title'] }}
                        @else
                            No active tasks
                        @endif
                    </p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-lg font-semibold text-gray-900">{{ $member->hours_today }}h</p>
                <p class="text-xs text-gray-600">today</p>
            </div>
        </div>
        @empty
        <p class="text-center text-sm text-gray-600 py-8">No activity today</p>
        @endforelse
    </div>
</div>
```
