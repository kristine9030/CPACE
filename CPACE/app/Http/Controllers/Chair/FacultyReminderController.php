<?php

namespace App\Http\Controllers\Chair;

use App\Http\Controllers\Controller;
use App\Mail\CommunicationMail;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Lets the Program Chair nudge an inactive faculty member from the dashboard's
 * Faculty tab: an in-app notification plus an email, at most once a day per
 * faculty member so a reminder can't turn into spam.
 */
class FacultyReminderController extends Controller
{
    /** Minimum hours between two reminders to the same faculty member. */
    public const COOLDOWN_HOURS = 24;

    /** notifications.reference_type used to find earlier reminders. */
    public const REFERENCE = 'faculty_reminder';

    public function store(Request $request, int $id)
    {
        $faculty = User::where('role_id', Role::FACULTY)->where('is_active', true)->findOrFail($id);

        $data = $request->validate([
            'title'   => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $last = self::lastReminderAt([$faculty->id])->get($faculty->id);
        if ($last && $last->gt(now()->subHours(self::COOLDOWN_HOURS))) {
            return back()->with('warning', "{$faculty->first_name} was already reminded {$last->diffForHumans()}. You can send another reminder after 24 hours.");
        }

        DB::table('notifications')->insert([
            'recipient_id'   => $faculty->id,
            'sender_id'      => Auth::id(),
            'type'           => 'normal',
            'title'          => $data['title'],
            'message'        => $data['message'],
            'link'           => route('faculty.dashboard', [], false),
            'is_read'        => false,
            'reference_type' => self::REFERENCE,
            'reference_id'   => $faculty->id,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        // The in-app notification is already saved; a mail failure only loses the email.
        $emailed = true;
        try {
            Mail::to($faculty->email)->send(new CommunicationMail(
                recipientName: $faculty->first_name,
                senderName: Auth::user()->name,
                title: $data['title'],
                body: $data['message'],
                priority: 'normal',
                ctaUrl: route('faculty.dashboard'),
            ));
        } catch (\Throwable $e) {
            $emailed = false;
            Log::warning('Failed to send faculty reminder email', ['faculty_id' => $faculty->id, 'error' => $e->getMessage()]);
        }

        return back()->with('status', $emailed
            ? "Reminder sent to {$faculty->name} in CPAce and by email."
            : "Reminder sent to {$faculty->name} in CPAce. The email could not be delivered.");
    }

    /**
     * When each of these faculty was last reminded, keyed by user id.
     *
     * @param  iterable<int>  $facultyIds
     * @return \Illuminate\Support\Collection<int, \Illuminate\Support\Carbon>
     */
    public static function lastReminderAt(iterable $facultyIds)
    {
        return DB::table('notifications')
            ->where('reference_type', self::REFERENCE)
            ->whereIn('recipient_id', collect($facultyIds)->all())
            ->groupBy('recipient_id')
            ->selectRaw('recipient_id, MAX(created_at) as last_at')
            ->pluck('last_at', 'recipient_id')
            ->map(fn ($at) => \Illuminate\Support\Carbon::parse($at));
    }
}
