<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Venue extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'idnumber',
        'ukprn',
        'postcode',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function ips(){
        return $this->hasMany(VenueIpAddress::class, 'venue_id', 'id');
    }

    public function employees()
    {
        return $this->belongsToMany(Employee::class);
    }
}
