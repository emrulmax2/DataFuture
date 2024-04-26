<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccCsvFile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'acc_bank_id',
        'name',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function trans(){
        return $this->hasMany(AccCsvTransaction::class, 'acc_csv_file_id', 'id');
    }

    public function bank(){
        return $this->belongsTo(AccBank::class, 'acc_bank_id');
    }
}
