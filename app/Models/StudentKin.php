<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentKin extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'student_kins';

    protected $fillable = [
        'student_id',
        'name',
        'kins_relation_id',
        'mobile',
        'email',
        'address_line_1',
        'address_line_2',
        'state',
        'post_code',
        'city',
        'country',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function student(){
        return $this->belongsTo(Student::class, 'student_id');
    }
    public function relation(){
        return $this->belongsTo(KinsRelation::class, 'kins_relation_id');
    }
}
