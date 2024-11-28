<ul class="nav nav-link-tabs flex-col sm:flex-row justify-center lg:justify-start text-center liveStudentMainMenu">
    <li class="nav-item" role="presentation">
        <a href="{{ route('user.account') }}" class="nav-link py-4 {{ Route::currentRouteName() == 'user.account' ? 'active' : '' }}">
            Profile
        </a>
    </li>
    @if(isset($employee->payment->holiday_entitled) && $employee->payment->holiday_entitled == 'Yes')
        <li class="nav-item" role="presentation">
            <a href="{{ route('user.account.holiday') }}" class="nav-link py-4 {{ Route::currentRouteName() == 'user.account.holiday' ? 'active' : '' }}">
                Holidays
            </a>
        </li>
    @endif
    @if((isset($employee->user->hourauth) && $employee->user->hourauth->count() > 0) || (isset($employee->user->holiauth) && $employee->user->holiauth->count() > 0))
        <li class="nav-item" role="presentation">
            <a href="{{ route('user.account.staff') }}" class="nav-link py-4 {{ Route::currentRouteName() == 'user.account.staff.team.holiday' || Route::currentRouteName() == 'user.account.staff' ? 'active' : '' }}">
                My Staff
            </a>
        </li>
    @endif
    <li class="nav-item" role="presentation">
        <a href="{{ route('user.account.extrabenefit') }}" class="nav-link py-4 {{ Route::currentRouteName() == 'user.account.extrabenefit' ? 'active' : '' }}">
            Extra Benefits
        </a>
    </li>
    @if(isset(auth()->user()->priv()['staff_groups']) && auth()->user()->priv()['staff_groups'] == 1)
        <li class="nav-item" role="presentation">
            <a href="{{ route('user.account.group') }}" class="nav-link py-4 {{ Route::currentRouteName() == 'user.account.group' ? 'active' : '' }}">
                Groups
            </a>
        </li>
    @endif
    @if(isset($vacanties) && $vacanties > 0)
    <li class="nav-item" role="presentation">
        <a href="{{ route('user.account.vacancy') }}" class="nav-link py-4 {{ Route::currentRouteName() == 'user.account.vacancy' ? 'active' : '' }}">
            Vacancies
        </a>
    </li>
    @endif
</ul>