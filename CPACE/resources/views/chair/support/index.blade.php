@extends('help.layout', ['active' => 'support'])

@section('title', 'Support Inbox')
@section('heading', 'Support Inbox')
@section('subheading', $subheading ?? 'Requests from Help & Support and the website’s “Report an issue”.')

@section('actions')
    <a class="btn btn-ghost" href="{{ route('help.index') }}"><i class="fas fa-circle-question"></i> Help & Support</a>
@endsection

@push('styles')
<style>
    .toolbar{display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap;padding:14px 18px;border-bottom:1px solid var(--line)}
    .tabs{display:flex;gap:6px;flex-wrap:wrap}
    .tab{display:inline-flex;align-items:center;gap:7px;padding:8px 13px;border-radius:9px;font-size:13px;font-weight:600;color:var(--text);text-decoration:none}
    .tab:hover{background:#f6f1f0}
    .tab.active{background:var(--primary-light);color:var(--primary)}
    .tab .count{font-size:11px;background:#fff;border:1px solid var(--line);border-radius:999px;padding:1px 8px;color:var(--muted)}
    .tab.active .count{border-color:#ecd2d0;color:var(--primary)}
    .search{position:relative}
    .search i{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#aaa;font-size:13px}
    .search input{width:260px;padding:9px 12px 9px 34px;border:1px solid #e0d6d5;border-radius:9px;font:13px 'Poppins',sans-serif;outline:none}
    .search input:focus{border-color:var(--primary)}

    .rows{list-style:none;margin:0;padding:0}
    .row{display:grid;grid-template-columns:70px minmax(0,1fr) 180px 120px 110px;gap:14px;align-items:center;padding:14px 18px;border-bottom:1px solid #f3ebea;text-decoration:none;color:inherit}
    .row:hover{background:#fcf8f7}
    .row.head{font-size:11px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:var(--muted);background:#faf6f5;padding:10px 18px}
    .row.head:hover{background:#faf6f5}
    .r-id{font-size:13px;font-weight:600;color:var(--muted)}
    .r-subject{font-size:14px;font-weight:600;color:var(--ink);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .r-sub{font-size:12px;color:var(--muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .r-from{font-size:13px;color:var(--ink);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .r-time{font-size:12px;color:var(--muted)}
    .row.is-new .r-subject::before{content:'';display:inline-block;width:8px;height:8px;border-radius:50%;background:var(--info);margin-right:8px;vertical-align:middle}
    .pager{display:flex;justify-content:center;gap:8px;padding:16px}
    .pager a,.pager span{padding:8px 13px;border-radius:8px;border:1px solid var(--line);background:#fff;color:var(--text);text-decoration:none;font-size:12px}
    .pager span{color:#bbb}
    @media(max-width:900px){
        .row{grid-template-columns:minmax(0,1fr) auto;gap:6px 12px}
        .row.head{display:none}
        .r-id,.r-time{display:none}
        .r-from{grid-column:1;font-size:12px;color:var(--muted)}
        .row .pill{grid-column:2;grid-row:1}
        .search input{width:100%}.search{flex:1}
    }
</style>
@endpush

@section('content')
    @php
        $tabs = [
            'open'      => ['Open', $counts['open']],
            'new'       => ['New', $counts['new']],
            'in_review' => ['In progress', $counts['in_review']],
            'resolved'  => ['Resolved', $counts['resolved']],
            'all'       => ['All', $counts['all']],
        ];
    @endphp
    <section class="card" style="overflow:hidden">
        <div class="toolbar">
            <nav class="tabs" aria-label="Filter by status">
                @foreach($tabs as $key => [$label, $count])
                    <a class="tab {{ $status === $key ? 'active' : '' }}" href="{{ route($indexRoute ?? 'chair.support.index', array_filter(['status' => $key, 'q' => $search])) }}">
                        {{ $label }} <span class="count">{{ $count }}</span>
                    </a>
                @endforeach
            </nav>
            <form class="search" method="GET" action="{{ route($indexRoute ?? 'chair.support.index') }}" role="search">
                <input type="hidden" name="status" value="{{ $status }}">
                <i class="fas fa-search"></i>
                <input type="search" name="q" value="{{ $search }}" placeholder="Search subject, name, email or #id" aria-label="Search requests">
            </form>
        </div>

        @if($tickets->isEmpty())
            <div class="empty" style="padding:60px 16px">
                <i class="fas fa-inbox"></i>
                {{ $search !== '' ? 'No requests match “' . $search . '”.' : 'Nothing here — every request in this view has been handled.' }}
            </div>
        @else
            <div class="rows" role="list">
                <div class="row head" aria-hidden="true"><span>#</span><span>Request</span><span>From</span><span>Last activity</span><span>Status</span></div>
                @foreach($tickets as $t)
                    <a role="listitem" class="row {{ $t->status === 'new' ? 'is-new' : '' }}" href="{{ route('help.tickets.show', $t) }}">
                        <span class="r-id">#{{ $t->id }}</span>
                        <span style="min-width:0">
                            <div class="r-subject">{{ $t->title() }}</div>
                            <div class="r-sub">{{ $t->categoryLabel() }}@if($t->isEscalated()) · Escalated by the Chair @endif @if($t->replies_count) · {{ $t->replies_count }} {{ Str::plural('reply', $t->replies_count) }}@endif · {{ Str::limit($t->message, 70) }}</div>
                        </span>
                        <span class="r-from">{{ $t->name }} <span class="tag">{{ $t->requesterRoleLabel() }}</span></span>
                        <span class="r-time">{{ ($t->last_activity_at ?? $t->created_at)->diffForHumans() }}</span>
                        <span><span class="pill pill-{{ $t->status }}">{{ $t->statusLabel() }}</span></span>
                    </a>
                @endforeach
            </div>
            @if($tickets->hasPages())
                <div class="pager">
                    @if($tickets->onFirstPage())<span>Previous</span>@else<a href="{{ $tickets->previousPageUrl() }}">Previous</a>@endif
                    <span>Page {{ $tickets->currentPage() }} of {{ $tickets->lastPage() }}</span>
                    @if($tickets->hasMorePages())<a href="{{ $tickets->nextPageUrl() }}">Next</a>@else<span>Next</span>@endif
                </div>
            @endif
        @endif
    </section>
@endsection
