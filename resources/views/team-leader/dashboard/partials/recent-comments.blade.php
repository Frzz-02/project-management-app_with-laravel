
<div class="backdrop-blur-xl bg-white/70 rounded-2xl border border-white/20 shadow-lg">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">Recent Comments</h2>
    </div>
    
    <div class="p-6 space-y-4 max-h-96 overflow-y-auto">
        @foreach($recentComments as $comment)
        <div class="flex items-start space-x-3">
            <div class="h-8 w-8 bg-gradient-to-br from-purple-400 to-pink-400 rounded-full flex items-center justify-center text-white text-sm font-semibold flex-shrink-0">
                {{ strtoupper(substr($comment->user->full_name, 0, 1)) }}
            </div>
            <div class="flex-1">
                <p class="text-sm text-gray-900">
                    <span class="font-medium">{{ $comment->user->full_name }}</span> commented on 
                    <span class="font-medium">{{ $comment->context }}</span>
                </p>
                <p class="text-sm text-gray-600 mt-1 line-clamp-2">{{ $comment->comment_text }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ $comment->time_ago }}</p>
            </div>
        </div>
        @endforeach
    </div>
</div>
```
