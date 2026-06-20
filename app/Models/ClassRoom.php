<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassRoom extends Model
{
    /**
     * "Class" is a reserved word in PHP, so this model is named
     * ClassRoom but maps to the existing `classes` table.
     */
    protected $table = 'classes';

    protected $primaryKey = 'class_id';

    protected $fillable = [
        'teacher_id',
        'class_name',
        'subject',
        'description',
        'class_code',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id', 'user_id');
    }

    public function enrollments()
    {
        return $this->hasMany(ClassEnrollment::class, 'class_id', 'class_id');
    }

    public function students()
    {
        return $this->belongsToMany(
            User::class,
            'class_enrollments',
            'class_id',
            'student_id',
            'class_id',
            'user_id'
        )->wherePivot('status', 'active');
    }

    /**
     * Courses posted into this class. Many-to-many — a class can have
     * several courses posted to it over the term.
     */
    public function postedCourses()
    {
        return $this->belongsToMany(
            Course::class,
            'class_course_postings',
            'class_id',
            'course_id',
            'class_id',
            'course_id'
        )->withPivot('posted_at');
    }

    public function materials()
    {
        return $this->hasMany(ClassMaterial::class, 'class_id', 'class_id')->latest('created_at');
    }
}
