<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApplicantTemporaryEmail extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'applicant_id',
        'email',
        'status',
        'activated_at',
        'created_by',
        'updated_by'
    ];
}
