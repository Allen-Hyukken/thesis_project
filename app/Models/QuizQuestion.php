<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizQuestion extends Model
{
    protected $table = 'quiz_questions';

    protected $primaryKey = 'question_id';

    public $timestamps = false;

    protected $fillable = [
        'quiz_id',
        'question_text',
        'question_type',
        'correct_answer',
        'points',
        'order_index',
    ];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class, 'quiz_id', 'quiz_id');
    }

    public function choices()
    {
        return $this->hasMany(QuizQuestionChoice::class, 'question_id', 'question_id');
    }
}
