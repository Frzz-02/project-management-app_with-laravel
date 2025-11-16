<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - STANDALONE</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <nav class="bg-white shadow-lg">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <h1 class="text-2xl font-bold text-gray-900">📊 ADMIN DASHBOARD - STANDALONE</h1>
                    </div>
                    <div class="flex items-center">
                        <span class="text-gray-600 mr-4">
                            <?php
                            require __DIR__ . '/../vendor/autoload.php';
                            $app = require_once __DIR__ . '/../bootstrap/app.php';
                            $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
                            $kernel->bootstrap();
                            
                            if (session_status() === PHP_SESSION_NONE) {
                                session_start();
                            }
                            
                            $user = null;
                            if (isset($_SESSION['user_id'])) {
                                $user = \App\Models\User::find($_SESSION['user_id']);
                            }
                            
                            if ($user) {
                                echo '👤 ' . htmlspecialchars($user->full_name) . ' (' . strtoupper($user->role) . ')';
                            } else {
                                echo '❌ NOT LOGGED IN';
                            }
                            ?>
                        </span>
                        <a href="/logout" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Logout</a>
                    </div>
                </div>
            </div>
        </nav>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <?php
            if (!$user) {
                echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">';
                echo '<strong>❌ NOT LOGGED IN!</strong><br>';
                echo '<a href="/force-admin.php" class="text-blue-600 underline">Click here to force login</a>';
                echo '</div>';
                exit;
            }
            
            if ($user->role !== 'admin') {
                echo '<div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">';
                echo '<strong>⚠️ ACCESS DENIED!</strong><br>';
                echo 'Your role is: <strong>' . strtoupper($user->role) . '</strong><br>';
                echo 'You need ADMIN role to access this page.';
                echo '</div>';
                exit;
            }
            
            // GET STATS
            $totalProjects = \App\Models\Project::count();
            $totalUsers = \App\Models\User::count();
            $totalCards = \App\Models\Card::count();
            $totalBoards = \App\Models\Board::count();
            
            $todoCards = \App\Models\Card::where('status', 'todo')->count();
            $inProgressCards = \App\Models\Card::where('status', 'in progress')->count();
            $reviewCards = \App\Models\Card::where('status', 'review')->count();
            $doneCards = \App\Models\Card::where('status', 'done')->count();
            ?>
            
            <!-- SUCCESS MESSAGE -->
            <div class="bg-green-100 border border-green-400 text-green-700 px-6 py-4 rounded-lg mb-8">
                <h2 class="text-2xl font-bold">🎉 SUCCESS! ADMIN DASHBOARD LOADED!</h2>
                <p class="mt-2">You are now viewing the admin dashboard. This is a standalone version without the problematic layout.</p>
            </div>

            <!-- STATS GRID -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Projects -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Total Projects</p>
                            <p class="text-3xl font-bold text-gray-900"><?= $totalProjects ?></p>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-full">
                            <span class="text-3xl">📁</span>
                        </div>
                    </div>
                </div>

                <!-- Total Users -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Total Users</p>
                            <p class="text-3xl font-bold text-gray-900"><?= $totalUsers ?></p>
                        </div>
                        <div class="bg-green-100 p-3 rounded-full">
                            <span class="text-3xl">👥</span>
                        </div>
                    </div>
                </div>

                <!-- Total Cards -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Total Cards</p>
                            <p class="text-3xl font-bold text-gray-900"><?= $totalCards ?></p>
                        </div>
                        <div class="bg-purple-100 p-3 rounded-full">
                            <span class="text-3xl">📋</span>
                        </div>
                    </div>
                </div>

                <!-- Total Boards -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Total Boards</p>
                            <p class="text-3xl font-bold text-gray-900"><?= $totalBoards ?></p>
                        </div>
                        <div class="bg-yellow-100 p-3 rounded-full">
                            <span class="text-3xl">📊</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CARD STATUS BREAKDOWN -->
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <h3 class="text-xl font-bold text-gray-900 mb-4">📈 Card Status Breakdown</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-gray-50 p-4 rounded">
                        <p class="text-gray-500 text-sm">To Do</p>
                        <p class="text-2xl font-bold text-gray-900"><?= $todoCards ?></p>
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                            <div class="bg-gray-600 h-2 rounded-full" style="width: <?= $totalCards > 0 ? ($todoCards / $totalCards * 100) : 0 ?>%"></div>
                        </div>
                    </div>

                    <div class="bg-blue-50 p-4 rounded">
                        <p class="text-blue-600 text-sm">In Progress</p>
                        <p class="text-2xl font-bold text-blue-900"><?= $inProgressCards ?></p>
                        <div class="w-full bg-blue-200 rounded-full h-2 mt-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: <?= $totalCards > 0 ? ($inProgressCards / $totalCards * 100) : 0 ?>%"></div>
                        </div>
                    </div>

                    <div class="bg-yellow-50 p-4 rounded">
                        <p class="text-yellow-600 text-sm">In Review</p>
                        <p class="text-2xl font-bold text-yellow-900"><?= $reviewCards ?></p>
                        <div class="w-full bg-yellow-200 rounded-full h-2 mt-2">
                            <div class="bg-yellow-600 h-2 rounded-full" style="width: <?= $totalCards > 0 ? ($reviewCards / $totalCards * 100) : 0 ?>%"></div>
                        </div>
                    </div>

                    <div class="bg-green-50 p-4 rounded">
                        <p class="text-green-600 text-sm">Done</p>
                        <p class="text-2xl font-bold text-green-900"><?= $doneCards ?></p>
                        <div class="w-full bg-green-200 rounded-full h-2 mt-2">
                            <div class="bg-green-600 h-2 rounded-full" style="width: <?= $totalCards > 0 ? ($doneCards / $totalCards * 100) : 0 ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RECENT PROJECTS -->
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <h3 class="text-xl font-bold text-gray-900 mb-4">📁 Recent Projects</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Project Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created By</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Deadline</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php
                            $projects = \App\Models\Project::with('creator')
                                ->orderBy('created_at', 'desc')
                                ->limit(10)
                                ->get();
                            
                            foreach ($projects as $project) {
                                $now = new DateTime();
                                $deadline = new DateTime($project->deadline);
                                $daysLeft = $now->diff($deadline)->days;
                                $isPast = $deadline < $now;
                                
                                echo '<tr>';
                                echo '<td class="px-6 py-4 whitespace-nowrap">';
                                echo '<div class="text-sm font-medium text-gray-900">' . htmlspecialchars($project->project_name) . '</div>';
                                echo '</td>';
                                echo '<td class="px-6 py-4 whitespace-nowrap">';
                                echo '<div class="text-sm text-gray-500">' . htmlspecialchars($project->creator->full_name ?? 'Unknown') . '</div>';
                                echo '</td>';
                                echo '<td class="px-6 py-4 whitespace-nowrap">';
                                echo '<div class="text-sm text-gray-900">' . $project->deadline . '</div>';
                                echo '</td>';
                                echo '<td class="px-6 py-4 whitespace-nowrap">';
                                if ($isPast) {
                                    echo '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Overdue</span>';
                                } elseif ($daysLeft <= 7) {
                                    echo '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Due Soon</span>';
                                } else {
                                    echo '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">On Track</span>';
                                }
                                echo '</td>';
                                echo '</tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- QUICK ACTIONS -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">⚡ Quick Actions</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <a href="/projects" class="block p-4 border-2 border-blue-500 rounded-lg hover:bg-blue-50 transition">
                        <div class="text-3xl mb-2">📁</div>
                        <div class="font-semibold text-gray-900">View All Projects</div>
                        <div class="text-sm text-gray-500">Manage projects</div>
                    </a>
                    
                    <a href="/dashboard" class="block p-4 border-2 border-green-500 rounded-lg hover:bg-green-50 transition">
                        <div class="text-3xl mb-2">🏠</div>
                        <div class="font-semibold text-gray-900">Go to Dashboard</div>
                        <div class="text-sm text-gray-500">Main dashboard</div>
                    </a>
                    
                    <a href="/reports" class="block p-4 border-2 border-purple-500 rounded-lg hover:bg-purple-50 transition">
                        <div class="text-3xl mb-2">📊</div>
                        <div class="font-semibold text-gray-900">View Reports</div>
                        <div class="text-sm text-gray-500">Analytics & stats</div>
                    </a>
                </div>
            </div>

            <!-- DEBUG INFO -->
            <div class="mt-8 bg-gray-900 text-white rounded-lg shadow p-6">
                <h3 class="text-xl font-bold mb-4">🔍 Debug Information</h3>
                <pre class="text-sm"><?php
                echo "✅ Admin Dashboard Loaded Successfully!\n\n";
                echo "User: " . $user->full_name . "\n";
                echo "Email: " . $user->email . "\n";
                echo "Role: " . strtoupper($user->role) . "\n";
                echo "Session ID: " . session_id() . "\n";
                echo "PHP Version: " . phpversion() . "\n";
                echo "Laravel Version: " . app()->version() . "\n";
                echo "\nThis is a STANDALONE admin dashboard (no layout.blade.php used).\n";
                echo "If you can see this, the problem is in the layout file!\n";
                ?></pre>
            </div>
        </div>
    </div>
</body>
</html>
