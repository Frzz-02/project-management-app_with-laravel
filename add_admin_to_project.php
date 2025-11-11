<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get admin user
$admin = App\Models\User::where('username', 'admin')->first();
if (!$admin) {
    echo "Admin user not found\n";
    exit;
}

// Get first project
$project = App\Models\Project::first();
if (!$project) {
    echo "No project found\n";
    exit;
}

// Check if admin is already member
if ($project->members()->where('user_id', $admin->id)->exists()) {
    echo "Admin is already a member of project: {$project->project_name}\n";
} else {
    // Add admin as team lead
    $project->members()->create([
        'user_id' => $admin->id,
        'role' => 'team lead'
    ]);
    echo "Admin added as team lead to project: {$project->project_name}\n";
}

// Show project info
echo "Project ID: {$project->id}\n";
echo "Project Name: {$project->project_name}\n";
echo "Creator: {$project->creator->username}\n";
echo "Members count: " . $project->members()->count() . "\n";