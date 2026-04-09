<?php
namespace App\Exports;

use App\Models\Assign;
use App\Models\Plan;
use App\Models\SlcAgreement;
use App\Models\SlcInstallment;
use App\Models\Student;
use App\Models\TermDeclaration;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class ActiveStudentsExport implements FromQuery, WithHeadings, WithMapping, WithChunkReading
{
    protected $date;

    public function __construct($date)
    {
        $this->date = $date;
    }

    public function query()
    {
        $termDeclaration = TermDeclaration::where('start_date', '<=', $this->date)->where('end_date', '>=', $this->date)->first();
        $student_ids = [0];
        if(isset($termDeclaration->id) && $termDeclaration->id > 0):
            $plan_ids = Plan::where('term_declaration_id', $termDeclaration->id)->pluck('id')->unique()->toArray();
            $student_ids = (!empty($plan_ids) ? Assign::whereIn('plan_id', $plan_ids)->pluck('student_id')->unique()->toArray() : []);
        endif;
        $inactiveStatuses = [14,16,17,21,22,26,27,30,31,33,36,42,43,45,46,47];

        $latestStatusSub = DB::table('student_attendance_term_statuses as s1')
            ->select('s1.student_id', 's1.status_id')
            ->whereRaw('s1.id = (
                SELECT MAX(s2.id)
                FROM student_attendance_term_statuses s2
                WHERE s2.student_id = s1.student_id
                AND s2.created_at <= ?
            )', [$this->date]);

        return DB::table('students')
            ->joinSub($latestStatusSub, 'latest_status', function ($join) {
                $join->on('students.id', '=', 'latest_status.student_id');
            })
            ->leftJoin('titles', 'students.title_id', '=', 'titles.id')
            ->leftJoin('statuses', 'students.status_id', '=', 'statuses.id')

            // JOIN extra tables instead of Eloquent (NO N+1)
            ->leftJoin('student_course_relations as cr', function ($join) {
                $join->on('students.id', '=', 'cr.student_id')
                     ->where('cr.active', 1);
            })
            ->leftJoin('course_creations as cc', 'cr.course_creation_id', '=', 'cc.id')
            ->leftJoin('student_proposed_courses as pr', 'cr.id', '=', 'pr.student_course_relation_id')
            ->leftJoin('semesters', 'pr.semester_id', '=', 'semesters.id')
            ->leftJoin('courses', 'cc.course_id', '=', 'courses.id')
            ->leftJoin('student_other_details as so', 'students.id', '=', 'so.student_id')

            ->whereNotIn('latest_status.status_id', $inactiveStatuses)
            ->whereIn('students.id', $student_ids)

            ->select(
                'students.id',
                'students.registration_no',
                'students.first_name',
                'students.last_name',
                DB::raw('COALESCE(pr.full_time, 0) as full_time'),
                DB::raw('COALESCE(so.disability_status, 0) as disability'),
                DB::raw('(SELECT CASE WHEN COUNT(DISTINCT sa.id) > 1 THEN 1 ELSE 0 END
                 FROM slc_agreements sa
                 WHERE sa.student_id = students.id
                   AND sa.student_course_relation_id = COALESCE(cr.id,0)
                   AND sa.deleted_at IS NULL
                ) as multi_agreement'),
                DB::raw("
                    CASE
                        WHEN NOT EXISTS (
                            SELECT 1
                            FROM slc_agreements sa
                            WHERE sa.student_id = students.id
                            AND sa.student_course_relation_id = cr.id
                            AND sa.deleted_at IS NULL
                        ) THEN 1

                        WHEN EXISTS (
                            SELECT 1
                            FROM slc_agreements sa

                            LEFT JOIN (
                                SELECT si.slc_agreement_id, SUM(si.amount) as total_claim
                                FROM slc_installments si
                                GROUP BY si.slc_agreement_id
                            ) inst ON inst.slc_agreement_id = sa.id

                            LEFT JOIN (
                                SELECT smr.slc_agreement_id,
                                    SUM(CASE WHEN smr.payment_type != 'Refund' THEN smr.amount ELSE 0 END) -
                                    SUM(CASE WHEN smr.payment_type = 'Refund' THEN smr.amount ELSE 0 END)
                                    as total_received
                                FROM slc_money_receipts smr
                                GROUP BY smr.slc_agreement_id
                            ) rec ON rec.slc_agreement_id = sa.id

                            LEFT JOIN (
                                SELECT slc_agreement_id, MAX(installment_date) as last_installment
                                FROM slc_installments
                                GROUP BY slc_agreement_id
                            ) last_inst ON last_inst.slc_agreement_id = sa.id

                            WHERE sa.student_id = students.id
                            AND sa.student_course_relation_id = cr.id
                            AND sa.deleted_at IS NULL
                            AND COALESCE(inst.total_claim,0) > COALESCE(rec.total_received,0)
                            AND (
                                    last_inst.last_installment IS NULL OR
                                    DATE_ADD(last_inst.last_installment, INTERVAL 30 DAY) < '{$this->date}'
                            )
                        ) THEN 4

                        WHEN EXISTS (
                            SELECT 1
                            FROM slc_agreements sa

                            LEFT JOIN (
                                SELECT si.slc_agreement_id, SUM(si.amount) as total_claim
                                FROM slc_installments si
                                GROUP BY si.slc_agreement_id
                            ) inst ON inst.slc_agreement_id = sa.id

                            LEFT JOIN (
                                SELECT smr.slc_agreement_id,
                                    SUM(CASE WHEN smr.payment_type != 'Refund' THEN smr.amount ELSE 0 END) -
                                    SUM(CASE WHEN smr.payment_type = 'Refund' THEN smr.amount ELSE 0 END)
                                    as total_received
                                FROM slc_money_receipts smr
                                GROUP BY smr.slc_agreement_id
                            ) rec ON rec.slc_agreement_id = sa.id

                            WHERE sa.student_id = students.id
                            AND sa.student_course_relation_id = cr.id
                            AND sa.deleted_at IS NULL
                            AND COALESCE(inst.total_claim,0) > COALESCE(rec.total_received,0)
                        ) THEN 3

                        ELSE 2
                        END AS due_status
                    "),
                'semesters.name as semester',
                'courses.name as course',
                'statuses.name as current_status',
                'cr.id as crel_id',
            )
            ->orderBy('students.id', 'DESC');
    }

    public function headings(): array
    {
        return [
            "ID",
            "Registration No",
            "First Name",
            "Last Name",
            "Evening & Weekend",
            "Disability",
            "Multiple Agreements",
            "Has Due",
            "Semester",
            "Course",
            "Status"
        ];
    }

    public function map($row): array
    {
        // $dueStatus = $this->due($row->id, $row->crel_id);
        // $multiAgreementStatus = $this->multiagreementStatus($row->id, $row->crel_id);

        //$student = Student::find($row->id);
        return [
            $row->id,
            $row->registration_no,
            $row->first_name,
            $row->last_name,
            $row->full_time,
            $row->disability,
            $row->multi_agreement,
            (isset($row->due_status) && $row->due_status > 1 ? 1 : 0),
            $row->semester,
            $row->course,
            $row->current_status,
        ];
    }

    public function chunkSize(): int
    {
        return 500;
    }

    protected function due($student_id, $activeCRel){
        $agreements = SlcAgreement::where('student_id', $student_id)->where('student_course_relation_id', $activeCRel)->orderBy('id', 'ASC')->get();
        $dueStatus = 2; /* Due Not Found */
        if($agreements->count() > 0):
            foreach($agreements as $agr):
                $ClaimAmount = (isset($agr->claim_amount) && $agr->claim_amount > 0 ? $agr->claim_amount : 0);
                $ReceivedAmount = (isset($agr->received_amount) && $agr->received_amount > 0 ? $agr->received_amount : 0);
                if($ClaimAmount > $ReceivedAmount):
                    $inst = SlcInstallment::where('slc_agreement_id', $agr->id)->orderBy('id', 'DESC')->get()->first();
                    $inst_date = (isset($inst->installment_date) && !empty($inst->installment_date) ? date('Y-m-d', strtotime($inst->installment_date)) : '');
                    if(!empty($inst_date)):
                        $inst_date = date('Y-m-d', strtotime('+30 Days', strtotime($inst_date)));
                        if($inst_date < date('Y-m-d')):
                            $dueStatus = 4; /* Due Found. And its over 30 days. Its a danger */
                        else:
                            $dueStatus = 3; /* Due Found. And its within 30 days. Its a warning */
                        endif;
                    else:
                        $dueStatus = 3; /* Due Found But Date Not Found. Its a warning.*/
                    endif;
                endif;
            endforeach;
        else:
            $dueStatus = 1; /* Agreement does not exist */
        endif;

        return $dueStatus;
    }

    protected function multiagreementStatus($student_id, $activeCRel){
        $query = DB::table('slc_agreements')
                 ->select(DB::raw('COUNT(DISTINCT id) as no_of_agreement'))
                 ->where('student_id', $student_id)
                 ->where('student_course_relation_id', $activeCRel)
                 ->whereNull('deleted_at')
                 ->groupBy('year')
                 ->get();
        $count = 0;
        if($query->count() > 0):
            foreach($query as $q):
                $count += (isset($q->no_of_agreement) && $q->no_of_agreement > 1 ? 1 : 0);
            endforeach;
        endif;
        return $count > 0 ? 2 : 0;
    }
}