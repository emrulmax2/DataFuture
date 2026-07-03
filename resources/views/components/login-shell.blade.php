@props([
    'title' => 'Sign in',
    'subtitle' => null,
    'brandEyebrow' => 'Student Management System',
    'brandHeadline' => 'One sign-in for everything at LCC.',
    'brandSubhead' => 'Records, attendance, assessments and student support — all in one place, secured with your college account.',
    'brandFeatures' => [],
    'showBrand' => true,
    'opt' => [],
])

@php
    // White logo for the dark brand panel; falls back to the shared white mark.
    $brandLogo = asset('build/assets/images/logo_white.svg');
    // Colour logo for the light card header (mobile / brand panel hidden).
    $cardLogo = (isset($opt['site_logo']) && !empty($opt['site_logo']) && Storage::disk('local')->exists('public/'.$opt['site_logo']))
        ? Storage::disk('local')->url('public/'.$opt['site_logo'])
        : 'https://sms.londonchurchillcollege.ac.uk/sms_new_copy_2/images/logo-with-blue-color-3.svg';
    $year = date('Y');
@endphp

@once
<style>
    .lcc-auth * { box-sizing: border-box; }
    .lcc-auth {
        font-family: 'Public Sans', system-ui, -apple-system, sans-serif;
        min-height: 100vh; display: flex; background: #EEF1F2; color: #152528;
    }

    /* ===== Brand panel ===== */
    .lcc-auth__brand {
        flex: 1.05; min-width: 0; background: #0F252D; position: relative; overflow: hidden;
        display: flex; flex-direction: column; padding: 44px 56px;
    }
    .lcc-auth__shape { position: absolute; border-radius: 50%; pointer-events: none; }
    .lcc-auth__shape--a { top: -180px; right: -180px; width: 460px; height: 460px; border: 1px solid rgba(255,255,255,0.06); }
    .lcc-auth__shape--b { top: -120px; right: -120px; width: 340px; height: 340px; border: 1px solid rgba(255,255,255,0.05); }
    .lcc-auth__shape--c { bottom: -220px; left: -160px; width: 520px; height: 520px; background: rgba(201,153,46,0.05); }

    .lcc-auth__brandlogo { position: relative; }
    .lcc-auth__brandlogo img { height: 46px; width: auto; display: block; }

    .lcc-auth__brandbody { position: relative; margin: auto 0; padding: 48px 0; max-width: 460px; }
    .lcc-auth__eyebrow {
        font-size: 11px; font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase;
        color: #C9992E; margin-bottom: 18px;
    }
    .lcc-auth__headline { font-size: 40px; line-height: 1.15; font-weight: 800; color: #fff; text-wrap: pretty; }
    .lcc-auth__subhead { margin-top: 18px; font-size: 15px; line-height: 1.6; color: #9DB4BA; text-wrap: pretty; }

    .lcc-auth__features { margin-top: 36px; display: flex; flex-direction: column; gap: 14px; }
    .lcc-auth__feature { display: flex; align-items: center; gap: 12px; }
    .lcc-auth__feature-ic {
        width: 30px; height: 30px; border-radius: 8px; background: rgba(201,153,46,0.16); color: #E5B94E;
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .lcc-auth__feature-tx { font-size: 13.5px; color: #C4D3D7; }

    .lcc-auth__brandfoot { position: relative; font-size: 12px; color: #6E8A91; }

    /* ===== Form panel ===== */
    .lcc-auth__panel {
        flex: 1; min-width: 0; display: flex; align-items: center; justify-content: center;
        padding: 48px 32px; background: #EEF1F2;
    }
    .lcc-auth__card {
        width: 100%; max-width: 400px; background: #fff; border: 1px solid #E5EBEC; border-radius: 16px;
        box-shadow: 0 12px 32px rgba(15,37,45,0.08); padding: 40px 40px 32px; display: flex; flex-direction: column;
    }
    .lcc-auth__cardlogo { display: none; margin-bottom: 24px; }
    .lcc-auth__cardlogo img { height: 42px; width: auto; }

    .lcc-auth__title { font-size: 24px; font-weight: 800; color: #0F252D; margin: 0; }
    .lcc-auth__subtitle { margin-top: 6px; font-size: 13.5px; line-height: 1.55; color: #5B6E72; }

    /* ===== Auth controls (used inside the slot) ===== */
    .lcc-sso {
        margin-top: 28px; height: 48px; display: flex; align-items: center; justify-content: center; gap: 12px;
        border: 1px solid #DDE4E5; background: #fff; border-radius: 10px; font-family: inherit; font-size: 14px;
        font-weight: 600; color: #152528; cursor: pointer; text-decoration: none;
        transition: border-color .18s, box-shadow .18s;
    }
    .lcc-sso:hover { border-color: #0B6B66; box-shadow: 0 2px 8px rgba(15,37,45,0.08); }
    .lcc-sso svg { width: 18px; height: 18px; flex-shrink: 0; }

    .lcc-divider { margin-top: 24px; display: flex; align-items: center; gap: 12px; }
    .lcc-divider__ln { flex: 1; height: 1px; background: #E5EBEC; }
    .lcc-divider__tx { font-size: 11px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: #8A9BA0; }

    .lcc-form { margin-top: 20px; display: flex; flex-direction: column; gap: 14px; }
    .lcc-form--top { margin-top: 28px; }
    .lcc-field { display: flex; flex-direction: column; gap: 6px; }
    .lcc-field__row { display: flex; align-items: baseline; }
    .lcc-label { font-size: 12px; font-weight: 700; color: #33484D; }
    .lcc-input {
        height: 44px; padding: 0 14px; border: 1px solid #DDE4E5; border-radius: 10px; font-family: inherit;
        font-size: 14px; color: #152528; outline: none; background: #fff; width: 100%;
        transition: border-color .15s, box-shadow .15s;
    }
    .lcc-input::placeholder { color: #A9B7BB; }
    .lcc-input:focus { border-color: #0B6B66; box-shadow: 0 0 0 3px rgba(11,107,102,0.12); }
    .lcc-input.border-danger { border-color: #d32f2f; }

    .lcc-error { font-size: 12px; color: #d32f2f; min-height: 0; }
    .lcc-error:empty { display: none; }

    .lcc-check { display: flex; align-items: center; gap: 8px; font-size: 12.5px; color: #5B6E72; cursor: pointer; user-select: none; }
    .lcc-check input { width: 15px; height: 15px; accent-color: #0B6B66; cursor: pointer; }

    .lcc-btn {
        height: 48px; border: none; background: #0F252D; border-radius: 10px; font-family: inherit; font-size: 14px;
        font-weight: 700; color: #fff; cursor: pointer; transition: background .18s; display: flex;
        align-items: center; justify-content: center;
    }
    .lcc-btn:hover { background: #183640; }

    .lcc-btn-ghost {
        height: 48px; display: flex; align-items: center; justify-content: center; border: 1px solid #DDE4E5;
        background: #fff; border-radius: 10px; font-family: inherit; font-size: 14px; font-weight: 600;
        color: #33484D; cursor: pointer; text-decoration: none; transition: border-color .18s, background .18s;
    }
    .lcc-btn-ghost:hover { border-color: #0B6B66; background: #F7FAFA; }

    .lcc-link { font-size: 12px; font-weight: 600; color: #0B6B66; text-decoration: none; }
    .lcc-link:hover { text-decoration: underline; }
    .lcc-link--right { margin-left: auto; }

    .lcc-auth__help {
        margin-top: 28px; padding-top: 20px; border-top: 1px solid #EDF1F2; font-size: 12.5px; line-height: 1.6; color: #5B6E72;
    }
    .lcc-auth__help a { color: #0B6B66; font-weight: 600; text-decoration: none; }
    .lcc-auth__help a:hover { text-decoration: underline; }

    /* ===== Responsive ===== */
    @media (max-width: 1023px) {
        .lcc-auth__brand { display: none; }
        .lcc-auth__cardlogo { display: block; }
    }
    @media (max-width: 480px) {
        .lcc-auth__panel { padding: 28px 18px; }
        .lcc-auth__card { padding: 28px 24px 24px; }
        .lcc-auth__headline { font-size: 32px; }
    }
</style>
@endonce

<div class="lcc-auth" data-screen-label="Login">

    @if($showBrand)
    <aside class="lcc-auth__brand">
        <span class="lcc-auth__shape lcc-auth__shape--a"></span>
        <span class="lcc-auth__shape lcc-auth__shape--b"></span>
        <span class="lcc-auth__shape lcc-auth__shape--c"></span>

        <div class="lcc-auth__brandlogo">
            <img src="{{ $brandLogo }}" alt="London Churchill College">
        </div>

        <div class="lcc-auth__brandbody">
            <div class="lcc-auth__eyebrow">{{ $brandEyebrow }}</div>
            <div class="lcc-auth__headline">{{ $brandHeadline }}</div>
            @if($brandSubhead)
                <div class="lcc-auth__subhead">{{ $brandSubhead }}</div>
            @endif

            @if(!empty($brandFeatures))
            <div class="lcc-auth__features">
                @foreach($brandFeatures as $feature)
                <div class="lcc-auth__feature">
                    <div class="lcc-auth__feature-ic">
                        @if(($feature['icon'] ?? 'lock') === 'shield')
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                        @else
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="10" rx="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                        @endif
                    </div>
                    <div class="lcc-auth__feature-tx">{{ $feature['text'] }}</div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        <div class="lcc-auth__brandfoot">&copy; {{ $year }} London Churchill College &middot; sms.londonchurchillcollege.ac.uk</div>
    </aside>
    @endif

    <main class="lcc-auth__panel">
        <div class="lcc-auth__card">
            <div class="lcc-auth__cardlogo">
                <img src="{{ $cardLogo }}" alt="London Churchill College">
            </div>

            <h1 class="lcc-auth__title">{{ $title }}</h1>
            @if($subtitle)
                <p class="lcc-auth__subtitle">{{ $subtitle }}</p>
            @endif

            {{ $slot }}

            @isset($footer)
                <div class="lcc-auth__help">{{ $footer }}</div>
            @endisset
        </div>
    </main>

</div>
