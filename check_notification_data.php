<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Notification Data Structure ===\n\n";

$notification = DB::table('notifications')
    ->orderBy('created_at', 'desc')
    ->first();

if ($notification) {
    echo "Sample Notification:\n";
    echo "ID: {$notification->id}\n";
    echo "User ID: {$notification->user_id}\n";
    echo "Type: {$notification->type}\n";
    echo "Title: {$notification->title}\n";
    echo "Message: {$notification->message}\n";
    echo "\nData (JSON):\n";
    
    $data = json_decode($notification->data, true);
    echo json_encode($data, JSON_PRETTY_PRINT) . "\n";
    
    echo "\nData Fields:\n";
    if ($data) {
        foreach ($data as $key => $value) {
            echo "  - {$key}: {$value}\n";
        }
        
        echo "\n=== Navigation URL ===\n";
        if (isset($data['board_id'])) {
            echo "✅ board_id exists: {$data['board_id']}\n";
            echo "Correct URL: /boards/{$data['board_id']}\n";
        }
        
        if (isset($data['project_id'])) {
            echo "✅ project_id exists: {$data['project_id']}\n";
        }
        
        if (isset($data['card_id'])) {
            echo "✅ card_id exists: {$data['card_id']}\n";
        }
    }
} else {
    echo "No notifications found.\n";
}

echo "\n=== Available Board Routes ===\n";
echo "- GET /boards/{board} → boards.show\n";
echo "- Correct navigation: window.location.href = `/boards/\${board_id}`\n";

echo "\n=== DONE ===\n";
