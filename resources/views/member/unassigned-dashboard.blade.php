@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    
    {{-- Welcome Banner --}}
    <div class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-2xl shadow-xl p-8 mb-8 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-4xl font-bold mb-2">Selamat Datang, {{ auth()->user()->full_name ?? auth()->user()->username }}! 👋</h1>
                <p class="text-lg opacity-90">Anda hampir siap untuk mulai bekerja pada proyek yang menarik!</p>
            </div>
            <div class="hidden md:block">
                <svg class="w-24 h-24 opacity-80" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 2a8 8 0 100 16 8 8 0 000-16zM8.5 8a1.5 1.5 0 113 0 1.5 1.5 0 01-3 0zm3 5.5a.5.5 0 01-.5.5h-2a.5.5 0 010-1h2a.5.5 0 01.5.5z"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- Status Alert --}}
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 mb-8 rounded-lg shadow-md">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="h-8 w-8 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-4">
                <h3 class="text-lg font-bold text-yellow-800 mb-1">Menunggu Penugasan Proyek</h3>
                <p class="text-yellow-700">Akun Anda sudah aktif! Admin akan meninjau profil Anda dan menugaskan Anda ke proyek segera. Pastikan profil Anda lengkap untuk mempercepat proses.</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        
        {{-- Profile Completion Card --}}
        <div class="lg:col-span-1">
            <div class="bg-white/60 backdrop-blur-xl rounded-2xl shadow-xl p-6 border border-white/20">
                <h2 class="text-xl font-bold text-gray-800 mb-6">Kelengkapan Profil</h2>
                
                {{-- Circular Progress --}}
                <div class="flex flex-col items-center mb-6">
                    <div class="relative">
                        <svg class="circular-progress" width="180" height="180">
                            <circle class="progress-bg" cx="90" cy="90" r="75"></circle>
                            <circle class="progress-bar" cx="90" cy="90" r="75" 
                                    style="--percentage: {{ $profileCompletion['percentage'] }}"></circle>
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-4xl font-bold text-blue-600">{{ $profileCompletion['percentage'] }}%</span>
                            <span class="text-sm text-gray-500 mt-1">Complete</span>
                        </div>
                    </div>
                </div>

                {{-- Checklist --}}
                <div class="space-y-3">
                    <div class="flex items-center {{ auth()->user()->full_name ? 'text-green-600' : 'text-gray-400' }}">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            @if(auth()->user()->full_name)
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            @else
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm0-2a6 6 0 100-12 6 6 0 000 12z" clip-rule="evenodd"/>
                            @endif
                        </svg>
                        <span class="text-sm">Full Name</span>
                    </div>
                    
                    <div class="flex items-center {{ auth()->user()->email && auth()->user()->email_verified_at ? 'text-green-600' : 'text-gray-400' }}">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            @if(auth()->user()->email && auth()->user()->email_verified_at)
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            @else
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm0-2a6 6 0 100-12 6 6 0 000 12z" clip-rule="evenodd"/>
                            @endif
                        </svg>
                        <span class="text-sm">Email Verified</span>
                    </div>
                    
                    <div class="flex items-center {{ auth()->user()->username ? 'text-green-600' : 'text-gray-400' }}">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            @if(auth()->user()->username)
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            @else
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm0-2a6 6 0 100-12 6 6 0 000 12z" clip-rule="evenodd"/>
                            @endif
                        </svg>
                        <span class="text-sm">Username Set</span>
                    </div>
                    
                    <div class="flex items-center {{ auth()->user()->profile_picture ? 'text-green-600' : 'text-gray-400' }}">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            @if(auth()->user()->profile_picture)
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            @else
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm0-2a6 6 0 100-12 6 6 0 000 12z" clip-rule="evenodd"/>
                            @endif
                        </svg>
                        <span class="text-sm">Profile Picture</span>
                    </div>

                    <div class="flex items-center text-green-600">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-sm">Account Created</span>
                    </div>
                </div>

                @if($profileCompletion['percentage'] < 100)
                    <a href="{{ route('profile.edit') }}" class="mt-6 block w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg text-center transition duration-200">
                        <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Lengkapi Profil Anda
                    </a>
                @else
                    <div class="mt-6 bg-green-50 border border-green-200 rounded-lg p-3 text-center">
                        <p class="text-sm text-green-700 font-semibold">
                            <svg class="w-5 h-5 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Profil 100% Lengkap!
                        </p>
                        <a href="{{ route('profile.edit') }}" class="text-xs text-blue-600 hover:text-blue-800 mt-1 inline-block">
                            Edit profil
                        </a>
                    </div>
                @endif
            </div>
        </div>

        {{-- Getting Started Guide --}}
        <div class="lg:col-span-2">
            <div class="bg-white/60 backdrop-blur-xl rounded-2xl shadow-xl p-6 border border-white/20">
                <h2 class="text-xl font-bold text-gray-800 mb-6">Panduan Memulai</h2>
                
                <div class="timeline">
                    @foreach($tutorialSteps as $index => $step)
                        <div class="timeline-item {{ $step['completed'] ? 'completed' : '' }}">
                            <div class="timeline-marker">
                                <i class="{{ $step['icon'] }}"></i>
                            </div>
                            <div class="timeline-content">
                                <h3 class="text-lg font-semibold text-gray-800">{{ $step['title'] }}</h3>
                                <p class="text-gray-600 mb-3">{{ $step['description'] }}</p>
                                @if($step['action_url'])
                                    <a href="{{ $step['action_url'] }}" class="inline-block bg-blue-500 hover:bg-blue-600 text-white font-medium py-1.5 px-4 rounded-lg text-sm transition duration-200">
                                        {{ $step['action_text'] }}
                                    </a>
                                @else
                                    <span class="inline-block bg-gray-300 text-gray-600 font-medium py-1.5 px-4 rounded-lg text-sm">
                                        {{ $step['action_text'] }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- System Statistics --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl shadow-xl p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-80 mb-1">Total Projects</p>
                    <p class="text-4xl font-bold">{{ $systemStats['total_projects'] }}</p>
                </div>
                <div class="bg-white/20 rounded-full p-4">
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl shadow-xl p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-80 mb-1">Active Members</p>
                    <p class="text-4xl font-bold">{{ $systemStats['active_members'] }}</p>
                </div>
                <div class="bg-white/20 rounded-full p-4">
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl shadow-xl p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-80 mb-1">Tasks Completed</p>
                    <p class="text-4xl font-bold">{{ $systemStats['total_tasks_completed'] }}</p>
                </div>
                <div class="bg-white/20 rounded-full p-4">
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- FAQ Section --}}
    <div class="bg-white/60 backdrop-blur-xl rounded-2xl shadow-xl p-6 border border-white/20 mb-8">
        <h2 class="text-xl font-bold text-gray-800 mb-6">Pertanyaan yang Sering Diajukan</h2>
        
        <div class="space-y-4">
            <div class="border-b border-gray-200 pb-4">
                <button class="faq-toggle flex items-center justify-between w-full text-left" type="button">
                    <span class="font-semibold text-gray-800">Berapa lama waktu yang dibutuhkan untuk mendapat penugasan proyek?</span>
                    <svg class="w-5 h-5 text-gray-500 transition-transform" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
                <div class="faq-content mt-3 text-gray-600" style="display: none;">
                    <p>Biasanya, dibutuhkan 1-3 hari kerja bagi admin untuk meninjau anggota baru dan menugaskan mereka ke proyek yang sesuai. Waktunya bisa bervariasi tergantung ketersediaan proyek dan kelengkapan profil Anda.</p>
                </div>
            </div>

            <div class="border-b border-gray-200 pb-4">
                <button class="faq-toggle flex items-center justify-between w-full text-left" type="button">
                    <span class="font-semibold text-gray-800">Apakah saya bisa memilih proyek yang ingin saya kerjakan?</span>
                    <svg class="w-5 h-5 text-gray-500 transition-transform" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
                <div class="faq-content mt-3 text-gray-600" style="display: none;">
                    <p>Penugasan proyek dilakukan oleh admin berdasarkan keahlian, ketersediaan, dan kebutuhan proyek. Namun, Anda bisa menyatakan minat Anda di profil, dan admin akan mencoba mencocokkan Anda dengan proyek yang sesuai.</p>
                </div>
            </div>

            <div class="border-b border-gray-200 pb-4">
                <button class="faq-toggle flex items-center justify-between w-full text-left" type="button">
                    <span class="font-semibold text-gray-800">Apa yang harus saya lakukan sambil menunggu penugasan?</span>
                    <svg class="w-5 h-5 text-gray-500 transition-transform" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
                <div class="faq-content mt-3 text-gray-600" style="display: none;">
                    <p>Pastikan profil Anda 100% lengkap dengan informasi yang akurat. Biasakan diri Anda dengan sistem dengan membaca dokumentasi. Anda juga dapat menghubungi admin jika ada pertanyaan atau kekhawatiran tertentu.</p>
                </div>
            </div>

            <div class="pb-4">
                <button class="faq-toggle flex items-center justify-between w-full text-left" type="button">
                    <span class="font-semibold text-gray-800">Apakah saya akan diberitahu saat ditugaskan ke proyek?</span>
                    <svg class="w-5 h-5 text-gray-500 transition-transform" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
                <div class="faq-content mt-3 text-gray-600" style="display: none;">
                    <p>Ya! Anda akan menerima notifikasi email dan dashboard akan otomatis diperbarui saat Anda ditugaskan ke proyek. Halaman memeriksa pembaruan setiap menit, jadi Anda akan diarahkan ke dashboard member secara otomatis.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Contact Support --}}
    <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-2xl shadow-xl p-6 text-white text-center">
        <h3 class="text-2xl font-bold mb-2">Butuh Bantuan?</h3>
        <p class="mb-4 opacity-90">Jika Anda memiliki pertanyaan atau membutuhkan bantuan, jangan ragu untuk menghubungi tim dukungan kami.</p>
        <a href="https://wa.me/6289688433133?text=Halo%20,%20saya%20ingin%20bertanya" class="inline-block bg-white text-purple-600 font-semibold py-2 px-6 rounded-lg hover:bg-gray-100 transition duration-200">
            Hubungi Dukungan
        </a>
    </div>

</div>
@endsection

@push('styles')
<style>
    /* Circular Progress */
    .circular-progress {
        transform: rotate(-90deg);
    }
    
    .progress-bg {
        fill: none;
        stroke: #e5e7eb;
        stroke-width: 10;
    }
    
    .progress-bar {
        fill: none;
        stroke: #3b82f6;
        stroke-width: 10;
        stroke-linecap: round;
        stroke-dasharray: 471; /* 2 * π * r = 2 * 3.14159 * 75 */
        stroke-dashoffset: calc(471 - (471 * var(--percentage) / 100));
        transition: stroke-dashoffset 1s ease-in-out;
    }

    /* Timeline */
    .timeline {
        position: relative;
        padding-left: 40px;
    }
    
    .timeline::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: linear-gradient(to bottom, #3b82f6, #8b5cf6);
    }
    
    .timeline-item {
        position: relative;
        margin-bottom: 30px;
        padding-bottom: 30px;
    }
    
    .timeline-item:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
    }
    
    .timeline-marker {
        position: absolute;
        left: -40px;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: white;
        border: 3px solid #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
        transition: all 0.3s ease;
    }
    
    .timeline-item.completed .timeline-marker {
        background: #10b981;
        border-color: #10b981;
        color: white;
    }
    
    .timeline-marker i {
        font-size: 14px;
        color: #9ca3af;
    }
    
    .timeline-item.completed .timeline-marker i {
        color: white;
    }
    
    .timeline-content {
        background: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border: 1px solid #e5e7eb;
    }
    
    .timeline-item.completed .timeline-content {
        border-color: #10b981;
        background: #f0fdf4;
    }

    /* FAQ Accordion */
    .faq-toggle svg {
        transition: transform 0.3s ease;
    }
    
    .faq-toggle.active svg {
        transform: rotate(180deg);
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/unassigned/dashboard.js') }}"></script>
@endpush
