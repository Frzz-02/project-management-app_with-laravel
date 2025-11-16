<?php

/**
 * Script untuk assign user sebagai Team Lead di project
 * 
 * Cara pakai:
 * php assign_team_lead.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Project;
use App\Models\ProjectMember;

echo "=== Assign Team Lead Script ===\n\n";

// Get all users
$users = User::all();
echo "Available Users:\n";
foreach ($users as $user) {
    echo "  [{$user->id}] {$user->full_name} ({$user->email}) - Role: {$user->role}\n";
}

echo "\nEnter User ID to assign as Team Lead: ";
$userId = trim(fgets(STDIN));

$user = User::find($userId);
if (!$user) {
    echo "Error: User not found!\n";
    exit(1);
}

// Get all projects
$projects = Project::all();
echo "\nAvailable Projects:\n";
foreach ($projects as $project) {
    echo "  [{$project->id}] {$project->project_name}\n";
}

echo "\nEnter Project ID (or 'all' for all projects): ";
$projectInput = trim(fgets(STDIN));

if (strtolower($projectInput) === 'all') {
    $projectIds = $projects->pluck('id')->toArray();
} else {
    $projectIds = [$projectInput];
}

foreach ($projectIds as $projectId) {
    $project = Project::find($projectId);
    if (!$project) {
        echo "Warning: Project ID {$projectId} not found, skipping...\n";
        continue;
    }
    
    // Check if already member
    $existing = ProjectMember::where('project_id', $projectId)
        ->where('user_id', $userId)
        ->first();
    
    if ($existing) {
        // Update role to team lead
        $existing->update(['role' => 'team lead']);
        echo "✓ Updated {$user->full_name} to Team Lead in project: {$project->project_name}\n";
    } else {
        // Create new team lead member
        ProjectMember::create([
            'project_id' => $projectId,
            'user_id' => $userId,
            'role' => 'team lead',
        ]);
        echo "✓ Added {$user->full_name} as Team Lead in project: {$project->project_name}\n";
    }
}

echo "\n=== Done! ===\n";
echo "User {$user->full_name} is now Team Lead in " . count($projectIds) . " project(s).\n";
echo "You can now login and access /team-leader/dashboard\n";
