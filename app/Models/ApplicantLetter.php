<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApplicantLetter extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'applicant_id',
        'letter_set_id',
        'signatory_id',
        'comon_smtp_id',
        'is_email_or_attachment',
        'applicant_document_id',
        'issued_by',
        'issued_date',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function applicant(){
        return $this->belongsTo(Applicant::class, 'applicant_id');
    }

    public function document(){
        return $this->belongsTo(ApplicantDocument::class, 'applicant_document_id');
    }
}
