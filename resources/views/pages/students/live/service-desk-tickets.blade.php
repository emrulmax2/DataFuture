{{--
    Service Desk tickets this student has been tagged on.

    They live in Operations and are worked there — this is a read-only window on
    them, so there is nothing to add, edit or reply to here. A row opens the
    conversation in a panel rather than navigating away, because somebody
    reading a student's notes is usually checking context, not switching task.

    $serviceDeskTickets is null when Operations could not be reached, which is a
    different thing from "no tickets" and is said differently.
--}}
<div class="intro-y box mt-5 student-profile-notes">
    <div class="student-profile-secthead">
        <div class="student-profile-secthead-title">
            <div class="font-medium text-base">Service Desk tickets</div>
        </div>
        <div class="student-profile-secthead-actions">
            <span class="text-slate-500 text-xs">
                Raised in Operations by staff, tagged to this student
            </span>
        </div>
    </div>

    @if ($serviceDeskTickets === null)
        <div class="p-5 text-slate-500 text-sm">
            <i data-lucide="alert-triangle" class="w-4 h-4 inline-block mr-1 text-warning"></i>
            The Service Desk could not be reached, so tickets are not shown. This does not mean there are none.
        </div>
    @elseif (count($serviceDeskTickets) === 0)
        <div class="p-5 text-slate-500 text-sm">No tickets have been tagged to this student.</div>
    @else
        {{-- Bordered, like the Notes table above it. Not `table-report`, which
             draws each row as a floating card — two different table styles in
             one page read as two different pages. --}}
        <div class="sd-tickets-wrap">
            <table class="sd-tickets-table">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Subject</th>
                        <th>Sent to</th>
                        <th>Raised by</th>
                        <th>Status</th>
                        <th class="is-right">Updated</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($serviceDeskTickets as $ticket)
                        @php
                            /* The Operations palette, so a status reads the same in both systems. */
                            $tone = match ($ticket['status'] ?? '') {
                                'new'         => 'text-primary bg-primary/10',
                                'assigned'    => 'text-primary bg-primary/10',
                                'in_progress' => 'text-warning bg-warning/10',
                                'resolved'    => 'text-success bg-success/10',
                                'reopened'    => 'text-danger bg-danger/10',
                                default       => 'text-slate-500 bg-slate-100',
                            };
                        @endphp

                        <tr class="sd-ticket-row" data-ticket="{{ $ticket['id'] }}">
                            <td class="is-ref">{{ $ticket['ref'] }}</td>
                            <td>
                                {{ $ticket['subject'] }}
                                @if (($ticket['messages_count'] ?? 0) > 0)
                                    <span class="text-slate-400 text-xs">· {{ $ticket['messages_count'] }} {{ \Illuminate\Support\Str::plural('message', $ticket['messages_count']) }}</span>
                                @endif
                            </td>
                            <td class="is-nowrap">
                                {{ $ticket['department'] ?? $ticket['target'] }}
                                @if (! empty($ticket['issue_type']))
                                    <span class="text-slate-400">· {{ $ticket['issue_type'] }}</span>
                                @endif
                            </td>
                            <td class="is-nowrap">{{ $ticket['raised_by'] }}</td>
                            <td class="is-nowrap">
                                <span class="sd-badge {{ $tone }}">{{ $ticket['status_label'] }}</span>
                                @if (! empty($ticket['is_overdue']))
                                    <span class="sd-badge text-danger bg-danger/10">Overdue</span>
                                @endif
                            </td>
                            <td class="is-nowrap is-right is-muted">{{ $ticket['updated_ago'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
