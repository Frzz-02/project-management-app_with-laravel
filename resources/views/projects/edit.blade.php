@extends(Auth::user()->role === 'admin' ? 'layouts.admin' : 'layouts.app')

@section('title', 'Edit Project')

@section('content')
{{-- 
    HALAMAN EDIT PROJECT - MODERN & AESTHETIC
    =========================================
    Styling yang digunakan:
    - Konsisten dengan create page tapi dengan data pre-filled
    - Warning indicators untuk perubahan yang belum disimpan
    - Glassmorphism dengan accent color berbeda untuk edit mode
    - Enhanced validation display untuk existing data
    - Action buttons yang lebih prominent untuk save changes
--}}

<div class="min-h-screen bg-gradient-to-br from-amber-50 via-orange-50 to-red-50 py-8 px-4">
    {{-- 
        Background gradient dengan tema edit (orange/amber):
        - from-amber-50: mulai dari amber super terang
        - via-orange-50: lewat orange super terang di tengah
        - to-red-50: berakhir di red terang
        - Memberikan visual cue bahwa ini mode edit
    --}}
    
    <!-- Floating Background Decorative Elements -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-20 left-10 w-32 h-32 bg-orange-200/20 rounded-full blur-xl animate-pulse"></div>
        <div class="absolute top-40 right-20 w-48 h-48 bg-amber-200/15 rounded-full blur-2xl animate-pulse" style="animation-delay: 1s;"></div>
        <div class="absolute bottom-20 left-20 w-40 h-40 bg-red-200/20 rounded-full blur-xl animate-pulse" style="animation-delay: 2s;"></div>
        <div class="absolute bottom-40 right-10 w-24 h-24 bg-yellow-200/25 rounded-full blur-lg animate-pulse" style="animation-delay: 0.5s;"></div>
        {{-- Elemen dekoratif dengan tema orange/amber untuk edit mode --}}
    </div>
    
    <!-- Main Container -->
    <div class="relative z-10 max-w-4xl mx-auto">
        
        <!-- Header Section dengan Edit Indicator -->
        <div class="mb-8 text-center" 
             x-data 
             x-init="$el.style.opacity = '0'; $el.style.transform = 'translateY(-20px)'; 
                    setTimeout(() => { 
                        $el.style.transition = 'all 0.6s ease-out'; 
                        $el.style.opacity = '1'; 
                        $el.style.transform = 'translateY(0)'; 
                    }, 100)">
            
            <!-- Edit Mode Badge -->
            <div class="inline-flex items-center bg-amber-100/80 backdrop-blur-sm text-amber-800 px-4 py-2 rounded-full text-sm font-medium mb-4 border border-amber-200/60">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit Mode
            </div>
            
            <h1 class="text-4xl lg:text-5xl font-bold bg-gradient-to-r from-gray-900 via-orange-800 to-red-900 bg-clip-text text-transparent mb-4">
                {{-- Gradient dengan tema edit (orange/red) --}}
                Edit Project
            </h1>
            
            <p class="text-lg text-gray-600 mb-6 max-w-2xl mx-auto">
                Update your project details and keep your team aligned with the latest information.
            </p>
            
            <!-- Breadcrumb dengan Current Project Name -->
            <nav class="flex justify-center">
                <ol class="flex items-center space-x-2 text-sm text-gray-500 bg-white/30 backdrop-blur-sm rounded-full px-4 py-2 border border-white/40">
                    <li><a href="{{ route('dashboard') }}" class="hover:text-orange-600 transition-colors duration-200">Dashboard</a></li>
                    <li><span class="mx-2 text-gray-400">→</span></li>
                    <li><a href="{{ route('projects.index') }}" class="hover:text-orange-600 transition-colors duration-200">Projects</a></li>
                    <li><span class="mx-2 text-gray-400">→</span></li>
                    <li><a href="{{ route('projects.show', $project ?? 1) }}" class="hover:text-orange-600 transition-colors duration-200">{{ isset($project) ? $project->project_name ?? 'Project' : 'Project' }}</a></li>
                    <li><span class="mx-2 text-gray-400">→</span></li>
                    <li class="text-orange-600 font-medium">Edit</li>
                </ol>
            </nav>
        </div>
        
        <!-- Main Glassmorphism Form Card dengan Edit Styling -->
        <div class="bg-white/40 backdrop-blur-xl rounded-3xl shadow-2xl border border-orange-200/30 p-8 lg:p-12" 
             x-data 
             x-init="$el.style.opacity = '0'; $el.style.transform = 'translateY(30px)'; 
                    setTimeout(() => { 
                        $el.style.transition = 'all 0.8s cubic-bezier(0.4, 0, 0.2, 1)'; 
                        $el.style.opacity = '1'; 
                        $el.style.transform = 'translateY(0)'; 
                    }, 300)">
            {{-- Border dengan accent color orange untuk edit mode --}}
            
            <form method="POST" action="{{ route('projects.update', $project ?? 1) }}" class="space-y-8">
                @csrf
                @method('PUT')
                {{-- Method PUT untuk update operation --}}
                
                <!-- Project Information Section -->
                <div class="bg-gradient-to-r from-orange-50/50 to-amber-50/50 rounded-2xl p-6 border border-orange-100/50 backdrop-blur-sm">
                    {{-- Section dengan gradient orange untuk edit mode --}}
                    
                    <!-- Section Header dengan Edit Icon -->
                    <div class="flex items-center mb-6">
                        <div class="w-12 h-12 bg-gradient-to-r from-orange-500 to-amber-600 rounded-xl flex items-center justify-center mr-4 shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-gray-800">Update Project Details</h3>
                            <p class="text-sm text-gray-600">Modify the information below to update your project</p>
                        </div>
                    </div>
                    
                    <!-- Form Fields Grid -->
                    <div class="grid md:grid-cols-2 gap-6">
                        
                        <!-- Project Name Field -->
                        <div class="md:col-span-2">
                            <label for="project_name" class="block text-sm font-semibold text-gray-700 mb-3">
                                <span class="flex items-center">
                                    Project Name 
                                    <span class="text-red-500 ml-1">*</span>
                                    <svg class="w-4 h-4 text-gray-400 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </span>
                            </label>
                            
                            <input type="text" 
                                   id="project_name" 
                                   name="project_name" 
                                   value="{{ old('project_name', $project->project_name ?? '') }}"
                                   required
                                   class="w-full px-5 py-4 bg-white/60 backdrop-blur-sm border border-gray-200/60 rounded-xl text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-orange-500/50 focus:border-orange-500/50 transition-all duration-300 hover:bg-white/80 hover:border-gray-300/60 shadow-sm"
                                   placeholder="Enter your awesome project name">
                            {{-- 
                                Pre-filled dengan data existing, focus ring orange untuk edit mode
                                old('project_name', $project->project_name ?? ''): prioritas old input untuk validation errors
                            --}}
                            
                            @error('project_name')
                            <div class="mt-2 flex items-center text-sm text-red-600 bg-red-50/50 rounded-lg p-2 border border-red-200/50">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                        
                        <!-- Current Team Lead Display (Read-only) -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                <span class="flex items-center">
                                    Current Team Lead
                                    <svg class="w-4 h-4 text-gray-400 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </span>
                            </label>
                            
                            <div class="px-5 py-4 bg-gray-50/60 backdrop-blur-sm border border-gray-200/60 rounded-xl text-gray-700 shadow-sm">
                                {{-- Display current team lead (read-only dalam edit mode project details) --}}
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-orange-500 rounded-full flex items-center justify-center text-white text-sm font-medium mr-3">
                                        {{ isset($project) && isset($project->teamLead) ? strtoupper(substr($project->teamLead->name ?? 'TL', 0, 1)) : 'TL' }}
                                    </div>
                                    <div>
                                        <p class="font-medium">{{ isset($project) && isset($project->teamLead) ? $project->teamLead->name ?? 'Team Lead' : 'Team Lead' }}</p>
                                        <p class="text-sm text-gray-500">{{ isset($project) && isset($project->teamLead) ? $project->teamLead->email ?? '' : '' }}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <p class="mt-2 text-xs text-gray-500">
                                💡 Team lead can only be changed through project member management
                            </p>
                        </div>
                        
                        <!-- Deadline Field -->
                        <div class="md:col-span-2">
                            <label for="deadline" class="block text-sm font-semibold text-gray-700 mb-3">
                                <span class="flex items-center">
                                    Project Deadline 
                                    <span class="text-red-500 ml-1">*</span>
                                    <svg class="w-4 h-4 text-gray-400 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </span>
                            </label>
                            
                            <input type="date" 
                                   id="deadline" 
                                   name="deadline" 
                                   value="{{ old('deadline', isset($project) && $project->deadline ? date('Y-m-d', strtotime($project->deadline)) : '') }}"
                                   required
                                   min="{{ date('Y-m-d') }}"
                                   class="w-full px-5 py-4 bg-white/60 backdrop-blur-sm border border-gray-200/60 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-orange-500/50 focus:border-orange-500/50 transition-all duration-300 hover:bg-white/80 hover:border-gray-300/60 shadow-sm">
                            {{-- 
                                Pre-filled dengan deadline existing, min=today untuk edit
                                Format tanggal yang proper untuk input date
                            --}}
                            
                            @error('deadline')
                            <div class="mt-2 flex items-center text-sm text-red-600 bg-red-50/50 rounded-lg p-2 border border-red-200/50">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <!-- Project Description Section -->
                <div class="bg-gradient-to-r from-red-50/50 to-pink-50/50 rounded-2xl p-6 border border-red-100/50 backdrop-blur-sm">
                    
                    <!-- Section Header -->
                    <div class="flex items-center mb-6">
                        <div class="w-12 h-12 bg-gradient-to-r from-red-500 to-pink-600 rounded-xl flex items-center justify-center mr-4 shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-gray-800">Update Description</h3>
                            <p class="text-sm text-gray-600">Revise your project goals and objectives</p>
                        </div>
                    </div>
                    
                    <div>
                        <label for="description" class="block text-sm font-semibold text-gray-700 mb-3">
                            <span class="flex items-center">
                                Description
                                <span class="text-gray-400 ml-2 text-xs">(Optional)</span>
                                <svg class="w-4 h-4 text-gray-400 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </span>
                        </label>
                        
                        <textarea id="description" 
                                  name="description" 
                                  rows="6"
                                  class="w-full px-5 py-4 bg-white/60 backdrop-blur-sm border border-gray-200/60 rounded-xl text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-red-500/50 focus:border-red-500/50 transition-all duration-300 hover:bg-white/80 hover:border-gray-300/60 resize-none shadow-sm"
                                  placeholder="Describe your project goals, key features, target audience, and success criteria. What problem does this project solve?">{{ old('description', $project->description ?? '') }}</textarea>
                        {{-- Pre-filled dengan description existing --}}
                        
                        @error('description')
                        <div class="mt-2 flex items-center text-sm text-red-600 bg-red-50/50 rounded-lg p-2 border border-red-200/50">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ $message }}
                        </div>
                        @enderror
                        
                        <!-- Character Counter -->
                        <div class="mt-3 flex justify-between items-center text-sm">
                            <span class="text-gray-500">💡 Tip: Keep your description updated as the project evolves</span>
                            <span class="text-gray-400" id="char-counter">0 characters</span>
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons Section untuk Edit -->
                <div class="flex flex-col sm:flex-row gap-4 pt-8 border-t border-gray-200/50">
                    
                    <!-- Cancel Button -->
                    <button type="button" 
                            onclick="window.history.back()" 
                            class="flex-1 px-6 py-4 bg-gray-100/80 hover:bg-gray-200/80 text-gray-700 font-semibold rounded-xl transition-all duration-300 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-gray-500/50 focus:ring-offset-2 shadow-sm hover:shadow-md backdrop-blur-sm border border-gray-200/50">
                        <span class="flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Cancel
                        </span>
                    </button>
                    
                    <!-- Reset Button -->
                    <button type="button" 
                            onclick="location.reload()"
                            class="flex-1 px-6 py-4 bg-yellow-100/80 hover:bg-yellow-200/80 text-yellow-800 font-semibold rounded-xl transition-all duration-300 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-yellow-500/50 focus:ring-offset-2 shadow-sm hover:shadow-md backdrop-blur-sm border border-yellow-200/50">
                        <span class="flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Reset
                        </span>
                    </button>
                    
                    <!-- Update Project Button -->
                    <button type="submit"
                            class="flex-1 px-6 py-4 bg-gradient-to-r from-orange-600 to-red-700 hover:from-orange-700 hover:to-red-800 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-orange-500/50 focus:ring-offset-2 backdrop-blur-sm">
                        {{-- Primary button dengan gradient orange/red untuk edit mode --}}
                        <span class="flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Update Project
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- JavaScript untuk Character Counter dan Edit Enhancements --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Character counter untuk textarea
    const textarea = document.getElementById('description');
    const counter = document.getElementById('char-counter');
    
    if (textarea && counter) {
        // Initialize counter dengan existing content
        counter.textContent = textarea.value.length + ' characters';
        
        textarea.addEventListener('input', function() {
            const count = this.value.length;
            counter.textContent = count + ' characters';
            
            // Change color based on length
            if (count > 500) {
                counter.classList.add('text-yellow-500');
                counter.classList.remove('text-red-500');
            } else if (count > 800) {
                counter.classList.add('text-red-500');
                counter.classList.remove('text-yellow-500');
            } else {
                counter.classList.remove('text-yellow-500', 'text-red-500');
            }
        });
    }
    
    // Warn about unsaved changes
    let formChanged = false;
    const form = document.querySelector('form');
    const inputs = form.querySelectorAll('input, textarea, select');
    
    inputs.forEach(input => {
        input.addEventListener('change', () => {
            formChanged = true;
        });
    });
    
    // Warn before leaving page with unsaved changes
    window.addEventListener('beforeunload', function(e) {
        if (formChanged) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
    
    // Reset form changed flag when form is submitted
    form.addEventListener('submit', () => {
        formChanged = false;
    });
});
</script>
@endsection