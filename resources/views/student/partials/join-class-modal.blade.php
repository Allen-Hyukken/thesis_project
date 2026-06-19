<div class="modal fade" id="joinClassModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('student.classes.join') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title font-bold">Join a Class</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label font-bold">Class Code</label>
                    <input type="text" name="class_code"
                           class="form-control text-uppercase @error('class_code') is-invalid @enderror"
                           placeholder="e.g. A1B2C3" maxlength="10" value="{{ old('class_code') }}">
                    @error('class_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <p class="text-muted mt-2 mb-0" style="font-size:12px;">Ask your teacher for the class code.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary font-bold">Join Class</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if ($errors->has('class_code'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            new bootstrap.Modal(document.getElementById('joinClassModal')).show();
        });
    </script>
@endif
