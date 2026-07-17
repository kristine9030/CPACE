<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function index()
    {
        $unreadCount = DB::table('notifications')
            ->where('recipient_id', Auth::id())
            ->where('is_read', false)
            ->count();

        $notifications = DB::table('notifications')
            ->leftJoin('users as senders', 'senders.id', '=', 'notifications.sender_id')
            ->where('notifications.recipient_id', Auth::id())
            ->orderByDesc('notifications.created_at')
            ->select('notifications.*', 'senders.first_name as sender_first_name', 'senders.last_name as sender_last_name')
            ->paginate(15);

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    public function read(int $id)
    {
        $notification = DB::table('notifications')
            ->where('id', $id)
            ->where('recipient_id', Auth::id())
            ->first();

        abort_unless($notification, 404);

        DB::table('notifications')->where('id', $id)->update(['is_read' => true, 'updated_at' => now()]);

        return $notification->link && str_starts_with($notification->link, '/')
            ? redirect($notification->link)
            : back();
    }

    public function readAll()
    {
        DB::table('notifications')->where('recipient_id', Auth::id())->where('is_read', false)
            ->update(['is_read' => true, 'updated_at' => now()]);

        return back()->with('status', 'All notifications marked as read.');
    }
}
