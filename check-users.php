<?php
/**
 * Check Users and Roles
 * Temporary script untuk debugging
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== ALL USERS IN DATABASE ===\n\n";

$users = \App\Models\User::all(['id', 'full_name', 'email', 'role']);

if ($users->isEmpty()) {
    echo "❌ No users found in database!\n";
    echo "Run: php artisan db:seed\n";
} else {
    foreach ($users as $user) {
        $roleColor = $user->role === 'admin' ? '✅ ADMIN' : '👤 ' . strtoupper($user->role);
        echo "ID: {$user->id}\n";
        echo "Name: {$user->full_name}\n";
        echo "Email: {$user->email}\n";
        echo "Role: {$roleColor}\n";
        echo "---\n";
    }
}

echo "\n=== SUMMARY ===\n";
echo "Total users: " . $users->count() . "\n";
echo "Admin users: " . $users->where('role', 'admin')->count() . "\n";
echo "Non-admin users: " . $users->where('role', '!=', 'admin')->count() . "\n";

echo "\n=== TEST LOGIN ===\n";
echo "To test admin access, login with:\n";
$admin = $users->where('role', 'admin')->first();
if ($admin) {
    echo "Email: {$admin->email}\n";
    echo "Password: (check your database or use default 'password')\n";
} else {
    echo "❌ No admin user found!\n";
    echo "Create one or update existing user role to 'admin'\n";
}
