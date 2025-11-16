@extends('layouts.app')

@section('title', 'My Dashboard')

@section('content')
<div class="container mx-auto px-4 py-6">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">
                Selamat datang kembali, {{ auth()->user()->full_name }}! 👋
            </h1>
            <p class="text-gray-600 mt-1">Berikut adalah hal-hal yang perlu Anda fokuskan hari ini</p>
        </div>
        <div class="flex items-center gap-3 mt-4 md:mt-0">
            <button onclick="location.reload()" 
                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Perbarui
            </button>
        </div>
    </div>

    {{-- Overview Stats Cards --}}
    @include('member.dashboard.partials.stats-cards')

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
        {{-- Active Tasks (Takes 2 columns) --}}
        <div class="xl:col-span-2">
            @include('member.dashboard.partials.active-tasks')
        </div>

        {{-- Upcoming Deadlines (Takes 1 column) --}}
        <div class="xl:col-span-1">
            @include('member.dashboard.partials.upcoming-deadlines')
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Today's Work Summary --}}
        <div>
            @include('member.dashboard.partials.work-summary')
        </div>

        {{-- Recent Feedback --}}
        <div>
            @include('member.dashboard.partials.recent-feedback')
        </div>
    </div>

    {{-- My Projects --}}
    @include('member.dashboard.partials.my-projects')
</div>

{{-- Include JavaScript --}}
<script src="{{ asset('js/member/dashboard.js') }}"></script>
@endsection
