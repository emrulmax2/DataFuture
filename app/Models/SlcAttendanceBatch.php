<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Bulk attendance: confirmed codes, downloaded together as one SIS attendance import file. */
class SlcAttendanceBatch extends Model
{
    protected $fillable = ['code', 'status', 'header', 'term', 'academic_year_id', 'file_name', 'disk', 'path', 'file_date', 'created_by', 'downloaded_by', 'downloaded_at', 'uploaded_by', 'uploaded_at'];

    protected $casts = ['downloaded_at' => 'datetime', 'uploaded_at' => 'datetime', 'file_date' => 'date'];
}
