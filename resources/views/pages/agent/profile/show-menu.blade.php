<ul class="nav nav-link-tabs flex-col sm:flex-row justify-center lg:justify-start text-center liveStudentMainMenu">
    <li class="nav-item" role="presentation">
        <a href="{{ route('agent-user.show', $employee->id) }}" class="nav-link py-4 {{ Route::currentRouteName() == 'agent-user.show' ? 'active' : '' }}">
            Applicants/Students Details
        </a>
    </li>
    
    <li class="nav-item" role="presentation">
        <a href="{{ route('sub-agent.show', $employee->id) }}" class="nav-link py-4 {{ Route::currentRouteName() == 'sub-agent.show' ? 'active' : '' }}">
            Sub Agents
        </a>
    </li>
    
    <li class="nav-item" role="presentation">
        <a href="{{ route('agent-user.documents', $employee->id) }}" class="nav-link py-4 {{ Route::currentRouteName() == 'agent-user.documents' ? 'active' : '' }}">
            Documents
        </a>
    </li>
    
    <li class="nav-item" role="presentation">
        <a href="{{ route('agent-user.payment.settings', $employee->id) }}" class="nav-link py-4 {{ Route::currentRouteName() == 'agent-user.payment.settings' ? 'active' : '' }}">
            Payment Settings
        </a>
    </li>
</ul>