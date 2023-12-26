<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmploymentReport extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'report_description',
        'file_name',
        'last_run',
        'created_by',
        'updated_by',
    ];

    protected $dates = ['deleted_at'];
}
