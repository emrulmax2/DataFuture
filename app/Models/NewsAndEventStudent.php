<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsAndEventStudent extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'news_and_event_id',
        'student_id',
    ];

    public function newsEvents(){
        return $this->belongsTo(NewsAndEvent::class, 'news_and_event_id');
    }

    public function student(){
        return $this->belongsTo(Student::class, 'student_id');
    }
}
