<?php

/*
 * Conversion & Task Automation error report (user, 30 Oct 2026). Whenever a step of the
 * applicant-to-student conversion fails, or a Task Automation action cannot finish for a
 * student (email account, welcome letter, induction invitation, SLC New status…), the
 * details are emailed here. App\Services\Conversion\ConversionErrorReport.
 */
return [
    // comma-separate several addresses
    'to' => env('CONVERSION_ERROR_REPORT_TO', 'limon@lcc.ac.uk'),

    // the same problem for the same student is mailed once in this many minutes (retries, re-runs)
    'repeat_minutes' => (int) env('CONVERSION_ERROR_REPORT_REPEAT_MINUTES', 30),
];
