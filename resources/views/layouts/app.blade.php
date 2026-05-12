<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'TUP LMS') — TUP AI Learning</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Your Bootstrap CSS (the one you pasted) -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <style>
        /* =============================================
           LAYOUT SHELL
        ============================================= */
        :root {
            --sidebar-width: 260px;
            --sidebar-collapsed-width: 72px;
            --topbar-height: 64px;
            --tup-red: #c0392b;
            --tup-red-dark: #96281b;
            --tup-gold: #f39c12;
            --sidebar-bg: #fff;
            --sidebar-border: #e9eef7;
            --active-pill: #f0f4ff;
            --active-text: #435ebe;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f2f7ff;
            color: #607080;
            margin: 0;
            overflow-x: hidden;
        }

        /* ---- Sidebar ---- */
        #sidebar {
            position: fixed;
            top: 0; left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            border-right: 1px solid var(--sidebar-border);
            display: flex;
            flex-direction: column;
            z-index: 1040;
            transition: width 0.25s cubic-bezier(.4,0,.2,1);
            overflow: hidden;
        }

        #sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        /* Brand */
        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 20px;
            height: var(--topbar-height);
            border-bottom: 1px solid var(--sidebar-border);
            text-decoration: none;
            flex-shrink: 0;
        }

        .sidebar-brand .brand-icon {
            width: 36px; height: 36px;
            background: var(--tup-red);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            font-size: 18px; color: #fff; font-weight: 800;
        }

        .sidebar-brand .brand-text {
            display: flex; flex-direction: column; line-height: 1.1;
            overflow: hidden; white-space: nowrap;
        }

        .sidebar-brand .brand-text strong {
            font-size: 15px; font-weight: 800; color: #25396f;
        }

        .sidebar-brand .brand-text span {
            font-size: 11px; color: #7c8db5; font-weight: 600;
        }

        #sidebar.collapsed .brand-text { display: none; }

        /* Nav */
        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 16px 0;
        }

        .sidebar-nav::-webkit-scrollbar { width: 4px; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: #dce7f1; border-radius: 4px; }

        .nav-section-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #adb9cc;
            padding: 12px 24px 4px;
            white-space: nowrap;
            overflow: hidden;
        }

        #sidebar.collapsed .nav-section-label { opacity: 0; }

        .sidebar-nav .nav-item { padding: 2px 12px; }

        .sidebar-nav .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border-radius: 10px;
            color: #607080;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            white-space: nowrap;
            transition: background .15s, color .15s;
            position: relative;
        }

        .sidebar-nav .nav-link i {
            font-size: 18px;
            flex-shrink: 0;
            width: 20px;
            text-align: center;
        }

        .sidebar-nav .nav-link .nav-label { overflow: hidden; }

        .sidebar-nav .nav-link:hover {
            background: var(--active-pill);
            color: var(--active-text);
        }

        .sidebar-nav .nav-link.active {
            background: var(--active-pill);
            color: var(--active-text);
            font-weight: 700;
        }

        .sidebar-nav .nav-link .badge {
            margin-left: auto;
            font-size: 11px;
        }

        #sidebar.collapsed .nav-label,
        #sidebar.collapsed .badge { display: none; }

        #sidebar.collapsed .sidebar-nav .nav-link {
            justify-content: center;
            padding: 10px;
        }

        /* User card at bottom */
        .sidebar-user {
            border-top: 1px solid var(--sidebar-border);
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            flex-shrink: 0;
        }

        .sidebar-user .avatar {
            width: 38px; height: 38px;
            border-radius: 50%;
            background: var(--tup-red);
            color: #fff;
            font-weight: 800;
            font-size: 15px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }

        .sidebar-user .user-info { overflow: hidden; white-space: nowrap; }
        .sidebar-user .user-info strong { display: block; font-size: 13px; color: #25396f; }
        .sidebar-user .user-info span { font-size: 11px; color: #7c8db5; }

        #sidebar.collapsed .user-info,
        #sidebar.collapsed .sidebar-user .btn { display: none; }

        /* ---- Main Content ---- */
        #main {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: margin-left 0.25s cubic-bezier(.4,0,.2,1);
            display: flex;
            flex-direction: column;
        }

        #main.expanded { margin-left: var(--sidebar-collapsed-width); }

        /* ---- Topbar ---- */
        #topbar {
            position: sticky;
            top: 0; z-index: 1030;
            height: var(--topbar-height);
            background: rgba(242,247,255,.9);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--sidebar-border);
            display: flex;
            align-items: center;
            padding: 0 24px;
            gap: 16px;
        }

        #topbar .toggle-btn {
            background: none; border: none; cursor: pointer;
            color: #607080; font-size: 22px; padding: 4px;
            display: flex; align-items: center;
            border-radius: 8px;
            transition: background .15s;
        }

        #topbar .toggle-btn:hover { background: #e9eef7; }

        #topbar .topbar-search {
            flex: 1; max-width: 420px;
            position: relative;
        }

        #topbar .topbar-search input {
            width: 100%;
            padding: 8px 14px 8px 38px;
            border: 1px solid #dce7f1;
            border-radius: 10px;
            font-size: 14px;
            font-family: 'Nunito', sans-serif;
            background: #fff;
            color: #607080;
            outline: none;
            transition: border-color .15s, box-shadow .15s;
        }

        #topbar .topbar-search input:focus {
            border-color: #a1afdf;
            box-shadow: 0 0 0 .2rem rgba(67,94,190,.18);
        }

        #topbar .topbar-search i {
            position: absolute; left: 12px; top: 50%;
            transform: translateY(-50%);
            color: #adb9cc; font-size: 16px;
        }

        #topbar .topbar-actions {
            margin-left: auto;
            display: flex; align-items: center; gap: 8px;
        }

        .topbar-icon-btn {
            width: 38px; height: 38px;
            border: none; background: #fff;
            border-radius: 10px; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            color: #607080; font-size: 18px;
            border: 1px solid #e9eef7;
            position: relative;
            transition: background .15s;
        }

        .topbar-icon-btn:hover { background: #f0f4ff; color: #435ebe; }

        .topbar-icon-btn .notif-dot {
            position: absolute; top: 7px; right: 8px;
            width: 7px; height: 7px;
            background: var(--tup-red);
            border-radius: 50%;
            border: 2px solid #fff;
        }

        .topbar-avatar {
            width: 38px; height: 38px;
            border-radius: 50%;
            background: var(--tup-red);
            color: #fff; font-weight: 800; font-size: 14px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; border: 2px solid #fff;
            box-shadow: 0 0 0 2px #dce7f1;
        }

        /* ---- Page Content ---- */
        .page-content {
            flex: 1;
            padding: 28px 28px;
        }

        .page-header {
            margin-bottom: 24px;
        }

        .page-header h4 {
            font-size: 22px;
            font-weight: 800;
            color: #25396f;
            margin: 0 0 2px;
        }

        .page-header p {
            margin: 0;
            color: #7c8db5;
            font-size: 14px;
        }

        /* ---- Stat Cards ---- */
        .stat-card {
            background: #fff;
            border-radius: 14px;
            padding: 22px 24px;
            border: 1px solid #e9eef7;
            display: flex;
            align-items: center;
            gap: 18px;
            transition: box-shadow .2s, transform .2s;
        }

        .stat-card:hover {
            box-shadow: 0 8px 24px rgba(67,94,190,.1);
            transform: translateY(-2px);
        }

        .stat-icon {
            width: 52px; height: 52px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; flex-shrink: 0;
        }

        .stat-icon.blue  { background: #e8edfa; color: #435ebe; }
        .stat-icon.green { background: #e0f5ec; color: #198754; }
        .stat-icon.red   { background: #fde8e6; color: #c0392b; }
        .stat-icon.gold  { background: #fef3dc; color: #f39c12; }
        .stat-icon.cyan  { background: #d9f6fd; color: #0dcaf0; }

        .stat-info strong {
            display: block;
            font-size: 26px; font-weight: 800; color: #25396f; line-height: 1;
        }

        .stat-info span {
            font-size: 13px; color: #7c8db5; font-weight: 600;
        }

        /* ---- Content Cards ---- */
        .content-card {
            background: #fff;
            border-radius: 14px;
            border: 1px solid #e9eef7;
            overflow: hidden;
        }

        .content-card .card-header {
            padding: 18px 22px;
            border-bottom: 1px solid #f0f4ff;
            background: #fff;
            display: flex; align-items: center; justify-content: space-between;
        }

        .content-card .card-header h6 {
            margin: 0;
            font-size: 15px; font-weight: 800; color: #25396f;
        }

        .content-card .card-body { padding: 20px 22px; }

        /* ---- Course Item ---- */
        .course-item {
            display: flex; align-items: center; gap: 14px;
            padding: 12px 0;
            border-bottom: 1px solid #f5f7fc;
        }

        .course-item:last-child { border-bottom: none; }

        .course-thumb {
            width: 44px; height: 44px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; flex-shrink: 0;
        }

        .course-info strong { display: block; font-size: 14px; color: #25396f; font-weight: 700; }
        .course-info span { font-size: 12px; color: #7c8db5; }

        /* ---- Progress bar ---- */
        .progress { height: 6px; border-radius: 4px; background: #e9eef7; }
        .progress-bar { border-radius: 4px; }

        /* ---- AI Badge ---- */
        .ai-badge {
            display: inline-flex; align-items: center; gap: 5px;
            background: linear-gradient(135deg, #435ebe, #6f42c1);
            color: #fff; font-size: 11px; font-weight: 700;
            padding: 3px 10px; border-radius: 20px;
            letter-spacing: .02em;
        }

        /* ---- TUP Role pill ---- */
        .role-pill-teacher { background: #fde8e6; color: #c0392b; }
        .role-pill-student { background: #e8edfa; color: #435ebe; }
        .role-pill {
            display: inline-block;
            font-size: 11px; font-weight: 700;
            padding: 3px 10px; border-radius: 20px;
            text-transform: uppercase; letter-spacing: .04em;
        }

        /* ---- Flash card preview ---- */
        .flashcard-preview {
            background: linear-gradient(135deg, #435ebe 0%, #6f42c1 100%);
            border-radius: 14px;
            padding: 24px;
            color: #fff;
            min-height: 130px;
            display: flex; flex-direction: column; justify-content: space-between;
        }

        /* ---- AI Tutor Chat Bubble ---- */
        .ai-bubble {
            background: #f0f4ff;
            border-radius: 0 14px 14px 14px;
            padding: 12px 16px;
            font-size: 14px; color: #25396f;
            max-width: 85%;
        }

        /* Mobile overlay */
        #sidebar-overlay {
            display: none;
            position: fixed; inset: 0;
            background: rgba(0,0,0,.35);
            z-index: 1039;
        }

        @media (max-width: 991.98px) {
            #sidebar { transform: translateX(-100%); width: var(--sidebar-width) !important; }
            #sidebar.mobile-open { transform: translateX(0); }
            #sidebar-overlay.active { display: block; }
            #main { margin-left: 0 !important; }
        }
    </style>

    @stack('styles')
</head>
<body>

<!-- Sidebar Overlay (mobile) -->
<div id="sidebar-overlay" onclick="closeSidebar()"></div>

<!-- ====== SIDEBAR ====== -->
<aside id="sidebar">
    <!-- Brand -->
    <a href="#" class="sidebar-brand">
        <div class="brand-icon">T</div>
        <div class="brand-text">
            <strong>TUP-LMS</strong>
            <span>AI Learning Platform</span>
        </div>
    </a>

    <!-- Navigation -->
    <nav class="sidebar-nav">
        @yield('sidebar-nav')
    </nav>

    <!-- User Info -->
    <div class="sidebar-user">
        <div class="avatar">{{ strtoupper(substr(auth()->user()->name ?? 'Louis Lane', 0, 1)) }}</div>
        <div class="user-info">
            <strong>{{ auth()->user()->name ?? 'Louis Lane' }}</strong>
            <span>{{ ucfirst(auth()->user()->role ?? 'Member') }}</span>
        </div>
    </div>
</aside>

<!-- ====== MAIN ====== -->
<div id="main">

    <!-- Topbar -->
    <div id="topbar">
        <button class="toggle-btn" id="sidebarToggle" onclick="toggleSidebar()">
            <i class="bi bi-list"></i>
        </button>

        <div class="topbar-search d-none d-md-block">
            <i class="bi bi-search"></i>
            <input type="text" placeholder="Search courses, classes…">
        </div>

        <div class="topbar-actions">
            <button class="topbar-icon-btn" title="Notifications">
                <i class="bi bi-bell"></i>
                <span class="notif-dot"></span>
            </button>
            <button class="topbar-icon-btn d-md-none" title="Search">
                <i class="bi bi-search"></i>
            </button>
            <div class="topbar-avatar" title="{{ auth()->user()->name ?? 'Louis Lane' }}">
                {{ strtoupper(substr(auth()->user()->name ?? 'Louis Lane', 0, 1)) }}
            </div>
        </div>
    </div>

    <!-- Page Content -->
    <div class="page-content">
        @yield('content')
    </div>

</div><!-- /#main -->

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-alpha2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    const sidebar  = document.getElementById('sidebar');
    const main     = document.getElementById('main');
    const overlay  = document.getElementById('sidebar-overlay');
    let collapsed  = false;

    function toggleSidebar() {
        if (window.innerWidth < 992) {
            sidebar.classList.toggle('mobile-open');
            overlay.classList.toggle('active');
        } else {
            collapsed = !collapsed;
            sidebar.classList.toggle('collapsed', collapsed);
            main.classList.toggle('expanded', collapsed);
        }
    }

    function closeSidebar() {
        sidebar.classList.remove('mobile-open');
        overlay.classList.remove('active');
    }

    // Active nav link highlight
    document.querySelectorAll('.sidebar-nav .nav-link').forEach(link => {
        if (link.getAttribute('href') === window.location.pathname) {
            link.classList.add('active');
        }
    });
</script>

@stack('scripts')
</body>
</html>
