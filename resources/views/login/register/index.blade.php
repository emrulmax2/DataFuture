@extends('../layout/' . $layout)

@section('head')
    <title>Create an applicant account - London Churchill College</title>
@endsection

@section('content')
    <x-applicant-auth-shell
        title="Create an applicant account"
        subtitle="Use an email you check often. You must verify it before you can start an application."
        eyebrow="Admissions portal"
        headline="Let's get you <em>set up</em>."
        subhead="Create your account, then complete your application, upload your documents and track your progress in one place."
    >
        <x-slot:badge>
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/></svg>
        </x-slot>

        <form id="register-form" style="display:grid;gap:16px" onsubmit="return false;" novalidate>
            <div class="field">
                <label for="email">Email address</label>
                <input id="email" name="email" type="email" class="ctl login__input" placeholder="you@example.com" autocomplete="email">
                <div id="error-email" class="err-msg login__input-error"></div>
            </div>

            <div class="field">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" class="ctl login__input" placeholder="••••••••" autocomplete="new-password" aria-describedby="reg-pw-strength">
                <div id="error-password" class="err-msg login__input-error"></div>

                <div class="pw-strength" id="reg-pw-strength" aria-live="polite">
                    <div class="pw-track"><i></i></div>
                    <div class="row"><span>Password strength</span><b>&mdash;</b></div>
                </div>

                <button type="button" class="pw-tip" id="pw-tip" aria-expanded="false" aria-controls="pw-rules">What is a secure password?</button>
                <ul class="pw-rules" id="pw-rules" hidden>
                    <li data-rule="len"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>At least 8 characters</li>
                    <li data-rule="case"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>Upper and lower case letters</li>
                    <li data-rule="num"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>At least one number</li>
                    <li data-rule="sym"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>A symbol, for example ! ? #</li>
                </ul>
            </div>

            <div class="field">
                <label for="password_confirmation">Confirm password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" class="ctl login__input" placeholder="••••••••" autocomplete="new-password">
                <div id="error-password_confirmation" class="err-msg login__input-error"></div>
            </div>

            <button id="btn-login" type="submit" class="btn btn-dark">Continue</button>
            <a href="{{ route('applicant.login') }}" class="btn btn-outline">Already registered? Sign in</a>
        </form>
    </x-applicant-auth-shell>

    <!-- BEGIN: Notification Content -->
    <div id="success-notification-content" class="toastify-content hidden flex">
        <i class="text-success" data-lucide="check-circle"></i>
        <div class="ml-4 mr-4">
            <div class="font-medium">Account Created!</div>
            <div class="text-slate-500 mt-1">An email sent to your email address to verify your account.</div>
        </div>
    </div>
    <!-- END: Notification Content -->
    <button id="success-notification-toggle" class="btn btn-primary hidden">Show Notification</button>
@endsection

@section('script')
    <script type="module">
        (function () {
            /* The four things the rules list shows, and the five strength steps the
               meter moves through — the label and the colour come from the same score. */
            const PW_RULES = [
                { id: 'len',  test: p => p.length >= 8 },
                { id: 'case', test: p => /[A-Z]/.test(p) && /[a-z]/.test(p) },
                { id: 'num',  test: p => /\d/.test(p) },
                { id: 'sym',  test: p => /[^A-Za-z0-9]/.test(p) },
            ]
            const PW_LEVELS = [
                { label: '',       color: '#c9d3e2' },
                { label: 'Weak',   color: '#d2203f' },
                { label: 'Fair',   color: '#e08a1e' },
                { label: 'Good',   color: '#2f4fe8' },
                { label: 'Strong', color: '#1b8a5a' },
            ]

            const meter = document.getElementById('reg-pw-strength')
            const rules = document.getElementById('pw-rules')
            const tip = document.getElementById('pw-tip')

            function paintStrength(password) {
                const passed = PW_RULES.filter(r => r.test(password))
                const score = password ? passed.length : 0
                const lvl = PW_LEVELS[score]

                meter.style.setProperty('--pw', lvl.color)
                meter.style.setProperty('--w', (score * 25) + '%')
                meter.querySelector('.row b').innerHTML = lvl.label || '&mdash;'

                PW_RULES.forEach(r => {
                    const li = rules.querySelector(`[data-rule="${r.id}"]`)
                    if (li) li.classList.toggle('ok', r.test(password))
                })
            }

            tip.addEventListener('click', function () {
                const open = rules.hasAttribute('hidden')
                if (open) { rules.removeAttribute('hidden') } else { rules.setAttribute('hidden', '') }
                tip.setAttribute('aria-expanded', open ? 'true' : 'false')
            })

            $('#password').on('keyup input', function () { paintStrength(this.value) })
            paintStrength('')

            async function register() {
                // Reset state
                $('#register-form').find('.login__input').removeClass('err')
                $('#register-form').find('.login__input-error').html('')

                // Post form
                let myform = document.getElementById("register-form");
                let formData = new FormData(myform);
                // Loading state
                $('#btn-login').html('<i data-loading-icon="oval" data-color="white" class="w-5 h-5 mx-auto"></i>')
                tailwind.svgLoader()
                await helper.delay(500)

                axios.post(`register`, formData).then(res => {

                    $("#success-notification-toggle").trigger('click')
                    $('#btn-login').html('Continue')
                    // The account is signed in as it is created (RegisterController),
                    // so the applicant goes straight to their dashboard.
                    setTimeout(function () {
                        location.href = @json(route('applicant.dashboard'));
                    }, 3000);
                }).catch(err => {
                    $('#register-form').find('.login__input').removeClass('err')
                    $('#register-form').find('.login__input-error').html('')
                    $('#btn-login').html('Continue')
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

            $('#register-form').on('keyup', function (e) {
                if (e.keyCode === 13) {
                    register()
                }
            })

            $('#btn-login').on('click', function () {
                register()
            })
        })()
    </script>
@endsection
