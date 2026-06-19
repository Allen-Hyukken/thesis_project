<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassEnrollment extends Model
{
    protected $table = 'class_enrollments';

    protected $primaryKey = 'enrollment_id';

    // The table only has `enrolled_at` (defaulted by MySQL), no `updated_at`,
    // so Eloquent's automatic timestamp handling is disabled here.
    public $timestamps = false;

    protected $fillable = [
        'class_id',
        'student_id',
        'enrolled_by',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'enrolled_at' => 'datetime',
        ];
    }

    public function classRoom()
    {
        return $this->belongsTo(ClassRoom::class, 'class_id', 'class_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id', 'user_id');
    }
}
