<?php

namespace App\Http\Controllers\Student;
use App\Http\Controllers\Controller;

use App\Http\Controllers\Concerns\ChecksStudentCourseAccess;
use App\Models\ActivitySubmission;
use App\Models\CourseModule;
use App\Models\StudentCourseProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ActivitySubmissionController extends Controller
{
    use ChecksStudentCourseAccess;

    public function store(Request $request, CourseModule $module)
    {
        if (! $module->isActivity()) abort(404);

        $user = Auth::user();

        if (! $this->studentHasAccessToCourse($module->course)) abort(403);

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
            $file = $request->file('file');
            // Remove old file if re-submitting
            if ($existing && $existing->file_path) {
                Storage::disk('local')->delete($existing->file_path);
            }
            $data['file_path']          = $file->store("activity-submissions/{$module->module_id}", 'local');
            $data['file_original_name'] = $file->getClientOriginalName();
            $data['file_mime_type']     = $file->getMimeType();
        }

        ActivitySubmission::updateOrCreate(
            ['module_id' => $module->module_id, 'student_id' => $user->user_id],
            $data
        );

        DB::statement(
            'INSERT INTO module_engagement
                (module_id, student_id, view_count, total_time_sec, last_viewed_at)
             VALUES (?, ?, 1, 0, NOW())
             ON DUPLICATE KEY UPDATE
                view_count     = view_count + 1,
                last_viewed_at = NOW()',
            [$module->module_id, $user->user_id]
        );

        StudentCourseProgress::recalculate($user->user_id, $module->course_id);

        return back()->with('success', 'Activity submitted successfully.');
    }

    /**
     * Download/preview a student's own submission file.
     */
    public function downloadFile(ActivitySubmission $submission)
    {
        $user = Auth::user();

        // Student can only download their own; teacher can download any in their course
        $isOwner   = (int) $submission->student_id === (int) $user->user_id;
        $isTeacher = $user->role === 'teacher'
            && (int) $submission->module->course->teacher_id === (int) $user->user_id;

        if (! $isOwner && ! $isTeacher) abort(403);

        if (! $submission->file_path || ! Storage::disk('local')->exists($submission->file_path)) {
            abort(404, 'File not found.');
        }

        return Storage::disk('local')->download(
            $submission->file_path,
            $submission->file_original_name
        );
    }

    /**
     * Stream submission file inline (for preview in browser).
     */
    public function previewFile(ActivitySubmission $submission)
    {
        $user = Auth::user();

        $isOwner   = (int) $submission->student_id === (int) $user->user_id;
        $isTeacher = $user->role === 'teacher'
            && (int) $submission->module->course->teacher_id === (int) $user->user_id;

        if (! $isOwner && ! $isTeacher) abort(403);

        if (! $submission->file_path || ! Storage::disk('local')->exists($submission->file_path)) {
            abort(404, 'File not found.');
        }

        $mime = $submission->file_mime_type ?? 'application/octet-stream';
        $path = Storage::disk('local')->path($submission->file_path);

        return response()->file($path, [
            'Content-Type'        => $mime,
            'Content-Disposition' => 'inline; filename="' . $submission->file_original_name . '"',
            'Cache-Control'       => 'private, max-age=3600',
        ]);
    }
}
