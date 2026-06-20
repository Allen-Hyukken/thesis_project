<div class="modal fade" id="postCourseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('teacher.classes.courses.post', $class->class_id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title font-bold">Post a Course to {{ $class->class_name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @if ($availableCourses->isEmpty())
                        <p class="text-muted mb-0">
                            You don't have any other published courses to post.
                            <a href="{{ route('teacher.courses.create') }}">Create one</a>, publish it, then come back here.
                        </p>
                    @else
                        <label class="form-label font-bold">Choose a published course</label>
                        <select name="course_id" class="form-control" required>
                            <option value="">Select a course...</option>
                            @foreach ($availableCourses as $course)
                                <option value="{{ $course->course_id }}">{{ $course->title }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    @if ($availableCourses->isNotEmpty())
                        <button type="submit" class="btn btn-primary font-bold">Post Course</button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
