<?php

namespace App\Services\Conversion;

use App\Mail\ConversionErrorReportMail;
use App\Models\Applicant;
use App\Models\Student;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Emails a problem in a student's conversion or Task Automation run to
 * config('conversion_report.to') (env CONVERSION_ERROR_REPORT_TO; user, 30 Oct 2026).
 *
 * Called where a step can fail: a conversion job that halts the chain
 * (StudentConversionLogSubscriber), the "Assign Student Tasks" step, an action that
 * throws or is not registered (TaskAssigner), a queued action that fails, and the
 * actions' own "could not finish" results (student email, welcome letter, induction
 * invitation, SLC New status). The same problem for the same student goes out once per
 * repeat window, so retries and re-runs do not flood the inbox. Reporting never throws:
 * a mail problem must not break a conversion.
 */
class ConversionErrorReport
{
    /**
     * @param string $step what was being done, e.g. "Conversion: Create Student Record" or "Action: Induction invitation"
     * @param array{student_id?:int|null, applicant_id?:int|null, task_id?:int|null, rule_id?:int|null, detail?:string|null} $context
     */
    public static function report(string $step, string $problem, array $context = []): void
    {
        try {
            $to = array_values(array_filter(array_map('trim', explode(',', (string) config('conversion_report.to', 'limon@lcc.ac.uk')))));
            if (!$to) {
                return;
            }

            $studentId = (int) ($context['student_id'] ?? 0) ?: null;
            $applicantId = (int) ($context['applicant_id'] ?? 0) ?: null;
            $key = 'conversion-error-report:'.sha1(implode('|', [$step, $problem, $studentId, $applicantId]));
            $minutes = max(1, (int) config('conversion_report.repeat_minutes', 30));
            if (!Cache::add($key, 1, now()->addMinutes($minutes))) {
                return; // already mailed within the window
            }

            $student = $studentId ? Student::find($studentId) : null;
            $applicant = $applicantId ? Applicant::find($applicantId) : ($student && $student->applicant_id ? Applicant::find($student->applicant_id) : null);

            Mail::to($to)->send(new ConversionErrorReportMail($step, $problem, [
                'student' => $student,
                'applicant' => $applicant,
                'task_id' => $context['task_id'] ?? null,
                'rule_id' => $context['rule_id'] ?? null,
                'detail' => $context['detail'] ?? null,
                'environment' => app()->environment(),
                'time' => now(),
            ]));
        } catch (Throwable $e) {
            Log::warning('Conversion error report could not be sent', ['step' => $step, 'problem' => $problem, 'error' => $e->getMessage()]);
        }
    }
}
