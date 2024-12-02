<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccAssetRegister extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'acc_transaction_id',
        'description',
        'acc_asset_type_id',
        'location',
        'serial',
        'barcode',
        'life',
        'active',
        
        'created_by',
        'updated_by',
    ];


    protected $dates = ['deleted_at'];

    public function trans(){
        return $this->belongsTo(AccTransaction::class, 'acc_transaction_id');
    }

    public function type(){
        return $this->belongsTo(AccAssetType::class, 'acc_asset_type_id');
    }
}
