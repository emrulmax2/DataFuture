<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One line of the /slc-registration Activity page. user_id null = System. */
class SlcBulkActivity extends Model
{
    protected $fillable = ['slc_bulk_import_id', 'user_id', 'text'];
}
