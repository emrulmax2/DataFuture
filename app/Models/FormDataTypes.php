<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormDataTypes extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'text_input',
        'number_input',
        'checkbox',
        'switch',
        'radio_button',
        'phone',
        'email',
        'date_format',
        'date_range',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];
}
