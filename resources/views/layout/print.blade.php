<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @yield('subhead')
    {{-- Include compiled Tailwind CSS for print styling --}}
    @vite(['resources/css/app.css'])
    <style>
        /* Basic print-friendly defaults */
        html,body{height:100%;background:#fff;color:#000;font-family: Arial, Helvetica, sans-serif;margin:0;padding:12px}
        .no-print{display:inline-block}
        @media print{ .no-print{display:none!important} }
    </style>
    @stack('head')
</head>
<body>
    <main>
        @yield('subcontent')
    </main>

    @yield('script')
    @stack('scripts')
</body>
</html>
