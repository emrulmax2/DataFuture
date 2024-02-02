<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Group extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'term_declaration_id',
        'course_id',
        'name',
        'evening_and_weekend',
        'active',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function term(){
        return $this->belongsTo(TermDeclaration::class, 'term_declaration_id');
    }

    public function course(){
        return $this->belongsTo(Course::class, 'course_id');
    }
}
