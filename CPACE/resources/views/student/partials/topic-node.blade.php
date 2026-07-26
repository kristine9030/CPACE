@php $depth = $depth ?? 0; @endphp
@foreach($topics as $i => $topic)
    <div class="topic-node" style="margin-left: {{ $depth * 20 }}px;">
        <div class="topic-row-item {{ $depth === 0 ? 'root' : '' }}">
            @if($topic->children->isNotEmpty())
                <button type="button" class="node-toggle" onclick="toggleStudentTopicChildren(this)"><i class="fas fa-chevron-right"></i></button>
            @else
                <span class="node-toggle-spacer"></span>
            @endif

            @if($depth === 0)
                <div class="topic-num" style="background:{{ $color }};">{{ $i + 1 }}</div>
            @endif

            <div class="node-body">
                <div class="node-name">{{ $topic->name }}</div>
                <div class="node-meta">
                    @if($topic->material_count > 0)
                        <a href="{{ route('subjects.topic', ['subject' => $subject->id, 'topic' => $topic->id]) }}" class="meta-link"><i class="fas fa-folder-open"></i> {{ $topic->material_count }} material{{ $topic->material_count == 1 ? '' : 's' }}</a>
                    @else
                        <span><i class="fas fa-folder-open"></i> 0 materials</span>
                    @endif
                    <span><i class="fas fa-circle-question"></i> {{ $topic->question_count }} questions</span>
                    @if($topic->children->isNotEmpty())
                        <span><i class="fas fa-list"></i> {{ $topic->children->count() }} subtopic{{ $topic->children->count() === 1 ? '' : 's' }}</span>
                    @endif
                </div>
                <div class="node-progress">
                    @if($topic->progress_attempts > 0)
                        @php $passed = $topic->progress_accuracy >= $subject->passing_threshold; @endphp
                        <div class="progress-track"><div class="progress-fill" style="width:{{ $topic->progress_accuracy }}%; background:{{ $passed ? '#059669' : '#dc2626' }};"></div></div>
                        <span class="progress-label" style="color:{{ $passed ? '#059669' : '#dc2626' }};">{{ $topic->progress_accuracy }}% accuracy ({{ $topic->progress_correct }}/{{ $topic->progress_attempts }})</span>
                    @else
                        <div class="progress-track"><div class="progress-fill empty"></div></div>
                        <span class="progress-label muted">Not attempted yet</span>
                    @endif
                </div>
            </div>

            <a href="{{ route('subjects.topic', ['subject' => $subject->id, 'topic' => $topic->id]) }}" class="node-materials-link" title="View materials for this topic">
                <i class="fas fa-book-open"></i>
                <span>View</span>
            </a>
        </div>

        @if($topic->children->isNotEmpty())
            <div class="topic-children" hidden>
                @include('student.partials.topic-node', ['topics' => $topic->children, 'subject' => $subject, 'color' => $color, 'depth' => $depth + 1])
            </div>
        @endif
    </div>
@endforeach
