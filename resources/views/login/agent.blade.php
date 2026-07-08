@extends('../layout/' . $layout)

@section('head')
    <title>Agent Login - London Churchill College</title>
@endsection

@section('content')
    <x-login-shell
        :opt="$opt"
        title="Agent sign in"
        subtitle="Sign in to manage referrals, applications and commissions."
        brand-eyebrow="Agent Portal"
        brand-headline="Manage your referrals<br>with <em>confidence</em>."
        brand-subhead="Submit applications, track student progress and view commissions with London Churchill College."
        :brand-features="[
            ['icon' => 'lock', 'text' => 'Sign in with your registered agent email'],
            ['icon' => 'shield', 'text' => 'Applicant data is handled securely and confidentially'],
        ]"
    >
        <form id="login-form" class="lcc-form lcc-form--top" onsubmit="return false;">
            <div class="lcc-field">
                <label class="lcc-label" for="email">Email address</label>
                <input id="email" type="email" class="lcc-input login__input" placeholder="you@agency.com" autocomplete="username">
                <div id="error-email" class="lcc-error login__input-error"></div>
            </div>
            <div class="lcc-field">
                <div class="lcc-field__row">
                    <label class="lcc-label" for="password">Password</label>
                    <a href="{{ route('agent.forget.password.get') }}" class="lcc-link lcc-link--right">Forgot password?</a>
                </div>
                <input id="password" type="password" class="lcc-input login__input" placeholder="••••••••" autocomplete="current-password">
                <div id="error-password" class="lcc-error login__input-error"></div>
            </div>
            <label class="lcc-check">
                <input id="remember-me" type="checkbox"> Remember me
            </label>
            <button id="btn-login" type="submit" class="lcc-btn">Sign in</button>
        </form>

        <x-slot:footer>
            Need an agent account or help signing in? Contact <a href="mailto:agents@londonchurchillcollege.ac.uk">agents@londonchurchillcollege.ac.uk</a>
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
                    location.href = '/agent/dashboard'
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
