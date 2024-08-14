<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Models\ApplicantOtherDetail;
use App\Models\ApplicantProposedCourse;
use App\Models\Option;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ApplicationAnalysisController extends Controller
{
    public function index(Request $request){
        $semester_id = (isset($request->semesters) && !empty($request->semesters) ? $request->semesters : 0);

        return view('pages.reports.application-analysis.index', [
            'title' => 'Application Analysis Report - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Reports', 'href' => 'javascript:void(0);'],
                ['label' => 'Application Analysis', 'href' => 'javascript:void(0);']
            ],
            'semesters' => Semester::all()->sortByDesc('name'),
            'searched_semesters' => $semester_id,
            'reports' => ($semester_id > 0 ? $this->generateReports($semester_id) : false)
        ]);
    }

    public function generateReports($semester_id){
        $res = [];
        $applicants = ApplicantProposedCourse::where('semester_id', $semester_id)->pluck('applicant_id')->unique()->toArray();
        $res['no_of_applicants'] = (!empty($applicants) && count($applicants) > 0 ? count($applicants) : 0);
        $res['personal_data'] = $this->applicantsPersonalDetailsAnalysis($semester_id, $applicants);
        $res['course_entry'] = $this->applicantCourseEntryAnalysis($semester_id, $applicants);
        $res['course_data'] = $this->applicantsCourseDataAnalysis($semester_id, $applicants);
        $res['applicants_ids'] = $applicants;

        return $res;
    }

    public function applicantsPersonalDetailsAnalysis($semester_id, $applicants){
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

    public function applicantCourseEntryAnalysis($semester_id, $applicants){
        $res = [];
        $res['academic_entry'] = ApplicantOtherDetail::whereIn('applicant_id', $applicants)->where('is_edication_qualification', 1)->get()->count();
        $res['mature_entry'] = Applicant::whereIn('id', $applicants)->whereHas('other', function($q){
                                                    $q->whereNotNull('employment_status');
                                               })->whereHas('employment')->get()->count();

        return $res;
    }

    public function applicantsCourseDataAnalysis($semester_id, $applicants){
        $res = [];
        $proposedCourse = DB::table('applicant_proposed_courses as apc')
                            ->select('cr.name', 'apc.course_creation_id', DB::raw('count(DISTINCT apc.id) as course_applicants'))
                            ->join('course_creations as crc', 'apc.course_creation_id', '=', 'crc.id')
                            ->join('courses as cr', 'crc.course_id', '=', 'cr.id')
                            ->groupBy('apc.course_creation_id')
                            ->where('apc.semester_id', $semester_id)
                            ->get();
        if(!empty($proposedCourse)):
            $i = 0;
            foreach($proposedCourse as $ppc):
                $res['courses'][$i]['name'] = (isset($ppc->name) && !empty($ppc->name) ? $ppc->name : '');
                $res['courses'][$i]['applicants'] = (isset($ppc->course_applicants) && $ppc->course_applicants > 0 ? $ppc->course_applicants : 0);
                $res['courses'][$i]['evening_weekends'] = ApplicantProposedCourse::where('semester_id', $semester_id)->whereIn('applicant_id', $applicants)
                                                   ->where('course_creation_id', $ppc->course_creation_id)->where('full_time', 1)->get()->count();
                $res['courses'][$i]['weekdays'] = ApplicantProposedCourse::where('semester_id', $semester_id)->whereIn('applicant_id', $applicants)
                                                   ->where('course_creation_id', $ppc->course_creation_id)->where('full_time', '!=', 1)->get()->count();

                $i++;
            endforeach;
        else:
            $res['courses'] = [];
        endif;

        $res['evening_weekends'] = ApplicantProposedCourse::where('semester_id', $semester_id)->whereIn('applicant_id', $applicants)->where('full_time', 1)->get()->count();
        $res['weekdays'] = ApplicantProposedCourse::where('semester_id', $semester_id)->whereIn('applicant_id', $applicants)->where('full_time', '!=', 1)->get()->count();

        return $res;
    }

    public function printPersonalData($semester_id){
        $user = User::find(auth()->user()->id);
        $semester = Semester::find($semester_id);
        $applicants = ApplicantProposedCourse::where('semester_id', $semester_id)->pluck('applicant_id')->unique()->toArray();

        $no_of_applicants = (!empty($applicants) && count($applicants) > 0 ? count($applicants) : 0);
        $personal_data = $this->applicantsPersonalDetailsAnalysis($semester_id, $applicants);
        $gender = (isset($personal_data['gender']) && !empty($personal_data['gender']) ? $personal_data['gender'] : []);
        $age = (isset($personal_data['age']) && !empty($personal_data['age']) ? $personal_data['age'] : []);
        $avg_age = (isset($personal_data['avg_age']) && $personal_data['avg_age'] > 0 ? $personal_data['avg_age'] : 0);
        $nationality = (isset($personal_data['nationality']) && !empty($personal_data['nationality']) ? $personal_data['nationality'] : []);

        $regNo = Option::where('category', 'SITE')->where('name', 'register_no')->get()->first();
        $regAt = Option::where('category', 'SITE')->where('name', 'register_at')->get()->first();

        $report_title = 'Applicant Personal Data Analysis';
        $PDFHTML = '';
        $PDFHTML .= '<html>';
            $PDFHTML .= '<head>';
                $PDFHTML .= '<title>'.$report_title.'</title>';
                $PDFHTML .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
                $PDFHTML .= '<style>
                                body{font-family: Tahoma, sans-serif; font-size: 13px; line-height: normal; color: #1e293b; padding-top: 10px;}
                                table{margin-left: 0px; width: 100%; border-collapse: collapse;}
                                figure{margin: 0;}
                                @page{margin-top: 110px;margin-left: 85px !important; margin-right:85px !important; }

                                header{position: fixed;left: 0px;right: 0px;height: 80px;margin-top: -90px;}
                                .headerTable tr td{vertical-align: top; padding: 0; line-height: 13px;}
                                .headerTable img{height: 70px; width: auto;}
                                .headerTable tr td.reportTitle{font-size: 16px; line-height: 16px; font-weight: bold;}

                                footer{position: fixed;left: 0px;right: 0px;bottom: 0;height: 100px;margin-bottom: -120px;}
                                .pageCounter{position: relative;}
                                .pageCounter:before{content: counter(page);position: relative;display: inline-block;}
                                .pinRow td{border-bottom: 1px solid gray;}
                                .text-center{text-align: center;}
                                .text-left{text-align: left;}
                                .text-right{text-align: right;}
                                @media print{ .pageBreak{page-break-after: always;} }
                                .pageBreak{page-break-after: always;}
                                
                                .mb-15{margin-bottom: 15px;}
                                .mb-10{margin-bottom: 10px;}
                                .table-bordered th, .table-bordered td {border: 1px solid #e5e7eb;}
                                .table-sm th, .table-sm td{padding: 5px 10px;}
                                .w-1/6{width: 16.666666%;}
                            </style>';
            $PDFHTML .= '</head>';

            $PDFHTML .= '<body>';
                $PDFHTML .= '<header>';
                    $PDFHTML .= '<table class="headerTable">';
                        $PDFHTML .= '<tr>';
                            $PDFHTML .= '<td colspan="2" class="reportTitle">'.$report_title.'</td>';
                            $PDFHTML .= '<td rowspan="3" class="text-right"><img src="https://sms.londonchurchillcollege.ac.uk/sms_new_copy_2/uploads/LCC_LOGO_01_263_100.png" alt="London Churchill College"/></td>';
                        $PDFHTML .= '</tr>';
                        $PDFHTML .= '<tr>';
                            $PDFHTML .= '<td>Semister</td>';
                            $PDFHTML .= '<td>'.(isset($semester->name) && !empty($semester->name) ? $semester->name : '').'</td>';
                        $PDFHTML .= '</tr>';
                        $PDFHTML .= '<tr>';
                            $PDFHTML .= '<td>Cereated By</td>';
                            $PDFHTML .= '<td>';
                                $PDFHTML .= (isset($user->employee->full_name) && !empty($user->employee->full_name) ? $user->employee->full_name : $user->name);
                                $PDFHTML .= '<br/>'.date('jS M, Y').' at '.date('h:i A');
                            $PDFHTML .= '</td>';
                        $PDFHTML .= '</tr>';
                    $PDFHTML .= '</table>';
                $PDFHTML .= '</header>';

                $PDFHTML .= '<table class="table-bordered table-sm mb-15">';
                    $PDFHTML .= '<tr>';
                        $PDFHTML .= '<th colspan="2" class="text-left">Total No of Applicant</th>';
                        $PDFHTML .= '<th class="w-1/6 text-left">'.$no_of_applicants.'</th>';
                    $PDFHTML .= '</tr>';
                    $PDFHTML .= '<tr>';
                        $PDFHTML .= '<th colspan="3" class="text-left">Gender</th>';
                    $PDFHTML .= '</tr>';
                    $PDFHTML .= '<tr>';
                        $PDFHTML .= '<td class="text-left">Male Applicants</td>';
                        $PDFHTML .= '<td class="w-1/6">'.(isset($gender['male']) && $gender['male'] > 0 ? $gender['male'] : 0).'</td>';
                        $PDFHTML .= '<td class="w-1/6">'.(isset($gender['male']) && $gender['male'] > 0 && $no_of_applicants > 0 ? number_format(($gender['male'] / $no_of_applicants) * 100, 2) : 0).'%</td>';
                    $PDFHTML .= '</tr>';
                    $PDFHTML .= '<tr>';
                        $PDFHTML .= '<td class="text-left">Female Applicants</td>';
                        $PDFHTML .= '<td class="w-1/6">'.(isset($gender['female']) && $gender['female'] > 0 ? $gender['female'] : 0).'</td>';
                        $PDFHTML .= '<td class="w-1/6">'.(isset($gender['female']) && $gender['female'] > 0 && $no_of_applicants > 0 ? number_format(($gender['female'] / $no_of_applicants) * 100, 2) : 0).'%</td>';
                    $PDFHTML .= '</tr>';
                    $PDFHTML .= '<tr>';
                        $PDFHTML .= '<td class="text-left">Other Applicants</td>';
                        $PDFHTML .= '<td class="w-1/6">'.(isset($gender['other']) && $gender['other'] > 0 ? $gender['other'] : 0).'</td>';
                        $PDFHTML .= '<td class="w-1/6">'.(isset($gender['other']) && $gender['other'] > 0 && $no_of_applicants > 0 ? number_format(($gender['other'] / $no_of_applicants) * 100, 2) : 0).'%</td>';
                    $PDFHTML .= '</tr>';
                $PDFHTML .= '</table>';

                if(!empty($nationality)):
                $PDFHTML .= '<table class="table-sm table-bordered mb-15">';
                    $PDFHTML .= '<tr>';
                        $PDFHTML .= '<th colspan="3" class="text-left">Nationality</th>';
                    $PDFHTML .= '</tr>';
                    foreach($nationality as $nation):
                        $PDFHTML .= '<tr>';
                            $PDFHTML .= '<td class="text-left">'.(isset($nation['name']) && !empty($nation['name']) ? $nation['name'] : '').'</td>';
                            $PDFHTML .= '<td class="w-1/6">'.(isset($nation['applicants']) && $nation['applicants'] > 0 ? $nation['applicants'] : 0).'</td>';
                            $PDFHTML .= '<td class="w-1/6">'.(isset($nation['applicants']) && $nation['applicants'] > 0 && $no_of_applicants > 0 ? number_format(($nation['applicants'] / $no_of_applicants) * 100, 2) : 0).'%</td>';
                        $PDFHTML .= '</tr>';
                    endforeach;
                $PDFHTML .= '</table>';
                endif;

                $PDFHTML .= '<table class="table-sm table-bordered mb-15">';
                    $PDFHTML .= '<tr>';
                        $PDFHTML .= '<th colspan="3" class="text-left">Age</th>';
                    $PDFHTML .= '</tr>';
                    if(!empty($age)):
                        foreach($age as $label => $ag):
                            $PDFHTML .= '<tr>';
                                $PDFHTML .= '<td class="text-left">Applicants Aged '.$label.'</td>';
                                $PDFHTML .= '<td class="w-1/6">'.$ag.'</td>';
                                $PDFHTML .= '<td class="w-1/6">'.($ag > 0 && $no_of_applicants > 0 ? number_format(($ag / $no_of_applicants) * 100, 2) : 0).'%</td>';
                            $PDFHTML .= '</tr>';
                        endforeach;
                    endif;
                    $PDFHTML .= '<tr>';
                        $PDFHTML .= '<td class="text-left">Mean Application Age</td>';
                        $PDFHTML .= '<td class="w-1/6">'.($avg_age > 0 ? $avg_age : '').'</td>';
                        $PDFHTML .= '<td class="w-1/6">&nbsp;</td>';
                    $PDFHTML .= '</tr>';
                $PDFHTML .= '</table>';
                
            $PDFHTML .= '</body>';
        $PDFHTML .= '</html>';

        $fileName = str_replace(' ', '_', $report_title).'.pdf';
        $pdf = PDF::loadHTML($PDFHTML)->setOption(['isRemoteEnabled' => true])
            ->setPaper('a4', 'portrait')
            ->setWarnings(false);
        return $pdf->download($fileName);
    }
}
