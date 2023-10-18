<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ComonSmtp extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'smtp_user',
        'smtp_pass',
        'smtp_host',
        'smtp_port',
        'smtp_encryption',
        'smtp_authentication',
        'account_type',
        'is_default',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function emails(){
        return $this->hasMany(ApplicantEmail::class, 'comon_smtp_id', 'id');
    }
}
