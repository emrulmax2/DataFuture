<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A student asking for tuition fees back.
 *
 * The account number and sort code are cast `encrypted`: they are written to
 * the database as ciphertext and only ever decrypted by this application, for
 * the one place that needs them — the ticket Finance works from. Anything that
 * only has to identify the account (a summary, the student's receipt) uses
 * `account_number_last4`, which is stored in the clear for exactly that.
 */
class StudentRefundRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'student_refund_requests';
    protected $guarded = ['id'];
    protected $dates = ['deleted_at'];

    protected $casts = [
        'account_number' => 'encrypted',
        'sort_code' => 'encrypted',
        'refund_amount' => 'decimal:2',
        'declaration_accepted' => 'boolean',
    ];

    /** Never log or serialise the bank details by accident. */
    protected $hidden = ['account_number', 'sort_code'];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function documents()
    {
        return $this->hasMany(StudentRefundRequestDocument::class, 'request_id');
    }

    /** The account as it can safely be shown: "Ending 4567". */
    public function maskedAccount(): string
    {
        return $this->account_number_last4 ? 'Ending '.$this->account_number_last4 : '—';
    }
}
