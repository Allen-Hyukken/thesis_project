<div class="offcanvas offcanvas-end" tabindex="-1" id="membersDrawer" style="width:420px;">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title font-bold">Class Members</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
            <div class="avatar avatar-sm me-2">
                <span class="d-flex align-items-center justify-content-center w-100 h-100 rounded-circle bg-danger text-white fw-bold" style="font-size:12px;">
                    {{ strtoupper(substr($class->teacher->full_name ?? '?', 0, 1)) }}
                </span>
            </div>
            <div>
                <p class="font-bold mb-0" style="font-size:13px;">{{ $class->teacher->full_name }}</p>
                <p class="text-muted mb-0" style="font-size:11px;">Teacher</p>
            </div>
        </div>

        @forelse ($members as $enrollment)
            <div class="d-flex align-items-center mb-3">
                <div class="avatar avatar-sm me-2">
                    <span class="d-flex align-items-center justify-content-center w-100 h-100 rounded-circle bg-primary text-white fw-bold" style="font-size:12px;">
                        {{ strtoupper(substr($enrollment->student->full_name ?? '?', 0, 1)) }}
                    </span>
                </div>
                <div>
                    <p class="font-bold mb-0" style="font-size:13px;">{{ $enrollment->student->full_name }}</p>
                    <p class="text-muted mb-0" style="font-size:11px;">Student</p>
                </div>
            </div>
        @empty
            <p class="text-muted" style="font-size:13px;">No other students have joined yet.</p>
        @endforelse
    </div>
</div>
