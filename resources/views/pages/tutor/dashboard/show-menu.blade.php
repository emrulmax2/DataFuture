<ul 
    class="nav nav-link-tabs flex-col sm:flex-row justify-center lg:justify-start text-center liveStudentMainMenu" 
    style="padding-bottom: {{ Route::currentRouteName() == 'student.course' ? '55' : '0' }}px;" 
    >
    <li class="nav-item" role="presentation">
        <a href="{{ route('profile.employee.view', $employee->id) }}" class="nav-link py-4 {{ Route::currentRouteName() == 'profile.employee.view' ? 'active' : '' }}">
            Dashboard
        </a>
    </li>
</ul>