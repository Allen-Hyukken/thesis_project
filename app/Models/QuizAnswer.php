<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizAnswer extends Model
{
    protected $table = 'quiz_answers';

    protected $primaryKey = 'answer_id';

    public $timestamps = false;

    protected $fillable = [
        'submission_id',
        'question_id',
        'answer_text',
        'is_correct',
        'points_earned',
    ];

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
        ];
    }

    public function submission()
    {
        return $this->belongsTo(QuizSubmission::class, 'submission_id', 'submission_id');
    }

    public function question()
    {
        return $this->belongsTo(QuizQuestion::class, 'question_id', 'question_id');
    }
}
