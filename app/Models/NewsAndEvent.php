<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NewsAndEvent extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'content',
        'fol_all',
        'active',

        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function students(){
        return $this->hasMany(NewsAndEventStudent::class, 'news_and_event_id', 'id');
    }

    public function documents(){
        return $this->hasMany(NewsAndEventDocument::class, 'news_and_event_id', 'id');
    }

    public function createdBy(){
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getCreatedAtHumanTimeAttribute(){
        $theInstance = Carbon::parse($this->created_at);
        return $theInstance->diffForHumans();
    }
}
