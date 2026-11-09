<?php

namespace App\Services\ESignature;

/**
 * Lets the caller that is about to email the applicant anyway fold the
 * e-signature link into its own message instead of a second email.
 *
 * "Apply now" fires the task engine, whose e-signature job would send its own
 * "complete your e-signature" email on the heels of the confirmation email.
 * While a capture is open, ESignatureRequestSender records the sign-request
 * audit trail and the SMS as usual but hands the email link back here rather
 * than dispatching it; the submission then sends one email carrying both the
 * reference number and the link. Request-scoped: one PHP request, one capture.
 */
class ESignatureEmailCapture
{
    private static bool $open = false;

    /** @var array{url: string, tracking_url: string}|null */
    private static ?array $captured = null;

    public static function start(): void
    {
        self::$open = true;
        self::$captured = null;
    }

    /** Closes the capture and returns what was caught, if anything. */
    public static function stop(): ?array
    {
        $caught = self::$captured;
        self::$open = false;
        self::$captured = null;

        return $caught;
    }

    public static function active(): bool
    {
        return self::$open;
    }

    public static function capture(string $url, string $trackingUrl): void
    {
        self::$captured = ['url' => $url, 'tracking_url' => $trackingUrl];
    }
}
