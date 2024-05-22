<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class DocumentInfo extends Model
{
    use HasFactory, SoftDeletes;

    protected $appends = ['download_url'];

    protected $fillable = [
        'document_folder_id',
        'doc_type',
        'disk_type',
        'path',
        'display_file_name',
        'current_file_name',
        'expire_at',
        'reminder_at',
        'description',
        
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function getDownloadUrlAttribute(){
        if ($this->current_file_name !== null && $this->path !== null && Storage::disk('local')->exists('public/file-manager/'.$this->path.'/'.$this->current_file_name)) {
            return Storage::disk('local')->url('public/file-manager/'.$this->path.'/'.$this->current_file_name);
        } else {
            return false;
        }
    }

    public function setExpireAtAttribute($value) {  
        $this->attributes['expire_at'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }

    public function getDateOfBirthAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }
}
