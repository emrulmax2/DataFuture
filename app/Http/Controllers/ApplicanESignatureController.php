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

        $url = "https://maps.googleapis.com/maps/api/staticmap?center={$latitude},{$longitude}&zoom=15&size=800x300&markers=color:red%7C{$latitude},{$longitude}&key={$apiKey}";

        $filename = 'location_' . time() . '.png';
        $folder = 'applicants/' . $applicant_id;

        if (!Storage::disk('public')->exists($folder)) {
            Storage::disk('public')->makeDirectory($folder, 0775, true);
        }

        $imageData = file_get_contents($url);
        if ($imageData === false) {
            return false;
        }

        Storage::disk('public')->put($folder . '/' . $filename, $imageData);

        $pngPath = storage_path('app/public/' . $folder . '/' . $filename);
        $jpgFilename = str_replace('.png', '.jpg', $filename);
        $jpgPath = storage_path('app/public/' . $folder . '/' . $jpgFilename);

        if (!file_exists($pngPath)) {
            return false;
        }

        $image = imagecreatefrompng($pngPath);
        if (!$image) {
            return false;
        }

        $bg = imagecreatetruecolor(imagesx($image), imagesy($image));
        $white = imagecolorallocate($bg, 255, 255, 255);
        imagefill($bg, 0, 0, $white);
        imagecopy($bg, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));

        $success = imagejpeg($bg, $jpgPath, 90);

        imagedestroy($image);
        imagedestroy($bg);

        unlink($pngPath);

        return $success ? $jpgPath : false;
    }




    public function download($id)
    {

        $applicant = Applicant::find($id);
        $applicantEsign = ApplicantESignature::where('applicant_id', $applicant->id)->first();

        $adminEsign = AdminESignature::with('user')->where('applicant_id', $applicant->id)->first();

        $applicantEsignEvents = ApplicantESignatureEvent::where('applicant_id', $applicant->id)->orderBy('id','asc')->get();
        $finalizedEvent = ApplicantESignatureEvent::where('applicant_id', $applicant->id)->where('event_type', EsignEventType::FINALIZED->value)->where('user_type', 'applicant')->first();
  

        $verifiedIcon = public_path('build/assets/images/report_icons/verified-icon.svg');
        $documentImage = public_path('build/assets/images/report_icons/document-image.jpg');
        $fileName = 'audit-' . $applicant->application_no . '.pdf';


        $PDFHTML = '';
        $PDFHTML .= '<html>';
        $PDFHTML .= '<head>';
            $PDFHTML .= '<title>Audit of '.$applicant->application_no. '</title>';
            $PDFHTML .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
            $PDFHTML .= '<style>';
                $PDFHTML .= 'body { font-family: Vardana, sans-serif; font-size: 13px; color: #1e293b; margin: 30px; }';
                $PDFHTML .= 'h1 { font-size: 20px; margin: 0 0 16px 0; }';
                $PDFHTML .= 'h2 { font-size: 16px; margin: 20px 0 12px 0; color: #374151; }';
                $PDFHTML .= '.mr-2 { margin-right: 4px; }';
                $PDFHTML .= '.-mt-2 { margin-top: -8px; }';
                $PDFHTML .= 'table.columns { width: 100%; border-collapse: collapse; }';
                $PDFHTML .= 'td.left { width: 150px; padding-right: 18px; vertical-align: top; }';
                $PDFHTML .= 'td.left img { width: 120px; height: auto; display: block; border: 1px solid #d1d5db; }';
                $PDFHTML .= 'td.right { vertical-align: top; }';
                $PDFHTML .= 'table.meta { width: 100%; border-collapse: collapse; }';
                $PDFHTML .= 'table.meta td { padding: 6px 4px; vertical-align: top; }';
                $PDFHTML .= '.label { width: 110px; }';
                $PDFHTML .= 'a { color: #2563eb; text-decoration: none; }';
                $PDFHTML .= '.signers-table { width: 100%; border-collapse: collapse; margin: 16px 0; }';
                $PDFHTML .= '.signers-table th, .signers-table td { padding: 8px 12px; text-align: left; border-bottom: 1px solid #e5e7eb; }';
                $PDFHTML .= '.signers-table th { background: #f9fafb; font-weight: bold; font-size: 12px; color: #374151; }';
                $PDFHTML .= '.signers-table td { font-size: 12px; }';
                $PDFHTML .= '.signer-info { margin: 8px 0; }';
                $PDFHTML .= '.signer-email { font-weight: bold; margin-bottom: 4px; }';
                $PDFHTML .= '.signer-details { font-size: 11px; color: #6b7280; }';
                $PDFHTML .= '.verification-badges { margin: 4px 0; }';
                $PDFHTML .= '.verification-badges .badge { margin-right: 4px; margin-bottom: 2px; }';
                $PDFHTML .= '.map-container { width: 100%; }';
                $PDFHTML .= '.map-image { width: 100%; height: 250px; border: 1px solid #d1d5db; border-radius: 4px; object-fit: cover; }';
                $PDFHTML .= '.signer-section { margin: 0 0; }';
            $PDFHTML .= '</style>';
        $PDFHTML .= '</head>';
        $PDFHTML .= '<body>';
            $PDFHTML .= '<h1>Audit of \'' . $applicant->application_no . '\'</h1>';
            $PDFHTML .= '<table class="columns">';
                $PDFHTML .= '<tr>';
                    $PDFHTML .= '<td class="left">';
                        $PDFHTML .= '<img src="'.$documentImage.'" alt="preview" />';
                    $PDFHTML .= '</td>';
                    $PDFHTML .= '<td class="right ">';
                        $PDFHTML .= '<table class="meta -mt-2">';
                            $PDFHTML .= '<tr>';
                                $PDFHTML .= '<td colspan="2">This document is a <span style="background: #eeeeee; padding: 3px 5px;border-radius: 5px;">FINALIZED</span> sign request.</td>';
                            $PDFHTML .= '</tr>';
                            $PDFHTML .= '<tr>';
                                $PDFHTML .= '<td class="label">From</td>';
                                $PDFHTML .= '<td>London Churchill College ('. (isset($adminEsign->smtp_email) && !empty($adminEsign->smtp_email) ? $adminEsign->smtp_email : 'N/A') . ')</td>';
                            $PDFHTML .= '</tr>';
                            $PDFHTML .= '<tr>';
                                $PDFHTML .= '<td class="label">File Owner</td>';
                                $PDFHTML .= '<td>London Churchill College</td>';
                            $PDFHTML .= '</tr>';
                            $PDFHTML .= '<tr>';
                                $PDFHTML .= '<td class="label">Signing Order</td>';
                                $PDFHTML .= '<td>';
                                    $PDFHTML .=  1 . '. ' . (isset($adminEsign->user->email) && !empty($adminEsign->user->email) ? $adminEsign->user->email : 'N/A')  . '<br/>';
                                    $PDFHTML .=  2 . '. ' . (isset($applicant->users->email) && !empty($applicant->users->email) ? $applicant->users->email : 'N/A') . '<br/>';
                                $PDFHTML .= '</td>';
                            $PDFHTML .= '</tr>';
                            $PDFHTML .= '<tr>';
                                $PDFHTML .= '<td class="label">Initialized</td>';
                                $PDFHTML .= '<td>'.(isset($adminEsign->created_at) && !empty($adminEsign->created_at) ? date('M d, Y', strtotime($adminEsign->created_at)).' '.date('h:i A T', strtotime($adminEsign->created_at)) : 'N/A').'</td>';
                            $PDFHTML .= '</tr>';
                            $PDFHTML .= '<tr>';
                                $PDFHTML .= '<td class="label">Finalized</td>';
                                $PDFHTML .= '<td>'.(isset($finalizedEvent->created_at) && !empty($finalizedEvent->created_at) ? date('M d, Y', strtotime($finalizedEvent->created_at)).' '.date('h:i A T', strtotime($finalizedEvent->created_at)) : 'N/A').'</td>';
                            $PDFHTML .= '</tr>';
                        $PDFHTML .= '</table>';
                    $PDFHTML .= '</td>';
                $PDFHTML .= '</tr>';
            $PDFHTML .= '</table>';
            if(isset($applicantEsign->signature) && !empty($applicantEsign->signature)):
            $PDFHTML .= '<div style="text-align:center; margin-top:30px;">';
                $PDFHTML .= '<img src="'. (isset($applicantEsign->signature) && !empty($applicantEsign->signature) ? $applicantEsign->signature : '') . '" style="max-width: 500px; height: auto; display:inline-block;" />';
                $PDFHTML .= '<p style="margin-top:10px;">Signature</p>';
            $PDFHTML .= '</div>';
            endif;
           $PDFHTML .= '<div style="page-break-before: always;">';
             $PDFHTML .= '<h2>Signers</h2>';
            $PDFHTML .= '<div class="signer-section">';
                $PDFHTML .= '<div class="signer-header" style="overflow: hidden; margin-bottom: 12px;">';
                    $PDFHTML .= '<img src="'. (isset($adminEsign->user->photo) && !empty($adminEsign->user->photo) && Storage::disk('local')->exists('public/users/' . $adminEsign->user->id . '/' . $adminEsign->user->photo) ? public_path('storage/users/' . $adminEsign->user->id . '/' . $adminEsign->user->photo) : public_path('build/assets/images/placeholders/200x200.jpg')) . '" alt="User Avatar" style="width: 50px; height: 50px;" />';
                $PDFHTML .= '</div>';
                $PDFHTML .= '<div class="signer-info">';
                    $PDFHTML .= '<div class="signer-email">'. (isset($adminEsign->user->email) && !empty($adminEsign->user->email) ? $adminEsign->user->email : 'N/A'). '</div>';
                    $PDFHTML .= '<div class="signer-details">Signer #1 - p.murphy+un0wqq</div>';
                    $PDFHTML .= '<div class="verification-badges">';
                        $PDFHTML .= '<span style="color: #6b7280; margin-right: 8px;"><img src="'.$verifiedIcon.'" alt="Verified" style="width: 8px; height: 8px; vertical-align: middle; margin-right: 8px;" />Verified Email</span>';
                        $PDFHTML .= '<span style="color: #6b7280; margin-right: 8px;"><img src="'.$verifiedIcon.'" alt="Verified" style="width: 8px; height: 8px; vertical-align: middle; margin-right: 8px;" />Verified IP ' .($adminEsign?->ip_address ?? 'N/A'). '</span>';
                        $PDFHTML .= '<span style="color: #6b7280; margin-right: 8px;"><img src="'.$verifiedIcon.'" alt="Verified" style="width: 8px; height: 8px; vertical-align: middle; margin-right: 8px;" />Verified consent to Esign</span>';
                    $PDFHTML .= '</div>';
                    $PDFHTML .= '<div class="signer-details">Verified geolocation '. $this->convertToDMS($adminEsign?->latitude, true) . ' ' . $this->convertToDMS($adminEsign?->longitude, false) . ' (66661 m)</div>';
                $PDFHTML .= '</div>';
            $PDFHTML .= '</div>';
            $PDFHTML .= '<div class="map-container">';
                $PDFHTML .= '<img src="'. (isset($adminEsign->latitude) && !empty($adminEsign->latitude) ? $this->getMapScreenshot($adminEsign->latitude,$adminEsign->longitude, $applicant->id) : public_path('build/assets/images/report_icons/google-map.jpg')).'" alt="Location Map" class="map-image" />';
            $PDFHTML .= '</div>';
            $PDFHTML .= '<div class="signer-section" style="margin-top: 20px">';
                $PDFHTML .= '<div class="signer-header" style="overflow: hidden; margin-bottom: 12px;">';
                    $PDFHTML .= '<img src="'.(isset($applicant->photo) && !empty($applicant->photo) && Storage::disk('local')->exists('public/applicants/' . $applicant->id . '/' . $applicant->photo) ? public_path('storage/applicants/' . $applicant->id . '/' . $applicant->photo) : public_path('build/assets/images/placeholders/200x200.jpg')) . '" alt="User Avatar" style="width: 50px; height: 50px;" />';
                $PDFHTML .= '</div>';
                $PDFHTML .= '<div class="signer-info">';
                    $PDFHTML .= '<div class="signer-email">'. $applicant->users->email. '</div>';
                    $PDFHTML .= '<div class="signer-details">Signer #1 - p.murphy+un0wqq</div>';
                    $PDFHTML .= '<div class="verification-badges">';
                        $PDFHTML .= '<span style="color: #6b7280; margin-right: 8px;"><img src="'.$verifiedIcon.'" alt="Verified" style="width: 8px; height: 8px; vertical-align: middle; margin-right: 8px;" />Verified Email</span>';
                        $PDFHTML .= '<span style="color: #6b7280; margin-right: 8px;"><img src="'.$verifiedIcon.'" alt="Verified" style="width: 8px; height: 8px; vertical-align: middle; margin-right: 8px;" />Verified IP ' .($applicantEsign?->view_ip_address ?? 'N/A'). '</span>';
                        $PDFHTML .= '<span style="color: #6b7280; margin-right: 8px;"><img src="'.$verifiedIcon.'" alt="Verified" style="width: 8px; height: 8px; vertical-align: middle; margin-right: 8px;" />Verified consent to Esign</span>';
                    $PDFHTML .= '</div>';
                    $PDFHTML .= '<div class="signer-details">Verified geolocation '. $this->convertToDMS($applicantEsign?->latitude, true) . ' ' . $this->convertToDMS($applicantEsign?->longitude, false) . ' (66661 m)</div>';
                $PDFHTML .= '</div>';
            $PDFHTML .= '</div>';
            $PDFHTML .= '<div class="map-container">';
                $PDFHTML .= '<img src="'.(isset($applicantEsign->latitude) && !empty($applicantEsign->latitude) ? $this->getMapScreenshot($applicantEsign->latitude,$applicantEsign->longitude, $applicant->id) : public_path('build/assets/images/report_icons/google-map.jpg')).'" alt="Location Map" class="map-image" />';
            $PDFHTML .= '</div>';
           $PDFHTML .= '</div>';

           $PDFHTML .= '<div style="margin-top:32px; page-break-before:always;">';
            $PDFHTML .= '<h2 style="font-size:18px;margin:28px 0 14px 0;font-weight:600;">Audit Trail</h2>';
            $PDFHTML .= '<table style="width:100%; border-collapse:collapse; font-size:13px; color:#1e293b;">';
            foreach ($applicantEsignEvents as $event) {
                $PDFHTML .= '<tr style="border-bottom:1px solid #e5e7eb; vertical-align:top;">';
                $PDFHTML .= '<td style="padding-top: 8px;vertical-align:top;">';
                if ($event->event_type === EsignEventType::SIGN_REQUEST_CREATED->value) {
                    $PDFHTML .= '<img style="width: 16px; height: 16px; background: transparent; border: none;" src="'.public_path('build/assets/images/report_icons/notebook-text.png').'" alt="Notebook" />';
                } elseif ($event->event_type === EsignEventType::EMAIL_SENT->value) {
                    $PDFHTML .= '<img style="width: 16px; height: 16px; background: transparent; border: none;" src="'.public_path('build/assets/images/report_icons/mail.png').'" alt="Mail Send" />';
                } elseif ($event->event_type === EsignEventType::VIEWED->value) {
                    $PDFHTML .= '<img style="width: 16px; height: 16px; background: transparent; border: none;" src="'.public_path('build/assets/images/report_icons/eye.png').'" alt="View" />';
                } elseif ($event->event_type === EsignEventType::LOCATION_VERIFIED->value) {
                    $PDFHTML .= '<img style="width: 16px; height: 16px; background: transparent; border: none;" src="'.public_path('build/assets/images/report_icons/map-pinned.png').'" alt="Location Map" />';
                } elseif ($event->event_type === EsignEventType::CONSENTED_TO_ESIGN->value) {
                    $PDFHTML .= '<img style="width: 16px; height: 16px; background: transparent; border: none;" src="'.public_path('build/assets/images/report_icons/square-check.png').'" alt="Consented to e-sign" />';
                } elseif ($event->event_type === EsignEventType::FINALIZED->value) {
                    $PDFHTML .= '<img style="width: 16px; height: 16px; background: transparent; border: none;" src="'.public_path('build/assets/images/report_icons/file-check.png').'" alt="Finalized" />';
                } elseif ($event->event_type === EsignEventType::EMAIL_READ->value) {
                    $PDFHTML .= '<img style="width: 16px; height: 16px; background: transparent; border: none;" src="'.public_path('build/assets/images/report_icons/mail-open.png').'" alt="Mail Read" />';
                }
                $PDFHTML .= '</td>';
                $PDFHTML .= '<td style="width:140px; padding:8px; font-weight:bold;">'. (EsignEventType::fromValue($event->event_type)?->label() ?? $event->event_type). '</td>';
                $PDFHTML .= '<td style="padding:8px;">' . $event->event_description;
                if (isset($event->extra_field['opened']) && $event->extra_field['opened'] === true):
                    $PDFHTML .= '<span style="background:#f3f4f6; color:#374151; padding:2px 6px; border-radius:4px; font-weight:500; margin-left:6px;">OPENED</span>';
                endif;
                if (in_array($event->event_type, [EsignEventType::SIGN_REQUEST_CREATED->value,EsignEventType::VIEWED->value, EsignEventType::CONSENTED_TO_ESIGN->value]) && !empty($event->ip_address)):
                    $PDFHTML .= '<div style="font-size:11px; color:#64748b; margin-top:2px;">' . 'IP ' . $event->ip_address . ', ' . $event->browser . ', ' . $event->os. '</div>';
                endif;
                if ($event->event_type === EsignEventType::EMAIL_SENT->value):
                    $PDFHTML .= '<div style="font-size:11px; color:#64748b; margin-top:2px;">' . $event->created_at->diffForHumans() . ', ' . date('M d, Y h:i A T', strtotime($event->created_at)). '</div>';
                endif;
                if ($event->event_type === EsignEventType::LOCATION_VERIFIED->value):
                    $PDFHTML .= '<div style="font-size:11px; color:#64748b; margin-top:2px;">'. 'IP ' . $event->ip_address . ', ' . $event->browser . ', ' . $event->os. '</div>';
                    $PDFHTML .= '<div style="font-size:11px; color:#64748b; margin-top:2px;">'. $event->latitude_d_m_s . ' ' . $event->longitude_d_m_s. '</div>';
                endif;
                $PDFHTML .= '</td>';
                $PDFHTML .= '<td style="padding:8px; text-align:right; font-size:12px; white-space:nowrap;">' . date('M d, Y', strtotime($event->created_at)) . '<br>'. date('h:i A T', strtotime($event->created_at)) . '</td>';
                $PDFHTML .= '</tr>';
            }
            $PDFHTML .= '</table>';
            $PDFHTML .= '</div>';
        $PDFHTML .= '</body>';
        $PDFHTML .= '</html>';

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