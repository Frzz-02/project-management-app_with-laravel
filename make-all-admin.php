<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔥 MAKING ALL USERS ADMIN...\n\n";

$users = \App\Models\User::all();

echo "Found " . $users->count() . " users:\n\n";

foreach ($users as $user) {
    $oldRole = $user->role;
    $user->role = 'admin';
    $user->save();
    
    echo "✅ {$user->full_name} ({$user->email}) - Role: {$oldRole} → ADMIN\n";
}

echo "\n🎉 ALL USERS ARE NOW ADMIN!\n";
echo "\nLogout and login again, then access /admin/dashboard\n";
