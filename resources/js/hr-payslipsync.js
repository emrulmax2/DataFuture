import IMask from 'imask';
import xlsx from 'xlsx';
import { createIcons, icons } from 'lucide';
import { createElement, Plus, Minus } from 'lucide';
import Tabulator from 'tabulator-tables';
import TomSelect from 'tom-select';
import Dropzone from 'dropzone';
import Toastify from 'toastify-js';

('use strict');

(function () {
    let tomOptions = {
        plugins: {
            dropdown_input: {},
        },
        placeholder: 'Search Here...',
        persist: false,
        create: true,
        allowEmptyOption: true,
        maxOptions: null,
        onDelete: function (values) {
            return confirm(
                values.length > 1
                    ? 'Are you sure you want to remove these ' +
                          values.length +
                          ' items?'
                    : 'Are you sure you want to remove "' + values[0] + '"?'
            );
        },
    };

    let holiday_year = new TomSelect('#holiday_year', tomOptions);
    let holiday_month = new TomSelect('#holiday_month', tomOptions);
    let paySliptype = new TomSelect('#type', tomOptions);
    
    const successModal = tailwind.Modal.getOrCreateInstance(
        document.querySelector('#successModal')
    );
    const confirmModal = tailwind.Modal.getOrCreateInstance(
        document.querySelector('#confirmModal')
    );
    const warningModal = tailwind.Modal.getOrCreateInstance(
        document.querySelector('#warningModal')
    );

    const uploadSubmissionDocumentModal = tailwind.Modal.getOrCreateInstance(
        document.querySelector('#synPaySlipModal')
    );

    const uploadSubmissionDocumentModalEl =
        document.getElementById('synPaySlipModal');

    /* Start Dropzone */
    if ($('#uploadDocumentForm').length > 0) {
        let dzError = false;
        let errorData = [];
        Dropzone.autoDiscover = false;
        Dropzone.options.uploadDocumentForm = {
            autoProcessQueue: false,
            maxFiles: 90,
            acceptedFiles: '.zip',
            addRemoveLinks: true,
            thumbnailWidth: 25,
            thumbnailHeight: 25,
            /*accept: function(file, done) {
            if(!file.name.match(/[`!@#$%^&*+\-=\[\]{};':"\\|,<>\/?~]/)){
                alert("Invalid File Name");
                done('Invalid file name');
            }else { 
                done(); 
            }
        },*/
        };

        let options = {
            accept: (file, done) => {
                console.log('Uploaded');
                done();
            },
        };

        var drzn1 = new Dropzone('#uploadDocumentForm', options);

        drzn1.on('addedfile', function (file) {
            if (file.name.match(/[`!@#$%^&*+\=\[\]{};':"\\|,<>\/?~]/)) {
                $('#uploadDocumentModal .modal-content .uploadError').remove();
                $('#uploadDocumentModal .modal-content').prepend(
                    '<div class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! One of your selected file name contain validation error & that file has been removed.</div>'
                );
                createIcons({
                    icons,
                    'stroke-width': 1.5,
                    nameAttr: 'data-lucide',
                });
                drzn1.removeFile(file);

                setTimeout(function () {
                    $(
                        '#uploadDocumentModal .modal-content .uploadError'
                    ).remove();
                }, 5000);
            }
        });

        drzn1.on('maxfilesexceeded', (file) => {
            $('#uploadDocumentModal .modal-content .uploadError').remove();
            $('#uploadDocumentModal .modal-content').prepend(
                '<div class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Can not upload more than 10 files at a time.</div>'
            );
            drzn1.removeFile(file);
            setTimeout(function () {
                $('#uploadDocumentModal .modal-content .uploadError').remove();
            }, 2000);
        });

        drzn1.on('error', function (file, response) {
            dzError = true;
            errorData = response;
            console.log(response);
        });

        drzn1.on('success', function (file, response) {
            //console.log(response);
            return file.previewElement.classList.add('dz-success');
        });

        drzn1.on('complete', function (file) {
            //drzn1.removeFile(file);
        });

        drzn1.on('queuecomplete', function () {
            $('#uploadEmpDocBtn').removeAttr('disabled');
            document.querySelector('#uploadEmpDocBtn svg').style.cssText =
                'display: none;';
            uploadSubmissionDocumentModal.hide();

            var hardCopyChecked = $(
                '#synPaySlipModal [name="holiday_month"]'
            ).val();

            if (!dzError) {
                successModal.show();
                document
                    .getElementById('successModal')
                    .addEventListener('shown.tw.modal', function (event) {
                        $('#successModal .successModalTitle').html(
                            'Congratulation!'
                        );
                        $('#successModal .successModalDesc').html(
                            'Payslip successfully uploaded.'
                        );
                    });

                setTimeout(function () {
                    successModal.hide();
                    let month_year = $(
                        '#synPaySlipModal [name="holiday_month"]'
                    ).val();
                    var payslips = $(
                        '#synPaySlipModal [name="typePaySlip"]'
                    ).val();
                    var holiday_year = $(
                        '#synPaySlipModal [name="holiday_year_id"]'
                    ).val();
                    if(month_year != ''){
                        location.href = route('hr.attendance.payroll.sync',month_year);
                    } else {
                        location.href = route('hr.attendance.payroll.sync',payslips+'_'+holiday_year);
                    }
                }, 2000);
            }else{
                warningModal.show();
                document.getElementById("warningModal").addEventListener("shown.tw.modal", function (event) {
                    $("#warningModal .warningModalTitle").html("Error Found!" );
                    $("#warningModal .warningModalDesc").html('Something went wrong. Please try later or contact administrator.');
                    $("#warningModal .warningCloser").attr('data-action', 'DISMISS');
                });
                setTimeout(function(){
                    warningModal.hide();
                    //window.location.reload();
                }, 2000);
            }
        });

        // Function to get the current month and year in 'MM-YYYY' format
        $('#uploadEmpDocBtn').on('click', function (e) {
            e.preventDefault();
            document
                .querySelector('#uploadEmpDocBtn')
                .setAttribute('disabled', 'disabled');
            document.querySelector('#uploadEmpDocBtn svg').style.cssText =
                'display: inline-block;';

            if (drzn1.files.length > 0) {
                var monthYear = $(
                    '#synPaySlipModal [name="holiday_month"]'
                ).val();
                var payslips = $(
                    '#synPaySlipModal [name="typePaySlip"]'
                ).val();
                var holiday_year = $(
                    '#synPaySlipModal [name="holiday_year_id"]'
                ).val();
                if(payslips != 'Payslips' && holiday_year != '') {
                    
                    $('#synPaySlipModal [name="dir_name"]').val(
                        payslips+'_'+holiday_year
                    );
                    $('#synPaySlipModal [name="type"]').val(
                        payslips
                    );
                    $(
                        '#synPaySlipModal [name="holiday_year_info"]'
                    ).val(
                        holiday_year
                    );

                    drzn1.processQueue();
                }else if (monthYear != '') {
                    $('#synPaySlipModal [name="dir_name"]').val(
                        monthYear
                    );
                    $('#synPaySlipModal [name="type"]').val(
                        payslips
                    );
                    $(
                        '#synPaySlipModal [name="holiday_year_info"]'
                    ).val(
                        holiday_year
                    );
                    drzn1.processQueue();
                } else {
                    $(
                        '#synPaySlipModal .modal-content .uploadError'
                    ).remove();
                    $('#synPaySlipModal .modal-content').prepend(
                        '<div class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Please select the Assessment.</div>'
                    );

                    createIcons({
                        icons,
                        'stroke-width': 1.5,
                        nameAttr: 'data-lucide',
                    });

                    setTimeout(function () {
                        $(
                            '#synPaySlipModal .modal-content .uploadError'
                        ).remove();
                        document
                            .querySelector('#uploadEmpDocBtn')
                            .removeAttribute('disabled', 'disabled');
                        document.querySelector(
                            '#uploadEmpDocBtn svg'
                        ).style.cssText = 'display: none;';
                    }, 2000);
                }
            } else {
                $('#synPaySlipModal .modal-content .uploadError').remove();
                $('#synPaySlipModal .modal-content').prepend(
                    '<div class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Please select at least one file.</div>'
                );

                createIcons({
                    icons,
                    'stroke-width': 1.5,
                    nameAttr: 'data-lucide',
                });

                setTimeout(function () {
                    $(
                        '#synPaySlipModal .modal-content .uploadError'
                    ).remove();
                    document
                        .querySelector('#uploadEmpDocBtn')
                        .removeAttribute('disabled', 'disabled');
                    document.querySelector(
                        '#uploadEmpDocBtn svg'
                    ).style.cssText = 'display: none;';
                }, 2000);
            }
        });

        $('#EmpSyncBtn').on('click', function (e) {
            e.preventDefault();
            var acceptedFiles = drzn1.getAcceptedFiles().length;

            if (acceptedFiles > 0) {
                document
                    .querySelector('#EmpSyncBtn')
                    .setAttribute('disabled', 'disabled');
                document.querySelector('#EmpSyncBtn svg').style.cssText =
                    'display: inline-block;';

                var hardCopyChecked = $(
                    '#synPaySlipModal [name="holiday_month"]'
                ).val();
                if (hardCopyChecked != '') {
                    $('#synPaySlipModal [name="dir_name"]').val(
                        hardCopyChecked
                    );
                    drzn1.processQueue();
                } else {
                    $('#synPaySlipModal .modal-content .uploadError').remove();
                    $('#synPaySlipModal .modal-content').prepend(
                        '<div class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Please select the hard copy check status.</div>'
                    );

                    createIcons({
                        icons,
                        'stroke-width': 1.5,
                        nameAttr: 'data-lucide',
                    });

                    setTimeout(function () {
                        $(
                            '#synPaySlipModal .modal-content .uploadError'
                        ).remove();
                        document
                            .querySelector('#EmpSyncBtn')
                            .removeAttribute('disabled', 'disabled');
                        document.querySelector(
                            '#EmpSyncBtn svg'
                        ).style.cssText = 'display: none;';
                    }, 2000);
                }
            } else {
                warningModal.show();
                document
                    .getElementById('warningModal')
                    .addEventListener('shown.tw.modal', function (event) {
                        $('#warningModal .warningModalTitle').html(
                            'Error Found!'
                        );
                        $('#warningModal .warningModalDesc').html(
                            'Empty submission are not accepted. Please upload some valid files.'
                        );
                        $('#warningModal .warningCloser').attr(
                            'data-action',
                            'NONE'
                        );
                    });

                setTimeout(function () {
                    warningModal.hide();
                }, 2000);
            }
        });

        uploadSubmissionDocumentModalEl.addEventListener(
            'hide.tw.modal',
            function (event) {
                $(
                    '#uploadSubmissionDocumentModal input[name="documents[]"]'
                ).val('');
                document
                    .querySelector('#uploadEmpDocBtn')
                    .removeAttribute('disabled', 'disabled');
                document.querySelector('#uploadEmpDocBtn svg').style.cssText =
                    'display: none;';

                Dropzone.forElement('#uploadDocumentForm').removeAllFiles(true);
            }
        );
    }
    /* End Dropzone */
    holiday_year.on('change', function (value) {
        // Your function to handle the change event
        console.log('Selected value:', value);
        // Call your function here
        handleHolidayMonthChange(value);
    });

    function handleHolidayMonthChange(value) {
        // Implement your logic here
        console.log('Handling change for value:', value);
        axios({
            method: 'get',
            url: route('holiday.month.list', value),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
        })
            .then((response) => {
                if (response.status == 200) {
                    let dataset = response.data;

                    holiday_month.clearOptions();

                    $.each(dataset, function (index, row) {
                        holiday_month.addOption({
                            value: row.value,
                            text: row.text,
                        });
                    });
                }
            })
            .catch((error) => {
                console.log(error);
            });
    }

    // $('#addSyncPaySlipForm').on('submit', function (e) {
    //     e.preventDefault();
    //     const form = document.getElementById('addSyncPaySlipForm');

    //     document
    //         .querySelector('#EmpSyncBtn')
    //         .setAttribute('disabled', 'disabled');
    //     document.querySelector('#EmpSyncBtn svg').style.cssText =
    //         'display: inline-block;';

    //     let form_data = new FormData(form);
    //     axios({
    //         method: 'post',
    //         url: route('hr.portal.update.payroll.sync'),
    //         data: form_data,
    //         headers: {
    //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
    //         },
    //     })
    //         .then((response) => {
    //             document
    //                 .querySelector('#EmpSyncBtn')
    //                 .removeAttribute('disabled');
    //             document.querySelector('#EmpSyncBtn svg').style.cssText =
    //                 'display: none;';

    //             if (response.status == 200) {
    //                 absentUpdateModal.hide();

    //                 successModal.show();
    //                 document
    //                     .getElementById('successModal')
    //                     .addEventListener('shown.tw.modal', function (event) {
    //                         $('#successModal .successModalTitle').html(
    //                             'Congratulations!'
    //                         );
    //                         $('#successModal .successModalDesc').html(
    //                             'Synchronising Successfull.'
    //                         );
    //                         $('#successModal .successCloser').attr(
    //                             'data-action',
    //                             'RELOAD'
    //                         );
    //                     });

    //                 setTimeout(function () {
    //                     successModal.hide();
    //                     window.location.reload();
    //                 }, 2000);
    //             }
    //         })
    //         .catch((error) => {
    //             document
    //                 .querySelector('#EmpSyncBtn')
    //                 .removeAttribute('disabled');
    //             document.querySelector('#EmpSyncBtn svg').style.cssText =
    //                 'display: none;';
    //             if (error.response) {
    //                 if (error.response.status == 422) {
    //                     for (const [key, val] of Object.entries(
    //                         error.response.data.errors
    //                     )) {
    //                         $(`#addSyncPaySlipForm .${key}`).addClass(
    //                             'border-danger'
    //                         );
    //                         $(`#addSyncPaySlipForm  .error-${key}`).html(val);
    //                     }
    //                 } else {
    //                     console.log('error');
    //                 }
    //             }
    //         });
    // });
})();
