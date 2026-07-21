<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resource Library - CPACE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary:#7B1D1D; --primary-hover:#6a1818; --primary-light:#f5e8e8; --accent:#c0392b; --green:#10b981; --blue:#3b82f6; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Poppins',sans-serif; background:#f4f5f7; color:#333; }

        .lib-wrap { max-width:960px; margin:0 auto; padding-bottom:40px; }

        .topbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
        .page-title { font-size:24px; font-weight:700; color:#1a1a1a; }
        .page-sub { font-size:12px; color:#999; margin-top:2px; }

        .flash { background:#e8f7ee; color:#1e7e46; border:1px solid #bfead0; padding:11px 16px; border-radius:10px; font-size:13px; font-weight:500; margin-bottom:16px; display:flex; align-items:center; gap:9px; }
        .flash.err { background:#fdeaea; color:#b23b3b; border-color:#f5c6c6; }

        .card { background:#fff; border-radius:16px; box-shadow:0 2px 10px rgba(0,0,0,.04); margin-bottom:18px; overflow:hidden; }

        /* Upload composer */
        .composer { padding:18px 20px; }
        .composer h4 { font-size:13px; font-weight:700; color:#555; margin-bottom:12px; display:flex; align-items:center; gap:7px; }
        .field { margin-bottom:12px; }
        .field label { display:block; font-size:11.5px; font-weight:600; color:#777; margin-bottom:5px; }
        .field textarea, .field input[type=text], .field select {
            width:100%; font-family:'Poppins',sans-serif; font-size:13px; color:#333;
            border:1px solid #e2e2e2; border-radius:9px; padding:10px 12px; outline:none; transition:border-color .18s; background:#fff;
        }
        .field textarea:focus, .field input:focus, .field select:focus { border-color:var(--primary); }
        .field textarea { resize:vertical; min-height:60px; }
        .field-row { display:flex; gap:12px; }
        .field-row .field { flex:1; }
        .file-drop { border:1.5px dashed #d5d5d5; border-radius:10px; padding:14px; text-align:center; color:#aaa; font-size:12.5px; cursor:pointer; transition:border-color .18s; }
        .file-drop:hover { border-color:var(--primary); }
        .file-drop i { font-size:18px; margin-right:6px; color:#c9a0a0; }
        .file-drop .fname { color:var(--primary); font-weight:600; }
        .btn-primary { background:var(--primary); color:#fff; border:none; padding:10px 20px; border-radius:9px; font-size:13px; font-weight:600; font-family:'Poppins',sans-serif; cursor:pointer; transition:background .18s; display:inline-flex; align-items:center; justify-content:center; gap:8px; }
        .btn-primary:hover { background:var(--primary-hover); }
        .composer-foot { display:flex; justify-content:flex-end; margin-top:10px; }

        /* Filter bar */
        .filter-bar { padding:14px 18px; display:flex; flex-wrap:wrap; gap:10px; align-items:center; }
        .filter-bar select, .filter-bar input[type=text] {
            font-family:'Poppins',sans-serif; font-size:12.5px; color:#333; border:1px solid #e2e2e2;
            border-radius:9px; padding:9px 12px; outline:none; background:#fff; transition:border-color .18s;
        }
        .filter-bar select:focus, .filter-bar input:focus { border-color:var(--primary); }
        .filter-bar .search-field { flex:1; min-width:180px; position:relative; }
        .filter-bar .search-field i { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#bbb; font-size:12px; }
        .filter-bar .search-field input { width:100%; padding-left:32px; }
        .filter-bar button { background:var(--primary); color:#fff; border:none; padding:9px 16px; border-radius:9px; font-size:12.5px; font-weight:600; font-family:'Poppins',sans-serif; cursor:pointer; }
        .filter-bar button:hover { background:var(--primary-hover); }

        /* Resource grid */
        .res-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(270px, 1fr)); gap:14px; }
        .res-card { background:#fff; border-radius:14px; box-shadow:0 2px 10px rgba(0,0,0,.04); padding:16px; display:flex; flex-direction:column; gap:10px; }
        .res-head { display:flex; align-items:flex-start; gap:12px; }
        .res-icon { width:42px; height:42px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:18px; color:#fff; flex-shrink:0; }
        .res-title { font-size:13.5px; font-weight:700; color:#1a1a1a; line-height:1.35; }
        .res-meta { font-size:11px; color:#aaa; margin-top:3px; }
        .res-del { margin-left:auto; }
        .res-desc { font-size:12px; color:#666; line-height:1.5; }
        .res-tags { display:flex; flex-wrap:wrap; gap:6px; }
        .badge { font-size:9.5px; font-weight:700; padding:3px 8px; border-radius:20px; text-transform:uppercase; letter-spacing:.3px; }
        .badge-subject { background:#eef2fb; color:var(--blue); }
        .badge-uploader { background:var(--primary-light); color:var(--primary); }
        .res-foot { display:flex; align-items:center; justify-content:space-between; border-top:1px solid #f2f2f2; padding-top:10px; margin-top:auto; }
        .res-downloads { font-size:11px; color:#999; display:flex; align-items:center; gap:5px; }
        .res-download-btn { display:flex; align-items:center; gap:6px; background:var(--primary-light); color:var(--primary); border:none; padding:7px 13px; border-radius:8px; font-size:12px; font-weight:600; font-family:'Poppins',sans-serif; cursor:pointer; text-decoration:none; transition:background .15s; }
        .res-download-btn:hover { background:#ecd9d9; }
        .icon-btn { width:28px; height:28px; border-radius:8px; border:none; background:transparent; color:#bbb; cursor:pointer; font-size:12px; transition:all .15s; }
        .icon-btn:hover { background:#fdeaea; color:var(--accent); }

        .empty { text-align:center; padding:50px 20px; color:#bbb; }
        .empty i { font-size:34px; color:#e0d0d0; display:block; margin-bottom:12px; }

        .pagination { display:flex; justify-content:center; gap:6px; margin-top:18px; }
        .pagination a, .pagination span { padding:7px 12px; border-radius:8px; font-size:12px; text-decoration:none; color:#666; background:#fff; border:1px solid #eee; }
        .pagination .active span { background:var(--primary); color:#fff; border-color:var(--primary); }

        @media (max-width:768px) { .main { padding:16px 12px 90px; } .field-row { flex-direction:column; gap:0; } }
    </style>
</head>
<body>

@if(Auth::user()->isAlumni())
    @include('partials.alumni-sidebar', ['active' => 'resources'])
@elseif(Auth::user()->isChair())
    @include('partials.chair-sidebar', ['active' => 'resources'])
@elseif(Auth::user()->isFaculty())
    @include('partials.faculty-sidebar', ['active' => ''])
@else
    @include('partials.sidebar', ['active' => 'resources'])
@endif

{{-- .main is used by the faculty/chair/alumni sidebars, .main-content by the student sidebar --}}
<main class="main main-content">
    <div class="lib-wrap">
        <div class="topbar">
            <div>
                <div class="page-title"><i class="fas fa-book" style="color:var(--primary);"></i> Resource Library</div>
                <div class="page-sub">Study materials shared by our alumni — filterable and always easy to find.</div>
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
                <h4><i class="fas fa-cloud-arrow-up"></i> Upload a material</h4>
                <form method="POST" action="{{ route('community.resources.store') }}" enctype="multipart/form-data" id="uploadForm">
                    @csrf
                    <div class="field">
                        <label>Title <span style="color:var(--accent);">*</span></label>
                        <input type="text" name="title" value="{{ old('title') }}" placeholder="e.g. Financial Accounting Reviewer Notes" required maxlength="200">
                    </div>
                    <div class="field-row">
                        <div class="field">
                            <label>Subject <span style="color:#bbb;font-weight:400;">(optional)</span></label>
                            <select name="subject_id">
                                <option value="">— None —</option>
                                @foreach($subjects as $s)
                                    <option value="{{ $s->id }}" {{ (string) old('subject_id') === (string) $s->id ? 'selected' : '' }}>{{ $s->code }} — {{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="field">
                        <label>Description <span style="color:#bbb;font-weight:400;">(optional)</span></label>
                        <textarea name="description" placeholder="What's in this file? Any notes for reviewers.">{{ old('description') }}</textarea>
                    </div>
                    <div class="field">
                        <label>File <span style="color:var(--accent);">*</span> <span style="color:#bbb;font-weight:400;">(PDF, Word, Excel — max 20MB)</span></label>
                        <label class="file-drop" for="fileInput" id="fileDrop">
                            <i class="fas fa-paperclip"></i><span id="fileLabel">Click to attach a material</span>
                        </label>
                        <input type="file" name="file" id="fileInput" hidden required
                               accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.csv,.txt,.rtf,.odt">
                    </div>
                    <div class="composer-foot">
                        <button type="submit" class="btn-primary"><i class="fas fa-upload"></i> Upload</button>
                    </div>
                </form>
            </div>
        @endif

        <div class="card">
            <form method="GET" action="{{ route('community.resources.index') }}" class="filter-bar">
                <div class="search-field">
                    <i class="fas fa-magnifying-glass"></i>
                    <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Search by title or filename...">
                </div>
                <select name="subject_id">
                    <option value="">All subjects</option>
                    @foreach($subjects as $s)
                        <option value="{{ $s->id }}" {{ (string) $filters['subject_id'] === (string) $s->id ? 'selected' : '' }}>{{ $s->code }} — {{ $s->name }}</option>
                    @endforeach
                </select>
                <select name="sort">
                    <option value="newest" {{ $filters['sort'] === 'newest' ? 'selected' : '' }}>Newest</option>
                    <option value="most_downloaded" {{ $filters['sort'] === 'most_downloaded' ? 'selected' : '' }}>Most downloaded</option>
                </select>
                <button type="submit"><i class="fas fa-filter"></i> Filter</button>
            </form>
        </div>

        @forelse($resources as $res)
            @php
                $meta = $res->iconMeta();
                $canDelete = Auth::user()->isChair() || $res->uploader_id === Auth::id();
            @endphp
            @if($loop->first)
                <div class="res-grid">
            @endif
                    <div class="res-card">
                        <div class="res-head">
                            <div class="res-icon" style="background:{{ $meta['color'] }};"><i class="fas {{ $meta['icon'] }}"></i></div>
                            <div style="flex:1; min-width:0;">
                                <div class="res-title">{{ $res->title }}</div>
                                <div class="res-meta">{{ strtoupper($res->file_category) }} · {{ $res->humanSize() }} · {{ $res->created_at?->diffForHumans() }}</div>
                            </div>
                            @if($canDelete)
                                <form method="POST" action="{{ route('community.resources.destroy', $res->id) }}" class="res-del" onsubmit="return confirm('Remove this material from the library?');">
                                    @csrf @method('DELETE')
                                    <button class="icon-btn" title="Delete"><i class="fas fa-trash"></i></button>
                                </form>
                            @endif
                        </div>

                        @if($res->description)
                            <div class="res-desc">{{ \Illuminate\Support\Str::limit($res->description, 120) }}</div>
                        @endif

                        <div class="res-tags">
                            @if($res->subject)
                                <span class="badge badge-subject">{{ $res->subject->code }}</span>
                            @endif
                            <span class="badge badge-uploader"><i class="fas fa-user" style="margin-right:3px;"></i>{{ $res->uploader->name ?? 'Former Member' }}</span>
                        </div>

                        <div class="res-foot">
                            <span class="res-downloads"><i class="fas fa-download"></i> {{ $res->downloads_count }} download{{ $res->downloads_count === 1 ? '' : 's' }}</span>
                            <a class="res-download-btn" href="{{ route('community.resources.download', $res->id) }}"><i class="fas fa-arrow-down-to-line"></i> Download</a>
                        </div>
                    </div>
            @if($loop->last)
                </div>
            @endif
        @empty
            <div class="card">
                <div class="empty">
                    <i class="fas fa-folder-open"></i>
                    No materials yet. @if(Auth::user()->isAlumni()) Be the first to upload a study material! @else Check back soon — alumni will start sharing reviewers and notes here. @endif
                </div>
            </div>
        @endforelse

        @if($resources->hasPages())
            <div class="pagination">{{ $resources->links() }}</div>
        @endif
    </div>
</main>

<script>
(function () {
    const fileInput = document.getElementById('fileInput');
    const fileLabel = document.getElementById('fileLabel');
    if (fileInput) {
        fileInput.addEventListener('change', function () {
            fileLabel.innerHTML = this.files.length
                ? '<span class="fname">' + this.files[0].name + '</span>'
                : 'Click to attach a material';
        });
    }
})();
</script>
</body>
</html>
