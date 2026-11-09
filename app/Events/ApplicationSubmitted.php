<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

/** Fired by ApplicationController@storeApplicantSubmission once the applicant is at 2 "New". */
class ApplicationSubmitted
{
    use Dispatchable;

    public function __construct(public int $applicantId)
    {
    }
}
