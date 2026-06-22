<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ChecksStudentCourseAccess;
use App\Http\Controllers\Concerns\HandlesAssessmentAttempts;
use App\Models\Exam;
use App\Models\ExamSubmission;
use App\Models\StudentCourseProgress;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExamAttemptController extends Controller
{
    use ChecksStudentCourseAccess, HandlesAssessmentAttempts;

    public function show(Exam $exam)
    {
        $user = Auth::user();

        if (! $exam->is_published || ! $this->studentHasAccessToCourse($exam->course)) {
            abort(403);
        }

        $submission = ExamSubmission::where('exam_id', $exam->exam_id)
            ->where('student_id', $user->user_id)
            ->with('answers')
            ->first();

        $exam->load('questions.choices');

        return view('student.exams.take', compact('exam', 'submission'));
    }

    public function submit(Request $request, Exam $exam)
    {
        $user = Auth::user();

        if (! $exam->is_published || ! $this->studentHasAccessToCourse($exam->course)) {
            abort(403);
        }

        $alreadyTaken = ExamSubmission::where('exam_id', $exam->exam_id)
            ->where('student_id', $user->user_id)
            ->exists();

        if ($alreadyTaken) {
            return redirect()->route('student.exams.take', $exam->exam_id)
                ->with('success', 'You have already taken this exam.');
        }

        $exam->load('questions.choices');

        try {
            $submission = ExamSubmission::create([
                'exam_id'    => $exam->exam_id,
                'student_id' => $user->user_id,
                'score'      => 0,
                'max_score'  => 0,
            ]);
        } catch (QueryException $e) {
            return redirect()->route('student.exams.take', $exam->exam_id)
                ->with('success', 'You have already taken this exam.');
        }

        [$score, $maxScore] = $this->recordAnswers($submission, $exam->questions, $request->input('answers', []));

        $submission->update(['score' => $score, 'max_score' => $maxScore]);

        // Recalculate course progress for FR.1.7.3
        StudentCourseProgress::recalculate($user->user_id, $exam->course_id);

        return redirect()->route('student.exams.take', $exam->exam_id)
            ->with('success', 'Exam submitted!');
    }
}
