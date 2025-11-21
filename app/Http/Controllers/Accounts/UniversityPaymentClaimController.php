<?php

namespace App\Http\Controllers\Accounts;

use App\Http\Controllers\Controller;
use App\Http\Requests\UniversityClaimStoreRequest;
use App\Models\AccAssetRegister;
use App\Models\AccBank;
use App\Models\AccCategory;
use App\Models\CourseCreation;
use App\Models\CourseCreationInstance;
use App\Models\CourseCreationVenue;
use App\Models\Semester;
use App\Models\SlcAgreement;
use App\Models\SlcInstallment;
use App\Models\Student;
use App\Models\StudentCourseRelation;
use App\Models\StudentProposedCourse;
use App\Models\TermDeclaration;
use App\Models\UniversityPaymentClaim;
use App\Models\UniversityPaymentClaimStudent;
use App\Models\Vendor;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;
use Illuminate\Support\Number;

class UniversityPaymentClaimController extends Controller
{
    public function index(){
        $audit_status = (auth()->user()->remote_access && isset(auth()->user()->priv()['access_account_type']) && auth()->user()->priv()['access_account_type'] == 3 ? ['1'] : ['0', '1']);
        return view('pages.accounts.university-claims', [
            'title' => 'Accounts - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'University Claims', 'href' => 'javascript:void(0);']
            ],
            'semesters' => Semester::orderBy('id', 'desc')->get(),
            'vendors' => Vendor::where('vendor_for', 2)->orderBy('name', 'asc')->get(),
            'banks' => AccBank::where('status', '1')->orderBy('bank_name', 'asc')->get(),
            'term_declarations' => TermDeclaration::orderBy('id', 'DESC')->get(),
        ]);
    }

    public function studentList(Request $request){
        $semester_id = (isset($request->semester_id) && !empty($request->semester_id) ? $request->semester_id : 0);
        $course_id = (isset($request->course_id) && !empty($request->course_id) ? $request->course_id : 0);
        $courseCreationIds = CourseCreation::where('semester_id', $semester_id)->where('course_id', $course_id)->pluck('id')->unique()->toArray();
        $studentCrelIds = StudentCourseRelation::whereIn('course_creation_id', $courseCreationIds)->where('active', 1)->pluck('id')->unique()->toArray();
        $studentIds = StudentCourseRelation::whereIn('course_creation_id', $courseCreationIds)->where('active', 1)->pluck('student_id')->unique()->toArray();


        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = DB::table('slc_installments as inst')
                ->select(
                        'inst.*', 
                        'std.id as the_student_id', 'std.first_name', 'std.last_name', 'std.registration_no',
                        'sem.name as semester_name', 'cr.name as course_name',
                        'sts.name as status_name'
                )
                ->leftJoin('students as std', 'std.id', 'inst.student_id')
                ->leftJoin('student_course_relations as scr', function ($join) use($studentCrelIds) {
                    $join->on('scr.student_id', '=', 'std.id')
                        ->where('scr.active', '=', 1)->whereIn('scr.id', $studentCrelIds);
                })
                ->leftJoin('course_creations as cc', 'scr.course_creation_id', 'cc.id')
                ->leftJoin('semesters as sem', 'sem.id', 'cc.semester_id')
                ->leftJoin('courses as cr', 'cr.id', 'cc.course_id')
                ->leftJoin('statuses as sts', 'sts.id', 'std.status_id')
                ->whereDate('inst.installment_date', '<=', now()->toDateString())
                ->whereIn('inst.student_course_relation_id', $studentCrelIds)
                ->whereIn('inst.student_id', $studentIds)
                ->whereNotExists(function ($q) {
                    $q->select('*')
                        ->from('university_payment_claim_students as upcs')
                        ->whereColumn('upcs.slc_installment_id', 'inst.id')
                        ->whereIn('upcs.status', [1, 2]);  // EXCLUDE status 1 & 2
                });

        // $query = Student::orderByRaw(implode(',', $sorts))->whereHas('activeCR', function($q) use($courseCreationIds){
        //     $q->whereIn('course_creation_id', $courseCreationIds);
        // });

        $total_rows = $query->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 50));
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
                    'amount_html' => (isset($list->amount) && !empty($list->amount) ? Number::currency($list->amount, 'GBP') : Number::currency(0, 'GBP')),
                    'amount' => (isset($list->amount) && !empty($list->amount) ? $list->amount : 0),
                    'installment_date' => (isset($list->installment_date) && !empty($list->installment_date) ? date('d-m-Y', strtotime($list->installment_date)) : ''),
                    'student_id' => $list->student_id,
                    'full_name' => $list->first_name.' '.$list->last_name,
                    'registration_no' => $list->registration_no,
                    'semester' => (isset($list->semester_name) && !empty($list->semester_name) ? $list->semester_name : ''),
                    'course' => (isset($list->course_name) && !empty($list->course_name) ? $list->course_name : ''),
                    'status' => (isset($list->status_name) && !empty($list->status_name) ? $list->status_name : '')
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function generateProformaNo(){
        $lastClaim = UniversityPaymentClaim::whereNotNull('proforma_no')->orderBy('id', 'desc')->first();
        if ($lastClaim) {
            $nextNumber = (int) $lastClaim->proforma_no + 1;
        } else {
            $nextNumber = 90001;
        }
        return str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }

    public function store(UniversityClaimStoreRequest $request){
        try {
            $claims = DB::transaction(function () use ($request) {
                $student_ids = (!empty($request->student_ids) ? explode(',', $request->student_ids) : []);
                $slc_installment_ids = (!empty($request->slc_installment_ids) ? explode(',', $request->slc_installment_ids) : []);
                $semester_id = (!empty($request->semester_id) ? $request->semester_id : null);
                $course_id = (!empty($request->course_id) ? $request->course_id : null);
                $claim = UniversityPaymentClaim::create([
                    'proforma_no' => $this->generateProformaNo(),
                    'semester_id' => $semester_id,
                    'course_id' => $course_id,
                    'vendor_id' => $request->vendor_id,
                    'claim_date' => !empty($request->claim_date) ? date('Y-m-d', strtotime($request->claim_date)) : null,
                    'acc_bank_id' => $request->acc_bank_id,
                    'created_by' => auth()->user()->id,
                ]);

                if(!empty($slc_installment_ids)):
                    foreach($slc_installment_ids as $installment_id):
                        $slcInstallment = SlcInstallment::find($installment_id);
                        UniversityPaymentClaimStudent::create([
                            'university_payment_claim_id' => $claim->id,
                            'student_id' => $slcInstallment->student_id,
                            'slc_installment_id' => $slcInstallment->id,
                            'status' => 1,
                        ]);
                    endforeach;
                endif;

                return $claim;
            });

            return response()->json([
                'success' => true,
                'message' => 'University payment claim successfully submitted.',
                'data'    => $claims,
                'red' => route('university.claims.invoices')
            ], 200);

        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again later or contact with the administrator.',
                'error'   => $e->getMessage(), 
            ], 500);
        }
    }

    public function invoices(){
        return view('pages.accounts.university-claims.invoices', [
            'title' => 'Accounts - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'University Claims', 'href' => 'javascript:void(0);'],
                ['label' => 'Invoices', 'href' => 'javascript:void(0);']
            ]
        ]);
    }

    public function invoicesList(Request $request){
        $queryStr = (isset($request->queryStr) && !empty($request->queryStr) ? $request->queryStr : '');
        $status = (isset($request->status) && !empty($request->status) ? $request->status : '');
        
        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = UniversityPaymentClaim::with(['semester', 'course', 'vendor', 'bank'])->orderByRaw(implode(',', $sorts));
        if(!empty($queryStr)):
            $query->where(function($q) use($queryStr){
                $q->where('invoice_no', 'LIKE', '%'.$queryStr.'%');
            });
        endif;

        $total_rows = $query->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 50));
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
                    'invoice_no' => (!empty($list->invoice_no) ? $list->invoice_no : ''),
                    'proforma_no' => (!empty($list->proforma_no) ? $list->proforma_no : ''),
                    'semester' => (isset($list->semester->name) ? $list->semester->name : ''),
                    'course' => (isset($list->semester->course) ? $list->semester->course : ''),
                    'vendor_name' => (isset($list->vendor->name) ? $list->vendor->name : ''),
                    'vendor_email' => (isset($list->vendor->email) ? $list->vendor->email : ''),
                    'vendor_phone' => (isset($list->vendor->phone) ? $list->vendor->phone : ''),
                    'bank_name' => (isset($list->bank->bank_name) ? $list->bank->bank_name : ''),
                    'ac_name' => (isset($list->bank->ac_name) ? $list->bank->ac_name : ''),
                    'sort_code' => (isset($list->bank->sort_code) ? $list->bank->sort_code : ''),
                    'ac_number' => (isset($list->bank->ac_number) ? $list->bank->ac_number : ''),
                    'no_of_students' => (isset($list->installments) && $list->installments->count() > 0 ? $list->installments->count() : 0),
                    'proforma_total' => (isset($list->proforma_total) && $list->proforma_total > 0 ? Number::currency($list->proforma_total, 'GBP') : Number::currency(0, 'GBP')),
                    'invoice_total' => (isset($list->invoice_total) && $list->invoice_total > 0 ? Number::currency($list->invoice_total, 'GBP') : Number::currency(0, 'GBP')),
                    'status' => $list->status,
                    'url' => route('university.claims.invoices.show', $list->id),
                    'created_at' => (!empty($list->claim_date) ? date('F d, Y', strtotime($list->claim_date)) : ''),
                    'invoiced_at' => (!empty($list->invoiced_at) ? date('F d, Y', strtotime($list->invoiced_at)) : ''),
                    'invoiced_by' => (isset($list->invoiced->employee->full_name) && !empty($list->invoiced->employee->full_name) ? $list->invoiced->employee->full_name : (isset($list->invoiced->name) ? $list->invoiced->name : ''))
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function proformaDownload($id){
        $claim = UniversityPaymentClaim::with(['semester', 'course', 'vendor', 'bank', 'installments', 'installments.student'])->find($id);
        
        $logoPath = resource_path('images/red_logo.png');
        $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        $report_title = 'Proforma Invoice of '.$claim->proforma_no;

        $VIEW = 'pages.accounts.university-claims.proforma-pdf';
        $fileName = $claim->proforma_no.'.pdf';
        $pdf = Pdf::loadView($VIEW, compact('claim', 'logoBase64', 'report_title'))
            ->setOption(['isRemoteEnabled' => true])
            ->setPaper('a4', 'portrait') //portrait landscape
            ->setWarnings(false);
        return $pdf->download($fileName);
    }

    public function invoiceDownload($id){
        $claim = UniversityPaymentClaim::with(['semester', 'course', 'vendor', 'bank', 'installments', 'installments.student'])->find($id);
        
        $logoPath = resource_path('images/red_logo.png');
        $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        $report_title = 'Invoice of '.$claim->invoice_no;

        $VIEW = 'pages.accounts.university-claims.invoices-pdf';
        $fileName = $claim->invoice_no.'.pdf';
        $pdf = Pdf::loadView($VIEW, compact('claim', 'logoBase64', 'report_title'))
            ->setOption(['isRemoteEnabled' => true])
            ->setPaper('a4', 'portrait') //portrait landscape
            ->setWarnings(false);
        return $pdf->download($fileName);
    }

    public function generateInvoiceNo(){
        $last = UniversityPaymentClaim::whereNotNull('invoice_no')
            ->orderBy('invoice_no', 'desc')
            ->first();

        $next = ($last && is_numeric($last->invoice_no)) ? $last->invoice_no + 1 : 20001;
        return $next;
    }

    public function invoiceStore(Request $request){
        $university_payment_claim_id = $request->university_payment_claim_id;
        $university_payment_claim_student_ids = (isset($request->university_payment_claim_student_ids) && !empty($request->university_payment_claim_student_ids) ? $request->university_payment_claim_student_ids : []);

        try {
            DB::transaction(function () use ($university_payment_claim_id, $university_payment_claim_student_ids) {
                $claim = UniversityPaymentClaim::findOrFail($university_payment_claim_id);
                if (empty($claim->invoice_no)) {
                    $claim->invoice_no   = $this->generateInvoiceNo();
                    $claim->status       = 2;
                    $claim->invoiced_at  = now();
                    $claim->invoiced_by  = auth()->user()->id;
                    $claim->save();
                }

                if (!empty($university_payment_claim_student_ids)) {
                    UniversityPaymentClaimStudent::where('university_payment_claim_id', $university_payment_claim_id)
                        ->whereIn('id', $university_payment_claim_student_ids)
                        ->update(['status' => 2]);
                    UniversityPaymentClaimStudent::where('university_payment_claim_id', $university_payment_claim_id)
                        ->whereNotIn('id', $university_payment_claim_student_ids)
                        ->update(['status' => 3]);
                } else {
                    UniversityPaymentClaimStudent::where('university_payment_claim_id', $university_payment_claim_id)
                        ->update(['status' => 3]);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Invoice successfully processed.',
                'red' => route('university.claims.invoices')
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again later of contact with the administrator.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function invoiceShow($id){
        return view('pages.accounts.university-claims.details', [
            'title' => 'Accounts - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'University Claims', 'href' => route('university.claims')],
                ['label' => 'Details', 'href' => 'javascript:void(0);']
            ],
            'claim' => UniversityPaymentClaim::with(['semester', 'course', 'vendor', 'bank', 'installments'])->withCount([
                            'installments as paid_installments_count' => function ($q) {
                                $q->where('status', 2);
                            }
                        ])->find($id),
            'term_declarations' => TermDeclaration::orderBy('id', 'DESC')->get()
        ]);
    }

    public function getCourses(Request $request){
        $semester_id = $request->semester_id;
        $courses = CourseCreation::with(['course:id,name'])
                    ->where('semester_id', $semester_id)
                    ->get()->pluck('course');
        return response()->json(['rows' => $courses], 200);
    }

    public function getInstances(Request $request){
        $semester_id = $request->semester_id;
        $course_id = $request->course_id;

        $instances = CourseCreationInstance::with('year')->whereHas('creation', function($q) use($semester_id, $course_id){
            $q->where('semester_id', $semester_id)->where('course_id', $course_id);
        })->orderBy('id', 'DESC')->get();

        $res = [];
        $i = 1;
        if($instances->count() > 0):
            foreach($instances as $inst):
                $res[$i]['id'] = $inst->id;
                $res[$i]['name'] = $inst->year->name;

                $i++;
            endforeach;
        endif;

        return response()->json(['rows' => $res], 200);
    }

    public function getInstance(Request $request){
        $instance = CourseCreationInstance::with('creation')->find($request->course_creation_instance_id);

        $commissionPercent = 0;
        if(isset($instance->university_commission) && $instance->university_commission > 0):
            $commissionPercent = $instance->university_commission;
        elseif(isset($instance->creation->university_commission) && $instance->creation->university_commission > 0):
            $commissionPercent = $instance->creation->university_commission;
        endif;
        $totalFees = (isset($instance->fees) && $instance->fees > 0 ? $instance->fees : 0);
        $commission = ($totalFees * $commissionPercent) / 100;
        $fees = $totalFees - $commission;

        return response()->json(['fees' => $fees, 'commission' => $commission, 'percentage' => $commissionPercent], 200);
    }

    public function bulkAgreement(){
        return view('pages.accounts.university-claims.bulk-agreement', [
            'title' => 'Accounts - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'University Claims', 'href' => 'javascript:void(0);'],
                ['label' => 'Bulk Agreement', 'href' => 'javascript:void(0);']
            ],
            'semesters' => Semester::orderBy('id', 'desc')->get(),
            'term_declarations' => TermDeclaration::orderBy('id', 'DESC')->get(),
        ]);
    }

    public function agreementStudentList(Request $request){
        $semester_id = (isset($request->semester_id) && !empty($request->semester_id) ? $request->semester_id : 0);
        $course_id = (isset($request->course_id) && !empty($request->course_id) ? $request->course_id : 0);
        $courseCreationIds = CourseCreation::where('semester_id', $semester_id)->where('course_id', $course_id)->pluck('id')->unique()->toArray();


        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = Student::orderByRaw(implode(',', $sorts))->whereHas('activeCR', function($q) use($courseCreationIds){
            $q->whereIn('course_creation_id', $courseCreationIds);
        });

        $total_rows = $query->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 50));
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
                    'registration_no' => $list->registration_no,
                    'full_name' => $list->full_name,
                    'semester' => (isset($list->activeCR->semester->name) && !empty($list->activeCR->semester->name) ? $list->activeCR->semester->name : ''),
                    'course' => (isset($list->activeCR->course->name) && !empty($list->activeCR->course->name) ? $list->activeCR->course->name : ''),
                    'status' => $list->status->name
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function createBulkAgreement(Request $request){
        $student_ids = (isset($request->student_ids) && !empty($request->student_ids) ? explode(',', $request->student_ids) : []);
        $semester_id = (isset($request->semester_id) && !empty($request->semester_id) ? $request->semester_id : 0);
        $course_id = (isset($request->course_id) && !empty($request->course_id) ? $request->course_id : 0);
        $ccids = CourseCreation::where('semester_id', $semester_id)->where('course_id', $course_id)->pluck('id')->unique()->toArray();

        $installment_date = (isset($request->installment_date) && !empty($request->installment_date) ? $request->installment_date : []);
        $term_declaration_id = (isset($request->term_declaration_id) && !empty($request->term_declaration_id) ? $request->term_declaration_id : []);
        $session_term = (isset($request->session_term) && !empty($request->session_term) ? $request->session_term : []);
        $amounts = (isset($request->amounts) && !empty($request->amounts) ? $request->amounts : []);
        $scrids = StudentCourseRelation::whereIn('student_id', $student_ids)->whereIn('course_creation_id', $ccids)->where('active', 1)->pluck('id')->unique()->toArray();

        $date = (!empty($request->date) ? date('Y-m-d', strtotime($request->date)) : null);
        $year = (!empty($request->year) ? $request->year : null);
        $course_creation_instance_id = (!empty($request->course_creation_instance_id) ? $request->course_creation_instance_id : null);
        $fees = (!empty($request->fees) ? $request->fees : 0);

        $agreementExist = SlcAgreement::whereIn('student_id', $student_ids)->where('year', $year)
                        ->whereIn('student_course_relation_id', $scrids)->pluck('student_id')->unique()->toArray();
        if(!empty($agreementExist)):
            $studentRegs = Student::whereIn('id', $agreementExist)->pluck('registration_no')->unique()->toArray();
            return response()->json([
                'success' => false,
                'message' => "'".implode(', ', $studentRegs)."' These students already has Year ".$year." agreements.",
                'red' => ''
            ], 500);
        endif;

        $students = Student::whereIn('id', $student_ids)->get();
        if($students->count() > 0):
            foreach($students as $student):
                $currentCourse = StudentProposedCourse::with('venue')->where('student_id',$student->id)
                                ->where('course_creation_id', $student->activeCR->course_creation_id)
                                ->where('student_course_relation_id', $student->activeCR->id)
                                ->get()
                                ->first();
                $venue_id = (isset($currentCourse->venue_id) && $currentCourse->venue_id > 0 ? $currentCourse->venue_id : 0);
                $CourseCreationVenue = CourseCreationVenue::where('course_creation_id', $student->activeCR->course_creation_id)->where('venue_id', $venue_id)->get()->first();
                
                $agreementData = [];
                $agreementData['student_id'] = $student->id;
                $agreementData['student_course_relation_id'] = $student->activeCR->id;
                $agreementData['course_creation_instance_id'] = $course_creation_instance_id;
                $agreementData['slc_registration_id'] = null;
                $agreementData['slc_coursecode'] = (isset($CourseCreationVenue->slc_code) && !empty($CourseCreationVenue->slc_code) ? $CourseCreationVenue->slc_code : null);
                $agreementData['is_self_funded'] = 0;
                $agreementData['date'] = $date;
                $agreementData['year'] = $year;
                $agreementData['commission_amount'] = (isset($request->commission_amount) && $request->commission_amount > 0 ? $request->commission_amount : 0);
                $agreementData['fees'] = $fees;
                $agreementData['discount'] = 0;
                $agreementData['total'] = $fees;
                $agreementData['note'] = (!empty($request->note) ? $request->note : null);
                $agreementData['created_by'] = auth()->user()->id;

                $slcAgreement = SlcAgreement::create($agreementData);

                if($slcAgreement->id && !empty($installment_date)):
                    foreach($installment_date as $key => $date):
                        $installmentData = [];
                        $installmentData['student_id'] = $student->id;
                        $installmentData['student_course_relation_id'] = $student->activeCR->id;
                        $installmentData['course_creation_instance_id'] = $course_creation_instance_id;
                        $installmentData['slc_attendance_id'] = null;
                        $installmentData['slc_agreement_id'] = $slcAgreement->id;
                        $installmentData['installment_date'] = (isset($installment_date[$key]) && !empty($installment_date[$key]) ? date('Y-m-d', strtotime($installment_date[$key])) : null);
                        $installmentData['amount'] = (isset($amounts[$key]) && !empty($amounts[$key]) ? $amounts[$key] : 0);
                        $installmentData['session_term'] = (isset($session_term[$key]) && !empty($session_term[$key]) ? $session_term[$key] : 0);
                        $installmentData['term_declaration_id'] = (isset($term_declaration_id[$key]) && !empty($term_declaration_id[$key]) ? $term_declaration_id[$key] : null);
                        $installmentData['created_by'] = auth()->user()->id;

                        $installment = SlcInstallment::create($installmentData);
                    endforeach;
                endif;
            endforeach;

            return response()->json([
                'success' => true,
                'message' => 'Bulk agreement successfylly created for selected students.',
                'red' => ''
            ], 200);
        else:
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again later or contact with the administrator.',
                'red' => ''
            ], 500);
        endif;
    }
}
