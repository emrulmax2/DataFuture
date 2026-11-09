<?php

namespace App\Jobs\TaskAssignment;

use App\Models\Applicant;
use App\Models\ApplicantESignature;
use App\Models\ApplicantTaskLog;
use App\Services\ESignature\ESignatureRequestContext;
use App\Services\ESignature\ESignatureRequestSender;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Runs after a rule assigns "Signed Application form": sends the applicant
 * the e-signature link by email and SMS, wherever an address or mobile
 * exists, as the responsible user from config. Skipped if the application
 * is already signed. Registered in config('tasks.assignment_jobs').
 */
class SendESignatureRequestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Who triggered the request and from where (see ESignatureRequestContext); null for the task engine alone. */
    public ?array $sender;

    public function __construct(public int $applicantId, public int $applicantTaskId, public ?int $ruleId = null, ?array $sender = null)
    {
        // Captured here, inside the HTTP request, so a queued run still has the submitter's details.
        $this->sender = $sender ?? ESignatureRequestContext::get();
    }

    public function handle(ESignatureRequestSender $sender): void
    {
        $applicant = Applicant::with(['users', 'contact'])->find($this->applicantId);
        if (!$applicant) {
            return;
        }
        $userId = (int) config('tasks.esignature_user_id', 1);

        if (ApplicantESignature::where('applicant_id', $applicant->id)->where('status', 'accepted')->exists()) {
            $this->log('E-signature request skipped', 'already signed', $userId);
            return;
        }

        try {
            $meta = ['user_id' => $userId, 'device' => 'Task Automation', 'browser' => 'System', 'os' => 'System'];
            if (!empty($this->sender)) {
                // The person who submitted the application: their IP, device, browser, OS and location.
                $meta = array_merge($meta, array_filter($this->sender, fn ($v) => $v !== null && $v !== ''));
                $meta['user_id'] = $userId;
            }
            $result = $sender->send($applicant, true, true, $meta);
        } catch (\Throwable $e) {
            Log::error('E-signature request failed', ['applicant_id' => $applicant->id, 'error' => $e->getMessage()]);
            $this->log('E-signature request failed', $e->getMessage(), $userId);
            return;
        }

        $channels = array_filter(['email' => $result['email'], 'sms' => $result['sms']]);
        $by = !empty($this->sender['by_email']) ? ' · on submission by '.($this->sender['by_type'] ?? 'user').' '.$this->sender['by_email'] : '';
        $this->log(
            $channels ? 'E-signature request sent' : 'E-signature request skipped',
            ($channels ? implode(', ', array_keys($channels)) : implode(', ', $result['skipped'])).$by,
            $userId
        );
    }

    private function log(string $action, string $detail, int $userId): void
    {
        ApplicantTaskLog::create([
            'applicant_tasks_id' => $this->applicantTaskId,
            'actions' => $action,
            'field_name' => 'rule_id',
            'prev_field_value' => '',
            'current_field_value' => trim(($this->ruleId ? "rule {$this->ruleId}: " : '') . $detail),
            'created_by' => $userId,
        ]);
    }
}
