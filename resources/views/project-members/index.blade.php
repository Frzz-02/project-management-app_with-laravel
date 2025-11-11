@extends('layouts.app')

@section('title', 'Project Members')

@section('content')
{{-- 
    HALAMAN PROJECT MEMBERS MANAGEMENT - MODERN DESIGN
    ================================================
    Features:
    - Real-time search dan filtering dengan database query
    - Invite existing users dengan pencarian modal
    - Edit role member dengan validasi blade
    - Delete confirmation dengan proper security
    - Statistics cards dengan data real dari database
    - Responsive design dengan glassmorphism effect
    - Alpine.js untuk UI state, Blade untuk logika dan validasi
    - Error handling dan flash messages
--}}



{{-- 
    MAIN ALPINE DATA CONFIGURATION
    ==============================
    Setup data dan methods untuk manage modal states,
    form data, dan user interactions
--}}
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 py-6 px-4"
     x-data="{
        // Modal visibility states untuk manage popup windows
        showAddModal: false,
        showEditModal: false,
        showDeleteModal: false,
        
        
        
        // Form data untuk invite new member (hanya untuk UI state)
        formData: {
            user_id: '',
            role: 'developer',
        },
        
        
        
        // Edit data untuk update member role (hanya untuk UI state)  
        editData: {
            id: null,
            role: 'developer'
        },
        
        
        
        // Delete confirmation data (hanya untuk UI state)
        deleteData: {
            id: null,
            name: ''
        },
        
        
        
        // User search untuk invite modal
        searchResults: [],
        searchLoading: false,
        selectedUser: null,
        
        
        
        // Modal management methods
        openAddModal() {
            this.formData = { user_id: '', role: 'developer' };
            this.selectedUser = null;
            this.searchResults = [];
            this.showAddModal = true;
        },
        
        
        
        openEditModal(member) {
            this.editData = {
                id: member.id,
                role: member.role
            };
            this.showEditModal = true;
        },
        
        
        
        openDeleteModal(member) {
            this.deleteData = {
                id: member.id,
                name: member.name
            };
            this.showDeleteModal = true;
        },
        
        
        
        closeModals() {
            this.showAddModal = false;
            this.showEditModal = false;
            this.showDeleteModal = false;
            this.selectedUser = null;
            this.searchResults = [];
        },
        
        
        
        // Search users functionality untuk invite modal
        async searchUsers(query) {
            if (query.length < 2) {
                this.searchResults = [];
                return;
            }
            
            this.searchLoading = true;
            try {
                // Debug: Log the URL being used
                const searchUrl = `{{ url('project-members/search-users') }}?search=${encodeURIComponent(query)}`;
                console.log('Searching with URL:', searchUrl);
                
                const response = await fetch(searchUrl);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                console.log('Search response:', data);
                
                this.searchResults = data.users || [];
            } catch (error) {
                console.error('Search error:', error);
                this.searchResults = [];
            }
            this.searchLoading = false;
        },
        
        
        
        // Select user untuk invite
        selectUser(user) {
            this.selectedUser = user;
            this.formData.user_id = user.id;
            this.searchResults = [];
        }
     }">

    <!-- Background Decorative Elements -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-20 left-10 w-32 h-32 bg-blue-200/20 rounded-full blur-xl animate-pulse"></div>
        <div class="absolute top-40 right-20 w-48 h-48 bg-indigo-200/15 rounded-full blur-2xl animate-pulse" style="animation-delay: 1s;"></div>
        <div class="absolute bottom-20 left-20 w-40 h-40 bg-purple-200/20 rounded-full blur-xl animate-pulse" style="animation-delay: 2s;"></div>
    </div>

    <!-- Main Container -->
    <div class="relative z-10 max-w-7xl mx-auto">
        
        <!-- Header Section -->
        <div class="mb-8" 
             x-data 
             x-init="$el.style.opacity = '0'; $el.style.transform = 'translateY(-20px)'; 
                    setTimeout(() => { 
                        $el.style.transition = 'all 0.8s ease-out'; 
                        $el.style.opacity = '1'; 
                        $el.style.transform = 'translateY(0)'; 
                    }, 100)">
            
            <!-- Breadcrumb -->
            <nav class="mb-6">
                <ol class="flex items-center space-x-2 text-sm text-gray-500">
                    <li><a href="{{ route('dashboard') }}" class="hover:text-blue-600 transition-colors duration-200">Dashboard</a></li>
                    <li><span class="mx-2 text-gray-400">→</span></li>
                    <li><a href="{{ route('projects.index') }}" class="hover:text-blue-600 transition-colors duration-200">Projects</a></li>
                    @if($members->isNotEmpty() && $members->first()->project)
                        <li><span class="mx-2 text-gray-400">→</span></li>
                        <li><a href="{{ route('projects.show', $members->first()->project->id) }}" class="hover:text-blue-600 transition-colors duration-200">{{ $members->first()->project->project_name }}</a></li>
                    @endif
                    <li><span class="mx-2 text-gray-400">→</span></li>
                    <li class="text-blue-600 font-medium">Team Members</li>
                </ol>
            </nav>
            
            <!-- Page Title & Actions -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 mb-8">
                <div>
                    <h1 class="text-4xl lg:text-5xl font-bold bg-gradient-to-r from-gray-900 via-blue-800 to-indigo-900 bg-clip-text text-transparent mb-2">
                        Team Members
                    </h1>
                    <p class="text-lg text-gray-600">Manage your project team and member roles</p>
                </div>
                
                <!-- Add Member Button -->
                <button @click="openAddModal()"
                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Add Member
                </button>
            </div>
        </div>

        {{-- 
            STATISTICS DASHBOARD CARDS
            =========================
            Menampilkan statistik team members dengan data real dari database
            Menggunakan data dari controller, bukan hardcoded
        --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8"
             x-data 
             x-init="$el.style.opacity = '0'; $el.style.transform = 'translateY(30px)'; 
                    setTimeout(() => { 
                        $el.style.transition = 'all 0.8s ease-out'; 
                        $el.style.opacity = '1'; 
                        $el.style.transform = 'translateY(0)'; 
                    }, 200)">
            
            <!-- Total Members Card dengan data dari database -->
            <div class="bg-white/70 backdrop-blur-xl rounded-2xl p-6 border border-white/50 shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-3xl font-bold text-blue-600">{{ $stats['totalMembers'] }}</p>
                        <p class="text-sm font-medium text-gray-600">Total Members</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-500 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-.5a4 4 0 110 5.292"/>
                        </svg>
                    </div>
                </div>
            </div>



            <!-- Team Leads Card dengan data dari database -->
            <div class="bg-white/70 backdrop-blur-xl rounded-2xl p-6 border border-white/50 shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-3xl font-bold text-purple-600">{{ $stats['teamLeads'] }}</p>
                        <p class="text-sm font-medium text-gray-600">Team Leads</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-500 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>



            <!-- Combined Developers & Designers Card -->
            <div class="bg-white/70 backdrop-blur-xl rounded-2xl p-6 border border-white/50 shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105">
                <div class="grid grid-cols-2 gap-4">
                    <div class="text-center">
                        <p class="text-2xl font-bold text-green-600">{{ $stats['developers'] }}</p>
                        <p class="text-xs text-gray-600">Developers</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-orange-600">{{ $stats['designers'] }}</p>
                        <p class="text-xs text-gray-600">Designers</p>
                    </div>
                </div>
            </div>
        </div>



        {{-- 
            SEARCH & FILTER FORM
            ====================
            Form untuk search dan filter menggunakan method GET
            Data dikirim ke controller untuk processing di backend
        --}}
        <div class="bg-white/60 backdrop-blur-xl rounded-2xl shadow-xl border border-white/40 p-6 mb-8"
             x-data 
             x-init="$el.style.opacity = '0'; $el.style.transform = 'translateY(30px)'; 
                    setTimeout(() => { 
                        $el.style.transition = 'all 0.8s ease-out'; 
                        $el.style.opacity = '1'; 
                        $el.style.transform = 'translateY(0)'; 
                    }, 300)">
            
            <form method="GET" action="{{ route('project-members.index') }}" class="flex flex-col lg:flex-row gap-4">
                
                <!-- Search Input Field dengan submit otomatis -->
                <div class="flex-1">
                    <div class="relative">
                        <input type="text" 
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Search members by name, email, or username..."
                               class="w-full pl-12 pr-4 py-3 bg-white/80 backdrop-blur-sm border border-gray-200 rounded-xl text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300">
                        <svg class="absolute left-4 top-3.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>
                
                
                
                <!-- Role Filter Dropdown dengan submit otomatis -->
                <div class="lg:w-64">
                    <select name="role_filter"
                            onchange="this.form.submit()"
                            class="w-full px-4 py-3 bg-white/80 backdrop-blur-sm border border-gray-200 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300">
                        <option value="">All Roles</option>
                        <option value="team lead" {{ request('role_filter') == 'team lead' ? 'selected' : '' }}>Team Lead</option>
                        <option value="developer" {{ request('role_filter') == 'developer' ? 'selected' : '' }}>Developer</option>
                        <option value="designer" {{ request('role_filter') == 'designer' ? 'selected' : '' }}>Designer</option>
                    </select>
                </div>
                
                
                
                <!-- Search Button (optional) -->
                <button type="submit"
                        class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-medium rounded-xl shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </button>
            </form>
        </div>

        {{-- 
            MEMBERS GRID DISPLAY  
            ====================
            Menampilkan data members real dari database dengan loop @forelse
            Menggunakan data dari controller, lengkap dengan relationships
        --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6"
             x-data 
             x-init="$el.style.opacity = '0'; $el.style.transform = 'translateY(40px)'; 
                    setTimeout(() => { 
                        $el.style.transition = 'all 0.8s ease-out'; 
                        $el.style.opacity = '1'; 
                        $el.style.transform = 'translateY(0)'; 
                    }, 400)">
            
            @forelse($members as $member)
            <div class="bg-white/70 backdrop-blur-xl rounded-2xl shadow-lg border border-white/40 p-6 hover:shadow-xl hover:scale-105 transition-all duration-300 group">
                
                {{-- Member Avatar & Actions Header --}}
                <div class="flex items-center justify-between mb-4">
                    <div class="relative">
                        {{-- Generate avatar dari initial nama user --}}
                        <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white text-xl font-bold shadow-lg">
                            @php
                                $initials = '';
                                $names = explode(' ', $member->user->full_name);
                                $initials .= strtoupper(substr($names[0], 0, 1));
                                if (count($names) > 1) {
                                    $initials .= strtoupper(substr($names[count($names)-1], 0, 1));
                                }
                            @endphp
                            {{ $initials }}
                        </div>
                    </div>
                    
                    
                    
                    {{-- Actions Dropdown Menu --}}
                    <div class="relative" x-data="{ showDropdown: false }">
                        <button @click="showDropdown = !showDropdown"
                                class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors duration-200 opacity-0 group-hover:opacity-100">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                            </svg>
                        </button>
                        
                        {{-- Dropdown Menu Items --}}
                        <div x-show="showDropdown" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             @click.away="showDropdown = false"
                             class="absolute right-0 top-full mt-2 w-48 bg-white/90 backdrop-blur-xl rounded-xl shadow-lg border border-white/40 py-2 z-10"
                             style="display: none;">
                            
                            {{-- Edit Role Button --}}
                            <button @click="openEditModal({
                                        id: {{ $member->id }},
                                        name: '{{ addslashes($member->user->full_name) }}',
                                        role: '{{ $member->role }}'
                                    }); showDropdown = false"
                                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 transition-colors duration-200 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Edit Role
                            </button>
                            
                            {{-- Remove Member Button --}}
                            <button @click="openDeleteModal({
                                        id: {{ $member->id }},
                                        name: '{{ addslashes($member->user->full_name) }}'
                                    }); showDropdown = false"
                                    class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors duration-200 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Remove Member
                            </button>
                        </div>
                    </div>
                </div>
                
                
                
                {{-- Member Information Display --}}
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ $member->user->full_name }}</h3>
                    <p class="text-sm text-gray-600 mb-1">{{ $member->user->email }}</p>
                    <p class="text-xs text-gray-500 mb-3">{{ '@' . $member->user->username }}</p>
                    
                    {{-- Role Badge dengan warna sesuai role --}}
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border {{ $member->role_badge_color }}">
                        {{ ucwords($member->role) }}
                    </span>
                </div>
                
                
                
                {{-- Member Join Date Information --}}
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <p class="text-xs text-gray-500">
                        <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Joined {{ $member->joined_at->format('M d, Y') }}
                    </p>
                </div>
            </div>
            @empty
            
            
            
            {{-- Empty State ketika tidak ada members --}}
            <div class="col-span-full flex flex-col items-center justify-center py-20">
                <div class="w-32 h-32 bg-gradient-to-br from-blue-100 to-purple-100 rounded-full flex items-center justify-center mb-6">
                    <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-.5a4 4 0 110 5.292"/>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">No Team Members Yet</h3>
                <p class="text-gray-600 mb-6">Start building your team by inviting existing users to join your project.</p>
                <button @click="openAddModal()"
                        class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300">
                    Invite First Member
                </button>
            </div>
            @endforelse
        </div>
    </div>

    {{-- 
        INVITE MEMBER MODAL
        ==================
        Modal untuk invite existing user ke project
        Features: 
        - Search existing users dengan AJAX
        - Select user dari hasil pencarian
        - Validasi menggunakan blade syntax
        - Alpine.js hanya untuk UI state management
    --}}
    <div x-show="showAddModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm"
         style="display: none;">
        
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center">
            <div x-show="showAddModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                 @click.away="closeModals()"
                 class="w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white/90 backdrop-blur-xl shadow-2xl rounded-2xl border border-white/40">
                
                {{-- Modal Header --}}
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-xl flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800">Invite Team Member</h3>
                    </div>
                    <button @click="closeModals()" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                
                
                {{-- Invite Form dengan validasi blade --}}
                <form action="{{ route('project-members.store') }}" method="POST" class="space-y-6">
                    @csrf
                    
                    {{-- User Search & Selection --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search & Select User <span class="text-red-500">*</span></label>
                        
                        {{-- Selected User Display --}}
                        <div x-show="selectedUser" class="mb-3 p-3 bg-blue-50 border border-blue-200 rounded-xl">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white text-sm font-bold mr-3">
                                        <span x-text="selectedUser ? selectedUser.avatar : ''"></span>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900" x-text="selectedUser ? selectedUser.name : ''"></p>
                                        <p class="text-sm text-gray-600" x-text="selectedUser ? selectedUser.email : ''"></p>
                                    </div>
                                </div>
                                <button type="button" @click="selectedUser = null; formData.user_id = ''" class="text-red-500 hover:text-red-700">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        
                        
                        
                        {{-- User Search Input --}}
                        <div x-show="!selectedUser" class="relative">
                            <input type="text"
                                   placeholder="Search users by name, email, or username..."
                                   @input="searchUsers($event.target.value)"
                                   class="w-full px-4 py-3 pr-10 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                            
                            {{-- Search Loading Indicator --}}
                            <div x-show="searchLoading" class="absolute right-3 top-3.5">
                                <svg class="w-5 h-5 text-blue-500 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                        </div>
                        
                        
                        
                        {{-- Search Results Dropdown --}}
                        <div x-show="searchResults.length > 0 && !selectedUser" 
                             class="mt-2 max-h-60 overflow-y-auto bg-white border border-gray-300 rounded-xl shadow-lg">
                            <template x-for="user in searchResults" :key="user.id">
                                <button type="button" 
                                        @click="selectUser(user)"
                                        class="w-full p-3 text-left hover:bg-blue-50 transition-colors duration-200 flex items-center border-b border-gray-100 last:border-b-0">
                                    <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white text-sm font-bold mr-3">
                                        <span x-text="user.avatar"></span>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900" x-text="user.name"></p>
                                        <p class="text-sm text-gray-600" x-text="user.email"></p>
                                        <p class="text-xs text-gray-500" x-text="'@' + user.username"></p>
                                    </div>
                                </button>
                            </template>
                        </div>
                        
                        
                        
                        {{-- Hidden User ID Input untuk form submission --}}
                        <input type="hidden" name="user_id" x-model="formData.user_id" required>

                        {{-- Hidden project_id Input untuk form submission --}}
                        <input type="hidden" name="project_id" value="{{ $currentProject->id ?? '1' }}" required>

                        {{-- Validation Error Display --}}
                        @error('user_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    
                    
                    {{-- Role Selection --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Role <span class="text-red-500">*</span></label>
                        <select name="role" 
                                x-model="formData.role"
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                            <option value="developer">Developer</option>
                            <option value="designer">Designer</option>
                            <option value="team lead">Team Lead</option>
                        </select>
                        
                        {{-- Validation Error Display --}}
                        @error('role')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    
                    
                    {{-- Action Buttons --}}
                    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
                        <button type="button" 
                                @click="closeModals()"
                                class="px-6 py-3 text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-all duration-200">
                            Cancel
                        </button>
                        <button type="submit"
                                :disabled="!selectedUser"
                                :class="selectedUser ? 'bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700' : 'bg-gray-400 cursor-not-allowed'"
                                class="px-6 py-3 text-white font-medium rounded-xl shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-200">
                            Invite Member
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 
        EDIT MEMBER ROLE MODAL
        =====================
        Modal untuk edit role member yang sudah ada
        Features: 
        - Hanya bisa edit role, tidak bisa edit user info
        - Validasi menggunakan blade syntax
        - Alpine.js hanya untuk UI state management
        - Menampilkan info member yang tidak bisa diedit
    --}}
    <div x-show="showEditModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm"
         style="display: none;">
        
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center">
            <div x-show="showEditModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                 @click.away="closeModals()"
                 class="w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white/90 backdrop-blur-xl shadow-2xl rounded-2xl border border-white/40">
                
                {{-- Modal Header --}}
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-gradient-to-r from-green-500 to-teal-500 rounded-xl flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800">Edit Member Role</h3>
                    </div>
                    <button @click="closeModals()" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                
                
                {{-- Member Information Display (Read-only) --}}
                <div class="mb-6 p-4 bg-gray-50 rounded-xl border">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">Member Information</h4>
                    <div class="space-y-2">
                        <p class="text-sm text-gray-900"><span class="font-medium">Name:</span> <span x-text="editData.name || 'N/A'"></span></p>
                        <p class="text-sm text-gray-600"><span class="font-medium">Current Role:</span> 
                            <span x-text="editData.role ? editData.role.charAt(0).toUpperCase() + editData.role.slice(1) : 'N/A'" 
                                  class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium ml-1"></span>
                        </p>
                    </div>
                </div>
                
                
                
                {{-- Edit Form (hanya role yang bisa diedit) --}}
                <form :action="`{{ route('project-members.index') }}/${editData.id}`" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')
                    
                    {{-- Role Selection (hanya field yang bisa diedit) --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">New Role <span class="text-red-500">*</span></label>
                        <select name="role" 
                                x-model="editData.role"
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200">
                            <option value="">Select Role</option>
                            <option value="developer">Developer</option>
                            <option value="designer">Designer</option>
                            <option value="team lead">Team Lead</option>
                        </select>
                        
                        {{-- Validation Error Display --}}
                        @error('role')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        
                        {{-- Helper Text --}}
                        <p class="mt-2 text-xs text-gray-500">
                            Note: Only the member's role can be modified. User information cannot be changed.
                        </p>
                    </div>
                    
                    
                    
                    {{-- Action Buttons --}}
                    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
                        <button type="button" 
                                @click="closeModals()"
                                class="px-6 py-3 text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-all duration-200">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-6 py-3 bg-gradient-to-r from-green-600 to-teal-600 hover:from-green-700 hover:to-teal-700 text-white font-medium rounded-xl shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-green-500 transition-all duration-200">
                            Update Role
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 
        DELETE MEMBER CONFIRMATION MODAL
        ===============================
        Modal konfirmasi untuk remove member dari project
        Features: 
        - Konfirmasi keamanan sebelum delete
        - Informasi member yang akan dihapus
        - Warning tentang action yang tidak bisa diundo
        - Form dengan method DELETE dan CSRF protection
    --}}
    <div x-show="showDeleteModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm"
         style="display: none;">
        
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center">
            <div x-show="showDeleteModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                 @click.away="closeModals()"
                 class="w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white/90 backdrop-blur-xl shadow-2xl rounded-2xl border border-white/40">
                
                {{-- Modal Header --}}
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-gradient-to-r from-red-500 to-pink-500 rounded-xl flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.664-.833-2.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800">Remove Team Member</h3>
                    </div>
                    <button @click="closeModals()" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                
                
                {{-- Warning Content --}}
                <div class="mb-6">
                    <div class="flex items-center p-4 bg-red-50 border border-red-200 rounded-xl mb-4">
                        <svg class="w-6 h-6 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.664-.833-2.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                        <div>
                            <h4 class="text-sm font-medium text-red-800">Warning</h4>
                            <p class="text-sm text-red-700">This action cannot be undone.</p>
                        </div>
                    </div>
                    
                    <p class="text-gray-700">
                        Are you sure you want to remove <strong class="text-gray-900" x-text="deleteData.name"></strong> from this project? 
                    </p>
                    
                    <p class="text-sm text-gray-600 mt-2">
                        The user will lose access to project resources, but their user account will remain intact.
                    </p>
                </div>
                
                
                
                {{-- Delete Form dengan proper security --}}
                <form :action="`{{ route('project-members.index') }}/${deleteData.id}`" method="POST" class="space-y-4">
                    @csrf
                    @method('DELETE')
                    
                    {{-- Action Buttons --}}
                    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
                        <button type="button" 
                                @click="closeModals()"
                                class="px-6 py-3 text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-all duration-200">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-6 py-3 bg-gradient-to-r from-red-600 to-pink-600 hover:from-red-700 hover:to-pink-700 text-white font-medium rounded-xl shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-red-500 transition-all duration-200">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Remove Member
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Flash Messages -->
@if(session('success'))
<div x-data="{ show: true }" 
     x-show="show" 
     x-init="setTimeout(() => show = false, 5000)"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 translate-y-2"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-300"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 translate-y-2"
     class="fixed top-6 right-6 z-50 bg-green-100 border border-green-400 text-green-700 px-6 py-4 rounded-xl shadow-lg backdrop-blur-xl">
    <div class="flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ session('success') }}
        <button @click="show = false" class="ml-4 text-green-600 hover:text-green-800">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
</div>
@endif

@if(session('error'))
<div x-data="{ show: true }" 
     x-show="show" 
     x-init="setTimeout(() => show = false, 5000)"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 translate-y-2"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-300"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 translate-y-2"
     class="fixed top-6 right-6 z-50 bg-red-100 border border-red-400 text-red-700 px-6 py-4 rounded-xl shadow-lg backdrop-blur-xl">
    <div class="flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ session('error') }}
        <button @click="show = false" class="ml-4 text-red-600 hover:text-red-800">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
</div>
@endif

<style>
/* Custom scrollbar for modals */
.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: rgba(0, 0, 0, 0.1);
    border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: linear-gradient(to bottom, #3B82F6, #6366F1);
    border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(to bottom, #2563EB, #4F46E5);
}
</style>
@endsection
