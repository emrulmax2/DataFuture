<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

/**
 * Stripe Checkout sessions, for payments started from the student app.
 *
 * Like PayPalClient, every method reports failure by its return value rather
 * than throwing, and logs what went wrong: Stripe having a bad minute must not
 * put a stack trace in front of a student halfway through paying.
 *
 * A session comes back as a plain array holding only what the callers read, so
 * nothing outside this class depends on the SDK's objects.
 */
class StripeCheckoutClient
{
    private string $secret;

    public function __construct()
    {
        $this->secret = (string) config('services.stripe.secret');
    }

    public function isConfigured(): bool
    {
        return $this->secret !== '';
    }

    /** @return array{id:string,url:?string,status:?string,payment_status:?string,payment_intent:?string,expires_at:?int}|null */
    public function createSession(array $params): ?array
    {
        if(!$this->isConfigured()):
            Log::error('[Stripe] Secret key is not configured; a payment could not be started.');

            return null;
        endif;

        try {
            return $this->session($this->client()->checkout->sessions->create($params));
        } catch (\Throwable $e) {
            Log::error('[Stripe] Checkout session could not be created.', ['error' => $e->getMessage()]);

            return null;
        }
    }

    /** @return array{id:string,url:?string,status:?string,payment_status:?string,payment_intent:?string,expires_at:?int}|null */
    public function retrieveSession($sessionId): ?array
    {
        if(!$this->isConfigured()):
            return null;
        endif;

        try {
            return $this->session($this->client()->checkout->sessions->retrieve($sessionId));
        } catch (\Throwable $e) {
            Log::error('[Stripe] Checkout session could not be read.', ['session' => $sessionId, 'error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Close a session so it can no longer be paid. False when Stripe would not,
     * which is also its answer for one that has already been paid or closed.
     */
    public function expireSession($sessionId): bool
    {
        if(!$this->isConfigured()):
            return false;
        endif;

        try {
            $this->client()->checkout->sessions->expire($sessionId);

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function client(): StripeClient
    {
        return new StripeClient($this->secret);
    }

    private function session($session): array
    {
        $intent = $session->payment_intent;

        return [
            'id' => $session->id,
            'url' => $session->url,
            /* open, complete or expired */
            'status' => $session->status,
            /* paid, unpaid or no_payment_required */
            'payment_status' => $session->payment_status,
            'payment_intent' => (is_object($intent) ? $intent->id : $intent),
            'expires_at' => $session->expires_at,
        ];
    }
}
