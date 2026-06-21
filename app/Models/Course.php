<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $table = 'courses';

    protected $primaryKey = 'course_id';

    protected $fillable = [
        'class_id',
        'teacher_id',
        'title',
        'description',
        'learning_objectives',
        'visibility',
        'status',
        'ai_generated',
    ];

    protected function casts(): array
    {
        return [
            'ai_generated' => 'boolean',
        ];
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id', 'user_id');
    }

    public function classRoom()
    {
        return $this->belongsTo(ClassRoom::class, 'class_id', 'class_id');
    }

    /**
     * All content rows (lessons/topics AND activities — see item_type).
     */
    public function modules()
    {
        return $this->hasMany(CourseModule::class, 'course_id', 'course_id')->orderBy('order_index');
    }

    public function lessons()
    {
        return $this->modules()->where('item_type', 'lesson');
    }

    public function activities()
    {
        return $this->modules()->where('item_type', 'activity');
    }

    public function quizzes()
    {
        return $this->hasMany(Quiz::class, 'course_id', 'course_id');
    }

    public function exams()
    {
        return $this->hasMany(Exam::class, 'course_id', 'course_id');
    }

    /**
     * Classes this (published) course has been posted into. Many-to-many —
     * the same course can be posted to several class sections.
     */
    public function postedClasses()
    {
        return $this->belongsToMany(
            ClassRoom::class,
            'class_course_postings',
            'course_id',
            'class_id',
            'course_id',
            'class_id'
        )->withPivot('posted_at');
    }

    /**
     * Plain-text bundle of this course's teacher-approved lesson content —
     * the only material the AI Study Assistant / Flashcard generator may
     * draw from (FR.1.5.2).
     */
    public function studyContent(int $charLimit = 15000): string
    {
        $text = $this->modules()
            ->visibleToStudents()
            ->get()
            ->map(fn ($m) => "## {$m->title}\n\n{$m->content}")
            ->implode("\n\n---\n\n");

        return mb_strlen($text) > $charLimit
            ? mb_substr($text, 0, $charLimit) . "\n\n[...content truncated...]"
            : $text;
    }
}
