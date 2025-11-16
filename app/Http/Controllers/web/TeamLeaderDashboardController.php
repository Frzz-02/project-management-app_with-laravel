<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\Comment;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * TeamLeaderDashboardController
 * 
 * Controller untuk dashboard Team Leader dengan fitur:
 * - Overview statistics (projects, tasks, reviews, overdue)
 * - List projects yang di-lead dengan completion rate
 * - Tasks requiring review (approve/reject)
 * - Team activity dan workload
 * - Recent comments timeline
 * - Charts: Task status distribution & Team workload
 * 
 * Optimization:
 * - Caching (5 menit) untuk query berat
 * - Eager loading untuk prevent N+1
 * - Query optimization dengan withCount & withSum
 */
class TeamLeaderDashboardController extends Controller
{
    /**
     * Main dashboard page
     * Aggregate all data untuk team leader dashboard
     */
    public function index()
    {
        // Get data dengan caching (5 menit)
        $cacheKey = 'team_leader_dashboard_' . Auth::id();
        
        $data = Cache::remember($cacheKey, 300, function () {
            return [
                'overviewStats' => $this->getOverviewStats(),
                'myProjects' => $this->getMyProjects(),
                'tasksRequiringReview' => $this->getTasksRequiringReview(),
                'teamActivity' => $this->getTeamActivity(),
                'recentComments' => $this->getRecentComments(),
            ];
        });
        
        return view('team-leader.dashboard', $data);
    }
    
    /**
     * Get overview statistics untuk team leader
     * - Total projects yang di-lead
     * - Total tasks di semua projects
     * - Tasks need review
     * - Tasks overdue
     * - Active members hari ini
     */
    private function getOverviewStats()
    {
        $userId = Auth::id();
        
        // Get project IDs dimana user adalah team lead
        $myProjectIds = ProjectMember::where('user_id', $userId)
            ->where('role', 'team lead')
            ->pluck('project_id');
        
        // Total projects
        $totalProjects = $myProjectIds->count();
        
        // Total tasks di semua projects yang di-lead
        $totalTasks = Card::whereHas('board', function($q) use ($myProjectIds) {
            $q->whereIn('project_id', $myProjectIds);
        })->count();
        
        // Tasks need review (status = 'review')
        $tasksNeedReview = Card::whereHas('board', function($q) use ($myProjectIds) {
            $q->whereIn('project_id', $myProjectIds);
        })->where('status', 'review')->count();
        
        // Tasks overdue (due_date < now AND status != done)
        $overdueTasks = Card::whereHas('board', function($q) use ($myProjectIds) {
            $q->whereIn('project_id', $myProjectIds);
        })
        ->where('due_date', '<', now())
        ->where('status', '!=', 'done')
        ->count();
        
        // Active members today (ada time_logs hari ini)
        $activeMembers = User::whereHas('projectMemberships', function($q) use ($myProjectIds) {
            $q->whereIn('project_id', $myProjectIds);
        })
        ->whereHas('timeLogs', function($q) {
            $q->whereDate('start_time', today());
        })
        ->count();
        
        return [
            'total_projects' => $totalProjects,
            'total_tasks' => $totalTasks,
            'tasks_need_review' => $tasksNeedReview,
            'overdue_tasks' => $overdueTasks,
            'active_members' => $activeMembers,
        ];
    }
    
    /**
     * Get projects yang di-lead oleh user
     * Include: total tasks, completion rate, days remaining
     */
    private function getMyProjects()
    {
        return Project::whereHas('members', function($q) {
            $q->where('user_id', Auth::id())
              ->where('role', 'team lead');
        })
        ->with(['creator:id,full_name,username', 'members:id,project_id,user_id,role'])
        ->withCount('members as team_count')
        ->get()
        ->map(function($project) {
            // Count total tasks via boards
            $totalTasks = Card::whereHas('board', function($q) use ($project) {
                $q->where('project_id', $project->id);
            })->count();
            
            // Count completed tasks
            $completedTasks = Card::whereHas('board', function($q) use ($project) {
                $q->where('project_id', $project->id);
            })->where('status', 'done')->count();
            
            // Calculate completion rate
            $project->total_tasks = $totalTasks;
            $project->completed_tasks = $completedTasks;
            $project->completion_rate = $totalTasks > 0 
                ? round(($completedTasks / $totalTasks) * 100, 2) 
                : 0;
            
            // Days until deadline
            $project->days_remaining = $project->deadline 
                ? now()->diffInDays($project->deadline, false) 
                : null;
            
            // Status berdasarkan deadline
            if ($project->days_remaining !== null) {
                if ($project->days_remaining < 0) {
                    $project->deadline_status = 'overdue';
                } elseif ($project->days_remaining <= 7) {
                    $project->deadline_status = 'urgent';
                } elseif ($project->days_remaining <= 30) {
                    $project->deadline_status = 'upcoming';
                } else {
                    $project->deadline_status = 'normal';
                }
            } else {
                $project->deadline_status = 'no_deadline';
            }
            
            return $project;
        })
        ->sortBy([
            ['deadline_status', 'asc'], // overdue first
            ['deadline', 'asc'],         // earliest first
        ]);
    }
    
    /**
     * Get tasks yang perlu di-review oleh team leader
     * Status = 'review', sorted by priority & due date
     */
    private function getTasksRequiringReview()
    {
        return Card::with([
            'board.project:id,project_name,slug',
            'creator:id,full_name,username',
            'assignments.user:id,full_name,username,email'
        ])
        ->whereHas('board.project.members', function($q) {
            $q->where('user_id', Auth::id())
              ->where('role', 'team lead');
        })
        ->where('status', 'review')
        ->orderByRaw("FIELD(priority, 'high', 'medium', 'low')")
        ->orderBy('due_date', 'asc')
        ->limit(10)
        ->get()
        ->map(function($card) {
            // Add urgency indicator
            if ($card->due_date) {
                $daysUntilDue = now()->diffInDays($card->due_date, false);
                
                if ($daysUntilDue < 0) {
                    $card->urgency = 'overdue';
                    $card->urgency_color = 'red';
                } elseif ($daysUntilDue <= 2) {
                    $card->urgency = 'critical';
                    $card->urgency_color = 'orange';
                } elseif ($daysUntilDue <= 7) {
                    $card->urgency = 'upcoming';
                    $card->urgency_color = 'yellow';
                } else {
                    $card->urgency = 'normal';
                    $card->urgency_color = 'blue';
                }
                
                $card->days_until_due = $daysUntilDue;
            } else {
                $card->urgency = 'no_deadline';
                $card->urgency_color = 'gray';
                $card->days_until_due = null;
            }
            
            return $card;
        });
    }
    
    /**
     * Get team activity hari ini
     * Include: current tasks, hours worked today
     */
    private function getTeamActivity()
    {
        $myProjectIds = ProjectMember::where('user_id', Auth::id())
            ->where('role', 'team lead')
            ->pluck('project_id');
        
        return User::whereHas('projectMemberships', function($q) use ($myProjectIds) {
            $q->whereIn('project_id', $myProjectIds);
        })
        ->where('id', '!=', Auth::id()) // Exclude team leader sendiri
        ->with(['cardAssignments' => function($q) use ($myProjectIds) {
            $q->where('assignment_status', 'in progress')
              ->with('card:id,card_title,status,priority')
              ->with('card.board.project:id,project_name')
              ->limit(2); // Max 2 current tasks per user
        }])
        ->withSum(['timeLogs as total_minutes_today' => function($q) {
            $q->whereDate('start_time', today())
              ->whereNotNull('end_time'); // Only completed logs
        }], 'duration_minutes')
        ->get()
        ->map(function($user) {
            // Convert minutes to hours
            $user->hours_today = $user->total_minutes_today 
                ? round($user->total_minutes_today / 60, 1) 
                : 0;
            
            // Get current task info
            $user->current_tasks = $user->cardAssignments->map(function($assignment) {
                return [
                    'title' => $assignment->card->card_title,
                    'project' => $assignment->card->board->project->project_name,
                    'priority' => $assignment->card->priority,
                    'status' => $assignment->card->status,
                ];
            });
            
            // Remove raw data
            unset($user->cardAssignments);
            unset($user->total_minutes_today);
            
            return $user;
        })
        ->sortByDesc('hours_today');
    }
    
    /**
     * Get recent comments di projects yang di-lead
     * Timeline dari team communication
     */
    private function getRecentComments()
    {
        return Comment::with([
            'user:id,full_name,username',
            'card:id,card_title',
            'card.board.project:id,project_name,slug',
            'subtask:id,subtask_name'
        ])
        ->whereHas('card.board.project.members', function($q) {
            $q->where('user_id', Auth::id())
              ->where('role', 'team lead');
        })
        ->orderBy('created_at', 'desc')
        ->limit(15)
        ->get()
        ->map(function($comment) {
            // Add human-readable timestamp
            $comment->time_ago = $comment->created_at->diffForHumans();
            
            // Add context (card or subtask)
            $comment->context = $comment->subtask_id 
                ? 'Subtask: ' . $comment->subtask->subtask_name 
                : 'Card: ' . $comment->card->card_title;
            
            return $comment;
        });
    }
    
    /**
     * API: Get task status distribution chart data
     * Donut chart untuk visualisasi status tasks
     */
    public function getTaskStatusChart()
    {
        $myProjectIds = ProjectMember::where('user_id', Auth::id())
            ->where('role', 'team lead')
            ->pluck('project_id');
        
        $data = Card::whereHas('board', function($q) use ($myProjectIds) {
            $q->whereIn('project_id', $myProjectIds);
        })
        ->select('status', DB::raw('COUNT(*) as count'))
        ->groupBy('status')
        ->get()
        ->map(function($item) {
            return [
                'label' => ucfirst(str_replace('_', ' ', $item->status)),
                'value' => $item->count,
                'status' => $item->status,
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
    
    /**
     * API: Get team workload distribution chart data
     * Bar chart untuk visualisasi beban kerja per member
     */
    public function getTeamWorkloadChart()
    {
        $myProjectIds = ProjectMember::where('user_id', Auth::id())
            ->where('role', 'team lead')
            ->pluck('project_id');
        
        $data = User::whereHas('projectMemberships', function($q) use ($myProjectIds) {
            $q->whereIn('project_id', $myProjectIds);
        })
        ->where('id', '!=', Auth::id()) // Exclude team leader
        ->withCount(['cardAssignments as total_tasks' => function($q) use ($myProjectIds) {
            $q->whereHas('card.board', function($q2) use ($myProjectIds) {
                $q2->whereIn('project_id', $myProjectIds);
            });
        }])
        ->withCount(['cardAssignments as in_progress_tasks' => function($q) use ($myProjectIds) {
            $q->where('assignment_status', 'in progress')
              ->whereHas('card.board', function($q2) use ($myProjectIds) {
                  $q2->whereIn('project_id', $myProjectIds);
              });
        }])
        ->withCount(['cardAssignments as completed_tasks' => function($q) use ($myProjectIds) {
            $q->where('assignment_status', 'completed')
              ->whereHas('card.board', function($q2) use ($myProjectIds) {
                  $q2->whereIn('project_id', $myProjectIds);
              });
        }])
        ->having('total_tasks', '>', 0) // Only users dengan tasks
        ->get()
        ->map(function($user) {
            return [
                'name' => $user->full_name,
                'total' => $user->total_tasks,
                'in_progress' => $user->in_progress_tasks,
                'completed' => $user->completed_tasks,
                'pending' => $user->total_tasks - $user->in_progress_tasks - $user->completed_tasks,
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
    
    /**
     * Clear dashboard cache
     * Called after actions yang mengubah data (approve, assign, dll)
     */
    public function clearCache()
    {
        $cacheKey = 'team_leader_dashboard_' . Auth::id();
        Cache::forget($cacheKey);
        
        return response()->json([
            'success' => true,
            'message' => 'Dashboard cache cleared',
        ]);
    }
}
