<?php

namespace App\Http\Controllers\Concerns;

use App\Models\ClassEnrollment;
use App\Models\ClassRoom;
use App\Models\Course;
use Illuminate\Support\Facades\Auth;

trait AuthorizesEnrollment
{
    protected function authorizeEnrollment(ClassRoom $class, Course $course): void
    {
        $isMember = ClassEnrollment::where('class_id', $class->class_id)
            ->where('student_id', Auth::id())
            ->where('status', 'active')
            ->exists();

        if (! $isMember) {
            abort(403);
        }

        $isPosted = $class->postedCourses()->where('courses.course_id', $course->course_id)->exists();

        if (! $isPosted) {
            abort(404);
        }
    }

    /**
     * Courses the student can reach an AI feature for — i.e. courses
     * posted into a class they're actively enrolled in. Used for the
     * sidebar's "pick a course" landing pages.
     */
    protected function accessibleCourses()
    {
        $classIds = ClassEnrollment::where('student_id', Auth::id())
            ->where('status', 'active')
            ->pluck('class_id');

        return Course::whereHas('postedClasses', fn ($q) => $q->whereIn('classes.class_id', $classIds))
            ->with(['postedClasses' => fn ($q) => $q->whereIn('classes.class_id', $classIds)])
            ->get();
    }
}
