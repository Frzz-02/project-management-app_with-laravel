{{--
    Member List View Component
    
    Komponen untuk menampilkan daftar member dalam format tabel/list view.
    Component ini menggantikan inline list view di halaman utama untuk
    meningkatkan keterbacaan dan maintainability kode.
    
    Features yang tersedia:
    - Table layout dengan header yang jelas
    - Row untuk setiap member dengan informasi lengkap
    - Action dropdown untuk setiap member (view profile, send email)
    - Status indicators dan progress bars
    - Responsive design dengan grid system
    - Task progress tracking dengan percentage calculation
    
    Props yang diterima:
    - $members: Array data members dari parent component (project-members/index.blade.php)
      Structure: [
          'id' => integer,
          'name' => string (dari users.full_name),
          'email' => string (dari users.email),
          'role' => enum (team lead|developer|designer dari project_members.role),
          'joinedAt' => timestamp (dari project_members.joined_at), 
          'avatar' => string (initials),
          'status' => string (online|offline dari users.current_task_status),
          'tasksCount' => integer,
          'completedTasks' => integer
      ]
    
    Alpine.js Integration:
    - Hanya untuk UI interactions (dropdowns, transitions, modal triggers)
    - Event dispatching untuk communication dengan parent component
    - No data manipulation, hanya styling dan animasi
    - CRUD operations disabled, view-only functionality
--}}

<!-- List View Container -->
<div 
    x-show="viewMode === 'list'"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 scale-95"
    x-transition:enter-end="opacity-100 scale-100"
    class="bg-white/60 backdrop-blur-xl rounded-2xl border border-white/30 overflow-hidden"
>


    <!-- Table Header -->
    <div class="bg-gray-50/50 px-6 py-4 border-b border-white/30">
        <div class="grid grid-cols-12 gap-4 text-sm font-medium text-gray-600">
            <div class="col-span-4">Member</div>
            <div class="col-span-2">Role</div>
            <div class="col-span-2">Joined</div>
            <div class="col-span-2">Tasks</div>
            <div class="col-span-1">Status</div>
            <div class="col-span-1">Actions</div>
        </div>
    </div>


    <!-- Table Body -->
    <div class="divide-y divide-white/30">
        @foreach($members as $member)
            <div class="grid grid-cols-12 gap-4 px-6 py-4 hover:bg-white/50 transition-all duration-200 group">
                <!-- Member info (4 columns) -->
                <div class="col-span-4 flex items-center space-x-3">


                    <!-- Avatar dengan status indicator -->
                    <div class="relative flex-shrink-0">
                        <div class="w-10 h-10 rounded-lg bg-gradient-to-r from-blue-500 to-indigo-500 flex items-center justify-center text-white font-semibold text-sm shadow-md">
                            {{ $member['avatar'] }}
                        </div>
                        <!-- Status dot -->
                        <div class="absolute -bottom-1 -right-1 w-3 h-3 rounded-full border-2 border-white shadow-sm {{ $member['status'] === 'online' ? 'bg-green-500' : 'bg-gray-400' }}">
                        </div>
                    </div>


                    <!-- Name dan email -->
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate group-hover:text-blue-600 transition-colors duration-200">
                            {{ $member['name'] }}
                        </p>
                        <p class="text-xs text-gray-600 truncate">
                            {{ $member['email'] }}
                        </p>
                    </div>


                </div>


                <!-- Role badge (2 columns) -->
                <div class="col-span-2 flex items-center">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium uppercase tracking-wide @if($member['role'] === 'team lead') bg-purple-100 text-purple-800 @elseif($member['role'] === 'developer') bg-blue-100 text-blue-800 @elseif($member['role'] === 'designer') bg-pink-100 text-pink-800 @endif">
                        {{ $member['role'] }}
                    </span>
                </div>


                <!-- Join date (2 columns) -->
                <div class="col-span-2 flex items-center">
                    <div class="text-sm text-gray-600">
                        <div>{{ date('M j, Y', strtotime($member['joinedAt'])) }}</div>
                        <div class="text-xs text-gray-500">
                            {{ floor((time() - strtotime($member['joinedAt'])) / (60 * 60 * 24)) }} days ago
                        </div>
                    </div>
                </div>


                <!-- Task progress (2 columns) -->
                <div class="col-span-2 flex items-center">
                    <div class="w-full">
                        <!-- Task numbers -->
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs text-gray-600">Tasks</span>
                            <span class="text-xs font-semibold text-gray-900">
                                {{ $member['completedTasks'] }}/{{ $member['tasksCount'] }}
                            </span>
                        </div>
                        <!-- Progress bar -->
                        <div class="w-full bg-gray-200 rounded-full h-1.5 overflow-hidden">
                            <div 
                                class="h-1.5 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-full transition-all duration-500"
                                style="width: {{ ($member['completedTasks'] / $member['tasksCount'] * 100) }}%"
                            ></div>
                        </div>
                        <!-- Percentage -->
                        <div class="text-right mt-1">
                            @php
                                $percentage = ($member['completedTasks'] / $member['tasksCount']);
                                $percentageClass = $percentage >= 0.8 ? 'text-green-600' : ($percentage >= 0.5 ? 'text-yellow-600' : 'text-red-600');
                            @endphp
                            <span class="text-xs font-medium {{ $percentageClass }}">
                                {{ round($percentage * 100) }}%
                            </span>
                        </div>
                    </div>
                </div>


                <!-- Status indicator (1 column) -->
                <div class="col-span-1 flex items-center justify-center">
                    <div class="flex items-center space-x-1">
                        <!-- Status dot -->
                        <div class="w-2 h-2 rounded-full {{ $member['status'] === 'online' ? 'bg-green-500' : 'bg-gray-400' }}"></div>
                        <!-- Status text untuk desktop -->
                        <span class="hidden lg:inline text-xs font-medium capitalize {{ $member['status'] === 'online' ? 'text-green-600' : 'text-gray-500' }}">
                            {{ $member['status'] }}
                        </span>
                    </div>
                </div>


                <!-- Actions dropdown (1 column) -->
                <div class="col-span-1 flex items-center justify-center">
                    <div class="relative" x-data="{ open: false }">
                        <!-- Action button -->
                        <button 
                            @click="open = !open"
                            class="p-2 text-gray-400 hover:text-gray-600 hover:bg-white/50 rounded-lg transition-all duration-200 opacity-0 group-hover:opacity-100"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                            </svg>
                        </button>

                        <!-- Dropdown menu -->
                        <div 
                            x-show="open"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            @click.away="open = false"
                            class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-xl bg-white/90 backdrop-blur-xl shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none border border-white/40"
                        >


                            <div class="py-2">
                                <button 
                                    @click="$dispatch('show-edit-member-modal', {{ json_encode($member) }}); open = false"
                                    class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-200"
                                >
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    Edit Member
                                </button>

                                <button 
                                    @click="$dispatch('view-member-profile', {{ json_encode($member) }}); open = false"
                                    class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-200"
                                >
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    View Profile
                                </button>

                                <button class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-200">
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    Send Email
                                </button>

                                <div class="border-t border-gray-200 my-1"></div>

                                <button 
                                    @click="$dispatch('show-delete-member-modal', {{ json_encode($member) }}); open = false"
                                    class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors duration-200"
                                >
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Remove Member
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>