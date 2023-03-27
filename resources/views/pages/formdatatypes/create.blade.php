@extends('../layout/' . $layout)

@section('subhead')
    <title>CRUD Form - Midone - Tailwind HTML Admin Template</title>
@endsection
<script src="https://unpkg.com/imask"></script>
@section('subcontent')
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Form Data Types Layout</h2>
    </div>
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12">
            <!-- BEGIN: Form Layout -->
            <form id="datatypeForm" action="#" method = "POST">
                <div class="intro-y box p-5">
                    <div>
                        <label for="formdataTypeText" class="form-label">Text</label>
                        <input id="formdataTypeText" type="text" name="textInput" class="regexp-mask form-control w-full" placeholder="Input text">
                    </div>
                    <div class="mt-3">
                        <label for="formdataTypeNumber" class="form-label">Currency</label>
                        <input id="formdataTypeNumber" type="text" name="numberInput" class="form-control w-full" placeholder="Currency">
                    </div>
                    <div class="mt-3">
                        <label for="formdataTypeSelect" class="form-label">Select Option</label>
                        <select id="formdataTypeSelect" name="selectOption" class="form-control">
                            <option value="">Please Select</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                        </select>
                    </div>
                    <div class="mt-3">
                        <label for="formdataTypeCheckbox" class="cursor-pointer select-none">Checkbox</label>
                        <input id="formdataTypeCheckbox" type="checkbox" name="checkboxInput" class="form-check-input border ml-3">                   
                    </div>
                    <div class="mt-3">
                        <label for="formdataTypeSwitch">Switch On/Off</label>
                        <div class="form-switch mt-2">
                            <input id="formdataTypeSwitch" type="checkbox" name="switchInput" class="form-check-input">
                        </div>
                    </div>
                    <div class="mt-3">
                        <label for="formdataTypeRadio">Radio Button</label>
                        <div class="mt-3 form-check mr-4">
                            <input id="condition-new" class="form-check-input" type="radio" name="horizontal_radio_button" value="first">
                            <label class="form-check-label" for="condition-new">First</label>
                        </div>
                        <div class="mt-3 form-check mr-4 mt-2 sm:mt-0">
                            <input id="condition-second" class="form-check-input" type="radio" name="horizontal_radio_button" value="second">
                            <label class="form-check-label" for="condition-second">Second</label>
                        </div>
                    </div>
                    <div class="mt-3">
                        <label for="formdataTypePhone" class="form-label">Phone</label>
                        <input id="formdataTypePhone" type="text" name="phone" class="form-control w-full" placeholder="+(000)0000000000">
                    </div>
                    <div class="mt-3">
                        <label for="formdataTypeEmail" class="form-label">Email</label>
                        <input id="formdataTypeEmail" type="text" name="email" class="intro-x datatype__input form-control py-3 px-4 block" placeholder="Email">
                        <div id="error-email" class="datatype__input-error text-danger mt-2"></div>
                    </div>
                    <div class="mt-3">
                        <label for="formdataTypeDate" class="form-label">Date Format</label>
                        <div class="absolute rounded-l w-10 h-10 flex items-center justify-center bg-slate-100 border text-slate-500 dark:bg-darkmode-700 dark:border-darkmode-800 dark:text-slate-400">
                            <i data-lucide="calendar" class="w-4 h-4"></i>
                        </div>
                        <input id="formdataTypeDate" type="text" name="dateformat" class="datepicker form-control pl-12" data-single-mode="true">
                    </div>
                    <div class="mt-3">
                        <label for="formdataTypeDaterange" class="form-label">Date Range Picker</label>
                        <input id="formdataTypeDaterange" type="text" name="daterange" data-daterange="true" class="datepicker form-control">
                    </div>
                    <div class="text-right mt-5">
                        <button type="button" class="btn btn-outline-secondary w-24 mr-1">Cancel</button>
                        <button type="submit" id="saveDataTypes" class="btn btn-primary w-24">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- BEGIN: Success Modal Content -->
    <div id="successModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="check-circle" class="w-16 h-16 text-success mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 successModalTitle">Good job!</div>
                        <div class="text-slate-500 mt-2 successModalDesc">You clicked the button!</div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Modal Content -->
@endsection

@section('script')
    @vite('resources/js/ckeditor-classic.js')
    <script>
        IMask(
            document.getElementById('formdataTypeNumber'),
            {
            mask: /^[1-9]\d{0,100}$/
            },
            document.getElementById('formdataTypePhone'),
            {
            mask: /^[1-9]\d{0,15}$/
            }
        );

        var emailField = document.getElementById('formdataTypeEmail');
        //document.querySelector("#formdataTypeEmail").addEventListener('input', e => {
        emailField.addEventListener('blur', function() {
            var reg = /^(?:[\w\!\#\$\%\&\'\*\+\-\/\=\?\^\`\{\|\}\~]+\.)*[\w\!\#\$\%\&\'\*\+\-\/\=\?\^\`\{\|\}\~]+@(?:(?:(?:[a-zA-Z0-9](?:[a-zA-Z0-9\-](?!\.)){0,61}[a-zA-Z0-9]?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9\-](?!$)){0,61}[a-zA-Z0-9]?)|(?:\[(?:(?:[01]?\d{1,2}|2[0-4]\d|25[0-5])\.){3}(?:[01]?\d{1,2}|2[0-4]\d|25[0-5])\]))$/;
            if (reg.test(emailField.value) == false) {
            $('#datatypeForm').find('.datatype__input').addClass('border-danger')
            $('#datatypeForm').find('.datatype__input-error').html('Please enter a valid email')
            return false;
            }  
            return true;                        
        });
    </script>
@endsection