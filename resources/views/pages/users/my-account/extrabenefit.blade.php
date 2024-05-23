@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}- </title>
@endsection

@section('subcontent')

    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">My HR</h2>
    </div>

    <!-- BEGIN: Profile Info -->
    @include('pages.users.my-account.show-info')
    <!-- END: Profile Info -->

    <div class="intro-y mt-5">
        <div class="intro-y box p-5">
            <div class="grid grid-cols-12 gap-0 items-center">
                <div class="col-span-12">
                    <img class="block w-full h-auto " src="{{ asset('build/assets/images/hr/extra-benifit.png') }}" />
                </div>
                <div class="col-span-12" style="margin: 25px auto;">
                    <div class="col-span-12 mb-2">
                        <div class="font-normal text-base">To access the benefits, please register at workplaceextras.com by following the link below:</div>
                    </div>
                    <div class="col-span-12 mb-2 text-center">
                        <a class="transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&:hover:not(:disabled)]:bg-opacity-90 [&:hover:not(:disabled)]:border-opacity-90 [&:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed bg-[#0077b5] border-[#0077b5] text-white dark:border-[#0077b5] mb-2 mr-2 w-48" href="https://app.workplaceextras.com/employee-register/fd1b3ab">Registration Link</a>
                    </div>
                    <div class="col-span-12 mb-2">
                        <div class="font-normal text-base">After submitting the registration form, please allow up to 48 working hours for your account to be activated. You will receive a confirmation email once activation is complete.</div>
                    </div>
                    <div class="col-span-12 mb-2">
                        <div class="font-normal text-base"> <span class="text-danger">*</span> Please note that you will need your college payroll/employee number and your college email address for registration. Your payroll/employee number can be found on your payslip.</div>
                    </div>
                    
                    <div class="col-span-12 mb-2">
                        <div class="font-normal text-base"> <span class="text-danger">**</span> These benefits are for personal use only. Any commercial use will be subject to disciplinary action</div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
    
 
@endsection