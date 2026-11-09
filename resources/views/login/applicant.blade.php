@extends('../layout/' . $layout)

@section('head')
    <title>Applicant Login - London Churchill College</title>
@endsection

@section('content')
    <x-applicant-auth-shell
        :opt="$opt"
        title="Applicant sign in"
        subtitle="Sign in to continue your application, or register to get started."
        eyebrow="Admissions portal"
        headline="Your application, all in one <em>place</em>."
        subhead="Complete your details, upload documents and track your application status with London Churchill College."
    >
        <form id="login-form" style="display:grid;gap:16px" onsubmit="return false;" novalidate>
            <div class="field">
                <label for="email">Email address</label>
                <input id="email" type="email" class="ctl login__input" placeholder="you@example.com" autocomplete="username">
                <div id="error-email" class="err-msg login__input-error"></div>
            </div>
            <div class="field">
                <div class="lbl-row">
                    <label for="password">Password</label>
                    <a href="{{ route('applicant.forget.password.get') }}" class="btn-link">Forgot password?</a>
                </div>
                <input id="password" type="password" class="ctl login__input" placeholder="••••••••" autocomplete="current-password">
                <div id="error-password" class="err-msg login__input-error"></div>
            </div>
            <label class="chk" for="remember-me">
                <input id="remember-me" type="checkbox"> Remember me
            </label>
            <button id="btn-login" type="submit" class="btn btn-dark">Sign in</button>
            <a href="{{ route('applicant.register') }}" class="btn btn-outline">Create an applicant account</a>
        </form>

        <p style="font-size:12.5px;color:#6b6a63;margin:0;text-align:center">
            Applying through an education agent? Your agent creates the application for you; admissions can send you a sign-in link later.
        </p>
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
                <div class="font-medium">Notice</div>
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
                    location.href = '/applicant/dashboard'
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
