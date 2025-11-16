@extends('layouts.app')

@section('title', 'Review History')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 py-6 sm:py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Header --}}
        <div class="mb-6 sm:mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">
                        <span class="inline-block mr-2">📋</span>
                        Review History
                    </h1>
                    <p class="mt-1 text-sm text-gray-600">
                        Review history dari semua card yang di-assign kepada Anda
                    </p>
                </div>
                
                {{-- Statistics Cards --}}
                <div class="flex gap-3 sm:gap-4">
                    <div class="backdrop-blur-xl bg-white/60 rounded-xl px-4 py-2 border border-white/20 shadow-lg">
                        <div class="text-xs text-gray-600">Total</div>
                        <div class="text-xl font-bold text-gray-900">{{ $stats['total'] }}</div>
                    </div>
                    <div class="backdrop-blur-xl bg-green-50/60 rounded-xl px-4 py-2 border border-green-200/20 shadow-lg">
                        <div class="text-xs text-green-700">Approved</div>
                        <div class="text-xl font-bold text-green-600">{{ $stats['approved'] }}</div>
                    </div>
                    <div class="backdrop-blur-xl bg-red-50/60 rounded-xl px-4 py-2 border border-red-200/20 shadow-lg">
                        <div class="text-xs text-red-700">Rejected</div>
                        <div class="text-xl font-bold text-red-600">{{ $stats['rejected'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Filters & Search --}}
        <div class="mb-6 backdrop-blur-xl bg-white/70 rounded-2xl p-4 sm:p-6 border border-white/20 shadow-lg"
             x-data="{ 
                 statusFilter: '{{ $statusFilter ?? 'all' }}',
                 search: '{{ $search ?? '' }}'
             }">
            <form method="GET" action="{{ route('card-reviews.my-reviews') }}" class="space-y-4">
                
                {{-- Filter Buttons --}}
                <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4">
                    <label class="text-sm font-medium text-gray-700 whitespace-nowrap">
                        Filter Status:
                    </label>
                    <div class="flex flex-wrap gap-2">
                        <button type="button"
                                @click="statusFilter = 'all'; $el.closest('form').submit()"
                                :class="statusFilter === 'all' 
                                    ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/30' 
                                    : 'bg-white/80 text-gray-700 hover:bg-gray-50'"
                                class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 border border-gray-200">
                            Semua
                        </button>
                        <button type="button"
                                @click="statusFilter = 'approved'; $el.closest('form').submit()"
                                :class="statusFilter === 'approved' 
                                    ? 'bg-green-600 text-white shadow-lg shadow-green-500/30' 
                                    : 'bg-white/80 text-gray-700 hover:bg-gray-50'"
                                class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 border border-gray-200">
                            ✅ Approved
                        </button>
                        <button type="button"
                                @click="statusFilter = 'rejected'; $el.closest('form').submit()"
                                :class="statusFilter === 'rejected' 
                                    ? 'bg-red-600 text-white shadow-lg shadow-red-500/30' 
                                    : 'bg-white/80 text-gray-700 hover:bg-gray-50'"
                                class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 border border-gray-200">
                            ❌ Rejected
                        </button>
                    </div>
                </div>
                
                <input type="hidden" name="status" x-model="statusFilter">
                
                {{-- Search Box --}}
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text" 
                           name="search" 
                           x-model="search"
                           placeholder="Cari card, reviewer, atau notes..." 
                           class="block w-full pl-10 pr-24 py-3 border border-gray-200 rounded-xl bg-white/80 backdrop-blur-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition-all duration-200"
                           value="{{ $search ?? '' }}">
                    <button type="submit"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <span class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200">
                            Cari
                        </span>
                    </button>
                </div>
            </form>
        </div>
        
        {{-- Timeline Content --}}
        @if($reviews->count() > 0)
            <div class="space-y-6 sm:space-y-8">
                @foreach($reviewsByDate as $date => $dateReviews)
                    {{-- Date Header --}}
                    <div class="relative">
                        <div class="sticky top-20 z-20 backdrop-blur-xl bg-gradient-to-r from-blue-500 to-purple-600 rounded-xl px-4 py-3 shadow-lg mb-4">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <h2 class="text-lg font-semibold text-white">
                                    {{ \Carbon\Carbon::parse($date)->format('d M Y') }}
                                </h2>
                                <span class="ml-auto bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full text-sm text-white font-medium">
                                    {{ $dateReviews->count() }} review{{ $dateReviews->count() > 1 ? 's' : '' }}
                                </span>
                            </div>
                        </div>
                        
                        {{-- Reviews for this date --}}
                        <div class="space-y-4">
                            @foreach($dateReviews as $review)
                                <div class="backdrop-blur-xl bg-white/70 rounded-2xl border border-white/20 shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden">
                                    <div class="p-4 sm:p-6">
                                        
                                        {{-- Header: Card info + Status Badge --}}
                                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-4">
                                            <div class="flex-1 min-w-0">
                                                <h3 class="text-lg font-semibold text-gray-900 mb-1 line-clamp-2">
                                                    {{ $review->card->card_title }}
                                                </h3>
                                                <div class="flex flex-wrap items-center gap-2 text-xs text-gray-600">
                                                    <span class="flex items-center gap-1">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                                        </svg>
                                                        {{ $review->card->board->board_name }}
                                                    </span>
                                                    <span class="text-gray-400">•</span>
                                                    <span class="flex items-center gap-1">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                        </svg>
                                                        {{ $review->card->board->project->project_name }}
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            {{-- Status Badge --}}
                                            <div class="flex-shrink-0">
                                                @if($review->status === 'approved')
                                                    <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-green-500 to-emerald-600 text-white text-sm font-semibold shadow-lg shadow-green-500/30">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                        Approved
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-red-500 to-rose-600 text-white text-sm font-semibold shadow-lg shadow-red-500/30">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                        </svg>
                                                        Changes Requested
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        {{-- Reviewer Info --}}
                                        <div class="flex items-center gap-3 mb-4 pb-4 border-b border-gray-200">
                                            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-semibold shadow-lg">
                                                {{ strtoupper(substr($review->reviewer->full_name, 0, 1)) }}
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900">
                                                    {{ $review->reviewer->full_name }}
                                                </p>
                                                <p class="text-xs text-gray-500">
                                                    {{ $review->reviewed_at->diffForHumans() }} 
                                                    <span class="text-gray-400">•</span>
                                                    {{ $review->reviewed_at->format('H:i') }}
                                                </p>
                                            </div>
                                        </div>
                                        
                                        {{-- Notes (if any) --}}
                                        @if($review->notes)
                                            <div class="bg-gray-50/80 backdrop-blur-sm rounded-xl p-4 border border-gray-200">
                                                <div class="flex items-start gap-2">
                                                    <svg class="w-5 h-5 text-gray-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                                                    </svg>
                                                    <div class="flex-1">
                                                        <p class="text-xs font-semibold text-gray-700 mb-1">Notes:</p>
                                                        <p class="text-sm text-gray-700 leading-relaxed">
                                                            {{ $review->notes }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                        
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            
            {{-- Pagination --}}
            <div class="mt-8">
                {{ $reviews->links() }}
            </div>
            
        @else
            {{-- Empty State --}}
            <div class="backdrop-blur-xl bg-white/70 rounded-2xl border border-white/20 shadow-lg p-12 text-center">
                <div class="max-w-md mx-auto">
                    <div class="w-20 h-20 bg-gradient-to-br from-blue-100 to-purple-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">
                        Belum Ada Review
                    </h3>
                    <p class="text-gray-600 mb-6">
                        @if($search || ($statusFilter && $statusFilter !== 'all'))
                            Tidak ada review yang sesuai dengan filter atau pencarian Anda.
                        @else
                            Card yang di-assign kepada Anda belum ada yang di-review.
                        @endif
                    </p>
                    @if($search || ($statusFilter && $statusFilter !== 'all'))
                        <a href="{{ route('card-reviews.my-reviews') }}" 
                           class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-medium transition-colors duration-200 shadow-lg shadow-blue-500/30">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Reset Filter
                        </a>
                    @endif
                </div>
            </div>
        @endif
        
    </div>
</div>
@endsection
