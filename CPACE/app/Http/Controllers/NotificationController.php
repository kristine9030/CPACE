<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
            ->select(
                'notifications.*',
                'senders.first_name as sender_first_name',
                'senders.last_name as sender_last_name',
                'senders.profile_photo as sender_photo',
                'senders.avatar_color as sender_avatar_color'
            )
            ->when(Schema::hasColumn('users', 'avatar'), fn ($q) => $q->addSelect('senders.avatar as sender_avatar'))
            ->paginate(15);

        // Files attached to announcements, keyed by announcement, for the rows below.
        $files = collect();
        $communicationIds = $notifications->getCollection()->pluck('communication_id')->filter()->unique();
        if ($communicationIds->isNotEmpty() && Schema::hasTable('communication_attachments')) {
            $files = \App\Models\CommunicationAttachment::whereIn('communication_id', $communicationIds)->get()
                ->groupBy('communication_id')
                ->map(fn ($group) => $group->map->forDisplay()->all());
        }

        return view('notifications.index', compact('notifications', 'unreadCount', 'files'));
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
