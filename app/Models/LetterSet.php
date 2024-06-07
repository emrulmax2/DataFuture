<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LetterSet extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'letter_type',
        'letter_title',
        'description',
        'admission',
        'live',
        'hr',
        'status',

        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];
}
