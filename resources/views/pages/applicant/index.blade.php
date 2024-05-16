@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Welcome! </h2>
        @if ($user->email_verified_at != NULL)
         @if(!isset($applicant))
            <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
                <a href="{{ route('applicant.application') }}" class="btn btn-primary shadow-md mr-2">Apply For a Course</a>
            </div>
         @endif
        @endif
    </div>
    <div class="intro-y box p-5 mt-5">
        @if (session('applicantSubmission'))
            <div class="alert alert-success-soft alert-dismissible show flex items-center mb-2" role="alert">
                <i data-lucide="check-circle" class="w-6 h-6 mr-2"></i> {{ Session::get('applicantSubmission') }}
                <button type="button" class="btn-close" data-tw-dismiss="alert" aria-label="Close">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
            @php session()->forget('applicantSubmission'); @endphp
        @endif

        <div class="overflow-x-auto scrollbar-hidden">
            <div id="applicantApplicantionList" class="mt-5 table-report table-report--tabulator"></div>
        </div>
    </div>
    @if (session('verifymessage'))
        <!-- BEGIN: Notification Content -->
        <div id="success-notification-content" class="toastify-content hidden flex">
            <i class="text-success" data-lucide="check-circle"></i>
            <div class="ml-4 mr-4">
                <div class="font-medium">Email Sent!</div>
                <div class="text-slate-500 mt-1">{{ session('verifymessage') }}</div>
            </div>
        </div>
        <!-- END: Notification Content -->
        <!-- BEGIN: Notification Toggle -->
        <button id="success-notification-toggle" class="btn hidden btn-primary">Show Notification</button>
        <!-- END: Notification Toggle -->
    @endif
    @if ($user->email_verified_at == NULL)
    
    <div class="intro-y box p-5 mt-5">
        <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
                   
            <form id="resendverification" method="post" action="{{ route('verification.send') }}" class="xl:flex sm:mr-auto" >
                @csrf
                <div class="sm:flex items-center sm:mr-4">
                    <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">A verification email has been sent to your inbox. Kindly go to your email address and click the verify button to confirm your email.

                        <br/>In case you don't see the email in your inbox, please check your Junk/Spam folder. Thank you.</label>
                </div>
                <div class="flex justify-end mx-auto sm:mt-0">
                    <button id="emailverification" type="submit" class="btn btn-dark w-1/2 sm:w-auto mr-2">
                        <i data-lucide="send" class="w-4 h-4 mr-2"></i> Click Here to Resend Verification Email
                    </button>
                </div>
            </form>
        </div>

    </div>

    @endif

    <!-- BEGIN: HTML Table Data -->

        <!-- ALL APPLICANT BASE DATA WILL BE HERE -->
  
    <!-- End: HTML Table Data --> 

@endsection


@section('script')
    <script type="module">
        (function () {
            if($('#success-notification-toggle').length>0) {
                $("#success-notification-toggle").trigger('click')
            }
        })()
    </script>
    @vite('resources/js/applicant-dahsboard.js')
@endsection
