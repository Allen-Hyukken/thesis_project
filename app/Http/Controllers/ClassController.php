<?php

namespace App\Http\Controllers;

use App\Models\ActivitySubmission;
use App\Models\ClassEnrollment;
use App\Models\ClassRoom;
use App\Models\Course;
use App\Models\ExamSubmission;
use App\Models\QuizSubmission;
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
            $classes = ClassRoom::where('teacher_id', $user->user_id)
                ->withCount(['enrollments' => function ($query) {
                    $query->where('status', 'active');
                }])
                ->latest('created_at')
                ->get();

            return view('teacher.classes', compact('classes'));
        }

        $classes = $user->enrolledClasses()
            ->with('teacher')
            ->get();

        return view('student.classes', compact('classes'));
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
     * Class detail page — posted courses, files drawer, members drawer,
     * and (for teachers) the gradebook drawer. Branches by role like
     * index() already does.
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
                'postedCourses.quizzes.questions',
                'postedCourses.exams.questions',
                'materials.teacher',
            ]);

            $members = $class->enrollments()
                ->where('status', 'active')
                ->with('student')
                ->get();

            $availableCourses = Course::where('teacher_id', $user->user_id)
                ->where('status', 'published')
                ->whereNotIn('course_id', $class->postedCourses->pluck('course_id'))
                ->orderBy('title')
                ->get();

            $gradebook = $this->buildGradebookData($class);

            return view('teacher.classes.show', compact('class', 'members', 'availableCourses', 'gradebook'));
        }

        $isMember = ClassEnrollment::where('class_id', $class->class_id)
            ->where('student_id', $user->user_id)
            ->where('status', 'active')
            ->exists();

        if (! $isMember) {
            abort(403);
        }

        $class->load([
            'postedCourses' => fn ($q) => $q->orderBy('title'),
            'postedCourses.lessons',
            'postedCourses.activities',
            'postedCourses.quizzes',
            'postedCourses.exams',
            'materials.teacher',
            'teacher',
        ]);

        $members = $class->enrollments()
            ->where('status', 'active')
            ->with('student')
            ->get();

        $moduleIds = $class->postedCourses->flatMap(fn ($c) => $c->activities->pluck('module_id'))->all();
        $quizIds   = $class->postedCourses->flatMap(fn ($c) => $c->quizzes->pluck('quiz_id'))->all();
        $examIds   = $class->postedCourses->flatMap(fn ($c) => $c->exams->pluck('exam_id'))->all();

        $myActivitySubmissions = ActivitySubmission::where('student_id', $user->user_id)
            ->whereIn('module_id', $moduleIds)
            ->get()
            ->keyBy('module_id');

        $myQuizSubmissions = QuizSubmission::where('student_id', $user->user_id)
            ->whereIn('quiz_id', $quizIds)
            ->with('answers')
            ->get()
            ->keyBy('quiz_id');

        $myExamSubmissions = ExamSubmission::where('student_id', $user->user_id)
            ->whereIn('exam_id', $examIds)
            ->with('answers')
            ->get()
            ->keyBy('exam_id');

        return view('student.classes.show', compact(
            'class', 'members', 'myActivitySubmissions', 'myQuizSubmissions', 'myExamSubmissions'
        ));
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

    /**
     * Assembles, per posted course, the submissions for every activity,
     * quiz, and exam — each with the student attached — for the
     * teacher's Gradebook drawer.
     */
    private function buildGradebookData(ClassRoom $class): array
    {
        $courses = $class->postedCourses;

        $moduleIds = $courses->flatMap(fn ($c) => $c->activities->pluck('module_id'))->all();
        $quizIds   = $courses->flatMap(fn ($c) => $c->quizzes->pluck('quiz_id'))->all();
        $examIds   = $courses->flatMap(fn ($c) => $c->exams->pluck('exam_id'))->all();

        $activitySubmissions = ActivitySubmission::whereIn('module_id', $moduleIds)
            ->with('student')
            ->get()
            ->groupBy('module_id');

        $quizSubmissions = QuizSubmission::whereIn('quiz_id', $quizIds)
            ->with('student', 'answers.question')
            ->get()
            ->groupBy('quiz_id');

        $examSubmissions = ExamSubmission::whereIn('exam_id', $examIds)
            ->with('student', 'answers.question')
            ->get()
            ->groupBy('exam_id');

        return compact('activitySubmissions', 'quizSubmissions', 'examSubmissions');
    }

    private function authorizeTeacherOwnsClass(ClassRoom $class): void
    {
        if ((int) $class->teacher_id !== (int) Auth::id()) {
            abort(403);
        }
    }
}
