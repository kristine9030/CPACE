<?php

namespace App\Http\Controllers\Chair;

use App\Http\Controllers\Controller;
use App\Mail\CommunicationMail;
use App\Models\Communication;
use App\Models\Role;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class CommunicationController extends Controller
{
    public function index(Request $request)
    {
        $tab = in_array($request->query('tab'), ['students', 'faculty', 'history'], true)
            ? $request->query('tab')
            : 'students';

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

        $history = Communication::query()
            ->where('sender_id', Auth::id())
            ->latest()
            ->paginate(10, ['*'], 'history_page')
            ->withQueryString();

        $readCounts = DB::table('notifications')
            ->whereIn('communication_id', $history->getCollection()->pluck('id'))
            ->where('is_read', true)
            ->selectRaw('communication_id, COUNT(*) as total')
            ->groupBy('communication_id')
            ->pluck('total', 'communication_id');

        $history->getCollection()->each(function (Communication $communication) use ($readCounts) {
            $communication->read_count = (int) ($readCounts[$communication->id] ?? 0);
        });

        return view('chair.communications', [
            'tab' => $tab,
            'students' => $students,
            'faculty' => $faculty,
            'subjects' => Subject::where('is_active', true)->orderBy('name')->get(),
            'yearLevels' => $students->pluck('studentProfile.year_level')->filter()->unique()->sort()->values(),
            'sections' => $students->pluck('studentProfile.section')->filter()->unique()->sort()->values(),
            'history' => $history,
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
            'priority' => ['required', Rule::in(['normal', 'high', 'urgent'])],
            'link' => ['nullable', 'string', 'max:255', 'regex:/^\/[A-Za-z0-9_\-\/?.=&%]*$/'],
        ], [
            'link.regex' => 'The optional link must be an internal path beginning with /.',
        ]);
        $data['link'] = $data['link'] ?? null;

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

        $communication = DB::transaction(function () use ($data, $recipientIds) {
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
        $ctaUrl = $data['link'] ? url($data['link']) : null;

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

        return redirect()->route('chair.communications', ['tab' => 'history'])
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
