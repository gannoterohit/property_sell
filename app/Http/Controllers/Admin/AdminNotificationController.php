<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use Illuminate\Http\Request;

class AdminNotificationController extends Controller
{
    public function index(Request $request)
    {
        $query = AdminNotification::query();

        // Filter by status (unread / read)
        if ($request->filled('status')) {
            if ($request->status === 'unread') {
                $query->where('is_read', false);
            } elseif ($request->status === 'read') {
                $query->where('is_read', true);
            }
        }

        // Filter by type
        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        // Search in title or message
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        $notifications = $query->latest()->paginate(25)->appends($request->query());

        $stats = [
            'total' => AdminNotification::count(),
            'unread' => AdminNotification::where('is_read', false)->count(),
            'read' => AdminNotification::where('is_read', true)->count(),
            'today' => AdminNotification::whereDate('created_at', today())->count(),
        ];

        // All distinct types in DB merged with standard types
        $dbTypes = AdminNotification::select('type')
            ->whereNotNull('type')
            ->where('type', '!=', '')
            ->distinct()
            ->pluck('type')
            ->toArray();

        $defaultTypes = [
            'payment_received',
            'new_user_registration',
            'new_broker_registration',
            'room_posted',
            'complaint_submitted',
            'complaint_reply',
            'contact_inquiry',
            'lead_unlock',
            'broadcast',
        ];

        $availableTypes = array_values(array_unique(array_merge($defaultTypes, $dbTypes)));

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'notifications' => $notifications->items(),
                'unread_count' => $stats['unread'],
                'stats' => $stats,
            ]);
        }

        return view('admin.notifications.index', compact('notifications', 'stats', 'availableTypes'));
    }

    public function markRead(AdminNotification $notification)
    {
        $notification->markAsRead();

        if ($notification->type === 'contact_inquiry') {
            \App\Models\ContactMessage::where('is_read', false)->update(['is_read' => true]);
        }

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'unread_count' => AdminNotification::where('is_read', false)->count(),
            ]);
        }

        if ($notification->link) {
            return redirect($notification->link);
        }

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllRead()
    {
        AdminNotification::where('is_read', false)->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        \App\Models\ContactMessage::where('is_read', false)->update(['is_read' => true]);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'unread_count' => 0,
            ]);
        }

        return back()->with('success', 'All notifications marked as read.');
    }

    public function unreadCount()
    {
        return response()->json([
            'unread_count' => AdminNotification::where('is_read', false)->count(),
        ]);
    }

    public function destroy(AdminNotification $notification)
    {
        $notification->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'unread_count' => AdminNotification::where('is_read', false)->count(),
                'message' => 'Notification deleted successfully.',
            ]);
        }

        return back()->with('success', 'Notification deleted successfully.');
    }

    public function clearAll(Request $request)
    {
        $mode = $request->get('mode', 'read'); // 'read' or 'all'

        if ($mode === 'all') {
            AdminNotification::query()->delete();
            $message = 'All notifications cleared.';
        } else {
            AdminNotification::where('is_read', true)->delete();
            $message = 'All read notifications cleared.';
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'unread_count' => AdminNotification::where('is_read', false)->count(),
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }
}
