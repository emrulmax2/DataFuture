<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentOrderItem extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $table = 'student_order_items';
    public function studentOrder()
    {
        return $this->belongsTo(StudentOrder::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
    public function letterSet()
    {
        return $this->belongsTo(LetterSet::class);
    }
    public function termDeclaration()
    {
        return $this->belongsTo(TermDeclaration::class);
    }
    public function studentDocumentRequestForm()
    {
        return $this->belongsTo(StudentDocumentRequestForm::class);
    }
}
