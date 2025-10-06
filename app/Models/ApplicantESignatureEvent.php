<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicantESignatureEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'applicant_id',
        'user_type',
        'event_type',
        'event_description',
        'ip_address',
        'browser',
        'os',
        'latitude',
        'longitude',
        'extra_field'
    ];


    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
    */
    protected $casts = [
        'extra_field' => 'array',
    ];


    public function convertToDMS($decimal, $isLat = true)
    {
        $direction = $decimal >= 0 ? ($isLat ? 'N' : 'E') : ($isLat ? 'S' : 'W');
        $decimal = abs($decimal);
        $degrees = floor($decimal);
        $minutesDecimal = ($decimal - $degrees) * 60;
        $minutes = floor($minutesDecimal);
        $seconds = ($minutesDecimal - $minutes) * 60;

        return sprintf("%d° %d' %.5f\" %s", $degrees, $minutes, $seconds, $direction);
    }

    public function getLatitudeDMSAttribute()
    {
        return $this->convertToDMS($this->latitude, true);
    }

    public function getLongitudeDMSAttribute()
    {
        return $this->convertToDMS($this->longitude, false);
    }

    public function applicant()
    {
        return $this->belongsTo(Applicant::class, 'applicant_id');
    }

    public function user()
    {
        return $this->hasOneThrough(User::class,AdminESignature::class,'applicant_id', 'id', 'applicant_id', 'user_id');
    }
}
