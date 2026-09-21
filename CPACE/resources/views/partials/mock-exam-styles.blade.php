{{--
    Shared chrome for every Mock Exam screen (faculty builder, chair review and
    monitor, student folders/runner/results). Kept in one partial because all
    thirteen views are standalone HTML documents in this app's convention, and
    duplicating this much CSS across them would guarantee they drift apart.

    Views still add their own page-specific rules after including this.
--}}
<style>
    :root {
        --primary:#7B1D1D; --primary-hover:#6a1818; --primary-light:#f5e8e8;
        --accent:#c0392b; --line:#e3e5ea; --ink:#14283E; --muted:#7a8296;
        --green:#1e9e63; --amber:#c98a08; --red:#c0392b; --blue:#2f6fd0;
    }
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:'Poppins',sans-serif; background:#f4f5f7; color:#333; }
    /* Faculty/chair views use .main; student views use .main-content. Both sit
       beside the same collapsible sidebar, so they share the offset. */
    .main, .main-content { margin-left:230px; padding:26px 30px; min-height:100vh; transition:margin-left .3s; }
    .sidebar.collapsed ~ .main, .sidebar.collapsed ~ .main-content { margin-left:70px; }

    .topbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:22px; gap:16px; position:relative; z-index:100; }
    .page-title { font-size:26px; font-weight:700; color:var(--ink); }
    .page-sub { font-size:12px; color:#999; margin-top:2px; }
    .topbar-right { display:flex; align-items:center; gap:10px; }

    .btn { display:inline-flex; align-items:center; gap:7px; padding:9px 18px; border-radius:8px; font-size:13px;
           font-weight:600; font-family:'Poppins',sans-serif; cursor:pointer; border:none; text-decoration:none;
           transition:all .2s; white-space:nowrap; }
    .btn-primary { background:var(--primary); color:#fff; }
    .btn-primary:hover { background:var(--primary-hover); }
    .btn-ghost { background:#fff; color:var(--ink); border:1px solid var(--line); }
    .btn-ghost:hover { background:#f7f8fa; }
    .btn-green { background:var(--green); color:#fff; }
    .btn-danger { background:var(--red); color:#fff; }
    .btn-sm { padding:6px 12px; font-size:12px; }
    .btn:disabled { opacity:.5; cursor:not-allowed; }

    .card { background:#fff; border:1px solid var(--line); border-radius:14px; padding:20px 22px; box-shadow:0 1px 3px rgba(0,0,0,.05); }
    .card + .card { margin-top:18px; }
    .card-title { font-size:15px; font-weight:700; color:var(--ink); display:flex; align-items:center; gap:9px; }
    .card-sub { font-size:12px; color:var(--muted); margin-top:3px; }

    /* Status chips — one vocabulary across faculty, chair and student. */
    .chip { display:inline-flex; align-items:center; gap:5px; padding:3px 10px; border-radius:999px;
            font-size:11px; font-weight:700; letter-spacing:.2px; }
    .chip-draft { background:#eef0f4; color:#5b6377; }
    .chip-review { background:#fdf0d8; color:var(--amber); }
    .chip-published { background:#dff3e8; color:var(--green); }
    .chip-closed { background:#eceff3; color:#8b93a6; }
    .chip-open { background:#dff3e8; color:var(--green); }
    .chip-upcoming { background:#e4edfb; color:var(--blue); }
    .chip-ended { background:#eceff3; color:#8b93a6; }
    .chip-late { background:#fde8e8; color:var(--red); }
    .chip-flag { background:#fde8e8; color:var(--red); }

    /* Subject cards, matching the Class Quizzes tiles elsewhere in the app. */
    .subject-grid { display:grid; grid-template-columns:repeat(3, 1fr); gap:22px; }
    .subject-card { display:flex; flex-direction:column; background:#fff; border:1px solid var(--line);
                    border-radius:16px; overflow:hidden; text-decoration:none; color:inherit;
                    box-shadow:0 1px 3px rgba(0,0,0,.05); transition:box-shadow .2s, transform .2s; min-height:196px; }
    .subject-card:hover { box-shadow:0 12px 28px -10px rgba(20,20,30,.22); transform:translateY(-3px); }
    .sc-banner { position:relative; padding:22px 24px 34px; color:#fff; min-height:84px; overflow:hidden;
                 background:linear-gradient(135deg, var(--sc-base) 0%, var(--sc-dark) 100%); }
    .sc-banner::before { content:''; position:absolute; inset:0;
                         background:linear-gradient(115deg, rgba(255,255,255,.18) 0%, rgba(255,255,255,0) 55%); }
    .sc-illus { position:absolute; right:6px; top:2px; font-size:62px; opacity:.14; pointer-events:none; }
    .sc-code { position:relative; font-size:20px; font-weight:700; text-shadow:0 1px 4px rgba(0,0,0,.25); }
    .sc-name { position:relative; font-size:12.5px; opacity:.92; margin-top:4px; line-height:1.4; padding-right:26px; }
    .sc-icon { position:relative; z-index:2; width:52px; height:52px; border-radius:15px; margin:-26px 0 0 24px;
               background:#fff; color:var(--sc-base); display:flex; align-items:center; justify-content:center;
               font-size:21px; box-shadow:0 4px 10px -2px rgba(0,0,0,.2); border:3px solid #fff; }
    .sc-body { padding:12px 24px 18px; flex:1; }
    .sc-stats { display:flex; gap:14px; flex-wrap:wrap; font-size:12px; color:var(--muted); }
    .sc-foot { padding:12px 24px; border-top:1px solid var(--line); font-size:12.5px; font-weight:600; color:var(--sc-base); }

    /* Tables */
    .tbl { width:100%; border-collapse:collapse; }
    .tbl th { text-align:left; font-size:11px; text-transform:uppercase; letter-spacing:.4px; color:var(--muted);
              padding:10px 12px; border-bottom:1px solid var(--line); font-weight:700; }
    .tbl td { padding:12px; border-bottom:1px solid #f0f1f4; font-size:13px; vertical-align:middle; }
    .tbl tr:last-child td { border-bottom:none; }
    .tbl tr:hover td { background:#fafbfc; }

    /* Forms */
    .field { margin-bottom:14px; }
    .field label { display:block; font-size:12px; font-weight:600; color:var(--ink); margin-bottom:6px; }
    .field input[type=text], .field input[type=datetime-local], .field input[type=number], .field select, .field textarea {
        width:100%; padding:10px 12px; border:1px solid var(--line); border-radius:8px;
        font-family:'Poppins',sans-serif; font-size:13px; background:#fff; }
    .field input:focus, .field select:focus, .field textarea:focus { outline:none; border-color:var(--primary); }
    .field-hint { font-size:11px; color:var(--muted); margin-top:4px; }
    .err { color:var(--red); font-size:11.5px; margin-top:4px; }

    /* Three-way Manual / Auto / All toggle used for both topics and questions. */
    .modes { display:inline-flex; background:#eef0f4; border-radius:10px; padding:3px; gap:3px; }
    .modes button { border:none; background:transparent; padding:7px 15px; border-radius:8px; cursor:pointer;
                    font-family:'Poppins',sans-serif; font-size:12.5px; font-weight:600; color:#5b6377; }
    .modes button.on { background:#fff; color:var(--primary); box-shadow:0 1px 3px rgba(0,0,0,.12); }

    .empty { text-align:center; padding:50px 20px; color:var(--muted); }
    .empty i { font-size:38px; opacity:.35; margin-bottom:12px; display:block; }
    .empty h3 { font-size:15px; color:var(--ink); margin-bottom:6px; }
    .empty p { font-size:13px; }

    .banner { border-radius:12px; padding:14px 16px; font-size:13px; display:flex; gap:11px; align-items:flex-start; margin-bottom:18px; }
    .banner i { margin-top:2px; }
    .banner-warn { background:#fdf6e6; border:1px solid #f0dfb5; color:#7a5c11; }
    .banner-info { background:#eef4fd; border:1px solid #d3e1f7; color:#254a80; }
    .banner-danger { background:#fdeceb; border:1px solid #f5cdc9; color:#8d2b22; }

    /* Redeem/access code display */
    .code-pill { display:inline-flex; align-items:center; gap:10px; background:#101828; color:#fff;
                 padding:10px 16px; border-radius:10px; font-family:'Montserrat',monospace;
                 font-weight:700; letter-spacing:1.5px; font-size:15px; }
    .code-pill button { background:rgba(255,255,255,.14); border:none; color:#fff; cursor:pointer;
                        border-radius:6px; padding:5px 9px; font-size:12px; }

    /* Student top-bar chrome (see partials/student-page-header). */
    .dropdown-menu { display:none; position:absolute; right:0; top:46px; background:#fff; border:1px solid var(--line);
                     border-radius:10px; box-shadow:0 10px 26px -10px rgba(0,0,0,.25); min-width:200px; padding:6px; z-index:200; }
    .dropdown-menu.active { display:block; }
    .dropdown-menu a, .logout-btn { display:flex; align-items:center; gap:9px; padding:9px 11px; font-size:13px;
                                    color:#333; text-decoration:none; border-radius:7px; border:none; background:none;
                                    width:100%; cursor:pointer; font-family:'Poppins',sans-serif; text-align:left; }
    .dropdown-menu a:hover, .logout-btn:hover { background:#f5f6f8; }
    .badge { position:absolute; top:3px; right:3px; background:var(--red); color:#fff; font-size:9px;
             padding:1px 4px; border-radius:999px; }
    .profile-avatar { width:38px; height:38px; border-radius:50%; border:none; cursor:pointer; overflow:hidden; padding:0; }

    @media (max-width: 1100px) { .subject-grid { grid-template-columns:repeat(2, 1fr); } }
    @media (max-width: 760px) {
        .main, .main-content { margin-left:0; padding:18px 16px; }
        .subject-grid { grid-template-columns:1fr; }
    }
</style>
