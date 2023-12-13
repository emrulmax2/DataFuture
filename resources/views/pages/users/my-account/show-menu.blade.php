<ul class="nav nav-link-tabs flex-col sm:flex-row justify-center lg:justify-start text-center liveStudentMainMenu">
    <li class="nav-item" role="presentation">
        <a href="{{ route('user.account') }}" class="nav-link py-4 {{ Route::currentRouteName() == 'user.account' ? 'active' : '' }}">
            Profile
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a href="{{ route('user.account.holiday') }}" class="nav-link py-4 {{ Route::currentRouteName() == 'user.account.holiday' ? 'active' : '' }}">
            Holidays
        </a>
    </li>
    
</ul>