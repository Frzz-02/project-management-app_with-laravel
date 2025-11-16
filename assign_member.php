<?php
/**
 * Assign Member Role - Quick Script
 * 
 * Script untuk assign user sebagai developer/designer di project
 * untuk testing Member Dashboard
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Card;
use App\Models\Board;
use App\Models\CardAssignment;

echo "🎯 Member Dashboard - Test Data Setup\n";
echo "=====================================\n\n";

// Get first user (atau bisa specify by email)
$user = User::where('email', 'john@example.com')->first() ?? User::first();

if (!$user) {
    echo "❌ No users found in database.\n";
    exit(1);
}

echo "👤 Selected User: {$user->full_name} ({$user->email})\n\n";

// Get first 3 projects and assign as developer
$projects = Project::take(3)->get();

if ($projects->isEmpty()) {
    echo "❌ No projects found. Create some projects first.\n";
    exit(1);
}

foreach ($projects as $project) {
    ProjectMember::updateOrCreate(
        [
            'project_id' => $project->id,
            'user_id' => $user->id,
        ],
        [
            'role' => 'developer', // Change to 'designer' if needed
            'joined_at' => now(),
        ]
    );
    
    echo "✓ Assigned as Developer in: {$project->project_name}\n";
}

echo "\n📋 Assigning test cards to user...\n";

// Assign some cards dengan berbagai status dan deadlines
$boards = Board::whereIn('project_id', $projects->pluck('id'))->take(5)->get();

$assignedCount = 0;
foreach ($boards as $board) {
    $cards = Card::where('board_id', $board->id)
        ->whereNotIn('status', ['done'])
        ->take(3)
        ->get();
    
    foreach ($cards as $card) {
        // Set various deadlines for testing
        if ($assignedCount === 0) {
            $card->due_date = now()->addDays(1); // Critical: tomorrow
        } elseif ($assignedCount === 1) {
            $card->due_date = now()->subDays(2); // Overdue
        } elseif ($assignedCount === 2) {
            $card->due_date = now()->addDays(5); // Upcoming
        }
        $card->save();
        
        CardAssignment::updateOrCreate(
            [
                'card_id' => $card->id,
                'user_id' => $user->id,
            ],
            [
                'assignment_status' => $assignedCount % 3 === 0 ? 'in progress' : 'assigned',
                'assigned_at' => now(),
            ]
        );
        
        $assignedCount++;
        if ($assignedCount >= 8) break 2; // Assign 8 cards total
    }
}

echo "✓ Assigned {$assignedCount} cards with various deadlines\n";

echo "\n✅ SETUP COMPLETE!\n";
echo "==================\n";
echo "👤 Login as: {$user->email}\n";
echo "🔑 Password: password (default)\n";
echo "🌐 Access: http://localhost/member/dashboard\n\n";
echo "Features to test:\n";
echo "  • Overview stats\n";
echo "  • Start/Pause task timer\n";
echo "  • Upcoming deadlines (1 overdue, 1 critical, others upcoming)\n";
echo "  • Today's work summary\n";
echo "  • My projects list\n\n";
