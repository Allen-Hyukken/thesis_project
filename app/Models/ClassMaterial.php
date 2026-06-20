<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassMaterial extends Model
{
    protected $table = 'class_materials';

    protected $primaryKey = 'material_id';

    public $timestamps = false;

    protected $fillable = [
        'class_id',
        'teacher_id',
        'title',
        'original_filename',
        'stored_path',
        'mime_type',
        'size_bytes',
    ];

    public function classRoom()
    {
        return $this->belongsTo(ClassRoom::class, 'class_id', 'class_id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id', 'user_id');
    }

    public function humanSize(): string
    {
        $bytes = (int) $this->size_bytes;

        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1) . ' MB';
        }

        if ($bytes >= 1024) {
            return round($bytes / 1024, 1) . ' KB';
        }

        return $bytes . ' B';
    }
}
