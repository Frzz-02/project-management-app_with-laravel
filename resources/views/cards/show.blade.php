@extends('layouts.app')

@section('title', $card->card_title)

@section('content')
<div class="container mx-auto px-6 py-8">
    
    <!-- Page Header -->
    <div class="flex items-center justify-between mb-8">
        <div class="flex items-center space-x-4">
            <a href="{{ route('cards.index') }}" 
               class="text-gray-600 hover:text-gray-800">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ $card->card_title }}</h1>
                <p class="text-gray-600 mt-1">
                    {{ $card->board->project->project_name }} → {{ $card->board->board_name }}
                </p>
            </div>
        </div>
        
        <div class="flex items-center space-x-3">
            @can('update', $card)
                <a href="{{ route('cards.edit', $card) }}" 
                class="bg-green-600 hover:bg-green-700 text-white font-medium px-4 py-2 rounded-lg transition-colors duration-200">
                    Edit Card
                </a>
                
                <form action="{{ route('cards.destroy', $card) }}" method="POST" class="inline">
                    @csrf 
                    @method('DELETE') 
                    <button type="submit" 
                            onclick="return confirm('Yakin ingin menghapus card ini?')"
                            class="bg-red-600 hover:bg-red-700 text-white font-medium px-4 py-2 rounded-lg transition-colors duration-200">
                        Delete Card
                    </button>
                </form>
            @endcan
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Card Details -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                <div class="p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Card Details</h2>
                    
                    <!-- Status and Priority Badges -->
                    <div class="flex items-center space-x-3 mb-6">
                        <span class="px-3 py-1 text-sm font-medium rounded-full
                            @if($card->status == 'done') bg-green-100 text-green-800
                            @elseif($card->status == 'in progress') bg-yellow-100 text-yellow-800
                            @elseif($card->status == 'review') bg-blue-100 text-blue-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ ucfirst($card->status) }}
                        </span>
                        
                        <span class="px-3 py-1 text-sm font-medium rounded-full
                            @if($card->priority == 'high') bg-red-100 text-red-800
                            @elseif($card->priority == 'medium') bg-yellow-100 text-yellow-800
                            @else bg-green-100 text-green-800 @endif">
                            {{ ucfirst($card->priority) }} Priority
                        </span>
                    </div>

                    @if($card->description)
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Description</h3>
                            <div class="prose prose-sm max-w-none text-gray-700">
                                {!! nl2br(e($card->description)) !!}
                            </div>
                        </div>
                    @endif

                    <!-- Card Metadata -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <!-- Due Date -->
                        @if($card->due_date)
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 mb-1">Due Date</h4>
                                <div class="flex items-center space-x-2">
                                    <svg class="w-4 h-4 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="text-gray-700 {{ $card->due_date->isPast() && $card->status != 'done' ? 'text-red-600 font-medium' : '' }}">
                                        {{ $card->due_date->format('d F Y') }}
                                        @if($card->due_date->isPast() && $card->status != 'done')
                                            (Overdue)
                                        @endif
                                    </span>
                                </div>
                            </div>
                        @endif

                        <!-- Estimated Hours -->
                        @if($card->estimated_hours)
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 mb-1">Estimated Hours</h4>
                                <div class="flex items-center space-x-2">
                                    <svg class="w-4 h-4 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="text-gray-700">{{ $card->estimated_hours }} hours</span>
                                </div>
                            </div>
                        @endif

                        <!-- Created By -->
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 mb-1">Created By</h4>
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-gray-700">{{ $card->creator->full_name }}</span>
                            </div>
                        </div>

                        <!-- Created Date -->
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 mb-1">Created Date</h4>
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-gray-700">{{ $card->created_at->format('d F Y, H:i') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Time Tracking Section -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                <div class="p-6">
                    
                    <!-- Flash Messages untuk Time Tracking -->
                    @if(session('success'))
                        <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg" role="alert">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span>{{ session('success') }}</span>
                            </div>
                        </div>
                    @endif
                    


                    @if(session('error'))
                        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg" role="alert">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                                <span>{{ session('error') }}</span>
                            </div>
                        </div>
                    @endif
                    


                    @if($errors->any())
                        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg" role="alert">
                            <div class="font-medium mb-2">Terjadi kesalahan:</div>
                            <ul class="list-disc list-inside text-sm">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    


                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-semibold text-gray-900 flex items-center space-x-2">
                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Time Tracking</span>
                        </h2>
                        
                        @php
                            // Legacy check untuk backward compatibility
                            // Gunakan data dari controller jika ada, fallback ke query di view
                            if (!isset($ongoingCardTracking)) {
                                $ongoingCardTracking = \App\Models\TimeLog::where('user_id', Auth::id())
                                    ->whereNull('end_time')
                                    ->whereNull('subtask_id')
                                    ->with(['card'])
                                    ->first();
                            }
                            
                            if (!isset($ongoingSubtaskTrackings)) {
                                $ongoingSubtaskTrackings = \App\Models\TimeLog::where('user_id', Auth::id())
                                    ->whereNull('end_time')
                                    ->whereNotNull('subtask_id')
                                    ->with(['card', 'subtask'])
                                    ->get();
                            }
                            
                            if (!isset($activeTimersCount)) {
                                $activeTimersCount = ($ongoingCardTracking ? 1 : 0) + $ongoingSubtaskTrackings->count();
                            }

                            // Untuk backward compatibility dengan code lama
                            $ongoingTimer = $ongoingCardTracking ?? $ongoingSubtaskTrackings->first();



                            // Ambil semua time logs untuk card ini
                            $timeLogs = \App\Models\TimeLog::where('card_id', $card->id)
                                ->with(['user', 'subtask'])
                                // ->orderBy('created_at', 'desc')
                                ->get();



                            // Hitung total waktu yang sudah dicatat (hanya yang sudah selesai)
                            $totalMinutes = $timeLogs->whereNotNull('end_time')->sum('duration_minutes');
                            $totalHours = intval($totalMinutes / 60);
                            $totalMins = $totalMinutes % 60;
                        @endphp
                        


                        <!-- Display Total Time -->
                        <div class="flex items-center space-x-4">
                            <!-- Concurrent Tracking Indicator -->
                            @if($activeTimersCount > 0)
                                <div class="flex items-center space-x-2 px-3 py-1 bg-green-100 border border-green-200 rounded-lg">
                                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                                    <span class="text-xs font-medium text-green-700">
                                        {{ $activeTimersCount }} Active Timer{{ $activeTimersCount > 1 ? 's' : '' }}
                                    </span>
                                </div>
                            @endif

                            <div class="text-right">
                                <p class="text-xs text-gray-500 uppercase font-medium">Total Logged</p>
                                <p class="text-lg font-bold text-indigo-600">
                                    {{ $totalHours }}h {{ $totalMins }}m
                                </p>
                            </div>
                        </div>
                    </div>



                    <!-- CONCURRENT TRACKING DISPLAY -->
                    @if($activeTimersCount > 0)
                        <div class="space-y-3 mb-6">
                            <!-- Card Tracking -->
                            @if($ongoingCardTracking && $ongoingCardTracking->card_id === $card->id)
                                <div class="bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-green-200 rounded-lg p-4" 
                                     x-data="{ 
                                         startTime: new Date('{{ $ongoingCardTracking->start_time->toIso8601String() }}'),
                                         elapsed: '00:00:00',
                                         updateTimer() {
                                             const now = new Date();
                                             const diff = Math.floor((now - this.startTime) / 1000);
                                             const hours = Math.floor(diff / 3600);
                                             const minutes = Math.floor((diff % 3600) / 60);
                                             const seconds = diff % 60;
                                             this.elapsed = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
                                         }
                                     }"
                                     x-init="updateTimer(); setInterval(() => updateTimer(), 1000)">
                                    
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-4">
                                            <!-- Animated Pulse Icon -->
                                            <div class="relative">
                                                <div class="w-3 h-3 bg-green-500 rounded-full animate-ping absolute"></div>
                                                <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                                            </div>

                                            <div>
                                                <p class="text-sm font-semibold text-gray-900">📋 Card Tracking</p>
                                                <p class="text-xs text-gray-600">
                                                    Entire card - {{ $card->card_title }}
                                                </p>
                                                <p class="text-xs text-gray-500 mt-1">
                                                    Started: {{ $ongoingCardTracking->start_time->format('H:i') }}
                                                </p>
                                            </div>

                                            <!-- Live Timer Display -->
                                            <div class="ml-4">
                                                <p class="text-2xl font-mono font-bold text-green-600" x-text="elapsed">00:00:00</p>
                                            </div>
                                        </div>

                                        <!-- Stop Button -->
                                        <form action="{{ route('time-logs.stop', $ongoingCardTracking) }}" method="POST" x-data="{ showDescription: false }">
                                            @csrf
                                            
                                            <div class="flex items-center space-x-2">
                                                <!-- Description Input (optional) -->
                                                <div x-show="showDescription" 
                                                     x-transition:enter="transition ease-out duration-200"
                                                     x-transition:enter-start="opacity-0 scale-95"
                                                     x-transition:enter-end="opacity-100 scale-100"
                                                     class="mr-2">
                                                    <input type="text" 
                                                           name="description" 
                                                           placeholder="What did you work on?"
                                                           value="{{ $ongoingCardTracking->description }}"
                                                           class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                                </div>

                                                <button type="button" 
                                                        @click="showDescription = !showDescription"
                                                        class="px-3 py-2 text-sm text-gray-600 hover:text-gray-800 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                    </svg>
                                                </button>

                                                <button type="submit"
                                                        onclick="return confirm('Stop card tracking? (This will also stop all related subtask tracking)')"
                                                        class="flex items-center space-x-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8 7a1 1 0 00-1 1v4a1 1 0 001 1h4a1 1 0 001-1V8a1 1 0 00-1-1H8z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    <span class="font-medium">Stop Card</span>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @endif

                            <!-- Subtask Tracking(s) -->
                            @foreach($ongoingSubtaskTrackings as $subtaskTracking)
                                @if($subtaskTracking->card_id === $card->id)
                                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-lg p-4" 
                                         x-data="{ 
                                             startTime: new Date('{{ $subtaskTracking->start_time->toIso8601String() }}'),
                                             elapsed: '00:00:00',
                                             updateTimer() {
                                                 const now = new Date();
                                                 const diff = Math.floor((now - this.startTime) / 1000);
                                                 const hours = Math.floor(diff / 3600);
                                                 const minutes = Math.floor((diff % 3600) / 60);
                                                 const seconds = diff % 60;
                                                 this.elapsed = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
                                             }
                                         }"
                                         x-init="updateTimer(); setInterval(() => updateTimer(), 1000)">
                                        
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center space-x-4">
                                                <!-- Animated Pulse Icon (Blue) -->
                                                <div class="relative">
                                                    <div class="w-3 h-3 bg-blue-500 rounded-full animate-ping absolute"></div>
                                                    <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                                                </div>

                                                <div>
                                                    <p class="text-sm font-semibold text-gray-900">🎯 Subtask Tracking</p>
                                                    <p class="text-xs text-gray-600">
                                                        {{ $subtaskTracking->subtask->subtask_name }}
                                                    </p>
                                                    <p class="text-xs text-gray-500 mt-1">
                                                        Started: {{ $subtaskTracking->start_time->format('H:i') }}
                                                    </p>
                                                </div>

                                                <!-- Live Timer Display -->
                                                <div class="ml-4">
                                                    <p class="text-2xl font-mono font-bold text-blue-600" x-text="elapsed">00:00:00</p>
                                                </div>
                                            </div>

                                            <!-- Stop Button -->
                                            <form action="{{ route('time-logs.stop', $subtaskTracking) }}" method="POST" x-data="{ showDescription: false }">
                                                @csrf
                                                
                                                <div class="flex items-center space-x-2">
                                                    <!-- Description Input (optional) -->
                                                    <div x-show="showDescription" 
                                                         x-transition:enter="transition ease-out duration-200"
                                                         x-transition:enter-start="opacity-0 scale-95"
                                                         x-transition:enter-end="opacity-100 scale-100"
                                                         class="mr-2">
                                                        <input type="text" 
                                                               name="description" 
                                                               placeholder="What did you work on?"
                                                               value="{{ $subtaskTracking->description }}"
                                                               class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                                    </div>

                                                    <button type="button" 
                                                            @click="showDescription = !showDescription"
                                                            class="px-3 py-2 text-sm text-gray-600 hover:text-gray-800 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                        </svg>
                                                    </button>

                                                    <button type="submit"
                                                            onclick="return confirm('Stop subtask tracking?')"
                                                            class="flex items-center space-x-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8 7a1 1 0 00-1 1v4a1 1 0 001 1h4a1 1 0 001-1V8a1 1 0 00-1-1H8z" clip-rule="evenodd"></path>
                                                        </svg>
                                                        <span class="font-medium">Stop Subtask</span>
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif

                    <!-- Legacy single timer display (backward compatibility, hidden if concurrent tracking active) -->
                    @if($ongoingTimer && $activeTimersCount == 0)
                        <!-- Ongoing Timer Display -->
                        <div class="bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-green-200 rounded-lg p-4 mb-6" 
                             x-data="{ 
                                 startTime: new Date('{{ $ongoingTimer->start_time->toIso8601String() }}'),
                                 elapsed: '00:00:00',
                                 updateTimer() {
                                     const now = new Date();
                                     const diff = Math.floor((now - this.startTime) / 1000);
                                     const hours = Math.floor(diff / 3600);
                                     const minutes = Math.floor((diff % 3600) / 60);
                                     const seconds = diff % 60;
                                     this.elapsed = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
                                 }
                             }"
                             x-init="updateTimer(); setInterval(() => updateTimer(), 1000)">
                            
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <!-- Animated Pulse Icon -->
                                    <div class="relative">
                                        <div class="w-3 h-3 bg-green-500 rounded-full animate-ping absolute"></div>
                                        <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                                    </div>
                                    


                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Timer Running</p>
                                        <p class="text-xs text-gray-600">
                                            @if($ongoingTimer->card_id === $card->id)
                                                Tracking on this card
                                                @if($ongoingTimer->subtask)
                                                    → {{ $ongoingTimer->subtask->subtask_name }}
                                                @endif
                                            @else
                                                Tracking on another card: {{ $ongoingTimer->card->card_title }}
                                            @endif
                                        </p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            Started: {{ $ongoingTimer->start_time->format('H:i') }}
                                        </p>
                                    </div>



                                    <!-- Live Timer Display -->
                                    <div class="ml-4">
                                        <p class="text-2xl font-mono font-bold text-green-600" x-text="elapsed">00:00:00</p>
                                    </div>
                                </div>



                                <!-- Stop Button -->
                                <form action="{{ route('time-logs.stop', $ongoingTimer) }}" method="POST" x-data="{ showDescription: false }">
                                    @csrf
                                    
                                    <div class="flex items-center space-x-2">
                                        <!-- Description Input (optional) -->
                                        <div x-show="showDescription" 
                                             x-transition:enter="transition ease-out duration-200"
                                             x-transition:enter-start="opacity-0 scale-95"
                                             x-transition:enter-end="opacity-100 scale-100"
                                             class="mr-2">
                                            <input type="text" 
                                                   name="description" 
                                                   placeholder="What did you work on?"
                                                   value="{{ $ongoingTimer->description }}"
                                                   class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                        </div>



                                        <button type="button" 
                                                @click="showDescription = !showDescription"
                                                class="px-3 py-2 text-sm text-gray-600 hover:text-gray-800 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>



                                        <button type="submit"
                                                onclick="return confirm('Stop timer and save time log?')"
                                                class="flex items-center space-x-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8 7a1 1 0 00-1 1v4a1 1 0 001 1h4a1 1 0 001-1V8a1 1 0 00-1-1H8z" clip-rule="evenodd"></path>
                                            </svg>
                                            <span class="font-medium">Stop</span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @else
                        <!-- Start Timer Form -->
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6" 
                             x-data="{ 
                                 showForm: false, 
                                 forSubtask: {{ $ongoingSubtaskTrackings->where('card_id', $card->id)->count() > 0 ? 'true' : 'false' }}, 
                                 selectedSubtask: null 
                             }">
                            <div class="flex items-center justify-between">
                                <p class="text-sm text-gray-600">No timer running</p>
                                
                                <button @click="showForm = !showForm"
                                        class="flex items-center space-x-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="font-medium">Start Work</span>
                                </button>
                            </div>



                            <!-- Start Timer Form (Collapsible) -->
                            <div x-show="showForm" 
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                                 x-transition:enter-end="opacity-100 transform translate-y-0"
                                 class="mt-4 pt-4 border-t border-gray-200">
                                
                                <form action="{{ route('time-logs.start') }}" method="POST" class="space-y-4">
                                    @csrf
                                    
                                    <input type="hidden" name="card_id" value="{{ $card->id }}">
                                    
                                    <!-- Hanya kirim subtask_id jika benar-benar dipilih (bukan empty string) -->
                                    <input type="hidden" 
                                           name="subtask_id" 
                                           :value="selectedSubtask && selectedSubtask !== '' ? selectedSubtask : ''"
                                           x-show="forSubtask && selectedSubtask && selectedSubtask !== ''">



                                    <!-- Track For Option -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Track time for:
                                        </label>
                                        <div class="flex items-center space-x-4">
                                            <!-- Entire Card Option - Hide jika ada subtask tracking -->
                                            @php
                                                $hasSubtaskTracking = $ongoingSubtaskTrackings->where('card_id', $card->id)->count() > 0;
                                            @endphp
                                            
                                            @if(!$hasSubtaskTracking)
                                                <label class="flex items-center cursor-pointer">
                                                    <input type="radio" 
                                                           name="track_type" 
                                                           value="card"
                                                           @click="forSubtask = false; selectedSubtask = null"
                                                           checked
                                                           class="mr-2 text-green-600 focus:ring-green-500">
                                                    <span class="text-sm text-gray-700">Entire Card</span>
                                                </label>
                                            @else
                                                <div class="flex items-center space-x-2 text-sm text-gray-500">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    <span>Card tracking unavailable (subtask tracking active)</span>
                                                </div>
                                            @endif
                                            
                                            <!-- Specific Subtask Option - Hide jika card belum tracking -->
                                            @if($card->subtasks->count() > 0)
                                                @php
                                                    $hasCardTracking = $ongoingCardTracking && $ongoingCardTracking->card_id === $card->id;
                                                @endphp
                                                
                                                @if($hasCardTracking)
                                                    <label class="flex items-center cursor-pointer">
                                                        <input type="radio" 
                                                               name="track_type" 
                                                               value="subtask"
                                                               @click="forSubtask = true"
                                                               {{ $hasSubtaskTracking ? 'checked' : '' }}
                                                               class="mr-2 text-green-600 focus:ring-green-500">
                                                        <span class="text-sm text-gray-700">Specific Subtask</span>
                                                    </label>
                                                @else
                                                    <div class="flex items-center space-x-2 text-sm text-amber-600 bg-amber-50 px-3 py-2 rounded-lg">
                                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                                        </svg>
                                                        <span class="font-medium">Start card tracking first to enable subtask tracking</span>
                                                    </div>
                                                @endif
                                            @endif
                                        </div>
                                    </div>



                                    <!-- Subtask Selector -->
                                    @if($card->subtasks->count() > 0)
                                        <div x-show="forSubtask" 
                                             x-transition
                                             class="space-y-2">
                                            <label class="block text-sm font-medium text-gray-700">
                                                Select Subtask
                                            </label>
                                            <select x-model="selectedSubtask"
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                                <option value="">-- Choose subtask --</option>
                                                @foreach($card->subtasks as $subtask)
                                                    <option value="{{ $subtask->id }}">
                                                        {{ $subtask->subtask_name }} ({{ ucfirst($subtask->status) }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif



                                    <!-- Description -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Description (optional)
                                        </label>
                                        <input type="text" 
                                               name="description" 
                                               placeholder="e.g., Implementing login feature..."
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    </div>



                                    <!-- Form Actions -->
                                    <div class="flex items-center justify-end space-x-2">
                                        <button type="button" 
                                                @click="showForm = false"
                                                class="px-4 py-2 text-sm text-gray-700 hover:text-gray-900">
                                            Cancel
                                        </button>
                                        <button type="submit"
                                                class="px-4 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition-colors font-medium">
                                            Start Tracking
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif



                    <!-- Time Logs History -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">History</h3>
                            <span class="text-xs text-gray-500">{{ $timeLogs->count() }} entries</span>
                        </div>



                        @if($timeLogs->count() > 0)
                            <div class="space-y-2 max-h-96 overflow-y-auto">
                                @foreach($timeLogs as $log)
                                    <div class="border border-gray-200 rounded-lg p-3 hover:border-gray-300 transition-colors {{ $log->isOngoing() ? 'bg-green-50' : 'bg-white' }}">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <div class="flex items-center space-x-2 mb-1">
                                                    <!-- User Avatar/Name -->
                                                    <span class="text-sm font-medium text-gray-900">
                                                        {{ $log->user->name }}
                                                    </span>
                                                    
                                                    @if($log->user_id === Auth::id())
                                                        <span class="px-2 py-0.5 text-xs bg-indigo-100 text-indigo-700 rounded">You</span>
                                                    @endif



                                                    @if($log->subtask)
                                                        <span class="text-xs text-gray-500">→</span>
                                                        <span class="text-xs text-gray-600">{{ $log->subtask->subtask_name }}</span>
                                                    @endif
                                                </div>



                                                @if($log->description)
                                                    <p class="text-sm text-gray-600 mb-2">{{ $log->description }}</p>
                                                @endif



                                                <div class="flex items-center space-x-4 text-xs text-gray-500">
                                                    <span class="flex items-center space-x-1">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                        <span>{{ $log->start_time->format('d M Y, H:i') }}</span>
                                                    </span>
                                                    
                                                    @if($log->end_time)
                                                        <span>→</span>
                                                        <span>{{ $log->end_time->format('H:i') }}</span>
                                                    @endif
                                                </div>
                                            </div>



                                            <div class="ml-4 flex items-center space-x-3">
                                                @if($log->isOngoing())
                                                    <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-700 rounded">
                                                        Running
                                                    </span>
                                                @else
                                                    <div class="text-right">
                                                        <p class="text-lg font-bold text-indigo-600">
                                                            {{ $log->formatted_duration }}
                                                        </p>
                                                        <p class="text-xs text-gray-500">
                                                            {{ $log->duration_in_hours }}h
                                                        </p>
                                                    </div>
                                                @endif



                                                <!-- Actions (hanya untuk owner) -->
                                                @if($log->user_id === Auth::id() && !$log->isOngoing())
                                                    <div class="flex items-center space-x-1">
                                                        <!-- Edit Description -->
                                                        <button @click="$dispatch('edit-time-log-modal', {{ \Illuminate\Support\Js::from([
                                                                'id' => $log->id,
                                                                'description' => $log->description,
                                                            ]) }})"
                                                                class="p-1 text-gray-400 hover:text-indigo-600 transition-colors">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                            </svg>
                                                        </button>



                                                        <!-- Delete -->
                                                        <form action="{{ route('time-logs.destroy', $log) }}" method="POST" class="inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                    onclick="return confirm('Delete this time log?')"
                                                                    class="p-1 text-gray-400 hover:text-red-600 transition-colors">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                                </svg>
                                                            </button>
                                                        </form>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8 text-gray-500">
                                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p class="text-sm">No time logs yet. Start tracking to record your work!</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Subtasks Section (Jira-like) -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm" x-data="{ showAddSubtask: false }" id="subtasks-section">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center space-x-3">
                            <h2 class="text-xl font-semibold text-gray-900">
                                Subtasks 
                                <span class="text-sm font-normal text-gray-500">
                                    ({{ $card->subtasks->where('status', 'done')->count() }}/{{ $card->subtasks->count() }})
                                </span>
                            </h2>
                            
                            @if($card->subtasks->count() > 0)
                                @php
                                    $allSubtasksDone = $card->subtasks->every(fn($s) => $s->status === 'done');
                                @endphp
                                
                                @if($allSubtasksDone && !in_array($card->status, ['review', 'done']))
                                    <!-- Badge: Card akan auto-update ke Review -->
                                    <div class="flex items-center space-x-1 px-2 py-1 bg-blue-50 border border-blue-200 rounded-lg">
                                        <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span class="text-xs font-medium text-blue-700">Card ready for review</span>
                                    </div>
                                @elseif($allSubtasksDone && $card->status === 'review')
                                    <!-- Badge: All done, in review -->
                                    <div class="flex items-center space-x-1 px-2 py-1 bg-purple-50 border border-purple-200 rounded-lg">
                                        <svg class="w-4 h-4 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span class="text-xs font-medium text-purple-700">All subtasks completed</span>
                                    </div>
                                @endif
                            @endif
                        </div>
                        
                        @php
                            // Cek apakah user adalah team lead atau member dari project
                            $currentUser = Auth::user();
                            $projectMember = $card->board->project->members->where('user_id', $currentUser->id)->first();
                            $isTeamLead = $projectMember && $projectMember->role === 'team lead';
                            $isMember = $projectMember && $projectMember->role === 'developer' || $projectMember && $projectMember->role === 'designer';
                            $canManageSubtasks = $isTeamLead || $isMember;
                        @endphp

                        @if($canManageSubtasks)
                            <button @click="showAddSubtask = !showAddSubtask"
                                    class="text-sm text-indigo-600 hover:text-indigo-700 font-medium flex items-center space-x-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                <span>Add Subtask</span>
                            </button>
                        @endif
                    </div>

                    <!-- Add Subtask Form -->
                    @if($canManageSubtasks)
                        <div x-show="showAddSubtask" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 transform scale-100"
                             x-transition:leave-end="opacity-0 transform scale-95"
                             class="mb-6 bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <form action="{{ route('subtasks.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="card_id" value="{{ $card->id }}">
                                
                                <div class="space-y-3">
                                    <!-- Subtask Name -->
                                    <div>
                                        <label for="subtask_name" class="block text-sm font-medium text-gray-700 mb-1">
                                            Subtask Name <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" 
                                               name="subtask_name" 
                                               id="subtask_name" 
                                               required
                                               placeholder="Enter subtask name"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                    </div>

                                    <!-- Description -->
                                    <div>
                                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                                            Description
                                        </label>
                                        <textarea name="description" 
                                                  id="description" 
                                                  rows="2"
                                                  placeholder="Enter description (optional)"
                                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"></textarea>
                                    </div>

                                    <!-- Estimated Hours -->
                                    <div>
                                        <label for="estimated_hours" class="block text-sm font-medium text-gray-700 mb-1">
                                            Estimated Hours
                                        </label>
                                        <input type="number" 
                                               name="estimated_hours" 
                                               id="estimated_hours"
                                               step="0.5"
                                               min="0"
                                               max="999.99"
                                               placeholder="e.g. 2.5"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                    </div>

                                    <!-- Form Actions -->
                                    <div class="flex items-center justify-end space-x-2 pt-2">
                                        <button type="button" 
                                                @click="showAddSubtask = false"
                                                class="px-4 py-2 text-sm text-gray-700 hover:text-gray-900">
                                            Cancel
                                        </button>
                                        <button type="submit"
                                                class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition-colors">
                                            Add Subtask
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    @endif
                    
                    <!-- Subtasks List -->
                    @if($card->subtasks->count() > 0)
                        <div class="space-y-2">
                            @foreach($card->subtasks->sortBy('position') as $subtask)
                                <div class="border border-gray-200 rounded-lg hover:border-gray-300 transition-colors" 
                                     x-data="{ editing: false, showActions: false }">
                                    
                                    <!-- Subtask Display Mode - Clickable untuk detail -->
                                    <div x-show="!editing" 
                                         @click="$dispatch('subtask-detail-modal', {{ \Illuminate\Support\Js::from([
                                             'id' => $subtask->id,
                                             'card_id' => $card->id,
                                             'subtask_name' => $subtask->subtask_name,
                                             'description' => $subtask->description,
                                             'status' => $subtask->status,
                                             'estimated_hours' => $subtask->estimated_hours,
                                             'actual_hours' => $subtask->actual_hours,
                                             'created_at' => $subtask->created_at->format('Y-m-d H:i:s'),
                                             'position' => $subtask->position,
                                         ]) }})"
                                         class="p-4 cursor-pointer hover:bg-gray-50">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1 flex items-start space-x-3">
                                                <!-- Status Badge -->
                                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $subtask->status_badge_color }}">
                                                    {{ ucfirst($subtask->status) }}
                                                </span>
                                                
                                                <!-- Subtask Info -->
                                                <div class="flex-1 min-w-0">
                                                    <h4 class="text-sm font-medium text-gray-900 mb-1">
                                                        {{ $subtask->subtask_name }}
                                                    </h4>
                                                    
                                                    @if($subtask->description)
                                                        <p class="text-sm text-gray-600 mb-2 line-clamp-2">
                                                            {{ $subtask->description }}
                                                        </p>
                                                    @endif
                                                    
                                                    <!-- Subtask Meta -->
                                                    <div class="flex items-center space-x-4 text-xs text-gray-500">
                                                        @if($subtask->estimated_hours)
                                                            <span class="flex items-center space-x-1">
                                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                                </svg>
                                                                <span>Est: {{ $subtask->estimated_hours }}h</span>
                                                            </span>
                                                        @endif
                                                        @if($subtask->actual_hours > 0)
                                                            <span class="flex items-center space-x-1">
                                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                                </svg>
                                                                <span>Actual: {{ $subtask->actual_hours }}h</span>
                                                            </span>
                                                        @endif
                                                        <span>{{ $subtask->created_at->diffForHumans() }}</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Actions Menu -->
                                            @if($canManageSubtasks)
                                                <div class="relative ml-2" @click.stop @click.away="showActions = false">
                                                    <button @click="showActions = !showActions"
                                                            class="p-1 text-gray-400 hover:text-gray-600 rounded">
                                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                                                        </svg>
                                                    </button>
                                                    
                                                    <!-- Dropdown Menu -->
                                                    <div x-show="showActions"
                                                         x-transition:enter="transition ease-out duration-100"
                                                         x-transition:enter-start="opacity-0 scale-95"
                                                         x-transition:enter-end="opacity-100 scale-100"
                                                         x-transition:leave="transition ease-in duration-75"
                                                         x-transition:leave-start="opacity-100 scale-100"
                                                         x-transition:leave-end="opacity-0 scale-95"
                                                         class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10">
                                                        <div class="py-1">
                                                            <!-- Change Status Dropdown -->
                                                            <div class="px-4 py-2 text-xs font-medium text-gray-500 uppercase">
                                                                Change Status
                                                            </div>
                                                            
                                                            @foreach(['to do', 'in progress', 'done'] as $statusOption)
                                                                @if($subtask->status !== $statusOption)
                                                                    <form action="{{ route('subtasks.update-status', $subtask) }}" method="POST">
                                                                        @csrf
                                                                        @method('PATCH')
                                                                        <input type="hidden" name="status" value="{{ $statusOption }}">
                                                                        <button type="submit" 
                                                                                class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center space-x-2">
                                                                            <span class="w-2 h-2 rounded-full {{ 
                                                                                $statusOption === 'to do' ? 'bg-gray-400' : 
                                                                                ($statusOption === 'in progress' ? 'bg-blue-500' : 'bg-green-500') 
                                                                            }}"></span>
                                                                            <span>{{ ucfirst($statusOption) }}</span>
                                                                        </button>
                                                                    </form>
                                                                @endif
                                                            @endforeach
                                                            
                                                            <div class="border-t border-gray-100 my-1"></div>
                                                            
                                                            <button @click="editing = true; showActions = false"
                                                                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center space-x-2">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                                </svg>
                                                                <span>Edit</span>
                                                            </button>
                                                            
                                                            <form action="{{ route('subtasks.destroy', $subtask) }}" method="POST"
                                                                  onsubmit="return confirm('Are you sure you want to delete this subtask?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit"
                                                                        class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center space-x-2">
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                                    </svg>
                                                                    <span>Delete</span>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Subtask Edit Mode (Inline) - Tanpa Status Input -->
                                    <div x-show="editing" class="p-4 bg-gray-50">
                                        <form action="{{ route('subtasks.update', $subtask) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            
                                            <div class="space-y-3">
                                                <!-- Subtask Name -->
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                                        Subtask Name <span class="text-red-500">*</span>
                                                    </label>
                                                    <input type="text" 
                                                           name="subtask_name" 
                                                           value="{{ $subtask->subtask_name }}"
                                                           required
                                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                                </div>

                                                <!-- Description -->
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                                        Description
                                                    </label>
                                                    <textarea name="description" 
                                                              rows="2"
                                                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">{{ $subtask->description }}</textarea>
                                                </div>

                                                <!-- Estimated Hours -->
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                                        Estimated Hours
                                                    </label>
                                                    <input type="number" 
                                                           name="estimated_hours" 
                                                           value="{{ $subtask->estimated_hours }}"
                                                           step="0.5"
                                                           min="0"
                                                           max="999.99"
                                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                                </div>

                                                <!-- Actual Hours -->
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                                        Actual Hours
                                                    </label>
                                                    <input type="number" 
                                                           name="actual_hours" 
                                                           value="{{ $subtask->actual_hours }}"
                                                           step="0.5"
                                                           min="0"
                                                           max="999.99"
                                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                                </div>

                                                <!-- Form Actions -->
                                                <div class="flex items-center justify-end space-x-2 pt-2">
                                                    <button type="button" 
                                                            @click="editing = false"
                                                            class="px-4 py-2 text-sm text-gray-700 hover:text-gray-900">
                                                        Cancel
                                                    </button>
                                                    <button type="submit"
                                                            class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition-colors">
                                                        Save Changes
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <p class="text-sm">No subtasks yet.</p>
                            @if($canManageSubtasks)
                                <button @click="showAddSubtask = true" class="mt-2 text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                                    Add your first subtask
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Comments Section -->
            @php
                // Filter hanya comments untuk card (bukan subtask)
                $cardComments = $card->comments->filter(function($comment) {
                    return $comment->subtask_id === null;
                });
            @endphp
            
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm" 
                 x-data="{
                     showAddComment: false,
                     comments: {{ $cardComments->map(function($c) {
                         return [
                             'id' => $c->id,
                             'comment_text' => $c->comment_text,
                             'user_id' => $c->user_id,
                             'user_name' => $c->user->full_name,
                             'user_initial' => substr($c->user->full_name, 0, 1),
                             'created_at_human' => $c->created_at->diffForHumans(),
                             'is_own' => $c->user_id === Auth::id()
                         ];
                     })->values() }},
                     editingId: null,
                     editingText: '',
                     newCommentText: '',
                     isSubmitting: false,
                     currentUserId: {{ Auth::id() }},
                     
                     async addComment() {
                         if (!this.newCommentText.trim()) return;
                         
                         this.isSubmitting = true;
                         
                         try {
                             const response = await fetch('{{ route('comments.store') }}', {
                                 method: 'POST',
                                 headers: {
                                     'Content-Type': 'application/json',
                                     'Accept': 'application/json',
                                     'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').content
                                 },
                                 body: JSON.stringify({
                                     card_id: {{ $card->id }},
                                     comment_type: 'card',
                                     comment_text: this.newCommentText
                                 })
                             });
                             
                             const data = await response.json();
                             
                             if (data.success) {
                                 // Add new comment to top
                                 this.comments.unshift({
                                     id: data.comment.id,
                                     comment_text: data.comment.comment_text,
                                     user_id: data.comment.user_id,
                                     user_name: data.comment.user_name,
                                     user_initial: data.comment.user_name.charAt(0).toUpperCase(),
                                     created_at_human: 'Just now',
                                     is_own: true
                                 });
                                 
                                 // Reset form
                                 this.newCommentText = '';
                                 this.showAddComment = false;
                                 
                                 // Show success notification (optional)
                                 console.log(data.message);
                             }
                         } catch (error) {
                             console.error('Error adding comment:', error);
                             alert('Failed to add comment');
                         } finally {
                             this.isSubmitting = false;
                         }
                     },
                     
                     startEdit(comment) {
                         this.editingId = comment.id;
                         this.editingText = comment.comment_text;
                     },
                     
                     cancelEdit() {
                         this.editingId = null;
                         this.editingText = '';
                     },
                     
                     async updateComment(commentId) {
                         if (!this.editingText.trim()) return;
                         
                         try {
                             const response = await fetch(`/comments/${commentId}`, {
                                 method: 'PUT',
                                 headers: {
                                     'Content-Type': 'application/json',
                                     'Accept': 'application/json',
                                     'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').content
                                 },
                                 body: JSON.stringify({
                                     comment_text: this.editingText
                                 })
                             });
                             
                             const data = await response.json();
                             
                             if (data.success) {
                                 // Update comment in array
                                 const index = this.comments.findIndex(c => c.id === commentId);
                                 if (index !== -1) {
                                     this.comments[index].comment_text = data.comment.comment_text;
                                 }
                                 
                                 // Exit edit mode
                                 this.cancelEdit();
                             }
                         } catch (error) {
                             console.error('Error updating comment:', error);
                             alert('Failed to update comment');
                         }
                     },
                     
                     async deleteComment(commentId) {
                         if (!confirm('Are you sure you want to delete this comment?')) return;
                         
                         try {
                             const response = await fetch(`/comments/${commentId}`, {
                                 method: 'DELETE',
                                 headers: {
                                     'Accept': 'application/json',
                                     'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').content
                                 }
                             });
                             
                             const data = await response.json();
                             
                             if (data.success) {
                                 // Remove comment from array
                                 this.comments = this.comments.filter(c => c.id !== commentId);
                             }
                         } catch (error) {
                             console.error('Error deleting comment:', error);
                             alert('Failed to delete comment');
                         }
                     }
                 }">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-semibold text-gray-900">
                            Comments (<span x-text="comments.length">{{ $cardComments->count() }}</span>)
                        </h2>
                        
                        <!-- Add Comment Button -->
                        <button @click="showAddComment = !showAddComment"
                                class="text-sm text-indigo-600 hover:text-indigo-700 font-medium flex items-center space-x-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            <span>Add Comment</span>
                        </button>
                    </div>
                    
                    <!-- Add Comment Form -->
                    <div x-show="showAddComment" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 transform scale-95"
                         x-transition:enter-end="opacity-100 transform scale-100"
                         class="mb-6 bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <form @submit.prevent="addComment()">
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Your Comment <span class="text-red-500">*</span>
                                    </label>
                                    <textarea x-model="newCommentText"
                                              rows="3"
                                              required
                                              :disabled="isSubmitting"
                                              placeholder="Write your comment here..."
                                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm disabled:opacity-50"></textarea>
                                </div>
                                
                                <!-- Form Actions -->
                                <div class="flex items-center justify-end space-x-2">
                                    <button type="button" 
                                            @click="showAddComment = false; newCommentText = ''"
                                            :disabled="isSubmitting"
                                            class="px-4 py-2 text-sm text-gray-700 hover:text-gray-900 disabled:opacity-50">
                                        Cancel
                                    </button>
                                    <button type="submit"
                                            :disabled="isSubmitting || !newCommentText.trim()"
                                            class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                        <span x-show="!isSubmitting">Post Comment</span>
                                        <span x-show="isSubmitting">Posting...</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Comments List -->
                    <template x-if="comments.length > 0">
                        <div class="space-y-4">
                            <template x-for="comment in comments" :key="comment.id">
                                <div class="flex space-x-3 group hover:bg-gray-50 rounded-lg p-3 -mx-3 transition-colors">
                                    <!-- User Avatar -->
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-gradient-to-br from-indigo-400 to-purple-500 rounded-full flex items-center justify-center">
                                            <span class="text-sm font-medium text-white" x-text="comment.user_initial"></span>
                                        </div>
                                    </div>
                                    
                                    <div class="flex-1 min-w-0">
                                        <!-- Display Mode -->
                                        <div x-show="editingId !== comment.id">
                                            <div class="flex items-center justify-between mb-1">
                                                <div class="flex items-center space-x-2">
                                                    <h4 class="text-sm font-medium text-gray-900">
                                                        <span x-text="comment.is_own ? 'You' : comment.user_name"></span>
                                                    </h4>
                                                    <span class="text-xs text-gray-500" x-text="comment.created_at_human"></span>
                                                </div>
                                                
                                                <!-- Edit/Delete Buttons (Only for comment owner) -->
                                                <template x-if="comment.is_own">
                                                    <div class="flex items-center space-x-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                        <button @click="startEdit(comment)"
                                                                class="p-1 text-gray-400 hover:text-indigo-600 transition-colors">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                            </svg>
                                                        </button>
                                                        <button @click="deleteComment(comment.id)"
                                                                class="p-1 text-gray-400 hover:text-red-600 transition-colors">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </template>
                                            </div>
                                            <p class="text-sm text-gray-700 whitespace-pre-wrap" x-text="comment.comment_text"></p>
                                        </div>
                                        
                                        <!-- Edit Mode -->
                                        <div x-show="editingId === comment.id" 
                                             x-transition:enter="transition ease-out duration-200"
                                             x-transition:enter-start="opacity-0 scale-95"
                                             x-transition:enter-end="opacity-100 scale-100">
                                            <form @submit.prevent="updateComment(comment.id)">
                                                <div class="space-y-2">
                                                    <textarea x-model="editingText"
                                                              rows="3"
                                                              required
                                                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"></textarea>
                                                    
                                                    <div class="flex items-center justify-end space-x-2">
                                                        <button type="button" 
                                                                @click="cancelEdit()"
                                                                class="px-3 py-1 text-sm text-gray-700 hover:text-gray-900">
                                                            Cancel
                                                        </button>
                                                        <button type="submit"
                                                                class="px-3 py-1 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition-colors">
                                                            Save
                                                        </button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                    
                    <!-- Empty State -->
                    <template x-if="comments.length === 0">
                        <div class="text-center py-8 text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                            <p class="text-sm">No comments yet. Be the first to comment!</p>
                            <button @click="showAddComment = true" 
                                    class="mt-2 text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                                Add your first comment
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            
            <!-- Assigned Users -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Assigned Users</h3>
                    
                    @if($card->assignments->count() > 0)
                        <div class="space-y-3">
                            @foreach($card->assignments as $assignment)
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                        <span class="text-sm font-medium text-blue-800">
                                            {{ substr($assignment->user->full_name, 0, 1) }}
                                        </span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $assignment->user->full_name }}</p>
                                        <p class="text-xs text-gray-500">{{ $assignment->user->email }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500">No users assigned to this card.</p>
                    @endif
                </div>
            </div>

            <!-- Card Statistics -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistics</h3>
                    
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Subtasks</span>
                            <span class="text-sm font-medium text-gray-900">{{ $card->subtasks->count() }}</span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Comments</span>
                            <span class="text-sm font-medium text-gray-900">{{ $card->comments->count() }}</span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Assigned Users</span>
                            <span class="text-sm font-medium text-gray-900">{{ $card->assignments->count() }}</span>
                        </div>
                        
                        @if($card->estimated_hours)
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Estimated</span>
                                <span class="text-sm font-medium text-gray-900">{{ $card->estimated_hours }}h</span>
                            </div>
                        @endif
                        
                        @if($card->actual_hours)
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Actual</span>
                                <span class="text-sm font-medium text-gray-900">{{ $card->actual_hours }}h</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Subtask Detail Modal -->
<div x-data="{ 
    showModal: false, 
    subtask: null,
    statusColor(status) {
        const colors = {
            'to do': 'bg-gray-100 text-gray-800',
            'in progress': 'bg-blue-100 text-blue-800',
            'done': 'bg-green-100 text-green-800'
        };
        return colors[status] || 'bg-gray-100 text-gray-800';
    }
}"
     @subtask-detail-modal.window="subtask = $event.detail; showModal = true"
     x-show="showModal"
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;">
    
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
         @click="showModal = false"
         x-show="showModal"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
    </div>

    <!-- Modal Content -->
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="relative bg-white rounded-xl shadow-2xl max-w-2xl w-full mx-auto"
             @click.away="showModal = false"
             x-show="showModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
            
            <!-- Modal Header -->
            <div class="flex items-start justify-between p-6 border-b border-gray-200">
                <div class="flex-1">
                    <h3 class="text-xl font-semibold text-gray-900" x-text="subtask?.subtask_name"></h3>
                    <div class="mt-2 flex items-center space-x-3">
                        <span class="px-3 py-1 text-sm font-medium rounded-full"
                              :class="statusColor(subtask?.status)"
                              x-text="subtask?.status ? subtask.status.charAt(0).toUpperCase() + subtask.status.slice(1) : ''">
                        </span>
                        <span class="text-sm text-gray-500">
                            Position: <span x-text="subtask?.position"></span>
                        </span>
                    </div>
                </div>
                <button @click="showModal = false"
                        class="ml-4 text-gray-400 hover:text-gray-500 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 space-y-6">
                <!-- Description -->
                <div>
                    <h4 class="text-sm font-semibold text-gray-900 mb-2">Description</h4>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm text-gray-700 whitespace-pre-line" 
                           x-text="subtask?.description || 'No description provided'">
                        </p>
                    </div>
                </div>

                <!-- Time Tracking -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-indigo-50 rounded-lg p-4">
                        <div class="flex items-center space-x-2 mb-2">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <h4 class="text-sm font-semibold text-gray-900">Estimated Hours</h4>
                        </div>
                        <p class="text-2xl font-bold text-indigo-600">
                            <span x-text="subtask?.estimated_hours || '0'"></span>h
                        </p>
                    </div>

                    <div class="bg-green-50 rounded-lg p-4">
                        <div class="flex items-center space-x-2 mb-2">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <h4 class="text-sm font-semibold text-gray-900">Actual Hours</h4>
                        </div>
                        <p class="text-2xl font-bold text-green-600">
                            <span x-text="subtask?.actual_hours || '0'"></span>h
                        </p>
                    </div>
                </div>

                <!-- Metadata -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="text-sm font-semibold text-gray-900 mb-3">Information</h4>
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Created At</span>
                            <span class="text-sm font-medium text-gray-900" x-text="subtask?.created_at ? new Date(subtask.created_at).toLocaleString('id-ID', { 
                                year: 'numeric', 
                                month: 'long', 
                                day: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit'
                            }) : ''"></span>
                        </div>
                        <div class="flex items-center justify-between" 
                             x-show="subtask?.estimated_hours && subtask?.actual_hours">
                            <span class="text-sm text-gray-600">Progress</span>
                            <span class="text-sm font-medium"
                                  :class="subtask?.actual_hours > subtask?.estimated_hours ? 'text-red-600' : 'text-green-600'"
                                  x-text="subtask?.estimated_hours ? Math.round((subtask.actual_hours / subtask.estimated_hours) * 100) + '%' : '0%'">
                            </span>
                        </div>
                    </div>
                </div>



                <!-- Comments Section (Only for Developer/Designer) -->
                @if($canManageSubtasks)
                    <div x-data="subtaskCommentData()" 
                         @subtask-detail-modal.window="
                            subtaskId = $event.detail.id; 
                            cardId = $event.detail.card_id; 
                            console.log('🔄 Comment section updated:', { subtaskId, cardId }); 
                            loadComments();
                         ">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-sm font-semibold text-gray-900">
                                Comments (<span x-text="comments.length">0</span>)
                            </h4>
                            <button @click="showAddComment = !showAddComment"
                                    class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                                Add Comment
                            </button>
                        </div>

                        <!-- Add Comment Form -->
                        <div x-show="showAddComment" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             class="bg-gray-50 rounded-lg p-4 mb-4">
                            <form @submit.prevent="addComment()">
                                <textarea x-model="newComment"
                                          rows="3"
                                          placeholder="Add a comment..."
                                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none"
                                          required></textarea>
                                <div class="flex items-center justify-end space-x-2 mt-3">
                                    <button type="button" 
                                            @click="showAddComment = false; newComment = ''"
                                            class="px-3 py-1 text-sm text-gray-600 hover:text-gray-700">
                                        Cancel
                                    </button>
                                    <button type="submit"
                                            :disabled="commentLoading"
                                            class="px-4 py-1 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700 disabled:opacity-50">
                                        <span x-show="!commentLoading">Comment</span>
                                        <span x-show="commentLoading">Posting...</span>
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Comments List -->
                        <div class="space-y-4 max-h-64 overflow-y-auto">
                            <template x-for="comment in comments" :key="comment.id">
                                <div class="flex space-x-3 p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors group">
                                    <div class="w-8 h-8 bg-gradient-to-br from-blue-400 to-indigo-500 rounded-full flex items-center justify-center text-white text-sm font-medium flex-shrink-0">
                                        <span x-text="comment.user_name?.charAt(0) || 'U'"></span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center space-x-2">
                                                <span x-text="comment.user_name" class="text-sm font-medium text-gray-900"></span>
                                                <span x-text="formatDate(comment.created_at)" class="text-xs text-gray-500"></span>
                                            </div>
                                            
                                            <!-- Edit/Delete buttons (only for comment owner) -->
                                            <div x-show="comment.user_id === {{ Auth::id() }}" 
                                                 class="flex items-center space-x-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <button @click="editComment(comment)"
                                                        class="p-1 text-gray-400 hover:text-indigo-600 transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                    </svg>
                                                </button>
                                                <button @click="deleteComment(comment.id)"
                                                        class="p-1 text-gray-400 hover:text-red-600 transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <!-- Comment text or edit form -->
                                        <div x-show="editingCommentId !== comment.id">
                                            <p x-text="comment.comment_text" class="mt-1 text-sm text-gray-700 whitespace-pre-wrap"></p>
                                        </div>
                                        
                                        <!-- Edit form -->
                                        <div x-show="editingCommentId === comment.id" 
                                             x-transition:enter="transition ease-out duration-200"
                                             x-transition:enter-start="opacity-0 scale-95"
                                             x-transition:enter-end="opacity-100 scale-100"
                                             class="mt-2">
                                            <form @submit.prevent="updateComment(comment.id)">
                                                <textarea x-model="editingCommentText"
                                                          rows="3"
                                                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none text-sm"
                                                          required></textarea>
                                                <div class="flex items-center justify-end space-x-2 mt-2">
                                                    <button type="button" 
                                                            @click="cancelEdit()"
                                                            class="px-3 py-1 text-xs text-gray-600 hover:text-gray-700">
                                                        Cancel
                                                    </button>
                                                    <button type="submit"
                                                            :disabled="commentLoading"
                                                            class="px-3 py-1 bg-indigo-600 text-white text-xs rounded-md hover:bg-indigo-700 disabled:opacity-50">
                                                        <span x-show="!commentLoading">Save</span>
                                                        <span x-show="commentLoading">Saving...</span>
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            
                            <!-- Empty State -->
                            <div x-show="comments.length === 0" class="text-center py-8 text-gray-500">
                                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                                <p class="text-sm">No comments yet. Be the first to comment!</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Modal Footer -->
            @if($canManageSubtasks)
                <div class="flex items-center justify-between px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-xl">
                    @php
                        // Check if user has ongoing tracking for this subtask
                        $ongoingSubtaskTracking = \App\Models\TimeLog::where('user_id', Auth::id())
                            ->whereNull('end_time')
                            ->where('subtask_id', ':subtask_id:') // Will be replaced by Alpine
                            ->first();
                        
                        // Check if user has ongoing card tracking (prerequisite for subtask tracking)
                        $hasCardTracking = $ongoingCardTracking && $ongoingCardTracking->card_id === $card->id;
                    @endphp
                    
                    <!-- Left side: Start Tracking Button -->
                    <div x-data="{ 
                        canStartTracking() {
                            // Check if card is being tracked
                            const hasCardTracking = {{ $hasCardTracking ? 'true' : 'false' }};
                            // Check if this subtask is already being tracked
                            const isTracking = {{ json_encode($ongoingSubtaskTrackings->pluck('subtask_id')->toArray()) }}.includes(this.subtask?.id);
                            return hasCardTracking && !isTracking;
                        },
                        getTooltip() {
                            const hasCardTracking = {{ $hasCardTracking ? 'true' : 'false' }};
                            const isTracking = {{ json_encode($ongoingSubtaskTrackings->pluck('subtask_id')->toArray()) }}.includes(this.subtask?.id);
                            
                            if (isTracking) return 'Already tracking this subtask';
                            if (!hasCardTracking) return 'Start card tracking first';
                            return 'Start tracking this subtask';
                        }
                    }">
                        <form :action="`{{ route('time-logs.start') }}`" method="POST">
                            @csrf
                            <input type="hidden" name="card_id" :value="subtask?.card_id">
                            <input type="hidden" name="subtask_id" :value="subtask?.id">
                            
                            <button type="submit"
                                    x-bind:disabled="!canStartTracking()"
                                    x-bind:title="getTooltip()"
                                    class="px-4 py-2 rounded-lg transition-colors flex items-center space-x-2 group relative"
                                    :class="canStartTracking() ? 'bg-green-600 text-white hover:bg-green-700' : 'bg-gray-300 text-gray-500 cursor-not-allowed'">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"></path>
                                </svg>
                                <span>Start Tracking</span>
                                
                                <!-- Tooltip on hover -->
                                <div x-show="!canStartTracking()" 
                                     class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-900 text-white text-xs rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                                    <span x-text="getTooltip()"></span>
                                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                                </div>
                            </button>
                        </form>
                    </div>

                    <!-- Right side: Edit & Delete Buttons -->
                    <div class="flex items-center space-x-3">
                        <!-- Edit Button -->
                        <button @click="showModal = false; 
                                        setTimeout(() => {
                                            document.querySelector(`[x-data*='editing'][x-data*='${subtask?.id}']`)?.querySelector('[\\@click*=editing]')?.click();
                                        }, 100)"
                                class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors flex items-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            <span>Edit Subtask</span>
                        </button>

                        <!-- Delete Button -->
                        <form :action="`/subtasks/${subtask?.id}`" method="POST" class="inline"
                              onsubmit="return confirm('Are you sure you want to delete this subtask?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                <span>Delete</span>
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-xl">
                    <button @click="showModal = false"
                            class="w-full px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                        Close
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Edit Time Log Description Modal -->
<div x-data="{ 
    showModal: false, 
    timeLog: null
}"
     @edit-time-log-modal.window="timeLog = $event.detail; showModal = true"
     x-show="showModal"
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;">
    
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
         @click="showModal = false"
         x-show="showModal"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
    </div>



    <!-- Modal Content -->
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="relative bg-white rounded-xl shadow-2xl max-w-md w-full mx-auto"
             @click.away="showModal = false"
             x-show="showModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
            
            <!-- Modal Header -->
            <div class="flex items-start justify-between p-6 border-b border-gray-200">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Edit Time Log Description</h3>
                    <p class="text-sm text-gray-500 mt-1">Update what you worked on during this time</p>
                </div>
                <button @click="showModal = false"
                        class="text-gray-400 hover:text-gray-500 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>



            <!-- Modal Body -->
            <form :action="`/time-logs/${timeLog?.id}`" method="POST" class="p-6">
                @csrf
                @method('PUT')
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Description
                        </label>
                        <textarea name="description" 
                                  rows="4"
                                  :value="timeLog?.description"
                                  placeholder="Describe what you worked on..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                  required></textarea>
                        <p class="text-xs text-gray-500 mt-1">
                            Maximum 1000 characters
                        </p>
                    </div>
                </div>



                <!-- Modal Footer -->
                <div class="flex items-center justify-end space-x-3 mt-6 pt-4 border-t border-gray-200">
                    <button type="button" 
                            @click="showModal = false"
                            class="px-4 py-2 text-sm text-gray-700 hover:text-gray-900">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition-colors">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>


// ========================================
// SUBTASK COMMENT FUNCTIONALITY
// ========================================
// Alpine.js component untuk mengelola komentar pada subtask
// Hanya bisa diakses oleh project member dengan role Developer atau Designer
// Menggunakan AJAX untuk komunikasi real-time dengan CommentController



function subtaskCommentData() {
    return {
        subtaskId: null,
        cardId: null,
        comments: [],
        showAddComment: false,
        newComment: '',
        commentLoading: false,
        editingCommentId: null,
        editingCommentText: '',



        // Inisialisasi component dan load komentar dari server
        init() {
            console.log('✅ Subtask Comment Component initialized');
            // Data akan diset oleh x-watch di parent
        },



        // Memuat komentar dari server untuk subtask yang dipilih
        // Menggunakan AJAX GET untuk mendapatkan data realtime dari database
        async loadComments() {
            if (!this.subtaskId) {
                console.log('⚠️ No subtaskId yet, skipping loadComments');
                this.comments = [];
                return;
            }

            console.log('📥 Loading comments for subtask:', this.subtaskId, 'card:', this.cardId);

            try {
                const response = await fetch(`/comments/subtask/${this.subtaskId}`);
                if (!response.ok) throw new Error('Failed to load comments');
                
                const data = await response.json();
                this.comments = data.comments || [];
                
                console.log('✅ Subtask comments loaded:', this.comments);
            } catch (error) {
                console.error('❌ Error loading subtask comments:', error);
                this.comments = [];
            }
        },



        // Menambahkan komentar baru untuk subtask
        // Menggunakan AJAX POST untuk menyimpan ke database melalui CommentController
        async addComment() {
            if (!this.newComment.trim() || !this.subtaskId || !this.cardId) {
                console.error('❌ Missing required fields:', {
                    subtaskId: this.subtaskId,
                    cardId: this.cardId,
                    comment: this.newComment
                });
                return;
            }

            this.commentLoading = true;
            try {
                console.log('📤 Sending comment:', {
                    card_id: this.cardId,
                    subtask_id: this.subtaskId,
                    comment_text: this.newComment,
                    comment_type: 'subtask'
                });

                const response = await fetch('/comments', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        card_id: this.cardId,
                        subtask_id: this.subtaskId,
                        comment_text: this.newComment,
                        comment_type: 'subtask'
                    })
                });

                const data = await response.json();
                
                if (!response.ok) {
                    throw new Error(data.message || 'Failed to add comment');
                }

                // Tampilkan komentar baru dengan animasi
                this.comments.push(data.comment);
                this.newComment = '';
                this.showAddComment = false;

                console.log('✅ Subtask comment added successfully');

            } catch (error) {
                console.error('❌ Error adding subtask comment:', error);
                alert(error.message || 'Failed to add comment. Please try again.');
            } finally {
                this.commentLoading = false;
            }
        },



        // Mengedit komentar (tampilkan form edit)
        // Set state untuk menampilkan textarea edit dengan Alpine.js animation
        editComment(comment) {
            this.editingCommentId = comment.id;
            this.editingCommentText = comment.comment_text;
        },



        // Membatalkan edit komentar
        // Reset state editing untuk kembali ke mode tampilan biasa
        cancelEdit() {
            this.editingCommentId = null;
            this.editingCommentText = '';
        },



        // Menyimpan perubahan komentar ke server
        // Menggunakan AJAX PUT untuk update melalui CommentController
        async updateComment(commentId) {
            if (!this.editingCommentText.trim()) return;

            this.commentLoading = true;
            try {
                const response = await fetch(`/comments/${commentId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        comment_text: this.editingCommentText
                    })
                });

                const data = await response.json();
                
                if (!response.ok) {
                    throw new Error(data.message || 'Failed to update comment');
                }

                // Update komentar di array dengan data baru
                const index = this.comments.findIndex(c => c.id === commentId);
                if (index !== -1) {
                    this.comments[index] = data.comment;
                }

                // Reset editing state
                this.cancelEdit();

                console.log('✅ Subtask comment updated successfully');

            } catch (error) {
                console.error('❌ Error updating subtask comment:', error);
                alert(error.message || 'Failed to update comment. Please try again.');
            } finally {
                this.commentLoading = false;
            }
        },



        // Menghapus komentar dari database
        // Menggunakan AJAX DELETE dengan konfirmasi user terlebih dahulu
        async deleteComment(commentId) {
            if (!confirm('Are you sure you want to delete this comment?')) return;

            try {
                const response = await fetch(`/comments/${commentId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();
                
                if (!response.ok) {
                    throw new Error(data.message || 'Failed to delete comment');
                }

                // Hapus dari array dengan animasi
                this.comments = this.comments.filter(c => c.id !== commentId);

                console.log('✅ Subtask comment deleted successfully');

            } catch (error) {
                console.error('❌ Error deleting subtask comment:', error);
                alert(error.message || 'Failed to delete comment. Please try again.');
            }
        },



        // Format tanggal untuk tampilan yang user-friendly
        // Menggunakan format Indonesia dengan tanggal, bulan, tahun, jam, dan menit
        formatDate(dateString) {
            if (!dateString) return '';
            return new Date(dateString).toLocaleString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    }
}
</script>
@endpush