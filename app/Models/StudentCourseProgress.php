<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class StudentCourseProgress extends Model
{
    protected $table      = 'student_course_progress';
    protected $primaryKey = 'progress_id';
    public    $timestamps = false;

    protected $fillable = [
        'student_id', 'course_id', 'modules_completed',
        'total_modules', 'completion_pct', 'last_accessed_at',
    ];

    protected function casts(): array
    {
        return ['last_accessed_at' => 'datetime'];
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id', 'user_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id', 'course_id');
    }

    /**
     * Option B — progress is driven by assessments, not lesson views.
     * total     = activities + published quizzes + published exams in the course
     * completed = submitted activities + quiz attempts + exam attempts by this student
     */
    public static function recalculate(int $studentId, int $courseId): void
    {
        // Total assessable items
        $totalActivities = CourseModule::where('course_id', $courseId)
            ->where('item_type', 'activity')
            ->count();

        $totalQuizzes = Quiz::where('course_id', $courseId)
            ->where('is_published', true)
            ->count();

        $totalExams = Exam::where('course_id', $courseId)
            ->where('is_published', true)
            ->count();

        $total = $totalActivities + $totalQuizzes + $totalExams;

        // Completed by this student
        $submittedActivities = ActivitySubmission::where('student_id', $studentId)
            ->whereHas('module', fn ($q) => $q->where('course_id', $courseId)->where('item_type', 'activity'))
            ->count();

        $attemptedQuizzes = QuizSubmission::where('student_id', $studentId)
            ->whereHas('quiz', fn ($q) => $q->where('course_id', $courseId))
            ->count();

        $attemptedExams = ExamSubmission::where('student_id', $studentId)
            ->whereHas('exam', fn ($q) => $q->where('course_id', $courseId))
            ->count();

        $completed     = $submittedActivities + $attemptedQuizzes + $attemptedExams;
        $completionPct = $total > 0 ? round(($completed / $total) * 100, 2) : 0;

        DB::table('student_course_progress')->upsert(
            [
                'student_id'        => $studentId,
                'course_id'         => $courseId,
                'modules_completed' => $completed,
                'total_modules'     => $total,
                'completion_pct'    => $completionPct,
                'last_accessed_at'  => now(),
            ],
            ['student_id', 'course_id'],
            ['modules_completed', 'total_modules', 'completion_pct', 'last_accessed_at']
        );
    }
}
