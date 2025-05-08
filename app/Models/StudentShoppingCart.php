<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentShoppingCart extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $table = 'student_shopping_carts';

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
    public function letterSet()
    {
        return $this->belongsTo(LetterSet::class);
    }
}
