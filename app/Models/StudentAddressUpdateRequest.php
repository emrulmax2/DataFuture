<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentAddressUpdateRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'student_task_id',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'postal_code',
        'country',
        'latitude',
        'longitude',
        'status',

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
        $this->belongsTo(Student::class, 'student_id');
    }

    public function task(){
        return $this->belongsTo(StudentTask::class, 'student_task_id');
    }

    public function docs(){
        return $this->hasMany(StudentAddressUpdateRequestDocument::class, 'student_address_update_request_id', 'id')->orderBy('id', 'DESC');
    }

    public function notes(){
        return $this->hasMany(StudentAddressUpdateRequestNote::class, 'student_address_update_request_id', 'id')->orderBy('id', 'DESC');
    }
}
