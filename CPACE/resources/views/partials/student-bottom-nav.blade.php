{{--
    Mobile bottom navigation bar for student pages.
    Usage: @include('partials.student-bottom-nav', ['active' => 'dashboard'])
    Active keys: dashboard | quizzes | subjects | more
--}}
@php $bnActive = $active ?? ''; @endphp

<style>
    .bottom-nav {
        display: none !important;
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        height: 64px;
        background: #fff;
        border-top: 1px solid #e8e8e8;
        box-shadow: 0 -4px 20px rgba(0,0,0,0.08);
        z-index: 1100;
        align-items: stretch;
    }

    .bottom-nav-items {
        display: flex;
        width: 100%;
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .bottom-nav-item {
        flex: 1;
        display: flex;
    }

    .bottom-nav-item a {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 4px;
        text-decoration: none;
        color: #aaa;
        font-size: 10px;
        font-weight: 500;
        font-family: 'Poppins', sans-serif;
        transition: color 0.2s;
        padding: 6px 0 8px;
        position: relative;
    }

    .bottom-nav-item a i {
        font-size: 20px;
        transition: transform 0.2s, color 0.2s;
    }

    .bottom-nav-item a.active {
        color: #7B1D1D;
    }

    .bottom-nav-item a.active i {
        transform: translateY(-2px);
    }

    .bottom-nav-item a.active::before {
        content: '';
        position: absolute;
        top: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 32px;
        height: 3px;
        background: #7B1D1D;
        border-radius: 0 0 4px 4px;
    }

    .bottom-nav-item a:hover:not(.active) { color: #7B1D1D; }

    /* More drawer overlay */
    .more-drawer-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.35);
        z-index: 1050;
    }
    .more-drawer-overlay.open { display: block; }

    .more-drawer {
        position: fixed;
        bottom: 64px;
        left: 0;
        right: 0;
        background: #fff;
        border-radius: 20px 20px 0 0;
        padding: 12px 0 20px;
        z-index: 1099;
        transform: translateY(100%);
        transition: transform 0.28s cubic-bezier(.4,0,.2,1);
    }
    .more-drawer.open { transform: translateY(0); }

    .more-drawer-handle {
        width: 36px;
        height: 4px;
        background: #e0e0e0;
        border-radius: 2px;
        margin: 0 auto 16px;
    }

    .more-drawer-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 4px;
        padding: 0 12px;
    }

    .more-drawer-item a {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        padding: 14px 8px;
        text-decoration: none;
        color: #555;
        font-size: 11px;
        font-family: 'Poppins', sans-serif;
        font-weight: 500;
        border-radius: 12px;
        transition: background 0.2s;
        text-align: center;
    }
    .more-drawer-item a:hover { background: #f5e8e8; color: #7B1D1D; }
    .more-drawer-item a i {
        width: 42px; height: 42px;
        background: #f5e8e8;
        color: #7B1D1D;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px;
    }

    @media (max-width: 768px) {
        .bottom-nav { display: flex !important; }
    }
</style>

<!-- More Drawer Backdrop -->
<div class="more-drawer-overlay" id="moreDrawerOverlay" onclick="closeMoreDrawer()"></div>

<!-- More Drawer -->
<div class="more-drawer" id="moreDrawer">
    <div class="more-drawer-handle"></div>
    <div class="more-drawer-grid">
        <div class="more-drawer-item">
            <a href="{{ route('mock-exams') }}">
                <i class="fas fa-file-alt"></i>
                Mock Exams
            </a>
        </div>
        <div class="more-drawer-item">
            <a href="{{ route('performance') }}">
                <i class="fas fa-chart-bar"></i>
                Performance
            </a>
        </div>
        <div class="more-drawer-item">
            <a href="{{ route('review-notes') }}">
                <i class="fas fa-sticky-note"></i>
                Review Notes
            </a>
        </div>
        <div class="more-drawer-item">
            <a href="{{ route('calendar') }}">
                <i class="fas fa-calendar-alt"></i>
                Calendar
            </a>
        </div>
        <div class="more-drawer-item">
            <a href="{{ route('achievements') }}">
                <i class="fas fa-trophy"></i>
                Achievements
            </a>
        </div>
        <div class="more-drawer-item">
            <a href="#">
                <i class="fas fa-layer-group"></i>
                Flashcards
            </a>
        </div>
        <div class="more-drawer-item">
            <a href="#">
                <i class="fas fa-cog"></i>
                Settings
            </a>
        </div>
        <div class="more-drawer-item">
            <a href="#" onclick="event.preventDefault(); document.getElementById('bottomNavLogoutForm').submit();">
                <i class="fas fa-sign-out-alt" style="background:#fde8e8; color:#e53e3e;"></i>
                Logout
            </a>
        </div>
    </div>
    <form id="bottomNavLogoutForm" method="POST" action="{{ route('logout') }}" style="display:none;">@csrf</form>
</div>

<!-- Bottom Nav Bar -->
<nav class="bottom-nav" id="bottomNav">
    <ul class="bottom-nav-items">
        <li class="bottom-nav-item">
            <a href="{{ route('dashboard') }}" class="{{ $bnActive === 'dashboard' ? 'active' : '' }}">
                <i class="fas fa-home"></i>
                <span>Home</span>
            </a>
        </li>
        <li class="bottom-nav-item">
            <a href="{{ route('subjects') }}" class="{{ $bnActive === 'subjects' ? 'active' : '' }}">
                <i class="fas fa-book-open"></i>
                <span>Subjects</span>
            </a>
        </li>
        <li class="bottom-nav-item">
            <a href="{{ route('adaptive-quizzes') }}" class="{{ $bnActive === 'quizzes' ? 'active' : '' }}">
                <i class="fas fa-pen-fancy"></i>
                <span>Quizzes</span>
            </a>
        </li>
        <li class="bottom-nav-item">
            <a href="#" id="moreNavBtn" class="{{ in_array($bnActive, ['mock-exams','performance','review-notes','calendar','achievements']) ? 'active' : '' }}" onclick="event.preventDefault(); toggleMoreDrawer();">
                <i class="fas fa-th"></i>
                <span>More</span>
            </a>
        </li>
    </ul>
</nav>

<script>
function toggleMoreDrawer() {
    const drawer = document.getElementById('moreDrawer');
    const overlay = document.getElementById('moreDrawerOverlay');
    const isOpen = drawer.classList.contains('open');
    if (isOpen) {
        closeMoreDrawer();
    } else {
        drawer.classList.add('open');
        overlay.classList.add('open');
    }
}
function closeMoreDrawer() {
    document.getElementById('moreDrawer').classList.remove('open');
    document.getElementById('moreDrawerOverlay').classList.remove('open');
}
</script>
