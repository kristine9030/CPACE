<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $topic->name }} - CPACE</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary:#7B1D1D; --primary-hover:#6a1818; --primary-light:#f5e8e8; --accent-red:#c0392b; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Poppins',sans-serif; background:#f4f5f7; color:#333; }

        .main-content { margin-left:220px; padding:30px 40px; min-height:100vh; transition:margin-left .3s; }
        .sidebar.collapsed ~ .main-content { margin-left:70px; }

        .breadcrumb { display:flex; align-items:center; gap:8px; font-size:13px; color:#999; margin-bottom:18px; flex-wrap:wrap; }
        .breadcrumb a { color:var(--primary); text-decoration:none; font-weight:500; }
        .breadcrumb a:hover { text-decoration:underline; }

        .topic-hero {
            background:#fff; border-radius:16px; padding:24px 28px; margin-bottom:26px;
            box-shadow:0 2px 10px rgba(0,0,0,.05); border-left:5px solid {{ $subject->color ?: '#7B1D1D' }};
        }
        .topic-hero h1 { font-size:23px; font-weight:800; color:#1a1a1a; }
        .topic-hero p { font-size:13px; color:#888; margin-top:5px; }
        .topic-hero .tag { display:inline-block; margin-top:10px; background:{{ $subject->color ?: '#7B1D1D' }}1a; color:{{ $subject->color ?: '#7B1D1D' }}; font-size:11px; font-weight:700; padding:4px 12px; border-radius:20px; }

        .section-title { font-size:16px; font-weight:700; color:#333; margin-bottom:14px; display:flex; align-items:center; gap:8px; }
        .section-title .count { font-size:12px; color:#aaa; font-weight:500; }

        .materials-list { display:flex; flex-direction:column; gap:12px; }
        .material-card {
            display:flex; align-items:center; gap:16px;
            background:#fff; border-radius:14px; padding:16px 20px;
            box-shadow:0 2px 8px rgba(0,0,0,.04); transition:box-shadow .2s;
        }
        .material-card:hover { box-shadow:0 6px 18px rgba(0,0,0,.08); }
        .m-icon { width:50px; height:50px; border-radius:13px; display:flex; align-items:center; justify-content:center; font-size:22px; color:#fff; flex-shrink:0; }
        .m-body { flex:1; min-width:0; }
        .m-title { font-size:14.5px; font-weight:600; color:#222; }
        .m-desc { font-size:12px; color:#999; margin-top:3px; line-height:1.5; }
        .m-meta { font-size:11px; color:#bbb; margin-top:6px; display:flex; gap:12px; flex-wrap:wrap; }
        .m-meta span { display:inline-flex; align-items:center; gap:5px; }
        .m-actions { display:flex; gap:8px; flex-shrink:0; }
        .m-btn {
            display:inline-flex; align-items:center; gap:7px;
            padding:9px 15px; border-radius:9px; font-size:12.5px; font-weight:600;
            text-decoration:none; border:none; cursor:pointer; font-family:'Poppins',sans-serif; transition:all .18s;
        }
        .m-open { background:var(--primary); color:#fff; }
        .m-open:hover { background:var(--primary-hover); }
        .m-download { background:#f1f1f4; color:#555; }
        .m-download:hover { background:#e6e6ea; }

        .empty { text-align:center; padding:64px 20px; color:#aaa; background:#fff; border-radius:16px; }
        .empty i { font-size:40px; color:#e5d5d5; display:block; margin-bottom:14px; }
        .empty p { font-size:13px; }

        @media (max-width:768px) {
            .main-content { margin-left:0; padding:20px 16px; }
            .material-card { flex-wrap:wrap; }
            .m-actions { width:100%; }
            .m-btn { flex:1; justify-content:center; }
        }

        /* Big preview modal — regardless of file type */
        .mat-modal-overlay {
            position: fixed; inset: 0; background: rgba(0,0,0,0.6);
            display: none; align-items: center; justify-content: center;
            z-index: 5500; padding: 30px;
        }
        .mat-modal-overlay.open { display: flex; }
        .mat-modal {
            background: #fff; border-radius: 14px;
            width: 100%; max-width: 980px; height: calc(100vh - 60px);
            display: flex; flex-direction: column; overflow: hidden;
            box-shadow: 0 20px 50px rgba(0,0,0,0.3);
        }
        .mat-modal-head {
            display: flex; align-items: center; justify-content: space-between; gap: 14px;
            padding: 16px 22px; border-bottom: 1px solid #f0f0f0; flex-shrink: 0;
        }
        .mat-modal-title-wrap { display: flex; align-items: center; gap: 12px; min-width: 0; }
        .mat-modal-icon { width:42px; height:42px; border-radius:10px; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-size:18px; color:#fff; }
        .mat-modal-title { font-size: 15.5px; font-weight: 600; color: #232327; }
        .mat-modal-sub { font-size: 11.5px; color: #999; margin-top: 2px; }
        .mat-modal-actions { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
        .mat-modal-body {
            flex: 1; background: #eef0f3; overflow: auto;
            display: flex; align-items: stretch; justify-content: center;
        }
        .mat-modal-body iframe { width: 100%; height: 100%; border: none; background: #fff; }
        .mat-modal-body img { max-width: 100%; max-height: 100%; margin: auto; object-fit: contain; }
        .mat-preview-fallback { margin: auto; text-align: center; padding: 50px 30px; color: #77777d; }
        .mat-preview-fallback i { font-size: 48px; margin-bottom: 16px; display: block; }
        .mat-preview-fallback p { font-size: 14px; margin-bottom: 6px; color: #3a3a3f; font-weight: 500; }
        .icon-btn { width:34px; height:34px; border-radius:8px; border:none; background:#f2f2f4; color:#55555b; cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:14px; }
        .icon-btn:hover { background:#e6e6ea; }
    </style>
</head>
<body>

@include('partials.sidebar', ['active' => 'subjects'])
@include('partials.student-bottom-nav', ['active' => 'subjects'])
@include('partials.student-mobile-header')

<main class="main-content">
    <div class="breadcrumb">
        <a href="{{ route('subjects') }}">Subjects</a>
        <span>/</span>
        <a href="{{ route('subjects.show', $subject->id) }}">{{ $subject->code }}</a>
        <span>/</span>
        <span>{{ $topic->name }}</span>
    </div>

    <div class="topic-hero">
        <h1>{{ $topic->name }}</h1>
        @if($topic->description)<p>{{ $topic->description }}</p>@endif
        <span class="tag">{{ $subject->code }} · {{ $subject->name }}</span>
    </div>

    <div class="section-title">
        <i class="fas fa-folder-open" style="color:var(--primary);"></i> Study Materials
        <span class="count">({{ $materials->count() }})</span>
    </div>

    @if($materials->isEmpty())
        <div class="empty">
            <i class="fas fa-folder-open"></i>
            <p>No study materials have been added for this topic yet.<br>Check back soon — your faculty will upload resources here.</p>
        </div>
    @else
        <div class="materials-list">
            @foreach($materials as $m)
                @php $meta = $m->iconMeta(); @endphp
                <div class="material-card">
                    <div class="m-icon" style="background:{{ $meta['color'] }};"><i class="fas {{ $meta['icon'] }}"></i></div>
                    <div class="m-body">
                        <div class="m-title">{{ $m->title }}</div>
                        @if($m->description)<div class="m-desc">{{ $m->description }}</div>@endif
                        <div class="m-meta">
                            @if($m->kind === 'file')
                                <span><i class="fas fa-file"></i> {{ strtoupper($m->file_category) }}</span>
                                @if($m->humanSize())<span><i class="fas fa-weight-hanging"></i> {{ $m->humanSize() }}</span>@endif
                            @else
                                <span><i class="fas fa-link"></i> External link</span>
                            @endif
                            @if($m->uploader)<span><i class="fas fa-user"></i> {{ $m->uploader->name }}</span>@endif
                        </div>
                    </div>
                    <div class="m-actions">
                        @if($m->kind === 'link')
                            <a class="m-btn m-open" href="{{ $m->url() }}" target="_blank" rel="noopener">
                                <i class="fas fa-up-right-from-square"></i> Open
                            </a>
                        @else
                            <button type="button" class="m-btn m-open" data-act="view" data-id="{{ $m->id }}">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <a class="m-btn m-download" href="{{ route('materials.download', $m->id) }}">
                                <i class="fas fa-download"></i> Download
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</main>

<div class="mat-modal-overlay" id="matModal">
    <div class="mat-modal">
        <div class="mat-modal-head">
            <div class="mat-modal-title-wrap">
                <div class="mat-modal-icon" id="matModalIcon"><i class="fas fa-file"></i></div>
                <div style="min-width:0;">
                    <div class="mat-modal-title" id="matModalTitle"></div>
                    <div class="mat-modal-sub" id="matModalSub"></div>
                </div>
            </div>
            <div class="mat-modal-actions">
                <a href="#" id="matModalDownload" class="m-btn m-download" title="Download"><i class="fas fa-download"></i> Download</a>
                <button class="icon-btn" id="matModalClose" title="Close"><i class="fas fa-xmark"></i></button>
            </div>
        </div>
        <div class="mat-modal-body" id="matModalBody"></div>
    </div>
</div>

<script>
    const MATERIALS = @json($materialsJson);

    const OFFICE_CATEGORIES = ['word', 'excel', 'powerpoint'];
    const esc = s => String(s ?? '').replace(/[&<>"']/g, c =>
        ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    // Office Online can only preview a file if it can fetch the URL itself,
    // so it's useless against localhost/private-network hosts — skip straight
    // to the download fallback there instead of showing a dead iframe.
    const IS_PUBLIC_HOST = (() => {
        const h = window.location.hostname;
        return h !== 'localhost' && h !== '127.0.0.1' &&
            !/^192\.168\.|^10\.|^172\.(1[6-9]|2[0-9]|3[0-1])\.|\.test$|\.local$/.test(h);
    })();

    function materialPreviewBody(m) {
        if (!m.view_url) {
            return `
                <div class="mat-preview-fallback">
                    <i class="fas fa-file-circle-exclamation"></i>
                    <p>Preview unavailable</p>
                    <span>Use the Download button to view this file.</span>
                </div>`;
        }
        if (m.file_category === 'pdf' || m.file_category === 'text') {
            return `<iframe src="${esc(m.view_url)}" title="${esc(m.title)}"></iframe>`;
        }
        if (m.file_category === 'image') {
            return `<img src="${esc(m.view_url)}" alt="${esc(m.title)}">`;
        }
        if (OFFICE_CATEGORIES.includes(m.file_category) && IS_PUBLIC_HOST) {
            const viewerUrl = 'https://view.officeapps.live.com/op/embed.aspx?src=' + encodeURIComponent(m.view_url);
            return `<iframe src="${viewerUrl}" title="${esc(m.title)}"></iframe>`;
        }
        if (OFFICE_CATEGORIES.includes(m.file_category)) {
            return `
                <div class="mat-preview-fallback">
                    <i class="fas ${m.icon}" style="color:${m.color};"></i>
                    <p>${esc(m.original_name || m.title)}</p>
                    <span>Preview isn't available on a local/private server — Office Online needs a public URL. Use Download to open it.</span>
                </div>`;
        }
        return `
            <div class="mat-preview-fallback">
                <i class="fas ${m.icon}" style="color:${m.color};"></i>
                <p>${esc(m.original_name || m.title)}</p>
                <span>This file type can't be previewed in-browser. Use Download to open it.</span>
            </div>`;
    }

    function openMaterialModal(id) {
        const m = MATERIALS.find(x => x.id === id);
        if (!m) return;

        document.getElementById('matModalIcon').style.background = m.color;
        document.getElementById('matModalIcon').innerHTML = `<i class="fas ${m.icon}"></i>`;
        document.getElementById('matModalTitle').textContent = m.title;
        document.getElementById('matModalSub').textContent =
            `${(m.file_category || '').toUpperCase()} · ${m.file_size || ''} · Uploaded by ${m.uploader_name}`;
        document.getElementById('matModalDownload').href = m.download_url;
        document.getElementById('matModalBody').innerHTML = materialPreviewBody(m);
        document.getElementById('matModal').classList.add('open');
    }

    function closeMaterialModal() {
        document.getElementById('matModal').classList.remove('open');
        document.getElementById('matModalBody').innerHTML = '';
    }

    document.addEventListener('DOMContentLoaded', function () {
        const sidebar = document.querySelector('.sidebar');
        if (sidebar && localStorage.getItem('sidebarCollapsed') === 'true') {
            sidebar.classList.add('collapsed');
        }

        document.querySelectorAll('[data-act="view"]').forEach(btn => {
            btn.addEventListener('click', () => openMaterialModal(Number(btn.dataset.id)));
        });

        document.getElementById('matModalClose').addEventListener('click', closeMaterialModal);
        document.getElementById('matModal').addEventListener('click', e => {
            if (e.target === e.currentTarget) closeMaterialModal();
        });
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && document.getElementById('matModal').classList.contains('open')) {
                closeMaterialModal();
            }
        });
    });
</script>
</body>
</html>
