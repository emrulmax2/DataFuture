@extends('../layout/' . $layout)

@section('head')
    <title>Student Login - London Churchill College</title>
@endsection

@section('content')
    <x-login-shell
        :opt="$opt ?? []"
        title="Student sign in"
        subtitle="Use your LCC student email to access your dashboard, timetable and results."
        brand-eyebrow="Student Portal"
        brand-headline="Everything you need,<br>in one <em>login</em>."
        brand-subhead="Timetable, attendance, results and support — all secured with your LCC student account."
        :brand-features="[
            ['icon' => 'lock', 'text' => 'Single sign-on with your LCC student email'],
            ['icon' => 'shield', 'text' => 'Your records are private and protected'],
        ]"
    >
        {{-- Primary: LCC student email single sign-on --}}
        @include('login.partials.sso-google', ['route' => route('students.redirect.google'), 'label' => 'Continue with LCC Email'])

        <div class="lcc-ssohint">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="10" rx="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
            Single sign-on · no extra passwords
        </div>

        @unless(app()->environment('production'))
            <div class="lcc-divider">
                <span class="lcc-divider__ln"></span>
                <span class="lcc-divider__tx">or with password</span>
                <span class="lcc-divider__ln"></span>
            </div>

            <form id="login-form" class="lcc-form" onsubmit="return false;">
                <div class="lcc-field">
                    <label class="lcc-label" for="email">Email address</label>
                    <input id="email" type="email" class="lcc-input login__input" placeholder="you@student.lcc.ac.uk" autocomplete="username">
                    <div id="error-email" class="lcc-error login__input-error"></div>
                </div>
                <div class="lcc-field">
                    <label class="lcc-label" for="password">Password</label>
                    <input id="password" type="password" class="lcc-input login__input" placeholder="••••••••" autocomplete="current-password">
                    <div id="error-password" class="lcc-error login__input-error"></div>
                </div>
                <button id="btn-login" type="submit" class="lcc-btn">Sign in</button>
            </form>
        @endunless

        <x-slot:footer>
            Trouble signing in? Contact <a href="mailto:itsupport@lcc.ac.uk">itsupport@lcc.ac.uk</a>
        </x-slot>
    </x-login-shell>

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

    {{-- SSO "no linked account" errors are now surfaced by the shell's error state (session google/microsoft). --}}
@endsection

@section('script')
    <script type="module">
        (function () {
            if ($('#success-notification-toggle').length > 0) { $("#success-notification-toggle").trigger('click') }
            if ($('#verify-notification-toggle').length > 0) { $("#verify-notification-toggle").trigger('click') }

            if ($('#login-form').length === 0) return;

            async function login() {
                $('#login-form').find('.login__input').removeClass('border-danger')
                $('#login-form').find('.login__input-error').html('')

                let email = $('#email').val()
                let password = $('#password').val()

                $('#btn-login').html('<i data-loading-icon="oval" data-color="white" class="w-5 h-5 mx-auto"></i>')
                tailwind.svgLoader()
                await helper.delay(1000)

                axios.post(route('students.login'), { email: email, password: password }).then(res => {
                    location.href = route('students.dashboard')
                }).catch(err => {
                    $('#btn-login').html('Sign in')
                    if (err.response.data.message != 'Wrong email or password.') {
                        for (const [key, val] of Object.entries(err.response.data.errors)) {
                            $(`#${key}`).addClass('border-danger')
                            $(`#error-${key}`).html(val)
                        }
                    } else {
                        $(`#password`).addClass('border-danger')
                        $(`#error-password`).html(err.response.data.message)
                    }
                })
            }

            $('#login-form').on('keyup', function (e) { if (e.keyCode === 13) login() })
            $('#btn-login').on('click', function () { login() })
        })()
    </script>
@endsection
