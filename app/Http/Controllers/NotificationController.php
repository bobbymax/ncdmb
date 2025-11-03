<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $notifications = $user->notifications()
            ->orderByRaw('read_at IS NULL DESC') // Unread first
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        return response()->json([
            'status' => 'success',
            'data' => $notifications->items(),
            'current_page' => $notifications->currentPage(),
            'last_page' => $notifications->lastPage(),
            'total' => $notifications->total(),
        ]);
    }
    
    public function unreadCount()
    {
        return response()->json([
            'status' => 'success',
            'count' => Auth::user()->unreadNotifications()->count()
        ]);
    }
    
    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Notification marked as read'
        ]);
    }
    
    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        
        return response()->json([
            'status' => 'success',
            'message' => 'All notifications marked as read'
        ]);
    }
    
    public function destroy($id)
    {
        Auth::user()->notifications()->findOrFail($id)->delete();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Notification deleted'
        ]);
    }
}

