@php
    $current  = Route::currentRouteName();
    $hrPortal = isset(auth()->user()->priv()['hr_porta']) && auth()->user()->priv()['hr_porta'] == 1;
    $canPriv  = (isset(auth()->user()->priv()['privilege_menu']) && auth()->user()->priv()['privilege_menu'] == 1) || in_array(auth()->user()->id, [1, 7]);
    $hasHoliday = isset($employee->payment->holiday_entitled) && $employee->payment->holiday_entitled == 'Yes';
    $hasPayslip = isset($employee->payslipWithTransfered) && $employee->payslipWithTransfered->count() > 0;

    // Once the new system governs access, the legacy screen edits a table nobody
    // reads any more. Hiding it stops HR making changes that appear to save and
    // silently do nothing.
    $legacyPrivilegeLive = config('privileges.source') !== 'new';

    // A tab with 'children' opens a menu of those pages instead of going to one.
    // It only becomes a menu when more than one child is visible to this user;
    // with a single visible child it stays an ordinary link to that page.
    $tabs = [
        ['show' => $hrPortal,                'route' => 'profile.employee.view',        'match' => ['profile.employee.view'],                          'icon' => 'user',        'label' => 'Profile'],
        ['show' => $hrPortal,                'route' => 'employee.payment.settings',    'match' => ['employee.payment.settings'],                      'icon' => 'credit-card', 'label' => 'Payment Settings'],
        ['show' => $hrPortal && $hasHoliday, 'route' => 'employee.holiday',             'match' => ['employee.holiday'],                               'icon' => 'sun',         'label' => 'Holidays'],
        ['show' => $hrPortal && $hasPayslip, 'route' => 'profile.employee.payslip.show','match' => ['profile.employee.payslip.show'],                  'icon' => 'receipt',     'label' => 'Payslips'],
        ['show' => $hrPortal,                'route' => 'employee.documents',           'match' => ['employee.documents'],                             'icon' => 'file-text',   'label' => 'Documents'],
        ['show' => $hrPortal,                'route' => 'employee.notes',               'match' => ['employee.notes'],                                 'icon' => 'sticky-note', 'label' => 'Notes'],
        ['show' => true,                     'route' => 'employee.appraisal',           'match' => ['employee.appraisal', 'employee.appraisal.documents'], 'icon' => 'award',   'label' => 'Appraisal & Training',
            'children' => [
                ['show' => true, 'route' => 'employee.appraisal', 'match' => ['employee.appraisal', 'employee.appraisal.documents'], 'icon' => 'award', 'label' => 'Appraisal & Training'],
                ['show' => $hrPortal && \App\Services\PolicyAssessmentService::canManage(), 'route' => 'employee.policy.assessment', 'match' => ['employee.policy.assessment'], 'icon' => 'clipboard-check', 'label' => 'Policy Assessments'],
            ]],
        ['show' => $hrPortal && $canPriv && $legacyPrivilegeLive, 'route' => 'employee.privilege', 'match' => ['employee.privilege'],              'icon' => 'lock',        'label' => 'Privilege (Old)'],
        ['show' => $hrPortal && $canPriv,    'route' => 'employee.privilege.new',       'match' => ['employee.privilege.new'],                         'icon' => 'shield-check','label' => $legacyPrivilegeLive ? 'Privilege (New)' : 'Privilege'],
        ['show' => $hrPortal,                'route' => 'employee.time.keeper',         'match' => ['employee.time.keeper'],                           'icon' => 'clock',       'label' => 'Time Recorded'],
        ['show' => $hrPortal,                'route' => 'employee.archive',             'match' => ['employee.archive'],                               'icon' => 'archive',     'label' => 'Archive'],
        ['show' => true,                     'route' => 'profile.employee.login.logs',  'match' => ['profile.employee.login.logs'],                    'icon' => 'list',        'label' => 'Logs'],
    ];
@endphp

<nav class="ep-tabs">
    <div class="ep-tabs__inner">
        @foreach($tabs as $t)
            @if($t['show'])
                @php
                    $children = [];
                    foreach((isset($t['children']) ? $t['children'] : []) as $child):
                        if($child['show']):
                            $children[] = $child;
                        endif;
                    endforeach;
                @endphp
                @if(count($children) > 1)
                    @php
                        $menuActive = false;
                        foreach($children as $child):
                            if(in_array($current, $child['match'])):
                                $menuActive = true;
                            endif;
                        endforeach;
                    @endphp
                    <div class="dropdown ep-tabs__dropdown" data-tw-placement="bottom-start">
                        <button type="button" class="dropdown-toggle ep-tabs__link {{ $menuActive ? 'is-active' : '' }}" aria-expanded="false" data-tw-toggle="dropdown">
                            <i data-lucide="{{ $t['icon'] }}" class="w-4 h-4"></i>
                            <span>{{ $t['label'] }}</span>
                            <i data-lucide="chevron-down" class="ep-tabs__caret"></i>
                        </button>
                        <div class="dropdown-menu ep-tabs__menu">
                            <ul class="dropdown-content">
                                @foreach($children as $child)
                                    <li>
                                        <a href="{{ route($child['route'], $employee->id) }}" class="dropdown-item {{ in_array($current, $child['match']) ? 'is-active' : '' }}" @if(in_array($current, $child['match'])) aria-current="page" @endif>
                                            <i data-lucide="{{ $child['icon'] }}" class="w-4 h-4"></i>
                                            <span>{{ $child['label'] }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @else
                    @php $link = (count($children) === 1 ? $children[0] : $t); @endphp
                    <a href="{{ route($link['route'], $employee->id) }}" class="ep-tabs__link {{ in_array($current, $link['match']) ? 'is-active' : '' }}">
                        <i data-lucide="{{ $t['icon'] }}" class="w-4 h-4"></i>
                        <span>{{ $t['label'] }}</span>
                    </a>
                @endif
            @endif
        @endforeach
    </div>
</nav>
