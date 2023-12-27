<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DatafutureField extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'datafuture_field_category_id',
        'name',
        'type',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $dates = ['deleted_at'];

    public function category(){
        return $this->belongsTo(DatafutureFieldCategory::class, 'datafuture_field_category_id');
    }

}
