{{--
    The picture and/or table attached to a question, shown under its text.
    Usage: @include('partials.question-exhibit', ['item' => $question])
    Works for a Question, a mock exam item or a class quiz item. Cells are
    always escaped; the table never carries markup from the faculty.
--}}
@php
    $exImage = method_exists($item, 'exhibitImageUrl') ? $item->exhibitImageUrl() : null;
    $exTable = $item->table_data ?? null;
    $exRows = is_array($exTable) ? ($exTable['rows'] ?? []) : [];
@endphp
@if($exImage || $exRows)
    @once
    <style>
        .q-exhibit { margin:12px 0 4px; display:flex; flex-direction:column; gap:12px; }
        .q-exhibit .qx-img { display:inline-block; max-width:100%; border:1px solid #e5e7eb; border-radius:10px; overflow:hidden; background:#fff; }
        .q-exhibit .qx-img img { display:block; max-width:100%; max-height:420px; height:auto; margin:0 auto; }
        .q-exhibit .qx-scroll { overflow-x:auto; border:1px solid #e5e7eb; border-radius:10px; background:#fff; max-width:100%; }
        .q-exhibit table.qx-table { border-collapse:collapse; width:100%; font-size:13px; color:#1f2937; }
        .q-exhibit .qx-table th, .q-exhibit .qx-table td { padding:8px 12px; border-bottom:1px solid #eef0f3; text-align:left; white-space:nowrap; }
        .q-exhibit .qx-table thead th { background:#f3f4f6; font-weight:700; color:#374151; border-bottom:1px solid #e5e7eb; }
        .q-exhibit .qx-table tbody tr:last-child td { border-bottom:0; }
        .q-exhibit .qx-table .num { text-align:right; font-variant-numeric:tabular-nums; }
        .q-exhibit .qx-table tr.qx-total td { font-weight:700; border-top:2px solid #6b7280; background:#fafafa; }
    </style>
    @endonce
    <div class="q-exhibit">
        @if($exImage)
            <a class="qx-img" href="{{ $exImage }}" target="_blank" rel="noopener" title="Open the picture in a new tab"><img src="{{ $exImage }}" alt="Picture for this question" loading="lazy"></a>
        @endif
        @if($exRows)
            @php
                $exHeader = ! empty($exTable['header']);
                $exTotal = ! empty($exTable['total']);
                $exLast = count($exRows) - 1;
            @endphp
            <div class="qx-scroll">
                <table class="qx-table">
                    @if($exHeader)
                        <thead><tr>@foreach($exRows[0] as $cell)<th>{{ $cell }}</th>@endforeach</tr></thead>
                    @endif
                    <tbody>
                        @foreach($exRows as $r => $row)
                            @continue($exHeader && $r === 0)
                            <tr class="{{ $exTotal && $r === $exLast ? 'qx-total' : '' }}">
                                @foreach($row as $cell)
                                    <td class="{{ \App\Support\QuestionExhibit::isNumeric((string) $cell) ? 'num' : '' }}">{{ $cell }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endif
