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
        'ac_name',
        'sort_code',
        'ac_number',
        'created_by',
        'updated_by',
    ];


    public function getImageUrlAttribute()
    {
        if ($this->hasImage()) {
            return Storage::disk('local')->url('public/banks/'.$this->bank_image);
        } else {
            return asset('build/assets/images/placeholders/200x200.jpg');
        }
    }

    /**
     * Whether a real logo is on disk. image_url always resolves — it falls back
     * to a placeholder — so callers that want to render their own fallback
     * (e.g. a monogram) need to ask separately.
     */
    public function hasImage()
    {
        return $this->bank_image !== null
            && $this->bank_image != ''
            && Storage::disk('local')->exists('public/banks/'.$this->bank_image);
    }

    /**
     * Two-letter monogram: initials of the first two words, else the first two
     * letters of a single-word name.
     */
    public function getMonogramAttribute()
    {
        $words = preg_split('/[^a-z0-9]+/i', (string) $this->bank_name, -1, PREG_SPLIT_NO_EMPTY);

        if (empty($words)) {
            return '#';
        }

        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1).substr($words[1], 0, 1));
        }

        return strtoupper(substr($words[0], 0, 2));
    }

    /**
     * Stable colour for the monogram tile — derived from the name so a storage
     * keeps the same tile across requests.
     */
    public function getMonogramColorAttribute()
    {
        $palette = ['#0e766c', '#7d2b2f', '#1d1d1f', '#0e9e6e', '#b1000e', '#006a4d', '#c8443a', '#8a6d1f', '#2f4858', '#5b2c6f'];

        return $palette[abs(crc32((string) $this->bank_name)) % count($palette)];
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
        //$audit_status = (auth()->user()->remote_access && isset(auth()->user()->priv()['access_account_type']) && auth()->user()->priv()['access_account_type'] == 3 ? ['1'] : ['0', '1']);
        if(!empty($this->opening_date)):
            return $this->hasMany(AccTransaction::class, 'acc_bank_id', 'id')->where('transaction_date_2', '>=', date('Y-m-d', strtotime($this->opening_date)))->whereNot('transaction_type', 2)->where('flow', 0)->where('parent', 0);//->whereIn('audit_status', $audit_status);
        else:
            return $this->hasMany(AccTransaction::class, 'acc_bank_id', 'id')->where('flow', 0)->whereNot('transaction_type', 2)->where('parent', 0);//->whereIn('audit_status', $audit_status);
        endif;
    }

    public function expenses(){
        //$audit_status = (auth()->user()->remote_access && isset(auth()->user()->priv()['access_account_type']) && auth()->user()->priv()['access_account_type'] == 3 ? ['1'] : ['0', '1']);
        if(!empty($this->opening_date)):
            return $this->hasMany(AccTransaction::class, 'acc_bank_id', 'id')->where('transaction_date_2', '>=', date('Y-m-d', strtotime($this->opening_date)))->whereNot('transaction_type', 2)->where('flow', 1)->where('parent', 0);//->whereIn('audit_status', $audit_status);
        else:
            return $this->hasMany(AccTransaction::class, 'acc_bank_id', 'id')->where('flow', 1)->whereNot('transaction_type', 2)->where('parent', 0);//->whereIn('audit_status', $audit_status);
        endif;
    }

    public function deposits(){
        //$audit_status = (auth()->user()->remote_access && isset(auth()->user()->priv()['access_account_type']) && auth()->user()->priv()['access_account_type'] == 3 ? ['1'] : ['0', '1']);
        if(!empty($this->opening_date)):
            return $this->hasMany(AccTransaction::class, 'acc_bank_id', 'id')->where('transaction_date_2', '>=', date('Y-m-d', strtotime($this->opening_date)))->where('transaction_type', 2)->where('flow', 0);//->whereIn('audit_status', $audit_status);
        else:
            return $this->hasMany(AccTransaction::class, 'acc_bank_id', 'id')->where('transaction_type', 2)->where('flow', 0);//->whereIn('audit_status', $audit_status);
        endif;
    }

    public function withdrawls(){
        //$audit_status = (auth()->user()->remote_access && isset(auth()->user()->priv()['access_account_type']) && auth()->user()->priv()['access_account_type'] == 3 ? ['1'] : ['0', '1']);
        if(!empty($this->opening_date)):
            return $this->hasMany(AccTransaction::class, 'acc_bank_id', 'id')->where('transaction_date_2', '>=', date('Y-m-d', strtotime($this->opening_date)))->where('transaction_type', 2)->where('flow', 1);//->whereIn('audit_status', $audit_status);
        else:
            return $this->hasMany(AccTransaction::class, 'acc_bank_id', 'id')->where('transaction_type', 2)->where('flow', 1);//->whereIn('audit_status', $audit_status);
        endif;
    }

    public function getBalanceAttribute(){
        $audit_status = (auth()->user()->remote_access && isset(auth()->user()->priv()['access_account_type']) && auth()->user()->priv()['access_account_type'] == 3 ? ['1'] : ['0', '1']);
        $openingBalance = (isset($this->opening_balance) && $this->opening_balance > 0 ? $this->opening_balance : 0);
        $incomes = $this->incomes()->whereIn('audit_status', $audit_status)->sum('transaction_amount');
        $deposits = $this->deposits()->whereIn('audit_status', $audit_status)->sum('transaction_amount');
        $expenses = $this->expenses()->whereIn('audit_status', $audit_status)->sum('transaction_amount');
        $withdrawls = $this->withdrawls()->whereIn('audit_status', $audit_status)->sum('transaction_amount');

        return (($openingBalance + $incomes + $deposits) - ($expenses + $withdrawls));
    }
}
