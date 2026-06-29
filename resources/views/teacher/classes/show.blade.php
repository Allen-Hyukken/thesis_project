@extends('layouts.app')

@section('title', $class->class_name)

@section('sidebar-nav')
    @include('teacher.partials.sidebar-nav')
@endsection

@section('page-heading')
    @include('teacher.classes.partials.class-header')
@endsection

@section('content')

    @include('teacher.classes.partials.class-nav')

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                <p class="mb-0">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="font-bold mb-0">Posted Courses</h5>
        <button type="button" class="btn btn-primary font-bold" data-bs-toggle="modal" data-bs-target="#postCourseModal">
            <i class="bi bi-plus-circle me-1"></i> Post a Course
        </button>
    </div>

    <div class="row">
        @forelse ($class->postedCourses as $course)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card mb-3 h-100">
                    <div class="card-body d-flex flex-column">
                        <h6 class="font-bold mb-1">{{ $course->title }}</h6>
                        @if ($course->description)
                            <p class="text-muted mb-2" style="font-size:13px;">{{ \Illuminate\Support\Str::limit($course->description, 80) }}</p>
                        @endif
                        <div class="d-flex gap-3 text-muted mb-3" style="font-size:12px;">
                            <span><i class="bi bi-journal-text"></i> {{ $course->lessons->count() }}</span>
                            <span><i class="bi bi-clipboard-check"></i> {{ $course->activities->count() }}</span>
                            <span><i class="bi bi-patch-question"></i> {{ $course->quizzes->count() }}</span>
                            <span><i class="bi bi-file-text"></i> {{ $course->exams->count() }}</span>
                        </div>
                        <div class="d-flex gap-2 mt-auto">
                            <a href="{{ route('teacher.courses.show', $course->course_id) }}" class="btn btn-outline-primary btn-sm flex-grow-1 font-bold">
                                Manage
                            </a>
                            <form action="{{ route('teacher.classes.courses.unpost', [$class->class_id, $course->course_id]) }}"
                                  method="POST" class="unpost-course-form">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="course_title" value="{{ $course->title }}">
                                <button type="submit" class="btn btn-outline-danger btn-sm font-bold" style="white-space:nowrap;">
                                    <i class="bi bi-x-lg me-1"></i> Remove
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-journal-plus fs-1 text-muted mb-3 d-block"></i>
                        <h5 class="font-bold">No courses posted yet</h5>
                        <p class="text-muted mb-0">Post one of your published courses so students in this class can take it.</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    @include('teacher.classes.partials.post-course-modal')
    @push('scripts')
        <script>
            document.querySelectorAll('.unpost-course-form').forEach(form => {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    const title = this.querySelector('[name="course_title"]').value;
                    Swal.fire({
                        title: 'Remove course?',
                        text: `Remove "${title}" from this class? Students will lose access.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, remove',
                    }).then(r => { if (r.isConfirmed) this.submit(); });
                });
            });
        </script>
    @endpush
@endsection
