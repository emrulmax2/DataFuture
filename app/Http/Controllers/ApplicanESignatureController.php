<?php

namespace App\Http\Controllers;

use App\Enums\EsignEventType;
use App\Jobs\StoreSignedApplicationDocuments;
use App\Jobs\UserMailerJob;
use App\Mail\CommunicationSendMail;
use App\Models\AdminESignature;
use App\Models\Applicant;
use App\Models\ApplicantESignature;
use App\Models\ApplicantESignatureEvent;
use App\Models\Option;
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

        /* Every opening of the link is part of the evidence trail (when, from
           where, through which link), not just the first. Refreshes from the
           same IP and device within ten minutes collapse into one event. */
        $browser = $this->getBrowser($request->header('User-Agent'));
        $os = $this->getOS($request->header('User-Agent'));
        $via = $firstOpenVia == 'e' ? 'email' : ($firstOpenVia == 's' ? 'sms' : null);
        $lastView = ApplicantESignatureEvent::where('applicant_id', $applicant->id)
            ->where('user_type', 'applicant')
            ->where('event_type', EsignEventType::VIEWED->value)
            ->orderBy('id', 'desc')
            ->first();
        $sameVisit = $lastView
            && $lastView->created_at && $lastView->created_at->gt(now()->subMinutes(10))
            && $lastView->ip_address === $request->ip()
            && $lastView->browser === $browser
            && $lastView->os === $os;
        if (!$sameVisit) {
            ApplicantESignatureEvent::create([
                'applicant_id' => $applicant->id,
                'user_type' => 'applicant',
                'event_type' => EsignEventType::VIEWED->value,
                'event_description' => "{$applicant->users->email} viewed the document".($via ? " via the {$via} link" : ''),
                'ip_address' => $request->ip(),
                'browser' => $browser,
                'os' => $os,
                'latitude' => $request->input('latitude'),
                'longitude' => $request->input('longitude'),
                'extra_field' => ['via' => $via, 'repeat' => $lastView !== null],
            ]);
        }



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
        /* Location is part of the signature's audit trail and is required to
           sign: the page only submits once the device has returned a fix, and
           the server holds the same line. */
        $request->validate([
            'applicant_id' => 'required',
            'video_consent' => 'sometimes|accepted',
            'declaration' => 'accepted',
            'signature' => 'required',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ], [
            'latitude.required' => 'Your location is needed to sign. Please allow location access and try again.',
            'longitude.required' => 'Your location is needed to sign. Please allow location access and try again.',
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

        // Only a real fix is "verified"; without coordinates the request is refused above.
        if ($request->filled('latitude') && $request->filled('longitude')) {
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
        }

        ApplicantESignatureEvent::create([
            'applicant_id' => $applicant->id,
            'user_type' => 'applicant',
            'event_type' => EsignEventType::FINALIZED->value,
            'event_description' => "{$applicant->users->email} finalized the sign request for the document",
        ]);

        // The signed application form + audit trail PDFs go into the applicant's Uploads ("Other").
        StoreSignedApplicationDocuments::dispatch((int) $applicant->id);

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

    /**
     * The certificate's map tiles are grayscale (the design); a converted copy is
     * cached under storage/app/public/esign-audit so it is made once per map.
     */
    private function greyscaleImage(?string $path): ?string
    {
        if (empty($path) || !is_file($path) || !function_exists('imagecreatefrompng')) {
            return $path ?: null;
        }
        $dir = storage_path('app/public/esign-audit');
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $out = $dir.'/'.md5($path.'|'.filemtime($path)).'_grey.png';
        if (is_file($out)) {
            return $out;
        }
        $info = @getimagesize($path);
        $im = match ($info[2] ?? null) {
            IMAGETYPE_PNG => @imagecreatefrompng($path),
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            default => null,
        };
        if (!$im) {
            return $path;
        }
        imagefilter($im, IMG_FILTER_GRAYSCALE);
        imagefilter($im, IMG_FILTER_CONTRAST, -5);
        imagesavealpha($im, true);
        $ok = imagepng($im, $out);
        imagedestroy($im);

        return $ok ? $out : $path;
    }

    /**
     * The crest alone, cut from the site logo (Site settings > logo), whose wordmark
     * is white on transparent and would vanish on paper. The certificate sets the
     * college name in type next to it, as the design does. Cached like the maps.
     */
    private function crestFromLogo(?string $path): ?string
    {
        if (empty($path) || !is_file($path) || !function_exists('imagecreatefrompng')) {
            return $path ?: null;
        }
        $dir = storage_path('app/public/esign-audit');
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $out = $dir.'/'.md5($path.'|'.filemtime($path)).'_crest.png';
        if (is_file($out)) {
            return $out;
        }
        $im = @imagecreatefrompng($path);
        if (!$im) {
            return $path;
        }
        $w = imagesx($im);
        $h = imagesy($im);
        // Scan left to right for the first fully transparent gap after the crest.
        $lastVisible = -1;
        $gap = 0;
        $cut = $w;
        for ($x = 0; $x < $w; $x++) {
            $visible = false;
            for ($y = 0; $y < $h; $y += 2) {
                if (((imagecolorat($im, $x, $y) >> 24) & 0x7F) < 120) { $visible = true; break; }
            }
            if ($visible) { $lastVisible = $x; $gap = 0; }
            elseif ($lastVisible > $w * 0.1) { $gap++; if ($gap >= max(4, (int) ($w * 0.01))) { $cut = $lastVisible + 1; break; } }
        }
        $crest = imagecrop($im, ['x' => 0, 'y' => 0, 'width' => max(1, $cut), 'height' => $h]);
        imagedestroy($im);
        if (!$crest) {
            return $path;
        }
        imagesavealpha($crest, true);
        $ok = imagepng($crest, $out);
        imagedestroy($crest);

        return $ok ? $out : $path;
    }

    /** The logo uploaded in Site settings, or the bundled one. */
    private function siteLogoPath(): ?string
    {
        $file = Option::where('category', 'SITE_SETTINGS')->where('name', 'site_logo')->value('value');
        if ($file && Storage::disk('local')->exists('public/'.$file)) {
            return storage_path('app/public/'.$file);
        }
        $bundled = public_path('build/assets/images/red_and_white_logo.png');

        return is_file($bundled) ? $bundled : null;
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
        [$pdf, $fileName] = $this->auditPdf($id);

        return $pdf->stream($fileName);
    }

    /**
     * The e-signature audit trail PDF and its file name. Also stored in the applicant's
     * Uploads once the applicant signs (App\Services\Admission\SignedApplicationDocuments).
     */
    public function auditPdf($id): array
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
        // The design prints the maps in grayscale.
        $adminMap = $this->greyscaleImage($adminMap);
        $applicantMap = $this->greyscaleImage($applicantMap);

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

        $isFinalised = !empty($applicantEsign?->signature) || $finalizedEvent !== null;
        $initialisedAt = $adminEsign?->created_at ?? $applicantEsign?->created_at;
        $signedAt = $finalizedEvent?->created_at ?? $applicantEsign?->signed_date;
        // "4 minutes", "2 days" … between the request and the signature.
        $duration = null;
        if ($isFinalised && $initialisedAt && $signedAt) {
            $duration = \Carbon\Carbon::parse($initialisedAt)->diffForHumans(\Carbon\Carbon::parse($signedAt), ['parts' => 1, 'syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE]);
        }
        // Who raised the request: the staff button, or the application submission (applicant / agent).
        $createdEvent = $applicantEsignEvents->first(fn ($e) => $e->event_type === EsignEventType::SIGN_REQUEST_CREATED->value);
        $triggerInfo = is_array($createdEvent?->extra_field) ? $createdEvent->extra_field : [];
        $trigger = !empty($triggerInfo['trigger'])
            ? 'On the application submission'.(!empty($triggerInfo['by_type']) ? ' by the '.$triggerInfo['by_type'] : '').(!empty($triggerInfo['by_email']) ? ' ('.$triggerInfo['by_email'].')' : '')
            : (($adminEsign?->device ?? null) === 'Task Automation' ? 'By task automation' : null);
        $via = [];
        if ((int) ($adminEsign?->via_email ?? 0) === 1) $via[] = 'email';
        if ((int) ($adminEsign?->via_sms ?? 0) === 1) $via[] = 'SMS';
        $consented = $applicantEsignEvents->contains(fn ($e) => $e->event_type === EsignEventType::CONSENTED_TO_ESIGN->value);

        $signers = [
            [
                'name' => $adminName,
                'email' => $adminEmail,
                'label' => 'Signer #1',
                'color' => '#0d7a76',
                'status' => 'Signed',
                'email_status' => 'Verified',
                'consent' => 'Agreed to eSign',
                'time_label' => 'Signed',
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
                'status' => $isFinalised ? 'Signed' : 'Pending',
                'email_status' => $isFinalised ? 'Verified' : ($applicantEsign ? 'Link opened' : 'Awaiting'),
                'consent' => $isFinalised || $consented ? 'Agreed to eSign' : 'Not yet given',
                'time_label' => $isFinalised ? 'Signed' : 'Last opened',
                'ip_address' => $applicantEsign?->ip_address,
                'browser' => $applicantEsign?->browser,
                'os' => $applicantEsign?->os,
                'latitude' => $applicantEsign?->latitude,
                'longitude' => $applicantEsign?->longitude,
                'signed_at' => $isFinalised ? ($finalizedEvent?->created_at ?? $applicantEsign?->signed_date) : $applicantEsign?->updated_at,
                'map' => $applicantMap,
                'photo' => $applicantPhoto,
            ],
        ];

        $reference = $applicant->application_no ?? $applicant->id;
        $PDFHTML = view('pages.students.admission.pdf.e-signature-audit', [
            'applicant' => $applicant,
            'applicantEsign' => $applicantEsign,
            'adminEsign' => $adminEsign,
            'applicantEsignEvents' => $applicantEsignEvents,
            'finalizedEvent' => $finalizedEvent,
            'signers' => $signers,
            'signatureImage' => $this->resolvePdfImagePath($applicantEsign?->signature),
            'logoImage' => $this->crestFromLogo($this->siteLogoPath()),
            'documentImage' => public_path('build/assets/images/report_icons/document-image.jpg'),
            'isFinalised' => $isFinalised,
            'statusLabel' => $isFinalised ? 'Finalised' : 'Awaiting signature',
            'initialisedAt' => $initialisedAt,
            'signedAt' => $signedAt,
            'duration' => $duration,
            'trigger' => $trigger,
            'requestVia' => implode(' + ', $via),
            'documentName' => (int) $applicant->status_id >= 6 ? 'Offer Acceptance Form' : 'Signed Application Form',
            'generatedAt' => now(),
            'reference' => $reference,
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

        // Running footer on every page: "Audit of <ref> · London Churchill College" and "Page n of m".
        $pdf->render();
        $dompdf = $pdf->getDomPDF();
        $canvas = $dompdf->getCanvas();
        $metrics = $dompdf->getFontMetrics();
        $font = $metrics->getFont('Hanken Grotesk', 'normal') ?: $metrics->getFont('DejaVu Sans', 'normal');
        $bold = $metrics->getFont('Hanken Grotesk', 'bold') ?: $font;
        $w = $canvas->get_width();
        $h = $canvas->get_height();
        $left = 14 * 72 / 25.4;
        $right = $w - $left;
        $y = $h - (9 * 72 / 25.4);
        $grey = [0.48, 0.52, 0.5];
        $canvas->line($left, $y - 9, $right, $y - 9, [0.85, 0.85, 0.83], 0.6);
        $canvas->page_text($left, $y, 'Audit of ', $font, 7.5, $grey);
        $refX = $left + $metrics->getTextWidth('Audit of ', $font, 7.5);
        $canvas->page_text($refX, $y, (string) $reference, $bold, 7.5, [0.08, 0.13, 0.12]);
        $canvas->page_text($refX + $metrics->getTextWidth((string) $reference, $bold, 7.5), $y, ' · London Churchill College', $font, 7.5, $grey);
        $pageText = 'Page {PAGE_NUM} of {PAGE_COUNT}';
        $canvas->page_text($right - $metrics->getTextWidth('Page 0 of 0', $font, 7.5), $y, $pageText, $font, 7.5, $grey);

        // return $pdf->stream($fileName);
        return [$pdf, $fileName];
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
