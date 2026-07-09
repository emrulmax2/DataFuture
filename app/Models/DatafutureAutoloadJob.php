<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DatafutureAutoloadJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
        'progress',
        'total',
        'processed',
        'message',
        'error',
        'payload',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'payload' => 'array',
    ];
}
