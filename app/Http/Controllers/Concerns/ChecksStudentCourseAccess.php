<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Course;
use Illuminate\Support\Facades\Auth;

trait ChecksStudentCourseAccess
{
    protected function studentHasAccessToCourse(Course $course): bool
    {
        if ($course->visibility === 'public') {
            return true;
        }

        $studentClassIds = Auth::user()->enrolledClasses()->pluck('classes.class_id');

        return $course->postedClasses()->whereIn('classes.class_id', $studentClassIds)->exists();
    }
}
