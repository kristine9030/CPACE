<div class="table-head-bar">
    <label class="select-all-wrap">
        <input type="checkbox" class="row-checkbox" id="selectAllCb">
        <span>Select all (<span id="selectAllCount">{{ number_format($questions->total()) }}</span>)</span>
    </label>
    <span class="count">Showing {{ $questions->firstItem() ?? 0 }}–{{ $questions->lastItem() ?? 0 }} of <strong>{{ number_format($questions->total()) }}</strong> questions</span>
</div>
<table id="testBankTable" class="tb-table">
    <colgroup>
        <col style="width:48px"><col><col style="width:140px"><col style="width:110px"><col style="width:150px"><col style="width:90px">
    </colgroup>
    <thead>
        <tr>
            <th class="col-check"><input type="checkbox" class="row-checkbox" id="headCheckbox"></th>
            <th>Question</th>
            <th>Type</th>
            <th>Difficulty</th>
            <th>Status</th>
            <th class="col-actions">Actions</th>
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
            <td class="col-check"><input type="checkbox" class="row-checkbox q-checkbox" value="{{ $q->id }}"></td>
            <td>
                <div class="q-text" title="{{ $q->question_text }}">{{ \Illuminate\Support\Str::limit($q->question_text, 110) }}</div>
                <div class="q-meta">
                    <span class="subj-badge {{ $subjectClass[$q->subject_code] ?? 'b-far' }}">{{ $q->subject_code }}</span>
                    <span class="q-topic" title="{{ $q->topic_name }}">{{ $q->topic_name }}</span>
                    @if($q->exhibitImageUrl())<i class="fas fa-image q-flag" title="Has a picture"></i>@endif
                    @if(! empty($q->table_data['rows']))<i class="fas fa-table q-flag" title="Has a table"></i>@endif
                    <span class="q-id">#{{ $q->id }}</span>
                </div>
            </td>
            <td><span class="type-badge">{{ $typeLabel[$q->question_type] ?? $q->question_type }}</span></td>
            <td><span class="diff-badge {{ $diffClass[$q->difficulty] }}">{{ $diffLabel[$q->difficulty] }}</span></td>
            <td>
                @if($q->is_active)
                    <span class="status-pill sp-active"><i class="fas fa-circle" style="font-size:6px;"></i> Active</span>
                @elseif($q->isPendingAiReview())
                    <a href="{{ route('faculty.test-bank.ai-review') }}" class="status-pill sp-ai" title="Drafted by AI because this topic was short of its TOS item count. Hidden from students until approved."><i class="fas fa-robot" style="font-size:9px;"></i> AI · Needs review</a>
                @elseif($q->source === 'ai_substitute' && $q->review_status === 'rejected')
                    <span class="status-pill sp-draft" title="AI substitute rejected on review"><i class="fas fa-robot" style="font-size:9px;"></i> AI · Rejected</span>
                @else
                    <span class="status-pill sp-draft"><i class="fas fa-circle" style="font-size:6px;"></i> Draft</span>
                @endif
            </td>
            <td class="col-actions">
                <div class="row-menu">
                    <button type="button" class="row-dots" onclick="toggleRowMenu(event, this)" aria-label="Actions for question {{ $q->id }}"><i class="fas fa-ellipsis"></i></button>
                    <div class="row-dropdown">
                        <a href="{{ route('faculty.question.edit', $q->id) }}"><i class="fas fa-pen"></i> Edit question</a>
                        <a href="{{ route('faculty.question.variants', $q->id) }}"><i class="fas fa-shuffle"></i> Manage variants @if($q->variants_count) <span class="rd-count">{{ $q->variants_count }}</span>@endif</a>
                        <form method="POST" action="{{ route('faculty.question.destroy', $q->id) }}"
                              data-confirm="This question and all of its variants will be permanently removed from the test bank."
                              data-confirm-title="Delete this question?"
                              data-confirm-ok="Yes, delete it"
                              data-confirm-danger>
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="danger"><i class="fas fa-trash"></i> Delete question</button>
                        </form>
                    </div>
                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="6" style="text-align:center;color:#aaa;padding:40px;">
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
