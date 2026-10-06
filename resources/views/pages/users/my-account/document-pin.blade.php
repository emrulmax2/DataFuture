@extends('../layout/my-account')

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('styles')
    @vite('resources/css/staff-document-pin.css')
@endsection

@section('subcontent')
    @include('pages.users.my-account.show-info')

    {{--
        One of three panels is on screen, switched by resources/js/user-document-pin.js:
          new     no PIN yet - choose one
          set     a PIN exists - it can only be changed, with the current one
          reset   forgotten - a code has been emailed; the code and a new PIN replace it
        A PIN is never shown, here or anywhere: only its hash is kept.
    --}}
    <section class="myhr-sign" id="documentPinPage" data-state="{{ $impersonating ? 'blocked' : ($hasPin ? 'set' : 'new') }}" data-screen-label="PIN">
        <header class="myhr-sign__header">
            <span class="myhr-sign__header-icon">
                <i data-lucide="key-round"></i>
            </span>
            <div class="myhr-sign__header-text">
                <h2>PIN</h2>
                <p>Your personal PIN for opening encrypted staff documents. You choose it: {{ $minLength }} to {{ $maxLength }} digits.</p>
            </div>
        </header>

        @if($impersonating)
            <div class="myhr-sign-alert">
                <i data-lucide="user-cog"></i>
                <div>
                    <strong>Not available while you are signed in as another user.</strong>
                    A PIN belongs to one person, so this account's PIN cannot be set or changed from an impersonated session. To open an encrypted document while signed in as someone else, enter your own PIN when asked. To manage your own PIN, leave impersonation first.
                </div>
            </div>
        @endif

        <div class="myhr-pin__layout">
            <div class="myhr-pin__main">
                @unless($impersonating)
                    <div class="myhr-pin-state" data-panel="new" {{ $hasPin ? 'hidden' : '' }}>
                        @if($prompted)
                            {{-- They were sent here and are held here until a PIN is set (PromptPinSetup), so say why. --}}
                            <div class="myhr-pin-notice" role="status">
                                <i data-lucide="info"></i>
                                <span><strong>PIN is enabled for your account.</strong> Set up your PIN to continue. Once it is saved you are taken on to where you were going.</span>
                            </div>
                        @endif
                        <span class="myhr-pin-state__eyebrow"><i data-lucide="sparkles"></i> Not set up yet</span>
                        <h3>Set up your PIN</h3>
                        <p>Choose a PIN of {{ $minLength }} to {{ $maxLength }} digits. You will be asked for it each time you view or download an encrypted staff document.</p>

                        <form id="documentPinSetupForm" class="myhr-pin-form" autocomplete="off" novalidate>
                            <div class="myhr-pin-field">
                                <label for="documentPinNew">New PIN</label>
                                <input type="password" id="documentPinNew" name="pin" inputmode="numeric" pattern="[0-9]*" minlength="{{ $minLength }}" maxlength="{{ $maxLength }}" autocomplete="one-time-code" aria-describedby="documentPinNewHint">
                                <small id="documentPinNewHint">{{ $minLength }} to {{ $maxLength }} digits. Not one digit repeated, and not a run like 123456.</small>
                            </div>
                            <div class="myhr-pin-field">
                                <label for="documentPinNewConfirm">Confirm PIN</label>
                                <input type="password" id="documentPinNewConfirm" name="pin_confirmation" inputmode="numeric" pattern="[0-9]*" maxlength="{{ $maxLength }}" autocomplete="one-time-code">
                            </div>
                            <div class="myhr-pin-error" data-error="new" role="alert"></div>
                            <div class="myhr-pin-state__actions">
                                <button type="submit" id="documentPinSetup" class="myhr-sign-btn myhr-sign-btn--primary">
                                    <i data-lucide="key-round"></i>
                                    Save my PIN
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="myhr-pin-state" data-panel="set" {{ $hasPin ? '' : 'hidden' }}>
                        <span class="myhr-pin-state__eyebrow"><i data-lucide="shield-check"></i> PIN is set</span>
                        <h3>Change your PIN</h3>
                        <p>
                            <span id="documentPinSetAt" data-prefix="Your PIN was last set on ">{{ $setAt !== '' ? 'Your PIN was last set on '.$setAt.'.' : '' }}</span>
                            To change it, enter your current PIN and then the new one.
                        </p>

                        <div class="myhr-pin-success" id="documentPinSuccess" role="status" hidden>
                            <span id="documentPinSuccessText"></span>
                            {{-- Shown only when sign-in sent them here on their way somewhere else. --}}
                            <a id="documentPinContinue" href="#" class="myhr-pin-success__link" hidden>Continue <i data-lucide="arrow-right"></i></a>
                        </div>

                        <form id="documentPinChangeForm" class="myhr-pin-form" autocomplete="off" novalidate>
                            <div class="myhr-pin-field">
                                <label for="documentPinCurrent">Current PIN</label>
                                <input type="password" id="documentPinCurrent" name="current_pin" inputmode="numeric" pattern="[0-9]*" maxlength="{{ $maxLength }}" autocomplete="one-time-code">
                            </div>
                            <div class="myhr-pin-field">
                                <label for="documentPinChangeNew">New PIN</label>
                                <input type="password" id="documentPinChangeNew" name="pin" inputmode="numeric" pattern="[0-9]*" minlength="{{ $minLength }}" maxlength="{{ $maxLength }}" autocomplete="one-time-code" aria-describedby="documentPinChangeHint">
                                <small id="documentPinChangeHint">{{ $minLength }} to {{ $maxLength }} digits. Not one digit repeated, and not a run like 123456.</small>
                            </div>
                            <div class="myhr-pin-field">
                                <label for="documentPinChangeConfirm">Confirm new PIN</label>
                                <input type="password" id="documentPinChangeConfirm" name="pin_confirmation" inputmode="numeric" pattern="[0-9]*" maxlength="{{ $maxLength }}" autocomplete="one-time-code">
                            </div>
                            <div class="myhr-pin-error" data-error="set" role="alert"></div>
                            <div class="myhr-pin-state__actions">
                                <button type="submit" id="documentPinChange" class="myhr-sign-btn myhr-sign-btn--primary">
                                    <i data-lucide="refresh-cw"></i>
                                    Change PIN
                                </button>
                            </div>
                        </form>

                        <div class="myhr-pin-forgot">
                            <p>Forgotten it? Your PIN cannot be shown again, but you can reset it yourself. We email a code to <strong>{{ $resetEmail }}</strong>.</p>
                            <button type="button" id="documentPinForgot" class="myhr-sign-btn">
                                <i data-lucide="mail"></i>
                                Reset it by email
                            </button>
                        </div>
                    </div>

                    <div class="myhr-pin-state" data-panel="reset" hidden>
                        <span class="myhr-pin-state__eyebrow"><i data-lucide="mail-check"></i> Check your email</span>
                        <h3>Reset your PIN</h3>
                        <p>We have emailed a {{ $codeLength }}-digit code to <strong>{{ $resetEmail }}</strong>. Enter it here with your new PIN. The code works once, for {{ $codeMinutes }} minutes, in this browser.</p>

                        <form id="documentPinResetForm" class="myhr-pin-form" autocomplete="off" novalidate>
                            <div class="myhr-pin-field">
                                <label for="documentPinResetCode">Code from the email</label>
                                <input type="text" id="documentPinResetCode" name="code" inputmode="numeric" pattern="[0-9]*" maxlength="{{ $codeLength }}" autocomplete="one-time-code">
                            </div>
                            <div class="myhr-pin-field">
                                <label for="documentPinResetNew">New PIN</label>
                                <input type="password" id="documentPinResetNew" name="pin" inputmode="numeric" pattern="[0-9]*" minlength="{{ $minLength }}" maxlength="{{ $maxLength }}" autocomplete="one-time-code" aria-describedby="documentPinResetHint">
                                <small id="documentPinResetHint">{{ $minLength }} to {{ $maxLength }} digits. Not one digit repeated, and not a run like 123456.</small>
                            </div>
                            <div class="myhr-pin-field">
                                <label for="documentPinResetConfirm">Confirm new PIN</label>
                                <input type="password" id="documentPinResetConfirm" name="pin_confirmation" inputmode="numeric" pattern="[0-9]*" maxlength="{{ $maxLength }}" autocomplete="one-time-code">
                            </div>
                            <div class="myhr-pin-error" data-error="reset" role="alert"></div>
                            <div class="myhr-pin-state__actions">
                                <button type="submit" id="documentPinReset" class="myhr-sign-btn myhr-sign-btn--primary">
                                    <i data-lucide="key-round"></i>
                                    Reset PIN
                                </button>
                                <button type="button" id="documentPinResend" class="myhr-sign-btn">
                                    <i data-lucide="mail"></i>
                                    Send a new code
                                </button>
                                <button type="button" id="documentPinResetCancel" class="myhr-sign-btn myhr-sign-btn--muted">Cancel</button>
                            </div>
                        </form>
                    </div>
                @endunless
            </div>

            <aside class="myhr-pin__aside myhr-pin-facts">
                <h4>How the PIN works</h4>
                <ul>
                    <li>
                        <i data-lucide="file-lock-2"></i>
                        <span><strong>It opens encrypted documents</strong>You are asked for it every time you view or download one.</span>
                    </li>
                    <li>
                        <i data-lucide="user-check"></i>
                        <span><strong>It is yours alone</strong>It is never shown on screen and nobody can look it up or reset it for you, HR included. Someone signed in as you cannot change it, and has to use their own PIN to open an encrypted document.</span>
                    </li>
                    <li>
                        <i data-lucide="shield-alert"></i>
                        <span><strong>It locks after {{ $maxAttempts }} wrong attempts</strong>Then it cannot be used for {{ $lockMinutes }} minutes.</span>
                    </li>
                    <li>
                        <i data-lucide="scan-eye"></i>
                        <span><strong>Every open is recorded</strong>Who opened which document, and when, is kept in the document access log.</span>
                    </li>
                </ul>
            </aside>
        </div>
    </section>
@endsection

@section('script')
    @vite('resources/js/user-document-pin.js')
@endsection
