<?php

namespace App\Http\Controllers\Reports;

use App\Exports\ArrayCollectionExport;
use App\Http\Controllers\Controller;
use App\Services\Reports\StudentAttendanceReport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Reports › Attendance Reports, "New version" tab (user, 24 Nov 2026). The page itself is still
 * AttendanceReportController::index, which also serves the old version until it is retired.
 */
class AttendanceReportNewController extends Controller
{
    /**
     * Every student of the search, A–Z, with the band counts and the search label (JSON).
     * The screen filters by band, sorts and pages these rows itself (user: no request per band).
     */
    public function list(Request $request)
    {
        $report = StudentAttendanceReport::fromRequest($request);
        if (!$report->hasSemester()) {
            return response()->json(['message' => 'Choose an Intake Semester or an Attendance Semester.'], 422);
        }

        $rows = $report->rows();

        return response()->json([
            'rows' => StudentAttendanceReport::arrange($rows, 'all', 'name', 'asc'),
            'bands' => StudentAttendanceReport::summarise($rows),
            'label' => $report->label(),
        ]);
    }

    /** Excel of the search (or of the ticked students), in the old export's columns. */
    public function export(Request $request)
    {
        $report = StudentAttendanceReport::fromRequest($request);
        if (!$report->hasSemester()) {
            return response()->json(['message' => 'Choose an Intake Semester or an Attendance Semester.'], 422);
        }
        [$band, $sort, $dir] = $this->arrangement($request);

        $sheet = [1 => [
            'ID', 'Reg No', 'Flag', 'Name', 'Date of Birth', 'Semester', 'Course', 'Course Code', 'SSN', 'Awarding Body Ref',
            'Status', 'Mobile', 'Email', 'Group', 'Total P', 'WEEKDAYS P', 'WEEKEND P', 'O', 'A', 'E', 'M', 'H', 'L', 'LE', 'TC',
            'Percentage(%)', 'Percentage W/O Excused(%)',
        ]];
        // The screen's order and band, always from a fresh query.
        $raw = $report->rawRows();
        $arranged = StudentAttendanceReport::arrange($raw->map(fn ($row) => StudentAttendanceReport::format($row))->values()->all(), $band, $sort, $dir);
        foreach ($arranged as $shown) {
            $row = $raw[$shown['id']];
            $sheet[] = [
                $row->id,
                $row->registration_no,
                (int) $row->note_flag === 1 ? 'Yes' : 'No',
                trim($row->first_name.' '.$row->last_name),
                !empty($row->date_of_birth) ? date('d-m-Y', strtotime($row->date_of_birth)) : '',
                $row->semester_name ?? '',
                $row->course_name ?? '',
                $row->slc_code ?? '',
                $row->ssn_no,
                $row->reference ?? '',
                $row->status,
                $row->mobile,
                $row->institutional_email,
                $row->group_name,
                $row->P, $row->EVENING_P, $row->WEEKEND_P, $row->O, $row->A, $row->E, $row->M, $row->H, $row->L, $row->LE, $row->TOTAL,
                !empty($row->percentage_withexcuse) ? number_format($row->percentage_withexcuse, 2) : '0',
                !empty($row->percentage_withoutexcuse) ? number_format($row->percentage_withoutexcuse, 2) : '0',
            ];
        }

        $name = preg_replace('/[^A-Za-z0-9]+/', '_', trim($report->label())) ?: 'Students';
        $fileName = 'Attendance_Report_'.trim($name, '_').(!empty($report->studentIds) ? '_Selected' : '').'.xlsx';

        return Excel::download(new ArrayCollectionExport($sheet), $fileName);
    }

    /** Band, sort key and direction from the request, checked. */
    private function arrangement(Request $request): array
    {
        $band = (string) $request->input('band', 'all');
        if (!in_array($band, StudentAttendanceReport::BANDS, true)) {
            $band = 'all';
        }
        $sort = (string) $request->input('sort', 'name');
        $dir = $request->input('dir') === 'desc' ? 'desc' : 'asc';

        return [$band, $sort, $dir];
    }
}
