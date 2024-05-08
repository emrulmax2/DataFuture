<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentRoleAndPermission extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'display_name',
        'type',
        'create',
        'read',
        'update',
        'delete',
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
