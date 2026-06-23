<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesEnrollment;
use App\Models\ClassRoom;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Flashcard;
use App\Services\GemmaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class FlashcardController extends Controller
{
    use AuthorizesEnrollment;

    public function __construct(private GemmaService $gemma)
    {
    }

    public function index(ClassRoom $class, Course $course)
    {
        $this->authorizeEnrollment($class, $course);

        $flashcards = Flashcard::where('course_id', $course->course_id)
            ->where('student_id', Auth::id())
            ->orderByDesc('generated_at')
            ->get();

        // Only pass published/approved lessons so students pick real content
        $lessons = $course->modules()
            ->where('item_type', 'lesson')
            ->where(function ($q) {
                $q->whereNull('ai_status')->orWhere('ai_status', 'approved');
            })
            ->orderBy('order_index')
            ->get(['module_id', 'title']);

        return view('student.flashcards', compact('class', 'course', 'flashcards', 'lessons'));
    }

    public function generate(Request $request, ClassRoom $class, Course $course)
    {
        $this->authorizeEnrollment($class, $course);

        $request->validate([
            'module_id' => 'required|integer',
            'count'     => 'nullable|integer|min:3|max:20',
        ]);

        // Verify the lesson belongs to this course and is visible
        $lesson = CourseModule::where('module_id', $request->module_id)
            ->where('course_id', $course->course_id)
            ->where('item_type', 'lesson')
            ->where(function ($q) {
                $q->whereNull('ai_status')->orWhere('ai_status', 'approved');
            })
            ->firstOrFail();

        if (trim((string) $lesson->content) === '') {
            return response()->json([
                'success' => false,
                'message' => 'This lesson has no content yet. Ask your teacher to add content first.',
            ], 422);
        }

        try {
            $data = $this->gemma->generateFlashcards(
                $course->title,
                $lesson->content,   // scope AI to just this lesson
                $lesson->title,     // use lesson title as the topic
                $request->integer('count', 10)
            );
        } catch (Throwable $e) {
            Log::warning('Gemma flashcard generation failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'AI flashcard generation failed. Please try again.',
            ], 422);
        }

        $saved = collect($data['flashcards'] ?? [])->map(fn ($card) => Flashcard::create([
            'course_id'  => $course->course_id,
            'student_id' => Auth::id(),
            'front_text' => $card['front'],
            'back_text'  => $card['back'],
        ]));

        return response()->json(['success' => true, 'flashcards' => $saved]);
    }

    public function pickCourse()
    {
        return view('student.ai-tools.pick-course', [
            'courses' => $this->accessibleCourses(),
            'mode'    => 'flashcards',
        ]);
    }
}
