<ul class="nav nav-pills mb-4 flex-wrap">
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('teacher.classes.show') ? 'active' : '' }}"
           href="{{ route('teacher.classes.show', $class->class_id) }}">
            <i class="bi bi-journal-text me-1"></i> Overview
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('teacher.classes.materials.index') ? 'active' : '' }}"
           href="{{ route('teacher.classes.materials.index', $class->class_id) }}">
            <i class="bi bi-folder-fill me-1"></i> Files
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('teacher.classes.members.index') ? 'active' : '' }}"
           href="{{ route('teacher.classes.members.index', $class->class_id) }}">
            <i class="bi bi-people-fill me-1"></i> Members
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('teacher.classes.gradebook') ? 'active' : '' }}"
           href="{{ route('teacher.classes.gradebook', $class->class_id) }}">
            <i class="bi bi-bar-chart-fill me-1"></i> Gradebook
        </a>
    </li>
</ul>
