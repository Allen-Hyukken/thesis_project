<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $table = 'quizzes';

    protected $primaryKey = 'quiz_id';

    protected $fillable = [
        'course_id',
        'module_id',
        'title',
        'description',
        'ai_generated',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'ai_generated' => 'boolean',
            'is_published' => 'boolean',
        ];
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id', 'course_id');
    }

    public function module()
    {
        return $this->belongsTo(CourseModule::class, 'module_id', 'module_id');
    }

    public function questions()
    {
        return $this->hasMany(QuizQuestion::class, 'quiz_id', 'quiz_id')->orderBy('order_index');
    }
}
