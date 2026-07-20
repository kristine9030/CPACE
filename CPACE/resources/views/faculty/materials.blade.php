<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learning Materials - CPACE Faculty</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary:#7B1D1D; --primary-hover:#6a1818; --primary-light:#f5e8e8; --accent:#c0392b; --green:#10b981; --blue:#3b82f6; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Poppins',sans-serif; background:#f4f5f7; color:#333; }

        .main { margin-left:230px; padding:26px 30px; min-height:100vh; transition:margin-left .3s; }
        .sidebar.collapsed ~ .main { margin-left:70px; }

        .topbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:22px; }
        .page-title { font-size:26px; font-weight:700; color:#1a1a1a; }
        .page-sub { font-size:12px; color:#999; margin-top:2px; }
        .topbar-right { display:flex; align-items:center; gap:12px; }

        .flash { background:#e8f7ee; color:#1e7e46; border:1px solid #bfead0; padding:11px 16px; border-radius:10px; font-size:13px; font-weight:500; margin-bottom:16px; display:flex; align-items:center; gap:9px; }
        .flash.err { background:#fdeaea; color:#b23b3b; border-color:#f5c6c6; }

        /* Subject tabs */
        .subject-tabs { display:flex; flex-wrap:wrap; gap:8px; margin-bottom:20px; }
        .subject-tab {
            display:inline-flex; align-items:center; gap:8px;
            padding:9px 16px; border-radius:22px; background:#fff; border:1px solid #ececec;
            font-size:13px; font-weight:600; color:#666; text-decoration:none; transition:all .18s;
        }
        .subject-tab:hover { border-color:#d8d8d8; }
        .subject-tab.active { background:var(--primary); color:#fff; border-color:var(--primary); }
        .subject-tab .dot { width:9px; height:9px; border-radius:50%; }

        .layout { display:grid; grid-template-columns:300px 1fr; gap:20px; align-items:start; }

        .panel { background:#fff; border-radius:16px; box-shadow:0 2px 10px rgba(0,0,0,.04); overflow:hidden; }
        .panel-head { padding:16px 20px; border-bottom:1px solid #f2f2f2; font-size:13px; font-weight:700; color:#333; display:flex; align-items:center; justify-content:space-between; }
        .panel-head small { font-weight:500; color:#aaa; font-size:11px; }

        /* Topics list */
        .topic-link {
            display:flex; align-items:center; gap:10px;
            padding:13px 20px; border-bottom:1px solid #f6f6f6;
            text-decoration:none; color:#444; font-size:13px; transition:background .15s;
        }
        .topic-link:last-child { border-bottom:none; }
        .topic-link:hover { background:#fafafa; }
        .topic-link.active { background:var(--primary-light); color:var(--primary); font-weight:600; }
        .topic-link .t-name { flex:1; }
        .topic-link .t-badge { font-size:11px; color:#fff; background:#c9a0a0; min-width:22px; text-align:center; padding:1px 7px; border-radius:11px; font-weight:600; }
        .topic-link.active .t-badge { background:var(--primary); }
        .empty { padding:26px 20px; text-align:center; color:#bbb; font-size:12.5px; }

        /* Materials */
        .mat-list { padding:8px 0; }
        .mat-item { display:flex; align-items:center; gap:14px; padding:14px 20px; border-bottom:1px solid #f6f6f6; }
        .mat-item:last-child { border-bottom:none; }
        .mat-icon { width:44px; height:44px; border-radius:11px; display:flex; align-items:center; justify-content:center; font-size:19px; color:#fff; flex-shrink:0; }
        .mat-info { flex:1; min-width:0; }
        .mat-title { font-size:13.5px; font-weight:600; color:#222; }
        .mat-desc { font-size:11.5px; color:#999; margin-top:2px; }
        .mat-meta { font-size:11px; color:#bbb; margin-top:3px; }
        .mat-actions { display:flex; gap:8px; }
        .mat-btn { width:34px; height:34px; border-radius:9px; border:none; cursor:pointer; font-size:13px; display:inline-flex; align-items:center; justify-content:center; text-decoration:none; transition:all .18s; }
        .mb-view { background:#eef2fb; color:var(--blue); }
        .mb-view:hover { background:#dde6f8; }
        .mb-del { background:#fdeaea; color:var(--accent); }
        .mb-del:hover { background:#f8d5d5; }

        /* Upload form */
        .upload-box { padding:18px 20px; border-top:1px solid #f2f2f2; background:#fbfbfb; }
        .upload-box h4 { font-size:12.5px; font-weight:700; color:#555; margin-bottom:12px; display:flex; align-items:center; gap:7px; }
        .field { margin-bottom:12px; }
        .field label { display:block; font-size:11.5px; font-weight:600; color:#777; margin-bottom:5px; }
        .field input[type=text], .field input[type=url], .field textarea, .field select {
            width:100%; font-family:'Poppins',sans-serif; font-size:13px; color:#333;
            border:1px solid #e2e2e2; border-radius:9px; padding:10px 12px; outline:none; transition:border-color .18s; background:#fff;
        }
        .field input:focus, .field textarea:focus, .field select:focus { border-color:var(--primary); }
        .field textarea { resize:vertical; min-height:54px; }
        .kind-toggle { display:flex; gap:8px; margin-bottom:12px; }
        .kind-opt { flex:1; }
        .kind-opt input { display:none; }
        .kind-opt label { display:block; text-align:center; padding:9px; border:1.5px solid #e2e2e2; border-radius:9px; font-size:12.5px; font-weight:600; color:#888; cursor:pointer; transition:all .18s; margin:0; }
        .kind-opt input:checked + label { border-color:var(--primary); background:var(--primary-light); color:var(--primary); }
        .file-drop { border:1.5px dashed #d5d5d5; border-radius:10px; padding:16px; text-align:center; color:#aaa; font-size:12.5px; cursor:pointer; transition:border-color .18s; }
        .file-drop:hover { border-color:var(--primary); }
        .file-drop i { font-size:22px; display:block; margin-bottom:6px; color:#c9a0a0; }
        .file-drop .fname { color:var(--primary); font-weight:600; }
        .btn-primary { width:100%; background:var(--primary); color:#fff; border:none; padding:11px; border-radius:9px; font-size:13px; font-weight:600; font-family:'Poppins',sans-serif; cursor:pointer; transition:background .18s; display:inline-flex; align-items:center; justify-content:center; gap:8px; }
        .btn-primary:hover { background:var(--primary-hover); }
        .err-text { color:#c0392b; font-size:11px; margin-top:4px; }

        @media (max-width:900px) { .main { margin-left:68px; } }
        @media (max-width:768px) { .main { margin-left:0; padding:16px; } .layout { grid-template-columns:1fr; } }
    </style>
</head>
<body>

@include('partials.faculty-sidebar', ['active' => 'materials'])

<main class="main">
    <div class="topbar">
        <div>
            <div class="page-title">Learning Materials</div>
            <div class="page-sub">Upload study resources (PDF, Word, PPT, links) for your students, organized by topic.</div>
        </div>
        <div class="topbar-right">
            @include('partials.topbar-actions')
        </div>
    </div>

    @if(session('status'))
        <div class="flash"><i class="fas fa-circle-check"></i> {{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="flash err"><i class="fas fa-circle-exclamation"></i> {{ $errors->first() }}</div>
    @endif

    @if($subjects->isEmpty())
        <div class="panel"><div class="empty" style="padding:50px 20px;">
            <i class="fas fa-book" style="font-size:34px;color:#e0d0d0;display:block;margin-bottom:12px;"></i>
            You have no subjects assigned yet. Ask the Program Chair to assign you a subject.
        </div></div>
    @else
        {{-- Subject selector --}}
        <div class="subject-tabs">
            @foreach($subjects as $s)
                <a href="{{ route('faculty.materials', ['subject' => $s->id]) }}"
                   class="subject-tab {{ $selectedSubject && $selectedSubject->id === $s->id ? 'active' : '' }}">
                    <span class="dot" style="background:{{ $s->color ?? '#7B1D1D' }};"></span>
                    {{ $s->code }}
                </a>
            @endforeach
        </div>

        <div class="layout">
            {{-- Topics --}}
            <div class="panel">
                <div class="panel-head">Topics <small>{{ $topics->count() }}</small></div>
                @forelse($topics as $t)
                    <a href="{{ route('faculty.materials', ['subject' => $selectedSubject->id, 'topic' => $t->id]) }}"
                       class="topic-link {{ $selectedTopic && $selectedTopic->id === $t->id ? 'active' : '' }}">
                        <span class="t-name">{{ $t->name }}</span>
                        <span class="t-badge">{{ $t->materials_count }}</span>
                    </a>
                @empty
                    <div class="empty">No topics in this subject yet.</div>
                @endforelse
            </div>

            {{-- Materials for selected topic --}}
            <div class="panel">
                @if($selectedTopic)
                    <div class="panel-head">
                        <span>{{ $selectedTopic->name }}</span>
                        <small>{{ $materials->count() }} material{{ $materials->count() === 1 ? '' : 's' }}</small>
                    </div>

                    <div class="mat-list">
                        @forelse($materials as $m)
                            @php $meta = $m->iconMeta(); @endphp
                            <div class="mat-item">
                                <div class="mat-icon" style="background:{{ $meta['color'] }};"><i class="fas {{ $meta['icon'] }}"></i></div>
                                <div class="mat-info">
                                    <div class="mat-title">{{ $m->title }}</div>
                                    @if($m->description)<div class="mat-desc">{{ $m->description }}</div>@endif
                                    <div class="mat-meta">
                                        @if($m->kind === 'file')
                                            {{ strtoupper($m->file_category) }} · {{ $m->humanSize() }}
                                        @else
                                            External link
                                        @endif
                                        · added {{ $m->created_at?->diffForHumans() }}
                                    </div>
                                </div>
                                <div class="mat-actions">
                                    <a class="mat-btn mb-view" href="{{ $m->url() }}" target="_blank" title="Open"><i class="fas fa-up-right-from-square"></i></a>
                                    <form method="POST" action="{{ route('faculty.materials.destroy', $m->id) }}" onsubmit="return confirm('Delete this material?');">
                                        @csrf @method('DELETE')
                                        <button class="mat-btn mb-del" title="Delete"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="empty">No materials yet. Add the first one below.</div>
                        @endforelse
                    </div>

                    {{-- Upload / add form --}}
                    <div class="upload-box">
                        <h4><i class="fas fa-cloud-arrow-up"></i> Add material to “{{ $selectedTopic->name }}”</h4>
                        <form method="POST" action="{{ route('faculty.materials.store') }}" enctype="multipart/form-data" id="matForm">
                            @csrf
                            <input type="hidden" name="topic_id" value="{{ $selectedTopic->id }}">

                            <div class="kind-toggle">
                                <div class="kind-opt">
                                    <input type="radio" name="kind" id="kindFile" value="file" checked>
                                    <label for="kindFile"><i class="fas fa-file-arrow-up"></i> Upload file</label>
                                </div>
                                <div class="kind-opt">
                                    <input type="radio" name="kind" id="kindLink" value="link">
                                    <label for="kindLink"><i class="fas fa-link"></i> External link</label>
                                </div>
                            </div>

                            <div class="field">
                                <label>Title</label>
                                <input type="text" name="title" value="{{ old('title') }}" placeholder="e.g. Revenue Recognition — Lecture Notes" required>
                            </div>

                            <div class="field">
                                <label>Description <span style="color:#bbb;font-weight:400;">(optional)</span></label>
                                <textarea name="description" placeholder="Short note about this material...">{{ old('description') }}</textarea>
                            </div>

                            <div class="field" id="fileField">
                                <label>File <span style="color:#bbb;font-weight:400;">(PDF, Word, PPT, Excel, image — max 20MB)</span></label>
                                <label class="file-drop" for="fileInput" id="fileDrop">
                                    <i class="fas fa-file-arrow-up"></i>
                                    <span id="fileLabel">Click to choose a file</span>
                                </label>
                                <input type="file" name="file" id="fileInput" hidden
                                       accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.csv,.txt,.rtf,.odt,.jpg,.jpeg,.png,.gif,.webp,.mp4,.zip,.rar">
                            </div>

                            <div class="field" id="linkField" style="display:none;">
                                <label>URL</label>
                                <input type="url" name="external_url" value="{{ old('external_url') }}" placeholder="https://...">
                            </div>

                            <button type="submit" class="btn-primary"><i class="fas fa-plus"></i> Add Material</button>
                        </form>
                    </div>
                @else
                    <div class="empty" style="padding:50px 20px;">
                        <i class="fas fa-hand-pointer" style="font-size:30px;color:#e0d0d0;display:block;margin-bottom:12px;"></i>
                        Select a topic on the left to manage its materials.
                    </div>
                @endif
            </div>
        </div>
    @endif
</main>

<script>
(function () {
    const fileRadio = document.getElementById('kindFile');
    const linkRadio = document.getElementById('kindLink');
    const fileField = document.getElementById('fileField');
    const linkField = document.getElementById('linkField');
    const fileInput = document.getElementById('fileInput');
    const fileLabel = document.getElementById('fileLabel');

    function sync() {
        if (!fileRadio) return;
        const isFile = fileRadio.checked;
        fileField.style.display = isFile ? '' : 'none';
        linkField.style.display = isFile ? 'none' : '';
        if (fileInput) fileInput.required = isFile;
        const urlInput = linkField.querySelector('input');
        if (urlInput) urlInput.required = !isFile;
    }
    if (fileRadio) fileRadio.addEventListener('change', sync);
    if (linkRadio) linkRadio.addEventListener('change', sync);
    sync();

    if (fileInput) {
        fileInput.addEventListener('change', function () {
            fileLabel.innerHTML = this.files.length
                ? '<span class="fname">' + this.files[0].name + '</span>'
                : 'Click to choose a file';
        });
    }
})();
</script>
</body>
</html>
