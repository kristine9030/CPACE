<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - CPACE Alumni</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary:#7B1D1D; --primary-hover:#6a1818; --primary-light:#f5e8e8; --accent:#c0392b; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Poppins',sans-serif; background:#f4f5f7; color:#333; }
        .main { padding:26px 30px; }
        .wrap { max-width:560px; margin:0 auto; }
        .topbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
        .page-title { font-size:24px; font-weight:700; color:#1a1a1a; }
        .page-sub { font-size:12px; color:#999; margin-top:2px; }
        .flash { background:#e8f7ee; color:#1e7e46; border:1px solid #bfead0; padding:11px 16px; border-radius:10px; font-size:13px; font-weight:500; margin-bottom:16px; display:flex; align-items:center; gap:9px; }
        .card { background:#fff; border-radius:16px; box-shadow:0 2px 10px rgba(0,0,0,.04); padding:22px; }
        .field { margin-bottom:16px; }
        .field label { display:block; font-size:12px; font-weight:600; color:#666; margin-bottom:6px; }
        .field input, .field textarea {
            width:100%; font-family:'Poppins',sans-serif; font-size:13px; color:#333;
            border:1.5px solid #e2e2e6; border-radius:9px; padding:10px 12px; outline:none; transition:border-color .18s;
        }
        .field input:focus, .field textarea:focus { border-color:var(--primary); }
        .field textarea { resize:vertical; min-height:80px; }
        .btn-primary { background:var(--primary); color:#fff; border:none; padding:11px 22px; border-radius:9px; font-size:13px; font-weight:600; font-family:'Poppins',sans-serif; cursor:pointer; display:inline-flex; align-items:center; gap:8px; }
        .btn-primary:hover { background:var(--primary-hover); }
        @media (max-width:768px) { .main { margin-left:0 !important; padding:16px; } }
    </style>
</head>
<body>
@include('partials.alumni-sidebar', ['active' => 'profile'])

<main class="main">
    <div class="wrap">
        <div class="topbar">
            <div>
                <div class="page-title">My Profile</div>
                <div class="page-sub">Shown to students alongside your posts in the Community feed.</div>
            </div>
        </div>

        @if(session('status'))
            <div class="flash"><i class="fas fa-circle-check"></i> {{ session('status') }}</div>
        @endif

        <div class="card">
            <form method="POST" action="{{ route('alumni.profile.update') }}">
                @csrf
                <div class="field">
                    <label>Batch Year</label>
                    <input type="text" name="batch_year" value="{{ old('batch_year', $profile->batch_year ?? '') }}" placeholder="e.g. 2021">
                </div>
                <div class="field">
                    <label>Current Job Title</label>
                    <input type="text" name="current_job" value="{{ old('current_job', $profile->current_job ?? '') }}" placeholder="e.g. Senior Auditor">
                </div>
                <div class="field">
                    <label>Company</label>
                    <input type="text" name="company" value="{{ old('company', $profile->company ?? '') }}" placeholder="e.g. SGV & Co.">
                </div>
                <div class="field">
                    <label>LinkedIn URL</label>
                    <input type="url" name="linkedin_url" value="{{ old('linkedin_url', $profile->linkedin_url ?? '') }}" placeholder="https://linkedin.com/in/...">
                </div>
                <div class="field">
                    <label>Bio</label>
                    <textarea name="bio" placeholder="A short line about yourself for the community...">{{ old('bio', $profile->bio ?? '') }}</textarea>
                </div>
                <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Save Profile</button>
            </form>
        </div>
    </div>
</main>
</body>
</html>
