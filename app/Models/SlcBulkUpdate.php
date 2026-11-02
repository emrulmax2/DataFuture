<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A change made to our record from SLC data on /slc-registration (also written to student_archives). */
class SlcBulkUpdate extends Model
{
    protected $fillable = ['slc_bulk_import_row_id', 'student_id', 'field', 'from_value', 'to_value', 'created_by'];
}
