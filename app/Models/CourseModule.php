<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseModule extends Model
{
    /**
     * Covers both "Lessons/Topics" (item_type = lesson) and
     * "Activities" (item_type = activity). They share the same
     * shape — a title plus body content — so one table is enough.
     */
    protected $table = 'course_modules';

    protected $primaryKey = 'module_id';

    protected $fillable = [
        'course_id',
        'item_type',
        'activity_type',
        'title',
        'content',
        'points',
        'due_at',
        'order_index',
        'ai_generated',
        'ai_status',
    ];

    protected function casts(): array
    {
        return [
            'ai_generated' => 'boolean',
            'due_at'       => 'datetime',
        ];
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id', 'course_id');
    }

    public function quizzes()
    {
        return $this->hasMany(Quiz::class, 'module_id', 'module_id');
    }

    public function exams()
    {
        return $this->hasMany(Exam::class, 'module_id', 'module_id');
    }

    public function submissions()
    {
        return $this->hasMany(ActivitySubmission::class, 'module_id', 'module_id');
    }

    public function isLesson(): bool
    {
        return $this->item_type === 'lesson';
    }

    public function isActivity(): bool
    {
        return $this->item_type === 'activity';
    }

    /**
     * Lessons safe to hand to the AI: teacher-typed, or AI-drafted and
     * already approved. Excludes pending_review/rejected drafts.
     */
    public function scopeVisibleToStudents($query)
    {
        return $query->where('item_type', 'lesson')
            ->where(function ($q) {
                $q->whereNull('ai_status')->orWhere('ai_status', 'approved');
            });
    }
}
