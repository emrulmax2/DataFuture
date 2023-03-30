@extends('../layout/' . $layout)

@section('subhead')
    <title>Tabulator - Midone - Tailwind HTML Admin Template</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Welcome! </h2>
        @if ($user->email_verified_at != NULL)
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <button class="btn btn-primary shadow-md mr-2">Apply For College</button>
        </div>
        @endif
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
                    <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Your email address wasn't verified.</label>
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
@endsection
