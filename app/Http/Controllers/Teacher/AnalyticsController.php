<?php

namespace App\Http\Controllers\Teacher;
use App\Http\Controllers\Controller;

use App\Models\ClassEnrollment;
use App\Models\ClassRoom;
use App\Models\Course;
use App\Models\ModuleEngagement;
use App\Models\QuizSubmission;
use App\Models\StudentCourseProgress;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index()
    {
        $teacherId = Auth::id();
        $courseIds = Course::where('teacher_id', $teacherId)->pluck('course_id');

        // ── FR.1.7.1 — Per-class enrollment, avg quiz score, completion ───
        $classes = ClassRoom::where('teacher_id', $teacherId)
            ->withCount(['enrollments as enrollment_count' => fn ($q) => $q->where('status', 'active')])
            ->with('postedCourses')
            ->get();

        $classStats = $classes->map(function ($class) {
            $enrolledStudentIds = ClassEnrollment::where('class_id', $class->class_id)
                ->where('status', 'active')
                ->pluck('student_id');

            $classCourseIds = $class->postedCourses->pluck('course_id');

            $avgQuizScore = 0;
            if ($classCourseIds->isNotEmpty() && $enrolledStudentIds->isNotEmpty()) {
                $avgQuizScore = QuizSubmission::whereIn('student_id', $enrolledStudentIds)
                    ->whereHas('quiz', fn ($q) => $q->whereIn('course_id', $classCourseIds))
                    ->selectRaw('AVG(score / NULLIF(max_score, 0) * 100) as avg_pct')
                    ->value('avg_pct') ?? 0;
            }

            $avgCompletion = 0;
            if ($classCourseIds->isNotEmpty() && $enrolledStudentIds->isNotEmpty()) {
                $avgCompletion = StudentCourseProgress::whereIn('student_id', $enrolledStudentIds)
                    ->whereIn('course_id', $classCourseIds)
                    ->avg('completion_pct') ?? 0;
            }

            return [
                'class_id'         => $class->class_id,
                'class_name'       => $class->class_name,
                'enrollment_count' => $class->enrollment_count,
                'course_count'     => $class->postedCourses->count(),
                'avg_quiz_score'   => round($avgQuizScore, 1),
                'avg_completion'   => round($avgCompletion, 1),
            ];
        });

        // ── FR.1.7.2 — Topic-level engagement (top 10 most viewed lessons) ─
        $moduleEngagement = ModuleEngagement::whereHas('module', fn ($q) => $q->whereIn('course_id', $courseIds))
            ->with('module')
            ->select(
                'module_id',
                DB::raw('SUM(view_count) as total_views'),
                DB::raw('COUNT(DISTINCT student_id) as unique_students')
            )
            ->groupBy('module_id')
            ->orderByDesc('total_views')
            ->take(10)
            ->get();

        // ── FR.1.7.3 — Individual student progress ─────────────────────────
        $studentProgress = StudentCourseProgress::whereIn('course_id', $courseIds)
            ->with(['student', 'course'])
            ->orderByDesc('last_accessed_at')
            ->get();

        return view('teacher.analytics', compact(
            'classStats',
            'moduleEngagement',
            'studentProgress'
        ));
    }
}
