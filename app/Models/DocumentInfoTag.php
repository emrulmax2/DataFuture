<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentInfoTag extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'document_info_id',
        'document_tag_id'
    ];

    public function info(){
        return $this->belongsTo(DocumentInfo::class, 'document_info_id');
    }

    public function tag(){
        return $this->belongsTo(DocumentTag::class, 'document_tag_id');
    }
}
