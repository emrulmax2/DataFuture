<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Lab404\Impersonate\Models\Impersonate;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes ,Impersonate;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'gender',
        'active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The attributes that appends to returned entities.
     *
     * @var array
     */
    protected $appends = ['photo', 'photo_url', 'remote_access', 'full_name'];

    /**
     * The getter that return accessible URL for user photo.
     *
     * @var array
     */
    public function getPhotoUrlAttribute()
    {
        if ($this->photo !== null && Storage::disk('s3')->exists('public/users/'.$this->id.'/'.$this->photo)) {
            return Storage::disk('s3')->url('public/users/'.$this->id.'/'.$this->photo);
        } else {
            return asset('build/assets/images/placeholders/200x200.jpg');
        }
    }
    

    public function getPhotoAttribute($value){
        return $value;
    }

    public function userRole(){
        return $this->hasMany(UserRole::class, 'user_id', 'id');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id');
    }

    public function interviews(): HasMany
    {
        return $this->hasMany(ApplicantInterview::class);
    }

    public function getFullNameAttribute(){
        return (isset($this->employee->full_name) && !empty($this->employee->full_name) ? $this->employee->full_name : $this->name);
    }

    public function employee(){
        return $this->hasOne(Employee::class, 'user_id', 'id')->withTrashed()->latestOfMany();
    }
    
    public function priv(){
        return $this->hasMany(UserPrivilege::class, 'user_id', 'id')
            ->select('access', 'name')->pluck('access', 'name')->toArray();
    }
    
    public function getRemoteAccessAttribute(){
        $userip = auth()->user()->last_login_ip;
        $ips = VenueIpAddress::pluck('ip')->unique()->toArray();
        $ips = (!empty($ips) ? $ips : ['62.31.168.43', '79.171.153.100', '149.34.178.243']);
        $remoteAccess = UserPrivilege::where('user_id', auth()->user()->id)->where('category', 'remote_access')->where('name', 'ra_status')->get()->first();
        if(isset($remoteAccess->access) && $remoteAccess->access == 1):
            $range = UserPrivilege::where('user_id', auth()->user()->id)->where('category', 'remote_access')->where('name', 'in_range')->get()->first();
            $dateRange = UserPrivilege::where('user_id', auth()->user()->id)->where('category', 'remote_access')->where('name', 'date_range')->get()->first();
            if((isset($range->access) && $range->access == 1) && isset($dateRange->access) && !empty($dateRange->access)):
                $dates = explode(' - ', $dateRange->access);
                $startDate = (isset($dates[0]) && !empty($dates[0]) ? date('Y-m-d', strtotime($dates[0])) : '');
                $endDate = (isset($dates[1]) && !empty($dates[1]) ? date('Y-m-d', strtotime($dates[1])) : '');
                if(!empty($startDate) && !empty($endDate)):
                    $today = date('Y-m-d');
                    if($today >= $startDate && $today <= $endDate):
                        return true;
                    else:
                        if(is_array($ips) && in_array($userip, $ips)):
                            return true;
                        else:
                            return false;
                        endif;
                    endif;
                else:
                    return true;
                endif;
            else:
                return true;
            endif;
        else:
            if(is_array($ips) && in_array($userip, $ips)):
                return 'true';
            else:
                return false;
            endif;
        endif;
    }

    public function hourauth(){
        return $this->hasMany(EmployeeHourAuthorisedBy::class, 'user_id', 'id');
    }

    public function holiauth(){
        return $this->hasMany(EmployeeHolidayAuthorisedBy::class, 'user_id', 'id');
    }
    
}
