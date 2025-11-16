<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * AdminMiddleware
 * 
 * Middleware untuk memvalidasi akses admin
 * Hanya user dengan role 'admin' yang bisa akses
 */
class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Please login to access this page.');
        }
        
        // Check if user has admin role
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized. Only administrators can access this page.');
        }
        
        return $next($request);
    }
}
