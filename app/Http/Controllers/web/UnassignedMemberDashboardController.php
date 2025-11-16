<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Unassigned Member Dashboard Controller
 * 
 * Dashboard untuk user yang belum di-assign ke project manapun.
 * Menampilkan welcome page dengan:
 * - Profile completion tracker
 * - Getting started guide
 * - System statistics
 * - FAQ section
 * - Help & support
 * 
 * @author Your Name
 * @package App\Http\Controllers\web
 */
class UnassignedMemberDashboardController extends Controller
{
    /**
     * Tampilkan unassigned dashboard
     * Redirect ke member dashboard jika sudah di-assign
     */
    public function index()
    {
        // Check apakah user sudah di-assign ke project
        $hasProjects = ProjectMember::where('user_id', auth()->id())->exists();
        
        if ($hasProjects) {
            // Redirect to member dashboard if already assigned
            return redirect()->route('member.dashboard');
        }

        $profileCompletion = $this->calculateProfileCompletion();
        $tutorialSteps = $this->getTutorialSteps();
        $systemStats = $this->getSystemStats();

        return view('member.unassigned-dashboard', compact(
            'profileCompletion',
            'tutorialSteps',
            'systemStats'
        ));
    }

    /**
     * Calculate profile completion percentage
     * 
     * @return array [completed, total, percentage]
     */
    private function calculateProfileCompletion()
    {
        $user = auth()->user();
        $completed = 0;
        $total = 5;

        // Check completed fields
        if ($user->full_name) $completed++;
        if ($user->email && $user->email_verified_at) $completed++;
        if ($user->username) $completed++;
        if ($user->profile_picture) $completed++;
        if ($user->created_at) $completed++; // Account created (always true)

        return [
            'completed' => $completed,
            'total' => $total,
            'percentage' => round(($completed / $total) * 100, 2)
        ];
    }

    /**
     * Get tutorial steps dengan completion status
     * 
     * @return array
     */
    private function getTutorialSteps()
    {
        $user = auth()->user();
        
        return [
            [
                'icon' => 'fas fa-user-check',
                'title' => 'Complete Your Profile',
                'description' => 'Fill in your information to help admin assign you to projects',
                'completed' => $user->full_name && $user->email_verified_at && $user->profile_picture,
                'action_url' => route('profile.edit'),
                'action_text' => 'Edit Profile'
            ],
            [
                'icon' => 'fas fa-clock',
                'title' => 'Wait for Assignment',
                'description' => 'Admin will review your profile and assign you to a project',
                'completed' => false,
                'action_url' => null,
                'action_text' => 'Pending'
            ],
            [
                'icon' => 'fas fa-tasks',
                'title' => 'Start Working on Tasks',
                'description' => 'Once assigned, team leader will give you tasks to work on',
                'completed' => false,
                'action_url' => null,
                'action_text' => 'Not Started'
            ],
            [
                'icon' => 'fas fa-comments',
                'title' => 'Collaborate with Team',
                'description' => 'Use comments and time tracking to work efficiently',
                'completed' => false,
                'action_url' => null,
                'action_text' => 'Not Started'
            ]
        ];
    }

    /**
     * Get system statistics
     * 
     * @return array
     */
    private function getSystemStats()
    {
        return [
            'total_projects' => Project::count(),
            'active_members' => User::where('current_task_status', 'working')->count(),
            'total_tasks_completed' => Card::where('status', 'done')->count(),
        ];
    }

    /**
     * API endpoint untuk check assignment status
     * Digunakan oleh JavaScript untuk auto-detect assignment
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkAssignment()
    {
        $hasAssignment = ProjectMember::where('user_id', auth()->id())->exists();
        
        return response()->json([
            'has_assignment' => $hasAssignment,
            'redirect_url' => $hasAssignment ? route('dashboard') : null
        ]);
    }
}
