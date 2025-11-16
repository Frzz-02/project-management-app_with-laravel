<div class="backdrop-blur-xl bg-white/70 rounded-2xl border border-white/20 shadow-lg h-full">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            Recent Feedback
        </h2>
        <p class="text-sm text-gray-600">Comments from team leaders</p>
    </div>

    <div class="p-6 space-y-4 max-h-96 overflow-y-auto">
        @forelse($recentFeedback as $comment)
        <div class="p-4 bg-gradient-to-br from-white to-blue-50/30 rounded-xl border border-gray-200">
            {{-- Comment Header --}}
            <div class="flex items-start gap-3 mb-2">
                <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white text-sm font-semibold flex-shrink-0">
                    {{ substr($comment->user->full_name, 0, 1) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-sm font-semibold text-gray-900">
                            {{ $comment->user->full_name }}
                        </span>
                        <span class="px-2 py-0.5 bg-blue-100 text-blue-700 text-xs font-medium rounded">
                            Team Lead
                        </span>
                    </div>
                    <p class="text-xs text-gray-600">
                        {{ $comment->time_ago }} • {{ $comment->card->card_title }}
                    </p>
                </div>
            </div>

            {{-- Comment Content --}}
            <p class="text-sm text-gray-700 leading-relaxed ml-11">
                {{ $comment->comment }}
            </p>

            {{-- Project Context --}}
            <div class="mt-2 ml-11 text-xs text-gray-500">
                📁 {{ $comment->card->board->project->project_name }}
            </div>
        </div>
        @empty
        <div class="text-center py-8">
            <div class="inline-flex items-center justify-center w-12 h-12 bg-blue-100 rounded-full mb-3">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
            </div>
            <p class="text-sm text-gray-600">No feedback yet</p>
        </div>
        @endforelse
    </div>
</div>
