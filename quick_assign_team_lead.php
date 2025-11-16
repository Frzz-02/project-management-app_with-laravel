<?php

/**
 * Quick script untuk assign user pertama sebagai team lead
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Project;
use App\Models\ProjectMember;

$user = User::first();
$projects = Project::take(3)->get(); // First 3 projects

if (!$user) {
    echo "No users found!\n";
    exit(1);
}

echo "Assigning {$user->full_name} as Team Lead...\n\n";

foreach ($projects as $project) {
    ProjectMember::updateOrCreate(
        [
            'project_id' => $project->id,
            'user_id' => $user->id,
        ],
        [
            'role' => 'team lead',
        ]
    );
    
    echo "✓ Team Lead in: {$project->project_name}\n";
}

echo "\nDone! User can now access /team-leader/dashboard\n";
echo "Login as: {$user->email}\n";
