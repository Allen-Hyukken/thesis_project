@extends('layouts.app')

@section('title', $class->class_name)

@section('sidebar-nav')
    @include('teacher.partials.sidebar-nav')
@endsection

@section('page-heading')
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
        <div>
            <h3 class="mb-1">{{ $class->class_name }}</h3>
            <span class="badge bg-light text-dark border" style="letter-spacing:1px;">{{ $class->class_code }}</span>
            <span class="text-muted ms-2" style="font-size:13px;">{{ $class->subject }}</span>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-light font-bold" data-bs-toggle="offcanvas" data-bs-target="#filesDrawer">
                <i class="bi bi-folder-fill me-1"></i> Files
            </button>
            <button class="btn btn-light font-bold" data-bs-toggle="offcanvas" data-bs-target="#membersDrawer">
                <i class="bi bi-people-fill me-1"></i> Members
                <span class="badge bg-secondary">{{ $members->count() }}</span>
            </button>
            <button class="btn btn-light font-bold" data-bs-toggle="offcanvas" data-bs-target="#gradebookDrawer">
                <i class="bi bi-bar-chart-fill me-1"></i> Gradebook
            </button>
        </div>
    </div>
@endsection

@section('content')

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
                <div class="card mb-3">
                    <div class="card-body">
                        <h6 class="font-bold mb-1">{{ $course->title }}</h6>
                        <div class="d-flex gap-3 text-muted mb-3" style="font-size:12px;">
                            <span><i class="bi bi-journal-text"></i> {{ $course->lessons->count() }}</span>
                            <span><i class="bi bi-clipboard-check"></i> {{ $course->activities->count() }}</span>
                            <span><i class="bi bi-patch-question"></i> {{ $course->quizzes->count() }}</span>
                            <span><i class="bi bi-file-text"></i> {{ $course->exams->count() }}</span>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('teacher.courses.show', $course->course_id) }}" class="btn btn-outline-primary btn-sm flex-grow-1 font-bold">
                                Manage
                            </a>
                            <form action="{{ route('teacher.classes.courses.unpost', [$class->class_id, $course->course_id]) }}" method="POST"
                                  onsubmit="return confirm('Remove this course from the class? Students will lose access.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                    <i class="bi bi-x-lg"></i>
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
    @include('teacher.classes.partials.files-drawer')
    @include('teacher.classes.partials.members-drawer')
    @include('teacher.classes.partials.gradebook-drawer')

@endsection

@push('scripts')
<script>
    // Lets the dashboard's "Needs Grading" links jump straight into the drawer.
    if (window.location.hash === '#gradebook') {
        document.addEventListener('DOMContentLoaded', function () {
            new bootstrap.Offcanvas(document.getElementById('gradebookDrawer')).show();
        });
    }
</script>
@endpush
