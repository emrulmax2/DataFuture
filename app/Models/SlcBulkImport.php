<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** One SIS Registration Confirmation Report uploaded to /slc-registration. */
class SlcBulkImport extends Model
{
    use SoftDeletes;

    protected $fillable = ['file_name', 'disk', 'path', 'header', 'footer', 'hei', 'academic_year_id', 'row_count', 'error_lines', 'created_by'];

    protected $casts = ['error_lines' => 'array'];

    public function rows()
    {
        return $this->hasMany(SlcBulkImportRow::class, 'slc_bulk_import_id')->orderBy('line_no');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }
}
