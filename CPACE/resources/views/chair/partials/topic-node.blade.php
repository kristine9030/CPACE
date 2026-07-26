@php $depth = $depth ?? 0; @endphp
@foreach($topics as $topic)
    <div class="topic-node" style="margin-left: {{ $depth * 18 }}px;">
        <div class="topic-row">
            @if($topic->children->isNotEmpty())
                <button type="button" class="topic-toggle" onclick="toggleTopicChildren(this)"><i class="fas fa-chevron-right"></i></button>
            @else
                <span class="topic-toggle-spacer"></span>
            @endif
            <span class="topic-order">{{ $topic->sort_order }}</span>
            <div class="topic-info">
                <div class="topic-name">{{ $topic->name }} @unless($topic->is_active)<span class="inactive-pill">Inactive</span>@endunless</div>
                <div class="topic-meta">{{ $topic->questions_count }} test-bank question{{ $topic->questions_count === 1 ? '' : 's' }}@if($topic->children->isNotEmpty()) &middot; {{ $topic->children->count() }} subtopic{{ $topic->children->count() === 1 ? '' : 's' }}@endif</div>
            </div>
            <div class="topic-actions">
                <button type="button" class="icon-btn ib-edit" title="Add subtopic" onclick="openTopic({{ $subject->id }}, '{{ addslashes($subject->code) }}', null, {{ $topic->id }})"><i class="fas fa-plus"></i></button>
                <button type="button" class="icon-btn ib-edit" title="Edit topic" onclick="openTopic({{ $subject->id }}, '{{ addslashes($subject->code) }}', {{ Illuminate\Support\Js::from(['id'=>$topic->id,'name'=>$topic->name,'description'=>$topic->description,'sort_order'=>$topic->sort_order,'is_active'=>$topic->is_active,'parent_id'=>$topic->parent_id]) }})"><i class="fas fa-pen"></i></button>
                <form method="POST" action="{{ route('chair.subjects.topics.destroy', [$subject, $topic]) }}" onsubmit="return confirm('Remove this topic? Topics containing questions or subtopics are protected.');">@csrf @method('DELETE')<button class="icon-btn ib-delete" title="Remove topic"><i class="fas fa-trash"></i></button></form>
            </div>
        </div>
        @if($topic->children->isNotEmpty())
            <div class="topic-children" hidden>
                @include('chair.partials.topic-node', ['subject' => $subject, 'topics' => $topic->children, 'depth' => $depth + 1])
            </div>
        @endif
    </div>
@endforeach
