
@extends('layouts.app')

@section('title', 'Team Leader Dashboard')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 py-6 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Dashboard Ketua Tim</h1>
                <p class="mt-1 text-sm text-gray-600">Kelola proyek dan tim Anda dengan efektif</p>
            </div>
            <div class="mt-4 sm:mt-0 flex gap-3">
                {{-- <a href="{{ route('projects.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                    </svg>
                    My Projects
                </a> --}}
                {{-- <a href="{{ route('cards.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 rounded-lg transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    All Tasks
                </a> --}}
            </div>
        </div>

        {{-- Stats Cards --}}
        @include('team-leader.dashboard.partials.stats-cards')

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
            {{-- Tasks Requiring Review (2/3 width) --}}
            <div class="xl:col-span-2">
                @include('team-leader.dashboard.partials.tasks-review')
            </div>

            {{-- My Projects (1/3 width) --}}
            <div class="xl:col-span-1">
                @include('team-leader.dashboard.partials.my-projects')
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-6">
            {{-- Team Activity --}}
            <div>
                @include('team-leader.dashboard.partials.team-activity')
            </div>

            {{-- Recent Comments --}}
            <div>
                @include('team-leader.dashboard.partials.recent-comments')
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            {{-- Task Status Chart --}}
            <div>
                @include('team-leader.dashboard.partials.task-status-chart')
            </div>

            {{-- Team Workload Chart --}}
            <div>
                @include('team-leader.dashboard.partials.team-workload-chart')
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="{{ asset('js/team-leader/dashboard.js') }}"></script>
@endpush

