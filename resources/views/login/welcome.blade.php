@extends('../layout/' . $layout)

@section('head')
    <title>Welcome - London Churchill College</title>
@endsection

@section('content')
    <x-login-shell
        :opt="$opt ?? []"
        state="welcome"
        :welcome-name="$name ?? null"
        :dashboard-url="$dashboardUrl ?? '/'"
        brand-eyebrow="Student Management System"
        brand-headline="Welcome to London Churchill College."
        brand-subhead="You're all set. Let's take you to your dashboard."
    >
        {{-- Welcome state does not use the sign-in slot. --}}
    </x-login-shell>
@endsection
