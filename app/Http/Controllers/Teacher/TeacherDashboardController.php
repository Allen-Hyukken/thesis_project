<?php

namespace App\Http\Controllers\Teacher;
use App\Http\Controllers\Controller;

use App\Models\ActivitySubmission;
use App\Models\ClassEnrollment;
use App\Models\ClassRoom;
use App\Models\Course;
use App\Models\ExamSubmission;
use App\Models\QuizSubmission;
use Illuminate\Support\Facades\Auth;

class TeacherDashboardController extends Controller
{
    public function index()
    {
        $teacherId = Auth::id();

        $activeClassesCount = ClassRoom::where('teacher_id', $teacherId)
            ->where('is_active', 1)
            ->count();

        $publishedCoursesCount = Course::where('teacher_id', $teacherId)
            ->where('status', 'published')
            ->count();

        $classIds = ClassRoom::where('teacher_id', $teacherId)->pluck('class_id');

        $totalStudents = ClassEnrollment::whereIn('class_id', $classIds)
            ->where('status', 'active')
            ->distinct('student_id')
            ->count('student_id');

        $classes = ClassRoom::where('teacher_id', $teacherId)
            ->withCount([
                'enrollments' => fn ($q) => $q->where('status', 'active'),
                'postedCourses',
            ])
            ->latest('created_at')
            ->take(5)
            ->get();

        $submissions = $this->fetchAllSubmissions($teacherId);
        $recentActivity = $submissions->take(8);
        $needsGrading = $submissions->where('needs_grading', true)->take(6)->values();
        $needsGradingCount = $submissions->where('needs_grading', true)->count();

        return view('teacher.dashboard', compact(
            'activeClassesCount',
            'publishedCoursesCount',
            'totalStudents',
            'classes',
            'recentActivity',
            'needsGrading',
            'needsGradingCount'
        ));
    }

    /**
     * One unified, time-sorted feed of every Activity/Quiz/Exam submission
     * across this teacher's courses — used to drive both the "Recent
     * Student Activity" table and the "Needs Grading" widget so we only
     * query the database once.
     */
    private function fetchAllSubmissions(int $teacherId)
    {
        $courses = Course::where('teacher_id', $teacherId)
            ->with('postedClasses')
            ->get()
            ->keyBy('course_id');

        $courseIds = $courses->keys();

        $primaryClassId = fn (?Course $course) => $course
            ? optional($course->postedClasses->first())->class_id
            : null;

        $activityItems = ActivitySubmission::whereHas('module', fn ($q) => $q->whereIn('course_id', $courseIds))
            ->with('student', 'module')
            ->get()
            ->map(function ($sub) use ($courses, $primaryClassId) {
                $course = $courses->get($sub->module->course_id);

                return [
                    'icon'          => 'bi-clipboard-check',
                    'action'        => $sub->isGraded() ? 'Graded Activity' : 'Submitted Activity',
                    'title'         => $sub->module->title,
                    'student'       => $sub->student->full_name ?? 'Unknown',
                    'score'         => $sub->isGraded() ? "{$sub->score}/{$sub->module->points}" : '—',
                    'needs_grading' => ! $sub->isGraded(),
                    'course'        => $course->title ?? '—',
                    'class_id'      => $primaryClassId($course),
                    'when'          => $sub->submitted_at,
                ];
            });

        $quizItems = QuizSubmission::whereHas('quiz', fn ($q) => $q->whereIn('course_id', $courseIds))
            ->with('student', 'quiz', 'answers')
            ->get()
            ->map(function ($sub) use ($courses, $primaryClassId) {
                $course = $courses->get($sub->quiz->course_id);

                return [
                    'icon'          => 'bi-patch-question',
                    'action'        => 'Completed Quiz',
                    'title'         => $sub->quiz->title,
                    'student'       => $sub->student->full_name ?? 'Unknown',
                    'score'         => "{$sub->score}/{$sub->max_score}",
                    'needs_grading' => $sub->needsReview(),
                    'course'        => $course->title ?? '—',
                    'class_id'      => $primaryClassId($course),
                    'when'          => $sub->submitted_at,
                ];
            });

        $examItems = ExamSubmission::whereHas('exam', fn ($q) => $q->whereIn('course_id', $courseIds))
            ->with('student', 'exam', 'answers')
            ->get()
            ->map(function ($sub) use ($courses, $primaryClassId) {
                $course = $courses->get($sub->exam->course_id);

                return [
                    'icon'          => 'bi-file-text',
                    'action'        => 'Completed Exam',
                    'title'         => $sub->exam->title,
                    'student'       => $sub->student->full_name ?? 'Unknown',
                    'score'         => "{$sub->score}/{$sub->max_score}",
                    'needs_grading' => $sub->needsReview(),
                    'course'        => $course->title ?? '—',
                    'class_id'      => $primaryClassId($course),
                    'when'          => $sub->submitted_at,
                ];
            });

        return $activityItems->concat($quizItems)->concat($examItems)
            ->sortByDesc('when')
            ->values();
    }
}
