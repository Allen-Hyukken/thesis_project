<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ChecksStudentCourseAccess;
use App\Models\ActivitySubmission;
use App\Models\CourseModule;
use App\Models\StudentCourseProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ActivitySubmissionController extends Controller
{
    use ChecksStudentCourseAccess;

    public function store(Request $request, CourseModule $module)
    {
        if (! $module->isActivity()) {
            abort(404);
        }

        $user = Auth::user();

        if (! $this->studentHasAccessToCourse($module->course)) {
            abort(403);
        }

        $existing = ActivitySubmission::where('module_id', $module->module_id)
            ->where('student_id', $user->user_id)
            ->first();

        if ($existing && $existing->isGraded()) {
            return back()->withErrors([
                'submission_text' => 'This activity has already been graded and can no longer be edited.',
            ]);
        }

        $request->validate([
            'submission_text' => 'nullable|string',
            'file'            => 'nullable|file|max:51200',
        ]);

        if (! $request->filled('submission_text') && ! $request->hasFile('file')) {
            return back()->withErrors([
                'submission_text' => 'Provide a text answer or upload a file (or both).',
            ]);
        }

        $data = [
            'submission_text' => $request->submission_text,
            'status'          => 'submitted',
            'submitted_at'    => now(),
        ];

        if ($request->hasFile('file')) {
            $file                      = $request->file('file');
            $data['file_path']         = $file->store("activity-submissions/{$module->module_id}", 'local');
            $data['file_original_name'] = $file->getClientOriginalName();
        }

        ActivitySubmission::updateOrCreate(
            ['module_id' => $module->module_id, 'student_id' => $user->user_id],
            $data
        );

        // Track activity-level engagement for FR.1.7.2
        DB::statement(
            'INSERT INTO module_engagement
                (module_id, student_id, view_count, total_time_sec, last_viewed_at)
             VALUES (?, ?, 1, 0, NOW())
             ON DUPLICATE KEY UPDATE
                view_count     = view_count + 1,
                last_viewed_at = NOW()',
            [$module->module_id, $user->user_id]
        );

        // Recalculate course progress for FR.1.7.3
        StudentCourseProgress::recalculate($user->user_id, $module->course_id);

        return back()->with('success', 'Activity submitted.');
    }
}
