<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentAddressUpdateRequestNote extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_address_update_request_id',
        'note',

        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function addrRequest(){
        $this->belongsTo(StudentAddressUpdateRequest::class, 'student_address_update_request_id');
    }
}
