{{--
    Member Card Component
    
    Component untuk menampilkan individual member dalam grid view.
    Menggunakan modern card design dengan glassmorphism effects.
    
    Props yang diterima:
    - $member: Object berisi data member (akan digunakan dari Alpine.js data)
    
    Features:
    - Role badges dengan color coding
    - Status indicators (online/offline)
    - Task completion progress
    - Action buttons (edit, delete, view profile)
    - Hover effects dan micro-interactions
    - Avatar dengan fallback ke initials
    - Responsive design untuk semua screen sizes
    
    Styling menggunakan:
    - Glassmorphism dengan backdrop-blur
    - Gradient backgrounds untuk role badges
    - Smooth transitions dan hover effects
    - Shadow effects untuk depth
    - Professional color palette
--}}

<div class="bg-white/70 backdrop-blur-xl rounded-2xl border border-white/40 p-6 hover:bg-white/80 hover:shadow-xl hover:scale-105 transition-all duration-300 group">
    

    <!-- Card header dengan avatar dan status -->
    <div class="flex items-start justify-between mb-4">
        

        <!-- Avatar dan basic info -->
        <div class="flex items-center space-x-3">
            

            <!-- Avatar container dengan status indicator -->
            <div class="relative">
                

                <!-- Avatar dengan gradient background -->
                <div 
                    class="w-12 h-12 rounded-xl bg-gradient-to-r from-blue-500 to-indigo-500 flex items-center justify-center text-white font-semibold text-lg shadow-lg"
                    x-text="member.avatar"
                >
                </div>
                

                <!-- Status indicator (online/offline) -->
                <div 
                    class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full border-2 border-white shadow-sm"
                    :class="member.status === 'online' ? 'bg-green-500' : 'bg-gray-400'"
                    :title="member.status === 'online' ? 'Online' : 'Offline'"
                >
                </div>


            </div>
            

            <!-- Member basic info -->
            <div class="flex-1 min-w-0">
                

                <h3 
                    class="text-lg font-semibold text-gray-900 truncate group-hover:text-blue-600 transition-colors duration-200"
                    x-text="member.name"
                >
                </h3>
                

                <p 
                    class="text-sm text-gray-600 truncate"
                    x-text="member.email"
                >
                </p>


            </div>


        </div>
        

        <!-- Quick actions dropdown -->
        <div class="relative" x-data="{ open: false }">
            

            <button 
                @click="open = !open"
                class="p-2 text-gray-400 hover:text-gray-600 hover:bg-white/50 rounded-lg transition-all duration-200 opacity-0 group-hover:opacity-100"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                        @click="$dispatch('edit-member-modal', member); open = false"
                        class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-200"
                    >
                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit Member
                    </button>
                    

                    <button class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-200">
                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        View Profile
                    </button>
                    

                    <button class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-200">
                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        Send Message
                    </button>
                    

                    <div class="border-t border-gray-200 my-1"></div>
                    

                    <button 
                        @click="$dispatch('delete-member-modal', member); open = false"
                        class="flex items-center w-full px-4 py-2 text-sm text-red-700 hover:bg-red-50 hover:text-red-600 transition-colors duration-200"
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



    <!-- Role badge dengan color coding -->
    <div class="mb-4">
        

        <span 
            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wide"
            :class="{
                'bg-purple-100 text-purple-800': member.role === 'team lead',
                'bg-blue-100 text-blue-800': member.role === 'developer', 
                'bg-pink-100 text-pink-800': member.role === 'designer'
            }"
            x-text="member.role"
        >
        </span>


    </div>



    <!-- Member statistics -->
    <div class="space-y-4">
        

        <!-- Join date info -->
        <div class="flex items-center text-sm text-gray-600">
            

            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            

            <span>
                Joined <span x-text="new Date(member.joinedAt).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })"></span>
            </span>


        </div>
        

        <!-- Task statistics -->
        <div class="flex items-center justify-between text-sm">
            

            <span class="text-gray-600">Tasks</span>
            

            <span class="font-semibold text-gray-900">
                <span x-text="member.completedTasks"></span>/<span x-text="member.tasksCount"></span>
            </span>


        </div>
        

        <!-- Progress bar untuk task completion -->
        <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
            

            <div 
                class="h-2 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-full transition-all duration-500 ease-out"
                :style="`width: ${(member.completedTasks / member.tasksCount * 100)}%`"
            >
            </div>


        </div>
        

        <!-- Progress percentage -->
        <div class="text-right">
            

            <span 
                class="text-xs font-medium"
                :class="{
                    'text-green-600': (member.completedTasks / member.tasksCount) >= 0.8,
                    'text-yellow-600': (member.completedTasks / member.tasksCount) >= 0.5 && (member.completedTasks / member.tasksCount) < 0.8,
                    'text-red-600': (member.completedTasks / member.tasksCount) < 0.5
                }"
                x-text="`${Math.round(member.completedTasks / member.tasksCount * 100)}% Complete`"
            >
            </span>


        </div>


    </div>



    <!-- Card footer dengan action buttons -->
    <div class="mt-6 pt-4 border-t border-white/30">
        

        <div class="flex items-center justify-between">
            

            <!-- Contact actions -->
            <div class="flex items-center space-x-2">
                

                <!-- Email button -->
                <button 
                    :title="`Email ${member.name}`"
                    class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </button>
                

                <!-- Message button -->
                <button 
                    :title="`Message ${member.name}`"
                    class="p-2 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition-all duration-200"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </button>


            </div>
            

            <!-- Primary action button -->
            <button 
                @click="$dispatch('view-member-profile', member)"
                class="flex items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600 text-white text-sm font-medium rounded-lg shadow-md hover:shadow-lg transition-all duration-200 transform hover:scale-105"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                View Profile
            </button>


        </div>


    </div>


</div>