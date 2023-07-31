<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApplicantEmail extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'applicant_id',
        'comon_smtp_id',
        'subject',
        'body',
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
    
    public function user(){
        return $this->belongsTo(User::class, 'created_by');
    }
    
    public function smtp(){
        return $this->belongsTo(ComonSmtp::class, 'comon_smtp_id');
    }

    public function documents(){
        return $this->belongsToMany(ApplicantDocument::class, 'applicant_emails_attachments');
    }
}
