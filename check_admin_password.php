<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Check admin user password
$admin = App\Models\User::where('email', 'admin@test.com')->orWhere('username', 'admin')->first();
if ($admin) {
    echo "Admin found:\n";
    echo "ID: {$admin->id}\n";
    echo "Email: {$admin->email}\n";
    echo "Username: {$admin->username}\n";
    echo "Full name: {$admin->full_name}\n";
    echo "Password hash: {$admin->password}\n";
    
    // Check if password 'admin123' matches
    if (\Illuminate\Support\Facades\Hash::check('admin123', $admin->password)) {
        echo "Password 'admin123' is CORRECT\n";
    } else {
        echo "Password 'admin123' is INCORRECT\n";
        
        // Try other common passwords
        $testPasswords = ['admin', 'password', '12345678', 'admin1234'];
        foreach ($testPasswords as $pass) {
            if (\Illuminate\Support\Facades\Hash::check($pass, $admin->password)) {
                echo "Password '$pass' is CORRECT\n";
                break;
            }
        }
    }
} else {
    echo "Admin user not found\n";
}