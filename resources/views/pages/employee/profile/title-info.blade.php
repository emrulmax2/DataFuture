<div class="intro-y flex flex-wrap items-center justify-between gap-3 mt-8 mb-1">
    <div class="text-sm font-semibold text-slate-500 dark:text-slate-400">
        Profile of
        <span class="font-display text-2xl font-semibold text-slate-800 dark:text-white align-middle ml-1">{{ $employee->title->name.' '.$employee->first_name.' '.$employee->last_name }}</span>
    </div>
    <div class="flex items-center gap-2">
        @if(isset(auth()->user()->priv()['login_as_user']) && auth()->user()->priv()['login_as_user'] == 1)
        <a href="{{ route('impersonate', $employee->user_id) }}" class="inline-flex items-center gap-2 h-10 px-4 rounded-lg bg-primary text-white text-[13.5px] font-bold shadow-sm shadow-primary/30 hover:bg-primary-hover transition-colors">
            Login As User <i data-lucide="log-in" class="w-4 h-4"></i>
        </a>
        @endif
        @if(auth()->user()->id == 7 || auth()->user()->id == 1 || auth()->user()->id == 8)
        <div class="dropdown inline-block" data-tw-placement="bottom-end" id="profileSettingsDropdown">
            <button class="dropdown-toggle inline-flex items-center justify-center w-10 h-10 rounded-lg border border-slate-200 dark:border-darkmode-400 bg-white dark:bg-darkmode-600 text-slate-500 hover:text-primary hover:border-primary transition-colors" aria-expanded="false" data-tw-toggle="dropdown">
                <i data-lucide="user-cog" class="w-5 h-5"></i>
            </button>
            <div class="dropdown-menu w-64">
                <div class="dropdown-content">
                    <h6 class="dropdown-header">Profile Settings</h6>
                    <hr class="dropdown-divider">
                    <form method="post" action="#" id="profileSettingsForm" class="p-2">
                        <div>
                            <div class="form-check form-switch">
                                <label class="form-check-label ml-0 mr-5" for="can_access_all">Can access all Profile</label>
                                <input id="can_access_all" {{ (isset($employee->can_access_all) && $employee->can_access_all == 1 ? 'checked' : '') }} class="form-check-input ml-auto" name="can_access_all" value="1" type="checkbox">
                            </div>
                        </div>
                        <div class="mt-3 mb-4">
                            <div class="form-check form-switch">
                                <label class="form-check-label ml-0 mr-5" for="locked_profile">Locked profile</label>
                                <input {{ (isset($employee->locked_profile) && $employee->locked_profile == 1 ? 'checked' : '') }} id="locked_profile" class="form-check-input ml-auto" name="locked_profile" value="1" type="checkbox">
                            </div>
                        </div>
                        <hr class="dropdown-divider">
                        <div class="flex justify-between items-center mt-3">
                            <button type="button" class="dismisProfSetDropdown btn btn-secondary btn-sm w-auto mr-auto">Close</button>
                            <button type="submit" id="saveProfileSettingBtn" class="btn btn-primary btn-sm w-auto ml-auto">
                                Save
                                <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                    stroke="white" class="w-4 h-4 ml-2">
                                    <g fill="none" fill-rule="evenodd">
                                        <g transform="translate(1 1)" stroke-width="4">
                                            <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                            <path d="M36 18c0-9.94-8.06-18-18-18">
                                                <animateTransform attributeName="transform" type="rotate" from="0 18 18"
                                                    to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                            </path>
                                        </g>
                                    </g>
                                </svg>
                            </button>
                        </div>
                        <input type="hidden" value="{{ $employee->id }}" name="employee_id"/>
                    </form>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
