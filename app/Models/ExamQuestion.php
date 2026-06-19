<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamQuestion extends Model
{
    protected $table = 'exam_questions';

    protected $primaryKey = 'question_id';

    public $timestamps = false;

    protected $fillable = [
        'exam_id',
        'question_text',
        'question_type',
        'correct_answer',
        'points',
        'order_index',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class, 'exam_id', 'exam_id');
    }

    public function choices()
    {
        return $this->hasMany(ExamQuestionChoice::class, 'question_id', 'question_id');
    }
}
