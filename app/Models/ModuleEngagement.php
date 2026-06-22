<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModuleEngagement extends Model
{
    protected $table      = 'module_engagement';
    protected $primaryKey = 'engagement_id';
    public    $timestamps = false;

    protected $fillable = [
        'module_id', 'student_id', 'view_count', 'total_time_sec', 'last_viewed_at',
    ];

    protected function casts(): array
    {
        return ['last_viewed_at' => 'datetime'];
    }

    public function module()
    {
        return $this->belongsTo(CourseModule::class, 'module_id', 'module_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id', 'user_id');
    }
}
