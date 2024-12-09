<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetRequisitionDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'budget_requisition_id',
        'display_file_name',
        'hard_copy_check',
        'doc_type',
        'disk_type',
        'current_file_name',

        'created_by',
        'updated_by',
    ];

    protected $dates = ['deleted_at'];
}
