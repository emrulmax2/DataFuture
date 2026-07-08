@extends('../layout/' . $layout)

@section('head')
    <title>Applicant Login - London Churchill College</title>
@endsection

@section('content')
    <x-login-shell
        :opt="$opt"
        title="Applicant sign in"
        subtitle="Sign in to continue your application, or register to get started."
        brand-eyebrow="Admissions Portal"
        brand-headline="Your application,<br>all in one <em>place</em>."
        brand-subhead="Complete your details, upload documents and track your application status with London Churchill College."
        :brand-features="[
            ['icon' => 'lock', 'text' => 'Sign in with the email you registered with'],
            ['icon' => 'shield', 'text' => 'Your personal details are kept secure and private'],
        ]"
    >
        <form id="login-form" class="lcc-form lcc-form--top" onsubmit="return false;">
            <div class="lcc-field">
                <label class="lcc-label" for="email">Email address</label>
                <input id="email" type="email" class="lcc-input login__input" placeholder="you@example.com" autocomplete="username">
                <div id="error-email" class="lcc-error login__input-error"></div>
            </div>
            <div class="lcc-field">
                <div class="lcc-field__row">
                    <label class="lcc-label" for="password">Password</label>
                    <a href="{{ route('applicant.forget.password.get') }}" class="lcc-link lcc-link--right">Forgot password?</a>
                </div>
                <input id="password" type="password" class="lcc-input login__input" placeholder="••••••••" autocomplete="current-password">
                <div id="error-password" class="lcc-error login__input-error"></div>
            </div>
            <label class="lcc-check">
                <input id="remember-me" type="checkbox"> Remember me
            </label>
            <button id="btn-login" type="submit" class="lcc-btn">Sign in</button>
            <a href="{{ route('applicant.register') }}" class="lcc-btn-ghost">Create an applicant account</a>
        </form>

        <x-slot:footer>
            Need help with your application? Contact <a href="mailto:admissions@londonchurchillcollege.ac.uk">admissions@londonchurchillcollege.ac.uk</a>
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
                $('#login-form').find('.login__input').removeClass('border-danger')
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

            if ($('#success-notification-toggle').length > 0) { $("#success-notification-toggle").trigger('click') }
            if ($('#verify-notification-toggle').length > 0) { $("#verify-notification-toggle").trigger('click') }
        })()
    </script>
@endsection
