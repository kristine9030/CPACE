<div class="table-head-bar">
    <span class="count">Showing {{ $questions->firstItem() ?? 0 }}–{{ $questions->lastItem() ?? 0 }} of <strong>{{ number_format($questions->total()) }}</strong> questions</span>
</div>
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Question</th>
            <th>Subject</th>
            <th>Topic</th>
            <th>Type</th>
            <th>Difficulty</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @php
        $subjectClass = ['FAR'=>'b-far','AUD'=>'b-aud','TAX'=>'b-tax','MS'=>'b-ms','RFBT'=>'b-rfbt','AFAR'=>'b-afar'];
        $diffClass = ['easy'=>'d-easy','moderate'=>'d-medium','difficult'=>'d-hard'];
        $diffLabel = ['easy'=>'Easy','moderate'=>'Medium','difficult'=>'Hard'];
        $typeLabel = ['mcq'=>'Multiple Choice','true_false'=>'True / False'];
        @endphp

        @forelse($questions as $q)
        <tr>
            <td style="color:#aaa;font-size:12px;">{{ $q->id }}</td>
            <td>
                <div class="q-text">{{ \Illuminate\Support\Str::limit($q->question_text, 70) }}</div>
                <div class="q-meta">{{ $diffLabel[$q->difficulty] }} difficulty</div>
            </td>
            <td><span class="subj-badge {{ $subjectClass[$q->subject_code] ?? 'b-far' }}">{{ $q->subject_code }}</span></td>
            <td style="font-size:12px;color:#666;">{{ $q->topic_name }}</td>
            <td><span class="type-badge">{{ $typeLabel[$q->question_type] ?? $q->question_type }}</span></td>
            <td><span class="diff-badge {{ $diffClass[$q->difficulty] }}">{{ $diffLabel[$q->difficulty] }}</span></td>
            <td>
                @if($q->is_active)
                    <span class="status-pill sp-active"><i class="fas fa-circle" style="font-size:6px;"></i> Active</span>
                @else
                    <span class="status-pill sp-draft"><i class="fas fa-circle" style="font-size:6px;"></i> Draft</span>
                @endif
            </td>
            <td style="white-space:nowrap;">
                <a href="{{ route('faculty.question.variants', $q->id) }}" class="action-btn ab-var" title="Manage variants (alternative wordings)">
                    <i class="fas fa-shuffle"></i>@if($q->variants_count)<span class="var-count">{{ $q->variants_count }}</span>@endif
                </a>
                <a href="{{ route('faculty.question.edit', $q->id) }}" class="action-btn ab-edit" style="margin-left:4px;" title="Edit"><i class="fas fa-pen"></i></a>
                <form method="POST" action="{{ route('faculty.question.destroy', $q->id) }}" style="display:inline;" onsubmit="return confirm('Delete this question?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="action-btn ab-del" style="margin-left:4px;" title="Delete"><i class="fas fa-trash"></i></button>
                </form>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="8" style="text-align:center;color:#aaa;padding:40px;">
                No questions found. <a href="{{ route('faculty.question.create') }}" style="color:var(--accent);font-weight:600;">Add a question</a>.
            </td>
        </tr>
        @endforelse
    </tbody>
</table>
<div class="pagination">
    <span class="pag-info">Showing {{ $questions->firstItem() ?? 0 }}–{{ $questions->lastItem() ?? 0 }} of {{ number_format($questions->total()) }} results</span>
    <div class="pag-btns">
        @if($questions->onFirstPage())
            <span class="pag-btn" style="opacity:.4;"><i class="fas fa-chevron-left"></i></span>
        @else
            <a href="{{ $questions->previousPageUrl() }}" class="pag-btn"><i class="fas fa-chevron-left"></i></a>
        @endif
        <span class="pag-btn active">{{ $questions->currentPage() }}</span>
        @if($questions->hasMorePages())
            <a href="{{ $questions->nextPageUrl() }}" class="pag-btn"><i class="fas fa-chevron-right"></i></a>
        @else
            <span class="pag-btn" style="opacity:.4;"><i class="fas fa-chevron-right"></i></span>
        @endif
    </div>
</div>
