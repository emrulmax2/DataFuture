<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'parent_id',
        'category_name',
        'code',
        'trans_type',
        'status',
        'audit_status',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function childrens(){
        return $this->hasMany(AccCategory::class, 'parent_id', 'id');
    }

    public function activechildrens(){
        return $this->hasMany(AccCategory::class, 'parent_id', 'id')->where('status', 1);
    }

    public function childrenRecursive(){
        return $this->childrens()->with('childrenRecursive');
    }

    public function transactions(){
        return $this->hasMany(AccTransaction::class, 'acc_category_id', 'id')->where('parent', 0);
    }

    public function incomes(){
        return $this->hasMany(AccTransaction::class, 'acc_category_id', 'id')->where('transaction_type', 0)->where('parent', 0);
    }

    public function expenses(){
        return $this->hasMany(AccTransaction::class, 'acc_category_id', 'id')->where('transaction_type', 1)->where('parent', 0);
    }

    public function deposits(){
        return $this->hasMany(AccTransaction::class, 'acc_category_id', 'id')->where('transaction_type', 2)->where('transfer_type', 0)->where('parent', 0);
    }

    public function withdrawls(){
        return $this->hasMany(AccTransaction::class, 'acc_category_id', 'id')->where('transaction_type', 2)->where('transfer_type', 1)->where('parent', 0);
    }

}
