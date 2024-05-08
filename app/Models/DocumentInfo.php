<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentInfo extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'document_folder_id',
        'doc_type',
        'disk_type',
        'path',
        'display_file_name',
        'current_file_name',
        'expire_at',
        'reminder_at',
        
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];
}
