<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class AccBank extends Model
{
    use HasFactory, SoftDeletes;

    protected $appends = ['image_url'];

    protected $fillable = [
        'bank_name',
        'bank_image',
        'status',
        'audit_status',
        'opening_balance',
        'opening_date',
        'created_by',
        'updated_by',
    ];


    public function getImageUrlAttribute()
    {
        if ($this->bank_image !== null && $this->bank_image != '' && Storage::disk('local')->exists('public/banks/'.$this->bank_image)) {
            return Storage::disk('local')->url('public/banks/'.$this->bank_image);
        } else {
            return asset('build/assets/images/placeholders/200x200.jpg');
        }
    }

    public function setOpeningDateAttribute($value) {  
        $this->attributes['opening_date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }
    
    public function getOpeningDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];
}
