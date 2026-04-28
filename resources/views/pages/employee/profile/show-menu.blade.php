<ul class="nav nav-link-tabs flex-col sm:flex-row justify-center lg:justify-start text-center liveStudentMainMenu">
    @if(isset(auth()->user()->priv()['hr_porta']) && auth()->user()->priv()['hr_porta'] == 1)
    <li class="nav-item" role="presentation">
        <a href="{{ route('profile.employee.view', $employee->id) }}" class="nav-link py-4 {{ Route::currentRouteName() == 'profile.employee.view' ? 'active' : '' }}">
            Profile
        </a>
    </li>
    @endif
    @if(isset(auth()->user()->priv()['hr_porta']) && auth()->user()->priv()['hr_porta'] == 1)
    <li class="nav-item" role="presentation">
        <a href="{{ route('employee.payment.settings', $employee->id) }}" class="nav-link py-4 {{ Route::currentRouteName() == 'employee.payment.settings' ? 'active' : '' }}">
            Payment Settings
        </a>
    </li>
    @endif
    @if((isset(auth()->user()->priv()['hr_porta']) && auth()->user()->priv()['hr_porta'] == 1) && (isset($employee->payment->holiday_entitled) && $employee->payment->holiday_entitled == 'Yes'))
    <li class="nav-item" role="presentation">
        <a href="{{ route('employee.holiday', $employee->id) }}" class="nav-link py-4 {{ Route::currentRouteName() == 'employee.holiday' ? 'active' : '' }}">
            Holidays
        </a>
    </li>
    @endif

    @if((isset(auth()->user()->priv()['hr_porta']) && auth()->user()->priv()['hr_porta'] == 1) && (isset($employee->payslipWithTransfered) && $employee->payslipWithTransfered->count() > 0))
    <li class="nav-item" role="presentation">
        <a href="{{ route('profile.employee.payslip.show', $employee->id) }}" class="nav-link py-4 {{ Route::currentRouteName() == 'profile.employee.payslip.show' ? 'active' : '' }}">
            Payslips
        </a>
    </li>
    @endif
    @if(isset(auth()->user()->priv()['hr_porta']) && auth()->user()->priv()['hr_porta'] == 1)
    <li class="nav-item" role="presentation">
        <a href="{{ route('employee.documents', $employee->id) }}" class="nav-link py-4 {{ Route::currentRouteName() == 'employee.documents' ? 'active' : '' }}">
            Documents
        </a>
    </li>

    @endif
    @if(isset(auth()->user()->priv()['hr_porta']) && auth()->user()->priv()['hr_porta'] == 1)
    <li class="nav-item" role="presentation">
        <a href="{{ route('employee.notes', $employee->id) }}" class="nav-link py-4 {{ Route::currentRouteName() == 'employee.notes' ? 'active' : '' }}">
            Notes
        </a>
    </li>
    @endif

    <li class="nav-item" role="presentation">
        <a href="{{ route('employee.appraisal', $employee->id) }}" class="nav-link py-4 {{ Route::currentRouteName() == 'employee.appraisal.documents' || Route::currentRouteName() == 'employee.appraisal' ? 'active' : '' }}">
            Appraisal & Training
        </a>
    </li>
    @if((isset(auth()->user()->priv()['hr_porta']) && auth()->user()->priv()['hr_porta'] == 1) && ((isset(auth()->user()->priv()['privilege_menu']) && auth()->user()->priv()['privilege_menu'] == 1) || in_array(auth()->user()->id, [1, 7])))
    <li class="nav-item" role="presentation">
        <a href="{{ route('employee.privilege', $employee->id) }}" class="nav-link py-4 {{ Route::currentRouteName() == 'employee.privilege' ? 'active' : '' }}">
            Privilege
        </a>
    </li>
    @endif

    {{--<li class="nav-item" role="presentation">
        <a href="{{ route('employee.privilege.new', $employee->id) }}" class="nav-link py-4 {{ Route::currentRouteName() == 'employee.privilege.new' ? 'active' : '' }}">
            Privilege New
        </a>
    </li>--}}
    @if(isset(auth()->user()->priv()['hr_porta']) && auth()->user()->priv()['hr_porta'] == 1)
    <li class="nav-item" role="presentation">
        <a href="{{ route('employee.time.keeper', $employee->id) }}" class="nav-link py-4 {{ Route::currentRouteName() == 'employee.time.keeper' ? 'active' : '' }}">
            Time Recored
        </a>
    </li>
    @endif

    @if(isset(auth()->user()->priv()['hr_porta']) && auth()->user()->priv()['hr_porta'] == 1)
    <li class="nav-item" role="presentation">
        <a href="{{ route('employee.archive', $employee->id) }}" class="nav-link py-4 {{ Route::currentRouteName() == 'employee.archive' ? 'active' : '' }}">
            Archive
        </a>
    </li>
    @endif
    
     <li class="nav-item" role="presentation">
        <a href="{{ route('profile.employee.login.logs', $employee->id) }}" class="nav-link py-4 {{ Route::currentRouteName() == 'profile.employee.login.logs' ? 'active' : '' }}">
            Login Logs
        </a>
    </li>

</ul>