<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetNameRequester extends Model
{
    use HasFactory;

    protected $fillable = [
        'budget_name_id',
        'user_id'
    ];

    public $timestamps = false;

    public function names(){
        return $this->belongsTo(BudgetName::class, 'budget_name_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
}
