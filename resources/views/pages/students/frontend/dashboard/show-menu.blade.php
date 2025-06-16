<button id="studentProfileMenu" class="sm:hidden w-full flex items-center justify-end text-gray-700 py-4 px-5 sm:px-0">
    <div class="bg-primary text-white font-semibold py-3 px-4 border border-gray-400 rounded flex items-center gap-2">
        <span>Menu</span>
        <i data-lucide="bar-chart2" class="w-4 h-4 -rotate-90"></i>
    </div>
</button>
<ul 
    class="nav nav-link-tabs flex-col sm:flex-row justify-center lg:justify-start text-center liveStudentMainMenu hidden sm:flex">
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
        <a href="{{ route('students.results.frontend.index',$student->id) }}" class="nav-link py-4 {{ Route::currentRouteName() == 'students.results.frontend.index' ? 'active' : '' }}">
            Result
        </a>
    </li>

    
    <li class="nav-item" role="presentation">
        <a href="{{ route('students.performance.frontend.index',$student->id) }}" class="nav-link py-4 {{ Route::currentRouteName() == 'students.performance.frontend.index' ? 'active' : '' }}">
            Student Performance
        </a>
    </li>
    @if(isset($student->crel->creation->is_workplacement) && $student->crel->creation->is_workplacement == 1)
    <li class="nav-item" role="presentation">
        <a href="{{ route('students.dashboard.workplacement') }}" class="nav-link md:py-4 {{ Route::currentRouteName() == 'students.dashboard.workplacement' ? 'active' : '' }}">
            Work Placement
        </a>
    </li>
    @endif
</ul>
<script>
    document.getElementById('studentProfileMenu').addEventListener('click', function() {
        const navMenu = document.querySelector('.liveStudentMainMenu');
        navMenu.classList.toggle('hidden');
    });
</script>