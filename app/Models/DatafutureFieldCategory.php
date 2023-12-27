<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DatafutureFieldCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'created_by',
        'updated_by',
    ];

    protected $dates = ['deleted_at'];

    public function fields(){
        return $this->hasMany(DatafutureField::class, 'datafuture_field_category_id', 'id');
    }
}
