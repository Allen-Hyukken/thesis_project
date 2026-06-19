<li class="sidebar-title">Main</li>

<li class="sidebar-item {{ request()->routeIs('teacher.dashboard') ? 'active' : '' }}">
    <a href="{{ route('teacher.dashboard') }}" class="sidebar-link">
        <i class="bi bi-grid-fill"></i>
        <span>Dashboard</span>
    </a>
</li>

<li class="sidebar-title">Teaching</li>

<li class="sidebar-item {{ request()->routeIs('teacher.classes') ? 'active' : '' }}">
    <a href="{{ route('teacher.classes') }}" class="sidebar-link">
        <i class="bi bi-people-fill"></i>
        <span>My Classes</span>
    </a>
</li>

<li class="sidebar-item {{ request()->routeIs('teacher.courses*') ? 'active' : '' }}">
    <a href="{{ route('teacher.courses') }}" class="sidebar-link">
        <i class="bi bi-journal-bookmark-fill"></i>
        <span>Courses</span>
    </a>
</li>

<li class="sidebar-item {{ request()->routeIs('teacher.materials') ? 'active' : '' }}">
    <a href="{{ route('teacher.materials') }}" class="sidebar-link">
        <i class="bi bi-folder-fill"></i>
        <span>Learning Materials</span>
    </a>
</li>

<li class="sidebar-item {{ request()->routeIs('teacher.students') ? 'active' : '' }}">
    <a href="{{ route('teacher.students') }}" class="sidebar-link">
        <i class="bi bi-person-badge-fill"></i>
        <span>Students</span>
    </a>
</li>

<li class="sidebar-title">AI Tools</li>

<li class="sidebar-item {{ request()->routeIs('teacher.ai-generate') ? 'active' : '' }}">
    <a href="{{ route('teacher.ai-generate') }}" class="sidebar-link">
        <i class="bi bi-stars"></i>
        <span>AI Course Builder</span>
    </a>
</li>

<li class="sidebar-item {{ request()->routeIs('teacher.results') ? 'active' : '' }}">
    <a href="{{ route('teacher.results') }}" class="sidebar-link">
        <i class="bi bi-bar-chart-fill"></i>
        <span>Results & Analytics</span>
    </a>
</li>

<li class="sidebar-title">Account</li>

<li class="sidebar-item {{ request()->routeIs('teacher.profile') ? 'active' : '' }}">
    <a href="{{ route('teacher.profile') }}" class="sidebar-link">
        <i class="bi bi-person-circle"></i>
        <span>My Profile</span>
    </a>
</li>

<li class="sidebar-item">
    <a href="{{ route('logout') }}"
       onclick="event.preventDefault(); document.getElementById('logout-form-t').submit();"
       class="sidebar-link">
        <i class="bi bi-box-arrow-left"></i>
        <span>Logout</span>
    </a>
    <form id="logout-form-t" action="{{ route('logout') }}" method="POST" class="d-none">
        @csrf
    </form>
</li>
