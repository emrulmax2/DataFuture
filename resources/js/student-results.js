import IMask from 'imask';
import helper from './helper';
import colors from './colors';
import Chart from 'chart.js/auto';

import { createIcons, icons } from 'lucide';
import TomSelect from 'tom-select';

import dayjs from 'dayjs';
import { Litepicker } from 'litepicker';

('use strict');

(function () {
    var tomSelectArray = [];
    var tomOptions = {
        plugins: {
            dropdown_input: {},
        },
        placeholder: 'Search Here...',
        persist: false,
        create: false,
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
        tomSelectArray.push(new TomSelect(this, tomOptions));
    });

    document
        .querySelectorAll('.datepicker_custom')
        .forEach(function (element, index) {
            new Litepicker({
                element: element,
                autoApply: false,
                singleMode: true,
                numberOfColumns: 1,
                numberOfMonths: 1,
                format: 'DD-MM-YYYY',

                dropdowns: {
                    minYear: 2000,
                    maxYear: null,
                    months: true,
                    years: true,
                },
                setup: (picker) => {
                    picker.on('render', (ui) => {
                        // Create a div element with class 'litepicker
                        const hourSelect = document.createElement('select');
                        hourSelect.id = `hourSelect-${index}`;

                        hourSelect.classList.add('focus:shadow-none'); // Add Tailwind CSS class for focus border color
                        // Populate hour select with options from 00 to 23
                        for (let hour = 0; hour < 24; hour++) {
                            const option = document.createElement('option');
                            const formattedHour = hour
                                .toString()
                                .padStart(2, '0');
                            option.value = formattedHour;
                            option.text = formattedHour;
                            hourSelect.appendChild(option);
                        }

                        // Create minute select element
                        const minuteSelect = document.createElement('select');
                        minuteSelect.id = `minuteSelect-${index}`;
                        minuteSelect.classList.add('focus:shadow-none'); // Add Tailwind CSS class for focus border color
                        // Populate minute select with options from 00 to 59
                        for (let minute = 1; minute < 60; minute++) {
                            const option = document.createElement('option');
                            const formattedMinute = minute
                                .toString()
                                .padStart(2, '0');
                            option.value = formattedMinute;
                            option.text = formattedMinute;
                            minuteSelect.appendChild(option);
                        }
                        // Add CSS styles to hourSelect and minuteSelect
                        const selectStyle = `
                        background-image: url(data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='rgb(74, 85, 104)' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='lucide lucide-chevron-down'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E);
                        background-size: 15px;
                        background-position: center right 0.6rem;
                        border-radius: 0.375rem;
                        border-width: 1px;
                        background-color: transparent;
                        background-repeat: no-repeat;
                        padding-top: 0.25rem;
                        padding-bottom: 0.25rem;
                        padding-left: 0.5rem;
                        padding-right: 2rem;
                        font-size: 0.875rem;
                        line-height: 1.25rem;
                    `;

                        hourSelect.style.cssText = selectStyle;
                        minuteSelect.style.cssText = selectStyle;
                        // Create a div element with class 'litepicker-time'
                        const wrapperDiv = document.createElement('div');

                        wrapperDiv.classList.add(
                            'litepicker-time',
                            'py-3',
                            'px-5',
                            'mx-auto'
                        );

                        hourSelect.classList.add('mr-1');
                        minuteSelect.classList.add('mr-1');

                        // Create labels for hourSelect and minuteSelect
                        const hourLabel = document.createElement('label');
                        hourLabel.setAttribute('for', 'hourSelect');
                        hourLabel.textContent = 'Hour: ';

                        const minuteLabel = document.createElement('label');
                        minuteLabel.setAttribute('for', 'minuteSelect');
                        minuteLabel.textContent = 'Minute: ';

                        // Append labels and select elements to the div
                        wrapperDiv.appendChild(hourLabel);
                        wrapperDiv.appendChild(hourSelect);
                        wrapperDiv.appendChild(minuteLabel);
                        wrapperDiv.appendChild(minuteSelect);

                        // Locate the container__footer element
                        const containerFooter =
                            ui.querySelector('.container__footer');

                        // Insert wrapperDiv before container__footer
                        if (containerFooter) {
                            containerFooter.parentNode.insertBefore(
                                wrapperDiv,
                                containerFooter
                            );
                        } else {
                            // Fallback if container__footer is not found
                            ui.appendChild(wrapperDiv);
                        }
                        //Add event listener to button-apply
                        const applyButton = ui.querySelector('.button-apply');

                        const closestHiddenInput = element
                            .closest('td')
                            .querySelector('input[type="hidden"]');

                        if (applyButton) {
                            applyButton.addEventListener('click', () => {
                                // Get the selected hour and minute values
                                const timeValue = `${hourSelect.value}:${minuteSelect.value}`;
                                // You can add additional logic here to handle the time value

                                // Get the selected date from Litepicker
                                const selectedDate = picker.getDate();
                                if (selectedDate) {
                                    // Format the date and time
                                    const formattedDate =
                                        selectedDate.format('DD-MM-YYYY');
                                    const combinedDateTime = `${formattedDate} ${timeValue}`;

                                    // Set the combined date and time value to the current element
                                    element.value = combinedDateTime;

                                    closestHiddenInput.value = combinedDateTime;
                                }
                            });
                        } else {
                            const closestHiddenInput = element
                                .closest('td')
                                .querySelector('input[type="hidden"]');

                            ui.querySelectorAll('.day-item').forEach(
                                (dayItem) => {
                                    dayItem.addEventListener('click', () => {
                                        // Get the selected hour and minute values
                                        const timeValue = `${hourSelect.value}:${minuteSelect.value}`;
                                        // You can add additional logic here to handle the time value

                                        // Get the selected date from Litepicker

                                        const selectedDate = picker.getDate();
                                        if (selectedDate) {
                                            // Format the date and time
                                            const formattedDate =
                                                selectedDate.format(
                                                    'DD-MM-YYYY'
                                                );
                                            const combinedDateTime = `${formattedDate} ${timeValue}`;

                                            // Set the combined date and time value to the current element
                                            element.value = combinedDateTime;

                                            closestHiddenInput.value =
                                                combinedDateTime;
                                        }
                                    });
                                }
                            );
                        }
                    });
                },
            });

            var maskOptions = {
                mask: 'DD-MM-YYYY HH:mm',
                blocks: {
                    MM: {
                        mask: IMask.MaskedRange,
                        placeholderChar: 'MM',
                        from: 1,
                        to: 12,
                        maxLength: 2,
                    },
                    YYYY: {
                        mask: IMask.MaskedRange,
                        placeholderChar: 'YYYY',
                        from: 2000,
                        to: 2099,
                        maxLength: 4,
                    },
                    DD: {
                        mask: IMask.MaskedRange,
                        placeholderChar: 'DD',
                        from: 1,
                        to: 31,
                        maxLength: 2,
                    },
                    HH: {
                        mask: IMask.MaskedRange,
                        placeholderChar: 'HH',
                        from: 0,
                        to: 23,
                        maxLength: 2,
                    },
                    mm: {
                        mask: IMask.MaskedRange,
                        placeholderChar: 'MM',
                        from: 0,
                        to: 59,
                        maxLength: 2,
                    },
                },
            };
            var mask = IMask(element, maskOptions);
        });

    // $('.tablepoint-toggle').on('click', function (e) {
    //     e.preventDefault();
    //     let tthis = $(this);
    //     let currentThis = tthis.children('.plusminus').eq(0);

    //     let nextThis = tthis.children('.plusminus').eq(1);
    //     if (currentThis.hasClass('hidden')) {
    //         currentThis.removeClass('hidden');
    //         nextThis.addClass('hidden');
    //     } else {
    //         nextThis.removeClass('hidden');
    //         currentThis.addClass('hidden');
    //     }

    //     tthis.parent().siblings('div.tabledataset').slideToggle();
    // });
    // $('.toggle-heading').on('click', function (e) {
    //     e.preventDefault();
    //     let tthis = $(this);
    //     tthis.siblings('div.tablepoint-toggle').trigger('click');
    // });

    const succModal = tailwind.Modal.getOrCreateInstance(
        document.querySelector('#successModal')
    );
    const confirmModal = tailwind.Modal.getOrCreateInstance(
        document.querySelector('#delete-confirmation-modal')
    );
    // const defaultModal = tailwind.Modal.getOrCreateInstance(
    //     document.querySelector('#default-confirmation-modal')
    // );

    // Add New Result
    $('.addNewRowBtn').on('click', function () {
        let $$this = $(this);
        let rowID = $$this.attr('data-id');
        // Find the latest data-index value
        let latestIndex = 0;
        $$this
            .closest('form')
            .find('input[name="grade_id[]"]')
            .each(function () {
                const index = parseInt($(this).attr('data-index'));
                if (index > latestIndex) {
                    latestIndex = index;
                }
            });

        // Increment the latest index for the new row
        const newIndex = latestIndex + 1;

        // Find the first row in tbody.bulk-update
        let $firstRow = $$this
            .closest('form')
            .find('tbody.bulk-update tr:first');
        // Remove the anchor element within $firstRow
        //$firstRow.find('a').remove();

        // Remove the delete_btn class and add delete_btn_new class
        let newAnchor = $firstRow.find('a').first();

        newAnchor.attr('data-id', 0);
        newAnchor.removeAttr('data-tw-toggle');
        newAnchor.removeAttr('data-tw-target');
        newAnchor.removeAttr('data-trigger');
        newAnchor.removeClass('delete_btn');
        newAnchor.addClass('delete_btn_new');

        // Clone the first row
        let $newRow = $firstRow.clone();

        // Append the anchor element to the new row

        let plan_id = $newRow.find('input[name="plan_id[]"]').val();
        let student_id = $newRow.find('input[name="student_id[]"]').val();
        let createdBy = $newRow.find('input[name="updated_by[]"]').val();

        $newRow.find('input, select, div.error-*').each(function () {
            const $element = $(this);
            const name = $element.attr('name');
            if (name) {
                $element.attr('data-index', newIndex);
            }
            const className = $element.attr('class');
            if (className && className.startsWith('error-')) {
                $element.attr('data-index', newIndex);
                $element.html(''); // Clear any previous error messages
            }
            // if ($element.is('input') || $element.is('select')) {
            //     $element.val(''); // Clear the value for the new row
            // }
        });
        // Empty the values of input fields and reset select elements
        $newRow.find('input').val('');
        $newRow.find('select').prop('selectedIndex', 0);
        $newRow.find('div.error').html('');

        $newRow.find('input[name="plan_id[]"]').val(plan_id);
        $newRow.find('input[name="student_id[]"]').val(student_id);
        $newRow.find('input[name="created_by[]"]').val(createdBy);
        $newRow.find('input[name="updated_by[]"]').val(createdBy);

        $newRow.find('div.lccTom').each(function () {
            this.remove(); // Remove the existing Tom Select instance
        });
        // Reset Tom Select elements
        $newRow.find('select.lccTom').each(function () {
            let NewTom = new TomSelect(this, tomOptions); // Initialize Tom Select for new elements
            NewTom.clear(); // Clear the selected values
            NewTom.setValue('');
        });

        $newRow.find('input.datepicker_custom').each(function () {
            this.setAttribute('placeholder', `DD-MM-YYYY HH:mm`);
        });
        $newRow.find('div.updated-name').html('');
        $newRow.find(`input[name="created_at[]"]`).val(getCurrentDate());
        // Create a new input element for created_at[]

        if (newAnchor.length == 0) {
            newAnchor = $('<a></a>'); // Create a new anchor element
            // Set attributes
            newAnchor.attr('href', 'javascript:;');
            newAnchor.attr('data-theme', 'light');
            newAnchor.attr('data-id', 0);
            newAnchor.attr('data-action', 'DELETE');
            newAnchor.attr('title', 'delete result');
            newAnchor.removeAttr('data-tw-toggle');
            newAnchor.removeAttr('data-tw-target');
            newAnchor.removeAttr('data-trigger');
            newAnchor.removeClass('delete_btn');
            newAnchor.addClass('delete_btn_new');
            // Set classes
            newAnchor.addClass(
                'intro-x text-danger flex items-center text-xs sm:text-sm cursor-pointer'
            );

            // Append inner HTML content
            newAnchor.html('<i data-lucide="x-circle" class="w-5 h-5"></i>');

            // Reinitialize Lucide icons to ensure the new icon is rendered

            $newRow.find('div.updated-name').html(newAnchor[0].outerHTML);
        }
        // Append the cloned row to tbody.bulk-update
        $$this.closest('form').find('tbody.bulk-update').append($newRow);
        createIcons({
            icons,
            'stroke-width': 1.5,
            nameAttr: 'data-lucide',
        });
        $newRow[0]
            .querySelectorAll('.datepicker_custom')
            .forEach(function (element, index) {
                new Litepicker({
                    element: element,
                    autoApply: false,
                    singleMode: true,
                    numberOfColumns: 1,
                    numberOfMonths: 1,
                    format: 'DD-MM-YYYY',

                    dropdowns: {
                        minYear: 2000,
                        maxYear: null,
                        months: true,
                        years: true,
                    },
                    setup: (picker) => {
                        picker.on('render', (ui) => {
                            // Create a div element with class 'litepicker
                            const hourSelect = document.createElement('select');
                            hourSelect.id = `hourSelect-${index}`;

                            hourSelect.classList.add('focus:shadow-none'); // Add Tailwind CSS class for focus border color
                            // Populate hour select with options from 00 to 23
                            for (let hour = 0; hour < 24; hour++) {
                                const option = document.createElement('option');
                                const formattedHour = hour
                                    .toString()
                                    .padStart(2, '0');
                                option.value = formattedHour;
                                option.text = formattedHour;
                                hourSelect.appendChild(option);
                            }

                            // Create minute select element
                            const minuteSelect =
                                document.createElement('select');
                            minuteSelect.id = `minuteSelect-${index}`;
                            minuteSelect.classList.add('focus:shadow-none'); // Add Tailwind CSS class for focus border color
                            // Populate minute select with options from 00 to 59
                            for (let minute = 1; minute < 60; minute++) {
                                const option = document.createElement('option');
                                const formattedMinute = minute
                                    .toString()
                                    .padStart(2, '0');
                                option.value = formattedMinute;
                                option.text = formattedMinute;
                                minuteSelect.appendChild(option);
                            }
                            // Add CSS styles to hourSelect and minuteSelect
                            const selectStyle = `
                        background-image: url(data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='rgb(74, 85, 104)' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='lucide lucide-chevron-down'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E);
                        background-size: 15px;
                        background-position: center right 0.6rem;
                        border-radius: 0.375rem;
                        border-width: 1px;
                        background-color: transparent;
                        background-repeat: no-repeat;
                        padding-top: 0.25rem;
                        padding-bottom: 0.25rem;
                        padding-left: 0.5rem;
                        padding-right: 2rem;
                        font-size: 0.875rem;
                        line-height: 1.25rem;
                    `;

                            hourSelect.style.cssText = selectStyle;
                            minuteSelect.style.cssText = selectStyle;
                            // Create a div element with class 'litepicker-time'
                            const wrapperDiv = document.createElement('div');

                            wrapperDiv.classList.add(
                                'litepicker-time',
                                'py-3',
                                'px-5',
                                'mx-auto'
                            );

                            hourSelect.classList.add('mr-1');
                            minuteSelect.classList.add('mr-1');

                            // Create labels for hourSelect and minuteSelect
                            const hourLabel = document.createElement('label');
                            hourLabel.setAttribute('for', 'hourSelect');
                            hourLabel.textContent = 'Hour: ';

                            const minuteLabel = document.createElement('label');
                            minuteLabel.setAttribute('for', 'minuteSelect');
                            minuteLabel.textContent = 'Minute: ';

                            // Append labels and select elements to the div
                            wrapperDiv.appendChild(hourLabel);
                            wrapperDiv.appendChild(hourSelect);
                            wrapperDiv.appendChild(minuteLabel);
                            wrapperDiv.appendChild(minuteSelect);

                            // Locate the container__footer element
                            const containerFooter =
                                ui.querySelector('.container__footer');

                            // Insert wrapperDiv before container__footer
                            if (containerFooter) {
                                containerFooter.parentNode.insertBefore(
                                    wrapperDiv,
                                    containerFooter
                                );
                            } else {
                                // Fallback if container__footer is not found
                                ui.appendChild(wrapperDiv);
                            }
                            //Add event listener to button-apply
                            const applyButton =
                                ui.querySelector('.button-apply');

                            if (applyButton) {
                                applyButton.addEventListener('click', () => {
                                    // Get the selected hour and minute values
                                    const timeValue = `${hourSelect.value}:${minuteSelect.value}`;
                                    // You can add additional logic here to handle the time value

                                    // Get the selected date from Litepicker
                                    const selectedDate = picker.getDate();
                                    if (selectedDate) {
                                        // Format the date and time
                                        const formattedDate =
                                            selectedDate.format('DD-MM-YYYY');
                                        const combinedDateTime = `${formattedDate} ${timeValue}`;

                                        // Set the combined date and time value to the current element
                                        element.value = combinedDateTime;
                                        const closestHiddenInput = element
                                            .closest('td')
                                            .querySelector(
                                                'input[type="hidden"]'
                                            );
                                        closestHiddenInput.value =
                                            combinedDateTime;
                                    }
                                });
                            } else {
                                ui.querySelectorAll('.day-item').forEach(
                                    (dayItem) => {
                                        dayItem.addEventListener(
                                            'click',
                                            () => {
                                                // Get the selected hour and minute values
                                                const timeValue = `${hourSelect.value}:${minuteSelect.value}`;
                                                // You can add additional logic here to handle the time value

                                                // Get the selected date from Litepicker
                                                const selectedDate =
                                                    picker.getDate();
                                                if (selectedDate) {
                                                    // Format the date and time
                                                    const formattedDate =
                                                        selectedDate.format(
                                                            'DD-MM-YYYY'
                                                        );
                                                    const combinedDateTime = `${formattedDate} ${timeValue}`;

                                                    // Set the combined date and time value to the current element
                                                    element.value =
                                                        combinedDateTime;
                                                    const closestHiddenInput =
                                                        element
                                                            .closest('td')
                                                            .querySelector(
                                                                'input[type="hidden"]'
                                                            );
                                                    closestHiddenInput.value =
                                                        combinedDateTime;
                                                }
                                            }
                                        );
                                    }
                                );
                            }
                        });
                    },
                });

                var maskOptions = {
                    mask: 'DD-MM-YYYY HH:mm',
                    blocks: {
                        MM: {
                            mask: IMask.MaskedRange,
                            placeholderChar: 'MM',
                            from: 1,
                            to: 12,
                            maxLength: 2,
                        },
                        YYYY: {
                            mask: IMask.MaskedRange,
                            placeholderChar: 'YYYY',
                            from: 2000,
                            to: 2099,
                            maxLength: 4,
                        },
                        DD: {
                            mask: IMask.MaskedRange,
                            placeholderChar: 'DD',
                            from: 1,
                            to: 31,
                            maxLength: 2,
                        },
                        HH: {
                            mask: IMask.MaskedRange,
                            placeholderChar: 'HH',
                            from: 0,
                            to: 23,
                            maxLength: 2,
                        },
                        mm: {
                            mask: IMask.MaskedRange,
                            placeholderChar: 'MM',
                            from: 0,
                            to: 59,
                            maxLength: 2,
                        },
                    },
                };
                var mask = IMask(element, maskOptions);
            });
    });

    // Confirm Modal Action

    $(document).on('click', '.delete_btn', function () {
        let $statusBTN = $(this);

        let rowID = $statusBTN.attr('data-id');
        let confModalDelTitle = 'Do you want to delete';
        confirmModal.show();
        document
            .getElementById('delete-confirmation-modal')
            .addEventListener('shown.tw.modal', function (event) {
                $('#delete-confirmation-modal .confModTitle').html(
                    confModalDelTitle
                );
                $('#delete-confirmation-modal .confModDesc').html(
                    'Do you really want to delete these record? If yes, the please click on agree btn.'
                );
                $('#delete-confirmation-modal .agreeWith').attr(
                    'data-id',
                    rowID
                );
                $('#delete-confirmation-modal .agreeWith').attr(
                    'data-action',
                    'DELETE'
                );
            });
    });
    $(document).on('click', '.delete_btn_new', function () {
        let $statusBTN = $(this);

        $statusBTN.closest('tr').remove();
    });

    $('.update_btn').on('click', function (e) {
        let $statusBTN = $(this);
        let rowID = $statusBTN.attr('data-id');
        let formId = $statusBTN.closest('form').attr('id');

        // let editId = $('#editAttemptForm input[name="id"]').val();

        e.preventDefault();
        const form = document.getElementById(formId);

        $statusBTN.attr('disabled', 'disabled');
        $('svg', $statusBTN).removeClass('hidden');

        let form_data = new FormData(form);
        const editAttemptModal = tailwind.Modal.getOrCreateInstance(
            document.querySelector('#editAttemptModal' + rowID)
        );
        axios({
            method: 'post',
            url: route('result.update.bulk'),
            data: form_data,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
        })
            .then((response) => {
                if (response.status == 200) {
                    $statusBTN.removeAttr('disabled');
                    $('svg', $statusBTN).removeClass('hidden');

                    editAttemptModal.hide();
                    succModal.show();

                    document
                        .getElementById('successModal')
                        .addEventListener('shown.tw.modal', function (event) {
                            $('#successModal .successModalTitle').html(
                                'Success!'
                            );
                            $('#successModal .successModalDesc').html(
                                'Result updated'
                            );
                        });
                    location.reload();
                }
            })
            .catch((error) => {
                $statusBTN.removeAttr('disabled');
                $('svg', $statusBTN).addClass('hidden');
                console.log(error.response.status);
                if (error.response.status == 422) {
                    $statusBTN
                        .closest('form')
                        .find('div.alert')
                        .removeClass('hidden');
                    $statusBTN
                        .closest('form')
                        .find('span.error-text')
                        .text(error.response.data.message);

                    for (const [key, val] of Object.entries(
                        error.response.data.errors
                    )) {
                        // Extract the field name and index from the key
                        const [field, index] = key.split('.');
                        const formElement = $statusBTN.closest('form')[0];
                        // Find the corresponding input element and error div
                        const inputElement = formElement.querySelector(
                            `input[name="${field}[]"][data-index="${index}"]`
                        );
                        const errorDiv = formElement.querySelector(
                            `div.error-${field}[data-index="${index}"]`
                        );

                        if (inputElement) {
                            inputElement.classList.add('border-danger');
                        }

                        if (errorDiv) {
                            errorDiv.innerHTML = val.join(', ');
                        }
                    }
                }
            });
    });

    // $('#editAttemptForm').on('submit', function (e) {
    //     let editId = $('#editAttemptForm input[name="id"]').val();

    //     e.preventDefault();
    //     const form = document.getElementById('editAttemptForm');

    //     document.querySelector('#update').setAttribute('disabled', 'disabled');
    //     document.querySelector('#update svg').style.cssText =
    //         'display: inline-block;';

    //     let form_data = new FormData(form);

    //     axios({
    //         method: 'post',
    //         url: route('result.update', editId),
    //         data: form_data,
    //         headers: {
    //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
    //         },
    //     })
    //         .then((response) => {
    //             if (response.status == 200) {
    //                 document
    //                     .querySelector('#update')
    //                     .removeAttribute('disabled');
    //                 document.querySelector('#update svg').style.cssText =
    //                     'display: none;';
    //                 editAttemptModal.hide();

    //                 succModal.show();
    //                 document
    //                     .getElementById('successModal')
    //                     .addEventListener('shown.tw.modal', function (event) {
    //                         $('#successModal .successModalTitle').html(
    //                             'Success!'
    //                         );
    //                         $('#successModal .successModalDesc').html(
    //                             'Result updated'
    //                         );
    //                     });
    //             }
    //             location.reload();
    //         })
    //         .catch((error) => {
    //             document.querySelector('#update').removeAttribute('disabled');
    //             document.querySelector('#update svg').style.cssText =
    //                 'display: none;';
    //             if (error.response) {
    //                 if (error.response.status == 422) {
    //                     for (const [key, val] of Object.entries(
    //                         error.response.data.errors
    //                     )) {
    //                         $(`#editForm .${key}`).addClass('border-danger');
    //                         $(`#editForm  .error-${key}`).html(val);
    //                     }
    //                 } else {
    //                     console.log('error');
    //                 }
    //             }
    //         });
    // });

    $('#delete-confirmation-modal .agreeWith').on('click', function () {
        let $agreeBTN = $(this);
        let resultId = $agreeBTN.attr('data-id');
        let action = $agreeBTN.attr('data-action');

        $('#delete-confirmation-modal button').attr('disabled', 'disabled');
        if (action == 'DELETE') {
            axios({
                method: 'delete',
                url: route('result.destroy', resultId),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                        'content'
                    ),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        $('#delete-confirmation-modal button').removeAttr(
                            'disabled'
                        );
                        confirmModal.hide();
                        succModal.show();
                        document
                            .getElementById('successModal')
                            .addEventListener(
                                'shown.tw.modal',
                                function (event) {
                                    $('#successModal .successModalTitle').html(
                                        'Done!'
                                    );
                                    $('#successModal .successModalDesc').html(
                                        'Data successfully deleted.'
                                    );
                                }
                            );

                        location.reload();
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        } else if (action == 'DEFAULT') {
            axios({
                method: 'post',
                url: route('result.default', resultId),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                        'content'
                    ),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        $('#default-confirmation-modal button').removeAttr(
                            'disabled'
                        );
                        confirmModal.hide();
                        succModal.show();
                        document
                            .getElementById('successModal')
                            .addEventListener(
                                'shown.tw.modal',
                                function (event) {
                                    $('#successModal .successModalTitle').html(
                                        'Done!'
                                    );
                                    $('#successModal .successModalDesc').html(
                                        'Result set as default.'
                                    );
                                }
                            );

                        location.reload();
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        }
    });

    // $('#default-confirmation-modal .agreeWith').on('click', function () {
    //     let $agreeBTN = $(this);
    //     let resultId = $agreeBTN.attr('data-id');
    //     let action = $agreeBTN.attr('data-action');

    //     $('#default-confirmation-modal button').attr('disabled', 'disabled');
    //     if (action == 'DEFAULT') {
    //         axios({
    //             method: 'post',
    //             url: route('result.default', resultId),
    //             headers: {
    //                 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
    //                     'content'
    //                 ),
    //             },
    //         })
    //             .then((response) => {
    //                 if (response.status == 200) {
    //                     $('#default-confirmation-modal button').removeAttr(
    //                         'disabled'
    //                     );
    //                     defaultModal.hide();
    //                     succModal.show();
    //                     document
    //                         .getElementById('successModal')
    //                         .addEventListener(
    //                             'shown.tw.modal',
    //                             function (event) {
    //                                 $('#successModal .successModalTitle').html(
    //                                     'Updated!'
    //                                 );
    //                                 $('#successModal .successModalDesc').html(
    //                                     'Result set as default.'
    //                                 );
    //                             }
    //                         );

    //                     location.reload();
    //                 }
    //             })
    //             .catch((error) => {
    //                 console.log(error);
    //             });
    //     }
    // });

    $('#sortable-table th').on('click', function () {
        var th = $(this);
        var table = $(this).parents('table').eq(0);
        var rows = table
            .find('tbody tr')
            .toArray()
            .sort(comparer($(this).index()));
        const asc = (this.asc = !this.asc);
        if (!this.asc) {
            rows = rows.reverse();
        }
        for (var i = 0; i < rows.length; i++) {
            table.append(rows[i]);
        }
        // Reset all sorting icons to arrow-up-down
        $('#sortable-table th svg').remove();

        // Add the arrow-up-down icon to all headers
        $('#sortable-table th').each(function () {
            const defaultIcon = $('<i>')
                .addClass('w-4 h-4 ml-2 inline-flex')
                .attr('data-lucide', 'arrow-up-down');
            $(this).append(defaultIcon);
        });

        // Update sorting icon for the clicked header
        const $th = $(th);
        const $icon = $th.find('svg');
        const $defaultNewIcon = $th.find('i');
        $defaultNewIcon.remove();
        if ($icon.length) {
            $icon.remove();
        }

        const newIcon = $('<i>').addClass('w-4 h-4 ml-2 inline-flex');
        if (asc) {
            newIcon.attr('data-lucide', 'arrow-up');
        } else {
            newIcon.attr('data-lucide', 'arrow-down');
        }
        $(th).append(newIcon);
        // Refresh Lucide icons with the icons object
        createIcons({
            icons,
            'stroke-width': 1.5,
            nameAttr: 'data-lucide',
        });
        paginateTable();
    });
    function paginateTable() {
        const rowsPerPage = 10;
        const rows = $('#sortable-table tbody tr');
        const rowsCount = rows.length;
        const pageCount = Math.ceil(rowsCount / rowsPerPage);
        const numbers = $('#pagination-container');

        numbers.html('');

        for (let i = 0; i < pageCount; i++) {
            numbers.append('<a href="#">' + (i + 1) + '</a>');
        }

        rows.hide();
        rows.slice(0, rowsPerPage).show();

        numbers.find('a').click(function (e) {
            e.preventDefault();
            const index = $(this).index();
            const start = index * rowsPerPage;
            const end = start + rowsPerPage;

            rows.hide();
            rows.slice(start, end).show();
        });
    }
    function comparer(index) {
        return function (a, b) {
            var valA = getCellValue(a, index);
            var valB = getCellValue(b, index);
            return $.isNumeric(valA) && $.isNumeric(valB)
                ? valA - valB
                : valA.localeCompare(valB);
        };
    }

    function getCellValue(row, index) {
        return $(row).children('td').eq(index).text();
    }

    // Function to get current date in DD-MM-YYYY H:i format
    function getCurrentDate() {
        const date = new Date();
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0'); // Months are zero-based
        const year = date.getFullYear();
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        return `${day}-${month}-${year} ${hours}:${minutes}`;
    }
})();
