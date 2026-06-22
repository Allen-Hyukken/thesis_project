@extends('layouts.app')

@section('title', 'Results & Analytics')

@section('sidebar-nav')
    @include('teacher.partials.sidebar-nav')
@endsection

@section('page-heading')
    <h3 class="mb-1">Results & Analytics</h3>
@endsection

@section('content')

    {{-- ═══════════════════════════════════════════════════════════════
         FR.1.7.1 — Class Overview
    ═══════════════════════════════════════════════════════════════ --}}
    <h5 class="font-bold mb-3">Class Overview</h5>

    @if ($classStats->isEmpty())
        <div class="card mb-4"><div class="card-body text-muted">No classes found.</div></div>
    @else
        <div class="row g-3 mb-4">
            @foreach ($classStats as $stat)
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h6 class="font-bold mb-3">{{ $stat['class_name'] }}</h6>
                            <div class="row text-center g-0">
                                <div class="col-4 border-end">
                                    <div class="fs-4 fw-bold text-primary">{{ $stat['enrollment_count'] }}</div>
                                    <small class="text-muted">Students</small>
                                </div>
                                <div class="col-4 border-end">
                                    <div class="fs-4 fw-bold text-success">{{ $stat['avg_quiz_score'] }}%</div>
                                    <small class="text-muted">Avg Quiz</small>
                                </div>
                                <div class="col-4">
                                    <div class="fs-4 fw-bold" style="color:#435ebe;">{{ $stat['avg_completion'] }}%</div>
                                    <small class="text-muted">Completion</small>
                                </div>
                            </div>
                            <hr class="my-3">
                            <small class="text-muted">{{ $stat['course_count'] }} course(s) posted</small>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0 font-bold">Quiz Scores &amp; Completion by Class</h6>
            </div>
            <div class="card-body">
                <canvas id="classChart" height="80"></canvas>
            </div>
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════
         FR.1.7.2 — Topic Engagement
    ═══════════════════════════════════════════════════════════════ --}}
    <h5 class="font-bold mb-3 mt-2">Topic Engagement</h5>

    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0 font-bold">Most Viewed Lessons</h6>
        </div>
        <div class="card-body">
            @if ($moduleEngagement->isEmpty())
                <p class="text-muted mb-0">
                    No lesson views recorded yet. Data appears here once students open a course.
                </p>
            @else
                <canvas id="moduleChart" height="120"></canvas>
            @endif
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
         FR.1.7.3 — Student Progress
    ═══════════════════════════════════════════════════════════════ --}}
    <h5 class="font-bold mb-3 mt-2">Student Progress</h5>

    <div class="card mb-4">
        <div class="card-body p-0">
            @if ($studentProgress->isEmpty())
                <div class="p-3 text-muted">
                    No progress data yet. Progress is recorded when students view course lessons.
                </div>
            @else
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                    <tr>
                        <th style="font-size:13px;">Student</th>
                        <th style="font-size:13px;">Course</th>
                        <th style="font-size:13px;">Progress</th>
                        <th style="font-size:13px;">Lessons</th>
                        <th style="font-size:13px;">Last Active</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($studentProgress as $progress)
                        <tr>
                            <td style="font-size:13px;">{{ $progress->student->full_name ?? '—' }}</td>
                            <td style="font-size:13px;">{{ $progress->course->title ?? '—' }}</td>
                            <td style="min-width:140px;">
                                <div class="progress mb-1" style="height:6px;">
                                    <div class="progress-bar" role="progressbar"
                                         style="width:{{ $progress->completion_pct }}%; background-color:#435ebe;">
                                    </div>
                                </div>
                                <small class="text-muted">{{ $progress->completion_pct }}%</small>
                            </td>
                            <td><small class="text-muted">{{ $progress->modules_completed }}/{{ $progress->total_modules }}</small></td>
                            <td><small class="text-muted">
                                    {{ $progress->last_accessed_at ? $progress->last_accessed_at->diffForHumans() : '—' }}
                                </small></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        (function () {

            // ── FR.1.7.1 Chart — Quiz Scores & Completion per Class ──────────────
            @if ($classStats->isNotEmpty())
            new Chart(document.getElementById('classChart'), {
                type: 'bar',
                data: {
                    labels: {!! $classStats->pluck('class_name')->toJson() !!},
                    datasets: [
                        {
                            label: 'Avg Quiz Score (%)',
                            data: {!! $classStats->pluck('avg_quiz_score')->toJson() !!},
                            backgroundColor: 'rgba(67, 94, 190, 0.75)',
                            borderRadius: 4,
                        },
                        {
                            label: 'Avg Completion (%)',
                            data: {!! $classStats->pluck('avg_completion')->toJson() !!},
                            backgroundColor: 'rgba(40, 167, 69, 0.75)',
                            borderRadius: 4,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    scales: { y: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%' } } },
                    plugins: { legend: { position: 'top' } }
                }
            });
            @endif

            // ── FR.1.7.2 Chart — Module Engagement ───────────────────────────────
            @if ($moduleEngagement->isNotEmpty())
            new Chart(document.getElementById('moduleChart'), {
                type: 'bar',
                data: {
                    labels: {!! $moduleEngagement->map(fn ($e) => $e->module->title ?? 'Unknown')->toJson() !!},
                    datasets: [
                        {
                            label: 'Total Views',
                            data: {!! $moduleEngagement->pluck('total_views')->toJson() !!},
                            backgroundColor: 'rgba(67, 94, 190, 0.75)',
                            borderRadius: 4,
                        },
                        {
                            label: 'Unique Students',
                            data: {!! $moduleEngagement->pluck('unique_students')->toJson() !!},
                            backgroundColor: 'rgba(255, 193, 7, 0.75)',
                            borderRadius: 4,
                        }
                    ]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } },
                    plugins: { legend: { position: 'top' } }
                }
            });
            @endif

        })();
    </script>
@endpush
