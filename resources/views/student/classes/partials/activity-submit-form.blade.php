{{-- activity-submit-form.blade.php — inline submit form --}}
<form action="{{ route('student.activities.submit', $activity->module_id) }}"
      method="POST" enctype="multipart/form-data">
    @csrf
    <div class="mb-2">
        <label class="tac-form-label">Your response</label>
        <textarea name="submission_text" class="form-control tac-textarea" rows="4"
                  placeholder="Type your answer here…">{{ old('submission_text', $sub->submission_text ?? '') }}</textarea>
    </div>
    <div class="mb-3">
        <label class="tac-form-label">Attach a file <span class="text-muted">(optional, max 50MB)</span></label>
        <div class="tac-dropzone" id="dropzone-{{ $activity->module_id }}">
            <i class="bi bi-cloud-upload fs-3 text-primary mb-1"></i>
            <div class="tac-dropzone-text">Drag & drop a file or <label for="file-{{ $activity->module_id }}" class="tac-browse-link">browse</label></div>
            <input type="file" name="file" id="file-{{ $activity->module_id }}" class="tac-file-input"
                   onchange="showFileName(this, 'dropzone-{{ $activity->module_id }}')">
            @if ($sub && $sub->file_original_name)
                <div class="tac-current-file mt-2">
                    <i class="bi bi-paperclip me-1"></i> Current: {{ $sub->file_original_name }}
                </div>
            @endif
        </div>
        <div class="tac-selected-file d-none mt-1" id="selected-{{ $activity->module_id }}"></div>
    </div>
    <button type="submit" class="btn btn-primary btn-sm px-4">
        <i class="bi bi-send me-1"></i> {{ $sub ? 'Resubmit' : 'Turn In' }}
    </button>
</form>
