<div class="intro-y box mt-5">
    <div class="relative flex items-center p-5">
        <div class="w-12 h-12 rounded-full inline-flex justify-center items-center bg-slate-100">
            <i data-lucide="settings" class="w-6 h-6 text-primary"></i>
        </div>
        <div class="ml-4 mr-auto">
            <div class="font-medium text-base">Settings</div>
            <div class="text-slate-500">{{ $subtitle }}</div>
        </div>
    </div>
    <div class="p-5 border-t border-slate-200/60 dark:border-darkmode-400 settingsMenu">
        <ul class="m-0 p-0">
            <li class="hasChild">
                <a class="flex items-center {{ Route::currentRouteName() == 'library-locations' ? 'active text-primary font-medium' : '' }}" href="javascript:void(0);">
                    <i data-lucide="globe" class="w-4 h-4 mr-2"></i> Library Settings <i data-lucide="chevron-down" class="w-4 h-4 ml-auto menuAgnle"></i>
                </a>
                <ul class="p-0 m-0 pl-5" style="display: {{ Route::currentRouteName() == 'library-locations' ? 'block' : 'none' }};">
                    <li>
                        <a class="flex items-center mt-4 {{ Route::currentRouteName() == 'site.setting' ? 'active text-primary' : '' }}" href="{{ route('site.setting') }}">
                            <i data-lucide="check-circle" class="w-3 h-3 mr-2"></i> Policy Elements Settings
                        </a>
                    </li>
                    <li>
                        <a class="flex items-center mt-4 {{ Route::currentRouteName() == 'library-locations' ? 'active text-primary' : '' }}" href="{{ route('library-locations') }}">
                            <i data-lucide="check-circle" class="w-3 h-3 mr-2"></i> Library Location Settings
                        </a>
                    </li>
                </ul>
            </li>
            <li>
                <a class="flex items-center mt-5 {{ Route::currentRouteName() == 'library.books' || Route::currentRouteName() == 'library.books.create' ? 'active text-primary font-medium' : '' }}" href="{{ route('library.books') }}">
                    <i data-lucide="book-copy" class="w-4 h-4 mr-2"></i> Books
                </a>
            </li>
        </ul>
    </div>
</div>