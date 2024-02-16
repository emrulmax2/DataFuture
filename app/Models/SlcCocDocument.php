<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SlcCocDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'slc_coc_id',
        'student_document_id',
        
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
    
    public function coc(){
        return $this->belongsTo(SlcCoc::class, 'slc_coc_id');
    }
    
    public function stddoc(){
        return $this->belongsTo(StudentDocument::class, 'student_document_id');
    }
}
