<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** /slc-registration/multiple-agreements: a group of duplicate agreements marked "Reviewed & Hide". */
class SlcAgreementDuplicateReview extends Model
{
    protected $fillable = ['student_id', 'student_course_relation_id', 'year', 'agreement_ids', 'hidden', 'reviewed_by', 'reviewed_at'];

    protected $casts = ['agreement_ids' => 'array', 'hidden' => 'boolean', 'reviewed_at' => 'datetime'];
}
