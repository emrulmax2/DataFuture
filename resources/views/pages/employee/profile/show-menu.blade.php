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

    <li class="nav-item" role="presentation">
        <a href="{{ route('employee.documents', $employee->id) }}" class="nav-link py-4 {{ Route::currentRouteName() == 'employee.documents' ? 'active' : '' }}">
            Documents
        </a>
    </li>

    <li class="nav-item" role="presentation">
        <a href="{{ route('employee.notes', $employee->id) }}" class="nav-link py-4 {{ Route::currentRouteName() == 'employee.notes' ? 'active' : '' }}">
            Notes
        </a>
    </li>

    <li class="nav-item" role="presentation">
        <a href="{{ route('employee.appraisal', $employee->id) }}" class="nav-link py-4 {{ Route::currentRouteName() == 'employee.appraisal.documents' || Route::currentRouteName() == 'employee.appraisal' ? 'active' : '' }}">
            Appraisal
        </a>
    </li>

    <li class="nav-item" role="presentation">
        <a href="{{ route('employee.privilege', $employee->id) }}" class="nav-link py-4 {{ Route::currentRouteName() == 'employee.privilege' ? 'active' : '' }}">
            Privilege
        </a>
    </li>

    <li class="nav-item" role="presentation">
        <a href="{{ route('employee.time.keeper', $employee->id) }}" class="nav-link py-4 {{ Route::currentRouteName() == 'employee.time.keeper' ? 'active' : '' }}">
            Time Recored
        </a>
    </li>
    
</ul>