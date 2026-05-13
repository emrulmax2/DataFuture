<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DatafutureReportExport extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_name',
        'file_path',
        'progress',
        'status',
        'payload',
        'error'
    ];

    protected $casts = [
        'payload' => 'array'
    ];

}
