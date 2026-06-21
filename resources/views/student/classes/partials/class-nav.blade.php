<ul class="nav nav-pills mb-4 flex-wrap">
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('student.classes.show') ? 'active' : '' }}"
           href="{{ route('student.classes.show', $class->class_id) }}">
            <i class="bi bi-journal-text me-1"></i> Overview
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('student.classes.materials.index') ? 'active' : '' }}"
           href="{{ route('student.classes.materials.index', $class->class_id) }}">
            <i class="bi bi-folder-fill me-1"></i> Files
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('student.classes.members.index') ? 'active' : '' }}"
           href="{{ route('student.classes.members.index', $class->class_id) }}">
            <i class="bi bi-people-fill me-1"></i> Members
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('student.classes.scores') ? 'active' : '' }}"
           href="{{ route('student.classes.scores', $class->class_id) }}">
            <i class="bi bi-bar-chart-fill me-1"></i> My Scores
        </a>
    </li>
</ul>
