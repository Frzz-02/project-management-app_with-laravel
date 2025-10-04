@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div x-data="{
    activeTab: 'overview',
    showQuickActions: false,
    projectProgress: [
        { name: 'E-commerce Redesign', progress: 75, status: 'In Progress', team: 5, dueDate: '2024-02-15' },
        { name: 'Mobile App Development', progress: 45, status: 'In Progress', team: 8, dueDate: '2024-03-01' },
        { name: 'Marketing Website', progress: 90, status: 'Review', team: 3, dueDate: '2024-01-30' }
    ],
    recentTasks: [
        { title: 'Design login screen', project: 'Mobile App', priority: 'High', status: 'In Progress', assignee: 'Sarah Wilson' },
        { title: 'Fix payment gateway bug', project: 'E-commerce', priority: 'Critical', status: 'To Do', assignee: 'Mike Johnson' },
        { title: 'Update user documentation', project: 'Marketing', priority: 'Medium', status: 'Done', assignee: 'Alex Chen' },
        { title: 'Code review for API endpoints', project: 'Mobile App', priority: 'High', status: 'In Progress', assignee: 'John Doe' }
    ]
}" class="p-6 space-y-6">

    <!-- Welcome Header dengan animasi -->
    <div class="bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-600 rounded-2xl shadow-xl p-8 text-white relative overflow-hidden">
        <div class="absolute inset-0 bg-black opacity-10"></div>
        <div class="relative z-10">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold mb-2" x-data x-init="$el.style.opacity = '0'; $el.style.transform = 'translateY(20px)'; setTimeout(() => { $el.style.transition = 'all 0.6s ease-out'; $el.style.opacity = '1'; $el.style.transform = 'translateY(0)'; }, 100)">
                        Good Morning, John! 👋
                    </h1>
                    <p class="text-blue-100 text-lg" x-data x-init="$el.style.opacity = '0'; setTimeout(() => { $el.style.transition = 'opacity 0.6s ease-out'; $el.style.opacity = '1'; }, 300)">
                        You have 12 tasks due this week. Let's get things done!
                    </p>
                </div>
                <div class="hidden md:block">
                    <div class="bg-white/20 rounded-xl p-6">
                        <div class="text-right">
                            <p class="text-sm text-blue-100 mb-1">Today's Progress</p>
                            <p class="text-2xl font-bold">67%</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Decorative elements -->
        <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full"></div>
        <div class="absolute -bottom-6 -left-6 w-32 h-32 bg-white/5 rounded-full"></div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Card 1: Active Projects -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Active Projects</p>
                    <p class="text-3xl font-bold text-gray-900">12</p>
                    <p class="text-sm text-green-600 flex items-center mt-2">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                        </svg>
                        +2 this month
                    </p>
                </div>
                <div class="h-12 w-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Card 2: Completed Tasks -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Completed Tasks</p>
                    <p class="text-3xl font-bold text-gray-900">248</p>
                    <p class="text-sm text-green-600 flex items-center mt-2">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        85% completion rate
                    </p>
                </div>
                <div class="h-12 w-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Card 3: Team Members -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Team Members</p>
                    <p class="text-3xl font-bold text-gray-900">24</p>
                    <p class="text-sm text-blue-600 flex items-center mt-2">
                        <div class="w-4 h-4 bg-green-500 rounded-full mr-1"></div>
                        18 online now
                    </p>
                </div>
                <div class="h-12 w-12 bg-purple-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Card 4: Pending Reviews -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Pending Reviews</p>
                    <p class="text-3xl font-bold text-gray-900">7</p>
                    <p class="text-sm text-orange-600 flex items-center mt-2">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        3 urgent
                    </p>
                </div>
                <div class="h-12 w-12 bg-orange-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-3.5-7.5L15 17zm1.5-9.5L15 17h2l1.5-9.5h-2z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="border-b border-gray-200">
            <nav class="flex space-x-8 px-6" aria-label="Tabs">
                <button @click="activeTab = 'overview'" 
                        :class="activeTab === 'overview' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    Overview
                </button>
                <button @click="activeTab = 'projects'" 
                        :class="activeTab === 'projects' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    Recent Projects
                </button>
                <button @click="activeTab = 'tasks'" 
                        :class="activeTab === 'tasks' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    My Tasks
                </button>
            </nav>
        </div>

        <!-- Tab Content -->
        <div class="p-6">
            <!-- Overview Tab -->
            <div x-show="activeTab === 'overview'" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform translate-x-4"
                 x-transition:enter-end="opacity-100 transform translate-x-0">
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Project Progress -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Project Progress</h3>
                        <div class="space-y-4">
                            <template x-for="project in projectProgress" :key="project.name">
                                <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors">
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="font-medium text-gray-900" x-text="project.name"></h4>
                                        <span class="text-sm font-medium text-gray-600" x-text="project.progress + '%'"></span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2 mb-3">
                                        <div class="bg-gradient-to-r from-blue-500 to-purple-500 h-2 rounded-full transition-all duration-500" 
                                             :style="'width: ' + project.progress + '%'"></div>
                                    </div>
                                    <div class="flex items-center justify-between text-sm text-gray-600">
                                        <span class="flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                            </svg>
                                            <span x-text="project.team + ' members'"></span>
                                        </span>
                                        <span x-text="'Due ' + project.dueDate"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Activity Feed -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Activity</h3>
                        <div class="space-y-4">
                            <div class="flex items-start space-x-3">
                                <div class="h-8 w-8 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm text-gray-900">
                                        <span class="font-medium">Sarah Wilson</span> completed task 
                                        <span class="font-medium">User Authentication</span>
                                    </p>
                                    <p class="text-xs text-gray-500">2 minutes ago</p>
                                </div>
                            </div>

                            <div class="flex items-start space-x-3">
                                <div class="h-8 w-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm text-gray-900">
                                        <span class="font-medium">Mike Johnson</span> added a comment to 
                                        <span class="font-medium">Payment Gateway</span>
                                    </p>
                                    <p class="text-xs text-gray-500">15 minutes ago</p>
                                </div>
                            </div>

                            <div class="flex items-start space-x-3">
                                <div class="h-8 w-8 bg-purple-100 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm text-gray-900">
                                        New project <span class="font-medium">Mobile App v2.0</span> was created
                                    </p>
                                    <p class="text-xs text-gray-500">1 hour ago</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Projects Tab -->
            <div x-show="activeTab === 'projects'" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform translate-x-4"
                 x-transition:enter-end="opacity-100 transform translate-x-0">
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <template x-for="project in projectProgress" :key="project.name">
                        <div class="bg-gradient-to-br from-white to-gray-50 rounded-xl border border-gray-200 p-6 hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="font-semibold text-gray-900" x-text="project.name"></h3>
                                <span class="px-2 py-1 text-xs font-medium rounded-full"
                                      :class="project.status === 'In Progress' ? 'bg-blue-100 text-blue-800' : 
                                              project.status === 'Review' ? 'bg-orange-100 text-orange-800' : 
                                              'bg-green-100 text-green-800'"
                                      x-text="project.status"></span>
                            </div>
                            
                            <div class="mb-4">
                                <div class="flex justify-between text-sm text-gray-600 mb-2">
                                    <span>Progress</span>
                                    <span x-text="project.progress + '%'"></span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-gradient-to-r from-blue-500 to-purple-500 h-2 rounded-full" 
                                         :style="'width: ' + project.progress + '%'"></div>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between text-sm text-gray-600">
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                    </svg>
                                    <span x-text="project.team"></span>
                                </span>
                                <span x-text="project.dueDate"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Tasks Tab -->
            <div x-show="activeTab === 'tasks'" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform translate-x-4"
                 x-transition:enter-end="opacity-100 transform translate-x-0">
                
                <div class="space-y-3">
                    <template x-for="task in recentTasks" :key="task.title">
                        <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-all duration-200 hover:border-gray-300">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3">
                                        <h4 class="font-medium text-gray-900" x-text="task.title"></h4>
                                        <span class="px-2 py-1 text-xs font-medium rounded-full"
                                              :class="task.priority === 'Critical' ? 'bg-red-100 text-red-800' :
                                                      task.priority === 'High' ? 'bg-orange-100 text-orange-800' :
                                                      task.priority === 'Medium' ? 'bg-yellow-100 text-yellow-800' :
                                                      'bg-gray-100 text-gray-800'"
                                              x-text="task.priority"></span>
                                    </div>
                                    <div class="flex items-center space-x-4 mt-2 text-sm text-gray-600">
                                        <span x-text="task.project"></span>
                                        <span>•</span>
                                        <span x-text="'Assigned to ' + task.assignee"></span>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="px-3 py-1 text-sm font-medium rounded-full"
                                          :class="task.status === 'Done' ? 'bg-green-100 text-green-800' :
                                                  task.status === 'In Progress' ? 'bg-blue-100 text-blue-800' :
                                                  'bg-gray-100 text-gray-800'"
                                          x-text="task.status"></span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions Floating Button -->
    <div class="fixed bottom-6 right-6 z-50">
        <div x-show="showQuickActions" 
             x-transition:enter="transition ease-out duration-200 transform"
             x-transition:enter-start="opacity-0 scale-95 translate-y-2"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150 transform"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-2"
             class="mb-4 space-y-2">
            
            <a href="" class="flex items-center space-x-3 bg-white rounded-lg shadow-lg px-4 py-3 hover:shadow-xl transition-all duration-200 hover:-translate-y-0.5">
                <div class="h-8 w-8 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                </div>
                <span class="text-gray-900 font-medium">New Task</span>
            </a>
            
            <a href="{{ route('projects.create') }}" class="flex items-center space-x-3 bg-white rounded-lg shadow-lg px-4 py-3 hover:shadow-xl transition-all duration-200 hover:-translate-y-0.5">
                <div class="h-8 w-8 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
                <span class="text-gray-900 font-medium">New Project</span>
            </a>
        </div>
        
        <button @click="showQuickActions = !showQuickActions" 
                class="h-14 w-14 bg-gradient-to-r from-blue-600 to-purple-600 rounded-full shadow-lg flex items-center justify-center text-white hover:shadow-xl transition-all duration-200 hover:scale-105">
            <svg class="w-6 h-6 transition-transform duration-200" 
                 :class="showQuickActions ? 'rotate-45' : 'rotate-0'" 
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
        </button>
    </div>
</div>
@endsection