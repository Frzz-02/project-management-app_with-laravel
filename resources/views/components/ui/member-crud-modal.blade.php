{{--
    Member CRUD Modal Component
    
    Modal komprehensif untuk Create, Read, Update, Delete project members.
    Menggunakan Alpine.js untuk state management dan form handling.
    
    Features yang tersedia:
    - Add new member form dengan semua field required
    - Edit existing member functionality  
    - Delete confirmation modal
    - Form validation real-time
    - User search/selection untuk add member
    - Role assignment dengan descriptions
    - Smooth animations dan transitions
    - Responsive design
    - Error handling dan success feedback
    
    Fields yang dihandle (sesuai ProjectMember model):
    - project_id (hidden, dari context)
    - user_id (searchable dropdown)
    - role (dropdown: team lead, developer, designer) 
    - joined_at (auto-set untuk new members)
    
    Styling menggunakan:
    - Glassmorphism effects dengan backdrop-blur
    - Modern form styling
    - Professional color coding untuk roles
    - Smooth transitions untuk semua interactions
    - Advanced form layouts dengan proper spacing
--}}

<div 
    x-data="{
        // Modal state management
        showAddModal: false,
        showEditModal: false,
        showDeleteModal: false,
        
        // Form mode (create/edit)
        formMode: 'create',
        
        // Loading states
        isLoading: false,
        isSearchingUsers: false,
        
        // Form data sesuai ProjectMember fillable fields
        formData: {
            project_id: 1, // TODO: Get from project context
            user_id: null,
            role: 'developer',
            joined_at: null
        },
        
        // Edit target member
        editingMember: null,
        
        // Delete target member  
        deletingMember: null,
        
        // Form validation errors
        errors: {},
        
        // User search untuk add member
        userSearchQuery: '',
        searchResults: [],
        selectedUser: null,
        showUserDropdown: false,
        
        // Dummy users untuk search (nanti diganti dengan API call)
        availableUsers: [
            { id: 1, name: 'Alice Johnson', email: 'alice@company.com', avatar: 'AJ' },
            { id: 2, name: 'Bob Smith', email: 'bob@company.com', avatar: 'BS' },
            { id: 3, name: 'Carol Wilson', email: 'carol@company.com', avatar: 'CW' },
            { id: 4, name: 'David Brown', email: 'david@company.com', avatar: 'DB' },
            { id: 5, name: 'Eva Davis', email: 'eva@company.com', avatar: 'ED' }
        ],
        
        // Role descriptions untuk help text
        roleDescriptions: {
            'team lead': 'Can manage project settings, assign tasks, and oversee team progress',
            'developer': 'Can create and edit tasks, update task status, and collaborate on development',
            'designer': 'Can create and edit design tasks, upload assets, and review design work'
        },
        
        // Computed property untuk filtered users
        get filteredUsers() {
            if (!this.userSearchQuery) return this.availableUsers.slice(0, 5);
            
            return this.availableUsers.filter(user =>
                user.name.toLowerCase().includes(this.userSearchQuery.toLowerCase()) ||
                user.email.toLowerCase().includes(this.userSearchQuery.toLowerCase())
            ).slice(0, 5);
        },
        
        // Reset form ke state awal
        resetForm() {
            this.formData = {
                project_id: 1,
                user_id: null,
                role: 'developer',
                joined_at: null
            };
            this.selectedUser = null;
            this.userSearchQuery = '';
            this.errors = {};
            this.isLoading = false;
            this.showUserDropdown = false;
        },
        
        // Open add member modal
        openAddModal() {
            this.formMode = 'create';
            this.resetForm();
            this.showAddModal = true;
            this.$nextTick(() => {
                this.$refs.userSearchInput?.focus();
            });
        },
        
        // Open edit member modal
        openEditModal(member) {
            this.formMode = 'edit';
            this.editingMember = member;
            this.formData = {
                project_id: member.project_id || 1,
                user_id: member.user_id || member.id,
                role: member.role,
                joined_at: member.joined_at || member.joinedAt
            };
            this.selectedUser = {
                id: member.user_id || member.id,
                name: member.name,
                email: member.email,
                avatar: member.avatar
            };
            this.userSearchQuery = member.name;
            this.errors = {};
            this.showEditModal = true;
        },
        
        // Open delete confirmation modal
        openDeleteModal(member) {
            this.deletingMember = member;
            this.showDeleteModal = true;
        },
        
        // Close all modals
        closeAllModals() {
            this.showAddModal = false;
            this.showEditModal = false; 
            this.showDeleteModal = false;
            setTimeout(() => this.resetForm(), 300);
        },
        
        // Select user dari search results
        selectUser(user) {
            this.selectedUser = user;
            this.formData.user_id = user.id;
            this.userSearchQuery = user.name;
            this.showUserDropdown = false;
            this.errors.user_id = null;
        },
        
        // Form validation
        validateForm() {
            this.errors = {};
            
            // Validate user selection
            if (!this.selectedUser) {
                this.errors.user_id = 'Please select a team member';
            }
            
            // Validate role
            if (!this.formData.role) {
                this.errors.role = 'Please select a role';
            }
            
            return Object.keys(this.errors).length === 0;
        },
        
        // Submit form (create/edit)
        submitForm() {
            if (!this.validateForm()) return;
            
            this.isLoading = true;
            
            // Set joined_at untuk new members
            if (this.formMode === 'create') {
                this.formData.joined_at = new Date().toISOString();
            }
            
            // Simulate API call
            setTimeout(() => {
                console.log('Form submitted:', this.formData);
                this.isLoading = false;
                this.closeAllModals();
                
                // TODO: Show success notification
                // TODO: Refresh member list
            }, 1500);
        },
        
        // Delete member
        deleteMember() {
            this.isLoading = true;
            
            // Simulate API call
            setTimeout(() => {
                console.log('Member deleted:', this.deletingMember);
                this.isLoading = false;
                this.closeAllModals();
                
                // TODO: Show success notification
                // TODO: Refresh member list
            }, 1000);
        }
    }"
    @add-member-modal.window="openAddModal()"
    @edit-member-modal.window="openEditModal($event.detail)"
    @delete-member-modal.window="openDeleteModal($event.detail)"
>


    <!-- Add/Edit Member Modal -->
    <div 
        x-show="showAddModal || showEditModal"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 overflow-y-auto"
        style="display: none;"
        @keydown.escape.window="closeAllModals()"
    >
        

        <!-- Background overlay -->
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            

            <div 
                class="fixed inset-0 transition-opacity bg-gray-900/50 backdrop-blur-sm"
                @click="closeAllModals()"
            ></div>
            

            <!-- Modal content -->
            <div 
                x-show="showAddModal || showEditModal"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="inline-block w-full max-w-2xl my-8 overflow-hidden text-left align-middle transition-all transform bg-white/90 backdrop-blur-xl shadow-2xl rounded-2xl border border-white/40"
            >
                

                <!-- Modal Header -->
                <div class="flex items-center justify-between p-6 border-b border-white/30">
                    

                    <div class="flex items-center">
                        

                        <!-- Icon -->
                        <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-xl flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                        </div>
                        

                        <div>
                            <h3 class="text-xl font-semibold text-gray-900" x-text="formMode === 'create' ? 'Add Team Member' : 'Edit Team Member'"></h3>
                            <p class="text-sm text-gray-600 mt-1" x-text="formMode === 'create' ? 'Add a new member to your project team' : 'Update member information and role'"></p>
                        </div>


                    </div>
                    

                    <!-- Close button -->
                    <button 
                        @click="closeAllModals()"
                        class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors duration-200"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>


                </div>
                

                <!-- Modal Body -->
                <form @submit.prevent="submitForm()" class="p-6 space-y-6">
                    

                    <!-- User Selection Field -->
                    <div class="space-y-2">
                        

                        <label class="block text-sm font-medium text-gray-700">
                            Team Member
                            <span class="text-red-500">*</span>
                        </label>
                        

                        <div class="relative">
                            

                            <!-- Search input -->
                            <div class="relative">
                                

                                <input 
                                    type="text"
                                    x-ref="userSearchInput"
                                    x-model="userSearchQuery"
                                    @focus="showUserDropdown = true"
                                    @input="showUserDropdown = true"
                                    placeholder="Search for team members..."
                                    class="w-full px-4 py-3 pl-12 pr-4 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                    :class="{ 'border-red-300 focus:ring-red-500': errors.user_id }"
                                    :readonly="formMode === 'edit'"
                                >
                                

                                <!-- Search icon -->
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                </div>
                                

                                <!-- Selected user badge -->
                                <div 
                                    x-show="selectedUser" 
                                    class="absolute inset-y-0 right-0 flex items-center pr-3"
                                >
                                    <div class="flex items-center space-x-2 bg-blue-50 rounded-lg px-3 py-1">
                                        <div 
                                            class="w-6 h-6 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-lg flex items-center justify-center text-white text-xs font-semibold"
                                            x-text="selectedUser?.avatar"
                                        ></div>
                                        <span class="text-sm font-medium text-blue-700" x-text="selectedUser?.name"></span>
                                    </div>
                                </div>


                            </div>
                            

                            <!-- Search results dropdown -->
                            <div 
                                x-show="showUserDropdown && filteredUsers.length > 0 && formMode === 'create'"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                @click.away="showUserDropdown = false"
                                class="absolute z-10 w-full mt-2 bg-white/90 backdrop-blur-xl border border-white/40 rounded-xl shadow-lg max-h-60 overflow-auto"
                            >
                                

                                <div class="p-2">
                                    

                                    <template x-for="user in filteredUsers" :key="user.id">
                                        

                                        <button 
                                            type="button"
                                            @click="selectUser(user)"
                                            class="w-full flex items-center space-x-3 p-3 hover:bg-blue-50 rounded-lg transition-colors duration-200 text-left"
                                        >
                                            

                                            <div 
                                                class="w-8 h-8 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-lg flex items-center justify-center text-white text-sm font-semibold"
                                                x-text="user.avatar"
                                            ></div>
                                            

                                            <div class="flex-1">
                                                <p class="text-sm font-medium text-gray-900" x-text="user.name"></p>
                                                <p class="text-xs text-gray-600" x-text="user.email"></p>
                                            </div>


                                        </button>


                                    </template>


                                </div>


                            </div>


                        </div>
                        

                        <!-- Error message -->
                        <p x-show="errors.user_id" x-text="errors.user_id" class="text-sm text-red-600"></p>
                        

                        <!-- Help text -->
                        <p class="text-xs text-gray-500">
                            <span x-show="formMode === 'create'">Search and select a user to add to this project</span>
                            <span x-show="formMode === 'edit'">Member information cannot be changed in edit mode</span>
                        </p>


                    </div>



                    <!-- Role Selection Field -->
                    <div class="space-y-2">
                        

                        <label class="block text-sm font-medium text-gray-700">
                            Role
                            <span class="text-red-500">*</span>
                        </label>
                        

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            

                            <!-- Team Lead option -->
                            <label class="relative cursor-pointer">
                                

                                <input 
                                    type="radio" 
                                    name="role" 
                                    value="team lead"
                                    x-model="formData.role"
                                    class="sr-only"
                                >
                                

                                <div 
                                    class="p-4 border-2 rounded-xl transition-all duration-200"
                                    :class="formData.role === 'team lead' ? 'border-purple-500 bg-purple-50' : 'border-gray-200 bg-white/50 hover:border-purple-300'"
                                >
                                    

                                    <div class="flex items-center justify-between mb-2">
                                        

                                        <div class="flex items-center">
                                            

                                            <div class="w-8 h-8 bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg flex items-center justify-center mr-3">
                                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </div>
                                            

                                            <span class="font-semibold text-gray-900">Team Lead</span>


                                        </div>
                                        

                                        <div 
                                            class="w-4 h-4 rounded-full border-2 flex items-center justify-center"
                                            :class="formData.role === 'team lead' ? 'border-purple-500 bg-purple-500' : 'border-gray-300'"
                                        >
                                            <div 
                                                x-show="formData.role === 'team lead'"
                                                class="w-2 h-2 bg-white rounded-full"
                                            ></div>
                                        </div>


                                    </div>
                                    

                                    <p class="text-xs text-gray-600" x-text="roleDescriptions['team lead']"></p>


                                </div>


                            </label>
                            

                            <!-- Developer option -->
                            <label class="relative cursor-pointer">
                                

                                <input 
                                    type="radio" 
                                    name="role" 
                                    value="developer"
                                    x-model="formData.role"
                                    class="sr-only"
                                >
                                

                                <div 
                                    class="p-4 border-2 rounded-xl transition-all duration-200"
                                    :class="formData.role === 'developer' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 bg-white/50 hover:border-blue-300'"
                                >
                                    

                                    <div class="flex items-center justify-between mb-2">
                                        

                                        <div class="flex items-center">
                                            

                                            <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg flex items-center justify-center mr-3">
                                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                                                </svg>
                                            </div>
                                            

                                            <span class="font-semibold text-gray-900">Developer</span>


                                        </div>
                                        

                                        <div 
                                            class="w-4 h-4 rounded-full border-2 flex items-center justify-center"
                                            :class="formData.role === 'developer' ? 'border-blue-500 bg-blue-500' : 'border-gray-300'"
                                        >
                                            <div 
                                                x-show="formData.role === 'developer'"
                                                class="w-2 h-2 bg-white rounded-full"
                                            ></div>
                                        </div>


                                    </div>
                                    

                                    <p class="text-xs text-gray-600" x-text="roleDescriptions['developer']"></p>


                                </div>


                            </label>
                            

                            <!-- Designer option -->
                            <label class="relative cursor-pointer">
                                

                                <input 
                                    type="radio" 
                                    name="role" 
                                    value="designer"
                                    x-model="formData.role"
                                    class="sr-only"
                                >
                                

                                <div 
                                    class="p-4 border-2 rounded-xl transition-all duration-200"
                                    :class="formData.role === 'designer' ? 'border-pink-500 bg-pink-50' : 'border-gray-200 bg-white/50 hover:border-pink-300'"
                                >
                                    

                                    <div class="flex items-center justify-between mb-2">
                                        

                                        <div class="flex items-center">
                                            

                                            <div class="w-8 h-8 bg-gradient-to-r from-pink-500 to-pink-600 rounded-lg flex items-center justify-center mr-3">
                                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v1m0 0h6V5a2 2 0 012-2h4a2 2 0 012 2v1m0 0v10a4 4 0 01-4 4H7"/>
                                                </svg>
                                            </div>
                                            

                                            <span class="font-semibold text-gray-900">Designer</span>


                                        </div>
                                        

                                        <div 
                                            class="w-4 h-4 rounded-full border-2 flex items-center justify-center"
                                            :class="formData.role === 'designer' ? 'border-pink-500 bg-pink-500' : 'border-gray-300'"
                                        >
                                            <div 
                                                x-show="formData.role === 'designer'"
                                                class="w-2 h-2 bg-white rounded-full"
                                            ></div>
                                        </div>


                                    </div>
                                    

                                    <p class="text-xs text-gray-600" x-text="roleDescriptions['designer']"></p>


                                </div>


                            </label>


                        </div>
                        

                        <!-- Error message -->
                        <p x-show="errors.role" x-text="errors.role" class="text-sm text-red-600"></p>


                    </div>



                    <!-- Form Actions -->
                    <div class="flex items-center justify-end space-x-4 pt-6 border-t border-white/30">
                        

                        <!-- Cancel button -->
                        <button 
                            type="button"
                            @click="closeAllModals()"
                            class="px-6 py-3 text-gray-700 bg-white/50 border border-gray-300 rounded-xl hover:bg-white/70 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200 font-medium"
                            :disabled="isLoading"
                        >
                            Cancel
                        </button>
                        

                        <!-- Submit button -->
                        <button 
                            type="submit"
                            class="px-8 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
                            :disabled="isLoading"
                        >
                            

                            <!-- Loading spinner -->
                            <svg 
                                x-show="isLoading" 
                                class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" 
                                fill="none" 
                                viewBox="0 0 24 24"
                            >
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            

                            <!-- Button text -->
                            <span x-show="!isLoading" x-text="formMode === 'create' ? 'Add Member' : 'Update Member'"></span>
                            <span x-show="isLoading" x-text="formMode === 'create' ? 'Adding...' : 'Updating...'"></span>


                        </button>


                    </div>


                </form>


            </div>


        </div>


    </div>



    <!-- Delete Confirmation Modal -->
    <div 
        x-show="showDeleteModal"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 overflow-y-auto"
        style="display: none;"
        @keydown.escape.window="closeAllModals()"
    >
        

        <!-- Background overlay -->
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            

            <div 
                class="fixed inset-0 transition-opacity bg-gray-900/50 backdrop-blur-sm"
                @click="closeAllModals()"
            ></div>
            

            <!-- Modal content -->
            <div 
                x-show="showDeleteModal"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="inline-block w-full max-w-md my-8 overflow-hidden text-left align-middle transition-all transform bg-white/90 backdrop-blur-xl shadow-2xl rounded-2xl border border-white/40"
            >
                

                <!-- Warning icon dan header -->
                <div class="p-6 text-center">
                    

                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.732 15.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                    </div>
                    

                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Remove Team Member</h3>
                    

                    <p class="text-gray-600 mb-2">
                        Are you sure you want to remove 
                        <span class="font-semibold" x-text="deletingMember?.name"></span> 
                        from this project?
                    </p>
                    

                    <p class="text-sm text-gray-500">
                        This action cannot be undone. The member will lose access to this project and all related data.
                    </p>


                </div>
                

                <!-- Action buttons -->
                <div class="flex items-center justify-end space-x-3 px-6 py-4 bg-gray-50/50 border-t border-white/30">
                    

                    <!-- Cancel button -->
                    <button 
                        @click="closeAllModals()"
                        class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200 font-medium"
                        :disabled="isLoading"
                    >
                        Cancel
                    </button>
                    

                    <!-- Delete button -->
                    <button 
                        @click="deleteMember()"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
                        :disabled="isLoading"
                    >
                        

                        <!-- Loading spinner -->
                        <svg 
                            x-show="isLoading" 
                            class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" 
                            fill="none" 
                            viewBox="0 0 24 24"
                        >
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        

                        <span x-show="!isLoading">Remove Member</span>
                        <span x-show="isLoading">Removing...</span>


                    </button>


                </div>


            </div>


        </div>


    </div>


</div>