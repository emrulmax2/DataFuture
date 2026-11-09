<?php

namespace App\Mail;

use App\Models\Applicant;
use App\Models\Option;
use App\Support\EmailLogo;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * The offer was declined (OfferAcceptanceWorkflow::decline): a confirmation to the applicant,
 * or a notice to their agent. Same look as the DocuSeal offer acceptance request email
 * (navy band, gold rule). Default mailer, so outside production it goes to MAIL_REDIRECT_TO.
 */
class OfferDeclinedNotice extends Mailable
{
    use Queueable, SerializesModels;

    public array $copy;

    /** @param string $audience "applicant" or "agent" */
    public function __construct(public string $audience, public Applicant $applicant, public array $details = [])
    {
        $name = trim(($applicant->first_name ?? '').' '.($applicant->last_name ?? ''));
        $this->copy = $audience === 'agent'
            ? [
                'subject' => 'Offer declined: '.$name.' ('.$applicant->application_no.')',
                'eyebrow' => 'Offer declined',
                'title' => 'Your applicant has declined their offer',
                'greeting' => 'Hello,',
                'paragraphs' => [
                    $name.' has declined the offer from London Churchill College, so the application is now marked as Offer Rejected.',
                    'If this was not intended, or the applicant would like to reconsider, please contact our Admissions Team. We can send a new offer acceptance form.',
                ],
            ]
            : [
                'subject' => 'You have declined your offer from London Churchill College',
                'eyebrow' => 'Offer declined',
                'title' => 'Your offer has been declined',
                'greeting' => 'Hi '.(trim((string) $applicant->first_name) ?: 'there').',',
                'paragraphs' => [
                    'This is to confirm that you have declined your offer to study at London Churchill College. Your offer acceptance form has been withdrawn, so its signing link will no longer work.',
                    'If you declined by mistake or change your mind, please contact our Admissions Team as soon as possible. We will be happy to help and can send you a new offer acceptance form.',
                ],
            ];
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->copy['subject']);
    }

    public function content(): Content
    {
        $applicant = $this->applicant->loadMissing(['course.creation.course', 'course.semester', 'title']);
        $site = Option::where('category', 'SITE_SETTINGS')->whereIn('name', ['site_logo', 'company_name', 'company_address', 'company_phone'])->pluck('value', 'name');
        $declinedAt = $this->details['declined_at'] ?? null;

        return new Content(view: 'emails.admission.offer-declined', with: [
            'logoPath' => EmailLogo::path(),
            'logoUrl' => !empty($site['site_logo']) ? url('storage/'.$site['site_logo']) : null,
            'companyName' => $site['company_name'] ?? 'London Churchill College',
            'companyAddress' => $site['company_address'] ?? 'Barclay Hall, 156B Green Street, London, E7 8JQ',
            'companyPhone' => OfferAcceptanceUpdate::displayPhone($site['company_phone'] ?? '02073771077'),
            'companyPhoneTel' => OfferAcceptanceUpdate::telPhone($site['company_phone'] ?? '02073771077'),
            'contactEmail' => (string) config('services.docuseal.contact_email', 'admission@lcc.ac.uk'),
            'rows' => array_filter([
                'Applicant' => $this->audience === 'agent' ? trim(implode(' ', array_filter([$applicant->title->name ?? null, $applicant->first_name, $applicant->last_name]))) : null,
                'Application ref no.' => $applicant->application_no,
                'Course' => $applicant->course->creation->course->name ?? null,
                'Intake' => $applicant->course->semester->name ?? null,
                'Declined on' => $declinedAt ? \Illuminate\Support\Carbon::parse($declinedAt)->format('j M Y, H:i') : null,
                'Reason given' => $this->details['reason'] ?? null,
            ], fn ($v) => $v !== null && $v !== ''),
        ]);
    }

    /** As kept in admission_notifications: the embedded logo points at the hosted file. */
    public function renderForRecord(): string
    {
        $html = $this->render();
        $logo = Option::where('category', 'SITE_SETTINGS')->where('name', 'site_logo')->value('value');

        return (string) preg_replace('#src="data:image/[^"]+"#', 'src="'.($logo ? url('storage/'.$logo) : '').'"', $html);
    }
}
