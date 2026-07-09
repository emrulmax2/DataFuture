<div class="ep-holiday-review">
    <div class="ep-holiday-review__intro">
        Review each requested date and choose whether to approve or deny it before saving.
    </div>

    <div class="ep-holiday-review-table">
        <table class="table leaveRequestDaysTable">
            <thead>
                <tr>
                    <th class="whitespace-nowrap">Date</th>
                    <th class="whitespace-nowrap">Hour</th>
                    <th class="whitespace-nowrap text-center">Approve</th>
                    <th class="whitespace-nowrap text-center">Deny</th>
                </tr>
            </thead>
            <tbody>
                @forelse(($employeeLeave?->leaveDays ?? collect()) as $leaveDay)
                    @php
                        $approveId = 'leave-day-'.$leaveDay->id.'-approve';
                        $denyId = 'leave-day-'.$leaveDay->id.'-deny';
                    @endphp
                    <tr>
                        <td>
                            <div class="ep-holiday-review__date">
                                <strong>{{ date('D jS F, Y', strtotime($leaveDay->leave_date)) }}</strong>
                            </div>
                        </td>
                        <td>
                            <span class="ep-holiday-review__hour">{{ $controller->calculateHourMinute($leaveDay->hour) }}</span>
                        </td>
                        <td class="text-center">
                            <label for="{{ $approveId }}" class="ep-holiday-review__choice">
                                <input
                                    id="{{ $approveId }}"
                                    {{ (isset($leaveDay->supervision_status) && $leaveDay->supervision_status == 1 ? 'checked' : '') }}
                                    class="ep-holiday-review__radio"
                                    name="leaveDay[{{ $leaveDay->id }}]"
                                    type="radio"
                                    value="Active"
                                >
                                <span class="sr-only">Approve {{ date('D jS F, Y', strtotime($leaveDay->leave_date)) }}</span>
                            </label>
                        </td>
                        <td class="text-center">
                            <label for="{{ $denyId }}" class="ep-holiday-review__choice is-deny">
                                <input
                                    id="{{ $denyId }}"
                                    {{ (isset($leaveDay->supervision_status) && $leaveDay->supervision_status == 2 ? 'checked' : '') }}
                                    class="ep-holiday-review__radio"
                                    name="leaveDay[{{ $leaveDay->id }}]"
                                    type="radio"
                                    value="In Active"
                                >
                                <span class="sr-only">Deny {{ date('D jS F, Y', strtotime($leaveDay->leave_date)) }}</span>
                            </label>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">
                            <div class="ep-holiday-review__empty">No leave days were found for this request.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
