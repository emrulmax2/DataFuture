<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class HrVacancy extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'hr_vacancy_type_id',
        'title',
        'link',
        'date',
        'document',
        'active',

        'created_by',
        'updated_by',
    ];
    
    public function getDocumentUrlAttribute($value) {
        if ($this->document !== null && Storage::disk('local')->exists('public/vacancies/'.$this->id.'/'.$this->document)) {
            return Storage::disk('local')->url('public/vacancies/'.$this->id.'/'.$this->document);
        } else {
            return '';
        }
    }

    public function setDateAttribute($value) {  
        $this->attributes['date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }
    
    public function getDateAttribute($value) {
        return (!empty($value) && $value != '0000-00-00' ? date('d-m-Y', strtotime($value)) : '');
    }

    public function type(){
        return $this->belongsTo(HrVacancyType::class, 'hr_vacancy_type_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'created_by');
    }
}
