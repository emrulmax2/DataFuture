<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One student line of an SLC attendance upload, and the student it was matched to. */
class SlcAttendanceImportRow extends Model
{
    protected $fillable = ['slc_attendance_import_id', 'line_no', 'raw', 'ssn', 'data', 'student_id', 'match_method', 'linked_by', 'linked_at',
        'attendance_code', 'code_by', 'code_at', 'confirmed_code', 'confirmed_by', 'confirmed_at', 'slc_attendance_batch_id', 'earlier_batch_id', 'slc_coc_id', 'carried_from_id',
        'slc_attendance_id', 'slc_installment_id', 'slc_recorded', 'slc_recorded_by', 'slc_recorded_at',
        'sis_rejected_batch_id', 'sis_rejected_item', 'sis_rejected_code', 'sis_rejected_reason', 'sis_rejected_by', 'sis_rejected_at',
        'record_session_term', 'record_term_declaration_id', 'record_term_by', 'record_term_at'];

    protected $casts = ['data' => 'array', 'linked_at' => 'datetime', 'code_at' => 'datetime', 'confirmed_at' => 'datetime', 'slc_recorded_at' => 'datetime', 'sis_rejected_at' => 'datetime', 'sis_rejected_item' => 'boolean', 'record_term_at' => 'datetime'];

    public function fields()
    {
        return $this->hasMany(SlcAttendanceRowField::class, 'slc_attendance_import_row_id');
    }

    public function import()
    {
        return $this->belongsTo(SlcAttendanceImport::class, 'slc_attendance_import_id');
    }
}
