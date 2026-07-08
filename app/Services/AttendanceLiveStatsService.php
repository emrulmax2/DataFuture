<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeAttendanceLive;

class AttendanceLiveStatsService
{
    public function getUserAttendanceLiveStatistics(): string
    {
        if (!auth()->check() || !isset(auth()->user()->employee->id)) {
            return '';
        }

        $employeeId = auth()->user()->employee->id;
        $today = date('Y-m-d');
        $employee = Employee::find($employeeId);

        if (!$employee || !isset($employee->employment->id)) {
            return '';
        }

        $html = '';
        $lastDate = (isset($employee->employment->last_action_date) && $employee->employment->last_action_date != '')
            ? $employee->employment->last_action_date
            : '';
        $lastAction = (isset($employee->employment->last_action) && $employee->employment->last_action > 0)
            ? $employee->employment->last_action
            : 0;

        $lastActionLabel = '';
        $lastActionClass = '';
        switch ($lastAction) {
            case 1:
                $lastActionLabel = 'Working';
                break;
            case 2:
                $lastActionLabel = 'Break';
                $lastActionClass = ' text-red-800';
                break;
            case 3:
                $lastActionLabel = 'Working';
                break;
            case 4:
                $lastActionLabel = 'Clocked Out';
                break;
            default:
                $lastActionLabel = 'No clock-in';
        }

        $live = EmployeeAttendanceLive::where('attendance_type', 1)
            ->where('date', $today)
            ->where('employee_id', $employeeId)
            ->orderBy('id', 'DESC')
            ->first();

        $liveLast = EmployeeAttendanceLive::where('attendance_type', 4)
            ->where('date', $today)
            ->where('employee_id', $employeeId)
            ->orderBy('id', 'DESC')
            ->first();

        if ($today == $lastDate && isset($live->id) && $live->id > 0) {
            $rtime = (isset($live->time) && $live->time != '00:00:00' && $live->time)
                ? strtotime($live->time)
                : strtotime(date('H:i:s'));
            $durationSeconds = $rtime * 1000;

            
            $html .= '<div class="clockinStatistics inline-flex justify-end items-start ml-auto">';
            $html .= '<div class="statusArea">';
            $html .= '<div class="text-slate-500 text-xs whitespace-nowrap uppercase">Status</div>';
            $html .= '<div class="font-medium whitespace-nowrap uppercase' . $lastActionClass . '">' . $lastActionLabel . '</div>';
            $html .= '</div>';
            $html .= '<div class="sinceArea">';
            $html .= '<div class="text-slate-500 text-xs whitespace-nowrap uppercase">since</div>';
            $html .= '<div class="font-medium whitespace-nowrap uppercase">'
                . date('H:i A', strtotime($live->time))
                . (isset($liveLast->time) && !empty($liveLast->time) ? ' - ' . date('H:i A', strtotime($liveLast->time)) : '')
                . '</div>';
            if ($lastAction != 4) {
                $html .= '<div class="text-slate-500 text-xs whitespace-nowrap clockedInFrom" id="clockedInFrom" data-starts="' . $durationSeconds . '">00:00</div>';
            }
            $html .= '</div>';
            $html .= '</div>';
        } else {
            $html .= '<div class="clockinStatistics inline-flex justify-end items-start ml-auto">';
            $html .= '<div class="statusArea">';
            $html .= '<div class="text-slate-500 text-xs whitespace-nowrap uppercase">Status</div>';
            $html .= '<div class="font-medium whitespace-nowrap uppercase text-danger">No clock-in</div>';
            $html .= '</div>';
            $html .= '</div>';
        }

        return $html;
    }
}
