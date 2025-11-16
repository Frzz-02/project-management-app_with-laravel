<?php

namespace App\Http\Middleware;

use App\Models\ProjectMember;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Member Middleware
 * 
 * Middleware untuk memverifikasi bahwa user memiliki role sebagai member
 * (developer atau designer) di minimal satu project.
 * 
 * Admin tetap bisa akses (bypass) untuk keperluan monitoring.
 * 
 * @author Your Name
 * @package App\Http\Middleware
 */
class MemberMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check jika user sudah login
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Please login to access member dashboard.');
        }

        $user = auth()->user();

        // Admin bypass - admin bisa akses semua dashboard untuk monitoring
        if ($user->role === 'admin') {
            return $next($request);
        }

        // Check apakah user adalah member di minimal satu project
        $isMember = ProjectMember::where('user_id', $user->id)
            ->whereIn('role', ['developer', 'designer'])
            ->exists();

        if (!$isMember) {
            return redirect()->route('dashboard')
                ->with('error', 'Access denied. You must be assigned as a member (developer/designer) in at least one project to access member dashboard.');
        }

        return $next($request);
    }
}
