{{--
    One node of the topic picker, recursing into its children. Topics are
    hierarchical (topics.parent_id), so the picker shows the same structure
    faculty see everywhere else rather than a flat list.

    Expects: $node, $selectedTopics, $depth, $readOnly
--}}
<label class="topic-row" data-depth="{{ $depth }}" style="padding-left:{{ 14 + ($depth * 22) }}px;">
    @if($depth > 0)
        <span class="topic-branch" aria-hidden="true"></span>
    @endif
    <input type="checkbox" class="topic-check" value="{{ $node->id }}"
           data-bank="{{ $node->bank_count ?? 0 }}"
           @checked(in_array($node->id, $selectedTopics)) @disabled($readOnly)>
    <span class="topic-name">{{ $node->name }}</span>
    <span class="topic-bank {{ ($node->bank_count ?? 0) === 0 ? 'zero' : '' }}">
        {{ $node->bank_count ?? 0 }} {{ Str::plural('item', $node->bank_count ?? 0) }}
    </span>
</label>

@foreach($node->children as $child)
    @include('faculty.partials.mock-topic-node', [
        'node' => $child,
        'selectedTopics' => $selectedTopics,
        'depth' => $depth + 1,
        'readOnly' => $readOnly,
    ])
@endforeach
