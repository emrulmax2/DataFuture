@extends('pages.attendance-integrity.shell')

@section('aim_page', 'guide')
@section('aim_eyebrow', 'Help')

@section('aim_sub')
    What Attendance Integrity does, how to read it, and how to work a case. Five minutes to read. Figures below are the current Settings.
@endsection

@section('aim_actions')
    @if($can['manage_settings'])<a href="{{ route('attendance.integrity.settings') }}" class="aim-btn"><i data-lucide="sliders-horizontal"></i> Settings</a>@endif
@endsection

@section('aim_content')
    @php
        $s = $settings;
        $tiers = collect($s['att_retro_tiers'])->map(fn ($t) => ($t[0] % 24 === 0 ? ($t[0] / 24).'d' : $t[0].'h').' = '.$t[1])->implode(', ');
        $windows = collect($s['att_bulk_windows'])->map(fn ($w) => $w[0].' in '.$w[1].'s')->implode(', ');
    @endphp
    <div class="aim-guide">
        <nav class="aim-guide__toc" aria-label="On this page">
            <a href="#what">What it is</a>
            <a href="#workflow">How to use it</a>
            <a href="#read">Reading a finding</a>
            <a href="#pages">The pages</a>
            <a href="#rules">What the rules look for</a>
            <a href="#staff">What staff see</a>
            <a href="#access">Who can see what</a>
            <a href="#limits">What it cannot tell you</a>
            <a href="#faq">Quick answers</a>
        </nav>

        <div style="display: grid; gap: 18px;">
            <section id="what" class="aim-panel"><div class="aim-panel__body">
                <h2>What it is</h2>
                <p>Attendance Integrity watches <strong>how attendance is entered and changed</strong>: late entries, Absent changed to Present, changes by staff who do not teach the class, bursts of entries and timetable clashes. It turns them into <strong>findings</strong>, ranks people by a <strong>risk score</strong>, and lets investigators open <strong>cases</strong> and export <strong>reports</strong>.</p>
                <div class="aim-callout aim-callout--warn" style="margin: 12px 0 0;"><i data-lucide="scale"></i><div>A finding is a pattern that needs an explanation, <strong>not proof of wrongdoing</strong>. Nothing here acts on staff or students automatically.</div></div>
            </div></section>

            <section id="workflow" class="aim-panel"><div class="aim-panel__body">
                <h2>How to use it</h2>
                <div class="aim-steps">
                    <div class="aim-step"><b>1</b><h4>Check the dashboard</h4><p>Pick the term. Look at the red and amber figures and the highest scores.</p></div>
                    <div class="aim-step"><b>2</b><h4>Review the findings</h4><p>Open a person or a rule page. Click a finding to see its evidence.</p></div>
                    <div class="aim-step"><b>3</b><h4>Open a case</h4><p>Tick the findings that need explaining and choose <em>Open a case</em>. Dismiss the rest with a reason.</p></div>
                    <div class="aim-step"><b>4</b><h4>Investigate and close</h4><p>Assign an investigator, add notes, explanations and evidence, then record the outcome.</p></div>
                </div>
            </div></section>

            <section id="read" class="aim-panel"><div class="aim-panel__body">
                <h2>Reading a finding</h2>
                <ul>
                    <li><strong>Basis</strong>: <span class="aim-badge aim-badge--fact">Confirmed fact</span> read straight from the records · <span class="aim-badge aim-badge--anomaly">Anomaly</span> a pattern that may have an innocent reason · <span class="aim-badge aim-badge--unavailable">Evidence not available</span> never recorded.</li>
                    <li><strong>Points</strong> say how much a finding should be looked at. When several rules flag the same record, only the highest points count.</li>
                    <li><strong>Risk score</strong> is a person's points for the chosen period. Levels: staff Medium from {{ number_format($s['att_band_staff_medium']) }} and High from {{ number_format($s['att_band_staff_high']) }}; students Medium from {{ number_format($s['att_band_student_medium']) }} and High from {{ number_format($s['att_band_student_high']) }}.</li>
                    <li><strong>Status</strong>: <span class="aim-badge aim-badge--open">Open</span> to review · <span class="aim-badge aim-badge--case">In a case</span> · <span class="aim-badge aim-badge--dismissed">Dismissed</span> with a reason (dismissed findings stop counting).</li>
                    <li>Findings refresh <strong>every night</strong> for running terms. Past terms are scanned from Settings.</li>
                </ul>
            </div></section>

            <section id="pages" class="aim-panel">
                <div class="aim-panel__body" style="padding-bottom: 6px;"><h2>The pages</h2></div>
                <table class="aim-table">
                    <thead><tr><th>Page</th><th>Answers</th></tr></thead>
                    <tbody>
                        <tr><td><strong>Dashboard</strong></td><td>How much attendance was recorded, entered late, changed, deleted, and what was found. Every figure opens the records behind it.</td></tr>
                        <tr><td><strong>Staff Analysis</strong></td><td>Who entered and changed attendance, compared with other staff; one person's findings and the students they affected.</td></tr>
                        <tr><td><strong>Student Analysis</strong></td><td>One student's class-by-class timeline: status, who entered and changed it, excuses, findings.</td></tr>
                        <tr><td><strong>Retrospective Attendance</strong></td><td>Registers first entered long after the class ended.</td></tr>
                        <tr><td><strong>Attendance Amendments</strong></td><td>Every status change with before and after, and the reason for late corrections.</td></tr>
                        <tr><td><strong>Bulk Entry Detection</strong></td><td>Many entries across different classes within seconds.</td></tr>
                        <tr><td><strong>Staff–Student Patterns</strong></td><td>Changes in classes the person does not teach, the same students again and again, lone Presents.</td></tr>
                        <tr><td><strong>Timetable Conflicts</strong></td><td>Present at cancelled classes, two classes at once, or classes the student is not on.</td></tr>
                        <tr><td><strong>Investigations</strong></td><td>Cases, their history, evidence and outcome.</td></tr>
                        <tr><td><strong>Reports</strong></td><td>Ten reports as Excel, CSV or PDF, using the current filters.</td></tr>
                    </tbody>
                </table>
            </section>

            <section id="rules" class="aim-panel">
                <div class="aim-panel__body" style="padding-bottom: 6px;"><h2>What the rules look for</h2><p class="aim-small" style="margin: 0;">Each rule can be switched off or tuned in Settings.</p></div>
                <table class="aim-table">
                    <thead><tr><th>Rule</th><th>Looks for</th><th>Points</th></tr></thead>
                    <tbody>
                        <tr><td><strong>A · Late entry</strong></td><td>Attendance first entered after the class's scheduled end, by delay.</td><td>{{ $tiers }}</td></tr>
                        <tr><td><strong>B · Changes</strong></td><td>Absent changed to Present (from the history, or from the absence notice the register leaves); late corrections; records changed {{ $s['att_repeated_min'] }}+ times.</td><td>{{ $s['att_points_abs_present_audit'] }} / {{ $s['att_points_abs_present_notice'] }}; own class {{ $s['att_points_abs_present_own'] }}</td></tr>
                        <tr><td><strong>C · Bulk entry</strong></td><td>Separate entries across 2+ classes: {{ $windows }}. One register saved at once counts as one entry.</td><td>{{ $s['att_points_bulk'] }}</td></tr>
                        <tr><td><strong>D · High attendance</strong></td><td>Classes at {{ $s['att_high_class_rate'] }}%+ and well above similar classes; sudden rises for a student.</td><td>{{ $s['att_points_high_class'] }}</td></tr>
                        <tr><td><strong>E · Patterns</strong></td><td>Changes in classes the person does not teach (without Edit Attendance); the same student changed to Present {{ $s['att_pair_min'] }}+ times; lone Presents between two absences.</td><td>{{ $s['att_points_non_linked'] }} / {{ $s['att_points_pair'] }} / {{ $s['att_points_lone'] }}</td></tr>
                        <tr><td><strong>F · Outside hours</strong></td><td>Entries at night ({{ $s['att_night_start'] }}–{{ $s['att_night_end'] }}), on non-working days, during approved leave, after the term ended.</td><td>{{ $s['att_points_outside_hours'] }}</td></tr>
                        <tr><td><strong>G · Timetable</strong></td><td>Present at cancelled or deleted class dates, at two campus classes at once (online left out), or not assigned to the class.</td><td>{{ $s['att_points_cancelled'] }}</td></tr>
                        <tr><td><strong>H · Evidence</strong></td><td>Changed to Present after the student's own absence excuse was rejected; Present during a reported absence; no class start record.</td><td>{{ $s['att_points_rejected_excuse'] }}</td></tr>
                        <tr><td><strong>J · Deletions</strong></td><td>Deleted attendance per class date, and who deleted it where recorded.</td><td>{{ $s['att_points_deletion'] }}</td></tr>
                    </tbody>
                </table>
            </section>

            <section id="staff" class="aim-panel"><div class="aim-panel__body">
                <h2>What staff see</h2>
                <ul>
                    <li><strong>Late corrections</strong>: changing a student's status, or deleting a record, more than <strong>{{ $s['att_correction_deadline_hours'] }} hours</strong> after the class ended {{ $s['att_require_late_reason'] ? 'asks for a reason (at least '.$s['att_reason_min_length'].' characters)' : 'does not ask for a reason (switched off)' }}. The reason is shown only here.</li>
                    <li><strong>Saving a register</strong>: {{ $s['att_enforce_class_ownership'] ? 'only the class\'s tutor, personal tutor or cover tutor can save it; others need Module Contents > Edit Attendance.' : 'any member of staff can save it (the class-tutor rule is switched off).' }}</li>
                    <li>Every attendance change is recorded with who, when, before and after. Staff do not see this module.</li>
                </ul>
            </div></section>

            <section id="access" class="aim-panel"><div class="aim-panel__body">
                <h2>Who can see what</h2>
                <p>Access needs the <strong>Attendance Integrity</strong> switch on the employee's Privilege tab, plus each area: View dashboard · View staff · View students · View attendance history · View investigations · Manage investigations · Export · Manage settings. Nobody has these by default except super admins. Every page opened and every export is logged.</p>
            </div></section>

            <section id="limits" class="aim-panel"><div class="aim-panel__body">
                <h2>What it cannot tell you</h2>
                <ul>
                    <li><strong>Before the history was switched on</strong>, each record kept only its first and last editor. Earlier changes are shown only where the absence notice proves them; nothing is guessed.</li>
                    <li>There is <strong>no card swipe, QR or online check-in</strong>. A missing record is never treated as proof of absence.</li>
                    <li><strong>"Late"</strong> is chosen by the tutor; nothing compares it with the class start time.</li>
                    <li><strong>Working days</strong> come from HR working patterns and teaching days, which may be incomplete.</li>
                </ul>
            </div></section>

            <section id="faq" class="aim-panel"><div class="aim-panel__body">
                <h2>Quick answers</h2>
                <ul>
                    <li><strong>Is a high score proof?</strong> No. It tells you where to look first.</li>
                    <li><strong>Why is a tutor flagged for their own class?</strong> Some rules (late entry, outside hours) apply to everyone. Own-class corrections score low.</li>
                    <li><strong>A finding is wrong.</strong> Dismiss it with a reason; it stops counting and keeps the reason.</li>
                    <li><strong>The data changed.</strong> The next scan updates or clears the finding automatically; cleared findings stay attached to their cases.</li>
                    <li><strong>How far back?</strong> Any term: queue it in Settings. Late-entry times are reliable from {{ \Carbon\Carbon::parse($s['att_retro_from_date'])->format('j M Y') }}.</li>
                </ul>
            </div></section>
        </div>
    </div>
@endsection
