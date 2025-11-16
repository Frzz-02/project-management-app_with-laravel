<!DOCTYPE html>
<html>
<head>
    <title>FORCE LOGIN ADMIN</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 100%;
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
            font-size: 28px;
            text-align: center;
        }
        .status {
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 16px;
            line-height: 1.6;
        }
        .success { background: #d1fae5; color: #065f46; border-left: 5px solid #10b981; }
        .error { background: #fee2e2; color: #991b1b; border-left: 5px solid #ef4444; }
        .info { background: #dbeafe; color: #1e40af; border-left: 5px solid #3b82f6; }
        .warning { background: #fef3c7; color: #92400e; border-left: 5px solid #f59e0b; }
        .btn {
            display: block;
            width: 100%;
            padding: 15px;
            background: #3b82f6;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 15px;
            transition: all 0.3s;
        }
        .btn:hover { background: #2563eb; transform: translateY(-2px); }
        .btn-danger { background: #ef4444; }
        .btn-danger:hover { background: #dc2626; }
        .btn-success { background: #10b981; }
        .btn-success:hover { background: #059669; }
        pre {
            background: #1f2937;
            color: #d1d5db;
            padding: 15px;
            border-radius: 8px;
            overflow-x: auto;
            font-size: 14px;
            margin: 15px 0;
        }
        .code { background: #f3f4f6; padding: 2px 6px; border-radius: 4px; font-family: monospace; }
        hr { border: none; border-top: 2px solid #e5e7eb; margin: 30px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔥 FORCE LOGIN ADMIN 🔥</h1>
        
        <?php
        // FORCE SESSION START
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // LOAD LARAVEL
        require __DIR__ . '/../vendor/autoload.php';
        $app = require_once __DIR__ . '/../bootstrap/app.php';
        $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
        $kernel->bootstrap();
        
        // HANDLE ACTIONS
        $action = $_GET['action'] ?? '';
        
        if ($action === 'force-login') {
            echo '<div class="status info"><strong>⚡ FORCING LOGIN...</strong></div>';
            
            try {
                // Find admin user
                $admin = \App\Models\User::where('role', 'admin')->first();
                
                if (!$admin) {
                    echo '<div class="status error">';
                    echo '<strong>❌ NO ADMIN USER FOUND!</strong><br><br>';
                    echo 'Creating admin user now...';
                    echo '</div>';
                    
                    // CREATE ADMIN USER
                    $admin = \App\Models\User::create([
                        'full_name' => 'Super Admin',
                        'email' => 'admin@test.com',
                        'password' => bcrypt('password'),
                        'role' => 'admin',
                    ]);
                    
                    echo '<div class="status success">';
                    echo '<strong>✅ ADMIN USER CREATED!</strong><br>';
                    echo 'Email: admin@test.com<br>';
                    echo 'Password: password';
                    echo '</div>';
                }
                
                // FORCE LOGIN VIA SESSION
                $_SESSION['login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d'] = $admin->id;
                $_SESSION['user_id'] = $admin->id;
                $_SESSION['user_role'] = 'admin';
                
                // ALSO TRY AUTH LOGIN
                auth()->loginUsingId($admin->id, true);
                
                echo '<div class="status success">';
                echo '<strong>🎉 LOGIN FORCED SUCCESSFULLY!</strong><br><br>';
                echo 'Logged in as: <strong>' . htmlspecialchars($admin->full_name) . '</strong><br>';
                echo 'Email: <strong>' . htmlspecialchars($admin->email) . '</strong><br>';
                echo 'Role: <strong>ADMIN</strong><br>';
                echo 'Session ID: <span class="code">' . session_id() . '</span>';
                echo '</div>';
                
                echo '<a href="/admin/dashboard" class="btn btn-success">📊 GO TO ADMIN DASHBOARD NOW!</a>';
                echo '<a href="?action=test-access" class="btn">🧪 TEST ACCESS FIRST</a>';
                
            } catch (\Exception $e) {
                echo '<div class="status error">';
                echo '<strong>❌ ERROR:</strong><br>';
                echo htmlspecialchars($e->getMessage());
                echo '<br><br><strong>Stack Trace:</strong><pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
                echo '</div>';
            }
            
        } elseif ($action === 'test-access') {
            echo '<div class="status info"><strong>🧪 TESTING ACCESS...</strong></div>';
            
            // TEST 1: Session
            echo '<div class="status ' . (isset($_SESSION['user_id']) ? 'success' : 'error') . '">';
            echo '<strong>1. Session Check:</strong> ';
            echo isset($_SESSION['user_id']) ? '✅ User ID in session: ' . $_SESSION['user_id'] : '❌ No user in session';
            echo '</div>';
            
            // TEST 2: Auth
            $isAuth = false;
            $authUser = null;
            try {
                $isAuth = auth()->check();
                $authUser = auth()->user();
            } catch (\Exception $e) {
                // ignore
            }
            
            echo '<div class="status ' . ($isAuth ? 'success' : 'error') . '">';
            echo '<strong>2. Laravel Auth:</strong> ';
            if ($isAuth && $authUser) {
                echo '✅ Logged in as: ' . htmlspecialchars($authUser->full_name);
                echo '<br>Role: <strong>' . strtoupper($authUser->role) . '</strong>';
            } else {
                echo '❌ Not authenticated';
            }
            echo '</div>';
            
            // TEST 3: Admin Check
            if ($isAuth && $authUser) {
                echo '<div class="status ' . ($authUser->role === 'admin' ? 'success' : 'error') . '">';
                echo '<strong>3. Admin Access:</strong> ';
                echo $authUser->role === 'admin' ? '✅ HAS ADMIN ROLE' : '❌ NOT ADMIN (Role: ' . $authUser->role . ')';
                echo '</div>';
                
                if ($authUser->role === 'admin') {
                    echo '<a href="/admin/dashboard" class="btn btn-success">🚀 GO TO ADMIN DASHBOARD!</a>';
                } else {
                    echo '<div class="status warning">';
                    echo '<strong>⚠️ FIX ROLE IN DATABASE:</strong><br>';
                    echo 'Run this SQL in phpMyAdmin:<br>';
                    echo '<pre>UPDATE users SET role = \'admin\' WHERE id = ' . $authUser->id . ';</pre>';
                    echo '</div>';
                }
            } else {
                echo '<a href="?action=force-login" class="btn btn-danger">⚡ FORCE LOGIN AGAIN</a>';
            }
            
        } elseif ($action === 'clear-cache') {
            echo '<div class="status info"><strong>🧹 CLEARING CACHE...</strong></div>';
            
            try {
                // Clear Laravel cache
                Artisan::call('config:clear');
                Artisan::call('route:clear');
                Artisan::call('view:clear');
                Artisan::call('cache:clear');
                
                echo '<div class="status success"><strong>✅ CACHE CLEARED!</strong></div>';
                echo '<a href="?action=force-login" class="btn">⚡ NOW FORCE LOGIN</a>';
                
            } catch (\Exception $e) {
                echo '<div class="status error">';
                echo '<strong>❌ ERROR:</strong> ' . htmlspecialchars($e->getMessage());
                echo '</div>';
            }
            
        } else {
            // DEFAULT VIEW
            echo '<div class="status info">';
            echo '<strong>📋 CURRENT STATUS:</strong><br><br>';
            
            // Check session
            if (isset($_SESSION['user_id'])) {
                $user = \App\Models\User::find($_SESSION['user_id']);
                if ($user) {
                    echo '✅ User in session: ' . htmlspecialchars($user->full_name) . '<br>';
                    echo 'Role: <strong>' . strtoupper($user->role) . '</strong><br>';
                    
                    if ($user->role === 'admin') {
                        echo '<br><a href="/admin/dashboard" class="btn btn-success">📊 GO TO ADMIN DASHBOARD</a>';
                    } else {
                        echo '<br><div class="status warning">⚠️ Your role is not admin!</div>';
                    }
                } else {
                    echo '❌ Session user not found in database<br>';
                }
            } else {
                echo '❌ No user in session<br>';
            }
            
            // Check auth
            try {
                if (auth()->check()) {
                    $authUser = auth()->user();
                    echo '<br>✅ Laravel Auth: ' . htmlspecialchars($authUser->full_name);
                } else {
                    echo '<br>❌ Laravel Auth: Not logged in';
                }
            } catch (\Exception $e) {
                echo '<br>❌ Laravel Auth: Error - ' . htmlspecialchars($e->getMessage());
            }
            
            echo '</div>';
            
            echo '<hr>';
            
            echo '<div class="status warning">';
            echo '<strong>🎯 CHOOSE ACTION:</strong>';
            echo '</div>';
            
            echo '<a href="?action=clear-cache" class="btn">🧹 1. CLEAR CACHE FIRST</a>';
            echo '<a href="?action=force-login" class="btn btn-danger">⚡ 2. FORCE LOGIN AS ADMIN</a>';
            echo '<a href="?action=test-access" class="btn">🧪 3. TEST ACCESS</a>';
            
            echo '<hr>';
            
            echo '<div class="status info">';
            echo '<strong>💡 TROUBLESHOOTING:</strong><br><br>';
            echo '1. Click "CLEAR CACHE FIRST"<br>';
            echo '2. Click "FORCE LOGIN AS ADMIN"<br>';
            echo '3. Click "GO TO ADMIN DASHBOARD"<br>';
            echo '4. If still fails, send screenshot!';
            echo '</div>';
        }
        
        echo '<hr>';
        echo '<div style="text-align: center; color: #666; font-size: 14px;">';
        echo '⚠️ DELETE THIS FILE AFTER USE!<br>';
        echo '<code>public/force-admin.php</code>';
        echo '</div>';
        ?>
    </div>
</body>
</html>
