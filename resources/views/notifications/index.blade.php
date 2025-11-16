@extends('layouts.app')

@section('content')
<div x-data="notificationPageData()" x-init="loadNotifications(1)" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Notifications</h1>
        <p class="mt-2 text-sm text-gray-600">Manage your notifications and stay updated</p>
    </div>

    <!-- Filters & Actions -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <!-- Filter Tabs -->
            <div class="flex space-x-2">
                <button @click="changeFilter('all')" 
                        :class="filter === 'all' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    All
                    <span x-text="`(${pagination.total})`" class="ml-1"></span>
                </button>
                <button @click="changeFilter('unread')" 
                        :class="filter === 'unread' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    Unread
                </button>
                <button @click="changeFilter('read')" 
                        :class="filter === 'read' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    Read
                </button>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex space-x-2">
                <button @click="markAllAsRead()" 
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Mark All as Read
                </button>
                <button @click="deleteAllRead()" 
                        class="inline-flex items-center px-4 py-2 border border-red-300 rounded-lg text-sm font-medium text-red-700 bg-white hover:bg-red-50 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Delete All Read
                </button>
            </div>
        </div>
    </div>

    <!-- Loading State -->
    <div x-show="loading" class="flex justify-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>

    <!-- Notifications List -->
    <div x-show="!loading" class="space-y-3">
        <!-- Empty State -->
        <template x-if="notifications.length === 0">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-3.393-3.768a8.5 8.5 0 01-2.607-5.732V8a3 3 0 00-6 0v-.5c0 2.09-.753 4.034-2.01 5.732L5 17h5m5 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">No notifications</h3>
                <p class="mt-2 text-sm text-gray-500">You're all caught up! No notifications to show.</p>
            </div>
        </template>

        <!-- Notification Items -->
        <template x-for="notification in notifications" :key="notification.id">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-shadow"
                 :class="{ 'border-l-4 border-l-blue-500': !notification.is_read }">
                <div class="p-6">
                    <div class="flex items-start space-x-4">
                        <!-- Icon -->
                        <div class="flex-shrink-0">
                            <div class="h-12 w-12 rounded-full flex items-center justify-center text-2xl"
                                 :class="notification.color_class">
                                <span x-text="notification.icon"></span>
                            </div>
                        </div>
                        
                        <!-- Content -->
                        <div class="flex-1 min-w-0 cursor-pointer" @click="handleNotificationClick(notification)">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <p class="text-base font-semibold text-gray-900 mb-1" 
                                       :class="{ 'font-bold': !notification.is_read }"
                                       x-text="notification.title"></p>
                                    <p class="text-sm text-gray-600 mb-2" x-text="notification.message"></p>
                                    <div class="flex items-center space-x-4 text-xs text-gray-500">
                                        <span x-text="notification.time_ago"></span>
                                        <span x-show="!notification.is_read" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            Unread
                                        </span>
                                        <span x-show="notification.is_read" class="text-gray-400">
                                            Read
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Actions -->
                        <div class="flex-shrink-0 flex items-center space-x-2">
                            <button x-show="!notification.is_read"
                                    @click.stop="markAsRead(notification)"
                                    class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                    title="Mark as read">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </button>
                            <button @click.stop="deleteNotification(notification)"
                                    class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                    title="Delete">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- Pagination -->
    <div x-show="!loading && notifications.length > 0 && pagination.last_page > 1" 
         class="mt-6 flex justify-center">
        <div class="flex items-center space-x-2">
            <!-- Previous -->
            <button @click="loadNotifications(pagination.current_page - 1)"
                    :disabled="pagination.current_page === 1"
                    :class="pagination.current_page === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100'"
                    class="px-3 py-2 rounded-lg border border-gray-300 text-sm font-medium text-gray-700 bg-white transition-colors">
                Previous
            </button>
            
            <!-- Page Numbers -->
            <template x-for="page in Array.from({length: pagination.last_page}, (_, i) => i + 1)" :key="page">
                <button @click="loadNotifications(page)"
                        :class="page === pagination.current_page ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'"
                        class="px-4 py-2 rounded-lg border border-gray-300 text-sm font-medium transition-colors"
                        x-text="page"></button>
            </template>
            
            <!-- Next -->
            <button @click="loadNotifications(pagination.current_page + 1)"
                    :disabled="pagination.current_page === pagination.last_page"
                    :class="pagination.current_page === pagination.last_page ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100'"
                    class="px-3 py-2 rounded-lg border border-gray-300 text-sm font-medium text-gray-700 bg-white transition-colors">
                Next
            </button>
        </div>
    </div>
</div>

<script>
    function notificationPageData() {
        return {
            notifications: [],
            filter: 'all',
            loading: false,
            pagination: {
                current_page: 1,
                last_page: 1,
                per_page: 20,
                total: 0
            },
            
            async loadNotifications(page = 1) {
                this.loading = true;
                try {
                    const response = await fetch(`/api/notifications?page=${page}&filter=${this.filter}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const data = await response.json();
                    this.notifications = data.data;
                    this.pagination = {
                        current_page: data.current_page,
                        last_page: data.last_page,
                        per_page: data.per_page,
                        total: data.total
                    };
                } catch (error) {
                    console.error('Failed to load notifications:', error);
                } finally {
                    this.loading = false;
                }
            },
            
            async markAsRead(notification) {
                if (notification.is_read) return;
                
                try {
                    await fetch(`/api/notifications/${notification.id}/read`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    notification.is_read = true;
                    notification.read_at = new Date().toISOString();
                } catch (error) {
                    console.error('Failed to mark as read:', error);
                }
            },
            
            async markAllAsRead() {
                if (!confirm('Mark all notifications as read?')) return;
                
                try {
                    await fetch('/api/notifications/mark-all-read', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    this.notifications.forEach(n => {
                        n.is_read = true;
                        n.read_at = new Date().toISOString();
                    });
                } catch (error) {
                    console.error('Failed to mark all as read:', error);
                }
            },
            
            async deleteNotification(notification) {
                if (!confirm('Delete this notification?')) return;
                
                try {
                    await fetch(`/api/notifications/${notification.id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    this.notifications = this.notifications.filter(n => n.id !== notification.id);
                } catch (error) {
                    console.error('Failed to delete notification:', error);
                }
            },
            
            async deleteAllRead() {
                if (!confirm('Delete all read notifications? This action cannot be undone.')) return;
                
                try {
                    await fetch('/api/notifications/read/all', {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    this.notifications = this.notifications.filter(n => !n.is_read);
                } catch (error) {
                    console.error('Failed to delete read notifications:', error);
                }
            },
            
            handleNotificationClick(notification) {
                this.markAsRead(notification);
                
                // Navigate ke board page
                if (notification.data && notification.data.board_id) {
                    window.location.href = `/boards/${notification.data.board_id}`;
                }
            },
            
            changeFilter(newFilter) {
                this.filter = newFilter;
                this.loadNotifications(1);
            }
        }
    }
</script>
@endsection
