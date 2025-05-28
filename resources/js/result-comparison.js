import IMask from 'imask';
import xlsx from 'xlsx';
import { createIcons, icons } from 'lucide';
import { createElement, Plus, Minus } from 'lucide';
import Tabulator from 'tabulator-tables';
import TomSelect from 'tom-select';
import Dropzone from 'dropzone';
import Toastify from 'toastify-js';

('use strict');
var submissionTable = (function () {
    var _tableGen = function ($id) {
        // Setup Tabulator
        let assessmentPlanId = $id;
        let tableContent = new Tabulator('#submissionListTable', {
            ajaxURL: route('results-staff-submission.list'),
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
                    width: '220',
                    formatter(cell, formatterParams) {
                        var html =
                            '<a href="' +
                            route('student.show', cell.getData().student_id) +
                            '" class="block">';
                        html +=
                            '<div class="w-10 h-10 intro-x image-fit mr-4 inline-block">';
                        html +=
                            '<img alt="' +
                            cell.getData().first_name +
                            '" class="rounded-full shadow" src="' +
                            cell.getData().student_photo +
                            '">';
                        html += '</div>';
                        html +=
                            '<div class="inline-block relative" style="top: -4px;">';
                        html +=
                            '<div class="font-medium whitespace-nowrap uppercase">' +
                            cell.getData().registration_no +
                            '</div>';
                        html +=
                            '<div class="text-slate-500 text-xs whitespace-nowrap">' +
                            (cell.getData().first_name != ''
                                ? cell.getData().first_name
                                : '') +
                            ' ' +
                            (cell.getData().last_name != ''
                                ? cell.getData().last_name
                                : '') +
                            '</div>';
                        html += '</div>';
                        html += '</a>';
                        return html;
                    },
                },
                {
                    title: 'Module Code',
                    field: 'module_code',
                    headerHozAlign: 'left',
                },
                {
                    title: 'Paper Id',
                    field: 'paper_id',
                    headerHozAlign: 'left',
                },
                {
                    title: 'Grade',
                    field: 'grade',
                    headerHozAlign: 'left',
                },
                {
                    title: 'Submission date',
                    field: 'created_at',
                    headerHozAlign: 'left',
                },
                {
                    title: 'Publish Date',
                    field: 'publish_at',
                    headerHozAlign: 'left',
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

    const warningModal = tailwind.Modal.getOrCreateInstance(
        document.querySelector('#warningModal')
    );

    const finalConfirmUploadTask = tailwind.Modal.getOrCreateInstance(
        document.querySelector('#finalConfirmUploadTask')
    );
    const PublishDateConfirmUploadTask = tailwind.Modal.getOrCreateInstance(
        document.querySelector('#PublishDateConfirmUploadTask')
    );
    
    const finalConfirmDeleteTask = tailwind.Modal.getOrCreateInstance(
        document.querySelector('#finalConfirmDeleteTask')
    );
    $('.checkbox-switch-all').on('change', function () {
        var checked = $(this).is(':checked');
        let selectCount = 0;
        if (checked) {
            $.each($('.fill-box:not(:disabled)'), function () {
                $(this).prop('checked', true);
                selectCount++;
            });
            $.each($('.checkbox-switch-all'), function () {
                $(this).prop('checked', true);
            });
            $('.savedSubmission').removeClass('hidden');
            $('.updateSubmission').removeClass('hidden');
            $('.deleteSubmission').removeClass('hidden');
        } else {
            $.each($('.fill-box:not(:disabled)'), function () {
                $(this).prop('checked', false);
            });

            $.each($('.checkbox-switch-all'), function () {
                $(this).prop('checked', false);
            });
            $('.savedSubmission').addClass('hidden');
            $('.updateSubmission').addClass('hidden');
            $('.deleteSubmission').addClass('hidden');
        }
    });

    $('.fill-box').on('click', function () {
        let checkFound = false;
        $('div.append-input').html('');
        $.each($('.fill-box'), function () {
            let tthis = $(this);
            if (tthis.is(':checked')) {
                // $('#resultFinalForm div.append-input').append(
                //     "<input type='hidden' name='ids[]' value='" +
                //         tthis.val() +
                //         "'>"
                // );
                checkFound = true;
                $('.savedSubmission').removeClass('hidden');
                $('.updateSubmission').removeClass('hidden');
                $('.deleteSubmission').removeClass('hidden');
            }
        });

        if (!checkFound) {
            $('.savedSubmission').addClass('hidden');
            $('.updateSubmission').addClass('hidden');
            $('.deleteSubmission').addClass('hidden');
        }
    });

    // $('#resultFinalForm').on('submit', function (e) {
    //     e.preventDefault();
    //     let planId = $("#resultFinalForm [name='plan_id']").val();

    //     const form = document.getElementById('resultFinalForm');
    //     let form_data = new FormData(form);

    //     $('.update').attr('disabled', 'disabled');
    //     $('.update svg').removeClass('hidden');

    //     axios({
    //         method: 'post',
    //         url: route('results-staff-submission.final', planId),
    //         data: form_data,
    //         headers: {
    //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
    //         },
    //     })
    //         .then((response) => {
    //             if (response.status == 200) {
    //                 $('.update').removeAttr('disabled', 'disabled');
    //                 $('.update svg').addClass('hidden');
    //                 finalConfirmUploadTask.hide();

    //                 successModal.show();
    //                 document
    //                     .getElementById('successModal')
    //                     .addEventListener('shown.tw.modal', function (event) {
    //                         $('#successModal .successModalTitle').html(
    //                             'Congratulations!'
    //                         );
    //                         $('#successModal .successModalDesc').html(
    //                             'Academic years data successfully updated.'
    //                         );
    //                     });

    //                 setTimeout(function () {
    //                     successModal.hide();
    //                     window.location.reload();
    //                 }, 3000);
    //             }
    //         })
    //         .catch((error) => {
    //             $('.update').removeAttr('disabled', 'disabled');
    //             $('.update svg').addClass('hidden');
    //             console.log(error);
    //         });
    // });

    // $('#callModalDeleteTask').on('click', function () {
    //     let $statusBTN = $(this);
    //     let rowID = $statusBTN.attr('data-id');

    //     confirmModal.show();
    //     document
    //         .getElementById('confirmModal')
    //         .addEventListener('shown.tw.modal', function (event) {
    //             $('#confirmModal .confModTitle').html(confModalDelTitle);
    //             $('#confirmModal .confModDesc').html(
    //                 'Do you really want to change status of this record? If yes then please click on the agree btn.'
    //             );
    //             $('#confirmModal .agreeWith').attr('data-id', rowID);
    //             $('#confirmModal .agreeWith').attr('data-action', 'CHANGESTAT');
    //         });
    // });
    $('.updateSubmission').on('click', function () {
        
        let rowID = [];
        document
            .getElementById('finalConfirmUploadTask')
            .addEventListener('shown.tw.modal', function (event) {
                $('#finalConfirmUploadTask .title').html(
                    'Update Result Submission'
                );
                $('#finalConfirmUploadTask .description').html(
                    'Do you really want to update this submission? If yes then please click on the update btn.'
                );
                $('#finalConfirmUploadTask .updateResult').attr('data-action', 'UPDATE');
            });
            
            $('#finalConfirmUploadTask .updateResult').removeClass('btn-primary');
            $('#finalConfirmUploadTask .updateResult').addClass('btn-pending');
    });
    $('.savedSubmission').on('click', function () {
        
        let rowID = [];
        document
            .getElementById('finalConfirmUploadTask')
            .addEventListener('shown.tw.modal', function (event) {
                $('#finalConfirmUploadTask .title').html(
                    'Save New Result'
                );
                $('#finalConfirmUploadTask .description').html(
                    'Do you really want to save this as a new result.'
                );
                $('#finalConfirmUploadTask .updateResult').attr('data-action', 'SAVE');
                $('#finalConfirmUploadTask .updateResult').addClass('btn-primary');
                $('#finalConfirmUploadTask .updateResult').removeClass('btn-pending');
                 
            });
    });

    $('.deleteSubmission').on('click', function () {
        $('#finalConfirmDeleteTask  div.append-input').html('');
        $.each($('.fill-box'), function () {
            let tthis = $(this);
            let result_submission_staff_id = tthis.data('result_submission_staff_id');
            if (tthis.is(':checked')) {
                $('#finalConfirmDeleteTask  div.append-input').append(
                    "<input type='hidden' name='id[]' value='" +
                        result_submission_staff_id +
                        "'>"
                );
            }
        });
        let rowID = [];

        document
            .getElementById('finalConfirmDeleteTask')
            .addEventListener('shown.tw.modal', function (event) {
                $('#finalConfirmDeleteTask .title').html(
                    'Do you really want to delete?'
                );
                $('#finalConfirmDeleteTask .description').html(
                    'Do you really want to delete these selected submission? If yes then please click on the delete btn.'
                );
                $('#finalConfirmDeleteTask .updateResult').attr('data-action', 'DELETE');
            });

            $('#finalConfirmDeleteTask .updateResult').removeClass('btn-primary');
            $('#finalConfirmDeleteTask .updateResult').addClass('btn-pending');

    });

    $('#deleteStaffSubmissionForm').on('submit', function (e) {
        e.preventDefault();
        
        let planId = $(".deleteSubmission").data('planid');
        
        let action = $('#finalConfirmDeleteTask .updateResult').attr('data-action');
        const form = document.getElementById('deleteStaffSubmissionForm');
        let form_data = new FormData(form);

        $('#finalConfirmDeleteTask .updateResult').attr('disabled', 'disabled');
        $('#finalConfirmDeleteTask .updateResult svg').removeClass('hidden');
        let url = '';
        url = route('result.comparison.deleteStaffSubmission',planId);
        
        axios({
            method: 'post',
            url: url,
            data: form_data,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
        })
        .then((response) => {
            if (response.status == 200) {
                $('#finalConfirmDeleteTask .updateResult').removeAttr('disabled', 'disabled');
                $('#finalConfirmDeleteTask .updateResult svg').addClass('hidden');
                finalConfirmDeleteTask.hide();
                successModal.show();
                document
                    .getElementById('successModal')
                    .addEventListener('shown.tw.modal', function (event) {
                        $('#successModal .successModalTitle').html(
                            'Congratulations!'
                        );
                        $('#successModal .successModalDesc').html(
                            'Result Successfully updated.'
                        );
                    });

                setTimeout(function () {
                    successModal.hide();
                    window.location.reload();
                }, 3000);
            }
        })
        .catch((error) => {
            $('#finalConfirmDeleteTask .updateResult').removeAttr('disabled', 'disabled');
            $('#finalConfirmDeleteTask .updateResult svg').addClass('hidden');
            finalConfirmDeleteTask.hide();
            warningModal.show();

            document
                    .getElementById('warningModal')
                    .addEventListener('shown.tw.modal', function (event) {
                        $('#warningModal .warningModalTitle').html(
                            'Result Data missing!'
                        );
                        $('#warningModal .warningModalDesc').html(
                            'Result Data missing.'
                        );
                    });
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        // Extract the index from the key (e.g., employee_id.0 -> 0)
                        let indexMatch = key.match(/\d+/);
                        let index = indexMatch ? indexMatch[0] : '';

                        // Remove the index number from the key (e.g., employee_id.0 -> employee_id)
                        let keyWithoutIndex = key.replace(/\.\d+/, '');
                        $(`#resultComparisonForm .${keyWithoutIndex}-${index}`).addClass('border-danger')
                        $(`#resultComparisonForm  .error-${keyWithoutIndex}-${index}`).html(val)
                    }
                }else if (error.response.status == 302) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        // Extract the index from the key (e.g., employee_id.0 -> 0)
                        let indexMatch = key.match(/\d+/);
                        let index = indexMatch ? indexMatch[0] : '';

                        // Remove the index number from the key (e.g., employee_id.0 -> employee_id)
                        let keyWithoutIndex = key.replace(/\.\d+/, '');
                        $(`#resultComparisonForm .${keyWithoutIndex}-${index}`).addClass('border-danger')
                        $(`#resultComparisonForm  .error-${keyWithoutIndex}-${index}`).html(val)
                    }
                } else {
                    console.log('resultComparisonForm error', error.response.data);
                }
            }
            
        });
    });

    $('#finalConfirmUploadTask .updateResult').on('click', function () {
            $('#resultComparisonForm').submit();
    });
    $('.theTimeField').each(function(){
        var timeMaskModal = IMask(this, {
                overwrite: true,
                autofix: true,
                mask: 'HH:MM',
                blocks: {
                    HH: {
                        mask: IMask.MaskedRange,
                        placeholderChar: 'HH',
                        from: 0,
                        to: 23,
                        maxLength: 2
                    },
                    MM: {
                        mask: IMask.MaskedRange,
                        placeholderChar: 'MM',
                        from: 0,
                        to: 59,
                        maxLength: 2
                    }
                }
        });
    });
    $('#resultComparisonForm').on('submit', function (e) {
        e.preventDefault();
        
        let planId = $(".savedSubmission").data('planid');
        
        let action = $('#finalConfirmUploadTask .updateResult').attr('data-action');
        const form = document.getElementById('resultComparisonForm');
        let form_data = new FormData(form);

        $('.updateResult').attr('disabled', 'disabled');
        $('.updateResult svg').removeClass('hidden');
        let url = '';
        if(action == 'SAVE'){
            url = route('result.comparison.store',planId);
        }else{
            url = route('result.comparison.update',planId);
        }
        axios({
            method: 'post',
            url: url,
            data: form_data,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
        })
        .then((response) => {
            if (response.status == 200) {
                $('.updateResult').removeAttr('disabled', 'disabled');
                $('.updateResult svg').addClass('hidden');
                finalConfirmUploadTask.hide();
                successModal.show();
                document
                    .getElementById('successModal')
                    .addEventListener('shown.tw.modal', function (event) {
                        $('#successModal .successModalTitle').html(
                            'Congratulations!'
                        );
                        $('#successModal .successModalDesc').html(
                            'Result Successfully updated.'
                        );
                    });

                setTimeout(function () {
                    successModal.hide();
                    window.location.reload();
                }, 3000);
            }
        })
        .catch((error) => {
            $('.updateResult').removeAttr('disabled', 'disabled');
            $('.updateResult svg').addClass('hidden');
            finalConfirmUploadTask.hide();
            warningModal.show();

            document
                    .getElementById('warningModal')
                    .addEventListener('shown.tw.modal', function (event) {
                        $('#warningModal .warningModalTitle').html(
                            'Result Data missing!'
                        );
                        $('#warningModal .warningModalDesc').html(
                            'Result Data missing.'
                        );
                    });
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        // Extract the index from the key (e.g., employee_id.0 -> 0)
                        let indexMatch = key.match(/\d+/);
                        let index = indexMatch ? indexMatch[0] : '';

                        // Remove the index number from the key (e.g., employee_id.0 -> employee_id)
                        let keyWithoutIndex = key.replace(/\.\d+/, '');
                        $(`#resultComparisonForm .${keyWithoutIndex}-${index}`).addClass('border-danger')
                        $(`#resultComparisonForm  .error-${keyWithoutIndex}-${index}`).html(val)
                    }
                }else if (error.response.status == 302) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        // Extract the index from the key (e.g., employee_id.0 -> 0)
                        let indexMatch = key.match(/\d+/);
                        let index = indexMatch ? indexMatch[0] : '';

                        // Remove the index number from the key (e.g., employee_id.0 -> employee_id)
                        let keyWithoutIndex = key.replace(/\.\d+/, '');
                        $(`#resultComparisonForm .${keyWithoutIndex}-${index}`).addClass('border-danger')
                        $(`#resultComparisonForm  .error-${keyWithoutIndex}-${index}`).html(val)
                    }
                } else {
                    console.log('resultComparisonForm error', error.response.data);
                }
            }
            
        });
    });
    $('#publishDateForm').on('submit', function (e) {
        e.preventDefault();
        const form = document.getElementById('publishDateForm');
        let form_data = new FormData(form);
        let assessmentPlanId = $("#publishDateForm input[name='id']").val();
        
        $('.updateResult').attr('disabled', 'disabled');
        $('.updateResult svg').removeClass('hidden');
        let url = route('plan-assessment.update',assessmentPlanId)
        
        axios({
            method: 'post',
            url: url,
            data: form_data,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
        })
        .then((response) => {

            if (response.status == 200) {
                $('.updateResult').removeAttr('disabled', 'disabled');
                $('.updateResult svg').addClass('hidden');
                PublishDateConfirmUploadTask.hide();
                successModal.show();
                document
                    .getElementById('successModal')
                    .addEventListener('shown.tw.modal', function (event) {
                        $('#successModal .successModalTitle').html(
                            'Publish Date update!'
                        );
                        $('#successModal .successModalDesc').html(
                            'Publish Date updated Successfully.'
                        );
                    });

                setTimeout(function () {
                    successModal.hide();
                    window.location.reload();
                }, 3000);
            }

        })
        .catch((error) => {

            $('.updateResult').removeAttr('disabled', 'disabled');
            $('.updateResult svg').addClass('hidden');
            PublishDateConfirmUploadTask.hide();
            warningModal.show();

            document
                    .getElementById('warningModal')
                    .addEventListener('shown.tw.modal', function (event) {
                        $('#warningModal .warningModalTitle').html(
                            'Result Publish Date missing!'
                        );
                        $('#warningModal .warningModalDesc').html(
                            'Publish Date couldn\'t update.'
                        );
                    });
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        // Extract the index from the key (e.g., employee_id.0 -> 0)
                        let indexMatch = key.match(/\d+/);
                        let index = indexMatch ? indexMatch[0] : '';

                        // Remove the index number from the key (e.g., employee_id.0 -> employee_id)
                        let keyWithoutIndex = key.replace(/\.\d+/, '');
                        $(`#resultComparisonForm .${keyWithoutIndex}`).addClass('border-danger')
                        $(`#resultComparisonForm  .error-${keyWithoutIndex}`).eq(index).html(val)
                    }
                } else {
                    console.log('resultComparisonForm error', error.response.data);
                }
            }
            
        });
    });
     

})();
