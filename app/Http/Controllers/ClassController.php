<?php

namespace App\Http\Controllers;

use App\Models\ClassEnrollment;
use App\Models\ClassRoom;
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
}
