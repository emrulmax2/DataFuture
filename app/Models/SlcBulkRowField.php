<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** What happens to one field that differs from SLC (coc / contact / accept) and its progress. */
class SlcBulkRowField extends Model
{
    protected $fillable = ['slc_bulk_import_row_id', 'field', 'route', 'state', 'state_by', 'state_at', 'slc_coc_id', 'carried_from_id', 'created_by'];

    protected $casts = ['state_at' => 'datetime'];

    public function row()
    {
        return $this->belongsTo(SlcBulkImportRow::class, 'slc_bulk_import_row_id');
    }
}
