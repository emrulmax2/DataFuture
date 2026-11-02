<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Bulk attendance › Data check: what happens to one field that differs (coc / contact / accept). */
class SlcAttendanceRowField extends Model
{
    protected $fillable = ['slc_attendance_import_row_id', 'field', 'route', 'slc_coc_id', 'created_by'];
}
