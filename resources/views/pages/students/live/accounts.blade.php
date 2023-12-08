@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Profile Review of <u><strong>{{ $student->title->name.' '.$student->first_name.' '.$student->last_name }}</strong></u></h2>
    </div>

    <!-- BEGIN: Profile Info -->
    @include('pages.students.live.show-info')
    <!-- END: Profile Info -->

    <div class="intro-y box p-5 mt-5">
        <div class="grid grid-cols-12 gap-0 items-center">
            <div class="col-span-6">
                <div class="font-medium text-base">Student Accounts</div>
            </div>
            <div class="col-span-6 text-right relative">
                <button data-tw-toggle="modal" data-tw-target="#addAgreementModal" type="button" class="btn btn-primary shadow-md"><i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i>Add Agreement</button>
            </div>
        </div>
    </div>

    @if(!empty($agreements) && $agreements->count() > 0)
        @foreach($agreements as $agr)
            <div class="intro-y box p-5 mt-5">
                <div class="grid grid-cols-12 gap-0 items-center">
                    <div class="col-span-6">
                        <div class="font-medium text-base">Agreements Details for <u class="text-success">Year {{ $agr->year }}</u></div>
                    </div>
                    <div class="col-span-6 text-right relative">
                        <button data-id="{{ $agr->id }}" data-tw-toggle="modal" data-tw-target="#editAgreementModal" type="button" class="edit_agreement_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 mr-1"><i data-lucide="Pencil" class="w-4 h-4"></i></button>
                        <button data-agr-id="{{ $agr->id }}" data-tw-toggle="modal" data-tw-target="#addInstallmentModal" type="button" class="add_attendance_btn btn btn-linkedin shadow-md"><i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i>Add Installment</button>
                    </div>
                </div>
                <div class="intro-y mt-5">
                    <div class="grid grid-cols-12 gap-2">
                        <div class="col-span-12 sm:col-span-4">
                            <div class="grid grid-cols-12 gap-0 gap-x-3">
                                <div class="col-span-4 text-slate-500 font-medium">Date</div>
                                <div class="col-span-8 font-medium">
                                    {{ (!empty($agr->date) ? date('jS M, Y', strtotime($agr->date)) : '---') }}
                                    {!! (isset($agr->user->employee->full_name) && !empty($agr->user->employee->full_name) ? 'by '.$agr->user->employee->full_name : '') !!}
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-4">
                            <div class="grid grid-cols-12 gap-0 gap-x-3">
                                <div class="col-span-4 text-slate-500 font-medium">SLC Course Code</div>
                                <div class="col-span-8 font-medium">
                                    {{ (!empty($agr->slc_coursecode) ? $agr->slc_coursecode : '---') }}
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-4">
                            <div class="grid grid-cols-12 gap-0 gap-x-3">
                                <div class="col-span-4 text-slate-500 font-medium">Self Funded</div>
                                <div class="col-span-8 font-medium">
                                    {!! (isset($agr->is_self_funded) && $agr->is_self_funded == 1 ? '<span class="btn btn-success px-2 py-0 text-white rounded-0">Yes</span>' : '<span class="btn btn-danger px-2 py-0 text-white rounded-0">No</span>') !!}
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-4">
                            <div class="grid grid-cols-12 gap-0 gap-x-3">
                                <div class="col-span-4 text-slate-500 font-medium">Fees</div>
                                <div class="col-span-8 font-medium">
                                    {{ (!empty($agr->fees) ? '£'.number_format($agr->fees, 2) : '£0.00') }}
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-4">
                            <div class="grid grid-cols-12 gap-0 gap-x-3">
                                <div class="col-span-4 text-slate-500 font-medium">Discount</div>
                                <div class="col-span-8 font-medium">
                                    {{ (!empty($agr->discount) ? '£'.number_format($agr->discount, 2) : '£0.00') }}
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-4">
                            <div class="grid grid-cols-12 gap-0 gap-x-3">
                                <div class="col-span-4 text-slate-500 font-medium">Total</div>
                                <div class="col-span-8 font-medium">
                                    {{ (!empty($agr->total) ? '£'.number_format($agr->total, 2) : '£0.00') }}
                                </div>
                            </div>
                        </div>
                        
                        @if(!empty($agr->note))
                        <div class="col-span-12"></div>
                        <div class="col-span-12 sm:col-span-4">
                            <div class="grid grid-cols-12 gap-0 gap-x-3">
                                <div class="col-span-4 text-slate-500 font-medium">Total</div>
                                <div class="col-span-8 font-medium">
                                    {{ (!empty($agr->note) ? $agr->note : '') }}
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                    <div class="installmentAndPaymentWrap mt-7">
                        <div class="grid grid-cols-12 gap-0 gap-x-4">
                            <div class="col-span-12 sm:col-span-4">
                                
                            </div>
                            <div class="col-span-12 sm:col-span-8">

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @endif

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
                        <button type="button" data-action="DISMISS" class="successCloser btn btn-primary w-24">Ok</button>
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
                        <button type="button" data-action="DISMISS" class="warningCloser btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Warning Modal Content -->

    <!-- BEGIN: Delete Confirm Modal Content -->
    <div id="confirmModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 confModTitle">Are you sure?</div>
                        <div class="text-slate-500 mt-2 confModDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" class="disAgreeWith btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                        <button type="button" data-recordid="0" data-status="none" data-student="{{ $student->id }}" class="agreeWith btn btn-danger w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Delete Confirm Modal Content -->
@endsection

@section('script')
    @vite('resources/js/student-global.js')
    @vite('resources/js/student-slc-agreement.js')
    @vite('resources/js/student-slc-installment.js')
@endsection