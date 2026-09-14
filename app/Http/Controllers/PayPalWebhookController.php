<?php

namespace App\Http\Controllers;

use App\Models\LibraryDeposit;
use App\Services\PayPalClient;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * PayPal's server-to-server notification of what happened to a payment.
 *
 * This is the reliable half of the flow. The browser return URL only fires if
 * the student comes back — close the tab on the PayPal page and the money is
 * taken with nothing recorded. The webhook settles those.
 *
 * Always answers 200 once the signature checks out, even for events we ignore:
 * a non-2xx makes PayPal retry for days over something we were never going to
 * act on.
 */
class PayPalWebhookController extends Controller
{
    public function __construct(private PayPalClient $paypal)
    {
    }

    public function handle(Request $request)
    {
        /* Verified before anything is read out of the body. Unsigned, this
           endpoint would let anyone mark a deposit paid by posting JSON. */
        if (!$this->paypal->verifyWebhook($request->headers->all(), $request->getContent())):
            Log::warning('[PayPal] Webhook signature rejected.', ['ip' => $request->ip()]);

            return response()->json(['ok' => false], 400);
        endif;

        $event = (string) $request->input('event_type');
        $resource = (array) $request->input('resource', []);

        switch ($event):
            case 'PAYMENT.CAPTURE.COMPLETED':
                $this->captureCompleted($resource);
                break;

            case 'PAYMENT.CAPTURE.DENIED':
            case 'PAYMENT.CAPTURE.DECLINED':
                $this->captureFailed($resource, $event);
                break;

            case 'PAYMENT.CAPTURE.REFUNDED':
            case 'PAYMENT.CAPTURE.REVERSED':
                $this->captureRefunded($resource);
                break;

            default:
                // Everything else is acknowledged and ignored on purpose.
                break;
        endswitch;

        return response()->json(['ok' => true]);
    }

    /** The deposit this capture belongs to, found by our own reference. */
    private function deposit(array $resource): ?LibraryDeposit
    {
        $custom = (string) ($resource['custom_id'] ?? '');

        if (str_starts_with($custom, 'LIBDEP-')):
            $deposit = LibraryDeposit::find((int) substr($custom, 7));

            if ($deposit):
                return $deposit;
            endif;
        endif;

        /* Falls back to the capture id, which is set once we have settled the
           row ourselves — this is how a refund event finds its deposit. */
        $captureId = (string) ($resource['id'] ?? '');

        return $captureId !== ''
            ? LibraryDeposit::where('provider_capture_id', $captureId)->first()
            : null;
    }

    private function captureCompleted(array $resource): void
    {
        $deposit = $this->deposit($resource);

        if (!$deposit):
            Log::warning('[PayPal] Capture completed for an unknown deposit.', ['custom_id' => $resource['custom_id'] ?? null]);

            return;
        endif;

        /* Conditional update, so the webhook and the return URL racing each
           other cannot both settle the same row. */
        $updated = LibraryDeposit::where('id', $deposit->id)
            ->where('status', '!=', 'paid')
            ->update([
                'status' => 'paid',
                'provider_capture_id' => $resource['id'] ?? $deposit->provider_capture_id,
                'paid_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

        if ($updated):
            Log::info('[PayPal] Library deposit settled by webhook.', ['deposit' => $deposit->id]);
        endif;
    }

    private function captureFailed(array $resource, string $event): void
    {
        $deposit = $this->deposit($resource);

        if (!$deposit || $deposit->status === 'paid'):
            return;
        endif;

        $deposit->update([
            'status' => 'failed',
            'failure_reason' => $event,
        ]);
    }

    private function captureRefunded(array $resource): void
    {
        /* On a refund event the resource is the refund; the capture it reverses
           is on the links, so match on our stored capture id instead. */
        $captureId = (string) data_get($resource, 'links.0.href', '');
        $deposit = $this->deposit($resource)
            ?: LibraryDeposit::whereNotNull('provider_capture_id')
                ->where('status', 'paid')
                ->when($captureId !== '', fn ($q) => $q->whereRaw('? LIKE CONCAT("%", provider_capture_id, "%")', [$captureId]))
                ->first();

        if (!$deposit || $deposit->status === 'refunded'):
            return;
        endif;

        $deposit->update([
            'status' => 'refunded',
            'provider_refund_id' => $resource['id'] ?? $deposit->provider_refund_id,
            'refunded_at' => Carbon::now(),
        ]);

        Log::info('[PayPal] Library deposit marked refunded by webhook.', ['deposit' => $deposit->id]);
    }
}
