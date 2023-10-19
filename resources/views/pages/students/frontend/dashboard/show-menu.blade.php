<ul 
    class="nav nav-link-tabs flex-col sm:flex-row justify-center lg:justify-start text-center liveStudentMainMenu" 
    style="padding-bottom: {{ Route::currentRouteName() == 'students.dashboard' ? '55' : '0' }}px;" 
    >
    <li class="nav-item hasChildren" role="presentation">
        <a href="javascript:void(0);" class="nav-link py-4 {{ Route::currentRouteName() == 'students.dashboard' ? 'active' : '' }}">
            Course <i data-lucide="chevron-down" class="inline-flex ml-1 w-4 h-4"></i>
        </a>
        <ul class="nav nav-link-tabs flex-col sm:flex-row justify-center lg:justify-start text-center liveStudentSubMenu {{ Route::currentRouteName() == 'students.dashboard' ? 'show' : '' }}">
            <li class="nav-item" role="presentation">
                <a href="{{ route('students.dashboard') }}" class="nav-link py-4 {{ Route::currentRouteName() == 'students.dashboard' ? 'active' : '' }}">
                    Course Details
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a href="javascript:void(0);" class="nav-link py-4">
                    Attendance
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a href="javascript:void(0);" class="nav-link py-4">
                    Result
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a href="javascript:void(0);" class="nav-link py-4">
                    SLC History
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a href="javascript:void(0);" class="nav-link py-4">
                    Work Placement
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a href="javascript:void(0);" class="nav-link py-4">
                    Accounts
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a href="javascript:void(0);" class="nav-link py-4">
                    Student Performance
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a href="javascript:void(0);" class="nav-link py-4">
                    Other Course Relations (1)
                </a>
            </li>
        </ul>
    </li>
    <li class="nav-item" role="presentation">
        <a href="{{ route('students.dashboard.profile') }}" class="nav-link py-4 {{ Route::currentRouteName() == 'students.dashboard.profile' ? 'active' : '' }}">
            Profile
        </a>
    </li>
</ul>