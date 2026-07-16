@extends('../layout/' . $layout)

@section('head')
    <title>Staff Login - London Churchill College</title>
@endsection

@section('content')
    <x-login-shell
        :opt="$opt"
        title="Sign in"
        subtitle="Use your college account to access the Student Management System."
        brand-eyebrow="Student Management System"
        brand-headline="Your studies,<br>one <em>sign-in</em> away."
        brand-subhead="Records, attendance, assessments and student support — secured with your college account."
        :brand-features="[
            ['icon' => 'lock', 'text' => 'Single sign-on with your LCC email — no extra passwords'],
            ['icon' => 'shield', 'text' => 'Your data is protected and only visible to authorised staff'],
        ]"
    >
        {{-- Primary: LCC email single sign-on --}}
        @include('login.partials.sso-google', ['route' => route('redirect.google')])

        <div class="lcc-ssohint">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="10" rx="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
            Single sign-on · no extra passwords
        </div>
        @unless(app()->environment('production'))
            {{-- Email / password is available only outside production --}}
            <div class="lcc-divider">
                <span class="lcc-divider__ln"></span>
                <span class="lcc-divider__tx">or with password</span>
                <span class="lcc-divider__ln"></span>
            </div>

            <form id="login-form" class="lcc-form" method="post" action="{{ route('login.check') }}">
                @csrf
                <div class="lcc-field">
                    <label class="lcc-label" for="email">Email address</label>
                    <input id="email" name="email" type="email" class="lcc-input login__input" placeholder="you@lcc.ac.uk" autocomplete="username">
                    <div id="error-email" class="lcc-error login__input-error"></div>
                </div>
                <div class="lcc-field">
                    <label class="lcc-label" for="password">Password</label>
                    <input id="password" name="password" type="password" class="lcc-input login__input" placeholder="••••••••" autocomplete="current-password">
                    <div id="error-password" class="lcc-error login__input-error"></div>
                </div>
                <label class="lcc-check">
                    <input id="remember-me" name="remember" type="checkbox" value="1"> Remember me
                </label>
                <button id="btn-login" type="submit" class="lcc-btn">Staff sign in</button>
                <a href="{{ route('applicant.register') }}" class="lcc-btn-ghost">Register as applicant</a>
            </form>
        @endunless

        <x-slot:footer>
            Trouble signing in? Contact <a href="mailto:itsupport@lcc.ac.uk">itsupport@lcc.ac.uk</a>
        </x-slot>
    </x-login-shell>
    {{-- SSO "no linked account" errors are now surfaced by the shell's error state (session google/microsoft). --}}
@endsection

@section('script')
    <script type="module">
        (function () {
            const form = document.getElementById('login-form');
            if (!form) return;

            let submitting = false;

            async function login(e) {
                e.preventDefault();
                if (submitting) return;            // guard against double submit (Enter + click) -> /undefined
                submitting = true;

                $(form).find('.login__input').removeClass('border-danger')
                $(form).find('.login__input-error').html('')

                $('#btn-login').html('<i data-loading-icon="oval" data-color="white" class="w-5 h-5 mx-auto"></i>')
                if (window.tailwind && typeof window.tailwind.svgLoader === 'function') {
                    window.tailwind.svgLoader()
                }

                try {
                    const res = await axios.post(form.action, new FormData(form), {
                        headers: { 'Accept': 'application/json' },
                    });
                    const redirectTo = (res.data && res.data.redirect) ? res.data.redirect : '/dashboard';
                    window.location.replace(redirectTo);
                } catch (err) {
                    submitting = false;            // allow retry after an error
                    $('#btn-login').html('Staff sign in')

                    const data = err.response && err.response.data ? err.response.data : {};
                    if (data.message !== 'Wrong email or password.' && data.errors) {
                        for (const [key, val] of Object.entries(data.errors)) {
                            $(`#${key}`).addClass('border-danger')
                            $(`#error-${key}`).html(val)
                        }
                    } else {
                        $(`#password`).addClass('border-danger')
                        $(`#error-password`).html(data.message || 'Unable to sign in. Please try again.')
                    }
                }
            }

            // The submit button (type=submit) already fires "submit" on both Enter and
            // click, so a single handler avoids invoking login() twice.
            $(form).on('submit', login)
        })()
    </script>
@endsection
