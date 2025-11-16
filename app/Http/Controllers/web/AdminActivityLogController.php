<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\Comment;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Controller untuk mengelola Activity Logs di Admin Dashboard
 * 
 * Fitur:
 * - Menampilkan log aktivitas sistem
 * - Filter berdasarkan tipe aktivitas, user, tanggal
 * - Pagination untuk performa
 * - Export log activities
 */
class AdminActivityLogController extends Controller
{
    /**
     * Menampilkan halaman activity logs
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Filter parameters
        $filterType = $request->get('type', 'all');
        $filterUser = $request->get('user');
        $filterDate = $request->get('date');
        $search = $request->get('search');

        // Build activities query
        $activities = collect();

        // 1. Project Activities (created/updated)
        $projectQuery = Project::with('creator')
            ->select('id', 'project_name', 'created_by', 'created_at')
            ->latest('created_at');

        if ($filterUser) {
            $projectQuery->where('created_by', $filterUser);
        }

        if ($filterDate) {
            $projectQuery->whereDate('created_at', $filterDate);
        }

        if ($search) {
            $projectQuery->where('project_name', 'like', "%{$search}%");
        }

        // dd($projectQuery->limit(50)->get());
        $projects = $projectQuery->limit(50)->get()->map(function ($project) {
            return [
                'type' => 'project_created',
                'icon' => 'folder-plus',
                'color' => 'blue',
                'title' => 'Project Created',
                'description' => "Created project: {$project->project_name}",
                'user' => $project->creator,
                'timestamp' => $project->created_at instanceof Carbon ? $project->created_at : Carbon::parse($project->created_at),
                'metadata' => [
                    'project_id' => $project->id,
                    'project_name' => $project->project_name,
                ]
            ];
        });

        // 2. Card Activities (created/status changed)
        $cardQuery = Card::with(['creator', 'board.project'])
            ->select('id', 'card_title', 'status', 'created_by', 'created_at')
            ->latest('created_at');

        if ($filterUser) {
            $cardQuery->where('created_by', $filterUser);
        }

        if ($filterDate) {
            $cardQuery->whereDate('created_at', $filterDate);
        }

        if ($search) {
            $cardQuery->where('card_title', 'like', "%{$search}%");
        }

        $cards = $cardQuery->limit(50)->get()->map(function ($card) {
            return [
                'type' => 'card_created',
                'icon' => 'clipboard-list',
                'color' => 'green',
                'title' => 'Task Created',
                'description' => "Created task: {$card->card_title}",
                'user' => $card->creator,
                'timestamp' => $card->created_at instanceof Carbon ? $card->created_at : Carbon::parse($card->created_at),
                'metadata' => [
                    'card_id' => $card->id,
                    'card_title' => $card->card_title,
                    'status' => $card->status,
                    'project' => $card->board?->project?->project_name,
                ]
            ];
        });

        // 3. Comment Activities
        $commentQuery = Comment::with(['user', 'card.board.project'])
            ->select('id', 'comment_text', 'user_id', 'card_id', 'created_at')
            ->latest('created_at');

        if ($filterUser) {
            $commentQuery->where('user_id', $filterUser);
        }

        if ($filterDate) {
            $commentQuery->whereDate('created_at', $filterDate);
        }

        if ($search) {
            $commentQuery->where('comment_text', 'like', "%{$search}%");
        }

        $comments = $commentQuery->limit(50)->get()->map(function ($comment) {
            return [
                'type' => 'comment_added',
                'icon' => 'chat-alt',
                'color' => 'purple',
                'title' => 'Comment Added',
                'description' => "Commented on: {$comment->card?->title}",
                'user' => $comment->user,
                'timestamp' => $comment->created_at instanceof Carbon ? $comment->created_at : Carbon::parse($comment->created_at),
                'metadata' => [
                    'comment_id' => $comment->id,
                    'content' => substr($comment->comment_text, 0, 100),
                    'card_title' => $comment->card?->title,
                    'project' => $comment->card?->board?->project?->project_name,
                ]
            ];
        });

        // 4. User Activities (login/registration) - simulated from created_at
        $userQuery = User::select('id', 'full_name', 'email', 'role', 'created_at')
            ->latest('created_at');

        if ($filterDate) {
            $userQuery->whereDate('created_at', $filterDate);
        }

        if ($search) {
            $userQuery->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $userQuery->limit(30)->get()->map(function ($user) {
            return [
                'type' => 'user_registered',
                'icon' => 'user-add',
                'color' => 'indigo',
                'title' => 'User Registered',
                'description' => "New user registered: {$user->full_name}",
                'user' => $user,
                'timestamp' => $user->created_at instanceof Carbon ? $user->created_at : Carbon::parse($user->created_at),
                'metadata' => [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'role' => $user->role,
                ]
            ];
        });

        // Merge and sort all activities
        $activities = $projects
            ->concat($cards)
            ->concat($comments)
            ->concat($users)
            ->sortByDesc('timestamp');

        // Apply type filter
        if ($filterType !== 'all') {
            $activities = $activities->filter(function ($activity) use ($filterType) {
                return $activity['type'] === $filterType;
            });
        }

        // Paginate manually
        $perPage = 20;
        $currentPage = $request->get('page', 1);
        $activitiesCollection = $activities->forPage($currentPage, $perPage);
        $total = $activities->count();

        // Get users for filter dropdown
        $users = User::select('id', 'full_name')->orderBy('full_name')->get();

        // Statistics - Convert timestamps to Carbon instances before filtering
        $stats = [
            'total_activities' => $total,
            'today_activities' => $activities->filter(function($a) {
                $timestamp = $a['timestamp'] instanceof Carbon ? $a['timestamp'] : Carbon::parse($a['timestamp']);
                return $timestamp->isToday();
            })->count(),
            'this_week' => $activities->filter(function($a) {
                $timestamp = $a['timestamp'] instanceof Carbon ? $a['timestamp'] : Carbon::parse($a['timestamp']);
                return $timestamp->isCurrentWeek();
            })->count(),
            'this_month' => $activities->filter(function($a) {
                $timestamp = $a['timestamp'] instanceof Carbon ? $a['timestamp'] : Carbon::parse($a['timestamp']);
                return $timestamp->isCurrentMonth();
            })->count(),
        ];

        return view('admin.activity-logs.index', [
            'activities' => $activitiesCollection,
            'stats' => $stats,
            'users' => $users,
            'filters' => [
                'type' => $filterType,
                'user' => $filterUser,
                'date' => $filterDate,
                'search' => $search,
            ],
            'pagination' => [
                'current_page' => $currentPage,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => ceil($total / $perPage),
            ]
        ]);
    }
}
