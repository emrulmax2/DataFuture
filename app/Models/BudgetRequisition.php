<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetRequisition extends Model
{
    use HasFactory, SoftDeletes;

    protected static function boot(){
        parent::boot();

        static::created(function ($model) {
            $budgetSetDetails = BudgetSetDetail::with('names')->find($model->budget_set_detail_id);
            $code = (isset($budgetSetDetails->names->code) && !empty($budgetSetDetails->names->code) ? $budgetSetDetails->names->code : '');
            $model->reference_no = $code.self::generateUniqueReferenceNumber();
            $model->save();
        });
    }

    protected $fillable = [
        'reference_no',
        'budget_year_id',
        'budget_set_id',
        'vendor_id',
        'date',
        'requisitioner',
        'budget_set_detail_id',
        'required_by',
        'venue_id',
        'first_approver',
        'final_approver',
        'note',

        'active',
        'created_by',
        'updated_by',
    ];

    protected $dates = ['deleted_at'];

    protected $appends = ['requisition_total', 'transanctions_total'];

    public function setDateAttribute($value) { 
        $this->attributes['date'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }

    public function getDateAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }

    public function setRequiredByAttribute($value) { 
        $this->attributes['required_by'] =  (!empty($value) ? date('Y-m-d', strtotime($value)) : '');
    }

    public function getRequiredByAttribute($value) {
        return (!empty($value) ? date('d-m-Y', strtotime($value)) : '');
    }

    public function getRequisitionTotalAttribute(){
        $items = BudgetRequisitionItem::where('budget_requisition_id', $this->id)->get();
        return ($items->count() > 0 ? $items->sum('total') : 0);
    }

    public function items(){
        return $this->hasMany(BudgetRequisitionItem::class, 'budget_requisition_id', 'id');
    }

    public function documents(){
        return $this->hasMany(BudgetRequisitionDocument::class, 'budget_requisition_id', 'id');
    }

    public function year(){
        return $this->belongsTo(BudgetYear::class, 'budget_year_id');
    }

    public function set(){
        return $this->belongsTo(BudgetSet::class, 'budget_set_id');
    }

    public function vendor(){
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function budget(){
        return $this->belongsTo(BudgetSetDetail::class, 'budget_set_detail_id');
    }

    public function venue(){
        return $this->belongsTo(Venue::class, 'venue_id');
    }

    public function requisitioners(){
        return $this->belongsTo(User::class, 'requisitioner');
    }

    public function fapprover(){
        return $this->belongsTo(User::class, 'first_approver');
    }

    public function lapprover(){
        return $this->belongsTo(User::class, 'final_approver');
    }

    public function transactions(){
        return $this->hasMany(BudgetRequisitionTransaction::class, 'budget_requisition_id', 'id');
    }

    public function getTransanctionsTotalAttribute(){
        $total = 0;
        $transactions = BudgetRequisitionTransaction::where('budget_requisition_id', $this->id)->get();
        if($transactions->count() > 0):
            foreach($transactions as $trans):
                $total += (isset($trans->transaction->transaction_amount) && $trans->transaction->transaction_amount > 0 ? $trans->transaction->transaction_amount : 0);
            endforeach;
        endif;

        return $total;
    }

    private static function generateUniqueReferenceNumber(){
        do{
            $number = mt_rand(100000, 999999);
        } while (self::where('reference_no', $number)->exists());

        return $number;
    }

    public function history(){
        return $this->hasMany(BudgetRequisitionHistory::class, 'budget_requisition_id', 'id')->where('approver', '>', 0)->orderBy('id', 'ASC');
    }
}
