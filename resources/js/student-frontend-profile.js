import xlsx from 'xlsx';
import { createIcons, icons } from 'lucide';
import Tabulator from 'tabulator-tables';
import TomSelect from 'tom-select';

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

    const successModal = tailwind.Modal.getOrCreateInstance(
        document.querySelector('#successModal')
    );
    const editOtherPersonalInfoModal = tailwind.Modal.getOrCreateInstance(
        document.querySelector('#editOtherPersonalInfoModal')
    );
    const editAdmissionContactDetailsModal = tailwind.Modal.getOrCreateInstance(
        document.querySelector('#editAdmissionContactDetailsModal')
    );
    const editAdmissionKinDetailsModal = tailwind.Modal.getOrCreateInstance(
        document.querySelector('#editAdmissionKinDetailsModal')
    );

    $('#successModal .successCloser').on('click', function (e) {
        e.preventDefault();
        if ($(this).attr('data-action') == 'RELOAD') {
            successModal.hide();
            window.location.reload();
        } else {
            successModal.hide();
        }
    });

    /* Edit Other Personal Information */
    $('#disability_status').on('change', function () {
        if ($('#disability_status').prop('checked')) {
            $('.disabilityItems').fadeIn('fast', function () {
                $('.disabilityItems input[type="checkbox"]').prop(
                    'checked',
                    false
                );
                $('.disabilityAllowance').fadeOut();
                $('.disabilityAllowance input[type="checkbox"]').prop(
                    'checked',
                    false
                );
            });
        } else {
            $('.disabilityItems').fadeOut('fast', function () {
                $('.disabilityItems input[type="checkbox"]').prop(
                    'checked',
                    false
                );
                $('.disabilityAllowance').fadeOut();
                $('.disabilityAllowance input[type="checkbox"]').prop(
                    'checked',
                    false
                );
            });
        }
    });

    $('.disabilityItems input[type="checkbox"]').on('change', function () {
        if ($('.disabilityItems input[type="checkbox"]:checked').length > 0) {
            if (!$('.disabilityAllowance').is(':visible')) {
                $('.disabilityAllowance').fadeIn('fast', function () {
                    $('input[type="checkbox"]', this).prop('checked', false);
                });
            }
        } else {
            $('.disabilityAllowance').fadeOut('fast', function () {
                $('input[type="checkbox"]', this).prop('checked', false);
            });
        }
    });
    $('#editOtherPersonalInfoForm').on('submit', function (e) {
        e.preventDefault();
        var $form = $(this);
        const form = document.getElementById('editOtherPersonalInfoForm');

        document.querySelector('#saveSOI').setAttribute('disabled', 'disabled');
        document.querySelector('#saveSOI svg').style.cssText =
            'display: inline-block;';

        let form_data = new FormData(form);
        axios({
            method: 'post',
            url: route('students.update.other.personal.details'),
            data: form_data,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
        })
            .then((response) => {
                if (response.status == 200) {
                    document
                        .querySelector('#saveSOI')
                        .removeAttribute('disabled');
                    document.querySelector('#saveSOI svg').style.cssText =
                        'display: none;';

                    editOtherPersonalInfoModal.hide();

                    successModal.show();
                    document
                        .getElementById('successModal')
                        .addEventListener('shown.tw.modal', function (event) {
                            $('#successModal .successModalTitle').html(
                                'Congratulation!'
                            );
                            $('#successModal .successModalDesc').html(
                                'Other Personal Information successfully updated.'
                            );
                            $('#successModal .successCloser').attr(
                                'data-action',
                                'RELOAD'
                            );
                        });

                    setTimeout(function () {
                        successModal.hide();
                        window.location.reload();
                    }, 2000);
                }
            })
            .catch((error) => {
                document.querySelector('#saveSOI').removeAttribute('disabled');
                document.querySelector('#saveSOI svg').style.cssText =
                    'display: none;';
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(
                            error.response.data.errors
                        )) {
                            $(`#editOtherPersonalInfoForm .${key}`).addClass(
                                'border-danger'
                            );
                            $(`#editOtherPersonalInfoForm  .error-${key}`).html(
                                val
                            );
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
    });
    /* Edit Other Personal Information */

    /* Edit Contact Details */
    $('#editAdmissionContactDetailsForm').on('submit', function (e) {
        e.preventDefault();
        var $form = $(this);
        const form = document.getElementById('editAdmissionContactDetailsForm');

        document.querySelector('#saveCD').setAttribute('disabled', 'disabled');
        document.querySelector('#saveCD svg').style.cssText =
            'display: inline-block;';

        let form_data = new FormData(form);
        axios({
            method: 'post',
            url: route('students.update.contact.details'),
            data: form_data,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
        })
            .then((response) => {
                if (response.status == 200) {
                    document
                        .querySelector('#saveCD')
                        .removeAttribute('disabled');
                    document.querySelector('#saveCD svg').style.cssText =
                        'display: none;';

                    editAdmissionContactDetailsModal.hide();

                    successModal.show();
                    document
                        .getElementById('successModal')
                        .addEventListener('shown.tw.modal', function (event) {
                            $('#successModal .successModalTitle').html(
                                'Congratulation!'
                            );
                            $('#successModal .successModalDesc').html(
                                'Contact Details Data successfully updated.'
                            );
                            $('#successModal .successCloser').attr(
                                'data-action',
                                'RELOAD'
                            );
                        });

                    setTimeout(function () {
                        successModal.hide();
                        window.location.reload();
                    }, 2000);
                }
            })
            .catch((error) => {
                document.querySelector('#saveCD').removeAttribute('disabled');
                document.querySelector('#saveCD svg').style.cssText =
                    'display: none;';
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(
                            error.response.data.errors
                        )) {
                            $(
                                `#editAdmissionContactDetailsForm .${key}`
                            ).addClass('border-danger');
                            $(
                                `#editAdmissionContactDetailsForm  .error-${key}`
                            ).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
    });
    /* Edit Contact Details*/

    /* Edit Kin Details */
    if ($('#editAdmissionKinDetailsForm').length > 0) {
        $('#editAdmissionKinDetailsForm').on('submit', function (e) {
            e.preventDefault();
            var $form = $(this);
            const form = document.getElementById('editAdmissionKinDetailsForm');

            document
                .querySelector('#saveNOK')
                .setAttribute('disabled', 'disabled');
            document.querySelector('#saveNOK svg').style.cssText =
                'display: inline-block;';

            let form_data = new FormData(form);
            axios({
                method: 'post',
                url: route('students.update.kin.details'),
                data: form_data,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                        'content'
                    ),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        document
                            .querySelector('#saveNOK')
                            .removeAttribute('disabled');
                        document.querySelector('#saveNOK svg').style.cssText =
                            'display: none;';

                        editAdmissionKinDetailsModal.hide();

                        successModal.show();
                        document
                            .getElementById('successModal')
                            .addEventListener(
                                'shown.tw.modal',
                                function (event) {
                                    $('#successModal .successModalTitle').html(
                                        'Congratulation!'
                                    );
                                    $('#successModal .successModalDesc').html(
                                        'Next of Kin Data successfully updated.'
                                    );
                                    $('#successModal .successCloser').attr(
                                        'data-action',
                                        'RELOAD'
                                    );
                                }
                            );

                        setTimeout(function () {
                            successModal.hide();
                            window.location.reload();
                        }, 2000);
                    }
                })
                .catch((error) => {
                    document
                        .querySelector('#saveNOK')
                        .removeAttribute('disabled');
                    document.querySelector('#saveNOK svg').style.cssText =
                        'display: none;';
                    if (error.response) {
                        if (error.response.status == 422) {
                            for (const [key, val] of Object.entries(
                                error.response.data.errors
                            )) {
                                $(
                                    `#editAdmissionKinDetailsForm .${key}`
                                ).addClass('border-danger');
                                $(
                                    `#editAdmissionKinDetailsForm  .error-${key}`
                                ).html(val);
                            }
                        } else {
                            console.log('error');
                        }
                    }
                });
        });
    }
    /* Edit Kin Details*/

    /*Address Modal*/
    if ($('#addressModal').length > 0) {
        const addressModal = tailwind.Modal.getOrCreateInstance(
            document.querySelector('#addressModal')
        );

        const addressModalEl = document.getElementById('addressModal');
        addressModalEl.addEventListener('hide.tw.modal', function (event) {
            $('#addressModal .acc__input-error').html('');
            $('#addressModal .modal-body input').val('');
            $('#addressModal input[name="address_id"]').val('0');
        });

        $('.addressPopupToggler').on('click', function (e) {
            e.preventDefault();

            var $btn = $(this);
            var $wrap = $btn.parents('.addressWrap');
            var $addressIdField = $btn.siblings('.address_id_field');

            var wrap_id = '#' + $wrap.attr('id');
            var address_id = $addressIdField.val();
            if (address_id > 0) {
                axios({
                    method: 'post',
                    url: route('students.address.get'),
                    data: { address_id: address_id },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                            'content'
                        ),
                    },
                })
                    .then((response) => {
                        if (response.status == 200) {
                            var dataset = response.data.res;

                            $(
                                '#addressModal #student_address_address_line_1'
                            ).val(
                                dataset.address_line_1
                                    ? dataset.address_line_1
                                    : ''
                            );
                            $(
                                '#addressModal #student_address_address_line_2'
                            ).val(
                                dataset.address_line_2
                                    ? dataset.address_line_2
                                    : ''
                            );
                            $('#addressModal #student_address_city').val(
                                dataset.city ? dataset.city : ''
                            );
                            $(
                                '#addressModal #student_address_state_province_region'
                            ).val(dataset.state ? dataset.state : '');
                            $(
                                '#addressModal #student_address_postal_zip_code'
                            ).val(dataset.post_code ? dataset.post_code : '');
                            $('#addressModal #student_address_country').val(
                                dataset.country ? dataset.country : ''
                            );

                            $('#addressModal input[name="place"]').val(wrap_id);
                            $('#addressModal input[name="address_id"]').val(
                                address_id
                            );
                        }
                    })
                    .catch((error) => {
                        if (error.response) {
                            console.log('error');
                        }
                    });
            } else {
                $('#addressModal input[name="place"]').val(wrap_id);
                $('#addressModal .modal-body input').val('');
                $('#addressModal input[name="address_id"]').val('0');
            }
        });

        $('#addressForm').on('submit', function (e) {
            e.preventDefault();
            const form = document.getElementById('addressForm');
            var $form = $(this);
            var wrapid = $('input[name="place"]', $form).val();
            var address_id = $('input[name="address_id"]', $form).val();

            var htmls = '';
            var post_code = $('#student_address_postal_zip_code', $form).val();
            htmls +=
                '<span class="text-slate-600 font-medium">' +
                $('#student_address_address_line_1', $form).val() +
                '</span><br/>';
            if ($('#student_address_address_line_2', $form).val() != '') {
                htmls +=
                    '<span class="text-slate-600 font-medium">' +
                    $('#student_address_address_line_2', $form).val() +
                    '</span><br/>';
            }
            htmls +=
                '<span class="text-slate-600 font-medium">' +
                $('#student_address_city', $form).val() +
                '</span>, ';
            if (
                $('#student_address_state_province_region', $form).val() != ''
            ) {
                htmls +=
                    '<span class="text-slate-600 font-medium">' +
                    $('#student_address_state_province_region', $form).val() +
                    '</span>, <br/>';
            } else {
                htmls += '<br/>';
            }
            htmls +=
                '<span class="text-slate-600 font-medium">' +
                $('#student_address_postal_zip_code', $form).val() +
                '</span>,<br/>';
            htmls +=
                '<span class="text-slate-600 font-medium">' +
                $('#student_address_country', $form).val() +
                '</span><br/>';

            document
                .querySelector('#insertAddress')
                .setAttribute('disabled', 'disabled');
            document.querySelector('#insertAddress svg').style.cssText =
                'display: inline-block;';

            let form_data = new FormData(form);
            axios({
                method: 'post',
                url: route('students.address.store'),
                data: form_data,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                        'content'
                    ),
                },
            })
                .then((response) => {
                    document
                        .querySelector('#insertAddress')
                        .removeAttribute('disabled');
                    document.querySelector('#insertAddress svg').style.cssText =
                        'display: none;';

                    if (response.status == 200) {
                        var dataset = response.data.res;
                        var newAddressId = dataset.id ? dataset.id : 0;

                        addressModal.hide();
                        $(wrapid + ' .addresses').html(htmls);
                        $(wrapid + ' button.addressPopupToggler span').html(
                            'Update Address'
                        );
                        $(wrapid + ' input.address_id_field').val(newAddressId);

                        if (
                            wrapid == '#permanentAddressWrap' &&
                            $(
                                '#editAdmissionContactDetailsModal input[name="permanent_post_code"]'
                            ).val() == ''
                        ) {
                            $(
                                '#editAdmissionContactDetailsModal input[name="permanent_post_code"]'
                            ).val(post_code);
                        }
                    }
                })
                .catch((error) => {
                    document
                        .querySelector('#insertAddress')
                        .removeAttribute('disabled');
                    document.querySelector('#insertAddress svg').style.cssText =
                        'display: none;';
                    if (error.response) {
                        if (error.response.status == 422) {
                            for (const [key, val] of Object.entries(
                                error.response.data.errors
                            )) {
                                $(`#addressForm .${key}`).addClass(
                                    'border-danger'
                                );
                                $(`#addressForm  .error-${key}`).html(val);
                            }
                        } else {
                            console.log('error');
                        }
                    }
                });
        });
    }
    /*Address Modal*/
})();
