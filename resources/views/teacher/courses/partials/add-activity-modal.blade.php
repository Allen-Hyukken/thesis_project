<div class="modal fade" id="addActivityModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('teacher.courses.modules.store', $course->course_id) }}" method="POST">
                @csrf
                <input type="hidden" name="item_type" value="activity">
                <input type="hidden" name="ai_generated" id="new-activity-ai-generated" value="0">

                <div class="modal-header">
                    <h5 class="modal-title font-bold">Add Activity</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label font-bold">Topic this activity relates to (for AI context)</label>
                        <div class="input-group">
                            <input type="text" id="new-activity-topic" class="form-control" placeholder="e.g. Linked Lists">
                            <button type="button" class="btn btn-outline-primary" onclick="generateNewActivity()">
                                <i class="bi bi-stars me-1"></i> Generate with AI
                            </button>
                        </div>
                        <span id="new-activity-status" class="text-muted" style="font-size:12px;"></span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label font-bold">Title</label>
                        <input type="text" name="title" id="new-activity-title"
                               class="form-control @error('title') is-invalid @enderror" required
                               value="{{ old('title') }}">
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label font-bold">Type</label>
                            <select name="activity_type" id="new-activity-type" class="form-control">
                                <option value="assignment">Assignment</option>
                                <option value="discussion">Discussion</option>
                                <option value="project">Project</option>
                                <option value="reflection">Reflection</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label font-bold">Points</label>
                            <input type="number" name="points" id="new-activity-points" class="form-control" min="1" max="100" value="10">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label font-bold">Due (optional)</label>
                            <input type="datetime-local" name="due_at" class="form-control">
                        </div>
                    </div>

                    <div class="mb-1">
                        <label class="form-label font-bold">Instructions</label>
                        <textarea name="content" id="new-activity-content" class="form-control" rows="6" required>{{ old('content') }}</textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary font-bold">Save Activity</button>
                </div>
            </form>
        </div>
    </div>
</div>
