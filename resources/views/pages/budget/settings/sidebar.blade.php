<div class="intro-y box mt-5">
    <div class="relative flex items-center p-5">
        <div class="w-12 h-12 rounded-full inline-flex justify-center items-center bg-slate-100">
            <i data-lucide="settings" class="w-6 h-6 text-primary"></i>
        </div>
        <div class="ml-4 mr-auto">
            <div class="font-medium text-base">Budget Settings</div>
            <div class="text-slate-500">{{ $subtitle }}</div>
        </div>
    </div>
    <div class="p-5 border-t border-slate-200/60 dark:border-darkmode-400 settingsMenu">
        <ul class="m-0 p-0">
            <li class="hasChild">
                <a class="flex items-center {{ Route::currentRouteName() == 'budget.settings.vendors' || Route::currentRouteName() == 'budget.settings.year' || Route::currentRouteName() == 'budget.settings.name' ? 'active text-primary font-medium' : '' }}" href="javascript:void(0);">
                    <i data-lucide="settings" class="w-4 h-4 mr-2"></i> Settings  <i data-lucide="chevron-down" class="w-4 h-4 ml-auto menuAgnle"></i>
                </a>
                <ul class="p-0 m-0 pl-5" style="display: {{ Route::currentRouteName() == 'budget.settings.year' || Route::currentRouteName() == 'budget.settings.name' ? 'block' : 'none' }};">
                    <li>
                        <a class="flex items-center mt-4 {{ Route::currentRouteName() == 'budget.settings.year' ? 'active text-primary' : '' }}" href="{{ route('budget.settings.year') }}">
                            <i data-lucide="calendar-days" class="w-4 h-4 mr-2"></i> Budget Years
                        </a>
                    </li>
                    <li>
                        <a class="flex items-center mt-4 {{ Route::currentRouteName() == 'budget.settings.name' ? 'active text-primary' : '' }}" href="{{ route('budget.settings.name') }}">
                            <i data-lucide="list" class="w-4 h-4 mr-2"></i> Budget Names
                        </a>
                    </li>
                    <li>
                        <a class="flex items-center mt-4 {{ Route::currentRouteName() == 'budget.settings.vendors' ? 'active text-primary' : '' }}" href="{{ route('budget.settings.vendors') }}">
                            <i data-lucide="contact" class="w-4 h-4 mr-2"></i> Vendors
                        </a>
                    </li>
                </ul>
            </li>
            <li>
                <a class="flex items-center mt-5 {{ Route::currentRouteName() == 'budget.settings.set' ? 'active text-primary font-medium' : '' }}" href="{{ route('budget.settings.set') }}">
                    <i data-lucide="pie-chart" class="w-4 h-4 mr-2"></i> Budgets
                </a>
            </li>
        </ul>
    </div>
</div>