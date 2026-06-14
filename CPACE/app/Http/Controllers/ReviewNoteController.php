<?php

namespace App\Http\Controllers;

use App\Models\ReviewNote;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReviewNoteController extends Controller
{
    /**
     * Review Notes page. Every figure - stat cards, the notes table, the review
     * streak and the top-reviewed-topics list - is computed live from the
     * current student's own review_notes rows. Nothing is hard-coded.
     */
    public function index(Request $request)
    {
        $studentId = Auth::id();

        // ── Filters / sort (drive the table + Quick Access shortcuts) ──────
        $search   = trim((string) $request->query('q', ''));
        $subject  = $request->query('subject');          // subject id or ''
        $sort     = $request->query('sort', 'recent');   // recent | oldest | az
        $filter   = $request->query('filter');           // favorites | recent (quick access)

        $query = ReviewNote::where('student_id', $studentId);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('tags', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($subject !== null && $subject !== '' && is_numeric($subject)) {
            $query->where('subject_id', (int) $subject);
        }

        if ($filter === 'favorites') {
            $query->where('is_favorite', true);
        }

        match ($sort) {
            'oldest' => $query->orderBy('created_at', 'asc'),
            'az'     => $query->orderBy('title', 'asc'),
            'reviewed' => $query->orderByDesc('last_reviewed_at'),
            default  => $filter === 'recent'
                ? $query->orderByDesc('last_reviewed_at')->orderByDesc('created_at')
                : $query->orderByDesc('created_at'),
        };

        $notes = $query->with(['subject', 'topic'])
            ->paginate(8)
            ->withQueryString();

        // Live search / filter / sort / pagination requests only need the table
        // markup so the page never has to do a full reload (same approach as the
        // faculty Test Bank).
        if ($request->ajax()) {
            return view('student.partials.review-notes-table', compact(
                'notes', 'search', 'subject', 'sort', 'filter'
            ));
        }

        // ── Stat cards ─────────────────────────────────────────────────────
        $allNotes      = ReviewNote::where('student_id', $studentId);
        $totalNotes    = (clone $allNotes)->count();
        $notesThisWeek = (clone $allNotes)->where('created_at', '>=', Carbon::now()->subDays(7))->count();
        $subjectsCovered = (clone $allNotes)->whereNotNull('subject_id')->distinct('subject_id')->count('subject_id');
        $subjectsTotal   = Subject::count();

        // Top note (most reviewed) and the most recent review for the cards.
        $topNote    = (clone $allNotes)->where('review_count', '>', 0)->orderByDesc('review_count')->first();
        $lastNote   = (clone $allNotes)->whereNotNull('last_reviewed_at')->orderByDesc('last_reviewed_at')->first();

        $stats = [
            'total'           => $totalNotes,
            'this_week'       => $notesThisWeek,
            'subjects'        => $subjectsCovered,
            'subjects_total'  => $subjectsTotal,
            'top_topic'       => $topNote->title ?? null,
            'top_topic_count' => $topNote->review_count ?? 0,
            'last_reviewed'   => $lastNote?->last_reviewed_at,
        ];

        // ── Review streak (shared gamification profile) ────────────────────
        $profile    = DB::table('student_profiles')->where('user_id', $studentId)->first();
        $streakDays = (int) ($profile->streak_days ?? 0);
        $weekDays   = $this->weeklyActivity($studentId);

        // ── Top reviewed topics (group the student's notes by tag/subject) ─
        $topReviewed = $this->topReviewedTopics($studentId);

        // ── Lookups for the New / Edit modal ───────────────────────────────
        $subjects = Subject::orderBy('id')->get(['id', 'code', 'name']);
        $topics   = DB::table('topics')
            ->orderBy('subject_id')->orderBy('name')
            ->get(['id', 'subject_id', 'name']);

        return view('student.review-notes', compact(
            'notes',
            'stats',
            'streakDays',
            'weekDays',
            'topReviewed',
            'subjects',
            'topics',
            'search',
            'subject',
            'sort',
            'filter'
        ));
    }

    /**
     * Create a new note for the current student.
     */
    public function store(Request $request)
    {
        $data = $this->validateNote($request);
        $data['student_id'] = Auth::id();

        $note = ReviewNote::create($data);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'note' => $this->present($note->fresh(['subject', 'topic']))]);
        }

        return redirect()->route('review-notes')->with('status', 'Note created.');
    }

    /**
     * Return a single note as JSON (for the View / Edit modal) and count it as
     * a review when opened for reading.
     */
    public function show(Request $request, ReviewNote $note)
    {
        $this->authorizeNote($note);

        if ($request->boolean('read')) {
            $note->increment('review_count');
            $note->forceFill(['last_reviewed_at' => now()])->save();
            $note->refresh();
        }

        return response()->json(['ok' => true, 'note' => $this->present($note->load(['subject', 'topic']))]);
    }

    /**
     * Update an existing note.
     */
    public function update(Request $request, ReviewNote $note)
    {
        $this->authorizeNote($note);

        $note->update($this->validateNote($request));

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'note' => $this->present($note->fresh(['subject', 'topic']))]);
        }

        return redirect()->route('review-notes')->with('status', 'Note updated.');
    }

    /**
     * Delete a note.
     */
    public function destroy(Request $request, ReviewNote $note)
    {
        $this->authorizeNote($note);
        $note->delete();

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()->route('review-notes')->with('status', 'Note deleted.');
    }

    /**
     * Toggle the favourite flag (used by the star quick action).
     */
    public function favorite(Request $request, ReviewNote $note)
    {
        $this->authorizeNote($note);
        $note->is_favorite = ! $note->is_favorite;
        $note->save();

        return response()->json(['ok' => true, 'is_favorite' => $note->is_favorite]);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────────────

    private function validateNote(Request $request): array
    {
        $validated = $request->validate([
            'title'      => ['required', 'string', 'max:180'],
            'content'    => ['nullable', 'string'],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'topic_id'   => ['nullable', 'integer', 'exists:topics,id'],
            'tags'       => ['nullable', 'string', 'max:255'],
        ]);

        // Normalise tags: trim, drop blanks, collapse to a clean comma list.
        if (! empty($validated['tags'])) {
            $validated['tags'] = collect(explode(',', $validated['tags']))
                ->map(fn ($t) => trim($t))
                ->filter()
                ->implode(', ');
        }

        return $validated;
    }

    private function authorizeNote(ReviewNote $note): void
    {
        abort_unless($note->student_id === Auth::id(), 403);
    }

    /**
     * Shape a note for the front-end (modal + table refresh).
     */
    private function present(ReviewNote $note): array
    {
        return [
            'id'             => $note->id,
            'title'          => $note->title,
            'content'        => $note->content,
            'subject_id'     => $note->subject_id,
            'subject_code'   => $note->subject->code ?? null,
            'topic_id'       => $note->topic_id,
            'topic_name'     => $note->topic->name ?? null,
            'tags'           => $note->tags,
            'tag_list'       => $note->tagList(),
            'is_favorite'    => $note->is_favorite,
            'review_count'   => $note->review_count,
            'last_reviewed'  => $note->last_reviewed_at?->diffForHumans(),
            'created_on'     => $note->created_at?->format('M j, Y'),
        ];
    }

    /**
     * Which of the last 7 days (Mon-Sun, current week) the student created or
     * reviewed at least one note - drives the streak day dots.
     */
    private function weeklyActivity(int $studentId): array
    {
        $weekStart = Carbon::now()->startOfWeek(Carbon::MONDAY);

        $dates = ReviewNote::where('student_id', $studentId)
            ->where(function ($q) use ($weekStart) {
                $q->where('created_at', '>=', $weekStart)
                  ->orWhere('last_reviewed_at', '>=', $weekStart);
            })
            ->get(['created_at', 'last_reviewed_at'])
            ->flatMap(function ($n) use ($weekStart) {
                $out = [];
                foreach ([$n->created_at, $n->last_reviewed_at] as $d) {
                    if ($d && $d->gte($weekStart)) {
                        $out[] = $d->toDateString();
                    }
                }
                return $out;
            })
            ->unique()
            ->all();

        $labels = ['M', 'T', 'W', 'T', 'F', 'S', 'S'];
        $days = [];
        foreach ($labels as $i => $label) {
            $date = $weekStart->copy()->addDays($i);
            $days[] = [
                'label' => $label,
                'done'  => in_array($date->toDateString(), $dates, true),
            ];
        }

        return $days;
    }

    /**
     * Top reviewed "topics": prefer the linked topic name, otherwise the
     * subject, otherwise the note title - summing the review counts.
     */
    private function topReviewedTopics(int $studentId)
    {
        return ReviewNote::where('student_id', $studentId)
            ->where('review_count', '>', 0)
            ->with(['subject', 'topic'])
            ->get()
            ->groupBy(fn ($n) => $n->topic->name ?? $n->subject->name ?? $n->title)
            ->map(fn ($group, $name) => [
                'name'    => $name,
                'reviews' => $group->sum('review_count'),
            ])
            ->sortByDesc('reviews')
            ->take(4)
            ->values();
    }
}
