<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** /slc-registration › Data check: a "No action" remembered for later uploads of the same difference. */
class SlcBulkNoAction extends Model
{
    protected $fillable = ['student_id', 'field', 'slc_value', 'ours_value', 'slc_display', 'ours_display', 'slc_bulk_import_row_id', 'created_by', 'revoked_by', 'revoked_at'];

    protected $casts = ['revoked_at' => 'datetime'];

    public function scopeActive($q)
    {
        return $q->whereNull('revoked_at');
    }
}
