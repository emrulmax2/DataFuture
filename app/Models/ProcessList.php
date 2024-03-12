<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class ProcessList extends Model
{
    use HasFactory, SoftDeletes;

    protected $appends = ['image_url'];

    protected $fillable = [
        'name',
        'phase',
        'auto_feed',
        'image',
        'image_path',
        'created_by',
        'updated_by',
    ];

    protected $dates = ['deleted_at'];

    public function tasks(){
        return $this->hasMany(TaskList::class, 'process_list_id', 'id');
    }

    /**
     * The getter that return accessible URL for user photo.
     *
     * @var array
     */
    public function getImageUrlAttribute()
    {
        if ($this->image !== null && Storage::disk('local')->exists('public/process/'.$this->id.'/'.$this->image)) {
            return Storage::disk('local')->url('public/process/'.$this->id.'/'.$this->image);
        } else {
            return asset('build/assets/images/placeholders/200x200.jpg');
        }
    }
}
