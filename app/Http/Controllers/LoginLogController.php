<?php

namespace App\Http\Controllers;

use App\Models\LoginLog;
use App\Models\User;
use App\Models\StudentUser;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LoginLogController extends Controller
{
    public function index()
    {
        return view('pages/login-log/index', [
            'title'       => 'Login Log - DataFutureD',
            'breadcrumbs' => [
                ['label' => 'Login Log', 'href' => 'javascript:void(0);'],
            ],
        ]);
    }

    public function list(Request $request)
    {
        $queryStr     = $request->filled('querystr')     ? $request->querystr     : '';
        $actorType    = $request->filled('actor_type')   ? $request->actor_type   : '';
        $logoutReason = $request->filled('logout_reason')? $request->logout_reason: '';
        $dateFrom     = $request->filled('date_from')    ? $request->date_from    : '';
        $dateTo       = $request->filled('date_to')      ? $request->date_to      : '';

        $sorters = (isset($request->sorters) && !empty($request->sorters))
            ? $request->sorters
            : [['field' => 'id', 'dir' => 'DESC']];

        $sorts = [];
        foreach ($sorters as $sort) {
            $sorts[] = 'login_logs.' . $sort['field'] . ' ' . $sort['dir'];
        }

        $query = LoginLog::orderByRaw(implode(',', $sorts));

        // Actor type filter
        if (!empty($actorType)) {
            $query->where('actor_type', $actorType);
        }

        // Logout reason filter
        if ($logoutReason === 'active') {
            $query->whereNull('logout_at');
        } elseif (!empty($logoutReason)) {
            $query->where('logout_reason', $logoutReason);
        }

        // Date range filter on login_at
        if (!empty($dateFrom)) {
            $query->whereDate('login_at', '>=', Carbon::parse($dateFrom)->format('Y-m-d'));
        }
        if (!empty($dateTo)) {
            $query->whereDate('login_at', '<=', Carbon::parse($dateTo)->format('Y-m-d'));
        }

        // Text search — we resolve names after fetching, but filter on stored fields pre-fetch
        // (Deep name-search is handled post-fetch below when actor data is loaded)

        $total_rows = $query->count();
        $page    = ($request->filled('page') && $request->page > 0) ? (int)$request->page : 1;
        $perpage = ($request->filled('size') && $request->size === 'true')
            ? $total_rows
            : (($request->filled('size') && $request->size > 0) ? (int)$request->size : 10);
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';
        $offset    = ($page - 1) * $perpage;

        $logs = $query->skip($offset)->take($perpage)->get();

        // Batch-load actor info
        $userIds        = $logs->where('actor_type', 'user')->pluck('actor_id')->unique()->values();
        $studentUserIds = $logs->where('actor_type', 'student_user')->pluck('actor_id')->unique()->values();

        $users        = User::with('employee','employee.employment','employee.employment.employeeJobTitle')->whereIn('id', $userIds)->get()->keyBy('id');
        $studentUsers = StudentUser::with('student')->whereIn('id', $studentUserIds)->get()->keyBy('id');

        $data = [];
        $i    = (($page - 1) * $perpage) + 1;

        foreach ($logs as $log) {
            $actorName  = 'N/A';
            $actorEmail = 'N/A';


            if ($log->actor_type === 'user' && isset($users[$log->actor_id])) {
                $u          = $users[$log->actor_id];
                $actorName  = $u->employee->full_name  ?? 'N/A';
                $actorEmail = $u->employee->employment->employeeJobTitle->name ?? 'N/A';
            } elseif ($log->actor_type === 'student_user' && isset($studentUsers[$log->actor_id])) {
                $u          = $studentUsers[$log->actor_id];
                $actorName  = ($u->student->full_name ?? '');
                $actorName  = trim($actorName) ?: 'N/A';
                $actorEmail = $u->student->registration_no ?? 'N/A';
            }

            // Client-side text search across actor name and email
            if (!empty($queryStr)) {
                $needle = strtolower($queryStr);
                if (
                    strpos(strtolower($actorName), $needle)  === false &&
                    strpos(strtolower($actorEmail), $needle) === false &&
                    strpos(strtolower($log->ip_address ?? ''), $needle) === false
                ) {
                    continue;
                }
            }

            // Duration
            $duration = null;
            if ($log->logout_at) {
                $mins     = $log->login_at->diffInMinutes($log->logout_at);
                $duration = floor($mins / 60) . 'h ' . ($mins % 60) . 'm';
            }

            $data[] = [
                'id'            => $log->id,
                'sl'            => $i,
                'actor_id'      => $log->actor_id,
                'actor_type'    => $log->actor_type,
                'actor_name'    => $actorName,
                'actor_email'   => $actorEmail,
                'guard_name'    => $log->guard_name,
                'login_at'      => $log->login_at  ? $log->login_at->format('d-m-Y H:i:s')  : '',
                'logout_at'     => $log->logout_at ? $log->logout_at->format('d-m-Y H:i:s') : '',
                'logout_reason' => $log->logout_reason ?? '',
                'ip_address'    => $log->ip_address ?? '',
                'duration'      => $duration,
                'device'        => $log->device   ?? '',
                'platform'      => $log->platform ?? '',
                'browser'       => $log->browser  ?? '',
                'country'       => $log->country  ?? '',
                'city'          => $log->city     ?? '',
            ];
            $i++;
        }

        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }
}
