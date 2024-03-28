<ul class="nav nav-link-tabs flex-col sm:flex-row justify-center lg:justify-start text-center liveStudentMainMenu">
    <li class="nav-item" role="presentation">
        <a href="{{ route('agent-user.show', $employee->id) }}" class="nav-link py-4 {{ Route::currentRouteName() == 'agent-user.show' ? 'active' : '' }}">
            Terms
        </a>
    </li>
    
    <li class="nav-item" role="presentation">
        <a href="" class="nav-link py-4 ">
            Applicants
        </a>
    </li>

    <li class="nav-item" role="presentation">
        <a href="" class="nav-link py-4 ">
            Students
        </a>
    </li>

   
    
</ul>