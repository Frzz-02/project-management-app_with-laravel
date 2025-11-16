<?php

/**
 * Test Script: Assign Unassigned User to Project
 * 
 * Assigns the unassigned test user to a project
 * to test the auto-detection and redirect functionality
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Project;
use App\Models\ProjectMember;

echo "=== Assigning Unassigned User to Project ===\n\n";

// Find unassigned user
$user = User::where('email', 'unassigned@example.com')->first();
if (!$user) {
    echo "❌ User not found: unassigned@example.com\n";
    echo "   Run create_unassigned_user.php first\n\n";
    exit(1);
}

echo "✅ Found user: {$user->full_name} (ID: {$user->id})\n\n";

// Check if already assigned
$existingAssignment = ProjectMember::where('user_id', $user->id)->first();
if ($existingAssignment) {
    echo "⚠️  User already assigned to project!\n";
    echo "   Project ID: {$existingAssignment->project_id}\n";
    echo "   Role: {$existingAssignment->role}\n\n";
    exit(1);
}

// Find first project
$project = Project::first();
if (!$project) {
    echo "❌ No projects found in database\n";
    echo "   Create a project first\n\n";
    exit(1);
}

echo "✅ Found project: {$project->project_name} (ID: {$project->id})\n\n";

// Assign user as developer
try {
    $member = ProjectMember::create([
        'project_id' => $project->id,
        'user_id' => $user->id,
        'role' => 'developer'
    ]);
    
    echo "✅ User assigned successfully!\n\n";
    echo "📋 Assignment Details:\n";
    echo "   Project: {$project->project_name}\n";
    echo "   User: {$user->full_name}\n";
    echo "   Role: {$member->role}\n\n";
    
    echo "🎯 TESTING AUTO-DETECTION:\n";
    echo "   1. Make sure user is logged in at /unassigned/dashboard\n";
    echo "   2. Within 60 seconds, JavaScript should detect assignment\n";
    echo "   3. Green notification appears: 'You've been assigned to a project!'\n";
    echo "   4. Automatic redirect to /dashboard → /member/dashboard\n";
    echo "   5. User can now see tasks and time tracking\n\n";
    
    echo "🔍 VERIFY IN BROWSER:\n";
    echo "   - Check browser console for API call to /api/check-assignment\n";
    echo "   - Response should be: {has_assignment: true, redirect_url: '...'}\n";
    echo "   - Dashboard should load member view with stats and tasks\n\n";
    
} catch (Exception $e) {
    echo "❌ Error assigning user: " . $e->getMessage() . "\n";
    exit(1);
}

echo "✅ Assignment completed!\n";
