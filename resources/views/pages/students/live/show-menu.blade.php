

<button id="menu-toggle" class="sm:hidden w-full flex items-center justify-end text-gray-700 mb-4 py-4">
    <div class="bg-primary text-white font-semibold py-3 px-4 border border-gray-400 rounded flex items-center gap-2">
        <span>Menu</span>
        <i data-lucide="bar-chart2" class="w-4 h-4 -rotate-90"></i>
    </div>
</button>

<ul 
    class="nav nav-link-tabs flex-col md:flex-row justify-center lg:justify-start md:text-center liveStudentMainMenu hidden md:flex" 
    style="padding-bottom: {{  Route::currentRouteName() == 'student-results.index' || Route::currentRouteName() == 'student.workplacement' || Route::currentRouteName() == 'student-performance.index' || Route::currentRouteName() == 'student.attendance.edit' || Route::currentRouteName() == 'student.attendance' || Route::currentRouteName() == 'student.accounts' || Route::currentRouteName() == 'student.slc.history' || Route::currentRouteName() == 'student.course' ? '55' : '0' }}px;" 
    >
    <li class="nav-item" role="presentation">
        <a href="{{ route('student.show', $student->id) }}" class="nav-link py-4 {{ Route::currentRouteName() == 'student.show' ? 'active' : '' }}">
            Profile
        </a>
    </li>
    <li class="nav-item hasChildren relative md:static" role="presentation">
        <a href="javascript:void(0);" class="nav-link py-4 {{ Route::currentRouteName() == 'student-results.index' || Route::currentRouteName() == 'student.workplacement' || Route::currentRouteName() == 'student-performance.index' || Route::currentRouteName() == 'student.attendance.edit' || Route::currentRouteName() == 'student.attendance' || Route::currentRouteName() == 'student.accounts' || Route::currentRouteName() == 'student.slc.history' || Route::currentRouteName() == 'student.course' ? 'active' : '' }} {{ (Session::has('student_temp_course_relation_'.$student->id) && Session::get('student_temp_course_relation_'.$student->id) > 0 ? 'temp-course' : '' ) }}">
            Course <i data-lucide="chevron-down" class="inline-flex ml-1 w-4 h-4"></i>
        </a>
        <ul class="absolute top-4 md:top-auto left-24 md:left-0 w-52 md:w-auto nav nav-link-tabs flex-col sm:flex-row justify-center lg:justify-start text-center liveStudentSubMenu  bg-gray-100 md:bg-white {{ Route::currentRouteName() == 'student-results.index' || Route::currentRouteName() == 'student-performance.index' || Route::currentRouteName() == 'student.attendance.edit' || Route::currentRouteName() == 'student.workplacement' || Route::currentRouteName() == 'student.attendance' || Route::currentRouteName() == 'student.accounts' || Route::currentRouteName() == 'student.slc.history' || Route::currentRouteName() == 'student.course' ? 'show' : '' }}">
            <li class="nav-item" role="presentation">
                <a href="{{ route('student.course', $student->id) }}" class="nav-link md:py-4 {{ Route::currentRouteName() == 'student.course' ? 'active' : '' }}">
                    Course Details
                </a>
            </li>
            @if(isset(auth()->user()->priv()['attendance_view']) && auth()->user()->priv()['attendance_view'] == 1)
            <li class="nav-item" role="presentation">
                <a href="{{ route('student.attendance', $student->id) }}" class="nav-link md:py-4 {{ (Route::currentRouteName() == 'student.attendance.edit' || Route::currentRouteName() == 'student.attendance') ? 'active' : '' }}" class="nav-link md:py-4">
                    Attendance
                </a>
            </li>
            @endif
            @if(isset(auth()->user()->priv()['result_view']) && auth()->user()->priv()['result_view'] == 1)
            <li class="nav-item" role="presentation">
                <a href="{{ route('student-results.index', $student->id) }}" class="nav-link md:py-4 {{ (Route::currentRouteName() == 'student-results.index') ? 'active' : '' }}">
                    Result
                </a>
            </li>
            @endif
            @if(isset(auth()->user()->priv()['slc_history_view']) && auth()->user()->priv()['slc_history_view'] == 1)
            
            <li class="nav-item" role="presentation">
                <a href="{{ route('student.slc.history', $student->id) }}" class="nav-link md:py-4 {{ Route::currentRouteName() == 'student.slc.history' ? 'active' : '' }}">
                    SLC History
                </a>
            </li>
            @endif
            @if(isset($student->crel->creation->is_workplacement) && $student->crel->creation->is_workplacement == 1)
            <li class="nav-item" role="presentation">
                <a href="{{ route('student.workplacement', $student->id) }}" class="nav-link md:py-4 {{ Route::currentRouteName() == 'student.workplacement' ? 'active' : '' }}">
                    Work Placement
                </a>
            </li>
            @endif
            @if(isset(auth()->user()->priv()['student_account_view']) && auth()->user()->priv()['student_account_view'] == 1)
            <li class="nav-item" role="presentation">
                <a href="{{ route('student.accounts', $student->id) }}" class="nav-link md:py-4 {{ Route::currentRouteName() == 'student.accounts' ? 'active' : '' }}">
                    Accounts
                </a>
            </li>
            @endif
            @if(isset(auth()->user()->priv()['student_performance_view']) && auth()->user()->priv()['student_performance_view'] == 1)
            <li class="nav-item" role="presentation">
                <a href="{{ route('student-performance.index', $student->id) }}" class="nav-link md:py-4 {{ Route::currentRouteName() == 'student-performance.index' ? 'active' : '' }}">
                    Student Performance
                </a>
            </li>
            @endif
            @if(isset(auth()->user()->priv()['student_course_change_view']) && auth()->user()->priv()['student_course_change_view'] == 1)
            <li class="nav-item hasDropdown" role="presentation">
                <a href="javascript:void(0);" class="nav-link md:py-4 {{ (Session::has('student_temp_course_relation_'.$student->id) && Session::get('student_temp_course_relation_'.$student->id) > 0 ? 'temp-course font-medium' : '' ) }}">
                    Other Course Relations ({{ (isset($student->otherCrels) ? $student->otherCrels->count() : 0)}})
                </a>
                @if(isset($student->otherCrels) && $student->otherCrels->count() > 0)
                    <ul class="theSubMenu">
                        @foreach($student->otherCrels as $ocrl)
                            <li class="{{ (Session::has('student_temp_course_relation_'.$student->id) && Session::get('student_temp_course_relation_'.$student->id) == $ocrl->id ? 'active-temp-course' : '' ) }}">
                                <a href="{{ route('student.set.temp.course', [$student->id, $ocrl->id]) }}">
                                    @if(isset($ocrl->creation->semester->name))
                                        <span>{{ $ocrl->creation->semester->name}}</span>
                                    @endif
                                    @if(isset($ocrl->creation->course->name))
                                        <span>{{ $ocrl->creation->course->name}}</span>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </li>
            @endif

        </ul>
    </li>
    <li class="nav-item" role="presentation">
        <a href="{{ route('student.communication', $student->id) }}" class="nav-link py-4 {{ Route::currentRouteName() == 'student.communication' ? 'active' : '' }}">
            Communications
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a href="{{ route('student.uploads', $student->id) }}" class="nav-link py-4 {{ Route::currentRouteName() == 'student.uploads' ? 'active' : '' }}">
            Documents
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a href="{{ route('student.notes', $student->id) }}" class="nav-link py-4 {{ Route::currentRouteName() == 'student.notes' ? 'active' : '' }}">
            Notes
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a href="{{ route('student.process', $student->id) }}" class="nav-link py-4 {{ Route::currentRouteName() == 'student.process' ? 'active' : '' }}">
            Task & Process
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a href="{{ route('student.datafuture', $student->id) }}" class="nav-link py-4 {{ Route::currentRouteName() == 'student.datafuture' ? 'active' : '' }}">
            Datafuture
        </a>
    </li>
    
</ul>