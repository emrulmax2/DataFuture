<?php

namespace App\Services;

use App\Models\EmployeePayslipLog;
use App\Models\PaySlipUploadSync;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Who may delete or replace a payslip, P45 or P60, and the trail that is left
 * when they do. The privilege is "Payslips (delete / re-upload)".
 */
class PayslipManagementService
{
    const PRIVILEGE = 'payslip_manage';

    /** event => [label, tone] — tone is the colour the log table draws it in. */
    const EVENTS = [
        'uploaded' => ['Uploaded', 'success'],
        'replaced' => ['Replaced', 'warning'],
        'deleted' => ['Deleted', 'danger'],
    ];

    /**
     * Whether the person really at the keyboard holds the privilege. In an
     * impersonated session that is the impersonator, not the account they are
     * signed in as, so borrowing someone else's login cannot unlock it.
     */
    public static function canManage(): bool
    {
        $holder = StaffDocumentVaultService::holder();
        if(!$holder):
            return false;
        endif;

        $priv = $holder->priv();

        return isset($priv[self::PRIVILEGE]) && $priv[self::PRIVILEGE] == 1;
    }

    /** "Payslip - October, 2026", "P45 - October, 2026", "P60 - 2025 - 2026". */
    public static function label(?string $type, ?string $monthYear, $holidayYear = null): string
    {
        $type = (!empty($type) ? $type : 'Payslips');
        if($type == 'P60'):
            return 'P60'.(isset($holidayYear->holiday_year) ? ' - '.$holidayYear->holiday_year : '');
        endif;

        try {
            $month = \Carbon\Carbon::createFromFormat('Y-m', (string) $monthYear)->format('F, Y');
        } catch (\Exception $e) {
            $month = (string) $monthYear;
        }

        return ($type == 'P45' ? 'P45' : 'Payslip').' - '.$month;
    }

    /**
     * Writes one line. Returns false when it could not be written, and the
     * callers that change or remove a file treat that as "do not do it".
     *
     * $userId is for queued uploads, which run with nobody signed in.
     */
    public static function log(string $event, PaySlipUploadSync $payslip, ?int $userId = null): bool
    {
        $user = auth()->user();
        $inRequest = ($user !== null);

        try {
            EmployeePayslipLog::create([
                'pay_slip_upload_sync_id' => $payslip->id,
                'employee_id' => $payslip->employee_id,
                'user_id' => ($user ? $user->id : $userId),
                'impersonator_id' => ($inRequest ? StaffDocumentVaultService::impersonatorId() : null),
                'event' => $event,
                'type' => $payslip->type,
                'month_year' => $payslip->month_year,
                'holiday_year_id' => $payslip->holiday_year_id,
                'file_name' => Str::limit((string) $payslip->file_name, 188, '...'),
                'ip_address' => ($inRequest ? request()->ip() : null),
                'user_agent' => ($inRequest ? Str::limit((string) request()->userAgent(), 252, '...') : null),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Payslip action could not be logged ('.$event.'): '.$e->getMessage());

            return false;
        }
    }
}
