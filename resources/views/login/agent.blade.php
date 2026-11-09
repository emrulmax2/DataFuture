@extends('../layout/' . $layout)

@section('head')
    <title>Agent Login - London Churchill College</title>
@endsection

@section('content')
    {{-- The agent sign-in on the applicant auth shell: same layout and palette,
         the agent portal's copy, a briefcase badge and its own three facts. --}}
    <x-applicant-auth-shell
        :opt="$opt"
        title="Agent sign in"
        subtitle="Sign in to manage your applicants, or register your agency to get started."
        eyebrow="Agent portal"
        headline="Apply for your students, <em>step by step</em>."
        subhead="Add an applicant, verify their phone and email, complete the four-step application with them and track every status and decision in one list."
        help-email="agents@londonchurchillcollege.ac.uk"
        footer-lead="Agent support & partnerships"
    >
        <x-slot:meta>
            <span><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>Referral code linked</span>
            <span><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="8" r="4"/><path d="M1 21c0-4 3.6-6 8-6"/><circle cx="17" cy="11" r="3"/><path d="M13 21c0-3 1.8-4.5 4-4.5s4 1.5 4 4.5"/></svg>Sub-agents supported</span>
            <span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 5 6v6c0 4.4 3 7.6 7 9 4-1.4 7-4.6 7-9V6l-7-3Z"/></svg>UK GDPR</span>
        </x-slot>

        <x-slot:badge>
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 7h-4V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2Z"/><path d="M10 7V5h4v2"/><path d="M2 13h20"/></svg>
        </x-slot>

        <form id="login-form" style="display:grid;gap:16px" onsubmit="return false;" novalidate>
            <div class="field">
                <label for="email">Email address</label>
                <input id="email" type="email" class="ctl login__input" placeholder="you@agency.com" autocomplete="username">
                <div id="error-email" class="err-msg login__input-error"></div>
            </div>
            <div class="field">
                <div class="lbl-row">
                    <label for="password">Password</label>
                    <a href="{{ route('agent.forget.password.get') }}" class="btn-link">Forgot password?</a>
                </div>
                <input id="password" type="password" class="ctl login__input" placeholder="••••••••" autocomplete="current-password">
                <div id="error-password" class="err-msg login__input-error"></div>
            </div>
            <label class="chk" for="remember-me">
                <input id="remember-me" type="checkbox"> Remember me
            </label>
            <button id="btn-login" type="submit" class="btn btn-dark">Sign in</button>
            <a href="{{ route('agent.register') }}" class="btn btn-outline">Create an agent account</a>
        </form>
    </x-applicant-auth-shell>

    @if (session('verifymessage'))
        <div id="verify-notification-content" class="toastify-content hidden flex">
            <i class="text-success" data-lucide="check-circle"></i>
            <div class="ml-4 mr-4">
                <div class="font-medium">Email Sent!</div>
                <div class="text-slate-500 mt-1">{{ session('verifymessage') }}</div>
            </div>
        </div>
        <button id="verify-notification-toggle" class="btn hidden btn-primary">Show Notification</button>
    @endif

    @if (session('verifySuccessMessage'))
        <div id="success-notification-content" class="toastify-content hidden flex">
            <i class="text-success" data-lucide="check-circle"></i>
            <div class="ml-4 mr-4">
                <div class="font-medium">Success !</div>
                <div class="text-slate-500 mt-1">{{ session('verifySuccessMessage') }}</div>
            </div>
        </div>
        <button id="success-notification-toggle" class="btn hidden btn-primary">Show Notification</button>
    @endif
@endsection

@section('script')
    <script type="module">
        (function () {
            async function login() {
                $('#login-form').find('.login__input').removeClass('err')
                $('#login-form').find('.login__input-error').html('')

                let email = $('#email').val()
                let password = $('#password').val()

                $('#btn-login').html('<i data-loading-icon="oval" data-color="white" class="w-5 h-5 mx-auto"></i>')
                tailwind.svgLoader()
                await helper.delay(1000)

                axios.post(`login`, { email: email, password: password }).then(res => {
                    location.href = '/agent/dashboard'
                }).catch(err => {
                    $('#btn-login').html('Sign in')
                    if (err.response.data.message != 'Wrong email or password.') {
                        for (const [key, val] of Object.entries(err.response.data.errors)) {
                            $(`#${key}`).addClass('err')
                            $(`#error-${key}`).html(val)
                        }
                    } else {
                        $(`#password`).addClass('err')
                        $(`#error-password`).html(err.response.data.message)
                    }
                })
            }

            $('#login-form').on('keyup', function (e) { if (e.keyCode === 13) login() })
            $('#btn-login').on('click', function () { login() })

            if ($('#success-notification-toggle').length > 0) { $("#success-notification-toggle").trigger('click') }
            if ($('#verify-notification-toggle').length > 0) { $("#verify-notification-toggle").trigger('click') }
        })()
    </script>
@endsection
