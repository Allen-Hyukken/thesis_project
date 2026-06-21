<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesCourseOwner;
use App\Models\ClassRoom;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{
    use AuthorizesCourseOwner;

    public function index()
    {
        $courses = Course::where('teacher_id', Auth::id())
            ->withCount(['lessons', 'activities', 'quizzes', 'exams'])
            ->latest('created_at')
            ->get();

        return view('teacher.courses.index', compact('courses'));
    }

    public function create()
    {
        $classes = ClassRoom::where('teacher_id', Auth::id())
            ->orderBy('class_name')
            ->get();

        return view('teacher.courses.create', compact('classes'));
    }

    /**
     * Saves the course plus whichever AI-suggested (or manually typed)
     * modules the teacher kept checked on the create page.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'                  => 'required|string|max:200',
            'description'            => 'nullable|string',
            'learning_objectives'    => 'nullable|string',
            'visibility'             => 'required|in:public,private',
            'class_id'               => 'nullable|integer|exists:classes,class_id',
            'modules'                => 'nullable|array',
            'modules.*.title'        => 'required_with:modules|string|max:200',
            'modules.*.content'      => 'nullable|string',
            'modules.*.ai_generated' => 'nullable|boolean',
        ]);

        if (! empty($validated['class_id'])) {
            $ownsClass = ClassRoom::where('class_id', $validated['class_id'])
                ->where('teacher_id', Auth::id())
                ->exists();

            if (! $ownsClass) {
                return back()->withErrors(['class_id' => 'Invalid class selected.'])->withInput();
            }
        }

        $course = Course::create([
            'class_id'            => $validated['class_id'] ?? null,
            'teacher_id'          => Auth::id(),
            'title'               => $validated['title'],
            'description'         => $validated['description'] ?? null,
            'learning_objectives' => $validated['learning_objectives'] ?? null,
            'visibility'          => $validated['visibility'],
            'status'              => 'draft',
            'ai_generated'        => ! empty($validated['modules']) && collect($validated['modules'])->contains(fn ($m) => ! empty($m['ai_generated'])),
        ]);

        foreach ($validated['modules'] ?? [] as $index => $module) {
            $course->modules()->create([
                'item_type'    => 'lesson',
                'title'        => $module['title'],
                'content'      => $module['content'] ?? null,
                'order_index'  => $index,
                'ai_generated' => ! empty($module['ai_generated']),
            ]);
        }

        return redirect()->route('teacher.courses.show', $course->course_id)
            ->with('success', 'Course created! Add lesson content, activities, quizzes, and exams below.');
    }

    public function show(Course $course)
    {
        $this->authorizeOwner($course);

        $course->load([
            'lessons' => fn ($q) => $q->orderBy('order_index'),
            'activities' => fn ($q) => $q->orderBy('order_index'),
            'quizzes.questions.choices',
            'exams.questions.choices',
            'classRoom',
        ]);

        return view('teacher.courses.show', compact('course'));
    }

    public function publish(Course $course)
    {
        $this->authorizeOwner($course);

        $course->status = $course->status === 'published' ? 'unpublished' : 'published';
        $course->save();

        return back()->with('success', $course->status === 'published'
            ? 'Course is now published.'
            : 'Course has been unpublished.');
    }
}
