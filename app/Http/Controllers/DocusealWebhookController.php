<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessDocusealSubmission;
use App\Models\ApplicantDocusealSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * DocuSeal webhooks (console.docuseal.com/webhooks): form.viewed, form.started,
 * form.completed, form.declined (and submission.* if enabled). Only forms we sent
 * (applicant_docuseal_submissions) are acted on, and the work is queued: the job asks
 * DocuSeal's API for the submission before changing anything, so a forged body
 * cannot move an application.
 *
 * Authenticity (Webhook → Security in DocuSeal), either one is enough:
 *  - HMAC tab: DocuSeal signs every delivery as X-Docuseal-Signature = "<unix ts>.<hex>",
 *    hex = HMAC-SHA256(whsec_… secret, "<ts>.<raw body>"), accepted within 5 minutes
 *    (DOCUSEAL_WEBHOOK_HMAC_SECRET);
 *  - Secret tab: a custom header DocuSeal adds (DOCUSEAL_WEBHOOK_SECRET_KEY / _SECRET).
 * With neither configured, webhooks are accepted outside production only.
 */
class DocusealWebhookController extends Controller
{
    private const ACT_ON = ['form.completed', 'form.declined', 'submission.completed', 'submission.expired'];

    public const SIGNATURE_TOLERANCE = 300;

    public function handle(Request $request)
    {
        $why = $this->rejectReason($request);
        if ($why !== null) {
            Log::warning('[DocuSeal] Webhook rejected: '.$why, ['ip' => $request->ip()]);

            return response()->json(['ok' => false], 401);
        }

        $event = (string) $request->input('event_type', '');
        $data = (array) $request->input('data', []);
        $submissionId = (int) (data_get($data, 'submission.id') ?: (str_starts_with($event, 'submission.') ? data_get($data, 'id') : 0));
        if ($submissionId <= 0) {
            return response()->json(['ok' => true, 'ignored' => 'no submission']);
        }
        $row = ApplicantDocusealSubmission::where('submission_id', $submissionId)->first();
        if (!$row) {
            Log::info('[DocuSeal] Webhook for a submission we did not record.', ['event' => $event, 'submission_id' => $submissionId]);

            return response()->json(['ok' => true, 'ignored' => 'unknown submission']);
        }

        if ($event === 'form.viewed' && (data_get($data, 'role') === 'Applicant') && !$row->opened_at && data_get($data, 'opened_at')) {
            $row->opened_at = Carbon::parse(data_get($data, 'opened_at'))->setTimezone(config('app.timezone'));
            $row->save();
        }
        if (in_array($event, self::ACT_ON, true)) {
            ProcessDocusealSubmission::dispatch((int) $row->id, array_filter(['decline_reason' => data_get($data, 'decline_reason')]));
        }

        return response()->json(['ok' => true]);
    }

    /** null when the delivery is authentic (or no check is configured outside production). */
    private function rejectReason(Request $request): ?string
    {
        $key = (string) config('services.docuseal.webhook_secret_key');
        $secret = (string) config('services.docuseal.webhook_secret');
        $hmac = (string) config('services.docuseal.webhook_hmac_secret');
        $headerConfigured = $key !== '' && $secret !== '';

        if ($hmac === '' && !$headerConfigured) {
            return app()->environment('production') ? 'no DocuSeal webhook secret is configured (DOCUSEAL_WEBHOOK_HMAC_SECRET or DOCUSEAL_WEBHOOK_SECRET_KEY / _SECRET).' : null;
        }
        if ($headerConfigured && hash_equals($secret, (string) $request->header($key))) {
            return null;
        }
        if ($hmac !== '') {
            $signature = (string) $request->header('X-Docuseal-Signature');
            [$ts, $sig] = array_pad(explode('.', $signature, 2), 2, '');
            if (!ctype_digit($ts) || $sig === '') {
                return $headerConfigured ? 'wrong or missing secret header, and no X-Docuseal-Signature.' : 'missing X-Docuseal-Signature.';
            }
            if (abs(time() - (int) $ts) > self::SIGNATURE_TOLERANCE) {
                return 'X-Docuseal-Signature is too old or in the future.';
            }
            if (hash_equals(hash_hmac('sha256', $ts.'.'.$request->getContent(), $hmac), $sig)) {
                return null;
            }

            return 'X-Docuseal-Signature does not match DOCUSEAL_WEBHOOK_HMAC_SECRET.';
        }

        return $request->header($key) === null
            ? 'the '.$key.' header is missing: DocuSeal is not sending it (Webhook → Security → Secret is not saved).'
            : 'the '.$key.' header value does not match DOCUSEAL_WEBHOOK_SECRET.';
    }
}
