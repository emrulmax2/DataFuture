<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IssueType extends Model
{
    use HasFactory,SoftDeletes;

    protected $guarded = ['id'];

    public function reportItAlls(){
        return $this->hasMany(ReportItAll::class,'issue_type_id','id');
    }
    public function smtp(){
        return $this->belongsTo(ComonSmtp::class,'comon_smtp_id','id');
    }
}
