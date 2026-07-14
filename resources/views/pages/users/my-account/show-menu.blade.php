<nav class="my-account-tabs" aria-label="My HR sections">
    <a href="{{ route('user.account') }}" class="my-account-tabs__item {{ Route::currentRouteName() == 'user.account' ? 'active' : '' }}">
        Profile
    </a>
    @if(isset($employee->payment->holiday_entitled) && $employee->payment->holiday_entitled == 'Yes')
        <a href="{{ route('user.account.holiday') }}" class="my-account-tabs__item {{ Route::currentRouteName() == 'user.account.holiday' ? 'active' : '' }}">
            Holidays
        </a>
    @endif
    @if(isset($employee->payslipWithTransfered) && $employee->payslipWithTransfered->count() > 0)
        <a href="{{ route('user.account.payslip') }}" class="my-account-tabs__item {{ Route::currentRouteName() == 'user.account.payslip' ? 'active' : '' }}">
            Payslips
        </a>
    @endif
    @if((isset($employee->user->hourauth) && $employee->user->hourauth->count() > 0) || (isset($employee->user->holiauth) && $employee->user->holiauth->count() > 0))
        <a href="{{ route('user.account.staff') }}" class="my-account-tabs__item {{ Route::currentRouteName() == 'user.account.staff.team.holiday' || Route::currentRouteName() == 'user.account.staff' ? 'active' : '' }}">
            My Staff
        </a>
    @endif
    <a href="{{ route('user.account.extrabenefit') }}" class="my-account-tabs__item {{ Route::currentRouteName() == 'user.account.extrabenefit' ? 'active' : '' }}">
        Extra Benefits
    </a>
    @if(isset(auth()->user()->priv()['staff_groups']) && auth()->user()->priv()['staff_groups'] == 1)
        <a href="{{ route('user.account.group') }}" class="my-account-tabs__item {{ Route::currentRouteName() == 'user.account.group' ? 'active' : '' }}">
            Groups
        </a>
    @endif
    @if(isset($vacanties) && $vacanties > 0)
        <a href="{{ route('user.account.vacancy') }}" class="my-account-tabs__item {{ Route::currentRouteName() == 'user.account.vacancy' ? 'active' : '' }}">
            Vacancies
        </a>
    @endif
</nav>
