<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\Comment;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\TimeLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Controller untuk menampilkan statistik dan analytics di Admin Dashboard
 * 
 * Fitur:
 * - Overview statistik keseluruhan sistem
 * - Chart dan grafik performa
 * - Analisis produktivitas user
 * - Trend data time series
 */
class AdminStatisticsController extends Controller
{
    /**
     * Menampilkan halaman statistics dengan comprehensive analytics
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Time range filter
        $range = $request->get('range', '30'); // days
        $startDate = now()->subDays($range);

        // 1. Overall Statistics
        $overallStats = [
            'total_projects' => Project::count(),
            'active_projects' => Project::where('deadline', '>=', now())->count(), // Projects dengan deadline belum lewat
            'total_users' => User::where('role', 'member')->count(),
            'total_admins' => User::where('role', 'admin')->count(),
            'total_tasks' => Card::count(),
            'completed_tasks' => Card::where('status', 'done')->count(),
            'in_progress_tasks' => Card::where('status', 'in progress')->count(),
            'total_comments' => Comment::count(),
            'total_time_logged' => TimeLog::sum('duration_minutes'),
        ];

        // Calculate percentages
        $overallStats['task_completion_rate'] = $overallStats['total_tasks'] > 0 
            ? round(($overallStats['completed_tasks'] / $overallStats['total_tasks']) * 100, 1) 
            : 0;
        
        $overallStats['project_activity_rate'] = $overallStats['total_projects'] > 0 
            ? round(($overallStats['active_projects'] / $overallStats['total_projects']) * 100, 1) 
            : 0;

        // 2. Tasks by Status (for pie chart)
        $tasksByStatus = Card::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        // 3. Tasks by Priority
        $tasksByPriority = Card::select('priority', DB::raw('count(*) as count'))
            ->groupBy('priority')
            ->get()
            ->pluck('count', 'priority')
            ->toArray();

        // 4. Projects Created Over Time (last 30 days)
        $projectsTrend = Project::where('created_at', '>=', $startDate)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();

        // 5. Tasks Created Over Time
        $tasksTrend = Card::where('created_at', '>=', $startDate)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();

        // 6. Top Active Users (by cards created)
        $topUsers = User::withCount(['createdCards' => function ($query) use ($startDate) {
                $query->where('created_at', '>=', $startDate);
            }])
            ->where('role', 'member')
            ->orderBy('created_cards_count', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($user) {
                return [
                    'name' => $user->full_name,
                    'email' => $user->email,
                    'tasks_created' => $user->created_cards_count,
                    'avatar' => substr($user->full_name, 0, 1),
                ];
            });

        // 7. Most Active Projects (by card count)
        $topProjects = Project::withCount(['boards as cards_count' => function ($query) {
                $query->join('cards', 'boards.id', '=', 'cards.board_id');
            }])
            ->orderBy('cards_count', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($project) {
                return [
                    'name' => $project->project_name,
                    'tasks' => $project->cards_count,
                    'deadline' => $project->deadline, // Gunakan deadline, bukan status
                    'members' => $project->members->count(),
                ];
            });

        // 8. Time Tracking Statistics
        $timeStats = [
            'total_hours' => round(TimeLog::sum('duration_minutes') / 60, 1),
            'avg_per_task' => Card::whereHas('timeLogs')->count() > 0 
                ? round(TimeLog::sum('duration_minutes') / Card::whereHas('timeLogs')->count(), 1) 
                : 0,
            'most_tracked_user' => TimeLog::select('user_id', DB::raw('SUM(duration_minutes) as total'))
                ->groupBy('user_id')
                ->orderBy('total', 'desc')
                ->with('user')
                ->first(),
        ];

        // 9. Comments Statistics
        $commentsStats = [
            'total' => Comment::count(),
            'recent' => Comment::where('created_at', '>=', $startDate)->count(),
            'avg_per_task' => Card::whereHas('comments')->count() > 0 
                ? round(Comment::count() / Card::whereHas('comments')->count(), 1) 
                : 0,
        ];

        // 10. User Growth (last 12 months)
        $userGrowth = User::where('created_at', '>=', now()->subMonths(12))
            ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('count(*) as count'))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('count', 'month')
            ->toArray();

        // 11. Task Completion Trend (last 30 days)
        // Note: Cards tidak punya updated_at, jadi kita pakai created_at untuk cards yang sudah done
        $completionTrend = Card::where('status', 'done')
            ->where('created_at', '>=', $startDate)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();

        return view('admin.statistics.index', [
            'overallStats' => $overallStats,
            'tasksByStatus' => $tasksByStatus,
            'tasksByPriority' => $tasksByPriority,
            'projectsTrend' => $projectsTrend,
            'tasksTrend' => $tasksTrend,
            'topUsers' => $topUsers,
            'topProjects' => $topProjects,
            'timeStats' => $timeStats,
            'commentsStats' => $commentsStats,
            'userGrowth' => $userGrowth,
            'completionTrend' => $completionTrend,
            'range' => $range,
        ]);
    }
}
