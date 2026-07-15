<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ $dark_mode ? 'dark' : '' }}{{ $color_scheme != 'default' ? ' ' . $color_scheme : '' }}">
<!-- BEGIN: Head -->
<head>
    <meta charset="utf-8">
    @php
        $opt = App\Models\Option::where('category', 'SITE_SETTINGS')->where('name','site_favicon')->pluck('value', 'name')->toArray();
    @endphp
    <link href="{{ (isset($opt['site_favicon']) && !empty($opt['site_favicon']) && Storage::disk('local')->exists('public/'.$opt['site_favicon']) ? url('storage/'.$opt['site_favicon']) : asset('build/assets/images/favicon.png')) }}" rel="shortcut icon">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- BEGIN: Fonts — Newsreader (display headings) + Public Sans (UI) per HR design handoff -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=Newsreader:opsz,wght@6..72,400;6..72,500;6..72,600;6..72,700&family=Public+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- END: Fonts -->

    @yield('head')

    <!-- BEGIN: CSS Assets-->
    @vite('resources/css/app.css')
    <!-- END: CSS Assets-->
    <script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAP_API', 'YOUR_API_KEY') }}&libraries=places" async></script>
    <script src="https://cdn.getaddress.io/scripts/getaddress-autocomplete-3.1.6.js" async></script>
    @routes
</head>
<!-- END: Head -->

@yield('body')

</html>
