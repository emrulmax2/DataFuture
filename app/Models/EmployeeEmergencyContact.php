<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeEmergencyContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'emergency_contact_name',
        'kins_relation_id',
        'emergency_contact_address',
        'emergency_contact_telephone',
        'emergency_contact_mobile',
        'emergency_contact_email',
        'address_id'
    ];

    public function kin() {
        return $this->belongsTo(KinsRelation::class, 'kins_relation_id');
    }

    public function address() {
        return $this->belongsTo(Address::class, 'address_id');
    }
}
