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

    $tabs = [
        ['show' => $hrPortal,                'route' => 'profile.employee.view',        'match' => ['profile.employee.view'],                          'icon' => 'user',        'label' => 'Profile'],
        ['show' => $hrPortal,                'route' => 'employee.payment.settings',    'match' => ['employee.payment.settings'],                      'icon' => 'credit-card', 'label' => 'Payment Settings'],
        ['show' => $hrPortal && $hasHoliday, 'route' => 'employee.holiday',             'match' => ['employee.holiday'],                               'icon' => 'sun',         'label' => 'Holidays'],
        ['show' => $hrPortal && $hasPayslip, 'route' => 'profile.employee.payslip.show','match' => ['profile.employee.payslip.show'],                  'icon' => 'receipt',     'label' => 'Payslips'],
        ['show' => $hrPortal,                'route' => 'employee.documents',           'match' => ['employee.documents'],                             'icon' => 'file-text',   'label' => 'Documents'],
        ['show' => $hrPortal,                'route' => 'employee.notes',               'match' => ['employee.notes'],                                 'icon' => 'sticky-note', 'label' => 'Notes'],
        ['show' => true,                     'route' => 'employee.appraisal',           'match' => ['employee.appraisal', 'employee.appraisal.documents'], 'icon' => 'award',   'label' => 'Appraisal & Training'],
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
                <a href="{{ route($t['route'], $employee->id) }}" class="ep-tabs__link {{ in_array($current, $t['match']) ? 'is-active' : '' }}">
                    <i data-lucide="{{ $t['icon'] }}" class="w-4 h-4"></i>
                    <span>{{ $t['label'] }}</span>
                </a>
            @endif
        @endforeach
    </div>
</nav>
