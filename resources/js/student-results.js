import IMask from 'imask';
import helper from './helper';
import colors from './colors';
import Chart from 'chart.js/auto';

import { createIcons, icons } from 'lucide';
import TomSelect from 'tom-select';

import dayjs from 'dayjs';
import Litepicker from 'litepicker';
('use strict');

(function () {
    document.querySelectorAll('.datepicker_custom').forEach(function (element) {
        new Litepicker({
            element: element,
            singleMode: true,
            numberOfColumns: 2,
            numberOfMonths: 2,
            format: 'DD-MM-YYYY HH:mm',
            time: {
                enabled: true,
            },
            dropdowns: {
                minYear: 2000,
                maxYear: null,
                months: true,
                years: true,
            },
            setup: (picker) => {
                picker.on('render', (ui) => {
                    // Customize the time display format
                    ui.querySelectorAll('.litepicker-time').forEach(
                        (timeElement) => {
                            const timeInput =
                                timeElement.querySelector('input');
                            if (timeInput) {
                                timeInput.setAttribute('placeholder', 'HH:mm');
                                timeInput.addEventListener('input', (event) => {
                                    const value = event.target.value;
                                    const formattedValue = value
                                        .replace(/[^0-9:]/g, '')
                                        .slice(0, 5);
                                    event.target.value = formattedValue;
                                });
                            }
                        }
                    );
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

    $('.tablepoint-toggle').on('click', function (e) {
        e.preventDefault();
        let tthis = $(this);
        let currentThis = tthis.children('.plusminus').eq(0);
        console.log(currentThis);
        let nextThis = tthis.children('.plusminus').eq(1);
        if (currentThis.hasClass('hidden')) {
            currentThis.removeClass('hidden');
            nextThis.addClass('hidden');
        } else {
            nextThis.removeClass('hidden');
            currentThis.addClass('hidden');
        }

        tthis.parent().siblings('div.tabledataset').slideToggle();
    });
    $('.toggle-heading').on('click', function (e) {
        e.preventDefault();
        let tthis = $(this);
        tthis.siblings('div.tablepoint-toggle').trigger('click');
    });

    const succModal = tailwind.Modal.getOrCreateInstance(
        document.querySelector('#successModal')
    );
    const confirmModal = tailwind.Modal.getOrCreateInstance(
        document.querySelector('#delete-confirmation-modal')
    );
    const editAttemptModal = tailwind.Modal.getOrCreateInstance(
        document.querySelector('#editAttemptModal')
    );
    // Confirm Modal Action

    $('.delete_btn').on('click', function () {
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
    $('.edit_btn').on('click', function () {
        let $statusBTN = $(this);
        let rowID = $statusBTN.attr('data-id');
        let grade = $statusBTN.attr('data-grade');
        let publishTime = $statusBTN.attr('data-publishTime');
        let publishDate = $statusBTN.attr('data-publishDate');

        let module = $statusBTN.attr('data-module');
        let code = $statusBTN.attr('data-code');
        let term = $statusBTN.attr('data-term');

        editAttemptModal.show();
        document
            .getElementById('editAttemptModal')
            .addEventListener('shown.tw.modal', function (event) {
                $('#editAttemptModal .modulename').html(module);
                $('#editAttemptModal .modulecode').html(code);
                $('#editAttemptModal .term').html(term);
                $('#editAttemptModal input[name="id"]').val(rowID);
                $('#editAttemptModal select[name="grade_id"]').val(grade);
                $('#editAttemptModal input[name="published_at"]').val(
                    publishDate
                );
                $('#editAttemptModal input[name="published_time"]').val(
                    publishTime
                );
            });
    });

    $('#editAttemptForm').on('submit', function (e) {
        let editId = $('#editAttemptForm input[name="id"]').val();

        e.preventDefault();
        const form = document.getElementById('editAttemptForm');

        document.querySelector('#update').setAttribute('disabled', 'disabled');
        document.querySelector('#update svg').style.cssText =
            'display: inline-block;';

        let form_data = new FormData(form);

        axios({
            method: 'post',
            url: route('result.update', editId),
            data: form_data,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
        })
            .then((response) => {
                if (response.status == 200) {
                    document
                        .querySelector('#update')
                        .removeAttribute('disabled');
                    document.querySelector('#update svg').style.cssText =
                        'display: none;';
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
                }
                location.reload();
            })
            .catch((error) => {
                document.querySelector('#update').removeAttribute('disabled');
                document.querySelector('#update svg').style.cssText =
                    'display: none;';
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(
                            error.response.data.errors
                        )) {
                            $(`#editForm .${key}`).addClass('border-danger');
                            $(`#editForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
    });

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
        }
    });

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
})();
