<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesEnrollment;
use App\Models\ClassRoom;
use App\Models\Course;
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

        return view('student.flashcards', compact('class', 'course', 'flashcards'));
    }

    public function generate(Request $request, ClassRoom $class, Course $course)
    {
        $this->authorizeEnrollment($class, $course);

        $request->validate([
            'topic' => 'required|string|max:200',
            'count' => 'nullable|integer|min:3|max:20',
        ]);

        $content = $course->studyContent();

        if (trim($content) === '') {
            return response()->json([
                'success' => false,
                'message' => "This course doesn't have any published lesson content yet.",
            ], 422);
        }

        try {
            $data = $this->gemma->generateFlashcards(
                $course->title,
                $content,
                $request->topic,
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
