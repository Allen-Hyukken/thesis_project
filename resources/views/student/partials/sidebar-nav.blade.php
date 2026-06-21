<li class="sidebar-title">Main</li>

<li class="sidebar-item {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
    <a href="{{ route('student.dashboard') }}" class="sidebar-link">
        <i class="bi bi-grid-fill"></i>
        <span>Dashboard</span>
    </a>
</li>

<li class="sidebar-title">Learning</li>

<li class="sidebar-item {{ request()->routeIs('student.classes*') ? 'active' : '' }}">
    <a href="{{ route('student.classes') }}" class="sidebar-link">
        <i class="bi bi-book-half"></i>
        <span>My Enrolled Classes</span>
    </a>
</li>

<li class="sidebar-title">AI Study Tools</li>

<li class="sidebar-item {{ request()->routeIs('student.flashcards') ? 'active' : '' }}">
    <a href="{{ route('student.flashcards') }}" class="sidebar-link">
        <i class="bi bi-layers-fill"></i>
        <span>AI Flashcards</span>
    </a>
</li>

<li class="sidebar-item {{ request()->routeIs('student.ai-tutor') ? 'active' : '' }}">
    <a href="{{ route('student.ai-tutor') }}" class="sidebar-link">
        <i class="bi bi-chat-quote-fill"></i>
        <span>AI Tutor Chat</span>
    </a>
</li>

<li class="sidebar-title">Account</li>

<li class="sidebar-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
    <a href="{{ route('profile.show') }}" class="sidebar-link">
        <i class="bi bi-person-circle"></i>
        <span>My Profile</span>
    </a>
</li>

<li class="sidebar-item">
    <a href="{{ route('logout') }}"
       onclick="event.preventDefault(); document.getElementById('logout-form-s').submit();"
       class="sidebar-link">
        <i class="bi bi-box-arrow-left"></i>
        <span>Logout</span>
    </a>
    <form id="logout-form-s" action="{{ route('logout') }}" method="POST" class="d-none">
        @csrf
    </form>
</li>
