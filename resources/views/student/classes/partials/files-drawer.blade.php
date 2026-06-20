<div class="offcanvas offcanvas-end" tabindex="-1" id="filesDrawer" style="width:420px;">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title font-bold">Class Files</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        @forelse ($class->materials as $material)
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <p class="font-bold mb-0" style="font-size:13px;">{{ $material->title }}</p>
                    <p class="text-muted mb-0" style="font-size:11px;">
                        {{ $material->original_filename }} • {{ $material->humanSize() }}
                    </p>
                </div>
                <a href="{{ route('student.classes.materials.download', [$class->class_id, $material->material_id]) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-download"></i>
                </a>
            </div>
        @empty
            <p class="text-muted" style="font-size:13px;">No files have been shared yet.</p>
        @endforelse
    </div>
</div>
