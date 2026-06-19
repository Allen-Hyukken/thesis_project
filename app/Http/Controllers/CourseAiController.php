<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesCourseOwner;
use App\Models\Course;
use App\Services\GemmaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Every method here is a pure "ask Gemma, return a draft" endpoint.
 * Nothing is written to the database — the teacher reviews/edits the
 * draft in the UI and saves it through the normal store endpoints
 * (ModuleController, QuizController, ExamController), which work
 * identically whether the content came from AI or was typed by hand.
 */
class CourseAiController extends Controller
{
    use AuthorizesCourseOwner;

    public function __construct(private GemmaService $gemma)
    {
    }

    /**
     * Used on the "Create Course" page, before the course exists.
     */
    public function outline(Request $request)
    {
        $request->validate([
            'topic' => 'required|string|max:200',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $data = $this->gemma->generateOutline($request->topic, $request->notes);

            return response()->json(['success' => true, 'data' => $data]);
        } catch (Throwable $e) {
            Log::warning('Gemma outline generation failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'AI generation failed. You can try again or fill the form in manually.',
            ], 422);
        }
    }

    /**
     * Full lesson content for one existing topic.
     */
    public function lessonContent(Request $request, Course $course)
    {
        $this->authorizeOwner($course);

        $request->validate([
            'title'   => 'required|string|max:200',
            'summary' => 'nullable|string|max:500',
        ]);

        try {
            $data = $this->gemma->generateLessonContent($course->title, $request->title, $request->summary);

            return response()->json(['success' => true, 'data' => $data]);
        } catch (Throwable $e) {
            Log::warning('Gemma lesson generation failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'AI generation failed. Please try again or write the content in manually.',
            ], 422);
        }
    }

    /**
     * A single activity draft.
     */
    public function activity(Request $request, Course $course)
    {
        $this->authorizeOwner($course);

        $request->validate([
            'topic' => 'required|string|max:200',
        ]);

        try {
            $data = $this->gemma->generateActivity($course->title, $request->topic);

            return response()->json(['success' => true, 'data' => $data]);
        } catch (Throwable $e) {
            Log::warning('Gemma activity generation failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'AI generation failed. Please try again or fill the activity in manually.',
            ], 422);
        }
    }

    /**
     * Quiz or exam question drafts — same generator, different label.
     */
    public function assessment(Request $request, Course $course)
    {
        $this->authorizeOwner($course);

        $request->validate([
            'topic'         => 'required|string|max:200',
            'kind'          => 'required|in:quiz,exam',
            'num_questions' => 'nullable|integer|min:3|max:15',
        ]);

        try {
            $data = $this->gemma->generateAssessment(
                $course->title,
                $request->topic,
                $request->kind,
                $request->integer('num_questions', 5)
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
}
