<?php

namespace App\Support;

use App\Models\Applicant;
use App\Models\ApplicantContact;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Other applications that look like the same household or the same person, for the
 * warnings above the applicant details (admission.show). Only submitted applications
 * count (status 1 "Submitting" drafts are left out, as on the admission list), and
 * the applicant itself never matches.
 *
 *  - Same address: address lines 1 + 2 and the postcode, compared with case, spaces
 *    and punctuation ignored, so "189 / SHAFTESBURY AVENUE" and "189 Shaftesbury
 *    Avenue" with "wc2h8tj" are the same place. No postcode, no address check.
 *  - Same email and date of birth: the login email (applicant_users) and the date of
 *    birth both equal.
 */
class ApplicantMatches
{
    /** @return array{address: Collection, identity: Collection, address_text: string} */
    public static function for(Applicant $applicant): array
    {
        return [
            'address' => self::sameAddress($applicant),
            'identity' => self::sameEmailAndDob($applicant),
            'address_text' => self::addressText($applicant),
        ];
    }

    public static function sameAddress(Applicant $applicant): Collection
    {
        $contact = ApplicantContact::where('applicant_id', $applicant->id)->orderByDesc('id')->first();
        $postcode = self::norm(optional($contact)->post_code);
        if ($postcode === '') {
            return collect();
        }
        $key = self::norm(optional($contact)->address_line_1.optional($contact)->address_line_2).$postcode;

        $candidates = ApplicantContact::query()
            ->whereRaw("UPPER(REPLACE(REPLACE(post_code, ' ', ''), '-', '')) = ?", [$postcode])
            ->where('applicant_id', '!=', $applicant->id)
            ->get(['applicant_id', 'address_line_1', 'address_line_2', 'post_code']);
        $ids = $candidates->filter(fn ($c) => self::norm($c->address_line_1.$c->address_line_2).self::norm($c->post_code) === $key)
            ->pluck('applicant_id')->unique()->values()->all();

        return self::applications($ids);
    }

    public static function sameEmailAndDob(Applicant $applicant): Collection
    {
        $email = strtolower(trim((string) DB::table('applicant_users')->where('id', $applicant->applicant_user_id)->value('email')));
        $dob = DB::table('applicants')->where('id', $applicant->id)->value('date_of_birth');
        if ($email === '' || empty($dob)) {
            return collect();
        }
        $ids = DB::table('applicants')
            ->join('applicant_users', 'applicant_users.id', '=', 'applicants.applicant_user_id')
            ->whereRaw('LOWER(TRIM(applicant_users.email)) = ?', [$email])
            ->where('applicants.date_of_birth', $dob)
            ->where('applicants.id', '!=', $applicant->id)
            ->pluck('applicants.id')->all();

        return self::applications($ids);
    }

    /** The matched applications, newest first, ready for the notice. */
    private static function applications(array $ids): Collection
    {
        if (!$ids) {
            return collect();
        }

        return Applicant::with('status')->whereIn('id', $ids)->where('status_id', '>', 1)
            ->orderByDesc('submission_date')->orderByDesc('id')
            ->get(['id', 'application_no', 'title_id', 'first_name', 'last_name', 'status_id', 'submission_date', 'applicant_user_id']);
    }

    public static function addressText(Applicant $applicant): string
    {
        $c = ApplicantContact::where('applicant_id', $applicant->id)->orderByDesc('id')->first();
        if (!$c) {
            return '';
        }

        return implode(', ', array_filter(array_map(fn ($v) => trim((string) $v), [$c->address_line_1, $c->address_line_2, $c->city, $c->post_code]), fn ($v) => $v !== ''));
    }

    private static function norm(?string $value): string
    {
        return strtoupper((string) preg_replace('/[^A-Za-z0-9]+/', '', (string) $value));
    }
}
