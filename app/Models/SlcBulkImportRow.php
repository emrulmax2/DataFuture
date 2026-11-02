<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One student line of an SLC upload: how it was matched, what was decided, which batch it went into. */
class SlcBulkImportRow extends Model
{
    protected $fillable = [
        'slc_bulk_import_id', 'line_no', 'raw', 'ssn', 'data', 'student_id', 'match_method', 'linked_by', 'linked_at',
        'decision', 'decision_reason', 'decided_by', 'decided_at', 'attendance_checked', 'slc_bulk_batch_id', 'earlier_batch_id', 'slc_registration_id', 'slc_agreement_id', 'slc_recorded', 'slc_recorded_by', 'slc_recorded_at', 'history', 'carried_from_id',
    ];

    protected $casts = ['data' => 'array', 'history' => 'array', 'linked_at' => 'datetime', 'decided_at' => 'datetime', 'attendance_checked' => 'boolean', 'slc_recorded_at' => 'datetime'];

    public function import()
    {
        return $this->belongsTo(SlcBulkImport::class, 'slc_bulk_import_id');
    }

    public function fields()
    {
        return $this->hasMany(SlcBulkRowField::class, 'slc_bulk_import_row_id');
    }

    public function batch()
    {
        return $this->belongsTo(SlcBulkBatch::class, 'slc_bulk_batch_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
