<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizQuestionChoice extends Model
{
    protected $table = 'quiz_question_choices';

    protected $primaryKey = 'choice_id';

    public $timestamps = false;

    protected $fillable = [
        'question_id',
        'choice_label',
        'choice_text',
        'is_correct',
    ];

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
        ];
    }

    public function question()
    {
        return $this->belongsTo(QuizQuestion::class, 'question_id', 'question_id');
    }
}
