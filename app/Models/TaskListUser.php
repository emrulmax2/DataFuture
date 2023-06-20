<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskListUser extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'task_list_id',
        'user_id',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function task(){
        return $this->belongsTo(TaskList::class, 'task_list_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
}
