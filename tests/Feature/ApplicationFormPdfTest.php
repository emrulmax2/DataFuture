<?php

namespace Tests\Feature;

use App\Http\Controllers\ApplicantProfilePrintController;
use App\Models\Applicant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

/**
 * /applicantprofilepdf/{id} on the "Application_<ref>_<name>" design (27 Oct 2026):
 * the eight numbered sections, UK phone spacing, addresses out of capitals.
 */
class ApplicationFormPdfTest extends TestCase
{
    use DatabaseTransactions;

    private const VIEW = 'pages.students.admission.pdf.application-form';

    private function applicant(): Applicant
    {
        $applicant = Applicant::whereNull('deleted_at')->whereNotNull('submission_date')
            ->whereHas('contact')->whereHas('course')->whereHas('other')->orderByDesc('id')->first();
        $this->assertNotNull($applicant);
        DB::table('applicant_contacts')->where('applicant_id', $applicant->id)->update([
            'mobile' => '07000000000', 'address_line_1' => '189', 'address_line_2' => 'LODGE ROAD',
            'city' => 'WRITTLE', 'state' => 'ENGLAND', 'post_code' => 'CM1 3JB', 'country' => 'UNITED KINGDOM',
        ]);

        return $applicant;
    }

    /** The data the controller hands the view. */
    private function formData(int $id): array
    {
        $data = [];
        View::composer(self::VIEW, function ($view) use (&$data) { $data = $view->getData(); });
        [$pdf] = app(ApplicantProfilePrintController::class)->buildPdf($id);
        $this->assertStringStartsWith('%PDF', $pdf->output());

        return $data;
    }

    public function test_the_form_uses_the_new_design(): void
    {
        $applicant = $this->applicant();

        $data = $this->formData((int) $applicant->id);
        $html = view(self::VIEW, $data)->render();

        foreach (['Personal details', 'Contact details', 'Next of kin', 'Proposed course', 'Educational qualification', 'Employment history', 'Residency status &amp; criminal convictions', 'Others'] as $i => $title) {
            $this->assertStringContainsString('<span class="n">0'.($i + 1).'</span>'.$title, $html);
        }
        $this->assertStringContainsString('UK/EU Student Application', $html);
        $this->assertStringContainsString((string) $applicant->application_no, $html);
        $this->assertStringContainsString('lcc-crest.png', $html);
    }

    public function test_phones_are_spaced_and_addresses_leave_capitals(): void
    {
        $applicant = $this->applicant();

        $data = $this->formData((int) $applicant->id);

        $this->assertSame('07000 000000', $data['contact']['mobile']);
        $this->assertSame('189 Lodge Road, Writtle, England, CM1 3JB, United Kingdom', $data['contact']['address']);
    }

    public function test_staff_download_it_as_a_pdf(): void
    {
        $applicant = $this->applicant();

        $response = $this->actingAs(User::find(7))->get(route('applicantprofile.print', $applicant->id));

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
        $this->assertStringContainsString('Application_of_', (string) $response->headers->get('content-disposition'));
    }
}
