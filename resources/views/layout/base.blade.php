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

    @yield('head')

    <!-- BEGIN: CSS Assets-->
    @vite('resources/css/app.css')
    <!-- END: CSS Assets-->
    <script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAP_API', 'YOUR_API_KEY') }}&libraries=places"></script>
    @routes
</head>
<!-- END: Head -->

@yield('body')

</html>
