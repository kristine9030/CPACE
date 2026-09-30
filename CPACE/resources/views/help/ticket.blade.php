@extends('help.layout', ['active' => $isChair ? 'support' : 'help'])

@section('title', 'Request #' . $report->id)
@section('heading', $report->title())
@section('subheading')
    Request #{{ $report->id }} · {{ $report->categoryLabel() }} · opened {{ $report->created_at->format('M j, Y g:i A') }}
@endsection

@section('actions')
    <a class="btn btn-ghost" href="{{ $isChair ? route('chair.support.index') : route('help.index') }}">
        <i class="fas fa-arrow-left"></i> {{ $isChair ? 'Support Inbox' : 'Help & Support' }}
    </a>
@endsection

@push('styles')
<style>
    .ticket-grid{display:grid;grid-template-columns:minmax(0,1fr) 300px;gap:22px;align-items:start}
    .ticket-grid > aside{margin:0} /* a shared partial gives every <aside> a top margin */
    .thread{display:flex;flex-direction:column;gap:14px}
    .msg{display:flex;gap:12px;align-items:flex-start}
    .msg.mine{flex-direction:row-reverse}
    .msg-avatar{flex-shrink:0;width:38px;height:38px;border-radius:50%;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;overflow:hidden}
    .msg-avatar.staff{background:#14283E}
    .msg-avatar img{width:100%;height:100%;object-fit:cover}
    .bubble{max-width:78%;background:#fff;border:1px solid var(--line);border-radius:4px 16px 16px 16px;padding:12px 16px}
    .msg.mine .bubble{background:#fbf3f2;border-color:#f0dcda;border-radius:16px 4px 16px 16px}
    .bubble-head{display:flex;gap:8px;align-items:baseline;flex-wrap:wrap;margin-bottom:4px}
    .bubble-head strong{font-size:13px;color:var(--ink)}
    .bubble-head span{font-size:11.5px;color:var(--muted)}
    .role-chip{font-size:10px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:#14283E;background:#e7edf5;padding:2px 7px;border-radius:5px}
    .bubble-body{font-size:14px;line-height:1.65;color:var(--text);white-space:pre-line;word-wrap:break-word}
    .reply-box{margin-top:18px}
    .reply-actions{display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-top:10px}
    .resolved-note{display:flex;gap:10px;align-items:center;padding:12px 14px;border-radius:10px;background:var(--ok-bg);color:var(--ok);font-size:13px;margin-bottom:14px}

    .meta-list{margin:0;padding:0;list-style:none}
    .meta-list li{display:flex;flex-direction:column;gap:2px;padding:10px 0;border-bottom:1px solid #f3ebea;font-size:13px;color:var(--ink);word-break:break-word}
    .meta-list li:last-child{border-bottom:none}
    .meta-list span{font-size:11px;font-weight:600;letter-spacing:.6px;text-transform:uppercase;color:var(--muted)}
    .status-form{display:flex;flex-direction:column;gap:10px;margin-top:6px}
    @media(max-width:1000px){.ticket-grid{grid-template-columns:1fr}}
    @media(max-width:600px){.bubble{max-width:100%}.msg-avatar{display:none}}
</style>
@endpush

@php
    $initials = function ($name) {
        $parts = preg_split('/\s+/', trim((string) $name));
        return strtoupper(substr($parts[0] ?? '', 0, 1) . substr(end($parts) ?: '', 0, 1));
    };
    $viewerId = Auth::id();
    $requesterIsMe = $report->user_id && (int) $report->user_id === (int) $viewerId;
@endphp

@section('content')
<div class="ticket-grid">
    <section class="card card-pad" aria-label="Conversation">
        <div class="thread">
            {{-- The original request is the first message in the thread. --}}
            <div class="msg {{ $requesterIsMe ? 'mine' : '' }}">
                <div class="msg-avatar">
                    @if($report->user?->profile_photo)<img src="{{ asset('storage/' . $report->user->profile_photo) }}" alt="">@else{{ $initials($report->name) }}@endif
                </div>
                <div class="bubble">
                    <div class="bubble-head">
                        <strong>{{ $requesterIsMe ? 'You' : $report->name }}</strong>
                        <span>{{ $report->created_at->diffForHumans() }}</span>
                    </div>
                    <div class="bubble-body">{{ $report->message }}</div>
                </div>
            </div>

            @foreach($report->replies as $reply)
                @php
                    $mine = (int) $reply->user_id === (int) $viewerId;
                    $isStaff = $reply->user?->isChair() ?? false;
                    $author = $reply->user?->name ?? 'Former user';
                @endphp
                <div class="msg {{ $mine ? 'mine' : '' }}" id="reply-{{ $reply->id }}">
                    <div class="msg-avatar {{ $isStaff ? 'staff' : '' }}">
                        @if($reply->user?->profile_photo)<img src="{{ asset('storage/' . $reply->user->profile_photo) }}" alt="">@elseif($isStaff)<i class="fas fa-headset"></i>@else{{ $initials($author) }}@endif
                    </div>
                    <div class="bubble">
                        <div class="bubble-head">
                            <strong>{{ $mine ? 'You' : $author }}</strong>
                            @if($isStaff)<span class="role-chip">CPAce Support</span>@endif
                            <span>{{ $reply->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="bubble-body">{{ $reply->body }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        <form class="reply-box" method="POST" action="{{ route('help.tickets.reply', $report) }}" data-loading="Sending reply...">
            @csrf
            @if($report->isResolved())
                <div class="resolved-note"><i class="fas fa-circle-check"></i>
                    This request is resolved. {{ $isChair ? 'Replying keeps it resolved unless you change the status.' : 'Replying will reopen it.' }}
                </div>
            @endif
            <label class="label" for="body">{{ $isChair && ! $requesterIsMe ? 'Reply to ' . $report->name : 'Add a reply' }}</label>
            <textarea class="field" id="body" name="body" maxlength="3000" required placeholder="Write your message...">{{ old('body') }}</textarea>
            @error('body')<div class="error-text">{{ $message }}</div>@enderror
            <div class="reply-actions">
                <span class="hint">
                    @if($isChair && ! $report->user_id)
                        This visitor wasn’t signed in — your reply is sent to {{ $report->email }} by email.
                    @elseif($isChair && ! $requesterIsMe)
                        {{ $report->name }} is notified in CPAce and by email.
                    @else
                        The CPAce support team is notified right away.
                    @endif
                </span>
                <button class="btn btn-primary" type="submit"><i class="fas fa-reply"></i> Send reply</button>
            </div>
        </form>
    </section>

    <aside class="card card-pad" aria-label="Request details">
        <h2 class="card-title"><i class="fas fa-circle-info"></i> Details</h2>
        <ul class="meta-list">
            <li><span>Status</span><div><span class="pill pill-{{ $report->status }}">{{ $report->statusLabel() }}</span></div></li>
            <li><span>Category</span>{{ $report->categoryLabel() }}</li>
            @if($isChair)
                <li><span>From</span>{{ $report->name }}<small style="color:var(--muted)">{{ $report->email }} · {{ $report->requesterRoleLabel() }}</small></li>
                @if($report->page_url)<li><span>Page</span>{{ $report->page_url }}</li>@endif
                @if($report->user_agent)<li><span>Browser</span><small>{{ $report->user_agent }}</small></li>@endif
            @endif
            <li><span>Last activity</span>{{ ($report->last_activity_at ?? $report->created_at)->format('M j, Y g:i A') }}</li>
            @if($report->resolved_at)<li><span>Resolved</span>{{ $report->resolved_at->format('M j, Y g:i A') }}</li>@endif
        </ul>

        @if($isChair)
            <form class="status-form" method="POST" action="{{ route('chair.support.status', $report) }}">
                @csrf @method('PATCH')
                <label class="label" for="status" style="margin-top:12px">Change status</label>
                <select class="field" id="status" name="status">
                    @foreach($statuses as $key => $label)
                        <option value="{{ $key }}" @selected($report->status === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                <button class="btn btn-ghost" type="submit"><i class="fas fa-check"></i> Update status</button>
                <span class="hint">Marking it Resolved notifies the requester.</span>
            </form>
        @endif
    </aside>
</div>
@endsection
