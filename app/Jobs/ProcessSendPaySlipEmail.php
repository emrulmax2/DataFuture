<?php

namespace App\Jobs;

use App\Mail\EmployeePaySlipMail;
use App\Models\PaySlipUploadSync;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProcessSendPaySlipEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $paySlipId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $paySlipId)
    {
        $this->paySlipId = $paySlipId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $paySlip = PaySlipUploadSync::with('employee')->find($this->paySlipId);

        if (!$paySlip || !$paySlip->employee || empty($paySlip->employee->email)) {
            return;
        }

        $attachment = $this->resolveAttachment($paySlip);

        if (empty($attachment)) {
            return;
        }

        Mail::to($paySlip->employee->email)->send(new EmployeePaySlipMail($paySlip, $attachment));

        $paySlip->update([
            'email_transferred_at' => now(),
        ]);
    }

    protected function resolveAttachment(PaySlipUploadSync $paySlip): array
    {
        $s3Base = Storage::disk('s3')->url('');
        $localBase = Storage::disk('local')->url('');

        $disk = null;
        $path = null;

        if (!empty($paySlip->file_path) && Str::startsWith($paySlip->file_path, $s3Base)) {
            $disk = 's3';
            $path = ltrim(Str::after($paySlip->file_path, $s3Base), '/');
        } elseif (!empty($paySlip->file_path) && Str::startsWith($paySlip->file_path, $localBase)) {
            $disk = 'local';
            $path = ltrim(Str::after($paySlip->file_path, $localBase), '/');
        } elseif (!empty($paySlip->month_year) && !empty($paySlip->file_name)) {
            $disk = 's3';
            $path = 'public/employee_payslips/' . $paySlip->month_year . '/' . $paySlip->file_name;
        } elseif (!empty($paySlip->file_path)) {
            $disk = 'local';
            $path = $paySlip->file_path;
        }

        if (empty($disk) || empty($path) || !Storage::disk($disk)->exists($path)) {
            return [];
        }

        $mime = Storage::disk($disk)->mimeType($path) ?? 'application/pdf';

        return [
            'disk' => $disk,
            'path' => $path,
            'name' => $paySlip->file_name ?? 'payslip.pdf',
            'mime' => $mime,
        ];
    }
}
