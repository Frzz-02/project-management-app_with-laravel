<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Notification System Check ===\n\n";

// 1. Check cards in review
$cardsInReview = DB::table('cards')->where('status', 'review')->count();
echo "1. Cards in review status: {$cardsInReview}\n";

// 2. Check card assignments
$totalAssignments = DB::table('card_assignments')->count();
echo "2. Total card assignments: {$totalAssignments}\n";

// 3. Check notifications
$totalNotifications = DB::table('notifications')->count();
echo "3. Total notifications: {$totalNotifications}\n";

// 4. Show sample card with assignments
echo "\n=== Sample Card with Assignments ===\n";
$card = DB::table('cards')
    ->where('status', 'review')
    ->first();

if ($card) {
    echo "Card ID: {$card->id}\n";
    echo "Card Title: {$card->card_title}\n";
    echo "Status: {$card->status}\n";
    echo "Board ID: {$card->board_id}\n";
    
    $assignments = DB::table('card_assignments')
        ->join('users', 'card_assignments.user_id', '=', 'users.id')
        ->where('card_assignments.card_id', $card->id)
        ->select('users.id', 'users.username', 'card_assignments.assignment_status')
        ->get();
    
    echo "\nAssigned Users:\n";
    foreach ($assignments as $assignment) {
        echo "  - User ID: {$assignment->id}, Username: {$assignment->username}, Status: {$assignment->assignment_status}\n";
    }
} else {
    echo "No cards in review status found.\n";
}

// 5. Show recent notifications
echo "\n=== Recent Notifications ===\n";
$notifications = DB::table('notifications')
    ->join('users', 'notifications.user_id', '=', 'users.id')
    ->select('notifications.*', 'users.username')
    ->orderBy('notifications.created_at', 'desc')
    ->limit(5)
    ->get();

if ($notifications->count() > 0) {
    foreach ($notifications as $notif) {
        echo "ID: {$notif->id} | User: {$notif->username} | Type: {$notif->type} | Read: " . ($notif->is_read ? 'Yes' : 'No') . "\n";
        echo "  Title: {$notif->title}\n";
        echo "  Created: {$notif->created_at}\n\n";
    }
} else {
    echo "No notifications found.\n";
}

echo "\n=== Project Members ===\n";
$members = DB::table('project_members')
    ->join('users', 'project_members.user_id', '=', 'users.id')
    ->join('projects', 'project_members.project_id', '=', 'projects.id')
    ->select('users.username', 'project_members.role', 'projects.project_name')
    ->get();

foreach ($members as $member) {
    echo "Project: {$member->project_name} | User: {$member->username} | Role: {$member->role}\n";
}

echo "\n=== DONE ===\n";
