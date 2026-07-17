<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;

use App\Models\ReviewNote;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReviewNoteController extends Controller
{
    /**
     * Review Notes workspace (folders / notes list / preview). The whole page
     * is driven client-side from the student's own notes - including archived
     * and trashed ones - so tabs, folders, search and sorting are instant.
     */
    public function index()
    {
        $studentId = Auth::id();

        $notes = ReviewNote::withTrashed()
            ->where('student_id', $studentId)
            ->with(['subject', 'topic'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (ReviewNote $note) => $this->present($note))
            ->values();

        // Lookups for the folder rail and the New / Edit note modal.
        $subjects = Subject::orderBy('id')->get(['id', 'code', 'name']);
        $topics   = DB::table('topics')
            ->orderBy('subject_id')->orderBy('name')
            ->get(['id', 'subject_id', 'name']);

        return view('student.review-notes', compact('notes', 'subjects', 'topics'));
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
     * Return a single note as JSON and count it as a review when opened for
     * reading (the preview pane sends read=1).
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
     * Delete a note: active notes move to Trash (soft delete); notes already
     * in Trash are removed permanently.
     */
    public function destroy(Request $request, ReviewNote $note)
    {
        $this->authorizeNote($note);

        if ($note->trashed()) {
            $note->forceDelete();
        } else {
            $note->delete();
        }

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'note' => $note->exists ? $this->present($note->load(['subject', 'topic'])) : null]);
        }

        return redirect()->route('review-notes')->with('status', 'Note deleted.');
    }

    /**
     * Restore a note from Trash.
     */
    public function restore(ReviewNote $note)
    {
        $this->authorizeNote($note);
        $note->restore();

        return response()->json(['ok' => true, 'note' => $this->present($note->load(['subject', 'topic']))]);
    }

    /**
     * Toggle the archived flag (Archived tab).
     */
    public function archive(ReviewNote $note)
    {
        $this->authorizeNote($note);
        $note->forceFill(['archived_at' => $note->archived_at ? null : now()])->save();

        return response()->json(['ok' => true, 'note' => $this->present($note->load(['subject', 'topic']))]);
    }

    /**
     * Toggle the favourite flag (used by the star quick action).
     */
    public function favorite(Request $request, ReviewNote $note)
    {
        $this->authorizeNote($note);
        $note->is_favorite = ! $note->is_favorite;
        $note->save();

        return response()->json(['ok' => true, 'is_favorite' => $note->is_favorite, 'note' => $this->present($note->load(['subject', 'topic']))]);
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
     * Shape a note for the front-end.
     */
    private function present(ReviewNote $note): array
    {
        return [
            'id'             => $note->id,
            'title'          => $note->title,
            'content'        => $note->content,
            'subject_id'     => $note->subject_id,
            'subject_code'   => $note->subject->code ?? null,
            'subject_name'   => $note->subject->name ?? null,
            'topic_id'       => $note->topic_id,
            'topic_name'     => $note->topic->name ?? null,
            'tags'           => $note->tags,
            'tag_list'       => $note->tagList(),
            'is_favorite'    => (bool) $note->is_favorite,
            'archived'       => $note->archived_at !== null,
            'trashed'        => $note->trashed(),
            'review_count'   => $note->review_count,
            'created_at'     => $note->created_at?->toIso8601String(),
            'last_reviewed_at' => $note->last_reviewed_at?->toIso8601String(),
            'date_display'   => $note->created_at?->format('M j, Y'),
            'created_human'  => $note->created_at?->diffForHumans(),
            'last_reviewed'  => $note->last_reviewed_at?->diffForHumans(),
            'created_on'     => $note->created_at?->format('M j, Y'),
        ];
    }
}
