<?php

namespace App\Http\Controllers;

use App\Models\ClassEnrollment;
use App\Models\ClassRoom;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ClassController extends Controller
{
    /**
     * Show the classes page for the logged-in user.
     * Teachers see classes they created; students see classes they joined.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'teacher') {
            $allClasses = ClassRoom::where('teacher_id', $user->user_id)
                ->withCount(['enrollments' => function ($query) {
                    $query->where('status', 'active');
                }])
                ->latest('created_at')
                ->get();

            $classes         = $allClasses->where('is_active', true)->values();
            $archivedClasses = $allClasses->where('is_active', false)->values();

            return view('teacher.classes', compact('classes', 'archivedClasses'));
        }

        $allClasses = $user->enrolledClasses()
            ->with('teacher')
            ->get();

        $classes         = $allClasses->filter(fn ($c) => ! ($c->pivot->is_archived ?? false))->values();
        $archivedClasses = $allClasses->filter(fn ($c) =>   ($c->pivot->is_archived ?? false))->values();

        return view('student.classes', compact('classes', 'archivedClasses'));
    }

    /**
     * Teacher: create a new class. A unique class code is generated
     * automatically and shown back to the teacher to share with students.
     */
    public function store(Request $request)
    {
        $request->validate([
            'class_name'  => 'required|string|max:150',
            'subject'     => 'required|string|max:150',
            'description' => 'nullable|string|max:1000',
        ]);

        $class = ClassRoom::create([
            'teacher_id'  => Auth::id(),
            'class_name'  => $request->class_name,
            'subject'     => $request->subject,
            'description' => $request->description,
            'class_code'  => $this->generateClassCode(),
            'is_active'   => 1,
        ]);

        return redirect()->route('teacher.classes')
            ->with('success', 'Class created successfully!')
            ->with('new_class_code', $class->class_code);
    }

    /**
     * Student: join a class using a teacher-provided class code.
     */
    public function join(Request $request)
    {
        $request->validate([
            'class_code' => 'required|string|max:10',
        ]);

        $class = ClassRoom::where('class_code', strtoupper($request->class_code))
            ->where('is_active', 1)
            ->first();

        if (! $class) {
            return back()->withErrors([
                'class_code' => 'Invalid or inactive class code.',
            ])->withInput();
        }

        if ((int) $class->teacher_id === (int) Auth::id()) {
            return back()->withErrors([
                'class_code' => 'You cannot join a class you teach.',
            ])->withInput();
        }

        $alreadyEnrolled = ClassEnrollment::where('class_id', $class->class_id)
            ->where('student_id', Auth::id())
            ->where('status', 'active')
            ->exists();

        if ($alreadyEnrolled) {
            return back()->withErrors([
                'class_code' => 'You are already enrolled in this class.',
            ])->withInput();
        }

        ClassEnrollment::create([
            'class_id'    => $class->class_id,
            'student_id'  => Auth::id(),
            'enrolled_by' => 'student',
            'status'      => 'active',
        ]);

        return redirect()->route('student.classes')
            ->with('success', 'You\'ve joined "' . $class->class_name . '"!');
    }

    /**
     * Generate a unique, easy-to-share class code (e.g. "A1B2C3").
     */
    private function generateClassCode(): string
    {
        do {
            $code = strtoupper(Str::random(6));
        } while (ClassRoom::where('class_code', $code)->exists());

        return $code;
    }

    /**
     * Class Overview page — posted courses only. Files, Members, and
     * Gradebook/My Scores each live on their own dedicated page now
     * (see ClassMaterialController, members() below, and GradebookController).
     */
    public function show(ClassRoom $class)
    {
        $user = Auth::user();

        if ($user->role === 'teacher') {
            $this->authorizeTeacherOwnsClass($class);

            $class->load([
                'postedCourses' => fn ($q) => $q->orderBy('title'),
                'postedCourses.lessons',
                'postedCourses.activities',
                'postedCourses.quizzes',
                'postedCourses.exams',
            ]);

            $availableCourses = Course::where('teacher_id', $user->user_id)
                ->where('status', 'published')
                ->whereNotIn('course_id', $class->postedCourses->pluck('course_id'))
                ->orderBy('title')
                ->get();

            return view('teacher.classes.show', compact('class', 'availableCourses'));
        }

        $this->authorizeStudentInClass($class, $user->user_id);

        $class->load([
            'postedCourses' => fn ($q) => $q->orderBy('title'),
            'postedCourses.lessons',
            'postedCourses.activities',
            'postedCourses.quizzes',
            'postedCourses.exams',
            'teacher',
        ]);

        return view('student.classes.show', compact('class'));
    }

    /**
     * Members page — full list for teachers (with kick), read-only
     * roster for students. Its own page now instead of a drawer.
     */
    public function members(ClassRoom $class)
    {
        $user = Auth::user();

        if ($user->role === 'teacher') {
            $this->authorizeTeacherOwnsClass($class);
        } else {
            $this->authorizeStudentInClass($class, $user->user_id);
        }

        $class->load('teacher');

        $members = $class->enrollments()
            ->where('status', 'active')
            ->with('student')
            ->get();

        $view = $user->role === 'teacher' ? 'teacher.classes.members' : 'student.classes.members';

        return view($view, compact('class', 'members'));
    }

    /**
     * Teacher: post one of their published courses into this class.
     */
    public function postCourse(Request $request, ClassRoom $class)
    {
        $this->authorizeTeacherOwnsClass($class);

        $request->validate([
            'course_id' => 'required|integer|exists:courses,course_id',
        ]);

        $course = Course::where('course_id', $request->course_id)
            ->where('teacher_id', Auth::id())
            ->first();

        if (! $course) {
            return back()->withErrors(['course_id' => 'Invalid course.']);
        }

        if ($course->status !== 'published') {
            return back()->withErrors(['course_id' => 'Publish the course first before posting it to a class.']);
        }

        $class->postedCourses()->syncWithoutDetaching([
            $course->course_id => ['posted_at' => now()],
        ]);

        return back()->with('success', "\"{$course->title}\" posted to {$class->class_name}.");
    }

    /**
     * Teacher: remove a course from this class (doesn't delete the course itself).
     */
    public function unpostCourse(ClassRoom $class, Course $course)
    {
        $this->authorizeTeacherOwnsClass($class);

        $class->postedCourses()->detach($course->course_id);

        return back()->with('success', "\"{$course->title}\" removed from {$class->class_name}.");
    }

    /**
     * Teacher: archive their own class (sets is_active = 0).
     */
    public function archive(ClassRoom $class)
    {
        $this->authorizeTeacherOwnsClass($class);
        $class->update(['is_active' => 0]);
        return back()->with('success', "\"{$class->class_name}\" has been archived.");
    }

    /**
     * Teacher: restore an archived class (sets is_active = 1).
     */
    public function unarchive(ClassRoom $class)
    {
        $this->authorizeTeacherOwnsClass($class);
        $class->update(['is_active' => 1]);
        return back()->with('success', "\"{$class->class_name}\" has been restored.");
    }

    /**
     * Student: archive (hide) an enrolled class from their main list.
     */
    public function studentArchive(ClassRoom $class)
    {
        ClassEnrollment::where('class_id', $class->class_id)
            ->where('student_id', Auth::id())
            ->where('status', 'active')
            ->update(['is_archived' => 1]);

        return back()->with('success', "\"{$class->class_name}\" moved to Archived.");
    }

    /**
     * Student: restore an archived class back to their main list.
     */
    public function studentUnarchive(ClassRoom $class)
    {
        ClassEnrollment::where('class_id', $class->class_id)
            ->where('student_id', Auth::id())
            ->where('status', 'active')
            ->update(['is_archived' => 0]);

        return back()->with('success', "\"{$class->class_name}\" restored to My Classes.");
    }

    /**
     * Teacher: kick a student out of the class. Soft — sets enrollment
     * status to 'removed' rather than deleting the row.
     */
    public function kickMember(ClassRoom $class, User $student)
    {
        $this->authorizeTeacherOwnsClass($class);

        ClassEnrollment::where('class_id', $class->class_id)
            ->where('student_id', $student->user_id)
            ->update(['status' => 'removed']);

        return back()->with('success', "{$student->full_name} has been removed from the class.");
    }

    private function authorizeTeacherOwnsClass(ClassRoom $class): void
    {
        if ((int) $class->teacher_id !== (int) Auth::id()) {
            abort(403);
        }
    }

    private function authorizeStudentInClass(ClassRoom $class, int $studentId): void
    {
        $isMember = ClassEnrollment::where('class_id', $class->class_id)
            ->where('student_id', $studentId)
            ->where('status', 'active')
            ->exists();

        if (! $isMember) {
            abort(403);
        }
    }
}
