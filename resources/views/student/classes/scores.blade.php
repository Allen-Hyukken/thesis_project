@extends('layouts.app')

@section('title', $class->class_name . ' — My Scores')

@section('sidebar-nav')
    @include('student.partials.sidebar-nav')
@endsection

@section('page-heading')
    @include('student.classes.partials.class-header')
@endsection

@section('content')

    @include('student.classes.partials.class-nav')

    @if ($class->postedCourses->isEmpty())
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-bar-chart fs-1 text-muted mb-3 d-block"></i>
                <h5 class="font-bold">No scores yet</h5>
                <p class="text-muted mb-0">Your teacher hasn't posted any courses to this class yet.</p>
            </div>
        </div>
    @else

        @php
            $buckets = ['done' => 0, 'pending' => 0, 'empty' => 0];
            foreach ($class->postedCourses as $course) {
                foreach ($course->activities as $activity) {
                    $sub = $myActivitySubmissions[$activity->module_id] ?? null;
                    if (!$sub) $buckets['empty']++;
                    elseif ($sub->isGraded()) $buckets['done']++;
                    else $buckets['pending']++;
                }
                foreach ($course->quizzes as $quiz) {
                    $sub = $myQuizSubmissions[$quiz->quiz_id] ?? null;
                    if (!$sub) $buckets['empty']++;
                    elseif (!$sub->needsReview()) $buckets['done']++;
                    else $buckets['pending']++;
                }
                foreach ($course->exams as $exam) {
                    $sub = $myExamSubmissions[$exam->exam_id] ?? null;
                    if (!$sub) $buckets['empty']++;
                    elseif (!$sub->needsReview()) $buckets['done']++;
                    else $buckets['pending']++;
                }
            }
            $total = array_sum($buckets);
        @endphp

        {{-- Overview chart --}}
        <div class="gb2-overview-card mb-4">
            <div class="gb2-overview-chart-wrap">
                <canvas id="scoresOverviewChart" width="120" height="120"
                        data-counts="{{ json_encode($buckets) }}"></canvas>
                <div class="gb2-overview-center">
                    <span class="gb2-overview-number">{{ $total }}</span>
                    <span class="gb2-overview-label">items</span>
                </div>
            </div>
            <div class="gb2-overview-legend">
                <div class="gb2-legend-row">
                    <span class="gb2-legend-dot" style="background:#16a34a;"></span>
                    Graded <strong>{{ $buckets['done'] }}</strong>
                </div>
                <div class="gb2-legend-row">
                    <span class="gb2-legend-dot" style="background:#d97706;"></span>
                    Awaiting grade <strong>{{ $buckets['pending'] }}</strong>
                </div>
                <div class="gb2-legend-row">
                    <span class="gb2-legend-dot" style="background:#aab1c2;"></span>
                    Not submitted <strong>{{ $buckets['empty'] }}</strong>
                </div>
            </div>
        </div>

        {{-- Course sections --}}
        <div class="sc2-courses">
            @foreach ($class->postedCourses as $course)
                @php
                    $cDone = 0; $cPending = 0; $cEmpty = 0;
                    foreach ($course->activities as $a) {
                        $s = $myActivitySubmissions[$a->module_id] ?? null;
                        if (!$s) $cEmpty++; elseif ($s->isGraded()) $cDone++; else $cPending++;
                    }
                    foreach ($course->quizzes as $q) {
                        $s = $myQuizSubmissions[$q->quiz_id] ?? null;
                        if (!$s) $cEmpty++; elseif (!$s->needsReview()) $cDone++; else $cPending++;
                    }
                    foreach ($course->exams as $e) {
                        $s = $myExamSubmissions[$e->exam_id] ?? null;
                        if (!$s) $cEmpty++; elseif (!$s->needsReview()) $cDone++; else $cPending++;
                    }
                    $cTotal = $cDone + $cPending + $cEmpty;
                    $cPct   = $cTotal > 0 ? round(($cDone / $cTotal) * 100) : 0;
                @endphp

                <div class="sc2-course mb-3">
                    {{-- Course header --}}
                    <div class="sc2-course-head" onclick="this.closest('.sc2-course').classList.toggle('is-open')">
                        <div class="d-flex align-items-center gap-3 flex-grow-1 min-width-0">
                            <i class="bi bi-chevron-right sc2-chevron"></i>
                            <div class="sc2-course-icon">
                                <i class="bi bi-journal-bookmark-fill"></i>
                            </div>
                            <div class="min-width-0">
                                <div class="sc2-course-title">{{ $course->title }}</div>
                                <div class="sc2-course-sub">
                                    {{ $cTotal }} item{{ $cTotal !== 1 ? 's' : '' }}
                                    @if ($cPending > 0)
                                        &bull; <span style="color:var(--tup-warning,#d97706);">{{ $cPending }} awaiting grade</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="sc2-progress-wrap">
                            <div class="sc2-progress">
                                <div class="sc2-progress-fill" style="width:{{ $cPct }}%;"></div>
                            </div>
                            <span class="sc2-progress-label">{{ $cDone }}/{{ $cTotal }}</span>
                        </div>
                    </div>

                    {{-- Course body --}}
                    <div class="sc2-course-body">
                        @if ($course->activities->isEmpty() && $course->quizzes->isEmpty() && $course->exams->isEmpty())
                            <p class="text-muted sc2-empty">Nothing to take in this course yet.</p>
                        @endif

                        @foreach ($course->activities as $activity)
                            @php $sub = $myActivitySubmissions[$activity->module_id] ?? null; @endphp
                            <div class="sc2-item">
                                <div class="sc2-item-left">
                                    <span class="sc2-item-icon sc2-icon-activity"><i class="bi bi-clipboard-check"></i></span>
                                    <div>
                                        <div class="sc2-item-title">{{ $activity->title }}</div>
                                        <div class="sc2-item-type">Activity</div>
                                    </div>
                                </div>
                                <div>
                                    @if (!$sub)
                                        <span class="sc2-badge sc2-badge-empty">Not submitted</span>
                                    @elseif ($sub->isGraded())
                                        <span class="sc2-badge sc2-badge-done">{{ $sub->score }}/{{ $activity->points }}</span>
                                    @else
                                        <span class="sc2-badge sc2-badge-pending">Awaiting grade</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                        @foreach ($course->quizzes as $quiz)
                            @php $sub = $myQuizSubmissions[$quiz->quiz_id] ?? null; @endphp
                            <div class="sc2-item">
                                <div class="sc2-item-left">
                                    <span class="sc2-item-icon sc2-icon-quiz"><i class="bi bi-patch-question"></i></span>
                                    <div>
                                        <div class="sc2-item-title">{{ $quiz->title }}</div>
                                        <div class="sc2-item-type">Quiz</div>
                                    </div>
                                </div>
                                <div>
                                    @if (!$sub)
                                        <a href="{{ route('student.quizzes.take', $quiz->quiz_id) }}" class="sc2-badge sc2-badge-take">Take Quiz <i class="bi bi-arrow-right ms-1"></i></a>
                                    @else
                                        <span class="sc2-badge {{ $sub->needsReview() ? 'sc2-badge-pending' : 'sc2-badge-done' }}">
                                            {{ $sub->score }}/{{ $sub->max_score }}{{ $sub->needsReview() ? ' (pending)' : '' }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                        @foreach ($course->exams as $exam)
                            @php $sub = $myExamSubmissions[$exam->exam_id] ?? null; @endphp
                            <div class="sc2-item">
                                <div class="sc2-item-left">
                                    <span class="sc2-item-icon sc2-icon-exam"><i class="bi bi-file-text"></i></span>
                                    <div>
                                        <div class="sc2-item-title">{{ $exam->title }}</div>
                                        <div class="sc2-item-type">Exam</div>
                                    </div>
                                </div>
                                <div>
                                    @if (!$sub)
                                        <a href="{{ route('student.exams.take', $exam->exam_id) }}" class="sc2-badge sc2-badge-take">Take Exam <i class="bi bi-arrow-right ms-1"></i></a>
                                    @else
                                        <span class="sc2-badge {{ $sub->needsReview() ? 'sc2-badge-pending' : 'sc2-badge-done' }}">
                                            {{ $sub->score }}/{{ $sub->max_score }}{{ $sub->needsReview() ? ' (pending)' : '' }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

    @endif

@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/pages/gradebook-modern.css') }}">
    <style>
        /* ── Scores page layout ── */
        .sc2-courses{ display:flex; flex-direction:column; gap:.75rem; }

        .sc2-course{
            background: var(--surface-card,#fff);
            border: 1px solid var(--surface-border,#e7eaf3);
            border-radius: var(--radius-lg,1.25rem);
            overflow: hidden;
        }

        .sc2-course-head{
            display: flex;
            align-items: center;
            gap:1rem;
            padding: 1.1rem 1.4rem;
            cursor: pointer;
            user-select: none;
            transition: background-color .15s ease;
        }
        .sc2-course-head:hover{ background: var(--surface,#f6f8fc); }

        .sc2-chevron{
            color: var(--ink-300,#aab1c2);
            font-size: .8rem;
            transition: transform .18s ease;
            flex-shrink: 0;
        }
        .sc2-course.is-open .sc2-chevron{ transform: rotate(90deg); }

        .sc2-course-icon{
            width: 38px; height: 38px; border-radius: .65rem;
            background: var(--tup-primary-light,#eef1fb);
            display: flex; align-items: center; justify-content: center;
            color: var(--tup-primary,#435ebe);
            flex-shrink: 0;
        }

        .sc2-course-title{
            font-weight: 700;
            font-size: .95rem;
            color: var(--ink-900,#1c2433);
        }
        .sc2-course-sub{
            font-size: .75rem;
            color: var(--ink-500,#6b7385);
            margin-top: .1rem;
        }

        .sc2-progress-wrap{
            display: flex; align-items: center; gap: .6rem;
            width: 180px; flex-shrink: 0;
        }
        .sc2-progress{
            flex:1; height:6px; border-radius:999px;
            background: var(--surface,#f6f8fc);
            overflow: hidden;
        }
        .sc2-progress-fill{
            height:100%; border-radius:999px;
            background: linear-gradient(135deg,var(--tup-primary,#435ebe),var(--tup-primary-dark,#34488f));
            transition: width .3s ease;
        }
        .sc2-progress-label{
            font-size:.75rem; font-weight:600;
            color:var(--ink-500,#6b7385); white-space:nowrap;
        }

        .sc2-course-body{
            display: none;
            border-top: 1px solid var(--surface-border,#e7eaf3);
            padding: .75rem 1.4rem 1rem;
        }
        .sc2-course.is-open .sc2-course-body{ display:block; }

        .sc2-empty{ font-size:.88rem; color:var(--ink-500,#6b7385); padding:.5rem 0; }

        /* ── Score item row ── */
        .sc2-item{
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .7rem 0;
            border-bottom: 1px solid var(--surface-border,#e7eaf3);
        }
        .sc2-item:last-child{ border-bottom: none; }
        .sc2-item-left{ display:flex; align-items:center; gap:.75rem; }

        .sc2-item-icon{
            width:32px; height:32px; border-radius:.5rem;
            display:flex; align-items:center; justify-content:center;
            font-size:.85rem; flex-shrink:0;
        }
        .sc2-icon-activity{ background:var(--tup-primary-light,#eef1fb); color:var(--tup-primary,#435ebe); }
        .sc2-icon-quiz{ background:var(--tup-info-light,#e8f7ff); color:var(--tup-info,#0ea5e9); }
        .sc2-icon-exam{ background:var(--tup-accent-light,#fdeceb); color:var(--tup-accent,#c0392b); }

        .sc2-item-title{ font-size:.88rem; font-weight:600; color:var(--ink-900,#1c2433); }
        .sc2-item-type{ font-size:.72rem; color:var(--ink-500,#6b7385); text-transform:uppercase; letter-spacing:.05em; margin-top:.1rem; }

        /* ── Badges ── */
        .sc2-badge{
            display:inline-flex; align-items:center;
            font-size:.78rem; font-weight:700;
            padding:.3rem .8rem; border-radius:999px;
            white-space:nowrap; text-decoration:none;
        }
        .sc2-badge-done{ background:var(--tup-success-light,#e9f9ef); color:var(--tup-success,#16a34a); }
        .sc2-badge-pending{ background:var(--tup-warning-light,#fff6e6); color:var(--tup-warning,#d97706); }
        .sc2-badge-empty{ background:var(--surface-border,#e7eaf3); color:var(--ink-500,#6b7385); }
        .sc2-badge-take{
            background: linear-gradient(135deg,var(--tup-primary,#435ebe),var(--tup-primary-dark,#34488f));
            color:#fff; box-shadow:0 4px 12px -4px rgba(67,94,190,.45);
        }
        .sc2-badge-take:hover{ color:#fff; box-shadow:0 6px 16px -4px rgba(67,94,190,.6); }

        @media(max-width:576px){
            .sc2-progress-wrap{ display:none; }
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script>
        (function () {
            const canvas = document.getElementById('scoresOverviewChart');
            if (!canvas) return;
            const counts = JSON.parse(canvas.dataset.counts || '{}');
            new Chart(canvas, {
                type: 'doughnut',
                data: {
                    labels: ['Graded', 'Awaiting grade', 'Not submitted'],
                    datasets: [{
                        data: [counts.done || 0, counts.pending || 0, counts.empty || 0],
                        backgroundColor: ['#16a34a', '#d97706', '#aab1c2'],
                        borderWidth: 0,
                    }],
                },
                options: {
                    cutout: '70%',
                    responsive: false,
                    plugins: { legend:{ display:false }, tooltip:{ enabled:true } },
                },
            });

            // Open first course by default
            const first = document.querySelector('.sc2-course');
            if (first) first.classList.add('is-open');
        })();
    </script>
@endpush
