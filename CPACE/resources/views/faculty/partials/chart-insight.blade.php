{{--
    Insight tip for one dashboard chart: a lightbulb in the tone of its most
    urgent insight, revealing the insight text on hover, tap or focus.

        @include('faculty.partials.chart-insight', ['tip' => $chartInsights['engagement'] ?? null])

    Renders nothing when that chart has no insight right now.
--}}
@if(! empty($tip))
    <x-tip class="insight-tip tone-{{ $tip['tone'] }}" icon="fa-lightbulb" label="Insight">
        @foreach($tip['items'] as $item)
            <span @class(['tip-note' => ! $loop->first])><strong>{{ $item['title'] }}</strong><br>{{ $item['text'] }}</span>
        @endforeach
    </x-tip>
@endif
