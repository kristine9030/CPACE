<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - CPACE Faculty</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary:#7B1D1D; --primary-hover:#6a1818; --primary-light:#f5e8e8; --accent:#c0392b; --green:#10b981; --blue:#3b82f6; --orange:#f59e0b; --ink:#1f2937; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Poppins',sans-serif; background:#f4f5f7; color:#333; }
        .main { margin-left:230px; padding:26px 30px; min-height:100vh; transition:margin-left .3s; }
        .sidebar.collapsed ~ .main { margin-left:70px; }
        .topbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:22px; gap:16px; position:relative; z-index:100; }
        .topbar-left { display:flex; align-items:center; gap:12px; }
        .topbar-right { display:flex; align-items:center; gap:10px; }
        .page-title { font-size:26px; font-weight:700; color:#1a1a1a; }
        .page-sub { font-size:12px; color:#999; margin-top:2px; }
        .btn { display:inline-flex; align-items:center; justify-content:center; gap:7px; padding:9px 18px; border-radius:8px; font-size:13px; font-weight:600; font-family:'Poppins',sans-serif; cursor:pointer; border:none; text-decoration:none; transition:all .2s; }
        .btn-primary { background:var(--primary); color:white; }
        .btn-primary:hover { background:var(--primary-hover); }
        .btn-ghost { background:white; color:#555; border:1px solid #e0e0e0; }
        .btn-ghost:hover { background:#f5f5f5; }
        .btn-soft { background:var(--primary-light); color:var(--primary); }
        .report-shell { display:grid; grid-template-columns:280px minmax(600px, 1fr) 290px; gap:18px; align-items:start; }
        .tool-panel, .export-panel { background:white; border-radius:14px; padding:18px; }
        .panel-title { font-size:13px; font-weight:700; color:#1a1a1a; margin-bottom:14px; display:flex; align-items:center; gap:8px; }
        .field { margin-bottom:14px; }
        .field label { display:block; font-size:11px; font-weight:700; color:#777; margin-bottom:6px; text-transform:uppercase; letter-spacing:.4px; }
        select, input { width:100%; font-family:'Poppins',sans-serif; font-size:13px; border:1px solid #e0e0e0; border-radius:8px; padding:9px 11px; color:#555; background:white; outline:none; }
        select:focus, input:focus { border-color:var(--primary); }
        .check-list { display:flex; flex-direction:column; gap:9px; margin-top:2px; }
        .check-row { display:flex; align-items:center; gap:8px; font-size:12px; color:#555; }
        .check-row input { width:auto; }
        .format-tabs { display:grid; grid-template-columns:repeat(3,1fr); gap:6px; background:#f7f7f7; border-radius:9px; padding:4px; margin-bottom:16px; }
        .format-tab { border:none; background:transparent; color:#777; padding:8px 6px; border-radius:7px; font-family:'Poppins',sans-serif; font-size:12px; font-weight:700; cursor:pointer; }
        .format-tab.active { background:white; color:var(--primary); box-shadow:0 1px 3px rgba(0,0,0,.08); }
        .preview-wrap { background:#e5e7eb; border-radius:14px; padding:24px; overflow:auto; min-height:760px; }
        .preview-toolbar { display:flex; align-items:center; justify-content:space-between; margin-bottom:14px; }
        .preview-title { font-size:13px; font-weight:700; color:#444; display:flex; align-items:center; gap:8px; }
        .zoom-group { display:flex; align-items:center; gap:8px; font-size:12px; color:#777; }
        .paper { width:794px; min-height:1123px; background:white; margin:0 auto; box-shadow:0 12px 28px rgba(0,0,0,.14); padding:48px 54px; color:var(--ink); }
        .paper-head { display:flex; justify-content:space-between; gap:24px; border-bottom:3px solid var(--primary); padding-bottom:18px; margin-bottom:22px; }
        .brand { display:flex; gap:12px; align-items:center; }
        .brand-mark { width:48px; height:48px; border-radius:10px; background:var(--primary-light); display:flex; align-items:center; justify-content:center; color:var(--primary); font-size:22px; }
        .brand h1 { font-size:20px; color:var(--primary); line-height:1.1; }
        .brand p, .report-meta p { font-size:10px; color:#6b7280; margin-top:3px; }
        .report-meta { text-align:right; }
        .report-meta strong { display:block; font-size:12px; color:#111827; margin-bottom:3px; }
        .doc-title { margin-bottom:18px; }
        .doc-title h2 { font-size:18px; color:#111827; margin-bottom:5px; }
        .doc-title p { font-size:11px; color:#6b7280; line-height:1.55; }
        .summary-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:10px; margin-bottom:18px; }
        .summary-box { border:1px solid #e5e7eb; border-radius:8px; padding:10px; }
        .summary-box .num { font-size:18px; font-weight:700; color:#111827; line-height:1; }
        .summary-box .lbl { font-size:9px; color:#6b7280; margin-top:5px; text-transform:uppercase; letter-spacing:.35px; }
        .paper-section { margin-top:18px; }
        .section-head { display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:9px; }
        .section-head h3 { font-size:13px; color:#111827; }
        .section-head span { font-size:9px; color:#9ca3af; }
        .chart-grid { display:grid; grid-template-columns:1.1fr .9fr; gap:14px; }
        .paper-card { border:1px solid #e5e7eb; border-radius:8px; padding:12px; }
        .bar-row { display:grid; grid-template-columns:46px 1fr 34px; gap:8px; align-items:center; margin-bottom:9px; }
        .bar-row:last-child { margin-bottom:0; }
        .bar-label, .bar-val { font-size:10px; color:#4b5563; font-weight:700; }
        .bar-track { height:8px; background:#f3f4f6; border-radius:99px; overflow:hidden; }
        .bar-fill { height:100%; border-radius:99px; }
        .line-chart { width:100%; height:128px; display:block; }
        .chart-caption { font-size:9px; color:#6b7280; margin-top:7px; line-height:1.5; }
        .dist-row { display:grid; grid-template-columns:74px 1fr 28px; gap:8px; align-items:center; margin-bottom:8px; }
        .dist-row span { font-size:10px; color:#4b5563; }
        .report-table { width:100%; border-collapse:collapse; border:1px solid #e5e7eb; }
        .report-table th { background:#fafafa; color:#6b7280; font-size:9px; text-align:left; padding:8px; text-transform:uppercase; letter-spacing:.3px; border-bottom:1px solid #e5e7eb; }
        .report-table td { font-size:10px; padding:8px; border-bottom:1px solid #f1f1f1; vertical-align:top; }
        .report-table tr:last-child td { border-bottom:none; }
        .status { display:inline-flex; padding:2px 7px; border-radius:99px; font-size:9px; font-weight:700; }
        .status-red { background:#fde8e8; color:#c0392b; }
        .status-amber { background:#fef3c7; color:#b45309; }
        .status-green { background:#d1fae5; color:#047857; }
        .two-col { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
        .recommend-list { padding-left:16px; color:#4b5563; font-size:10px; line-height:1.7; }
        .signature-row { display:grid; grid-template-columns:1fr 1fr; gap:60px; margin-top:34px; }
        .sig-line { border-top:1px solid #9ca3af; padding-top:7px; font-size:10px; color:#4b5563; text-align:center; }
        .paper-foot { margin-top:22px; padding-top:10px; border-top:1px solid #e5e7eb; font-size:9px; color:#9ca3af; display:flex; justify-content:space-between; }
        .export-actions { display:grid; gap:8px; margin-bottom:18px; }
        .export-actions .btn { width:100%; }
        .mini-stat { display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:1px solid #f1f1f1; }
        .mini-stat:last-child { border-bottom:none; }
        .mini-stat span { font-size:11px; color:#777; }
        .mini-stat strong { font-size:13px; color:#1a1a1a; }
        .outline-list { display:flex; flex-direction:column; gap:8px; }
        .outline-item { display:flex; gap:9px; align-items:flex-start; font-size:12px; color:#555; }
        .outline-item i { color:var(--primary); width:15px; margin-top:2px; }
        .csv-preview { border:1px solid #e5e7eb; border-radius:8px; overflow:hidden; margin-top:12px; }
        .csv-row { display:grid; grid-template-columns:1.1fr .7fr .7fr .8fr; }
        .csv-row span { font-size:9px; padding:7px; border-right:1px solid #eee; border-bottom:1px solid #eee; color:#555; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .csv-row span:last-child { border-right:none; }
        .csv-row.head span { background:#fafafa; color:#6b7280; font-weight:700; text-transform:uppercase; }
        @keyframes fadeUp { from{opacity:0;transform:translateY(14px)} to{opacity:1;transform:translateY(0)} }
        .a0{animation:fadeUp .4s ease both} .a1{animation:fadeUp .4s .07s ease both} .a2{animation:fadeUp .4s .14s ease both}
        @media (max-width: 1260px) {
            .report-shell { grid-template-columns:260px 1fr; }
            .export-panel { grid-column:1 / -1; }
        }
        @media (max-width: 980px) {
            .report-shell { grid-template-columns:1fr; }
            .paper { transform-origin:top left; width:720px; }
        }
        @media (max-width: 768px) {
            .topbar { flex-direction:column; align-items:flex-start; }
            .topbar-right { width:100%; justify-content:flex-end; flex-wrap:wrap; }
            .preview-wrap { padding:14px; }
            .paper { width:680px; padding:36px; }
        }
    </style>
</head>
<body>

@include('partials.faculty-sidebar', ['active' => 'reports'])

<main class="main">
    <div class="topbar a0">
        <div class="topbar-left">
            <div>
                <div class="page-title">Reports</div>
                <div class="page-sub">Build paper-ready student performance reports for PDF, print, and CSV export.</div>
            </div>
        </div>
        <div class="topbar-right">
            <button class="btn btn-ghost"><i class="fas fa-eye"></i> Preview</button>
            <button class="btn btn-ghost"><i class="fas fa-file-csv"></i> CSV</button>
            <button class="btn btn-primary"><i class="fas fa-file-pdf"></i> PDF</button>
            @include('partials.topbar-actions')
        </div>
    </div>

    <div class="report-shell a1">
        <aside class="tool-panel">
            <div class="panel-title"><i class="fas fa-sliders"></i> Report Setup</div>
            <div class="format-tabs">
                <button class="format-tab active">PDF</button>
                <button class="format-tab">Print</button>
                <button class="format-tab">CSV</button>
            </div>
            <div class="field">
                <label>Report Type</label>
                <select>
                    <option>Class Performance Summary</option>
                    <option>At-Risk Student Report</option>
                    <option>Subject Mastery Report</option>
                    <option>Question Quality Report</option>
                </select>
            </div>
            <div class="field">
                <label>Subject Scope</label>
                <select>
                    <option>Assigned Subjects</option>
                    <option>Financial Accounting and Reporting</option>
                    <option>Auditing</option>
                    <option>Taxation</option>
                </select>
            </div>
            <div class="field">
                <label>Date Range</label>
                <select>
                    <option>Current Term</option>
                    <option>Last 30 Days</option>
                    <option>Last 7 Days</option>
                    <option>All Time</option>
                </select>
            </div>
            <div class="field">
                <label>Student Group</label>
                <select>
                    <option>All Sections</option>
                    <option>BSA 4-A</option>
                    <option>BSA 4-B</option>
                    <option>At-Risk Only</option>
                </select>
            </div>
            <div class="field">
                <label>Include Sections</label>
                <div class="check-list">
                    <label class="check-row"><input type="checkbox" checked> Executive summary</label>
                    <label class="check-row"><input type="checkbox" checked> Performance charts</label>
                    <label class="check-row"><input type="checkbox" checked> At-risk students</label>
                    <label class="check-row"><input type="checkbox" checked> Weak topics</label>
                    <label class="check-row"><input type="checkbox"> Question bank gaps</label>
                    <label class="check-row"><input type="checkbox" checked> Recommendations</label>
                </div>
            </div>
            <button class="btn btn-soft" style="width:100%;"><i class="fas fa-rotate"></i> Regenerate Preview</button>
        </aside>

        <section class="preview-wrap">
            <div class="preview-toolbar">
                <div class="preview-title"><i class="fas fa-file-lines"></i> Paper Preview</div>
                <div class="zoom-group">
                    <button class="btn btn-ghost" style="padding:7px 10px;"><i class="fas fa-minus"></i></button>
                    <span>100%</span>
                    <button class="btn btn-ghost" style="padding:7px 10px;"><i class="fas fa-plus"></i></button>
                </div>
            </div>

            <article class="paper">
                <header class="paper-head">
                    <div class="brand">
                        <div class="brand-mark"><i class="fas fa-graduation-cap"></i></div>
                        <div>
                            <h1>CPACE</h1>
                            <p>CPA Reviewer Faculty Report</p>
                        </div>
                    </div>
                    <div class="report-meta">
                        <strong>Class Performance Summary</strong>
                        <p>Prepared by: {{ Auth::user()->name }}</p>
                        <p>Coverage: Current Term</p>
                        <p>Generated: {{ now()->format('M d, Y') }}</p>
                    </div>
                </header>

                <section class="doc-title">
                    <h2>Student Performance and Intervention Report</h2>
                    <p>This report summarizes quiz activity, accuracy, weak topic concentration, and recommended interventions for students under the selected faculty subject scope.</p>
                </section>

                <section class="summary-grid">
                    <div class="summary-box"><div class="num">48</div><div class="lbl">Active Students</div></div>
                    <div class="summary-box"><div class="num">72%</div><div class="lbl">Class Accuracy</div></div>
                    <div class="summary-box"><div class="num">7</div><div class="lbl">At Risk</div></div>
                    <div class="summary-box"><div class="num">12</div><div class="lbl">Weak Topics</div></div>
                </section>

                <section class="paper-section">
                    <div class="section-head">
                        <h3>Performance Charts</h3>
                        <span>Based on completed non-training quizzes</span>
                    </div>
                    <div class="chart-grid">
                        <div class="paper-card">
                            <div class="section-head" style="margin-bottom:11px;">
                                <h3>Subject Accuracy</h3>
                                <span>Assigned subjects</span>
                            </div>
                            @php
                                $subjectBars = [
                                    ['FAR', 78, '#3b82f6'],
                                    ['AUD', 74, '#e8567d'],
                                    ['TAX', 59, '#c0392b'],
                                    ['MS', 69, '#8b5cf6'],
                                    ['RFBT', 63, '#d97706'],
                                    ['AFAR', 81, '#059669'],
                                ];
                            @endphp
                            @foreach($subjectBars as $bar)
                                <div class="bar-row">
                                    <div class="bar-label">{{ $bar[0] }}</div>
                                    <div class="bar-track"><div class="bar-fill" style="width:{{ $bar[1] }}%;background:{{ $bar[2] }};"></div></div>
                                    <div class="bar-val">{{ $bar[1] }}%</div>
                                </div>
                            @endforeach
                        </div>
                        <div class="paper-card">
                            <div class="section-head" style="margin-bottom:6px;">
                                <h3>Accuracy Trend</h3>
                                <span>Last 6 weeks</span>
                            </div>
                            <svg class="line-chart" viewBox="0 0 260 128" role="img" aria-label="Class accuracy trend line">
                                <line x1="20" y1="18" x2="20" y2="106" stroke="#e5e7eb" />
                                <line x1="20" y1="106" x2="244" y2="106" stroke="#e5e7eb" />
                                <polyline points="20,78 64,70 108,74 152,56 196,49 240,38" fill="none" stroke="#7B1D1D" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" />
                                <polyline points="20,78 64,70 108,74 152,56 196,49 240,38 240,106 20,106" fill="#f5e8e8" opacity=".75" />
                                <circle cx="240" cy="38" r="5" fill="#7B1D1D" />
                                <text x="20" y="122" font-size="8" fill="#9ca3af">W1</text>
                                <text x="235" y="122" font-size="8" fill="#9ca3af">W6</text>
                            </svg>
                            <p class="chart-caption">Class accuracy improved from 64% to 72%, but Taxation remains below the 60% intervention threshold.</p>
                        </div>
                    </div>
                </section>

                <section class="paper-section">
                    <div class="section-head">
                        <h3>Score Distribution</h3>
                        <span>48 students</span>
                    </div>
                    <div class="paper-card">
                        @php
                            $ranges = [
                                ['90-100%', 3, '#059669'],
                                ['75-89%', 18, '#3b82f6'],
                                ['60-74%', 17, '#d97706'],
                                ['Below 60%', 10, '#c0392b'],
                            ];
                        @endphp
                        @foreach($ranges as $r)
                            <div class="dist-row">
                                <span>{{ $r[0] }}</span>
                                <div class="bar-track"><div class="bar-fill" style="width:{{ ($r[1] / 48) * 100 }}%;background:{{ $r[2] }};"></div></div>
                                <span>{{ $r[1] }}</span>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="paper-section">
                    <div class="section-head">
                        <h3>At-Risk Student List</h3>
                        <span>Sorted by intervention priority</span>
                    </div>
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Section</th>
                                <th>Accuracy</th>
                                <th>Weak Area</th>
                                <th>Activity</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Carlo Garcia</td>
                                <td>BSA 4-A</td>
                                <td>44%</td>
                                <td>Estate Tax, Contracts</td>
                                <td>6d inactive</td>
                                <td><span class="status status-red">High</span></td>
                            </tr>
                            <tr>
                                <td>Nina Cruz</td>
                                <td>BSA 3-B</td>
                                <td>58%</td>
                                <td>Financial Instruments</td>
                                <td>5d inactive</td>
                                <td><span class="status status-red">High</span></td>
                            </tr>
                            <tr>
                                <td>Leo Ramos</td>
                                <td>BSA 4-B</td>
                                <td>62%</td>
                                <td>Audit Reports</td>
                                <td>Declining trend</td>
                                <td><span class="status status-amber">Watch</span></td>
                            </tr>
                            <tr>
                                <td>Carla Mendoza</td>
                                <td>BSA 4-A</td>
                                <td>75%</td>
                                <td>Capital Budgeting</td>
                                <td>Active yesterday</td>
                                <td><span class="status status-green">On Track</span></td>
                            </tr>
                        </tbody>
                    </table>
                </section>

                <section class="paper-section two-col">
                    <div>
                        <div class="section-head">
                            <h3>Top Weak Topics</h3>
                        </div>
                        <table class="report-table">
                            <thead><tr><th>Topic</th><th>Students</th><th>Accuracy</th></tr></thead>
                            <tbody>
                                <tr><td>Financial Instruments</td><td>18</td><td>48%</td></tr>
                                <tr><td>Estate Tax</td><td>15</td><td>52%</td></tr>
                                <tr><td>Capital Budgeting</td><td>11</td><td>58%</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div>
                        <div class="section-head">
                            <h3>Recommended Actions</h3>
                        </div>
                        <div class="paper-card">
                            <ul class="recommend-list">
                                <li>Schedule a focused review for topics under 60% accuracy.</li>
                                <li>Assign a short follow-up quiz to High and Watch students.</li>
                                <li>Add more moderate-level questions for weak but frequently attempted topics.</li>
                                <li>Check question explanations for items with high wrong-answer rates.</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <section class="signature-row">
                    <div class="sig-line">Faculty Signature</div>
                    <div class="sig-line">Program Chair / Reviewer</div>
                </section>

                <footer class="paper-foot">
                    <span>CPACE CPA Reviewer</span>
                    <span>Page 1 of 1</span>
                </footer>
            </article>
        </section>

        <aside class="export-panel">
            <div class="panel-title"><i class="fas fa-file-export"></i> Export Package</div>
            <div class="export-actions">
                <button class="btn btn-primary"><i class="fas fa-file-pdf"></i> Export PDF</button>
                <button class="btn btn-ghost"><i class="fas fa-print"></i> Print Preview</button>
                <button class="btn btn-ghost"><i class="fas fa-file-csv"></i> Download CSV</button>
            </div>
            <div class="mini-stat"><span>Paper Size</span><strong>A4</strong></div>
            <div class="mini-stat"><span>Orientation</span><strong>Portrait</strong></div>
            <div class="mini-stat"><span>Charts</span><strong>3 included</strong></div>
            <div class="mini-stat"><span>Student Rows</span><strong>48</strong></div>

            <div class="panel-title" style="margin-top:18px;"><i class="fas fa-list-check"></i> Report Outline</div>
            <div class="outline-list">
                <div class="outline-item"><i class="fas fa-check-circle"></i><span>Executive summary with key totals</span></div>
                <div class="outline-item"><i class="fas fa-check-circle"></i><span>Subject accuracy and trend charts</span></div>
                <div class="outline-item"><i class="fas fa-check-circle"></i><span>At-risk student intervention table</span></div>
                <div class="outline-item"><i class="fas fa-check-circle"></i><span>Weak topics and recommended actions</span></div>
            </div>

            <div class="panel-title" style="margin-top:18px;"><i class="fas fa-table"></i> CSV Preview</div>
            <div class="csv-preview">
                <div class="csv-row head"><span>Student</span><span>Section</span><span>Accuracy</span><span>Status</span></div>
                <div class="csv-row"><span>Carlo Garcia</span><span>BSA 4-A</span><span>44%</span><span>High</span></div>
                <div class="csv-row"><span>Nina Cruz</span><span>BSA 3-B</span><span>58%</span><span>High</span></div>
                <div class="csv-row"><span>Leo Ramos</span><span>BSA 4-B</span><span>62%</span><span>Watch</span></div>
            </div>
        </aside>
    </div>
</main>

</body>
</html>
