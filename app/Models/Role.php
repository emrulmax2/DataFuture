<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'type',
        'display_name',
        'created_by',
        'updated_by',
    ];

    protected $dates = ['deleted_at'];

    public function creation(){
        return $this->hasMany(PermissionTemplate::class);
    }
}
