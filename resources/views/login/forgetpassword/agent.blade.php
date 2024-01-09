@extends('../layout/' . $layout)

@section('head')
    <title>Agent Forget Password Page For London Churchill College</title>
@endsection

@section('content')
    <div class="container sm:px-10">
        <div class="block xl:grid grid-cols-2 gap-4">
            <!-- BEGIN: Login Info -->
            <div class="hidden xl:flex flex-col min-h-screen">
                <a href="" class="-intro-x flex items-center pt-5">
                    <img alt="London Churchill College" class="w-48" src="https://sms.londonchurchillcollege.ac.uk/sms_new_copy_2/images/logo-with-blue-color-3.svg" />
                </a>
                <div class="my-auto">
                    <img alt="Icewall Tailwind HTML Admin Template" class="-intro-x w-1/2 -mt-16" src="{{ asset('build/assets/images/forgot-password-pana.svg') }}">
                    <div class="-intro-x text-white font-medium text-4xl leading-tight mt-10">Reset your account.</div>
                    {{-- <div class="-intro-x mt-5 text-lg text-white text-opacity-70 dark:text-slate-400">Manage all your e-commerce accounts in one place</div> --}}
                </div>
            </div>
            <!-- END: Login Info -->
            <!-- BEGIN: Login Form -->
            <div class="h-screen xl:h-auto flex py-5 xl:py-0 my-10 xl:my-0">
                <div class="my-auto mx-auto xl:ml-20 bg-white dark:bg-darkmode-600 xl:bg-transparent px-5 sm:px-8 py-8 xl:p-0 rounded-md shadow-md xl:shadow-none w-full sm:w-3/4 lg:w-2/4 xl:w-auto">
                    <h2 class="intro-x font-bold text-2xl xl:text-3xl text-center xl:text-left">Forget Passord</h2>
                    <div class="intro-x mt-2 text-slate-400 xl:hidden text-center">Reset your account</div>
                    <div class="intro-x mt-8">
                        <form id="login-form">
                            <input id="email" type="text" class="intro-x login__input form-control py-3 px-4 block" placeholder="Email">
                            <div id="error-email" class="login__input-error text-danger mt-2"></div>
                        </form>
                    </div>
                    
                    <div class="intro-x flex text-slate-600 dark:text-slate-500 text-xs sm:text-sm mt-4">
                        <div class="flex items-center mr-auto">
                            <label class="cursor-pointer select-none" for="remember-me">Aready Have an account? Please <a href="{{ route('agent.login') }}">Sign In</a></label>
                        </div>
                    </div>
                    <div class="intro-x mt-5 xl:mt-8 text-center xl:text-left">
                        <button id="btn-login" class="btn btn-primary py-3 px-4 w-full xl:mr-3 align-top">Send Password Email</button>
                    </div>
                </div>
            </div>
            <!-- END: Login Form -->
            <!-- BEGIN: Notification Content -->
            <div id="success-notification-content" class="toastify-content hidden flex">
                <i class="text-success" data-lucide="check-circle"></i>
                <div class="ml-4 mr-4">
                    <div class="font-medium">Reset Password Email Sent!</div>
                    <div class="text-slate-500 mt-1">A reset password email sent to your email address.</div>
                </div>
            </div>
            <!-- END: Notification Content -->                                
            <button id="success-notification-toggle" class="btn btn-primary hidden">Show Notification</button>
        </div>
    </div>
@endsection

@section('script')
    <script type="module">
        (function () {
            async function resetForm() {
                // Reset state
                $('#login-form').find('.login__input').removeClass('border-danger')
                $('#login-form').find('.login__input-error').html('')

                // Post form
                let email = $('#email').val()

                // Loading state
                $('#btn-login').html('<i data-loading-icon="oval" data-color="white" class="w-5 h-5 mx-auto"></i>')
                tailwind.svgLoader()
                await helper.delay(300)

                axios.post(`forget-password`, {
                    email: email
                }).then(res => {
                    $("#success-notification-toggle").trigger('click')
                    $('#btn-login').html('Send Passwored Email Link')
                    setInterval(function(){
                         location.href = '/agent/login'
                    }, 3000);
                    

                }).catch(err => {
                    $('#btn-login').html('Send Passwored Email Link')
                    if (err.response.data.message != 'No User Found.') {
                        for (const [key, val] of Object.entries(err.response.data.errors)) {
                            $(`#${key}`).addClass('border-danger')
                            $(`#error-${key}`).html(val)
                        }
                    } else {
                        $(`#email`).addClass('border-danger')
                        $(`#error-email`).html(err.response.data.message)
                    }
                })
            }

            $('#login-form').on('keyup', function(e) {
                if (e.keyCode === 13) {
                    resetForm()
                }
            })

            $('#btn-login').on('click', function() {
                resetForm()
            })
        })()
    </script>
@endsection
