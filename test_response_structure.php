<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Testing Notification API Response Structure ===\n\n";

// Simulate NotificationController->index() logic
$userId = 2; // janesmith
$perPage = 20;
$filter = 'all';

echo "Simulating: GET /api/notifications?page=1&filter=all\n";
echo "For User ID: {$userId}\n\n";

$query = App\Models\Notification::where('user_id', $userId)
    ->orderBy('created_at', 'desc');

if ($filter === 'unread') {
    $query->unread();
} elseif ($filter === 'read') {
    $query->read();
}

$notifications = $query->paginate($perPage);

// Transform data
$transformedData = $notifications->map(function ($notification) {
    return [
        'id' => $notification->id,
        'type' => $notification->type,
        'title' => $notification->title,
        'message' => $notification->message,
        'data' => $notification->data,
        'is_read' => $notification->is_read,
        'read_at' => $notification->read_at?->format('Y-m-d H:i:s'),
        'time_ago' => $notification->time_ago,
        'icon' => $notification->icon,
        'color_class' => $notification->color_class,
        'created_at' => $notification->created_at->format('Y-m-d H:i:s'),
    ];
});

// Build response
$response = [
    'data' => $transformedData,
    'current_page' => $notifications->currentPage(),
    'last_page' => $notifications->lastPage(),
    'per_page' => $notifications->perPage(),
    'total' => $notifications->total(),
];

echo "Response Structure:\n";
echo "{\n";
echo "  'data': array(" . count($response['data']) . " items),\n";
echo "  'current_page': {$response['current_page']},\n";
echo "  'last_page': {$response['last_page']},\n";
echo "  'per_page': {$response['per_page']},\n";
echo "  'total': {$response['total']}\n";
echo "}\n\n";

if (count($response['data']) > 0) {
    echo "Sample notification (first item):\n";
    $first = $response['data'][0];
    echo "  ID: {$first['id']}\n";
    echo "  Type: {$first['type']}\n";
    echo "  Title: {$first['title']}\n";
    echo "  Message: {$first['message']}\n";
    echo "  Icon: {$first['icon']}\n";
    echo "  Color: {$first['color_class']}\n";
    echo "  Is Read: " . ($first['is_read'] ? 'Yes' : 'No') . "\n";
    echo "  Time Ago: {$first['time_ago']}\n";
    echo "  Created: {$first['created_at']}\n";
} else {
    echo "❌ NO NOTIFICATIONS FOUND for user {$userId}\n";
}

echo "\n=== Frontend JavaScript Expectation ===\n";
echo "const data = await response.json();\n";
echo "this.notifications = data.data; // ✅ Should work now\n";
echo "this.pagination = {\n";
echo "  current_page: data.current_page,\n";
echo "  last_page: data.last_page,\n";
echo "  per_page: data.per_page,\n";
echo "  total: data.total\n";
echo "};\n";

echo "\n=== DONE ===\n";
