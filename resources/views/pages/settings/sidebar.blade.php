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
                <a class="flex items-center {{ Route::currentRouteName() == 'site.setting' || Route::currentRouteName() == 'site.setting.addr.api' ? 'active text-primary font-medium' : '' }}" href="javascript:void(0);">
                    <i data-lucide="globe" class="w-4 h-4 mr-2"></i> Site Settings  <i data-lucide="chevron-down" class="w-4 h-4 ml-auto menuAgnle"></i>
                </a>
                <ul class="p-0 m-0 pl-5" style="display: {{ Route::currentRouteName() == 'site.setting' || Route::currentRouteName() == 'site.setting.addr.api' ? 'block' : 'none' }};">
                    <li>
                        <a class="flex items-center mt-4 {{ Route::currentRouteName() == 'site.setting' ? 'active text-primary' : '' }}" href="{{ route('site.setting') }}">
                            <i data-lucide="check-circle" class="w-3 h-3 mr-2"></i> Company Information
                        </a>
                    </li>
                    <li>
                        <a class="flex items-center mt-4 {{ Route::currentRouteName() == 'site.setting.addr.api' ? 'active text-primary' : '' }}" href="{{ route('site.setting.addr.api') }}">
                            <i data-lucide="check-circle" class="w-3 h-3 mr-2"></i> Address Capture Setting
                        </a>
                    </li>
                </ul>
            </li>
            <li class="hasChild">
                <a class="flex items-center mt-5 {{ Route::currentRouteName() == 'coursequalification' || Route::currentRouteName() == 'sourcetutionfees' || Route::currentRouteName() == 'awardingbody' || Route::currentRouteName() == 'academicyears' || Route::currentRouteName() == 'academicyears.show' ? 'active text-primary font-medium' : '' }}" href="javascript:void(0);">
                    <i data-lucide="hash" class="w-4 h-4 mr-2"></i> Course Parameters <i data-lucide="chevron-down" class="w-4 h-4 ml-auto menuAgnle"></i>
                </a>
                <ul class="p-0 m-0 pl-5" style="display: {{ Route::currentRouteName() == 'coursequalification' || Route::currentRouteName() == 'sourcetutionfees' || Route::currentRouteName() == 'awardingbody' || Route::currentRouteName() == 'academicyears' || Route::currentRouteName() == 'academicyears.show' ? 'block' : 'none' }};">
                    <li>
                        <a class="flex items-center  mt-4 {{ Route::currentRouteName() == 'awardingbody' ? 'active text-primary' : '' }}" href="{{ route('awardingbody') }}">
                            <i data-lucide="check-circle" class="w-3 h-3 mr-2"></i> Awarding Body
                        </a>
                    </li>
                    <li>
                        <a class="flex items-center mt-5 {{ Route::currentRouteName() == 'academicyears' || Route::currentRouteName() == 'academicyears.show' ? 'active text-primary' : '' }}" href="{{ route('academicyears') }}">
                            <i data-lucide="check-circle" class="w-3 h-3 mr-2"></i> Academic Years
                        </a>
                    </li>
                    <li>
                        <a class="flex items-center mt-5 {{ Route::currentRouteName() == 'sourcetutionfees' ? 'active text-primary' : '' }}" href="{{ route('sourcetutionfees') }}">
                            <i data-lucide="check-circle" class="w-3 h-3 mr-2"></i> Source of Tuition Fees
                        </a>
                    </li>
                    <li>
                        <a class="flex items-center mt-5 {{ Route::currentRouteName() == 'coursequalification' ? 'active text-primary' : '' }}" href="{{ route('coursequalification') }}">
                            <i data-lucide="check-circle" class="w-3 h-3 mr-2"></i> Course Qualifications
                        </a>
                    </li>
                </ul>
            </li>
            <li class="hasChild">
                <a class="flex items-center mt-5 {{ Route::currentRouteName() == 'room.show' || Route::currentRouteName() == 'venues.show' || Route::currentRouteName() == 'venues'? 'active text-primary font-medium' : '' }}" href="javascript:void(0);">
                    <i data-lucide="map-pin" class="w-4 h-4 mr-2"></i> Campus Settings <i data-lucide="chevron-down" class="w-4 h-4 ml-auto menuAgnle"></i>
                </a>
                <ul class="p-0 m-0 pl-5" style="display: {{ Route::currentRouteName() == 'room.show' || Route::currentRouteName() == 'venues.show' || Route::currentRouteName() == 'venues' ? 'block' : 'none' }};">
                    <li>
                        <a class="flex items-center mt-4 {{ Route::currentRouteName() == 'room.show' || Route::currentRouteName() == 'venues.show' || Route::currentRouteName() == 'venues' ? 'active text-primary' : '' }}" href="{{ route('venues') }}">
                            <i data-lucide="check-circle" class="w-3 h-3 mr-2"></i> Venues
                        </a>
                    </li>
                </ul>
            </li>
            <li class="hasChild">
                <a class="flex items-center mt-5 {{ Route::currentRouteName() == 'tasklist' || Route::currentRouteName() == 'processlist' || Route::currentRouteName() == 'documentsettings' || Route::currentRouteName() == 'statuses' ? 'active text-primary font-medium' : '' }}" href="javascript:void(0);">
                    <i data-lucide="user-check" class="w-4 h-4 mr-2"></i> Applicant Settings <i data-lucide="chevron-down" class="w-4 h-4 ml-auto menuAgnle"></i>
                </a>
                <ul class="p-0 m-0 pl-5" style="display: {{ Route::currentRouteName() == 'tasklist' || Route::currentRouteName() == 'processlist' || Route::currentRouteName() == 'documentsettings' || Route::currentRouteName() == 'statuses' ? 'block' : 'none' }};">
                    <li>
                        <a class="flex items-center mt-4 {{ Route::currentRouteName() == 'statuses' ? 'active text-primary' : '' }}" href="{{ route('statuses') }}">
                            <i data-lucide="check-circle" class="w-3 h-3 mr-2"></i> Statuses
                        </a>
                    </li>
                    <li>
                        <a class="flex items-center mt-4 {{ Route::currentRouteName() == 'documentsettings' ? 'active text-primary' : '' }}" href="{{ route('documentsettings') }}">
                            <i data-lucide="check-circle" class="w-3 h-3 mr-2"></i> Document Settings
                        </a>
                    </li>
                    <li>
                        <a class="flex items-center mt-4 {{ Route::currentRouteName() == 'processlist' ? 'active text-primary' : '' }}" href="{{ route('processlist') }}">
                            <i data-lucide="check-circle" class="w-3 h-3 mr-2"></i> Process List
                        </a>
                    </li>
                    <li>
                        <a class="flex items-center mt-4 {{ Route::currentRouteName() == 'tasklist' ? 'active text-primary' : '' }}" href="{{ route('tasklist') }}">
                            <i data-lucide="check-circle" class="w-3 h-3 mr-2"></i> Task List
                        </a>
                    </li>
                </ul>
            </li>
            <li>
                <a class="flex items-center mt-5 {{ Route::currentRouteName() == 'student.options' ? 'active text-primary font-medium' : '' }}" href="{{ route('student.options') }}">
                    <i data-lucide="sliders" class="w-4 h-4 mr-2"></i> Student Option Values
                </a>
            </li>
            <li class="hasChild">
                <a class="flex items-center mt-5 {{ Route::currentRouteName() == 'letterheaderfooter' || Route::currentRouteName() == 'signatory' || Route::currentRouteName() == 'consent' || Route::currentRouteName() == 'letter.set' || Route::currentRouteName() == 'common.smtp' || Route::currentRouteName() == 'email.template' || Route::currentRouteName() == 'sms.template' || Route::currentRouteName() == 'site.setting.sms.api' ? 'active text-primary font-medium' : '' }}" href="javascript:void(0);">
                    <i data-lucide="mail" class="w-4 h-4 mr-2 "></i> Communication Settings <i data-lucide="chevron-down" class="w-4 h-4 ml-auto menuAgnle"></i>
                </a>
                <ul class="p-0 m-0 pl-5" style="display: {{ Route::currentRouteName() == 'letterheaderfooter' || Route::currentRouteName() == 'signatory' || Route::currentRouteName() == 'consent' || Route::currentRouteName() == 'letter.set' || Route::currentRouteName() == 'common.smtp' || Route::currentRouteName() == 'email.template' || Route::currentRouteName() == 'sms.template' || Route::currentRouteName() == 'site.setting.sms.api' ? 'block' : 'none' }};">
                    <li class="hasChild">
                        <a class="flex items-center  mt-4 {{ Route::currentRouteName() == 'sms.template' || Route::currentRouteName() == 'site.setting.sms.api' ? 'active text-primary font-medium' : '' }}" href="javascript:void(0);">
                            <i data-lucide="check-circle" class="w-3 h-3 mr-2"></i> SMS Settings <i data-lucide="chevron-down" class="w-4 h-4 ml-auto menuAgnle"></i>
                        </a>
                        <ul class="p-0 m-0 pl-5" style="display: {{ Route::currentRouteName() == 'sms.template' || Route::currentRouteName() == 'site.setting.sms.api' ? 'block' : 'none' }};">
                            <li>
                                <a class="flex items-center  mt-4 {{ Route::currentRouteName() == 'site.setting.sms.api' ? 'active text-primary' : '' }}" href="{{ route('site.setting.sms.api') }}">
                                    <i data-lucide="check-circle" class="w-3 h-3 mr-2"></i> API Settings
                                </a>
                            </li>
                            <li>
                                <a class="flex items-center  mt-4 {{ Route::currentRouteName() == 'sms.template' ? 'active text-primary' : '' }}" href="{{ route('sms.template') }}">
                                    <i data-lucide="check-circle" class="w-3 h-3 mr-2"></i> SMS Templates
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="hasChild">
                        <a class="flex items-center  mt-4 {{ Route::currentRouteName() == 'common.smtp' || Route::currentRouteName() == 'email.template' ? 'active text-primary font-medium' : '' }}" href="javascript:void(0);">
                            <i data-lucide="check-circle" class="w-3 h-3 mr-2"></i> Email Settings <i data-lucide="chevron-down" class="w-4 h-4 ml-auto menuAgnle"></i>
                        </a>
                        <ul class="p-0 m-0 pl-5" style="display:  {{ Route::currentRouteName() == 'common.smtp' || Route::currentRouteName() == 'email.template' ? 'block' : 'none' }};">
                            <li>
                                <a class="flex items-center  mt-4 {{ Route::currentRouteName() == 'email.template' ? 'active text-primary' : '' }}" href="{{ route('email.template') }}">
                                    <i data-lucide="check-circle" class="w-3 h-3 mr-2"></i> Email Templates
                                </a>
                            </li>
                            <li>
                                <a class="flex items-center  mt-4 {{ Route::currentRouteName() == 'common.smtp' ? 'active text-primary' : '' }}" href="{{ route('common.smtp') }}">
                                    <i data-lucide="check-circle" class="w-3 h-3 mr-2"></i> SMTP Settings
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a class="flex items-center  mt-4 {{ Route::currentRouteName() == 'letter.set' ? 'active text-primary font-medium' : '' }}" href="{{ route('letter.set') }}">
                            <i data-lucide="check-circle" class="w-3 h-3 mr-2"></i> Letter Templates
                        </a>
                    </li>
                    <li>
                        <a class="flex items-center mt-4 {{ Route::currentRouteName() == 'consent' ? 'active text-primary font-medium' : '' }}" href="{{ route('consent') }}">
                            <i data-lucide="check-circle" class="w-3 h-3 mr-2"></i> Consent Policy
                        </a>
                    </li>
                    <li>
                        <a class="flex items-center  mt-4 {{ Route::currentRouteName() == 'signatory' ? 'active text-primary font-medium' : '' }}" href="{{ route('signatory') }}">
                            <i data-lucide="check-circle" class="w-3 h-3 mr-2"></i> Signatory Settings
                        </a>
                    </li>
                    <li>
                        <a class="flex items-center  mt-4 {{ Route::currentRouteName() == 'letterheaderfooter' ? 'active text-primary font-medium' : '' }}" href="{{ route('letterheaderfooter') }}">
                            <i data-lucide="check-circle" class="w-3 h-3 mr-2"></i> Header & Footer Settings
                        </a>
                    </li>
                </ul>
            </li>
            <li>
                <a class="flex items-center mt-5 {{ Route::currentRouteName() == 'elearning' ? 'active text-primary font-medium' : '' }}" href="{{ route('elearning') }}">
                    <i data-lucide="frame" class="w-4 h-4 mr-2"></i> E-Learning Activity Setting
                </a>
            </li>
            <li class="hasChild">
                <a class="flex items-center mt-5 {{ Route::currentRouteName() == 'roles.show' || Route::currentRouteName() == 'department' || Route::currentRouteName() == 'permissioncategory' || Route::currentRouteName() == 'roles' ? 'active text-primary font-medium' : '' }}" href="javascript:void(0);">
                    <i data-lucide="user-cog-2" class="w-4 h-4 mr-2"></i> User Privilege <i data-lucide="chevron-down" class="w-4 h-4 ml-auto menuAgnle"></i>
                </a>
                <ul class="p-0 m-0 pl-5" style="display: {{ Route::currentRouteName() == 'roles.show' || Route::currentRouteName() == 'department' || Route::currentRouteName() == 'permissioncategory' || Route::currentRouteName() == 'roles' ? 'block' : 'none' }};">
                    <li>
                        <a class="flex items-center mt-4 {{ Route::currentRouteName() == 'roles.show' || Route::currentRouteName() == 'roles' ? 'active text-primary font-medium' : '' }}" href="{{ route('roles') }}">
                            <i data-lucide="check-circle" class="w-3 h-3 mr-2"></i> Role
                        </a>
                    </li>
                    <li>
                        <a class="flex items-center mt-4 {{ Route::currentRouteName() == 'permissioncategory' ? 'active text-primary font-medium' : '' }}" href="{{ route('permissioncategory') }}">
                            <i data-lucide="check-circle" class="w-3 h-3 mr-2"></i> Permission Category
                        </a>
                    </li>
                    <li>
                        <a class="flex items-center mt-4 {{ Route::currentRouteName() == 'department' ? 'active text-primary font-medium' : '' }}" href="{{ route('department') }}">
                            <i data-lucide="check-circle" class="w-3 h-3 mr-2"></i> Department
                        </a>
                    </li>
                </ul>
            </li>
            <li class="hasChild">
                <a class="flex items-center mt-5 {{ Route::currentRouteName() == 'holiday.year.leave.option' || Route::currentRouteName() == 'hr.bank.holiday' || Route::currentRouteName() == 'holiday.year' ? 'active text-primary font-medium' : '' }}" href="javascript:void(0);">
                    <i data-lucide="contact-2" class="w-4 h-4 mr-2"></i> HR Settings <i data-lucide="chevron-down" class="w-4 h-4 ml-auto menuAgnle"></i>
                </a>
                <ul class="p-0 m-0 pl-5" style="display: {{ Route::currentRouteName() == 'holiday.year.leave.option' || Route::currentRouteName() == 'hr.bank.holiday' || Route::currentRouteName() == 'holiday.year' ? 'block' : 'none' }};">
                    <li>
                        <a class="flex items-center mt-4 {{ Route::currentRouteName() == 'holiday.year.leave.option' || Route::currentRouteName() == 'hr.bank.holiday' || Route::currentRouteName() == 'holiday.year' ? 'active text-primary font-medium' : '' }}" href="{{ route('holiday.year') }}">
                            <i data-lucide="check-circle" class="w-3 h-3 mr-2"></i> Holiday Years
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</div>