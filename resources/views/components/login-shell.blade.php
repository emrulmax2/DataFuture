@props([
    'title' => 'Sign in',
    'subtitle' => null,
    'brandEyebrow' => 'Student Management System',
    'brandHeadline' => 'Your studies,<br>one <em>sign-in</em> away.',
    'brandSubhead' => 'Records, attendance, assessments and student support — secured with your college account.',
    'brandFeatures' => [],
    'showBrand' => true,
    'opt' => [],
    // State control: leave null to auto-resolve from session (error/welcome), else force a state.
    'state' => null,
    // Where the "Continue to dashboard" button on the welcome state points.
    'dashboardUrl' => '/',
    // Name shown on the welcome state; falls back to session flag then authenticated user.
    'welcomeName' => null,
    // Optional error copy override for the error state.
    'errorHeading' => "That account won't work here",
    'errorDomain' => '@lcc.ac.uk',
])

@php
    // Full-colour crest + white wordmark for the dark brand panel; colour logo for the light card header (mobile).
    $brandLogo = asset('build/assets/images/red_and_white_logo.png');
    $cardLogo = (isset($opt['site_logo']) && !empty($opt['site_logo']) && Storage::disk('local')->exists('public/'.$opt['site_logo']))
        ? Storage::disk('local')->url('public/'.$opt['site_logo'])
        : 'https://sms.londonchurchillcollege.ac.uk/sms_new_copy_2/images/logo-with-blue-color-3.svg';
    $year = date('Y');

    // ---- Resolve which card state is shown on first paint (server-driven, genuine) ----
    $ssoError = session('google') ?: session('microsoft') ?: session('login_error');
    if ($state) {
        $resolvedState = $state;
    } elseif ($ssoError) {
        $resolvedState = 'error';
    } elseif (session('login_welcome')) {
        $resolvedState = 'welcome';
    } else {
        $resolvedState = 'signin';
    }

    $errorMessage = $ssoError
        ?: 'The Student Management System requires your college account ending in ' . $errorDomain . '.';

    $resolvedWelcomeName = $welcomeName ?: session('login_welcome');
    if (!$resolvedWelcomeName && auth()->check() && isset(auth()->user()->name)) {
        $resolvedWelcomeName = trim(explode(' ', auth()->user()->name)[0] ?? auth()->user()->name);
    }
@endphp

@once
<link href="https://fonts.googleapis.com/css2?family=Source+Serif+4:ital,opsz,wght@0,8..60,500;0,8..60,600;0,8..60,700;1,8..60,600&display=swap" rel="stylesheet">
<style>
    .lcc-auth, .lcc-auth * { box-sizing: border-box; }

    /* ===== Neutralise the Midone `body.login` template decorations so the new
       full-bleed design owns the whole viewport (teal blob, padding, input width) ===== */
    body.login { padding: 0 !important; margin: 0 !important; overflow-x: hidden; background: #F5F4F0 !important; }
    body.login::before, body.login::after { display: none !important; content: none !important; }
    .lcc-auth { z-index: 1; }
    .lcc-auth .login__input { min-width: 0 !important; }

    @keyframes lccRise    { from { opacity:0; transform:translateY(22px); } to { opacity:1; transform:translateY(0); } }
    @keyframes lccLogoIn  { from { opacity:0; transform:translateY(-10px) scale(0.97); } to { opacity:1; transform:translateY(0) scale(1); } }
    @keyframes lccSpin    { to { transform:rotate(360deg); } }
    @keyframes lccDriftA  { 0%,100% { transform:translate(0,0) scale(1); } 50% { transform:translate(60px,-40px) scale(1.12); } }
    @keyframes lccDriftB  { 0%,100% { transform:translate(0,0) scale(1); } 50% { transform:translate(-70px,50px) scale(1.08); } }
    @keyframes lccLineGrow{ from { width:0; } to { width:56px; } }
    @keyframes lccStateIn { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:translateY(0); } }

    /* ===== Stage ===== */
    .lcc-auth {
        font-family: 'Public Sans', system-ui, -apple-system, sans-serif;
        min-height: 100vh; position: relative; overflow: hidden; background: #F5F4F0; color: #152528;
    }
    .lcc-abs { position: absolute; pointer-events: none; }

    /* Light-side texture + gold blob */
    .lcc-auth__dots {
        inset: 0; background-image: radial-gradient(rgba(15,37,45,0.06) 1px, transparent 1px); background-size: 26px 26px;
    }
    .lcc-auth__glow {
        right: -14%; top: -30%; width: 48vw; height: 48vw; border-radius: 50%;
        background: radial-gradient(circle at 55% 45%, rgba(201,153,46,0.16), rgba(201,153,46,0) 62%);
        filter: blur(48px); animation: lccDriftB 32s ease-in-out infinite;
    }
    /* Navy diagonal panel */
    .lcc-auth__navy {
        inset: 0; clip-path: polygon(0 0, 60% 0, 48% 100%, 0 100%);
        background: linear-gradient(150deg,#12303A,#0A1B21); overflow: hidden;
    }
    .lcc-auth__navy-a {
        left: -10%; bottom: -26%; width: 52vw; height: 52vw; border-radius: 50%;
        background: radial-gradient(circle at 40% 40%, rgba(11,107,102,0.5), rgba(11,107,102,0) 65%);
        filter: blur(40px); animation: lccDriftA 26s ease-in-out infinite;
    }
    .lcc-auth__navy-b {
        left: 26%; top: -18%; width: 36vw; height: 36vw; border-radius: 50%;
        background: radial-gradient(circle, rgba(201,153,46,0.24), rgba(201,153,46,0) 60%);
        filter: blur(48px); animation: lccDriftB 34s ease-in-out infinite;
    }
    .lcc-auth__navy-dots { inset: 0; background-image: radial-gradient(rgba(255,255,255,0.05) 1px, transparent 1px); background-size: 26px 26px; }
    /* Gold seam + top line */
    .lcc-auth__seam { inset: 0; clip-path: polygon(60% 0, 60.35% 0, 48.35% 100%, 48% 100%); background: linear-gradient(180deg,#E5B94E,#C9992E,#0B6B66); }
    .lcc-auth__topline { left: 0; right: 0; top: 0; height: 3px; background: linear-gradient(90deg, #C9992E, #A31621 55%, #0B6B66); }

    /* ===== Layout ===== */
    .lcc-auth__wrap {
        position: relative; min-height: 100vh; display: flex; align-items: center;
        padding: 56px 8vw; gap: 64px; pointer-events: none;
    }

    /* ===== Brand (left) ===== */
    .lcc-auth__brand {
        flex: 1; min-width: 0; max-width: 520px; color: #fff;
        animation: lccLogoIn 0.9s cubic-bezier(0.2,0.7,0.2,1) both;
    }
    .lcc-auth__brandlogo { width: 275px; max-width: 70%; height: auto; display: block; filter: drop-shadow(0 6px 18px rgba(0,0,0,0.35)); }
    .lcc-auth__eyebrow-row { margin-top: 40px; display: flex; align-items: center; gap: 12px; }
    .lcc-auth__eyebrow-ln { height: 3px; background: linear-gradient(90deg,#C9992E,#E5B94E); border-radius: 2px; animation: lccLineGrow 1.1s 0.5s cubic-bezier(0.2,0.7,0.2,1) both; }
    .lcc-auth__eyebrow { font-size: 11px; font-weight: 700; letter-spacing: 0.28em; text-transform: uppercase; color: #C9992E; }
    .lcc-auth__headline {
        margin-top: 18px; font-family: 'Source Serif 4', Georgia, serif;
        font-size: clamp(32px, 3.4vw, 46px); line-height: 1.15; font-weight: 600; text-wrap: balance;
    }
    .lcc-auth__headline em { font-style: italic; color: #E5B94E; }
    .lcc-auth__subhead { margin-top: 18px; font-size: 15px; line-height: 1.7; color: #B9CCD1; max-width: 380px; text-wrap: pretty; }
    .lcc-auth__features { margin-top: 32px; display: flex; align-items: center; flex-wrap: wrap; gap: 14px 20px; font-size: 12px; font-weight: 600; color: #8FA9B0; }
    .lcc-auth__feature { display: inline-flex; align-items: center; gap: 8px; }
    .lcc-auth__feature svg { flex-shrink: 0; }
    .lcc-auth__feature-sep { width: 1px; height: 14px; background: rgba(255,255,255,0.16); }

    /* ===== Card (right) ===== */
    .lcc-auth__cardwrap {
        margin-left: auto; width: 416px; max-width: 100%; flex-shrink: 0; perspective: 900px;
        pointer-events: auto; animation: lccRise 0.9s 0.15s cubic-bezier(0.2,0.7,0.2,1) both;
    }
    .lcc-auth__cardtilt {
        position: relative; border-radius: 26px; padding: 1px;
        background: linear-gradient(155deg, rgba(15,37,45,0.10) 0%, rgba(15,37,45,0.05) 30%, rgba(201,153,46,0.4) 68%, rgba(11,107,102,0.32) 100%);
        box-shadow: 0 30px 70px rgba(15,37,45,0.18); transition: transform 0.15s ease-out; will-change: transform;
    }
    .lcc-auth__card { background: #FFFFFF; border-radius: 25px; padding: 38px 40px 28px; }
    .lcc-auth__cardlogo { display: none; text-align: center; margin-bottom: 22px; }
    .lcc-auth__cardlogo img { height: 40px; width: auto; }

    /* ===== States ===== */
    .lcc-state { display: none; }
    .lcc-state.is-active { display: block; animation: lccStateIn 0.35s ease both; }

    .lcc-auth__crest {
        width: 52px; height: 52px; margin: 0 auto 16px; border-radius: 16px;
        background: linear-gradient(150deg,#12303A,#0F252D); display: flex; align-items: center; justify-content: center;
        box-shadow: 0 10px 24px rgba(15,37,45,0.28);
    }
    .lcc-auth__title { font-family: 'Source Serif 4', Georgia, serif; font-size: 25px; font-weight: 600; color: #0F252D; margin: 0; }
    .lcc-auth__subtitle { margin-top: 6px; font-size: 13.5px; line-height: 1.55; color: #5B6E72; }
    .lcc-tc { text-align: center; }

    .lcc-auth__help {
        margin-top: 26px; padding-top: 16px; border-top: 1px solid #EDF1F2; text-align: center;
        font-size: 11.5px; line-height: 1.6; color: #9AA7AA;
    }
    .lcc-auth__help a { color: #8A6D1F; font-weight: 600; text-decoration: none; }
    .lcc-auth__help a:hover { text-decoration: underline; }

    /* Loading */
    .lcc-load { padding: 34px 0 26px; display: flex; flex-direction: column; align-items: center; gap: 18px; }
    .lcc-load__ring { position: relative; width: 44px; height: 44px; }
    .lcc-load__ring::before, .lcc-load__ring::after { content: ''; position: absolute; border-radius: 50%; }
    .lcc-load__ring::before { inset: 0; border: 3px solid #EDF1F2; border-top-color: #C9992E; animation: lccSpin 0.8s linear infinite; }
    .lcc-load__ring::after  { inset: 7px; border: 2px solid #F4F7F7; border-bottom-color: #0B6B66; animation: lccSpin 1.4s linear infinite reverse; }
    .lcc-load__t { font-size: 14px; font-weight: 600; color: #152528; }
    .lcc-load__s { font-size: 12.5px; color: #7C8D91; }

    /* Error / Welcome badges */
    .lcc-badge { width: 48px; height: 48px; margin: 4px auto 0; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
    .lcc-badge--err { background: #FBEDEB; border: 1px solid #F0C7C2; }
    .lcc-badge--ok  { width: 52px; height: 52px; background: #E5F2F0; border: 1px solid #BFDEDA; }
    .lcc-state__h { margin-top: 16px; font-family: 'Source Serif 4', Georgia, serif; font-size: 21px; font-weight: 600; color: #0F252D; }
    .lcc-state__p { margin-top: 8px; font-size: 13px; line-height: 1.6; color: #5B6E72; }
    .lcc-state__p b { color: #8A6D1F; font-weight: 600; }

    /* ============================================================
       Auth controls used inside the signin slot (kept stable so all
       four login pages keep rendering their buttons / password forms)
       ============================================================ */
    .lcc-sso {
        margin-top: 26px; width: 100%; height: 52px; display: flex; align-items: center; justify-content: center; gap: 12px;
        border: none; background: #0F252D; border-radius: 14px; font-family: inherit; font-size: 14.5px; font-weight: 700;
        color: #fff; cursor: pointer; text-decoration: none; box-shadow: 0 12px 28px rgba(15,37,45,0.28);
        transition: box-shadow .2s, transform .2s;
    }
    .lcc-sso:hover { box-shadow: 0 16px 36px rgba(15,37,45,0.36), 0 0 0 3px rgba(201,153,46,0.45); transform: translateY(-1px); color:#fff; }
    .lcc-sso__chip { width: 24px; height: 24px; border-radius: 6px; background: #fff; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .lcc-sso__chip svg { width: 16px; height: 16px; }
    /* Bare svg (older markup) still gets a sensible size */
    .lcc-sso > svg { width: 18px; height: 18px; flex-shrink: 0; }

    .lcc-divider { margin-top: 22px; display: flex; align-items: center; gap: 12px; }
    .lcc-divider__ln { flex: 1; height: 1px; background: #E5EBEC; }
    .lcc-divider__tx { font-size: 11px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: #8A9BA0; }

    .lcc-form { margin-top: 20px; display: flex; flex-direction: column; gap: 14px; }
    .lcc-form--top { margin-top: 26px; }
    .lcc-field { display: flex; flex-direction: column; gap: 6px; }
    .lcc-field__row { display: flex; align-items: baseline; }
    .lcc-label { font-size: 12px; font-weight: 700; color: #33484D; }
    .lcc-input {
        height: 46px; padding: 0 14px; border: 1px solid #DDE4E5; border-radius: 12px; font-family: inherit;
        font-size: 14px; color: #152528; outline: none; background: #fff; width: 100%;
        transition: border-color .15s, box-shadow .15s;
    }
    .lcc-input::placeholder { color: #A9B7BB; }
    .lcc-input:focus { border-color: #C9992E; box-shadow: 0 0 0 3px rgba(201,153,46,0.18); }
    .lcc-input.border-danger { border-color: #d32f2f; }

    .lcc-error { font-size: 12px; color: #d32f2f; }
    .lcc-error:empty { display: none; }

    .lcc-check { display: flex; align-items: center; gap: 8px; font-size: 12.5px; color: #5B6E72; cursor: pointer; user-select: none; }
    .lcc-check input { width: 15px; height: 15px; accent-color: #C9992E; cursor: pointer; }

    .lcc-btn {
        height: 50px; border: none; background: #0F252D; border-radius: 14px; font-family: inherit; font-size: 14.5px;
        font-weight: 700; color: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center;
        box-shadow: 0 12px 28px rgba(15,37,45,0.22); transition: box-shadow .2s, transform .2s;
    }
    .lcc-btn:hover { box-shadow: 0 16px 36px rgba(15,37,45,0.32), 0 0 0 3px rgba(201,153,46,0.4); transform: translateY(-1px); color:#fff; }
    .lcc-btn--gold { background: linear-gradient(150deg,#E5B94E,#C9992E); color: #0F252D; box-shadow: 0 10px 24px rgba(201,153,46,0.3); }
    .lcc-btn--gold:hover { box-shadow: 0 14px 30px rgba(201,153,46,0.42); color:#0F252D; }

    .lcc-btn-ghost {
        height: 50px; display: flex; align-items: center; justify-content: center; border: 1px solid #E2E8E9;
        background: #fff; border-radius: 14px; font-family: inherit; font-size: 14px; font-weight: 600;
        color: #33484D; cursor: pointer; text-decoration: none; transition: border-color .18s, background .18s;
    }
    .lcc-btn-ghost:hover { border-color: rgba(201,153,46,0.55); background: #FAFBFB; color:#33484D; }

    .lcc-link { font-size: 12px; font-weight: 600; color: #8A6D1F; text-decoration: none; }
    .lcc-link:hover { text-decoration: underline; }
    .lcc-link--right { margin-left: auto; }

    .lcc-ssohint { margin-top: 16px; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 12px; color: #7C8D91; }

    /* ===== Responsive ===== */
    @media (max-width: 1023px) {
        .lcc-auth__navy, .lcc-auth__seam, .lcc-auth__navy-dots, .lcc-auth__brand { display: none; }
        .lcc-auth__wrap { padding: 40px 24px; justify-content: center; }
        .lcc-auth__cardwrap { margin: 0 auto; }
        .lcc-auth__cardlogo { display: block; }
    }
    @media (max-width: 480px) {
        .lcc-auth__wrap { padding: 24px 14px; }
        .lcc-auth__card { padding: 30px 24px 24px; }
    }
</style>
@endonce

<div class="lcc-auth" data-screen-label="Login" data-initial-state="{{ $resolvedState }}">

    {{-- Background layers --}}
    <div class="lcc-abs lcc-auth__dots"></div>
    <div class="lcc-abs lcc-auth__glow"></div>

    @if($showBrand)
    <div class="lcc-abs lcc-auth__navy">
        <div class="lcc-abs lcc-auth__navy-a"></div>
        <div class="lcc-abs lcc-auth__navy-b"></div>
        <div class="lcc-abs lcc-auth__navy-dots"></div>
    </div>
    <div class="lcc-abs lcc-auth__seam"></div>
    @endif
    <div class="lcc-abs lcc-auth__topline"></div>

    <div class="lcc-auth__wrap">

        {{-- Left: brand statement --}}
        @if($showBrand)
        <div class="lcc-auth__brand">
            <img src="{{ $brandLogo }}" alt="London Churchill College" class="lcc-auth__brandlogo">
            <div class="lcc-auth__eyebrow-row">
                <div class="lcc-auth__eyebrow-ln" style="width:56px;"></div>
                <div class="lcc-auth__eyebrow">{{ $brandEyebrow }}</div>
            </div>
            {{-- Headline is developer-controlled copy (set in the blade views), rendered raw so a gold-italic <em> accent can be used. --}}
            <div class="lcc-auth__headline">{!! $brandHeadline !!}</div>
            @if($brandSubhead)
                <div class="lcc-auth__subhead">{{ $brandSubhead }}</div>
            @endif
            <div class="lcc-auth__features">
                <span class="lcc-auth__feature"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#6FD3C7" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="10" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>Single sign-on</span>
                <span class="lcc-auth__feature-sep"></span>
                <span class="lcc-auth__feature"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#6FD3C7" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>Available 24/7</span>
                <span class="lcc-auth__feature-sep"></span>
                <span class="lcc-auth__feature"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#6FD3C7" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>UK GDPR</span>
            </div>
        </div>
        @endif

        {{-- Right: card --}}
        <div class="lcc-auth__cardwrap" id="lccTiltWrap">
            <div class="lcc-auth__cardtilt" id="lccTiltCard">
                <div class="lcc-auth__card">

                    <div class="lcc-auth__cardlogo"><img src="{{ $cardLogo }}" alt="London Churchill College"></div>

                    {{-- SIGN IN --}}
                    <div class="lcc-state {{ $resolvedState === 'signin' ? 'is-active' : '' }}" data-state="signin">
                        <div class="lcc-tc">
                            <div class="lcc-auth__crest">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#E5B94E" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                            </div>
                            <h1 class="lcc-auth__title">{{ $title }}</h1>
                            @if($subtitle)
                                <p class="lcc-auth__subtitle">{{ $subtitle }}</p>
                            @endif
                        </div>

                        {{ $slot }}

                        @isset($footer)
                            <div class="lcc-auth__help">{{ $footer }}</div>
                        @endisset
                    </div>

                    {{-- LOADING --}}
                    <div class="lcc-state {{ $resolvedState === 'loading' ? 'is-active' : '' }}" data-state="loading">
                        <div class="lcc-load">
                            <div class="lcc-load__ring"></div>
                            <div class="lcc-load__t">Signing you in…</div>
                            <div class="lcc-load__s" data-loading-detail>Redirecting to your college account</div>
                        </div>
                    </div>

                    {{-- ERROR --}}
                    <div class="lcc-state {{ $resolvedState === 'error' ? 'is-active' : '' }}" data-state="error">
                        <div class="lcc-tc">
                            <div class="lcc-badge lcc-badge--err">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#B3392E" stroke-width="2.4" stroke-linecap="round"><path d="M12 8v5"></path><circle cx="12" cy="17" r="0.5" fill="#B3392E"></circle><circle cx="12" cy="12" r="10" stroke-width="2"></circle></svg>
                            </div>
                            <div class="lcc-state__h">{{ $errorHeading }}</div>
                            <div class="lcc-state__p">{{ $errorMessage }}</div>
                        </div>
                        <button class="lcc-btn" style="margin-top:24px; width:100%;" data-go="signin">Try a different account</button>
                    </div>

                    {{-- WELCOME --}}
                    <div class="lcc-state {{ $resolvedState === 'welcome' ? 'is-active' : '' }}" data-state="welcome">
                        <div class="lcc-tc">
                            <div class="lcc-badge lcc-badge--ok">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#0B6B66" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"></path></svg>
                            </div>
                            <div class="lcc-state__h">Welcome{{ $resolvedWelcomeName ? ', '.$resolvedWelcomeName : '' }}</div>
                            <div class="lcc-state__p">You're signed in for the first time. We'll take you to your dashboard.</div>
                        </div>
                        <a href="{{ $dashboardUrl }}" class="lcc-btn lcc-btn--gold" style="margin-top:24px; width:100%; text-decoration:none;">Continue to dashboard</a>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>

@once
<script>
(function () {
    function activate(name) {
        document.querySelectorAll('.lcc-auth .lcc-state').forEach(function (el) {
            el.classList.toggle('is-active', el.getAttribute('data-state') === name);
        });
    }

    document.addEventListener('click', function (e) {
        // Explicit "go back" buttons (e.g. error -> signin)
        var back = e.target.closest('[data-go]');
        if (back) { e.preventDefault(); activate(back.getAttribute('data-go')); return; }

        // Show the genuine loading state right before navigating to the SSO provider.
        var sso = e.target.closest('.lcc-auth a.lcc-sso');
        if (sso) {
            var detail = document.querySelector('.lcc-auth [data-loading-detail]');
            if (detail) { detail.textContent = 'Taking you to your college account'; }
            activate('loading');
        }
    });

    // Cursor tilt on the card (desktop only)
    var wrap = document.getElementById('lccTiltWrap');
    var card = document.getElementById('lccTiltCard');
    if (wrap && card && window.matchMedia('(min-width: 1024px)').matches) {
        window.addEventListener('mousemove', function (e) {
            var r = wrap.getBoundingClientRect();
            var dx = (e.clientX - (r.left + r.width / 2)) / window.innerWidth;
            var dy = (e.clientY - (r.top + r.height / 2)) / window.innerHeight;
            card.style.transform = 'rotateY(' + (dx * 6).toFixed(2) + 'deg) rotateX(' + (-dy * 6).toFixed(2) + 'deg)';
        });
        document.documentElement.addEventListener('mouseleave', function () {
            card.style.transform = 'rotateY(0deg) rotateX(0deg)';
        });
    }
})();
</script>
@endonce
