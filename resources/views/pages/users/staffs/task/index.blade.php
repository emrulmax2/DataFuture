@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
<main class="tkm-main">

    {{-- ── page head ─────────────────────────────────────────────── --}}
    <div class="tkm-pagehead">
        <div>
            <div class="tkm-pagehead__eyebrow">Outstanding work</div>
            <h1 class="tkm-pagehead__title">Task Manager</h1>
        </div>
        <div class="tkm-pagehead__tail">
            @if(!empty($mytasks))
                <span class="tkm-count">
                    <span class="tkm-count__label">In queue</span>
                    <span class="tkm-count__value">{{ number_format($pendingTotal ?? 0) }}</span>
                </span>
            @endif
            <a href="{{ route('dashboard') }}" class="tkm-btn tkm-btn--green">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 6l-6 6 6 6"></path></svg>
                Back to Dashboard
            </a>
        </div>
    </div>

    {{-- ── assigned tasks ────────────────────────────────────────── --}}
    <section class="tkm-card">
        <div class="tkm-tablehead">
            <h2 class="tkm-tablehead__title">Tasks</h2>
            <span class="tkm-tablehead__note">Select a task to view its student list</span>
            @if(!empty($mytasks))
                <span class="tkm-tablehead__count">{{ count($mytasks) }} {{ Str::plural('task type', count($mytasks)) }}</span>
            @endif
        </div>

        @if(!empty($mytasks))
            <div class="tkm-tiles">
                @foreach($mytasks as $task_id => $task)
                    @php
                        // Applicant-phase tasks queue applicants, not students;
                        // the detail screen makes the same distinction.
                        $tileSubject = (($task->processlist->phase ?? 'Live') == 'Applicant') ? 'Applicants' : 'Students';
                    @endphp
                    <a href="{{ route('task.manager.show', $task_id) }}" class="tkm-tile">
                        <div class="tkm-tile__name">{{ $task->name }}</div>
                        @if(!empty($task->short_description))
                            <div class="tkm-tile__desc">{{ $task->short_description }}</div>
                        @endif
                        <div class="tkm-tile__foot">
                            <span class="tkm-tile__count">{{ $task->pending_task }}</span>
                            <span class="tkm-tile__subject">{{ $tileSubject }}</span>
                            <svg class="tkm-tile__go" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h13M13 7l5 5-5 5"></path></svg>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="tkm-empty">
                <div class="tkm-empty__title">Nothing waiting on you</div>
                <div class="tkm-empty__note">There are no pending tasks assigned to your account.</div>
            </div>
        @endif
    </section>
</main>
@endsection
