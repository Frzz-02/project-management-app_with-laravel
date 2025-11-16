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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\AdminReportExport;

/**
 * AdminReportController
 * 
 * Comprehensive admin reporting system dengan:
 * - Overview statistics
 * - Project performance analytics
 * - Team performance tracking
 * - Task analytics dan distribution
 * - Time tracking dan estimation
 * - Export ke Excel dan PDF
 */
class AdminReportController extends Controller
{
    /**
     * Constructor - Apply auth middleware
     */
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }
    
    /**
     * Display main report page
     */
    public function index(Request $request)
    {
        return view('admin.reports.index');
    }
    
    /**
     * Get overview statistics
     * Cached for 5 minutes for performance
     */
    public function getOverviewStats(Request $request)
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        
        $cacheKey = "admin_overview_stats_{$dateFrom}_{$dateTo}";
        
        $stats = Cache::remember($cacheKey, 300, function () use ($dateFrom, $dateTo) {
            $today = now()->format('Y-m-d');
            $nextWeek = now()->addDays(7)->format('Y-m-d');
            
            // Total Projects
            $totalProjects = Project::when($dateFrom, function($q) use ($dateFrom) {
                return $q->whereDate('created_at', '>=', $dateFrom);
            })->when($dateTo, function($q) use ($dateTo) {
                return $q->whereDate('created_at', '<=', $dateTo);
            })->count();
            
            // Active Projects (belum deadline)
            $activeProjects = Project::where('deadline', '>=', $today)
                ->when($dateFrom, function($q) use ($dateFrom) {
                    return $q->whereDate('created_at', '>=', $dateFrom);
                })->count();
            
            // Total Active Users (status working)
            $activeUsers = User::where('current_task_status', 'working')
                ->where('role', 'member')
                ->count();
            
            // Total Users
            $totalUsers = User::where('role', 'member')->count();
            
            // Total Cards
            $totalCards = Card::when($dateFrom, function($q) use ($dateFrom) {
                return $q->whereDate('created_at', '>=', $dateFrom);
            })->when($dateTo, function($q) use ($dateTo) {
                return $q->whereDate('created_at', '<=', $dateTo);
            })->count();
            
            // Completed Cards
            $completedCards = Card::where('status', 'done')
                ->when($dateFrom, function($q) use ($dateFrom) {
                    return $q->whereDate('created_at', '>=', $dateFrom);
                })->count();
            
            // Completion Rate
            $completionRate = $totalCards > 0 
                ? round(($completedCards / $totalCards) * 100, 2) 
                : 0;
            
            // Overdue Cards
            $overdueCards = Card::where('due_date', '<', $today)
                ->where('status', '!=', 'done')
                ->count();
            
            // Project Status Distribution
            $projectStatusDist = [
                'on_track' => Project::where('deadline', '>=', $nextWeek)->count(),
                'due_soon' => Project::whereBetween('deadline', [$today, $nextWeek])->count(),
                'overdue' => Project::where('deadline', '<', $today)->count(),
            ];
            
            // Task Status Distribution
            $taskStatusDist = Card::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get()
                ->mapWithKeys(function($item) {
                    return [$item->status => $item->count];
                });
            
            return [
                'total_projects' => $totalProjects,
                'active_projects' => $activeProjects,
                'active_users' => $activeUsers,
                'total_users' => $totalUsers,
                'total_cards' => $totalCards,
                'completed_cards' => $completedCards,
                'completion_rate' => $completionRate,
                'overdue_cards' => $overdueCards,
                'project_status_distribution' => $projectStatusDist,
                'task_status_distribution' => $taskStatusDist,
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => $stats,
            'generated_at' => now()->format('Y-m-d H:i:s'),
        ]);
    }
    
    /**
     * Get project performance data
     * Returns data for project timeline chart and project health table
     */
    public function getProjectPerformance(Request $request)
    {
        $projectId = $request->input('project_id');
        
        $projects = Project::with(['creator:id,full_name'])
            ->select('projects.*')
            ->selectRaw('COUNT(DISTINCT boards.id) as total_boards')
            ->selectRaw('COUNT(DISTINCT cards.id) as total_tasks')
            ->selectRaw('COUNT(DISTINCT CASE WHEN cards.status = "done" THEN cards.id END) as completed_tasks')
            ->selectRaw('ROUND(COUNT(DISTINCT CASE WHEN cards.status = "done" THEN cards.id END) * 100.0 / NULLIF(COUNT(DISTINCT cards.id), 0), 2) as completion_percentage')
            ->selectRaw('COUNT(DISTINCT CASE WHEN cards.due_date < CURDATE() AND cards.status != "done" THEN cards.id END) as overdue_tasks')
            ->selectRaw('DATEDIFF(projects.deadline, CURDATE()) as days_remaining')
            ->selectRaw('COUNT(DISTINCT project_members.id) as team_size')
            ->selectRaw('ROUND(SUM(cards.actual_hours), 2) as total_hours_spent')
            ->selectRaw('ROUND(SUM(cards.estimated_hours), 2) as total_hours_estimated')
            ->selectRaw('
                CASE 
                    WHEN DATEDIFF(projects.deadline, CURDATE()) < 0 THEN "Overdue"
                    WHEN COUNT(DISTINCT CASE WHEN cards.due_date < CURDATE() AND cards.status != "done" THEN cards.id END) > 5 THEN "At Risk"
                    WHEN ROUND(COUNT(DISTINCT CASE WHEN cards.status = "done" THEN cards.id END) * 100.0 / NULLIF(COUNT(DISTINCT cards.id), 0), 2) >= 80 THEN "On Track"
                    ELSE "Needs Attention"
                END as health_status
            ')
            ->leftJoin('project_members', 'projects.id', '=', 'project_members.project_id')
            ->leftJoin('boards', 'projects.id', '=', 'boards.project_id')
            ->leftJoin('cards', 'boards.id', '=', 'cards.board_id')
            ->when($projectId, function($q) use ($projectId) {
                return $q->where('projects.id', $projectId);
            })
            ->groupBy('projects.id', 'projects.slug', 'projects.project_name', 'projects.description', 'projects.created_by', 'projects.deadline', 'projects.created_at')
            ->orderByRaw('
                CASE health_status
                    WHEN "Overdue" THEN 1
                    WHEN "At Risk" THEN 2
                    WHEN "Needs Attention" THEN 3
                    WHEN "On Track" THEN 4
                END
            ')
            ->get()
            ->map(function($project) {
                return [
                    'id' => $project->id,
                    'project_name' => $project->project_name,
                    'creator' => $project->creator->full_name ?? 'Unknown',
                    'deadline' => $project->deadline,
                    'days_remaining' => $project->days_remaining,
                    'team_size' => $project->team_size ?? 0,
                    'total_boards' => $project->total_boards ?? 0,
                    'total_tasks' => $project->total_tasks ?? 0,
                    'completed_tasks' => $project->completed_tasks ?? 0,
                    'overdue_tasks' => $project->overdue_tasks ?? 0,
                    'completion_percentage' => $project->completion_percentage ?? 0,
                    'total_hours_spent' => $project->total_hours_spent ?? 0,
                    'total_hours_estimated' => $project->total_hours_estimated ?? 0,
                    'health_status' => $project->health_status,
                ];
            });
        
        return response()->json([
            'success' => true,
            'data' => $projects,
        ]);
    }
    
    /**
     * Get team performance data
     * Returns data for team workload chart and team performance table
     */
    public function getTeamPerformance(Request $request)
    {
        $userId = $request->input('user_id');
        
        $teamMembers = User::select('users.*')
            ->selectRaw('COUNT(DISTINCT card_assignments.id) as assigned_cards')
            ->selectRaw('COUNT(DISTINCT CASE WHEN card_assignments.assignment_status = "in progress" THEN card_assignments.id END) as in_progress_cards')
            ->selectRaw('COUNT(DISTINCT CASE WHEN card_assignments.assignment_status = "completed" THEN card_assignments.id END) as completed_cards')
            ->selectRaw('ROUND(SUM(cards.estimated_hours), 2) as total_estimated_hours')
            ->selectRaw('ROUND(SUM(cards.actual_hours), 2) as total_actual_hours')
            ->selectRaw('COUNT(DISTINCT CASE WHEN cards.due_date < CURDATE() AND cards.status != "done" THEN cards.id END) as overdue_tasks')
            ->selectRaw('ROUND(COUNT(DISTINCT CASE WHEN card_assignments.assignment_status = "completed" THEN card_assignments.id END) * 100.0 / NULLIF(COUNT(DISTINCT card_assignments.id), 0), 2) as completion_rate')
            ->leftJoin('card_assignments', 'users.id', '=', 'card_assignments.user_id')
            ->leftJoin('cards', 'card_assignments.card_id', '=', 'cards.id')
            ->where('users.role', 'member')
            ->when($userId, function($q) use ($userId) {
                return $q->where('users.id', $userId);
            })
            ->groupBy('users.id', 'users.username', 'users.full_name', 'users.current_task_status', 'users.email', 'users.role', 'users.email_verified_at', 'users.password', 'users.remember_token', 'users.created_at')
            ->orderByDesc('assigned_cards')
            ->get()
            ->map(function($user, $index) {
                return [
                    'rank' => $index + 1,
                    'id' => $user->id,
                    'full_name' => $user->full_name,
                    'username' => $user->username,
                    'current_status' => $user->current_task_status,
                    'assigned_cards' => $user->assigned_cards ?? 0,
                    'in_progress_cards' => $user->in_progress_cards ?? 0,
                    'completed_cards' => $user->completed_cards ?? 0,
                    'overdue_tasks' => $user->overdue_tasks ?? 0,
                    'total_estimated_hours' => $user->total_estimated_hours ?? 0,
                    'total_actual_hours' => $user->total_actual_hours ?? 0,
                    'completion_rate' => $user->completion_rate ?? 0,
                    'badge' => $index < 3 ? ['🥇', '🥈', '🥉'][$index] : null,
                ];
            });
        
        return response()->json([
            'success' => true,
            'data' => $teamMembers,
        ]);
    }
    
    /**
     * Get task analytics
     * Returns data for task distribution charts
     */
    public function getTaskAnalytics(Request $request)
    {
        // Card Priority Distribution
        $priorityDist = Card::select('priority', DB::raw('count(*) as count'))
            ->groupBy('priority')
            ->get()
            ->mapWithKeys(function($item) {
                return [$item->priority => $item->count];
            });
        
        // Card Status Distribution (detailed)
        $statusDist = Card::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->mapWithKeys(function($item) {
                return [$item->status => $item->count];
            });
        
        // Monthly Card Creation Trend (last 6 months)
        $sixMonthsAgo = now()->subMonths(6)->format('Y-m-d');
        $monthlyTrend = Card::select(
            DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
            DB::raw('count(*) as count')
        )
            ->where('created_at', '>=', $sixMonthsAgo)
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();
        
        // Estimated vs Actual Hours (last 6 months)
        $hoursComparison = Card::select(
            DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
            DB::raw('ROUND(SUM(estimated_hours), 2) as estimated'),
            DB::raw('ROUND(SUM(actual_hours), 2) as actual')
        )
            ->where('created_at', '>=', $sixMonthsAgo)
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => [
                'priority_distribution' => $priorityDist,
                'status_distribution' => $statusDist,
                'monthly_trend' => $monthlyTrend,
                'hours_comparison' => $hoursComparison,
            ],
        ]);
    }
    
    /**
     * Get overdue tasks
     * Returns critical alert table data
     */
    public function getOverdueTasks(Request $request)
    {
        $today = now()->format('Y-m-d');
        
        $overdueTasks = Card::with(['board.project:id,project_name', 'creator:id,full_name'])
            ->select('cards.*')
            ->selectRaw('DATEDIFF(CURDATE(), cards.due_date) as days_overdue')
            ->where('due_date', '<', $today)
            ->where('status', '!=', 'done')
            ->orderBy('due_date', 'asc')
            ->orderByRaw('FIELD(priority, "high", "medium", "low")')
            ->limit(50)
            ->get()
            ->map(function($card) {
                return [
                    'id' => $card->id,
                    'card_title' => $card->card_title,
                    'project_name' => $card->board->project->project_name ?? 'Unknown',
                    'board_name' => $card->board->board_name ?? 'Unknown',
                    'creator' => $card->creator->full_name ?? 'Unknown',
                    'due_date' => $card->due_date,
                    'days_overdue' => $card->days_overdue,
                    'status' => $card->status,
                    'priority' => $card->priority,
                    'estimated_hours' => $card->estimated_hours ?? 0,
                ];
            });
        
        return response()->json([
            'success' => true,
            'data' => $overdueTasks,
        ]);
    }
    
    /**
     * Export report to Excel
     */
    public function exportExcel(Request $request)
    {
        try {
            $dateFrom = $request->input('date_from');
            $dateTo = $request->input('date_to');
            
            $filename = 'admin_report_' . now()->format('Y-m-d_His') . '.xlsx';
            
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\AdminReportExport($dateFrom, $dateTo),
                $filename
            );
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to export Excel: ' . $e->getMessage());
        }
    }
    
    /**
     * Export report to PDF
     */
    public function exportPdf(Request $request)
    {
        try {
            // Load data untuk PDF
            $today = now()->format('Y-m-d');
            $nextWeek = now()->addDays(7)->format('Y-m-d');
            
            // Overview Stats
            $overview = [
                'total_projects' => Project::count(),
                'active_projects' => Project::where('deadline', '>=', $today)->count(),
                'total_users' => User::where('role', 'member')->count(),
                'active_users' => User::where('current_task_status', 'working')->where('role', 'member')->count(),
                'total_cards' => Card::count(),
                'completed_cards' => Card::where('status', 'done')->count(),
                'overdue_cards' => Card::where('due_date', '<', $today)->where('status', '!=', 'done')->count(),
            ];
            $overview['completion_rate'] = $overview['total_cards'] > 0 
                ? round(($overview['completed_cards'] / $overview['total_cards']) * 100, 2) 
                : 0;
            
            // Project Performance (Top 10)
            $projects = Project::with(['creator:id,full_name'])
                ->select('projects.*')
                ->selectRaw('COUNT(DISTINCT cards.id) as total_tasks')
                ->selectRaw('COUNT(DISTINCT CASE WHEN cards.status = "done" THEN cards.id END) as completed_tasks')
                ->selectRaw('ROUND(COUNT(DISTINCT CASE WHEN cards.status = "done" THEN cards.id END) * 100.0 / NULLIF(COUNT(DISTINCT cards.id), 0), 2) as completion_percentage')
                ->selectRaw('DATEDIFF(projects.deadline, CURDATE()) as days_remaining')
                ->selectRaw('
                    CASE 
                        WHEN DATEDIFF(projects.deadline, CURDATE()) < 0 THEN "Overdue"
                        WHEN DATEDIFF(projects.deadline, CURDATE()) <= 7 THEN "At Risk"
                        ELSE "On Track"
                    END as health_status
                ')
                ->leftJoin('boards', 'projects.id', '=', 'boards.project_id')
                ->leftJoin('cards', 'boards.id', '=', 'cards.board_id')
                ->groupBy('projects.id', 'projects.slug', 'projects.project_name', 'projects.description', 'projects.created_by', 'projects.deadline', 'projects.created_at')
                ->limit(10)
                ->get();
            
            // Team Performance (Top 10)
            $teamMembers = User::select('users.*')
                ->selectRaw('COUNT(DISTINCT card_assignments.id) as assigned_cards')
                ->selectRaw('COUNT(DISTINCT CASE WHEN card_assignments.assignment_status = "completed" THEN card_assignments.id END) as completed_cards')
                ->selectRaw('ROUND(COUNT(DISTINCT CASE WHEN card_assignments.assignment_status = "completed" THEN card_assignments.id END) * 100.0 / NULLIF(COUNT(DISTINCT card_assignments.id), 0), 2) as completion_rate')
                ->leftJoin('card_assignments', 'users.id', '=', 'card_assignments.user_id')
                ->where('users.role', 'member')
                ->groupBy('users.id', 'users.username', 'users.full_name', 'users.current_task_status', 'users.email', 'users.role', 'users.email_verified_at', 'users.password', 'users.remember_token', 'users.created_at')
                ->orderByDesc('assigned_cards')
                ->limit(10)
                ->get();
            
            // Overdue Tasks (Top 20)
            $overdueTasks = Card::with(['board.project:id,project_name', 'creator:id,full_name'])
                ->select('cards.*')
                ->selectRaw('DATEDIFF(CURDATE(), cards.due_date) as days_overdue')
                ->where('due_date', '<', $today)
                ->where('status', '!=', 'done')
                ->orderBy('due_date', 'asc')
                ->limit(20)
                ->get();
            
            $data = [
                'overview' => $overview,
                'projects' => $projects,
                'teamMembers' => $teamMembers,
                'overdueTasks' => $overdueTasks,
                'generated_at' => now()->format('Y-m-d H:i:s'),
            ];

            
            
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.reports.pdf', $data);
            $pdf->setPaper('a4', 'landscape');
            
            $filename = 'admin_report_' . now()->format('Y-m-d_His') . '.pdf';
            
            return $pdf->download($filename);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to export PDF: ' . $e->getMessage());
        }
    }
}
