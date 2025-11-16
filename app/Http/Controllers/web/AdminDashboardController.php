<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Models\{Project, Board, Card, Subtask, User, CardAssignment, Comment, TimeLog};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Auth, Cache};
use Carbon\Carbon;

/**
 * AdminDashboardController
 * 
 * Controller untuk halaman Dashboard Admin yang menampilkan:
 * - Key metrics dan statistik ringkasan
 * - Aktivitas terbaru dalam 24 jam
 * - Proyek yang berisiko (overdue/mendekati deadline)
 * - Status tim dan beban kerja
 * - Data untuk chart/visualisasi
 * - Upcoming deadlines
 * - System statistics mingguan
 */
class AdminDashboardController extends Controller
{
    /**
     * Tampilkan dashboard admin dengan semua statistik dan data
     * 
     * @return \Illuminate\View\View
     */
    public function index()
    {
        try {
            // 1️⃣ Key Metrics - Summary Stats Cards
            $stats = $this->getKeyMetrics();
            
            // 2️⃣ Recent Activities - 24 jam terakhir
            $activities = $this->getRecentActivities();
            
            // 3️⃣ Projects at Risk - Overdue atau mendekati deadline
            $projectsAtRisk = $this->getProjectsAtRisk();
            
            // 4️⃣ Team Status - Status kerja tim
            $teamStatus = $this->getTeamStatus();
            
            // 5️⃣ Charts Data - Data untuk visualisasi
            $charts = $this->getChartsData();
            
            // 6️⃣ Upcoming Deadlines - Task yang akan due
            $upcomingDeadlines = $this->getUpcomingDeadlines();
            
            // 7️⃣ System Stats - Overview mingguan
            $systemStats = $this->getSystemStats();

            return view('admin.dashboard', compact(
                'stats',
                'activities',
                'projectsAtRisk',
                'teamStatus',
                'charts',
                'upcomingDeadlines',
                'systemStats'
            ));
            
        } catch (\Exception $e) {
            // Log error dan redirect dengan pesan error
            return $e->getMessage();
            \Log::error('Admin Dashboard Error: ' . $e->getMessage());
            // return back()->with('error', 'Terjadi kesalahan saat memuat dashboard admin. Silakan coba lagi.');
        }
    }
    
    /**
     * 1️⃣ Dapatkan Key Metrics - Summary Stats Cards
     * 
     * @return array
     */
    private function getKeyMetrics(): array
    {
        $now = Carbon::now();
        $thirtyDaysAgo = $now->copy()->subDays(30);
        $weekStart = $now->copy()->startOfWeek();
        
        // Total proyek dan growth 30 hari terakhir
        $totalProjects = Project::count();
        $newProjectsLast30Days = Project::where('created_at', '>=', $thirtyDaysAgo)->count();
        $projectGrowth = $totalProjects > 0 
            ? round(($newProjectsLast30Days / $totalProjects) * 100, 1) 
            : 0;
        
        // Jumlah pengguna aktif (status 'working')
        $activeUsers = User::where('current_task_status', 'working')->count();
        $totalUsers = User::count();
        
        // Status task breakdown
        $taskStats = Card::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
        
        $totalTasks = array_sum($taskStats);
        $todoTasks = $taskStats['todo'] ?? 0;
        $inProgressTasks = $taskStats['in progress'] ?? 0;
        $reviewTasks = $taskStats['review'] ?? 0;
        $doneTasks = $taskStats['done'] ?? 0;
        
        // Task overdue dan due soon
        $overdueTasks = Card::where('due_date', '<', $now->toDateString())
            ->where('status', '!=', 'done')
            ->count();
        
        $dueSoonTasks = Card::whereBetween('due_date', [
                $now->toDateString(),
                $now->copy()->addDays(7)->toDateString()
            ])
            ->where('status', '!=', 'done')
            ->count();
        
        // Total jam kerja minggu ini
        $totalWorkHoursThisWeek = TimeLog::where('start_time', '>=', $weekStart)
            ->sum('duration_minutes');
        $workHoursFormatted = round($totalWorkHoursThisWeek / 60, 1);
        
        // Kondisi kesehatan proyek berdasarkan deadline
        $projectsOnTrack = Project::where('deadline', '>=', $now->toDateString())
            ->count();
        $projectsOverdue = Project::where('deadline', '<', $now->toDateString())
            ->count();
        $projectsAtRisk = Project::whereBetween('deadline', [
                $now->toDateString(),
                $now->copy()->addDays(7)->toDateString()
            ])
            ->count();
        
        // Hitung health percentage
        $healthyProjects = $projectsOnTrack - $projectsAtRisk;
        $projectHealth = $totalProjects > 0 
            ? round(($healthyProjects / $totalProjects) * 100, 1) 
            : 100;
        
        // Task completion rate
        $completionRate = $totalTasks > 0 
            ? round(($doneTasks / $totalTasks) * 100, 1) 
            : 0;
        
        return [
            'total_projects' => $totalProjects,
            'new_projects_30_days' => $newProjectsLast30Days,
            'project_growth' => $projectGrowth,
            
            'active_users' => $activeUsers,
            'total_users' => $totalUsers,
            'user_activity_rate' => $totalUsers > 0 ? round(($activeUsers / $totalUsers) * 100, 1) : 0,
            
            'total_tasks' => $totalTasks,
            'todo_tasks' => $todoTasks,
            'in_progress_tasks' => $inProgressTasks,
            'review_tasks' => $reviewTasks,
            'done_tasks' => $doneTasks,
            
            'overdue_tasks' => $overdueTasks,
            'due_soon_tasks' => $dueSoonTasks,
            
            'work_hours_this_week' => $workHoursFormatted,
            'work_hours_minutes' => $totalWorkHoursThisWeek,
            
            'project_health' => $projectHealth,
            'projects_on_track' => $projectsOnTrack,
            'projects_overdue' => $projectsOverdue,
            'projects_at_risk' => $projectsAtRisk,
            'healthy_projects' => $healthyProjects,
            
            'completion_rate' => $completionRate,
        ];
    }
    
    /**
     * 2️⃣ Dapatkan Recent Activities - 24 jam terakhir
     * 
     * @return array
     */
    private function getRecentActivities(): array
    {
        $last24Hours = Carbon::now()->subHours(24);
        $activities = [];
        
        // Proyek baru dibuat
        $newProjects = Project::with('creator')
            ->where('created_at', '>=', $last24Hours)
            ->get()
            ->map(function ($project) {
                return [
                    'type' => 'project_created',
                    'icon' => 'folder-plus',
                    'color' => 'blue',
                    'title' => 'Proyek baru dibuat',
                    'description' => $project->project_name,
                    'user' => $project->creator->full_name ?? 'Unknown',
                    'time' => $project->created_at,
                    'link' => route('projects.show', $project->id),
                ];
            });
        
        // Card/Task baru dibuat
        $newCards = Card::with(['creator', 'board.project'])
            ->where('created_at', '>=', $last24Hours)
            ->get()
            ->map(function ($card) {
                return [
                    'type' => 'card_created',
                    'icon' => 'clipboard-list',
                    'color' => 'green',
                    'title' => 'Task baru ditambahkan',
                    'description' => $card->card_title,
                    'user' => $card->creator->full_name ?? 'Unknown',
                    'project' => $card->board->project->project_name ?? '-',
                    'time' => $card->created_at,
                    'link' => route('projects.show', $card->board->project_id),
                ];
            });
        
        // Komentar baru
        $newComments = Comment::with(['user', 'card.board.project'])
            ->where('created_at', '>=', $last24Hours)
            ->get()
            ->map(function ($comment) {
                return [
                    'type' => 'comment_added',
                    'icon' => 'chat-bubble-left-right',
                    'color' => 'purple',
                    'title' => 'Komentar baru',
                    'description' => \Str::limit($comment->comment_text, 50),
                    'user' => $comment->user->full_name ?? 'Unknown',
                    'card' => $comment->card->card_title ?? '-',
                    'time' => $comment->created_at,
                    'link' => route('projects.show', $comment->card->board->project_id),
                ];
            });
        
        // Task selesai (completed_at dalam 24 jam terakhir)
        $completedTasks = CardAssignment::with(['card.board.project', 'user'])
            ->where('completed_at', '>=', $last24Hours)
            ->where('assignment_status', 'completed')
            ->get()
            ->map(function ($assignment) {
                return [
                    'type' => 'task_completed',
                    'icon' => 'check-circle',
                    'color' => 'emerald',
                    'title' => 'Task diselesaikan',
                    'description' => $assignment->card->card_title ?? '-',
                    'user' => $assignment->user->full_name ?? 'Unknown',
                    'project' => $assignment->card->board->project->project_name ?? '-',
                    'time' => $assignment->completed_at,
                    'link' => route('projects.show', $assignment->card->board->project_id),
                ];
            });
        
        // Gabungkan semua aktivitas
        $activities = collect()
            ->merge($newProjects)
            ->merge($newCards)
            ->merge($newComments)
            ->merge($completedTasks)
            ->sortByDesc('time')
            ->take(20) // Ambil 20 aktivitas terbaru
            ->values()
            ->all();
        
        return $activities;
    }
    
    /**
     * 3️⃣ Dapatkan Projects at Risk
     * 
     * @return array
     */
    private function getProjectsAtRisk(): array
    {
        $now = Carbon::now();
        $sevenDaysFromNow = $now->copy()->addDays(7);
        
        // Proyek yang sudah lewat deadline
        $overdueProjects = Project::with('creator')
            ->where('deadline', '<', $now->toDateString())
            ->withCount([
                'boards as total_cards' => function ($query) {
                    $query->join('cards', 'boards.id', '=', 'cards.board_id');
                },
                'boards as done_cards' => function ($query) {
                    $query->join('cards', 'boards.id', '=', 'cards.board_id')
                          ->where('cards.status', 'done');
                },
                'boards as overdue_cards' => function ($query) use ($now) {
                    $query->join('cards', 'boards.id', '=', 'cards.board_id')
                          ->where('cards.due_date', '<', $now->toDateString())
                          ->where('cards.status', '!=', 'done');
                }
            ])
            ->get()
            ->map(function ($project) use ($now) {
                $progress = $project->total_cards > 0 
                    ? round(($project->done_cards / $project->total_cards) * 100, 1) 
                    : 0;
                    
                $daysOverdue = Carbon::parse($project->deadline)->diffInDays($now, false);
                
                return [
                    'id' => $project->id,
                    'name' => $project->project_name,
                    'deadline' => $project->deadline,
                    'days_overdue' => abs($daysOverdue),
                    'risk_level' => 'critical',
                    'risk_label' => 'Terlambat',
                    'progress' => $progress,
                    'total_cards' => $project->total_cards,
                    'done_cards' => $project->done_cards,
                    'overdue_cards' => $project->overdue_cards,
                    'creator' => $project->creator->full_name ?? 'Unknown',
                ];
            });
        
        // Proyek mendekati deadline (7 hari)
        $nearDeadlineProjects = Project::with('creator')
            ->whereBetween('deadline', [
                $now->toDateString(),
                $sevenDaysFromNow->toDateString()
            ])
            ->withCount([
                'boards as total_cards' => function ($query) {
                    $query->join('cards', 'boards.id', '=', 'cards.board_id');
                },
                'boards as done_cards' => function ($query) {
                    $query->join('cards', 'boards.id', '=', 'cards.board_id')
                          ->where('cards.status', 'done');
                },
                'boards as overdue_cards' => function ($query) use ($now) {
                    $query->join('cards', 'boards.id', '=', 'cards.board_id')
                          ->where('cards.due_date', '<', $now->toDateString())
                          ->where('cards.status', '!=', 'done');
                }
            ])
            ->get()
            ->map(function ($project) use ($now) {
                $progress = $project->total_cards > 0 
                    ? round(($project->done_cards / $project->total_cards) * 100, 1) 
                    : 0;
                    
                $daysUntilDeadline = Carbon::parse($project->deadline)->diffInDays($now);
                
                // Tentukan risk level berdasarkan progress dan waktu tersisa
                $riskLevel = 'warning';
                if ($daysUntilDeadline <= 3 && $progress < 70) {
                    $riskLevel = 'high';
                } elseif ($daysUntilDeadline <= 5 && $progress < 50) {
                    $riskLevel = 'high';
                }
                
                return [
                    'id' => $project->id,
                    'name' => $project->project_name,
                    'deadline' => $project->deadline,
                    'days_until_deadline' => $daysUntilDeadline,
                    'risk_level' => $riskLevel,
                    'risk_label' => 'Mendekati Deadline',
                    'progress' => $progress,
                    'total_cards' => $project->total_cards,
                    'done_cards' => $project->done_cards,
                    'overdue_cards' => $project->overdue_cards,
                    'creator' => $project->creator->full_name ?? 'Unknown',
                ];
            });
        
        // Gabungkan dan sort berdasarkan risk level
        $allRiskyProjects = $overdueProjects
            ->merge($nearDeadlineProjects)
            ->sortBy([
                ['risk_level', 'desc'],
                ['progress', 'asc']
            ])
            ->values()
            ->all();
        
        return $allRiskyProjects;
    }
    
    /**
     * 4️⃣ Dapatkan Team Status
     * 
     * @return array
     */
    private function getTeamStatus(): array
    {
        $today = Carbon::today();
        
        $teamMembers = User::withCount([
                'cardAssignments as active_tasks' => function ($query) {
                    $query->where('assignment_status', 'in_progress');
                },
                'cardAssignments as completed_tasks' => function ($query) {
                    $query->where('assignment_status', 'completed');
                },
                'cardAssignments as total_assigned' => function ($query) {
                    $query->whereIn('assignment_status', ['assigned', 'in_progress']);
                }
            ])
            ->with(['cardAssignments' => function ($query) {
                $query->where('assignment_status', 'in_progress')
                      ->with('card')
                      ->latest('assigned_at')
                      ->limit(1);
            }])
            ->get()
            ->map(function ($user) use ($today) {
                // Total jam kerja hari ini
                $workHoursToday = TimeLog::where('user_id', $user->id)
                    ->whereDate('start_time', $today)
                    ->sum('duration_minutes');
                
                $workHoursFormatted = round($workHoursToday / 60, 1);
                
                // Current task yang sedang dikerjakan
                $currentTask = $user->cardAssignments->first();
                
                return [
                    'id' => $user->id,
                    'name' => $user->full_name,
                    'email' => $user->email,
                    'status' => $user->current_task_status,
                    'status_label' => $user->current_task_status === 'working' ? 'Sedang Bekerja' : 'Idle',
                    'active_tasks' => $user->active_tasks,
                    'completed_tasks' => $user->completed_tasks,
                    'total_assigned' => $user->total_assigned,
                    'work_hours_today' => $workHoursFormatted,
                    'current_task' => $currentTask ? [
                        'title' => $currentTask->card->card_title ?? '-',
                        'status' => $currentTask->assignment_status,
                        'priority' => $currentTask->card->priority ?? 'medium',
                    ] : null,
                ];
            })
            ->sortByDesc('status')
            ->values()
            ->all();
        
        return $teamMembers;
    }
    
    /**
     * 5️⃣ Dapatkan Charts Data - Data untuk visualisasi frontend
     * 
     * @return array
     */
    private function getChartsData(): array
    {
        $now = Carbon::now();
        $sevenDaysAgo = $now->copy()->subDays(7);
        
        // 📊 Distribusi status task
        $taskStatusDistribution = Card::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->map(function ($item) {
                return [
                    'status' => $item->status,
                    'label' => ucfirst($item->status),
                    'count' => $item->count,
                ];
            })
            ->keyBy('status')
            ->all();
        
        // 📊 Progress proyek (perbandingan done vs total cards)
        $projectProgress = Project::with('boards.cards')
            ->get()
            ->map(function ($project) {
                $totalCards = $project->boards->sum(function ($board) {
                    return $board->cards->count();
                });
                
                $doneCards = $project->boards->sum(function ($board) {
                    return $board->cards->where('status', 'done')->count();
                });
                
                $progress = $totalCards > 0 
                    ? round(($doneCards / $totalCards) * 100, 1) 
                    : 0;
                
                return [
                    'project_name' => $project->project_name,
                    'total_cards' => $totalCards,
                    'done_cards' => $doneCards,
                    'progress' => $progress,
                ];
            })
            ->sortByDesc('progress')
            ->take(10) // Top 10 projects
            ->values()
            ->all();
        
        // 📊 Beban kerja tim (jumlah card assigned per user)
        $teamWorkload = User::withCount('cardAssignments')
            ->having('card_assignments_count', '>', 0)
            ->get()
            ->map(function ($user) {
                // Breakdown by status
                $assigned = CardAssignment::where('user_id', $user->id)
                    ->where('assignment_status', 'assigned')
                    ->count();
                    
                $inProgress = CardAssignment::where('user_id', $user->id)
                    ->where('assignment_status', 'in_progress')
                    ->count();
                    
                $completed = CardAssignment::where('user_id', $user->id)
                    ->where('assignment_status', 'completed')
                    ->count();
                
                return [
                    'user_name' => $user->full_name,
                    'total_assignments' => $user->card_assignments_count,
                    'assigned' => $assigned,
                    'in_progress' => $inProgress,
                    'completed' => $completed,
                ];
            })
            ->sortByDesc('total_assignments')
            ->values()
            ->all();
        
        // 📊 Aktivitas harian selama 7 hari terakhir
        $dailyActivity = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i);
            $dateString = $date->toDateString();
            
            $cardsCreated = Card::whereDate('created_at', $dateString)->count();
            $commentsAdded = Comment::whereDate('created_at', $dateString)->count();
            $tasksCompleted = CardAssignment::whereDate('completed_at', $dateString)
                ->where('assignment_status', 'completed')
                ->count();
            
            $dailyActivity[] = [
                'date' => $dateString,
                'day_name' => $date->format('D'),
                'cards_created' => $cardsCreated,
                'comments_added' => $commentsAdded,
                'tasks_completed' => $tasksCompleted,
                'total_activity' => $cardsCreated + $commentsAdded + $tasksCompleted,
            ];
        }
        
        return [
            'task_status_distribution' => $taskStatusDistribution,
            'project_progress' => $projectProgress,
            'team_workload' => $teamWorkload,
            'daily_activity' => $dailyActivity,
        ];
    }
    
    /**
     * 6️⃣ Dapatkan Upcoming Deadlines - Task yang akan due
     * 
     * @return array
     */
    private function getUpcomingDeadlines(): array
    {
        $now = Carbon::now();
        $sevenDaysFromNow = $now->copy()->addDays(7);
        
        $upcomingCards = Card::with(['board.project', 'creator', 'assignments.user'])
            ->whereBetween('due_date', [
                $now->toDateString(),
                $sevenDaysFromNow->toDateString()
            ])
            ->where('status', '!=', 'done')
            ->orderByRaw("FIELD(priority, 'high', 'medium', 'low')")
            ->orderBy('due_date', 'asc')
            ->get()
            ->map(function ($card) use ($now) {
                $daysUntil = Carbon::parse($card->due_date)->diffInDays($now);
                $isToday = Carbon::parse($card->due_date)->isToday();
                $isTomorrow = Carbon::parse($card->due_date)->isTomorrow();
                
                // Urgency label
                if ($isToday) {
                    $urgencyLabel = 'Hari ini';
                    $urgencyColor = 'red';
                } elseif ($isTomorrow) {
                    $urgencyLabel = 'Besok';
                    $urgencyColor = 'orange';
                } elseif ($daysUntil <= 3) {
                    $urgencyLabel = $daysUntil . ' hari lagi';
                    $urgencyColor = 'yellow';
                } else {
                    $urgencyLabel = $daysUntil . ' hari lagi';
                    $urgencyColor = 'blue';
                }
                
                // Assigned users
                $assignedUsers = $card->assignments->map(function ($assignment) {
                    return $assignment->user->full_name ?? 'Unknown';
                })->implode(', ') ?: 'Belum ditugaskan';
                
                return [
                    'id' => $card->id,
                    'title' => $card->card_title,
                    'project' => $card->board->project->project_name ?? '-',
                    'due_date' => $card->due_date,
                    'days_until' => $daysUntil,
                    'urgency_label' => $urgencyLabel,
                    'urgency_color' => $urgencyColor,
                    'priority' => $card->priority,
                    'status' => $card->status,
                    'assigned_to' => $assignedUsers,
                    'estimated_hours' => $card->estimated_hours,
                    'link' => route('projects.show', $card->board->project_id),
                ];
            })
            ->all();
        
        return $upcomingCards;
    }
    
    /**
     * 7️⃣ Dapatkan System Stats - Weekly overview
     * 
     * @return array
     */
    private function getSystemStats(): array
    {
        $now = Carbon::now();
        $weekStart = $now->copy()->startOfWeek();
        $weekEnd = $now->copy()->endOfWeek();
        
        // Total jam kerja minggu ini
        $totalWorkMinutes = TimeLog::whereBetween('start_time', [$weekStart, $weekEnd])
            ->sum('duration_minutes');
        $totalWorkHours = round($totalWorkMinutes / 60, 1);
        
        // Jumlah komentar baru minggu ini
        $newCommentsThisWeek = Comment::whereBetween('created_at', [$weekStart, $weekEnd])
            ->count();
        
        // Persentase subtask selesai
        $totalSubtasks = Subtask::count();
        $completedSubtasks = Subtask::where('status', 'done')->count();
        $subtaskCompletionRate = $totalSubtasks > 0 
            ? round(($completedSubtasks / $totalSubtasks) * 100, 1) 
            : 0;
        
        // Task completed this week
        $tasksCompletedThisWeek = CardAssignment::whereBetween('completed_at', [$weekStart, $weekEnd])
            ->where('assignment_status', 'completed')
            ->count();
        
        // New projects this week
        $newProjectsThisWeek = Project::whereBetween('created_at', [$weekStart, $weekEnd])
            ->count();
        
        // Average completion time (cards completed this week)
        $avgCompletionTime = CardAssignment::whereBetween('completed_at', [$weekStart, $weekEnd])
            ->where('assignment_status', 'completed')
            ->whereNotNull('started_at')
            ->get()
            ->avg(function ($assignment) {
                if ($assignment->started_at && $assignment->completed_at) {
                    return Carbon::parse($assignment->started_at)
                        ->diffInHours(Carbon::parse($assignment->completed_at));
                }
                return 0;
            });
        
        $avgCompletionHours = round($avgCompletionTime ?? 0, 1);
        
        return [
            'total_work_hours' => $totalWorkHours,
            'total_work_minutes' => $totalWorkMinutes,
            'new_comments' => $newCommentsThisWeek,
            'subtask_completion_rate' => $subtaskCompletionRate,
            'total_subtasks' => $totalSubtasks,
            'completed_subtasks' => $completedSubtasks,
            'tasks_completed' => $tasksCompletedThisWeek,
            'new_projects' => $newProjectsThisWeek,
            'avg_completion_hours' => $avgCompletionHours,
            'week_start' => $weekStart->format('Y-m-d'),
            'week_end' => $weekEnd->format('Y-m-d'),
        ];
    }
}
