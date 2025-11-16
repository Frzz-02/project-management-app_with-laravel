<?php

/**
 * Test Script: Create Unassigned User
 * 
 * Creates a new test user without project assignments
 * to test the unassigned dashboard functionality
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "=== Creating Unassigned Test User ===\n\n";

// Check if user already exists
$existingUser = User::where('email', 'unassigned@example.com')->first();
if ($existingUser) {
    echo "❌ User already exists: unassigned@example.com\n";
    echo "   To test again, delete this user first:\n";
    echo "   php artisan tinker\n";
    echo "   User::where('email', 'unassigned@example.com')->first()->delete();\n\n";
    exit(1);
}

// Create new unassigned user
try {
    $user = User::create([
        'username' => 'unassigned_user',
        'email' => 'unassigned@example.com',
        'password' => Hash::make('password'),
        'full_name' => 'Unassigned Test User',
        'is_admin' => false,
        'email_verified_at' => now(), // Email verified
        // profile_picture intentionally null for testing profile completion
    ]);
    
    echo "✅ User created successfully!\n\n";
    echo "📋 User Details:\n";
    echo "   ID: {$user->id}\n";
    echo "   Username: {$user->username}\n";
    echo "   Email: {$user->email}\n";
    echo "   Full Name: {$user->full_name}\n";
    echo "   Password: password\n\n";
    
    // Check profile completion
    $completed = 0;
    $total = 5;
    if ($user->full_name) $completed++;
    if ($user->email && $user->email_verified_at) $completed++;
    if ($user->username) $completed++;
    if ($user->profile_picture) $completed++;
    if ($user->created_at) $completed++;
    
    $percentage = round(($completed / $total) * 100, 2);
    
    echo "📊 Profile Completion:\n";
    echo "   Completed: {$completed}/{$total}\n";
    echo "   Percentage: {$percentage}%\n\n";
    
    echo "✅ Full Name: " . ($user->full_name ? '✓' : '✗') . "\n";
    echo "✅ Email Verified: " . ($user->email_verified_at ? '✓' : '✗') . "\n";
    echo "✅ Username: " . ($user->username ? '✓' : '✗') . "\n";
    echo "❌ Profile Picture: " . ($user->profile_picture ? '✓' : '✗') . " (intentionally null)\n";
    echo "✅ Account Created: ✓\n\n";
    
    // Check project assignments
    $hasProjects = \App\Models\ProjectMember::where('user_id', $user->id)->exists();
    echo "📦 Project Assignments: " . ($hasProjects ? 'YES' : 'NO') . "\n\n";
    
    if (!$hasProjects) {
        echo "🎯 TESTING INSTRUCTIONS:\n";
        echo "   1. Start the server: php artisan serve\n";
        echo "   2. Login with: unassigned@example.com / password\n";
        echo "   3. Should automatically redirect to: /unassigned/dashboard\n";
        echo "   4. Verify:\n";
        echo "      - Welcome banner shows 'Unassigned Test User'\n";
        echo "      - Profile completion shows 80% (4/5 complete)\n";
        echo "      - Profile picture checkmark is gray (incomplete)\n";
        echo "      - Timeline shows 4 steps\n";
        echo "      - FAQ accordion works\n";
        echo "      - System stats display correctly\n";
        echo "      - Console shows: 'Auto-check running every 60 seconds'\n\n";
        
        echo "🔄 TO TEST AUTO-DETECTION:\n";
        echo "   Run this command to assign user:\n";
        echo "   php assign_unassigned_user.php\n\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error creating user: " . $e->getMessage() . "\n";
    exit(1);
}

echo "✅ Test user creation completed!\n";
