<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * PayPal REST v2 — orders, captures and refunds.
 *
 * Every method returns null on failure rather than throwing, and logs what went
 * wrong: a payment provider having a bad minute must not put a stack trace in
 * front of a student halfway through paying.
 *
 * Amounts are always passed as decimal strings with two places. PayPal rejects
 * "20" and silently mis-reads a float, so formatting happens here once rather
 * than at every call site.
 */
class PayPalClient
{
    private string $clientId;
    private string $secret;
    private string $baseUrl;

    public function __construct()
    {
        $this->clientId = (string) config('services.paypal.client_id');
        $this->secret = (string) config('services.paypal.secret');
        $this->baseUrl = config('services.paypal.sandbox')
            ? 'https://api-m.sandbox.paypal.com'
            : 'https://api-m.paypal.com';
    }

    public function isConfigured(): bool
    {
        return $this->clientId !== '' && $this->secret !== '';
    }

    private static function money($amount): string
    {
        return number_format((float) $amount, 2, '.', '');
    }

    /**
     * Access tokens last ~9 hours; cached just under that so a burst of
     * payments does not mint one token per click.
     */
    private function accessToken(): ?string
    {
        if (!$this->isConfigured()):
            Log::error('[PayPal] Client id or secret is not configured.');

            return null;
        endif;

        $cacheKey = 'paypal.token.'.md5($this->baseUrl.$this->clientId);

        return Cache::remember($cacheKey, now()->addMinutes(200), function () {
            try {
                $response = Http::withBasicAuth($this->clientId, $this->secret)
                    ->asForm()
                    ->timeout(20)
                    ->post($this->baseUrl.'/v1/oauth2/token', ['grant_type' => 'client_credentials']);
            } catch (\Throwable $e) {
                Log::error('[PayPal] Token request failed.', ['error' => $e->getMessage()]);

                return null;
            }

            if (!$response->successful()):
                Log::error('[PayPal] Token rejected.', ['status' => $response->status(), 'body' => $response->body()]);

                return null;
            endif;

            return $response->json('access_token');
        });
    }

    private function http()
    {
        $token = $this->accessToken();

        return $token ? Http::withToken($token)->timeout(20) : null;
    }

    /**
     * Create an order and return its id plus the URL to send the payer to.
     *
     * @return array{id:string,approve_url:string}|null
     */
    public function createOrder(array $options): ?array
    {
        $http = $this->http();

        if (!$http):
            return null;
        endif;

        $amount = self::money($options['amount']);
        $currency = $options['currency'] ?? 'GBP';

        try {
            $response = $http->post($this->baseUrl.'/v2/checkout/orders', [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    /* Echoed back on the capture and the webhook, so a payment
                       can always be traced to the row that started it. */
                    'custom_id' => (string) ($options['reference'] ?? ''),
                    'description' => mb_substr((string) ($options['description'] ?? 'Payment'), 0, 127),
                    'amount' => [
                        'currency_code' => $currency,
                        'value' => $amount,
                        'breakdown' => [
                            'item_total' => ['currency_code' => $currency, 'value' => $amount],
                        ],
                    ],
                    'items' => [[
                        'name' => mb_substr((string) ($options['item_name'] ?? 'Payment'), 0, 127),
                        'quantity' => '1',
                        'unit_amount' => ['currency_code' => $currency, 'value' => $amount],
                    ]],
                ]],
                'application_context' => [
                    'brand_name' => 'London Churchill College',
                    'shipping_preference' => 'NO_SHIPPING',
                    'user_action' => 'PAY_NOW',
                    'return_url' => $options['return_url'],
                    'cancel_url' => $options['cancel_url'],
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('[PayPal] Order create failed.', ['error' => $e->getMessage()]);

            return null;
        }

        if (!$response->successful()):
            Log::error('[PayPal] Order rejected.', ['status' => $response->status(), 'body' => $response->body()]);

            return null;
        endif;

        $approve = collect($response->json('links', []))->firstWhere('rel', 'approve');

        if (!$approve):
            Log::error('[PayPal] Order created without an approve link.', ['body' => $response->body()]);

            return null;
        endif;

        return ['id' => (string) $response->json('id'), 'approve_url' => (string) $approve['href']];
    }

    /** Read an order back without capturing — used to confirm state. */
    public function getOrder(string $orderId): ?array
    {
        $http = $this->http();

        if (!$http):
            return null;
        endif;

        try {
            $response = $http->get($this->baseUrl.'/v2/checkout/orders/'.$orderId);
        } catch (\Throwable $e) {
            Log::error('[PayPal] Order read failed.', ['order' => $orderId, 'error' => $e->getMessage()]);

            return null;
        }

        return $response->successful() ? (array) $response->json() : null;
    }

    /**
     * Take the money.
     *
     * @return array{capture_id:string,status:string,amount:string,currency:string,payer_email:?string}|null
     */
    public function captureOrder(string $orderId): ?array
    {
        $http = $this->http();

        if (!$http):
            return null;
        endif;

        try {
            /* PayPal treats the request id as an idempotency key: a double
               submit or a refreshed return URL captures once, not twice. */
            $response = $http->withHeaders(['PayPal-Request-Id' => 'capture-'.$orderId])
                ->post($this->baseUrl.'/v2/checkout/orders/'.$orderId.'/capture', new \stdClass());
        } catch (\Throwable $e) {
            Log::error('[PayPal] Capture failed.', ['order' => $orderId, 'error' => $e->getMessage()]);

            return null;
        }

        if (!$response->successful()):
            /* An order already captured comes back 422 UNPROCESSABLE_ENTITY.
               That is a success from our side — read the order and use it. */
            if ($response->status() === 422 && str_contains($response->body(), 'ORDER_ALREADY_CAPTURED')):
                $order = $this->getOrder($orderId);

                return $order ? $this->readCapture($order) : null;
            endif;

            Log::error('[PayPal] Capture rejected.', ['order' => $orderId, 'status' => $response->status(), 'body' => $response->body()]);

            return null;
        endif;

        return $this->readCapture((array) $response->json());
    }

    /** Pull the bits we store out of an order/capture payload. */
    private function readCapture(array $order): ?array
    {
        $capture = data_get($order, 'purchase_units.0.payments.captures.0');

        if (!$capture):
            return null;
        endif;

        return [
            'capture_id' => (string) ($capture['id'] ?? ''),
            'status' => (string) ($capture['status'] ?? ''),
            'amount' => (string) data_get($capture, 'amount.value', '0.00'),
            'currency' => (string) data_get($capture, 'amount.currency_code', 'GBP'),
            'custom_id' => (string) data_get($order, 'purchase_units.0.custom_id', ''),
            'payer_email' => data_get($order, 'payer.email_address'),
        ];
    }

    /**
     * Refund a capture, in full when no amount is given.
     *
     * @return array{refund_id:string,status:string}|null
     */
    public function refundCapture(string $captureId, $amount = null, string $currency = 'GBP', string $note = ''): ?array
    {
        $http = $this->http();

        if (!$http):
            return null;
        endif;

        $body = [];

        if ($amount !== null):
            $body['amount'] = ['value' => self::money($amount), 'currency_code' => $currency];
        endif;

        if ($note !== ''):
            $body['note_to_payer'] = mb_substr($note, 0, 255);
        endif;

        try {
            $response = $http->withHeaders(['PayPal-Request-Id' => 'refund-'.$captureId])
                ->post($this->baseUrl.'/v2/payments/captures/'.$captureId.'/refund', $body ?: new \stdClass());
        } catch (\Throwable $e) {
            Log::error('[PayPal] Refund failed.', ['capture' => $captureId, 'error' => $e->getMessage()]);

            return null;
        }

        if (!$response->successful()):
            Log::error('[PayPal] Refund rejected.', ['capture' => $captureId, 'status' => $response->status(), 'body' => $response->body()]);

            return null;
        endif;

        return [
            'refund_id' => (string) $response->json('id'),
            'status' => (string) $response->json('status'),
        ];
    }

    /**
     * Confirm a webhook really came from PayPal.
     *
     * Without this the endpoint is an open door: anyone who knows the URL could
     * post a "payment completed" body and unlock borrowing for free.
     */
    public function verifyWebhook(array $headers, string $rawBody): bool
    {
        $webhookId = (string) config('services.paypal.webhook_id');
        $http = $this->http();

        if (!$http || $webhookId === ''):
            Log::warning('[PayPal] Webhook id not configured; refusing to trust the payload.');

            return false;
        endif;

        $header = fn (string $name) => $headers[strtolower($name)][0] ?? '';

        try {
            $response = $http->post($this->baseUrl.'/v1/notifications/verify-webhook-signature', [
                'auth_algo' => $header('paypal-auth-algo'),
                'cert_url' => $header('paypal-cert-url'),
                'transmission_id' => $header('paypal-transmission-id'),
                'transmission_sig' => $header('paypal-transmission-sig'),
                'transmission_time' => $header('paypal-transmission-time'),
                'webhook_id' => $webhookId,
                'webhook_event' => json_decode($rawBody, true),
            ]);
        } catch (\Throwable $e) {
            Log::error('[PayPal] Webhook verification failed.', ['error' => $e->getMessage()]);

            return false;
        }

        return $response->successful() && $response->json('verification_status') === 'SUCCESS';
    }
}
