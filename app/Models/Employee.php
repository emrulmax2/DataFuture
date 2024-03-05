<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class Employee extends Model
{
    use HasFactory ,SoftDeletes;

    protected $appends = ['full_name','age','retire','photo', 'photo_url'];

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
        "photo",
        "status",
    ];

    /**
     * The getter that return accessible URL for user photo.
     *
     * @var array
     */
    public function getPhotoUrlAttribute()
    {
        if ($this->photo !== null && Storage::disk('local')->exists('public/employees/'.$this->id.'/'.$this->photo)) {
            return Storage::disk('local')->url('public/employees/'.$this->id.'/'.$this->photo);
        } else {
            return asset('build/assets/images/placeholders/200x200.jpg');
        }
    }

    public function getPhotoAttribute($value){
        return $value;
    }

    public function setDateOfBirthAttribute($value) {  
        $this->attributes['date_of_birth'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }

    public function getDateOfBirthAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }
    
    public function getFullNameAttribute() {
        return strtoupper($this->first_name . ' ' . $this->last_name);
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

    public function payment(){
        return $this->hasOne(EmployeePaymentSetting::class, 'employee_id', 'id')->latestOfMany();
    }

    public function hourauth(){
        return $this->hasMany(EmployeeHourAuthorisedBy::class, 'employee_id', 'id');
    }

    public function holidayAuth(){
        return $this->hasMany(EmployeeHolidayAuthorisedBy::class, 'employee_id', 'id');
    }

    public function activePatterns(){
        $patterns = $this->hasMany(EmployeeWorkingPattern::class, 'employee_id', 'id');
        $patterns->getQuery()->where('end_to', '=', '')->orWhereNull('end_to');
        return $patterns;
    }

    public function employment(){
        return $this->hasOne(Employment::class, 'employee_id', 'id')->latestOfMany();
    }

    public function workingPattern(){
        return $this->hasOne(EmployeeWorkingPattern::class, 'employee_id', 'id')->latestOfMany();
    }
    
    public function eligibilities(){
        return $this->hasOne(EmployeeEligibilites::class, 'employee_id', 'id')->latestOfMany();
    }

    public function emergencyContact(){
        return $this->hasOne(EmployeeEmergencyContact::class, 'employee_id', 'id')->latestOfMany();
    }

    // public function latestBankDetail(){

    //     $bankDetail = $this->hasMany(EmployeeBankDetail::class, 'employee_id', 'id');
    //     $bankDetail->getQuery()->where('active', '=', 1)->latestOfMany();

    //     return $bankDetail;
    // }
}
