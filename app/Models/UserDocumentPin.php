<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDocumentPin extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'pin',
        'failed_attempts',
        'locked_until',
        'generated_at',
        'created_by',
        'updated_by',
    ];

    protected $hidden = ['pin'];

    protected $casts = [
        'locked_until' => 'datetime',
        'generated_at' => 'datetime',
    ];

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
}
