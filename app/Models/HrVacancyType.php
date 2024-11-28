<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HrVacancyType extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'name',

        'created_by',
        'updated_by',
    ];

    public function vacancy(){
        return $this->hasMany(HrVacancy::class, 'hr_vacancy_type_id', 'id');
    }
}
