<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Status extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'process_list_id',
        'letter_set_id',
        'signatory_id',
        'active',
        'email_template_id',
        'eligible_for_award',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function process(){
        return $this->belongsTo(ProcessList::class, 'process_list_id', 'id');
    }

    public function letter(){
        return $this->belongsTo(LetterSet::class, 'letter_set_id', 'id');
    }

    public function mail(){
        return $this->belongsTo(EmailTemplate::class, 'email_template_id', 'id');
    }

    public function signatory(){
        return $this->belongsTo(Signatory::class, 'signatory_id', 'id');
    }
}
