<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    use HasFactory;
    protected $fillable = [
        'course_id', 'teacher_id', 'title', 'url', 'description'
    ];

    // علاقة مع Course
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    // علاقة مع Teacher
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}
