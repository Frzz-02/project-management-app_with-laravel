<?php
/**
 * Check Admin Status
 * Upload file ini ke public_html/ untuk check user role
 * DELETE setelah digunakan!
 */

// Load Laravel
require __DIR__ . '/../laravel/vendor/autoload.php';

$app = require_once __DIR__ . '/../laravel/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Handle request
$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

// Check auth
if (!auth()->check()) {
    echo "<h2>❌ Not logged in</h2>";
    echo "<p><a href='/login'>Login here</a></p>";
    exit;
}

// Get current user
$user = auth()->user();

echo "<h2>✅ Logged in as:</h2>";
echo "<ul>";
echo "<li><strong>ID:</strong> {$user->id}</li>";
echo "<li><strong>Name:</strong> {$user->full_name}</li>";
echo "<li><strong>Email:</strong> {$user->email}</li>";
echo "<li><strong>Role:</strong> <span style='color: " . ($user->role === 'admin' ? 'green' : 'red') . "; font-weight: bold;'>{$user->role}</span></li>";
echo "</ul>";

if ($user->role === 'admin') {
    echo "<h3 style='color: green;'>✅ You have ADMIN access!</h3>";
    echo "<p><a href='/admin/dashboard'>Go to Admin Dashboard</a></p>";
} else {
    echo "<h3 style='color: red;'>❌ You are NOT an admin!</h3>";
    echo "<p>Your role is: <strong>{$user->role}</strong></p>";
    echo "<p>You need 'admin' role to access admin dashboard.</p>";
    
    echo "<hr>";
    echo "<h4>To grant admin access:</h4>";
    echo "<ol>";
    echo "<li>Login to phpMyAdmin</li>";
    echo "<li>Open 'users' table</li>";
    echo "<li>Find your user (ID: {$user->id})</li>";
    echo "<li>Edit 'role' column to: <strong>admin</strong></li>";
    echo "<li>Save and try again</li>";
    echo "</ol>";
}

echo "<hr>";
echo "<p><strong>⚠️ DELETE THIS FILE AFTER USE!</strong></p>";
echo "<p><a href='/dashboard'>Back to Dashboard</a></p>";
