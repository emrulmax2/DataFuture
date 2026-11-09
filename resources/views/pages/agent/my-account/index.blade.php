@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div style="display:grid;gap:20px;max-width:760px">
        <div style="display:grid;gap:6px">
            <span class="apd-eyebrow">My account</span>
            <h1 class="apd-h1">Agent <em style="font-style:normal;color:var(--steel)">profile</em></h1>
        </div>

        @if(!$verified)
            <div class="apd-notice apd-notice--warn">
                <b>Verify your email address to add applicants</b>
                <div>We sent a verification link to <b style="display:inline">{{ $user->email }}</b>. Check your Junk or Spam folder if you do not see it. Adding applicants and starting applications unlocks once the address is verified.</div>
                <div class="apd-notice__actions">
                    <form method="post" action="{{ route('agent.verification.send') }}">
                        @csrf
                        <button type="submit" class="apd-btn apd-btn--ghost apd-btn--sm">Resend verification email</button>
                    </form>
                </div>
            </div>
        @endif

        @if(session('verifymessage'))
            <div class="apd-notice apd-notice--ok"><b>Email sent</b><div>{{ session('verifymessage') }}</div></div>
        @endif
        @if(session('account_status'))
            <div class="apd-notice apd-notice--ok">{{ session('account_status') }}</div>
        @endif

        <div class="apd-panel">
            <h3>Profile</h3>
            <dl class="apd-kv">
                <dt>Company</dt>
                <dd>{{ $company ?: 'Added by admissions after registration' }}</dd>

                <dt>Email</dt>
                <dd>
                    {{ $user->email }}
                    @if($verified)
                        <span class="apd-pill apd-pill--green" style="margin-left:6px"><i></i>Verified</span>
                    @else
                        <span class="apd-pill apd-pill--amber" style="margin-left:6px"><i></i>Not verified</span>
                    @endif
                </dd>

                <dt>Referral code</dt>
                <dd>
                    @if($code)
                        <span style="display:inline-flex;gap:8px;align-items:center">
                            <b class="apd-mono" id="agpReferralCode">{{ $code }}</b>
                            <button type="button" class="agp-iconbtn" id="agpCopyCode" data-code="{{ $code }}" title="Copy referral code" aria-label="Copy referral code">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15V6a2 2 0 012-2h9"/></svg>
                            </button>
                            <span class="apd-hint" id="agpCopied" role="status" hidden>Copied</span>
                        </span>
                    @else
                        Issued by admissions
                    @endif
                </dd>

                @if($subs->count())
                    <dt>Sub-agents</dt>
                    <dd>
                        @foreach($subs as $sub)
                            <div>{{ $sub['name'] }} @if($sub['code'])<span class="apd-mono" style="color:var(--muted)">[{{ $sub['code'] }}]</span>@endif</div>
                        @endforeach
                    </dd>
                @endif
            </dl>
            <p style="font-size:13px;color:var(--muted);margin:0">Company details, bank details, documents and commission are managed by LCC admissions. Contact them to change any of these.</p>
        </div>

        <div class="apd-panel">
            <h3>Change password</h3>

            @if($errors->any())
                <div class="apd-notice apd-notice--bad">{{ $errors->first() }}</div>
            @endif

            <form method="post" action="{{ route('agent.account.password') }}" class="apd-form" style="display:grid;gap:12px">
                @csrf
                <div class="apd-field">
                    <label for="current_password">Current password</label>
                    <input id="current_password" name="current_password" type="password" autocomplete="current-password"
                           class="apd-ctl {{ $errors->has('current_password') ? 'err' : '' }}">
                </div>
                <div class="apd-field">
                    <label for="password">New password</label>
                    <input id="password" name="password" type="password" autocomplete="new-password"
                           class="apd-ctl {{ $errors->has('password') ? 'err' : '' }}">
                    <span class="apd-hint">At least 8 characters.</span>
                </div>
                <div class="apd-field">
                    <label for="password_confirmation">Confirm new password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" class="apd-ctl">
                </div>
                <div>
                    <button type="submit" class="apd-btn apd-btn--primary">Update password</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('script')
    <script>
        /* Copy the referral code; where the clipboard is unavailable, select it instead. */
        (function () {
            var btn = document.getElementById('agpCopyCode'), done = document.getElementById('agpCopied'), code = document.getElementById('agpReferralCode');
            if (!btn) return;
            var say = function (text) { done.textContent = text; done.hidden = false; clearTimeout(say.t); say.t = setTimeout(function () { done.hidden = true; }, 1800); };
            var select = function () { try { var r = document.createRange(); r.selectNodeContents(code); var s = window.getSelection(); s.removeAllRanges(); s.addRange(r); } catch (e) {} say('Select the code and copy it.'); };
            btn.addEventListener('click', function () {
                if (navigator.clipboard && navigator.clipboard.writeText) { navigator.clipboard.writeText(btn.getAttribute('data-code')).then(function () { say('Copied'); }, select); } else { select(); }
            });
        })();
    </script>
@endsection
