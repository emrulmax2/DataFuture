<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentNote extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'student_document_id',
        'opening_date',
        'note',
        'phase',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function document() {
        return $this->belongsTo(StudentDocument::class, 'student_document_id');
    }
    
    public function user(){
        return $this->belongsTo(User::class, 'created_by');
    }

    public function setOpeningDateAttribute($value) {  
        $this->attributes['opening_date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }

    public function getOpeningDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }
}
