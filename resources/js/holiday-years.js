import xlsx from 'xlsx';
import { createIcons, icons } from 'lucide';
import Tabulator from 'tabulator-tables';

import dayjs from 'dayjs';
import Litepicker from 'litepicker';

('use strict');

var hrHolidayYearsListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $('#query-HY').val() != '' ? $('#query-HY').val() : '';
        let status = $('#status-HY').val() != '' ? $('#status-HY').val() : '';

        let tableContent = new Tabulator('#hrHolidayYearsListTable', {
            ajaxURL: route('holiday.year.list'),
            ajaxParams: { querystr: querystr, status: status },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: 'remote',
            paginationSize: 10,
            paginationSizeSelector: [true, 5, 10, 20, 30, 40],
            layout: 'fitColumns',
            responsiveLayout: 'collapse',
            placeholder: 'No matching records found',
            columns: [
                {
                    title: '#ID',
                    field: 'id',
                    width: '180',
                },
                {
                    title: 'Year',
                    field: 'year',
                    headerHozAlign: 'left',
                },
                {
                    title: 'Start Date',
                    field: 'start_date',
                    headerHozAlign: 'left',
                },
                {
                    title: 'End Date',
                    field: 'end_date',
                    headerHozAlign: 'left',
                },
                {
                    title: 'Notice Period',
                    field: 'notice_period',
                    headerHozAlign: 'left',
                },
                {
                    title: 'Status',
                    field: 'active',
                    headerHozAlign: 'left',
                    formatter(cell, formatterParams) {
                        return (
                            '<div class="form-check form-switch"><input data-id="' +
                            cell.getData().id +
                            '" ' +
                            (cell.getData().active == 1 ? 'Checked' : '') +
                            ' value="' +
                            cell.getData().active +
                            '" type="checkbox" class="status_updater form-check-input"> </div>'
                        );
                    },
                },
                {
                    title: 'Actions',
                    field: 'id',
                    headerSort: false,
                    hozAlign: 'right',
                    headerHozAlign: 'right',
                    width: '210',
                    download: false,
                    formatter(cell, formatterParams) {
                        var btns = '';
                        if (cell.getData().deleted_at == null) {
                            btns +=
                                '<a href="' +
                                route('hr.bank.holiday', cell.getData().id) +
                                '" class="btn-rounded btn btn-linkedin text-white p-0 w-9 h-9 ml-1"><i data-lucide="landmark" class="w-4 h-4"></i></a>';
                            btns +=
                                '<a href="' +
                                route(
                                    'holiday.year.leave.option',
                                    cell.getData().id
                                ) +
                                '" class="btn-rounded btn btn-facebook text-white p-0 w-9 h-9 ml-1"><i data-lucide="list-ordered" class="w-4 h-4"></i></a>';
                            btns +=
                                '<button data-id="' +
                                cell.getData().id +
                                '" data-tw-toggle="modal" data-tw-target="#editHolidayYearModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
                            btns +=
                                '<button data-id="' +
                                cell.getData().id +
                                '"  class="delete_btn btn btn-danger text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="Trash2" class="w-4 h-4"></i></button>';
                        } else if (cell.getData().deleted_at != null) {
                            btns +=
                                '<button data-id="' +
                                cell.getData().id +
                                '"  class="restore_btn btn btn-linkedin text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="rotate-cw" class="w-4 h-4"></i></button>';
                        }

                        return btns;
                    },
                },
            ],
            renderComplete() {
                createIcons({
                    icons,
                    'stroke-width': 1.5,
                    nameAttr: 'data-lucide',
                });
                const actionColumn = this.getColumn("id");
                if (actionColumn) {
                    const currentWidth = actionColumn.getWidth();
                    actionColumn.setWidth(currentWidth - 1);
                }
            },
        });

        // Export
        $('#tabulator-export-csv-HY').on('click', function (event) {
            tableContent.download('csv', 'data.csv');
        });

        $('#tabulator-export-json-HY').on('click', function (event) {
            tableContent.download('json', 'data.json');
        });

        $('#tabulator-export-xlsx-HY').on('click', function (event) {
            window.XLSX = xlsx;
            tableContent.download('xlsx', 'data.xlsx', {
                sheetName: 'Roles Details',
            });
        });

        $('#tabulator-export-html-HY').on('click', function (event) {
            tableContent.download('html', 'data.html', {
                style: true,
            });
        });

        // Print
        $('#tabulator-print-HY').on('click', function (event) {
            tableContent.print();
        });
    };
    return {
        init: function () {
            _tableGen();
        },
    };
})();

(function () {
    if ($('#hrHolidayYearsListTable').length) {
        // Init Table
        hrHolidayYearsListTable.init();

        // Filter function
        function filterHTMLForm() {
            hrHolidayYearsListTable.init();
        }

        // On submit filter form
        $('#tabulatorFilterForm-HY')[0].addEventListener(
            'keypress',
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == '13') {
                    event.preventDefault();
                    filterHTMLForm();
                }
            }
        );

        // On click go button
        $('#tabulator-html-filter-go-HY').on('click', function (event) {
            filterHTMLForm();
        });

        // On reset filter form
        $('#tabulator-html-filter-reset-HY').on('click', function (event) {
            $('#query-HY').val('');
            $('#status-HY').val('1');
            filterHTMLForm();
        });
    }

    const addHolidayYearModal = tailwind.Modal.getOrCreateInstance(
        document.querySelector('#addHolidayYearModal')
    );
    const editHolidayYearModal = tailwind.Modal.getOrCreateInstance(
        document.querySelector('#editHolidayYearModal')
    );
    const successModal = tailwind.Modal.getOrCreateInstance(
        document.querySelector('#successModal')
    );
    const confirmModal = tailwind.Modal.getOrCreateInstance(
        document.querySelector('#confirmModal')
    );

    let confModalDelTitle = 'Are you sure?';

    const addHolidayYearModalEl = document.getElementById(
        'addHolidayYearModal'
    );
    addHolidayYearModalEl.addEventListener('hide.tw.modal', function (event) {
        $('#addHolidayYearModal .acc__input-error').html('');
        $('#addHolidayYearModal .modal-body input').val('');
        $('#addHolidayYearModal .modal-footer input[type="checkbox"]').prop(
            'checked',
            true
        );
    });

    const editRoleModalEl = document.getElementById('editHolidayYearModal');
    editRoleModalEl.addEventListener('hide.tw.modal', function (event) {
        $('#editHolidayYearModal .acc__input-error').html('');
        $('#editHolidayYearModal .modal-body input').val('');
        $('#editHolidayYearModal .modal-footer input[type="checkbox"]').prop(
            'checked',
            false
        );
        $('#editHolidayYearModal input[name="id"]').val('0');
    });

    const confirmModalEl = document.getElementById('confirmModal');
    confirmModalEl.addEventListener('hidden.tw.modal', function (event) {
        $('#confirmModal .roomAgreeWith').attr('data-id', '0');
        $('#confirmModal .roomAgreeWith').attr('data-action', 'none');
    });

    let dateOption = {
        autoApply: true,
        singleMode: true,
        numberOfColumns: 1,
        numberOfMonths: 1,
        showWeekNumbers: true,
        format: 'DD-MM-YYYY',
        dropdowns: {
            minYear: 1900,
            maxYear: 2050,
            months: true,
            years: true,
        },
    };

    const start_date = new Litepicker({
        element: document.getElementById('start_date'),
        ...dateOption,
        setup: (picker) => {
            picker.on('selected', (date1, date2) => {
                end_date.setOptions({
                    minDate: picker.getDate(),
                });
            });
        },
    });

    const end_date = new Litepicker({
        element: document.getElementById('end_date'),
        ...dateOption,
    });

    const edit_start_date = new Litepicker({
        element: document.getElementById('edit_start_date'),
        ...dateOption,
        setup: (picker) => {
            picker.on('selected', (date1, date2) => {
                edit_end_date.clearSelection();
                edit_end_date.setOptions({
                    minDate: picker.getDate(),
                });
            });
        },
    });

    const edit_end_date = new Litepicker({
        element: document.getElementById('edit_end_date'),
        ...dateOption,
    });

    $('#addHolidayYearForm').on('submit', function (e) {
        e.preventDefault();
        const form = document.getElementById('addHolidayYearForm');

        $('#addHolidayYearForm').find('input').removeClass('border-danger');
        $('#addHolidayYearForm').find('.acc__input-error').html('');

        document.querySelector('#saveHY').setAttribute('disabled', 'disabled');
        document.querySelector('#saveHY svg').style.cssText =
            'display: inline-block;';

        let form_data = new FormData(form);

        axios({
            method: 'post',
            url: route('holiday.year.store'),
            data: form_data,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
        })
            .then((response) => {
                document.querySelector('#saveHY').removeAttribute('disabled');
                document.querySelector('#saveHY svg').style.cssText =
                    'display: none;';

                if (response.status == 200) {
                    addHolidayYearModal.hide();

                    successModal.show();
                    document
                        .getElementById('successModal')
                        .addEventListener('shown.tw.modal', function (event) {
                            $('#successModal .successModalTitle').html(
                                'Congratulations!'
                            );
                            $('#successModal .successModalDesc').html(
                                'HR Holiday years successfully inserted.'
                            );
                        });
                }
                hrHolidayYearsListTable.init();
            })
            .catch((error) => {
                document.querySelector('#saveHY').removeAttribute('disabled');
                document.querySelector('#saveHY svg').style.cssText =
                    'display: none;';
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(
                            error.response.data.errors
                        )) {
                            $(`#addHolidayYearForm .${key}`).addClass(
                                'border-danger'
                            );
                            $(`#addHolidayYearForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
    });

    $('#hrHolidayYearsListTable').on('click', '.edit_btn', function () {
        let $editBtn = $(this);
        let editId = $editBtn.attr('data-id');

        axios({
            method: 'post',
            url: route('holiday.year.edit'),
            data: { rowID: editId },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
        })
            .then((response) => {
                if (response.status == 200) {
                    let dataset = response.data;
                    $('#editHolidayYearModal input[name="notice_period"]').val(
                        dataset.notice_period ? dataset.notice_period : ''
                    );

                    var startDate = new Date(dataset.start_date_modified);
                    edit_start_date.setOptions({
                        startDate: dataset.start_date,
                    });

                    if (dataset.end_date && dataset.end_date != '') {
                        edit_end_date.setOptions({
                            startDate: dataset.end_date,
                            minDate: startDate,
                        });
                    } else {
                        edit_end_date.clearSelection();
                        edit_end_date.setOptions({
                            minDate: startDate,
                        });
                    }
                    if (dataset.active == 1) {
                        $('#editHolidayYearModal input[name="active"]').prop(
                            'checked',
                            true
                        );
                    } else {
                        $('#editHolidayYearModal input[name="active"]').prop(
                            'checked',
                            false
                        );
                    }
                    $('#editHolidayYearModal input[name="id"]').val(editId);
                }
            })
            .catch((error) => {
                console.log(error);
            });
    });

    $('#editHolidayYearForm').on('submit', function (e) {
        e.preventDefault();
        const form = document.getElementById('editHolidayYearForm');

        $('#editHolidayYearForm').find('input').removeClass('border-danger');
        $('#editHolidayYearForm').find('.acc__input-error').html('');

        document
            .querySelector('#updateHY')
            .setAttribute('disabled', 'disabled');
        document.querySelector('#updateHY svg').style.cssText =
            'display: inline-block;';

        let form_data = new FormData(form);

        axios({
            method: 'post',
            url: route('holiday.year.update'),
            data: form_data,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
        })
            .then((response) => {
                document.querySelector('#updateHY').removeAttribute('disabled');
                document.querySelector('#updateHY svg').style.cssText =
                    'display: none;';

                if (response.status == 200) {
                    editHolidayYearModal.hide();

                    successModal.show();
                    document
                        .getElementById('successModal')
                        .addEventListener('shown.tw.modal', function (event) {
                            $('#successModal .successModalTitle').html(
                                'Congratulations!'
                            );
                            $('#successModal .successModalDesc').html(
                                'Holiday year successfully updated.'
                            );
                        });
                }
                hrHolidayYearsListTable.init();
            })
            .catch((error) => {
                document.querySelector('#updateHY').removeAttribute('disabled');
                document.querySelector('#updateHY svg').style.cssText =
                    'display: none;';
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(
                            error.response.data.errors
                        )) {
                            $(`#editHolidayYearForm .${key}`).addClass(
                                'border-danger'
                            );
                            $(`#editHolidayYearForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
    });

    // Delete Room
    $('#hrHolidayYearsListTable').on('click', '.delete_btn', function () {
        let $statusBTN = $(this);
        let rowID = $statusBTN.attr('data-id');

        confirmModal.show();
        document
            .getElementById('confirmModal')
            .addEventListener('shown.tw.modal', function (event) {
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html(
                    'Do you really want to delete these record? If yes, the please click on agree btn.'
                );
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'DELETE');
            });
    });

    $('#hrHolidayYearsListTable').on('click', '.restore_btn', function () {
        let $statusBTN = $(this);
        let rowID = $statusBTN.attr('data-id');

        confirmModal.show();
        document
            .getElementById('confirmModal')
            .addEventListener('shown.tw.modal', function (event) {
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html(
                    'Do you really want to restore these record?'
                );
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'RESTORE');
            });
    });

    $('#hrHolidayYearsListTable').on('click', '.status_updater', function () {
        let $statusBTN = $(this);
        let rowID = $statusBTN.attr('data-id');

        confirmModal.show();
        document
            .getElementById('confirmModal')
            .addEventListener('shown.tw.modal', function (event) {
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html(
                    'Do you really want to change status of this record? If yes then please click on the agree btn.'
                );
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'CHANGESTAT');
            });
    });

    // Confirm Modal Action
    $('#confirmModal .agreeWith').on('click', function () {
        let $agreeBTN = $(this);
        let recordID = $agreeBTN.attr('data-id');
        let action = $agreeBTN.attr('data-action');

        $('#confirmModal button').attr('disabled', 'disabled');
        if (action == 'DELETE') {
            axios({
                method: 'delete',
                url: route('holiday.year.destory', recordID),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                        'content'
                    ),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        $('#confirmModal button').removeAttr('disabled');
                        confirmModal.hide();

                        successModal.show();
                        document
                            .getElementById('successModal')
                            .addEventListener(
                                'shown.tw.modal',
                                function (event) {
                                    $('#successModal .successModalTitle').html(
                                        'WOW!'
                                    );
                                    $('#successModal .successModalDesc').html(
                                        'Record successfully deleted from DB row.'
                                    );
                                }
                            );
                    }
                    hrHolidayYearsListTable.init();
                })
                .catch((error) => {
                    console.log(error);
                });
        } else if (action == 'RESTORE') {
            axios({
                method: 'post',
                url: route('holiday.year.restore', recordID),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                        'content'
                    ),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        $('#confirmModal button').removeAttr('disabled');
                        confirmModal.hide();

                        successModal.show();
                        document
                            .getElementById('successModal')
                            .addEventListener(
                                'shown.tw.modal',
                                function (event) {
                                    $('#successModal .successModalTitle').html(
                                        'WOW!'
                                    );
                                    $('#successModal .successModalDesc').html(
                                        'Record Successfully Restored!'
                                    );
                                }
                            );
                    }
                    hrHolidayYearsListTable.init();
                })
                .catch((error) => {
                    console.log(error);
                });
        } else if (action == 'CHANGESTAT') {
            axios({
                method: 'post',
                url: route('holiday.year.update.status'),
                data: { recordID: recordID },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                        'content'
                    ),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        $('#confirmModal button').removeAttr('disabled');
                        confirmModal.hide();

                        successModal.show();
                        document
                            .getElementById('successModal')
                            .addEventListener(
                                'shown.tw.modal',
                                function (event) {
                                    $('#successModal .successModalTitle').html(
                                        'WOW!'
                                    );
                                    $('#successModal .successModalDesc').html(
                                        'Record status successfully updated!'
                                    );
                                }
                            );
                    }
                    hrHolidayYearsListTable.init();
                })
                .catch((error) => {
                    console.log(error);
                });
        }
    });
})();
