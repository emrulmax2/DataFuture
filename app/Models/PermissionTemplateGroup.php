<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PermissionTemplateGroup extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'permission_template_id',
        'name',
        'R',
        'W',
        'D',
        'created_by',
        'updated_by'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function template(){
        return $this->belongsTo(PermissionTemplate::class, 'permission_template_id');
    }
}
