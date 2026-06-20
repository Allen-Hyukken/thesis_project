<?php

namespace App\Http\Controllers;

use App\Models\ActivitySubmission;
use App\Models\ExamAnswer;
use App\Models\QuizAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GradebookController extends Controller
{
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

    private function authorizeTeacher(int $courseTeacherId): void
    {
        if ($courseTeacherId !== (int) Auth::id()) {
            abort(403);
        }
    }
}
