<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Students confirmed Yes, downloaded together as one SIS import file. */
class SlcBulkBatch extends Model
{
    protected $fillable = ['code', 'status', 'header', 'file_name', 'disk', 'path', 'file_date', 'created_by', 'downloaded_by', 'downloaded_at', 'uploaded_by', 'uploaded_at'];

    protected $casts = ['downloaded_at' => 'datetime', 'uploaded_at' => 'datetime', 'file_date' => 'date'];

    public function items()
    {
        return $this->hasMany(SlcBulkImportRow::class, 'slc_bulk_batch_id')->orderBy('decided_at')->orderBy('id');
    }
}
