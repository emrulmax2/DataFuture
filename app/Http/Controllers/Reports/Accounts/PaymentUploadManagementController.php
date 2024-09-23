<?php

namespace App\Http\Controllers\Reports\Accounts;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseCreation;
use App\Models\CourseCreationVenue;
use App\Models\SlcAgreement;
use App\Models\SlcInstallment;
use App\Models\SlcMoneyReceipt;
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

            $HTML = '';
            if(!empty($csvData) && count($csvData) > 0):
                $summaryRow = $csvData[0];
                if(strlen($summaryRow[3]) == 7):
                    $trans_date = '0'.substr($summaryRow[3], 0, 1).'-'.substr($summaryRow[3], 1, 2).'-'.substr($summaryRow[3], 3, 4);
                else:
                    $trans_date = substr($summaryRow[3], 0, 2).'-'.substr($summaryRow[3], 2, 2).'-'.substr($summaryRow[3], 4, 4);
                endif;

                $HTML .= '<input type="hidden" name="parent_date" value="'.$trans_date.'"/>';
                $HTML .= '<input type="hidden" name="parent_total" value="'.$summaryRow[5].'"/>';
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
                                        $HTML .= date('d-m-Y', strtotime($dob));
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
                $HTML .= '<div class="pt-5 text-right">';
                    $HTML .= '<button type="submit" id="saveCSVTransBtn" class="btn btn-success text-white">';
                        $HTML .= 'Save Transaction'; 
                        $HTML .= '<svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                    stroke="white" class="w-4 h-4 ml-2">
                                    <g fill="none" fill-rule="evenodd">
                                        <g transform="translate(1 1)" stroke-width="4">
                                            <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                            <path d="M36 18c0-9.94-8.06-18-18-18">
                                                <animateTransform attributeName="transform" type="rotate" from="0 18 18"
                                                    to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                            </path>
                                        </g>
                                    </g>
                                </svg>';
                    $HTML .= '</button>';
                $HTML .= '</div>';
            else:
                $HTML = '<div class="alert alert-danger-soft show flex items-center mb-2" role="alert">
                            <i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Empty csv file uploaded. Please upload a valid .csv file with transactions.
                        </div>';
            endif;
            return response()->json(['htm' => $HTML], 200);
        else:
            $HTML = '<div class="alert alert-danger-soft show flex items-center mb-2" role="alert">
                        <i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> File Not Found. Please upload a valid .csv file.
                    </div>';
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

    public function storeCsvTransactions(Request $request){
        $transaction_date = (isset($request->parent_date) && !empty($request->parent_date) ? date('Y-m-d', strtotime($request->parent_date)) : date('Y-m-d')); 
        $parent_total = (isset($request->parent_total) && $request->parent_total > 0 ? $request->parent_total : 0);
        $transactions = (isset($request->trans) && !empty($request->trans) ? $request->trans : []);

        $receipts = SlcMoneyReceipt::max('invoice_no');
        $invoice = preg_replace('~\D~', '', $receipts);

        if(!empty($transactions)):
            $errorCount = 0;
            $insertCount = 0;
            foreach($transactions as $trans):
                $status = (isset($trans['stats']) && $trans['stats'] > 0) ? $trans['stats'] : 2;
                $error_code = (isset($trans['error_code']) && $trans['error_code'] > 0) ? $trans['error_code'] : 2;
                $errors = (isset($trans['errors']) && !empty($trans['errors'])) ? $trans['errors'] : '';
                $slc_installment_id = (isset($trans['slc_installment_id']) && $trans['slc_installment_id'] > 0 ? $trans['slc_installment_id'] : 0);
                $agreement_id = (isset($trans['agreement_id']) && $trans['agreement_id'] > 0 ? $trans['agreement_id'] : 0);
                $course_relation_id = (isset($trans['course_relation_id']) && $trans['course_relation_id'] > 0 ? $trans['course_relation_id'] : 0);
                $student_id = (isset($trans['student_id']) && $trans['student_id'] > 0 ? $trans['student_id'] : 0);

                $data = [];
                $data['student_id'] = $student_id;
                $data['transaction_date'] = $transaction_date;
                $data['term_name'] = $trans['term_name'];
                $data['ssn'] = $trans['ssn'];
                $data['first_name'] = $trans['first_name'];
                $data['last_name'] = $trans['last_name'];
                $data['dob'] = (isset($trans['dob']) && $trans['dob'] != '' ? date('Y-m-d', strtotime($trans['dob'])) : '');
                $data['course_id'] = $trans['course_id'];
                $data['course_code'] = $trans['course_code'];
                $data['course_name'] = trim(addslashes($trans['course_name']));
                $data['year'] = $trans['year'];
                $data['amount'] = $trans['amount'];
                $data['status'] = $status;
                $data['error_code'] = $error_code;
                $data['errors'] = $errors;
                $data['created_by'] = auth()->user()->id;

                if($status != 1):
                    $slcPayHistory = SlcPaymentHistory::create($data);
                    $errorCount += 1;
                endif;

                if($status == 1):
                    $invoice += 1;
                    $invoice_no = '';
                    $refund_invoice_no = '';
                    if ($trans['amount'] > 0):
                        $invoice_no = $invoice;
                        $payment_type = 'Course Fee';
                    else:
                        $refund_invoice_no = 'R-' . $invoice;
                        $payment_type = 'Refund';
                    endif;

                    $termName = (isset($trans['term_name']) && !empty($trans['term_name']) ? $trans['term_name'] : '');
                    $termType = TermType::where('code', $termName)->get()->first();
                    $termTypeId = (isset($termType->id) && $termType->id > 0 ? $termType->id : 0);
                    $slcInstallment = SlcInstallment::where('id', $slc_installment_id)->get()->first();

                    $data = [];
                    $data['student_id'] = $student_id;
                    $data['student_course_relation_id'] = $course_relation_id;
                    $data['course_creation_instance_id'] = (isset($slcInstallment->course_creation_instance_id) && $slcInstallment->course_creation_instance_id > 0 ? $slcInstallment->course_creation_instance_id : null);
                    $data['slc_agreement_id'] = ($agreement_id > 0 ? $agreement_id : 0);
                    $data['term_declaration_id'] = (isset($slcInstallment->term_declaration_id) && $slcInstallment->term_declaration_id > 0 ? $slcInstallment->term_declaration_id : null);
                    $data['session_term'] = (isset($slcInstallment->session_term) && $slcInstallment->session_term > 0 ? $slcInstallment->session_term : null);
                    $data['invoice_no'] = ($trans['amount'] > 0 ? $invoice_no : $refund_invoice_no);
                    $data['refund_invoice_no'] = ($trans['amount'] > 0 ? null : $refund_invoice_no);
                    $data['slc_coursecode'] = $trans['course_code'];
                    $data['slc_payment_method_id'] = 2;
                    $data['entry_date'] = date('Y-m-d');
                    $data['payment_date'] = $transaction_date;
                    $data['amount'] = $trans['amount'];
                    $data['discount'] = 0;
                    $data['payment_type'] = $payment_type;
                    $data['remarks'] = date('Y-m-d H:i:s').' Bulk Upload';
                    $data['force_entry'] = 0;
                    $data['received_by'] = auth()->user()->id;
                    $data['created_by'] = auth()->user()->id;

                    $moneyReceipt = SlcMoneyReceipt::create($data);
                    if(isset($slcInstallment->id) && $slcInstallment->id > 0 && $moneyReceipt):
                        SlcInstallment::where('id', $slcInstallment->id)->update(['slc_money_receipt_id' => $moneyReceipt->id]);
                    endif;

                    $insertCount += 1;
                endif;
            endforeach;

            $message = ($insertCount > 0 ? '<strong>'.$insertCount.'</strong> Transactions successully inserted as Money Receipt. ' : '');
            $message .= ($errorCount > 0 ? ' Errors found for <span data-transdate="'.date('d-m-Y', strtotime($transaction_date)).'" class="transactionErrors text-primary" style="cursor: pointer;"><strong><u>'.$errorCount.'</u></strong></span> transactions.' : '');
            return response()->json(['msg' => $message], 200);
        else:
            return response()->json(['msg' => 'Transactions not found. Please insert some valid transactions.'], 422);
        endif;
    }
}
