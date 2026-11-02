{{--
    /slc-registration — SLC bulk registration confirmation (the user's "SLC Registration Check"
    prototype). A page of its own, without the theme's app.css, so the prototype's design is
    reproduced exactly; the Vue app (resources/js/slc-registration) renders everything from the
    embedded first payload. See App\Http\Controllers\SlcRegistration\SlcRegistrationController.
--}}
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }}</title>
    @php
        $slrFavicon = App\Models\Option::where('category', 'SITE_SETTINGS')->where('name', 'site_favicon')->value('value');
    @endphp
    <link href="{{ $slrFavicon && Storage::disk('local')->exists('public/'.$slrFavicon) ? url('storage/'.$slrFavicon) : asset('build/assets/images/favicon.png') }}" rel="shortcut icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=IBM+Plex+Sans:wght@400;500;600;700&display=swap">
    @vite('resources/css/slc-registration.css')
    @routes
</head>
<body>
    <div id="slc-registration-app"><div class="wrap" style="padding-block:40px">Loading SLC registration…</div></div>
    <script type="application/json" id="slr-boot">{!! json_encode(['page' => $page ?? 'main', 'user' => $user, 'logo' => $logo, 'state' => $state, 'multiple' => $multiple ?? null], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) !!}</script>
    @vite('resources/js/slc-registration/main.js')
</body>
</html>
