<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class TermDeclaration extends Model
{
    use HasFactory,SoftDeletes;

    protected $guarded = [ "id" ];

    public function updatedBy(): HasOne 
    {
        return $this->hasOne(User::class);
    }

    public function createdBy(): HasOne
    {
        return $this->hasOne(User::class);
    }
    
    public function termType(){
        return $this->belongsTo(TermType::class, 'term_type_id');
    }

    public function academicYear() {
        return $this->belongsTo(AcademicYear::class);
    }

    public function installments() {
        return $this->hasMany(SlcInstallment::class, 'term_declaration_id', 'id');
    }


}
