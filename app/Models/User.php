<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $primaryKey = 'user_id';

    protected $fillable = [
        'full_name',
        'email',
        'password_hash',
        'role',
        'avatar',
        'bio',
        'program',
        'year_level',
        'department',
        'position',
    ];



    protected $hidden = [
        'password_hash',
    ];

    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
        ];
    }

    /**
     * Classes this user teaches (role: teacher).
     */
    public function classesTaught()
    {
        return $this->hasMany(ClassRoom::class, 'teacher_id', 'user_id');
    }

    /**
     * Raw enrollment records for this user (role: student).
     */
    public function classEnrollments()
    {
        return $this->hasMany(ClassEnrollment::class, 'student_id', 'user_id');
    }

    /**
     * Classes this user has actively joined as a student.
     */
    public function enrolledClasses()
    {
        return $this->belongsToMany(
            ClassRoom::class,
            'class_enrollments',
            'student_id',
            'class_id',
            'user_id',
            'class_id'
        )->wherePivot('status', 'active');
    }

    public function activitySubmissions()
    {
        return $this->hasMany(ActivitySubmission::class, 'student_id', 'user_id');
    }

    public function quizSubmissions()
    {
        return $this->hasMany(QuizSubmission::class, 'student_id', 'user_id');
    }

    public function examSubmissions()
    {
        return $this->hasMany(ExamSubmission::class, 'student_id', 'user_id');
    }
}
