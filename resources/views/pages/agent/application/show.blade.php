@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    @php
        $dash = '—';
        $fix = fn($n) => $n === 'Awating Decision' ? 'Awaiting Decision' : $n;
        $statusName = fn($id) => $fix($statusNames[$id] ?? 'Unknown');
        /* "28 Sept 2026": the design's en-GB short months (September is "Sept"). */
        $date = function ($v) use ($dash) {
            if (!$v) return $dash;
            $c = null;
            if ($v instanceof \DateTimeInterface) { $c = \Carbon\Carbon::instance($v); }
            else { foreach (['d-m-Y', 'Y-m-d H:i:s', 'Y-m-d'] as $f) { try { $c = \Carbon\Carbon::createFromFormat($f, (string) $v); break; } catch (\Throwable $e) {} } }
            if (!$c) { try { $c = \Carbon\Carbon::parse($v); } catch (\Throwable $e) { return (string) $v; } }
            return str_replace(' Sep ', ' Sept ', $c->format('d M Y'));
        };

        /* The status machine as the agent sees it: 1-7 is the main path; 8, 9
           and 46 are branches off the step they can happen at. */
        $sid = (int) $applicant->status_id;
        $tone = [1 => 'muted', 2 => 'muted', 3 => 'amber', 4 => 'blue', 5 => 'green', 6 => 'blue', 7 => 'green', 8 => 'red', 9 => 'red', 46 => 'red'][$sid] ?? 'muted';
        $mainPath = [1, 2, 3, 4, 5, 6, 7];
        $branchAt = [8 => 4, 9 => 6, 46 => 3];
        $idx = array_search($sid, $mainPath, true);
        $branch = $branchAt[$sid] ?? null;

        $ref = $applicant->application_no;
        $first = $applicant->first_name ?: 'the applicant';
        $name = trim(implode(' ', array_filter([optional($applicant->title)->name, $applicant->first_name, $applicant->last_name]))) ?: (optional($applicant->users)->email ?? $dash);
        $email = optional($applicant->users)->email ?: $dash;
        $mobile = optional($applicant->contact)->mobile ?: (optional($applicant->users)->phone ?: $dash);
        $courseName = optional(optional(optional($applicant->course)->creation)->course)->name;
        $semester = optional(optional($applicant->course)->semester)->name;
        $submitted = $applicant->submission_date ? $date($applicant->submission_date) : null;

        /* What this status means for the agent, in the design's words. */
        $explain = [
            1  => ['warn', 'Not submitted yet', "This application is a draft. It reaches admissions only after you complete Review & Apply."],
            2  => ['', 'Received', "Admissions have {$first}'s application. A confirmation SMS and email with reference {$ref} went to {$mobile} and {$email}. Checks start shortly."],
            3  => ['', 'Being processed', "Admissions are working through {$first}'s checks: signed application form, proof of ID, address and residency, course entry documents, academic interview and English language assessment. They contact the applicant by email, SMS or phone."],
            4  => ['', 'Awaiting decision', "All checks are complete. The application is with the admissions panel. A decision letter goes to {$email}."],
            5  => ['ok', 'Accepted', "A Conditional Offer Letter was emailed to {$email} as a PDF. The offer acceptance form follows."],
            6  => ['ok', 'Offer placed', "An offer acceptance form was emailed to {$email} for signature. Remind {$first} to open it, check the prefilled details and sign."],
            7  => ['ok', 'Offer accepted — now a student', "{$first} signed the offer. A student record exists, so this application counts towards your referral."],
            8  => ['bad', 'Application unsuccessful', "A rejection letter was emailed to {$email}." . ($reason ? " Reason recorded: {$reason}." : '') . " You can start a new application. It is prefilled from this one."],
            9  => ['bad', 'Offer declined', "This offer was recorded as rejected. If that is a mistake, contact admission@lcc.ac.uk quoting {$ref}."],
            46 => ['bad', 'Application cancelled', "This application was cancelled. If that is unexpected, contact admission@lcc.ac.uk quoting {$ref}."],
        ][$sid] ?? ['', $statusName($sid), ''];
    @endphp

    <div class="apd-view">
        <div><a href="{{ route('agent.dashboard') }}" class="apd-backlink">&larr; Dashboard</a></div>

        <div class="apd-view__head">
            <div class="apd-view__who">
                <span class="apd-stamp"><small>Reference</small><b>{{ $ref }}</b></span>
                <div style="display:grid;gap:4px">
                    <div><span class="apd-pill apd-pill--{{ $tone }}"><i></i>{{ $statusName($sid) }}</span></div>
                    <h1 class="apd-h1" style="font-size:clamp(26px,3.2vw,36px)">{{ $name }}</h1>
                    <p class="apd-view__meta">{{ $courseName ?: $dash }} &middot; {{ $semester ?: $dash }} &middot; {{ $submitted ? 'submitted ' . $submitted : 'not submitted yet' }}</p>
                </div>
            </div>
            @if($canRestart)
                <a href="{{ route('agent.application.create', $applicant->applicant_user_id) }}" class="apd-btn apd-btn--primary">Start a new application</a>
            @endif
        </div>

        {{-- progress track --}}
        <div class="apd-card" style="padding:20px 22px">
            <div class="apd-journey" aria-label="Application progress">
                @foreach($mainPath as $i => $step)
                    @php
                        $cls = 'apd-journey__node';
                        if ($idx !== false) {
                            if ($i < $idx) { $cls .= ' done'; }
                            if ($i === $idx) { $cls .= ' now'; }
                            if ($i === $idx && $step === 7) { $cls .= ' done'; }
                        } elseif ($branch !== null) {
                            $bi = array_search($branch, $mainPath, true);
                            if ($i < $bi) { $cls .= ' done'; }
                            if ($i === $bi) { $cls .= ' branch now'; }
                        }
                        $isDone = str_contains($cls, ' done') && !str_contains($cls, 'now');
                        $isBranch = str_contains($cls, 'branch');
                    @endphp
                    <div class="{{ $cls }}">
                        <div class="apd-journey__dot">
                            @if($isDone)
                                <svg width="11" height="11" viewBox="0 0 10 10" aria-hidden="true"><path d="M1.5 5.2l2.3 2.3 4.7-4.8" stroke="#fff" stroke-width="1.8" fill="none"/></svg>
                            @else
                                {{ $isBranch ? '!' : $i + 1 }}
                            @endif
                        </div>
                        <span>{{ $isBranch ? $statusName($sid) : $statusName($step) }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- No role="tab" here: the theme's bundled script (app.js) hooks that role
             and throws on every click. The buttons carry aria-current instead. --}}
        <div class="apd-tabs" aria-label="Application">
            <button type="button" class="on" aria-current="true" data-tab="status">Status &amp; messages</button>
            <button type="button" data-tab="details">Application answers</button>
            <button type="button" data-tab="history">History</button>
        </div>

        {{-- Status & messages --}}
        <div class="apd-tabpanel" data-panel="status" style="max-width:820px">
            <div class="apd-notice {{ $explain[0] ? 'apd-notice--' . $explain[0] : '' }}">
                <b>{{ $explain[1] }}</b>
                <div>{{ $explain[2] }}</div>
            </div>

            <div class="apd-panel">
                <h3>Messages the college sent to the applicant</h3>
                <table class="apd-msgs"><tbody>
                    @if($submitted)
                        <tr><td class="apd-mono">{{ $submitted }}</td><td>SMS to {{ $mobile }} + email to {{ $email }} &middot; confirmation with reference {{ $ref }}</td></tr>
                    @endif
                    @foreach($messages as $m)
                        <tr><td class="apd-mono">{{ $date($m['at']) }}</td><td>{{ $m['text'] }}</td></tr>
                    @endforeach
                    @if(!$submitted && $messages->isEmpty())
                        <tr><td class="apd-mono">{{ $dash }}</td><td>Nothing sent yet &mdash; messages start once the application is submitted.</td></tr>
                    @endif
                </tbody></table>
            </div>

            <div class="apd-panel">
                <h3>Referral link</h3>
                <p>
                    @if($referralOwner)
                        This application is linked to <b>{{ $referralOwner }}</b> (<span class="apd-mono">{{ $applicant->referral_code }}</span>).
                    @else
                        No referral code on this application.
                    @endif
                </p>
            </div>

            <div class="apd-panel">
                <h3>Need to change something?</h3>
                <p>Submitted applications are locked. Email admission@lcc.ac.uk with the reference and the correction. A new mobile or email is verified by admissions before it is updated.</p>
            </div>
        </div>

        {{-- Application answers: the same read-back as the applicant's review step --}}
        <div class="apd-tabpanel" data-panel="details" style="max-width:900px" hidden>
            @include('components.applicant.review', ['applicant' => $applicant, 'compact' => true])
            <div class="apd-panel" style="font-size:14px">
                <h3>Declaration</h3>
                <div>
                    Agreed on {{ $submitted ?: $dash }}.
                    <p class="apd-panel__fine">I hereby verify the accuracy and truthfulness of the information provided in this form to the best of my knowledge. It is my responsibility to stay informed about the terms and conditions as well as the policies of the college, and I commit to comply with them. I have thoroughly reviewed the college's terms and conditions and student privacy policy and pledge to adhere to them throughout my entire course of study.</p>
                </div>
            </div>
        </div>

        {{-- History --}}
        <div class="apd-tabpanel" data-panel="history" style="max-width:720px" hidden>
            <div class="apd-panel">
                <h3>Application history</h3>
                <table class="apd-msgs"><tbody>
                    @if($submitted)
                        <tr><td class="apd-mono">{{ $submitted }}</td><td>Submitted (New)</td></tr>
                    @endif
                    @foreach($history as $h)
                        @continue(($h['from'] ?? '') === 'Submitting' && ($h['to'] ?? '') === 'New')
                        <tr><td class="apd-mono">{{ $date($h['at']) }}</td><td>{{ $fix($h['to'] ?? $dash) }}@if(!empty($h['from']))<span class="apd-msgs__sub">from {{ $fix($h['from']) }}</span>@endif</td></tr>
                    @endforeach
                    @if(!$submitted && $history->isEmpty())
                        <tr><td class="apd-mono">{{ $dash }}</td><td>Draft &mdash; not submitted yet.</td></tr>
                    @endif
                </tbody></table>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        /* Tabs: one panel at a time; the chosen tab survives a reload through the hash. */
        (function () {
            var tabs = document.querySelectorAll('.apd-tabs [data-tab]'), panels = document.querySelectorAll('.apd-tabpanel');
            tabs.forEach(function (t) {
                t.addEventListener('click', function () {
                    tabs.forEach(function (x) { var on = x === t; x.classList.toggle('on', on); if (on) { x.setAttribute('aria-current', 'true'); } else { x.removeAttribute('aria-current'); } });
                    panels.forEach(function (p) { p.hidden = p.getAttribute('data-panel') !== t.getAttribute('data-tab'); });
                    try { history.replaceState(null, '', '#' + t.getAttribute('data-tab')); } catch (e) {}
                });
            });
            var h = (location.hash || '').slice(1), t = h && document.querySelector('.apd-tabs [data-tab="' + h + '"]');
            if (t) t.click();
        })();
    </script>
@endsection
