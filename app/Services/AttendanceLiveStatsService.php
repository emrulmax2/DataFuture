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

        $lastDate = (isset($employee->employment->last_action_date) && $employee->employment->last_action_date != '')
            ? $employee->employment->last_action_date
            : '';
        $lastAction = (isset($employee->employment->last_action) && $employee->employment->last_action > 0)
            ? $employee->employment->last_action
            : 0;

        $lastActionLabel = '';
        $lastActionState = 'no-clock-in';
        switch ($lastAction) {
            case 1:
                $lastActionLabel = 'Working';
                $lastActionState = 'working';
                break;
            case 2:
                $lastActionLabel = 'Break';
                $lastActionState = 'break';
                break;
            case 3:
                $lastActionLabel = 'Working';
                $lastActionState = 'working';
                break;
            case 4:
                $lastActionLabel = 'Clocked Out';
                $lastActionState = 'clocked-out';
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

            $timeRange = date('H:i A', strtotime($live->time))
                . (isset($liveLast->time) && !empty($liveLast->time) ? ' - ' . date('H:i A', strtotime($liveLast->time)) : '');

            $html = '<div class="lcc-clock-status lcc-clock-status--' . $lastActionState . '">';
            $html .= '<span class="lcc-clock-status__state">';
            $html .= '<span class="lcc-clock-status__label">Status</span>';
            $html .= '<span class="lcc-clock-status__dot" aria-hidden="true"></span>';
            $html .= '<strong>' . e($lastActionLabel) . '</strong>';
            $html .= '</span>';
            $html .= '<span class="lcc-clock-status__since">';
            $html .= '<span class="lcc-clock-status__label">Since</span>';
            $html .= '<strong>' . e($timeRange) . '</strong>';
            if ($lastAction != 4) {
                $html .= '<small class="clockedInFrom" id="clockedInFrom" data-starts="' . $durationSeconds . '">0h 0m</small>';
            }
            $html .= '</span>';
            $html .= '</div>';
        } else {
            $html = '<div class="lcc-clock-status lcc-clock-status--no-clock-in">';
            $html .= '<span class="lcc-clock-status__state">';
            $html .= '<span class="lcc-clock-status__label">Status</span>';
            $html .= '<strong>No clock-in</strong>';
            $html .= '</span>';
            $html .= '</div>';
        }

        return $html;
    }
}
