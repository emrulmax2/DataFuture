<ul 
    class="nav nav-link-tabs flex-col sm:flex-row justify-center lg:justify-start text-center liveStudentMainMenu" 
    style="padding-bottom: {{ Route::currentRouteName() == 'student.course' ? '55' : '0' }}px;" 
    >
    <li class="nav-item" role="presentation">
        <a href="{{ route('student.show', $employee->id) }}" class="nav-link py-4 {{ Route::currentRouteName() == 'student.show' ? 'active' : '' }}">
            Profile
        </a>
    </li>
    
    <li class="nav-item" role="presentation">
        <a href="{{ route('student.notes', $employee->id) }}" class="nav-link py-4 {{ Route::currentRouteName() == 'student.notes' ? 'active' : '' }}">
            Documents
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a href="{{ route('student.notes', $employee->id) }}" class="nav-link py-4 {{ Route::currentRouteName() == 'student.notes' ? 'active' : '' }}">
            Notes
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a href="{{ route('student.communication', $employee->id) }}" class="nav-link py-4 {{ Route::currentRouteName() == 'student.communication' ? 'active' : '' }}">
            Priviliges
        </a>
    </li>
    
</ul>