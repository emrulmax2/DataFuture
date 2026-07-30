<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Models\ApplicantOtherDetail;
use App\Models\Option;
use Illuminate\Support\Facades\Storage;
use App\Models\CourseCreation;
use App\Models\CourseCreationVenue;
use App\Models\Semester;
use App\Models\TermDeclaration;
use App\Models\User;
use App\Support\CourseTheme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ApplicantAnalysisReportController extends Controller
{
    /** Masthead for the PDF. Fetched over the wire, hence isRemoteEnabled. */
    const PDF_LOGO = 'https://sms.londonchurchillcollege.ac.uk/sms_new_copy_2/uploads/LCC_LOGO_01_263_100.png';

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

    public function generateReport(Request $request){
        $semester_id = (isset($request->ap_an_semester_id) && !empty($request->ap_an_semester_id) ? $request->ap_an_semester_id : 0);
        $html = $this->getHtml($semester_id);
        
        return response()->json(['htm' => $html], 200);
    }

    public function printReport($semester_id = 0){
        $semesterNames = ($semester_id > 0 ? Semester::whereIn('id', [$semester_id])->pluck('name')->unique()->toArray() : []);
        $semesterLabel = (!empty($semesterNames) ? implode(', ', $semesterNames) : 'Undefined');
        $user = User::find(auth()->user()->id);
        $author = (isset($user->employee->full_name) && !empty($user->employee->full_name) ? $user->employee->full_name : $user->name);

        $report_title = 'Admission Report';

        $PDFHTML = '<html><head>';
            $PDFHTML .= '<title>'.e($report_title).'</title>';
            $PDFHTML .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
            $PDFHTML .= '<style>'.$this->pdfStylesheet().'</style>';
        $PDFHTML .= '</head><body>';

            /* Running header. Fixed elements are placed against the paper, so
             * the @page margins reserve the band they sit in. */
            $PDFHTML .= '<header>';
                $PDFHTML .= '<table class="rhead">';
                    $PDFHTML .= '<tr>';
                        $PDFHTML .= '<td class="rhead__logo"><img src="'.$this->pdfLogoSrc().'" alt="London Churchill College"/></td>';
                        $PDFHTML .= '<td class="rhead__id">';
                            $PDFHTML .= '<div class="rhead__title">'.e($report_title).'</div>';
                            $PDFHTML .= '<div class="rhead__kicker">APPLICATION ANALYSIS</div>';
                        $PDFHTML .= '</td>';
                        $PDFHTML .= '<td class="rhead__meta">';
                            $PDFHTML .= '<div class="rhead__sem">Semester '.e($semesterLabel).'</div>';
                            $PDFHTML .= '<div class="rhead__by">By '.e($author).' &middot; '.date('jS M, Y').'</div>';
                        $PDFHTML .= '</td>';
                    $PDFHTML .= '</tr>';
                $PDFHTML .= '</table>';
            $PDFHTML .= '</header>';

            $PDFHTML .= '<footer>';
                $PDFHTML .= '<table class="rfoot">';
                    $PDFHTML .= '<tr>';
                        $PDFHTML .= '<td>London Churchill College &middot; '.e($report_title).'</td>';
                        $PDFHTML .= '<td class="text-right">Confidential &mdash; internal use only</td>';
                    $PDFHTML .= '</tr>';
                $PDFHTML .= '</table>';
            $PDFHTML .= '</footer>';

            $PDFHTML .= $this->getPdfHtml($semester_id, $semesterLabel);

        $PDFHTML .= '</body></html>';

        $fileName = str_replace(' ', '_', $report_title).'.pdf';
        $pdf = PDF::loadHTML($PDFHTML)->setOption(['isRemoteEnabled' => true])
            ->setPaper('a4', 'portrait')
            ->setWarnings(false);

        return $pdf->download($fileName);
    }

    /**
     * Masthead image, as a base64 data URI built from the logo uploaded in
     * Site Settings.
     *
     * Embedding beats linking here: DomPDF would otherwise fetch the file over
     * HTTP on every download, and a blocked or slow request leaves the report
     * with a broken image where the crest should be.
     */
    protected function pdfLogoSrc(){
        $logos = Option::where('category', 'SITE_SETTINGS')
            ->whereIn('name', ['site_logo', 'site_dark_logo'])
            ->pluck('value', 'name')
            ->toArray();

        foreach(['site_logo', 'site_dark_logo'] as $key):
            $value = (isset($logos[$key]) && !empty($logos[$key]) ? $logos[$key] : null);
            if(empty($value) || !Storage::disk('local')->exists('public/'.$value)):
                continue;
            endif;

            $path = Storage::disk('local')->path('public/'.$value);

            /* Keyed on the modification time so re-uploading the logo in Site
             * Settings takes effect without anyone clearing the cache. */
            return cache()->rememberForever('admission_report_pdf_logo_'.md5($value.'|'.filemtime($path)), function() use ($path){
                return $this->pdfDataUri($path);
            });
        endforeach;

        return self::PDF_LOGO;
    }

    /**
     * Read an image off disk as a data URI, shrinking it first when it is far
     * larger than the 24pt slot it prints in — the upload is a full-resolution
     * PNG and every byte of it would otherwise ride along in the PDF.
     */
    protected function pdfDataUri($path, $maxHeight = 120){
        $raw = @file_get_contents($path);
        if($raw === false):
            return self::PDF_LOGO;
        endif;

        $info = @getimagesize($path);
        $mime = (!empty($info['mime']) ? $info['mime'] : 'image/png');

        if($mime === 'image/png' && !empty($info) && $info[1] > $maxHeight && extension_loaded('gd')):
            $smaller = $this->pdfDownscalePng($path, $info[0], $info[1], $maxHeight);
            if($smaller !== null):
                $raw = $smaller;
            endif;
        endif;

        return 'data:'.$mime.';base64,'.base64_encode($raw);
    }

    /**
     * Resample a PNG to `$maxHeight`, preserving the alpha channel the crest
     * is cut out with. Returns null when GD cannot read the file, so callers
     * fall back to the original bytes.
     */
    protected function pdfDownscalePng($path, $width, $height, $maxHeight){
        $source = @imagecreatefrompng($path);
        if($source === false):
            return null;
        endif;

        $targetWidth = (int) max(1, round($width * ($maxHeight / $height)));
        $canvas = imagecreatetruecolor($targetWidth, $maxHeight);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        imagefill($canvas, 0, 0, imagecolorallocatealpha($canvas, 0, 0, 0, 127));
        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $targetWidth, $maxHeight, $width, $height);

        ob_start();
        imagepng($canvas, null, 9);
        $data = ob_get_clean();

        imagedestroy($canvas);
        imagedestroy($source);

        return ($data !== '' ? $data : null);
    }

    /**
     * Print stylesheet.
     *
     * Everything here has to survive DomPDF 2.0.7, which has no flexbox and
     * no grid — the design's flex rows are all tables below — and no
     * `text-transform`, so the small-caps labels are upper-cased in PHP.
     * Measurements are in points, matching the design.
     */
    protected function pdfStylesheet(){
        return '
            @page { margin: 84pt 40pt 58pt; }

            body { color: #0F252D; font-family: "public sans", sans-serif; font-size: 8.5pt; line-height: 1.35; margin: 0; }
            table { border-collapse: collapse; width: 100%; }
            th, td { text-align: left; vertical-align: top; }
            .text-right { text-align: right; }
            .num { font-variant-numeric: tabular-nums; text-align: right; }

            /* --- running header / footer ---
             * DomPDF honours `bottom` on a fixed element but not `top`: with
             * `top` the header rendered over the first block of content. The
             * negative margin lifts it out of the flow into the page margin
             * instead, and it repeats there on every page. */
            header { left: 0; margin-top: -54pt; position: fixed; right: 0; }
            .rhead { border-bottom: 2pt solid #0F252D; padding-bottom: 6pt; }
            .rhead td { vertical-align: middle; }
            .rhead__logo { width: 96pt; }
            .rhead__logo img { height: 24pt; width: auto; }
            .rhead__id { border-left: 1pt solid #d8dfe1; padding-left: 8pt; }
            .rhead__title { color: #0F252D; font-size: 10.5pt; font-weight: bold; }
            .rhead__kicker { color: #8b98a0; font-size: 7pt; font-weight: bold; letter-spacing: 0.08em; }
            .rhead__meta { text-align: right; }
            .rhead__sem { color: #0B6B66; font-size: 8.5pt; font-weight: bold; }
            .rhead__by { color: #8b98a0; font-size: 7pt; }

            /* `left`/`bottom` on a fixed element are resolved against the page
             * content box, which already carries the @page margins — `left:40pt`
             * indented it a second time and pushed it off the right edge. Zero
             * sides plus a negative margin drops it into the bottom margin,
             * squared up with the header. */
            footer { bottom: 0; color: #a2adb4; left: 0; margin-bottom: -32pt; position: fixed; right: 0; }
            .rfoot { border-top: 1pt solid #e0e6e8; padding-top: 5pt; }
            .rfoot td { color: #a2adb4; font-size: 7.5pt; }

            /* --- title block --- */
            .lede { margin-bottom: 14pt; }
            .lede td { vertical-align: top; }
            .lede__eyebrow { color: #a2adb4; font-size: 7.5pt; font-weight: bold; letter-spacing: 0.12em; }
            .lede__title { color: #0F252D; font-family: Georgia, "Times New Roman", serif; font-size: 21pt; font-weight: bold; margin: 2pt 0 3pt; }
            .lede__sub { color: #5a6b74; font-size: 9pt; }
            .lede__stat { width: 150pt; }
            .conv { background: #E5F2F0; border-radius: 8pt; padding: 9pt 14pt; text-align: right; }
            .conv__label { color: #0B6B66; font-size: 7pt; font-weight: bold; letter-spacing: 0.08em; }
            .conv__value { color: #0B6B66; font-size: 19pt; font-weight: bold; line-height: 1.1; }
            .conv__note { color: #4d8f8b; font-size: 7.5pt; }

            /* --- kpi strip --- */
            .kpis { margin-bottom: 14pt; }
            .kpis td { padding-right: 6pt; }
            .kpis td.is-last { padding-right: 0; }
            /* Fixed height so a label that wraps to two lines ("Total
             * Applications") does not leave its tile taller than the rest. */
            .kpi { border: 1pt solid #e0e6e8; border-radius: 7pt; height: 46pt; padding: 8pt 9pt; }
            .kpi__label { color: #7d8c96; font-size: 6.8pt; font-weight: bold; letter-spacing: 0.05em; }
            .kpi__dot { display: inline-block; height: 5pt; margin-right: 4pt; width: 5pt; }
            .kpi__value { color: #0F252D; font-size: 18pt; font-weight: bold; line-height: 1.1; margin-top: 4pt; }

            /* --- section heading --- */
            .sec { margin: 0 0 9pt; }
            .sec td { vertical-align: middle; }
            .sec__title { color: #0F252D; font-size: 12pt; font-weight: bold; white-space: nowrap; width: 1pt; }
            .sec__rule { padding: 0 8pt; }
            .sec__rule div { background: #e0e6e8; font-size: 0; height: 1pt; line-height: 0; }
            .sec__pill { white-space: nowrap; width: 1pt; }
            .sec__pill span { background: #eaf4ee; border-radius: 12pt; color: #1E6B4E; font-size: 8pt; font-weight: bold; padding: 3pt 10pt; }

            /* --- cards --- */
            .card { border: 1pt solid #e0e6e8; border-radius: 8pt; margin-bottom: 11pt; page-break-inside: avoid; }
            .card__head { border-bottom: 1pt solid #e0e6e8; }
            .card__head td { padding: 9pt 12pt; vertical-align: middle; }
            .card__bar { padding-right: 0 !important; width: 3pt; }
            .card__bar div { border-radius: 2pt; font-size: 0; height: 22pt; line-height: 0; width: 3pt; }
            .card__name { color: #0F252D; font-size: 10pt; font-weight: bold; }
            .card__venues { color: #5a6b74; font-size: 7.5pt; margin-top: 1pt; }
            .card__target { text-align: right; white-space: nowrap; width: 70pt; }
            .card__targetlabel { font-size: 6.8pt; font-weight: bold; letter-spacing: 0.06em; }
            .card__targetvalue { font-size: 13pt; font-weight: bold; line-height: 1.1; }

            /* --- data tables --- */
            .grid th { background: #f8fafa; color: #7d8c96; font-size: 6.6pt; font-weight: bold; letter-spacing: 0.05em; padding: 6pt; }
            .grid th.pad-l { padding-left: 12pt; }
            .grid th.pad-r { padding-right: 12pt; }
            .grid td { border-top: 1pt solid #f0f4f5; color: #3d5563; font-size: 8.5pt; padding: 6pt; vertical-align: middle; }
            .grid td.metric { color: #1c3346; font-weight: bold; padding-left: 12pt; }
            .grid td.pad-r { padding-right: 12pt; }
            .grid td.total { color: #0F252D; font-size: 9pt; font-weight: bold; }
            .grid td.void { color: #c6ced2; }
            .grid td.rowlabel { color: #7d8c96; font-size: 7.5pt; font-weight: bold; letter-spacing: 0.05em; }
            .grid td.venue { color: #1c3346; font-weight: bold; padding-left: 12pt; }
            .dot { display: inline-block; height: 5pt; margin-right: 5pt; width: 5pt; }

            /* --- demographics --- */
            .panel { border: 1pt solid #e0e6e8; border-radius: 8pt; margin-bottom: 11pt; padding: 12pt 14pt; page-break-inside: avoid; }
            .panel__title { color: #0F252D; font-size: 10pt; font-weight: bold; }
            .panel__head { margin-bottom: 8pt; }
            .panel__head td { vertical-align: middle; }
            .panel__meta { color: #a2adb4; font-size: 7.5pt; text-align: right; }
            .panel__pill { background: #f4f1fa; border-radius: 12pt; color: #6d4bb0; font-size: 7.5pt; font-weight: bold; padding: 2pt 9pt; }
            .panel__empty { color: #a2adb4; font-size: 8pt; font-style: italic; }

            .barrow { margin-bottom: 7pt; }
            .barrow__top { margin-bottom: 3pt; }
            .barrow__label { color: #3d5563; font-size: 8.5pt; font-weight: bold; }
            .barrow__value { color: #0F252D; font-size: 8.5pt; font-weight: bold; text-align: right; white-space: nowrap; }
            .barrow__pct { color: #a2adb4; font-size: 7.5pt; }
            .bar { background: #eef2f3; border-radius: 3pt; font-size: 0; height: 5pt; line-height: 0; }
            .bar div { border-radius: 3pt; font-size: 0; height: 5pt; line-height: 0; }

            .rank { margin-bottom: 5pt; }
            .rank td { vertical-align: middle; }
            .rank__label { color: #3d5563; font-size: 8.5pt; width: 120pt; }
            .rank__bar { padding-right: 9pt; }
            .rank__n { color: #0F252D; font-size: 8.5pt; font-weight: bold; text-align: right; width: 26pt; }
            .rank__pct { color: #a2adb4; font-size: 7.5pt; text-align: right; width: 36pt; }

            .page-break { page-break-before: always; }
            .empty { border: 1pt solid #e0e6e8; border-radius: 8pt; color: #a2adb4; padding: 30pt; text-align: center; }
        ';
    }

    /* ------------------------------------------------------------------ *
     * Report body — screen
     *
     * Injected into #applicantAnalysisReptWrap by
     * resources/js/applicant-analysis-report.js and dressed by the
     * `.adm-rpt-*` rules in resources/css/admission-redesign.css.
     *
     * The PDF download renders its own markup (getPdfHtml) instead of
     * sharing this: DomPDF understands neither flexbox nor grid, so the two
     * media deliberately do not share HTML.
     * ------------------------------------------------------------------ */

    public function getHtml($semester_id){
        $totalTarget = $this->getTotalApplicantTarget($semester_id);
        $basicAnalysis = $this->getApplicationCoreAnalysis($semester_id);
        $courseAnalysis = $this->getApplicationCourseAnalysis($semester_id);
        $offeredAnalysis = $this->getOfferedStudentsAnalysis($semester_id);
        $offeredCourseAnalysis = $this->getOfferedStudentsCourseAnalysis($semester_id);

        $no_of_applicants = (isset($offeredCourseAnalysis['no_of_applicants']) && !empty($offeredCourseAnalysis['no_of_applicants']) ? $offeredCourseAnalysis['no_of_applicants'] : 0);
        $offeredCourses = (isset($offeredCourseAnalysis['offeredCourses']) && !empty($offeredCourseAnalysis['offeredCourses']) ? $offeredCourseAnalysis['offeredCourses'] : []);
        $offeredPersonal = (isset($offeredCourseAnalysis['offeredPersonal']) && !empty($offeredCourseAnalysis['offeredPersonal']) ? $offeredCourseAnalysis['offeredPersonal'] : []);

        $totalApplications = ($basicAnalysis->count() > 0 ? $basicAnalysis->sum('TOTAL') : 0);
        $totalOffered = ($offeredAnalysis->count() > 0 ? $offeredAnalysis->sum('TOTAL') : 0);

        if($totalTarget < 1 && $totalApplications < 1 && empty($courseAnalysis)):
            return $this->reportEmptyState();
        endif;

        $html = '<div class="adm-rpt">';

            /* --- headline figures: the two totals, then one tile per status --- */
            $html .= '<div class="adm-rpt-kpis">';
                $html .= $this->reportKpi('Total Target', $totalTarget, '#2f6ea5');
                $html .= $this->reportKpi('Total Applications', $totalApplications, '#0b6b66');
                if($basicAnalysis->count() > 0):
                    foreach($basicAnalysis as $ba):
                        $html .= $this->reportKpi($ba->status_name, $ba->TOTAL, $this->reportStatusAccent($ba->status_name));
                    endforeach;
                endif;
            $html .= '</div>';

            /* --- analysis by course --- */
            if(!empty($courseAnalysis)):
                $html .= $this->reportSectionHead('Analysis By Course');
                foreach($courseAnalysis as $course):
                    $html .= $this->reportCourseCard($course);
                endforeach;
            endif;

            /* --- offered students --- */
            /* The per-status breakdown rides in the section heading beside the
             * total rather than on a line of its own. */
            $offeredChips = '';
            if($offeredAnalysis->count() > 0):
                $offeredChips .= '<div class="adm-rpt-chips">';
                    foreach($offeredAnalysis as $oa):
                        $offeredChips .= '<span class="adm-rpt-chip">';
                            $offeredChips .= '<i class="adm-rpt-chip__dot" style="background: '.$this->reportStatusAccent($oa->status_name).';"></i>';
                            $offeredChips .= e($this->reportLabel($oa->status_name));
                            $offeredChips .= '<b>'.$oa->TOTAL.'</b>';
                        $offeredChips .= '</span>';
                    endforeach;
                $offeredChips .= '</div>';
            endif;
            $html .= $this->reportSectionHead('Offered Students Analysis', 'Total Offered', $totalOffered, $offeredChips);
            if(!empty($offeredCourses)):
                foreach($offeredCourses as $course):
                    $html .= $this->reportOfferedRows($course);
                endforeach;
            endif;

            /* --- demographics of the offered cohort --- */
            if(!empty($offeredPersonal)):
                $html .= $this->reportDemographics($offeredPersonal, $no_of_applicants);
            endif;

        $html .= '</div>';

        return $html;
    }

    /**
     * One headline tile.
     */
    protected function reportKpi($label, $value, $accent){
        $html = '<div class="adm-rpt-kpi">';
            $html .= '<div class="adm-rpt-kpi__label">';
                $html .= '<i class="adm-rpt-kpi__dot" style="background: '.$accent.';"></i>';
                $html .= e($this->reportLabel($label));
            $html .= '</div>';
            $html .= '<div class="adm-rpt-kpi__value">'.($value > 0 ? $value : 0).'</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Section title with a hairline rule and an optional summary pill.
     *
     * `$trailingHtml` is markup dropped in after the rule, so it lands on the
     * right of the heading ahead of the pill — callers escape their own text.
     */
    protected function reportSectionHead($title, $pillLabel = '', $pillValue = '', $trailingHtml = ''){
        $html = '<div class="adm-rpt-sechead">';
            $html .= '<span class="adm-rpt-sechead__title">'.e($title).'</span>';
            $html .= '<i class="adm-rpt-sechead__rule"></i>';
            $html .= $trailingHtml;
            if($pillLabel !== ''):
                $html .= '<span class="adm-rpt-sechead__pill">'.e($pillLabel).'<b>'.($pillValue > 0 ? $pillValue : 0).'</b></span>';
            endif;
        $html .= '</div>';

        return $html;
    }

    /**
     * Target vs. applications for a single course, keyed by application status.
     */
    protected function reportCourseCard(array $course){
        $venues = (isset($course['venues']) && !empty($course['venues']) ? $course['venues'] : []);
        $applications = (isset($course['applications']) && !empty($course['applications']) ? $course['applications'] : []);
        $weekdays = (!empty($venues) ? array_sum(array_column($venues, 'weekdays')) : 0);
        $weekends = (!empty($venues) ? array_sum(array_column($venues, 'weekends')) : 0);

        $html = '<div class="adm-rpt-course" style="'.$this->reportCourseAccent($course).'">';
            $html .= '<div class="adm-rpt-course__head">';
                $html .= '<i class="adm-rpt-accentbar"></i>';
                $html .= '<div class="adm-rpt-course__id">';
                    $html .= '<div class="adm-rpt-course__name">'.e($this->reportLabel(isset($course['name']) ? $course['name'] : '')).'</div>';
                    if(!empty($venues)):
                        $html .= '<div class="adm-rpt-venue">'.$this->reportIcon('pin').e(implode(' · ', array_column($venues, 'name'))).'</div>';
                    endif;
                $html .= '</div>';
                $html .= $this->reportTargetChip($weekdays, $weekends);
            $html .= '</div>';

            $html .= '<div class="adm-rpt-tablewrap">';
                $html .= '<table class="adm-rpt-table">';
                    $html .= '<thead>';
                        $html .= '<tr>';
                            $html .= '<th>Metric</th>';
                            $html .= '<th class="adm-rpt-table__num">Weekdays</th>';
                            $html .= '<th class="adm-rpt-table__num">Evening / Weekend</th>';
                            $html .= '<th class="adm-rpt-table__num">Total</th>';
                            $html .= '<th class="adm-rpt-table__num">Mature Entry</th>';
                            $html .= '<th class="adm-rpt-table__num">Academic Entry</th>';
                        $html .= '</tr>';
                    $html .= '</thead>';
                    $html .= '<tbody>';
                        /* The header chip carries the target, so the per-venue
                         * split is only worth a row when it is a real split. */
                        if(count($venues) > 1):
                            foreach($venues as $venue):
                                $html .= '<tr>';
                                    $html .= $this->reportMetricCell('Target · '.e($venue['name']), '#8b98a0');
                                    $html .= '<td class="adm-rpt-table__num">'.$venue['weekdays'].'</td>';
                                    $html .= '<td class="adm-rpt-table__num">'.$venue['weekends'].'</td>';
                                    $html .= '<td class="adm-rpt-table__num adm-rpt-table__total">'.$venue['total'].'</td>';
                                    $html .= '<td class="adm-rpt-table__num adm-rpt-table__void">&ndash;</td>';
                                    $html .= '<td class="adm-rpt-table__num adm-rpt-table__void">&ndash;</td>';
                                $html .= '</tr>';
                            endforeach;
                        endif;

                        if(!empty($applications)):
                            $html .= '<tr>';
                                $html .= $this->reportMetricCell('Total Application', '#5a6b74');
                                $html .= '<td class="adm-rpt-table__num">'.array_sum(array_column($applications, 'WEEKDAYS')).'</td>';
                                $html .= '<td class="adm-rpt-table__num">'.array_sum(array_column($applications, 'WEEKENDS')).'</td>';
                                $html .= '<td class="adm-rpt-table__num adm-rpt-table__total">'.array_sum(array_column($applications, 'TOTAL')).'</td>';
                                $html .= '<td class="adm-rpt-table__num">'.array_sum(array_column($applications, 'MATURE')).'</td>';
                                $html .= '<td class="adm-rpt-table__num">'.array_sum(array_column($applications, 'ACADEMIC')).'</td>';
                            $html .= '</tr>';
                            foreach($applications as $row):
                                $html .= '<tr>';
                                    $html .= $this->reportMetricCell(e($this->reportLabel($row['STATUS_NAME'])), $this->reportStatusAccent($row['STATUS_NAME']));
                                    $html .= '<td class="adm-rpt-table__num">'.$row['WEEKDAYS'].'</td>';
                                    $html .= '<td class="adm-rpt-table__num">'.$row['WEEKENDS'].'</td>';
                                    $html .= '<td class="adm-rpt-table__num adm-rpt-table__total">'.$row['TOTAL'].'</td>';
                                    $html .= '<td class="adm-rpt-table__num">'.$row['MATURE'].'</td>';
                                    $html .= '<td class="adm-rpt-table__num">'.$row['ACADEMIC'].'</td>';
                                $html .= '</tr>';
                            endforeach;
                        else:
                            $html .= '<tr><td colspan="6" class="adm-rpt-table__empty">No applications recorded against this course.</td></tr>';
                        endif;
                    $html .= '</tbody>';
                $html .= '</table>';
            $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Target vs. offered for every venue of a course, one row card each.
     */
    protected function reportOfferedRows(array $course){
        $venues = (isset($course['venues']) && !empty($course['venues']) ? $course['venues'] : []);
        if(empty($venues)):
            return '';
        endif;

        $accent = $this->reportCourseAccent($course);
        $name = $this->reportLabel(isset($course['name']) ? $course['name'] : '');

        $html = '';
        foreach($venues as $venue):
            $target = ($venue['total_trget'] > 0 ? $venue['total_trget'] : 0);
            $offered = ($venue['total_offered'] > 0 ? $venue['total_offered'] : 0);
            $fill = ($target > 0 ? (int) round(($offered / $target) * 100) : 0);
            $fillAccent = $this->reportFillAccent($fill, $target);

            $html .= '<div class="adm-rpt-offer" style="'.$accent.'">';
                $html .= '<i class="adm-rpt-accentbar"></i>';
                $html .= '<div class="adm-rpt-offer__id">';
                    $html .= '<div class="adm-rpt-offer__name">'.e($name).'</div>';
                    $html .= '<div class="adm-rpt-venue">'.$this->reportIcon('pin').e($this->reportLabel($venue['name'])).'</div>';
                $html .= '</div>';
                $html .= $this->reportOfferStat('Target', $target, $venue['weekdays_trget'], $venue['weekends_trget'], false);
                $html .= $this->reportOfferStat('Offered', $offered, $venue['weekdays_offered'], $venue['weekends_offered'], true);
                $html .= '<div class="adm-rpt-offer__boxes">';
                    $html .= $this->reportStatBox('Mature', (string) $venue['mature_entry']);
                    $html .= $this->reportStatBox('Academic', (string) $venue['academic_entry']);
                    $html .= $this->reportStatBox('Unknown', $this->reportUnknownEntry($venue));
                $html .= '</div>';
                $html .= '<div class="adm-rpt-meter">';
                    $html .= '<div class="adm-rpt-meter__label">Fill Rate</div>';
                    $html .= '<div class="adm-rpt-meter__track"><span style="width: '.min($fill, 100).'%; background: '.$fillAccent.';"></span></div>';
                    $html .= '<div class="adm-rpt-meter__pct" style="color: '.$fillAccent.';">'.$fill.'%</div>';
                $html .= '</div>';
            $html .= '</div>';
        endforeach;

        return $html;
    }

    /**
     * Gender / nationality / age of the offered cohort.
     */
    protected function reportDemographics(array $personal, $total){
        $gender = (isset($personal['gender']) && !empty($personal['gender']) ? $personal['gender'] : []);
        $age = (isset($personal['age']) && !empty($personal['age']) ? $personal['age'] : []);
        $avg_age = (isset($personal['avg_age']) && $personal['avg_age'] > 0 ? $personal['avg_age'] : 0);
        $nationality = (isset($personal['nationality']) && !empty($personal['nationality']) ? array_values($personal['nationality']) : []);

        usort($nationality, function($a, $b){
            return $b['applicants'] <=> $a['applicants'];
        });
        $topNationality = (!empty($nationality) ? max(array_column($nationality, 'applicants')) : 0);

        $html = $this->reportSectionHead('Applicant Demographics');
        $html .= '<div class="adm-rpt-demo">';

            $html .= '<div class="adm-rpt-panel">';
                $html .= $this->reportPanelHead('users', 'Gender', '#2f6ea5', '#eef4fb');
                $html .= '<div class="adm-rpt-panel__body">';
                    $html .= $this->reportBarRow('Male Applicants', (isset($gender['male']) ? $gender['male'] : 0), $total, '#2f6ea5');
                    $html .= $this->reportBarRow('Female Applicants', (isset($gender['female']) ? $gender['female'] : 0), $total, '#d6337a');
                    $html .= $this->reportBarRow('Other Applicants', (isset($gender['other']) ? $gender['other'] : 0), $total, '#8b98a0');
                $html .= '</div>';
            $html .= '</div>';

            $html .= '<div class="adm-rpt-panel">';
                $html .= $this->reportPanelHead('globe', 'Nationality', 'var(--adm-pri)', 'var(--adm-tint)', count($nationality).' '.(count($nationality) == 1 ? 'country' : 'countries'));
                $html .= '<div class="adm-rpt-panel__body">';
                    if(!empty($nationality)):
                        foreach($nationality as $nation):
                            $html .= $this->reportRankRow($this->reportLabel($nation['name']), $nation['applicants'], $total, $topNationality);
                        endforeach;
                    else:
                        $html .= '<div class="adm-rpt-panel__empty">No nationality recorded.</div>';
                    endif;
                $html .= '</div>';
            $html .= '</div>';

            $html .= '<div class="adm-rpt-panel">';
                $html .= $this->reportPanelHead('clock', 'Age', '#6d4bb0', '#f4f1fa', '', ($avg_age > 0 ? 'Mean '.$avg_age : ''));
                $html .= '<div class="adm-rpt-panel__body">';
                    if(!empty($age)):
                        foreach($age as $band => $count):
                            $html .= $this->reportBarRow(str_replace('60 and over', '60+', $band), $count, $total, '#6d4bb0');
                        endforeach;
                    else:
                        $html .= '<div class="adm-rpt-panel__empty">No date of birth recorded.</div>';
                    endif;
                $html .= '</div>';
            $html .= '</div>';

        $html .= '</div>';

        return $html;
    }

    /**
     * Aggregate target chip sitting in a course card header.
     */
    protected function reportTargetChip($weekdays, $weekends){
        $html = '<div class="adm-rpt-targetchip">';
            $html .= '<div class="adm-rpt-targetchip__label">Target</div>';
            $html .= '<div class="adm-rpt-targetchip__value">'.($weekdays + $weekends).'</div>';
            $html .= '<div class="adm-rpt-targetchip__split">'.$weekdays.' wd · '.$weekends.' eve</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Target / Offered block of an offered row card.
     */
    protected function reportOfferStat($label, $value, $weekdays, $weekends, $accented){
        $html = '<div class="adm-rpt-offer__stat'.($accented ? ' adm-rpt-offer__stat--acc' : '').'">';
            $html .= '<div class="adm-rpt-offer__statlabel">'.e($label).'</div>';
            $html .= '<div class="adm-rpt-offer__statvalue">'.($value > 0 ? $value : 0).'</div>';
            $html .= '<div class="adm-rpt-offer__statsplit">'.($weekdays > 0 ? $weekdays : 0).' wd · '.($weekends > 0 ? $weekends : 0).' eve</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Small square stat. `$valueHtml` is markup — the Unknown box carries a link.
     */
    protected function reportStatBox($label, $valueHtml){
        $html = '<div class="adm-rpt-statbox">';
            $html .= '<div class="adm-rpt-statbox__label">'.e($label).'</div>';
            $html .= '<div class="adm-rpt-statbox__value">'.$valueHtml.'</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Unknown-entry count, linked to the applicant modal when there is one.
     */
    protected function reportUnknownEntry(array $venue){
        $count = (isset($venue['unknown_entry']) && $venue['unknown_entry'] > 0 ? $venue['unknown_entry'] : 0);
        $ids = (isset($venue['unknown_ids']) && !empty($venue['unknown_ids']) ? $venue['unknown_ids'] : []);
        if($count < 1 || empty($ids)):
            return (string) $count;
        endif;

        return '<a href="javascript:void(0);" data-ids="'.e(implode(',', $ids)).'" data-tw-toggle="modal" data-tw-target="#viewUnknownEntryModal" class="viewUnknownEntryBtn">'.$count.'</a>';
    }

    /**
     * Label + count + share of the cohort, over a proportional bar.
     */
    protected function reportBarRow($label, $value, $total, $accent){
        $value = ($value > 0 ? $value : 0);
        $share = ($total > 0 && $value > 0 ? ($value / $total) * 100 : 0);

        $html = '<div class="adm-rpt-barrow">';
            $html .= '<div class="adm-rpt-barrow__top">';
                $html .= '<span class="adm-rpt-barrow__label">'.e($label).'</span>';
                $html .= '<span class="adm-rpt-barrow__value">'.$value.'<small>'.number_format($share, 2).'%</small></span>';
            $html .= '</div>';
            $html .= '<div class="adm-rpt-track"><span style="width: '.round($share, 2).'%; background: '.$accent.';"></span></div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * One nationality: bar scaled against the biggest group, not the cohort,
     * so a long tail of single applicants stays readable.
     */
    protected function reportRankRow($label, $value, $total, $top){
        $value = ($value > 0 ? $value : 0);
        $share = ($total > 0 && $value > 0 ? ($value / $total) * 100 : 0);
        $width = ($top > 0 && $value > 0 ? ($value / $top) * 100 : 0);

        $html = '<div class="adm-rpt-rank">';
            $html .= '<span class="adm-rpt-rank__label">'.e($label).'</span>';
            $html .= '<span class="adm-rpt-track adm-rpt-rank__track"><span style="width: '.round($width, 2).'%;"></span></span>';
            $html .= '<b class="adm-rpt-rank__value">'.$value.'</b>';
            $html .= '<span class="adm-rpt-rank__pct">'.number_format($share, 2).'%</span>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Icon chip + title, with either a caption or a pill on the right.
     */
    protected function reportPanelHead($icon, $title, $accent, $tint, $meta = '', $pill = ''){
        $html = '<div class="adm-rpt-panel__head">';
            $html .= '<span class="adm-rpt-panel__icon" style="background: '.$tint.'; color: '.$accent.';">'.$this->reportIcon($icon).'</span>';
            $html .= '<span class="adm-rpt-panel__title">'.e($title).'</span>';
            if($meta !== ''):
                $html .= '<span class="adm-rpt-panel__meta">'.e($meta).'</span>';
            endif;
            if($pill !== ''):
                $html .= '<span class="adm-rpt-panel__pill" style="background: '.$tint.'; color: '.$accent.';">'.e($pill).'</span>';
            endif;
        $html .= '</div>';

        return $html;
    }

    /**
     * Metric cell of a course table: coloured dot + name.
     * `$labelHtml` is markup, so callers escape their own text.
     */
    protected function reportMetricCell($labelHtml, $accent){
        return '<td><span class="adm-rpt-metric"><i class="adm-rpt-metric__dot" style="background: '.$accent.';"></i>'.$labelHtml.'</span></td>';
    }

    /**
     * Inline `style` payload carrying a course's own palette into a card.
     */
    protected function reportCourseAccent(array $course){
        $palette = (isset($course['theme']) && !empty($course['theme']) ? $course['theme'] : CourseTheme::forKey(null));

        return '--rpt-acc: '.$palette['primary'].'; --rpt-tint: '.$palette['tint'].';';
    }

    /**
     * Status colours are matched on the name because the `statuses` table is
     * seeded per environment — ids are not stable, wording is.
     */
    protected function reportStatusAccent($name){
        $key = strtolower((string) $name);

        if(strpos($key, 'accept') !== false):
            return '#1e6b4e';
        endif;
        if(strpos($key, 'reject') !== false && strpos($key, 'offer') !== false):
            return '#6d4bb0';
        endif;
        if(strpos($key, 'declin') !== false || strpos($key, 'withdraw') !== false):
            return '#6d4bb0';
        endif;
        if(strpos($key, 'reject') !== false):
            return '#c0453f';
        endif;
        if(strpos($key, 'await') !== false || strpos($key, 'pending') !== false || strpos($key, 'progress') !== false || strpos($key, 'submitting') !== false):
            return '#a9842d';
        endif;
        if(strpos($key, 'offer') !== false || strpos($key, 'new') !== false):
            return '#2f6ea5';
        endif;

        return '#5a6b74';
    }

    /**
     * Fill-rate colour. No target at all is amber, not red — the venue is
     * unplanned rather than under-recruited.
     */
    protected function reportFillAccent($fill, $target){
        if($target < 1):
            return '#a9842d';
        endif;
        if($fill >= 100):
            return '#1e6b4e';
        endif;
        if($fill >= 75):
            return '#2f6ea5';
        endif;
        if($fill >= 50):
            return '#a9842d';
        endif;

        return '#c0453f';
    }

    protected function reportLabel($value){
        return (trim((string) $value) !== '' ? $value : '—');
    }

    protected function reportIcon($name){
        $icons = [
            'pin' => '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle>',
            'users' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"></path>',
            'globe' => '<circle cx="12" cy="12" r="10"></circle><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>',
            'clock' => '<circle cx="12" cy="12" r="10"></circle><path d="M12 6v6l4 2"></path>',
        ];

        if(!isset($icons[$name])):
            return '';
        endif;

        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.$icons[$name].'</svg>';
    }

    protected function reportEmptyState(){
        $html = '<div class="adm-rpt">';
            $html .= '<div class="adm-rpt-empty">';
                $html .= '<div class="adm-rpt-empty__title">Nothing to analyse yet</div>';
                $html .= '<div class="adm-rpt-empty__text">This intake carries no course targets and no submitted applications.</div>';
            $html .= '</div>';
        $html .= '</div>';

        return $html;
    }
    /* ------------------------------------------------------------------ *
     * Report body — PDF
     *
     * The screen report and this one deliberately do not share markup:
     * DomPDF 2.0.7 understands neither flexbox nor grid, so every row the
     * design lays out with flex is a table here. Class names are defined in
     * pdfStylesheet().
     * ------------------------------------------------------------------ */

    public function getPdfHtml($semester_id, $semesterLabel = ''){
        $totalTarget = $this->getTotalApplicantTarget($semester_id);
        $basicAnalysis = $this->getApplicationCoreAnalysis($semester_id);
        $courseAnalysis = $this->getApplicationCourseAnalysis($semester_id);
        $offeredAnalysis = $this->getOfferedStudentsAnalysis($semester_id);
        $offeredCourseAnalysis = $this->getOfferedStudentsCourseAnalysis($semester_id);

        $offeredCourses = (isset($offeredCourseAnalysis['offeredCourses']) && !empty($offeredCourseAnalysis['offeredCourses']) ? $offeredCourseAnalysis['offeredCourses'] : []);
        $offeredPersonal = (isset($offeredCourseAnalysis['offeredPersonal']) && !empty($offeredCourseAnalysis['offeredPersonal']) ? $offeredCourseAnalysis['offeredPersonal'] : []);
        $no_of_applicants = (isset($offeredCourseAnalysis['no_of_applicants']) && !empty($offeredCourseAnalysis['no_of_applicants']) ? $offeredCourseAnalysis['no_of_applicants'] : 0);

        $totalApplications = ($basicAnalysis->count() > 0 ? $basicAnalysis->sum('TOTAL') : 0);
        $totalOffered = ($offeredAnalysis->count() > 0 ? $offeredAnalysis->sum('TOTAL') : 0);

        $html = $this->pdfLede($semesterLabel, $totalApplications, $totalOffered);

        /* --- headline figures --- */
        $kpis = [
            ['label' => 'Total Target', 'value' => $totalTarget, 'accent' => '#2f6ea5'],
            ['label' => 'Total Application', 'value' => $totalApplications, 'accent' => '#0b6b66'],
        ];
        if($basicAnalysis->count() > 0):
            foreach($basicAnalysis as $ba):
                $kpis[] = [
                    'label' => $this->reportLabel($ba->status_name),
                    'value' => $ba->TOTAL,
                    'accent' => $this->reportStatusAccent($ba->status_name),
                ];
            endforeach;
        endif;
        $html .= $this->pdfKpis($kpis);

        /* --- analysis by course --- */
        if(!empty($courseAnalysis)):
            $html .= $this->pdfSectionHead('Analysis By Course');
            foreach($courseAnalysis as $course):
                $html .= $this->pdfCourseCard($course);
            endforeach;
        endif;

        /* --- offered students --- */
        if(!empty($offeredCourses)):
            $html .= $this->pdfSectionHead('Offered Students Analysis', 'Total Offered '.$totalOffered, true);
            foreach($offeredCourses as $course):
                $html .= $this->pdfOfferedCard($course);
            endforeach;
        endif;

        /* --- demographics of the offered cohort --- */
        if(!empty($offeredPersonal)):
            $html .= $this->pdfDemographics($offeredPersonal, $no_of_applicants);
        endif;

        if($totalTarget < 1 && $totalApplications < 1 && empty($courseAnalysis)):
            $html .= '<div class="empty">This intake carries no course targets and no submitted applications.</div>';
        endif;

        return $html;
    }

    /**
     * Title block: report name on the left, conversion rate on the right.
     */
    protected function pdfLede($semesterLabel, $totalApplications, $totalOffered){
        $conversion = ($totalApplications > 0 ? ($totalOffered / $totalApplications) * 100 : 0);

        $html = '<table class="lede">';
            $html .= '<tr>';
                $html .= '<td>';
                    $html .= '<div class="lede__eyebrow">REPORTS</div>';
                    $html .= '<div class="lede__title">Application Analysis</div>';
                    $html .= '<div class="lede__sub">Intake semester '.e($this->reportLabel($semesterLabel)).' &middot; all campuses and delivery patterns</div>';
                $html .= '</td>';
                $html .= '<td class="lede__stat">';
                    $html .= '<div class="conv">';
                        $html .= '<div class="conv__label">CONVERSION</div>';
                        $html .= '<div class="conv__value">'.number_format($conversion, 1).'%</div>';
                        $html .= '<div class="conv__note">'.$totalOffered.' of '.$totalApplications.' applications</div>';
                    $html .= '</div>';
                $html .= '</td>';
            $html .= '</tr>';
        $html .= '</table>';

        return $html;
    }

    /**
     * Headline tiles, five to a row — the count is driven by how many
     * application statuses the intake actually used.
     */
    protected function pdfKpis(array $kpis){
        if(empty($kpis)):
            return '';
        endif;

        $html = '';
        foreach(array_chunk($kpis, 5) as $row):
            $count = count($row);
            $html .= '<table class="kpis"><tr>';
                foreach($row as $index => $kpi):
                    /* The last tile of a full row drops its gutter; a short
                     * final row keeps five columns so the tiles stay the same
                     * width as the row above. */
                    $isLast = ($index === $count - 1 && $count === 5);
                    $html .= '<td style="width: 20%;"'.($isLast ? ' class="is-last"' : '').'>';
                        $html .= '<div class="kpi">';
                            $html .= '<div class="kpi__label">';
                                $html .= '<span class="kpi__dot" style="background: '.$kpi['accent'].';"></span>';
                                $html .= e(strtoupper($kpi['label']));
                            $html .= '</div>';
                            $html .= '<div class="kpi__value">'.($kpi['value'] > 0 ? $kpi['value'] : 0).'</div>';
                        $html .= '</div>';
                    $html .= '</td>';
                endforeach;
                for($pad = $count; $pad < 5; $pad++):
                    $html .= '<td style="width: 20%;"'.($pad === 4 ? ' class="is-last"' : '').'>&nbsp;</td>';
                endfor;
            $html .= '</tr></table>';
        endforeach;

        return $html;
    }

    /**
     * Section title with a hairline rule and an optional pill.
     */
    protected function pdfSectionHead($title, $pill = '', $breakBefore = false){
        $html = '<table class="sec'.($breakBefore ? ' page-break' : '').'">';
            $html .= '<tr>';
                $html .= '<td class="sec__title">'.e($title).'</td>';
                $html .= '<td class="sec__rule"><div></div></td>';
                if($pill !== ''):
                    $html .= '<td class="sec__pill"><span>'.e($pill).'</span></td>';
                endif;
            $html .= '</tr>';
        $html .= '</table>';

        return $html;
    }

    /**
     * Target vs. applications for one course, keyed by application status.
     */
    protected function pdfCourseCard(array $course){
        $palette = (isset($course['theme']) && !empty($course['theme']) ? $course['theme'] : CourseTheme::forKey(null));
        $accent = $palette['primary'];
        $tint = $palette['tint'];

        $venues = (isset($course['venues']) && !empty($course['venues']) ? $course['venues'] : []);
        $applications = (isset($course['applications']) && !empty($course['applications']) ? $course['applications'] : []);
        $weekdays = (!empty($venues) ? array_sum(array_column($venues, 'weekdays')) : 0);
        $weekends = (!empty($venues) ? array_sum(array_column($venues, 'weekends')) : 0);

        $html = '<div class="card">';
            $html .= '<table class="card__head" style="background: '.$tint.';">';
                $html .= '<tr>';
                    $html .= '<td class="card__bar"><div style="background: '.$accent.';"></div></td>';
                    $html .= '<td>';
                        $html .= '<div class="card__name">'.e($this->reportLabel(isset($course['name']) ? $course['name'] : '')).'</div>';
                        if(!empty($venues)):
                            $html .= '<div class="card__venues">'.$this->pdfVenueCaption($venues).'</div>';
                        endif;
                    $html .= '</td>';
                    $html .= '<td class="card__target">';
                        $html .= '<div class="card__targetlabel" style="color: '.$accent.';">TARGET</div>';
                        $html .= '<div class="card__targetvalue" style="color: '.$accent.';">'.($weekdays + $weekends).'</div>';
                    $html .= '</td>';
                $html .= '</tr>';
            $html .= '</table>';

            $html .= '<table class="grid">';
                $html .= '<thead><tr>';
                    $html .= '<th class="pad-l">METRIC</th>';
                    $html .= '<th class="text-right">WEEKDAYS</th>';
                    $html .= '<th class="text-right">EVENING / WEEKEND</th>';
                    $html .= '<th class="text-right">TOTAL</th>';
                    $html .= '<th class="text-right">MATURE ENTRY</th>';
                    $html .= '<th class="text-right pad-r">ACADEMIC ENTRY</th>';
                $html .= '</tr></thead>';
                $html .= '<tbody>';
                    /* No per-venue target rows: the caption above carries that
                     * split and the chip carries the aggregate, so the table is
                     * left to the application statuses alone. */
                    if(!empty($applications)):
                        $html .= '<tr>';
                            $html .= $this->pdfMetricCell('Total Application', '#5a6b74');
                            $html .= '<td class="num">'.array_sum(array_column($applications, 'WEEKDAYS')).'</td>';
                            $html .= '<td class="num">'.array_sum(array_column($applications, 'WEEKENDS')).'</td>';
                            $html .= '<td class="num total">'.array_sum(array_column($applications, 'TOTAL')).'</td>';
                            $html .= '<td class="num">'.array_sum(array_column($applications, 'MATURE')).'</td>';
                            $html .= '<td class="num pad-r">'.array_sum(array_column($applications, 'ACADEMIC')).'</td>';
                        $html .= '</tr>';
                        foreach($applications as $row):
                            $html .= '<tr>';
                                $html .= $this->pdfMetricCell(e($this->reportLabel($row['STATUS_NAME'])), $this->reportStatusAccent($row['STATUS_NAME']));
                                $html .= '<td class="num">'.$row['WEEKDAYS'].'</td>';
                                $html .= '<td class="num">'.$row['WEEKENDS'].'</td>';
                                $html .= '<td class="num total">'.$row['TOTAL'].'</td>';
                                $html .= '<td class="num">'.$row['MATURE'].'</td>';
                                $html .= '<td class="num pad-r">'.$row['ACADEMIC'].'</td>';
                            $html .= '</tr>';
                        endforeach;
                    else:
                        $html .= '<tr><td colspan="6" class="void" style="padding: 9pt 12pt; text-align: center;">No applications recorded against this course.</td></tr>';
                    endif;
                $html .= '</tbody>';
            $html .= '</table>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Venue list for a course-card caption, each venue carrying its own share
     * of the target: "Barclay Hall (80 weekday) · Avicenna house (90 evening/weekend)".
     *
     * Names are escaped individually and joined afterwards — running e() over
     * the joined string would escape the separator and print "&middot;".
     */
    protected function pdfVenueCaption(array $venues){
        $parts = [];
        foreach($venues as $venue):
            $split = [];
            if(isset($venue['weekdays']) && $venue['weekdays'] > 0):
                $split[] = $venue['weekdays'].' weekday';
            endif;
            if(isset($venue['weekends']) && $venue['weekends'] > 0):
                $split[] = $venue['weekends'].' evening/weekend';
            endif;

            $parts[] = e($this->reportLabel(isset($venue['name']) ? $venue['name'] : '')).(!empty($split) ? ' ('.implode(', ', $split).')' : '');
        endforeach;

        return implode(' &middot; ', $parts);
    }

    /**
     * Target over offered for every venue of a course, two rows per venue.
     */
    protected function pdfOfferedCard(array $course){
        $venues = (isset($course['venues']) && !empty($course['venues']) ? $course['venues'] : []);
        if(empty($venues)):
            return '';
        endif;

        $palette = (isset($course['theme']) && !empty($course['theme']) ? $course['theme'] : CourseTheme::forKey(null));
        $accent = $palette['primary'];
        $tint = $palette['tint'];

        $html = '<div class="card">';
            $html .= '<table class="card__head">';
                $html .= '<tr>';
                    $html .= '<td class="card__bar"><div style="background: '.$accent.'; height: 20pt;"></div></td>';
                    $html .= '<td class="card__name">'.e($this->reportLabel(isset($course['name']) ? $course['name'] : '')).'</td>';
                $html .= '</tr>';
            $html .= '</table>';

            $html .= '<table class="grid">';
                $html .= '<thead><tr>';
                    $html .= '<th class="pad-l">VENUE</th>';
                    $html .= '<th></th>';
                    $html .= '<th class="text-right">WEEKDAYS</th>';
                    $html .= '<th class="text-right">EVENING / WEEKEND</th>';
                    $html .= '<th class="text-right">TOTAL</th>';
                    $html .= '<th class="text-right">MATURE</th>';
                    $html .= '<th class="text-right">ACADEMIC</th>';
                    $html .= '<th class="text-right pad-r">UNKNOWN</th>';
                $html .= '</tr></thead>';
                $html .= '<tbody>';
                    foreach($venues as $venue):
                        $html .= '<tr>';
                            $html .= '<td rowspan="2" class="venue">'.e($this->reportLabel($venue['name'])).'</td>';
                            $html .= '<td class="rowlabel">TARGET</td>';
                            $html .= '<td class="num">'.$venue['weekdays_trget'].'</td>';
                            $html .= '<td class="num">'.$venue['weekends_trget'].'</td>';
                            $html .= '<td class="num" style="font-weight: bold;">'.$venue['total_trget'].'</td>';
                            $html .= '<td class="num void">&mdash;</td>';
                            $html .= '<td class="num void">&mdash;</td>';
                            $html .= '<td class="num void pad-r">&mdash;</td>';
                        $html .= '</tr>';
                        /* The offered row is tinted so the pair reads as one
                         * block even when a course runs at several venues. */
                        $html .= '<tr style="background: '.$tint.';">';
                            $html .= '<td class="rowlabel" style="color: '.$accent.';">OFFERED</td>';
                            $html .= '<td class="num">'.$venue['weekdays_offered'].'</td>';
                            $html .= '<td class="num">'.$venue['weekends_offered'].'</td>';
                            $html .= '<td class="num total">'.$venue['total_offered'].'</td>';
                            $html .= '<td class="num">'.$venue['mature_entry'].'</td>';
                            $html .= '<td class="num">'.$venue['academic_entry'].'</td>';
                            $html .= '<td class="num pad-r">'.(isset($venue['unknown_entry']) && $venue['unknown_entry'] > 0 ? $venue['unknown_entry'] : 0).'</td>';
                        $html .= '</tr>';
                    endforeach;
                $html .= '</tbody>';
            $html .= '</table>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Gender / nationality / age of the offered cohort, one panel each.
     */
    protected function pdfDemographics(array $personal, $total){
        $gender = (isset($personal['gender']) && !empty($personal['gender']) ? $personal['gender'] : []);
        $age = (isset($personal['age']) && !empty($personal['age']) ? $personal['age'] : []);
        $avg_age = (isset($personal['avg_age']) && $personal['avg_age'] > 0 ? $personal['avg_age'] : 0);
        $nationality = (isset($personal['nationality']) && !empty($personal['nationality']) ? array_values($personal['nationality']) : []);

        usort($nationality, function($a, $b){
            return $b['applicants'] <=> $a['applicants'];
        });
        $topNationality = (!empty($nationality) ? max(array_column($nationality, 'applicants')) : 0);

        $html = $this->pdfSectionHead('Applicant Demographics', '', true);

        $html .= '<div class="panel">';
            $html .= '<div class="panel__title" style="margin-bottom: 8pt;">Gender</div>';
            $html .= $this->pdfBarRow('Male Applicants', (isset($gender['male']) ? $gender['male'] : 0), $total, '#2f6ea5');
            $html .= $this->pdfBarRow('Female Applicants', (isset($gender['female']) ? $gender['female'] : 0), $total, '#d6337a');
            $html .= $this->pdfBarRow('Other Applicants', (isset($gender['other']) ? $gender['other'] : 0), $total, '#8b98a0');
        $html .= '</div>';

        $html .= '<div class="panel">';
            $html .= '<table class="panel__head"><tr>';
                $html .= '<td class="panel__title">Nationality</td>';
                $html .= '<td class="panel__meta">'.count($nationality).' '.(count($nationality) == 1 ? 'country' : 'countries').' &middot; '.$total.' applicants</td>';
            $html .= '</tr></table>';
            if(!empty($nationality)):
                foreach($nationality as $nation):
                    $html .= $this->pdfRankRow($this->reportLabel($nation['name']), $nation['applicants'], $total, $topNationality);
                endforeach;
            else:
                $html .= '<div class="panel__empty">No nationality recorded.</div>';
            endif;
        $html .= '</div>';

        $html .= '<div class="panel">';
            $html .= '<table class="panel__head"><tr>';
                $html .= '<td class="panel__title">Age</td>';
                $html .= '<td class="text-right">'.($avg_age > 0 ? '<span class="panel__pill">Mean '.$avg_age.'</span>' : '').'</td>';
            $html .= '</tr></table>';
            if(!empty($age)):
                foreach($age as $band => $count):
                    $html .= $this->pdfBarRow(str_replace('60 and over', '60+', $band), $count, $total, '#6d4bb0');
                endforeach;
            else:
                $html .= '<div class="panel__empty">No date of birth recorded.</div>';
            endif;
        $html .= '</div>';

        return $html;
    }

    /**
     * Label + count + share, over a proportional bar.
     */
    protected function pdfBarRow($label, $value, $total, $accent){
        $value = ($value > 0 ? $value : 0);
        $share = ($total > 0 && $value > 0 ? ($value / $total) * 100 : 0);

        $html = '<div class="barrow">';
            $html .= '<table class="barrow__top"><tr>';
                $html .= '<td class="barrow__label">'.e($label).'</td>';
                $html .= '<td class="barrow__value">'.$value.' <span class="barrow__pct">'.number_format($share, 2).'%</span></td>';
            $html .= '</tr></table>';
            $html .= '<div class="bar"><div style="width: '.round($share, 2).'%; background: '.$accent.';"></div></div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * One nationality. The bar is scaled against the largest group rather
     * than the cohort, so a long tail of single applicants stays readable.
     */
    protected function pdfRankRow($label, $value, $total, $top){
        $value = ($value > 0 ? $value : 0);
        $share = ($total > 0 && $value > 0 ? ($value / $total) * 100 : 0);
        $width = ($top > 0 && $value > 0 ? ($value / $top) * 100 : 0);

        $html = '<table class="rank"><tr>';
            $html .= '<td class="rank__label">'.e($label).'</td>';
            $html .= '<td class="rank__bar"><div class="bar" style="height: 4.5pt;"><div style="width: '.round($width, 2).'%; background: #0B6B66; height: 4.5pt;"></div></div></td>';
            $html .= '<td class="rank__n">'.$value.'</td>';
            $html .= '<td class="rank__pct">'.number_format($share, 2).'%</td>';
        $html .= '</tr></table>';

        return $html;
    }

    /**
     * Metric cell of a course table: coloured dot + name.
     * `$labelHtml` is markup, so callers escape their own text.
     */
    protected function pdfMetricCell($labelHtml, $accent){
        return '<td class="metric"><span class="dot" style="background: '.$accent.';"></span>'.$labelHtml.'</td>';
    }

    public function getTotalApplicantTarget($semester_id){
        $totalTarget = 0;
        $courseCreationsIds = CourseCreation::where('semester_id', $semester_id)->pluck('id')->unique()->toArray();
        $crVenues = CourseCreationVenue::whereIn('course_creation_id', $courseCreationsIds)->get();
        $totalTarget += $crVenues->sum('weekdays');
        $totalTarget += $crVenues->sum('weekends');

        return $totalTarget;
    }

    public function getApplicationCoreAnalysis($semester_id){
        $courseCreationsIds = CourseCreation::where('semester_id', $semester_id)->pluck('id')->unique()->toArray();
        $Query = DB::table('applicant_proposed_courses as apc')
                 ->select(
                    'sts.name as status_name', 'ap.status_id',
                    DB::raw('COUNT(ap.id) as TOTAL'),
                 )
                 ->leftJoin('applicants as ap', 'apc.applicant_id', 'ap.id')
                 ->leftJoin('statuses as sts', 'ap.status_id', 'sts.id')
                 ->whereIn('apc.course_creation_id', $courseCreationsIds)
                 ->where('apc.semester_id', $semester_id)
                 ->where('ap.status_id', '>', 1)
                 ->groupBy('ap.status_id')->orderBy('ap.status_id', 'ASC')
                 ->get();
        return $Query;
    }

    public function getApplicationCourseAnalysis($semester_id){
        $res = [];
        $creations = CourseCreation::where('semester_id', $semester_id)->get();
        if($creations->count() > 0):
            foreach($creations as $creation):
                $res[$creation->course_id]['name'] = (isset($creation->course->name) && !empty($creation->course->name) ? $creation->course->name : '');
                // Screen cards are accented with the course's own palette; the
                // PDF renderer simply ignores this key.
                $res[$creation->course_id]['theme'] = CourseTheme::forKey(optional($creation->course)->color_theme);
                $creationVenues = CourseCreationVenue::where('course_creation_id', $creation->id)->get();
                if($creationVenues->count() > 0):
                    foreach($creationVenues as $venue):
                        $res[$creation->course_id]['venues'][$venue->venue_id]['name'] = (isset($venue->venue->name) && !empty($venue->venue->name) ? $venue->venue->name : '');
                        $res[$creation->course_id]['venues'][$venue->venue_id]['weekdays'] = ($venue->weekdays > 0 ? $venue->weekdays : 0);
                        $res[$creation->course_id]['venues'][$venue->venue_id]['weekends'] = ($venue->weekends > 0 ? $venue->weekends : 0);
                        $res[$creation->course_id]['venues'][$venue->venue_id]['total'] = (($venue->weekends > 0 ? $venue->weekends : 0) + ($venue->weekdays > 0 ? $venue->weekdays : 0));
                    endforeach;
                endif;

                $applications = DB::table('applicant_proposed_courses as apc')
                        ->select(
                            'sts.name as status_name', 'ap.status_id',
                            DB::raw('GROUP_CONCAT(DISTINCT(apc.applicant_id)) as applicant_ids'),
                            DB::raw('COUNT(ap.id) as TOTAL'),
                            DB::raw('SUM(CASE WHEN apc.full_time = 0 THEN 1 ELSE 0 END) AS WEEKDAYS'), 
                            DB::raw('SUM(CASE WHEN apc.full_time = 1 THEN 1 ELSE 0 END) AS WEEKENDS'), 
                        )
                        ->leftJoin('applicants as ap', 'apc.applicant_id', 'ap.id')
                        ->leftJoin('statuses as sts', 'ap.status_id', 'sts.id')
                        ->where('apc.course_creation_id', $creation->id)
                        ->where('apc.semester_id', $semester_id)
                        ->where('ap.status_id', '>', 1)
                        ->groupBy('ap.status_id')->orderBy('ap.status_id', 'ASC')
                        ->get();
                if(!empty($applications) && count($applications) > 0):
                    foreach($applications as $appcnt):
                        $applicant_ids = (isset($appcnt->applicant_ids) && !empty($appcnt->applicant_ids) ? explode(',', str_replace(' ', '', $appcnt->applicant_ids)) : []);
                        $res[$creation->course_id]['applications'][$appcnt->status_id]['STATUS_NAME'] = (isset($appcnt->status_name) && !empty($appcnt->status_name) ? $appcnt->status_name : '');
                        $res[$creation->course_id]['applications'][$appcnt->status_id]['TOTAL'] = (isset($appcnt->TOTAL) && $appcnt->TOTAL > 0 ? $appcnt->TOTAL : 0);
                        $res[$creation->course_id]['applications'][$appcnt->status_id]['WEEKDAYS'] = (isset($appcnt->WEEKDAYS) && $appcnt->WEEKDAYS > 0 ? $appcnt->WEEKDAYS : 0);
                        $res[$creation->course_id]['applications'][$appcnt->status_id]['WEEKENDS'] = (isset($appcnt->WEEKENDS) && $appcnt->WEEKENDS > 0 ? $appcnt->WEEKENDS : 0);

                        $academicEntry = (!empty($applicant_ids) ? ApplicantOtherDetail::whereIn('applicant_id', $applicant_ids)->where('is_edication_qualification', 1)->get()->count() : 0);
                        $matureEntry = 0;
                        if(!empty($applicant_ids)):
                            $matureEntry = Applicant::whereIn('id', $applicant_ids)->whereHas('other', function($q){
                                                $q->whereNotNull('employment_status');
                                            })->whereHas('employment')->get()->count();
                        endif;
                        $res[$creation->course_id]['applications'][$appcnt->status_id]['MATURE'] = $matureEntry;
                        $res[$creation->course_id]['applications'][$appcnt->status_id]['ACADEMIC'] = $academicEntry;
                    endforeach;
                endif;
                //$res[$creation->course_id]['applications'] = $applications;
            endforeach;
        endif; 

        return $res;
    }

    public function getOfferedStudentsAnalysis($semester_id){
        $courseCreationsIds = CourseCreation::where('semester_id', $semester_id)->pluck('id')->unique()->toArray();
        $Query = DB::table('applicant_proposed_courses as apc')
                 ->select(
                    'sts.name as status_name', 'ap.status_id',
                    DB::raw('COUNT(ap.id) as TOTAL'),
                 )
                 ->leftJoin('applicants as ap', 'apc.applicant_id', 'ap.id')
                 ->leftJoin('statuses as sts', 'ap.status_id', 'sts.id')
                 ->whereIn('apc.course_creation_id', $courseCreationsIds)
                 ->where('apc.semester_id', $semester_id)
                 ->whereIn('ap.status_id', [5, 6, 7])
                 ->groupBy('ap.status_id')->orderBy('ap.status_id', 'ASC')
                 ->get();
        return $Query;
    }

    public function getOfferedStudentsCourseAnalysis($semester_id){
        $res = [];
        $offeredApplicants = [];
        $creations = CourseCreation::where('semester_id', $semester_id)->get();
        if($creations->count() > 0):
            foreach($creations as $creation):
                $res[$creation->course_id]['name'] = (isset($creation->course->name) && !empty($creation->course->name) ? $creation->course->name : '');
                $res[$creation->course_id]['theme'] = CourseTheme::forKey(optional($creation->course)->color_theme);
                $creationVenues = CourseCreationVenue::where('course_creation_id', $creation->id)->get();
                if($creationVenues->count() > 0):
                    foreach($creationVenues as $venue):
                        $res[$creation->course_id]['venues'][$venue->venue_id]['name'] = (isset($venue->venue->name) && !empty($venue->venue->name) ? $venue->venue->name : '');
                        $res[$creation->course_id]['venues'][$venue->venue_id]['weekdays_trget'] = ($venue->weekdays > 0 ? $venue->weekdays : 0);
                        $res[$creation->course_id]['venues'][$venue->venue_id]['weekends_trget'] = ($venue->weekends > 0 ? $venue->weekends : 0);
                        $res[$creation->course_id]['venues'][$venue->venue_id]['total_trget'] = (($venue->weekends > 0 ? $venue->weekends : 0) + ($venue->weekdays > 0 ? $venue->weekdays : 0));

                        $query = DB::table('applicant_proposed_courses as apc')
                                ->select(
                                    'sts.name as status_name', 'ap.status_id',
                                    DB::raw('GROUP_CONCAT(DISTINCT (apc.applicant_id) ) as applicant_ids'),
                                    DB::raw('COUNT(ap.id) as TOTAL'),
                                    DB::raw('SUM(CASE WHEN apc.full_time = 0 THEN 1 ELSE 0 END) AS WEEKDAYS'), 
                                    DB::raw('SUM(CASE WHEN apc.full_time = 1 THEN 1 ELSE 0 END) AS WEEKENDS'), 
                                )
                                ->leftJoin('applicants as ap', 'apc.applicant_id', 'ap.id')
                                ->leftJoin('statuses as sts', 'ap.status_id', 'sts.id')
                                ->where('apc.course_creation_id', $creation->id)
                                ->where('apc.semester_id', $semester_id)
                                ->whereIn('ap.status_id', [5, 6, 7])
                                ->where('apc.venue_id', $venue->venue_id)
                                ->get()->first();
                        $res[$creation->course_id]['venues'][$venue->venue_id]['weekdays_offered'] = (isset($query->WEEKDAYS) && $query->WEEKDAYS > 0 ? $query->WEEKDAYS : 0);
                        $res[$creation->course_id]['venues'][$venue->venue_id]['weekends_offered'] = (isset($query->WEEKENDS) && $query->WEEKENDS > 0 ? $query->WEEKENDS : 0);
                        $res[$creation->course_id]['venues'][$venue->venue_id]['total_offered'] = (isset($query->TOTAL) && $query->TOTAL > 0 ? $query->TOTAL : 0);

                        $applicant_ids = (isset($query->applicant_ids) && !empty($query->applicant_ids) ? explode(',', str_replace(' ', '', $query->applicant_ids)) : []);
                        $offeredApplicants = array_merge($offeredApplicants, $applicant_ids);
                        $academicEntry = 0;
                        $matureEntry = 0;
                        $unknownEntryCount = 0;
                        $unknownEntryids = [];
                        if(!empty($applicant_ids)):
                            $academicEntry = ApplicantOtherDetail::whereIn('applicant_id', $applicant_ids)->where('is_edication_qualification', 1)->get()->count();
                            $matureEntry = Applicant::whereIn('id', $applicant_ids)->whereHas('other', function($q){
                                                $q->whereNotNull('employment_status');
                                            })->whereHas('employment')->get()->count();
                            $unknownEntry = Applicant::whereIn('id', $applicant_ids)->whereHas('other', function($q){
                                                $q->whereNot('is_edication_qualification', 1);
                                                $q->whereNotNull('employment_status');
                                            })->has('employment', '=', 0)->get();
                            $unknownEntryCount = $unknownEntry->count();
                            $unknownEntryids = $unknownEntry->pluck('id')->unique()->toArray();
                        endif;
                        $res[$creation->course_id]['venues'][$venue->venue_id]['mature_entry'] = $matureEntry;
                        $res[$creation->course_id]['venues'][$venue->venue_id]['academic_entry'] = $academicEntry;
                        $res[$creation->course_id]['venues'][$venue->venue_id]['unknown_entry'] = $unknownEntryCount;
                        $res[$creation->course_id]['venues'][$venue->venue_id]['unknown_ids'] = $unknownEntryids;
                    endforeach;
                endif;
            endforeach;
        endif;

        $offeredPersonalDataAnalysis = (!empty($offeredApplicants) && count($offeredApplicants) > 0 ? $this->applicantsPersonalDetailsAnalysis($offeredApplicants) : []);
        return ['no_of_applicants' => (!empty($offeredApplicants) ? count($offeredApplicants) : 0), 'offeredCourses' => $res, 'offeredPersonal' => $offeredPersonalDataAnalysis];
    }

    public function applicantsPersonalDetailsAnalysis($applicants){
        $res = [];
        $res['gender']['male'] = Applicant::whereIn('id', $applicants)->where('sex_identifier_id', 2)->get()->count();
        $res['gender']['female'] = Applicant::whereIn('id', $applicants)->where('sex_identifier_id', 1)->get()->count();
        $res['gender']['other'] = Applicant::whereIn('id', $applicants)->where('sex_identifier_id', 3)->get()->count();

        $today = date('Y-m-d');
        $res['age']['18-21'] = Applicant::whereIn('id', $applicants)->where('date_of_birth', '<=', date('Y-m-d', strtotime($today.' -18 years')))
                               ->where('date_of_birth', '>=', date('Y-m-d', strtotime($today.' -21 years')))
                               ->get()->count();
        $res['age']['21-29'] = Applicant::whereIn('id', $applicants)->where('date_of_birth', '<=', date('Y-m-d', strtotime($today.' -21 years')))
                               ->where('date_of_birth', '>=', date('Y-m-d', strtotime($today.' -29 years')))
                               ->get()->count();
        $res['age']['30-39'] = Applicant::whereIn('id', $applicants)->where('date_of_birth', '<=', date('Y-m-d', strtotime($today.' -30 years')))
                               ->where('date_of_birth', '>=', date('Y-m-d', strtotime($today.' -39 years')))
                               ->get()->count();
        $res['age']['40-49'] = Applicant::whereIn('id', $applicants)->where('date_of_birth', '<=', date('Y-m-d', strtotime($today.' -40 years')))
                               ->where('date_of_birth', '>=', date('Y-m-d', strtotime($today.' -49 years')))
                               ->get()->count();
        $res['age']['50-59'] = Applicant::whereIn('id', $applicants)->where('date_of_birth', '<=', date('Y-m-d', strtotime($today.' -50 years')))
                               ->where('date_of_birth', '>=', date('Y-m-d', strtotime($today.' -59 years')))
                               ->get()->count();
        $res['age']['60 and over'] = Applicant::whereIn('id', $applicants)->where('date_of_birth', '<=', date('Y-m-d', strtotime($today.' -60 years')))
                               ->get()->count();
        $avgage = DB::table('applicants')
                    ->select(DB::raw('ROUND(AVG(TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()))) AS average_age'))
                    ->whereNotNull('date_of_birth')
                    ->get()->first();
        $res['avg_age'] = (isset($avgage->average_age) && $avgage->average_age > 0 ? $avgage->average_age : 0);

        $nationalities = DB::table('applicants as ap')
                        ->select('ct.name', 'ap.nationality_id', DB::raw('count(DISTINCT ap.id) as nationality_count'))
                        ->join('countries as ct', 'ap.nationality_id', '=', 'ct.id')
                        ->groupBy('ap.nationality_id')
                        ->whereIn('ap.id', $applicants)
                        ->get();
        if(!empty($nationalities)):
            $i = 1;
            foreach($nationalities as $nations):
                $res['nationality'][$i]['name'] = (isset($nations->name) && !empty($nations->name) ? $nations->name : '');
                $res['nationality'][$i]['applicants'] = (isset($nations->nationality_count) && $nations->nationality_count > 0 ? $nations->nationality_count : 0);

                $i++;
            endforeach;
        endif;

        return $res;
    }

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
