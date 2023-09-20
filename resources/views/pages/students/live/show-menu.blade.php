<ul class="nav nav-link-tabs flex-col sm:flex-row justify-center lg:justify-start text-center liveStudentMainMenu">
    <li class="nav-item" role="presentation">
        <a href="{{ route('student.show', $student->id) }}" class="nav-link py-4 {{ Route::currentRouteName() == 'student.show' ? 'active' : '' }}">
            Profile
        </a>
    </li>
    <li class="nav-item hasChildren" role="presentation">
        <a href="javascript:void(0);" class="nav-link py-4">
            Course <i data-lucide="chevron-down" class="inline-flex ml-1 w-4 h-4"></i>
        </a>
        <ul class="nav nav-link-tabs flex-col sm:flex-row justify-center lg:justify-start text-center liveStudentSubMenu">
            <li class="nav-item" role="presentation">
                <a href="javascript:void(0);" class="nav-link py-4">
                    Course Details
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a href="javascript:void(0);" class="nav-link py-4">
                    Awarding Body
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
        <a href="#" class="nav-link py-4">
            Communications
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a href="#" class="nav-link py-4">
            Documents
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a href="#" class="nav-link py-4">
            Notes
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a href="javascript:void(0);" class="nav-link py-4">
            Task & Process
        </a>
    </li>
    
</ul>