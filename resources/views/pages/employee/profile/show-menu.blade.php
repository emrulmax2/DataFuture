@php
    $current  = Route::currentRouteName();
    $tabBase  = 'whitespace-nowrap px-4 py-3.5 text-[13.5px] border-b-[3px] transition-colors';
    $tabOn    = 'border-primary text-primary font-bold';
    $tabOff   = 'border-transparent text-slate-500 dark:text-slate-400 font-semibold hover:text-primary';
    $tab = fn($active) => $tabBase.' '.($active ? $tabOn : $tabOff);
    $hrPortal = isset(auth()->user()->priv()['hr_porta']) && auth()->user()->priv()['hr_porta'] == 1;
@endphp

<nav class="flex gap-1 overflow-x-auto liveStudentMainMenu">
    @if($hrPortal)
    <a href="{{ route('profile.employee.view', $employee->id) }}" class="{{ $tab($current == 'profile.employee.view') }}">
        Profile
    </a>
    @endif
    @if($hrPortal)
    <a href="{{ route('employee.payment.settings', $employee->id) }}" class="{{ $tab($current == 'employee.payment.settings') }}">
        Payment Settings
    </a>
    @endif
    @if($hrPortal && (isset($employee->payment->holiday_entitled) && $employee->payment->holiday_entitled == 'Yes'))
    <a href="{{ route('employee.holiday', $employee->id) }}" class="{{ $tab($current == 'employee.holiday') }}">
        Holidays
    </a>
    @endif

    @if($hrPortal && (isset($employee->payslipWithTransfered) && $employee->payslipWithTransfered->count() > 0))
    <a href="{{ route('profile.employee.payslip.show', $employee->id) }}" class="{{ $tab($current == 'profile.employee.payslip.show') }}">
        Payslips
    </a>
    @endif
    @if($hrPortal)
    <a href="{{ route('employee.documents', $employee->id) }}" class="{{ $tab($current == 'employee.documents') }}">
        Documents
    </a>
    @endif
    @if($hrPortal)
    <a href="{{ route('employee.notes', $employee->id) }}" class="{{ $tab($current == 'employee.notes') }}">
        Notes
    </a>
    @endif

    <a href="{{ route('employee.appraisal', $employee->id) }}" class="{{ $tab($current == 'employee.appraisal.documents' || $current == 'employee.appraisal') }}">
        Appraisal &amp; Training
    </a>
    @if($hrPortal && ((isset(auth()->user()->priv()['privilege_menu']) && auth()->user()->priv()['privilege_menu'] == 1) || in_array(auth()->user()->id, [1, 7])))
    <a href="{{ route('employee.privilege', $employee->id) }}" class="{{ $tab($current == 'employee.privilege') }}">
        Privilege
    </a>
    @endif

    @if($hrPortal)
    <a href="{{ route('employee.time.keeper', $employee->id) }}" class="{{ $tab($current == 'employee.time.keeper') }}">
        Time Recorded
    </a>
    @endif

    @if($hrPortal)
    <a href="{{ route('employee.archive', $employee->id) }}" class="{{ $tab($current == 'employee.archive') }}">
        Archive
    </a>
    @endif

    <a href="{{ route('profile.employee.login.logs', $employee->id) }}" class="{{ $tab($current == 'profile.employee.login.logs') }}">
        Logs
    </a>
</nav>
