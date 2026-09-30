{{--
    Institution mark shown above the user footer in every sidebar (student,
    faculty, Program Chair, alumni): the Batangas State University seal in
    full colour with the campus name. Collapses to the seal alone when the
    sidebar is collapsed or narrowed to icons.

    Include between </ul> and <div class="sidebar-footer"> of a sidebar.
--}}
<style>
    .sidebar .sidebar-inst {
        display: flex;
        align-items: center;
        gap: 9px;
        margin: 10px 12px 12px;
        padding: 9px 10px;
        border-radius: 12px;
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.08);
        flex-shrink: 0;
    }
    .sidebar .sidebar-inst img {
        width: 32px;
        height: 32px;
        flex-shrink: 0;
        object-fit: contain;
        filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));
    }
    .sidebar .sidebar-inst-text {
        min-width: 0;
        font-family: 'Montserrat', 'Poppins', sans-serif;
        font-size: 10px;
        font-weight: 700;
        line-height: 1.4;
        white-space: nowrap;
        color: rgba(255,255,255,0.78);
    }
    .sidebar .sidebar-inst-text span {
        display: block;
        margin-top: 1px;
        font-size: 9px;
        font-weight: 600;
        letter-spacing: .3px;
        color: rgba(255,255,255,0.5);
    }
    .sidebar.collapsed .sidebar-inst { margin: 10px 10px 12px; padding: 8px 0; justify-content: center; background: none; border-color: transparent; }
    .sidebar.collapsed .sidebar-inst-text { display: none; }
    @media (max-width: 900px) {
        .sidebar .sidebar-inst { margin: 10px 10px 12px; padding: 8px 0; justify-content: center; background: none; border-color: transparent; }
        .sidebar .sidebar-inst-text { display: none; }
    }
</style>
<div class="sidebar-inst" aria-label="Batangas State University, The National Engineering University, ARASOF-Nasugbu" data-dm-skip>
    <img src="{{ asset('images/logo bsu (3).png') }}" alt="Batangas State University seal">
    <div class="sidebar-inst-text">Batangas State University<span>TNEU · ARASOF-Nasugbu</span></div>
</div>
