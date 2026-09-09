{{--
    One Service Desk ticket, rendered into the panel on the student notes page.

    Laid out as the conversation it is: the facts at the top, then the thread,
    with the person who raised it on one side and everyone answering on the
    other — the same shape it has in Operations, so somebody who has seen it
    there recognises it here.

    Read-only by construction. There is nothing to reply with, because replying
    happens in Operations where the ticket's own rules live. Internal notes are
    stripped at that end and never reach this template.
--}}
@php
    $tone = match ($ticket['status'] ?? '') {
        'new', 'assigned' => 'text-primary bg-primary/10',
        'in_progress'     => 'text-warning bg-warning/10',
        'resolved'        => 'text-success bg-success/10',
        'reopened'        => 'text-danger bg-danger/10',
        default           => 'text-slate-500 bg-slate-100',
    };
@endphp

<div class="sd-panel">
    <div class="sd-panel-head">
        <div class="flex items-center gap-2 flex-wrap">
            <span class="font-mono text-xs text-slate-500">{{ $ticket['ref'] }}</span>
            <span class="px-2 py-1 rounded text-xs font-medium {{ $tone }}">{{ $ticket['status_label'] }}</span>

            @if (! empty($ticket['is_overdue']))
                <span class="px-2 py-1 rounded text-xs font-medium text-danger bg-danger/10">Overdue</span>
            @endif

            @if (($ticket['priority'] ?? '') === 'high')
                <span class="px-2 py-1 rounded text-xs font-medium text-danger bg-danger/10">High priority</span>
            @endif
        </div>

        <h3 class="sd-panel-subject">{{ $ticket['subject'] }}</h3>

        <div class="sd-panel-facts">
            <div>
                <div class="sd-panel-label">Sent to</div>
                <div>{{ $ticket['department'] ?? $ticket['target'] }}@if (! empty($ticket['issue_type'])) · {{ $ticket['issue_type'] }}@endif</div>
            </div>
            <div>
                <div class="sd-panel-label">Raised by</div>
                <div>{{ $ticket['raised_by'] }} · {{ $ticket['raised_ago'] }}</div>
            </div>
            <div>
                <div class="sd-panel-label">Assigned to</div>
                <div>
                    @if (! empty($ticket['assigned_to']))
                        {{ implode(', ', $ticket['assigned_to']) }}
                    @else
                        <span class="text-warning">Nobody yet</span>
                    @endif
                </div>
            </div>
        </div>

        @if (! empty($ticket['students']))
            <div class="mt-3">
                <div class="sd-panel-label">About</div>
                <div>
                    {{ implode(', ', array_column($ticket['students'], 'label')) }}
                    <span class="text-slate-400">· tagged for the record; students are not notified</span>
                </div>
            </div>
        @endif
    </div>

    <div class="sd-panel-thread">
        @forelse ($ticket['messages'] as $message)
            @php $mine = ! empty($message['is_requester']); @endphp

            <div class="sd-msg {{ $mine ? 'is-requester' : '' }}">
                {{-- Whose message this is, at a glance. The initials come from
                     Operations so both systems draw the same two letters for
                     the same person. --}}
                <span class="sd-msg-avatar" title="{{ $message['author'] }}" aria-hidden="true">{{ $message['author_initials'] ?? '—' }}</span>

                <div class="sd-msg-main">
                    <div class="sd-msg-meta">{{ $message['author'] }} · {{ $message['said'] }}</div>

                    @if (! empty($message['body']))
                        <div class="sd-msg-body">{!! nl2br(e($message['body'])) !!}</div>
                    @endif

                    @if (! empty($message['attachments']))
                        <div class="sd-msg-files">
                        @foreach ($message['attachments'] as $file)
                            {{-- Through this app, not straight at Operations: that
                                 endpoint answers to a shared key a browser cannot
                                 present. Operations still refuses anything on an
                                 internal note. --}}
                            <a href="{{ route('student.service-desk.attachment', $file['id']) }}"
                               class="sd-msg-file" title="{{ $file['name'] }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                     stroke-linejoin="round" aria-hidden="true">
                                    <path d="M13.234 20.252 21 12.3"/>
                                    <path d="m16 6-8.414 8.586a2 2 0 0 0 0 2.828 2 2 0 0 0 2.828 0l8.414-8.586a4 4 0 0 0 0-5.656 4 4 0 0 0-5.656 0l-8.415 8.585a6 6 0 1 0 8.486 8.486"/>
                                </svg>
                                <span class="sd-msg-file-name">{{ $file['name'] }}</span>
                                <span class="sd-msg-file-size">{{ $file['size'] }}</span>
                            </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-slate-500 text-sm p-5">Nothing has been said on this ticket yet.</p>
        @endforelse
    </div>
</div>
