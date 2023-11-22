@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">{{ $subtitle }}</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('holiday.year') }}" class="add_btn btn btn-primary shadow-md mr-2">Back To Holiday Years</a>
        </div>
    </div>

    <!-- BEGIN: Settings Page Content -->
    <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12 lg:col-span-4 2xl:col-span-3 flex lg:block flex-col-reverse">
            <!-- BEGIN: Profile Info -->
            @include('pages.settings.sidebar')
            <!-- END: Profile Info -->
        </div>

        <div class="col-span-12 lg:col-span-8 2xl:col-span-9">
            <!-- BEGIN: Display Information -->
            <form method="POST" action="#" id="hrConditionForm">
                <div class="grid grid-cols-12 gap-4">
                    <div class="col-span-12 sm:col-span-6">
                        <div class="intro-y box lg:mt-5">
                            <div class="flex items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                                <h2 class="font-medium text-base mr-auto">Clock In Conditions</h2>
                                <button type="submit" id="updateHRC1" class="updateHRC btn btn-primary ml-auto shadow-md w-auto">
                                    Update Settings
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
                            <div class="p-5">
                                <table class="table table-bordered table-sm">
                                    <thead>
                                        <tr>
                                            <th>Time Frame</th>
                                            <th>Minuites</th>
                                            <th>Notify</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><strong>Upto</strong></td>
                                            <td>
                                                <input type="number" value="{{ (isset($clockIn_1->minutes) && !empty($clockIn_1->minutes) ? $clockIn_1->minutes : '' ) }}" class="form-control w-full" name="clockin[1][time]"/>
                                            </td>
                                            <td>
                                                <div class="form-check form-switch m-0">
                                                    <input class="form-check-input m-0" {{ (isset($clockIn_1->notify) && $clockIn_1->notify == 1 ? 'checked' : '' ) }} value="1" name="clockin[1][notify]" type="checkbox">
                                                </div>
                                            </td>
                                            <td>
                                                <select class="form-control w-full" name="clockin[1][action]">
                                                    <option value="0">Select Actions</option>
                                                    <option {{ (isset($clockIn_1->action) && $clockIn_1->action == 1 ? 'selected' : '' ) }} value="1">Contract</option>
                                                    <option {{ (isset($clockIn_1->action) && $clockIn_1->action == 2 ? 'selected' : '' ) }} value="2">Actual</option>
                                                    <option {{ (isset($clockIn_1->action) && $clockIn_1->action == 3 ? 'selected' : '' ) }} value="3">Blank</option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Till</strong></td>
                                            <td>
                                                <input type="number" value="{{ (isset($clockIn_2->minutes) && !empty($clockIn_2->minutes) ? $clockIn_2->minutes : '' ) }}" class="form-control w-full" name="clockin[2][time]"/>
                                            </td>
                                            <td>
                                                <div class="form-check form-switch m-0">
                                                    <input class="form-check-input m-0"  {{ (isset($clockIn_2->notify) && $clockIn_2->notify == 1 ? 'checked' : '' ) }} value="1" name="clockin[2][notify]" type="checkbox">
                                                </div>
                                            </td>
                                            <td>
                                                <select class="form-control w-full" name="clockin[2][action]">
                                                    <option value="0">Select Actions</option>
                                                    <option {{ (isset($clockIn_2->action) && $clockIn_2->action == 1 ? 'selected' : '' ) }} value="1">Contract</option>
                                                    <option {{ (isset($clockIn_2->action) && $clockIn_2->action == 2 ? 'selected' : '' ) }} value="2">Actual</option>
                                                    <option {{ (isset($clockIn_2->action) && $clockIn_2->action == 3 ? 'selected' : '' ) }} value="3">Blank</option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>After</strong></td>
                                            <td>
                                                <input type="number" value="{{ (isset($clockIn_3->minutes) && !empty($clockIn_3->minutes) ? $clockIn_3->minutes : '' ) }}" class="form-control w-full" name="clockin[3][time]"/>
                                            </td>
                                            <td>
                                                <div class="form-check form-switch m-0">
                                                    <input class="form-check-input m-0" {{ (isset($clockIn_3->notify) && $clockIn_3->notify == 1 ? 'checked' : '' ) }} value="1" name="clockin[3][notify]" type="checkbox">
                                                </div>
                                            </td>
                                            <td>
                                                <select class="form-control w-full" name="clockin[3][action]">
                                                    <option value="0">Select Actions</option>
                                                    <option {{ (isset($clockIn_3->action) && $clockIn_3->action == 1 ? 'selected' : '' ) }} value="1">Contract</option>
                                                    <option {{ (isset($clockIn_3->action) && $clockIn_3->action == 2 ? 'selected' : '' ) }} value="2">Actual</option>
                                                    <option {{ (isset($clockIn_3->action) && $clockIn_3->action == 3 ? 'selected' : '' ) }} value="3">Blank</option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>No Clock In</strong></td>
                                            <td>
                                                <input type="number" value="{{ (isset($clockIn_4->minutes) && !empty($clockIn_4->minutes) ? $clockIn_4->minutes : '' ) }}" class="form-control w-full" name="clockin[4][time]"/>
                                            </td>
                                            <td>
                                                <div class="form-check form-switch m-0">
                                                    <input class="form-check-input m-0" {{ (isset($clockIn_4->notify) && $clockIn_4->notify == 1 ? 'checked' : '' ) }} value="1" name="clockin[4][notify]" type="checkbox">
                                                </div>
                                            </td>
                                            <td>
                                                <select class="form-control w-full" name="clockin[4][action]">
                                                    <option value="0">Select Actions</option>
                                                    <option {{ (isset($clockIn_4->action) && $clockIn_4->action == 1 ? 'selected' : '' ) }} value="1">Contract</option>
                                                    <option {{ (isset($clockIn_4->action) && $clockIn_4->action == 2 ? 'selected' : '' ) }} value="2">Actual</option>
                                                    <option {{ (isset($clockIn_4->action) && $clockIn_4->action == 3 ? 'selected' : '' ) }} value="3">Blank</option>
                                                </select>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                    <div class="intro-y box lg:mt-5">
                            <div class="flex items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                                <h2 class="font-medium text-base mr-auto">Clock Out Conditions</h2>
                                <button type="submit" id="updateHRC2" class="updateHRC btn btn-primary ml-auto shadow-md w-auto">
                                    Update Settings
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
                            <div class="p-5">
                                <table class="table table-bordered table-sm">
                                    <thead>
                                        <tr>
                                            <th>Time Frame</th>
                                            <th>Minuites</th>
                                            <th>Notify</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><strong>Before</strong></td>
                                            <td>
                                                <input type="number" value="{{ (isset($clockOut_1->minutes) && !empty($clockOut_1->minutes) ? $clockOut_1->minutes : '' ) }}" class="form-control w-full" name="clockout[1][time]"/>
                                            </td>
                                            <td>
                                                <div class="form-check form-switch m-0">
                                                    <input class="form-check-input m-0" {{ (isset($clockOut_1->notify) && $clockOut_1->notify == 1 ? 'checked' : '' ) }} value="1" name="clockout[1][notify]" type="checkbox">
                                                </div>
                                            </td>
                                            <td>
                                                <select class="form-control w-full" name="clockout[1][action]">
                                                    <option value="0">Select Actions</option>
                                                    <option {{ (isset($clockOut_1->action) && $clockOut_1->action == 1 ? 'selected' : '' ) }} value="1">Contract</option>
                                                    <option {{ (isset($clockOut_1->action) && $clockOut_1->action == 2 ? 'selected' : '' ) }} value="2">Actual</option>
                                                    <option {{ (isset($clockOut_1->action) && $clockOut_1->action == 3 ? 'selected' : '' ) }} value="3">Blank</option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>After</strong></td>
                                            <td>
                                                <input type="number" value="{{ (isset($clockOut_2->minutes) && !empty($clockOut_2->minutes) ? $clockOut_2->minutes : '' ) }}" class="form-control w-full" name="clockout[2][time]"/>
                                            </td>
                                            <td>
                                                <div class="form-check form-switch m-0">
                                                    <input class="form-check-input m-0" {{ (isset($clockOut_2->notify) && $clockOut_2->notify == 1 ? 'checked' : '' ) }} value="1" name="clockout[2][notify]" type="checkbox">
                                                </div>
                                            </td>
                                            <td>
                                                <select class="form-control w-full" name="clockout[2][action]">
                                                    <option value="0">Select Actions</option>
                                                    <option {{ (isset($clockOut_2->action) && $clockOut_2->action == 1 ? 'selected' : '' ) }} value="1">Contract</option>
                                                    <option {{ (isset($clockOut_2->action) && $clockOut_2->action == 2 ? 'selected' : '' ) }} value="2">Actual</option>
                                                    <option {{ (isset($clockOut_2->action) && $clockOut_2->action == 3 ? 'selected' : '' ) }} value="3">Blank</option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>No Clock Out</strong></td>
                                            <td>
                                                <input type="number" value="{{ (isset($clockOut_3->minutes) && !empty($clockOut_3->minutes) ? $clockOut_3->minutes : '' ) }}" class="form-control w-full" name="clockout[3][time]"/>
                                            </td>
                                            <td>
                                                <div class="form-check form-switch m-0">
                                                    <input class="form-check-input m-0" {{ (isset($clockOut_3->notify) && $clockOut_3->notify == 1 ? 'checked' : '' ) }} value="1" name="clockout[3][notify]" type="checkbox">
                                                </div>
                                            </td>
                                            <td>
                                                <select class="form-control w-full" name="clockout[3][action]">
                                                    <option value="0">Select Actions</option>
                                                    <option {{ (isset($clockOut_3->action) && $clockOut_3->action == 1 ? 'selected' : '' ) }} value="1">Contract</option>
                                                    <option {{ (isset($clockOut_3->action) && $clockOut_3->action == 2 ? 'selected' : '' ) }} value="2">Actual</option>
                                                    <option {{ (isset($clockOut_3->action) && $clockOut_3->action == 3 ? 'selected' : '' ) }} value="3">Blank</option>
                                                </select>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Settings Page Content -->

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
@endsection

@section('script')
    @vite('resources/js/settings.js')
    @vite('resources/js/hr-conditions.js')
@endsection