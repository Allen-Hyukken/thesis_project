<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesCourseOwner;
use App\Models\Course;
use App\Services\EdithService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class CourseAiController extends Controller
{
    use AuthorizesCourseOwner;

    public function __construct(private EdithService $edith) {}

    public function outline(Request $request)
    {
        $request->validate([
            'topic' => 'required|string|max:200',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $data = $this->edith->generateOutline($request->topic, $request->notes);
            return response()->json(['success' => true, 'data' => $data]);
        } catch (Throwable $e) {
            Log::warning('Gemma outline generation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'AI generation failed. You can try again or fill the form in manually.',
            ], 422);
        }
    }

    public function lessonContent(Request $request, Course $course)
    {
        $this->authorizeOwner($course);

        $request->validate([
            'title'   => 'required|string|max:200',
            'summary' => 'nullable|string|max:500',
        ]);

        try {
            $data = $this->edith->generateLessonContent($course->title, $request->title, $request->summary);
            return response()->json(['success' => true, 'data' => $data]);
        } catch (Throwable $e) {
            Log::warning('Gemma lesson generation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'AI generation failed. Please try again or write the content in manually.',
            ], 422);
        }
    }

    public function activity(Request $request, Course $course)
    {
        $this->authorizeOwner($course);

        $request->validate([
            'topic' => 'required|string|max:200',
        ]);

        $courseContent = $this->buildCourseContent($course);

        if ($this->hasNoContent($courseContent)) {
            return response()->json([
                'success' => false,
                'message' => 'This course has no lesson content yet. Please write and save your lessons first — activities should be based on what students have learned.',
            ], 422);
        }

        try {
            $data = $this->edith->generateActivity($course->title, $request->topic, $courseContent);
            return response()->json(['success' => true, 'data' => $data]);
        } catch (Throwable $e) {
            Log::warning('Gemma activity generation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'AI generation failed. Please try again or fill the activity in manually.',
            ], 422);
        }
    }

    public function assessment(Request $request, Course $course)
    {
        $this->authorizeOwner($course);

        $request->validate([
            // topic is required for quiz, optional for exam (exam covers everything)
            'topic'         => $request->input('kind') === 'exam' ? 'nullable|string|max:200' : 'required|string|max:200',
            'kind'          => 'required|in:quiz,exam',
            'num_questions' => 'nullable|integer|min:3|max:30', // raised from 15 to 30
        ]);

        $courseContent = $this->buildCourseContent($course);

        if ($this->hasNoContent($courseContent)) {
            return response()->json([
                'success' => false,
                'message' => 'This course has no lesson content yet. Please write and save your lessons first — assessments should test what was actually taught.',
            ], 422);
        }

        // For exams: cover all topics. For quizzes: use the specific topic the teacher chose.
        $topic = $request->kind === 'exam'
            ? 'All Topics in ' . $course->title
            : $request->topic;

        try {
            $data = $this->edith->generateAssessment(
                $course->title,
                $topic,
                $request->kind,
                $request->integer('num_questions', 5),
                $courseContent
            );
            return response()->json(['success' => true, 'data' => $data]);
        } catch (Throwable $e) {
            Log::warning('Gemma assessment generation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'AI generation failed. Please try again or build the questions manually.',
            ], 422);
        }
    }

    private function buildCourseContent(Course $course, int $charLimit = 12000): string
    {
        $lessons = $course->modules()
            ->where('item_type', 'lesson')
            ->orderBy('order_index')
            ->get();

        if ($lessons->isEmpty()) {
            return 'No lesson content has been added to this course yet.';
        }

        $text = $lessons
            ->map(fn ($m) => "## {$m->title}\n\n{$m->content}")
            ->implode("\n\n---\n\n");

        return mb_strlen($text) > $charLimit
            ? mb_substr($text, 0, $charLimit) . "\n\n[...content truncated...]"
            : $text;
    }

    private function hasNoContent(string $courseContent): bool
    {
        return $courseContent === '' || str_starts_with($courseContent, 'No lesson content');
    }
}
