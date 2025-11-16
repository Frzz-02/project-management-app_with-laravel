<?php

namespace App\Http\Middleware;

use App\Models\ProjectMember;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * TeamLeaderMiddleware
 * 
 * Middleware untuk validasi user adalah Team Leader
 * Check dari tabel project_members dengan role = 'team lead'
 * 
 * User dianggap Team Leader jika:
 * - Ada minimal 1 project dengan role 'team lead' di project_members
 * - Atau role di users table adalah 'admin' (admin bisa akses semua)
 */
class TeamLeaderMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'Please login to access this page.');
        }
        
        $user = Auth::user();
        
        // Admin bisa akses Team Leader dashboard
        if ($user->role === 'admin') {
            return $next($request);
        }
        
        // Check if user adalah team lead di minimal 1 project
        $isTeamLead = ProjectMember::where('user_id', $user->id)
            ->where('role', 'team lead')
            ->exists();
        
        if (!$isTeamLead) {
            // Redirect ke dashboard dengan error message
            return redirect()->route('dashboard')
                ->with('error', 'Unauthorized access. Team Leader role required. You must be assigned as Team Leader in at least one project.');
        }
        
        return $next($request);
    }
}
