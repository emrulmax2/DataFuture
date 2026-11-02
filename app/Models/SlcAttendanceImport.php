<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** One SIS Attendance Confirmation Report uploaded to /slc-registration › Bulk attendance. */
class SlcAttendanceImport extends Model
{
    use SoftDeletes;

    protected $fillable = ['file_name', 'disk', 'path', 'header', 'hei', 'hei_name', 'report_date', 'academic_year_id', 'term', 'row_count', 'totals', 'error_lines', 'created_by'];

    protected $casts = ['totals' => 'array', 'error_lines' => 'array', 'report_date' => 'date'];

    public function rows()
    {
        return $this->hasMany(SlcAttendanceImportRow::class, 'slc_attendance_import_id')->orderBy('line_no');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }
}
