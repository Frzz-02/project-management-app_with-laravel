@extends('layouts.admin')

@section('title', 'System Settings')

@section('content')
<div class="p-4 sm:p-6 lg:p-8">
    <div class="max-w-7xl mx-auto">
        
        <!-- Header -->
        <div class="mb-6 sm:mb-8">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">System Settings</h1>
            <p class="text-gray-600">Manage system configuration and maintenance</p>
        </div>

        <!-- System Information -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                System Information
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">PHP Version</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $systemInfo['php_version'] }}</p>
                </div>

                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Laravel Version</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $systemInfo['laravel_version'] }}</p>
                </div>

                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Environment</p>
                    <p class="text-lg font-semibold text-gray-900">
                        <span class="px-2 py-1 text-xs rounded-full {{ $systemInfo['environment'] === 'production' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700' }}">
                            {{ ucfirst($systemInfo['environment']) }}
                        </span>
                    </p>
                </div>

                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Debug Mode</p>
                    <p class="text-lg font-semibold text-gray-900">
                        <span class="px-2 py-1 text-xs rounded-full {{ $systemInfo['debug_mode'] ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700' }}">
                            {{ $systemInfo['debug_mode'] ? 'Enabled' : 'Disabled' }}
                        </span>
                    </p>
                </div>

                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Timezone</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $systemInfo['timezone'] }}</p>
                </div>

                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Locale</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $systemInfo['locale'] }}</p>
                </div>

                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Database</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $systemInfo['database_driver'] }}</p>
                </div>

                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Cache Driver</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $systemInfo['cache_driver'] }}</p>
                </div>

                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Queue Driver</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $systemInfo['queue_driver'] }}</p>
                </div>
            </div>
        </div>

        <!-- Database Statistics -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-6 h-6 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
                </svg>
                Database Information
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 bg-purple-50 rounded-lg">
                    <p class="text-xs text-purple-600 uppercase tracking-wide mb-1">Connection</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $dbStats['connection'] }}</p>
                </div>

                <div class="p-4 bg-purple-50 rounded-lg">
                    <p class="text-xs text-purple-600 uppercase tracking-wide mb-1">Total Tables</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $dbStats['tables_count'] }}</p>
                </div>
            </div>
        </div>

        <!-- Application Settings -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-6 h-6 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                Application Settings
            </h2>

            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div>
                        <p class="font-medium text-gray-900">Application Name</p>
                        <p class="text-sm text-gray-600">{{ $appSettings['app_name'] }}</p>
                    </div>
                </div>

                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div>
                        <p class="font-medium text-gray-900">Application URL</p>
                        <p class="text-sm text-gray-600">{{ $appSettings['app_url'] }}</p>
                    </div>
                </div>

                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div>
                        <p class="font-medium text-gray-900">Maintenance Mode</p>
                        <p class="text-sm text-gray-600">
                            <span class="px-2 py-1 text-xs rounded-full {{ $appSettings['maintenance_mode'] ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                                {{ $appSettings['maintenance_mode'] ? 'Active' : 'Inactive' }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Maintenance Actions -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-6 h-6 mr-2 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                System Maintenance
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Clear Cache -->
                <div class="p-4 border border-gray-200 rounded-lg hover:border-blue-500 transition-colors">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 mb-1">Clear All Cache</h3>
                            <p class="text-sm text-gray-600 mb-3">Clear application, config, route, and view cache</p>
                            <form method="POST" action="{{ route('admin.settings.clear-cache') }}">
                                @csrf
                                <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                                    Clear Cache
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Optimize Application -->
                <div class="p-4 border border-gray-200 rounded-lg hover:border-green-500 transition-colors">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 mb-1">Optimize Application</h3>
                            <p class="text-sm text-gray-600 mb-3">Cache config, routes, and views for better performance</p>
                            <form method="POST" action="{{ route('admin.settings.optimize') }}">
                                @csrf
                                <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm font-medium">
                                    Optimize
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Clear Logs -->
                <div class="p-4 border border-gray-200 rounded-lg hover:border-yellow-500 transition-colors">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 mb-1">Clear Logs</h3>
                            <p class="text-sm text-gray-600 mb-3">Delete all application log files</p>
                            <form method="POST" action="{{ route('admin.settings.clear-logs') }}">
                                @csrf
                                <button type="submit" class="w-full px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors text-sm font-medium">
                                    Clear Logs
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Run Migrations -->
                {{-- <div class="p-4 border border-gray-200 rounded-lg hover:border-purple-500 transition-colors">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 mb-1">Run Migrations</h3>
                            <p class="text-sm text-gray-600 mb-3">Execute pending database migrations</p>
                            <form method="POST" action="{{ route('admin.settings.run-migrations') }}" onsubmit="return confirm('Are you sure you want to run migrations?')">
                                @csrf
                                <button type="submit" class="w-full px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors text-sm font-medium">
                                    Run Migrations
                                </button>
                            </form>
                        </div>
                    </div>
                </div> --}}
            </div>

            <!-- Warning Notice -->
            <div class="mt-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                <div class="flex gap-3">
                    <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <div>
                        <h4 class="text-sm font-semibold text-red-900 mb-1">⚠️ Warning</h4>
                        <p class="text-sm text-red-700">These actions can affect your application's performance and data. Use with caution, especially in production environments.</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
