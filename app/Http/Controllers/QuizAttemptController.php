<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ChecksStudentCourseAccess;
use App\Http\Controllers\Concerns\HandlesAssessmentAttempts;
use App\Models\Quiz;
use App\Models\QuizSubmission;
use App\Models\StudentCourseProgress;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuizAttemptController extends Controller
{
    use ChecksStudentCourseAccess, HandlesAssessmentAttempts;

    public function show(Quiz $quiz)
    {
        $user = Auth::user();

        if (! $quiz->is_published || ! $this->studentHasAccessToCourse($quiz->course)) {
            abort(403);
        }

        $submission = QuizSubmission::where('quiz_id', $quiz->quiz_id)
            ->where('student_id', $user->user_id)
            ->with('answers')
            ->first();

        $quiz->load('questions.choices');

        return view('student.quizzes.take', compact('quiz', 'submission'));
    }

    public function submit(Request $request, Quiz $quiz)
    {
        $user = Auth::user();

        if (! $quiz->is_published || ! $this->studentHasAccessToCourse($quiz->course)) {
            abort(403);
        }

        $alreadyTaken = QuizSubmission::where('quiz_id', $quiz->quiz_id)
            ->where('student_id', $user->user_id)
            ->exists();

        if ($alreadyTaken) {
            return redirect()->route('student.quizzes.take', $quiz->quiz_id)
                ->with('success', 'You have already taken this quiz.');
        }

        $quiz->load('questions.choices');

        try {
            $submission = QuizSubmission::create([
                'quiz_id'    => $quiz->quiz_id,
                'student_id' => $user->user_id,
                'score'      => 0,
                'max_score'  => 0,
            ]);
        } catch (QueryException $e) {
            return redirect()->route('student.quizzes.take', $quiz->quiz_id)
                ->with('success', 'You have already taken this quiz.');
        }

        [$score, $maxScore] = $this->recordAnswers($submission, $quiz->questions, $request->input('answers', []));

        $submission->update(['score' => $score, 'max_score' => $maxScore]);

        // Recalculate course progress for FR.1.7.3
        StudentCourseProgress::recalculate($user->user_id, $quiz->course_id);

        return redirect()->route('student.quizzes.take', $quiz->quiz_id)
            ->with('success', 'Quiz submitted!');
    }
}
