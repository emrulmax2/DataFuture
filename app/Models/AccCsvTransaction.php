<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccCsvTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'acc_csv_file_id',
        'trans_date',
        'description',
        'amount',
        'transaction_type',
        'flow',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function files(){
        return $this->belongsTo(AccCsvFile::class, 'acc_csv_file_id');
    }
}
