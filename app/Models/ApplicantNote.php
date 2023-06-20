<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApplicantNote extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'applicant_id',
        'applicant_document_id',
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

    public function document(){
        return $this->belongsTo(ApplicantDocument::class, 'applicant_document_id');
    }
    
    public function user(){
        return $this->belongsTo(User::class, 'created_by');
    }
}
