import xlsx from 'xlsx';
import { createIcons, icons } from 'lucide';
import Tabulator from 'tabulator-tables';
import TomSelect from 'tom-select';
import { createApp } from 'vue';

('use strict');
(function () {
    let tomOptions = {
        plugins: {
            dropdown_input: {},
        },
        placeholder: 'Search Here...',
        persist: false,
        create: true,
        maxOptions: null,
        allowEmptyOption: true,
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
    //var employment_status = new TomSelect('#employment_status', tomOptions);

    $('.lccTom').each(function () {
        if ($(this).attr('multiple') !== undefined) {
            tomOptions = {
                ...tomOptions,
                plugins: {
                    ...tomOptions.plugins,
                    remove_button: {
                        title: 'Remove this item',
                    },
                },
            };
        }
        new TomSelect(this, tomOptions);
    });

    const studentLoginAlertModal = tailwind.Modal.getOrCreateInstance(
        document.querySelector('#student-login-alert')
    );

    studentLoginAlertModal.show();

    // const studenttermTimeAddressAlertModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#termtime-address-modal"));
    // const studentpermanentAddressAlertModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#permanent-address-modal"));

    const disagreeCurrentAddressModal = tailwind.Modal.getOrCreateInstance(document.querySelector('#disagreeCurrentAddressModal'));
    // click on next button
    $('.form-wizard-next-btn').on('click', function (e) {
        e.preventDefault();

        var parentFieldset = $(this).parents('.wizard-fieldset');
        var parentForm = $(this).parents('.wizard-step-form');
        var currentActiveStep = $(this)
            .parents('.form-wizard')
            .find('.form-wizard-steps .active');
        var next = $(this);
        let nextWizardStep = true;
        //console.log(currentActiveStep);
        /* Form Submission Start*/
        var formID = parentForm.attr('id');
        const form = document.getElementById(formID);
        let studentId = $('#studentId').val();
        $('.form-wizard-next-btn, .form-wizard-previous-btn', parentForm).attr(
            'disabled',
            'disabled'
        );
        $('.form-wizard-next-btn svg', parentForm).fadeIn();

        let form_data = new FormData(form);
        form_data.append('student_id', studentId);
        let url, redURL;
        if (parentFieldset.index() == 2) {
            url = route('students.address.confirm.data');
        } else if (parentFieldset.index() == 3) {
            url = route('students.consent.confirm.data');
            redURL = $('input[name="url"]', parentForm).val();
        } else {
            url = route('students.first.data');
        }
        $.ajax({
            method: 'POST',
            url: url,
            data: form_data,
            dataType: 'json',
            async: false,
            enctype: 'multipart/form-data',
            processData: false,
            contentType: false,
            cache: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
            success: function (res, textStatus, xhr) {
                $('.acc__input-error', parentForm).html('');
                $(
                    '.form-wizard-next-btn, .form-wizard-previous-btn',
                    parentForm
                ).removeAttr('disabled');
                $('.form-wizard-next-btn svg', parentForm).fadeOut();
                if (xhr.status == 200) {
                    if (parentFieldset.index() == 1) {
                        //No work load here still
                    } else if (parentFieldset.index() == 2) {
                        $('.reviewContentWrap').attr(
                            'data-review-id',
                            res.applicant_id
                        );
                    } else if (parentFieldset.index() == 3) {
                        window.location.href = redURL;
                    }
                }
                nextWizardStep = true;
            },
            error: function (jqXHR, textStatus, errorThrown) {
                $(
                    '.form-wizard-next-btn, .form-wizard-previous-btn',
                    parentForm
                ).removeAttr('disabled');
                $('.form-wizard-next-btn svg', parentForm).fadeOut();
                if (jqXHR.status == 422) {
                    for (const [key, val] of Object.entries(
                        jqXHR.responseJSON.errors
                    )) {
                        $(`#${formID} .${key}`).addClass('border-danger');
                        $(`#${formID}  .error-${key}`).html(val);
                    }
                } else {
                    console.log(textStatus + ' => ' + errorThrown);
                }
                nextWizardStep = false;
            },
        });
        //nextWizardStep = false;
        /* Form Submission End*/

        if (nextWizardStep) {
            next.parents('.wizard-fieldset').removeClass('show');
            currentActiveStep
                .removeClass('active')
                .addClass('activated')
                .next()
                .addClass('active');
            console.log(currentActiveStep);
            next.parents('.wizard-fieldset')
                .next('.wizard-fieldset')
                .addClass('show');

            $(document)
                .find('.wizard-fieldset')
                .each(function () {
                    if ($(this).hasClass('show')) {
                        var activeIndex = $(this).index();
                        var indexCount = 1;

                        $(document)
                            .find('.form-wizard-steps .form-wizard-step-item')
                            .each(function () {
                                if (activeIndex == indexCount) {
                                    $(this).addClass('active');
                                } else {
                                    $(this).removeClass('active');
                                }
                                indexCount++;
                            });

                        /* Check If Last Step */
                        var $lastStep = $(this);
                        if (
                            $lastStep.hasClass('wizard-last-step') &&
                            $('.reviewContentWrap', $lastStep).length > 0
                        ) {
                            // var applicant_id = $('.reviewContentWrap', $lastStep).attr('data-review-id');
                        }
                    }
                });
        }
    });
    //click on previous button
    $('.form-wizard-previous-btn').on('click', function () {
        var counter = parseInt($('.wizard-counter').text());

        var prev = $(this);
        var currentActiveStep = $(this)
            .parents('.form-wizard')
            .find('.form-wizard-steps .active');
        prev.parents('.wizard-fieldset').removeClass('show');
        prev.parents('.wizard-fieldset')
            .prev('.wizard-fieldset')
            .addClass('show');
        currentActiveStep
            .removeClass('active')
            .prev()
            .removeClass('activated')
            .addClass('active');
        $(document)
            .find('.wizard-fieldset')
            .each(function () {
                if ($(this).hasClass('show')) {
                    var activeIndex = $(this).index();
                    var indexCount = 1;
                    $(document)
                        .find('.form-wizard-steps .form-wizard-step-item')
                        .each(function () {
                            if (activeIndex == indexCount) {
                                $(this).addClass('active');
                            } else {
                                $(this).removeClass('active');
                            }
                            indexCount++;
                        });
                }
            });
    });

    $('#agreeCurrentAddress').on('click', function (e) {
        e.preventDefault();
        disagreeCurrentAddressModal.hide();
        let addressId = $('#agreeCurrentAddress').data('addressid');

        $("input[name='disagree_current_address']").val(0);

        $("input[name='current_address_id']").val(addressId);
        $('#currentAdressQuestion').fadeOut(300, function (e) {
            $('#currentAddress').fadeIn(150, function (e) {
                $('#currenAddress__yes').fadeIn();
            });
            //$('#askPermanentAdress').fadeIn();
            $('#accomodationType__next').fadeIn();
            $('#agreePermanentAddress').trigger('click');
        });
    });

    $('#disagreeCurrentAddress').on('click', function (e) {
        // e.preventDefault();
        // let tthis = $(this);

        // $("input[name='disagree_current_address']").val(1);

        // $('#currentAdressQuestion').fadeOut(300, function (e) {
        //     $('#currentAddress').fadeIn(150, function (e) {
        //         $('#currenAdress__no').fadeIn();
        //     });
        //     $('#askPermanentAdress').fadeIn();
        //     $('#accomodationType__next').fadeIn();
        // });

        e.preventDefault();
        disagreeCurrentAddressModal.show();
        let addressId = $('#agreeCurrentAddress').data('addressid');

        $("input[name='disagree_current_address']").val(0);

        $("input[name='current_address_id']").val(addressId);
        $('#currentAdressQuestion').fadeOut(500, function (e) {
            
            $('#currentAddress').fadeIn(1500, function (e) {
                $('#currenAddress__yes').fadeIn();
            });
            //$('#askPermanentAdress').fadeIn();
            $('#accomodationType__next').fadeIn();
            $('#agreePermanentAddress').trigger('click');
        });
    });

    $('#agreePermanentAddress').on('click', function (e) {
        e.preventDefault();
        let addressId = $(this).data('addressid');
        let currentAddressId = $("input[name='current_address_id']").val();

        $("input[name='permanent_address_id']").val(addressId);
        if (!currentAddressId) {
            let addressLine1_p = $("input[name='address_line_1']").val();
            let addressLine2_p = $("input[name='address_line_2']").val();
            let postCode_p = $("input[name='post_code']").val();
            let state_p = $("input[name='state']").val();
            let country_p = $("input[name='country']").val();
            let city_p = $("input[name='city']").val();
            let addressHtml = '';
            addressHtml += $("input[name='address_line_2']").val() + '<br />';
            addressHtml += $("input[name='post_code']").val() + '<br />';
            addressHtml += $("input[name='state']").val() + ', ';
            addressHtml += $("input[name='city']").val() + '<br />';
            addressHtml += $("input[name='country']").val() + '';
            $('#permanentAddress__yes div').html(addressHtml);
            if (
                addressLine1_p != '' &&
                postCode_p != '' &&
                state_p != '' &&
                country_p != ''
            ) {
                $('#askPermanentAdress').fadeOut(300, function (e) {
                    $('#permanentAddress__no').hide();
                    $('#permanentAdressBox').fadeIn(150, function (e) {
                        $('#permanentAddress__yes').fadeIn();
                    });
                    $('#form2SaveButton').fadeIn();
                });
            } else {
                $(`#agreePermanentAddress`).addClass('border-danger');
                $(`.error-agreePermanentAddress`).html(
                    'Adrress field required'
                );
            }
        } else {
            $('#askPermanentAdress').fadeOut(300, function (e) {
                $('#permanentAddress__no').hide();
                $('#permanentAdressBox').fadeIn(150, function (e) {
                    $('#permanentAddress__yes').fadeIn();
                });
                $('#form2SaveButton').fadeIn();
            });
        }
    });

    $('#disagreePermanentAddress').on('click', function (e) {
        e.preventDefault();
        $("input[name='disagree_permanent_address']").val(1);
        $('#askPermanentAdress').fadeOut(300, function (e) {
            $('#permanentAddress__yes').hide();
            $('#permanentAdressBox').fadeIn(150, function (e) {
                $('#permanentAddress__no').fadeIn();
            });
            $('#form2SaveButton').fadeIn();
        });
    });
})();
