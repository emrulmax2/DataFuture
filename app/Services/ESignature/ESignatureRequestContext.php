<?php

namespace App\Services\ESignature;

use Illuminate\Http\Request;

/**
 * Who is triggering an automatic e-signature request, and from where.
 *
 * "Apply now" (the applicant portal, or an agent submitting on the
 * applicant's behalf) fires the task engine, whose e-signature job used to
 * record the request as "Task Automation / System" with no IP or device.
 * The submission sets this context first; SendESignatureRequestJob copies
 * it when it is constructed (still inside the HTTP request, whether the job
 * then runs inline or on the queue) and the sender writes it into the
 * admin_e_signatures row and the sign-request audit event. Request-scoped.
 */
class ESignatureRequestContext
{
    private static ?array $meta = null;

    /**
     * @param string $trigger  applicant_submission | agent_submission
     * @param array  $by       type, id, name, email of the person submitting
     */
    public static function fromRequest(Request $request, string $trigger, array $by = []): array
    {
        $agent = (string) $request->header('User-Agent');

        return [
            'trigger' => $trigger,
            'ip' => $request->ip(),
            'device' => $agent !== '' ? $agent : 'Unknown device',
            'browser' => ESignatureRequestSender::browserFrom($agent),
            'os' => ESignatureRequestSender::osFrom($agent),
            'latitude' => $request->input('latitude') ?: null,
            'longitude' => $request->input('longitude') ?: null,
            'by_type' => $by['type'] ?? null,
            'by_id' => $by['id'] ?? null,
            'by_name' => $by['name'] ?? null,
            'by_email' => $by['email'] ?? null,
        ];
    }

    public static function set(array $meta): void
    {
        self::$meta = $meta;
    }

    public static function get(): ?array
    {
        return self::$meta;
    }

    public static function clear(): void
    {
        self::$meta = null;
    }
}
