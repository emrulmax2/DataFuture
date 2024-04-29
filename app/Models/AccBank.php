<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class AccBank extends Model
{
    use HasFactory, SoftDeletes;

    protected $appends = ['image_url', 'balance'];

    protected $fillable = [
        'bank_name',
        'bank_image',
        'status',
        'audit_status',
        'opening_balance',
        'opening_date',
        'created_by',
        'updated_by',
    ];


    public function getImageUrlAttribute()
    {
        if ($this->bank_image !== null && $this->bank_image != '' && Storage::disk('local')->exists('public/banks/'.$this->bank_image)) {
            return Storage::disk('local')->url('public/banks/'.$this->bank_image);
        } else {
            return asset('build/assets/images/placeholders/200x200.jpg');
        }
    }

    public function setOpeningDateAttribute($value) {  
        $this->attributes['opening_date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }
    
    public function getOpeningDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function incomes(){
        $audit_status = (auth()->user()->remote_access && isset(auth()->user()->priv()['access_account_type']) && auth()->user()->priv()['access_account_type'] == 3 ? ['1'] : ['0', '1']);
        if(!empty($this->opening_date)):
            return $this->hasMany(AccTransaction::class, 'acc_bank_id', 'id')->where('transaction_date_2', '>=', date('Y-m-d', strtotime($this->opening_date)))->where('transaction_type', 0)->where('parent', 0)->whereIn('audit_status', $audit_status);
        else:
            return $this->hasMany(AccTransaction::class, 'acc_bank_id', 'id')->where('transaction_type', 0)->where('parent', 0)->whereIn('audit_status', $audit_status);
        endif;
    }

    public function expenses(){
        $audit_status = (auth()->user()->remote_access && isset(auth()->user()->priv()['access_account_type']) && auth()->user()->priv()['access_account_type'] == 3 ? ['1'] : ['0', '1']);
        if(!empty($this->opening_date)):
            return $this->hasMany(AccTransaction::class, 'acc_bank_id', 'id')->where('transaction_date_2', '>=', date('Y-m-d', strtotime($this->opening_date)))->where('transaction_type', 1)->where('parent', 0)->whereIn('audit_status', $audit_status);
        else:
            return $this->hasMany(AccTransaction::class, 'acc_bank_id', 'id')->where('transaction_type', 1)->where('parent', 0)->whereIn('audit_status', $audit_status);
        endif;
    }

    public function deposits(){
        $audit_status = (auth()->user()->remote_access && isset(auth()->user()->priv()['access_account_type']) && auth()->user()->priv()['access_account_type'] == 3 ? ['1'] : ['0', '1']);
        if(!empty($this->opening_date)):
            return $this->hasMany(AccTransaction::class, 'acc_bank_id', 'id')->where('transaction_date_2', '>=', date('Y-m-d', strtotime($this->opening_date)))->where('transaction_type', 2)->where('transfer_type', 0)->whereIn('audit_status', $audit_status);
        else:
            return $this->hasMany(AccTransaction::class, 'acc_bank_id', 'id')->where('transaction_type', 2)->where('transfer_type', 0)->whereIn('audit_status', $audit_status);
        endif;
    }

    public function withdrawls(){
        $audit_status = (auth()->user()->remote_access && isset(auth()->user()->priv()['access_account_type']) && auth()->user()->priv()['access_account_type'] == 3 ? ['1'] : ['0', '1']);
        if(!empty($this->opening_date)):
            return $this->hasMany(AccTransaction::class, 'acc_bank_id', 'id')->where('transaction_date_2', '>=', date('Y-m-d', strtotime($this->opening_date)))->where('transaction_type', 2)->where('transfer_type', 1)->whereIn('audit_status', $audit_status);
        else:
            return $this->hasMany(AccTransaction::class, 'acc_bank_id', 'id')->where('transaction_type', 2)->where('transfer_type', 1)->whereIn('audit_status', $audit_status);
        endif;
    }

    public function getBalanceAttribute(){
        $openingBalance = (isset($this->opening_balance) && $this->opening_balance > 0 ? $this->opening_balance : 0);
        $incomes = $this->incomes()->sum('transaction_amount');
        $deposits = $this->deposits()->sum('transaction_amount');
        $expenses = $this->expenses()->sum('transaction_amount');
        $withdrawls = $this->withdrawls()->sum('transaction_amount');

        return (($openingBalance + $incomes + $deposits) - ($expenses + $withdrawls));
    }
}
