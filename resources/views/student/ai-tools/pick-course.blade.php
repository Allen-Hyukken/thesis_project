@extends('layouts.app')

@section('title', $mode === 'flashcards' ? 'EDITH Flashcards' : 'EDITH Tutor Chat')

@section('sidebar-nav')
    @include('student.partials.sidebar-nav')
@endsection

@section('page-heading')
    <h3 class="mb-1">
        @if ($mode === 'flashcards')
            <i class="bi bi-layers-fill me-1"></i> EDITH Flashcards
        @else
            <i class="bi bi-chat-quote-fill me-1"></i> EDITH Tutor Chat
        @endif
    </h3>
@endsection

@section('content')

    <p class="text-muted" style="font-size:13px;">
        Pick a course to {{ $mode === 'flashcards' ? 'generate flashcards for' : 'chat with EDITH about' }}.
    </p>

    <div class="row g-3">
        @forelse ($courses as $course)
            @php $class = $course->postedClasses->first(); @endphp
            @if ($class)
                <div class="col-md-4 col-sm-6">
                    <a href="{{ route($mode === 'flashcards' ? 'student.classes.courses.flashcards' : 'student.classes.courses.ai-tutor', [$class->class_id, $course->course_id]) }}" class="text-decoration-none">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="font-bold mb-1">{{ $course->title }}</h6>
                                <span class="text-muted" style="font-size:12px;">{{ $class->class_name }}</span>
                            </div>
                        </div>
                    </a>
                </div>
            @endif
        @empty
            <p class="text-muted">You're not enrolled in any courses yet.</p>
        @endforelse
    </div>

@endsection
