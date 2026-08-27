<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Student;
use App\Models\StudentConversionLog;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Site-settings overview of every applicant-to-student conversion run: one row
 * per dispatched batch, aggregated from student_conversion_logs. The
 * per-applicant step detail lives on the admission Conversion Log tab
 * (admission.conversion.log), which each row links to.
 */
class StudentConversionLogController extends Controller
{
    public function index()
    {
        return view('pages.settings.conversion-logs.index', [
            'title' => 'Student Conversion Logs - London Churchill College',
            'subtitle' => 'Student Conversion Logs',
            'slug' => 'student_conversion_logs',
            'breadcrumbs' => [
                ['label' => 'Settings', 'href' => 'javascript:void(0);'],
                ['label' => 'Student Conversion Logs', 'href' => 'javascript:void(0);']
            ],
        ]);
    }

    public function list(Request $request){
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) && !empty($request->status) ? $request->status : '');

        $query = StudentConversionLog::selectRaw("batch_id,
                MAX(applicant_id) as applicant_id,
                MAX(student_id) as student_id,
                COUNT(*) as total_jobs,
                SUM(status = 'completed') as completed_jobs,
                SUM(status = 'failed') as failed_jobs,
                SUM(status = 'cancelled') as cancelled_jobs,
                SUM(status IN ('queued', 'processing')) as pending_jobs,
                MAX(created_by) as created_by,
                MIN(created_at) as dispatched_at,
                MAX(finished_at) as finished_at")
            ->groupBy('batch_id');

        if(!empty($queryStr)):
            $query->whereHas('applicant', function($qs) use($queryStr){
                $qs->where('first_name', 'LIKE', '%'.$queryStr.'%')
                    ->orWhere('last_name', 'LIKE', '%'.$queryStr.'%')
                    ->orWhere('application_no', 'LIKE', '%'.$queryStr.'%');
            });
        endif;
        if($status == 'failed'):
            $query->havingRaw('failed_jobs > 0');
        elseif($status == 'inprogress'):
            $query->havingRaw('failed_jobs = 0 AND pending_jobs > 0');
        elseif($status == 'completed'):
            $query->havingRaw('failed_jobs = 0 AND pending_jobs = 0');
        endif;

        // Grouped queries break ->count(), and Tabulator's sorters reference
        // aggregate aliases, so fetch the (low-volume: one row per conversion
        // run) batches and paginate the collection instead.
        $batches = $query->orderByRaw('dispatched_at DESC')->get();

        $total_rows = $batches->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 10));
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';

        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $Query = $batches->slice($offset, $limit);

        $applicants = Applicant::whereIn('id', $Query->pluck('applicant_id')->filter())->get()->keyBy('id');
        $students = Student::whereIn('id', $Query->pluck('student_id')->filter())->get()->keyBy('id');
        $users = User::whereIn('id', $Query->pluck('created_by')->filter())->get()->keyBy('id');
        $failedSteps = StudentConversionLog::where('status', StudentConversionLog::STATUS_FAILED)
            ->whereIn('batch_id', $Query->pluck('batch_id'))
            ->get()
            ->groupBy('batch_id');

        $data = array();

        $i = $offset + 1;
        foreach($Query as $list):
            $applicant = (isset($applicants[$list->applicant_id]) ? $applicants[$list->applicant_id] : null);
            $student = (isset($students[$list->student_id]) ? $students[$list->student_id] : null);

            if($list->failed_jobs > 0):
                $state = 'failed';
            elseif($list->pending_jobs > 0):
                $state = 'inprogress';
            else:
                $state = 'completed';
            endif;

            $data[] = [
                'sl' => $i,
                'batch_id' => $list->batch_id,
                'batch_ref' => (!empty($list->batch_id) ? substr($list->batch_id, -8) : ''),
                'applicant_id' => $list->applicant_id,
                'applicant_name' => ($applicant ? trim(ucfirst($applicant->first_name).' '.ucfirst($applicant->last_name)) : 'Unknown Applicant'),
                'application_no' => (isset($applicant->application_no) && !empty($applicant->application_no) ? $applicant->application_no : ''),
                'registration_no' => (isset($student->registration_no) && !empty($student->registration_no) ? $student->registration_no : ''),
                'steps' => (int) $list->completed_jobs.' / '.(int) $list->total_jobs,
                'state' => $state,
                'failed_steps' => (isset($failedSteps[$list->batch_id]) ? $failedSteps[$list->batch_id]->pluck('job_name')->implode(', ') : ''),
                'dispatched_at' => (!empty($list->dispatched_at) ? date('d-m-Y H:i:s', strtotime($list->dispatched_at)) : ''),
                'finished_at' => (!empty($list->finished_at) ? date('d-m-Y H:i:s', strtotime($list->finished_at)) : ''),
                'created_by' => (isset($users[$list->created_by]) ? $users[$list->created_by]->name : ''),
                'detail_url' => ($list->applicant_id > 0 ? route('admission.conversion.log', $list->applicant_id) : ''),
            ];
            $i++;
        endforeach;

        return response()->json(['last_page' => $last_page, 'total_rows' => $total_rows, 'data' => array_values($data)]);
    }
}
