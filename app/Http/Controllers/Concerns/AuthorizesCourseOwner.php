<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Course;
use Illuminate\Support\Facades\Auth;

trait AuthorizesCourseOwner
{
    protected function authorizeOwner(Course $course): void
    {
        if ((int) $course->teacher_id !== (int) Auth::id()) {
            abort(403);
        }
    }
}
