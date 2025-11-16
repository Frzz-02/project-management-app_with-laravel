<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Controller untuk mengelola System Settings di Admin Dashboard
 * 
 * Fitur:
 * - Manage application settings
 * - Cache management
 * - System maintenance
 * - Database optimization
 */
class AdminSettingsController extends Controller
{
    /**
     * Menampilkan halaman system settings
     * 
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get system information
        $systemInfo = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'environment' => config('app.env'),
            'debug_mode' => config('app.debug'),
            'timezone' => config('app.timezone'),
            'locale' => config('app.locale'),
            'database_driver' => config('database.default'),
            'cache_driver' => config('cache.default'),
            'queue_driver' => config('queue.default'),
        ];

        // Get cache statistics
        $cacheStats = [
            'driver' => config('cache.default'),
            'enabled' => Cache::has('test_key') !== null,
        ];

        // Get database statistics
        try {
            $dbStats = [
                'connection' => DB::connection()->getDatabaseName(),
                'tables_count' => count(DB::select('SHOW TABLES')),
            ];
        } catch (\Exception $e) {
            $dbStats = [
                'connection' => 'Error',
                'tables_count' => 0,
            ];
        }

        // Get application settings (you can expand this with actual settings from DB)
        $appSettings = [
            'app_name' => config('app.name'),
            'app_url' => config('app.url'),
            'maintenance_mode' => app()->isDownForMaintenance(),
        ];

        return view('admin.settings.index', [
            'systemInfo' => $systemInfo,
            'cacheStats' => $cacheStats,
            'dbStats' => $dbStats,
            'appSettings' => $appSettings,
        ]);
    }

    /**
     * Clear application cache
     * 
     * @return \Illuminate\Http\RedirectResponse
     */
    public function clearCache()
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');

            return redirect()->route('admin.settings')
                ->with('success', 'All caches cleared successfully!');
        } catch (\Exception $e) {
            return redirect()->route('admin.settings')
                ->with('error', 'Failed to clear cache: ' . $e->getMessage());
        }
    }

    /**
     * Optimize application
     * 
     * @return \Illuminate\Http\RedirectResponse
     */
    public function optimize()
    {
        try {
            // Only cache views in development (safe and effective)
            Artisan::call('view:cache');
            
            // In production, also cache config and routes
            if (config('app.env') === 'production') {
                Artisan::call('config:cache');
                Artisan::call('route:cache');
                Artisan::call('optimize');
            }

            return redirect()->route('admin.settings')
                ->with('success', 'Application optimized successfully! (Views cached)');
        } catch (\Exception $e) {
            return redirect()->route('admin.settings')
                ->with('error', 'Failed to optimize: ' . $e->getMessage());
        }
    }

    /**
     * Clear logs
     * 
     * @return \Illuminate\Http\RedirectResponse
     */
    public function clearLogs()
    {
        try {
            $logPath = storage_path('logs/laravel.log');
            
            if (file_exists($logPath)) {
                file_put_contents($logPath, '');
            }

            return redirect()->route('admin.settings')
                ->with('success', 'Logs cleared successfully!');
        } catch (\Exception $e) {
            return redirect()->route('admin.settings')
                ->with('error', 'Failed to clear logs: ' . $e->getMessage());
        }
    }

    /**
     * Run database migrations
     * 
     * @return \Illuminate\Http\RedirectResponse
     */
    public function runMigrations()
    {
        try {
            Artisan::call('migrate', ['--force' => true]);

            return redirect()->route('admin.settings')
                ->with('success', 'Migrations ran successfully!');
        } catch (\Exception $e) {
            return redirect()->route('admin.settings')
                ->with('error', 'Failed to run migrations: ' . $e->getMessage());
        }
    }
}
