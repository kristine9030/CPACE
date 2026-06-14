{{--
    Review Notes table fragment. Rendered both inside the full page and on its
    own for live (AJAX) search / filter / sort / pagination - the same pattern
    used by the faculty Test Bank table.
--}}
@if($notes->isEmpty())
    <div class="empty-state">
        <i class="fas fa-file-circle-plus"></i>
        <p>No notes found</p>
        <span>{{ $search || $subject || $filter ? 'Try clearing your filters, or' : 'Start by creating your first note.' }}
            <a href="#" onclick="openCreateModal(); return false;" style="color:#7B1D1D;font-weight:600;">create a note</a>.
        </span>
    </div>
@else
<table class="notes-table">
    <thead>
        <tr>
            <th>Note Title</th>
            <th>Subject</th>
            <th>Topics</th>
            <th>Last Reviewed</th>
            <th>
                <a href="{{ route('review-notes', array_merge(request()->query(), ['sort' => $sort === 'oldest' ? 'recent' : 'oldest'])) }}">
                    Created On <i class="fas fa-chevron-down"></i>
                </a>
            </th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @php
            $iconColors = ['red','green','blue','purple','amber'];
        @endphp
        @foreach($notes as $i => $note)
            @php
                $code = strtolower($note->subject->code ?? '');
                $tagCount = count($note->tagList());
            @endphp
            <tr data-id="{{ $note->id }}">
                <td>
                    <div class="note-title-cell">
                        <div class="note-icon {{ $iconColors[$i % count($iconColors)] }}"><i class="fas fa-file-lines"></i></div>
                        <span class="note-title-text">{{ $note->title }}</span>
                    </div>
                </td>
                <td>
                    @if($note->subject)
                        <span class="subject-tag {{ $code }}">{{ $note->subject->code }}</span>
                    @else
                        <span class="subject-tag none">General</span>
                    @endif
                </td>
                <td>{{ $tagCount }}</td>
                <td>
                    @if($note->last_reviewed_at)
                        @if($note->last_reviewed_at->gt(now()->subDay()))
                            <span class="last-reviewed-recent">{{ $note->last_reviewed_at->diffForHumans() }}</span>
                        @else
                            {{ $note->last_reviewed_at->diffForHumans() }}
                        @endif
                    @else
                        <span class="muted-cell">Not yet</span>
                    @endif
                </td>
                <td>{{ $note->created_at->format('M j, Y') }}</td>
                <td>
                    <div class="actions-cell">
                        <i class="fas fa-eye" title="View" onclick="viewNote({{ $note->id }})"></i>
                        <i class="fas fa-star {{ $note->is_favorite ? 'fav-on' : '' }}" title="Favorite" onclick="toggleFavorite({{ $note->id }}, this)"></i>
                        <i class="fas fa-pen" title="Edit" onclick="editNote({{ $note->id }})"></i>
                        <i class="fas fa-trash del" title="Delete" onclick="deleteNote({{ $note->id }}, '{{ addslashes($note->title) }}')"></i>
                    </div>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<div class="notes-footer">
    <div class="info">
        Showing {{ $notes->firstItem() }} to {{ $notes->lastItem() }} of {{ $notes->total() }} notes
    </div>
    <div class="pagination">
        @if($notes->onFirstPage())
            <span class="disabled"><i class="fas fa-chevron-left"></i></span>
        @else
            <a href="{{ $notes->previousPageUrl() }}"><i class="fas fa-chevron-left"></i></a>
        @endif

        @foreach($notes->getUrlRange(1, $notes->lastPage()) as $page => $url)
            @if($page == $notes->currentPage())
                <span class="active">{{ $page }}</span>
            @else
                <a href="{{ $url }}">{{ $page }}</a>
            @endif
        @endforeach

        @if($notes->hasMorePages())
            <a href="{{ $notes->nextPageUrl() }}"><i class="fas fa-chevron-right"></i></a>
        @else
            <span class="disabled"><i class="fas fa-chevron-right"></i></span>
        @endif
    </div>
</div>
@endif
