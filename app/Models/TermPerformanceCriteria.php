<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TermPerformanceCriteria extends Model
{
    use HasFactory;

    protected $table = "term_performance_criteria";
    protected $guarded = ['id'];

}
