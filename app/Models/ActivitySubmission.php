<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivitySubmission extends Model
{
    protected $table = 'activity_submissions';

    protected $primaryKey = 'submission_id';

    public $timestamps = false;

    protected $fillable = [
        'module_id',
        'student_id',
        'submission_text',
        'file_data',
        'file_original_name',
        'file_mime_type',
        'score',
        'feedback',
        'status',
        'submitted_at',
        'graded_at',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'graded_at'    => 'datetime',
        ];
    }

    public function module()
    {
        return $this->belongsTo(CourseModule::class, 'module_id', 'module_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id', 'user_id');
    }

    public function isGraded(): bool
    {
        return $this->status === 'graded';
    }
}
