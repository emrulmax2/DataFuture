<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Employee extends Model
{
    use HasFactory ,SoftDeletes;

    protected $appends = ['full_name','age','retire'];

    protected $fillable = [
        'user_id',
        'title_id',
        'first_name',
        'last_name',
        'date_of_birth',
        'sex_identifier_id',
        'nationality_id',
        'address_id',
        'ethnicity_id',
        'telephone',
        'mobile',
        'email',
        'created_by',
        'updated_by',
        'ni_number',
        'car_reg_number',
        'drive_license_number',
        'disability_status',
    ];

    public function setDateOfBirthAttribute($value) {  
        $this->attributes['date_of_birth'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }

    public function getDateOfBirthAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }
    
    public function getFullNameAttribute() {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function venues()
    {
        return $this->belongsToMany(Venue::class);
    }
    
    public function title(){
        return $this->belongsTo(Title::class, 'title_id');
    }

    public function address(){
        return $this->belongsTo(Address::class, 'address_id');
    }

    public function ethnicity(){
        return $this->belongsTo(Ethnicity::class, 'ethnicity_id');
    }

    public function nationality(){
        return $this->belongsTo(Country::class, 'nationality_id');
    }

    public function disability(){

        return $this->belongsToMany(Disability::class, 'employee_disability', 'employee_id','disability_id');
    }

    public function sex(){
        return $this->belongsTo(SexIdentifier::class, 'sex_identifier_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }


    public function getAgeAttribute(){

        return Carbon::parse($this->attributes['date_of_birth'])->diff(Carbon::now())->format('%y years, %m months and %d days');
        
    }

    public function getRetireAttribute() {
        
        $retirementAge = Carbon::parse($this->attributes['date_of_birth'])->addYears(60);
        return $retirementAge->diff(Carbon::now())->format('%y years, %m months and %d days');
    }
}
