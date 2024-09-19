<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendanceExcuseDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'attendance_excuse_id',
        'hard_copy_check',
        'doc_type',
        'disk_type',
        'path',
        'display_file_name',
        'current_file_name',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function excuse(){
        return $this->belongsTo(AttendanceExcuse::class, 'attendance_excuse_id');
    }
}
