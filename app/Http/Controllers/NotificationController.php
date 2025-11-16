<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * NotificationController
 * 
 * Handle semua operasi terkait notifikasi user
 * - Get all notifications (dengan pagination)
 * - Get unread count
 * - Mark as read (single/bulk)
 * - Delete notifications
 */
class NotificationController extends Controller
{
    /**
     * Display notifications page
     */
    public function page()
    {
        return view('notifications.index');
    }
    
    /**
     * Get all notifications untuk user yang login
     * Dengan pagination dan filter
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $perPage = $request->input('per_page', 15);
        $filter = $request->input('filter', 'all'); // all, unread, read
        
        $query = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc');
        
        // Apply filter
        if ($filter === 'unread') {
            $query->unread();
        } elseif ($filter === 'read') {
            $query->read();
        }
        
        // Pagination
        $notifications = $query->paginate($perPage);
        
        // Transform data with accessors
        $transformedData = $notifications->map(function ($notification) {
            return [
                'id' => $notification->id,
                'type' => $notification->type,
                'title' => $notification->title,
                'message' => $notification->message,
                'data' => $notification->data,
                'is_read' => $notification->is_read,
                'read_at' => $notification->read_at?->format('Y-m-d H:i:s'),
                'time_ago' => $notification->time_ago,
                'icon' => $notification->icon,
                'color_class' => $notification->color_class,
                'created_at' => $notification->created_at->format('Y-m-d H:i:s'),
            ];
        });
        
        // Return dengan format pagination standard Laravel
        return response()->json([
            'data' => $transformedData,
            'current_page' => $notifications->currentPage(),
            'last_page' => $notifications->lastPage(),
            'per_page' => $notifications->perPage(),
            'total' => $notifications->total(),
        ]);
    }
    
    /**
     * Get unread notifications count
     */
    public function unreadCount()
    {
        $user = Auth::user();
        $count = Notification::where('user_id', $user->id)
            ->unread()
            ->count();
        
        return response()->json([
            'success' => true,
            'unread_count' => $count,
        ]);
    }
    
    /**
     * Get recent notifications untuk dropdown (limit 10)
     */
    public function recent()
    {
        $user = Auth::user();
        
        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'data' => $notification->data,
                    'is_read' => $notification->is_read,
                    'time_ago' => $notification->time_ago,
                    'icon' => $notification->icon,
                    'color_class' => $notification->color_class,
                ];
            });
        
        return response()->json([
            'success' => true,
            'notifications' => $notifications,
        ]);
    }
    
    /**
     * Mark single notification as read
     */
    public function markAsRead(Notification $notification)
    {
        // Authorization check
        if ($notification->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }
        
        $notification->markAsRead();
        
        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
        ]);
    }
    
    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        
        Notification::where('user_id', $user->id)
            ->unread()
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        
        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
        ]);
    }
    
    /**
     * Delete notification
     */
    public function destroy(Notification $notification)
    {
        // Authorization check
        if ($notification->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }
        
        $notification->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Notification deleted',
        ]);
    }
    
    /**
     * Delete all read notifications
     */
    public function deleteAllRead()
    {
        $user = Auth::user();
        
        $deleted = Notification::where('user_id', $user->id)
            ->read()
            ->delete();
        
        return response()->json([
            'success' => true,
            'message' => "{$deleted} notifications deleted",
            'deleted_count' => $deleted,
        ]);
    }
}
