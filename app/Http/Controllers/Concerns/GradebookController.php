<?php

namespace App\Http\Controllers;

use App\Models\ActivitySubmission;
use App\Models\ClassEnrollment;
use App\Models\ClassRoom;
use App\Models\ExamAnswer;
use App\Models\ExamSubmission;
use App\Models\QuizAnswer;
use App\Models\QuizSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GradebookController extends Controller
{
    /**
     * Teacher's Gradebook page — its own page now instead of a drawer.
     * Nested per posted course -> activity/quiz/exam -> student, with
     * inline grading forms for whatever still needs a human.
     */
    public function show(ClassRoom $class)
    {
        if ((int) $class->teacher_id !== (int) Auth::id()) {
            abort(403);
        }

        $class->load([
            'postedCourses' => fn ($q) => $q->orderBy('title'),
            'postedCourses.activities',
            'postedCourses.quizzes.questions',
            'postedCourses.exams.questions',
        ]);

        $gradebook = $this->buildGradebookData($class);

        return view('teacher.classes.gradebook', compact('class', 'gradebook'));
    }

    /**
     * Student's My Scores page — same nested shape, read-only.
     */
    public function myScores(ClassRoom $class)
    {
        $user = Auth::user();

        $isMember = ClassEnrollment::where('class_id', $class->class_id)
            ->where('student_id', $user->user_id)
            ->where('status', 'active')
            ->exists();

        if (! $isMember) {
            abort(403);
        }

        $class->load([
            'postedCourses' => fn ($q) => $q->orderBy('title'),
            'postedCourses.activities',
            'postedCourses.quizzes',
            'postedCourses.exams',
        ]);

        $moduleIds = $class->postedCourses->flatMap(fn ($c) => $c->activities->pluck('module_id'))->all();
        $quizIds   = $class->postedCourses->flatMap(fn ($c) => $c->quizzes->pluck('quiz_id'))->all();
        $examIds   = $class->postedCourses->flatMap(fn ($c) => $c->exams->pluck('exam_id'))->all();

        $myActivitySubmissions = ActivitySubmission::where('student_id', $user->user_id)
            ->whereIn('module_id', $moduleIds)
            ->get()
            ->keyBy('module_id');

        $myQuizSubmissions = QuizSubmission::where('student_id', $user->user_id)
            ->whereIn('quiz_id', $quizIds)
            ->with('answers')
            ->get()
            ->keyBy('quiz_id');

        $myExamSubmissions = ExamSubmission::where('student_id', $user->user_id)
            ->whereIn('exam_id', $examIds)
            ->with('answers')
            ->get()
            ->keyBy('exam_id');

        return view('student.classes.scores', compact(
            'class', 'myActivitySubmissions', 'myQuizSubmissions', 'myExamSubmissions'
        ));
    }

    public function gradeActivity(Request $request, ActivitySubmission $submission)
    {
        $course = $submission->module->course;
        $this->authorizeTeacher($course->teacher_id);

        $request->validate([
            'score'    => 'required|numeric|min:0|max:' . max(1, (int) $submission->module->points),
            'feedback' => 'nullable|string|max:2000',
        ]);

        $submission->update([
            'score'     => $request->score,
            'feedback'  => $request->feedback,
            'status'    => 'graded',
            'graded_at' => now(),
        ]);

        return back()->with('success', 'Activity graded.');
    }

    public function gradeQuizAnswer(Request $request, QuizAnswer $answer)
    {
        $course = $answer->submission->quiz->course;
        $this->authorizeTeacher($course->teacher_id);

        $this->gradeOpenEndedAnswer($request, $answer);

        return back()->with('success', 'Answer graded.');
    }

    public function gradeExamAnswer(Request $request, ExamAnswer $answer)
    {
        $course = $answer->submission->exam->course;
        $this->authorizeTeacher($course->teacher_id);

        $this->gradeOpenEndedAnswer($request, $answer);

        return back()->with('success', 'Answer graded.');
    }

    /**
     * Shared logic for grading a single open-ended answer (works for both
     * QuizAnswer and ExamAnswer — they share the same shape) and rolling
     * the new total back up to the parent submission's score.
     */
    private function gradeOpenEndedAnswer(Request $request, $answer): void
    {
        $maxPoints = $answer->question->points;

        $request->validate([
            'points_earned' => "required|numeric|min:0|max:{$maxPoints}",
        ]);

        $answer->update([
            'points_earned' => $request->points_earned,
            'is_correct'    => (float) $request->points_earned >= (float) $maxPoints,
        ]);

        $submission = $answer->submission;
        $submission->update([
            'score' => $submission->answers()->sum('points_earned'),
        ]);
    }

    /**
     * Assembles, per posted course, the submissions for every activity,
     * quiz, and exam — each with the student attached.
     */
    private function buildGradebookData(ClassRoom $class): array
    {
        $courses = $class->postedCourses;

        $moduleIds = $courses->flatMap(fn ($c) => $c->activities->pluck('module_id'))->all();
        $quizIds   = $courses->flatMap(fn ($c) => $c->quizzes->pluck('quiz_id'))->all();
        $examIds   = $courses->flatMap(fn ($c) => $c->exams->pluck('exam_id'))->all();

        $activitySubmissions = ActivitySubmission::whereIn('module_id', $moduleIds)
            ->with('student')
            ->get()
            ->groupBy('module_id');

        $quizSubmissions = QuizSubmission::whereIn('quiz_id', $quizIds)
            ->with('student', 'answers.question')
            ->get()
            ->groupBy('quiz_id');

        $examSubmissions = ExamSubmission::whereIn('exam_id', $examIds)
            ->with('student', 'answers.question')
            ->get()
            ->groupBy('exam_id');

        return compact('activitySubmissions', 'quizSubmissions', 'examSubmissions');
    }

    private function authorizeTeacher(int $courseTeacherId): void
    {
        if ($courseTeacherId !== (int) Auth::id()) {
            abort(403);
        }
    }
}
