<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Testing API Endpoints ===\n\n";

// Test 1: Recent endpoint (untuk dropdown)
echo "1. Testing /api/notifications/recent endpoint:\n";
echo "   SQL: SELECT * FROM notifications ORDER BY created_at DESC LIMIT 10\n";
$recent = DB::table('notifications')
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get();

echo "   Result: " . $recent->count() . " notifications\n";
foreach($recent as $n) {
    echo "   - ID: {$n->id}, User: {$n->user_id}, Title: {$n->title}\n";
}

// Test 2: Index endpoint dengan pagination (untuk /notifications page)
echo "\n2. Testing /api/notifications?page=1 endpoint:\n";
echo "   SQL: SELECT * FROM notifications ORDER BY created_at DESC with pagination\n";

// Simulate what NotificationController->index() does
$perPage = 20;
$notifications = DB::table('notifications')
    ->orderBy('created_at', 'desc')
    ->paginate($perPage);

echo "   Total: {$notifications->total()}\n";
echo "   Per Page: {$notifications->perPage()}\n";
echo "   Current Page: {$notifications->currentPage()}\n";
echo "   Last Page: {$notifications->lastPage()}\n";
echo "   Data Count: {$notifications->count()}\n";

if ($notifications->count() > 0) {
    echo "\n   First 3 results:\n";
    foreach($notifications->take(3) as $n) {
        echo "   - ID: {$n->id}, User: {$n->user_id}, Title: {$n->title}\n";
    }
}

// Test 3: Check NotificationController methods
echo "\n3. Checking NotificationController:\n";
$controllerPath = app_path('Http/Controllers/NotificationController.php');
if (file_exists($controllerPath)) {
    echo "   ✅ NotificationController exists\n";
    
    $content = file_get_contents($controllerPath);
    
    if (strpos($content, 'public function recent') !== false) {
        echo "   ✅ recent() method exists\n";
    }
    
    if (strpos($content, 'public function index') !== false) {
        echo "   ✅ index() method exists\n";
    }
} else {
    echo "   ❌ NotificationController NOT FOUND\n";
}

// Test 4: Check routes
echo "\n4. Checking routes:\n";
$routes = Route::getRoutes();

$recentRoute = null;
$indexRoute = null;

foreach ($routes as $route) {
    if ($route->uri() === 'api/notifications/recent') {
        $recentRoute = $route;
        echo "   ✅ GET /api/notifications/recent → {$route->getActionName()}\n";
    }
    if ($route->uri() === 'api/notifications' && in_array('GET', $route->methods())) {
        $indexRoute = $route;
        echo "   ✅ GET /api/notifications → {$route->getActionName()}\n";
    }
}

if (!$recentRoute) {
    echo "   ❌ Route /api/notifications/recent NOT FOUND\n";
}

if (!$indexRoute) {
    echo "   ❌ Route /api/notifications NOT FOUND\n";
}

// Test 5: Check if user is authenticated (simulate)
echo "\n5. Auth Check:\n";
$users = DB::table('users')->limit(3)->get(['id', 'username']);
echo "   Available test users:\n";
foreach($users as $user) {
    echo "   - User ID: {$user->id}, Username: {$user->username}\n";
}

echo "\n=== DONE ===\n";
