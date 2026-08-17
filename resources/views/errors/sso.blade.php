<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Single sign-on error</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f4f5f7;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            color: #1f2430;
        }
        .card {
            max-width: 26rem;
            padding: 2rem;
            background: #fff;
            border-radius: .5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .12);
        }
        h1 { margin: 0 0 .75rem; font-size: 1.125rem; }
        p  { margin: 0 0 1rem; line-height: 1.5; color: #4a5160; }
        a  { color: #2563eb; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Single sign-on could not continue</h1>
        <p>{{ $message }}</p>
        <p>If you reached this page from another London Churchill College system, please contact IT support so the connection can be checked.</p>
        <p><a href="{{ url('/') }}">Return to {{ config('app.name') }}</a></p>
    </div>
</body>
</html>
