<ul class="nav nav-link-tabs flex-col sm:flex-row justify-center lg:justify-start text-center liveStudentMainMenu">
    <li class="nav-item" role="presentation">
        <a href="{{ route('profile.employee.view', $employee->id) }}" class="nav-link py-4 {{ Route::currentRouteName() == 'profile.employee.view' ? 'active' : '' }}">
            Profile
        </a>
    </li>
    
    <li class="nav-item" role="presentation">
        <a href="{{ route('employee.payment.settings', $employee->id) }}" class="nav-link py-4 {{ Route::currentRouteName() == 'employee.payment.settings' ? 'active' : '' }}">
            Payment Settings
        </a>
    </li>
    
    <li class="nav-item" role="presentation">
        <a href="{{ route('employee.holiday', $employee->id) }}" class="nav-link py-4 {{ Route::currentRouteName() == 'employee.holiday' ? 'active' : '' }}">
            Holidays
        </a>
    </li>
    
</ul>