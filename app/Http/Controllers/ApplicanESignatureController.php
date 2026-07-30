<?php

namespace App\Http\Controllers;

use App\Enums\EsignEventType;
use App\Jobs\UserMailerJob;
use App\Mail\CommunicationSendMail;
use App\Models\AdminESignature;
use App\Models\Applicant;
use App\Models\ApplicantESignature;
use App\Models\ApplicantESignatureEvent;
use App\Models\ComonSmtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PDF;

class ApplicanESignatureController extends Controller
{
    public function index(Request $request, $hashedId){

        $firstOpenVia = substr($hashedId, -1);
        $hashedUri = substr($hashedId, 0, -1);
        $id = base64_decode(urldecode($hashedUri));

        $applicant = Applicant::with([
                                    'title', 'quals', 'employment',
                                     'sexid', 'nation', 'country', 'other.ethnicity', 
                                    'disability.disabilities', 'users', 'contact', 'kin.relation',
                                    'course.semester','course.creation.course', 'course.venue', 
                                    'feeeligibility.elegibility', 'employment.reference'
                                ])->findOrFail($id);

        $offerAcceptance = ApplicantESignature::where('applicant_id', $applicant->id)->first();

       if (!$offerAcceptance || !$offerAcceptance->viewed_via) {
            $offerAcceptance = ApplicantESignature::updateOrCreate(
                ['applicant_id' => $applicant->id],
                [
                    'ip_address' => $request->ip(),
                    'device' => $request->header('User-Agent'),
                    'browser' => $this->getBrowser($request->header('User-Agent')),
                    'os' => $this->getOS($request->header('User-Agent')),
                    'latitude' => null,
                    'longitude' => null,
                    'viewed_via' => $firstOpenVia == 'e' ? 'email' : ($firstOpenVia == 's' ? 'sms' : null),
                ]
            );
        }

        ApplicantESignatureEvent::firstOrCreate(
            [
                'applicant_id' => $applicant->id,
                'user_type' => 'applicant',
                'event_type' => EsignEventType::VIEWED->value,
            ],
            [
                'event_description' => "{$applicant->users->email} viewed the document",
                'ip_address' => $request->ip(),
                'browser' => $this->getBrowser($request->header('User-Agent')),
                'os' => $this->getOS($request->header('User-Agent')),
                'latitude' => $request->input('latitude'),
                'longitude' => $request->input('longitude'),
            ]
        );



        return view('pages.students.admission.e-signature', [
            'applicant' => $applicant,
            'hashedId' => $hashedUri,
            'alreadyAccepted' => $offerAcceptance && $offerAcceptance->status == 'accepted' ? true : false,
        ]);
    }


    public function location(Request $request, $hashedId)
    {
        $id = base64_decode(urldecode($hashedId));
        $applicantESignature = ApplicantESignature::where('applicant_id', $id)->firstOrFail();

        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $applicantESignature->update([
            'latitude' => $request->input('latitude'),
            'longitude' => $request->input('longitude'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Location updated successfully.'
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'applicant_id' => 'required',
            'video_consent' => 'sometimes|accepted',
            'declaration' => 'accepted',
            'signature' => 'required',
        ]);



        $signaturePath = null;
        if ($request->filled('signature')) {
            $signature = $request->input('signature');
            $signature = preg_replace('/^data:image\/\w+;base64,/', '', $signature);
            $signature = str_replace(' ', '+', $signature);
            $imageData = base64_decode($signature);

            $fileName = 'signature_' . time() . '.png';
            $filePath = 'signatures/' . $fileName;

            Storage::disk('public')->put($filePath, $imageData);

            $signaturePath = 'storage/' . $filePath;
        }

        $applicant_id = urldecode(base64_decode($request->input('applicant_id')));

        $applicantESignature = ApplicantESignature::where('applicant_id', $applicant_id)->firstOrFail();
        $oldSignature = $applicantESignature->signature;
        $applicantESignature->update([
            'applicant_id' => urldecode(base64_decode($request->input('applicant_id'))),
            'ip_address' => $request->ip(),
            'device' => $request->header('User-Agent'),
            'browser' => $this->getBrowser($request->header('User-Agent')),
            'os' => $this->getOS($request->header('User-Agent')),
            'latitude' => $request->input('latitude'),
            'longitude' => $request->input('longitude'),
            'status' => 'accepted',
            'video_consent' => $request->has('video_consent') ? 1 : 0,
            'declaration' => $request->has('declaration') ? 1 : 0,
            'signature' => $signaturePath,
            'signed_date' => now(),

        ]);


        if(!$oldSignature):
            $commonSmtp = ComonSmtp::where('is_default', 1)->first();

            $admin = AdminESignature::with('user')->where('applicant_id', $applicant_id)->first();

            $esignEvent =  ApplicantESignatureEvent::create([
                'applicant_id' => $applicant_id,
                'user_type' => 'user',
                'event_type' => EsignEventType::EMAIL_SENT->value,
                'event_description' => "{$admin->user->email} was notified by email",
                'extra_field' => ['opened' => false],
            ]);

            $configuration = [
                'smtp_host'    => $commonSmtp->smtp_host,
                'smtp_port'    => $commonSmtp->smtp_port,
                'smtp_username'  => $commonSmtp->smtp_user,
                'smtp_password'  => $commonSmtp->smtp_pass,
                'smtp_encryption'  => $commonSmtp->smtp_encryption,
                'from_email'    => $commonSmtp->smtp_user,
                'from_name'    => strtok($commonSmtp->smtp_user, '@'),
            ];

            $documentUrl = route('admission.show.e.signature', $applicant_id);

            $trackingUrl = route('tracking.email.open', $esignEvent->id);

            $MAILHTML = '<p>Dear ' . $admin->user->name . ',</p>';
            $MAILHTML .= '<p>An e-signature has been accepted.</p>';
            $MAILHTML .= '<p>Please click the button below to view the document:</p>';
            $MAILHTML .= '<img src="' . $trackingUrl . '" width="1" height="1" style="display:none;" alt="" />';
                $MAILHTML .= '<table align="center" cellspacing="0" cellpadding="0" border="0">';
                    $MAILHTML .= '<tr>';
                        $MAILHTML .= '<td align="center" bgcolor="#1a73e8" style="border-radius:5px;">';
                            $MAILHTML .= '<a href="' . $documentUrl . '" target="_blank" style="display:inline-block; padding:12px 24px; color:#ffffff; text-decoration:none; font-weight:bold; background-color: #164e63;">View Document</a>';
                        $MAILHTML .= '</td>';
                $MAILHTML .= ' </tr>';
            $MAILHTML .= ' </table>';
            $MAILHTML .= '<p style="text-align:center;">If the button above does not work, please copy and paste the following link into your browser:</p>';
            $MAILHTML .= '<p style="text-align:center;">' . $documentUrl . '</p>';
            $MAILHTML .= '<p>Best regards,<br>London Churchill College</p>';

            UserMailerJob::dispatch($configuration, [$admin->user->email], new CommunicationSendMail('E-Signature Accepted', $MAILHTML, []));

            
        endif;

        $applicant = Applicant::find($applicant_id);

        ApplicantESignatureEvent::create([
            'applicant_id' => $applicant->id,
            'user_type' => 'applicant',
            'event_type' => EsignEventType::CONSENTED_TO_ESIGN->value,
            'event_description' => "{$applicant->users->email} consented to esign",
            'ip_address' => $request->ip(),
            'browser' => $this->getBrowser($request->header('User-Agent')),
            'os' => $this->getOS($request->header('User-Agent')),
            'latitude' => $request->input('latitude'),
            'longitude' => $request->input('longitude')
        ]);

        ApplicantESignatureEvent::create([
            'applicant_id' => $applicant->id,
            'user_type' => 'applicant',
            'event_type' => EsignEventType::LOCATION_VERIFIED->value,
            'event_description' => "{$applicant->users->email}'s location was verified",
            'ip_address' => $request->ip(),
            'browser' => $this->getBrowser($request->header('User-Agent')),
            'os' => $this->getOS($request->header('User-Agent')),
            'latitude' => $request->input('latitude'),
            'longitude' => $request->input('longitude')
        ]);

        ApplicantESignatureEvent::create([
            'applicant_id' => $applicant->id,
            'user_type' => 'applicant',
            'event_type' => EsignEventType::FINALIZED->value,
            'event_description' => "{$applicant->users->email} finalized the sign request for the document",
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Offer acceptance submitted successfully.'
        ], 200);
    }


    private function getBrowser($userAgent)
    {
        if (strpos($userAgent, 'Firefox') !== false) return 'Firefox';
        if (strpos($userAgent, 'Chrome') !== false) return 'Chrome';
        if (strpos($userAgent, 'Safari') !== false) return 'Safari';
        if (strpos($userAgent, 'MSIE') !== false || strpos($userAgent, 'Trident') !== false) return 'Internet Explorer';
        return 'Unknown';
    }

    private function getOS($userAgent)
    {
        if (preg_match('/linux/i', $userAgent)) return 'Linux';
        if (preg_match('/macintosh|mac os x/i', $userAgent)) return 'Mac';
        if (preg_match('/windows|win32/i', $userAgent)) return 'Windows';
        return 'Unknown';
    }

    private function convertToDMS($decimal, $isLat = true)
    {
        $direction = $decimal >= 0 ? ($isLat ? 'N' : 'E') : ($isLat ? 'S' : 'W');

        $decimal = abs($decimal);
        $degrees = floor($decimal);
        $minutesDecimal = ($decimal - $degrees) * 60;
        $minutes = floor($minutesDecimal);
        $seconds = ($minutesDecimal - $minutes) * 60;

        return sprintf("%d° %d' %.5f\" %s", $degrees, $minutes, $seconds, $direction);
    }

    private function getMapScreenshot($latitude, $longitude, $applicant_id)
    {
        $apiKey = env('GOOGLE_MAP_API');
        $latitude = number_format((float) $latitude, 7, '.', '');
        $longitude = number_format((float) $longitude, 7, '.', '');
        $mapSize = '640x250';

        $filename = 'location_' . md5($latitude . ',' . $longitude . '|' . $mapSize . '|esign-audit-v3') . '.png';
        $folder = 'applicants/' . $applicant_id;
        $storagePath = $folder . '/' . $filename;
        $pngPath = storage_path('app/public/' . $storagePath);

        if (!Storage::disk('public')->exists($folder)) {
            Storage::disk('public')->makeDirectory($folder, 0775, true);
        }

        if (Storage::disk('public')->exists($storagePath)) {
            return $pngPath;
        }

        $url = 'https://maps.googleapis.com/maps/api/staticmap?' . http_build_query([
            'center' => $latitude . ',' . $longitude,
            'zoom' => 16,
            'size' => $mapSize,
            'scale' => 2,
            'format' => 'png32',
            'markers' => 'color:red|' . $latitude . ',' . $longitude,
            'key' => $apiKey,
        ], '', '&', PHP_QUERY_RFC3986);

        $imageData = @file_get_contents($url);
        if ($imageData === false) {
            return false;
        }

        Storage::disk('public')->put($storagePath, $imageData);

        if (!file_exists($pngPath)) {
            return false;
        }

        return $pngPath;
    }

    private function resolvePdfImagePath(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        if (str_starts_with($path, 'data:') || filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        $normalizedPath = ltrim($path, '/');
        $publicPath = public_path($normalizedPath);

        if (is_file($publicPath)) {
            return $publicPath;
        }

        if (str_starts_with($normalizedPath, 'storage/')) {
            $storagePath = storage_path('app/public/' . substr($normalizedPath, strlen('storage/')));

            if (is_file($storagePath)) {
                return $storagePath;
            }
        }

        return null;
    }




    public function download($id)
    {

        $applicant = Applicant::with('users')->findOrFail($id);
        $applicantEsign = ApplicantESignature::where('applicant_id', $applicant->id)->first();

        $adminEsign = AdminESignature::with('user')->where('applicant_id', $applicant->id)->first();

        $applicantEsignEvents = ApplicantESignatureEvent::where('applicant_id', $applicant->id)->orderBy('id','asc')->get();
        $finalizedEvent = ApplicantESignatureEvent::where('applicant_id', $applicant->id)->where('event_type', EsignEventType::FINALIZED->value)->where('user_type', 'applicant')->first();
        $defaultMap = public_path('build/assets/images/report_icons/google-map.jpg');
        $adminMap = $defaultMap;
        $applicantMap = $defaultMap;

        if (!empty($adminEsign?->latitude) && !empty($adminEsign?->longitude)) {
            $generatedAdminMap = $this->getMapScreenshot($adminEsign->latitude, $adminEsign->longitude, $applicant->id);
            $adminMap = $generatedAdminMap && is_file($generatedAdminMap) ? $generatedAdminMap : $defaultMap;
        }

        if (!empty($applicantEsign?->latitude) && !empty($applicantEsign?->longitude)) {
            $generatedApplicantMap = $this->getMapScreenshot($applicantEsign->latitude, $applicantEsign->longitude, $applicant->id);
            $applicantMap = $generatedApplicantMap && is_file($generatedApplicantMap) ? $generatedApplicantMap : $defaultMap;
        }

        $applicantEmail = $applicant->users->email ?? 'N/A';
        $adminEmail = $adminEsign?->user?->email ?? 'N/A';
        $adminName = $adminEsign?->user?->full_name ?? $adminEsign?->user?->name ?? $adminEmail;
        $applicantName = trim((string) $applicant->full_name) ?: $applicantEmail;
        $fileName = 'audit-' . $applicant->application_no . '.pdf';
        $adminPhoto = !empty($adminEsign?->user?->photo) && Storage::disk('local')->exists('public/users/' . $adminEsign->user->id . '/' . $adminEsign->user->photo)
            ? public_path('storage/users/' . $adminEsign->user->id . '/' . $adminEsign->user->photo)
            : null;
        $applicantPhoto = !empty($applicant->photo) && Storage::disk('local')->exists('public/applicants/' . $applicant->id . '/' . $applicant->photo)
            ? public_path('storage/applicants/' . $applicant->id . '/' . $applicant->photo)
            : null;

        $signers = [
            [
                'name' => $adminName,
                'email' => $adminEmail,
                'label' => 'Signer #1',
                'color' => '#0d7a76',
                'ip_address' => $adminEsign?->ip_address,
                'browser' => $adminEsign?->browser,
                'os' => $adminEsign?->os,
                'latitude' => $adminEsign?->latitude,
                'longitude' => $adminEsign?->longitude,
                'signed_at' => $adminEsign?->created_at,
                'map' => $adminMap,
                'photo' => $adminPhoto,
            ],
            [
                'name' => $applicantName,
                'email' => $applicantEmail,
                'label' => 'Signer #2',
                'color' => '#8E2A3C',
                'ip_address' => $applicantEsign?->ip_address,
                'browser' => $applicantEsign?->browser,
                'os' => $applicantEsign?->os,
                'latitude' => $applicantEsign?->latitude,
                'longitude' => $applicantEsign?->longitude,
                'signed_at' => $finalizedEvent?->created_at ?? $applicantEsign?->signed_date,
                'map' => $applicantMap,
                'photo' => $applicantPhoto,
            ],
        ];

        $PDFHTML = view('pages.students.admission.pdf.e-signature-audit', [
            'applicant' => $applicant,
            'applicantEsign' => $applicantEsign,
            'adminEsign' => $adminEsign,
            'applicantEsignEvents' => $applicantEsignEvents,
            'finalizedEvent' => $finalizedEvent,
            'signers' => $signers,
            'signatureImage' => $this->resolvePdfImagePath($applicantEsign?->signature),
            'logoImage' => public_path('build/assets/images/logo_white.svg'),
            'documentImage' => public_path('build/assets/images/report_icons/document-image.jpg'),
            'reference' => $applicant->application_no ?? $applicant->id,
            'signatureId' => $applicantEsign?->id ? 'ESIG-' . $applicant->id . '-' . $applicantEsign->id : 'ESIG-' . $applicant->id,
            'fromEmail' => $adminEsign?->smtp_email ?? 'N/A',
            'applicantEmail' => $applicantEmail,
            'adminEmail' => $adminEmail,
            'applicantName' => $applicantName,
        ])->render();

        $pdf = PDF::loadHTML($PDFHTML)
            ->setOption(['isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true])
            ->setPaper('a4', 'portrait')
            ->setWarnings(false);

        return $pdf->stream($fileName);
    }


    public function trackingEmailOpen($eventId)
    {
        $event = ApplicantESignatureEvent::find($eventId);

        if ($event->event_type === EsignEventType::EMAIL_SENT->value && $event->user_type === 'applicant' && isset($event->extra_field['opened']) && $event->extra_field['opened'] === false) {
            ApplicantESignatureEvent::create([
                'applicant_id'      => $event->applicant_id,
                'user_type'         => 'applicant',
                'event_type'        => EsignEventType::EMAIL_READ->value,
                'event_description' => $event->applicant->users->email . " opened the notificaiton email",
            ]);
        }
        if ($event->event_type === EsignEventType::EMAIL_SENT->value && $event->user_type === 'user' && isset($event->extra_field['opened']) && $event->extra_field['opened'] === false) {
            ApplicantESignatureEvent::create([
                'applicant_id'      => $event->applicant_id,
                'user_type'         => 'user',
                'event_type'        => EsignEventType::EMAIL_READ->value,
                'event_description' => $event->user->email . " opened the notificaiton email",
            ]);
        }

        if ($event) {
            $event->extra_field = array_merge($event->extra_field ?? [], [
                'opened' => true,
                'opened_at' => now()->toDateTimeString(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
            $event->save();
        }

         $transparentGif = base64_decode('R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==');
        return response($transparentGif, 200)
            ->header('Content-Type', 'image/gif')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }


}
