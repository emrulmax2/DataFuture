<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProcessList extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'phase',
        'created_by',
        'updated_by',
    ];

    protected $dates = ['deleted_at'];

    public function tasks(){
        return $this->hasMany(TaskList::class, 'process_list_id', 'id');
    }
}
