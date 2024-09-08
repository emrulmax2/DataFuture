<ul 
    class="nav nav-link-tabs flex-col sm:flex-row justify-center lg:justify-start text-center liveStudentMainMenu">
    <li class="nav-item" role="presentation">
        <a href="{{ route('students.dashboard') }}" class="nav-link py-4 {{ Route::currentRouteName() == 'students.dashboard' ? 'active' : '' }}">
            Dashboard
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a href="{{ route('students.dashboard.profile') }}" class="nav-link py-4 {{ Route::currentRouteName() == 'students.dashboard.profile' ? 'active' : '' }}">
            Profile
        </a>
    </li>
    
    <li class="nav-item" role="presentation">
        <a href="javascript::void()" class="nav-link py-4">
            Result
        </a>
    </li>
</ul>