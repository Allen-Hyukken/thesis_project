@extends('layouts.app')

@section('title', $exam->title)

@section('sidebar-nav')
    @include('student.partials.sidebar-nav')
@endsection

@section('page-heading')
    <h3>{{ $exam->title }}</h3>
@endsection

@section('content')

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($submission)
        {{-- ===================== READ-ONLY RESULT ===================== --}}
        <div class="alert {{ $submission->needsReview() ? 'alert-warning' : 'alert-success' }}">
            <strong>Score: {{ $submission->score }} / {{ $submission->max_score }}</strong>
            @if ($submission->needsReview())
                <span class="ms-2">Some open-ended answers are still awaiting your teacher's review.</span>
            @endif
        </div>

        @foreach ($exam->questions as $question)
            @php $answer = $submission->answers->firstWhere('question_id', $question->question_id); @endphp
            <div class="card mb-2">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <p class="font-bold mb-0">{{ $loop->iteration }}. {{ $question->question_text }}</p>
                        <span class="badge bg-light text-dark border">{{ $question->points }} pts</span>
                    </div>

                    @if ($question->question_type === 'multiple_choice')
                        @foreach ($question->choices as $choice)
                            <p class="mb-1" style="font-size:14px;">
                                @if ($answer && $answer->answer_text === $choice->choice_text)
                                    <i class="bi bi-check-circle-fill {{ $answer->is_correct ? 'text-success' : 'text-danger' }}"></i>
                                @else
                                    <i class="bi bi-circle text-muted"></i>
                                @endif
                                {{ $choice->choice_label }}. {{ $choice->choice_text }}
                            </p>
                        @endforeach
                        <p class="text-muted mb-0 mt-1" style="font-size:12px;">
                            {{ $answer && $answer->is_correct ? 'Correct' : 'Incorrect' }} — {{ $answer->points_earned ?? 0 }} / {{ $question->points }} pts
                        </p>
                    @else
                        <p class="text-muted mb-1" style="font-size:13px;">Your answer:</p>
                        <p class="mb-1" style="font-size:14px;">{{ $answer->answer_text ?? '(no answer)' }}</p>
                        @if ($answer && $answer->points_earned !== null)
                            <span class="badge bg-light text-dark border">{{ $answer->points_earned }} / {{ $question->points }} pts</span>
                        @else
                            <span class="badge bg-warning text-dark">Awaiting review</span>
                        @endif
                    @endif
                </div>
            </div>
        @endforeach
    @else
        {{-- ===================== TAKE FORM ===================== --}}
        <div class="alert alert-info">
            You have <strong>one attempt</strong> at this exam. Review your answers before submitting.
        </div>

        <form action="{{ route('student.exams.submit', $exam->exam_id) }}" method="POST">
            @csrf

            @foreach ($exam->questions as $question)
                <div class="card mb-2">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <p class="font-bold mb-0">{{ $loop->iteration }}. {{ $question->question_text }}</p>
                            <span class="badge bg-light text-dark border">{{ $question->points }} pts</span>
                        </div>

                        @if ($question->question_type === 'multiple_choice')
                            @foreach ($question->choices as $choice)
                                <div class="form-check mb-1">
                                    <input class="form-check-input" type="radio"
                                           name="answers[{{ $question->question_id }}]"
                                           id="choice-{{ $choice->choice_id }}"
                                           value="{{ $choice->choice_id }}" required>
                                    <label class="form-check-label" for="choice-{{ $choice->choice_id }}">
                                        {{ $choice->choice_label }}. {{ $choice->choice_text }}
                                    </label>
                                </div>
                            @endforeach
                        @else
                            <textarea name="answers[{{ $question->question_id }}]" class="form-control" rows="3" required></textarea>
                        @endif
                    </div>
                </div>
            @endforeach

            <button type="submit" class="btn btn-primary btn-lg font-bold"
                    onclick="return confirm('Submit your answers? You only get one attempt.');">
                <i class="bi bi-check-circle me-1"></i> Submit Exam
            </button>
        </form>
    @endif

@endsection
