<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Models\Semester;
use App\Models\TermDeclaration;
use App\Services\Reports\ApplicationAnalysis;
use App\Support\ReportPdfLogo;
use Illuminate\Http\Request;

/**
 * Admission Report (/reports/applicant-analysis): accepted against target
 * and who accepted, for one intake semester. The figures come from
 * App\Services\Reports\ApplicationAnalysis; the screen and the PDF render
 * the same view (pages/reports/application-analysis/report.blade.php).
 */
class ApplicantAnalysisReportController extends Controller
{
    public function index(){
        return view('pages.reports.application-analysis.index', [
            'title' => 'Application Analysis Report - London Churchill College',
            // Report is reached from the admission list, so it wears the
            // redesigned admission shell (header + breadcrumb strip).
            'layout' => 'admission-top-menu',
            'breadcrumbs' => [
                ['label' => 'Students Admission', 'href' => route('admission')],
                ['label' => 'Application Analysis Report', 'href' => 'javascript:void(0);']
            ],
            'terms' => TermDeclaration::orderBy('id', 'DESC')->get(),
            'semester' => Semester::orderBy('id', 'DESC')->get(),
        ]);
    }

    public function generateReport(Request $request, ApplicationAnalysis $analysis){
        $semester_id = (int) ($request->ap_an_semester_id ?? 0);

        $html = view('pages.reports.application-analysis.report', $this->viewData($analysis, $semester_id) + ['medium' => 'screen'])->render();

        return response()->json(['htm' => $html], 200);
    }

    public function printReport(ApplicationAnalysis $analysis, $semester_id = 0){
        $data = $this->viewData($analysis, (int) $semester_id);
        $fileName = 'Admission_Report_'.preg_replace('/[^A-Za-z0-9]+/', '_', trim($data['report']['semester'] !== '' ? $data['report']['semester'] : 'Intake')).'.pdf';

        return $this->pdf($data)->download($fileName);
    }

    /**
     * The PDF, rendered twice: DomPDF cannot print the page total in CSS
     * ("Page 1 of 2"), so the first pass counts the pages for the second.
     */
    public function pdf(array $data)
    {
        $data['logo'] = ReportPdfLogo::dataUri();
        $make = function (int $pages) use ($data) {
            return app('dompdf.wrapper')
                ->loadView('pages.reports.application-analysis.pdf', $data + ['pages' => $pages])
                ->setPaper('a4', 'landscape')
                ->setWarnings(false);
        };

        $first = $make(0);
        $first->render();

        return $make((int) $first->getDomPDF()->getCanvas()->get_page_count());
    }

    protected function viewData(ApplicationAnalysis $analysis, int $semester_id): array
    {
        $user = auth()->user();
        $author = (isset($user->employee->full_name) && !empty($user->employee->full_name) ? $user->employee->full_name : (string) optional($user)->name);

        return [
            'report' => $analysis->build($semester_id),
            'author' => $author,
            'date' => now()->format('j M Y'),
        ];
    }

    /** Applicants whose entry route is unknown (the Unknown link in Entry route). */
    public function unknownEntryList(Request $request){
        $applicant_ids = (isset($request->applicant_ids) && !empty($request->applicant_ids) ? explode(',', str_replace(' ', '', $request->applicant_ids)) : [0]);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = Applicant::orderByRaw(implode(',', $sorts))->whereNotNull('submission_date')->whereIn('id', $applicant_ids);


        $total_rows = $query->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 10));
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';

        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $Query = $query->skip($offset)
               ->take($limit)
               ->get();

        $data = array();

        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'application_no' => (empty($list->application_no) ? $list->id : $list->application_no),
                    'first_name' => ucfirst($list->first_name),
                    'last_name' => ucfirst($list->last_name),
                    'full_name' => ucfirst($list->first_name)." ".ucfirst($list->last_name),

                    'date_of_birth'=> $list->date_of_birth,
                    'course'=> (isset($list->course->creation->course->name) ? $list->course->creation->course->name : ''),
                    'semester'=> (isset($list->course->semester->name) ? $list->course->semester->name : ''),
                    'full_time'=> (isset($list->course->full_time) ? "Yes": "No"),
                    'gender'=> (isset($list->sexid->name) && !empty($list->sexid->name) ? $list->sexid->name : ''),
                    'status_id'=> (isset($list->status->name) ? $list->status->name : ''),
                    'url' => route('admission.show', $list->id),
                    'photo_url' => $list->photo_url
                ];
                $i++;
            endforeach;
        endif;

        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }
}
