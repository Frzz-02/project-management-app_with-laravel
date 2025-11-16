<?php
/**
 * CHECK CURRENT USER ROLE
 * Upload ke public/ dan akses untuk check user saat ini
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Create request and handle it
$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Check Current User</title>
    <style>
        body { font-family: Arial; max-width: 800px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .box { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .success { background: #d1fae5; border-left: 5px solid #10b981; padding: 15px; margin: 10px 0; }
        .error { background: #fee2e2; border-left: 5px solid #ef4444; padding: 15px; margin: 10px 0; }
        .warning { background: #fef3c7; border-left: 5px solid #f59e0b; padding: 15px; margin: 10px 0; }
        .info { background: #dbeafe; border-left: 5px solid #3b82f6; padding: 15px; margin: 10px 0; }
        h1 { color: #333; margin-bottom: 20px; }
        h2 { color: #666; margin-top: 20px; }
        .btn { display: inline-block; padding: 12px 24px; background: #3b82f6; color: white; text-decoration: none; border-radius: 5px; margin: 5px; font-weight: bold; }
        .btn:hover { background: #2563eb; }
        .btn-danger { background: #ef4444; }
        .btn-danger:hover { background: #dc2626; }
        .btn-success { background: #10b981; }
        .btn-success:hover { background: #059669; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f9fafb; font-weight: bold; }
        code { background: #f3f4f6; padding: 2px 6px; border-radius: 4px; font-family: monospace; }
    </style>
</head>
<body>
    <div class="box">
        <h1>🔍 Current User Check</h1>
        
        <?php
        try {
            // Check if user is logged in
            $isLoggedIn = auth()->check();
            
            if (!$isLoggedIn) {
                echo '<div class="error">';
                echo '<strong>❌ NOT LOGGED IN!</strong><br><br>';
                echo 'You are not logged in to Laravel session.';
                echo '</div>';
                
                echo '<a href="/login" class="btn">Go to Login</a>';
                echo '<a href="/force-admin.php" class="btn btn-danger">Force Login as Admin</a>';
            } else {
                $user = auth()->user();
                
                // Check if admin
                $isAdmin = $user->role === 'admin';
                
                if ($isAdmin) {
                    echo '<div class="success">';
                    echo '<strong>✅ YOU ARE ADMIN!</strong><br><br>';
                    echo 'You should be able to access admin dashboard.';
                    echo '</div>';
                } else {
                    echo '<div class="error">';
                    echo '<strong>❌ YOU ARE NOT ADMIN!</strong><br><br>';
                    echo 'Your current role: <strong>' . strtoupper($user->role) . '</strong><br>';
                    echo 'You need <strong>ADMIN</strong> role to access admin dashboard.';
                    echo '</div>';
                }
                
                // User details table
                echo '<h2>👤 Current User Details:</h2>';
                echo '<table>';
                echo '<tr><th>Field</th><th>Value</th></tr>';
                echo '<tr><td>ID</td><td>' . $user->id . '</td></tr>';
                echo '<tr><td>Full Name</td><td>' . htmlspecialchars($user->full_name) . '</td></tr>';
                echo '<tr><td>Email</td><td>' . htmlspecialchars($user->email) . '</td></tr>';
                echo '<tr><td>Role</td><td><strong style="color: ' . ($isAdmin ? 'green' : 'red') . ';">' . strtoupper($user->role) . '</strong></td></tr>';
                echo '<tr><td>Created</td><td>' . $user->created_at . '</td></tr>';
                echo '</table>';
                
                // Actions
                echo '<h2>⚡ Actions:</h2>';
                
                if ($isAdmin) {
                    echo '<a href="/admin/dashboard" class="btn btn-success">📊 Go to Admin Dashboard</a>';
                } else {
                    echo '<div class="warning">';
                    echo '<strong>🔧 To make this user an admin:</strong><br><br>';
                    echo '1. Open phpMyAdmin or database tool<br>';
                    echo '2. Run this SQL:<br>';
                    echo '<code style="display: block; margin: 10px 0; padding: 10px; background: #1f2937; color: #d1d5db;">UPDATE users SET role = \'admin\' WHERE id = ' . $user->id . ';</code>';
                    echo '3. Refresh this page<br><br>';
                    echo 'OR<br><br>';
                    echo '<a href="/force-admin.php" class="btn btn-danger">Force Login as Admin User</a>';
                    echo '</div>';
                }
                
                echo '<a href="/dashboard" class="btn">Go to Dashboard</a>';
                
                // Logout form
                echo '<form method="POST" action="/logout" style="display: inline;">';
                echo '<input type="hidden" name="_token" value="' . csrf_token() . '">';
                echo '<button type="submit" class="btn btn-danger">Logout</button>';
                echo '</form>';
            }
            
            // Debug info
            echo '<h2>🐛 Debug Info:</h2>';
            echo '<table>';
            echo '<tr><th>Item</th><th>Status</th></tr>';
            echo '<tr><td>Laravel Auth</td><td>' . ($isLoggedIn ? '✅ Logged in' : '❌ Not logged in') . '</td></tr>';
            echo '<tr><td>Session ID</td><td>' . session_id() . '</td></tr>';
            echo '<tr><td>Session Status</td><td>' . (session_status() === PHP_SESSION_ACTIVE ? '✅ Active' : '❌ Inactive') . '</td></tr>';
            
            if ($isLoggedIn) {
                echo '<tr><td>User in Session</td><td>✅ Yes (ID: ' . $user->id . ')</td></tr>';
                echo '<tr><td>User Role</td><td><strong>' . strtoupper($user->role) . '</strong></td></tr>';
                echo '<tr><td>Can Access Admin?</td><td>' . ($user->role === 'admin' ? '✅ YES' : '❌ NO') . '</td></tr>';
            } else {
                echo '<tr><td>User in Session</td><td>❌ No</td></tr>';
            }
            
            echo '</table>';
            
            // Middleware check simulation
            echo '<h2>🛡️ Middleware Check Simulation:</h2>';
            echo '<div class="info">';
            
            if (!$isLoggedIn) {
                echo '<strong>❌ MIDDLEWARE RESULT: REDIRECT TO LOGIN</strong><br>';
                echo 'Reason: User not authenticated<br>';
                echo 'Action: Redirect to /login';
            } elseif ($user->role !== 'admin') {
                echo '<strong>❌ MIDDLEWARE RESULT: REDIRECT TO DASHBOARD</strong><br>';
                echo 'Reason: User role is "' . $user->role . '", not "admin"<br>';
                echo 'Action: Redirect to /dashboard with error message<br>';
                echo 'Message: "Akses ditolak. Halaman ini hanya untuk Admin."';
            } else {
                echo '<strong>✅ MIDDLEWARE RESULT: ACCESS GRANTED</strong><br>';
                echo 'Reason: User is admin<br>';
                echo 'Action: Show admin dashboard';
            }
            
            echo '</div>';
            
        } catch (\Exception $e) {
            echo '<div class="error">';
            echo '<strong>❌ ERROR:</strong><br>';
            echo htmlspecialchars($e->getMessage());
            echo '</div>';
        }
        ?>
    </div>
    
    <div style="text-align: center; color: #666; font-size: 12px; margin-top: 20px;">
        ⚠️ DELETE this file after debugging: <code>public/check-current-user.php</code>
    </div>
</body>
</html>
