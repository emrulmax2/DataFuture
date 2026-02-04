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
        $filePath = $paySlip->file_path;
        $s3Base = rtrim((string) config('filesystems.disks.s3.url', ''), '/');
        $publicBase = rtrim((string) config('filesystems.disks.public.url', ''), '/');

        $disk = null;
        $path = null;

        if (!empty($filePath) && $s3Base !== '' && Str::startsWith($filePath, $s3Base)) {
            $disk = 's3';
            $path = ltrim(Str::after($filePath, $s3Base), '/');
        } elseif (!empty($filePath) && $publicBase !== '' && Str::startsWith($filePath, $publicBase)) {
            $disk = 'local';
            $path = ltrim(Str::after($filePath, $publicBase), '/');
        } elseif (!empty($paySlip->month_year) && !empty($paySlip->file_name)) {
            $disk = 's3';
            $path = 'public/employee_payslips/' . $paySlip->month_year . '/' . $paySlip->file_name;
        } elseif (!empty($filePath) && !Str::startsWith($filePath, ['http://', 'https://'])) {
            $disk = 'local';
            $path = $filePath;
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
