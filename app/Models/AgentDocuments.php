<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AgentDocuments extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'agent_id',
        'document_setting_id',
        'hard_copy_check',
        'doc_type',
        'disk_type',
        'path',
        'display_file_name',
        'current_file_name',
        'type',
        'created_by',
        'updated_by',
    ];

    protected $dates = ['deleted_at'];

    public function agent(){
        return $this->belongsTo(Agent::class, 'agent_id');
    }

    public function documentSetting(){
        return $this->belongsTo(DocumentSettings::class, 'document_setting_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'created_by');
    }
}
