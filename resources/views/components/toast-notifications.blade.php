{{--
/**
 * Toast Notifications Component
 * 
 * Component untuk menampilkan toast notifications di pojok kanan atas layar.
 * 
 * Features:
 * - Success, error, warning, info notification types
 * - Auto dismiss after 5 seconds
 * - Manual dismiss dengan click
 * - Animation untuk show/hide
 * - Queue system untuk multiple notifications
 * - Responsive design
 */
--}}

<div x-data="toastNotifications()" 
     x-init="init()"
     class="fixed top-4 right-4 z-50 space-y-2 w-80 sm:w-96">
     
    <!-- Notifications Container -->
    <template x-for="notification in notifications" :key="notification.id">
        <div x-show="notification.show"
             x-transition:enter="transform ease-out duration-300 transition"
             x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
             x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="relative bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden"
             :class="{
                 'border-green-200': notification.type === 'success',
                 'border-red-200': notification.type === 'error',
                 'border-yellow-200': notification.type === 'warning',
                 'border-blue-200': notification.type === 'info'
             }">
             
            <!-- Colored Bar -->
            <div class="absolute top-0 left-0 w-full h-1"
                 :class="{
                     'bg-green-500': notification.type === 'success',
                     'bg-red-500': notification.type === 'error',
                     'bg-yellow-500': notification.type === 'warning',
                     'bg-blue-500': notification.type === 'info'
                 }"></div>

            <!-- Content -->
            <div class="p-4">
                <div class="flex items-start">
                    <!-- Icon -->
                    <div class="flex-shrink-0">
                        <!-- Success Icon -->
                        <div x-show="notification.type === 'success'" 
                             class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        </div>

                        <!-- Error Icon -->
                        <div x-show="notification.type === 'error'" 
                             class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </div>

                        <!-- Warning Icon -->
                        <div x-show="notification.type === 'warning'" 
                             class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                        </div>

                        <!-- Info Icon -->
                        <div x-show="notification.type === 'info'" 
                             class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    </div>

                    <!-- Message Content -->
                    <div class="ml-3 flex-1">
                        <h4 class="text-sm font-medium text-gray-900" x-text="notification.title"></h4>
                        <p class="mt-1 text-sm text-gray-600" x-text="notification.message"></p>
                    </div>

                    <!-- Close Button -->
                    <div class="ml-4 flex-shrink-0 flex">
                        <button @click="dismissNotification(notification.id)"
                                class="inline-flex text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 rounded-md">
                            <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Progress Bar (for auto dismiss) -->
                <div x-show="notification.autodismiss" class="mt-3">
                    <div class="w-full bg-gray-200 rounded-full h-1">
                        <div class="h-1 rounded-full transition-all duration-100 ease-linear"
                             :class="{
                                 'bg-green-500': notification.type === 'success',
                                 'bg-red-500': notification.type === 'error',
                                 'bg-yellow-500': notification.type === 'warning',
                                 'bg-blue-500': notification.type === 'info'
                             }"
                             :style="`width: ${notification.progress || 0}%`"></div>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>

<script>
/**
 * Toast Notifications Component
 * 
 * Alpine.js component untuk mengelola toast notifications
 */
function toastNotifications() {
    return {
        notifications: [],
        notificationIdCounter: 1,

        /**
         * Initialize component
         */
        init() {
            // Listen for global notification events
            document.addEventListener('show-notification', (e) => {
                this.addNotification(e.detail);
            });

            // Listen for legacy notification events (for backward compatibility)
            document.addEventListener('notification', (e) => {
                this.addNotification(e.detail);
            });
        },

        /**
         * Add a new notification
         * 
         * @param {Object} options - Notification options
         * @param {string} options.type - Type: success, error, warning, info
         * @param {string} options.title - Notification title
         * @param {string} options.message - Notification message
         * @param {number} options.duration - Auto dismiss duration in ms (default: 5000)
         * @param {boolean} options.autodismiss - Whether to auto dismiss (default: true)
         */
        addNotification(options) {
            const notification = {
                id: this.notificationIdCounter++,
                type: options.type || 'info',
                title: options.title || this.getDefaultTitle(options.type),
                message: options.message || '',
                show: false,
                autodismiss: options.autodismiss !== false,
                duration: options.duration || 5000,
                progress: 100
            };

            // Add to notifications array
            this.notifications.push(notification);

            // Show with slight delay for animation
            setTimeout(() => {
                notification.show = true;
            }, 50);

            // Auto dismiss if enabled
            if (notification.autodismiss) {
                this.setupAutoDismiss(notification);
            }

            // Limit notifications to prevent overflow
            if (this.notifications.length > 5) {
                this.dismissNotification(this.notifications[0].id);
            }
        },

        /**
         * Setup auto dismiss with progress bar
         */
        setupAutoDismiss(notification) {
            const interval = 100; // Update every 100ms
            const steps = notification.duration / interval;
            let currentStep = 0;

            const progressInterval = setInterval(() => {
                currentStep++;
                notification.progress = 100 - (currentStep / steps * 100);

                if (currentStep >= steps) {
                    clearInterval(progressInterval);
                    this.dismissNotification(notification.id);
                }
            }, interval);

            // Store interval ID for cleanup if manually dismissed
            notification.progressInterval = progressInterval;
        },

        /**
         * Dismiss a notification
         */
        dismissNotification(id) {
            const notification = this.notifications.find(n => n.id === id);
            if (notification) {
                // Clear progress interval if exists
                if (notification.progressInterval) {
                    clearInterval(notification.progressInterval);
                }

                // Hide with animation
                notification.show = false;

                // Remove from array after animation
                setTimeout(() => {
                    this.notifications = this.notifications.filter(n => n.id !== id);
                }, 300);
            }
        },

        /**
         * Get default title based on type
         */
        getDefaultTitle(type) {
            const titles = {
                success: 'Success',
                error: 'Error',
                warning: 'Warning',
                info: 'Information'
            };
            return titles[type] || 'Notification';
        },

        /**
         * Clear all notifications
         */
        clearAll() {
            this.notifications.forEach(notification => {
                if (notification.progressInterval) {
                    clearInterval(notification.progressInterval);
                }
                notification.show = false;
            });

            setTimeout(() => {
                this.notifications = [];
            }, 300);
        }
    }
}

/**
 * Global helper functions for easy notification usage
 */
window.showNotification = function(options) {
    document.dispatchEvent(new CustomEvent('show-notification', { detail: options }));
};

window.showSuccess = function(message, title = 'Success') {
    window.showNotification({ type: 'success', title, message });
};

window.showError = function(message, title = 'Error') {
    window.showNotification({ type: 'error', title, message });
};

window.showWarning = function(message, title = 'Warning') {
    window.showNotification({ type: 'warning', title, message });
};

window.showInfo = function(message, title = 'Info') {
    window.showNotification({ type: 'info', title, message });
};
</script>