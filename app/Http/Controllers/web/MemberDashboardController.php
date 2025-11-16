<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\CardAssignment;
use App\Models\Comment;
use App\Models\ProjectMember;
use App\Models\Subtask;
use App\Models\TimeLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Member Dashboard Controller
 * 
 * Menangani dashboard untuk Developer/Designer (member) yang berisi:
 * - Overview statistics (assigned tasks, in progress, completed, hours)
 * - Active tasks dengan progress tracking
 * - Upcoming deadlines
 * - Today's work summary
 * - Recent feedback dari team leader
 * - Projects yang di-join
 * - Quick actions (Start/Pause task)
 * 
 * @author Your Name
 * @package App\Http\Controllers\web
 */
class MemberDashboardController extends Controller
{
    /**
     * Tampilkan dashboard member dengan semua data yang diperlukan
     * Data di-cache selama 5 menit untuk performance
     */
    public function index()
    {
        $userId = auth()->id();
        $cacheKey = "member_dashboard_{$userId}";

        // Cache dashboard data untuk 5 menit
        $dashboardData = Cache::remember($cacheKey, 300, function () {
            return [
                'overviewStats' => $this->getOverviewStats(),
                'myActiveTasks' => $this->getMyActiveTasks(),
                'upcomingDeadlines' => $this->getUpcomingDeadlines(),
                'todayWorkSummary' => $this->getTodayWorkSummary(),
                'recentFeedback' => $this->getRecentFeedback(),
                'myProjects' => $this->getMyProjects(),
            ];
        });

        return view('member.dashboard', $dashboardData);
    }

    /**
     * Get overview statistics untuk stat cards
     * 
     * @return array [assigned_tasks, in_progress_tasks, completed_this_week, hours_today]
     */
    private function getOverviewStats(): array
    {
        $userId = auth()->id();

        // Total assigned tasks yang belum done
        $assignedTasks = CardAssignment::where('user_id', $userId)
            ->whereHas('card', function ($query) {
                $query->where('status', '!=', 'done');
            })
            ->count();

        // Tasks yang sedang in progress
        $inProgressTasks = CardAssignment::where('user_id', $userId)
            ->where('assignment_status', 'in progress')
            ->count();

        // Tasks completed minggu ini
        $completedThisWeek = CardAssignment::where('user_id', $userId)
            ->where('assignment_status', 'completed')
            ->where('completed_at', '>=', now()->startOfWeek())
            ->count();

        // Total jam kerja hari ini
        $hoursToday = TimeLog::where('user_id', $userId)
            ->whereDate('start_time', today())
            ->sum('duration_minutes') / 60;

        return [
            'assigned_tasks' => $assignedTasks,
            'in_progress_tasks' => $inProgressTasks,
            'completed_this_week' => $completedThisWeek,
            'hours_today' => round($hoursToday, 2),
        ];
    }

    /**
     * Get active tasks yang sedang dikerjakan member
     * Include progress subtasks, time spent, dan sort by status
     * 
     * @return \Illuminate\Support\Collection
     */
    private function getMyActiveTasks()
    {
        return CardAssignment::with([
            'card.board.project',
            'card.subtasks',
            'card.creator:id,full_name',
        ])
            ->where('user_id', auth()->id())
            ->whereIn('assignment_status', ['assigned', 'in progress'])
            ->whereHas('card', function ($query) {
                $query->where('status', '!=', 'done');
            })
            ->get()
            ->map(function ($assignment) {
                $card = $assignment->card;

                // Hitung total waktu yang sudah dihabiskan user ini
                $card->time_spent = TimeLog::where('card_id', $card->id)
                    ->where('user_id', auth()->id())
                    ->sum('duration_minutes') / 60;

                // Hitung progress subtasks
                $card->subtasks_completed = $card->subtasks()
                    ->where('status', 'done')
                    ->count();
                $card->subtasks_total = $card->subtasks()->count();

                // Progress percentage
                $card->progress_percentage = $card->subtasks_total > 0
                    ? round(($card->subtasks_completed / $card->subtasks_total) * 100)
                    : 0;

                // Check apakah ada timer aktif
                $card->active_timer = TimeLog::where('card_id', $card->id)
                    ->where('user_id', auth()->id())
                    ->whereNull('end_time')
                    ->orderBy('start_time', 'desc')
                    ->first();

                // Deadline info
                if ($card->due_date) {
                    $daysUntilDue = now()->startOfDay()->diffInDays($card->due_date, false);
                    $card->days_until_due = $daysUntilDue;
                    $card->is_overdue = $daysUntilDue < 0;
                    $card->is_urgent = $daysUntilDue >= 0 && $daysUntilDue <= 2;
                }

                return $assignment;
            })
            ->sortByDesc(function ($assignment) {
                // Sort: in progress dulu, baru assigned
                return $assignment->assignment_status === 'in progress' ? 1 : 0;
            });
    }

    /**
     * Get upcoming deadlines dalam 7 hari ke depan
     * Sort by due date dan priority
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getUpcomingDeadlines()
    {
        return Card::whereHas('assignments', function ($query) {
            $query->where('user_id', auth()->id());
        })
            ->with([
                'board.project',
                'assignments' => function ($query) {
                    $query->where('user_id', auth()->id());
                },
            ])
            ->where('due_date', '<=', now()->addDays(7))
            ->where('status', '!=', 'done')
            ->orderBy('due_date', 'asc')
            ->orderByRaw('FIELD(priority, "high", "medium", "low")')
            ->limit(10)
            ->get()
            ->map(function ($card) {
                $daysUntilDue = now()->startOfDay()->diffInDays($card->due_date, false);
                $card->days_until_due = $daysUntilDue;
                $card->is_overdue = $daysUntilDue < 0;
                $card->is_today = $daysUntilDue === 0;
                $card->is_critical = $daysUntilDue >= 0 && $daysUntilDue <= 2;

                // Urgency color untuk UI
                if ($card->is_overdue) {
                    $card->urgency_color = 'red';
                } elseif ($card->is_critical) {
                    $card->urgency_color = 'orange';
                } elseif ($daysUntilDue <= 5) {
                    $card->urgency_color = 'yellow';
                } else {
                    $card->urgency_color = 'blue';
                }

                return $card;
            });
    }

    /**
     * Get ringkasan pekerjaan hari ini
     * Include time logs, completed subtasks, dan sessions
     * 
     * @return array
     */
    private function getTodayWorkSummary(): array
    {
        $userId = auth()->id();

        // Get all time logs hari ini
        $timeLogs = TimeLog::where('user_id', $userId)
            ->whereDate('start_time', today())
            ->with('card:id,card_title')
            ->get();

        // Subtasks yang diselesaikan hari ini
        $completedSubtasks = Subtask::whereHas('card.assignments', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
            ->where('status', 'done')
            // ->whereDate('updated_at', today())
            ->count();

        // Active timer jika ada
        $activeTimer = TimeLog::where('user_id', $userId)
            ->whereNull('end_time')
            ->with('card:id,card_title')
            ->orderBy('start_time', 'desc')
            ->first();

        return [
            'total_hours' => round($timeLogs->sum('duration_minutes') / 60, 2),
            'sessions_count' => $timeLogs->count(),
            'completed_subtasks' => $completedSubtasks,
            'time_logs' => $timeLogs,
            'active_timer' => $activeTimer,
        ];
    }

    /**
     * Get feedback terbaru dari team leader
     * Hanya tampilkan comments dari user dengan role team lead
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getRecentFeedback()
    {
        return Comment::with([
            'user:id,full_name,profile_picture',
            'card:id,card_title',
            'card.board:id,board_name,project_id',
            'card.board.project:id,project_name',
        ])
            ->whereHas('card.assignments', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->whereHas('user.projectMemberships', function ($query) {
                $query->where('role', 'team lead');
            })
            ->where('user_id', '!=', auth()->id()) // Exclude komentar sendiri
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($comment) {
                // Time ago formatting
                $comment->time_ago = $comment->created_at->diffForHumans();
                return $comment;
            });
    }

    /**
     * Get projects yang diikuti member
     * Include jumlah tasks yang di-assign di setiap project
     * 
     * @return \Illuminate\Support\Collection
     */
    private function getMyProjects()
    {
        return ProjectMember::with([
            'project.creator:id,full_name',
            'project.boards',
        ])
            ->where('user_id', auth()->id())
            ->whereIn('role', ['developer', 'designer'])
            ->get()
            ->map(function ($membership) {
                // Count tasks di project ini
                $membership->my_tasks_count = Card::whereHas('assignments', function ($query) {
                    $query->where('user_id', auth()->id());
                })
                    ->whereHas('board', function ($query) use ($membership) {
                        $query->where('project_id', $membership->project_id);
                    })
                    ->count();

                // Count active tasks
                $membership->active_tasks_count = Card::whereHas('assignments', function ($query) {
                    $query->where('user_id', auth()->id())
                        ->whereIn('assignment_status', ['assigned', 'in progress']);
                })
                    ->whereHas('board', function ($query) use ($membership) {
                        $query->where('project_id', $membership->project_id);
                    })
                    ->count();

                return $membership;
            });
    }

    /**
     * Start working on a task
     * Update assignment status, user status, dan mulai time log
     * 
     * @param int $cardId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function startTask(Request $request, $cardId)
    {
        try {
            DB::transaction(function () use ($cardId) {
                $userId = auth()->id();

                // Verify assignment exists
                $assignment = CardAssignment::where('card_id', $cardId)
                    ->where('user_id', $userId)
                    ->firstOrFail();

                // Update assignment status
                $assignment->update([
                    'assignment_status' => 'in progress',
                    'started_at' => now(),
                ]);

                // Update card status jika masih todo
                $card = Card::findOrFail($cardId);
                if ($card->status === 'todo') {
                    $card->update(['status' => 'in progress']);
                }

                // Update user status
                User::find($userId)->update([
                    'current_task_status' => 'working',
                ]);

                // Start time log
                TimeLog::create([
                    'card_id' => $cardId,
                    'user_id' => $userId,
                    'start_time' => now(),
                    'description' => 'Started working on task',
                ]);
            });

            // Clear cache
            Cache::forget("member_dashboard_" . auth()->id());

            return redirect()->back()->with('success', 'Task started! Timer is running. ⏱️');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to start task: ' . $e->getMessage());
        }
    }

    /**
     * Pause working on a task
     * Stop active time log dan hitung durasi
     * 
     * @param int $cardId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function pauseTask(Request $request, $cardId)
    {
        try {
            // Find active time log
            $activeLog = TimeLog::where('card_id', $cardId)
                ->where('user_id', auth()->id())
                ->whereNull('end_time')
                ->orderBy('start_time', 'desc')
                ->first();

            if (!$activeLog) {
                return redirect()->back()->with('error', 'No active timer found for this task.');
            }

            // Calculate duration dan stop timer
            $activeLog->update([
                'end_time' => now(),
                'duration_minutes' => now()->diffInMinutes($activeLog->start_time),
            ]);

            // Update user status
            User::find(auth()->id())->update([
                'current_task_status' => 'available',
            ]);

            // Clear cache
            Cache::forget("member_dashboard_" . auth()->id());

            return redirect()->back()->with('success', 'Task paused. Take a break! ☕');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to pause task: ' . $e->getMessage());
        }
    }

    /**
     * Clear dashboard cache
     * Useful after completing tasks, creating subtasks, etc
     * 
     * @return \Illuminate\Http\RedirectResponse
     */
    public function clearCache()
    {
        Cache::forget("member_dashboard_" . auth()->id());
        return redirect()->back()->with('success', 'Dashboard refreshed!');
    }
}
