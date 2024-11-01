<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LibraryBook extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'book_location_id',
        'amazon_book_information_id',
        'book_barcode',
        'book_status',
        'created_by',
        'updated_by',
    ];

    protected $dates = ['deleted_at'];

    public function abi(){
        return $this->belongsTo(AmazonBookInformation::class, 'amazon_book_information_id', 'id');
    }

    public function location(){
        return $this->belongsTo(LibraryLocation::class, 'book_location_id', 'id');
    }
}
