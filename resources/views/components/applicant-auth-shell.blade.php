@props([
    'title' => 'Applicant sign in',
    'subtitle' => null,
    'eyebrow' => 'Admissions portal',
    // The headline carries one <em> — it renders italic in the accent tint.
    'headline' => 'Your application, all in one <em>place</em>.',
    'subhead' => 'Complete your details, upload documents and track your application status with London Churchill College.',
    'helpEmail' => 'admissions@lcc.ac.uk',
    'opt' => [],
])

@php
    /* The mark comes from Site settings -> Logos & favicon (`site_logo`), so
       replacing it there replaces it here and on every other screen that reads
       the setting. Resolved in the component rather than from the `opt` prop,
       because the register screen's controller does not load the options.

       The fallback is a tracked source image: `public/build` is build output and
       `vite build` empties it, so no asset may be written there by hand. */
    $siteLogoFile = \App\Models\Option::where('category', 'SITE_SETTINGS')->where('name', 'site_logo')->value('value');
    $brandLogo = ($siteLogoFile && Storage::disk('local')->exists('public/'.$siteLogoFile))
        ? url('storage/'.$siteLogoFile)
        : asset('build/assets/images/red_and_white_logo.png');

    /* Messages the applicant auth flow can land on this page with. The forget
       password controller redirects here with `message` on a bad token, and the
       verification controller with `verifymessage`; before this shell there was
       nowhere on the page for the first one to show. */
    $flashBad = session('error') === 'token' ? session('message') : (session('error') ? session('error') : null);
    $flashOk = session('status');
@endphp

@once
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,650;12..96,700&family=Hanken+Grotesk:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Source+Serif+4:ital,opsz,wght@0,8..60,500;0,8..60,600;1,8..60,500&display=swap" rel="stylesheet">
<style>
/* ============================================================================
   Applicant portal auth — midnight slanted panel on a cool light canvas.
   Every rule below is scoped to .apa so the four other login screens that share
   the login-shell component (staff, student, agent, welcome) keep their design.
   ========================================================================== */
.apa, .apa *{box-sizing:border-box}
/* Several rules below set `display`, which would otherwise beat the `hidden`
   attribute the password-rules list is toggled with. */
.apa [hidden]{display:none !important}

/* Neutralise the Midone `body.login` decorations so this design owns the viewport. */
body.login{padding:0 !important;margin:0 !important;overflow-x:hidden;background:#f3f4f8 !important}
body.login::before, body.login::after{display:none !important;content:none !important}
.apa .login__input{min-width:0 !important}

.apa{
  --paper:#eef1f7; --paper-2:#e6ebf4; --card:#ffffff; --ink:#0d1a30; --ink-2:#3b4a64; --muted:#667590; --rule:#e1e7f0; --rule-2:#c9d3e2;
  --btn:#2f4fe8; --btn-h:#243fc9; --glow:rgba(47,79,232,.16);
  --red:#d2203f; --red-soft:#fdeaee; --steel:#2f4fe8; --steel-soft:#e8edff; --sage:#1f8a5f; --sage-soft:#def4ea; --ochre:#b06a0b; --ochre-soft:#fdf0d9;
  --display:"Bricolage Grotesque","Hanken Grotesk",system-ui,sans-serif;
  --sans:"Hanken Grotesk",system-ui,sans-serif;
  --au-green:#0b1228; --au-green-2:#16224a; --au-gold:#8fa4ff; --au-gold-2:#b3c1ff; --au-red:#d2203f; --au-cream:#f3f4f8; --au-ink:#0d1a30;
  --serif:"Source Serif 4",Georgia,"Times New Roman",serif;
  font-family:var(--sans);font-size:15px;line-height:1.55;color:var(--ink);background:var(--paper);
  -webkit-font-smoothing:antialiased;color-scheme:light;
}
.apa h1,.apa h2{text-wrap:balance;margin:0}
.apa input,.apa select,.apa textarea,.apa button{font:inherit}

/* ---- form primitives ---- */
.apa .field{display:grid;gap:6px;min-width:0}
.apa .field label{font-size:13px;font-weight:650;color:var(--ink)}
.apa .field .hint{font-size:12.5px;color:var(--muted)}
.apa .ctl{width:100%;border:1px solid var(--rule-2);border-radius:10px;padding:10px 12px;background:var(--card);color:var(--ink);min-height:44px;transition:border-color .12s,box-shadow .12s}
.apa .ctl:hover{border-color:var(--muted)}
.apa .ctl:focus{outline:0;border-color:var(--steel);box-shadow:0 0 0 4px var(--glow)}
.apa .ctl.err{border-color:var(--red);background:var(--red-soft)}
.apa .err-msg{font-size:12.5px;color:var(--red);font-weight:500}
.apa .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;border-radius:10px;padding:10px 20px;font-weight:650;font-size:14px;border:1px solid transparent;cursor:pointer;min-height:44px;white-space:nowrap;transition:background .12s,color .12s,border-color .12s,box-shadow .12s}
.apa .btn:focus-visible{outline:0;box-shadow:0 0 0 4px var(--glow),0 0 0 1.5px var(--steel)}
.apa .btn[disabled]{opacity:.5;cursor:not-allowed;box-shadow:none}
.apa .btn-link{background:none;border:0;color:var(--steel);text-decoration:underline;text-underline-offset:3px;padding:0;min-height:0;font-weight:600;cursor:pointer}
.apa .notice{--n:var(--steel);--ns:var(--steel-soft);position:relative;background:var(--ns);border:1px solid color-mix(in srgb,var(--n) 25%,transparent);padding:14px 16px 14px 52px;border-radius:14px;font-size:14px;color:var(--ink)}
.apa .notice::before{content:"i";position:absolute;left:16px;top:14px;width:24px;height:24px;border-radius:50%;background:var(--n);color:var(--card);display:grid;place-items:center;font:700 13px/1 var(--display)}
.apa .notice b{color:var(--ink)}
.apa .notice.warn{--n:var(--ochre);--ns:var(--ochre-soft)} .apa .notice.warn::before{content:"!"}
.apa .notice.ok{--n:var(--sage);--ns:var(--sage-soft)} .apa .notice.ok::before{content:"✓"}
.apa .notice.bad{--n:var(--red);--ns:var(--red-soft)} .apa .notice.bad::before{content:"!"}

/* ---- stage ---- */
.apa .auth-grid{display:grid;grid-template-columns:minmax(0,1.12fr) minmax(0,1fr);min-height:100vh;position:relative;background:var(--au-cream);isolation:isolate}
.apa .auth-grid::before{content:"";position:absolute;inset:0 0 auto 0;height:3px;background:linear-gradient(90deg,var(--au-red),#4a67ff 45%,var(--au-green-2));z-index:3}
.apa .auth-side{position:relative;color:#fff;padding-block:52px;padding-inline:max(48px,env(safe-area-inset-left,0px)) 160px;display:grid;align-content:center;gap:34px;background:linear-gradient(165deg,#4a67ff,#8fa4ff 45%,var(--au-red));clip-path:polygon(0 0,100% 0,calc(100% - 200px) 100%,0 100%);min-height:100vh;z-index:2}
.apa .auth-side::before{content:"";position:absolute;inset:0;background:radial-gradient(900px 340px at 100% -20%,rgba(74,103,255,.55),transparent 60%),radial-gradient(500px 260px at 0% 120%,rgba(210,32,63,.35),transparent 60%),linear-gradient(135deg,#0b1228,#16224a);clip-path:polygon(0 0,calc(100% - 3px) 0,calc(100% - 203px) 100%,0 100%);z-index:0}
.apa .auth-side::after{content:"";position:absolute;inset:0;background-image:radial-gradient(rgba(255,255,255,.14) 1px,transparent 1px);background-size:22px 22px;clip-path:polygon(0 0,calc(100% - 3px) 0,calc(100% - 203px) 100%,0 100%);opacity:.5;pointer-events:none;z-index:0}
.apa .auth-side>*{position:relative;z-index:1}
.apa .auth-logo{display:flex;align-items:center;gap:14px;max-width:420px}
/* The lockup carries the wordmark, so it is sized to the width the crest plus
   type occupied in the design rather than to the crest alone. */
.apa .auth-logo img{width:min(300px,100%);height:auto;flex:none;filter:drop-shadow(0 6px 14px rgba(0,0,0,.35))}
.apa .auth-eyebrow{display:flex;align-items:center;gap:14px;font-size:11.5px;letter-spacing:.28em;text-transform:uppercase;font-weight:700;color:var(--au-gold)}
.apa .auth-eyebrow::before{content:"";width:48px;height:2px;background:var(--au-gold)}
.apa .auth-side h1{font-family:var(--serif);font-weight:500;font-size:clamp(34px,3.9vw,56px);line-height:1.08;letter-spacing:-.012em;color:#fff;max-width:17ch;text-wrap:balance}
.apa .auth-side h1 em{font-style:italic;color:var(--au-gold-2)}
.apa .auth-side .fine{color:rgba(255,255,255,.78);font-size:15.5px;line-height:1.6;max-width:44ch;margin:0}
.apa .auth-meta{display:flex;flex-wrap:wrap;gap:8px 22px;font-size:13px;color:rgba(255,255,255,.7)}
.apa .auth-meta span{display:inline-flex;align-items:center;gap:7px;position:relative}
.apa .auth-meta span+span::before{content:"";width:1px;height:14px;background:rgba(255,255,255,.25);margin-right:10px}
.apa .auth-meta svg{width:14px;height:14px;fill:none;stroke:var(--au-gold);stroke-width:1.9;stroke-linecap:round;stroke-linejoin:round}
.apa .auth-form{position:relative;z-index:1;padding-block:52px;padding-inline:max(32px,env(safe-area-inset-right,0px));display:grid;align-content:center;justify-items:center;background:var(--au-cream);background-image:radial-gradient(rgba(13,26,48,.12) 1px,transparent 1px);background-size:22px 22px;margin-left:-200px;padding-left:232px}
.apa .auth-card{width:min(430px,100%);background:#fff;border-radius:20px;box-shadow:0 30px 70px -30px rgba(21,32,30,.35),0 2px 6px rgba(21,32,30,.06);padding:40px 36px 30px;display:grid;gap:18px;color:var(--au-ink)}
.apa .auth-card .field label,.apa .auth-card h2,.apa .auth-card p,.apa .auth-card .chk{color:var(--au-ink)}
.apa .auth-card .ctl{background:#fff;color:var(--au-ink);border-color:#c9d3e2}
.apa .auth-card .ctl::placeholder{color:#a39f95}
.apa .auth-card .ctl:focus{border-color:#2f4fe8;box-shadow:0 0 0 4px rgba(47,79,232,.16)}
.apa .auth-card .hint{color:#6b6a63}
.apa .auth-head{display:grid;justify-items:center;text-align:center;gap:8px}
.apa .auth-badge{width:54px;height:54px;border-radius:14px;background:linear-gradient(135deg,#16224a,#0b1228);display:grid;place-items:center;box-shadow:0 10px 24px -10px rgba(11,18,40,.7);margin-bottom:6px}
.apa .auth-badge svg{width:24px;height:24px;fill:none;stroke:var(--au-gold-2);stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
.apa .auth-head h2{font-family:var(--serif);font-weight:600;font-size:28px;letter-spacing:-.01em;line-height:1.1}
.apa .auth-head p{font-size:14px;color:#6b6a63;max-width:34ch;margin:0}
.apa .auth-card .field{gap:7px}
.apa .auth-card .field label{font-size:13.5px;font-weight:650}
.apa .lbl-row{display:flex;justify-content:space-between;align-items:baseline;gap:10px}
.apa .lbl-row .btn-link{font-size:12.5px;font-weight:650;color:#2f4fe8;padding:0}
.apa .auth-card .btn-dark{width:100%;background:#2f4fe8;color:#fff;border-color:#2f4fe8;padding:13px 16px;font-size:15px;border-radius:12px;box-shadow:0 12px 24px -12px rgba(47,79,232,.7)}
.apa .auth-card .btn-dark:hover{background:#243fc9;border-color:#243fc9}
.apa .auth-card .btn-outline{width:100%;background:#fff;color:var(--au-ink);border:1px solid #c9d3e2;padding:12px 16px;font-size:15px;border-radius:12px}
.apa .auth-card .btn-outline:hover{border-color:#0d1a30;background:#f3f4f8}
.apa .auth-help{border-top:1px solid #e1e7f0;padding-top:16px;text-align:center;font-size:12.5px;color:#6b6a63;line-height:1.6}
.apa .auth-help b{display:block;color:#2f4fe8;font-weight:700;word-break:break-all}

/* ---- register: password strength + tip ---- */
.apa .pw-strength{display:grid;gap:7px;margin-top:4px}
.apa .pw-strength .row{display:flex;justify-content:space-between;align-items:center;font-size:12.5px;color:#6b7690}
.apa .pw-strength .row b{font-weight:650;color:var(--pw,#6b7690)}
.apa .pw-track{height:6px;border-radius:999px;background:#e9edf4;overflow:hidden}
.apa .pw-track i{display:block;height:100%;width:var(--w,0%);background:var(--pw,#c9d3e2);border-radius:inherit;transition:width .25s ease,background .25s ease}
.apa .pw-tip{font-size:13px;font-weight:650;color:#2f4fe8;background:none;border:0;padding:0;cursor:pointer;text-align:left;justify-self:start}
.apa .pw-tip:hover{text-decoration:underline}
.apa .pw-rules{margin:0;padding:10px 12px;border-radius:10px;background:#f3f4f8;border:1px solid #e1e7f0;font-size:13px;color:#3b4a64;display:grid;gap:6px;list-style:none}
.apa .pw-rules li{display:flex;gap:9px;align-items:center}
.apa .pw-rules li svg{width:14px;height:14px;flex:none;fill:none;stroke:#c9d3e2;stroke-width:2.4;stroke-linecap:round;stroke-linejoin:round}
.apa .pw-rules li.ok{color:#1b8a5a}.apa .pw-rules li.ok svg{stroke:#1b8a5a}
.apa .auth-card .chk{display:inline-flex;align-items:center;gap:9px;font-size:13.5px;cursor:pointer}
.apa .auth-card .chk input{width:16px;height:16px;accent-color:#0d1a30;margin:0}
/* @tailwindcss/forms strips the native checkbox (appearance:none plus its own
   border and tick), which would leave accent-color with nothing to colour.
   Hand the control back to the browser so the box matches the design. */
.apa .auth-card .chk input[type="checkbox"]{-webkit-appearance:checkbox;appearance:checkbox;background-image:none;background-color:initial;border:0;padding:0;box-shadow:none;border-radius:0}
.apa .auth-card .notice{text-align:left}

@media (max-width:980px){
  .apa .auth-grid{grid-template-columns:1fr}
  .apa .auth-side{clip-path:none;min-height:0;padding:38px max(20px,env(safe-area-inset-right,0px)) 42px max(20px,env(safe-area-inset-left,0px));background:var(--au-green)}
  .apa .auth-side::before,.apa .auth-side::after{clip-path:none}
  .apa .auth-side::before{clip-path:polygon(0 0,100% 0,100% calc(100% - 3px),0 100%)}
  .apa .auth-form{margin-left:0;padding:28px 16px 40px}
  .apa .auth-card{padding:30px 22px 24px}
}

/* The prototype trims the controls once the form primitives are in place. Inside
   the card the three .auth-card rules above still win, which is why the fields
   there keep their 7px gap and 13.5px labels. */
.apa .ctl{min-height:36px;padding:6px 11px;font-size:14px;border-radius:9px}
.apa .field{gap:5px}
.apa .field label{font-size:12.5px}
.apa .btn{min-height:40px;padding:8px 18px}
</style>
@endonce

<div class="apa">
    <div class="auth-grid">
        <aside class="auth-side">
            <div class="auth-logo">
                {{-- The site logo is the full lockup (crest + wordmark), so the
                     shell does not set the college name beside it in type. --}}
                <img src="{{ $brandLogo }}" alt="London Churchill College">
            </div>
            <div style="display:grid;gap:22px">
                <span class="auth-eyebrow">{{ $eyebrow }}</span>
                <h1>{!! $headline !!}</h1>
                <p class="fine">{{ $subhead }}</p>
            </div>
            {{-- The three facts under the headline. A page passes its own
                 through the `meta` slot; the applicant screens use these. --}}
            <div class="auth-meta">
                @isset($meta)
                    {{ $meta }}
                @else
                    <span><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>Single sign-on</span>
                    <span><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>Available 24/7</span>
                    <span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 5 6v6c0 4.4 3 7.6 7 9 4-1.4 7-4.6 7-9V6l-7-3Z"/></svg>UK GDPR</span>
                @endisset
            </div>
        </aside>
        <section class="auth-form">
            <div class="auth-card">
                <div class="auth-head">
                    <span class="auth-badge">
                        {{ $badge ?? '' }}
                        @if(!isset($badge))
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M22 10 12 5 2 10l10 5 10-5Z"/><path d="M6 12v5c0 1.7 2.7 3 6 3s6-1.3 6-3v-5"/><path d="M22 10v6"/></svg>
                        @endif
                    </span>
                    <h2>{{ $title }}</h2>
                    @if($subtitle)<p>{{ $subtitle }}</p>@endif
                </div>

                {{-- `verifymessage` / `verifySuccessMessage` keep their existing toast on the
                     login page, so they are deliberately not repeated here. --}}
                @if($flashBad)<div class="notice bad">{{ $flashBad }}</div>@endif
                @if($flashOk)<div class="notice ok">{{ $flashOk }}</div>@endif
                {{ $flash ?? '' }}

                {{ $slot }}

                <div class="auth-help">
                    {{ $footerLead ?? 'Need help with your application? Contact' }}
                    <b><a href="mailto:{{ $helpEmail }}" style="color:inherit;text-decoration:none">{{ $helpEmail }}</a></b>
                </div>
            </div>
        </section>
    </div>
</div>
