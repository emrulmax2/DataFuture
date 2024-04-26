@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Profile of <u><strong>{{ $employee->title->name.' '.$employee->full_name }}</strong></u></h2>
    </div>

    <!-- BEGIN: Profile Info -->
    @include('pages.employee.profile.show-info')
    <!-- END: Profile Info -->
    
    <form method="post" action="#" id="employeePrivilegeForm">
        <input type="hidden" name="employee_id" value="{{ $employee->id }}"/>
        <div class="intro-y box p-5 mt-5">
            <div class="grid grid-cols-12 gap-0 items-center">
                <div class="col-span-6">
                    <div class="font-medium text-base">Remote Access Privileges</div>
                </div>
                <div class="col-span-6 text-right relative">
                    <button type="submit" class="btn btn-primary shadow-md mr-2"><i data-lucide="save-all" class="w-4 h-4 mr-2"></i>Save All</button>
                </div>
            </div>
            <div class="intro-y mt-5">
                <div class="grid grid-cols-12 gap-4 items-center">
                    <div class="col-span-12 sm:col-span-3">
                        <div class="form-check form-switch">
                            <input {{ (isset($priv['remote_access']['ra_status']) && $priv['remote_access']['ra_status'] == 1 ? 'checked' : '') }} id="permission_remote_access_1" class="form-check-input" type="checkbox" value="1" name="permission[remote_access][ra_status]">
                            <label class="form-check-label ml-4 ra_status_label" for="permission_remote_access_1">
                                {{ (isset($priv['remote_access']['ra_status']) && $priv['remote_access']['ra_status'] == 1 ? 'Allowed' : 'Not Allowed') }}
                            </label>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-3" id="inRangeSwitch" style="display: {{ (isset($priv['remote_access']['ra_status']) && $priv['remote_access']['ra_status'] == 1 ? 'block' : 'none') }};">
                        <div class="form-check form-switch">
                            <input {{ (isset($priv['remote_access']['in_range']) && $priv['remote_access']['in_range'] == 1 ? 'checked' : '') }} id="permission_remote_access_2" class="form-check-input" type="checkbox" value="1" name="permission[remote_access][in_range]">
                            <label class="form-check-label ml-4" for="permission_remote_access_2">Allowed Termporary</label>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-3" id="dateRangeWrap" style="display: {{ (isset($priv['remote_access']['in_range']) && $priv['remote_access']['in_range'] == 1 ? 'block' : 'none') }};">
                        <div class="flex justify-between items-center">
                            <input type="text" name="permission[remote_access][date_range]" value="{{ (isset($priv['remote_access']['date_range']) && !empty($priv['remote_access']['date_range']) ? $priv['remote_access']['date_range'] : '') }}" data-daterange="true" id="rangepicker" class="rangepicker form-control w-56 block mx-auto">
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-3">
                        <div class="form-check form-switch">
                            <input {{ (isset($priv['remote_access']['work_home']) && $priv['remote_access']['work_home'] == 1 ? 'checked' : '') }} id="permission_remote_access_4" class="form-check-input" type="checkbox" value="1" name="permission[remote_access][work_home]">
                            <label class="form-check-label ml-4" for="permission_remote_access_4">Working From Home</label>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-3">
                        <div class="form-check form-switch">
                            <input {{ (isset($priv['remote_access']['desktop_login']) && $priv['remote_access']['desktop_login'] == 1 ? 'checked' : '') }} id="permission_remote_access_5" class="form-check-input" type="checkbox" value="1" name="permission[remote_access][desktop_login]">
                            <label class="form-check-label ml-4" for="permission_remote_access_5">Desktop Clock In</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="intro-y box p-5 mt-5">
            <div class="grid grid-cols-12 gap-0 items-center">
                <div class="col-span-6">
                    <div class="font-medium text-base">Menu Privileges</div>
                </div>
                <div class="col-span-6 text-right relative">
                    <button type="submit" class="btn btn-primary shadow-md mr-2"><i data-lucide="save-all" class="w-4 h-4 mr-2"></i>Save All</button>
                </div>
            </div>
            <div class="intro-y mt-5">
                <div class="grid grid-cols-12 gap-4">
                    <div class="col-span-12 sm:col-span-3">
                        <div class="form-check form-switch">
                            <input {{ (isset($priv['top_menue']['course_manage']) && $priv['top_menue']['course_manage'] == 1 ? 'checked' : '') }} id="permission_menue_1" class="form-check-input" type="checkbox" value="1" name="permission[top_menue][course_manage]">
                            <label class="form-check-label ml-4" for="permission_menue_1">Course Management</label>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-3">
                        <div class="form-check form-switch">
                            <input {{ (isset($priv['top_menue']['student_manage']) && $priv['top_menue']['student_manage'] == 1 ? 'checked' : '') }} id="permission_menue_2" class="form-check-input" type="checkbox" value="1" name="permission[top_menue][student_manage]">
                            <label class="form-check-label ml-4" for="permission_menue_2">Student Management</label>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-3">
                        <div class="form-check form-switch">
                            <input {{ (isset($priv['top_menue']['settings']) && $priv['top_menue']['settings'] == 1 ? 'checked' : '') }} id="permission_menue_3" class="form-check-input" type="checkbox" value="1" name="permission[top_menue][settings]">
                            <label class="form-check-label ml-4" for="permission_menue_3">Settings</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="intro-y box p-5 mt-5">
            <div class="grid grid-cols-12 gap-0 items-center">
                <div class="col-span-6">
                    <div class="font-medium text-base">Dashboard Privileges</div>
                </div>
                <div class="col-span-6 text-right relative">
                    <button type="submit" class="btn btn-primary shadow-md mr-2"><i data-lucide="save-all" class="w-4 h-4 mr-2"></i>Save All</button>
                </div>
            </div>
            <div class="intro-y mt-5">
                <div class="grid grid-cols-12 gap-4">
                    <div class="col-span-12 sm:col-span-3">
                        <div class="form-check form-switch">
                            <input {{ (isset($priv['dashboard']['applicant']) && $priv['dashboard']['applicant'] == 1 ? 'checked' : '') }} id="permission_dashboard_1" class="form-check-input" type="checkbox" value="1" name="permission[dashboard][applicant]">
                            <label class="form-check-label ml-4" for="permission_dashboard_1">Applicant</label>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-3">
                        <div class="form-check form-switch">
                            <input {{ (isset($priv['dashboard']['live']) && $priv['dashboard']['live'] == 1 ? 'checked' : '') }} id="permission_dashboard_2" class="form-check-input" type="checkbox" value="1" name="permission[dashboard][live]">
                            <label class="form-check-label ml-4" for="permission_dashboard_2">Live Student</label>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-3">
                        <div class="form-check form-switch">
                            <input {{ (isset($priv['dashboard']['tutor']) && $priv['dashboard']['tutor'] == 1 ? 'checked' : '') }} id="permission_dashboard_3" class="form-check-input" type="checkbox" value="1" name="permission[dashboard][tutor]">
                            <label class="form-check-label ml-4" for="permission_dashboard_3">Tutor Dashboard</label>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-3">
                        <div class="form-check form-switch">
                            <input {{ (isset($priv['dashboard']['tutor_2']) && $priv['dashboard']['tutor_2'] == 1 ? 'checked' : '') }} id="permission_dashboard_4" class="form-check-input" type="checkbox" value="1" name="permission[dashboard][tutor_2]">
                            <label class="form-check-label ml-4" for="permission_dashboard_4">Tutor Dashboard 2</label>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-3">
                        <div class="form-check form-switch">
                            <input {{ (isset($priv['dashboard']['personal_tutor']) && $priv['dashboard']['personal_tutor'] == 1 ? 'checked' : '') }} id="permission_dashboard_5" class="form-check-input" type="checkbox" value="1" name="permission[dashboard][personal_tutor]">
                            <label class="form-check-label ml-4" for="permission_dashboard_5">Personal Tutor Dashboard</label>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-3">
                        <div class="form-check form-switch">
                            <input {{ (isset($priv['dashboard']['req_interview']) && $priv['dashboard']['req_interview'] == 1 ? 'checked' : '') }} id="permission_dashboard_6" class="form-check-input" type="checkbox" value="1" name="permission[dashboard][req_interview]">
                            <label class="form-check-label ml-4" for="permission_dashboard_6">Required Interviews</label>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-3">
                        <div class="form-check form-switch">
                            <input {{ (isset($priv['dashboard']['hr_porta']) && $priv['dashboard']['hr_porta'] == 1 ? 'checked' : '') }} id="permission_dashboard_7" class="form-check-input" type="checkbox" value="1" name="permission[dashboard][hr_porta]">
                            <label class="form-check-label ml-4" for="permission_dashboard_7">HR Portal</label>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-3">
                        <div class="form-check form-switch">
                            <input {{ (isset($priv['dashboard']['programme_dashboard']) && $priv['dashboard']['programme_dashboard'] == 1 ? 'checked' : '') }} id="permission_dashboard_8" class="form-check-input" type="checkbox" value="1" name="permission[dashboard][programme_dashboard]">
                            <label class="form-check-label ml-4" for="permission_dashboard_8">Programme Dashboard</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="intro-y box p-5 mt-5">
            <div class="grid grid-cols-12 gap-0 items-center">
                <div class="col-span-6">
                    <div class="font-medium text-base">HR Portal Privileges</div>
                </div>
                <div class="col-span-6 text-right relative">
                    <button type="submit" class="btn btn-primary shadow-md mr-2"><i data-lucide="save-all" class="w-4 h-4 mr-2"></i>Save All</button>
                </div>
            </div>
            <div class="intro-y mt-5">
                <div class="grid grid-cols-12 gap-4 items-center">
                    <div class="col-span-12 sm:col-span-3">
                        <div class="form-check form-switch">
                            <input {{ (isset($priv['hr_portal']['add_attendance']) && $priv['hr_portal']['add_attendance'] == 1 ? 'checked' : '') }} id="permission_hr_portal_1" class="form-check-input" type="checkbox" value="1" name="permission[hr_portal][add_attendance]">
                            <label class="form-check-label ml-4" for="permission_hr_portal_1">Add Attendance</label>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-3">
                        <div class="form-check form-switch">
                            <input {{ (isset($priv['hr_portal']['del_attendance']) && $priv['hr_portal']['del_attendance'] == 1 ? 'checked' : '') }} id="permission_hr_portal_2" class="form-check-input" type="checkbox" value="1" name="permission[hr_portal][del_attendance]">
                            <label class="form-check-label ml-4" for="permission_hr_portal_2">Delete Attendance</label>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-3">
                        <div class="form-check form-switch">
                            <input {{ (isset($priv['hr_portal']['privilege_menu']) && $priv['hr_portal']['privilege_menu'] == 1 ? 'checked' : '') }} id="permission_hr_portal_3" class="form-check-input" type="checkbox" value="1" name="permission[hr_portal][privilege_menu]">
                            <label class="form-check-label ml-4" for="permission_hr_portal_3">Privilege Menu</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="intro-y box p-5 mt-5">
            <div class="grid grid-cols-12 gap-0 items-center">
                <div class="col-span-6">
                    <div class="font-medium text-base">Internal Links Privileges</div>
                </div>
                <div class="col-span-6 text-right relative">
                    <button type="submit" class="btn btn-primary shadow-md mr-2"><i data-lucide="save-all" class="w-4 h-4 mr-2"></i>Save All</button>
                </div>
            </div>
            <div class="intro-y mt-5">
                <div class="grid grid-cols-12 gap-4">
                    @if($links->count() > 0)
                        @foreach($links as $lnk)
                            <div class="col-span-12 mb-2">
                                <div class="form-check form-switch">
                                    <input {{ (isset($priv['parent_internal_links'][$lnk->id]) && $priv['parent_internal_links'][$lnk->id] == 1 ? 'checked' : '') }} id="permission_parent_internal_links_{{ $lnk->id }}" class="form-check-input parentPermissionItem" type="checkbox" value="1" name="permission[parent_internal_links][{{ $lnk->id }}]">
                                    <label class="form-check-label ml-4" for="permission_parent_internal_links_{{ $lnk->id }}">{{ $lnk->name }}</label>
                                </div>
                                @if(isset($lnk->children) && $lnk->children->count() > 0)
                                <div class="grid grid-cols-12 gap-4 pl-12 pt-3 childrenPermissionWrap">
                                    @foreach($lnk->children as $clnk)
                                        @php 
                                            $childAttr = (isset($priv['parent_internal_links'][$lnk->id]) && $priv['parent_internal_links'][$lnk->id] == 1 ? '' : ' disabled ');
                                            $childAttr .= (isset($priv['parent_internal_links'][$lnk->id]) && $priv['parent_internal_links'][$lnk->id] == 1) && (isset($priv['parent_child_'.$lnk->id.'_links'][$clnk->id]) && $priv['parent_child_'.$lnk->id.'_links'][$clnk->id] == 1) ? ' checked ' : '';
                                        @endphp
                                        <div class="col-span-12 sm:col-span-3">
                                            <div class="form-check form-switch">
                                                <input {{ $childAttr }} id="permission_child_internal_links_{{ $clnk->id }}" class="form-check-input" type="checkbox" value="1" name="permission[parent_child_{{$lnk->id}}_links][{{ $clnk->id }}]">
                                                <label class="form-check-label ml-4" for="permission_child_internal_links_{{ $clnk->id }}">{{ $clnk->name }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        
        <div class="intro-y box p-5 mt-5 {{ (in_array(auth()->user()->id, [1, 7, 8]) ? '' : 'magicBox') }}">
            <div class="grid grid-cols-12 gap-0 items-center">
                <div class="col-span-6">
                    <div class="font-medium text-base">Accounts Privileges</div>
                </div>
                <div class="col-span-6 text-right relative">
                    <button type="submit" class="btn btn-primary shadow-md mr-2"><i data-lucide="save-all" class="w-4 h-4 mr-2"></i>Save All</button>
                </div>
            </div>
            <div class="intro-y mt-5">
                <div class="grid grid-cols-12 gap-4 items-center">
                    <div class="col-span-12 sm:col-span-3">
                        <div class="form-check form-switch">
                            <input {{ (isset($priv['acc_privilege']['access_account']) && $priv['acc_privilege']['access_account'] == 1 ? 'checked' : '') }} id="permission_acc_privilege_1" class="form-check-input" type="checkbox" value="1" name="permission[acc_privilege][access_account]">
                            <label class="form-check-label ml-4" for="permission_acc_privilege_1">Account's Privilege</label>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-3 accountsUserTypeWrap" style="display: {{ (isset($priv['acc_privilege']['access_account']) && $priv['acc_privilege']['access_account'] == 1 ? 'block' : 'none') }};">
                        <select id="permission_acc_privilege_2" name="permission[acc_privilege][access_account_type]" class="form-control w-auto">
                            <option value="">Please Select</option>
                            <option {{ (isset($priv['acc_privilege']['access_account_type']) && $priv['acc_privilege']['access_account_type'] == 1 ? 'selected' : '') }} value="1">Admin</option>
                            <option {{ (isset($priv['acc_privilege']['access_account_type']) && $priv['acc_privilege']['access_account_type'] == 2 ? 'selected' : '') }} value="2">User</option>
                            <option {{ (isset($priv['acc_privilege']['access_account_type']) && $priv['acc_privilege']['access_account_type'] == 3 ? 'selected' : '') }} value="3">Audit</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>


    </form>

    <!-- BEGIN: Success Modal Content -->
    <div id="successModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="check-circle" class="w-16 h-16 text-success mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 successModalTitle"></div>
                        <div class="text-slate-500 mt-2 successModalDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->

    <!-- BEGIN: Warning Modal Content -->
    <div id="warningModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="alert-octagon" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 warningModalTitle"></div>
                        <div class="text-slate-500 mt-2 warningModalDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Warning Modal Content -->
@endsection

@section('script')
    @vite('resources/js/employee-privilege.js')
@endsection