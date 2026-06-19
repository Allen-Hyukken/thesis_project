<div class="modal fade" id="addLessonModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('teacher.courses.modules.store', $course->course_id) }}" method="POST">
                @csrf
                <input type="hidden" name="item_type" value="lesson">
                <input type="hidden" name="ai_generated" id="new-lesson-ai-generated" value="0">

                <div class="modal-header">
                    <h5 class="modal-title font-bold">Add Lesson / Topic</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label font-bold">Title</label>
                        <input type="text" name="title" id="new-lesson-title"
                               class="form-control @error('title') is-invalid @enderror" required
                               value="{{ old('title') }}">
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <button type="button" class="btn btn-outline-primary btn-sm mb-2" onclick="generateNewLessonContent()">
                        <i class="bi bi-stars me-1"></i> Generate content with AI
                    </button>
                    <span id="new-lesson-status" class="text-muted ms-2" style="font-size:12px;"></span>

                    <div class="mb-1">
                        <label class="form-label font-bold">Content</label>
                        <textarea name="content" id="new-lesson-content" class="form-control" rows="8" required>{{ old('content') }}</textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary font-bold">Save Lesson</button>
                </div>
            </form>
        </div>
    </div>
</div>
