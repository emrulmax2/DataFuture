import IMask from 'imask';
import xlsx from 'xlsx';
import { createIcons, icons } from 'lucide';
import { createElement, Plus, Minus } from 'lucide';
import Tabulator from 'tabulator-tables';
import TomSelect from 'tom-select';
import Dropzone from 'dropzone';
import Toastify from 'toastify-js';

('use strict');

// Mirror tmParticipantInitials()/tmParticipantAvatarStyle() in plan-tasks.js so the modal's
// student avatars use the same initials and colour as the Participants table.
function submissionInitials(first, last, fallback) {
    const f = String(first || '').trim();
    const l = String(last || '').trim();
    const fb = String(fallback || 'LC').trim();
    const firstInitial = (f || fb).charAt(0);
    const lastInitial = (l || fb.charAt(1) || firstInitial).charAt(0);
    return (firstInitial + lastInitial).toUpperCase();
}

function submissionAvatarColor(seed) {
    const colors = ['#7a4fa3', '#137a70', '#2f8f5b', '#c94f7c', '#b5602f', '#2f5fa1', '#a13f6b', '#4a7a2f', '#b3261e', '#0d7c73'];
    let hash = 0;
    String(seed || 'student').split('').forEach(function (char) {
        hash = ((hash * 31) + char.charCodeAt(0)) >>> 0;
    });
    return colors[hash % colors.length];
}

// Colour a grade chip by its meaning (code-name string, e.g. "P-Pass", "A-Absent").
function submissionGradeStyle(grade) {
    const g = String(grade || '').toLowerCase();
    if (g.includes('distinction')) return ['#7a4fa3', '#f0e9f7', '#ddccec'];
    if (g.includes('merit')) return ['#2f6fb0', '#e8f1f9', '#c5ddf0'];
    if (g.includes('pass')) return ['#0d7c73', '#e4f1ee', '#c4e2da'];
    if (g.includes('absent') || g.includes('fail') || g.startsWith('a-') || g.startsWith('f-')) return ['#c0392b', '#fbeceb', '#f2cfca'];
    return ['#93a09d', '#f4f5f4', '#e6e8e3'];
}

function submissionEscape(value) {
    return String(value == null ? '' : value).replace(/[&<>"']/g, function (ch) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ch];
    });
}

var submissionTable = (function () {
    var _tableGen = function ($id) {
        // Setup Tabulator
        let assessmentPlanId = $id;
        let tableContent = new Tabulator('#submissionListTable', {
            ajaxURL: route('result-submission.list'),
            ajaxParams: { assessmentPlanId: assessmentPlanId },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: 'remote',
            paginationSize: 25,
            paginationSizeSelector: [25, 50, 100],
            layout: 'fitColumns',
            responsiveLayout: 'collapse',
            placeholder: 'No matching records found',
            columns: [
                {
                    title: 'S/N',
                    field: 'sl',
                    headerSort: false,
                    width: '30',
                },
                {
                    title: 'Student',
                    field: 'registration_no',
                    headerHozAlign: 'left',
                    width: '240',
                    formatter(cell, formatterParams) {
                        const data = cell.getData();
                        const reg = submissionEscape(data.registration_no);
                        const name = submissionEscape(
                            [data.first_name, data.last_name].filter(Boolean).join(' ')
                        );
                        const initials = submissionInitials(
                            data.first_name,
                            data.last_name,
                            data.registration_no
                        );
                        const color = submissionAvatarColor(
                            (data.first_name || '') + (data.last_name || '') + reg
                        );
                        const avatar = data.student_photo
                            ? '<span class="tm-sub-avatar"><img src="' + submissionEscape(data.student_photo) + '" alt="' + reg + '"></span>'
                            : '<span class="tm-sub-avatar" style="background:' + color + ';">' + initials + '</span>';
                        return (
                            '<a href="' +
                            route('student.show', data.student_id) +
                            '" class="tm-sub-student">' +
                            avatar +
                            '<span style="min-width:0;">' +
                            '<span class="tm-sub-reg">' + reg + '</span>' +
                            '<span class="tm-sub-name">' + name + '</span>' +
                            '</span>' +
                            '</a>'
                        );
                    },
                },
                {
                    title: 'Module',
                    field: 'module_code',
                    headerHozAlign: 'left',
                },
                {
                    title: 'Paper Id',
                    field: 'paper_id',
                    headerHozAlign: 'left',
                    formatter(cell) {
                        const v = cell.getValue();
                        return v
                            ? '<span class="tm-sub-mono">' + submissionEscape(v) + '</span>'
                            : '<span class="tm-sub-mono" style="color:#c3ccc9;">—</span>';
                    },
                },
                {
                    title: 'Grade',
                    field: 'grade',
                    headerHozAlign: 'left',
                    formatter(cell) {
                        const v = cell.getValue();
                        if (!v) return '';
                        const c = submissionGradeStyle(v);
                        return (
                            '<span class="tm-sub-grade" style="color:' + c[0] +
                            ';background:' + c[1] + ';border:1px solid ' + c[2] + ';">' +
                            submissionEscape(v) + '</span>'
                        );
                    },
                },
                {
                    title: 'Submitted',
                    field: 'created_at',
                    headerHozAlign: 'left',
                },
                {
                    title: 'Published',
                    field: 'publish_at',
                    headerHozAlign: 'left',
                    formatter(cell) {
                        const v = cell.getValue();
                        return v ? submissionEscape(v) : '<span style="color:#c3ccc9;">—</span>';
                    },
                },
                {
                    title: 'Uploaded By',
                    field: 'created_by',
                    headerHozAlign: 'left',
                    width: 200,
                },
            ],
            renderComplete() {
                createIcons({
                    icons,
                    'stroke-width': 1.5,
                    nameAttr: 'data-lucide',
                });
                const columnLists = this.getColumns();
                if (columnLists.length > 0) {
                    const lastColumn = columnLists[columnLists.length - 1];
                    const currentWidth = lastColumn.getWidth();
                    lastColumn.setWidth(currentWidth - 1);
                }   
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
    };
    return {
        init: function ($id) {
            _tableGen($id);
        },
    };
})();

(function () {
    submissionTable.init(1);
    const successModal = tailwind.Modal.getOrCreateInstance(
        document.querySelector('#successModal')
    );
    const confirmModal = tailwind.Modal.getOrCreateInstance(
        document.querySelector('#confirmModal')
    );
    const warningModal = tailwind.Modal.getOrCreateInstance(
        document.querySelector('#warningModal')
    );
    const endClassModal = tailwind.Modal.getOrCreateInstance(
        document.querySelector('#endClassModal')
    );
    const uploadSubmissionDocumentModal = tailwind.Modal.getOrCreateInstance(
        document.querySelector('#uploadSubmissionDocumentModal')
    );
    const finalConfirmUploadTask = tailwind.Modal.getOrCreateInstance(
        document.querySelector('#finalConfirmUploadTask')
    );

    const uploadSubmissionDocumentModalEl = document.getElementById(
        'uploadSubmissionDocumentModal'
    );

    /* Start Dropzone */
    if ($('#uploadDocumentForm').length > 0) {
        let dzError = false;
        let errorData = [];
        Dropzone.autoDiscover = false;
        Dropzone.options.uploadDocumentForm = {
            autoProcessQueue: false,
            maxFiles: 1,
            acceptedFiles: '.xls,.xlsx,.csv,',
            addRemoveLinks: true,
            thumbnailWidth: 100,
            thumbnailHeight: 100,
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
            //console.log(response);
        });

        drzn1.on('success', function (file, response) {
            //console.log(response);
            dzError = false;
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
            console.log(dzError);
            if (!dzError) {
                successModal.show();
                document
                    .getElementById('successModal')
                    .addEventListener('shown.tw.modal', function (event) {
                        $('#successModal .successModalTitle').html(
                            'Congratulation!'
                        );
                        $('#successModal .successModalDesc').html(
                            'Applicant document successfully uploaded.'
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
            } else {
                errorData.message;
                const errorDataSet = errorData.errors;
                let html =
                    '<ul class=" list-decimal pl-5 font-medium text-sm">';

                errorData.errors.forEach((element) => {
                    html += '<li>' + element + '</li>';
                });
                html += '</ul>';
                $('#displayError').removeClass('hidden');

                $('#displayError .errorList').html('Students Data Error Found!');
                $('#displayError .errorMessage').html(errorData.message);

                $('#displayError .error-students').html(html);

                document
                    .getElementById('warningModal')
                    .addEventListener('shown.tw.modal', function (event) {
                        $('#warningModal .warningModalTitle').html(
                            'Upload Failed!'
                        );
                        $('#warningModal .warningModalDesc').html(
                            "Data Couldn't be uploaded due to mismatched data."
                        );
                        $('#warningModal .warningCloser').attr(
                            'data-action',
                            'DISMISS'
                        );
                    });

                warningModal.show();
                setTimeout(function () {
                    warningModal.hide();
                    //window.location.reload();
                }, 2000);
            }
        });

        $('#uploadEmpDocBtn').on('click', function (e) {
            e.preventDefault();
            document
                .querySelector('#uploadEmpDocBtn')
                .setAttribute('disabled', 'disabled');
            document.querySelector('#uploadEmpDocBtn svg').style.cssText =
                'display: inline-block;';

            if (drzn1.files.length > 0) {
                if (
                    $(
                        '#uploadSubmissionDocumentModal [name="assessmentPlanId"]'
                    ).val() > 0
                ) {
                    let assessmentPlanId = $(
                        '#uploadSubmissionDocumentModal [name="assessmentPlanId"]'
                    ).val();
                    $(
                        '#uploadSubmissionDocumentModal [name="assessment_plan_id"]'
                    ).val(assessmentPlanId);
                    drzn1.processQueue();
                } else {
                    $(
                        '#uploadSubmissionDocumentModal .modal-content .uploadError'
                    ).remove();
                    $('#uploadSubmissionDocumentModal .modal-content').prepend(
                        '<div class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Please select the Assessment.</div>'
                    );

                    createIcons({
                        icons,
                        'stroke-width': 1.5,
                        nameAttr: 'data-lucide',
                    });

                    setTimeout(function () {
                        $(
                            '#uploadSubmissionDocumentModal .modal-content .uploadError'
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
                $('#uploadDocumentModal .modal-content .uploadError').remove();
                $('#uploadDocumentModal .modal-content').prepend(
                    '<div class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Please select at least one file.</div>'
                );

                createIcons({
                    icons,
                    'stroke-width': 1.5,
                    nameAttr: 'data-lucide',
                });

                setTimeout(function () {
                    $(
                        '#uploadDocumentModal .modal-content .uploadError'
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
    $('.edit_btn_submission').on('click', function () {
        let $statusBTN = $(this);
        let rowID = $statusBTN.attr('data-assesmentPlanId');
        submissionTable.init(rowID);
    });
    $('#checkbox-switch-all').on('change', function () {
        var checked = $(this).is(':checked');
        if (checked) {
            $.each($('.fill-box'), function () {
                $(this).prop('checked', true);
            });
            $('#savedSubmission').removeClass('hidden');
        } else {
            $.each($('.fill-box'), function () {
                $(this).prop('checked', false);
            });

            $('#savedSubmission').addClass('hidden');
        }
    });

    $('#savedSubmission').on('click', function () {
        $('div.append-input').html('');
        $.each($('.fill-box'), function () {
            let tthis = $(this);
            if (tthis.is(':checked')) {
                $('#resultFinalForm div.append-input').append(
                    "<input type='hidden' name='ids[]' value='" +
                        tthis.val() +
                        "'>"
                );
            }
        });
    });

    $('#resultFinalForm').on('submit', function (e) {
        e.preventDefault();
        let planId = $("#resultFinalForm [name='plan_id']").val();

        const form = document.getElementById('resultFinalForm');
        let form_data = new FormData(form);

        $('.update').attr('disabled', 'disabled');
        $('.update svg').removeClass('hidden');

        axios({
            method: 'post',
            url: route('result-submission.final', planId),
            data: form_data,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
        })
            .then((response) => {
                if (response.status == 200) {
                    $('.update').removeAttr('disabled', 'disabled');
                    $('.update svg').addClass('hidden');
                    finalConfirmUploadTask.hide();

                    successModal.show();
                    document
                        .getElementById('successModal')
                        .addEventListener('shown.tw.modal', function (event) {
                            $('#successModal .successModalTitle').html(
                                'Congratulations!'
                            );
                            $('#successModal .successModalDesc').html(
                                'Result data successfully updated.'
                            );
                        });

                    setTimeout(function () {
                        successModal.hide();
                        window.location.reload();
                    }, 3000);
                }
            })
            .catch((error) => {
                $('.update').removeAttr('disabled', 'disabled');
                $('.update svg').addClass('hidden');
                console.log(error);
            });
    });

    $('#callModalDeleteTask').on('click', function () {
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
})();
