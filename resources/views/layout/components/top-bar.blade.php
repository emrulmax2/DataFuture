@php
$opt = App\Models\Option::where('category', 'SITE_SETTINGS')->where('name','site_logo')->pluck('value', 'name')->toArray()
@endphp

@if(Auth::guard('applicant')->check())
  
@elseif(Auth::guard('student')->check())

@elseif(Auth::guard('agent')->check())

@else
    @php $employeeUser = cache()->get('employeeCache'.Auth::id()) ?? Auth::user()->load('employee'); @endphp
@endif

<!-- BEGIN: Top Bar -->
<div class="top-bar-boxed {{ isset($class) ? $class : '' }} h-[70px] md:h-[65px] z-[51] border-b border-white/[0.08] mt-12 md:mt-0 -mx-3 sm:-mx-8 md:-mx-0 px-3 md:border-b-0 relative md:fixed md:inset-x-0 md:top-0 sm:px-8 md:px-10 md:pt-10 md:bg-gradient-to-b md:from-slate-100 md:to-transparent dark:md:from-darkmode-700">
    <div class="h-full flex items-center">
        <!-- BEGIN: Logo -->

        @if(Auth::guard('applicant')->check())
            <a href="{{ route('applicant.login') }}" class="logo -intro-x hidden md:flex xl:w-[180px] block max-[639px]:hidden">
                <img alt="London Churchill College" class="logo__image w-auto h-12" src="{{ (isset($opt['site_logo']) && !empty($opt['site_logo']) && Storage::disk('local')->exists('public/'.$opt['site_logo']) ? Storage::disk('local')->url('public/'.$opt['site_logo']) : asset('build/assets/images/placeholders/200x200.jpg')) }}">
                {{-- <span class="logo__text text-white text-lg ml-3">
                    Enigma
                </span> --}}
            </a>
        @elseif(Auth::guard('student')->check())
            <a href="{{ route('students.login') }}" class="logo -intro-x hidden md:flex xl:w-[180px] block max-[639px]:hidden">
                <img alt="London Churchill College" class="logo__image w-auto h-12" src="{{ (isset($opt['site_logo']) && !empty($opt['site_logo']) && Storage::disk('local')->exists('public/'.$opt['site_logo']) ? Storage::disk('local')->url('public/'.$opt['site_logo']) : asset('build/assets/images/placeholders/200x200.jpg')) }}">
                {{-- <span class="logo__text text-white text-lg ml-3">
                    Enigma
                </span> --}}
            </a>
        @elseif(Auth::guard('agent')->check())
            <a href="{{ route('agent.login') }}" class="logo -intro-x hidden md:flex xl:w-[180px] block max-[639px]:hidden">
                <img alt="London Churchill College" class="logo__image w-auto h-12" src="{{ (isset($opt['site_logo']) && !empty($opt['site_logo']) && Storage::disk('local')->exists('public/'.$opt['site_logo']) ? Storage::disk('local')->url('public/'.$opt['site_logo']) : asset('build/assets/images/placeholders/200x200.jpg')) }}">
                {{-- <span class="logo__text text-white text-lg ml-3">
                    Enigma
                </span> --}}
            </a>
        @else
            <a href="{{ url('/') }}" class="logo -intro-x hidden md:flex xl:w-[180px] block max-[639px]:hidden">
                <img alt="London Churchill College" class="logo__image w-auto h-12" src="{{ (isset($opt['site_logo']) && !empty($opt['site_logo']) && Storage::disk('local')->exists('public/'.$opt['site_logo']) ? Storage::disk('local')->url('public/'.$opt['site_logo']) : asset('build/assets/images/placeholders/200x200.jpg')) }}">
                {{-- <span class="logo__text text-white text-lg ml-3">
                    Enigma
                </span> --}}
            </a>
        @endif
        
        <!-- END: Logo -->
        <!-- BEGIN: Breadcrumb -->
        <nav aria-label="breadcrumb" class="-intro-x h-[45px] mr-auto">
            <ol class="breadcrumb breadcrumb-light flex-wrap max-[639px]:pr-5">
                @if(Auth::guard('applicant')->check())
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Applicant</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('applicant.dashboard')  }}">Dashboard</a></li>
                @elseif(Auth::guard('student')->check())
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Student</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('students.dashboard') }}">Dashboard</a></li>
                @elseif(Auth::guard('agent')->check())
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Agent</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('agent.dashboard') }}">Dashboard</a></li>
                @else
                    <li class="breadcrumb-item"><a href="javascript:void(0);">User</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">Dashboard</a></li>
                @endif
                
                @if(isset($breadcrumbs) && !empty($breadcrumbs))
                    @php $i = 1; @endphp
                    @foreach($breadcrumbs as $crumbs)
                        <li class="breadcrumb-item {{ $i == count($breadcrumbs) ? 'active' : '' }}" aria-current="{{ $i == count($breadcrumbs) ? 'page' : '' }}">
                            @if($i != count($breadcrumbs)) <a href="{{ $crumbs['href'] }}"> @endif
                                {{ $crumbs['label'] }}
                            @if($i != count($breadcrumbs)) </a> @endif
                        </li>
                        @php $i++; @endphp
                    @endforeach
                @endif
            </ol>
        </nav>
        <!-- END: Breadcrumb -->
        {{-- @if(Auth::check())
        <!-- BEGIN: Search -->
        <div class="intro-x relative mr-3 sm:mr-6">
            <div class="search hidden sm:block">
                <input type="text" class="search__input form-control border-transparent" placeholder="Search...">
                <i data-lucide="search" class="search__icon dark:text-slate-500"></i>
            </div>
            <a class="notification notification--light sm:hidden" href="">
                <i data-lucide="search" class="notification__icon dark:text-slate-500"></i>
            </a>
            <div class="search-result">
                <div class="search-result__content">
                    <div class="search-result__content__title">Pages</div>
                    <div class="mb-5">
                        <a href="" class="flex items-center">
                            <div class="w-8 h-8 bg-success/20 dark:bg-success/10 text-success flex items-center justify-center rounded-full">
                                <i class="w-4 h-4" data-lucide="inbox"></i>
                            </div>
                            <div class="ml-3">Mail Settings</div>
                        </a>
                        <a href="" class="flex items-center mt-2">
                            <div class="w-8 h-8 bg-pending/10 text-pending flex items-center justify-center rounded-full">
                                <i class="w-4 h-4" data-lucide="users"></i>
                            </div>
                            <div class="ml-3">Users & Permissions</div>
                        </a>
                        <a href="" class="flex items-center mt-2">
                            <div class="w-8 h-8 bg-primary/10 dark:bg-primary/20 text-primary/80 flex items-center justify-center rounded-full">
                                <i class="w-4 h-4" data-lucide="credit-card"></i>
                            </div>
                            <div class="ml-3">Transactions Report</div>
                        </a>
                    </div>
                    <div class="search-result__content__title">Users</div>
                    <div class="mb-5">
                        @foreach (array_slice($fakers, 0, 4) as $faker)
                            <a href="" class="flex items-center mt-2">
                                <div class="w-8 h-8 image-fit">
                                    <img alt="London Churchill College" class="rounded-full" src="{{ asset('build/assets/images/' . $faker['photos'][0]) }}">
                                </div>
                                <div class="ml-3">{{ $faker['users'][0]['name'] }}</div>
                                <div class="ml-auto w-48 truncate text-slate-500 text-xs text-right">{{ $faker['users'][0]['email'] }}</div>
                            </a>
                        @endforeach
                    </div>
                    <div class="search-result__content__title">Products</div>
                    @foreach (array_slice($fakers, 0, 4) as $faker)
                        <a href="" class="flex items-center mt-2">
                            <div class="w-8 h-8 image-fit">
                                <img alt="London Churchill College" class="rounded-full" src="{{ asset('build/assets/images/' . $faker['images'][0]) }}">
                            </div>
                            <div class="ml-3">{{ $faker['products'][0]['name'] }}</div>
                            <div class="ml-auto w-48 truncate text-slate-500 text-xs text-right">{{ $faker['products'][0]['category'] }}</div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
        <!-- END: Search -->
        <!-- BEGIN: Notifications -->
        <div class="intro-x dropdown mr-4 sm:mr-6">
            <div class="dropdown-toggle notification notification--bullet cursor-pointer" role="button" aria-expanded="false" data-tw-toggle="dropdown">
                <i data-lucide="bell" class="notification__icon dark:text-slate-500"></i>
            </div>
            <div class="notification-content pt-2 dropdown-menu">
                <div class="notification-content__box dropdown-content">
                    <div class="notification-content__title">Notifications</div>
                    @foreach (array_slice($fakers, 0, 5) as $key => $faker)
                        <div class="cursor-pointer relative flex items-center {{ $key ? 'mt-5' : '' }}">
                            <div class="w-12 h-12 flex-none image-fit mr-1">
                                <img alt="London Churchill College" class="rounded-full" src="{{ asset('build/assets/images/' . $faker['photos'][0]) }}">
                                <div class="w-3 h-3 bg-success absolute right-0 bottom-0 rounded-full border-2 border-white"></div>
                            </div>
                            <div class="ml-2 overflow-hidden">
                                <div class="flex items-center">
                                    <a href="javascript:;" class="font-medium truncate mr-5">{{ $faker['users'][0]['name'] }}</a>
                                    <div class="text-xs text-slate-400 ml-auto whitespace-nowrap">{{ $faker['times'][0] }}</div>
                                </div>
                                <div class="w-full truncate text-slate-500 mt-0.5">{{ $faker['news'][0]['short_content'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <!-- END: Notifications -->
        @endif --}}
        <!-- BEGIN: Account Menu -->
        <div class="intro-x dropdown w-8 h-8">
            <div class="dropdown-toggle w-8 h-8 rounded-full overflow-hidden shadow-lg image-fit zoom-in scale-110" role="button" aria-expanded="false" data-tw-toggle="dropdown">
                @if(Auth::guard('applicant')->check())
                    <img src="{{ asset('build/assets/images/avater.png') }}">
                @elseif(Auth::guard('student')->check())
                    <img src="{{ asset('build/assets/images/avater.png') }}">
                @elseif(Auth::guard('agent')->check())
                    <img src="{{ asset('build/assets/images/avater.png') }}">
                @else
                    <img alt="{{ $employeeUser->employee->title->name.' '.$employeeUser->employee->first_name.' '.$employeeUser->employee->last_name }}"  src="{{ (isset($employeeUser->employee->photo) && !empty($employeeUser->employee->photo) && Storage::disk('local')->exists('public/employees/'.$employeeUser->employee->id.'/'.$employeeUser->employee->photo) ? Storage::disk('local')->url('public/employees/'.$employeeUser->employee->id.'/'.$employeeUser->employee->photo) : asset('build/assets/images/avater.png')) }}">
                @endif
                
            </div>
            <div class="dropdown-menu w-56">
                <ul class="dropdown-content bg-primary/80 before:block before:absolute before:bg-black before:inset-0 before:rounded-md before:z-[-1] text-white">
                    <li class="p-2">
                        @if(Auth::guard('agent')->check())
                            <div class="font-medium">{{ auth('agent')->user()->email }}</div>
                            <div class="text-xs text-white/60 mt-0.5 dark:text-slate-500">{{ auth('agent')->user()->email }}</div>
                        
                        @elseif(Auth::guard('applicant')->check())
                        <div class="font-medium">{{ auth('applicant')->user()->email }}</div>
                        <div class="text-xs text-white/60 mt-0.5 dark:text-slate-500">{{ auth('applicant')->user()->email }}</div>
                        @elseif(Auth::guard('student')->check())
                            <div class="font-medium">{{ auth('student')->user()->email }}</div>
                            <div class="text-xs text-white/60 mt-0.5 dark:text-slate-500">{{ auth('student')->user()->email }}</div>
                        @else
                            <div class="font-medium">{{ $employeeUser->employee->title->name.' '.$employeeUser->employee->first_name.' '.$employeeUser->employee->last_name }}</div>
                            <div class="text-xs text-white/60 mt-0.5 dark:text-slate-500">{{ auth()->user()->email }}</div>
                        @endif
                        
                    </li>
                    @if(Auth::guard('agent')->check())
                    <li><hr class="dropdown-divider border-white/[0.08]"></li>
                    <li>
                        <a href="javascript:void()" data-tw-toggle="modal" data-tw-target="#changePasswordModal" class="dropdown-item hover:bg-white/5">
                            <i data-lucide="user" class="w-4 h-4 mr-2"></i> Change Password
                        </a>
                    </li>

                    <!-- BEGIN: Edit Modal -->
                    <div id="changePasswordModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <form method="POST" action="#" id="AgentChangePasswordModalForm" enctype="multipart/form-data">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h2 class="font-medium text-base mr-auto">Change Password</h2>
                                        <a data-tw-dismiss="modal" href="javascript:;">
                                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                                        </a>
                                    </div>
                                    <div class="modal-body">
                                        <div class="grid grid-cols-12 gap-4">
                                            <div class="p-5 col-span-12">
                                                <div class="border-b pb-5">
                                                    <input id="old_password" type="password" name="old_password" class="intro-x login__input form-control py-3 px-4 block mt-4" placeholder="Old Password">
                                                    <div id="error-old_password" class="login__input-error text-danger mt-2"></div>
                                                </div>

                                                <input type="password" autocomplete="off" id="password" name="password" class="intro-x login__input form-control py-3 px-4 block mt-4" placeholder="New Password">
                                                <div id="error-password" class="login__input-error text-danger mt-2"></div>

                                                <div class="intro-x w-full grid grid-cols-12 gap-4 h-1 mt-3">
                                                    <div id="strength-1" class="col-span-3 h-full rounded bg-slate-100 dark:bg-darkmode-800"></div>
                                                    <div id="strength-2" class="col-span-3 h-full rounded bg-slate-100 dark:bg-darkmode-800"></div>
                                                    <div id="strength-3" class="col-span-3 h-full rounded bg-slate-100 dark:bg-darkmode-800"></div>
                                                    <div id="strength-4" class="col-span-3 h-full rounded bg-slate-100 dark:bg-darkmode-800"></div>
                                                </div>
                                                <!-- BEGIN: Custom Tooltip Toggle -->
                                                <a href="javascript:;" data-theme="light" data-tooltip-content="#custom-content-tooltip" data-trigger="click" class="tooltip intro-x text-slate-500 block mt-2 text-xs sm:text-sm" title="What is a secure password?">What is a secure password?</a>
                                                <!-- END: Custom Tooltip Toggle -->
                                                <!-- BEGIN: Custom Tooltip Content -->
                                                <div class="tooltip-content">
                                                    <div id="custom-content-tooltip" class="relative flex items-center py-1">
                                                        <ul class="list-disc mt-5 ml-4 text-md dark:text-slate-400">
                                                            <li class="">
                                                                <span class="low-upper-case">
                                                                    <i class="fas fa-circle" aria-hidden="true"></i>
                                                                    &nbsp;Lowercase &amp; Uppercase
                                                                </span>
                                                            </li>
                                                            <li class="">
                                                                <span class="one-number">
                                                                    <i class="fas fa-circle" aria-hidden="true"></i>
                                                                    &nbsp;Number (0-9)
                                                                </span> 
                                                            </li>
                                                            <li class="">
                                                                <span class="one-special-char">
                                                                    <i class="fas fa-circle" aria-hidden="true"></i>
                                                                    &nbsp;Special Character (!@#$%^&*)
                                                                </span>
                                                            </li>
                                                            <li class="">
                                                                <span class="eight-character">
                                                                    <i class="fas fa-circle" aria-hidden="true"></i>
                                                                    &nbsp;Atleast 8 Character
                                                                </span>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <!-- END: Custom Tooltip Content -->
                                                <input type="password" id="password_confirmation" name="password_confirmation" class="intro-x login__input form-control py-3 px-4 block mt-4" placeholder="Password Confirmation">
                                                <div id="error-confirmation" class="login__input-error text-danger mt-2"></div>
                                            </div>
                                        </div> 
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                                        <button type="button" id="btn-changepassword" class="btn btn-primary w-auto">
                                            Update Password
                                        </button>
                                        
                                        @if(Auth::guard('agent')->check())
                                            <input type="hidden" name="id" value="{{ auth('agent')->user()->id }}" />
                                        @elseif(Auth::guard('applicant')->check())
                                            <input type="hidden" name="id" value="{{ auth('applicant')->user()->id }}" />
                                        @elseif(Auth::guard('student')->check())
                                            <input type="hidden" name="id" value="{{ auth('student')->user()->id }}" />
                                        @else
                                            <input type="hidden" name="id" value="{{ auth()->user()->id }}" />
                                        @endif
                                        
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!-- END: Edit Modal -->
                    @elseif(Auth::guard('student')->check())
                    <li><hr class="dropdown-divider border-white/[0.08]"></li>
                    <li>
                        <a href="{{ route('students.dashboard.profile') }}" class="dropdown-item hover:bg-white/5">
                            <i data-lucide="user" class="w-4 h-4 mr-2"></i> Profile
                        </a>
                    </li>
                    @else
                        <li><hr class="dropdown-divider border-white/[0.08]"></li>
                        <li>
                            <a href="{{ route('user.account') }}" class="dropdown-item hover:bg-white/5">
                                <i data-lucide="user" class="w-4 h-4 mr-2"></i> Profile
                            </a>
                        </li>
                    @endif
                    {{--<li>
                        <a href="" class="dropdown-item hover:bg-white/5">
                            <i data-lucide="edit" class="w-4 h-4 mr-2"></i> Add Account
                        </a>
                    </li>
                    <li>
                        <a href="" class="dropdown-item hover:bg-white/5">
                            <i data-lucide="lock" class="w-4 h-4 mr-2"></i> Reset Password
                        </a>
                    </li>
                    <li>
                        <a href="" class="dropdown-item hover:bg-white/5">
                            <i data-lucide="help-circle" class="w-4 h-4 mr-2"></i> Help
                        </a>
                    </li>--}}
                    <li><hr class="dropdown-divider border-white/[0.08]"></li>
                    <li>
                        @if(Auth::guard('agent')->check())
                            <a href="{{ route('agent.logout') }}" class="dropdown-item hover:bg-white/5">
                                <i data-lucide="toggle-right" class="w-4 h-4 mr-2"></i> Logout
                            </a>
                        @elseif(Auth::guard('applicant')->check())
                            <a href="{{ route('applicant.logout') }}" class="dropdown-item hover:bg-white/5">
                                <i data-lucide="toggle-right" class="w-4 h-4 mr-2"></i> Logout
                            </a>
                        @elseif(Auth::guard('student')->check())
                            <a href="{{ route('students.logout') }}" class="dropdown-item hover:bg-white/5">
                                <i data-lucide="toggle-right" class="w-4 h-4 mr-2"></i> Logout
                            </a>
                        @else
                            <a href="{{ route('logout') }}" class="dropdown-item hover:bg-white/5">
                                <i data-lucide="toggle-right" class="w-4 h-4 mr-2"></i> Logout
                            </a>
                        @endif
                    </li>
                </ul>
            </div>
        </div>
        <!-- END: Account Menu -->
    </div>
</div>
<!-- END: Top Bar -->
