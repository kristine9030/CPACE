{{--
    Review Notes card grid fragment. Rendered both inside the full page and on its
    own for live (AJAX) search / filter / sort / pagination.
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

<div class="notes-cards-grid">
    @php
        $subjectAccents = [
            'aud'  => ['border'=>'#e05a5a','bg'=>'#fdeaea','text'=>'#c0392b'],
            'tax'  => ['border'=>'#3aac6b','bg'=>'#e8f7ee','text'=>'#1e7e4e'],
            'far'  => ['border'=>'#5491e8','bg'=>'#e9f1fd','text'=>'#2861c4'],
            'afar' => ['border'=>'#4a7fd6','bg'=>'#eaf0fb','text'=>'#2f63c4'],
            'rfbt' => ['border'=>'#9970d8','bg'=>'#f0eafb','text'=>'#7a4fc7'],
            'ms'   => ['border'=>'#e8a23a','bg'=>'#fef3e2','text'=>'#c07e0a'],
        ];
    @endphp

    @foreach($notes as $note)
        @php
            $code    = strtolower($note->subject->code ?? '');
            $accent  = $subjectAccents[$code] ?? ['border'=>'#bbb','bg'=>'#f5f5f5','text'=>'#777'];
            $tags    = $note->tagList();
            $preview = mb_strimwidth(strip_tags($note->content ?? ''), 0, 110, '…');
        @endphp

        <div class="note-card" style="--nc-border:{{ $accent['border'] }};">

            <div class="nc-body">
                {{-- Subject + topic badges --}}
                <div class="nc-top">
                    <div class="nc-badges">
                        <span class="nc-subject-badge" style="background:{{ $accent['bg'] }};color:{{ $accent['text'] }};">
                            {{ $note->subject->code ?? 'General' }}
                        </span>
                        @if($note->topic)
                            <span class="nc-topic-badge">{{ $note->topic->name }}</span>
                        @endif
                    </div>
                    <button class="nc-fav {{ $note->is_favorite ? 'active' : '' }}"
                            title="{{ $note->is_favorite ? 'Remove from favorites' : 'Add to favorites' }}"
                            onclick="toggleFavorite({{ $note->id }}, this)">
                        <i class="{{ $note->is_favorite ? 'fas' : 'far' }} fa-star"></i>
                    </button>
                </div>

                {{-- Title --}}
                <div class="nc-title" onclick="viewNote({{ $note->id }})">{{ $note->title }}</div>

                {{-- Content preview --}}
                <div class="nc-preview {{ $preview ? '' : 'nc-preview--empty' }}" onclick="viewNote({{ $note->id }})">
                    {{ $preview ?: 'No content yet. Click to add some.' }}
                </div>

                {{-- Tags --}}
                @if(count($tags))
                    <div class="nc-tags">
                        @foreach(array_slice($tags, 0, 4) as $tag)
                            <span class="nc-tag">{{ trim($tag) }}</span>
                        @endforeach
                        @if(count($tags) > 4)
                            <span class="nc-tag nc-tag--more">+{{ count($tags) - 4 }}</span>
                        @endif
                    </div>
                @endif

                {{-- Footer --}}
                <div class="nc-footer">
                    <div class="nc-meta">
                        <span title="Created"><i class="far fa-calendar-alt"></i> {{ $note->created_at->format('M j, Y') }}</span>
                        @if($note->last_reviewed_at)
                            <span class="{{ $note->last_reviewed_at->gt(now()->subDay()) ? 'nc-meta--recent' : '' }}" title="Last reviewed">
                                <i class="fas fa-eye"></i> {{ $note->last_reviewed_at->diffForHumans() }}
                            </span>
                        @else
                            <span class="nc-meta--muted"><i class="fas fa-eye-slash"></i> Not reviewed</span>
                        @endif
                    </div>
                    <div class="nc-actions">
                        <button class="nca-btn" title="Open" onclick="viewNote({{ $note->id }})"><i class="fas fa-expand-alt"></i></button>
                        <button class="nca-btn" title="Edit" onclick="editNote({{ $note->id }})"><i class="fas fa-pen"></i></button>
                        <button class="nca-btn nca-btn--del" title="Delete" onclick="deleteNote({{ $note->id }}, '{{ addslashes($note->title) }}')"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="notes-footer">
    <div class="info">
        Showing {{ $notes->firstItem() }}–{{ $notes->lastItem() }} of {{ $notes->total() }} notes
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
