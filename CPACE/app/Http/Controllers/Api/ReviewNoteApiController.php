<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReviewNote;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReviewNoteApiController extends Controller
{
    public function index(Request $request)
    {
        $studentId = Auth::id();
        $search    = trim((string) $request->query('q', ''));
        $subject   = $request->query('subject');
        $sort      = $request->query('sort', 'recent');
        $filter    = $request->query('filter');
        $page      = max(1, (int) $request->query('page', 1));

        $query = ReviewNote::where('student_id', $studentId);

        if ($search !== '') {
            $query->where(fn ($q) => $q->where('title', 'like', "%{$search}%")->orWhere('tags', 'like', "%{$search}%")->orWhere('content', 'like', "%{$search}%"));
        }

        if ($subject !== null && $subject !== '' && is_numeric($subject)) {
            $query->where('subject_id', (int) $subject);
        }

        if ($filter === 'favorites') {
            $query->where('is_favorite', true);
        }

        match ($sort) {
            'oldest'   => $query->orderBy('created_at', 'asc'),
            'az'       => $query->orderBy('title', 'asc'),
            'reviewed' => $query->orderByDesc('last_reviewed_at'),
            default    => $query->orderByDesc('created_at'),
        };

        $notes = $query->with(['subject', 'topic'])->paginate(15, ['*'], 'page', $page);

        $allNotes        = ReviewNote::where('student_id', $studentId);
        $totalNotes      = (clone $allNotes)->count();
        $notesThisWeek   = (clone $allNotes)->where('created_at', '>=', Carbon::now()->subDays(7))->count();
        $subjectsCovered = (clone $allNotes)->whereNotNull('subject_id')->distinct('subject_id')->count('subject_id');

        $subjects = Subject::orderBy('id')->get(['id', 'code', 'name']);
        $topics   = DB::table('topics')->orderBy('subject_id')->orderBy('name')->get(['id', 'subject_id', 'name']);

        return response()->json([
            'data'             => $notes->map(fn ($n) => $this->present($n)),
            'current_page'     => $notes->currentPage(),
            'last_page'        => $notes->lastPage(),
            'total'            => $notes->total(),
            'stats'            => ['total' => $totalNotes, 'this_week' => $notesThisWeek, 'subjects' => $subjectsCovered],
            'subjects'         => $subjects,
            'topics'           => $topics,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateNote($request);
        $data['student_id'] = Auth::id();
        $note = ReviewNote::create($data);

        return response()->json(['ok' => true, 'note' => $this->present($note->fresh(['subject', 'topic']))], 201);
    }

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

    public function update(Request $request, ReviewNote $note)
    {
        $this->authorizeNote($note);
        $note->update($this->validateNote($request));

        return response()->json(['ok' => true, 'note' => $this->present($note->fresh(['subject', 'topic']))]);
    }

    public function destroy(ReviewNote $note)
    {
        $this->authorizeNote($note);
        $note->delete();

        return response()->json(['ok' => true]);
    }

    public function favorite(ReviewNote $note)
    {
        $this->authorizeNote($note);
        $note->is_favorite = ! $note->is_favorite;
        $note->save();

        return response()->json(['ok' => true, 'is_favorite' => $note->is_favorite]);
    }

    private function validateNote(Request $request): array
    {
        $validated = $request->validate([
            'title'      => ['required', 'string', 'max:180'],
            'content'    => ['nullable', 'string'],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'topic_id'   => ['nullable', 'integer', 'exists:topics,id'],
            'tags'       => ['nullable', 'string', 'max:255'],
        ]);

        if (! empty($validated['tags'])) {
            $validated['tags'] = collect(explode(',', $validated['tags']))
                ->map(fn ($t) => trim($t))->filter()->implode(', ');
        }

        return $validated;
    }

    private function authorizeNote(ReviewNote $note): void
    {
        abort_unless($note->student_id === Auth::id(), 403);
    }

    private function present(ReviewNote $note): array
    {
        return [
            'id'            => $note->id,
            'title'         => $note->title,
            'content'       => $note->content,
            'subject_id'    => $note->subject_id,
            'subject_code'  => $note->subject->code ?? null,
            'topic_id'      => $note->topic_id,
            'topic_name'    => $note->topic->name ?? null,
            'tags'          => $note->tags,
            'tag_list'      => $note->tagList(),
            'is_favorite'   => $note->is_favorite,
            'review_count'  => $note->review_count,
            'last_reviewed' => $note->last_reviewed_at?->diffForHumans(),
            'created_on'    => $note->created_at?->format('M j, Y'),
        ];
    }
}
