@extends('../layout/' . $layout)

@section('head')
    <title>Applicant Password Change For London Churchill College</title>
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
                    <div class="-intro-x text-white font-medium text-4xl leading-tight mt-10">Change Password</div>
                    {{-- <div class="-intro-x mt-5 text-lg text-white text-opacity-70 dark:text-slate-400">Manage all your e-commerce accounts in one place</div> --}}
                </div>
            </div>
            <!-- END: Login Info -->
            <!-- BEGIN: Login Form -->
            <div class="h-screen xl:h-auto flex py-5 xl:py-0 my-10 xl:my-0">
                <div class="my-auto mx-auto xl:ml-20 bg-white dark:bg-darkmode-600 xl:bg-transparent px-5 sm:px-8 py-8 xl:p-0 rounded-md shadow-md xl:shadow-none w-full sm:w-3/4 lg:w-2/4 xl:w-auto">
                    <h2 class="intro-x font-bold text-2xl xl:text-3xl text-center xl:text-left">Change Passord</h2>
                    <div class="intro-x mt-2 text-slate-400 xl:hidden text-center">Reset your account</div>
                    <div class="intro-x mt-8">
                        <form id="register-form">
                            <input type="hidden" name="token" value="{{ $token }}">
                            <input type="hidden" name="email" value="{{ $email }}">
                            <input type="password" autocomplete="off" id="password" name="password" class="intro-x login__input form-control py-3 px-4 block mt-4" placeholder="Password">
                            <div id="error-password" class="login__input-error text-danger mt-2"></div>

                            <div class="intro-x w-full grid grid-cols-12 gap-4 h-1 mt-3">
                                <div id="strength-1" class="col-span-3 h-full rounded bg-slate-100 dark:bg-darkmode-800"></div>
                                <div id="strength-2" class="col-span-3 h-full rounded bg-slate-100 dark:bg-darkmode-800"></div>
                                <div id="strength-3" class="col-span-3 h-full rounded bg-slate-100 dark:bg-darkmode-800"></div>
                                <div id="strength-4" class="col-span-3 h-full rounded bg-slate-100 dark:bg-darkmode-800"></div>
                            </div>
                            <!-- BEGIN: Custom Tooltip Toggle -->
                            <a href="javascript:;" data-theme="light" data-tooltip-content="#custom-content-tooltip" data-trigger="click" class="tooltip intro-x text-slate-500 block mt-2 text-xs sm:text-sm" title="What is a secure password?">What is a secure password?</a>
                            <!-- END: Custom Tooltip Toggle -->
                            <!-- BEGIN: Custom Tooltip Content -->
                            <div class="tooltip-content">
                                <div id="custom-content-tooltip" class="relative flex items-center py-1">
                                    <ul class="list-disc mt-5 ml-4 text-md dark:text-slate-400">
                                        <li class="">
                                            <span class="low-upper-case">
                                                <i class="fas fa-circle" aria-hidden="true"></i>
                                                &nbsp;Lowercase &amp; Uppercase
                                            </span>
                                        </li>
                                        <li class="">
                                            <span class="one-number">
                                                <i class="fas fa-circle" aria-hidden="true"></i>
                                                &nbsp;Number (0-9)
                                            </span> 
                                        </li>
                                        <li class="">
                                            <span class="one-special-char">
                                                <i class="fas fa-circle" aria-hidden="true"></i>
                                                &nbsp;Special Character (!@#$%^&*)
                                            </span>
                                        </li>
                                        <li class="">
                                            <span class="eight-character">
                                                <i class="fas fa-circle" aria-hidden="true"></i>
                                                &nbsp;Atleast 8 Character
                                            </span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <!-- END: Custom Tooltip Content -->
                            <input type="password" id="password_confirmation" name="password_confirmation" class="intro-x login__input form-control py-3 px-4 block mt-4" placeholder="Password Confirmation">
                            <div id="error-confirmation" class="login__input-error text-danger mt-2"></div>
                        </form>
                    </div>
                    <div class="intro-x mt-5 xl:mt-8 text-center xl:text-left">
                        <button id="btn-login" class="btn btn-primary py-3 px-4 w-full xl:mr-3 align-top">Update Passwored</button>
                    </div>
                </div>
            </div>
            <!-- END: Login Form -->
            <!-- BEGIN: Notification Content -->
            <div id="success-notification-content" class="toastify-content hidden flex">
                <i class="text-success" data-lucide="check-circle"></i>
                <div class="ml-4 mr-4">
                    <div class="font-medium">Password Updated!</div>
                    <div class="text-slate-500 mt-1">Your password updated. Please login</div>
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
                $('#register-form').find('.login__input').removeClass('border-danger')
                $('#register-form').find('.login__input-error').html('')

                // Post form
                let myform = document.getElementById("register-form");
                let formData = new FormData(myform);
                // Loading state
                $('#btn-login').html('<i data-loading-icon="oval" data-color="white" class="w-5 h-5 mx-auto"></i>')
                tailwind.svgLoader()
                await helper.delay(500)

                axios.post(route('applicant.reset.password.post'), formData).then(res => {
                    
                    $("#success-notification-toggle").trigger('click')
                    $('#btn-login').html('Update Passwored')
                    setInterval(function(){
                        location.href = '/applicant/login'
                    }, 2000);
                    

                }).catch(err => {
                    $('#register-form').find('.login__input').removeClass('border-danger')
                    $('#register-form').find('.login__input-error').html('')
                    $('#btn-login').html('Update Passwored')
                    if (err.response.data.message != 'Password Could not Updated.') {
                        for (const [key, val] of Object.entries(err.response.data.errors)) {
                            $(`#${key}`).addClass('border-danger')
                            $(`#error-${key}`).html(val)
                        }
                    } else {
                        
                        $(`#password`).addClass('border-danger')
                        $(`#error-password`).html(err.response.data.message)
                    }
                })
            }

            $('#register-form').on('keyup', function(e) {
                if (e.keyCode === 13) {
                    resetForm()
                }
            })

            $('#btn-login').on('click', function() {
                resetForm()
            })

            $('#password').on('keyup', function(e) {
                let totalText = this.value
                let strenghtTips = checkPasswordStrength(totalText)
                const box1 = document.getElementById('strength-1');
                const box2 = document.getElementById('strength-2');
                const box3 = document.getElementById('strength-3');
                const box4 = document.getElementById('strength-4');

                switch (strenghtTips) {
                    case 1:
                            box1.classList.remove('bg-slate-100','dark:bg-darkmode-800')
                            box1.classList.add('bg-danger');
                            break;
                    case 2: 
                            box2.classList.remove('bg-slate-100','dark:bg-darkmode-800')
                            box2.classList.add('bg-warning');
                            break;
                    case 3: 
                            box3.classList.remove('bg-slate-100','dark:bg-darkmode-800')
                            box3.classList.add('bg-success');
                            break;
                    case 4: 
                    case 5: 
                    case 6: 
                    case 7: 
                    case 8: 
                    case 9: 
                            box4.classList.remove('bg-slate-100','dark:bg-darkmode-800')
                            box4.classList.add('bg-success');
                            break;
                    default:
                            box1.classList.remove('bg-danger', 'bg-warning','bg-success','bg-slate-100','dark:bg-darkmode-800');
                            box2.classList.remove('bg-danger', 'bg-warning','bg-success','bg-slate-100','dark:bg-darkmode-800');
                            box3.classList.remove('bg-danger', 'bg-warning','bg-success','bg-slate-100','dark:bg-darkmode-800');
                            box4.classList.remove('bg-danger', 'bg-warning','bg-success','bg-slate-100','dark:bg-darkmode-800');
                            
                            box1.classList.add('bg-slate-100','dark:bg-darkmode-800');
                            box2.classList.add('bg-slate-100','dark:bg-darkmode-800');
                            box3.classList.add('bg-slate-100','dark:bg-darkmode-800');
                            box4.classList.add('bg-slate-100','dark:bg-darkmode-800');
                            break;
                }
            })
        })()
        function checkPasswordStrength(password) {
            // Initialize variables
            let strength = 0;
            let tips = "";
            //let lowUpperCase = document.querySelector(".low-upper-case i");
  
            //let number = document.querySelector(".one-number i");
            //let specialChar = document.querySelector(".one-special-char i");
            //let eightChar = document.querySelector(".eight-character i");

            //If password contains both lower and uppercase characters
            if (password.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/)) {
                strength += 1;
                //lowUpperCase.classList.remove('fa-circle');
                //lowUpperCase.classList.add('fa-check');
            } else {
                //lowUpperCase.classList.add('fa-circle');
                //lowUpperCase.classList.remove('fa-check');
            }
            //If it has numbers and characters
            if (password.match(/([0-9])/)) {
                strength += 1;
                //number.classList.remove('fa-circle');
                //number.classList.add('fa-check');
            } else {
                //number.classList.add('fa-circle');
                //number.classList.remove('fa-check');
            }
            //If it has one special character
            if (password.match(/([!,%,&,@,#,$,^,*,?,_,~])/)) {
                strength += 1;
                //specialChar.classList.remove('fa-circle');
                //specialChar.classList.add('fa-check');
            } else {
                //specialChar.classList.add('fa-circle');
                //specialChar.classList.remove('fa-check');
            }
            //If password is greater than 7
            if (password.length > 7) {
                strength += 1;
                //eightChar.classList.remove('fa-circle');
                //eightChar.classList.add('fa-check');
            } else {
                //eightChar.classList.add('fa-circle');
                //eightChar.classList.remove('fa-check');   
            }
           
            // Return results
            if (strength < 2) {
                return strength;
            } else if (strength === 2) {
                return strength;
            } else if (strength === 3) {
                return strength;
            } else {
                return strength;
            }
            }
    </script>
@endsection
