<?php

namespace Tests\Feature;

use App\Models\Applicant;
use App\Models\StudentConversionLog;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Conversion log page on the admission portal shell: latest batch summary +
 * jobs table (chain order, status pills, stored exceptions behind "View error"),
 * earlier runs in an accordion, and the empty state before any conversion.
 */
class AdmissionConversionLogPortalTest extends TestCase
{
    use DatabaseTransactions;

    private function staff(): User
    {
        return User::where('email', 'sakib@lcc.ac.uk')->first() ?? User::where('active', 1)->first();
    }

    public function test_a_stopped_run_shows_the_failed_job_its_error_and_the_cancelled_rest(): void
    {
        // A stopped run of its own (inside the test transaction): step 1 done, step 2 failed, the rest cancelled.
        $applicant = Applicant::whereNotNull('submission_date')->orderBy('id', 'desc')->firstOrFail();
        $batch = (string) \Illuminate\Support\Str::uuid();
        foreach (StudentConversionLog::JOB_SEQUENCE as $i => $jobClass) {
            $row = StudentConversionLog::create([
                'applicant_id' => $applicant->id, 'batch_id' => $batch, 'job_class' => $jobClass, 'job_name' => StudentConversionLog::jobLabel($jobClass),
                'status' => $i === 0 ? 'completed' : ($i === 1 ? 'failed' : 'cancelled'),
                'attempts' => $i < 2 ? 1 : 0,
                'message' => $i === 0 ? 'Completed successfully.' : ($i === 1 ? 'Failed: S3 said no' : 'Skipped: the conversion halted.'),
                'exception' => $i === 1 ? "RuntimeException: S3 said no\n#0 stack" : null,
                'started_at' => $i < 2 ? now() : null, 'finished_at' => now(), 'created_by' => 1,
            ]);
            if ($i === 1) {
                $failed = $row;
            }
        }

        $html = $this->actingAs($this->staff())->get(route('admission.conversion.log', $failed->applicant_id))->assertOk()->getContent();

        $this->assertStringContainsString('adp-body', $html);
        $this->assertStringContainsString('data-adp-convlog', $html);
        $this->assertStringContainsString('Conversion stopped', $html);
        $this->assertStringContainsString('Batch <span class="adp-mono" title="'.$failed->batch_id.'">'.substr($failed->batch_id, -8).'</span>', $html);
        $this->assertStringContainsString('data-adp-error="'.$failed->id.'"', $html);
        $this->assertStringContainsString('<template data-adp-exception="'.$failed->id.'">', $html);
        $this->assertStringContainsString('adp-pill--red" data-adp-cell="status"><i></i>Failed', $html);
        $this->assertStringContainsString('<i></i>Cancelled', $html);
        // Chain order: the first job of the sequence is row 01.
        $first = StudentConversionLog::jobLabel(StudentConversionLog::JOB_SEQUENCE[0]);
        $this->assertMatchesRegularExpression('/>01<\/td>\s*<td class="adp-convlog__job"><b>'.preg_quote($first, '/').'<\/b>/', $html);
        // Tools and the error dialog.
        foreach (['query-CL', 'status-CL', 'tabulator-print-CL', 'tabulator-export-csv-CL', 'tabulator-export-xlsx-CL', 'viewConversionErrorModal', 'conversionErrorContent'] as $id) {
            $this->assertStringContainsString('id="'.$id.'"', $html, $id);
        }
        // The section tab is current and the record header is the portal one.
        $this->assertMatchesRegularExpression('/class="on" aria-current="page">\s*<svg[^>]*>.*?<\/svg>\s*Conversion log/s', $html);
        $this->assertStringContainsString('id="changeStatusModal"', $html);
    }

    public function test_a_completed_run_lists_every_job_done_with_the_student_id(): void
    {
        $row = StudentConversionLog::whereNotNull('student_id')->where('status', 'completed')->orderBy('id', 'desc')->first();
        $this->assertNotNull($row, 'needs a completed conversion');
        $batchRows = StudentConversionLog::where('batch_id', $row->batch_id)->get();
        if ($batchRows->where('status', '!=', 'completed')->count() > 0) {
            $this->markTestSkipped('latest completed batch is mixed');
        }

        $html = $this->actingAs($this->staff())->get(route('admission.conversion.log', $row->applicant_id))->assertOk()->getContent();

        $this->assertStringContainsString($batchRows->count().' of '.$batchRows->count().' jobs done', $html);
        $this->assertStringContainsString('Student ID <a class="adp-mono" href="'.route('student.show', $row->student_id).'">', $html);
        // The "stopped" notice is rendered hidden (the live refresh may reveal it) when nothing failed.
        $this->assertStringContainsString('data-adp-stopped hidden', $html);
        $this->assertStringContainsString('<i></i>Completed</span>', $html);
        // every job of this run is listed (earlier runs of the same applicant sit in their own accordion)
        foreach ($batchRows as $batchRow) {
            $this->assertStringContainsString('data-adp-row data-id="'.$batchRow->id.'"', $html);
        }
    }

    public function test_an_applicant_without_a_conversion_sees_the_empty_state(): void
    {
        $converted = StudentConversionLog::select('applicant_id');
        $applicant = Applicant::whereNotNull('submission_date')->whereNotIn('id', $converted)->orderBy('id', 'desc')->first();
        $this->assertNotNull($applicant);

        $html = $this->actingAs($this->staff())->get(route('admission.conversion.log', $applicant->id))->assertOk()->getContent();

        $this->assertStringContainsString('Not converted yet.', $html);
        $this->assertStringContainsString(count(StudentConversionLog::JOB_SEQUENCE).' queued jobs copy the applicant', $html);
        $this->assertStringNotContainsString('data-adp-convlog-table', $html);
    }
}
