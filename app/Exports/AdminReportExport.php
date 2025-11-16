<?php

namespace App\Exports;

use App\Models\Card;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * AdminReportExport
 * 
 * Export comprehensive admin report ke Excel dengan multiple sheets:
 * 1. Overview Statistics
 * 2. Project Performance
 * 3. Team Performance
 * 4. Task Analytics
 * 5. Overdue Tasks
 */
class AdminReportExport implements WithMultipleSheets
{
    protected $dateFrom;
    protected $dateTo;
    
    public function __construct($dateFrom = null, $dateTo = null)
    {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
    }
    
    public function sheets(): array
    {
        return [
            new OverviewSheet($this->dateFrom, $this->dateTo),
            new ProjectPerformanceSheet(),
            new TeamPerformanceSheet(),
            new OverdueTasksSheet(),
        ];
    }
}

/**
 * Overview Statistics Sheet
 */
class OverviewSheet implements FromCollection, WithTitle, WithHeadings, WithStyles
{
    protected $dateFrom;
    protected $dateTo;
    
    public function __construct($dateFrom = null, $dateTo = null)
    {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
    }
    
    public function collection()
    {
        $today = now()->format('Y-m-d');
        
        $totalProjects = Project::when($this->dateFrom, function($q) {
            return $q->whereDate('created_at', '>=', $this->dateFrom);
        })->count();
        
        $activeProjects = Project::where('deadline', '>=', $today)->count();
        $overdueProjects = Project::where('deadline', '<', $today)->count();
        
        $totalUsers = User::where('role', 'member')->count();
        $activeUsers = User::where('current_task_status', 'working')->where('role', 'member')->count();
        
        $totalCards = Card::count();
        $completedCards = Card::where('status', 'done')->count();
        $overdueCards = Card::where('due_date', '<', $today)->where('status', '!=', 'done')->count();
        
        $completionRate = $totalCards > 0 ? round(($completedCards / $totalCards) * 100, 2) : 0;
        
        return collect([
            ['Metric', 'Value'],
            ['Total Projects', $totalProjects],
            ['Active Projects', $activeProjects],
            ['Overdue Projects', $overdueProjects],
            ['Total Team Members', $totalUsers],
            ['Active Members', $activeUsers],
            ['Total Tasks', $totalCards],
            ['Completed Tasks', $completedCards],
            ['Overdue Tasks', $overdueCards],
            ['Completion Rate', $completionRate . '%'],
            ['Generated At', now()->format('Y-m-d H:i:s')],
        ]);
    }
    
    public function title(): string
    {
        return 'Overview Statistics';
    }
    
    public function headings(): array
    {
        return [];
    }
    
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            'A' => ['font' => ['bold' => true]],
        ];
    }
}

/**
 * Project Performance Sheet
 */
class ProjectPerformanceSheet implements FromCollection, WithTitle, WithHeadings, WithStyles
{
    public function collection()
    {
        $projects = Project::with(['creator:id,full_name'])
            ->select('projects.*')
            ->selectRaw('COUNT(DISTINCT cards.id) as total_tasks')
            ->selectRaw('COUNT(DISTINCT CASE WHEN cards.status = "done" THEN cards.id END) as completed_tasks')
            ->selectRaw('ROUND(COUNT(DISTINCT CASE WHEN cards.status = "done" THEN cards.id END) * 100.0 / NULLIF(COUNT(DISTINCT cards.id), 0), 2) as completion_percentage')
            ->selectRaw('COUNT(DISTINCT CASE WHEN cards.due_date < CURDATE() AND cards.status != "done" THEN cards.id END) as overdue_tasks')
            ->selectRaw('DATEDIFF(projects.deadline, CURDATE()) as days_remaining')
            ->selectRaw('COUNT(DISTINCT project_members.id) as team_size')
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
            ->groupBy('projects.id', 'projects.slug', 'projects.project_name', 'projects.description', 'projects.created_by', 'projects.deadline', 'projects.created_at')
            ->get();
        
        return $projects->map(function($project) {
            return [
                'project_name' => $project->project_name,
                'creator' => $project->creator->full_name ?? 'Unknown',
                'health_status' => $project->health_status,
                'deadline' => $project->deadline,
                'days_remaining' => $project->days_remaining,
                'team_size' => $project->team_size ?? 0,
                'total_tasks' => $project->total_tasks ?? 0,
                'completed_tasks' => $project->completed_tasks ?? 0,
                'overdue_tasks' => $project->overdue_tasks ?? 0,
                'completion_percentage' => ($project->completion_percentage ?? 0) . '%',
            ];
        });
    }
    
    public function title(): string
    {
        return 'Project Performance';
    }
    
    public function headings(): array
    {
        return [
            'Project Name',
            'Creator',
            'Health Status',
            'Deadline',
            'Days Remaining',
            'Team Size',
            'Total Tasks',
            'Completed',
            'Overdue',
            'Completion %',
        ];
    }
    
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'color' => ['rgb' => 'E2E8F0']]],
        ];
    }
}

/**
 * Team Performance Sheet
 */
class TeamPerformanceSheet implements FromCollection, WithTitle, WithHeadings, WithStyles
{
    public function collection()
    {
        $teamMembers = User::select('users.*')
            ->selectRaw('COUNT(DISTINCT card_assignments.id) as assigned_cards')
            ->selectRaw('COUNT(DISTINCT CASE WHEN card_assignments.assignment_status = "in progress" THEN card_assignments.id END) as in_progress_cards')
            ->selectRaw('COUNT(DISTINCT CASE WHEN card_assignments.assignment_status = "completed" THEN card_assignments.id END) as completed_cards')
            ->selectRaw('ROUND(SUM(cards.estimated_hours), 2) as total_estimated_hours')
            ->selectRaw('ROUND(SUM(cards.actual_hours), 2) as total_actual_hours')
            ->selectRaw('ROUND(COUNT(DISTINCT CASE WHEN card_assignments.assignment_status = "completed" THEN card_assignments.id END) * 100.0 / NULLIF(COUNT(DISTINCT card_assignments.id), 0), 2) as completion_rate')
            ->leftJoin('card_assignments', 'users.id', '=', 'card_assignments.user_id')
            ->leftJoin('cards', 'card_assignments.card_id', '=', 'cards.id')
            ->where('users.role', 'member')
            ->groupBy('users.id', 'users.username', 'users.full_name', 'users.current_task_status', 'users.email', 'users.role', 'users.email_verified_at', 'users.password', 'users.remember_token', 'users.created_at')
            ->orderByDesc('assigned_cards')
            ->get();
        
        return $teamMembers->map(function($user, $index) {
            return [
                'rank' => $index + 1,
                'full_name' => $user->full_name,
                'username' => $user->username,
                'status' => $user->current_task_status,
                'assigned_cards' => $user->assigned_cards ?? 0,
                'in_progress' => $user->in_progress_cards ?? 0,
                'completed' => $user->completed_cards ?? 0,
                'completion_rate' => ($user->completion_rate ?? 0) . '%',
                'estimated_hours' => $user->total_estimated_hours ?? 0,
                'actual_hours' => $user->total_actual_hours ?? 0,
            ];
        });
    }
    
    public function title(): string
    {
        return 'Team Performance';
    }
    
    public function headings(): array
    {
        return [
            'Rank',
            'Name',
            'Username',
            'Status',
            'Assigned',
            'In Progress',
            'Completed',
            'Completion Rate',
            'Est. Hours',
            'Actual Hours',
        ];
    }
    
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'color' => ['rgb' => 'E2E8F0']]],
        ];
    }
}

/**
 * Overdue Tasks Sheet
 */
class OverdueTasksSheet implements FromCollection, WithTitle, WithHeadings, WithStyles
{
    public function collection()
    {
        $today = now()->format('Y-m-d');
        
        $overdueTasks = Card::with(['board.project:id,project_name', 'creator:id,full_name'])
            ->select('cards.*')
            ->selectRaw('DATEDIFF(CURDATE(), cards.due_date) as days_overdue')
            ->where('due_date', '<', $today)
            ->where('status', '!=', 'done')
            ->orderBy('due_date', 'asc')
            ->orderByRaw('FIELD(priority, "high", "medium", "low")')
            ->limit(100)
            ->get();
        
        return $overdueTasks->map(function($card) {
            return [
                'card_title' => $card->card_title,
                'project_name' => $card->board->project->project_name ?? 'Unknown',
                'creator' => $card->creator->full_name ?? 'Unknown',
                'due_date' => $card->due_date,
                'days_overdue' => $card->days_overdue,
                'status' => $card->status,
                'priority' => $card->priority,
            ];
        });
    }
    
    public function title(): string
    {
        return 'Overdue Tasks';
    }
    
    public function headings(): array
    {
        return [
            'Task Title',
            'Project',
            'Creator',
            'Due Date',
            'Days Overdue',
            'Status',
            'Priority',
        ];
    }
    
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'color' => ['rgb' => 'FEE2E2']]],
        ];
    }
}
