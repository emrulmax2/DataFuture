<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessExtractedFiles;
use App\Jobs\ProcessExtractedFilesForP45;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeAttendanceDayBreak;
use App\Models\EmployeeAttendanceLive;
use App\Models\EmployeeLeave;
use App\Models\EmployeeLeaveDay;
use App\Models\EmployeeWorkingPattern;
use App\Models\EmployeeWorkingPatternDetail;
use App\Models\EmployeeWorkingPatternPay;
use App\Models\HrCondition;
use App\Models\HrHolidayYear;
use App\Models\PaySlipUploadSync;
use App\Models\VenueIpAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Polyfill\Intl\Idn\Resources\unidata\Regex;
use ZipArchive;

class EmployeeAttendanceController extends Controller
{

    public function index(Request $request){
        
        $holidayList = HrHolidayYear::orderBy('start_date','desc')->get();

        return view('pages.hr.attendance.index', [
            'title' => 'HR Portal - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'HR Monthly Attendance', 'href' => 'javascript:void(0);']
            ],
            'html_table' => $this->listHtml(date('d-m-Y')),
            'holiday_years' => $holidayList,
            'RemainpaySlips' => PaySlipUploadSync::whereNull('file_transffered_at')->pluck('month_year')->unique()->toArray(),
        ]);
    }
    function getDirectories($path) {
        $directories = array_filter(glob($path . '/*'), function($dir) {
            return is_dir($dir) && basename($dir) !== '__MACOSX';
        });
        return $directories;
    }
    public function upload(Request $request)
    {

        $request->validate([
            'file' => 'required|file|mimes:zip|max:200480',
        ]);
        $type = $request->type;
        $holiday_year_Id = $request->holiday_year_info;
        $file = $request->file('file');
        $fileOriginalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $dirName = $request->dir_name;
        // Store the uploaded ZIP locally on server, then dispatch a job to process it
        $tempPath = $file->storeAs('temp', $file->getClientOriginalName());

        // preload employee and payslip mappings to avoid per-file DB queries
        $activeListofDuplicateNiNumber = DB::table('employees')
            ->where('status', 1)
            ->whereNotNull('ni_number')
            ->where('ni_number', '<>', '')
            ->whereIn('ni_number', function ($q) {
                $q->from('employees')
                ->select('ni_number')
                ->groupBy('ni_number')
                ->havingRaw('COUNT(*) > 1');
            })
            ->orderBy('ni_number')
            ->pluck('id', 'ni_number');

        $allEmployees = DB::table('employees')
            ->select('id', 'ni_number')
            ->get();

        $employeeMap = [];
        foreach ($allEmployees as $emp) {
            $normalizedNi = preg_replace('/[\s-]+/', '', strtoupper(trim($emp->ni_number)));
            $duplicatedCurrentEmployeeId = $activeListofDuplicateNiNumber[$emp->ni_number] ?? 0;
            // if duplicate among active employees, mark ambiguous
            if ($duplicatedCurrentEmployeeId > 0) {
                $employeeMap[$normalizedNi] = $duplicatedCurrentEmployeeId;
            } elseif (!isset($employeeMap[$normalizedNi])) {
                $employeeMap[$normalizedNi] = $emp->id;
            }
        }

        ProcessExtractedFiles::dispatch($tempPath, $dirName, $type, $holiday_year_Id, $employeeMap);

        return response()->json(['success' => 'File process started. Extraction and processing are running in background.'], 200);
        
    }

    public function uploadEid(Request $request)
    {

        
        $request->validate([
            'file' => 'required|file|mimetypes:application/pdf,application/x-pdf,application/octet-stream|max:200480',
        ]);
        
        $type = $request->type;
        $holiday_year_Id = $request->holiday_year_info;
        $employee = Employee::find($request->employee_id);
        if(!$employee || $employee->user_id != $request->user_id){
            return response()->json(['error' => 'Invalid employee or user.'], 400);
        }
        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        if ($extension !== 'pdf') {
            return response()->json(['error' => 'Only PDF files are allowed.'], 422);
        }
        $fileOriginalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $dirName = $request->dir_name;
        // Store the uploaded ZIP locally on server, then dispatch a job to process it
        $tempPath = $file->storeAs('temp', $file->getClientOriginalName());

        $employeeMap[] = $employee->id;

        // preload employee and payslip mappings to avoid per-file DB queries
        // $activeListofDuplicateNiNumber = DB::table('employees')
        //     ->where('status', 1)
        //     ->whereNotNull('ni_number')
        //     ->where('ni_number', '<>', '')
        //     ->whereIn('ni_number', function ($q) {
        //         $q->from('employees')
        //         ->select('ni_number')
        //         ->groupBy('ni_number')
        //         ->havingRaw('COUNT(*) > 1');
        //     })
        //     ->orderBy('ni_number')
        //     ->pluck('id', 'ni_number');

        // $allEmployees = DB::table('employees')
        //     ->select('id', 'ni_number')
        //     ->get();

        // $employeeMap = [];
        // foreach ($allEmployees as $emp) {
        //     $normalizedNi = preg_replace('/[\s-]+/', '', strtoupper(trim($emp->ni_number)));
        //     $duplicatedCurrentEmployeeId = $activeListofDuplicateNiNumber[$emp->ni_number] ?? 0;
        //     // if duplicate among active employees, mark ambiguous
        //     if ($duplicatedCurrentEmployeeId > 0) {
        //         $employeeMap[$normalizedNi] = $duplicatedCurrentEmployeeId;
        //     } elseif (!isset($employeeMap[$normalizedNi])) {
        //         $employeeMap[$normalizedNi] = $emp->id;
        //     }
        // }

        ProcessExtractedFilesForP45::dispatch($tempPath, $dirName, $type, $holiday_year_Id, $employeeMap);

        return response()->json(['success' => 'File process started. Extraction and processing are running in background.'], 200);
        
    }
    public function payrollSyncShow($month_year){
        $paySlipUploadSync = PaySlipUploadSync::where('month_year', $month_year)->whereNull('file_transffered_at')->get();
        $checkEmploye =  PaySlipUploadSync::where('month_year', $month_year)->whereNull('file_transffered_at')->pluck('employee_id')->unique()->toArray();
        return view('pages.hr.attendance.payroll_sync', [
            'title' => 'HR Portal - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'HR Monthly Attendance', 'href' => route('hr.attendance')],
                ['label' => 'Payroll Sync', 'href' => 'javascript:void(0);']
            ],
            'employees'=> Employee::all(),
            'paySlipUploadSync' => $paySlipUploadSync,
            'month_year' => $month_year,
            'checkEmploye' => count($checkEmploye) ?? 0
        ]);
    }
    public function getListHtml(Request $request){
        $queryDate = (isset($request->queryDate) && !empty($request->queryDate) ? date('Y-m-d', strtotime('01-'.$request->queryDate)) : date('Y-m-d'));
        $html = $this->listHtml($queryDate);

        return response()->json(['res' => $html], 200);
    }

    public function listHtml($attendanceDate){
        $html = '';
        $month = date('m', strtotime($attendanceDate));
        $year = date('Y', strtotime($attendanceDate));
        $canDelete = (isset(auth()->user()->priv()['del_attendance']) && auth()->user()->priv()['del_attendance'] == 1);
        for($i = 1; $i <= date('t', strtotime($attendanceDate)); $i++):
            $todayDate = $year.'-'.$month.'-'.($i < 10 ? '0'.$i: $i);
            $isSyncronised = $this->isSynchronised($todayDate);
            $theUrl = $isSyncronised == 1 ? route('hr.attendance.show',strtotime($todayDate)) : 'javascript:void(0);';
            
            $issues = ($isSyncronised == 1 ? EmployeeAttendance::where('date', $todayDate)->where('user_issues', '>', 0)->where('overtime_status', '!=', 1)->get()->count() : 0);
            $absents = ($isSyncronised == 1 ? EmployeeAttendance::where('date', $todayDate)->where('leave_status', '>', 1)->get()->count() : 0);
            $overtime = ($isSyncronised == 1 ? EmployeeAttendance::where('date', $todayDate)->where('overtime_status', 1)->get()->count() : 0);
            $pendings = ($isSyncronised == 1 ? EmployeeAttendance::where('date', $todayDate)->whereNull('updated_by')->get()->count() : 0);
            $allRows = ($isSyncronised == 1 ? EmployeeAttendance::where('date', $todayDate)->get()->count() : 0);

            $ts      = strtotime($todayDate);
            $weekend = ((int) date('N', $ts) >= 6);
            $hasAtt  = $allRows > 0;

            $badgeClass = 'att-date__badge';
            if($weekend):
                $badgeClass .= ' att-date__badge--weekend';
            elseif($isSyncronised == 1 && $hasAtt):
                $badgeClass .= ' att-date__badge--synced';
            endif;

            if($weekend):
                $tag = 'Weekend'; $tagClass = 'att-date__tag';
            elseif($isSyncronised == 1):
                $tag = 'Working day'; $tagClass = 'att-date__tag';
            else:
                $tag = 'Awaiting sync'; $tagClass = 'att-date__tag att-date__tag--await';
            endif;

            $html .= '<tr'.($weekend ? ' class="att-row--weekend"' : '').'>';

                /* Date */
                $html .= '<td>';
                    $html .= '<div class="att-date">';
                        $html .= '<div class="'.$badgeClass.'">';
                            $html .= '<span class="att-date__dow">'.strtoupper(date('D', $ts)).'</span>';
                            $html .= '<span class="att-date__day">'.date('j', $ts).'</span>';
                        $html .= '</div>';
                        $html .= '<div class="att-date__meta">';
                            $html .= '<div class="att-date__long">'.date('D jS F, Y', $ts).'</div>';
                            $html .= '<div class="'.$tagClass.'">'.$tag.'</div>';
                        $html .= '</div>';
                    $html .= '</div>';
                $html .= '</td>';

                /* Synchronise */
                $html .= '<td>';
                    if($isSyncronised == 1):
                        $html .= '<span class="att-sync--done"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"></path></svg>Synchronised</span>';
                    else:
                        $html .= '<button type="button" data-date="'.$todayDate.'" class="att-sync-btn syncroniseAttendance">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-2.6-6.4"></path><path d="M21 3v5h-5"></path></svg>
                                    Synchronise
                                    <svg style="display: none;" width="14" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" class="w-4 h-4 ml-1">
                                        <g fill="none" fill-rule="evenodd">
                                            <g transform="translate(1 1)" stroke-width="4">
                                                <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                                <path d="M36 18c0-9.94-8.06-18-18-18">
                                                    <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                                </path>
                                            </g>
                                        </g>
                                    </svg>
                                </button>';
                    endif;
                $html .= '</td>';

                /* Counts */
                $html .= '<td>'.$this->renderCountPill($theUrl, $issues, 'Issues', 'issue').'</td>';
                $html .= '<td>'.$this->renderCountPill($theUrl, $absents, 'Absents', 'issue').'</td>';
                $html .= '<td>'.$this->renderCountPill($theUrl, $overtime, 'Overtime', 'warn').'</td>';
                $html .= '<td>'.$this->renderCountPill($theUrl, $pendings, 'Pendings', 'warn').'</td>';
                /* Actions */
                $html .= '<td>';
                    if($hasAtt):
                        $html .= '<div class="att-actions">';
                            $html .= '<a href="'.$theUrl.'" target="_blank" class="att-link"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"></path><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>'.$allRows.' Attendances</a>';
                            if($canDelete):
                                $html .= '<button data-date="'.date('Y-m-d', $ts).'" class="att-del deleteAllSyncd" type="button" title="Clear day"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4h8v2M6 6l1 14h10l1-14"></path></svg></button>';
                            endif;
                        $html .= '</div>';
                    else:
                        $html .= '<span class="att-link att-link--zero">0 Attendances</span>';
                    endif;
                $html .= '</td>';
            $html .= '</tr>';
        endfor;
        return $html;
    }

    private function renderCountPill($url, $count, $word, $tone){
        if($count > 0):
            $cls = ($tone == 'issue') ? 'att-pill att-pill--issue' : 'att-pill att-pill--warn';
            return '<a href="'.$url.'" target="_blank" class="'.$cls.'">'.$count.' '.$word.'</a>';
        endif;
        return '<a href="'.$url.'" class="att-pill att-pill--zero">0 '.$word.'</a>';
    }

    public function isSynchronised($theDate){
        $employeeAttendance = EmployeeAttendance::where('date', $theDate)->get()->count();
        return ($employeeAttendance > 0 ? 1 : 0);
    }

    /** Timeline runs 07:00 -> 21:00; every rostered shift we run falls inside it. */
    private const TIMELINE_START = 420;
    private const TIMELINE_SPAN  = 840;

    /** Minutes a punch may drift from the contract time and still read as "on time". */
    private const TOLERANCE = 5;

    /**
     * leave_status is a copy of the leave's leave_type.
     *
     * 1-5 match the leave-type picker and the leave calendar's colour legend. 6 and 7
     * are real and in use (394 and 10 attendance rows) but the picker's switch only
     * goes to 5, so nothing in the app ever named them - they rendered blank here and
     * on the old screen. Identified from their leave records: every type 6 leave is a
     * maternity leave, the single type 7 is a paternity leave.
     */
    private const LEAVE_NAMES = [
        1 => 'Holiday / Vacation',
        2 => 'Unauthorised Absent',
        3 => 'Sick Leave',
        4 => 'Authorised Unpaid',
        5 => 'Authorised Paid',
        6 => 'Maternity Leave',
        7 => 'Paternity Leave',
    ];

    /**
     * The daily attendance screen. $date arrives as a unix timestamp.
     *
     * The four buckets are still resolved by the four original queries, so every
     * row lands in exactly the section(s) it always did - including the overlap
     * where a leave row also carries issues. What changed is that the page now
     * renders each attendance ONCE, tagged with every bucket it belongs to,
     * instead of emitting a second copy of the same form fields in a second table.
     */
    public function show($date)
    {
        $timestamp = (int) $date;
        $theDate   = date('Y-m-d', $timestamp);

        $onDate = fn () => EmployeeAttendance::where('date', $theDate);

        $bucketIds = [
            'issues'   => $onDate()->where('user_issues', '>', 0)->where('overtime_status', '!=', 1)->pluck('id')->all(),
            'absents'  => $onDate()->where('leave_status', '>', 1)->pluck('id')->all(),
            'overtime' => $onDate()->where('overtime_status', 1)->pluck('id')->all(),
            'noissues' => $onDate()->where('overtime_status', 0)->where('leave_status', '<', 2)->where('user_issues', 0)->pluck('id')->all(),
        ];

        $visibleIds = array_values(array_unique(array_merge(...array_values($bucketIds))));

        // Eager loaded up front. The old page lazy-loaded employee, pay and leave
        // per row, and each location badge ran two more queries - several hundred
        // queries for a normal day.
        $attendances = EmployeeAttendance::with([
                'employee.title',
                'employee.employment.employeeJobTitle',
                'pay',
                'leaveDay.leave',
            ])
            ->whereIn('id', $visibleIds)
            ->orderBy('id', 'ASC')
            ->get();

        $venues = $this->punchVenues($attendances, $theDate);

        $rows = $attendances->map(function ($atten) use ($bucketIds, $venues) {
            $buckets = [];
            foreach ($bucketIds as $bucket => $ids) {
                if (in_array($atten->id, $ids)) {
                    $buckets[] = $bucket;
                }
            }

            return $this->presentAttendance($atten, $buckets, $venues);
        });

        $pending = $rows->where('is_reviewed', false);

        return view('pages.hr.attendance.show', [
            'title' => 'HR Portal - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'HR Monthly Attendance', 'href' => route('hr.attendance')],
                ['label' => 'HR Daily Attendance', 'href' => 'javascript:void(0);']
            ],
            'date' => $timestamp,
            'theDate' => date('D jS F, Y', $timestamp),
            'rows' => $rows,
            'counts' => [
                'all'      => $rows->count(),
                'issues'   => count($bucketIds['issues']),
                'absents'  => count($bucketIds['absents']),
                'overtime' => count($bucketIds['overtime']),
                'noissues' => count($bucketIds['noissues']),
                'pending'  => $pending->count(),
                'reviewed' => $rows->count() - $pending->count(),
                'ontime'   => $pending->where('is_on_time', true)->count(),
            ],
        ]);
    }

    /**
     * Resolves the clock-in / clock-out venue for every row on the day in two
     * queries instead of the two-per-row the model accessors would run.
     *
     * Mirrors EmployeeAttendance::getClockInLocationAttribute() exactly:
     *   suc 1 = punched from a known venue IP, 0 = punched from somewhere else,
     *   2 = no live punch at all.
     */
    private function punchVenues($attendances, string $theDate): array
    {
        $employeeIds = $attendances->pluck('employee_id')->filter()->unique()->values()->all();

        if (empty($employeeIds)) {
            return [];
        }

        $venueByIp = VenueIpAddress::with('venue')->get()->keyBy('ip');

        // The accessors take the LAST punch of the day (orderBy id DESC, first()),
        // so walking ASC and letting later rows overwrite lands on the same one.
        $punches = EmployeeAttendanceLive::whereIn('employee_id', $employeeIds)
            ->where('date', $theDate)
            ->whereIn('attendance_type', [1, 4])
            ->orderBy('id', 'ASC')
            ->get();

        $map = [];
        foreach ($punches as $punch) {
            $side = ((int) $punch->attendance_type === 1) ? 'in' : 'out';

            if (empty($punch->ip)) {
                $map[$punch->employee_id][$side] = ['suc' => 0, 'ip' => '', 'venue' => ''];
                continue;
            }

            $venueIp = $venueByIp->get($punch->ip);
            $venueName = $venueIp->venue->name ?? '';

            $map[$punch->employee_id][$side] = !empty($venueName)
                ? ['suc' => 1, 'ip' => $venueIp->ip, 'venue' => $venueName]
                : ['suc' => 0, 'ip' => $punch->ip, 'venue' => ''];
        }

        return $map;
    }

    /**
     * Flattens one attendance into everything the screen needs, so the Blade holds
     * markup rather than payroll arithmetic.
     */
    private function presentAttendance(EmployeeAttendance $atten, array $buckets, array $venues): array
    {
        $issueFlags = !empty($atten->isses_field) ? @unserialize(base64_decode($atten->isses_field)) : [];
        $issueFlags = is_array($issueFlags) ? $issueFlags : [];

        // 00:00 on the CONTRACT means "not rostered at all", exactly as it does on a
        // punch - the sync writes the pair 00:00/00:00 on 25,376 rows (every "not in
        // schedule" row, and every leave day). It is never half of a real shift: no row
        // anywhere has a 00:00 start with a real finish, or the reverse.
        //
        // Keeping it as a literal midnight compared a 09:04 punch against 00:00 and
        // produced "9h 4m late", a red bar, a rostered stub drawn at the far left of
        // the timeline, and a "7h 39m over rostered" verdict - on a shift nobody
        // rostered.
        $schedIn   = $this->attendanceClock($atten->clockin_contract);
        $schedOut  = $this->attendanceClock($atten->clockout_contract);
        $punchIn   = $this->attendanceClock($atten->clockin_punch);
        $punchOut  = $this->attendanceClock($atten->clockout_punch);
        $systemIn  = $this->attendanceClock($atten->clockin_system);
        $systemOut = $this->attendanceClock($atten->clockout_system);

        $unpaidBreak  = $this->convertStringToMinute((string) ($atten->unpadi_break ?: '00:00'));
        $allowedBreak = (int) $atten->allowed_break;
        $takenBreak   = (int) ($atten->total_break ?: 0);
        $workedMin    = (int) ($atten->total_work_hour ?: 0);

        $leaveStatus     = (int) ($atten->leave_status ?: 0);
        $leaveHour       = (int) ($atten->leave_hour ?: 0);
        $leaveAdjustment = (string) ($atten->leave_adjustment ?: '');

        $hasPunch  = ($punchIn !== '' || $punchOut !== '');
        $hasSystem = ($systemIn !== '' || $systemOut !== '');
        $isOnlyLeave = ($punchIn === '' && $punchOut === '' && $workedMin === 0 && $leaveStatus > 0);

        // A punch is compared against the contract, so these describe what actually
        // happened and do not move when HR edits the recorded (system) time.
        $inDelta = ($schedIn !== '' && $punchIn !== '')
            ? $this->convertStringToMinute($punchIn) - $this->convertStringToMinute($schedIn)
            : null;
        $outDelta = ($schedOut !== '' && $punchOut !== '')
            ? $this->convertStringToMinute($punchOut) - $this->convertStringToMinute($schedOut)
            : null;

        $notRostered = ($schedIn === '' && $schedOut === '');

        $inState  = $this->clockState($inDelta, 'in', $schedIn !== '');
        $outState = $this->clockState($outDelta, 'out', $schedOut !== '');

        $isOnTime = $inDelta !== null && $outDelta !== null
            && abs($inDelta) <= self::TOLERANCE && abs($outDelta) <= self::TOLERANCE;

        $rosteredMin = ($schedIn !== '' && $schedOut !== '')
            ? ($this->convertStringToMinute($schedOut) - $this->convertStringToMinute($schedIn)) - $unpaidBreak
            : null;
        $hourDelta = $rosteredMin !== null ? $workedMin - $rosteredMin : null;

        $hasMissingPunch = $hasPunch && ($punchIn === '' || $punchOut === '');

        // Someone worked a full day that nobody rostered. There is nothing to measure
        // them against - which is precisely why it wants a look - so it is flagged
        // rather than left neutral. Amber, not red: red on this page means the row is
        // broken (a punch is missing, the hours are negative). This row is not broken,
        // it is unexplained.
        //
        // The row's edge and avatar ring follow the bar, so they turn amber with it.
        $barTone = $notRostered
            ? 'warn'
            : (($hasMissingPunch || $inState['tone'] === 'bad' || $outState['tone'] === 'bad') ? 'bad'
                : (($inState['tone'] === 'warn' || $outState['tone'] === 'warn') ? 'warn' : 'good'));

        $isReviewed = (int) ($atten->updated_by ?: 0) > 0;
        $adjustment = (string) ($atten->adjustment ?: '');
        // A leave adjustment only counts where there is leave; the column can hold a
        // stale value on a row whose leave was later cleared.
        $isAdjusted = $this->signedMinutes($adjustment) !== 0
            || ($leaveStatus > 0 && $this->signedMinutes($leaveAdjustment) !== 0);

        $rowFlags = [$isReviewed ? 'reviewed' : 'pending'];
        if ($isOnTime) {
            $rowFlags[] = 'on-time';
        }
        if ($inDelta !== null && $inDelta > self::TOLERANCE) {
            $rowFlags[] = 'late-in';
        }
        if ($outDelta !== null && $outDelta < -self::TOLERANCE) {
            $rowFlags[] = 'early-out';
        }
        if ($isAdjusted) {
            $rowFlags[] = 'adjusted';
        }

        $first = trim((string) ($atten->employee->first_name ?? ''));
        $last  = trim((string) ($atten->employee->last_name ?? ''));
        $employeePhoto = $this->employeePhotoUrl($atten->employee);

        return [
            'id'          => (int) $atten->id,
            'employee_id' => (int) $atten->employee_id,
            'date'        => date('Y-m-d', strtotime((string) $atten->date)),

            'name'      => trim(($atten->employee->title->name ?? '').' '.$first.' '.$last),
            'job_title' => (string) ($atten->employee->employment->employeeJobTitle->name ?? ''),
            'rate'      => $atten->pay->hourly_rate ?? null,
            'initials'  => strtoupper(mb_substr($first, 0, 1).mb_substr($last, 0, 1)) ?: '?',
            'photo_url' => $employeePhoto,

            'buckets' => $buckets,
            'flags'   => $rowFlags,

            'sched_in'   => $schedIn,
            'sched_out'  => $schedOut,
            'punch_in'   => $punchIn,
            'punch_out'  => $punchOut,
            'system_in'  => $systemIn,
            'system_out' => $systemOut,

            'loc_in'  => $venues[$atten->employee_id]['in']  ?? ['suc' => 2, 'ip' => '', 'venue' => ''],
            'loc_out' => $venues[$atten->employee_id]['out'] ?? ['suc' => 2, 'ip' => '', 'venue' => ''],

            'in_state'  => $inState,
            'out_state' => $outState,
            'bar_tone'  => $barTone,

            // The left edge and the avatar ring take the TIMELINE BAR's colour, so a
            // row's accent always agrees with the bar sitting next to it. Deliberately
            // NOT the recommendation's tone: the reco is about hours against the
            // roster, the bar is about the punches, and the two can disagree - clock in
            // 2h early and out 2h early and the bar is red while the hours still add up.
            // Punches that both land on the roster carry no accent: nothing to flag.
            'edge_tone' => $isReviewed
                ? ($isAdjusted ? 'accent' : 'done')
                : ($isOnTime ? 'none' : ($hasPunch ? $barTone : 'muted')),

            'paid_break'    => (string) ($atten->paid_break ?: '00:00'),
            'unpaid_break'  => (string) ($atten->unpadi_break ?: '00:00'),
            'taken_break'   => $takenBreak,
            'allowed_break' => $allowedBreak,
            'break_taken'   => $atten->break_time,
            'break_issue'   => !empty($issueFlags['break_issue']),
            'break_over'    => $takenBreak > $allowedBreak,
            'has_breaks'    => $takenBreak > 0,

            'clockin_issue'  => !empty($issueFlags['clockin_system']),
            'clockout_issue' => !empty($issueFlags['clockout_system']),

            'adjustment'      => $adjustment,
            'is_adjusted'     => $isAdjusted,
            'total_work_hour' => $workedMin,
            'work_hour'       => $atten->work_hour,
            // The browser re-derives the "22m less than rostered" line as HR edits, so
            // it needs the rostered figure in minutes, not just formatted.
            'rostered_min'    => $rosteredMin,
            'rostered_hour'   => $rosteredMin !== null ? $this->calculateHourMinute($rosteredMin) : null,
            'hour_delta'      => $hourDelta,
            'hour_delta_plain' => $hourDelta === null
                ? 'No rostered shift'
                : ($hourDelta === 0
                    ? 'Same as rostered'
                    : $this->humanDelta($hourDelta).($hourDelta > 0 ? ' more' : ' less')),

            'reco'      => $this->recommendation($isOnTime, $hourDelta, $punchIn, $punchOut),
            // "No rostered start - no rostered finish" says the same thing twice. When
            // there is no roster at all, say it once.
            'fact_line' => ($notRostered && $hasPunch)
                ? 'Worked outside any rostered shift'
                : $this->factLine($inDelta, $outDelta, $punchIn, $punchOut),

            'timeline' => ($hasPunch || $hasSystem) ? [
                'sched' => $this->timelineBar($schedIn, $schedOut),
                'clock' => $this->timelineBar($systemIn ?: $punchIn, $systemOut ?: $punchOut),
            ] : null,

            'leave_status'     => $leaveStatus,
            // Never fall back to an empty string: an unnamed type would render a blank
            // band, which is how 6 and 7 went unnoticed in the first place.
            'leave_name'       => self::LEAVE_NAMES[$leaveStatus]
                ?? ($leaveStatus > 0 ? 'Leave (type '.$leaveStatus.')' : ''),
            'leave_note'       => (string) ($atten->leaveDay->leave->note ?? ''),
            'leave_day_hour'   => $this->calculateHourMinute((int) ($atten->leaveDay->hour ?? 0)),
            'leave_locked'     => (int) ($atten->employee_leave_day_id ?: 0) > 0,
            'leave_adjustment' => $leaveAdjustment,
            'leave_hour'       => $leaveHour,
            // leave_hour already has any saved adjustment folded into it, so the raw
            // figure is recovered by reversing it. The editor recomputes from this
            // base every keystroke; the old screen recomputed from the running total,
            // which quietly re-applied the adjustment each time HR retyped it.
            'leave_base'       => $leaveHour - $this->signedMinutes($leaveAdjustment),
            'leave_hour_text'  => $atten->leaves_hour,

            'note'        => (string) ($atten->note ?: ''),
            'user_issues' => (int) ($atten->user_issues ?: 0),
            'is_only_leave' => $isOnlyLeave,
            'is_reviewed'   => $isReviewed,
            'is_on_time'    => $isOnTime,
            'has_punch'     => $hasPunch,
        ];
    }

    private function employeePhotoUrl($employee): string
    {
        $photo = trim((string) ($employee->photo ?? ''));

        if ($photo === '' || empty($employee->id)) {
            return '';
        }

        $path = 'public/employees/'.$employee->id.'/'.$photo;

        return Storage::disk('local')->exists($path)
            ? Storage::disk('local')->url($path)
            : '';
    }

    /**
     * Old sync stores a missing in/out punch as 00:00. That is a placeholder, not
     * a midnight shift, so normalise it before any timeline or issue arithmetic.
     */
    private function attendanceClock($value, bool $zeroMeansMissing = true): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        if (preg_match('/^(\d{1,2}):(\d{2})(?::\d{2})?$/', $value, $match)) {
            $clock = sprintf('%02d:%02d', (int) $match[1], (int) $match[2]);

            return $zeroMeansMissing && $clock === '00:00' ? '' : $clock;
        }

        return $value;
    }

    /** How far a punch sits from its contract time, in words. */
    private function clockState(?int $delta, string $side, bool $rostered = true): array
    {
        if ($delta === null) {
            // Two different absences: nobody rostered them, or they never punched.
            return ['label' => $rostered ? 'Not punched' : 'Not rostered', 'tone' => 'muted'];
        }

        if (abs($delta) <= self::TOLERANCE) {
            return ['label' => 'On time', 'tone' => 'good'];
        }

        if ($side === 'in') {
            return $delta > 0
                ? ['label' => $this->humanDelta($delta).' late', 'tone' => $delta >= 60 ? 'bad' : 'warn']
                : ['label' => $this->humanDelta($delta).' early', 'tone' => 'good'];
        }

        return $delta < 0
            ? ['label' => $this->humanDelta($delta).' early', 'tone' => $delta <= -60 ? 'bad' : 'warn']
            : ['label' => $this->humanDelta($delta).' over', 'tone' => 'neutral'];
    }

    /** The single line HR reads to decide whether a row needs them at all. */
    private function recommendation(bool $isOnTime, ?int $hourDelta, string $punchIn, string $punchOut): array
    {
        if ($punchIn === '' && $punchOut === '') {
            return ['label' => 'No clocking recorded', 'tone' => 'muted'];
        }

        if ($punchIn === '') {
            return ['label' => 'Clock-in missing', 'tone' => 'bad'];
        }

        if ($punchOut === '') {
            return ['label' => 'Clock-out missing', 'tone' => 'bad'];
        }

        if ($hourDelta === null) {
            return ['label' => 'No rostered shift to compare', 'tone' => 'neutral'];
        }

        if ($isOnTime) {
            return ['label' => 'Matches schedule', 'tone' => 'good'];
        }

        if (abs($hourDelta) <= self::TOLERANCE) {
            return ['label' => 'Hours match schedule', 'tone' => 'good'];
        }

        return $hourDelta < 0
            ? ['label' => $this->humanDelta($hourDelta).' short — review', 'tone' => abs($hourDelta) >= 60 ? 'bad' : 'warn']
            : ['label' => $this->humanDelta($hourDelta).' over rostered', 'tone' => 'neutral'];
    }

    private function factLine(?int $inDelta, ?int $outDelta, string $punchIn, string $punchOut): string
    {
        $parts = [];

        if ($inDelta === null) {
            $parts[] = $punchIn === '' ? 'No clock-in' : 'No rostered start';
        } elseif (abs($inDelta) <= self::TOLERANCE) {
            $parts[] = 'In on time';
        } else {
            $parts[] = 'In '.$this->humanDelta($inDelta).($inDelta > 0 ? ' late' : ' early');
        }

        if ($outDelta === null) {
            $parts[] = $punchOut === '' ? 'no clock-out' : 'no rostered finish';
        } elseif (abs($outDelta) <= self::TOLERANCE) {
            $parts[] = 'out on time';
        } else {
            $parts[] = 'out '.$this->humanDelta($outDelta).($outDelta < 0 ? ' early' : ' over');
        }

        return implode(' · ', $parts);
    }

    /** Left / width of a bar on the 07:00-21:00 track, as percentages. */
    private function timelineBar(string $from, string $to): ?array
    {
        if ($from === '' || $to === '') {
            return null;
        }

        $pct = function (string $time) {
            $at = ($this->convertStringToMinute($time) - self::TIMELINE_START) / self::TIMELINE_SPAN * 100;
            return max(0, min(100, $at));
        };

        $left = $pct($from);

        return [
            'left'  => round($left, 3),
            'width' => round(max(0.6, $pct($to) - $left), 3),
        ];
    }

    /** 85 -> "1h 25m", 22 -> "22m". Sign is dropped; the caller supplies the word. */
    private function humanDelta(int $minutes): string
    {
        $minutes = abs($minutes);
        $hours = intdiv($minutes, 60);
        $mins  = $minutes % 60;

        if ($hours && $mins) {
            return $hours.'h '.$mins.'m';
        }

        return $hours ? $hours.'h' : $mins.'m';
    }

    /** "-01:30" -> -90, "+00:30" -> 30, "" -> 0. */
    private function signedMinutes(string $adjustment): int
    {
        $adjustment = trim($adjustment);

        if ($adjustment === '') {
            return 0;
        }

        $sign = str_starts_with($adjustment, '-') ? -1 : 1;

        return $sign * $this->convertStringToMinute(ltrim($adjustment, '+-'));
    }

    public function syncronise(Request $request){
        $theDate = date('Y-m-d', strtotime($request->theDate));
        $syncronised = $this->syncroniseAttendanceData($theDate);
        return response()->json(['res' => 'Employee attendance successfully sincronised.', 'date' => date('D jS M', strtotime($theDate)), 'url' => url('hr/attendance/show/'.strtotime($theDate))], 200);
    }

    public function syncroniseAttendanceData($theDate, $employee_id = 0){
        $theDay = date('D', strtotime($theDate));
        $theDayNum = date('N', strtotime($theDate));
        if($employee_id > 0):
            $employees = Employee::where('id', $employee_id)->where('status', 1)->orderBy('first_name', 'ASC')->get();
        else:
            $employees = Employee::has('activePatterns')->where('status', 1)->orderBy('first_name', 'ASC')->get();
        endif;

        foreach($employees as $employee):
            if(isset($employee->payment->subject_to_clockin) && $employee->payment->subject_to_clockin == 'Yes'):
                $employee_id = $employee->id;
                $employee = Employee::find($employee_id);

                $data               = [];
                $issues             = 0;
                $issues_array       = [];

                $todayAttendance = EmployeeAttendanceLive::where('employee_id', $employee_id)->where('date', $theDate)->orderBy('id', 'ASC')->get();
                $todayLastOutRow = EmployeeAttendanceLive::where('employee_id', $employee_id)->where('date', $theDate)->where('attendance_type', 4)->orderBy('id', 'DESC')->get()->first();
                $todayLastOutId = (isset($todayLastOutRow->id) && $todayLastOutRow->id > 0 ? $todayLastOutRow->id : 0);
                
                $breakArray         = [];
                $break_return       = [];
                $work_start         = '';
                $work_end           = '';
                $system_work_start  = '';
                $system_work_end    = '';
                $br                 = 1;
                $day_user_pay_info  = [];
                $start_contract = $end_contract = $paid_break = $unpaid_break = $n_dif = $p_dif = $en_dif = $ep_dif = '';

                $activePattern = EmployeeWorkingPattern::where('employee_id', $employee_id)
                                         ->where('effective_from', '<=', $theDate)
                                         ->where(function($query) use($theDate){
                                            $query->whereNull('end_to')->orWhere('end_to', '>=', $theDate);
                                         })->where('active', 1)->orderBy('id', 'DESC')->get()->first();
                $activePatternId = (isset($activePattern->id) && $activePattern->id > 0 ? $activePattern->id : 0);
                $patternPay = EmployeeWorkingPatternPay::where('employee_working_pattern_id', $activePatternId)
                              ->where('effective_from', '<=', $theDate)
                              ->where(function($query) use($theDate){
                                    $query->whereNull('end_to')->orWhere('end_to', '>=', $theDate);
                              })->where('active', 1)->orderBy('id', 'DESC')->get()->first();
                $activePatternPayId = (isset($patternPay->id) && $patternPay->id > 0 ? $patternPay->id : 0);

                $patternDay         = EmployeeWorkingPatternDetail::where('employee_working_pattern_id', $activePatternId)->where('day', $theDayNum)->get()->first();
                $day_status         = (isset($patternDay->id) && $patternDay->id > 0 ? 1 : 2);
                $contractStart      = (isset($patternDay->start) && !empty($patternDay->start)) ? $patternDay->start : '00:00';
                $contractEnd        = (isset($patternDay->end) && !empty($patternDay->end)) ? $patternDay->end : '00:00';
                $paid_break         = (isset($patternDay->paid_br) && !empty($patternDay->paid_br)) ? $patternDay->paid_br : '00:00';
                $unpaid_break       = (isset($patternDay->unpaid_br) && !empty($patternDay->unpaid_br)) ? $patternDay->unpaid_br : '00:00';
                $total_hour         = (isset($patternDay->total) && !empty($patternDay->total)) ? $patternDay->total : '00:00';

                $employeeLeaveIds   = EmployeeLeave::where('employee_id', $employee_id)->where('from_date', '<=', $theDate)->where('to_date', '>=', $theDate)
                                      ->where('status', 'Approved')->pluck('id')->toArray();
                $employeeLeaveDay   = EmployeeLeaveDay::whereIn('employee_leave_id', $employeeLeaveIds)->where('status', 'Active')
                                      ->where('leave_date', $theDate)->get()->first();

                $is_leave_day       = 0;
                $today_leave_id     = 0;
                $leave_type         = 0;
                $leave_day_hours    = 0;

                $total_hours_day    = $total_hour;
                $total_mints_day    = $this->convertStringToMinute($total_hour);
                $leave_note         = '';
                if(!empty($employeeLeaveDay) && isset($employeeLeaveDay->id) && $employeeLeaveDay->id > 0):
                    $is_leave_day   = 1;
                    $today_leave_id = $employeeLeaveDay->id;
                    $leave_note = (isset($employeeLeaveDay->leave->note) && !empty($employeeLeaveDay->leave->note) ? $employeeLeaveDay->leave->note : '');
                    $todayHour = ($total_hour != '00:00' && $total_hour != '') ? $this->convertStringToMinute($total_hour) : 0;
                    //$leaveHour = ($employeeLeaveDay->hour > 0 ? $employeeLeaveDay->hour : $this->convertStringToMinute($total_hour));
                    $leaveHour = ($employeeLeaveDay->hour > 0 ? $employeeLeaveDay->hour : 0);
                    
                    $leave_day_hours = $leaveHour;

                    $total_hours_day = ($leaveHour <= $todayHour) ? $this->calculateHourMinute($leaveHour) : $this->calculateHourMinute($todayHour);
                    $total_mints_day = ($leaveHour <= $todayHour) ? $leaveHour : $todayHour;

                    $leave_type = (isset($employeeLeaveDay->leave->leave_type) && $employeeLeaveDay->leave->leave_type > 0) ? $employeeLeaveDay->leave->leave_type : 0;
                endif;
                
                $data['employee_id'] = $employee_id;
                $data['employee_working_pattern_id'] = $activePatternId;
                $data['employee_working_pattern_pay_id'] = $activePatternPayId;
                $data['date'] = $theDate;

                if(($day_status == 1 && $todayAttendance->count() > 0) || ($day_status == 2 && $todayAttendance->count() > 0)):
                    /* Start If clock in not found */
                    if(!$this->isPunchExist($employee_id, $theDate, 1)):
                        if($day_status == 2):
                            $value = '';
                        else:
                            $work_start = 1;
                            $notify = ($this->getConditionSet('Clock In', 4, 'notify', 0) == 1) ? 'notfy_input' : '';
                            if($this->getConditionSet('Clock In', 4, 'notify', 0) == 1){
                                $issues += 1;
                                $issues_array['clockin_system'] = 1;
                            }
                            $value = '';
                            $action = $this->getConditionSet('Clock In', 4, 'action', 0);
                            if($this->getConditionSet('Clock In', 4, 'action', 0) == 1){
                                $value = date('H:i', strtotime($contractStart));
                            }
                        endif;

                        $data['clockin_contract'] = date('H:i', strtotime($contractEnd));
                        $data['clockin_punch'] = '00:00';
                        $data['clockin_system'] = $value;
                    endif;
                    /* End If clock in not found */

                    /* Start Loop for Attendance Feed from Live */
                    $in_status = 0;
                    $out_status = 0;
                    foreach($todayAttendance as $k => $clocks):
                        if($clocks->attendance_type == 1 && $day_status == 1 && $in_status == 0):
                            $in_status += 1;
                            $work_start = date('H:i', strtotime($clocks->time));

                            $to_time = strtotime($contractStart);
                            $from_time = strtotime($work_start);
                            if($to_time > $from_time):
                                $n_dif = round(abs($to_time - $from_time) / 60,2);
                            else:
                                $p_dif = round(abs($to_time - $from_time) / 60,2);
                            endif;

                            if($clocks->time != ''):
                                if($n_dif != '' && ($n_dif > 0 && $n_dif <= $this->getConditionSet('Clock In', 1, 'minutes', 0))):
                                    $notify = ($this->getConditionSet('Clock In', 1, 'notify', 0) == 1) ? 'notfy_input' : '';
                                    if($this->getConditionSet('Clock In', 1, 'notify', 0) == 1){
                                        $issues += 1;
                                        $issues_array['clockin_system'] = 1;
                                    }
                                    $value = '';
                                    $action = $this->getConditionSet('Clock In', 1, 'action', 0);
                                    if($this->getConditionSet('Clock In', 1, 'action', 0) == 1){
                                        $value = date('H:i', strtotime($contractStart));
                                    }elseif($this->getConditionSet('Clock In', 1, 'action', 0) == 2){
                                        $value = date('H:i', strtotime($clocks->time));
                                    }
                                    $system_work_start = $value;
                                elseif($n_dif != '' && $n_dif > $this->getConditionSet('Clock In', 1, 'minutes', 0)):
                                    $system_work_start = date('H:i', strtotime($clocks->time));
                                    $issues += 1;
                                    $issues_array['clockin_system'] = 1;
                                elseif($p_dif != '' && $p_dif > 0 && $p_dif <= $this->getConditionSet('Clock In', 2, 'minutes', 0)):
                                    $notify = ($this->getConditionSet('Clock In', 2, 'notify', 0) == 1) ? 'notfy_input' : '';
                                    if($this->getConditionSet('Clock In', 2, 'notify', 0) == 1){
                                        $issues += 1;
                                        $issues_array['clockin_system'] = 1;
                                    }
                                    $value = '';
                                    $action = $this->getConditionSet('Clock In', 2, 'action', 0);
                                    if($this->getConditionSet('Clock In', 2, 'action', 0) == 1){
                                        $value = date('H:i', strtotime($contractStart));
                                    }elseif($this->getConditionSet('Clock In', 2, 'action', 0) == 2){
                                        $value = date('H:i', strtotime($clocks->time));
                                    }
                                    $system_work_start = $value;
                                elseif($p_dif != '' && $p_dif > $this->getConditionSet('Clock In', 3, 'minutes', 0)):
                                    $notify = ($this->getConditionSet('Clock In', 3, 'notify', 0) == 1) ? 'notfy_input' : '';
                                    if($this->getConditionSet('Clock In', 3, 'notify', 0) == 1){
                                        $issues += 1;
                                        $issues_array['clockin_system'] = 1;
                                    }
                                    $value = '';
                                    $action = $this->getConditionSet('Clock In', 3, 'action', 0);
                                    if($this->getConditionSet('Clock In', 3, 'action', 0) == 1){
                                        $value = date('H:i', strtotime($contractStart));
                                    }elseif($this->getConditionSet('Clock In', 3, 'action', 0) == 2){
                                        $value = date('H:i', strtotime($clocks->time));
                                    }
                                    $system_work_start = $value;
                                else:
                                    $system_work_start = date('H:i', strtotime(strtr($clocks->time, '/', '-')));
                                endif;
                            else:
                                $notify = ($this->getConditionSet('Clock In', 4, 'notify', 0) == 1) ? 'notfy_input' : '';
                                if($this->getConditionSet('Clock In', 4, 'notify', 0) == 1){
                                    $issues += 1;
                                    $issues_array['clockin_system'] = 1;
                                }
                                $value = '';
                                $action = $this->getConditionSet('Clock In', 4, 'action', 0);
                                if($this->getConditionSet('Clock In', 4, 'action', 0) == 1){
                                    $value = date('H:i', strtotime($contractStart));
                                }elseif($this->getConditionSet('Clock In', 4, 'action', 0) == 2){
                                    $value = date('H:i', strtotime($clocks->time));
                                } 
                                $system_work_start = $value;
                            endif;
                            $data['clockin_contract'] = date('H:i', strtotime($contractStart));
                            $data['clockin_punch'] = date('H:i', strtotime($clocks->time));
                            $data['clockin_system'] = $system_work_start;
                        elseif($clocks->attendance_type == 1 && $day_status == 2 && $in_status == 0):
                            $in_status += 1;
                            $system_work_start = date('H:i', strtotime($clocks->time));

                            $data['clockin_contract'] = date('H:i', strtotime($contractStart));
                            $data['clockin_punch'] = date('H:i', strtotime($clocks->time));
                            $data['clockin_system'] = date('H:i', strtotime($clocks->time));
                        elseif($clocks->attendance_type == 4 && $day_status == 1 && $out_status == 0 && $todayLastOutId == $clocks->id):
                            $out_status += 1;
                            $work_end = date('H:i', strtotime($clocks->time));

                            $eto_time = strtotime($contractEnd);
                            $efrom_time = strtotime($work_end);

                            if($eto_time > $efrom_time):
                                $en_dif = round(abs($eto_time - $efrom_time) / 60,2);
                            else:
                                $ep_dif = round(abs($eto_time - $efrom_time) / 60,2);
                            endif;

                            if($clocks->time != ''):
                                if($en_dif != '' && $en_dif > 0 && $en_dif <= $this->getConditionSet('Clock Out', 1, 'minutes', 0)):
                                    $notify = ($this->getConditionSet('Clock Out', 1, 'notify', 0) == 1) ? 'notfy_input' : '';
                                    if($this->getConditionSet('Clock Out', 1, 'notify', 0) == 1){
                                        $issues += 1;
                                        $issues_array['clockout_system'] = 1;
                                    }
                                    $value = '';
                                    $action2 = $this->getConditionSet('Clock Out', 1, 'action', 0);
                                    if($this->getConditionSet('Clock Out', 1, 'action', 0) == 1){
                                        $value = date('H:i', strtotime($contractEnd));
                                    }elseif($this->getConditionSet('Clock Out', 1, 'action', 0) == 2){
                                        $value = date('H:i', strtotime($clocks->time));
                                    }
                                    $system_work_end = $value;
                                elseif($en_dif != '' && $en_dif > $this->getConditionSet('Clock Out', 1, 'minutes', 0)):
                                    $system_work_end = date('H:i', strtotime($clocks->time));
                                    $issues += 1;
                                    $issues_array['clockout_system'] = 1;
                                elseif($ep_dif != '' && $ep_dif > 0 && $ep_dif <= $this->getConditionSet('Clock Out', 2, 'minutes', 0)):
                                    $notify = ($this->getConditionSet('Clock Out', 2, 'notify', 0) == 1) ? 'notfy_input' : '';
                                    if($this->getConditionSet('Clock Out', 2, 'notify', 0) == 1){
                                        $issues += 1;
                                        $issues_array['clockout_system'] = 1;
                                    }
                                    $value = '';
                                    $action2 = $this->getConditionSet('Clock Out', 2, 'action', 0);
                                    if($this->getConditionSet('Clock Out', 2, 'action', 0) == 1){
                                        $value = date('H:i', strtotime($contractEnd));
                                    }elseif($this->getConditionSet('Clock Out', 2, 'action', 0) == 2){
                                        $value = date('H:i', strtotime($clocks->time));
                                    }
                                    $system_work_end = $value;
                                elseif($ep_dif != '' && $ep_dif > $this->getConditionSet('Clock Out', 2, 'minutes', 0)):
                                    $system_work_end = date('H:i', strtotime($clocks->time));
                                    $issues += 1;
                                    $issues_array['clockout_system'] = 1;
                                else:
                                    $system_work_end = date('H:i', strtotime($clocks->time));
                                endif;
                            else:
                                $notify = ($this->getConditionSet('Clock Out', 3, 'notify', 0) == 1) ? 'notfy_input' : '';
                                if($this->getConditionSet('Clock Out', 3, 'notify', 0) == 1){
                                    $issues += 1;
                                    $issues_array['clockout_system'] = 1;
                                }
                                $value = '';
                                $action2 = $this->getConditionSet('Clock Out', 3, 'action', 0);
                                if($this->getConditionSet('Clock Out', 3, 'action', 0) == 1){
                                    $value = date('H:i', strtotime($contractEnd));
                                }elseif($this->getConditionSet('Clock Out', 3, 'action', 0) == 2){
                                    $value = date('H:i', strtotime($clocks->time));
                                }
                                $system_work_end = $value;
                            endif; 
                            $data['clockout_contract'] = date('H:i', strtotime($contractEnd));
                            $data['clockout_punch'] = date('H:i', strtotime($clocks->time));
                            $data['clockout_system'] = $system_work_end;
                        elseif($clocks->attendance_type == 4 && $day_status == 2 && $out_status == 0 && $todayLastOutId == $clocks->id):
                            $out_status += 1;
                            $system_work_end = date('H:i', strtotime($clocks->time));

                            $data['clockout_contract'] = date('H:i', strtotime($contractEnd));
                            $data['clockout_punch'] = date('H:i', strtotime($clocks->time));
                            $data['clockout_system'] = date('H:i', strtotime($clocks->time));
                        elseif($clocks->attendance_type == 2):
                            $break_return['break_'.$br] = $clocks->time;
                            $br++;
                        elseif($clocks->attendance_type == 3):
                            $break_return['return_'.$br] = $clocks->time;
                            $br++;
                        endif;
                    endforeach;
                    /* End Loop for Attendance Feed from Live */

                    /* Start If clock Out not found */
                    if(!$this->isPunchExist($employee_id, $theDate, 4)):
                        if($day_status == 2):
                            $value = '';
                            $system_work_end = $value;
                        else:
                            $work_end = 1;
                            $notify = ($this->getConditionSet('Clock Out', 3, 'notify', 0) == 1) ? 'notfy_input' : '';
                            if($this->getConditionSet('Clock Out', 3, 'notify', 0) == 1){
                                $issues += 1;
                                $issues_array['clockout_system'] = 1;
                            }
                            $value = '';
                            $action2 = $this->getConditionSet('Clock Out', 3, 'action', 0);
                            if($this->getConditionSet('Clock Out', 3, 'action', 0) == 1){
                                $value = date('H:i', strtotime($contractEnd));
                            }
                            $system_work_end = $value;
                        endif;

                        $data['clockout_contract'] = date('H:i', strtotime($contractEnd));
                        $data['clockout_punch'] = '00:00';
                        $data['clockout_system'] = $value;
                    endif;
                    /* End If clock Out not found */

                    /* Start Break Calculations */
                    
                    $b = 1;
                    $b_start = '';
                    $break_details = '';

                    $total_break = 0;
                    $break_ids = [];
                    $breakArray = [];
                    $count = (!empty($break_return) ? count($break_return) : 0);
                    $break_issue_count = 0;

                    if(is_array($break_return) && !empty($break_return)):
                        $bi = 1;
                        $bik = 1;
                        $br_issue = 0;
                        foreach($break_return as $key => $time):
                            if($bi % 2 == 0){
                                if(strpos($key, 'return_') !== false){
                                    if(!isset($breakArray[$bik]['start'])):
                                        $breakArray[$bik]['start'] = '00:00:00';
                                        $issues += 1;
                                        $break_issue_count += 1;
                                    endif;

                                    $breakArray[$bik]['end'] = $time;
                                    $bik += 1;
                                }else{
                                    if(!isset($breakArray[$bik]['end'])):
                                        $breakArray[$bik]['end'] = '00:00:00';
                                        $issues += 1;
                                        $break_issue_count += 1;
                                        $bik += 1;
                                    endif;

                                    $breakArray[$bik]['start'] = $time;

                                    if($bi == $count){
                                        $breakArray[$bik]['end'] = '00:00:00';
                                        $issues += 1;
                                        $break_issue_count += 1;
                                        $bik += 1;
                                    }
                                }
                            }else{
                                if(strpos($key, 'break_') !== false){
                                    if(!isset($breakArray[$bik]['end']) && isset($breakArray[$bik]['start']) && $bik > 1):
                                        $breakArray[$bik]['end'] = '00:00:00';
                                        $issues += 1;
                                        $break_issue_count += 1;
                                        $bik += 1;
                                    endif;

                                    $breakArray[$bik]['start'] = $time;

                                    if($bi == $count){
                                        $breakArray[$bik]['end'] = '00:00:00';
                                        $issues += 1;
                                        $break_issue_count += 1;
                                        $bik += 1;
                                        $br_issue += 1;
                                    }
                                }else{
                                    if(!isset($breakArray[$bik]['start'])):
                                        $breakArray[$bik]['start'] = '00:00:00';
                                        $issues += 1;
                                        $break_issue_count += 1;
                                    endif;

                                    $breakArray[$bik]['end'] = $time;
                                    $bik += 1;
                                }
                            }
                            $bi++;
                        endforeach;
                    endif;
                    if(!empty($breakArray)):
                        foreach($breakArray as $brks):
                            $breakData = [];
                            $breakData['employee_id'] = $employee_id;
                            $breakData['date'] = $theDate;
                            $breakData['start'] = (isset($brks['start']) && !empty($brks['start']) && $brks['end'] != '00:00:00' ? date('H:i', strtotime(strtr($brks['start'], '/', '-'))) : '00:00');
                            $breakData['end'] = (isset($brks['end']) && !empty($brks['end']) && $brks['end'] != '00:00:00' ? date('H:i', strtotime(strtr($brks['end'], '/', '-'))) : '00:00');
                            $breakData['created_by'] = auth()->user()->id;
                            $breakData['total'] = 0;

                            if((isset($brks['start']) && !empty($brks['start']) && $brks['start'] != '00:00:00') && (isset($brks['end']) && !empty($brks['end']) && $brks['end'] != '00:00:00')):
                                $start = strtotime(date('H:i', strtotime(strtr($brks['start'], '/', '-'))));
                                $end = strtotime(date('H:i', strtotime(strtr($brks['end'], '/', '-'))));
                                $theBreakTotal = round(abs($start - $end) / 60, 2);
                                $total_break += $theBreakTotal;
                                $breakData['total'] = $theBreakTotal;
                            endif;
                            $theBreakRow = EmployeeAttendanceDayBreak::create($breakData);
                            $break_ids[] = $theBreakRow->id;
                        endforeach;
                    endif;

                    $break = ($this->convertStringToMinute($paid_break) + $this->convertStringToMinute($unpaid_break));
                    $unpaidBreakMinute = $this->convertStringToMinute($unpaid_break);
                    $actualBreak = 0;
                    if($break < $total_break):
                        $actualBreak = $total_break - $break;
                    endif;
                    $break_issue_count += ($total_break == 0 && $unpaidBreakMinute > 0 ? 1 : 0);
                    $issues += ($total_break == 0 && $unpaidBreakMinute > 0 ? 1 : 0);
                    $break_issue_count += ($unpaidBreakMinute > 0 && $total_break > $unpaidBreakMinute && ($total_break - $unpaidBreakMinute) > 15 ? 1 : 0);
                    $issues += ($unpaidBreakMinute > 0 && $total_break > $unpaidBreakMinute && ($total_break - $unpaidBreakMinute) > 15 ? 1 : 0);

                    if($break_issue_count > 0):
                        $issues_array['break_issue'] = $break_issue_count;
                    endif;
                    $data['break_details_html'] = '';//$break_details;
                    $data['total_break'] = $total_break;
                    /* End Break Calculations */

                    $data['paid_break'] = $paid_break;
                    $data['unpadi_break'] = $unpaid_break;
                    $data['adjustment'] = '+00:00';

                    if($work_start == 1 || $work_end == 1):
                        $total_work = 0;
                        $hours = '00';
                        $minutes = '00';
                    else:
                        $work_start = strtotime($work_start);
                        $work_end = strtotime($work_end);

                        $system_start = ($system_work_start != '' ? strtotime($system_work_start) : 0);
                        $system_end = ($system_work_end != '' ? strtotime($system_work_end) : 0);

                        if($system_end != '' && $system_end > 0 && $system_start != '' && $system_start > 0):
                            $total_today_break = $actualBreak + $this->convertStringToMinute($unpaid_break);

                            $total_today = round(abs($system_start - $system_end) / 60,2);
                            //$total_work = ($total_today > $total_today_break ? ($total_today - $total_today_break) : $total_today);
                            $total_work = ($total_today > $total_today_break ? ($total_today - $total_today_break) : 0);
                        else:
                            $total_work = 0;
                        endif;
                        $hours = floor($total_work / 60);
                        $hours = ($hours < 10 ? '0'.$hours : $hours);
                        $minutes = $total_work % 60;
                        $minutes = ($minutes < 10 ? '0'.$minutes : $minutes);
                    endif;
                    //if($is_leave_day == 1 && $today_leave_id > 0 && ($leave_type == 1 || $leave_type == 2)):
                        //$total_work += $leave_day_hours;
                    if($is_leave_day == 1 && $today_leave_id > 0):
                        $issues += 1;
                        $issues_array['work_leave'] = 1;
                    endif;

                    if($day_status == 2): 
                        $issues += 1; 
                        $issues_array['over_time'] = 1;
                    endif;

                    $data['total_work_hour'] = $total_work;
                    $data['user_issues'] = ($issues > 0 ? $issues : 0);
                    $data['leave_status'] = $leave_type;
                    $data['leave_hour'] = ($leave_type > 0) ? $leave_day_hours : 0;
                    $data['leave_adjustment'] = '+00:00';
                    $data['employee_leave_day_id'] = (isset($today_leave_id) && $today_leave_id > 0 && $leave_type > 0 ? $today_leave_id : null);
                    $data['overtime_status'] = ($day_status == 2) ? 1 : 0;
                    $data['isses_field'] = (!empty($issues_array) ? base64_encode(serialize($issues_array)) : null);
                    $data['note'] = '';
                    $data['status'] = 1; 
                    $data['created_by'] = auth()->user()->id;
                    
                    $EmployeeAttendance = EmployeeAttendance::create($data);
                    if($EmployeeAttendance->id && !empty($break_ids)):
                        EmployeeAttendanceDayBreak::where('employee_id', $employee_id)->where('date', $theDate)->whereIn('id', $break_ids)->update(['employee_attendance_id' => $EmployeeAttendance->id]);
                    endif;
                    if(isset($today_leave_id) && $today_leave_id > 0 && $leave_type > 0):
                        EmployeeLeaveDay::where('id', $today_leave_id)->update(['is_taken' => 1]);
                    endif;
                elseif($day_status == 1 && $todayAttendance->count() == 0):
                    $leave_type = ($leave_type > 0) ? $leave_type : 4;
                    $data['clockin_contract'] = '';
                    $data['clockin_punch'] = '';
                    $data['clockin_system'] = '';
                    $data['clockout_contract'] = '';
                    $data['clockout_punch'] = '';
                    $data['clockout_system'] = '';
                    $data['total_break'] = 0;
                    $data['paid_break'] = $paid_break;
                    $data['unpadi_break'] = $unpaid_break;
                    $data['adjustment'] = '+00:00';
                    $data['total_work_hour'] = 0;
                    $data['employee_leave_day_id'] = (isset($today_leave_id) && $today_leave_id > 0 ? $today_leave_id : null);
                    $data['leave_status'] = $leave_type;
                    $data['leave_hour'] = ($leave_type > 0) ? ($leave_day_hours > 0 ? $leave_day_hours : 0) : 0;
                    //$data['leave_hour'] = ($leave_type > 0) ? $total_mints_day : 0;
                    $data['leave_adjustment'] = '+00:00';
                    $data['note'] = '';
                    $data['user_issues'] = 0;
                    $data['isses_field'] = '';
                    $data['overtime_status'] = 0;
                    $data['status'] = 1; 
                    $data['created_by'] = auth()->user()->id;

                    EmployeeAttendance::create($data);
                    if(isset($today_leave_id) && $today_leave_id > 0):
                        EmployeeLeaveDay::where('id', $today_leave_id)->update(['is_taken' => 1]);
                    endif;
                endif;
            endif;
        endforeach;

        return 1;
    }

    public function convertStringToMinute($string){
        $min = 0;
        $str = explode(':', $string);

        $min += (isset($str[0]) && $str[0] != '') ? $str[0] * 60 : 0;
        $min += (isset($str[1]) && $str[1] != '') ? $str[1] : 0;

        return $min;
    }

    function calculateHourMinute($minutes){
        $hours = (intval(trim($minutes)) / 60 >= 1) ? intval(intval(trim($minutes)) / 60) : '00';
        $mins = (intval(trim($minutes)) % 60 != 0) ? intval(trim($minutes)) % 60 : '00';
     
        $hourMins = (($hours < 10 && $hours != '00') ? '0' . $hours : $hours);
        $hourMins .= ':';
        $hourMins .= ($mins < 10 && $mins != '00') ? '0'.$mins : $mins;
        
        return $hourMins;
    }

    function isPunchExist($employee_id, $date, $punch){
        $live = EmployeeAttendanceLive::where('employee_id', $employee_id)->where('date', $date)->where('attendance_type', $punch)->get()->first();
        return (isset($live->id) && $live->id > 0 ? true : false);
    }

    function getConditionSet($type, $frame, $col, $default = ''){
        $condition = HrCondition::where('type', $type)->where('time_frame', $frame)->get()->first();
        return (isset($condition->$col) && $condition->$col != '' ? $condition->$col : $default);
    }

    public function update(Request $request){
        parse_str($request->rowData, $rowData);
        $attendance = $rowData['attendance'];

        $rowNote = (isset($request->rowNote) && !empty($request->rowNote) ? $request->rowNote : null);

        if(!empty($request->leaveData)):
            parse_str($request->leaveData, $leaveData);
        endif;
        $leave = (isset($leaveData['attendance']) && !empty($leaveData['attendance']) ? $leaveData['attendance'] : []);
        $isLeaveRow = (isset($request->isLeaveRow) && $request->isLeaveRow ? true : false);

        if(!empty($attendance)):
            foreach($attendance as $attenid => $atten):
                $attendance_id = $atten['attendance_id'];
                
                $data = [];
                $data['adjustment'] = $atten['adjustment'];
                $data['clockin_system'] = $atten['clockin_system'];
                $data['clockout_system'] = $atten['clockout_system'];
                $data['total_work_hour'] = $atten['total_work_hour'];
                $data['total_break'] = $atten['total_break'];
                $data['paid_break'] = $atten['paid_break'];
                $data['unpadi_break'] = $atten['unpadi_break'];
                $data['user_issues'] = 0;
                $data['isses_field'] = null;
                $data['note'] = $rowNote;
                $data['updated_by'] = auth()->user()->id;

                if(isset($leave[$attenid]['leave_status']) &&  $leave[$attenid]['leave_status'] > 0):
                    $data['leave_adjustment'] = $leave[$attenid]['leave_adjustment'];
                    $data['leave_hour'] = $leave[$attenid]['leave_hour'];
                    $data['leave_status'] = $leave[$attenid]['leave_status'];
                elseif((isset($atten['leave_status']) && $atten['leave_status'] > 0) || ($isLeaveRow && isset($atten['leave_status']) && $atten['leave_status'] > 0)):
                    $data['leave_adjustment'] = $atten['leave_adjustment'];
                    $data['leave_hour'] = $atten['leave_hour'];
                    $data['leave_status'] = $atten['leave_status'];
                else:
                    $leave_status = (isset($atten['leave_status']) && $atten['leave_status'] > 0 ? $atten['leave_status'] : 0);
                    $data['leave_status'] = $leave_status;
                endif;

                //return response()->json($data);
                EmployeeAttendance::where('id', $attendance_id)->update($data);
            endforeach;

            return response()->json(['res' => 'The attendance row has been successfully updated.'], 200);
        else:
            return response()->json(['res' => 'Something went wrong. Please try later.'], 422);
        endif;
    }

    public function updateAll(Request $request){
        parse_str($request->allData, $rowData);
        $attendance = $rowData['attendance'];

        if(!empty($attendance)):
            foreach($attendance as $atten):
                if(isset($atten['id']) && $atten['id'] > 0):
                    $attendance_id = $atten['attendance_id'];
                    $leave_status = (isset($atten['leave_status']) && $atten['leave_status'] > 0 ? $atten['leave_status'] : 0);
                    $isOnlyLeave = (isset($atten['only_leave']) && $atten['only_leave'] == 1 ? true : false);
                    $data = [];
                    $data['adjustment'] = $atten['adjustment'];
                    $data['clockin_system'] = $atten['clockin_system'];
                    $data['clockout_system'] = $atten['clockout_system'];
                    $data['total_work_hour'] = $atten['total_work_hour'];
                    $data['total_break'] = $atten['total_break'];
                    $data['paid_break'] = $atten['paid_break'];
                    $data['unpadi_break'] = $atten['unpadi_break'];
                    $data['user_issues'] = 0;
                    $data['isses_field'] = null;
                    $data['note'] = (isset($atten['note']) && !empty($atten['note']) ? $atten['note'] : null);
                    $data['leave_status'] = $leave_status;
                    $data['updated_by'] = auth()->user()->id;

                    if((isset($atten['leave_status']) &&  $atten['leave_status'] > 0) || $isOnlyLeave):
                        $data['leave_adjustment'] = $atten['leave_adjustment'];
                        $data['leave_hour'] = $atten['leave_hour'];
                    endif;

                    EmployeeAttendance::where('id', $attendance_id)->update($data);
                endif;
            endforeach;

            return response()->json(['res' => 'The attendance row has been successfully updated.'], 200);
        else:
            return response()->json(['res' => 'Something went wrong. Please try later.'], 422);
        endif;
    }

    public function edit(Request $request){
        $rowID = $request->rowID;
        $attendance = EmployeeAttendance::find($rowID);
        $theDayTotal = 0;

        // One "card" per break, matching the drawer's field/readout language. The JS
        // hooks (.breakRow / .breakStart / .breakEnd / .breakRowTotal / .breakGrandTotal)
        // and the field names are unchanged, so edit + save keep working.
        $breakCard = function($idx, $start, $end, $total, $nameStart, $nameEnd, $nameTotal){
            return '<div class="att-brk breakRow">'
                . '<div class="att-brk__num">Break '.$idx.'</div>'
                . '<div class="att-brk__grid">'
                    . '<label class="att-field"><span>Start</span>'
                        . '<input value="'.$start.'" type="text" class="att-input att-input--time breakStart timepicker" name="'.$nameStart.'"/></label>'
                    . '<i data-lucide="arrow-right" class="att-brk__arrow w-4 h-4"></i>'
                    . '<label class="att-field"><span>End</span>'
                        . '<input value="'.$end.'" type="text" class="att-input att-input--time breakEnd timepicker" name="'.$nameEnd.'"/></label>'
                    . '<div class="att-field att-field--dur"><span>Duration</span>'
                        . '<input readonly value="'.$total.'" type="text" class="att-input att-input--time att-input--readonly breakRowTotal timepicker" name="'.$nameTotal.'"/></div>'
                . '</div>'
            . '</div>';
        };

        $cards = '';
        if(isset($attendance->breaks) && $attendance->breaks->count() > 0):
            $i = 1;
            foreach($attendance->breaks as $brks):
                $cards .= $breakCard(
                    $i, $brks->start, $brks->end, $this->calculateHourMinute($brks->total),
                    'breaks['.$rowID.']['.$brks->id.'][start]',
                    'breaks['.$rowID.']['.$brks->id.'][end]',
                    'breaks['.$rowID.']['.$brks->id.'][total]'
                );
                $theDayTotal += $brks->total;
                $i++;
            endforeach;
        else:
            $cards .= $breakCard(
                1, '', '', '',
                'newBreaks['.$rowID.'][start]',
                'newBreaks['.$rowID.'][end]',
                'newBreaks['.$rowID.'][total]'
            );
        endif;

        $html = '<div class="att-breaklist">'
            . $cards
            . '<div class="att-brk-total">'
                . '<span class="att-brk-total__label">Day total</span>'
                . '<input value="'.$this->calculateHourMinute($theDayTotal).'" type="text" class="att-input att-input--time att-input--readonly att-brk-total__value breakGrandTotal" readonly name="total_break"/>'
            . '</div>'
        . '</div>';

        return response()->json(['res' => $html], 200);
    }

    public function updateBreak(Request $request){
        $attendance_id = $request->id;
        $employeeAttendance = EmployeeAttendance::find($attendance_id);
        $total_break = (isset($request->total_break) && !empty($request->total_break) && $request->total_break> 0 ?  $this->convertStringToMinute($request->total_break) : 0);
        $breaks = (isset($request->breaks) && !empty($request->breaks) ? $request->breaks : []);
        $newBreaks = (isset($request->newBreaks) && !empty($request->newBreaks) ? $request->newBreaks : []);
        //return response()->json($breaks);

        $grand_total = 0;
        if(!empty($breaks)):
            foreach($breaks as $attendance_id => $break):
                foreach($break as $break_id => $brk):
                    $total = (isset($brk['total']) && !empty($brk['total']) ? $this->convertStringToMinute($brk['total']) : 0);
                    $grand_total += $total;

                    $data = [];
                    $data['start'] = (isset($brk['start']) && !empty($brk['start']) ? $brk['start'] : '00:00');
                    $data['end'] = (isset($brk['end']) && !empty($brk['end']) ? $brk['end'] : '00:00');
                    $data['total'] = $total;
                    $data['updated_by'] = auth()->user()->id;

                    EmployeeAttendanceDayBreak::where('id', $break_id)->update($data);
                endforeach;
            endforeach;
        elseif(!empty($newBreaks)):
            $brk = (isset($newBreaks[$attendance_id]) && !empty($newBreaks[$attendance_id]) ? $newBreaks[$attendance_id] : []);
            if(!empty($brk)):
                $total = (isset($brk['total']) && !empty($brk['total']) ? $this->convertStringToMinute($brk['total']) : 0);
                $grand_total += $total;

                $data = [];
                $data['employee_attendance_id'] = $attendance_id;
                $data['employee_id'] = $employeeAttendance->employee_id;
                $data['date'] = (isset($employeeAttendance->date) && !empty($employeeAttendance->date) ? date('Y-m-d', strtotime($employeeAttendance->date)) : null);
                $data['start'] = (isset($brk['start']) && !empty($brk['start']) ? $brk['start'] : '00:00');
                $data['end'] = (isset($brk['end']) && !empty($brk['end']) ? $brk['end'] : '00:00');
                $data['total'] = $total;
                $data['created_by'] = auth()->user()->id;

                EmployeeAttendanceDayBreak::create($data);
            endif;
        endif;
        $actualBreakTaken = ($total_break == $grand_total ? $total_break : $grand_total);

        $isses_field = (isset($employeeAttendance->isses_field) && !empty($employeeAttendance->isses_field) ? unserialize(base64_decode($employeeAttendance->isses_field)) : []);
        $user_issues = (isset($employeeAttendance->user_issues) && $employeeAttendance->user_issues > 0 ? $employeeAttendance->user_issues : 0);
        $break_issue = (isset($isses_field['break_issue']) && $isses_field['break_issue'] == 1) ? 1 : 0;
        if($user_issues > 0 && $break_issue == 1):
            $user_issues -= 1;
            unset($isses_field['break_issue']);
        endif;

        $total_break = (isset($employeeAttendance->total_break) && $employeeAttendance->total_break > 0 ? $employeeAttendance->total_break : 0);
        // Was $employeeAttendance->tottotal_work_hourl_break - a property that does not
        // exist, so this always read null and the over-allowance branch below wrote the
        // employee's hours down to 0.
        $total_work_hour = (isset($employeeAttendance->total_work_hour) && $employeeAttendance->total_work_hour > 0 ? $employeeAttendance->total_work_hour : 0);

        $paid_break = (!empty($employeeAttendance->paid_break) ? $this->convertStringToMinute($employeeAttendance->paid_break) : 0);
        $unpaid_break = (!empty($employeeAttendance->unpadi_break) ? $this->convertStringToMinute($employeeAttendance->unpadi_break) : 0);
        $allowedBreak = ($paid_break + $unpaid_break);

        $data = [];                            
        if($actualBreakTaken > $allowedBreak){
            $deduct = ($actualBreakTaken - $allowedBreak);
            $new_total_work_hour = ($total_work_hour - $unpaid_break) - $deduct;
            $total_work_hour = ($new_total_work_hour > 0 ? $new_total_work_hour : $total_work_hour);

            $data['total_work_hour'] = $total_work_hour;
            $data['total_break'] = $actualBreakTaken;
            $data['break_details_html'] = '';
        }else{
            $data['total_break'] = $actualBreakTaken;
            $data['break_details_html'] = '';
        }
        $data['user_issues'] = $user_issues;
        $data['isses_field'] = base64_encode(serialize($isses_field));

        EmployeeAttendance::where('id', $attendance_id)->update($data); 
        
        return response()->json(['res' => $isses_field], 200);
    }


    public function destroy(Request $request){
        $theDate = (isset($request->theDate) && !empty($request->theDate) ? date('Y-m-d', strtotime($request->theDate)) : '');
        if(!empty($theDate)):
            $leaveDayIds = EmployeeAttendance::where('date', $theDate)->pluck('employee_leave_day_id')->unique()->toArray();
            $empAttendance = EmployeeAttendance::where('date', $theDate)->forceDelete();
            if(!empty($leaveDayIds)):
                $leaveDays = EmployeeLeaveDay::whereIn('id', $leaveDayIds)->update(['is_taken' => 0]);
            endif;
            return response()->json(['suc' => 1, 'msg' => 'Employee attendance of <strong>'.date('jS F, Y').'</strong> successfully deleted.'], 200);
        else:
            return response()->json(['suc' => 2, 'msg' => 'Something went wrong. Please try later.'], 200);
        endif;
    }

    public function reSyncronise(Request $request){
        $employee_id = $request->employee_id;
        $the_date = date('Y-m-d', strtotime($request->the_date));

        $empLeaveDay = EmployeeLeaveDay::where('leave_date', $the_date)->where('was_absent_day', 1)
                        ->whereHas('leave', function($q) use($employee_id, $the_date){
                            $q->where('employee_id', $employee_id)->where('from_date', $the_date)->where('to_date', $the_date)
                                ->whereIn('leave_type', [2, 3, 4, 5]);
                        })->get()->first();
        if(isset($empLeaveDay->id) && $empLeaveDay->id > 0):
            $leave_day_id = $empLeaveDay->id;
            $leave_id = $empLeaveDay->leave->id;
            EmployeeLeaveDay::where('id', $leave_day_id)->forceDelete();
            EmployeeLeave::where('id', $leave_id)->forceDelete();
        endif;

        $deleteAttendance = EmployeeAttendance::where('employee_id', $employee_id)->where('date', $the_date)->forceDelete();
        $syncronised = $this->syncroniseAttendanceData($the_date, $employee_id);

        return response()->json(['res' => 1], 200);
    }
}
