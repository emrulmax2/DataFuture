<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeNotes extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'employee_document_id',
        'opening_date',
        'note',
        'phase',
        'created_by',
        'updated_by',
    ];

    protected $dates = ['deleted_at'];

    public function document() {
        return $this->belongsTo(EmployeeDocuments::class, 'employee_document_id')->withTrashed();
    }
    
    public function user(){
        return $this->belongsTo(User::class, 'created_by');
    }

    public function setOpeningDateAttribute($value) {  
        $this->attributes['opening_date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }

    public function getOpeningDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }
}
