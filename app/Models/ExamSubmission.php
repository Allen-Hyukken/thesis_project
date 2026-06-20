<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamSubmission extends Model
{
    protected $table = 'exam_submissions';

    protected $primaryKey = 'submission_id';

    public $timestamps = false;

    protected $fillable = [
        'exam_id',
        'student_id',
        'score',
        'max_score',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
        ];
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class, 'exam_id', 'exam_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id', 'user_id');
    }

    public function answers()
    {
        return $this->hasMany(ExamAnswer::class, 'submission_id', 'submission_id');
    }

    public function needsReview(): bool
    {
        return $this->answers->contains(fn ($a) => $a->is_correct === null && $a->points_earned === null);
    }
}
