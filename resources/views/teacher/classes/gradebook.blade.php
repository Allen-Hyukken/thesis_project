@extends('layouts.app')

@section('title', $class->class_name . ' — Gradebook')

@section('sidebar-nav')
    @include('teacher.partials.sidebar-nav')
@endsection

@section('page-heading')
    @include('teacher.classes.partials.class-header')
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/pages/gradebook-modern.css') }}">
@endpush

@section('content')

    @include('teacher.classes.partials.class-nav')

    @include('student.classes.partials.activity-styles')

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($class->postedCourses->isEmpty())
        <div class="gb2-empty">
            <i class="bi bi-bar-chart"></i>
            <h5>Nothing to grade yet</h5>
            <p>Post a course to this class to start seeing submissions here.</p>
        </div>
    @else
        @php
            // First pass: aggregate grading status across every course, for the overview chart.
            $overviewBuckets = ['done' => 0, 'pending' => 0, 'empty' => 0];

            foreach ($class->postedCourses as $c) {
                foreach ($c->activities as $activity) {
                    $subs = $gradebook['activitySubmissions'][$activity->module_id] ?? collect();
                    $ungraded = $subs->filter(fn($s) => ! $s->isGraded())->count();
                    if ($subs->isEmpty())      $overviewBuckets['empty']++;
                    elseif ($ungraded > 0)     $overviewBuckets['pending']++;
                    else                       $overviewBuckets['done']++;
                }
                foreach ($c->quizzes as $quiz) {
                    $subs = $gradebook['quizSubmissions'][$quiz->quiz_id] ?? collect();
                    $ungraded = $subs->filter(fn($s) => $s->needsReview())->count();
                    if ($subs->isEmpty())      $overviewBuckets['empty']++;
                    elseif ($ungraded > 0)     $overviewBuckets['pending']++;
                    else                       $overviewBuckets['done']++;
                }
                foreach ($c->exams as $exam) {
                    $subs = $gradebook['examSubmissions'][$exam->exam_id] ?? collect();
                    $ungraded = $subs->filter(fn($s) => $s->needsReview())->count();
                    if ($subs->isEmpty())      $overviewBuckets['empty']++;
                    elseif ($ungraded > 0)     $overviewBuckets['pending']++;
                    else                       $overviewBuckets['done']++;
                }
            }

            $overviewTotal = array_sum($overviewBuckets);
        @endphp

        {{-- Overview chart --}}
        <div class="gb2-overview-card mb-4">
            <div class="gb2-overview-chart-wrap">
                <canvas id="gb2OverviewChart" width="120" height="120"
                        data-counts="{{ json_encode($overviewBuckets) }}"></canvas>
                <div class="gb2-overview-center">
                    <span class="gb2-overview-number">{{ $overviewTotal }}</span>
                    <span class="gb2-overview-label">items</span>
                </div>
            </div>
            <div class="gb2-overview-legend">
                <div class="gb2-legend-row">
                    <span class="gb2-legend-dot" style="background:#16a34a;"></span>
                    Graded <strong>{{ $overviewBuckets['done'] }}</strong>
                </div>
                <div class="gb2-legend-row">
                    <span class="gb2-legend-dot" style="background:#d97706;"></span>
                    Needs review <strong>{{ $overviewBuckets['pending'] }}</strong>
                </div>
                <div class="gb2-legend-row">
                    <span class="gb2-legend-dot" style="background:#aab1c2;"></span>
                    No submissions <strong>{{ $overviewBuckets['empty'] }}</strong>
                </div>
            </div>
        </div>

        <div class="gb2-courses">
            @foreach ($class->postedCourses as $course)
                @php
                    $courseTotalSubs  = 0;
                    $courseGradedSubs = 0;
                    $items = collect();

                    foreach ($course->activities as $activity) {
                        $subs = $gradebook['activitySubmissions'][$activity->module_id] ?? collect();
                        $ungraded = $subs->filter(fn($s) => ! $s->isGraded())->count();
                        $courseTotalSubs  += $subs->count();
                        $courseGradedSubs += $subs->count() - $ungraded;
                        $items->push(['type' => 'activity', 'icon' => 'bi-clipboard-check', 'label' => 'Activity',
                            'title' => $activity->title, 'subs' => $subs, 'ungraded' => $ungraded, 'model' => $activity]);
                    }
                    foreach ($course->quizzes as $quiz) {
                        $subs = $gradebook['quizSubmissions'][$quiz->quiz_id] ?? collect();
                        $ungraded = $subs->filter(fn($s) => $s->needsReview())->count();
                        $courseTotalSubs  += $subs->count();
                        $courseGradedSubs += $subs->count() - $ungraded;
                        $items->push(['type' => 'quiz', 'icon' => 'bi-patch-question', 'label' => 'Quiz',
                            'title' => $quiz->title, 'subs' => $subs, 'ungraded' => $ungraded, 'model' => $quiz]);
                    }
                    foreach ($course->exams as $exam) {
                        $subs = $gradebook['examSubmissions'][$exam->exam_id] ?? collect();
                        $ungraded = $subs->filter(fn($s) => $s->needsReview())->count();
                        $courseTotalSubs  += $subs->count();
                        $courseGradedSubs += $subs->count() - $ungraded;
                        $items->push(['type' => 'exam', 'icon' => 'bi-file-text', 'label' => 'Exam',
                            'title' => $exam->title, 'subs' => $subs, 'ungraded' => $ungraded, 'model' => $exam]);
                    }

                    $pct = $courseTotalSubs > 0 ? round(($courseGradedSubs / $courseTotalSubs) * 100) : 0;

                    $courseBuckets = ['done' => 0, 'pending' => 0, 'empty' => 0];
                    foreach ($items as $it) {
                        if ($it['subs']->isEmpty())   $courseBuckets['empty']++;
                        elseif ($it['ungraded'] > 0)   $courseBuckets['pending']++;
                        else                            $courseBuckets['done']++;
                    }
                @endphp

                <div class="gb2-course">
                    <button type="button" class="gb2-course-head">
                        <i class="bi bi-chevron-right gb2-chevron"></i>
                        <span class="gb2-course-title">{{ $course->title }}</span>
                        <div class="gb2-progress-wrap">
                            <div class="gb2-progress">
                                <div class="gb2-progress-fill" style="width:{{ $pct }}%;"></div>
                            </div>
                            <span class="gb2-progress-label">
                                {{ $courseTotalSubs > 0 ? "{$courseGradedSubs}/{$courseTotalSubs} graded" : 'No submissions' }}
                            </span>
                        </div>
                    </button>

                    <div class="gb2-course-body">
                        @if ($items->isEmpty())
                            <p class="gb2-muted">This course has no activities, quizzes, or exams yet.</p>
                        @else
                            <div class="gb2-course-chart-row mb-3">
                                <canvas class="gb2-course-chart" width="56" height="56"
                                        data-counts="{{ json_encode($courseBuckets) }}"></canvas>
                                <div class="gb2-course-chart-legend">
                                    <span><span class="gb2-legend-dot" style="background:#16a34a;"></span>{{ $courseBuckets['done'] }} graded</span>
                                    <span><span class="gb2-legend-dot" style="background:#d97706;"></span>{{ $courseBuckets['pending'] }} needs review</span>
                                    <span><span class="gb2-legend-dot" style="background:#aab1c2;"></span>{{ $courseBuckets['empty'] }} no submissions</span>
                                </div>
                            </div>
                        @endif

                        @foreach ($items as $item)
                            <div class="gb2-item">
                                <button type="button" class="gb2-item-head">
                                    <i class="bi bi-chevron-right gb2-chevron-sm"></i>
                                    <i class="bi {{ $item['icon'] }} gb2-item-icon"></i>
                                    <span class="gb2-item-title">{{ $item['title'] }}</span>
                                    <span class="gb2-item-type">{{ $item['label'] }}</span>
                                    <span class="gb2-item-status {{ $item['subs']->isEmpty() ? 'is-empty' : ($item['ungraded'] > 0 ? 'is-pending' : 'is-done') }}">
                                        @if ($item['subs']->isEmpty())
                                            No submissions
                                        @elseif ($item['ungraded'] > 0)
                                            {{ $item['ungraded'] }} needs review
                                        @else
                                            All graded
                                        @endif
                                    </span>
                                </button>

                                <div class="gb2-item-body">
                                    @if ($item['type'] === 'activity')
                                        @include('teacher.classes.partials.gradebook-activity-section', ['activity' => $item['model'], 'subs' => $item['subs']])
                                    @else
                                        @include('teacher.classes.partials.gradebook-assessment-section', [
                                            'assessment' => $item['model'],
                                            'subs'       => $item['subs'],
                                            'type'       => $item['type'],
                                        ])
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

@push('scripts')
    @include('student.classes.partials.activity-scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script>
        document.querySelectorAll('.gb2-course-head').forEach(btn => {
            btn.addEventListener('click', () => {
                btn.closest('.gb2-course').classList.toggle('is-open');
            });
        });
        document.querySelectorAll('.gb2-item-head').forEach(btn => {
            btn.addEventListener('click', () => {
                btn.closest('.gb2-item').classList.toggle('is-open');
            });
        });
        const firstCourse = document.querySelector('.gb2-course');
        if (firstCourse) firstCourse.classList.add('is-open');

        function gb2RenderDonut(canvas) {
            if (!canvas) return;
            const counts = JSON.parse(canvas.dataset.counts || '{}');
            new Chart(canvas, {
                type: 'doughnut',
                data: {
                    labels: ['Graded', 'Needs review', 'No submissions'],
                    datasets: [{
                        data: [counts.done || 0, counts.pending || 0, counts.empty || 0],
                        backgroundColor: ['#16a34a', '#d97706', '#aab1c2'],
                        borderWidth: 0,
                    }],
                },
                options: {
                    cutout: '70%',
                    responsive: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { enabled: true },
                    },
                },
            });
        }

        gb2RenderDonut(document.getElementById('gb2OverviewChart'));
        document.querySelectorAll('.gb2-course-chart').forEach(gb2RenderDonut);
    </script>
@endpush
