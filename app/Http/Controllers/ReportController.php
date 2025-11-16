<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Card;
use App\Models\CardAssignment;
use App\Models\Comment;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Subtask;
use App\Models\TimeLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * ReportController
 * 
 * Controller untuk generate laporan lengkap sistem
 * - Hanya bisa diakses oleh Admin
 * - Menampilkan statistik projects, users, cards, dll
 * - Support grafik dan tabel
 * - Print-friendly layout
 */
class ReportController extends Controller
{
    /**
     * Constructor - Apply auth middleware
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    /**
     * Display report page
     * Only accessible by admin users
     */
    public function index()
    {
        $user = Auth::user();
        
        // Authorization: Only admin can access reports
        if ($user->role !== 'admin') {
            abort(403, 'Unauthorized. Only administrators can access reports.');
        }
        
        return view('reports.index');
    }
    
    /**
     * Get report data untuk AJAX request
     * Returns comprehensive statistics
     */
    public function getData(Request $request)
    {
        $user = Auth::user();
        
        // Authorization check
        if ($user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }
        
        // 1. Overview Statistics
        $overview = [
            'total_users' => User::count(),
            'total_projects' => Project::count(),
            'total_boards' => Board::count(),
            'total_cards' => Card::count(),
            'completed_cards' => Card::where('status', 'done')->count(),
            'in_progress_cards' => Card::where('status', 'in progress')->count(),
            'review_cards' => Card::where('status', 'review')->count(),
            'todo_cards' => Card::where('status', 'todo')->count(),
        ];
        
        // 2. User Statistics
        $userStats = [
            'admin_count' => User::where('role', 'admin')->count(),
            'member_count' => User::where('role', 'member')->count(),
            'working_users' => User::where('current_task_status', 'working')->count(),
            'idle_users' => User::where('current_task_status', 'idle')->count(),
        ];
        
        // 3. Project Statistics
        $today = now()->format('Y-m-d');
        $nextWeek = now()->addDays(7)->format('Y-m-d');
        
        $projectStats = [
            'total' => Project::count(),
            'overdue' => Project::where('deadline', '<', $today)->count(),
            'due_soon' => Project::whereBetween('deadline', [$today, $nextWeek])->count(),
        ];
        
        // 4. Card Status Distribution
        $cardByStatus = Card::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->status => $item->count];
            });
        
        // 5. Card Priority Distribution
        $cardByPriority = Card::select('priority', DB::raw('count(*) as count'))
            ->groupBy('priority')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->priority => $item->count];
            });
        
        // 6. Top Active Users (by assigned cards)
        $topUsers = User::select('users.id', 'users.full_name', 'users.username', DB::raw('COUNT(card_assignments.id) as card_count'))
            ->leftJoin('card_assignments', 'users.id', '=', 'card_assignments.user_id')
            ->groupBy('users.id', 'users.full_name', 'users.username')
            ->orderBy('card_count', 'desc')
            ->limit(10)
            ->get();
        
        // 7. Recent Projects
        $recentProjects = Project::with('creator:id,full_name')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($project) {
                return [
                    'id' => $project->id,
                    'project_name' => $project->project_name,
                    'creator' => $project->creator->full_name ?? 'Unknown',
                    'deadline' => $project->deadline,
                    'created_at' => $project->created_at->format('Y-m-d'),
                ];
            });
        
        // 8. Card Assignment Statistics
        $assignmentStats = [
            'total_assignments' => CardAssignment::count(),
            'completed_assignments' => CardAssignment::where('assignment_status', 'completed')->count(),
            'in_progress_assignments' => CardAssignment::where('assignment_status', 'in progress')->count(),
            'assigned_only' => CardAssignment::where('assignment_status', 'assigned')->count(),
        ];
        
        // 9. Time Tracking Statistics
        $timeStats = [
            'total_logs' => TimeLog::count(),
            'total_hours' => round((TimeLog::sum('duration_minutes') ?? 0) / 60, 2),
            'avg_hours' => round((TimeLog::avg('duration_minutes') ?? 0) / 60, 2),
        ];
        
        // 10. Subtask Statistics
        $subtaskStats = [
            'total_subtasks' => Subtask::count(),
            'completed_subtasks' => Subtask::where('status', 'done')->count(),
            'pending_subtasks' => Subtask::whereIn('status', ['to do', 'in progress'])->count(),
        ];
        
        // 11. Comment Statistics
        $totalCards = Card::count();
        $totalComments = Comment::count();
        $avgCommentsPerCard = $totalCards > 0 ? round($totalComments / $totalCards, 2) : 0;
        
        $commentStats = [
            'total_comments' => $totalComments,
            'avg_comments_per_card' => $avgCommentsPerCard,
        ];
        
        // 12. Project Member Statistics
        $memberStats = ProjectMember::select('role', DB::raw('count(*) as count'))
            ->groupBy('role')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->role => $item->count];
            });
        
        // 13. Monthly Card Creation Trend (last 6 months)
        $sixMonthsAgo = now()->subMonths(6)->format('Y-m-d');
        $monthlyCards = Card::select(
            DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
            DB::raw('count(*) as count')
        )
            ->where('created_at', '>=', $sixMonthsAgo)
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();
        
        // 14. Board Statistics
        $totalBoards = Board::count();
        $totalCardsInBoards = Card::count();
        $avgCardsPerBoard = $totalBoards > 0 ? round($totalCardsInBoards / $totalBoards, 2) : 0;
        
        $boardStats = [
            'total_boards' => $totalBoards,
            'avg_cards_per_board' => $avgCardsPerBoard,
        ];
        
        return response()->json([
            'success' => true,
            'data' => [
                'overview' => $overview,
                'users' => $userStats,
                'projects' => $projectStats,
                'cards' => [
                    'by_status' => $cardByStatus,
                    'by_priority' => $cardByPriority,
                ],
                'assignments' => $assignmentStats,
                'time_logs' => $timeStats,
                'subtasks' => $subtaskStats,
                'comments' => $commentStats,
                'project_members' => $memberStats,
                'monthly_trend' => $monthlyCards,
                'boards' => $boardStats,
                'top_users' => $topUsers,
                'recent_projects' => $recentProjects,
            ],
            'generated_at' => now()->format('Y-m-d H:i:s'),
        ]);
    }
}
