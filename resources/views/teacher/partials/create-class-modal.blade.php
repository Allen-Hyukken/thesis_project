<div class="modal fade" id="createClassModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('teacher.classes.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title font-bold">Create a New Class</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label font-bold">Class Name</label>
                        <input type="text" name="class_name"
                               class="form-control @error('class_name') is-invalid @enderror"
                               placeholder="e.g. BSIT 3-1" value="{{ old('class_name') }}">
                        @error('class_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-bold">Subject</label>
                        <input type="text" name="subject"
                               class="form-control @error('subject') is-invalid @enderror"
                               placeholder="e.g. Data Structures and Algorithms" value="{{ old('subject') }}">
                        @error('subject') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-bold">Description (optional)</label>
                        <textarea name="description" class="form-control" rows="3"
                                  placeholder="Short description of this class">{{ old('description') }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary font-bold">Create Class</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if ($errors->has('class_name') || $errors->has('subject'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            new bootstrap.Modal(document.getElementById('createClassModal')).show();
        });
    </script>
@endif
