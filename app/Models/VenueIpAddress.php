<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VenueIpAddress extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'venue_id',
        'ip',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function venue(){
        return $this->belongsTo(Venue::class, 'venue_id');
    }
}
