<div class="offcanvas offcanvas-end" tabindex="-1" id="membersDrawer" style="width:420px;">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title font-bold">Class Members</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        @forelse ($members as $enrollment)
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-sm me-2">
                        <span class="d-flex align-items-center justify-content-center w-100 h-100 rounded-circle bg-primary text-white fw-bold" style="font-size:12px;">
                            {{ strtoupper(substr($enrollment->student->full_name ?? '?', 0, 1)) }}
                        </span>
                    </div>
                    <div>
                        <p class="font-bold mb-0" style="font-size:13px;">{{ $enrollment->student->full_name }}</p>
                        <p class="text-muted mb-0" style="font-size:11px;">{{ $enrollment->student->email }}</p>
                    </div>
                </div>
                <form action="{{ route('teacher.classes.members.kick', [$class->class_id, $enrollment->student->user_id]) }}" method="POST"
                      onsubmit="return confirm('Remove {{ $enrollment->student->full_name }} from this class?');">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-person-dash"></i>
                    </button>
                </form>
            </div>
        @empty
            <p class="text-muted" style="font-size:13px;">No students have joined yet. Share the class code: <strong>{{ $class->class_code }}</strong></p>
        @endforelse
    </div>
</div>
