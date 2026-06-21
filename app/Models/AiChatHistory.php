<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiChatHistory extends Model
{
    protected $table = 'ai_chat_history';
    protected $primaryKey = 'chat_id';
    public $timestamps = false; // only `created_at`, defaulted by the DB

    protected $fillable = ['course_id', 'student_id', 'role', 'message'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public function course() { return $this->belongsTo(Course::class, 'course_id', 'course_id'); }
    public function student() { return $this->belongsTo(User::class, 'student_id', 'user_id'); }
}
