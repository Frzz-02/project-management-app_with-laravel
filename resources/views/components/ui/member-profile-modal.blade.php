{{--
    Member Profile Modal Component (View Only)
    
    Komponen modal untuk menampilkan detail lengkap profil member dalam project.
    Modal ini hanya untuk viewing, tidak ada CRUD operations.
    
    Features yang tersedia:
    - Header dengan avatar dan informasi dasar member
    - Statistics cards (join date, total tasks, completion rate)
    - Task progress visualization dengan progress bar
    - Contact information section
    - Close button untuk menutup modal
    
    Props yang diterima:
    - Data member diakses melalui 'selectedMember' dari parent scope
    
    Alpine.js Integration:
    - Hanya untuk UI interactions (visibility, transitions)
    - No data manipulation, hanya display
--}}

<!-- Member Profile Modal -->
<div 
    x-show="showProfileModal"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 overflow-y-auto"
    style="display: none;"
    @keydown.escape.window="closeProfileModal()"
>
    <!-- Background overlay -->
    <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div 
            class="fixed inset-0 transition-opacity bg-gray-900/50 backdrop-blur-sm"
            @click="closeProfileModal()"
        ></div>

        <!-- This spacing div helps center the modal -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <!-- Modal content -->
        <div 
            x-show="showProfileModal"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"  
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="inline-block w-full max-w-2xl p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-2xl sm:align-middle"
        >


            <!-- Modal Header -->
            <div class="relative bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-8 text-white">
                <!-- Background pattern -->
                <div class="absolute inset-0 bg-black/10"></div>
                

                <!-- Tombol close di pojok kanan atas -->
                <!-- @click event memanggil function closeProfileModal() -->
                <button 
                    @click="closeProfileModal()"
                    class="absolute top-4 right-4 p-2 text-white/80 hover:text-white hover:bg-white/20 rounded-lg transition-colors duration-200"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
                

                <!-- Konten header utama dengan avatar dan info member -->
                <div class="relative flex items-center space-x-6">
                    

                    <!-- Avatar besar dengan status indicator -->
                    <div class="relative">
                        

                        <!-- Avatar circle dengan initials -->
                        <!-- x-text menampilkan avatar initials dari data selectedMember -->
                        <!-- selectedMember?.avatar menggunakan optional chaining untuk avoid error -->
                        <div 
                            class="w-20 h-20 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center text-white font-bold text-2xl shadow-xl border border-white/30"
                            x-text="selectedMember?.avatar"
                        >
                        </div>
                        

                        <!-- Status indicator dot (online/offline) -->
                        <!-- :class binding memberikan warna berbeda berdasarkan status -->
                        <!-- Conditional class: hijau jika online, abu-abu jika offline -->
                        <div 
                            class="absolute -bottom-2 -right-2 w-6 h-6 rounded-full border-4 border-white shadow-lg"
                            :class="selectedMember?.status === 'online' ? 'bg-green-500' : 'bg-gray-400'"
                        >
                        </div>


                    </div>
                    

                    <!-- Informasi member (nama, email, role) -->
                    <div class="flex-1">
                        

                        <!-- Nama member -->
                        <!-- x-text mengambil nama dari selectedMember object -->
                        <h2 
                            class="text-2xl font-bold text-white mb-1"
                            x-text="selectedMember?.name"
                        >
                        </h2>
                        

                        <!-- Email member -->
                        <!-- x-text menampilkan email address -->
                        <p 
                            class="text-blue-100 mb-2"
                            x-text="selectedMember?.email"
                        >
                        </p>
                        

                        <!-- Role badge dengan conditional styling -->
                        <!-- :class object binding memberikan warna berbeda untuk setiap role -->
                        <span 
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold"
                            :class="{
                                'bg-purple-100 text-purple-800': selectedMember?.role === 'team lead',
                                'bg-blue-100 text-blue-800': selectedMember?.role === 'developer', 
                                'bg-pink-100 text-pink-800': selectedMember?.role === 'designer'
                            }"
                            x-text="selectedMember?.role"
                        >
                        </span>


                    </div>


                </div>


            </div>
            

            <!-- Modal Body -->
            <div class="p-6 space-y-6">
                

                <!-- Member Statistics -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    

                    <!-- Join Date Card -->
                    <!-- Menampilkan tanggal bergabung member -->
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-4 border border-blue-100">
                        

                        <div class="flex items-center mb-2">
                            <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <h3 class="font-semibold text-gray-900">Joined Date</h3>
                        </div>
                        

                        <!-- Tanggal bergabung dengan format yang readable -->
                        <!-- Menggunakan JavaScript Date object untuk formatting -->
                        <p 
                            class="text-lg font-bold text-blue-600"
                            x-text="selectedMember ? new Date(selectedMember.joinedAt).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' }) : ''"
                        >
                        </p>
                        

                        <!-- Menampilkan berapa hari yang lalu member bergabung -->
                        <!-- Perhitungan matematika untuk menghitung selisih hari -->
                        <p 
                            class="text-sm text-gray-600 mt-1"
                            x-text="selectedMember ? `${Math.floor((Date.now() - new Date(selectedMember.joinedAt)) / (1000 * 60 * 60 * 24))} days ago` : ''"
                        >
                        </p>


                    </div>
                    

                    <!-- Tasks Statistics Card -->
                    <!-- Menampilkan total tasks yang assigned ke member -->
                    <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl p-4 border border-green-100">
                        

                        <div class="flex items-center mb-2">
                            <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <h3 class="font-semibold text-gray-900">Total Tasks</h3>
                        </div>
                        

                        <!-- Jumlah total tasks -->
                        <!-- || 0 memberikan fallback value jika data kosong -->
                        <p 
                            class="text-lg font-bold text-green-600"
                            x-text="selectedMember?.tasksCount || 0"
                        >
                        </p>
                        

                        <p class="text-sm text-gray-600 mt-1">Assigned tasks</p>


                    </div>
                    

                    <!-- Completion Rate Card -->
                    <!-- Menampilkan persentase tasks yang sudah completed -->
                    <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl p-4 border border-purple-100">
                        

                        <div class="flex items-center mb-2">
                            <svg class="w-5 h-5 text-purple-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <h3 class="font-semibold text-gray-900">Completion Rate</h3>
                        </div>
                        

                        <!-- Persentase completion rate -->
                        <!-- Math.round() membulatkan angka decimal -->
                        <!-- Rumus: (completed / total) * 100 -->
                        <p 
                            class="text-lg font-bold text-purple-600"
                            x-text="selectedMember ? `${Math.round(selectedMember.completedTasks / selectedMember.tasksCount * 100)}%` : '0%'"
                        >
                        </p>
                        

                        <!-- Detail completed vs total tasks -->
                        <p 
                            class="text-sm text-gray-600 mt-1"
                            x-text="selectedMember ? `${selectedMember.completedTasks}/${selectedMember.tasksCount} completed` : ''"
                        >
                        </p>


                    </div>


                </div>
                

                <!-- Task Progress Section -->
                <div class="bg-gray-50 rounded-xl p-6">
                    

                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Task Progress</h3>
                    

                    <!-- Progress Bar Container -->
                    <div class="mb-4">
                        

                        <!-- Header progress bar dengan label dan persentase -->
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm text-gray-600">Progress</span>
                            
                            <!-- Persentase progress (sama seperti completion rate) -->
                            <span 
                                class="text-sm font-semibold text-gray-900"
                                x-text="selectedMember ? `${Math.round(selectedMember.completedTasks / selectedMember.tasksCount * 100)}%` : '0%'"
                            >
                            </span>
                        </div>
                        

                        <!-- Progress Bar Visual -->
                        <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                            
                            <!-- Bar yang terisi sesuai dengan progress -->
                            <!-- :style binding untuk mengatur width secara dynamic -->
                            <!-- Width dihitung berdasarkan persentase completion -->
                            <div 
                                class="h-3 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-full transition-all duration-1000 ease-out"
                                :style="selectedMember ? `width: ${(selectedMember.completedTasks / selectedMember.tasksCount * 100)}%` : 'width: 0%'"
                            >
                            </div>
                        </div>


                    </div>
                    

                    <!-- Task Breakdown Cards -->
                    <!-- Grid 2 kolom menampilkan completed vs remaining tasks -->
                    <div class="grid grid-cols-2 gap-4 text-center">
                        

                        <!-- Completed Tasks Card -->
                        <div class="bg-white rounded-lg p-4">
                            
                            <!-- Jumlah tasks yang sudah completed -->
                            <div 
                                class="text-2xl font-bold text-green-600"
                                x-text="selectedMember?.completedTasks || 0"
                            >
                            </div>
                            <div class="text-sm text-gray-600">Completed</div>
                        </div>
                        

                        <!-- Remaining Tasks Card -->
                        <div class="bg-white rounded-lg p-4">
                            
                            <!-- Perhitungan tasks yang belum selesai -->
                            <!-- Rumus: total tasks - completed tasks -->
                            <div 
                                class="text-2xl font-bold text-orange-600"
                                x-text="selectedMember ? (selectedMember.tasksCount - selectedMember.completedTasks) : 0"
                            >
                            </div>
                            <div class="text-sm text-gray-600">Remaining</div>
                        </div>


                    </div>


                </div>
                

                <!-- Contact Information -->
                <div class="bg-gray-50 rounded-xl p-6">
                    

                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Contact Information</h3>
                    

                    <div class="space-y-3">
                        

                        <!-- Email Row -->
                        <div class="flex items-center justify-between p-3 bg-white rounded-lg">
                            

                            <!-- Email dengan icon -->
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                
                                <!-- Email address member -->
                                <span 
                                    class="text-gray-900"
                                    x-text="selectedMember?.email"
                                >
                                </span>
                            </div>
                            

                            <!-- Send Email Button -->
                            <button class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                Send Email
                            </button>


                        </div>
                        

                        <!-- Status Row -->
                        <div class="flex items-center justify-between p-3 bg-white rounded-lg">
                            

                            <!-- Status dengan indicator dot -->
                            <div class="flex items-center">
                                
                                <!-- Status dot dengan conditional coloring -->
                                <div 
                                    class="w-3 h-3 rounded-full mr-3"
                                    :class="selectedMember?.status === 'online' ? 'bg-green-500' : 'bg-gray-400'"
                                >
                                </div>
                                <span class="text-gray-900">Status</span>
                            </div>
                            

                            <!-- Status text dengan conditional styling -->
                            <!-- Capitalize untuk membuat huruf pertama kapital -->
                            <span 
                                class="text-sm font-medium capitalize"
                                :class="selectedMember?.status === 'online' ? 'text-green-600' : 'text-gray-500'"
                                x-text="selectedMember?.status"
                            >
                            </span>


                        </div>


                    </div>


                </div>


            </div>
            

            <!-- Modal Footer dengan close button saja -->
            <div class="flex items-center justify-end px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-2xl">
                

                <!-- Close Button -->
                <!-- @click event memanggil function closeProfileModal() -->
                <button 
                    @click="closeProfileModal()"
                    class="px-6 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200 font-medium"
                >
                    Close
                </button>



            </div>


        </div>
    </div>
</div>