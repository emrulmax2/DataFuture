<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentOtherPersonalInformation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'sexual_orientation_id',
        'hesa_gender_id',
        'religion_id',
        'created_by',
        'updated_by',
    ];

    public function student(){
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function sexori(){
        return $this->belongsTo(SexualOrientation::class, 'sexual_orientation_id');
    }

    public function gender(){
        return $this->belongsTo(HesaGender::class, 'hesa_gender_id');
    }

    public function religion(){
        return $this->belongsTo(Religion::class, 'religion_id');
    }

}
