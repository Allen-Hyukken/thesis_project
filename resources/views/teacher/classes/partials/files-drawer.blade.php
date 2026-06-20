<div class="offcanvas offcanvas-end" tabindex="-1" id="filesDrawer" style="width:420px;">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title font-bold">Class Files</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">

        <form action="{{ route('teacher.classes.materials.store', $class->class_id) }}" method="POST" enctype="multipart/form-data" class="mb-4">
            @csrf
            <label class="form-label font-bold" style="font-size:13px;">Upload a file</label>
            <input type="text" name="title" class="form-control mb-2" placeholder="Title (e.g. Week 3 Reading)" required>
            <input type="file" name="file" class="form-control mb-2" required>
            <button type="submit" class="btn btn-primary btn-sm w-100 font-bold">
                <i class="bi bi-upload me-1"></i> Upload
            </button>
            <p class="text-muted mt-1 mb-0" style="font-size:11px;">Max 50MB. Any file type.</p>
        </form>

        <hr>

        @forelse ($class->materials as $material)
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <p class="font-bold mb-0" style="font-size:13px;">{{ $material->title }}</p>
                    <p class="text-muted mb-0" style="font-size:11px;">
                        {{ $material->original_filename }} • {{ $material->humanSize() }}
                    </p>
                </div>
                <div class="d-flex gap-1">
                    <a href="{{ route('teacher.classes.materials.download', [$class->class_id, $material->material_id]) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-download"></i>
                    </a>
                    <form action="{{ route('teacher.classes.materials.destroy', [$class->class_id, $material->material_id]) }}" method="POST"
                          onsubmit="return confirm('Delete this file?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <p class="text-muted" style="font-size:13px;">No files uploaded yet.</p>
        @endforelse
    </div>
</div>
