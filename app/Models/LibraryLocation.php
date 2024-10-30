<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LibraryLocation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "book_location";

    protected $fillable = [
        'name',
        'venue_id',
        'description',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at', 'created_at', 'updated_at'];

    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }


}
