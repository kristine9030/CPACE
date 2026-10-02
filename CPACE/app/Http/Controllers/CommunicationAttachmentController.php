<?php

namespace App\Http\Controllers;

use App\Models\Communication;
use App\Models\CommunicationAttachment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CommunicationAttachmentController extends Controller
{
    /**
     * Open or download a file attached to an announcement. Only the person who
     * sent the announcement and the people it was delivered to can get it.
     */
    public function download(CommunicationAttachment $attachment)
    {
        $communication = Communication::findOrFail($attachment->communication_id);
        $userId = Auth::id();

        $allowed = $communication->sender_id === $userId
            || DB::table('notifications')
                ->where('communication_id', $communication->id)
                ->where('recipient_id', $userId)
                ->exists();

        abort_unless($allowed, 403);
        abort_unless(Storage::disk('public')->exists($attachment->path), 404);

        // Pictures open in the browser; everything else downloads.
        return $attachment->isImage()
            ? Storage::disk('public')->response($attachment->path, $attachment->original_name)
            : Storage::disk('public')->download($attachment->path, $attachment->original_name);
    }
}
