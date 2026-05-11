<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StudentProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $student = $this;

        return [

            // ── Personal Details ──────────────────────────────────────────────
            'personal_details' => [
                'title'           => $student->title->name ?? '',
                'first_name'      => $student->first_name,
                'last_name'       => $student->last_name,
                'full_name'       => ($student->title->name ?? '') . ' ' . $student->first_name . ' ' . $student->last_name,
                'date_of_birth'   => $student->date_of_birth ?? '',
                'sex_identifier'  => $student->sexid->name ?? '',
                'nationality'     => $student->nation->name ?? '',
                'country_of_birth'=> $student->country->name ?? '',
                'ethnicity'       => $student->other->ethnicity->name ?? '',
                'care_leaver'     => $student->other->leaver->name ?? '',
            ],

            // ── Other Personal Information ────────────────────────────────────
            'other_personal_info' => [
                'sexual_orientation'  => $student->other->sexori->name ?? '',
                'gender_identity'     => $student->other->gender->name ?? '',
                'religion_or_belief'  => $student->other->religion->name ?? '',
                'disability_status'   => (bool) ($student->other->disability_status ?? false),
                'disabilities'        => $student->disability->map(fn ($d) => $d->disabilities->name ?? '')->values(),
                'disability_allowance_claimed' => (bool) ($student->other->disabilty_allowance ?? false),
            ],

            // ── Residency Status & Criminal Convictions ───────────────────────
            'residency_and_criminal' => [
                'residency_status'           => optional(optional($student->residency)->residencyStatus)->name ?? '',
                'criminal_conviction'        => isset($student->criminalConviction->have_you_been_convicted)
                                                    ? (bool) $student->criminalConviction->have_you_been_convicted
                                                    : null,
                'conviction_details'         => $student->criminalConviction->criminal_conviction_details ?? '',
            ],

            // ── Other Identifications ─────────────────────────────────────────
            'identifications' => [
                'application_no'    => $student->application_no ?? '',
                'submission_date'   => $student->submission_date ?? '',
                'ssn_no'            => $student->ssn_no ?? '',
                'uhn_no'            => $student->uhn_no ?? '',
                'registration_no'   => $student->registration_no ?? '',
                'df_sid_number'     => $student->df_sid_number ?? '',
                'study_mode'        => $student->other->mode->name ?? '',
            ],

            // ── Contact Details ───────────────────────────────────────────────
            'contact_details' => [
                'login_email'                       => $student->users->email ?? '',
                'personal_email'                    => $student->contact->personal_email ?? '',
                'personal_email_verified'           => (bool) ($student->contact->personal_email_verification ?? false),
                'institutional_email'               => $student->contact->institutional_email ?? '',
                'home_phone'                        => $student->contact->home ?? '',
                'mobile'                            => $student->contact->mobile ?? '',
                'mobile_verified'                   => (bool) ($student->contact->mobile_verification ?? false),

                'term_time_address' => [
                    'address_line_1' => $student->contact->termaddress->address_line_1 ?? '',
                    'address_line_2' => $student->contact->termaddress->address_line_2 ?? '',
                    'city'           => $student->contact->termaddress->city ?? '',
                    'state'          => $student->contact->termaddress->state ?? '',
                    'post_code'      => $student->contact->termaddress->post_code ?? '',
                    'country'        => $student->contact->termaddress->country ?? '',
                    'polar4_quantile'=> $student->contact->termaddress->polar_4_quantile ?? '',
                ],
                'term_time_accommodation_type' => $student->contact->ttacom->name ?? '',
                'term_time_post_code'          => $student->contact->term_time_post_code ?? '',

                'permanent_address' => [
                    'address_line_1' => $student->contact->permaddress->address_line_1 ?? '',
                    'address_line_2' => $student->contact->permaddress->address_line_2 ?? '',
                    'city'           => $student->contact->permaddress->city ?? '',
                    'state'          => $student->contact->permaddress->state ?? '',
                    'post_code'      => $student->contact->permaddress->post_code ?? '',
                    'country'        => $student->contact->permaddress->country ?? '',
                    'polar4_quantile'=> $student->contact->permaddress->polar_4_quantile ?? '',
                ],
                'permanent_country_code' => $student->contact->pcountry->name ?? '',
                'permanent_post_code'    => $student->contact->permanent_post_code ?? '',
            ],

            // ── Next of Kin ───────────────────────────────────────────────────
            'next_of_kin' => [
                'name'     => $student->kin->name ?? '',
                'relation' => $student->kin->relation->name ?? '',
                'mobile'   => $student->kin->mobile ?? '',
                'email'    => $student->kin->email ?? '',
                'address'  => [
                    'address_line_1' => $student->kin->address->address_line_1 ?? '',
                    'address_line_2' => $student->kin->address->address_line_2 ?? '',
                    'city'           => $student->kin->address->city ?? '',
                    'state'          => $student->kin->address->state ?? '',
                    'post_code'      => $student->kin->address->post_code ?? '',
                    'country'        => $student->kin->address->country ?? '',
                ],
            ],

            // ── Educational Qualification ─────────────────────────────────────
            'education' => [
                'has_formal_qualification' => (bool) ($student->other->is_education_qualification ?? false),
            ],

            // ── Employment History ────────────────────────────────────────────
            'employment' => [
                'employment_status' => $student->other->employment_status ?? '',
            ],

            // ── Communication Consent & Referral ─────────────────────────────
            'consent_and_referral' => [
                'consents'      => $student->consents->map(fn ($c) => [
                    'id'          => $c->consent_id,
                    'name'        => $c->consent->name ?? '',
                    'description' => $c->consent->description ?? '',
                ])->values(),
                'referral' => $this->when(
                    isset($student->referral_code)
                    && !empty($student->referral_code)
                    && isset($student->is_referral_varified)
                    && $student->is_referral_varified == 1,
                    fn () => $this->resolveReferral($student)
                ),
            ],

            // ── Status & Photo ────────────────────────────────────────────────
            'status'    => $student->status->name ?? '',
            'photo_url' => $student->photo_url ?? '',
            'registration_no' => $student->registration_no ?? '',
            'full_name'=> $student->full_name ?? '',
            'course_detail' => ($student->crel->creation->course->name ?? '') . ' - ' . ($student->crel->propose->semester->name ?? ''),
            "course" => $student->crel->creation->course->name ?? '',
            "intake_semester" => $student->crel->propose->semester->name ?? '',
            'dashboard_url' => route('api.user.dashboard'),
            

        ];
    }

    private function resolveReferral($student): array
    {
        $referral = $student->referralData;  // pre-loaded in controller
        if (!$referral) {
            return [];
        }

        $referrerInfo = [];
        if ($referral->type === 'Student') {
            $referrerInfo = [
                'name'   => ($referral->student->first_name ?? '') . ' ' . ($referral->student->last_name ?? ''),
                'email'  => $referral->student->users->email ?? '',
                'mobile' => $referral->student->contact->mobile ?? '',
            ];
        } elseif ($referral->type === 'Agent') {
            $referrerInfo = ['name' => 'N/A'];
        } else {
            $referrerInfo = [
                'name'  => $referral->user->name ?? '',
                'email' => $referral->user->email ?? '',
            ];
        }

        return [
            'code'     => $referral->code ?? '',
            'type'     => $referral->type ?? '',
            'referrer' => $referrerInfo,
        ];
    }
}
