<!DOCTYPE html>
<html>
<head>
    <title>Quick Login - Admin</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 500px;
            margin: 100px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .login-box {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        .btn {
            background: #3b82f6;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            margin-top: 10px;
        }
        .btn:hover {
            background: #2563eb;
        }
        .info {
            background: #dbeafe;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #3b82f6;
        }
        .success {
            background: #d1fae5;
            border-left-color: #10b981;
        }
        .error {
            background: #fee2e2;
            border-left-color: #ef4444;
        }
        pre {
            background: #1f2937;
            color: #d1d5db;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h1>🔐 Quick Admin Login</h1>
        
        <?php
        // Check if already logged in
        session_start();
        
        require __DIR__ . '/../vendor/autoload.php';
        $app = require_once __DIR__ . '/../bootstrap/app.php';
        $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();
        
        $isLoggedIn = false;
        $currentUser = null;
        
        try {
            if (isset($_SESSION['user_id'])) {
                $currentUser = \App\Models\User::find($_SESSION['user_id']);
                if ($currentUser) {
                    $isLoggedIn = true;
                }
            }
        } catch (\Exception $e) {
            // Ignore
        }
        
        if ($isLoggedIn && $currentUser) {
            echo '<div class="info success">';
            echo '<strong>✅ Already logged in as:</strong><br>';
            echo 'Name: ' . htmlspecialchars($currentUser->full_name) . '<br>';
            echo 'Email: ' . htmlspecialchars($currentUser->email) . '<br>';
            echo 'Role: <strong>' . strtoupper($currentUser->role) . '</strong>';
            echo '</div>';
            
            if ($currentUser->role === 'admin') {
                echo '<a href="/admin/dashboard" class="btn">📊 Go to Admin Dashboard</a>';
            } else {
                echo '<div class="info error">';
                echo '<strong>⚠️ Warning:</strong> Your role is "' . $currentUser->role . '". You need "admin" role to access admin dashboard.';
                echo '</div>';
                echo '<a href="/dashboard" class="btn">Go to Dashboard</a>';
            }
            
            echo '<br><br>';
            echo '<form method="GET" action="?logout=1" style="display:inline;">';
            echo '<button type="submit" class="btn" style="background: #ef4444;">Logout</button>';
            echo '</form>';
            
        } else {
            // Handle logout
            if (isset($_GET['logout'])) {
                $_SESSION = [];
                session_destroy();
                header('Location: quick-login.php');
                exit;
            }
            
            // Handle login
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $email = $_POST['email'] ?? '';
                $password = $_POST['password'] ?? '';
                
                try {
                    $user = \App\Models\User::where('email', $email)->first();
                    
                    if ($user && password_verify($password, $user->password)) {
                        $_SESSION['user_id'] = $user->id;
                        
                        // Also login via Laravel Auth
                        auth()->login($user);
                        
                        echo '<div class="info success">';
                        echo '<strong>✅ Login successful!</strong><br>';
                        echo 'Redirecting to admin dashboard...';
                        echo '</div>';
                        echo '<script>setTimeout(() => window.location.href = "/admin/dashboard", 1000);</script>';
                    } else {
                        echo '<div class="info error">';
                        echo '<strong>❌ Login failed!</strong><br>';
                        echo 'Invalid email or password.';
                        echo '</div>';
                    }
                } catch (\Exception $e) {
                    echo '<div class="info error">';
                    echo '<strong>❌ Error:</strong> ' . htmlspecialchars($e->getMessage());
                    echo '</div>';
                }
            }
            
            // Show login form
            echo '<div class="info">';
            echo '<strong>ℹ️ Admin Credentials:</strong><br>';
            echo 'Email: <code>admin@test.com</code><br>';
            echo 'Password: <code>password</code>';
            echo '</div>';
            
            echo '<form method="POST">';
            echo '<input type="email" name="email" placeholder="Email" value="admin@test.com" required style="width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 5px;">';
            echo '<input type="password" name="password" placeholder="Password" value="password" required style="width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 5px;">';
            echo '<button type="submit" class="btn">🚀 Login as Admin</button>';
            echo '</form>';
        }
        
        echo '<hr style="margin: 30px 0; border: none; border-top: 1px solid #e5e7eb;">';
        
        echo '<h3>🔍 Debug Info:</h3>';
        echo '<div class="info">';
        echo '<strong>Session Status:</strong> ' . (session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Inactive') . '<br>';
        echo '<strong>Session ID:</strong> ' . (session_id() ?: 'None') . '<br>';
        echo '<strong>User in Session:</strong> ' . (isset($_SESSION['user_id']) ? 'Yes (ID: ' . $_SESSION['user_id'] . ')' : 'No') . '<br>';
        echo '<strong>Laravel Auth:</strong> ' . (auth()->check() ? 'Logged in' : 'Not logged in');
        echo '</div>';
        ?>
        
        <p style="text-align: center; color: #666; font-size: 12px; margin-top: 20px;">
            ⚠️ DELETE this file after use!
        </p>
    </div>
</body>
</html>
