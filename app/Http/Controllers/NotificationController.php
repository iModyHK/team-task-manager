<?php

namespace App\Http\Controllers;

use App\Http\Requests\Notification\UpdateNotificationRequest;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    /**
     * Display a listing of notifications.
     */
    public function index(Request $request)
    {
        $query = $request->user()
            ->notifications()
            ->with(['task', 'sender'])
            ->latest();

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by read status
        if ($request->has('read')) {
            if ($request->boolean('read')) {
                $query->whereNotNull('read_at');
            } else {
                $query->whereNull('read_at');
            }
        }

        $notifications = $query->paginate(20);

        return view('notifications.index', [
            'notifications' => $notifications,
            'unreadCount' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    /**
     * Get unread notifications count (AJAX).
     */
    public function getUnreadCount(Request $request)
    {
        $count = $request->user()->unreadNotifications()->count();

        return response()->json([
            'count' => $count,
        ]);
    }

    /**
     * Mark notifications as read.
     */
    public function markAsRead(UpdateNotificationRequest $request)
    {
        try {
            DB::beginTransaction();

            $query = $request->user()->notifications();

            if ($request->has('notification_ids')) {
                $query->whereIn('id', $request->notification_ids);
            }

            $query->update(['read_at' => now()]);

            // Log notifications marked as read
            activity_log('notifications_marked_read', [
                'user_id' => $request->user()->id,
                'count' => $query->count(),
            ]);

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'message' => 'Notifications marked as read successfully.',
                    'unread_count' => $request->user()->unreadNotifications()->count(),
                ]);
            }

            return back()->with('status', 'Notifications marked as read successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            report($e);

            if ($request->ajax()) {
                return response()->json([
                    'error' => 'An error occurred while marking notifications as read.',
                ], 500);
            }

            return back()->withErrors([
                'error' => 'An error occurred while marking notifications as read.',
            ]);
        }
    }

    /**
     * Mark notifications as unread.
     */
    public function markAsUnread(UpdateNotificationRequest $request)
    {
        try {
            DB::beginTransaction();

            $query = $request->user()->notifications();

            if ($request->has('notification_ids')) {
                $query->whereIn('id', $request->notification_ids);
            }

            $query->update(['read_at' => null]);

            // Log notifications marked as unread
            activity_log('notifications_marked_unread', [
                'user_id' => $request->user()->id,
                'count' => $query->count(),
            ]);

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'message' => 'Notifications marked as unread successfully.',
                    'unread_count' => $request->user()->unreadNotifications()->count(),
                ]);
            }

            return back()->with('status', 'Notifications marked as unread successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            report($e);

            if ($request->ajax()) {
                return response()->json([
                    'error' => 'An error occurred while marking notifications as unread.',
                ], 500);
            }

            return back()->withErrors([
                'error' => 'An error occurred while marking notifications as unread.',
            ]);
        }
    }

    /**
     * Delete notifications.
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'notification_ids' => ['required', 'array'],
            'notification_ids.*' => ['uuid'],
        ]);

        try {
            DB::beginTransaction();

            $query = $request->user()
                ->notifications()
                ->whereIn('id', $request->notification_ids);

            $count = $query->count();
            $query->delete();

            // Log notifications deleted
            activity_log('notifications_deleted', [
                'user_id' => $request->user()->id,
                'count' => $count,
            ]);

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'message' => 'Notifications deleted successfully.',
                    'unread_count' => $request->user()->unreadNotifications()->count(),
                ]);
            }

            return back()->with('status', 'Notifications deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            report($e);

            if ($request->ajax()) {
                return response()->json([
                    'error' => 'An error occurred while deleting notifications.',
                ], 500);
            }

            return back()->withErrors([
                'error' => 'An error occurred while deleting notifications.',
            ]);
        }
    }

    /**
     * Clear all notifications.
     */
    public function clearAll(Request $request)
    {
        try {
            DB::beginTransaction();

            $count = $request->user()->notifications()->count();
            $request->user()->notifications()->delete();

            // Log all notifications cleared
            activity_log('notifications_cleared', [
                'user_id' => $request->user()->id,
                'count' => $count,
            ]);

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'message' => 'All notifications cleared successfully.',
                ]);
            }

            return back()->with('status', 'All notifications cleared successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            report($e);

            if ($request->ajax()) {
                return response()->json([
                    'error' => 'An error occurred while clearing notifications.',
                ], 500);
            }

            return back()->withErrors([
                'error' => 'An error occurred while clearing notifications.',
            ]);
        }
    }

    /**
     * Load more notifications (AJAX).
     */
    public function loadMore(Request $request)
    {
        $page = $request->input('page', 1);
        
        $notifications = $request->user()
            ->notifications()
            ->with(['task', 'sender'])
            ->latest()
            ->paginate(20, ['*'], 'page', $page);

        return view('notifications.partials.notifications', [
            'notifications' => $notifications,
        ])->render();
    }
}
