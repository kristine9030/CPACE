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

        .topbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:22px; position:relative; z-index:100; }
        .page-title { font-size:26px; font-weight:700; color:#14283E; }
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

        .layout { display:grid; grid-template-columns:280px 1fr 280px; gap:20px; align-items:start; }
        @media (max-width:1200px) { .layout { grid-template-columns:260px 1fr; } .mat-list-panel { grid-column:1 / -1; } }

        .panel { background:#fff; border-radius:16px; box-shadow:0 2px 10px rgba(0,0,0,.04); overflow:hidden; }
        .panel-head { padding:16px 20px; border-bottom:1px solid #f2f2f2; font-size:13px; font-weight:700; color:#333; display:flex; align-items:center; justify-content:space-between; }
        .panel-head small { font-weight:500; color:#aaa; font-size:11px; }

        /* Topics search */
        .topics-search { padding:14px 16px 10px; }
        .topics-search-wrap { position:relative; }
        .topics-search-wrap i { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#bbb; font-size:12px; }
        .topics-search-inp { width:100%; font-family:'Poppins',sans-serif; font-size:12.5px; border:1px solid #ececec; border-radius:9px; padding:9px 12px 9px 32px; outline:none; background:#fafafa; color:#555; transition:all .18s; }
        .topics-search-inp:focus { border-color:var(--primary); background:#fff; }

        /* Topics list */
        .topic-list { max-height:calc(100vh - 290px); overflow-y:auto; }
        .topic-list::-webkit-scrollbar { width:7px; }
        .topic-list::-webkit-scrollbar-thumb { background:#e0d0d0; border-radius:6px; }
        .topic-list::-webkit-scrollbar-track { background:transparent; }
        .topic-link {
            display:flex; align-items:center; gap:12px;
            padding:12px 18px; border-left:3px solid transparent;
            text-decoration:none; color:#444; font-size:13px; transition:background .15s, border-color .15s;
        }
        .topic-link:hover { background:#fafafa; }
        .topic-link.active { background:var(--primary-light); border-left-color:var(--primary); }
        .topic-icon { width:34px; height:34px; border-radius:9px; background:#f3eeee; color:#b08a8a; display:flex; align-items:center; justify-content:center; font-size:14px; flex-shrink:0; }
        .topic-link.active .topic-icon { background:var(--primary); color:#fff; }
        .topic-info { flex:1; min-width:0; display:flex; flex-direction:column; gap:2px; }
        .topic-link .t-name { font-size:13px; font-weight:600; color:#333; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .topic-link.active .t-name { color:var(--primary); }
        .t-count { font-size:11px; color:#aaa; }
        .topic-chevron { font-size:11px; color:#ccc; flex-shrink:0; }
        .topic-link.active .topic-chevron { color:var(--primary); }
        .empty { padding:26px 20px; text-align:center; color:#bbb; font-size:12.5px; }
        .mat-empty { display:flex; align-items:center; gap:8px; padding:12px 20px; color:#bbb; font-size:12px; border-bottom:1px solid #f6f6f6; }
        .mat-empty i { font-size:13px; color:#d5c5c5; }

        /* Materials */
        /* Materials list — its own card on the right, beside the add-material form */
        .mat-list-col { max-height:calc(100vh - 230px); overflow-y:auto; }
        .mat-list-col::-webkit-scrollbar { width:6px; }
        .mat-list-col::-webkit-scrollbar-thumb { background:#e0d0d0; border-radius:6px; }
        .mat-list { padding:6px 0; }
        .mat-item { padding:12px 16px; border-bottom:1px solid #f6f6f6; }
        .mat-item:last-child { border-bottom:none; }
        .mat-item-top { display:flex; align-items:flex-start; gap:10px; margin-bottom:6px; }
        .mat-icon { width:32px; height:32px; border-radius:9px; display:flex; align-items:center; justify-content:center; font-size:14px; color:#fff; flex-shrink:0; }
        .mat-info { flex:1; min-width:0; }
        .mat-title { font-size:12.5px; font-weight:600; color:#222; line-height:1.35; word-break:break-word; }
        .mat-desc { display:none; } /* trimmed from the compact sidebar view */
        .mat-meta { font-size:10.5px; color:#bbb; margin:0 0 8px; }
        .status-badge { display:inline-flex; align-items:center; gap:4px; font-size:9.5px; font-weight:600; padding:2px 7px; border-radius:20px; margin-top:4px; }
        .status-badge.s-pub { background:#e8f7ee; color:#1e7e46; }
        .status-badge.s-draft { background:#fff5e6; color:#b5790a; }
        .mat-actions { display:flex; gap:6px; }
        .mat-btn { width:26px; height:26px; border-radius:7px; border:none; cursor:pointer; font-size:11px; display:inline-flex; align-items:center; justify-content:center; text-decoration:none; transition:all .18s; }
        .mb-view { background:#eef2fb; color:var(--blue); }
        .mb-view:hover { background:#dde6f8; }
        .mb-del { background:#fdeaea; color:var(--accent); }
        .mb-del:hover { background:#f8d5d5; }
        .mb-draft { background:#fff5e6; color:#b5790a; }
        .mb-draft:hover { background:#fbead0; }
        .mb-publish { background:#e8f7ee; color:#1e7e46; }
        .mb-publish:hover { background:#d7f0e1; }

        @media (max-width:1200px) {
            .mat-list-col { max-height:320px; }
        }

        /* Upload form */
        .field { margin-bottom:0; }
        .field label { display:block; font-size:11.5px; font-weight:600; color:#777; margin-bottom:5px; }
        .field input[type=text], .field input[type=url], .field textarea, .field select {
            width:100%; font-family:'Poppins',sans-serif; font-size:13px; color:#333;
            border:1px solid #e2e2e2; border-radius:9px; padding:10px 12px; outline:none; transition:border-color .18s; background:#fff;
        }
        .field input:focus, .field textarea:focus, .field select:focus { border-color:var(--primary); }
        .field textarea { resize:vertical; min-height:54px; }
        .err-text { color:#c0392b; font-size:11px; margin-top:4px; }

        /* Add-material panel header */
        .mat-form-head { display:flex; gap:14px; align-items:flex-start; padding:20px 22px 4px; }
        .mat-form-icon { width:44px; height:44px; border-radius:12px; background:var(--primary-light); color:var(--primary); display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0; }
        .mat-form-title { font-size:15.5px; font-weight:700; color:#1a1a1a; }
        .mat-form-sub { font-size:12px; color:#999; margin-top:3px; line-height:1.5; }

        /* Drag & drop zone */
        .dropzone { margin:18px 22px 0; border:1.5px dashed #d8d2d2; border-radius:14px; padding:34px 20px; text-align:center; background:#fcfbfb; transition:border-color .2s, background .2s; cursor:pointer; }
        .dropzone.drag-over { border-color:var(--primary); background:var(--primary-light); }
        .dropzone .dz-cloud { font-size:30px; color:var(--primary); margin-bottom:10px; display:block; }
        .dropzone h5 { font-size:15px; font-weight:700; color:#333; margin-bottom:5px; }
        .dropzone p { font-size:11.5px; color:#aaa; margin-bottom:16px; }
        .dz-choose-btn { background:var(--primary); color:#fff; border:none; padding:10px 20px; border-radius:9px; font-size:13px; font-weight:600; font-family:'Poppins',sans-serif; cursor:pointer; display:inline-flex; align-items:center; gap:8px; }
        .dz-choose-btn:hover { background:var(--primary-hover); }

        /* Source options — small segmented tab / radio-style toggle */
        .source-row { display:inline-flex; background:#f2eeee; border-radius:9px; padding:3px; gap:2px; margin:16px 22px 0; }
        .source-opt { display:inline-flex; align-items:center; gap:6px; padding:7px 14px; border-radius:7px; cursor:pointer; font-family:'Poppins',sans-serif; font-size:12px; font-weight:600; color:#888; transition:all .18s; }
        .source-opt i { font-size:11px; }
        .source-opt:hover:not(.active) { color:var(--primary); }
        .source-opt.active { background:#fff; color:var(--primary); box-shadow:0 1px 3px rgba(0,0,0,.1); }

        /* Title / description */
        .form-grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:16px; margin:18px 22px 0; }
        .field-label-row { display:flex; justify-content:space-between; align-items:baseline; margin-bottom:5px; }
        .char-count { font-size:10px; color:#bbb; }

        /* Selected-file row */
        .file-row { margin:14px 22px 0; display:flex; align-items:center; gap:12px; border:1px solid #ececec; border-radius:10px; padding:11px 14px; cursor:pointer; background:#fff; transition:border-color .18s; }
        .file-row:hover { border-color:var(--primary); }
        .file-row-icon { width:30px; height:30px; border-radius:8px; background:var(--primary-light); color:var(--primary); display:flex; align-items:center; justify-content:center; font-size:13px; flex-shrink:0; }
        .file-row-text { flex:1; min-width:0; }
        .file-row-name { font-size:12.5px; font-weight:600; color:#333; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .file-row-hint { font-size:10.5px; color:#aaa; margin-top:1px; }
        .file-row-clip { color:#bbb; font-size:13px; }

        .file-chip-list { margin:10px 22px 0; display:flex; flex-direction:column; gap:6px; }
        .file-chip { display:flex; align-items:center; gap:10px; background:#fafafa; border:1px solid #f0f0f0; border-radius:8px; padding:8px 10px; }
        .file-chip i.fc-icon { color:var(--primary); font-size:12px; flex-shrink:0; }
        .file-chip .fc-name { flex:1; min-width:0; font-size:12px; font-weight:600; color:#444; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .file-chip .fc-size { font-size:10.5px; color:#bbb; flex-shrink:0; }
        .file-chip .fc-remove { border:none; background:none; color:#ccc; cursor:pointer; font-size:12px; padding:2px 4px; flex-shrink:0; }
        .file-chip .fc-remove:hover { color:var(--accent); }

        .field-hint { font-size:10.5px; color:#b5790a; margin-top:5px; }

        .link-row { margin:14px 22px 0; }

        /* Bottom actions */
        .mat-form-actions { display:flex; justify-content:space-between; align-items:center; gap:12px; margin-top:22px; padding:16px 22px; border-top:1px solid #f2f2f2; background:#fbfbfb; }
        .mfa-draft { background:#fff; color:var(--primary); border:1.5px solid var(--primary-light); padding:10px 18px; border-radius:9px; font-size:13px; font-weight:600; font-family:'Poppins',sans-serif; cursor:pointer; display:inline-flex; align-items:center; gap:8px; }
        .mfa-draft:hover { background:var(--primary-light); }
        .mfa-publish { background:var(--primary); color:#fff; border:none; padding:10px 20px; border-radius:9px; font-size:13px; font-weight:600; font-family:'Poppins',sans-serif; cursor:pointer; display:inline-flex; align-items:center; gap:8px; }
        .mfa-publish:hover { background:var(--primary-hover); }

        @media (max-width:640px) {
            .form-grid-2 { grid-template-columns:1fr; }
            .mat-form-actions { flex-direction:column-reverse; align-items:stretch; }
            .mfa-draft, .mfa-publish { justify-content:center; }
        }

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

    {{-- Status and validation messages surface as SweetAlert popups via partials.alerts --}}

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
            <div class="panel" style="position:sticky; top:26px;">
                <div class="panel-head">Topics</div>
                <div class="topics-search">
                    <div class="topics-search-wrap">
                        <i class="fas fa-search"></i>
                        <input type="text" class="topics-search-inp" id="topicSearch" placeholder="Search topics..." autocomplete="off">
                    </div>
                </div>
                <div class="topic-list" id="topicList">
                    @forelse($topics as $t)
                        <a href="{{ route('faculty.materials', ['subject' => $selectedSubject->id, 'topic' => $t->id]) }}"
                           class="topic-link {{ $selectedTopic && $selectedTopic->id === $t->id ? 'active' : '' }}"
                           data-topic-name="{{ strtolower($t->name) }}">
                            <span class="topic-icon"><i class="fas fa-folder"></i></span>
                            <span class="topic-info">
                                <span class="t-name">{{ $t->name }}</span>
                                <span class="t-count">{{ $t->materials_count }} material{{ $t->materials_count === 1 ? '' : 's' }}</span>
                            </span>
                            <i class="fas fa-chevron-right topic-chevron"></i>
                        </a>
                    @empty
                        <div class="empty">No topics in this subject yet.</div>
                    @endforelse
                    <div class="empty" id="topicNoResults" style="display:none;">No topics match your search.</div>
                </div>
            </div>

            {{-- Add-material form --}}
            <div class="panel">
                @if($selectedTopic)
                    <div class="panel-head"><span>{{ $selectedTopic->name }}</span></div>

                    {{-- Upload / add form --}}
                    <form method="POST" action="{{ route('faculty.materials.store') }}" enctype="multipart/form-data" id="matForm"
                          data-confirm="This material will be published to &ldquo;{{ $selectedTopic->name }}&rdquo; and becomes visible to every student taking this topic."
                          data-confirm-title="Publish this material?"
                          data-confirm-ok="Yes, publish it"
                          data-confirm-icon="question"
                          data-loading="Uploading material...">
                        @csrf
                        <input type="hidden" name="topic_id" value="{{ $selectedTopic->id }}">
                        <input type="hidden" name="kind" id="kindInput" value="file">
                        <input type="hidden" name="status" id="statusInput" value="publish">

                        <div class="mat-form-head">
                            <div class="mat-form-icon"><i class="fas fa-folder"></i></div>
                            <div>
                                <div class="mat-form-title">Add Material to “{{ $selectedTopic->name }}”</div>
                                <div class="mat-form-sub">Upload files or add a link to share with your students. You can also save it as a draft and publish later.</div>
                            </div>
                        </div>

                        {{-- Drag & drop zone --}}
                        <div class="dropzone" id="dropzone">
                            <i class="fas fa-cloud-arrow-up dz-cloud"></i>
                            <h5>Drag and drop your files here</h5>
                            <p>PDF, Word, PPT, Excel, Image &bull; Max 20MB per file &bull; you can select several at once</p>
                            <button type="button" class="dz-choose-btn" id="dzChooseBtn"><i class="fas fa-upload"></i> Choose Files</button>
                        </div>

                        <input type="file" name="file[]" id="fileInput" multiple hidden
                               accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.csv,.txt,.rtf,.odt,.jpg,.jpeg,.png,.gif,.webp,.mp4,.zip,.rar">

                        {{-- Source options --}}
                        <div class="source-row">
                            <div class="source-opt active" id="srcDevice" data-kind="file">
                                <i class="fas fa-desktop"></i>
                                <span class="so-label">Upload from device</span>
                            </div>
                            <div class="source-opt" id="srcLink" data-kind="link">
                                <i class="fas fa-link"></i>
                                <span class="so-label">Add from link</span>
                            </div>
                        </div>

                        {{-- Title / description --}}
                        <div class="form-grid-2">
                            <div class="field">
                                <div class="field-label-row">
                                    <label style="margin:0;">Title <span id="titleReq" style="color:var(--accent);">*</span></label>
                                    <span class="char-count"><span id="titleCount">0</span>/255</span>
                                </div>
                                <input type="text" name="title" id="titleInput" value="{{ old('title') }}" maxlength="255" placeholder="e.g. Revenue Recognition — Lecture Notes">
                                <div class="field-hint" id="titleHint" style="display:none;">Several files selected — each material will be named after its own file.</div>
                            </div>
                            <div class="field">
                                <div class="field-label-row">
                                    <label style="margin:0;">Description <span style="color:#bbb;font-weight:400;">(optional)</span></label>
                                    <span class="char-count"><span id="descCount">0</span>/1000</span>
                                </div>
                                <textarea name="description" id="descInput" maxlength="1000" placeholder="Short note about this material...">{{ old('description') }}</textarea>
                            </div>
                        </div>

                        {{-- Selected file(s) --}}
                        <div class="file-row" id="fileRow">
                            <div class="file-row-icon"><i class="fas fa-paperclip"></i></div>
                            <div class="file-row-text">
                                <div class="file-row-name" id="fileRowName">No file selected</div>
                                <div class="file-row-hint">Choose a file, or several, or drag and drop them here</div>
                            </div>
                            <i class="fas fa-paperclip file-row-clip"></i>
                        </div>
                        <div class="file-chip-list" id="fileChipList"></div>

                        {{-- URL field (link mode) --}}
                        <div class="field link-row" id="linkField" style="display:none;">
                            <label>URL</label>
                            <input type="url" name="external_url" id="urlInput" value="{{ old('external_url') }}" placeholder="https://...">
                        </div>

                        <div class="mat-form-actions">
                            <button type="button" class="mfa-draft" id="draftBtn"><i class="fas fa-file"></i> Save as draft</button>
                            <button type="button" class="mfa-publish" id="publishBtn"><i class="fas fa-upload"></i> Publish Material</button>
                        </div>
                    </form>
                @else
                    <div class="empty" style="padding:50px 20px;">
                        <i class="fas fa-hand-pointer" style="font-size:30px;color:#e0d0d0;display:block;margin-bottom:12px;"></i>
                        Select a topic on the left to manage its materials.
                    </div>
                @endif
            </div>

            {{-- Materials list — its own card on the right --}}
            <div class="panel mat-list-panel">
                @if($selectedTopic)
                    <div class="panel-head">
                        <span>Materials</span>
                        <small>{{ $materials->count() }} material{{ $materials->count() === 1 ? '' : 's' }}</small>
                    </div>
                    <div class="mat-list-col">
                    <div class="mat-list">
                        @forelse($materials as $m)
                            @php $meta = $m->iconMeta(); @endphp
                            <div class="mat-item">
                                <div class="mat-item-top">
                                    <div class="mat-icon" style="background:{{ $meta['color'] }};"><i class="fas {{ $meta['icon'] }}"></i></div>
                                    <div class="mat-info">
                                        <div class="mat-title">{{ $m->title }}</div>
                                        @if($m->is_active)
                                            <span class="status-badge s-pub"><i class="fas fa-circle-check"></i> Published</span>
                                        @else
                                            <span class="status-badge s-draft"><i class="fas fa-pen"></i> Draft</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="mat-meta">
                                    @if($m->kind === 'file')
                                        {{ strtoupper($m->file_category) }} · {{ $m->humanSize() }}
                                    @else
                                        External link
                                    @endif
                                    · added {{ $m->created_at?->diffForHumans() }}
                                </div>
                                <div class="mat-actions">
                                    <a class="mat-btn mb-view" href="{{ $m->url() }}" target="_blank" title="Open"><i class="fas fa-up-right-from-square"></i></a>
                                    <form method="POST" action="{{ route('faculty.materials.toggle-status', $m->id) }}">
                                        @csrf
                                        @if($m->is_active)
                                            <button class="mat-btn mb-draft" title="Move to draft (hide from students)"><i class="fas fa-eye-slash"></i></button>
                                        @else
                                            <button class="mat-btn mb-publish" title="Publish (show to students)"><i class="fas fa-eye"></i></button>
                                        @endif
                                    </form>
                                    <form method="POST" action="{{ route('faculty.materials.destroy', $m->id) }}"
                                          data-confirm="&quot;{{ $m->title }}&quot; will be permanently deleted and students will lose access to it."
                                          data-confirm-title="Delete this material?"
                                          data-confirm-ok="Yes, delete it"
                                          data-confirm-danger>
                                        @csrf @method('DELETE')
                                        <button class="mat-btn mb-del" title="Delete"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="mat-empty"><i class="fas fa-folder-open"></i> No materials yet — add one on the left.</div>
                        @endforelse
                    </div>
                    </div>
                @else
                    <div class="empty" style="padding:40px 16px;">
                        <i class="fas fa-layer-group" style="font-size:24px;color:#e0d0d0;display:block;margin-bottom:10px;"></i>
                        Select a topic to see its materials.
                    </div>
                @endif
            </div>
        </div>
    @endif
</main>

<script>
(function () {
    // ── Topic search (client-side filter over the already-loaded list) ──
    const topicSearch = document.getElementById('topicSearch');
    const topicList = document.getElementById('topicList');
    if (topicSearch && topicList) {
        const links = Array.from(topicList.querySelectorAll('.topic-link'));
        const noResults = document.getElementById('topicNoResults');
        topicSearch.addEventListener('input', function () {
            const q = this.value.trim().toLowerCase();
            let anyVisible = false;
            links.forEach(function (link) {
                const match = link.dataset.topicName.includes(q);
                link.style.display = match ? '' : 'none';
                if (match) anyVisible = true;
            });
            if (noResults) noResults.style.display = (!anyVisible && links.length) ? '' : 'none';
        });
    }

    // ── Add-material form ──
    const matForm = document.getElementById('matForm');
    if (!matForm) return;

    const kindInput = document.getElementById('kindInput');
    const statusInput = document.getElementById('statusInput');
    const fileInput = document.getElementById('fileInput');
    const urlInput = document.getElementById('urlInput');
    const titleInput = document.getElementById('titleInput');
    const titleHint = document.getElementById('titleHint');
    const titleReq = document.getElementById('titleReq');
    const dropzone = document.getElementById('dropzone');
    const dzChooseBtn = document.getElementById('dzChooseBtn');
    const fileRow = document.getElementById('fileRow');
    const fileRowName = document.getElementById('fileRowName');
    const fileChipList = document.getElementById('fileChipList');
    const linkField = document.getElementById('linkField');
    const srcDevice = document.getElementById('srcDevice');
    const srcLink = document.getElementById('srcLink');

    // Switch between "file" and "link" mode.
    function setKind(kind) {
        kindInput.value = kind;
        const isFile = kind === 'file';
        dropzone.style.display = isFile ? '' : 'none';
        fileRow.style.display = isFile ? '' : 'none';
        fileChipList.style.display = isFile ? '' : 'none';
        linkField.style.display = isFile ? 'none' : '';
        titleReq.style.display = isFile ? 'none' : '';
        srcDevice.classList.toggle('active', isFile);
        srcLink.classList.toggle('active', !isFile);
    }
    setKind('file');

    srcDevice.addEventListener('click', function () { setKind('file'); fileInput.click(); });
    srcLink.addEventListener('click', function () { setKind('link'); if (urlInput) urlInput.focus(); });

    // ── Bulk file selection ──
    // Each time the file picker or a drop adds files, they're merged into
    // this running list (rather than replacing it) so "Choose Files" can be
    // used more than once to keep building the batch, then re-synced back
    // onto the real <input> via a DataTransfer so the form still submits them.
    let selectedFiles = [];

    function formatSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return Math.round(bytes / 1024) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    }

    function syncFileInput() {
        const dt = new DataTransfer();
        selectedFiles.forEach(function (f) { dt.items.add(f); });
        fileInput.files = dt.files;
    }

    function renderFiles() {
        const n = selectedFiles.length;
        fileRowName.textContent = n === 0 ? 'No file selected' : (n === 1 ? selectedFiles[0].name : n + ' files selected');
        fileRowName.style.color = n ? 'var(--primary)' : '';

        // A shared title only makes sense for a single file — bulk uploads
        // name each material after its own file (see MaterialController@store).
        titleHint.style.display = n > 1 ? '' : 'none';
        titleInput.disabled = n > 1;
        titleInput.placeholder = n > 1 ? 'Auto-named per file' : 'e.g. Revenue Recognition — Lecture Notes';

        fileChipList.innerHTML = '';
        if (n > 1) {
            selectedFiles.forEach(function (f, idx) {
                const chip = document.createElement('div');
                chip.className = 'file-chip';
                chip.innerHTML =
                    '<i class="fas fa-file fc-icon"></i>' +
                    '<span class="fc-name"></span>' +
                    '<span class="fc-size">' + formatSize(f.size) + '</span>' +
                    '<button type="button" class="fc-remove" title="Remove"><i class="fas fa-xmark"></i></button>';
                chip.querySelector('.fc-name').textContent = f.name;
                chip.querySelector('.fc-remove').addEventListener('click', function (e) {
                    e.stopPropagation();
                    selectedFiles.splice(idx, 1);
                    syncFileInput();
                    renderFiles();
                });
                fileChipList.appendChild(chip);
            });
        }
    }

    function addFiles(fileList) {
        const existingKeys = new Set(selectedFiles.map(function (f) { return f.name + f.size; }));
        Array.from(fileList).forEach(function (f) {
            const key = f.name + f.size;
            if (!existingKeys.has(key)) { selectedFiles.push(f); existingKeys.add(key); }
        });
        syncFileInput();
        renderFiles();
    }

    // Clicking the dropzone or its button opens the file picker.
    function openPicker(e) { if (e) e.stopPropagation(); setKind('file'); fileInput.click(); }
    dropzone.addEventListener('click', openPicker);
    dzChooseBtn.addEventListener('click', openPicker);
    fileRow.addEventListener('click', openPicker);

    // Drag & drop onto the dropzone.
    ['dragenter', 'dragover'].forEach(function (evt) {
        dropzone.addEventListener(evt, function (e) { e.preventDefault(); dropzone.classList.add('drag-over'); });
    });
    ['dragleave', 'drop'].forEach(function (evt) {
        dropzone.addEventListener(evt, function (e) { e.preventDefault(); dropzone.classList.remove('drag-over'); });
    });
    dropzone.addEventListener('drop', function (e) {
        if (e.dataTransfer && e.dataTransfer.files.length) addFiles(e.dataTransfer.files);
    });

    fileInput.addEventListener('change', function () {
        if (this.files.length) addFiles(this.files);
    });

    // Live character counters.
    function wireCounter(inputId, countId) {
        const input = document.getElementById(inputId);
        const count = document.getElementById(countId);
        if (!input || !count) return;
        const update = () => { count.textContent = input.value.length; };
        input.addEventListener('input', update);
        update();
    }
    wireCounter('titleInput', 'titleCount');
    wireCounter('descInput', 'descCount');

    // Save as draft / Publish — two explicit actions instead of a status
    // toggle. Only publishing asks for the "students will see this" confirmation.
    // Validation feedback goes through the site's SweetAlert helpers
    // (window.CPACE) instead of native browser popups, to match the rest of CPACE.
    const draftBtn = document.getElementById('draftBtn');
    const publishBtn = document.getElementById('publishBtn');
    const confirmText = matForm.getAttribute('data-confirm');

    function validate() {
        const isFile = kindInput.value === 'file';
        if (isFile && selectedFiles.length === 0) {
            window.CPACE.warning('Nothing to upload', 'Choose at least one file, or switch to “Add from link”.');
            return false;
        }
        if (!isFile) {
            if (!urlInput.value.trim()) {
                window.CPACE.warning('URL required', 'Paste a link before saving this material.');
                urlInput.focus();
                return false;
            }
            if (!titleInput.value.trim()) {
                window.CPACE.warning('Title required', 'Give this link a title so students can recognize it.');
                titleInput.focus();
                return false;
            }
        }
        return true;
    }

    function submitAs(status) {
        if (!validate()) return;
        statusInput.value = status;
        if (status === 'draft') matForm.removeAttribute('data-confirm');
        else matForm.setAttribute('data-confirm', confirmText);
        if (typeof matForm.requestSubmit === 'function') matForm.requestSubmit();
        else matForm.submit();
    }
    draftBtn.addEventListener('click', function () { submitAs('draft'); });
    publishBtn.addEventListener('click', function () { submitAs('publish'); });
})();
</script>

    @include('partials.alerts')
</body>
</html>
