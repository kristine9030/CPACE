<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subjects & Topics - CPACE Faculty</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary:#7B1D1D; --primary-hover:#6a1818; --primary-light:#f5e8e8; --accent:#c0392b; --green:#10b981; --blue:#3b82f6; --orange:#f59e0b; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Poppins',sans-serif; background:#f4f5f7; color:#333; }

        /* SIDEBAR */
        .sidebar { background:var(--primary); position:fixed; width:230px; height:100vh; display:flex; flex-direction:column; overflow-y:auto; overflow-x:hidden; z-index:1000; transition:width .3s; }
        .sidebar.collapsed { width:70px; }
        .sidebar-header { padding:20px 18px 16px; border-bottom:1px solid rgba(255,255,255,.12); }
        .sidebar-brand { display:flex; align-items:center; gap:10px; }
        .brand-circle { width:42px; height:42px; flex-shrink:0; background:rgba(255,255,255,.15); border:2px solid rgba(255,255,255,.3); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:18px; color:white; }
        .brand-text strong { display:block; font-size:14px; font-weight:700; color:white; }
        .brand-text small { font-size:10px; color:rgba(255,255,255,.7); }
        .faculty-badge { display:inline-block; margin-top:6px; background:rgba(255,255,255,.2); color:white; font-size:9px; font-weight:700; padding:2px 8px; border-radius:20px; letter-spacing:1px; text-transform:uppercase; }
        .sidebar.collapsed .brand-text, .sidebar.collapsed .faculty-badge { display:none; }
        .sidebar-nav { list-style:none; flex:1; padding:10px 0; }
        .sidebar-nav .nav-group-label { padding:14px 22px 4px; font-size:9px; font-weight:700; color:rgba(255,255,255,.4); text-transform:uppercase; letter-spacing:1.2px; }
        .sidebar.collapsed .nav-group-label { display:none; }
        .sidebar-nav li a { display:flex; align-items:center; gap:12px; padding:10px 22px; color:rgba(255,255,255,.72); text-decoration:none; font-size:13px; border-left:3px solid transparent; transition:all .2s; }
        .sidebar-nav li a:hover { color:white; background:rgba(255,255,255,.1); }
        .sidebar-nav li a.active { color:white; background:rgba(255,255,255,.18); border-left-color:white; font-weight:500; }
        .sidebar-nav li a i { width:18px; text-align:center; font-size:14px; flex-shrink:0; }
        .sidebar.collapsed .sidebar-nav li a { padding:10px 0; justify-content:center; gap:0; }
        .sidebar.collapsed .sidebar-nav li a span { display:none; }
        .nav-badge { margin-left:auto; background:var(--accent); color:white; font-size:9px; font-weight:700; padding:2px 6px; border-radius:20px; min-width:18px; text-align:center; }
        .sidebar.collapsed .nav-badge { display:none; }
        .sidebar-footer { border-top:1px solid rgba(255,255,255,.12); padding:14px 18px; }
        .user-row { display:flex; align-items:center; gap:10px; }
        .user-av { width:36px; height:36px; border-radius:50%; background:var(--accent); display:flex; align-items:center; justify-content:center; font-weight:700; font-size:12px; color:white; flex-shrink:0; }
        .user-info .un { display:block; font-size:12px; font-weight:600; color:white; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .user-info .ur { display:block; font-size:10px; color:rgba(255,255,255,.6); }
        .sidebar.collapsed .user-info, .sidebar.collapsed .user-chevron { display:none; }

        /* MAIN */
        .main { margin-left:230px; padding:26px 30px; min-height:100vh; transition:margin-left .3s; }
        .sidebar.collapsed ~ .main { margin-left:70px; }

        /* TOPBAR */
        .topbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; }
        .topbar-left { display:flex; align-items:center; gap:12px; }
        .toggle-btn { width:36px; height:36px; border:1px solid #ddd; background:white; border-radius:8px; cursor:pointer; display:flex; align-items:center; justify-content:center; color:var(--primary); font-size:15px; }
        .page-title { font-size:26px; font-weight:700; color:#1a1a1a; }
        .page-sub { font-size:12px; color:#999; margin-top:2px; }
        .btn { display:inline-flex; align-items:center; gap:7px; padding:9px 18px; border-radius:8px; font-size:13px; font-weight:600; font-family:'Poppins',sans-serif; cursor:pointer; border:none; text-decoration:none; transition:all .2s; }
        .btn-primary { background:var(--primary); color:white; }
        .btn-primary:hover { background:var(--primary-hover); }
        .btn-ghost { background:white; color:#555; border:1px solid #e0e0e0; }
        .btn-ghost:hover { background:#f5f5f5; }

        /* GRID */
        .subjects-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:18px; }

        /* SUBJECT CARD */
        .subject-card { background:white; border-radius:16px; overflow:hidden; }
        .subject-card-head {
            padding:20px 22px;
            display:flex; align-items:center; gap:14px;
            border-bottom:1px solid #f5f5f5;
        }
        .subject-icon {
            width:52px; height:52px; border-radius:14px; flex-shrink:0;
            display:flex; align-items:center; justify-content:center;
            font-size:22px; font-weight:900; color:white;
        }
        .subject-card-info { flex:1; }
        .subject-abbr { font-size:16px; font-weight:700; color:#1a1a1a; }
        .subject-name { font-size:12px; color:#888; }
        .subject-stats { display:flex; gap:16px; margin-top:6px; }
        .subject-stats span { font-size:11px; color:#aaa; }
        .subject-stats strong { color:#555; font-weight:600; }
        .subject-card-actions { display:flex; gap:8px; }

        .icon-btn {
            width:32px; height:32px; border-radius:8px; border:none;
            cursor:pointer; font-size:13px; display:inline-flex; align-items:center; justify-content:center; transition:all .2s;
        }
        .ib-edit { background:#dbeafe; color:var(--blue); }
        .ib-edit:hover { background:#bfdbfe; }

        .expand-btn {
            width:100%; border:none; background:none;
            padding:12px 22px; display:flex; justify-content:space-between; align-items:center;
            cursor:pointer; font-family:'Poppins',sans-serif; font-size:12px; font-weight:600; color:#888;
            border-top:1px solid #f0f0f0;
        }
        .expand-btn:hover { background:#fafafa; }
        .expand-icon { transition:transform .25s; }
        .expand-btn.open .expand-icon { transform:rotate(180deg); }

        /* TOPICS LIST */
        .topics-list { padding:0 22px 18px; display:none; }
        .topics-list.open { display:block; }

        .topic-item {
            display:flex; align-items:center; gap:10px;
            padding:10px 0; border-bottom:1px solid #f8f8f8;
        }
        .topic-item:last-of-type { border-bottom:none; }
        .topic-dot { width:8px; height:8px; border-radius:50%; flex-shrink:0; }
        .topic-name { flex:1; font-size:13px; color:#444; }
        .topic-count { font-size:11px; color:#aaa; white-space:nowrap; }
        .topic-actions { display:flex; gap:6px; }
        .topic-btn { width:24px; height:24px; border-radius:6px; border:none; cursor:pointer; font-size:11px; display:inline-flex; align-items:center; justify-content:center; transition:all .2s; }
        .tb-edit { background:#dbeafe; color:var(--blue); }
        .tb-del  { background:#fde8e8; color:var(--accent); }
        .tb-edit:hover { background:#bfdbfe; }
        .tb-del:hover  { background:#fecaca; }

        .add-topic-row {
            display:flex; gap:8px; margin-top:14px; align-items:center;
        }
        .add-topic-inp {
            flex:1; font-family:'Poppins',sans-serif; font-size:12px;
            border:1.5px dashed #e0e0e0; border-radius:8px; padding:8px 12px;
            outline:none; color:#555; transition:border-color .2s;
        }
        .add-topic-inp:focus { border-color:var(--primary); border-style:solid; }
        .add-topic-inp::placeholder { color:#ccc; }
        .add-topic-btn {
            padding:8px 14px; border-radius:8px; border:none; background:var(--primary);
            color:white; font-size:12px; font-weight:600; font-family:'Poppins',sans-serif; cursor:pointer; transition:all .2s;
        }
        .add-topic-btn:hover { background:var(--primary-hover); }

        @keyframes fadeUp { from{opacity:0;transform:translateY(14px)} to{opacity:1;transform:translateY(0)} }
        .a0{animation:fadeUp .4s ease both} .a1{animation:fadeUp .4s .07s ease both}
    </style>
</head>
<body>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-brand">
            <div class="brand-circle"><i class="fas fa-shield-alt"></i></div>
            <div class="brand-text"><strong>CPACE</strong><small>CPA Reviewer</small></div>
        </div>
        <div class="faculty-badge">Faculty Portal</div>
    </div>
    <ul class="sidebar-nav">
        <li class="nav-group-label">Main</li>
        <li><a href="{{ route('faculty.dashboard') }}"><i class="fas fa-home"></i><span>Dashboard</span></a></li>
        <li class="nav-group-label">Content</li>
        <li><a href="{{ route('faculty.test-bank') }}"><i class="fas fa-database"></i><span>Test Bank</span><span class="nav-badge">1,543</span></a></li>
        <li><a href="{{ route('faculty.question.create') }}"><i class="fas fa-plus-circle"></i><span>Add Question</span></a></li>
        <li><a href="{{ route('faculty.subjects') }}" class="active"><i class="fas fa-book-open"></i><span>Subjects &amp; Topics</span></a></li>
        <li class="nav-group-label">Analytics</li>
        <li><a href="{{ route('faculty.performance') }}"><i class="fas fa-users"></i><span>Student Performance</span></a></li>
        <li><a href="#"><i class="fas fa-chart-line"></i><span>Reports</span></a></li>
        <li class="nav-group-label">System</li>
        <li><a href="#"><i class="fas fa-cog"></i><span>Settings</span></a></li>
        <li><a href="{{ route('dashboard') }}"><i class="fas fa-arrow-left"></i><span>Student View</span></a></li>
    </ul>
    <div class="sidebar-footer">
        <div class="user-row">
            <div class="user-av">KD</div>
            <div class="user-info">
                <span class="un">{{ Auth::user()->name }}</span>
                <span class="ur">Faculty</span>
            </div>
            <i class="fas fa-chevron-down user-chevron" style="color:rgba(255,255,255,.5);font-size:10px;"></i>
        </div>
    </div>
</aside>

<main class="main">
    <div class="topbar a0">
        <div class="topbar-left">
            <button class="toggle-btn" id="sidebarToggle"><i class="fas fa-bars"></i></button>
            <div>
                <div class="page-title">Subjects &amp; Topics</div>
                <div class="page-sub">Manage subjects and their topics. Click a subject to expand and edit its topics.</div>
            </div>
        </div>
        <div style="display:flex;align-items:center;">
            <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                @csrf
                <button type="submit" style="display:flex;align-items:center;gap:7px;padding:9px 16px;border-radius:8px;font-size:13px;font-weight:600;font-family:'Poppins',sans-serif;cursor:pointer;background:white;color:#c0392b;border:1.5px solid #c0392b;transition:all .2s;">
                    <i class="fas fa-sign-out-alt"></i> Sign Out
                </button>
            </form>
        </div>
    </div>

    @php
    $subjects = [
        ['abbr'=>'FAR','name'=>'Financial Accounting & Reporting','color'=>'#3b82f6','icon'=>'fa-calculator','questions'=>342,'topics'=>['Revenue Recognition','Financial Instruments','Inventory','PPE & Investment Property','Business Combinations','Leases','Financial Statements']],
        ['abbr'=>'AUD','name'=>'Auditing','color'=>'#e8567d','icon'=>'fa-search','questions'=>289,'topics'=>['Audit Risk & Materiality','Internal Controls','Audit Evidence','Audit Reports','Professional Ethics & Standards','Related Parties']],
        ['abbr'=>'TAX','name'=>'Taxation','color'=>'#27ae60','icon'=>'fa-landmark','questions'=>310,'topics'=>['Income Tax (Individual)','Income Tax (Corporate)','VAT','Estate Tax','Donor\'s Tax','Business Taxes & Fees']],
        ['abbr'=>'MS','name'=>'Management Services','color'=>'#8b5cf6','icon'=>'fa-chart-pie','questions'=>198,'topics'=>['Cost Accounting','Capital Budgeting','Macroeconomics','Financial Management','Ratio Analysis']],
        ['abbr'=>'RFBT','name'=>'Regulatory Framework for Business Transactions','color'=>'#f59e0b','icon'=>'fa-gavel','questions'=>220,'topics'=>['Contracts & Obligations','Business Organizations','Insurance Law','Banking Laws','Securities Regulation','Real Estate Laws']],
        ['abbr'=>'AFAR','name'=>'Advanced Financial Accounting & Reporting','color'=>'#17a2b8','icon'=>'fa-globe','questions'=>184,'topics'=>['Partnership Accounting','Joint Arrangements','Foreign Currency Transactions','Derivatives','Consolidated Financial Statements']],
    ];
    @endphp

    <div class="subjects-grid a1">
        @foreach($subjects as $i => $s)
        <div class="subject-card">
            <div class="subject-card-head">
                <div class="subject-icon" style="background:{{ $s['color'] }};"><i class="fas {{ $s['icon'] }}"></i></div>
                <div class="subject-card-info">
                    <div class="subject-abbr">{{ $s['abbr'] }}</div>
                    <div class="subject-name">{{ $s['name'] }}</div>
                    <div class="subject-stats">
                        <span><strong>{{ count($s['topics']) }}</strong> Topics</span>
                        <span><strong>{{ $s['questions'] }}</strong> Questions</span>
                    </div>
                </div>
                <div class="subject-card-actions">
                    <button class="icon-btn ib-edit" title="Edit Subject"><i class="fas fa-pen"></i></button>
                </div>
            </div>

            <button class="expand-btn" onclick="toggleTopics(this, 'topics-{{ $i }}')">
                <span><i class="fas fa-list-ul" style="margin-right:6px;"></i> Topics ({{ count($s['topics']) }})</span>
                <i class="fas fa-chevron-down expand-icon"></i>
            </button>

            <div class="topics-list" id="topics-{{ $i }}">
                @foreach($s['topics'] as $j => $topic)
                <div class="topic-item" id="topic-{{ $i }}-{{ $j }}">
                    <div class="topic-dot" style="background:{{ $s['color'] }};"></div>
                    <span class="topic-name topic-label">{{ $topic }}</span>
                    <span class="topic-count">{{ rand(18, 68) }} questions</span>
                    <div class="topic-actions">
                        <button class="topic-btn tb-edit" onclick="editTopic({{ $i }}, {{ $j }})" title="Edit"><i class="fas fa-pen"></i></button>
                        <button class="topic-btn tb-del" title="Delete"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
                @endforeach

                <div class="add-topic-row">
                    <input class="add-topic-inp" type="text" placeholder="Add new topic..." id="new-topic-{{ $i }}">
                    <button class="add-topic-btn" onclick="addTopic({{ $i }}, '{{ $s['color'] }}')"><i class="fas fa-plus"></i> Add</button>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('sidebarToggle');
    const sb  = document.getElementById('sidebar');
    if (btn) btn.addEventListener('click', () => { sb.classList.toggle('collapsed'); localStorage.setItem('facultySidebar', sb.classList.contains('collapsed')); });
    if (localStorage.getItem('facultySidebar') === 'true') sb.classList.add('collapsed');
});

function toggleTopics(btn, id) {
    const list = document.getElementById(id);
    const isOpen = list.classList.toggle('open');
    btn.classList.toggle('open', isOpen);
}

let editingTopic = null;
function editTopic(si, ti) {
    const lbl = document.querySelector(`#topic-${si}-${ti} .topic-label`);
    if (!lbl) return;
    const old = lbl.textContent.trim();
    const inp = document.createElement('input');
    inp.style.cssText = 'font-family:Poppins,sans-serif;font-size:13px;border:1.5px solid #7B1D1D;border-radius:6px;padding:4px 8px;outline:none;width:160px;color:#333;';
    inp.value = old;
    lbl.replaceWith(inp);
    inp.focus();
    inp.select();
    inp.addEventListener('blur', () => { const span = document.createElement('span'); span.className='topic-name topic-label'; span.textContent = inp.value || old; inp.replaceWith(span); });
    inp.addEventListener('keydown', e => { if (e.key==='Enter') inp.blur(); if (e.key==='Escape') { inp.value=old; inp.blur(); }});
}

function addTopic(si, color) {
    const inp = document.getElementById(`new-topic-${si}`);
    const val = inp.value.trim();
    if (!val) return;
    const list = document.getElementById(`topics-${si}`);
    const addRow = list.querySelector('.add-topic-row');
    const j = list.querySelectorAll('.topic-item').length;
    const div = document.createElement('div');
    div.className = 'topic-item';
    div.id = `topic-${si}-${j}`;
    div.innerHTML = `<div class="topic-dot" style="background:${color};"></div><span class="topic-name topic-label">${val}</span><span class="topic-count">0 questions</span><div class="topic-actions"><button class="topic-btn tb-edit" onclick="editTopic(${si},${j})"><i class="fas fa-pen"></i></button><button class="topic-btn tb-del"><i class="fas fa-trash"></i></button></div>`;
    list.insertBefore(div, addRow);
    inp.value = '';
    inp.focus();
}
</script>
</body>
</html>
