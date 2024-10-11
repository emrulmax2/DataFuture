import xlsx from 'xlsx';
import { createIcons, icons } from 'lucide';
import Tabulator from 'tabulator-tables';
import TomSelect from 'tom-select';
import ClassicEditor from '@ckeditor/ckeditor5-build-decoupled-document';

('use strict');
var attendanceReportListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let form_data = $('#classStatusForm').serialize();

        let tableContent = new Tabulator('#statusListTable', {
            ajaxURL: route('reports.class-status.list'),
            ajaxParams: { form_data: form_data },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: 'remote',
            paginationSize: 50,
            paginationSizeSelector: [50, 100, 250],
            layout: 'fitColumns',
            responsiveLayout: 'collapse',
            placeholder: 'No matching records found',
            selectable: true,
            columns: [
                {
                    formatter: 'rowSelection',
                    titleFormatter: 'rowSelection',
                    hozAlign: 'left',
                    headerHozAlign: 'left',
                    width: '60',
                    headerSort: false,
                    download: false,
                    cellClick: function (e, cell) {
                        cell.getRow().toggleSelect();
                    },
                },
                {
                    title: 'Courses',
                    field: 'course_name',
                    headerHozAlign: 'left',
                    headerSort: false,
                    formatter(cell, formatterParams) {
                        var html = '<div class="inline-block relative">';
                        html +=
                            '<div class="font-medium text-sm whitespace-nowrap uppercase">' +
                            cell.getData().course_name +
                            '</div>';
                        html +=
                            '<div class="font-medium text-sm whitespace-nowrap uppercase">' +
                            cell.getData().term_name +
                            '</div>';
                        html += '</div>';
                        return html;
                    },
                },
                {
                    title: 'Schedule',
                    field: 'schedule',
                    headerHozAlign: 'left',
                    headerSort: false,
                    headerSort: false,
                    formatter(cell, formatterParams) {
                        return (
                            '<div class="text-xs">' +
                            cell.getData().schedule +
                            '</div>'
                        );
                    },
                },
                {
                    title: 'Future Schedule',
                    field: 'future_schedule',
                    headerHozAlign: 'left',
                    headerSort: false,
                    width: '180',
                    formatter(cell, formatterParams) {
                        return (
                            '<div class="text-xs whitespace-normal break-all">' +
                            cell.getData().future_schedule +
                            '</div>'
                        );
                    },
                },
                {
                    title: 'Held',
                    field: 'held',
                    headerHozAlign: 'left',
                    headerSort: false,
                    formatter(cell, formatterParams) {
                        return (
                            '<div class="text-xs">' +
                            cell.getData().held +
                            '</div>'
                        );
                    },
                },
                {
                    title: 'Cancel',
                    field: 'cancel',
                    headerHozAlign: 'left',
                    headerSort: false,
                    formatter(cell, formatterParams) {
                        return (
                            '<div class="text-xs">' +
                            cell.getData().Cancel +
                            '</div>'
                        );
                    },
                },
                {
                    title: 'Unkown',
                    field: 'unknow',
                    headerHozAlign: 'left',
                    headerSort: false,
                    formatter(cell, formatterParams) {
                        return (
                            '<div class="text-xs">' +
                            cell.getData().unknow +
                            '</div>'
                        );
                    },
                },
                {
                    title: 'Proxy',
                    field: 'proxy',
                    headerHozAlign: 'left',
                    headerSort: false,
                    formatter(cell, formatterParams) {
                        return (
                            '<div class="text-xs">' +
                            cell.getData().proxy +
                            '</div>'
                        );
                    },
                },
                {
                    title: 'Email',
                    field: 'institutional_email',
                    headerHozAlign: 'left',
                    headerSort: false,
                    formatter(cell, formatterParams) {
                        return (
                            '<div class="text-xs">' +
                            cell.getData().institutional_email +
                            '</div>'
                        );
                    },
                },
                {
                    title: 'Group',
                    field: 'group',
                    headerHozAlign: 'left',
                    headerHozAlign: 'center',
                    hozAlign: 'center',
                    headerSort: false,
                    formatter(cell, formatterParams) {
                        return (
                            '<div class="text-xs font-medium">' +
                            cell.getData().group +
                            '</div>'
                        );
                    },
                },
                {
                    title: 'P',
                    field: 'P',
                    headerHozAlign: 'center',
                    hozAlign: 'center',
                    headerSort: false,
                    width: '45',
                    formatter(cell, formatterParams) {
                        return (
                            '<div class="text-xs font-medium">' +
                            cell.getData().P +
                            '</div>'
                        );
                    },
                },
                {
                    title: 'O',
                    field: 'O',
                    headerHozAlign: 'center',
                    hozAlign: 'center',
                    headerSort: false,
                    width: '45',
                    formatter(cell, formatterParams) {
                        return (
                            '<div class="text-xs font-medium">' +
                            cell.getData().O +
                            '</div>'
                        );
                    },
                },
                {
                    title: 'A',
                    field: 'A',
                    headerHozAlign: 'center',
                    hozAlign: 'center',
                    headerSort: false,
                    width: '45',
                    formatter(cell, formatterParams) {
                        return (
                            '<div class="text-xs font-medium">' +
                            cell.getData().A +
                            '</div>'
                        );
                    },
                },
                {
                    title: 'E',
                    field: 'E',
                    headerHozAlign: 'center',
                    hozAlign: 'center',
                    headerSort: false,
                    width: '45',
                    formatter(cell, formatterParams) {
                        return (
                            '<div class="text-xs font-medium">' +
                            cell.getData().E +
                            '</div>'
                        );
                    },
                },
                {
                    title: 'M',
                    field: 'M',
                    headerHozAlign: 'center',
                    hozAlign: 'center',
                    headerSort: false,
                    width: '45',
                    formatter(cell, formatterParams) {
                        return (
                            '<div class="text-xs font-medium">' +
                            cell.getData().M +
                            '</div>'
                        );
                    },
                },
                {
                    title: 'H',
                    field: 'H',
                    headerHozAlign: 'center',
                    hozAlign: 'center',
                    headerSort: false,
                    width: '45',
                    formatter(cell, formatterParams) {
                        return (
                            '<div class="text-xs font-medium">' +
                            cell.getData().H +
                            '</div>'
                        );
                    },
                },
                {
                    title: 'L',
                    field: 'L',
                    headerHozAlign: 'center',
                    hozAlign: 'center',
                    headerSort: false,
                    width: '45',
                    formatter(cell, formatterParams) {
                        return (
                            '<div class="text-xs font-medium">' +
                            cell.getData().L +
                            '</div>'
                        );
                    },
                },
                {
                    title: 'L.E',
                    field: 'LE',
                    headerHozAlign: 'center',
                    hozAlign: 'center',
                    headerSort: false,
                    width: '45',
                    formatter(cell, formatterParams) {
                        return (
                            '<div class="text-xs font-medium">' +
                            cell.getData().LE +
                            '</div>'
                        );
                    },
                },
                {
                    title: 'TC',
                    field: 'TC',
                    headerHozAlign: 'center',
                    hozAlign: 'center',
                    headerSort: false,
                    width: '60',
                    formatter(cell, formatterParams) {
                        return (
                            '<div class="text-xs font-medium">' +
                            cell.getData().TC +
                            '</div>'
                        );
                    },
                },
                {
                    title: '(%)',
                    field: 'w_excuse',
                    headerHozAlign: 'center',
                    hozAlign: 'center',
                    headerSort: false,
                    width: '100',
                    formatter(cell, formatterParams) {
                        return (
                            '<div class="text-sm font-medium">' +
                            cell.getData().w_excuse +
                            '</div>'
                        );
                    },
                },
                {
                    title: 'W/O Excuse',
                    field: 'wo_excuse',
                    headerHozAlign: 'center',
                    hozAlign: 'center',
                    headerSort: false,
                    width: '100',
                    formatter(cell, formatterParams) {
                        return (
                            '<div class="text-sm font-medium">' +
                            cell.getData().wo_excuse +
                            '</div><input type="hidden" name="student_ids[]" class="student_ids" value="' +
                            cell.getData().id +
                            '"/>'
                        );
                    },
                },
            ],
            ajaxResponse: function (url, params, response) {
                var total_rows =
                    response.all_rows && response.all_rows > 0
                        ? response.all_rows
                        : 0;
                if (total_rows > 0) {
                    $('#reportRowCountWrap')
                        .find('.reportTotalRowCount')
                        .html('No of Students: ' + total_rows);
                } else {
                    $('#reportRowCountWrap')
                        .find('.reportTotalRowCount')
                        .html('No of Students: 0');
                }

                return response;
            },
            renderComplete() {
                createIcons({
                    icons,
                    'stroke-width': 1.5,
                    nameAttr: 'data-lucide',
                });
            },
            rowSelectionChanged: function (data, rows) {
                var ids = [];
                if (rows.length > 0) {
                    $('#communicationBtnsArea').fadeIn();
                } else {
                    $('#communicationBtnsArea').fadeOut();
                }
            },
            selectableCheck: function (row) {
                return row.getData().id > 0; //allow selection of rows where the age is greater than 18
            },
        });

        // Redraw table onresize
        window.addEventListener('resize', () => {
            tableContent.redraw();
            createIcons({
                icons,
                'stroke-width': 1.5,
                nameAttr: 'data-lucide',
            });
        });

        // Export
        $('#tabulator-export-csv-LSD').on('click', function (event) {
            tableContent.download('csv', 'data.csv');
        });

        $('#tabulator-export-json-LSD').on('click', function (event) {
            tableContent.download('json', 'data.json');
        });

        $('#tabulator-export-xlsx-LSD').on('click', function (event) {
            window.XLSX = xlsx;
            tableContent.download('xlsx', 'data.xlsx', {
                sheetName: 'Students Details',
            });
        });

        $('#tabulator-export-html-LSD').on('click', function (event) {
            tableContent.download('html', 'data.html', {
                style: true,
            });
        });

        // Print
        $('#tabulator-print-LSD').on('click', function (event) {
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
    if ($('#attendanceReportListTable').length > 0) {
        $('#studentGroupSearchBtn').on('click', function (e) {
            e.preventDefault();
            let intakeSemester = $('#intake_semester').val();
            let attendSemester = $('#attendance_semester').val();

            if (intakeSemester.length > 0 || attendSemester.length > 0) {
                $('#studentGroupSearchForm .reportAlert').remove();
                $('.attendanceReportListTableWrap').fadeIn();
                attendanceReportListTable.init();
            } else {
                $('#studentGroupSearchForm .reportAlert').remove();
                $('#studentGroupSearchForm').prepend(
                    '<div class="alert reportAlert alert-danger-soft show flex items-start mb-5" role="alert">\
                    <i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> <span><strong>Oops</strong>&nbsp; Validation error found! Please select <span class="font-medium underline">Intake</span> or  <span class="font-medium underline">Attendance</span> Semester. </span>\
                </div>'
                );

                createIcons({
                    icons,
                    'stroke-width': 1.5,
                    nameAttr: 'data-lucide',
                });

                $('.attendanceReportListTableWrap').fadeOut(
                    'fast',
                    function () {
                        $('#attendanceReportListTable')
                            .html('')
                            .removeClass('tabulator')
                            .removeAttr('tabulator-layout')
                            .removeAttr('role');
                    }
                );

                setTimeout(() => {
                    $('#studentGroupSearchForm .reportAlert').remove();
                }, 5000);
            }
        });

        $('#attendanceReportExcelBtn').on('click', function (e) {
            e.preventDefault();
            let $theBtn = $(this);
            let $form = $('#studentGroupSearchForm');
            const form = document.getElementById('addSettingsForm');

            $theBtn.attr('disabled', 'disabled');
            $theBtn.find('svg.loading').fadeIn();

            let form_data = $('#studentGroupSearchForm').serialize();
            axios({
                method: 'post',
                url: route('report.attendance.reports.excel'),
                data: { form_data: form_data },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                        'content'
                    ),
                },
                //responseType: 'blob',
            })
                .then((response) => {
                    $theBtn.removeAttr('disabled');
                    $theBtn.find('svg.loading').fadeOut();

                    if (response.status == 200) {
                        let url = response.data.url;
                        const link = document.createElement('a');
                        link.href = url;
                        link.setAttribute(
                            'download',
                            'Student_Attendance_Reports.xlsx'
                        );
                        document.body.appendChild(link);
                        link.click();
                        link.remove();
                    }
                })
                .catch((error) => {
                    $theBtn.removeAttr('disabled');
                    $theBtn.find('svg.loading').fadeOut();

                    if (error.response) {
                        console.log('error');
                    }
                });
        });
    }

    let tomOptions = {
        plugins: {
            dropdown_input: {},
        },
        placeholder: 'Search Here...',
        //persist: false,
        create: false,
        allowEmptyOption: true,
        //maxItems: null,
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
    let sms_template_id = new TomSelect('#sms_template_id', tomOptions);
    let email_template_id = new TomSelect('#email_template_id', tomOptions);
    let letter_set_id = new TomSelect('#letter_set_id', tomOptions);

    let mailEditor;
    if ($('#mailEditor').length > 0) {
        const el = document.getElementById('mailEditor');
        ClassicEditor.create(el)
            .then((editor) => {
                mailEditor = editor;
                $(el)
                    .closest('.editor')
                    .find('.document-editor__toolbar')
                    .append(editor.ui.view.toolbar.element);
            })
            .catch((error) => {
                console.error(error);
            });
    }

    let letterEditor;
    if ($('#letterEditor').length > 0) {
        const el = document.getElementById('letterEditor');
        ClassicEditor.create(el)
            .then((editor) => {
                letterEditor = editor;
                $(el)
                    .closest('.editor')
                    .find('.document-editor__toolbar')
                    .append(editor.ui.view.toolbar.element);
            })
            .catch((error) => {
                console.error(error);
            });
    }

    const successModal = tailwind.Modal.getOrCreateInstance(
        document.querySelector('#successModal')
    );
    const confirmModal = tailwind.Modal.getOrCreateInstance(
        document.querySelector('#confirmModal')
    );
    const warningModal = tailwind.Modal.getOrCreateInstance(
        document.querySelector('#warningModal')
    );
    const sendBulkSmsModal = tailwind.Modal.getOrCreateInstance(
        document.querySelector('#sendBulkSmsModal')
    );
    const sendBulkMailModal = tailwind.Modal.getOrCreateInstance(
        document.querySelector('#sendBulkMailModal')
    );
    const generateBulkLetterModal = tailwind.Modal.getOrCreateInstance(
        document.querySelector('#generateBulkLetterModal')
    );

    const sendBulkSmsModalEl = document.getElementById('sendBulkSmsModal');
    sendBulkSmsModalEl.addEventListener('hide.tw.modal', function (event) {
        $('#sendBulkSmsModal .acc__input-error').html('');
        $(
            '#sendBulkSmsModal .modal-body input, #sendBulkSmsModal .modal-body textarea'
        ).val('');
        $('#sendBulkSmsModal .sms_countr').html('160 / 1');
        $('#sendBulkSmsModal input[name="student_ids"]').val('');
        sms_template_id.clear(true);
    });

    const sendBulkMailModalEl = document.getElementById('sendBulkMailModal');
    sendBulkMailModalEl.addEventListener('hide.tw.modal', function (event) {
        $('#sendBulkMailModal .acc__input-error').html('');
        $('#sendBulkMailModal .modal-body input#sendMailsDocument').val('');
        $(
            '#sendBulkMailModal .modal-body input, #sendBulkMailModal .modal-body select'
        ).val('');
        $('#sendBulkMailModal .sendMailsDocumentNames').html('').fadeOut();
        $('#sendBulkMailModal input[name="student_ids"]').val('');

        mailEditor.setData('');
        email_template_id.clear(true);
    });

    const generateBulkLetterModalEl = document.getElementById(
        'generateBulkLetterModal'
    );
    generateBulkLetterModalEl.addEventListener(
        'hide.tw.modal',
        function (event) {
            $('#generateBulkLetterModal .acc__input-error').html('');
            $('#generateBulkLetterModal .modal-body input').val('');
            $('#generateBulkLetterModal .modal-body select').val('');
            $(
                '#generateBulkLetterModal .modal-footer input#is_send_email'
            ).prop('checked', true);
            $('#generateBulkLetterModal .letterEditorArea').fadeOut();
            $('#generateBulkLetterModal input[name="student_ids"]').val('');

            letterEditor.setData('');
            letter_set_id.clear(true);
        }
    );

    /* Bulk Letter Start */
    $('.generateBulkLetterBtn').on('click', function (e) {
        var $btn = $(this);
        var ids = [];

        $('#attendanceReportListTable')
            .find('.tabulator-row.tabulator-selected')
            .each(function () {
                var $row = $(this);
                ids.push($row.find('.student_ids').val());
            });

        if (ids.length > 0) {
            generateBulkLetterModal.show();
            document
                .getElementById('generateBulkLetterModal')
                .addEventListener('shown.tw.modal', function (event) {
                    $('#generateBulkLetterModal [name="student_ids"]').val(
                        ids.join(',')
                    );
                });
        } else {
            warningModal.show();
            document
                .getElementById('warningModal')
                .addEventListener('shown.tw.modal', function (event) {
                    $('#warningModal .warningModalTitle').html('Error Found!');
                    $('#warningModal .warningModalDesc').html(
                        'Selected students not foudn. Please select some students first or contact with the site administrator.'
                    );
                });
        }
    });

    $('#generateBulkLetterModal #letter_set_id').on('change', function () {
        var letterSetId = $(this).val();
        if (letterSetId > 0) {
            axios({
                method: 'post',
                url: route('bulk.communication.get.letter.set'),
                data: { letterSetId: letterSetId },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                        'content'
                    ),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        $('#generateBulkLetterModal .letterEditorArea').fadeIn(
                            'fast',
                            function () {
                                var description = response.data.res.description
                                    ? response.data.res.description
                                    : '';
                                letterEditor.setData(description);
                            }
                        );
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        } else {
            $('#generateBulkLetterModal .letterEditorArea').fadeOut(
                'fast',
                function () {
                    letterEditor.setData('');
                }
            );
        }
    });

    $('#generateBulkLetterForm').on('submit', function (e) {
        e.preventDefault();
        const form = document.getElementById('generateBulkLetterForm');

        document
            .querySelector('#sendLetterBtn')
            .setAttribute('disabled', 'disabled');
        document.querySelector('#sendLetterBtn svg').style.cssText =
            'display: inline-block;';

        let form_data = new FormData(form);
        form_data.append('letter_body', letterEditor.getData());
        axios({
            method: 'post',
            url: route('bulk.communication.send.letter'),
            data: form_data,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
        })
            .then((response) => {
                document
                    .querySelector('#sendLetterBtn')
                    .removeAttribute('disabled');
                document.querySelector('#sendLetterBtn svg').style.cssText =
                    'display: none;';

                if (response.status == 200) {
                    generateBulkLetterModal.hide();

                    successModal.show();
                    document
                        .getElementById('successModal')
                        .addEventListener('shown.tw.modal', function (event) {
                            $('#successModal .successModalTitle').html(
                                'Congratulation!'
                            );
                            $('#successModal .successModalDesc').html(
                                'Letter successfully generated and send it to selected students.'
                            );
                        });

                    setTimeout(function () {
                        successModal.hide();
                    }, 2000);
                }
            })
            .catch((error) => {
                document
                    .querySelector('#sendLetterBtn')
                    .removeAttribute('disabled');
                document.querySelector('#sendLetterBtn svg').style.cssText =
                    'display: none;';
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(
                            error.response.data.errors
                        )) {
                            $(`#generateBulkLetterForm .${key}`).addClass(
                                'border-danger'
                            );
                            $(`#generateBulkLetterForm  .error-${key}`).html(
                                val
                            );
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
    });
    /* Bulk Letter End */

    /* Bulk Email Start */
    $('.sendBulkMailBtn').on('click', function (e) {
        var $btn = $(this);
        var ids = [];

        $('#attendanceReportListTable')
            .find('.tabulator-row.tabulator-selected')
            .each(function () {
                var $row = $(this);
                ids.push($row.find('.student_ids').val());
            });

        if (ids.length > 0) {
            sendBulkMailModal.show();
            document
                .getElementById('sendBulkMailModal')
                .addEventListener('shown.tw.modal', function (event) {
                    $('#sendBulkMailModal [name="student_ids"]').val(
                        ids.join(',')
                    );
                });
        } else {
            warningModal.show();
            document
                .getElementById('warningModal')
                .addEventListener('shown.tw.modal', function (event) {
                    $('#warningModal .warningModalTitle').html('Error Found!');
                    $('#warningModal .warningModalDesc').html(
                        'Selected students not foudn. Please select some students first or contact with the site administrator.'
                    );
                });
        }
    });

    $('#sendBulkMailForm #sendMailsDocument').on('change', function () {
        var inputs = document.getElementById('sendMailsDocument');
        var html = '';
        for (var i = 0; i < inputs.files.length; ++i) {
            var name = inputs.files.item(i).name;
            html +=
                '<div class="mb-1 text-primary font-medium flex justify-start items-center"><i data-lucide="disc" class="w-3 h3 mr-2"></i>' +
                name +
                '</div>';
        }

        $('#sendBulkMailForm .sendMailsDocumentNames').fadeIn().html(html);
        createIcons({
            icons,
            'stroke-width': 1.5,
            nameAttr: 'data-lucide',
        });
    });

    $('#sendBulkMailForm [name="email_template_id"]').on('change', function () {
        var emailTemplateID = $(this).val();
        if (emailTemplateID != '') {
            axios({
                method: 'post',
                url: route('bulk.communication.get.mail.template'),
                data: { emailTemplateID: emailTemplateID },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                        'content'
                    ),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        if (response.data.row.description) {
                            mailEditor.setData(response.data.row.description);
                        } else {
                            mailEditor.setData('');
                        }
                    }
                })
                .catch((error) => {
                    if (error.response) {
                        console.log('error');
                    }
                });
        } else {
            mailEditor.setData('');
        }
    });

    $('#sendBulkMailForm').on('submit', function (e) {
        e.preventDefault();
        const form = document.getElementById('sendBulkMailForm');

        document
            .querySelector('#sendEmailBtn')
            .setAttribute('disabled', 'disabled');
        document.querySelector('#sendEmailBtn svg').style.cssText =
            'display: inline-block;';

        let form_data = new FormData(form);
        form_data.append(
            'file',
            $('#sendBulkMailForm input#sendMailsDocument')[0].files[0]
        );
        form_data.append('body', mailEditor.getData());
        axios({
            method: 'post',
            url: route('bulk.communication.send.email'),
            data: form_data,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
        })
            .then((response) => {
                document
                    .querySelector('#sendEmailBtn')
                    .removeAttribute('disabled');
                document.querySelector('#sendEmailBtn svg').style.cssText =
                    'display: none;';

                if (response.status == 200) {
                    sendBulkMailModal.hide();

                    successModal.show();
                    document
                        .getElementById('successModal')
                        .addEventListener('shown.tw.modal', function (event) {
                            $('#successModal .successModalTitle').html(
                                'Congratulation!'
                            );
                            $('#successModal .successModalDesc').html(
                                response.data.message
                            );
                        });

                    setTimeout(function () {
                        successModal.hide();
                    }, 2000);
                }
            })
            .catch((error) => {
                document
                    .querySelector('#sendEmailBtn')
                    .removeAttribute('disabled');
                document.querySelector('#sendEmailBtn svg').style.cssText =
                    'display: none;';
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(
                            error.response.data.errors
                        )) {
                            $(`#sendBulkMailForm .${key}`).addClass(
                                'border-danger'
                            );
                            $(`#sendBulkMailForm  .error-${key}`).html(val);
                        }
                    } else if (error.response.status == 412) {
                        warningModal.show();
                        document
                            .getElementById('warningModal')
                            .addEventListener(
                                'shown.tw.modal',
                                function (event) {
                                    $('#warningModal .warningModalTitle').html(
                                        'Oops!'
                                    );
                                    $('#warningModal .warningModalDesc').html(
                                        error.response.data.message
                                    );
                                }
                            );

                        setTimeout(function () {
                            warningModal.hide();
                        }, 5000);
                    } else {
                        console.log('error');
                    }
                }
            });
    });
    /* Bulk Email End */

    /* Bulk SMS Start */
    $('.sendBulkSmsBtn').on('click', function (e) {
        var $btn = $(this);
        var ids = [];

        $('#attendanceReportListTable')
            .find('.tabulator-row.tabulator-selected')
            .each(function () {
                var $row = $(this);
                ids.push($row.find('.student_ids').val());
            });

        if (ids.length > 0) {
            sendBulkSmsModal.show();
            document
                .getElementById('sendBulkSmsModal')
                .addEventListener('shown.tw.modal', function (event) {
                    $('#sendBulkSmsModal [name="student_ids"]').val(
                        ids.join(',')
                    );
                });
        } else {
            warningModal.show();
            document
                .getElementById('warningModal')
                .addEventListener('shown.tw.modal', function (event) {
                    $('#warningModal .warningModalTitle').html('Error Found!');
                    $('#warningModal .warningModalDesc').html(
                        'Selected students not foudn. Please select some students first or contact with the site administrator.'
                    );
                });
        }
    });

    $('#smsTextArea').on('keyup', function () {
        var maxlength =
            $(this).attr('maxlength') > 0 && $(this).attr('maxlength') != ''
                ? $(this).attr('maxlength')
                : 0;
        var chars = this.value.length,
            messages = Math.ceil(chars / 160),
            remaining =
                messages * 160 - (chars % (messages * 160) || messages * 160);
        if (chars > 0) {
            if (chars >= maxlength && maxlength > 0) {
                $('#sendBulkSmsModal .modal-content .smsWarning').remove();
                $('#sendBulkSmsModal .modal-content')
                    .prepend(
                        '<div class="alert smsWarning alert-danger-soft show flex items-center mb-0" role="alert"><i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i>Opps! Your maximum character limit exceeded. Please make the text short or contact with administrator.</div>'
                    )
                    .fadeIn();
            } else {
                $('#sendBulkSmsModal .modal-content .smsWarning').remove();
            }
            $('#sendBulkSmsModal .sms_countr').html(
                remaining + ' / ' + messages
            );
        } else {
            $('#sendBulkSmsModal .sms_countr').html('160 / 1');
            $('#sendBulkSmsModal .modal-content .smsWarning').remove();
        }
    });

    $('#sendBulkSmsForm #sms_template_id').on('change', function () {
        var smsTemplateId = $(this).val();
        if (smsTemplateId != '') {
            axios({
                method: 'post',
                url: route('bulk.communication.get.sms.template'),
                data: { smsTemplateId: smsTemplateId },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                        'content'
                    ),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        $('#sendBulkSmsForm #smsTextArea')
                            .val(
                                response.data.row.description
                                    ? response.data.row.description
                                    : ''
                            )
                            .trigger('keyup');
                    }
                })
                .catch((error) => {
                    if (error.response) {
                        console.log('error');
                    }
                });
        } else {
            $('#sendBulkSmsForm #smsTextArea').val('');
            $('#sendBulkSmsForm .sms_countr').html('160 / 1');
        }
    });

    $('#sendBulkSmsForm').on('submit', function (e) {
        e.preventDefault();
        const form = document.getElementById('sendBulkSmsForm');

        document
            .querySelector('#sendSMSBtn')
            .setAttribute('disabled', 'disabled');
        document.querySelector('#sendSMSBtn svg').style.cssText =
            'display: inline-block;';

        let form_data = new FormData(form);
        axios({
            method: 'post',
            url: route('bulk.communication.send.sms'),
            data: form_data,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
        })
            .then((response) => {
                document
                    .querySelector('#sendSMSBtn')
                    .removeAttribute('disabled');
                document.querySelector('#sendSMSBtn svg').style.cssText =
                    'display: none;';

                if (response.status == 200) {
                    sendBulkSmsModal.hide();

                    successModal.show();
                    document
                        .getElementById('successModal')
                        .addEventListener('shown.tw.modal', function (event) {
                            $('#successModal .successModalTitle').html(
                                'Congratulation!'
                            );
                            $('#successModal .successModalDesc').html(
                                response.data.message
                            );
                        });

                    setTimeout(function () {
                        successModal.hide();
                    }, 5000);
                }
            })
            .catch((error) => {
                document
                    .querySelector('#sendSMSBtn')
                    .removeAttribute('disabled');
                document.querySelector('#sendSMSBtn svg').style.cssText =
                    'display: none;';
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(
                            error.response.data.errors
                        )) {
                            $(`#sendBulkSmsForm .${key}`).addClass(
                                'border-danger'
                            );
                            $(`#sendBulkSmsForm  .error-${key}`).html(val);
                        }
                    } else if (error.response.status == 412) {
                        warningModal.show();
                        document
                            .getElementById('warningModal')
                            .addEventListener(
                                'shown.tw.modal',
                                function (event) {
                                    $('#warningModal .warningModalTitle').html(
                                        'Oops!'
                                    );
                                    $('#warningModal .warningModalDesc').html(
                                        error.response.data.message
                                    );
                                }
                            );

                        setTimeout(function () {
                            warningModal.hide();
                        }, 5000);
                    } else {
                        console.log('error');
                    }
                }
            });
    });
    /* Bulk SMS End */
})();
