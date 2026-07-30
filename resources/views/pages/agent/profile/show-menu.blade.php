@php
    $profileTabCounts = $profileTabCounts ?? [];
    $profileTabs = [
        [
            'route' => 'agent-user.show',
            'label' => 'Applicants/Students Details',
            'icon' => 'users',
            'count' => $profileTabCounts['applicants'] ?? 0,
        ],
        [
            'route' => 'sub-agent.show',
            'label' => 'Sub Agents',
            'icon' => 'user-plus',
            'count' => $profileTabCounts['sub'] ?? 0,
        ],
        [
            'route' => 'agent-user.documents',
            'label' => 'Documents',
            'icon' => 'file-text',
            'count' => $profileTabCounts['docs'] ?? 0,
        ],
        [
            'route' => 'agent-user.payment.settings',
            'label' => 'Payment Settings',
            'icon' => 'landmark',
            'count' => $profileTabCounts['pay'] ?? 0,
        ],
    ];
@endphp

<nav class="agm-profile-tabs" aria-label="Agent profile sections">
    @foreach($profileTabs as $tab)
        @php
            $isActive = Route::currentRouteName() === $tab['route'];
        @endphp
        <a href="{{ route($tab['route'], $employee->id) }}" class="agm-profile-tab {{ $isActive ? 'is-active' : '' }}">
            <i data-lucide="{{ $tab['icon'] }}"></i>
            <span>{{ $tab['label'] }}</span>
            <small>{{ $tab['count'] }}</small>
        </a>
    @endforeach
</nav>
