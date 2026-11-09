{{--
    /offer/decline/{slug} (OfferDeclineController): the Decline button in the DocuSeal offer
    acceptance email. Standalone (no theme CSS/JS), in the email's look: navy band, gold rule.
    States: open (confirm + optional reason), declined, signed, closed, missing.
--}}
@php
    $titles = [
        'open' => 'Decline your offer?',
        'declined' => 'Your offer has been declined',
        'signed' => 'This offer has already been accepted',
        'closed' => 'This link is no longer active',
        'missing' => 'We could not find this offer',
    ];
    $eyebrows = ['open' => 'Offer acceptance', 'declined' => 'Offer declined', 'signed' => 'Offer accepted', 'closed' => 'Offer acceptance', 'missing' => 'Offer acceptance'];
    $title = $titles[$state] ?? $titles['missing'];
    $firstName = $offer['firstName'] ?? '';
@endphp
<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title }} - {{ $companyName }}</title>
    <style>
        :root { --navy: #102d50; --navy2: #123c70; --gold: #c9a257; --gold-ink: #98783e; --ink: #303b49; --ink2: #475569; --muted: #64748b; --line: #e3e8ef; --soft: #f5f7fa; --bad: #a3242f; }
        * { box-sizing: border-box; }
        body { margin: 0; background: #f2f0ed; font-family: Arial, Helvetica, sans-serif; color: var(--ink); -webkit-font-smoothing: antialiased; }
        .wrap { max-width: 600px; margin: 0 auto; padding: 30px 16px 40px; }
        .card { background: #fff; border-radius: 14px; overflow: hidden; box-shadow: 0 1px 2px rgba(16, 45, 80, .06); }
        .band { background: var(--navy); padding: 28px 24px 24px; text-align: center; }
        .band img { display: block; width: 100%; max-width: 300px; height: auto; margin: 0 auto; }
        .band .name { color: #fff; font-size: 20px; font-weight: bold; }
        .band p { margin: 18px 0 0; color: #e4eaf2; font-size: 11px; font-weight: bold; letter-spacing: 2px; text-transform: uppercase; }
        .rule { height: 4px; background: var(--gold); }
        .body { padding: 34px 32px 28px; }
        .eyebrow { margin: 0 0 12px; color: var(--gold-ink); font-size: 11px; font-weight: bold; letter-spacing: 1.5px; text-transform: uppercase; }
        h1 { margin: 0 0 22px; color: var(--navy); font-size: 26px; line-height: 1.3; }
        p.copy { margin: 0 0 18px; font-size: 15px; line-height: 1.75; color: var(--ink2); }
        .summary { margin: 6px 0 24px; background: var(--soft); border: 1px solid var(--line); border-radius: 8px; }
        .summary div { display: flex; gap: 16px; padding: 12px 18px; border-bottom: 1px solid var(--line); }
        .summary div:last-child { border-bottom: 0; }
        .summary dt { flex: 0 0 140px; margin: 0; font-size: 11px; font-weight: bold; letter-spacing: 1px; text-transform: uppercase; color: #6b7280; padding-top: 2px; }
        .summary dd { margin: 0; font-size: 14px; font-weight: bold; color: var(--navy); line-height: 1.5; overflow-wrap: anywhere; }
        label { display: block; margin: 0 0 8px; font-size: 13px; font-weight: bold; color: var(--ink); }
        label span { font-weight: normal; color: var(--muted); }
        textarea { width: 100%; min-height: 96px; padding: 12px 14px; border: 1px solid #cfd6e0; border-radius: 8px; font: inherit; font-size: 15px; color: var(--ink); resize: vertical; }
        textarea:focus { outline: 2px solid var(--navy2); outline-offset: 1px; border-color: var(--navy2); }
        .hint { margin: 6px 0 0; font-size: 12px; color: var(--muted); }
        .actions { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 26px; }
        .btn { display: inline-block; padding: 15px 26px; border-radius: 6px; font: inherit; font-size: 15px; font-weight: bold; line-height: 20px; text-align: center; text-decoration: none; cursor: pointer; }
        .btn-bad { background: var(--bad); border: 1px solid var(--bad); color: #fff; }
        .btn-bad:hover { background: #8c1d27; }
        .btn-bad[disabled] { opacity: .65; cursor: default; }
        .btn-line { background: #fff; border: 1px solid var(--navy2); color: var(--navy2); }
        .btn-line:hover { background: var(--soft); }
        .btn-navy { background: var(--navy2); border: 1px solid var(--navy2); color: #fff; }
        .note { margin: 22px 0 0; padding: 14px 16px; border-left: 3px solid var(--gold); background: #fbf8f1; font-size: 13px; line-height: 1.6; color: var(--ink2); }
        .alert { margin: 0 0 20px; padding: 12px 16px; border-radius: 8px; background: #fdecee; color: #7d1b24; font-size: 14px; line-height: 1.5; }
        .ok { margin: 0 0 20px; padding: 12px 16px; border-radius: 8px; background: #edf7f1; color: #1d5b3c; font-size: 14px; line-height: 1.5; }
        .contact { margin: 26px 0 0; padding-top: 20px; border-top: 1px solid #e5e7eb; font-size: 14px; line-height: 1.7; color: var(--ink2); }
        .contact a { color: #1257a5; text-decoration: none; }
        .foot { padding: 16px 20px 20px; border-top: 1px dotted #d8d8d8; text-align: center; font-size: 12px; line-height: 1.6; color: #737373; }
        .foot a { color: #1257a5; text-decoration: none; font-size: 13px; }

        /* confirmation modal */
        body.lock { overflow: hidden; }
        .ovl { position: fixed; inset: 0; z-index: 50; display: flex; align-items: center; justify-content: center; padding: 20px 16px; overflow-y: auto; background: rgba(16, 45, 80, .55); -webkit-backdrop-filter: blur(2px); backdrop-filter: blur(2px); animation: ovlIn .18s ease-out; }
        .ovl[hidden] { display: none; }
        .dlg { width: 100%; max-width: 480px; margin: auto; background: #fff; border-radius: 14px; overflow: hidden; box-shadow: 0 24px 60px -18px rgba(16, 45, 80, .6); animation: dlgIn .22s cubic-bezier(.2, .8, .2, 1); }
        .dlg-rule { height: 4px; background: var(--gold); }
        .dlg-pane { padding: 28px 28px 24px; }
        .dlg-pane[hidden] { display: none; }
        .dlg-icon { display: flex; align-items: center; justify-content: center; width: 48px; height: 48px; margin-bottom: 16px; border-radius: 50%; }
        .dlg-icon svg { width: 24px; height: 24px; }
        .dlg-icon.is-warn { background: #fdecee; color: var(--bad); }
        .dlg-icon.is-ok { background: #e7f4ec; color: #1f7a4d; }
        .dlg-icon.is-ok svg { width: 30px; height: 30px; }
        .dlg-icon.is-info { background: #eef2f8; color: var(--navy2); }
        .dlg h2 { margin: 0 0 10px; color: var(--navy); font-size: 21px; line-height: 1.3; outline: none; }
        .dlg p.lead { margin: 0 0 18px; font-size: 14.5px; line-height: 1.65; color: var(--ink2); }
        .dlg textarea { min-height: 84px; }
        .dlg-alert { margin-top: 14px; padding: 10px 14px; border-radius: 8px; background: #fdecee; color: #7d1b24; font-size: 13.5px; line-height: 1.5; }
        .dlg-alert[hidden] { display: none; }
        .dlg-actions { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 10px; margin-top: 22px; }
        .dlg-actions .btn { padding: 13px 22px; }
        .btn[disabled] { opacity: .6; cursor: default; }
        @keyframes ovlIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes dlgIn { from { opacity: 0; transform: translateY(10px) scale(.98); } to { opacity: 1; transform: none; } }
        @media (prefers-reduced-motion: reduce) { .ovl, .dlg { animation: none; } }
        @media (max-width: 480px) {
            .body { padding: 28px 20px 24px; }
            h1 { font-size: 23px; }
            .summary div { flex-direction: column; gap: 2px; }
            .summary dt { flex-basis: auto; }
            .actions .btn { width: 100%; }
            .dlg-pane { padding: 24px 20px 20px; }
            .dlg-actions { flex-direction: column-reverse; }
            .dlg-actions .btn { width: 100%; }
        }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <div class="band">
            @if($logoUrl)<img src="{{ $logoUrl }}" alt="{{ $companyName }}">@else<div class="name">{{ $companyName }}</div>@endif
            <p>Admissions Office</p>
        </div>
        <div class="rule"></div>

        <div class="body">
            <p class="eyebrow">{{ $eyebrows[$state] ?? 'Offer acceptance' }}</p>
            <h1>{{ $title }}</h1>

            @if(session('offer_decline_error'))
                <div class="alert" role="alert">{{ session('offer_decline_error') }}</div>
            @endif

            @if($state === 'open')
                {{-- Previously the reason + "Yes, decline my offer" form sat on the page itself; it now lives
                     in the confirmation modal below (user, 27 Oct 2026: confirm in a modal first).
                <form method="post" action="{{ route('offer.decline.store', $slug) }}" id="declineForm">
                    @csrf
                    <label for="reason">Why are you declining? <span>(optional)</span></label>
                    <textarea id="reason" name="reason" maxlength="1000" placeholder="For example: I have accepted an offer elsewhere">{{ old('reason') }}</textarea>
                    @error('reason')<p class="hint" style="color: var(--bad);">{{ $message }}</p>@enderror
                    <p class="hint">Your reason helps us improve. It is shared with the Admissions Team only.</p>
                    <div class="actions">
                        <button type="submit" class="btn btn-bad" id="declineBtn">Yes, decline my offer</button>
                        @if(!empty($offer['signingUrl']))<a class="btn btn-line" href="{{ $offer['signingUrl'] }}">Review &amp; sign instead</a>@endif
                    </div>
                </form>
                --}}
                <div id="offerBody">
                    <p class="copy">{{ $firstName ? 'Hi '.$firstName.', you' : 'You' }} are about to decline your offer to study at {{ $companyName }}. Nothing changes until you confirm.</p>
                    <dl class="summary">
                        @if(!empty($offer['ref']))<div><dt>Application ref</dt><dd>{{ $offer['ref'] }}</dd></div>@endif
                        @if(!empty($offer['course']))<div><dt>Course</dt><dd>{{ $offer['course'] }}</dd></div>@endif
                        @if(!empty($offer['intake']))<div><dt>Intake</dt><dd>{{ $offer['intake'] }}</dd></div>@endif
                    </dl>
                    <div class="actions">
                        <button type="button" class="btn btn-bad" data-open-decline>Decline offer</button>
                        @if(!empty($offer['signingUrl']))<a class="btn btn-line" href="{{ $offer['signingUrl'] }}">Review &amp; sign instead</a>@endif
                    </div>
                    <p class="note">Changed your mind later? Contact our Admissions Team and we can send you a new offer acceptance form.</p>
                </div>
            @elseif($state === 'declined')
                @if(!empty($justDeclined))
                    <div class="ok" role="status">Done. We have emailed you a confirmation.</div>
                @endif
                <p class="copy">You have declined your offer to study at {{ $companyName }}{{ !empty($offer['course']) ? ' ('.$offer['course'].')' : '' }}. Your offer acceptance form has been withdrawn, so its signing link no longer works.</p>
                <p class="copy">If you declined by mistake or would like to reconsider, please contact our Admissions Team as soon as possible.</p>
            @elseif($state === 'signed')
                <p class="copy">Your offer acceptance form has already been signed, so the offer can no longer be declined online.</p>
                <p class="copy">If you no longer wish to take up your place, please contact our Admissions Team and we will help you.</p>
            @elseif($state === 'closed')
                <p class="copy">This offer acceptance form has been replaced or your application has moved on, so it can no longer be declined from this link.</p>
                <p class="copy">If you have received a newer offer email, please use the link in that email. Otherwise, contact our Admissions Team.</p>
            @else
                <p class="copy">This link does not match an offer acceptance form. Please check you opened the full link from your email, or contact our Admissions Team.</p>
            @endif

            <div class="contact">
                <strong style="color: var(--navy);">Admissions Team</strong><br>
                <a href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a> &middot; <a href="tel:{{ $companyPhoneTel }}">{{ $companyPhone }}</a>
            </div>
        </div>

        <div class="foot">
            {{ $companyAddress }}<br>
            <a href="tel:{{ $companyPhoneTel }}">{{ $companyPhone }}</a>
        </div>
    </div>
</div>
@if($state === 'open')
    {{-- The confirmation modal: opens on load; nothing changes until "Yes, decline my offer".
         It posts in the background (JSON) and turns into the success message; without
         JavaScript the form still posts normally. --}}
    <div class="ovl" id="declineModal" role="dialog" aria-modal="true" aria-labelledby="dmTitle" aria-describedby="dmDesc" hidden>
        <div class="dlg">
            <div class="dlg-rule"></div>

            <div class="dlg-pane" data-pane="confirm">
                <div class="dlg-icon is-warn" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4"/><path d="M12 17h.01"/><path d="M10.3 3.9 1.8 18.2A2 2 0 0 0 3.5 21h17a2 2 0 0 0 1.7-2.8L13.7 3.9a2 2 0 0 0-3.4 0Z"/></svg>
                </div>
                <h2 id="dmTitle">Are you sure you want to decline?</h2>
                <p class="lead" id="dmDesc">Your offer to study{{ !empty($offer['course']) ? ' '.$offer['course'] : '' }} at {{ $companyName }} will be declined and your offer acceptance form withdrawn, so it can no longer be signed. This cannot be undone online.</p>
                <form method="post" action="{{ route('offer.decline.store', $slug) }}" id="declineForm">
                    @csrf
                    <label for="reason">Why are you declining? <span>(optional)</span></label>
                    <textarea id="reason" name="reason" maxlength="1000" placeholder="For example: I have accepted an offer elsewhere">{{ old('reason') }}</textarea>
                    @error('reason')<p class="hint" style="color: var(--bad);">{{ $message }}</p>@enderror
                    <p class="hint">Shared with the Admissions Team only.</p>
                    <div class="dlg-alert" role="alert" hidden></div>
                    <div class="dlg-actions">
                        <button type="button" class="btn btn-line" data-close-decline>No, keep my offer</button>
                        <button type="submit" class="btn btn-bad" id="declineBtn">Yes, decline my offer</button>
                    </div>
                </form>
            </div>

            <div class="dlg-pane" data-pane="done" hidden>
                <div class="dlg-icon is-ok" aria-hidden="true" data-done-icon>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M4.5 12.5 9.5 17.5 19.5 6.5"/></svg>
                </div>
                <h2 data-done-title tabindex="-1">Your offer has been declined</h2>
                <p class="lead" data-done-msg>We have emailed you a confirmation. If you change your mind, please contact our Admissions Team.</p>
                <div class="dlg-actions">
                    <button type="button" class="btn btn-navy" data-close-decline>Done</button>
                </div>
            </div>
        </div>
    </div>

    {{-- what the page says once the offer is declined (swapped in after the background post) --}}
    <template id="declinedTpl">
        <p class="copy">You have declined your offer to study at {{ $companyName }}{{ !empty($offer['course']) ? ' ('.$offer['course'].')' : '' }}. Your offer acceptance form has been withdrawn, so its signing link no longer works.</p>
        <p class="copy">If you declined by mistake or would like to reconsider, please contact our Admissions Team as soon as possible.</p>
    </template>

    <script>
        /* previous script (form on the page):
        document.getElementById('declineForm').addEventListener('submit', function () {
            var btn = document.getElementById('declineBtn');
            btn.disabled = true;
            btn.textContent = 'Declining…';
        });
        */
        (function () {
            var modal = document.getElementById('declineModal');
            var form = document.getElementById('declineForm');
            var btn = document.getElementById('declineBtn');
            var alertBox = modal.querySelector('.dlg-alert');
            var confirmPane = modal.querySelector('[data-pane="confirm"]');
            var donePane = modal.querySelector('[data-pane="done"]');
            var busy = false, lastFocus = null;
            var titles = { declined: 'Your offer has been declined', signed: 'This offer has already been accepted', closed: 'This link is no longer active' };

            function open() {
                lastFocus = document.activeElement;
                modal.hidden = false;
                document.body.classList.add('lock');
                // the safe choice has the focus, so Enter never declines by accident
                setTimeout(function () { (donePane.hidden ? confirmPane.querySelector('[data-close-decline]') : donePane.querySelector('button')).focus(); }, 30);
            }
            function close() {
                if (busy) return;
                modal.hidden = true;
                document.body.classList.remove('lock');
                if (lastFocus && lastFocus.focus && document.body.contains(lastFocus)) lastFocus.focus();
            }
            function setBusy(on) {
                busy = on;
                btn.disabled = on;
                btn.textContent = on ? 'Declining…' : 'Yes, decline my offer';
                modal.querySelectorAll('[data-close-decline]').forEach(function (b) { b.disabled = on; });
            }
            function showError(text) {
                alertBox.textContent = text;
                alertBox.hidden = false;
            }
            function finish(state, message) {
                var heading = document.querySelector('.body h1');
                var eyebrow = document.querySelector('.body .eyebrow');
                var body = document.getElementById('offerBody');
                donePane.querySelector('[data-done-title]').textContent = titles[state] || titles.declined;
                donePane.querySelector('[data-done-msg]').textContent = message;
                if (state !== 'declined') {
                    var icon = donePane.querySelector('[data-done-icon]');
                    icon.className = 'dlg-icon is-info'; // not .ok/.info: the page's notice classes
                    icon.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>';
                }
                confirmPane.hidden = true;
                donePane.hidden = false;
                donePane.querySelector('[data-done-title]').focus();
                // the page behind follows
                if (heading) heading.textContent = titles[state] || titles.declined;
                if (eyebrow) eyebrow.textContent = state === 'declined' ? 'Offer declined' : (state === 'signed' ? 'Offer accepted' : 'Offer acceptance');
                if (body) {
                    if (state === 'declined') {
                        body.innerHTML = document.getElementById('declinedTpl').innerHTML;
                    } else {
                        body.innerHTML = '';
                        var p = document.createElement('p');
                        p.className = 'copy';
                        p.textContent = message;
                        body.appendChild(p);
                    }
                }
            }

            document.querySelectorAll('[data-open-decline]').forEach(function (b) { b.addEventListener('click', open); });
            modal.addEventListener('click', function (e) {
                if (e.target.closest('[data-close-decline]') || e.target === modal) close();
            });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && !modal.hidden) close();
            });

            form.addEventListener('submit', function (e) {
                if (!window.fetch || !window.FormData) { setBusy(true); return; } // plain post
                e.preventDefault();
                if (busy) return;
                alertBox.hidden = true;
                setBusy(true);
                fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                }).then(function (res) {
                    return res.json().catch(function () { return {}; }).then(function (data) { return { status: res.status, data: data }; });
                }).then(function (r) {
                    setBusy(false);
                    if (r.status === 422) {
                        var errs = r.data.errors || {};
                        showError((errs.reason && errs.reason[0]) || r.data.message || 'Please check your reason and try again.');
                    } else if (r.status === 419) {
                        showError('This page has expired. Please refresh it and try again.');
                    } else if (r.status >= 400 || !r.data.state || r.data.state === 'error') {
                        showError(r.data.message || 'We could not complete this just now. Please try again in a few minutes.');
                    } else {
                        finish(r.data.state, r.data.message);
                    }
                }).catch(function () {
                    setBusy(false);
                    showError('We could not reach the server. Please check your connection and try again.');
                });
            });

            open(); // on load: confirm first
        })();
    </script>
@endif
</body>
</html>
