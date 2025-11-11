<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

// Create a GET request to /cards
$request = \Illuminate\Http\Request::create('/cards', 'GET');

// Simulate user authentication
$admin = App\Models\User::where('username', 'admin')->first();
if ($admin) {
    $request->setUserResolver(function () use ($admin) {
        return $admin;
    });
    \Illuminate\Support\Facades\Auth::setUser($admin);
}

try {
    $response = $kernel->handle($request);
    echo "Status Code: " . $response->getStatusCode() . "\n";
    
    if ($response->getStatusCode() === 200) {
        echo "Route /cards works successfully!\n";
        echo "Response length: " . strlen($response->getContent()) . " characters\n";
    } else {
        echo "Error occurred:\n";
        echo substr($response->getContent(), 0, 500) . "\n";
    }
} catch (\Exception $e) {
    echo "Exception occurred: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
} finally {
    $kernel->terminate($request, $response ?? null);
}