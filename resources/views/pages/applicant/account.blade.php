@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    @php $apdVerified = !empty($user->email_verified_at); @endphp

    <div style="display:grid;gap:20px;max-width:720px">
        <div style="display:grid;gap:6px">
            <span class="apd-eyebrow">My account</span>
            <h1 class="apd-h1">Sign-in <em style="font-style:normal;color:var(--steel)">details</em></h1>
        </div>

        @if(session('account_status'))
            <div class="apd-notice apd-notice--ok">{{ session('account_status') }}</div>
        @endif

        @if(!$apdVerified)
            <div class="apd-notice apd-notice--warn">
                <b>Verify your email address</b>
                <div>
                    A verification link has been sent to <b style="display:inline">{{ $user->email }}</b>. Until you
                    click it you cannot start or submit an application. If it is not in your inbox, please check your
                    Junk/Spam folder.
                </div>
                <div class="apd-notice__actions">
                    <form id="resendverification" method="post" action="{{ route('verification.send') }}">
                        @csrf
                        <button id="emailverification" type="submit" class="apd-btn apd-btn--ghost apd-btn--sm">
                            Resend verification email
                        </button>
                    </form>
                </div>
            </div>
        @endif

        <div class="apd-card" style="padding:22px;display:grid;gap:12px">
            <dl class="apd-kv">
                <dt>Email</dt>
                <dd>
                    {{ $user->email }}
                    @if($apdVerified)
                        <span class="apd-pill apd-pill--green" style="margin-left:6px"><i></i>Verified</span>
                    @else
                        <span class="apd-pill apd-pill--amber" style="margin-left:6px"><i></i>Not verified</span>
                    @endif
                </dd>
                <dt>Mobile</dt>
                <dd>
                    @if($mobile)
                        {{ $mobile }}
                        <span style="display:block;color:var(--muted);font-size:13px">Taken from your application form</span>
                    @else
                        <span style="color:var(--muted)">Taken from your application form</span>
                    @endif
                </dd>
            </dl>
            <p style="font-size:13px;color:var(--muted);margin:0">
                To change the email address you sign in with, contact admissions. They will send a verification link to
                the new address; your login changes only after you click it.
            </p>
        </div>

        <div class="apd-card" style="padding:22px;display:grid;gap:12px">
            <h3 class="apd-h3">Change password</h3>

            @if($errors->any())
                <div class="apd-notice apd-notice--bad">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="post" action="{{ route('applicant.account.password') }}" style="display:grid;gap:12px">
                @csrf
                <div class="apd-field">
                    <label for="current_password">Current password</label>
                    <input id="current_password" name="current_password" type="password" autocomplete="current-password"
                           class="apd-ctl {{ $errors->has('current_password') ? 'err' : '' }}">
                    @error('current_password')<span class="apd-err">{{ $message }}</span>@enderror
                </div>

                <div class="apd-field">
                    <label for="password">New password</label>
                    <input id="password" name="password" type="password" autocomplete="new-password"
                           class="apd-ctl {{ $errors->has('password') ? 'err' : '' }}">
                    @error('password')
                        <span class="apd-err">{{ $message }}</span>
                    @else
                        <span class="apd-hint">At least 8 characters.</span>
                    @enderror
                </div>

                <div class="apd-field">
                    <label for="password_confirmation">Confirm new password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password"
                           autocomplete="new-password" class="apd-ctl">
                </div>

                <div>
                    <button type="submit" class="apd-btn apd-btn--primary">Save password</button>
                </div>
            </form>
        </div>
    </div>
@endsection
