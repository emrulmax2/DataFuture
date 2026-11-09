<?php

namespace App\Services\ESignature;

use App\Enums\EsignEventType;
use App\Jobs\UserMailerJob;
use App\Mail\CommunicationSendMail;
use App\Models\AdminESignature;
use App\Models\Applicant;
use App\Models\ApplicantESignatureEvent;
use App\Models\ComonSmtp;
use App\Models\Option;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Sends an applicant the link to e-sign their application.
 *
 * One code path for both callers: the "Send Request" button on the
 * admission process page (which passes the signed-in user, their IP,
 * browser and geolocation) and the task engine (which passes the
 * responsible user from config and no device data). The audit trail -
 * sign-request event, per-channel events, the admin_e_signatures row - is
 * written the same way whichever one calls.
 */
class ESignatureRequestSender
{
    /**
     * @param array $meta user_id, ip, device, browser, os, latitude, longitude
     * @return array email: bool, sms: bool, skipped: string[]
     */
    public function send(Applicant $applicant, bool $viaEmail, bool $viaSms, array $meta = []): array
    {
        $applicant->loadMissing(['users', 'contact']);
        $email = optional($applicant->users)->email;
        $mobile = optional($applicant->contact)->mobile;

        $skipped = [];
        if ($viaEmail && empty($email)) { $viaEmail = false; $skipped[] = 'no email address'; }
        if ($viaSms && empty($mobile)) { $viaSms = false; $skipped[] = 'no mobile number'; }
        if (!$viaEmail && !$viaSms) {
            return ['email' => false, 'sms' => false, 'skipped' => $skipped ?: ['no channel']];
        }

        $hashedId = urlencode(base64_encode($applicant->id));
        $commonSmtp = ComonSmtp::where('is_default', 1)->first();
        $userId = (int) ($meta['user_id'] ?? config('tasks.esignature_user_id', 1));
        $ip = $meta['ip'] ?? null;
        $browser = $meta['browser'] ?? 'System';
        $os = $meta['os'] ?? 'System';

        /* Who triggered it: the submission (applicant or agent) when the task engine
           fired on "Apply now", recorded in the description and kept in extra_field. */
        $trigger = $meta['trigger'] ?? null;
        $byText = '';
        if ($trigger) {
            $who = trim(($meta['by_type'] ?? 'user').' '.($meta['by_name'] ?? ''));
            $byText = ' on the application submission by '.$who.(!empty($meta['by_email']) ? ' ('.$meta['by_email'].')' : '');
        }
        ApplicantESignatureEvent::create([
            'applicant_id' => $applicant->id,
            'user_type' => 'user',
            'event_type' => EsignEventType::SIGN_REQUEST_CREATED->value,
            'event_description' => "London Churchill College ({$commonSmtp->smtp_user}) initialized a sign request with the document".$byText,
            'ip_address' => $ip,
            'browser' => $browser,
            'os' => $os,
            'latitude' => $meta['latitude'] ?? null,
            'longitude' => $meta['longitude'] ?? null,
            'extra_field' => $trigger ? [
                'trigger' => $trigger,
                'by_type' => $meta['by_type'] ?? null,
                'by_id' => $meta['by_id'] ?? null,
                'by_name' => $meta['by_name'] ?? null,
                'by_email' => $meta['by_email'] ?? null,
                'device' => $meta['device'] ?? null,
            ] : null,
        ]);

        if ($viaEmail) {
            $esignEvent = ApplicantESignatureEvent::create([
                'applicant_id' => $applicant->id,
                'user_type' => 'applicant',
                'event_type' => EsignEventType::EMAIL_SENT->value,
                'event_description' => "{$email} was notified by email",
                'extra_field' => ['opened' => false],
            ]);
            $configuration = [
                'smtp_host' => $commonSmtp->smtp_host,
                'smtp_port' => $commonSmtp->smtp_port,
                'smtp_username' => $commonSmtp->smtp_user,
                'smtp_password' => $commonSmtp->smtp_pass,
                'smtp_encryption' => $commonSmtp->smtp_encryption,
                'from_email' => $commonSmtp->smtp_user,
                'from_name' => strtok($commonSmtp->smtp_user, '@'),
            ];
            $esignUrl = route('applicant.e.signature', ['hashedId' => $hashedId . 'e']);
            $trackingUrl = route('tracking.email.open', $esignEvent->id);
            if (ESignatureEmailCapture::active()) {
                /* "Apply now" is emailing the applicant anyway: that message carries
                   the link, so no separate e-signature email goes out. */
                ESignatureEmailCapture::capture($esignUrl, $trackingUrl);
            } else {
                $html = '<p>Dear ' . $applicant->first_name . ' ' . $applicant->last_name . ',</p>';
                $html .= '<p>Please click the button below to complete your e-signature:</p>';
                $html .= '<img src="' . $trackingUrl . '" width="1" height="1" style="display:none;" alt="" />';
                $html .= '<table align="center" cellspacing="0" cellpadding="0" border="0"><tr><td align="center" bgcolor="#1a73e8" style="border-radius:5px;">';
                $html .= '<a href="' . $esignUrl . '" target="_blank" style="display:inline-block; padding:12px 24px; color:#ffffff; text-decoration:none; font-weight:bold; background-color: #164e63;">Complete E-Signature</a>';
                $html .= '</td></tr></table>';
                $html .= '<p style="text-align:center;">If the button above does not work, please copy and paste the following link into your browser:</p>';
                $html .= '<p style="text-align:center;">' . $esignUrl . '</p>';
                UserMailerJob::dispatch($configuration, [$email], new CommunicationSendMail('E-Signature Form', $html, []));
            }
        }

        if ($viaSms) {
            $message = 'Dear ' . $applicant->first_name . ' ' . $applicant->last_name . ', Please visit the following link to submit your e-signature: ' . route('applicant.e.signature', ['hashedId' => $hashedId . 's']);
            if (app()->environment(['development', 'local', 'testing'])) {
                Log::info('SMS OTP: ' . $message . ' sent to ' . $mobile);
            } else {
                $activeApi = Option::where('category', 'SMS')->where('name', 'active_api')->pluck('value')->first();
                $textlocalApi = Option::where('category', 'SMS')->where('name', 'textlocal_api')->pluck('value')->first();
                $smseagleApi = Option::where('category', 'SMS')->where('name', 'smseagle_api')->pluck('value')->first();
                if ($activeApi == 1 && !empty($textlocalApi)) {
                    Http::timeout(-1)->post('https://api.textlocal.in/send/', [
                        'apikey' => $textlocalApi,
                        'message' => $message,
                        'sender' => 'London Churchill College',
                        'numbers' => $mobile,
                    ]);
                } elseif ($activeApi == 2 && !empty($smseagleApi)) {
                    Http::withHeaders(['access-token' => $smseagleApi, 'Content-Type' => 'application/json'])
                        ->withoutVerifying()->withOptions(['verify' => false])
                        ->post('https://79.171.153.104/api/v2/messages/sms', ['to' => [$mobile], 'text' => $message]);
                }
            }
        }

        AdminESignature::create([
            'user_id' => $userId,
            'applicant_id' => $applicant->id,
            'smtp_email' => $commonSmtp->smtp_user,
            'ip_address' => $ip,
            'device' => $meta['device'] ?? 'Task Automation',
            'browser' => $browser,
            'os' => $os,
            'latitude' => $meta['latitude'] ?? null,
            'longitude' => $meta['longitude'] ?? null,
            'via_email' => $viaEmail ? 1 : 0,
            'via_sms' => $viaSms ? 1 : 0,
        ]);

        return ['email' => $viaEmail, 'sms' => $viaSms, 'skipped' => $skipped];
    }

    public static function browserFrom(?string $userAgent): string
    {
        $ua = (string) $userAgent;
        if (strpos($ua, 'Firefox') !== false) return 'Firefox';
        if (strpos($ua, 'Chrome') !== false) return 'Chrome';
        if (strpos($ua, 'Safari') !== false) return 'Safari';
        if (strpos($ua, 'MSIE') !== false || strpos($ua, 'Trident') !== false) return 'Internet Explorer';
        return 'Unknown';
    }

    public static function osFrom(?string $userAgent): string
    {
        $ua = (string) $userAgent;
        if (preg_match('/linux/i', $ua)) return 'Linux';
        if (preg_match('/macintosh|mac os x/i', $ua)) return 'Mac';
        if (preg_match('/windows|win32/i', $ua)) return 'Windows';
        return 'Unknown';
    }
}
