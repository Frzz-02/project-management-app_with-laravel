<?php
/**
 * Test Admin Dashboard Access
 * Run this script to simulate admin access
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Simulate request to /admin/dashboard
$request = Illuminate\Http\Request::create('/admin/dashboard', 'GET');
$request->headers->set('Accept', 'text/html');

// Login as admin user
$admin = \App\Models\User::where('role', 'admin')->first();

if (!$admin) {
    echo "❌ No admin user found!\n";
    exit(1);
}

echo "=== TESTING ADMIN ACCESS ===\n\n";
echo "Login as: {$admin->full_name} ({$admin->email})\n";
echo "Role: {$admin->role}\n";
echo "Request: GET /admin/dashboard\n\n";

// Manually authenticate
auth()->login($admin);

echo "Auth check: " . (auth()->check() ? "✅ Logged in" : "❌ Not logged in") . "\n";
echo "Current user: " . auth()->user()->full_name . "\n";
echo "Current role: " . auth()->user()->role . "\n\n";

// Test middleware
$middleware = new \App\Http\Middleware\isAdmin();

try {
    $result = $middleware->handle($request, function ($req) {
        return response('✅ Middleware PASSED! Admin access granted.');
    });
    
    echo "=== MIDDLEWARE RESULT ===\n";
    echo $result->getContent() . "\n";
    
    if ($result instanceof Illuminate\Http\RedirectResponse) {
        echo "⚠️ Redirected to: " . $result->getTargetUrl() . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n=== CONCLUSION ===\n";
echo "If you see '✅ Middleware PASSED', admin can access /admin/dashboard\n";
echo "If redirected, there's still an issue with the middleware\n";
