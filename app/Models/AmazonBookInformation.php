<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class AmazonBookInformation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'amazon_book_informations';
    
    protected $appends = ['photo_url'];

    protected $fillable = [
        'title',
        'author',
        'publisher',
        'isbn13',
        'isbn10',
        'language',
        'number_of_pages',
        'publication_date',
        'picture_data',
        'image_type',
        'image_name',
        'edition',
        'price',
        'entry_date',
        'quantity',
        'remaining_qty_for_section',

        'created_by',
        'updated_by',
    ];

    protected $dates = ['deleted_at'];

    public function getPhotoUrlAttribute()
    {
        if ($this->image_name !== null && Storage::disk('local')->exists('public/amazon_book/'.$this->image_name)) {
            return Storage::disk('local')->url('public/amazon_book/'.$this->image_name);
        } else {
            return asset('build/assets/images/placeholders/200x200.jpg');
        }
    }

    public function setPublicationDateAttribute($value) {  
        $this->attributes['publication_date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : null);
    }
    
    public function getPublicationDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }

    public function book(){
        return $this->hasMany(LibraryBook::class, 'amazon_book_information_id', 'id');
    }
}
