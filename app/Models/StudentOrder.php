<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentOrder extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = ['id'];
    protected $table = 'student_orders';
    protected $appends = ['total_paid_quantity'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
    public function letterSet()
    {
        return $this->belongsTo(LetterSet::class);
    }
    public function studentDocumentRequestForm()
    {
        return $this->belongsTo(StudentDocumentRequestForm::class);
    }
    public function studentOrderItems()
    {
        return $this->hasMany(StudentOrderItem::class);
    }

    public function getFormattedCreatedAtAttribute()
    {
        return Carbon::parse($this->created_at)->format('d F, H:i');
    }

    public function getFormattedTransactionDateAttribute()
    {
        return Carbon::parse($this->transaction_date)->format('d F, H:i');
    }

    public function getFormattedUpdatedAtAttribute()
    {
        return Carbon::parse($this->updated_at)->format('d F, H:i');
    }

    public function getTotalPaidQuantityAttribute()
    {
        return $this->studentOrderItems->sum(function ($item) {
            return $item->quantity - $item->number_of_free;
        });
    }
}
