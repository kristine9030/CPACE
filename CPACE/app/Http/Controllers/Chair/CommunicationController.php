<?php

namespace App\Http\Controllers\Chair;

use App\Http\Controllers\Controller;
use App\Mail\CommunicationMail;
use App\Models\Communication;
use App\Models\CommunicationAttachment;
use App\Models\Role;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class CommunicationController extends Controller
{
    /**
     * Communications now live inside Messages (Chats | Announcements). This
     * route stays so old links and bookmarks keep working.
     */
    public function index(Request $request)
    {
        $to = match ($request->query('tab')) {
            'students' => 'students',
            'faculty' => 'faculty',
            default => 'all',
        };

        return redirect()->route('messages.index', array_filter(['view' => 'announcements', 'to' => $to === 'all' ? null : $to]));
    }

    /**
     * Everything the Announcements view in Messages needs: the announcements
     * this chair has sent (optionally narrowed to one audience), per-audience
     * counts for the filter pills, and the lists the "Make an announcement"
     * form picks recipients from.
     *
     * @return array<string, mixed>
     */
    public function boardData(Request $request): array
    {
        $filter = in_array($request->query('to'), ['students', 'faculty'], true) ? $request->query('to') : 'all';

        $students = User::query()
            ->where('role_id', Role::STUDENT)
            ->where('is_active', true)
            ->with('studentProfile')
            ->orderBy('first_name')->orderBy('last_name')
            ->get();

        $faculty = User::query()
            ->where('role_id', Role::FACULTY)
            ->where('is_active', true)
            ->with('assignedSubjects:id,code,name')
            ->orderBy('first_name')->orderBy('last_name')
            ->get();

        $counts = Communication::query()
            ->where('sender_id', Auth::id())
            ->selectRaw('audience, COUNT(*) as total')
            ->groupBy('audience')
            ->pluck('total', 'audience');

        $items = Communication::query()
            ->where('sender_id', Auth::id())
            ->when($filter !== 'all', fn ($query) => $query->where('audience', $filter))
            ->when(Schema::hasTable('communication_attachments'), fn ($query) => $query->withCount('attachments'))
            ->latest()
            ->paginate(12, ['*'], 'page')
            ->withQueryString();

        $readCounts = DB::table('notifications')
            ->whereIn('communication_id', $items->getCollection()->pluck('id'))
            ->where('is_read', true)
            ->selectRaw('communication_id, COUNT(*) as total')
            ->groupBy('communication_id')
            ->pluck('total', 'communication_id');

        $items->getCollection()->each(function (Communication $communication) use ($readCounts) {
            $communication->read_count = (int) ($readCounts[$communication->id] ?? 0);
        });

        return [
            'filter' => $filter,
            'items' => $items,
            'counts' => [
                'all' => (int) $counts->sum(),
                'students' => (int) ($counts['students'] ?? 0),
                'faculty' => (int) ($counts['faculty'] ?? 0),
            ],
            'students' => $students,
            'faculty' => $faculty,
            'subjects' => Subject::where('is_active', true)->orderBy('name')->get(),
            'yearLevels' => $students->pluck('studentProfile.year_level')->filter()->unique()->sort()->values(),
            'sections' => $students->pluck('studentProfile.section')->filter()->unique()->sort()->values(),
        ];
    }

    /**
     * One announcement opened from the board: the full message, its files, and
     * who has (and hasn't) seen it. Only its sender may open it.
     */
    public function show(Communication $communication)
    {
        abort_unless($communication->sender_id === Auth::id(), 403);

        $attachments = Schema::hasTable('communication_attachments')
            ? $communication->attachments()->get()->map->forDisplay()->all()
            : [];

        $recipients = DB::table('notifications')
            ->join('users', 'users.id', '=', 'notifications.recipient_id')
            ->leftJoin('student_profiles', 'student_profiles.user_id', '=', 'users.id')
            ->where('notifications.communication_id', $communication->id)
            ->orderBy('users.first_name')->orderBy('users.last_name')
            ->get(['users.first_name', 'users.last_name', 'users.email', 'student_profiles.year_level', 'student_profiles.section', 'notifications.is_read', 'notifications.updated_at'])
            ->map(function ($row) use ($communication) {
                $seen = (bool) $row->is_read;

                return [
                    'name' => trim($row->first_name . ' ' . $row->last_name),
                    'initials' => strtoupper(mb_substr((string) $row->first_name, 0, 1) . mb_substr((string) $row->last_name, 0, 1)),
                    'meta' => $communication->audience === 'students'
                        ? collect(['Year ' . ($row->year_level ?: '—'), $row->section ?: 'No section'])->join(' · ')
                        : $row->email,
                    'seen' => $seen,
                    'seen_at' => $seen && $row->updated_at ? \Illuminate\Support\Carbon::parse($row->updated_at)->format('M j, g:i A') : null,
                ];
            })->values();

        return response()->json([
            'id' => $communication->id,
            'title' => $communication->title,
            'message' => $communication->message,
            'audience' => $communication->audience,
            'type' => $communication->type,
            'link' => $communication->link,
            'sent_at' => $communication->created_at->format('M j, Y · g:i A'),
            'to' => $communication->targetSummary(),
            'recipient_count' => $communication->recipient_count,
            'seen_count' => $recipients->where('seen', true)->count(),
            'attachments' => $attachments,
            'recipients' => $recipients,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'audience' => ['required', Rule::in(['students', 'faculty'])],
            'target_type' => ['required', Rule::in(['all', 'group', 'selected'])],
            'year_level' => ['nullable', 'integer', 'between:1,6'],
            'section' => ['nullable', 'string', 'max:30'],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'recipient_ids' => ['nullable', 'array'],
            'recipient_ids.*' => ['integer', 'distinct', 'exists:users,id'],
            'title' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:5000'],
            'type' => ['required', Rule::in(['announcement', 'reminder', 'schedule_change'])],
            // Priority is no longer asked for; kept optional so older callers still work.
            'priority' => ['nullable', Rule::in(['normal', 'high', 'urgent'])],
            'link' => ['nullable', 'string', 'max:255', 'regex:/^\/[A-Za-z0-9_\-\/?.=&%]*$/'],
            'attachments' => ['nullable', 'array', 'max:' . CommunicationAttachment::MAX_FILES],
            'attachments.*' => ['file', 'mimes:' . CommunicationAttachment::ALLOWED_EXTENSIONS, 'max:' . CommunicationAttachment::MAX_KILOBYTES],
        ], [
            'link.regex' => 'The optional link must be an internal path beginning with /.',
            'attachments.max' => 'You can attach up to ' . CommunicationAttachment::MAX_FILES . ' files.',
            'attachments.*.max' => 'Each file must be ' . (CommunicationAttachment::MAX_KILOBYTES / 1024) . ' MB or smaller.',
            'attachments.*.mimes' => 'That file type cannot be attached. Use a document, spreadsheet, presentation, PDF, image or zip.',
            'attachments.*.uploaded' => 'A file could not be uploaded. Try a smaller file.',
        ]);
        $data['link'] = $data['link'] ?? null;
        $data['priority'] = $data['priority'] ?? 'normal';
        $files = array_values(array_filter((array) $request->file('attachments')));

        if ($data['target_type'] === 'selected' && empty($data['recipient_ids'])) {
            return back()->withInput()->withErrors(['recipient_ids' => 'Select at least one recipient.']);
        }

        $roleId = $data['audience'] === 'students' ? Role::STUDENT : Role::FACULTY;
        $recipients = User::query()->where('role_id', $roleId)->where('is_active', true);
        $this->applyTarget($recipients, $data);
        $recipientUsers = $recipients->get(['id', 'first_name', 'last_name', 'email']);
        $recipientIds = $recipientUsers->pluck('id');

        if ($recipientIds->isEmpty()) {
            return back()->withInput()->withErrors(['audience' => 'No active recipients matched that selection.']);
        }

        $communication = DB::transaction(function () use ($data, $recipientIds, $files) {
            $filters = array_filter([
                'year_level' => $data['year_level'] ?? null,
                'section' => $data['section'] ?? null,
                'subject_id' => $data['subject_id'] ?? null,
                'recipient_ids' => $data['target_type'] === 'selected' ? $recipientIds->values()->all() : null,
            ], fn ($value) => $value !== null && $value !== '');

            $communication = Communication::create([
                'sender_id' => Auth::id(),
                'audience' => $data['audience'],
                'target_type' => $data['target_type'],
                'target_filters' => $filters,
                'title' => $data['title'],
                'message' => $data['message'],
                'type' => $data['type'],
                'priority' => $data['priority'],
                'link' => $data['link'] ?: null,
                'recipient_count' => $recipientIds->count(),
            ]);

            foreach ($files as $file) {
                $communication->attachments()->create([
                    'path' => $file->store('communication-attachments', 'public'),
                    'original_name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'category' => \App\Models\Material::categoryFor(strtolower($file->getClientOriginalExtension())),
                ]);
            }

            $now = now();
            $recipientIds->chunk(500)->each(function ($ids) use ($communication, $data, $now) {
                DB::table('notifications')->insert($ids->map(fn ($recipientId) => [
                    'communication_id' => $communication->id,
                    'recipient_id' => $recipientId,
                    'sender_id' => Auth::id(),
                    'type' => $data['priority'],
                    'title' => $data['title'],
                    'message' => $data['message'],
                    'link' => $data['link'] ?: null,
                    'is_read' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all());
            });

            return $communication;
        });

        $senderName = Auth::user()->name;
        // Files are opened in CPACE, so an announcement with files but no link
        // sends people to their notifications, where the files are listed.
        $attachmentNames = collect($files)->map(fn ($file) => $file->getClientOriginalName())->all();
        $ctaUrl = $data['link'] ? url($data['link']) : ($attachmentNames ? url('/notifications') : null);

        // Real SMTP email to each recipient, mirroring the in-app notification
        // above. Failures (bad address, mail server down, etc.) are logged and
        // skipped so one bad recipient doesn't block the rest of the batch.
        $emailed = 0;
        $failed = 0;
        foreach ($recipientUsers as $recipientUser) {
            if (empty($recipientUser->email)) {
                continue;
            }

            try {
                Mail::to($recipientUser->email)->send(new CommunicationMail(
                    recipientName: $recipientUser->first_name,
                    senderName: $senderName,
                    title: $data['title'],
                    body: $data['message'],
                    priority: $data['priority'],
                    ctaUrl: $ctaUrl,
                    attachmentNames: $attachmentNames,
                ));
                $emailed++;
            } catch (\Throwable $e) {
                $failed++;
                Log::warning('Failed to send communication email', [
                    'communication_id' => $communication->id,
                    'recipient_id' => $recipientUser->id,
                    'email' => $recipientUser->email,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $status = "Message sent to {$communication->recipient_count} recipient" . ($communication->recipient_count === 1 ? '' : 's')
            . " ({$emailed} email" . ($emailed === 1 ? '' : 's') . ' delivered' . ($failed ? ", {$failed} failed" : '') . ').';

        return redirect()->route('messages.index', ['view' => 'announcements'])
            ->with('status', $status);
    }

    private function applyTarget(Builder $query, array $data): void
    {
        if ($data['target_type'] === 'selected') {
            $query->whereIn('id', $data['recipient_ids'] ?? []);
            return;
        }

        if ($data['target_type'] !== 'group') {
            return;
        }

        if ($data['audience'] === 'students') {
            $query->whereHas('studentProfile', function (Builder $profile) use ($data) {
                if (! empty($data['year_level'])) {
                    $profile->where('year_level', $data['year_level']);
                }
                if (! empty($data['section'])) {
                    $profile->where('section', $data['section']);
                }
            });
        } elseif (! empty($data['subject_id'])) {
            $query->whereHas('assignedSubjects', fn (Builder $subjects) => $subjects->where('subjects.id', $data['subject_id']));
        }
    }
}
