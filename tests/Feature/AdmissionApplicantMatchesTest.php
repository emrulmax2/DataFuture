<?php

namespace Tests\Feature;

use App\Models\Applicant;
use App\Models\ApplicantContact;
use App\Models\User;
use App\Support\ApplicantMatches;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Warnings above the applicant details: other applications at the same address, and
 * other applications with the same email + date of birth; plus Login as applicant
 * only for user 1.
 */
class AdmissionApplicantMatchesTest extends TestCase
{
    use DatabaseTransactions;

    /** Two submitted applicants with contacts, neither matching anything yet. */
    private function pair(): array
    {
        $list = Applicant::whereNotNull('submission_date')->where('status_id', '>', 1)->whereHas('users')->whereHas('contact')->orderByDesc('id')->limit(40)->get()
            ->filter(fn ($a) => ApplicantMatches::sameAddress($a)->isEmpty() && ApplicantMatches::sameEmailAndDob($a)->isEmpty())->values();
        $this->assertGreaterThanOrEqual(2, $list->count());

        return [$list[0], $list[1]];
    }

    private function show(Applicant $applicant, int $userId = 7): string
    {
        return $this->actingAs(User::find($userId))->get(route('admission.show', $applicant->id))->assertOk()->getContent();
    }

    public function test_same_address_ignores_case_spacing_and_how_the_lines_are_split(): void
    {
        [$a, $b] = $this->pair();
        ApplicantContact::where('applicant_id', $a->id)->update(['address_line_1' => '189', 'address_line_2' => 'SHAFTESBURY AVENUE', 'post_code' => 'WC2H 8TJ']);
        ApplicantContact::where('applicant_id', $b->id)->update(['address_line_1' => '189 Shaftesbury Avenue', 'address_line_2' => null, 'post_code' => 'wc2h8tj']);

        $this->assertSame([$b->id], ApplicantMatches::sameAddress($a->fresh())->pluck('id')->all());
        $html = $this->show($a);
        $this->assertStringContainsString('data-adp-match="address" data-count="1"', $html);
        $this->assertStringContainsString('1 other application has the same address', $html);
        $this->assertStringContainsString(route('admission.show', $b->id), $html);
        $this->assertLessThan(strpos($html, 'Personal details'), strpos($html, 'data-adp-match="address"'), 'the notice sits before the applicant details');

        // a different house on the same postcode is not the same address
        ApplicantContact::where('applicant_id', $b->id)->update(['address_line_1' => '191 Shaftesbury Avenue']);
        $this->assertTrue(ApplicantMatches::sameAddress($a->fresh())->isEmpty());
    }

    public function test_same_email_and_date_of_birth_both_have_to_match(): void
    {
        [$a, $b] = $this->pair();
        $dob = DB::table('applicants')->where('id', $a->id)->value('date_of_birth');
        DB::table('applicants')->where('id', $b->id)->update(['applicant_user_id' => $a->applicant_user_id, 'date_of_birth' => $dob]);

        $html = $this->show($a);
        $this->assertStringContainsString('data-adp-match="identity" data-count="1"', $html);
        $this->assertStringContainsString('1 other application has the same email and date of birth', $html);

        // same email, different date of birth: no match
        DB::table('applicants')->where('id', $b->id)->update(['date_of_birth' => '1901-01-01']);
        $this->assertTrue(ApplicantMatches::sameEmailAndDob($a->fresh())->isEmpty());
    }

    public function test_drafts_do_not_count_and_the_count_is_shown(): void
    {
        [$a, $b] = $this->pair();
        ApplicantContact::where('applicant_id', $b->id)->update(ApplicantContact::where('applicant_id', $a->id)->first()->only(['address_line_1', 'address_line_2', 'post_code']));
        $this->assertSame(1, ApplicantMatches::sameAddress($a->fresh())->count());

        DB::table('applicants')->where('id', $b->id)->update(['status_id' => 1]);
        $this->assertSame(0, ApplicantMatches::sameAddress($a->fresh())->count());
        $this->assertStringNotContainsString('data-adp-match=', $this->show($a));
    }

    public function test_login_as_applicant_shows_only_for_user_1(): void
    {
        [$a] = $this->pair();
        $this->assertStringContainsString('Login as applicant', $this->show($a, 1));
        $this->assertStringNotContainsString('Login as applicant', $this->show($a, 7));
    }
}
