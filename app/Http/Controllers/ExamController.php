<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesCourseOwner;
use App\Http\Controllers\Concerns\BuildsAssessmentQuestions;
use App\Models\Course;
use App\Models\Exam;
use Illuminate\Http\Request;

class ExamController extends Controller
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

        $exam = $course->exams()->create([
            'module_id'    => $validated['module_id'] ?? null,
            'title'        => $validated['title'],
            'description'  => $validated['description'] ?? null,
            'ai_generated' => $request->boolean('ai_generated'),
            'is_published' => false,
        ]);

        $this->saveAssessmentQuestions($exam, $validated['questions']);

        return back()->with('success', 'Exam created.');
    }

    public function update(Request $request, Course $course, Exam $exam)
    {
        $this->authorizeOwner($course);

        if ((int) $exam->course_id !== (int) $course->course_id) {
            abort(404);
        }

        $validated = $request->validate([
            'title'                      => 'required|string|max:200',
            'description'                => 'nullable|string',
            'questions'                  => 'required|array|min:1',
            'questions.*.question_text'  => 'required|string',
            'questions.*.question_type'  => 'required|in:multiple_choice,open_ended',
            'questions.*.points'         => 'required|integer|min:1|max:100',
            'questions.*.choices'        => 'nullable|array',
            'questions.*.choices.*.text' => 'nullable|string|max:255',
            'questions.*.correct_choice' => 'nullable|integer|min:0|max:3',
            'questions.*.correct_answer' => 'nullable|string',
        ]);

        $exam->update([
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? null,
        ]);

        foreach ($exam->questions as $question) {
            $question->choices()->delete();
        }
        $exam->questions()->delete();

        $this->saveAssessmentQuestions($exam, $validated['questions']);

        return back()->with('success', 'Exam updated.');
    }

    public function publish(Course $course, Exam $exam)
    {
        $this->authorizeOwner($course);

        if ((int) $exam->course_id !== (int) $course->course_id) {
            abort(404);
        }

        $exam->is_published = ! $exam->is_published;
        $exam->save();

        return back()->with('success', $exam->is_published
            ? 'Exam published to students.'
            : 'Exam unpublished.');
    }

    public function destroy(Course $course, Exam $exam)
    {
        $this->authorizeOwner($course);

        if ((int) $exam->course_id !== (int) $course->course_id) {
            abort(404);
        }

        $exam->delete();

        return back()->with('success', 'Exam deleted.');
    }

}
