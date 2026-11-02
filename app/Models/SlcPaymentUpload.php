<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** /slc-registration › Bulk payment upload: one SLC remittance file as uploaded. */
class SlcPaymentUpload extends Model
{
    protected $fillable = ['file_name', 'disk', 'path', 'header', 'hei', 'hei_name', 'remittance_date', 'header_count', 'header_total', 'line_count', 'line_total', 'error_lines', 'created_by'];

    protected $casts = ['remittance_date' => 'date', 'error_lines' => 'array'];

    public function rows()
    {
        return $this->hasMany(SlcPaymentUploadRow::class);
    }
}
