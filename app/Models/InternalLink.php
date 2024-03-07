<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InternalLink extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'name',
        'parent_id',
        'image',
        'link',
        'created_by',
        'updated_by',
    ];
    
    protected $dates = ['deleted_at'];
}
