<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Flashcard extends Model
{
    protected $table = 'flashcards';
    protected $primaryKey = 'flashcard_id';
    public $timestamps = false; // only `generated_at`, defaulted by the DB

    protected $fillable = ['course_id', 'module_id', 'student_id', 'front_text', 'back_text'];

    protected function casts(): array
    {
        return ['generated_at' => 'datetime'];
    }

    public function course() { return $this->belongsTo(Course::class, 'course_id', 'course_id'); }
    public function module() { return $this->belongsTo(CourseModule::class, 'module_id', 'module_id'); }
    public function student() { return $this->belongsTo(User::class, 'student_id', 'user_id'); }
}
