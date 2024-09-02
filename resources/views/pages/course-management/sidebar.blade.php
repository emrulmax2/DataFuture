<div class="intro-y box mt-5">
    <div class="relative flex items-center p-5">
        <div class="w-12 h-12 rounded-full inline-flex justify-center items-center bg-slate-100">
            <i data-lucide="book-open" class="w-6 h-6 text-primary"></i>
        </div>
        <div class="ml-4 mr-auto">
            <div class="font-medium text-base">Course Management</div>
            <div class="text-slate-500">{{ $subtitle }}</div>
        </div>
    </div>
    <div class="p-5 border-t border-slate-200/60 dark:border-darkmode-400 settingsMenu">
        @if(
            (!isset(auth()->user()->priv()['course_and_semesters']) || auth()->user()->priv()['course_and_semesters'] != 1) && 
            (!isset(auth()->user()->priv()['terms_and_modules']) || auth()->user()->priv()['terms_and_modules'] != 1) && 
            (!isset(auth()->user()->priv()['plans']) || auth()->user()->priv()['plans'] != 1)
           )
            <div class="alert alert-danger-soft show flex items-start mb-2" role="alert">
                <i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> <span><strong>Oops!</strong> &nbsp;Does not have permission to access this menu.</span>
            </div>
        @else
        <ul class="m-0 p-0">
            @if(isset(auth()->user()->priv()['course_and_semesters']) && auth()->user()->priv()['course_and_semesters'] == 1)
            <li class="hasChild">
                <a class="flex items-center {{ Route::currentRouteName() == 'course.creation.show' || Route::currentRouteName() == 'course.creation' || Route::currentRouteName() == 'course.module.show' || Route::currentRouteName() == 'courses.show' || Route::currentRouteName() == 'courses' || Route::is('term-declaration.index') || Route::currentRouteName() == 'semester' ? 'active text-primary font-medium' : '' }}" href="javascript:void(0);">
                    <i data-lucide="book-copy" class="w-4 h-4 mr-2"></i> Courses & Semesters  <i data-lucide="chevron-down" class="w-4 h-4 ml-auto menuAgnle"></i>
                </a>
                <ul class="p-0 m-0 pl-5" style="display: {{ Route::currentRouteName() == 'course.creation.show' || Route::currentRouteName() == 'course.creation' || Route::currentRouteName() == 'course.module.show' || Route::currentRouteName() == 'courses.show' || Route::currentRouteName() == 'courses' || Route::is('term-declaration.index') || Route::currentRouteName() == 'semester' ? 'block' : 'none' }};">
                    
                
                
                    <li>
                        <a class="flex items-center mt-4 {{ Route::currentRouteName() == 'semester' ? 'active text-primary' : '' }}" href="{{ route('semester') }}">
                            <i data-lucide="check-circle" class="w-3 h-3 mr-2"></i> Semesters
                        </a>
                    </li>
                    <li>
                        <a class="flex items-center mt-4 {{ Route::is('term-declaration.index') ? 'active text-primary' : '' }}" href="{{ route('term-declaration.index') }}">
                            <i data-lucide="check-circle" class="w-3 h-3 mr-2"></i> Term Declarations
                        </a>
                    </li>
                    <li>
                        <a class="flex items-center mt-4 {{ Route::currentRouteName() == 'course.module.show' || Route::currentRouteName() == 'courses.show' || Route::currentRouteName() == 'courses' ? 'active text-primary' : '' }}" href="{{ route('courses') }}">
                            <i data-lucide="check-circle" class="w-3 h-3 mr-2"></i> Courses
                        </a>
                    </li>
                    <li>
                        <a class="flex items-center mt-4 {{ Route::currentRouteName() == 'course.creation.show' || Route::currentRouteName() == 'course.creation' ? 'active text-primary' : '' }}" href="{{ route('course.creation') }}">
                            <i data-lucide="check-circle" class="w-3 h-3 mr-2"></i> Course Creations
                        </a>
                    </li>
                </ul>
            </li>
            @endif
            @if(isset(auth()->user()->priv()['terms_and_modules']) && auth()->user()->priv()['terms_and_modules'] == 1)
            <li class="hasChild">
                <a class="flex items-center mt-5 {{ Route::currentRouteName() == 'modulelevels' || Route::currentRouteName() == 'term.module.creation.module.details' || Route::currentRouteName() == 'term.module.creation.show' || Route::currentRouteName() == 'term.module.creation.add' || Route::currentRouteName() == 'term.module.creation' ? 'active text-primary font-medium' : '' }}" href="javascript:void(0);">
                    <i data-lucide="calendar-range" class="w-4 h-4 mr-2"></i> Terms & Modules  <i data-lucide="chevron-down" class="w-4 h-4 ml-auto menuAgnle"></i>
                </a>
                <ul class="p-0 m-0 pl-5" style="display: {{ Route::currentRouteName() == 'modulelevels' || Route::currentRouteName() == 'term.module.creation.module.details' || Route::currentRouteName() == 'term.module.creation.show' || Route::currentRouteName() == 'term.module.creation.add' || Route::currentRouteName() == 'term.module.creation' ? 'block' : 'none' }};">
                    <li>
                        <a class="flex items-center mt-4 {{ Route::currentRouteName() == 'term.module.creation.module.details' || Route::currentRouteName() == 'term.module.creation.show' || Route::currentRouteName() == 'term.module.creation.add' || Route::currentRouteName() == 'term.module.creation' ? 'active text-primary' : '' }}" href="{{ route('term.module.creation') }}">
                            <i data-lucide="check-circle" class="w-3 h-3 mr-2"></i> Term Module Creations
                        </a>
                    </li>
                    <li>
                        <a class="flex items-center mt-4 {{ Route::currentRouteName() == 'modulelevels' ? 'active text-primary' : '' }}" href="{{ route('modulelevels') }}">
                            <i data-lucide="check-circle" class="w-3 h-3 mr-2"></i> Module Levels
                        </a>
                    </li>
                </ul>
            </li>
            @endif
            @if(isset(auth()->user()->priv()['plans']) && auth()->user()->priv()['plans'] == 1)
            <li class="hasChild">
                <a class="flex items-center mt-5 {{ Route::currentRouteName() == 'groups' || Route::currentRouteName() == 'assign' || Route::currentRouteName() == 'plans.tree' || Route::currentRouteName() == 'class.plan.builder' || Route::currentRouteName() == 'class.plan.add' || Route::currentRouteName() == 'plan.dates' || Route::currentRouteName() == 'class.plan' ? 'active text-primary font-medium' : '' }}" href="javascript:void(0);">
                    <i data-lucide="calendar-days" class="w-4 h-4 mr-2"></i> Plans <i data-lucide="chevron-down" class="w-4 h-4 ml-auto menuAgnle"></i>
                </a>
                <ul class="p-0 m-0 pl-5" style="display: {{ Route::currentRouteName() == 'groups' || Route::currentRouteName() == 'assign' || Route::currentRouteName() == 'plans.tree' || Route::currentRouteName() == 'class.plan.builder' || Route::currentRouteName() == 'class.plan.add' || Route::currentRouteName() == 'plan.dates' || Route::currentRouteName() == 'class.plan' ? 'block' : 'none' }};">
                    <li>
                        <a class="flex items-center mt-4 {{ Route::currentRouteName() == 'class.plan.builder' || Route::currentRouteName() == 'class.plan.add' || Route::currentRouteName() == 'plan.dates' || Route::currentRouteName() == 'class.plan' ? 'active text-primary' : '' }}" href="{{ route('class.plan') }}">
                            <i data-lucide="check-circle" class="w-3 h-3 mr-2"></i> Plans
                        </a>
                    </li>
                    <li>
                        <a class="flex items-center mt-4 {{ Route::currentRouteName() == 'plans.tree' ? 'active text-primary' : '' }}" href="{{ route('plans.tree') }}">
                            <i data-lucide="check-circle" class="w-3 h-3 mr-2"></i> Plan Tree View
                        </a>
                    </li>
                    <li>
                        <a class="flex items-center mt-4 {{ Route::currentRouteName() == 'groups' ? 'active text-primary' : '' }} " href="{{ route('groups') }}">
                            <i data-lucide="check-circle" class="w-3 h-3 mr-2"></i> Groups
                        </a>
                    </li>
                    {{--<li>
                        <a class="flex items-center mt-4 {{ Route::currentRouteName() == 'assign' ? 'active text-primary' : '' }}" href="{{ route('assign') }}">
                            <i data-lucide="check-circle" class="w-3 h-3 mr-2"></i> Student Assign / Deassign
                        </a>
                    </li>--}}
                </ul>
            </li>
            @endif
        </ul>
        @endif
    </div>
</div>