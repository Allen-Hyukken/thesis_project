<?php

namespace App\Http\Controllers\AI;
use App\Http\Controllers\Controller;

use App\Http\Controllers\Concerns\AuthorizesEnrollment;
use App\Models\AiChatHistory;
use App\Models\ClassRoom;
use App\Models\Course;
use App\Services\GemmaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class AiTutorController extends Controller
{
    use AuthorizesEnrollment;

    public function __construct(private GemmaService $gemma)
    {
    }

    public function show(ClassRoom $class, Course $course)
    {
        $this->authorizeEnrollment($class, $course);

        $history = AiChatHistory::where('course_id', $course->course_id)
            ->where('student_id', Auth::id())
            ->orderBy('created_at')
            ->get();

        return view('student.ai-tutor', compact('class', 'course', 'history'));
    }

    public function ask(Request $request, ClassRoom $class, Course $course)
    {
        $this->authorizeEnrollment($class, $course);

        $request->validate(['question' => 'required|string|max:1000']);

        $content = $course->studyContent();

        if (trim($content) === '') {
            return response()->json([
                'success' => false,
                'message' => "This course doesn't have any published lesson content yet, so the AI tutor has nothing to work from.",
            ], 422);
        }

        $recentHistory = AiChatHistory::where('course_id', $course->course_id)
            ->where('student_id', Auth::id())
            ->orderByDesc('created_at')
            ->take(10)
            ->get()
            ->reverse()
            ->map(fn ($row) => ['role' => $row->role, 'message' => $row->message])
            ->all();

        try {
            $answer = $this->gemma->askTutor($course->title, $content, $recentHistory, $request->question);
        } catch (Throwable $e) {
            Log::warning('Gemma tutor request failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'The AI tutor is unavailable right now. Please try again in a moment.',
            ], 422);
        }

        AiChatHistory::create([
            'course_id'  => $course->course_id,
            'student_id' => Auth::id(),
            'role'       => 'user',
            'message'    => $request->question,
        ]);

        AiChatHistory::create([
            'course_id'  => $course->course_id,
            'student_id' => Auth::id(),
            'role'       => 'assistant',
            'message'    => $answer,
        ]);

        return response()->json(['success' => true, 'answer' => $answer]);
    }

    public function pickCourse()
    {
        return view('student.ai-tools.pick-course', [
            'courses' => $this->accessibleCourses(),
            'mode'    => 'ai-tutor',
        ]);
    }
}
