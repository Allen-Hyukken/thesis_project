<?php

namespace App\Http\Controllers;

use App\Models\ActivitySubmission;
use App\Models\ClassEnrollment;
use App\Models\ClassRoom;
use App\Models\Course;
use App\Models\ExamSubmission;
use App\Models\QuizSubmission;
use Illuminate\Support\Facades\Auth;

class StudentCourseController extends Controller
{
    public function show(ClassRoom $class, Course $course)
    {
        $user = Auth::user();

        $isMember = ClassEnrollment::where('class_id', $class->class_id)
            ->where('student_id', $user->user_id)
            ->where('status', 'active')
            ->exists();

        if (! $isMember) {
            abort(403);
        }

        $isPosted = $class->postedCourses()->where('courses.course_id', $course->course_id)->exists();

        if (! $isPosted) {
            abort(404);
        }

        $course->load([
            'lessons' => fn ($q) => $q->orderBy('order_index'),
            'activities' => fn ($q) => $q->orderBy('order_index'),
            'quizzes.questions',
            'exams.questions',
        ]);

        $activitySubmissions = ActivitySubmission::where('student_id', $user->user_id)
            ->whereIn('module_id', $course->activities->pluck('module_id'))
            ->get()
            ->keyBy('module_id');

        $quizSubmissions = QuizSubmission::where('student_id', $user->user_id)
            ->whereIn('quiz_id', $course->quizzes->pluck('quiz_id'))
            ->with('answers')
            ->get()
            ->keyBy('quiz_id');

        $examSubmissions = ExamSubmission::where('student_id', $user->user_id)
            ->whereIn('exam_id', $course->exams->pluck('exam_id'))
            ->with('answers')
            ->get()
            ->keyBy('exam_id');

        return view('student.courses.show', compact(
            'class', 'course', 'activitySubmissions', 'quizSubmissions', 'examSubmissions'
        ));
    }
}
