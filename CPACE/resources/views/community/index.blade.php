<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Alumni Community - CPACE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary:#7B1D1D; --primary-hover:#6a1818; --primary-light:#f5e8e8; --accent:#c0392b; --green:#10b981; --blue:#3b82f6; --fb-blue:#1877f2; --fb-blue-light:#e7f3ff; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Poppins',sans-serif; background:#f0f2f5; color:#333; }

        /* All three columns travel together as one centered group so the
           feed grows into whatever room is left — no dead gaps between them. */
        .fb-page { display:flex; gap:24px; align-items:flex-start; max-width:1400px; margin:0 auto; }
        .feed-wrap { flex:1; min-width:0; max-width:760px; padding-bottom:40px; }

        .topbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
        .page-title { font-size:24px; font-weight:700; color:#1a1a1a; }
        .page-sub { font-size:12px; color:#999; margin-top:2px; }
        .topbar-right { display:flex; align-items:center; gap:12px; }

        .flash { background:#e8f7ee; color:#1e7e46; border:1px solid #bfead0; padding:11px 16px; border-radius:10px; font-size:13px; font-weight:500; margin-bottom:16px; display:flex; align-items:center; gap:9px; }
        .flash.err { background:#fdeaea; color:#b23b3b; border-color:#f5c6c6; }

        .card { background:#fff; border-radius:10px; box-shadow:0 1px 2px rgba(0,0,0,.08); margin-bottom:16px; overflow:hidden; border:1px solid #e4e6eb; }

        /* ── Contacts rail (Facebook right sidebar) — a column in the same
           centered group as the feed, so it never leaves a dead gap ── */
        .fb-contacts-rail { width:260px; flex-shrink:0; position:sticky; top:16px; }
        .rail-title { font-size:15px; font-weight:700; color:#050505; padding:14px 16px 8px; }
        .contact-list { max-height:calc(100vh - 180px); overflow-y:auto; padding-bottom:8px; }
        .contact-item { display:flex; align-items:center; gap:10px; padding:8px 14px; cursor:pointer; transition:background .12s; border-radius:8px; margin:0 6px; }
        .contact-item:hover { background:#f2f2f2; }
        .contact-avatar { width:34px; height:34px; border-radius:50%; background:var(--primary); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:12px; flex-shrink:0; position:relative; }
        .contact-avatar::after { content:''; position:absolute; bottom:-1px; right:-1px; width:9px; height:9px; border-radius:50%; background:#31a24c; border:2px solid #fff; }
        .contact-name { font-size:13px; font-weight:600; color:#050505; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .contact-role { font-size:10.5px; color:#999; text-transform:capitalize; }
        .contact-empty { padding:10px 16px 16px; font-size:12px; color:#bbb; }

        /* ── Explore rail (left column) — fills the gap between the main nav
           and the feed with community-specific navigation. Sized up a bit
           wider/taller than a bare minimum so it reads as a real column
           rather than a leftover sliver. ── */
        .fb-explore-rail { width:280px; flex-shrink:0; position:sticky; top:16px; }
        .explore-link { display:flex; align-items:center; gap:14px; padding:13px 16px; margin:0 8px; border-radius:9px; color:#050505; text-decoration:none; font-size:14.5px; font-weight:600; transition:background .12s; }
        .explore-link:hover { background:#f2f2f2; }
        .explore-link.active { background:var(--fb-blue-light); color:var(--fb-blue); }
        .explore-link i { width:20px; text-align:center; color:#65676b; font-size:15px; }
        .explore-link.active i { color:var(--fb-blue); }
        .explore-divider { padding:18px 18px 8px; font-size:11.5px; font-weight:700; color:#999; text-transform:uppercase; letter-spacing:.6px; }
        .subject-list { max-height:calc(100vh - 320px); overflow-y:auto; padding-bottom:10px; }
        .subject-link { display:flex; align-items:center; gap:11px; padding:11px 16px; margin:0 8px; border-radius:9px; color:#333; text-decoration:none; font-size:13.5px; font-weight:600; transition:background .12s; }
        .subject-link:hover { background:#f2f2f2; }
        .subject-link.active { background:var(--fb-blue-light); color:var(--fb-blue); }
        .subject-link .subject-dot { width:9px; height:9px; border-radius:50%; background:#c9c9c9; flex-shrink:0; }
        .subject-link.active .subject-dot { background:var(--fb-blue); }
        .subject-link span { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }

        @media (max-width:1400px) { .fb-explore-rail, .fb-contacts-rail { display:none; } }

        /* ── Composer (collapsed FB-style trigger + full form) ── */
        .composer { padding:14px 16px; }
        .composer-trigger { display:flex; align-items:center; gap:10px; }
        .composer-avatar { width:40px; height:40px; border-radius:50%; background:var(--primary); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:14px; flex-shrink:0; }
        .composer-fake-input { flex:1; background:#f0f2f5; border-radius:22px; padding:10px 16px; font-size:14px; color:#65676b; cursor:pointer; transition:background .15s; }
        .composer-fake-input:hover { background:#e4e6eb; }
        .composer-full { display:none; margin-top:14px; }
        .composer-full.open { display:block; }
        .composer h4 { font-size:13px; font-weight:700; color:#555; margin-bottom:12px; display:flex; align-items:center; gap:7px; }
        .kind-toggle { display:flex; gap:8px; margin-bottom:12px; }
        .kind-opt { flex:1; }
        .kind-opt input { display:none; }
        .kind-opt label { display:block; text-align:center; padding:9px; border:1.5px solid #e2e2e2; border-radius:9px; font-size:12.5px; font-weight:600; color:#888; cursor:pointer; transition:all .18s; margin:0; }
        .kind-opt input:checked + label { border-color:var(--fb-blue); background:var(--fb-blue-light); color:var(--fb-blue); }
        .field { margin-bottom:12px; }
        .field label { display:block; font-size:11.5px; font-weight:600; color:#777; margin-bottom:5px; }
        .field textarea, .field input[type=text] {
            width:100%; font-family:'Poppins',sans-serif; font-size:13px; color:#333;
            border:1px solid #e2e2e2; border-radius:9px; padding:10px 12px; outline:none; transition:border-color .18s; background:#fff;
        }
        .field textarea:focus, .field input:focus { border-color:var(--fb-blue); }
        .field textarea { resize:vertical; min-height:70px; }
        .file-drop { border:1.5px dashed #d5d5d5; border-radius:10px; padding:14px; text-align:center; color:#aaa; font-size:12.5px; cursor:pointer; transition:border-color .18s; }
        .file-drop:hover { border-color:var(--fb-blue); }
        .file-drop i { font-size:18px; margin-right:6px; color:#c9a0a0; }
        .file-drop .fname { color:var(--fb-blue); font-weight:600; }
        .btn-primary { background:var(--fb-blue); color:#fff; border:none; padding:10px 20px; border-radius:9px; font-size:13px; font-weight:600; font-family:'Poppins',sans-serif; cursor:pointer; transition:background .18s; display:inline-flex; align-items:center; justify-content:center; gap:8px; }
        .btn-primary:hover { background:#166fe0; }
        .composer-foot { display:flex; justify-content:flex-end; margin-top:10px; }

        /* Post card */
        .post { padding:14px 16px; }
        .post-head { display:flex; align-items:center; gap:11px; }
        .post-avatar {
            width:42px; height:42px; border-radius:50%; background:var(--primary); color:#fff;
            display:flex; align-items:center; justify-content:center; font-weight:700; font-size:14px; flex-shrink:0;
        }
        .post-who { flex:1; min-width:0; }
        .post-name { font-size:14px; font-weight:700; color:#050505; display:flex; align-items:center; gap:6px; flex-wrap:wrap; }
        .role-badge { font-size:9px; font-weight:700; background:var(--primary-light); color:var(--primary); padding:2px 7px; border-radius:20px; text-transform:uppercase; letter-spacing:.4px; }
        .post-meta { font-size:12px; color:#65676b; margin-top:1px; }
        .pin-badge { font-size:10px; color:#d97706; font-weight:700; margin-left:6px; }

        /* Overflow "···" menu, Facebook style */
        .post-menu { margin-left:auto; position:relative; }
        .icon-btn { width:32px; height:32px; border-radius:50%; border:none; background:transparent; color:#65676b; cursor:pointer; font-size:14px; transition:all .15s; }
        .icon-btn:hover { background:#f2f2f2; }
        .menu-dropdown { display:none; position:absolute; right:0; top:36px; background:#fff; border-radius:10px; box-shadow:0 2px 14px rgba(0,0,0,.18); min-width:190px; z-index:50; overflow:hidden; }
        .menu-dropdown.open { display:block; }
        .menu-dropdown button, .menu-dropdown a { display:flex; align-items:center; gap:10px; width:100%; text-align:left; border:none; background:none; padding:11px 14px; font-size:13px; font-family:'Poppins',sans-serif; color:#050505; cursor:pointer; text-decoration:none; }
        .menu-dropdown button:hover, .menu-dropdown a:hover { background:#f2f2f2; }
        .menu-dropdown .danger { color:var(--accent); }
        .menu-dropdown i { width:16px; color:#65676b; }
        .menu-dropdown .danger i { color:var(--accent); }

        .post-body { font-size:15px; color:#050505; line-height:1.55; margin-top:10px; white-space:pre-line; }
        .post-body.quote { font-style:italic; font-size:16px; color:#5a1515; border-left:4px solid var(--primary); padding-left:14px; position:relative; }
        .post-body.quote i.fa-quote-left { color:var(--primary-light); font-size:22px; display:block; margin-bottom:4px; }
        .quote-attr { display:block; margin-top:8px; font-size:12px; font-weight:600; color:var(--primary); font-style:normal; }

        .attachment { display:flex; align-items:center; gap:12px; padding:12px 14px; background:#f7f8fa; border:1px solid #e4e6eb; border-radius:11px; margin-top:12px; text-decoration:none; color:inherit; transition:background .15s; }
        .attachment:hover { background:#eef0f2; }
        .att-icon { width:38px; height:38px; border-radius:9px; display:flex; align-items:center; justify-content:center; font-size:16px; color:#fff; flex-shrink:0; }
        .att-info { flex:1; min-width:0; }
        .att-title { font-size:12.5px; font-weight:600; color:#222; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .att-meta { font-size:11px; color:#aaa; margin-top:2px; }

        .post-stats { display:flex; justify-content:space-between; align-items:center; margin-top:12px; padding-top:6px; font-size:12px; color:#65676b; }
        .stat-likes { display:flex; align-items:center; gap:5px; }
        .stat-likes .dot { width:16px; height:16px; border-radius:50%; background:var(--fb-blue); color:#fff; display:flex; align-items:center; justify-content:center; font-size:8px; }
        .post-actions { display:flex; gap:2px; margin-top:6px; border-top:1px solid #e4e6eb; padding-top:4px; }
        .act-btn { flex:1; display:flex; align-items:center; justify-content:center; gap:7px; border:none; background:transparent; color:#65676b; font-size:13px; font-weight:600; font-family:'Poppins',sans-serif; padding:9px 12px; border-radius:8px; cursor:pointer; transition:all .15s; }
        .act-btn:hover { background:#f2f2f2; }
        .act-btn.liked { color:var(--fb-blue); }

        .comments { margin-top:8px; border-top:1px solid #e4e6eb; padding-top:12px; }
        .comment { display:flex; gap:9px; margin-bottom:10px; position:relative; }
        .comment-avatar { width:28px; height:28px; border-radius:50%; background:#e5cccc; color:var(--primary); display:flex; align-items:center; justify-content:center; font-weight:700; font-size:10.5px; flex-shrink:0; }
        .comment-bubble { background:#f0f2f5; border-radius:14px; padding:8px 12px; flex:1; }
        .comment-name { font-size:12.5px; font-weight:700; color:#050505; margin-right:6px; }
        .comment-text { font-size:13px; color:#050505; display:inline; }
        .comment-del { border:none; background:none; color:#ccc; font-size:11px; cursor:pointer; padding:2px 6px; flex-shrink:0; align-self:center; }
        .comment-del:hover { color:var(--accent); }
        .comment-form { display:flex; gap:9px; margin-top:8px; align-items:center; }
        .comment-form input { flex:1; border:none; background:#f0f2f5; border-radius:20px; padding:9px 14px; font-size:12.5px; font-family:'Poppins',sans-serif; outline:none; }
        .comment-form input:focus { background:#e4e6eb; }
        .comment-form button { border:none; background:transparent; color:var(--fb-blue); width:34px; height:34px; border-radius:50%; cursor:pointer; flex-shrink:0; font-size:14px; }
        .comment-form button:hover { background:#f2f2f2; }

        .empty { text-align:center; padding:50px 20px; color:#bbb; }
        .empty i { font-size:34px; color:#e0d0d0; display:block; margin-bottom:12px; }

        .pagination { display:flex; justify-content:center; gap:6px; margin-top:10px; }
        .pagination a, .pagination span { padding:7px 12px; border-radius:8px; font-size:12px; text-decoration:none; color:#666; background:#fff; border:1px solid #eee; }
        .pagination .active span { background:var(--fb-blue); color:#fff; border-color:var(--fb-blue); }

        @media (max-width:768px) { .main { padding:16px 12px 90px; } }
    </style>
</head>
<body>

@if(Auth::user()->isAlumni())
    @include('partials.alumni-sidebar', ['active' => 'community'])
@elseif(Auth::user()->isChair())
    @include('partials.chair-sidebar', ['active' => 'community'])
@elseif(Auth::user()->isFaculty())
    @include('partials.faculty-sidebar', ['active' => ''])
@else
    @include('partials.sidebar', ['active' => 'community'])
@endif

{{-- .main is used by the faculty/chair/alumni sidebars, .main-content by the student sidebar --}}
<main class="main main-content">
    <div class="fb-page">

    {{-- Explore rail — fills the gap between the main nav and the feed --}}
    <aside class="fb-explore-rail" id="exploreRail">
        <div class="card" style="margin-bottom:0;padding:10px 0;">
            <a href="{{ route('community.index') }}" class="explore-link {{ !$activeSubjectId ? 'active' : '' }}">
                <i class="fas fa-house"></i> All Posts
            </a>
            <a href="{{ route('community.resources.index') }}" class="explore-link">
                <i class="fas fa-book"></i> Resource Library
            </a>

            @if($subjects->isNotEmpty())
                <div class="explore-divider">Browse by Subject</div>
                <div class="subject-list">
                    @foreach($subjects as $s)
                        <a href="{{ route('community.index', ['subject_id' => $s->id]) }}" class="subject-link {{ $activeSubjectId === $s->id ? 'active' : '' }}">
                            <span class="subject-dot"></span>
                            <span>{{ $s->code }} — {{ $s->name }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </aside>

    <div class="feed-wrap">
        <div class="topbar">
            <div>
                <div class="page-title"><i class="fas fa-people-group" style="color:var(--primary);"></i> Alumni Community</div>
                <div class="page-sub">Motivation, updates, and study materials shared by our alumni.</div>
            </div>
        </div>

        @if($activeSubjectId)
            @php $activeSubject = $subjects->firstWhere('id', $activeSubjectId); @endphp
            <div class="flash" style="background:var(--fb-blue-light);color:var(--fb-blue);border-color:#cfe4fc;justify-content:space-between;">
                <span><i class="fas fa-filter"></i> Showing posts about <strong>{{ $activeSubject->code ?? 'this subject' }}</strong></span>
                <a href="{{ route('community.index') }}" style="color:var(--fb-blue);font-weight:600;text-decoration:none;">Clear <i class="fas fa-times"></i></a>
            </div>
        @endif

        @if(session('status'))
            <div class="flash"><i class="fas fa-circle-check"></i> {{ session('status') }}</div>
        @endif
        @if($errors->any())
            <div class="flash err"><i class="fas fa-circle-exclamation"></i> {{ $errors->first() }}</div>
        @endif

        @if(Auth::user()->isAlumni() || Auth::user()->isChair())
            @php $meInitials = strtoupper(substr(Auth::user()->first_name,0,1).substr(Auth::user()->last_name,0,1)); @endphp
            <div class="card composer">
                <div class="composer-trigger" id="composerTrigger">
                    <div class="composer-avatar">{{ $meInitials }}</div>
                    <div class="composer-fake-input">What's on your mind, {{ Auth::user()->first_name }}?</div>
                </div>

                <div class="composer-full" id="composerFull">
                    <h4><i class="fas fa-pen"></i> Share something with the community</h4>
                    <form method="POST" action="{{ route('community.posts.store') }}" enctype="multipart/form-data" id="composerForm">
                        @csrf
                        <div class="kind-toggle">
                            <div class="kind-opt">
                                <input type="radio" name="post_type" id="typePost" value="discussion" checked>
                                <label for="typePost"><i class="fas fa-comment-dots"></i> Post / Update</label>
                            </div>
                            <div class="kind-opt">
                                <input type="radio" name="post_type" id="typeQuote" value="tip">
                                <label for="typeQuote"><i class="fas fa-quote-right"></i> Motivational Quote</label>
                            </div>
                        </div>

                        <div class="field">
                            <textarea name="body" placeholder="What do you want to share with the reviewers?" required>{{ old('body') }}</textarea>
                        </div>

                        <div class="field" id="quoteAuthorField" style="display:none;">
                            <label>Attributed to <span style="color:#bbb;font-weight:400;">(optional — leave blank if it's your own words)</span></label>
                            <input type="text" name="title" value="{{ old('title') }}" placeholder="e.g. Warren Buffett">
                        </div>

                        <div class="field">
                            <label>Related subject <span style="color:#bbb;font-weight:400;">(optional)</span></label>
                            <select name="subject_id" style="width:100%; font-family:'Poppins',sans-serif; font-size:13px; color:#333; border:1px solid #e2e2e2; border-radius:9px; padding:10px 12px; outline:none; background:#fff;">
                                <option value="">— None —</option>
                                @foreach($subjects as $s)
                                    <option value="{{ $s->id }}" {{ (string) old('subject_id') === (string) $s->id ? 'selected' : '' }}>{{ $s->code }} — {{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="field">
                            <label>Attach a file <span style="color:#bbb;font-weight:400;">(PDF, Word, Excel, notes, computations — max 20MB, optional)</span></label>
                            <label class="file-drop" for="fileInput" id="fileDrop">
                                <i class="fas fa-paperclip"></i><span id="fileLabel">Click to attach a material</span>
                            </label>
                            <input type="file" name="file" id="fileInput" hidden
                                   accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.csv,.txt,.rtf,.odt,.jpg,.jpeg,.png,.gif,.webp,.zip,.rar">
                        </div>

                        @if($myResources->isNotEmpty())
                            <div class="field">
                                <label>...or link an existing material <span style="color:#bbb;font-weight:400;">(from your Resource Library uploads, optional)</span></label>
                                <select name="resource_id" id="resourceSelect" style="width:100%; font-family:'Poppins',sans-serif; font-size:13px; color:#333; border:1px solid #e2e2e2; border-radius:9px; padding:10px 12px; outline:none; background:#fff;">
                                    <option value="">— None —</option>
                                    @foreach($myResources as $r)
                                        <option value="{{ $r->id }}" {{ (string) old('resource_id') === (string) $r->id ? 'selected' : '' }}>{{ $r->title }} ({{ $r->original_name }})</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="composer-foot">
                            <button type="submit" class="btn-primary"><i class="fas fa-paper-plane"></i> Share</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        @forelse($posts as $post)
            @php
                $author = $post->author;
                $initials = $author ? strtoupper(substr($author->first_name,0,1).substr($author->last_name,0,1)) : '?';
                $isQuote = $post->post_type === 'tip';
                $canDelete = Auth::user()->isChair() || $post->author_id === Auth::id();
                $liked = $post->isLikedBy(Auth::user());
                $likeCount = $post->likes->count();
                $commentCount = $post->replies->count();
            @endphp
            <div class="card post">
                <div class="post-head">
                    <div class="post-avatar">{{ $initials }}</div>
                    <div class="post-who">
                        <div class="post-name">
                            {{ $author->name ?? 'Former Member' }}
                            <span class="role-badge">Alumni</span>
                            @if($post->is_pinned)<span class="pin-badge"><i class="fas fa-thumbtack"></i> Pinned</span>@endif
                        </div>
                        <div class="post-meta">
                            @if($author?->alumniProfile?->batch_year) Batch {{ $author->alumniProfile->batch_year }} · @endif
                            @if($author?->alumniProfile?->current_job){{ $author->alumniProfile->current_job }}{{ $author->alumniProfile->company ? ' at '.$author->alumniProfile->company : '' }} · @endif
                            @if($post->subject) <span class="role-badge" style="background:#eef2fb;color:var(--blue);">{{ $post->subject->code }}</span> · @endif
                            {{ $post->created_at?->diffForHumans() }} · <i class="fas fa-earth-americas"></i>
                        </div>
                    </div>
                    <div class="post-menu">
                        <button type="button" class="icon-btn" onclick="toggleMenu({{ $post->id }})"><i class="fas fa-ellipsis"></i></button>
                        <div class="menu-dropdown" id="menu-{{ $post->id }}">
                            @if($author && $author->id !== Auth::id())
                                <button type="button" onclick="openFbChat({{ $author->id }}, '{{ addslashes($author->name ?? 'User') }}')"><i class="fas fa-comment-dots"></i> Message {{ $author->first_name }}</button>
                            @endif
                            @if($canDelete)
                                <form method="POST" action="{{ route('community.posts.destroy', $post->id) }}" onsubmit="return confirm('Delete this post?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="danger"><i class="fas fa-trash"></i> Delete post</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="post-body {{ $isQuote ? 'quote' : '' }}">
                    @if($isQuote)<i class="fas fa-quote-left"></i>@endif
                    {{ $post->body }}
                    @if($isQuote && $post->title)<span class="quote-attr">— {{ $post->title }}</span>@endif
                </div>

                @foreach($post->attachments as $att)
                    @php $meta = $att->iconMeta(); @endphp
                    <a class="attachment" href="{{ route('community.attachments.download', $att->id) }}">
                        <div class="att-icon" style="background:{{ $meta['color'] }};"><i class="fas {{ $meta['icon'] }}"></i></div>
                        <div class="att-info">
                            <div class="att-title">{{ $att->original_name }}</div>
                            <div class="att-meta">{{ strtoupper($att->file_category) }} · {{ $att->humanSize() }}</div>
                        </div>
                        <i class="fas fa-download" style="color:#bbb;"></i>
                    </a>
                @endforeach

                @if($post->resource)
                    @php $rmeta = $post->resource->iconMeta(); @endphp
                    <a class="attachment" href="{{ route('community.resources.download', $post->resource->id) }}" style="border-color:var(--primary-light);">
                        <div class="att-icon" style="background:{{ $rmeta['color'] }};"><i class="fas {{ $rmeta['icon'] }}"></i></div>
                        <div class="att-info">
                            <div class="att-title">{{ $post->resource->title }} <span class="role-badge" style="margin-left:4px;"><i class="fas fa-book" style="margin-right:3px;"></i>Library</span></div>
                            <div class="att-meta">{{ strtoupper($post->resource->file_category) }} · {{ $post->resource->humanSize() }} · {{ $post->resource->downloads_count }} downloads</div>
                        </div>
                        <i class="fas fa-download" style="color:#bbb;"></i>
                    </a>
                @endif

                @if($likeCount || $commentCount)
                    <div class="post-stats">
                        <div class="stat-likes">
                            @if($likeCount)<span class="dot"><i class="fas fa-thumbs-up"></i></span> {{ $likeCount }}@endif
                        </div>
                        <div>@if($commentCount){{ $commentCount }} comment{{ $commentCount === 1 ? '' : 's' }}@endif</div>
                    </div>
                @endif

                <div class="post-actions">
                    <form method="POST" action="{{ route('community.posts.like', $post->id) }}" style="flex:1;">
                        @csrf
                        <button type="submit" class="act-btn {{ $liked ? 'liked' : '' }}" style="width:100%;">
                            <i class="fa{{ $liked ? 's' : 'r' }} fa-thumbs-up"></i> {{ $liked ? 'Liked' : 'Like' }}
                        </button>
                    </form>
                    <button type="button" class="act-btn" onclick="document.getElementById('comments-{{ $post->id }}').classList.toggle('open-hidden')">
                        <i class="far fa-comment"></i> Comment
                    </button>
                </div>

                <div class="comments" id="comments-{{ $post->id }}">
                    @foreach($post->replies as $comment)
                        @php $cinit = $comment->author ? strtoupper(substr($comment->author->first_name,0,1).substr($comment->author->last_name,0,1)) : '?'; @endphp
                        <div class="comment">
                            <div class="comment-avatar">{{ $cinit }}</div>
                            <div class="comment-bubble">
                                <span class="comment-name">{{ $comment->author->name ?? 'User' }}</span><span class="comment-text">{{ $comment->body }}</span>
                            </div>
                            @if($comment->author_id === Auth::id())
                                <form method="POST" action="{{ route('community.comments.destroy', $comment->id) }}" onsubmit="return confirm('Delete this comment?');">
                                    @csrf @method('DELETE')
                                    <button class="comment-del" title="Delete"><i class="fas fa-times"></i></button>
                                </form>
                            @endif
                        </div>
                    @endforeach

                    <form method="POST" action="{{ route('community.comments.store', $post->id) }}" class="comment-form">
                        @csrf
                        <input type="text" name="body" placeholder="Write a comment..." maxlength="1000" required>
                        <button type="submit"><i class="fas fa-paper-plane"></i></button>
                    </form>
                </div>
            </div>
        @empty
            <div class="card">
                <div class="empty">
                    <i class="fas fa-people-group"></i>
                    No posts yet. @if(Auth::user()->isAlumni()) Be the first to share something! @else Check back soon — alumni will start posting updates and materials here. @endif
                </div>
            </div>
        @endforelse

        @if($posts->hasPages())
            <div class="pagination">{{ $posts->links() }}</div>
        @endif
    </div>

    <aside class="fb-contacts-rail" id="contactsRail">
        <div class="card" style="margin-bottom:0;">
            <div class="rail-title">Contacts</div>
            <div class="contact-list">
                @forelse($contacts as $c)
                    <div class="contact-item" onclick="openFbChat({{ $c->id }}, '{{ addslashes($c->name) }}')">
                        <div class="contact-avatar">{{ strtoupper(substr($c->first_name,0,1)) }}</div>
                        <div style="min-width:0;">
                            <div class="contact-name">{{ $c->name }}</div>
                            <div class="contact-role">{{ $c->roleName() }}</div>
                        </div>
                    </div>
                @empty
                    <div class="contact-empty">No one else here yet.</div>
                @endforelse
            </div>
        </div>
    </aside>

    </div>
</main>

@include('partials.chat-widget', ['contacts' => $contacts])

<script>
(function () {
    const postRadio = document.getElementById('typePost');
    const quoteRadio = document.getElementById('typeQuote');
    const quoteField = document.getElementById('quoteAuthorField');
    function sync() {
        quoteField.style.display = quoteRadio.checked ? '' : 'none';
    }
    if (postRadio) postRadio.addEventListener('change', sync);
    if (quoteRadio) quoteRadio.addEventListener('change', sync);
    sync();

    const fileInput = document.getElementById('fileInput');
    const fileLabel = document.getElementById('fileLabel');
    if (fileInput) {
        fileInput.addEventListener('change', function () {
            fileLabel.innerHTML = this.files.length
                ? '<span class="fname">' + this.files[0].name + '</span>'
                : 'Click to attach a material';
            // A post carries either a fresh attachment or a linked library
            // material, never both — picking one clears the other.
            if (this.files.length && resourceSelect) resourceSelect.value = '';
        });
    }

    const resourceSelect = document.getElementById('resourceSelect');
    if (resourceSelect) {
        resourceSelect.addEventListener('change', function () {
            if (this.value && fileInput) {
                fileInput.value = '';
                fileLabel.textContent = 'Click to attach a material';
            }
        });
    }

    const trigger = document.getElementById('composerTrigger');
    const full = document.getElementById('composerFull');
    if (trigger && full) {
        trigger.addEventListener('click', function () {
            full.classList.add('open');
            trigger.style.display = 'none';
            const ta = full.querySelector('textarea');
            if (ta) ta.focus();
        });
    }
})();

function toggleMenu(id) {
    const el = document.getElementById('menu-' + id);
    document.querySelectorAll('.menu-dropdown.open').forEach(function (m) { if (m !== el) m.classList.remove('open'); });
    el.classList.toggle('open');
}
document.addEventListener('click', function (e) {
    if (!e.target.closest('.post-menu')) {
        document.querySelectorAll('.menu-dropdown.open').forEach(function (m) { m.classList.remove('open'); });
    }
});
</script>
<style>.open-hidden{display:none;}</style>
<script>
    // Comments are shown by default; clicking "Comment" toggles them away/back.
    document.querySelectorAll('.comments').forEach(function (el) { el.classList.remove('open-hidden'); });
</script>
</body>
</html>
