<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumni Community - CPACE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary:#7B1D1D; --primary-hover:#6a1818; --primary-light:#f5e8e8; --accent:#c0392b; --green:#10b981; --blue:#3b82f6; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Poppins',sans-serif; background:#f4f5f7; color:#333; }

        .feed-wrap { max-width:640px; margin:0 auto; padding-bottom:40px; }

        .topbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
        .page-title { font-size:24px; font-weight:700; color:#1a1a1a; }
        .page-sub { font-size:12px; color:#999; margin-top:2px; }
        .topbar-right { display:flex; align-items:center; gap:12px; }

        .flash { background:#e8f7ee; color:#1e7e46; border:1px solid #bfead0; padding:11px 16px; border-radius:10px; font-size:13px; font-weight:500; margin-bottom:16px; display:flex; align-items:center; gap:9px; }
        .flash.err { background:#fdeaea; color:#b23b3b; border-color:#f5c6c6; }

        .card { background:#fff; border-radius:16px; box-shadow:0 2px 10px rgba(0,0,0,.04); margin-bottom:18px; overflow:hidden; }

        /* Composer */
        .composer { padding:18px 20px; }
        .composer h4 { font-size:13px; font-weight:700; color:#555; margin-bottom:12px; display:flex; align-items:center; gap:7px; }
        .kind-toggle { display:flex; gap:8px; margin-bottom:12px; }
        .kind-opt { flex:1; }
        .kind-opt input { display:none; }
        .kind-opt label { display:block; text-align:center; padding:9px; border:1.5px solid #e2e2e2; border-radius:9px; font-size:12.5px; font-weight:600; color:#888; cursor:pointer; transition:all .18s; margin:0; }
        .kind-opt input:checked + label { border-color:var(--primary); background:var(--primary-light); color:var(--primary); }
        .field { margin-bottom:12px; }
        .field label { display:block; font-size:11.5px; font-weight:600; color:#777; margin-bottom:5px; }
        .field textarea, .field input[type=text] {
            width:100%; font-family:'Poppins',sans-serif; font-size:13px; color:#333;
            border:1px solid #e2e2e2; border-radius:9px; padding:10px 12px; outline:none; transition:border-color .18s; background:#fff;
        }
        .field textarea:focus, .field input:focus { border-color:var(--primary); }
        .field textarea { resize:vertical; min-height:70px; }
        .file-drop { border:1.5px dashed #d5d5d5; border-radius:10px; padding:14px; text-align:center; color:#aaa; font-size:12.5px; cursor:pointer; transition:border-color .18s; }
        .file-drop:hover { border-color:var(--primary); }
        .file-drop i { font-size:18px; margin-right:6px; color:#c9a0a0; }
        .file-drop .fname { color:var(--primary); font-weight:600; }
        .btn-primary { background:var(--primary); color:#fff; border:none; padding:10px 20px; border-radius:9px; font-size:13px; font-weight:600; font-family:'Poppins',sans-serif; cursor:pointer; transition:background .18s; display:inline-flex; align-items:center; justify-content:center; gap:8px; }
        .btn-primary:hover { background:var(--primary-hover); }
        .composer-foot { display:flex; justify-content:flex-end; margin-top:10px; }

        /* Post card */
        .post { padding:18px 20px; }
        .post-head { display:flex; align-items:center; gap:11px; }
        .post-avatar {
            width:42px; height:42px; border-radius:50%; background:var(--primary); color:#fff;
            display:flex; align-items:center; justify-content:center; font-weight:700; font-size:14px; flex-shrink:0;
        }
        .post-who { flex:1; min-width:0; }
        .post-name { font-size:13.5px; font-weight:700; color:#1a1a1a; display:flex; align-items:center; gap:6px; flex-wrap:wrap; }
        .role-badge { font-size:9px; font-weight:700; background:var(--primary-light); color:var(--primary); padding:2px 7px; border-radius:20px; text-transform:uppercase; letter-spacing:.4px; }
        .post-meta { font-size:11px; color:#aaa; margin-top:1px; }
        .pin-badge { font-size:10px; color:#d97706; font-weight:700; margin-left:6px; }
        .post-del { margin-left:auto; }
        .icon-btn { width:30px; height:30px; border-radius:8px; border:none; background:transparent; color:#bbb; cursor:pointer; font-size:13px; transition:all .15s; }
        .icon-btn:hover { background:#fdeaea; color:var(--accent); }

        .post-body { font-size:14px; color:#2a2a2a; line-height:1.6; margin-top:12px; white-space:pre-line; }
        .post-body.quote { font-style:italic; font-size:16px; color:#5a1515; border-left:4px solid var(--primary); padding-left:14px; position:relative; }
        .post-body.quote i.fa-quote-left { color:var(--primary-light); font-size:22px; display:block; margin-bottom:4px; }
        .quote-attr { display:block; margin-top:8px; font-size:12px; font-weight:600; color:var(--primary); font-style:normal; }

        .attachment { display:flex; align-items:center; gap:12px; padding:12px 14px; background:#faf9f9; border:1px solid #f0eaea; border-radius:11px; margin-top:12px; text-decoration:none; color:inherit; transition:background .15s; }
        .attachment:hover { background:#f5eeee; }
        .att-icon { width:38px; height:38px; border-radius:9px; display:flex; align-items:center; justify-content:center; font-size:16px; color:#fff; flex-shrink:0; }
        .att-info { flex:1; min-width:0; }
        .att-title { font-size:12.5px; font-weight:600; color:#222; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .att-meta { font-size:11px; color:#aaa; margin-top:2px; }

        .post-actions { display:flex; gap:6px; margin-top:14px; border-top:1px solid #f2f2f2; padding-top:10px; }
        .act-btn { display:flex; align-items:center; gap:7px; border:none; background:transparent; color:#888; font-size:12.5px; font-weight:600; font-family:'Poppins',sans-serif; padding:7px 12px; border-radius:8px; cursor:pointer; transition:all .15s; }
        .act-btn:hover { background:#f6f6f6; }
        .act-btn.liked { color:var(--accent); }
        .act-btn.liked i { font-weight:900; }

        .comments { margin-top:12px; border-top:1px solid #f2f2f2; padding-top:12px; }
        .comment { display:flex; gap:9px; margin-bottom:10px; }
        .comment-avatar { width:28px; height:28px; border-radius:50%; background:#e5cccc; color:var(--primary); display:flex; align-items:center; justify-content:center; font-weight:700; font-size:10.5px; flex-shrink:0; }
        .comment-bubble { background:#f4f5f7; border-radius:12px; padding:8px 12px; flex:1; }
        .comment-name { font-size:11.5px; font-weight:700; color:#1a1a1a; }
        .comment-text { font-size:12.5px; color:#444; margin-top:1px; }
        .comment-del { border:none; background:none; color:#ccc; font-size:11px; cursor:pointer; padding:2px 6px; flex-shrink:0; align-self:flex-start; }
        .comment-del:hover { color:var(--accent); }
        .comment-form { display:flex; gap:9px; margin-top:8px; }
        .comment-form input { flex:1; border:1px solid #e2e2e2; border-radius:20px; padding:8px 14px; font-size:12.5px; font-family:'Poppins',sans-serif; outline:none; }
        .comment-form input:focus { border-color:var(--primary); }
        .comment-form button { border:none; background:var(--primary); color:#fff; width:34px; height:34px; border-radius:50%; cursor:pointer; flex-shrink:0; }
        .comment-form button:hover { background:var(--primary-hover); }

        .empty { text-align:center; padding:50px 20px; color:#bbb; }
        .empty i { font-size:34px; color:#e0d0d0; display:block; margin-bottom:12px; }

        .pagination { display:flex; justify-content:center; gap:6px; margin-top:10px; }
        .pagination a, .pagination span { padding:7px 12px; border-radius:8px; font-size:12px; text-decoration:none; color:#666; background:#fff; border:1px solid #eee; }
        .pagination .active span { background:var(--primary); color:#fff; border-color:var(--primary); }

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
    <div class="feed-wrap">
        <div class="topbar">
            <div>
                <div class="page-title"><i class="fas fa-people-group" style="color:var(--primary);"></i> Alumni Community</div>
                <div class="page-sub">Motivation, updates, and study materials shared by our alumni.</div>
            </div>
        </div>

        @if(session('status'))
            <div class="flash"><i class="fas fa-circle-check"></i> {{ session('status') }}</div>
        @endif
        @if($errors->any())
            <div class="flash err"><i class="fas fa-circle-exclamation"></i> {{ $errors->first() }}</div>
        @endif

        @if(Auth::user()->isAlumni() || Auth::user()->isChair())
            <div class="card composer">
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
        @endif

        @forelse($posts as $post)
            @php
                $author = $post->author;
                $initials = $author ? strtoupper(substr($author->first_name,0,1).substr($author->last_name,0,1)) : '?';
                $isQuote = $post->post_type === 'tip';
                $canDelete = Auth::user()->isChair() || $post->author_id === Auth::id();
                $liked = $post->isLikedBy(Auth::user());
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
                            {{ $post->created_at?->diffForHumans() }}
                        </div>
                    </div>
                    <div class="post-del" style="display:flex; gap:4px;">
                        @if($author && $author->id !== Auth::id())
                            <form method="POST" action="{{ route('messages.start') }}">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $author->id }}">
                                <button class="icon-btn" title="Message {{ $author->first_name }}"><i class="fas fa-comment-dots"></i></button>
                            </form>
                        @endif
                        @if($canDelete)
                            <form method="POST" action="{{ route('community.posts.destroy', $post->id) }}" onsubmit="return confirm('Delete this post?');">
                                @csrf @method('DELETE')
                                <button class="icon-btn" title="Delete post"><i class="fas fa-trash"></i></button>
                            </form>
                        @endif
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

                <div class="post-actions">
                    <form method="POST" action="{{ route('community.posts.like', $post->id) }}">
                        @csrf
                        <button type="submit" class="act-btn {{ $liked ? 'liked' : '' }}">
                            <i class="fa{{ $liked ? 's' : 'r' }} fa-heart"></i> {{ $post->likes->count() ?: '' }} {{ $liked ? 'Liked' : 'Like' }}
                        </button>
                    </form>
                    <button type="button" class="act-btn" onclick="document.getElementById('comments-{{ $post->id }}').classList.toggle('open-hidden')">
                        <i class="far fa-comment"></i> {{ $post->replies->count() ?: '' }} Comment{{ $post->replies->count() === 1 ? '' : 's' }}
                    </button>
                </div>

                <div class="comments" id="comments-{{ $post->id }}">
                    @foreach($post->replies as $comment)
                        @php $cinit = $comment->author ? strtoupper(substr($comment->author->first_name,0,1).substr($comment->author->last_name,0,1)) : '?'; @endphp
                        <div class="comment">
                            <div class="comment-avatar">{{ $cinit }}</div>
                            <div class="comment-bubble">
                                <div class="comment-name">{{ $comment->author->name ?? 'User' }}</div>
                                <div class="comment-text">{{ $comment->body }}</div>
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
</main>

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
})();
</script>
<style>.open-hidden{display:none;}</style>
<script>
    // Comments are shown by default; clicking "Comment" toggles them away/back.
    document.querySelectorAll('.comments').forEach(function (el) { el.classList.remove('open-hidden'); });
</script>
</body>
</html>
