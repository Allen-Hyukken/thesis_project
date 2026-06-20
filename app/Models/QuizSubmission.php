<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizSubmission extends Model
{
    protected $table = 'quiz_submissions';

    protected $primaryKey = 'submission_id';

    public $timestamps = false;

    protected $fillable = [
        'quiz_id',
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

    public function quiz()
    {
        return $this->belongsTo(Quiz::class, 'quiz_id', 'quiz_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id', 'user_id');
    }

    public function answers()
    {
        return $this->hasMany(QuizAnswer::class, 'submission_id', 'submission_id');
    }

    /**
     * True if any open-ended answer hasn't been manually scored yet.
     */
    public function needsReview(): bool
    {
        return $this->answers->contains(fn ($a) => $a->is_correct === null && $a->points_earned === null);
    }
}
