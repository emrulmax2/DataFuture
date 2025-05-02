<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentDocumentRequestForm extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'term_declaration_id',
        'letter_set_id',
        'name',
        'description',
        'service_type',
        'status',
        'student_consent',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
        'created_by',
        'updated_by',
    ];
    /**
     * The attributes that should be appended to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'created_at_formatted',
        'updated_at_formatted',
        'created_at_human',
        'updated_at_human',
        'deleted_at_human',
    ];

    /**
     * Get the formatted created_at attribute.
     *
     * @return string
     */
    public function getCreatedAtFormattedAttribute()
    {
        return $this->created_at ? $this->created_at->toFormattedDateString() : null;
    }
    /**
     * Get the formatted updated_at attribute.
     *
     * @return string
     */
    public function getUpdatedAtFormattedAttribute()
    {
        return $this->updated_at ? $this->updated_at->toFormattedDateString(): null;
    }

    /**
     * Get the human readable created_at attribute.
     *
     * @return string
     */
    public function getCreatedAtHumanAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    
    /**
     * Get the human readable updated_at attribute.
     *
     * @return string
     */
    public function getUpdatedAtHumanAttribute()
    {
        return $this->updated_at->diffForHumans();
    }
    /**
     * Get the human readable deleted_at attribute.
     *
     * @return string
     */
    public function getDeletedAtHumanAttribute()
    {
        return $this->deleted_at ? $this->deleted_at->diffForHumans() : null;
    }

    /**
     * Get the student that owns the document request form.
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
    /**
     * Get the term declaration that owns the document request form.
     */
    public function termDeclaration()
    {
        return $this->belongsTo(TermDeclaration::class);
    }
    /**
     * Get the letter set that owns the document request form.
     */
    public function letterSet()
    {
        return $this->belongsTo(LetterSet::class);
    }
}
