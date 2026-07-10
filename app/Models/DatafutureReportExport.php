<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class DatafutureReportExport extends Model
{
    use HasFactory;

    protected $appends = ['download_url'];

    protected $fillable = [
        'file_name',
        'file_path',
        'progress',
        'status',
        'payload',
        'error',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'payload' => 'array'
    ];

    public function getDownloadUrlAttribute()
    {
        if ($this->file_path !== null && Storage::disk('public')->exists($this->file_path)) {
            return Storage::disk('public')->url($this->file_path);
        } else {
            return false;
        }
    }

}
