<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesCourseOwner;
use App\Http\Controllers\Concerns\BuildsAssessmentQuestions;
use App\Models\Course;
use App\Models\Quiz;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    use AuthorizesCourseOwner, BuildsAssessmentQuestions;

    public function store(Request $request, Course $course)
    {
        $this->authorizeOwner($course);

        $validated = $request->validate([
            'module_id'                  => 'nullable|integer|exists:course_modules,module_id',
            'title'                      => 'required|string|max:200',
            'description'                => 'nullable|string',
            'ai_generated'               => 'nullable|boolean',
            'questions'                  => 'required|array|min:1',
            'questions.*.question_text'  => 'required|string',
            'questions.*.question_type'  => 'required|in:multiple_choice,open_ended',
            'questions.*.points'         => 'required|integer|min:1|max:100',
            'questions.*.choices'        => 'nullable|array',
            'questions.*.choices.*.text' => 'nullable|string|max:255',
            'questions.*.correct_choice' => 'nullable|integer|min:0|max:3',
            'questions.*.correct_answer' => 'nullable|string',
        ]);

        $quiz = $course->quizzes()->create([
            'module_id'    => $validated['module_id'] ?? null,
            'title'        => $validated['title'],
            'description'  => $validated['description'] ?? null,
            'ai_generated' => $request->boolean('ai_generated'),
            'is_published' => false,
        ]);

        $this->saveAssessmentQuestions($quiz, $validated['questions']);

        return back()->with('success', 'Quiz created.');
    }

    public function publish(Course $course, Quiz $quiz)
    {
        $this->authorizeOwner($course);

        if ((int) $quiz->course_id !== (int) $course->course_id) {
            abort(404);
        }

        $quiz->is_published = ! $quiz->is_published;
        $quiz->save();

        return back()->with('success', $quiz->is_published
            ? 'Quiz published to students.'
            : 'Quiz unpublished.');
    }

    public function destroy(Course $course, Quiz $quiz)
    {
        $this->authorizeOwner($course);

        if ((int) $quiz->course_id !== (int) $course->course_id) {
            abort(404);
        }

        $quiz->delete();

        return back()->with('success', 'Quiz deleted.');
    }

}
