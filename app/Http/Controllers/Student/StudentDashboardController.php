<?php

namespace App\Http\Controllers\Student;
use App\Http\Controllers\Controller;

use App\Models\ActivitySubmission;
use App\Models\Course;
use App\Models\ExamSubmission;
use App\Models\QuizSubmission;
use Illuminate\Support\Facades\Auth;

class StudentDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $studentId = $user->user_id;

        $classes = $user->enrolledClasses()
            ->with('teacher')
            ->withCount('postedCourses')
            ->latest('class_enrollments.enrolled_at')
            ->take(5)
            ->get();

        $classIds = $user->enrolledClasses()->pluck('classes.class_id');
        $enrolledClassesCount = $classIds->count();

        [$pendingTasks, $pendingCount, $avgScore, $completedCount] = $this->buildTaskData($studentId, $classIds);

        return view('student.dashboard', compact(
            'classes',
            'enrolledClassesCount',
            'pendingTasks',
            'pendingCount',
            'avgScore',
            'completedCount'
        ));
    }

    /**
     * Walks every course posted into the student's active classes and
     * works out, per activity/quiz/exam, whether it's done, pending, or
     * graded — driving the stat cards, the "My Classes" list's course
     * counts, and the Pending Tasks widget all from one pass.
     */
    private function buildTaskData(int $studentId, $classIds): array
    {
        $courses = Course::whereHas('postedClasses', fn ($q) => $q->whereIn('classes.class_id', $classIds))
            ->with([
                'activities',
                'quizzes',
                'exams',
                'postedClasses' => fn ($q) => $q->whereIn('classes.class_id', $classIds),
            ])
            ->get()
            ->unique('course_id');

        $moduleIds = $courses->flatMap(fn ($c) => $c->activities->pluck('module_id'));
        $quizIds   = $courses->flatMap(fn ($c) => $c->quizzes->where('is_published', true)->pluck('quiz_id'));
        $examIds   = $courses->flatMap(fn ($c) => $c->exams->where('is_published', true)->pluck('exam_id'));

        $activitySubs = ActivitySubmission::where('student_id', $studentId)
            ->whereIn('module_id', $moduleIds)
            ->get()
            ->keyBy('module_id');

        $quizSubs = QuizSubmission::where('student_id', $studentId)
            ->whereIn('quiz_id', $quizIds)
            ->with('answers')
            ->get()
            ->keyBy('quiz_id');

        $examSubs = ExamSubmission::where('student_id', $studentId)
            ->whereIn('exam_id', $examIds)
            ->with('answers')
            ->get()
            ->keyBy('exam_id');

        $pending = collect();
        $percentages = collect();
        $completedCount = 0;

        foreach ($courses as $course) {
            foreach ($course->activities as $activity) {
                $sub = $activitySubs->get($activity->module_id);
                $studentClassId = optional($course->postedClasses->first())->class_id;

                if (! $sub) {
                    $pending->push([
                        'icon'  => 'bi-clipboard-check',
                        'type'  => 'Activity',
                        'title' => $activity->title,
                        'course' => $course->title,
                        'due_at' => $activity->due_at,
                        'class_id' => $studentClassId,
                        'link' => $studentClassId
                            ? route('student.classes.courses.show', [$studentClassId, $course->course_id])
                            : null,
                    ]);
                } elseif ($sub->isGraded()) {
                    $completedCount++;
                    if ($activity->points > 0) {
                        $percentages->push(($sub->score / $activity->points) * 100);
                    }
                }
            }

            foreach ($course->quizzes->where('is_published', true) as $quiz) {
                $sub = $quizSubs->get($quiz->quiz_id);

                if (! $sub) {
                    $pending->push([
                        'icon'  => 'bi-patch-question',
                        'type'  => 'Quiz',
                        'title' => $quiz->title,
                        'course' => $course->title,
                        'due_at' => null,
                        'class_id' => null,
                        'link' => route('student.quizzes.take', $quiz->quiz_id),
                    ]);
                } else {
                    $completedCount++;
                    if (! $sub->needsReview() && $sub->max_score > 0) {
                        $percentages->push(($sub->score / $sub->max_score) * 100);
                    }
                }
            }

            foreach ($course->exams->where('is_published', true) as $exam) {
                $sub = $examSubs->get($exam->exam_id);

                if (! $sub) {
                    $pending->push([
                        'icon'  => 'bi-file-text',
                        'type'  => 'Exam',
                        'title' => $exam->title,
                        'course' => $course->title,
                        'due_at' => null,
                        'class_id' => null,
                        'link' => route('student.exams.take', $exam->exam_id),
                    ]);
                } else {
                    $completedCount++;
                    if (! $sub->needsReview() && $sub->max_score > 0) {
                        $percentages->push(($sub->score / $sub->max_score) * 100);
                    }
                }
            }
        }

        $pendingSorted = $pending->sortBy(fn ($i) => $i['due_at'] ?? now()->addYears(10))->values();
        $avgScore = $percentages->isNotEmpty() ? round($percentages->avg()) : null;

        return [$pendingSorted->take(6), $pending->count(), $avgScore, $completedCount];
    }
}
