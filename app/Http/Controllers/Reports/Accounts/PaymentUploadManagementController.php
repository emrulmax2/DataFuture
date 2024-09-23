<?php

namespace App\Http\Controllers\Reports\Accounts;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseCreation;
use App\Models\CourseCreationVenue;
use App\Models\SlcAgreement;
use App\Models\SlcInstallment;
use App\Models\SlcPaymentHistory;
use App\Models\Student;
use App\Models\TermType;
use Illuminate\Http\Request;

class PaymentUploadManagementController extends Controller
{
    public function slcPaymentHistoryList(Request $request){
        $dates = (isset($request->date_range) && !empty($request->date_range) ? explode(' - ', $request->date_range) : []);
        $from_date = isset($dates[0]) && !empty($dates[0]) ? date('Y-m-d', strtotime($dates[0])) : date('Y-m-d');
        $to_date = isset($dates[1]) && !empty($dates[1]) ? date('Y-m-d', strtotime($dates[1])) : date('Y-m-d');

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = SlcPaymentHistory::with('student')->orderByRaw(implode(',', $sorts))->whereBetween('transaction_date', [$from_date, $to_date]);

        $total_rows = $query->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 10));
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $Query= $query->skip($offset)
               ->take($limit)
               ->get();

        $data = array();

        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'student_id' => $list->student_id,
                    'term_name' => $list->term_name,
                    'ssn' => $list->ssn,
                    'registration_no' => (isset($list->student->registration_no) && !empty($list->student->registration_no) ? $list->student->registration_no : ''),
                    'dob' => (isset($list->dob) && !empty($list->dob) ? date('d-m-Y', strtotime($list->dob)) : ''),
                    'course_id' => $list->course_id,
                    'course_code' => $list->course_code,
                    'course_name' => $list->course_name,
                    'year' => $list->year,
                    'amount' => $list->amount,
                    'transaction_date' => (isset($list->transaction_date) && !empty($list->transaction_date) ? date('d-m-Y', strtotime($list->transaction_date)) : ''),
                    'status' => $list->status,
                    'errors' => $list->errors,
                    'error_code' => $list->error_code,
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function uploadCSV(Request $request){
        if($request->hasFile('payment_file_csv')):
            $csv_doc = $request->file('payment_file_csv');
            $csvTmpPath = $csv_doc->getPathname();

            $csvData = [];
            $theCSVFile = fopen($csvTmpPath, 'r');
            while (($line = fgetcsv($theCSVFile)) !== FALSE) {
                $csvData[] = $line;
            }
            fclose($theCSVFile);

            //return response()->json($csvData);

            $THHTML = '';
            $HTML = '';
            if(!empty($csvData) && count($csvData) > 0):
                $summaryRow = $csvData[0];
                if(strlen($summaryRow[3]) == 7):
                    $trans_date = '0'.substr($summaryRow[3], 0, 1).'-'.substr($summaryRow[3], 1, 2).'-'.substr($summaryRow[3], 3, 4);
                else:
                    $trans_date = substr($summaryRow[3], 0, 2).'-'.substr($summaryRow[3], 2, 2).'-'.substr($summaryRow[3], 4, 4);
                endif;
                $HTML .= '<table class="table table-bordered table-sm mb-3">';
                    $HTML .= '<thead>';
                        $HTML .= '<tr><th>Name</th><th>No of Transactions</th><th>Date</th><th>Total</th></tr>';
                        $HTML .= '<tr>';
                            $HTML .= '<td>'.$summaryRow[1].'</td>';
                            $HTML .= '<td>'.$summaryRow[2].'</td>';
                            $HTML .= '<td>'.date('d-m-Y', strtotime($trans_date)).'</td>';
                            $HTML .= '<td>'.$summaryRow[5].'</td>';
                        $HTML .= '</tr>';
                    $HTML .= '</thead>';
                $HTML .= '</table>';

                $HTML .= '<table class="table table-bordered table-sm">';
                    $HTML .= '<thead>';
                        $HTML .= '<tr>';
                            $HTML .= '<th>#</th>';
                            $HTML .= '<th>Term Name</th>';
                            $HTML .= '<th>LCC ID</th>';
                            $HTML .= '<th>SSN</th>';
                            $HTML .= '<th>Student Name</th>';
                            $HTML .= '<th>DOB</th>';
                            $HTML .= '<th>Course</th>';
                            $HTML .= '<th>Academic Year</th>';
                            $HTML .= '<th>Amount</th>';
                        $HTML .= '</tr>';
                    $HTML .= '</thead>';
                    $HTML .= '<tbody>';
                        $r = 1;
                        foreach($csvData as $row):
                            if($r > 1):
                                $term = $row[0];
                                $ssn = $row[1];
                                $first_name = $row[2];
                                $last_name = $row[3];
                                $course_code = $row[6];
                                $course_name = $row[7];
                                $year = $row[8];
                                $amount = $row[10];

                                if(strlen($row[4]) == 7):
                                    $dob = '0'.substr($row[4], 0, 1).'-'.substr($row[4], 1, 2).'-'.substr($row[4], 3, 4);
                                    $dob = date('Y-m-d', strtotime($dob));
                                else:
                                    $dob = substr($row[4], 0, 2).'-'.substr($row[4], 2, 2).'-'.substr($row[4], 4, 4);
                                    $dob = date('Y-m-d', strtotime($dob));
                                endif;
                                $student = Student::where('ssn_no', $ssn)->where('date_of_birth', $dob)->orderBy('id', 'DESC')->get()->first();
                                $student_course_id = (isset($student->activeCR->creation->course_id) && $student->activeCR->creation->course_id > 0 ? $student->activeCR->creation->course_id : false);
                                $student_course_relation_id = (isset($student->activeCR->id) && $student->activeCR->id > 0 ? $student->activeCR->id : 0);
                                $courseCreationIds = CourseCreationVenue::where('slc_code', $course_code)->pluck('course_creation_id')->unique()->toArray();
                                $courseIds = (!empty($courseCreationIds) ? CourseCreation::whereIn('id', $courseCreationIds)->pluck('course_id')->unique()->toArray() : []);
                                $courseId = (isset($courseIds[0]) && $courseIds[0] > 0 ? $courseIds[0] : '');

                                $errors = array();
                                $tr_class = $term_class = $ssn_class = $dob_class = $course_class = '';
                                $checked = '';
                                $disabled = '';
                                $labels = '';
                                $error = false;
                                $errorCode = '';
                                $exist_installment_ID = 0;
                                $agreement_id = 0;
                                $course_relation_id = 0;

                                if(isset($student->id) && $student->id > 0 && ($student_course_id && $student_course_id == $courseId)):
                                    $exist_installment = $this->get_exist_installment($student->id, $year, $term, $courseId, $student_course_relation_id);
                                    if($exist_installment){
                                        $exist_installment_ID = (isset($exist_installment->id) && $exist_installment->id > 0 ? $exist_installment->id : 0);
                                        $agreement_id = (isset($exist_installment->slc_agreement_id) && $exist_installment->slc_agreement_id > 0 ? $exist_installment->slc_agreement_id : 0);
                                        $course_relation_id = (isset($exist_installment->student_course_relation_id) && $exist_installment->student_course_relation_id > 0 ? $exist_installment->student_course_relation_id : 0);
                                        $tr_class = 'match_found';
                                        $checked = 'checked="checked"';
                                        $errorCode = 1;
                                    }else{
                                        $term_class = 'font-medium';
                                        $tr_class = 'match_not_found';
                                        $checked = '';
                                        $labels = 'Installment Not Found.';
                                        $error = true;
                                        $errorCode = 2;
                                        //$disabled = ' disabled="disabled" ';
                                    }
                                elseif(!isset($student->id)):
                                    $ssn_class = 'font-medium';
                                    $dob_class = 'font-medium';
                                    $tr_class = 'match_not_found';
                                    $checked = '';
                                    $disabled = ' disabled="disabled" ';
                                    $labels = 'Student Not Found. Please check SSN number or Date of Birth.';
                                    $error = true;
                                    $errorCode = 3;
                                elseif((isset($student->id) && $student->id > 0) && ($courseId == '' || $student_course_id != $courseId)):
                                    $course_class = 'font-medium';
                                    $tr_class = 'match_not_found';
                                    $checked = '';
                                    $labels = 'Course Does not Match with existing student course.';
                                    $error = true;
                                    $errorCode = 4;
                                    //$disabled = ' disabled="disabled" ';
                                endif;

                                $HTML .= '<tr class="'.$tr_class.'" style="'.($error ? 'background: #f2dede; border-bottom-color: #ebccd1;' : '').'">';
                                    $HTML .= '<td>';
                                        $HTML .= '<div class="form-check m-0"><input '.$disabled.' '.$checked.' name="trans['.$r.'][stats]" id="trans_row_'.$r.'" class="form-check-input m-0" type="checkbox" value="1"></div>';
                                    $HTML .= '</td>';
                                    $HTML .= '<td class="'.$term_class.'">';
                                        $HTML .= $term;
                                        $HTML .= '<input type="hidden" name="trans['.$r.'][term_name]" value="'.$term.'"/>';
                                    $HTML .= '</td>';
                                    $HTML .= '<td>';
                                        $HTML .= (isset($student->registration_no) && !empty($student->registration_no) ? $student->registration_no : '');
                                        $HTML .= '<input type="hidden" name="trans['.$r.'][student_id]" value="'.(isset($student->id) && $student->id > 0 ? $student->id : 0).'"/>';
                                    $HTML .= '</td>';
                                    $HTML .= '<td class="'.$ssn_class.'">';
                                        $HTML .= $ssn;
                                        $HTML .= '<input type="hidden" name="trans['.$r.'][ssn]" value="'.$ssn.'"/>';
                                    $HTML .= '</td>';
                                    $HTML .= '<td>';
                                        $HTML .= $first_name.' '.$last_name;
                                        $HTML .= '<input type="hidden" name="trans['.$r.'][first_name]" value="'.$first_name.'"/>';
                                        $HTML .= '<input type="hidden" name="trans['.$r.'][last_name]" value="'.$last_name.'"/>';
                                    $HTML .= '</td>';
                                    $HTML .= '<td class="'.$dob_class.'">';
                                        $HTML .= $dob;
                                        $HTML .= '<input type="hidden" name="trans['.$r.'][dob]" value="'.date('Y-m-d', strtotime($dob)).'"/>';
                                    $HTML .= '</td>';
                                    $HTML .= '<td>';
                                        $HTML .= '<span class="tooltip" title="'.$course_name.'">'.($courseId > 0 ? $courseId : '---').' / '.$course_code.'</span>';
                                        $HTML .= '<input type="hidden" name="trans['.$r.'][course_id]" value="'.$courseId.'"/>';
                                        $HTML .= '<input type="hidden" name="trans['.$r.'][course_code]" value="'.$course_code.'"/>';
                                        $HTML .= '<input type="hidden" name="trans['.$r.'][course_name]" value="'.$course_name.'"/>';
                                    $HTML .= '</td>';
                                    $HTML .= '<td>';
                                        $HTML .= $year;
                                        $HTML .= '<input type="hidden" name="trans['.$r.'][year]" value="'.$year.'"/>';
                                    $HTML .= '</td>';
                                    $HTML .= '<td>';
                                        $HTML .= $amount;
                                        $HTML .= '<input type="hidden" name="trans['.$r.'][amount]" value="'.$amount.'"/>';
                                        $HTML .= '<input type="hidden" name="trans['.$r.'][slc_installment_id]" value="'.$exist_installment_ID.'"/>';
                                        $HTML .= '<input type="hidden" name="trans['.$r.'][agreement_id]" value="'.$agreement_id.'"/>';
                                        $HTML .= '<input type="hidden" name="trans['.$r.'][course_relation_id]" value="'.$course_relation_id.'"/>';
                                        $HTML .= '<input type="hidden" name="trans['.$r.'][errors]" value="'.$labels.'"/>';
                                        $HTML .= '<input type="hidden" name="trans['.$r.'][error_code]" value="'.$errorCode.'"/>';
                                    $HTML .= '</td>';
                                $HTML .= '</tr>';
                                
                                if($error && !empty($labels)):
                                    $HTML .= '<tr class="'.$tr_class.'" style="background: '.($error ? '#ebccd1;' : '#FFF').'">';
                                        $HTML .= '<td colspan="9" class="text-center font-medium text-danger">'.$labels.'</td>';
                                    $HTML .= '</tr>';
                                endif;
                            endif;
                            $r++;
                        endforeach;
                    $HTML .= '</tbody>';
                $HTML .= '</table>';
            endif;

            return response()->json(['htm' => $HTML], 200);
        endif;
    }

    public function get_exist_installment($student_id, $year, $term, $courseId, $student_course_relation_id){
        if($student_id > 0 && $year > 0 && !empty($term) && $courseId > 0):
            $termType = TermType::where('code', $term)->get()->first();
            $termTypeId = (isset($termType->id) && $termType->id > 0 ? $termType->id : 0);
            $agreement = SlcAgreement::where('student_id', $student_id)->where('student_course_relation_id', $student_course_relation_id)->where('year', $year)->orderBy('id', 'DESC')->get()->first();
            if(isset($agreement->id) && $agreement->id > 0):
                $inst = SlcInstallment::where('slc_agreement_id', $agreement->id)->where('term_type_id', $termTypeId)->where('student_id', $student_id)->orderBy('id', 'DESC')->get()->first();
                return (isset($inst->id) && $inst->id > 0 ? $inst : false);
            else:
                return false;
            endif;
        else:
            return false;
        endif;
    }
}
