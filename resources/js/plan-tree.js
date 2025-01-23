import IMask from 'imask';
import xlsx from 'xlsx';
import { createIcons, icons } from 'lucide';
import Tabulator from 'tabulator-tables';
import TomSelect from 'tom-select';
import tippy, { roundArrow } from 'tippy.js';

('use strict');
var classPlanTreeListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let courses = $('#classPlanTreeListTable').attr('data-course');
        //let instance_term = $("#classPlanTreeListTable").attr('data-term');
        let group = $('#classPlanTreeListTable').attr('data-group');
        let year = $('#classPlanTreeListTable').attr('data-year');
        let attendanceSemester = $('#classPlanTreeListTable').attr(
            'data-attendanceSemester'
        );

        let tableContent = new Tabulator('#classPlanTreeListTable', {
            ajaxURL: route('plans.tree.list'),
            ajaxParams: {
                courses: courses,
                group: group,
                year: year,
                attendanceSemester: attendanceSemester,
            }, //instance_term: instance_term,
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
            selectable: true,
            columns: [
                {
                    formatter: 'rowSelection',
                    titleFormatter: 'rowSelection',
                    hozAlign: 'left',
                    headerHozAlign: 'left',
                    width: '50',
                    headerSort: false,
                    download: false,
                    cellClick: function (e, cell) {
                        cell.getRow().toggleSelect();
                    },
                },
                {
                    title: 'ID',
                    field: 'id',
                    width: 120,
                    headerHozAlign: 'left',
                    formatter(cell, formatterParams) {
                        var html = '<div>';
                        html += cell.getData().id;
                        html +=
                            cell.getData().child_id > 0
                                ? ' - ' + cell.getData().child_id
                                : '';
                        html += '</div>';
                        return html;
                    },
                },
                {
                    title: 'Module',
                    field: 'module',
                    headerHozAlign: 'left',
                    formatter(cell, formatterParams) {
                        var html = '<div class="break-all whitespace-normal">';
                        html +=
                            '<a class="font-medium text-primary whitespace-normal break-all" href="' +
                            route(
                                'tutor-dashboard.plan.module.show',
                                cell.getData().id
                            ) +
                            '">';
                        html += cell.getData().module;
                        html +=
                            cell.getData().class_type != ''
                                ? '<br/>' + cell.getData().class_type
                                : '';
                        html += '</a>';
                        html += '</div>';
                        return html;
                    },
                },
                {
                    title: 'Tutor',
                    field: 'tutor',
                    headerHozAlign: 'left',
                },
                {
                    title: 'Personal Tutor',
                    field: 'personalTutor',
                    headerHozAlign: 'left',
                },
                {
                    title: 'No of Student',
                    field: 'on_of_student',
                    headerHozAlign: 'left',
                    formatter(cell, formatterParams) {
                        if (cell.getData().assigned_count > 0) {
                            return (
                                '<a data-id="' +
                                cell.getData().id +
                                '" data-tw-toggle="modal" data-tw-target="#viewAssignedStudentModal" href="javascript:void(0);" class="viewAssignStdBtn text-primary underline font-medium">' +
                                cell.getData().on_of_student +
                                '</a>'
                            );
                        } else {
                            return (
                                '<span>' +
                                cell.getData().on_of_student +
                                '</span>'
                            );
                        }
                    },
                },
                {
                    title: 'Theory Days',
                    field: 'id',
                    headerHozAlign: 'left',
                    formatter(cell, formatterParams) {
                        var html = '';
                        if (
                            cell.getData().class_type != 'Tutorial' &&
                            cell.getData().parent_id == 0
                        ) {
                            if (cell.getData().dates > 0) {
                                html +=
                                    '<a target="_blank" href="' +
                                    route('plan.dates', cell.getData().id) +
                                    '" class="text-primary font-medium"><u>' +
                                    cell.getData().dates +
                                    '</u></a>';
                            } else {
                                html += '<span>0</span>';
                            }
                            html += '<div>';
                            html +=
                                '<span>' + cell.getData().day + '</span><br/>';
                            html += '<span>' + cell.getData().time + '</span>';
                            html += '</div>';
                        }

                        return html;
                    },
                },
                {
                    title: 'Tutorial Days',
                    field: 'id',
                    headerHozAlign: 'left',
                    formatter(cell, formatterParams) {
                        var html = '';
                        var tutorials = cell.getData().tutorial
                            ? cell.getData().tutorial
                            : false;
                        if (
                            cell.getData().class_type == 'Tutorial' &&
                            cell.getData().parent_id == 0
                        ) {
                            if (cell.getData().dates > 0) {
                                html +=
                                    '<a target="_blank" href="' +
                                    route('plan.dates', cell.getData().id) +
                                    '" class="text-primary font-medium"><u>' +
                                    cell.getData().dates +
                                    '</u></a>';
                            } else {
                                html += '<span>0</span>';
                            }
                            html += '<div>';
                            html +=
                                '<span>' + cell.getData().day + '</span><br/>';
                            html += '<span>' + cell.getData().time + '</span>';
                            html += '</div>';

                            if (cell.getData().parent_id == 0) {
                                html +=
                                    '<button  data-id="' +
                                    cell.getData().id +
                                    '" data-tw-toggle="modal" data-tw-target="#syncTutorialModal" type="button" class="syncBtn btn btn-twitter rounded-full w-6 h-6 inline-flex justify-center items-center p-0"><i data-lucide="refresh-cw" class="w-4 h-4"></i></button>';
                            }
                        } else if (tutorials) {
                            if (tutorials.dates > 0) {
                                html +=
                                    '<a target="_blank" href="' +
                                    route('plan.dates', tutorials.id) +
                                    '" class="text-primary font-medium"><u>' +
                                    tutorials.dates +
                                    '</u></a>';
                            } else {
                                html += '<span>0</span>';
                            }
                            html += '<div>';
                            html += '<span>' + tutorials.day + '</span><br/>';
                            html += '<span>' + tutorials.time + '</span>';
                            html += '</div>';
                            html +=
                                '<input type="hidden" class="classPlanId" name="classPlanIds[]" value="' +
                                tutorials.id +
                                '"/>';
                        } else if (
                            cell.getData().class_type == 'Theory' &&
                            cell.getData().parent_id == 0 &&
                            !tutorials
                        ) {
                            html +=
                                '<button data-theory="' +
                                cell.getData().id +
                                '" data-tutorial="0" data-tw-toggle="modal" data-tw-target="#tutorialDetailsModal" type="button" class="tutorial_btn btn-round btn btn-success text-xs text-white px-2 py-1"><i data-lucide="plus-circle" class="w-4 h-4 mr-1"></i> Add Tutorial</a>';
                        }

                        return html;
                    },
                },
                {
                    title: 'Actions',
                    field: 'id',
                    headerSort: false,
                    hozAlign: 'center',
                    headerHozAlign: 'left',
                    download: false,
                    formatter(cell, formatterParams) {
                        var tutorials = cell.getData().tutorial
                            ? cell.getData().tutorial
                            : false;
                        var submissionAvailable = cell.getData().submissionAvailable;
                        var uploadAssesment = cell.getData().uploadAssesment;
                        var SubmissionDone = cell.getData().submissionDone;
                        
                        var btns = '';
                        if (cell.getData().deleted_at == null) {
                            btns +=
                                '<button data-id="' +
                                cell.getData().id +
                                '" data-tw-toggle="modal" data-tw-target="#editPlanModal" type="button" class="edit_btn btn-round btn btn-primary text-xs text-white px-2 py-1 mr-1 mb-1"><i data-lucide="Pencil" class="w-4 h-4 mr-1"></i> Edit Plan</button>';
                            if (tutorials) {
                                btns +=
                                    '<button data-theory="' +
                                    cell.getData().id +
                                    '" data-tutorial="' +
                                    tutorials.id +
                                    '" data-tw-toggle="modal" data-tw-target="#tutorialDetailsModal" type="button" class="tutorial_btn btn-round btn btn-primary text-xs text-white px-2 py-1 mb-1"><i data-lucide="Pencil" class="w-4 h-4 mr-1"></i> Edit Tutorial</button>';
                            }
                            if(uploadAssesment==1) {
                                let btnColor = '';
                                let btnText = '';
                                if(SubmissionDone=="Yes") {
                                    btnColor = 'btn-success';
                                    btnText = 'View Result';
                                }else {
                                    btnColor = 'btn-pending';
                                    btnText = 'Upload Submission';
                                }
                            btns +=
                                '<a href="' +
                                route(
                                    'results-staff-submission.show',
                                    cell.getData().id
                                ) +
                                '" type="button" class=" btn-round btn '+btnColor+' text-xs text-white px-2 py-1 mr-1 mb-1"><i data-lucide="Pencil" class="w-4 h-4 mr-1"></i> '+btnText+'</a>';
                            }
                            if(submissionAvailable==1) {
                                if(SubmissionDone!="Yes") {
                                btns +=
                                    '<a href="' +
                                    route(
                                        'result.comparison',
                                        cell.getData().id
                                    ) +
                                    '" type="button" class=" btn-round btn text-success text-xs btn-outline-success px-2 py-1 mr-1 mb-1"><i data-lucide="Pencil" class="w-4 h-4 mr-1"></i> View Submission</a>';
                                }
                            }
                            //btns +='<button data-id="'+cell.getData().id +'"  class="delete_btn btn btn-danger text-xs text-white btn-round px-2 py-1 ml-1"><i data-lucide="Trash2" class="w-4 h-4 mr-1"></i> Delete</button>';
                        } else if (cell.getData().deleted_at != null) {
                            //btns += '<button data-id="'+cell.getData().id +'"  class="restore_btn btn btn-linkedin text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="rotate-cw" class="w-4 h-4"></i></button>';
                        }

                        btns +=
                            '<input type="hidden" class="classPlanId" name="classPlanIds[]" value="' +
                            cell.getData().id +
                            '"/>';

                        return (
                            '<div style="white-space: normal; text-align: left;">' +
                            btns +
                            '</div>'
                        );
                    },
                },
            ],
            rowSelectionChanged: function (data, rows) {
                var ids = [];
                if (rows.length > 0) {
                    $('#generateDaysBtn, #bulkCommunication').fadeIn();
                } else {
                    $('#generateDaysBtn, #bulkCommunication').fadeOut();
                }
            },
            renderComplete() {
                createIcons({
                    icons,
                    'stroke-width': 1.5,
                    nameAttr: 'data-lucide',
                });
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
        $('#tabulator-export-csv-CPL').on('click', function (event) {
            tableContent.download('csv', 'data.csv');
        });

        $('#tabulator-export-json-CPL').on('click', function (event) {
            tableContent.download('json', 'data.json');
        });

        $('#tabulator-export-xlsx-CPL').on('click', function (event) {
            window.XLSX = xlsx;
            tableContent.download('xlsx', 'data.xlsx', {
                sheetName: 'Plan Tree Details',
            });
        });

        $('#tabulator-export-html-CPL').on('click', function (event) {
            tableContent.download('html', 'data.html', {
                style: true,
            });
        });

        // Print
        $('#tabulator-print-CPL').on('click', function (event) {
            tableContent.print();
        });
    };
    return {
        init: function () {
            _tableGen();
        },
    };
})();

var assignedStudentModalListTable = (function () {
    var _tableGen = function (plan_id) {
        let tableContent = new Tabulator('#assignedStudentModalListTable', {
            ajaxURL: route('plans.tree.assigned.list'),
            ajaxParams: { plan_id: plan_id },
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
                    title: 'Reg. No',
                    field: 'registration_no',
                    headerHozAlign: 'left',
                    formatter(cell, formatterParams) {
                        var html = '<div class="block">';
                        html +=
                            '<div class="w-10 h-10 intro-x image-fit mr-4 inline-block">';
                        html +=
                            '<img alt="' +
                            cell.getData().first_name +
                            '" class="rounded-full shadow" src="' +
                            cell.getData().photo_url +
                            '">';
                        html += '</div>';
                        html +=
                            '<div class="inline-block relative" style="top: -13px;">';
                        html +=
                            '<div class="font-medium whitespace-nowrap uppercase">' +
                            cell.getData().registration_no +
                            '</div>';

                        html += '</div>';
                        html += '</div>';
                        return html;
                    },
                },
                {
                    title: 'First Name',
                    field: 'first_name',
                    headerHozAlign: 'left',
                },
                {
                    title: 'Last Name',
                    field: 'last_name',
                    headerHozAlign: 'left',
                },
                {
                    title: '',
                    field: 'full_time',
                    headerHozAlign: 'left',
                    headerSort: false,
                    formatter(cell, formatterParams) {
                        let day = false;
                        if (cell.getData().full_time == 1)
                            day = 'text-slate-900';
                        else day = 'text-amber-600';
                        var html = '<div class="flex">';
                        html +=
                            '<div class="w-8 h-8 ' +
                            day +
                            ' intro-x inline-flex">';
                        if (cell.getData().full_time == 1) {
                            html +=
                                '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="sunset" class="lucide lucide-sunset w-6 h-6"><path d="M12 10V2"></path><path d="m4.93 10.93 1.41 1.41"></path><path d="M2 18h2"></path><path d="M20 18h2"></path><path d="m19.07 10.93-1.41 1.41"></path><path d="M22 22H2"></path><path d="m16 6-4 4-4-4"></path><path d="M16 18a4 4 0 0 0-8 0"></path></svg>';
                        } else {
                            html +=
                                '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="sun" class="lucide lucide-sun w-6 h-6"><circle cx="12" cy="12" r="4"></circle><path d="M12 2v2"></path><path d="M12 20v2"></path><path d="m4.93 4.93 1.41 1.41"></path><path d="m17.66 17.66 1.41 1.41"></path><path d="M2 12h2"></path><path d="M20 12h2"></path><path d="m6.34 17.66-1.41 1.41"></path><path d="m19.07 4.93-1.41 1.41"></path></svg>';
                        }
                        html += '</div>';

                        html += '</div>';
                        createIcons({
                            icons,
                            'stroke-width': 1.5,
                            nameAttr: 'data-lucide',
                        });

                        return html;
                    },
                },
                {
                    title: 'Semester',
                    field: 'semester',
                    headerSort: false,
                    headerHozAlign: 'left',
                },
                {
                    title: 'Course',
                    field: 'course',
                    headerSort: false,
                    headerHozAlign: 'left',
                },
                {
                    title: 'Status',
                    field: 'status_id',
                    headerHozAlign: 'left',
                },
            ],
            renderComplete() {
                createIcons({
                    icons,
                    'stroke-width': 1.5,
                    nameAttr: 'data-lucide',
                });
            },
            rowClick: function (e, row) {
                window.open(row.getData().url, '_blank');
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
        init: function (plan_id) {
            _tableGen(plan_id);
        },
    };
})();

(function () {
    let tomOptions = {
        plugins: {
            dropdown_input: {},
            remove_button: {
                title: 'Remove this item',
            },
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

    let tomOptionsSingle = {
        plugins: {
            dropdown_input: {},
        },
        placeholder: 'Search Here...',
        //persist: false,
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

    $('.theTimeField').each(function () {
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
                    maxLength: 2,
                },
                MM: {
                    mask: IMask.MaskedRange,
                    placeholderChar: 'MM',
                    from: 0,
                    to: 59,
                    maxLength: 2,
                },
            },
        });
    });

    let tomOptionMult;
    let assigned_user_ids = new TomSelect(
        document.getElementById('assigned_user_ids'),
        tomOptions
    );
    let tutorId = new TomSelect(
        document.getElementById('tutor_id'),
        tomOptionsSingle
    );
    let personalTutorId = new TomSelect(
        document.getElementById('personal_tutor_id'),
        tomOptionsSingle
    );

    let tutorial_rooms_id = new TomSelect(
        '#tutorial_rooms_id',
        tomOptionsSingle
    );
    let tutorial_personal_tutor_id = new TomSelect(
        '#tutorial_personal_tutor_id',
        tomOptionsSingle
    );

    const warningModalCP = tailwind.Modal.getOrCreateInstance(
        document.querySelector('#warningModalCP')
    );
    const successModalCP = tailwind.Modal.getOrCreateInstance(
        document.querySelector('#successModalCP')
    );
    const editPlanModal = tailwind.Modal.getOrCreateInstance(
        document.querySelector('#editPlanModal')
    );
    const confirmModalCP = tailwind.Modal.getOrCreateInstance(
        document.querySelector('#confirmModalCP')
    );
    const assignManagerOrCoOrdinatorModal = tailwind.Modal.getOrCreateInstance(
        document.querySelector('#assignManagerOrCoOrdinatorModal')
    );
    const viewAssignedStudentModal = tailwind.Modal.getOrCreateInstance(
        document.querySelector('#viewAssignedStudentModal')
    );
    const syncTutorialModal = tailwind.Modal.getOrCreateInstance(
        document.querySelector('#syncTutorialModal')
    );
    const tutorialDetailsModal = tailwind.Modal.getOrCreateInstance(
        document.querySelector('#tutorialDetailsModal')
    );

    const assignManagerOrCoOrdinatorModalEl = document.getElementById(
        'assignManagerOrCoOrdinatorModal'
    );
    assignManagerOrCoOrdinatorModalEl.addEventListener(
        'hide.tw.modal',
        function (event) {
            $('#assignManagerOrCoOrdinatorModalEl .acc__input-error').html('');
            $('#assignManagerOrCoOrdinatorModalEl input[type="hidden"]').val(
                ''
            );
            assigned_user_ids.clear(true);
        }
    );

    let confModalDelTitle = 'Are you sure?';

    const editPlanModalEl = document.getElementById('editPlanModal');
    editPlanModalEl.addEventListener('hide.tw.modal', function (event) {
        $('#editPlanModal .acc__input-error').html('');
        $('#editPlanModal .modal-body select').val('');
        $('#editPlanModal .modal-body textarea').val('');
        $('#editPlanModal .modal-body input:not([type="radio"])').val('');
        $('#editPlanModal input[name="id"]').val('0');
        $('#editPlanModal input[type="radio"]').prop('checked', false);
        $('#editPlanModal .tutorWrap').fadeOut('fase');
        $('#editPlanModal .PersonalTutorWrap').fadeOut('fase');
        tutorId.clear(true);
        personalTutorId.clear(true);
    });

    const tutorialDetailsModalEl = document.getElementById(
        'tutorialDetailsModal'
    );
    tutorialDetailsModalEl.addEventListener('hide.tw.modal', function (event) {
        $('#tutorialDetailsModal .tutorial_modal_title').html('Plan Details');
        $('#tutorialDetailsModal .acc__input-error').html('');
        $('#tutorialDetailsModal .modal-body select').val('');
        $('#tutorialDetailsModal .modal-body textarea').val('');
        $('#tutorialDetailsModal .modal-body input:not([type="radio"])').val(
            ''
        );
        $('#tutorialDetailsModal input[type="radio"]').prop('checked', false);
        $('#tutorialDetailsModal input[name="theory_id"]').val('0');
        $('#tutorialDetailsModal input[name="tutorial_id"]').val('0');
        tutorial_rooms_id.clear(true);
        tutorial_personal_tutor_id.clear(true);
    });

    const viewAssignedStudentModalEl = document.getElementById(
        'viewAssignedStudentModal'
    );
    viewAssignedStudentModalEl.addEventListener(
        'hide.tw.modal',
        function (event) {
            $('#assignedStudentModalListTable')
                .html('')
                .removeClass('tabulator')
                .removeAttr('tabulator-layout')
                .removeAttr('role');
        }
    );

    const syncTutorialModalEl = document.getElementById('syncTutorialModal');
    syncTutorialModalEl.addEventListener('hide.tw.modal', function (event) {
        $('#syncTutorialModal #sync_plan_id').html(
            '<option value="">Please Select</option>'
        );
        $('#syncTutorialModal [name="id"]').val('0');
    });

    /* View Assigned Student List START */
    $('.classPlanTreeResultWrap').on(
        'click',
        '#classPlanTreeListTable .viewAssignStdBtn',
        function (e) {
            var $theLink = $(this);
            var plan_id = $theLink.attr('data-id');

            assignedStudentModalListTable.init(plan_id);
        }
    );
    /* View Assigned Student List END */

    /* Get Term By AC Year */
    $('.classPlanTree').on('click', '.academicYear', function (e) {
        e.preventDefault();
        var $link = $(this);
        var $parent = $link.parent('li');

        if ($parent.hasClass('hasData')) {
            $('> .theChild', $parent).slideToggle();
            $parent.toggleClass('opened');
        } else {
            $('svg', $link).fadeIn();
            var academicyear = $link.attr('data-yearid');
            axios({
                method: 'post',
                url: route('plans.tree.get.semester'),
                data: { academicyear: academicyear },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                        'content'
                    ),
                },
            })
                .then((response) => {
                    $('svg', $link).fadeOut();
                    if (response.status == 200) {
                        $parent.addClass('hasData opened');
                        $parent.append(response.data.htm);

                        $('.classPlanTreeResultWrap').fadeOut(
                            'fast',
                            function () {
                                $('.classPlanTreeResultWrap').html('');
                                $('.classPlanTreeResultNotice').fadeIn(
                                    'fast',
                                    function () {
                                        createIcons({
                                            icons,
                                            'stroke-width': 1.5,
                                            nameAttr: 'data-lucide',
                                        });
                                    }
                                );
                            }
                        );

                        tailwind.svgLoader();
                        createIcons({
                            icons,
                            'stroke-width': 1.5,
                            nameAttr: 'data-lucide',
                        });
                    }
                })
                .catch((error) => {
                    if (error.response) {
                        $('svg', $link).fadeOut();
                        console.log('error');
                    }
                });
        }
    });
    /* Get Term By AC Year */

    /* Get Course By Term */
    $('.classPlanTree').on('click', '.theTerm', function (e) {
        e.preventDefault();
        var $link = $(this);
        var $parent = $link.parent('li');

        if ($parent.hasClass('hasData')) {
            $('> .theChild', $parent).slideToggle();
            $parent.toggleClass('opened');
        } else {
            $('svg', $link).fadeIn();
            var academicYearId = $link.attr('data-yearid');
            var attendanceSemester = $link.attr('data-attendanceSemester');
            axios({
                method: 'post',
                url: route('plans.tree.get.courses'),
                data: {
                    academicYearId: academicYearId,
                    attendanceSemester: attendanceSemester,
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                        'content'
                    ),
                },
            })
                .then((response) => {
                    $('svg', $link).fadeOut();
                    if (response.status == 200) {
                        $parent.addClass('hasData opened');
                        $parent.append(response.data.htm);

                        $('.classPlanTreeResultWrap').fadeOut(
                            'fast',
                            function () {
                                $('.classPlanTreeResultWrap').html('');
                                $('.classPlanTreeResultNotice').fadeIn(
                                    'fast',
                                    function () {
                                        createIcons({
                                            icons,
                                            'stroke-width': 1.5,
                                            nameAttr: 'data-lucide',
                                        });
                                    }
                                );
                            }
                        );

                        tailwind.svgLoader();
                        createIcons({
                            icons,
                            'stroke-width': 1.5,
                            nameAttr: 'data-lucide',
                        });
                    }
                })
                .catch((error) => {
                    if (error.response) {
                        $('svg', $link).fadeOut();
                        console.log('error');
                    }
                });
        }
    });
    /* Get Course By Term */

    /* Get Group By Course */
    $('.classPlanTree').on('click', '.theCourse', function (e) {
        e.preventDefault();
        var $link = $(this);
        var $parent = $link.parent('li');

        if ($parent.hasClass('hasData')) {
            $('> .theChild', $parent).slideToggle();
            $parent.toggleClass('opened');
        } else {
            $('svg', $link).fadeIn();
            var courseId = $link.attr('data-courseid');
            var attendanceSemester = $link.attr('data-attendanceSemester');
            var academicYearId = $link.attr('data-yearid');
            axios({
                method: 'post',
                url: route('plans.tree.get.groups'),
                data: {
                    courseId: courseId,
                    attendanceSemester: attendanceSemester,
                    academicYearId: academicYearId,
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                        'content'
                    ),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        $('svg', $link).fadeOut();
                        $parent.addClass('hasData opened');
                        $parent.append(response.data.htm);

                        $('.classPlanTreeResultWrap').fadeOut(
                            'fast',
                            function () {
                                $('.classPlanTreeResultWrap').html('');
                                $('.classPlanTreeResultNotice').fadeIn(
                                    'fast',
                                    function () {
                                        createIcons({
                                            icons,
                                            'stroke-width': 1.5,
                                            nameAttr: 'data-lucide',
                                        });
                                    }
                                );
                            }
                        );

                        tailwind.svgLoader();
                        createIcons({
                            icons,
                            'stroke-width': 1.5,
                            nameAttr: 'data-lucide',
                        });

                        $parent.find('.tooltip').each(function () {
                            let toolTIpOptions = {
                                content: $(this).attr('title'),
                                placement: 'right',
                            };
                            $(this).removeAttr('title');

                            tippy(this, {
                                arrow: roundArrow,
                                animation: 'shift-away',
                                ...toolTIpOptions,
                            });
                        });
                    }
                })
                .catch((error) => {
                    if (error.response) {
                        $('svg', $link).fadeOut();
                        console.log('error');
                    }
                });
        }
    });
    /* Get Group By Course */

    /* Get Module By Group */
    $('.classPlanTree').on('click', '.theGroup', function (e) {
        e.preventDefault();
        var $link = $(this);
        var $parent = $link.parent('li');

        if (!$parent.hasClass('hasData')) {
            $parent.siblings('li').removeClass('hasData opened');
            $('svg', $link).fadeIn();
            var courseId = $link.attr('data-courseid');
            //var termId = $link.attr('data-termid');
            var academicYearId = $link.attr('data-yearid');
            var attendancesemester = $link.attr('data-attendancesemester');
            var groupId = $link.attr('data-groupid');

            //termId : termId,
            axios({
                method: 'post',
                url: route('plans.tree.get.module'),
                data: {
                    courseId: courseId,
                    attendancesemester: attendancesemester,
                    academicYearId: academicYearId,
                    groupId: groupId,
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                        'content'
                    ),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        $('svg', $link).fadeOut();
                        $parent.addClass('hasData opened');

                        $('.classPlanTreeResultNotice').fadeOut(
                            'fast',
                            function () {
                                $('.classPlanTreeResultWrap').fadeIn(
                                    'fast',
                                    function () {
                                        $('.classPlanTreeResultWrap').html(
                                            response.data.htm
                                        );

                                        if (
                                            $('#classPlanTreeListTable')
                                                .length > 0
                                        ) {
                                            classPlanTreeListTable.init();
                                        }
                                        createIcons({
                                            icons,
                                            'stroke-width': 1.5,
                                            nameAttr: 'data-lucide',
                                        });

                                        $('.classPlanTreeResultWrap')
                                            .find('.tooltip')
                                            .each(function () {
                                                let toolTIpOptions = {
                                                    content:
                                                        $(this).attr('title'),
                                                    placement: 'right',
                                                };
                                                $(this).removeAttr('title');

                                                tippy(this, {
                                                    arrow: roundArrow,
                                                    animation: 'shift-away',
                                                    ...toolTIpOptions,
                                                });
                                            });
                                    }
                                );
                            }
                        );

                        tailwind.svgLoader();
                        createIcons({
                            icons,
                            'stroke-width': 1.5,
                            nameAttr: 'data-lucide',
                        });
                    }
                })
                .catch((error) => {
                    if (error.response) {
                        $('svg', $link).fadeOut();
                        console.log('error');
                    }
                });
        }
    });
    /* Get Modul By Group */

    /* Edit Plan */
    $('.classPlanTreeResultWrap').on(
        'click',
        '#classPlanTreeListTable .edit_btn',
        function (e) {
            var $btn = $(this);
            var planid = $btn.attr('data-id');

            axios({
                method: 'get',
                url: route('plans.tree.edit', planid),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                        'content'
                    ),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        let dataset = response.data;

                        $('#editPlanModal .termName').html(
                            dataset.plan.term ? dataset.plan.term : ''
                        );
                        $('#editPlanModal .courseName').html(
                            dataset.plan.course ? dataset.plan.course : ''
                        );
                        $('#editPlanModal .groupName').html(
                            dataset.plan.group ? dataset.plan.group : ''
                        );

                        $(
                            '#editPlanModal select[name="module_creation_id"]'
                        ).html(
                            dataset.plan.modules ? dataset.plan.modules : ''
                        );
                        $('#editPlanModal select[name="rooms_id"]').val(
                            dataset.plan.rooms_id ? dataset.plan.rooms_id : ''
                        );

                        var classType = dataset.plan.class_type ? dataset.plan.class_type : '';
                        $('#editPlanModal select[name="class_type"]').val(dataset.plan.class_type ? dataset.plan.class_type : '');
                        
                        if (classType == 'Tutorial' || classType == 'Seminar') {
                            $('#editPlanModal .tutorWrap').fadeOut(
                                'fast',
                                function () {
                                    tutorId.clear(true);
                                }
                            );
                            $('#editPlanModal .PersonalTutorWrap').fadeIn('fast', function(){
                                personalTutorId.addItem(dataset.plan.personal_tutor_id);
                            });
                        } else {
                            $('#editPlanModal .tutorWrap').fadeIn(
                                'fast',
                                function () {
                                    tutorId.addItem(dataset.plan.tutor_id);
                                }
                            );
                            
                            $('#editPlanModal .PersonalTutorWrap').fadeOut('fast', function(){
                                personalTutorId.clear(true);
                            });
                        }
                        //$('#editPlanModal select[name="tutor_id"]').val(dataset.plan.tutor_id ? dataset.plan.tutor_id : '');
                        //$('#editPlanModal select[name="personal_tutor_id"]').val(dataset.plan.personal_tutor_id ? dataset.plan.personal_tutor_id : '');
                        /*if (dataset.plan.personal_tutor_id > 0) {
                            personalTutorId.addItem(
                                dataset.plan.personal_tutor_id
                            );
                        } else {
                            personalTutorId.clear(true);
                        }*/
                        //$('#editPlanModal input[name="module_enrollment_key"]').val(dataset.plan.module_enrollment_key ? dataset.plan.module_enrollment_key : '');
                        $('#editPlanModal input[name="start_time"]').val(
                            dataset.plan.start_time
                                ? dataset.plan.start_time
                                : ''
                        );
                        $('#editPlanModal input[name="end_time"]').val(
                            dataset.plan.end_time ? dataset.plan.end_time : ''
                        );
                        $('#editPlanModal input[name="submission_date"]').val(
                            dataset.plan.submission_date
                                ? dataset.plan.submission_date
                                : ''
                        );
                        $('#editPlanModal textarea[name="virtual_room"]').val(
                            dataset.plan.virtual_room
                                ? dataset.plan.virtual_room
                                : ''
                        );
                        $('#editPlanModal textarea[name="note"]').val(
                            dataset.plan.note ? dataset.plan.note : ''
                        );

                        if (dataset.plan.sat == 1) {
                            $('#editPlanModal #day_sat').prop('checked', true);
                        } else if (dataset.plan.sun == 1) {
                            $('#editPlanModal #day_sun').prop('checked', true);
                        } else if (dataset.plan.mon == 1) {
                            $('#editPlanModal #day_mon').prop('checked', true);
                        } else if (dataset.plan.tue == 1) {
                            $('#editPlanModal #day_tue').prop('checked', true);
                        } else if (dataset.plan.wed == 1) {
                            $('#editPlanModal #day_wed').prop('checked', true);
                        } else if (dataset.plan.thu == 1) {
                            $('#editPlanModal #day_thu').prop('checked', true);
                        } else if (dataset.plan.fri == 1) {
                            $('#editPlanModal #day_fri').prop('checked', true);
                        }

                        $('#editPlanModal input[name="id"]').val(planid);
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        }
    );

    $('#editPlanModal [name="class_type"]').on('change', function (e) {
        var $classType = $(this);
        var classType = $classType.val();

        if (classType == 'Tutorial' || classType == 'Seminar') {
            $('#editPlanModal .tutorWrap').fadeOut('fast', function () {
                tutorId.clear(true);
            });
        } else {
            $('#editPlanModal .tutorWrap').fadeIn('fast', function () {
                tutorId.clear(true);
            });
        }
    });
    $('#editPlanForm').on('submit', function (e) {
        e.preventDefault();
        const form = document.getElementById('editPlanForm');

        document
            .querySelector('#updatePlans')
            .setAttribute('disabled', 'disabled');
        document.querySelector('#updatePlans svg').style.cssText =
            'display: inline-block;';

        let form_data = new FormData(form);
        axios({
            method: 'post',
            url: route('plans.tree.update'),
            data: form_data,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
        })
            .then((response) => {
                if (response.status == 200) {
                    console.log(response.data);
                    document
                        .querySelector('#updatePlans')
                        .removeAttribute('disabled');
                    document.querySelector('#updatePlans svg').style.cssText =
                        'display: none;';

                    editPlanModal.hide();

                    successModalCP.show();
                    document
                        .getElementById('successModalCP')
                        .addEventListener('shown.tw.modal', function (event) {
                            $('#successModalCP .successModalTitleCP').html(
                                'Congratulation!'
                            );
                            $('#successModalCP .successModalDescCP').html(
                                'Class Plan date successfully updated.'
                            );
                        });
                }
                classPlanTreeListTable.init();
            })
            .catch((error) => {
                document
                    .querySelector('#updatePlans')
                    .removeAttribute('disabled');
                document.querySelector('#updatePlans svg').style.cssText =
                    'display: none;';
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(
                            error.response.data.errors
                        )) {
                            $(`#editPlanForm .${key}`).addClass(
                                'border-danger'
                            );
                            $(`#editPlanForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
    });
    /* Edit Plan */

    /* Delete & Restore Plan */
    $('.classPlanTreeResultWrap').on(
        'click',
        '#classPlanTreeListTable .delete_btn',
        function (e) {
            e.preventDefault();
            let $deleteBTN = $(this);
            let rowID = $deleteBTN.attr('data-id');

            confirmModalCP.show();
            document
                .getElementById('confirmModalCP')
                .addEventListener('shown.tw.modal', function (event) {
                    $('#confirmModalCP .confModTitleCP').html(
                        confModalDelTitle
                    );
                    $('#confirmModalCP .confModDescCP').html(
                        'Do you really want to delete these record? Click on agree to continue.'
                    );
                    $('#confirmModalCP .agreeWithCP').attr('data-id', rowID);
                    $('#confirmModalCP .agreeWithCP').attr(
                        'data-action',
                        'DELETE'
                    );
                });
        }
    );

    $('.classPlanTreeResultWrap').on(
        'click',
        '#classPlanTreeListTable .restore_btn',
        function (e) {
            e.preventDefault();
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confirmModalCP.show();
            document
                .getElementById('confirmModalCP')
                .addEventListener('shown.tw.modal', function (event) {
                    $('#confirmModalCP .confModTitleCP').html(
                        confModalDelTitle
                    );
                    $('#confirmModalCP .confModDescCP').html(
                        'Do you really want to restore these record? Click on agree to continue.'
                    );
                    $('#confirmModalCP .agreeWithCP').attr('data-id', courseID);
                    $('#confirmModalCP .agreeWithCP').attr(
                        'data-action',
                        'RESTORE'
                    );
                });
        }
    );

    $('#confirmModalCP .agreeWithCP').on('click', function (e) {
        e.preventDefault();
        let $agreeBTN = $(this);
        let recordID = $agreeBTN.attr('data-id');
        let action = $agreeBTN.attr('data-action');

        $('#confirmModalDP button').attr('disabled', 'disabled');
        if (action == 'DELETE') {
            axios({
                method: 'delete',
                url: route('plans.tree.destory', recordID),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                        'content'
                    ),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        $('#confirmModalCP button').removeAttr('disabled');
                        confirmModalCP.hide();

                        successModalCP.show();
                        document
                            .getElementById('successModalCP')
                            .addEventListener(
                                'shown.tw.modal',
                                function (event) {
                                    $(
                                        '#successModalCP .successModalTitleCP'
                                    ).html('Congratulation!');
                                    $(
                                        '#successModalCP .successModalDescCP'
                                    ).html(
                                        'Class Plan successfully deleted form the list.'
                                    );
                                }
                            );
                    }
                    classPlanTreeListTable.init();
                })
                .catch((error) => {
                    console.log(error);
                });
        } else if (action == 'RESTORE') {
            axios({
                method: 'post',
                url: route('plans.tree.restore', recordID),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                        'content'
                    ),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        $('#confirmModalCP button').removeAttr('disabled');
                        confirmModalCP.hide();

                        successModalCP.show();
                        document
                            .getElementById('successModalCP')
                            .addEventListener(
                                'shown.tw.modal',
                                function (event) {
                                    $(
                                        '#successModalCP .successModalTitleCP'
                                    ).html('Congratulation!');
                                    $(
                                        '#successModalCP .successModalDescCP'
                                    ).html(
                                        'Class Plan successfully restored to the list.'
                                    );
                                }
                            );
                    }
                    classPlanTreeListTable.init();
                })
                .catch((error) => {
                    console.log(error);
                });
        }
    });
    /* Delete & Restore Plan */

    /* Generate Days For Plan */
    $('.classPlanTreeResultWrap').on('click', '#generateDaysBtn', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var ids = [];

        $('#classPlanTreeListTable')
            .find('.tabulator-row.tabulator-selected')
            .each(function () {
                var $row = $(this);
                $row.find('.classPlanId').each(function () {
                    ids.push($(this).val());
                });
                //ids.push($row.find('.classPlanId').val());
            });

        if (ids.length > 0) {
            $btn.attr('disabled', 'disabled');
            $('svg', $btn).fadeIn('fast');

            axios({
                method: 'post',
                url: route('plan.dates.generate'),
                data: { classPlansIds: ids },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                        'content'
                    ),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        $btn.removeAttr('disabled', 'disabled');
                        $('svg', $btn).fadeOut('fast');
                        successModalCP.show();
                        document
                            .getElementById('successModalCP')
                            .addEventListener(
                                'shown.tw.modal',
                                function (event) {
                                    $(
                                        '#successModalCP .successModalTitleCP'
                                    ).html(response.data.title);
                                    $(
                                        '#successModalCP .successModalDescCP'
                                    ).html(response.data.Message);
                                }
                            );

                        setTimeout(function () {
                            successModalCP.hide();
                        }, 3000);

                        classPlanTreeListTable.init();
                    }
                })
                .catch((error) => {
                    $btn.removeAttr('disabled', 'disabled');
                    $('svg', $btn).fadeOut('fast');
                    if (
                        error.response.status == 422 ||
                        error.response.status == 304
                    ) {
                        warningModalCP.show();
                        document
                            .getElementById('warningModalCP')
                            .addEventListener(
                                'shown.tw.modal',
                                function (event) {
                                    $(
                                        '#warningModalCP .warningModalTitleCP'
                                    ).html(error.response.data.title);
                                    $(
                                        '#warningModalCP .warningModalDescCP'
                                    ).html(error.response.data.Message);
                                }
                            );
                        console.log(error.response);
                        setTimeout(function () {
                            warningModalCP.hide();
                        }, 3000);

                        classPlanTreeListTable.init();
                    } else {
                        console.log('error');
                    }
                });
        } else {
            warningModalCP.show();
            document
                .getElementById('warningModalCP')
                .addEventListener('shown.tw.modal', function (event) {
                    $('#warningModalCP .warningModalTitleCP').html(
                        'Error Found!'
                    );
                    $('#warningModalCP .warningModalDescCP').html(
                        'Selected plans id not foudn. Please select some plan first or contact with the site administrator.'
                    );
                });
        }
    });
    /* Generate Days For Plan */

    /* Assign Manager & Co-Ordinator */
    $(document).on('click', '.assignManager', function (e) {
        e.preventDefault();
        var $btn = $(this);

        var yearid =
            typeof $btn.attr('data-yearid') !== 'undefined' &&
            $btn.attr('data-yearid') !== false
                ? $btn.attr('data-yearid')
                : false;
        var termid =
            typeof $btn.attr('data-attendanceSemester') !== 'undefined' &&
            $btn.attr('data-attendanceSemester') !== false
                ? $btn.attr('data-attendanceSemester')
                : false;
        var courseid =
            typeof $btn.attr('data-courseid') !== 'undefined' &&
            $btn.attr('data-courseid') !== false
                ? $btn.attr('data-courseid')
                : false;
        var groupid =
            typeof $btn.attr('data-groupid') !== 'undefined' &&
            $btn.attr('data-groupid') !== false
                ? $btn.attr('data-groupid')
                : false;

        assignManagerOrCoOrdinatorModal.show();
        $('.assignRoleTitle').text('Manager');

        axios({
            method: 'post',
            url: route('plans.get.assign.details'),
            data: {
                yearid: yearid,
                termid: termid,
                courseid: courseid,
                groupid: groupid,
                type: 'Manager',
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
        })
            .then((response) => {
                if (response.status == 200) {
                    let plans = response.data.plans;
                    let participants = response.data.participants;
                    let title = response.data.title;
                    $('#assignManagerOrCoOrdinatorModal .theModTitle').html(
                        title
                    );

                    if (plans.length > 0) {
                        $(
                            '#assignManagerOrCoOrdinatorModal input[name="plan_ids"]'
                        ).val(plans.join());
                    }

                    if (participants.length > 0) {
                        $.each(participants, function (name, value) {
                            assigned_user_ids.addItem(value, true);
                        });
                    } else {
                        assigned_user_ids.clear(true);
                    }
                    $(
                        '#assignManagerOrCoOrdinatorModal input[name="type"]'
                    ).val('Manager');
                }
            })
            .catch((error) => {
                if (
                    error.response.status == 422 ||
                    error.response.status == 304
                ) {
                    console.log('error');
                }
            });
    });

    $(document).on('click', '.assignCoOrdinator', function (e) {
        e.preventDefault();
        var $btn = $(this);

        var yearid =
            typeof $btn.attr('data-yearid') !== 'undefined' &&
            $btn.attr('data-yearid') !== false
                ? $btn.attr('data-yearid')
                : false;
        var termid =
            typeof $btn.attr('data-attendanceSemester') !== 'undefined' &&
            $btn.attr('data-attendanceSemester') !== false
                ? $btn.attr('data-attendanceSemester')
                : false;
        var courseid =
            typeof $btn.attr('data-courseid') !== 'undefined' &&
            $btn.attr('data-courseid') !== false
                ? $btn.attr('data-courseid')
                : false;
        var groupid =
            typeof $btn.attr('data-groupid') !== 'undefined' &&
            $btn.attr('data-groupid') !== false
                ? $btn.attr('data-groupid')
                : false;

        assignManagerOrCoOrdinatorModal.show();
        $('.assignRoleTitle').text('Audit User');

        axios({
            method: 'post',
            url: route('plans.get.assign.details'),
            data: {
                yearid: yearid,
                termid: termid,
                courseid: courseid,
                groupid: groupid,
                type: 'Auditor',
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
        })
            .then((response) => {
                if (response.status == 200) {
                    let plans = response.data.plans;
                    let participants = response.data.participants;
                    let title = response.data.title;
                    $('#assignManagerOrCoOrdinatorModal .theModTitle').html(
                        title
                    );

                    if (plans.length > 0) {
                        $(
                            '#assignManagerOrCoOrdinatorModal input[name="plan_ids"]'
                        ).val(plans.join());
                    }

                    if (participants.length > 0) {
                        $.each(participants, function (name, value) {
                            assigned_user_ids.addItem(value, true);
                        });
                    } else {
                        assigned_user_ids.clear(true);
                    }
                    $(
                        '#assignManagerOrCoOrdinatorModal input[name="type"]'
                    ).val('Auditor');
                }
            })
            .catch((error) => {
                if (
                    error.response.status == 422 ||
                    error.response.status == 304
                ) {
                    console.log('error');
                }
            });
    });

    $('#assignManagerOrCoOrdinatorForm').on('submit', function (e) {
        e.preventDefault();
        var $form = $(this);
        const form = document.getElementById('assignManagerOrCoOrdinatorForm');

        document
            .querySelector('#updateParticipants')
            .setAttribute('disabled', 'disabled');
        document.querySelector('#updateParticipants svg').style.cssText =
            'display: inline-block;';

        let plan_id = $(
            '#assignManagerOrCoOrdinatorForm input[name="plan_ids"]'
        ).val();
        if (plan_id == '') {
            $('participantError', $form).remove();
            $('participantError', $form).prepend(
                '<div class="alert alert-danger-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Plan Id Not found. Please add plan first.</div>'
            );

            createIcons({
                icons,
                'stroke-width': 1.5,
                nameAttr: 'data-lucide',
            });

            setTimeout(function () {
                $('participantError', $form).remove();
            }, 3000);
        } else {
            let form_data = new FormData(form);
            axios({
                method: 'post',
                url: route('plans.assign.participants'),
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
                            .querySelector('#updateParticipants')
                            .removeAttribute('disabled');
                        document.querySelector(
                            '#updateParticipants svg'
                        ).style.cssText = 'display: none;';

                        assignManagerOrCoOrdinatorModal.hide();

                        successModalCP.show();
                        document
                            .getElementById('successModalCP')
                            .addEventListener(
                                'shown.tw.modal',
                                function (event) {
                                    $(
                                        '#successModalCP .successModalTitleCP'
                                    ).html('Congratulation!');
                                    $(
                                        '#successModalCP .successModalDescCP'
                                    ).html(
                                        'Participants are successfully assignd.'
                                    );
                                }
                            );

                        setTimeout(function () {
                            successModalCP.hide();
                        }, 4000);
                    }
                })
                .catch((error) => {
                    document
                        .querySelector('#updateParticipants')
                        .removeAttribute('disabled');
                    document.querySelector(
                        '#updateParticipants svg'
                    ).style.cssText = 'display: none;';
                    if (error.response) {
                        if (error.response.status == 422) {
                            for (const [key, val] of Object.entries(
                                error.response.data.errors
                            )) {
                                $(
                                    `#assignManagerOrCoOrdinatorForm .${key}`
                                ).addClass('border-danger');
                                $(
                                    `#assignManagerOrCoOrdinatorForm  .error-${key}`
                                ).html(val);
                            }
                        } else {
                            console.log('error');
                        }
                    }
                });
        }
    });
    /* Assign Manager & Co-Ordinator */

    /* Plan Visibility Set */
    $(document).on('click', '.visibilityBtn', function (e) {
        e.preventDefault();
        var $btn = $(this);

        var visibility =
            typeof $btn.attr('data-visibility') !== 'undefined' &&
            $btn.attr('data-visibility') !== false
                ? $btn.attr('data-visibility')
                : 1;
        var yearid =
            typeof $btn.attr('data-yearid') !== 'undefined' &&
            $btn.attr('data-yearid') !== false
                ? $btn.attr('data-yearid')
                : false;
        //var termid = ((typeof $btn.attr('data-termid') !== 'undefined' && $btn.attr('data-termid') !== false) ? $btn.attr('data-termid') : false);
        var courseid =
            typeof $btn.attr('data-courseid') !== 'undefined' &&
            $btn.attr('data-courseid') !== false
                ? $btn.attr('data-courseid')
                : false;
        var groupid =
            typeof $btn.attr('data-groupid') !== 'undefined' &&
            $btn.attr('data-groupid') !== false
                ? $btn.attr('data-groupid')
                : false;
        var attendancesemester =
            typeof $btn.attr('data-attendancesemester') !== 'undefined' &&
            $btn.attr('data-attendancesemester') !== false
                ? $btn.attr('data-attendancesemester')
                : false;

        $btn.attr('disabled', 'disabled');

        axios({
            method: 'post',
            url: route('plans.update.visibility'),
            data: {
                yearid: yearid,
                attendancesemester: attendancesemester,
                courseid: courseid,
                groupid: groupid,
                visibility: visibility,
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
        })
            .then((response) => {
                if (response.status == 200) {
                    console.log(response.data);
                    var suc = response.data.suc;
                    var visibilities = response.data.visibility;
                    $btn.removeAttr('disabled')
                        .removeClass('visibility_' + visibilities)
                        .addClass('visibility_' + (visibilities == 1 ? 0 : 1))
                        .attr('data-visibility', visibilities);
                    if (suc == 2) {
                        warningModalCP.show();
                        document
                            .getElementById('warningModalCP')
                            .addEventListener(
                                'shown.tw.modal',
                                function (event) {
                                    $(
                                        '#warningModalCP .warningModalTitleCP'
                                    ).html('Oops!');
                                    $(
                                        '#warningModalCP .warningModalDescCP'
                                    ).html(
                                        'Plans not found under selectd criteria. Please add class plans first.'
                                    );
                                }
                            );

                        setTimeout(function () {
                            warningModalCP.hide();
                        }, 5000);
                    } else {
                        successModalCP.show();
                        document
                            .getElementById('successModalCP')
                            .addEventListener(
                                'shown.tw.modal',
                                function (event) {
                                    $(
                                        '#successModalCP .successModalTitleCP'
                                    ).html('Congratulation!');
                                    $(
                                        '#successModalCP .successModalDescCP'
                                    ).html(
                                        'Plans visibility successfully updated.'
                                    );
                                }
                            );

                        setTimeout(function () {
                            successModalCP.hide();
                        }, 5000);
                    }
                }
            })
            .catch((error) => {
                if (error.response) {
                    console.log('error');
                }
            });
    });
    /* Plan Visibility Set */

    /* Bulk Communication Start */
    $('.classPlanTreeResultWrap').on(
        'click',
        '#bulkCommunication',
        function (e) {
            e.preventDefault();
            var $btn = $(this);
            var ids = [];

            $('#classPlanTreeListTable')
                .find('.tabulator-row.tabulator-selected')
                .each(function () {
                    var $row = $(this);
                    $row.find('.classPlanId').each(function () {
                        ids.push($(this).val());
                    });
                });

            if (ids.length > 0) {
                var url_ids = ids.join('-');
                window.location.href = route('bulk.communication', url_ids);
            } else {
                warningModalCP.show();
                document
                    .getElementById('warningModalCP')
                    .addEventListener('shown.tw.modal', function (event) {
                        $('#warningModalCP .warningModalTitleCP').html(
                            'Error Found!'
                        );
                        $('#warningModalCP .warningModalDescCP').html(
                            'Selected plans id not foudn. Please select some plan first or contact with the site administrator.'
                        );
                    });
            }
        }
    );
    /* Bulk Communication End */

    /* Sync Plan */
    $('.classPlanTreeResultWrap').on(
        'click',
        '#classPlanTreeListTable .syncBtn',
        function (e) {
            e.preventDefault();
            var $theBtn = $(this);
            var plan_id = $theBtn.attr('data-id');

            axios({
                method: 'post',
                url: route('plans.tree.get.theories'),
                data: { plan_id: plan_id },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                        'content'
                    ),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        $('#syncTutorialModal #sync_plan_id').html(
                            response.data.htm
                        );
                        $('#syncTutorialModal [name="id"]').val(plan_id);
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        }
    );

    $('#syncTutorialForm').on('submit', function (e) {
        e.preventDefault();
        var $form = $(this);
        const form = document.getElementById('syncTutorialForm');

        document
            .querySelector('#syncPlanBtn')
            .setAttribute('disabled', 'disabled');
        document.querySelector('#syncPlanBtn svg').style.cssText =
            'display: inline-block;';

        let form_data = new FormData(form);
        axios({
            method: 'post',
            url: route('plans.tree.sync.tutorial'),
            data: form_data,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
        })
            .then((response) => {
                if (response.status == 200) {
                    document
                        .querySelector('#syncPlanBtn')
                        .removeAttribute('disabled');
                    document.querySelector('#syncPlanBtn svg').style.cssText =
                        'display: none;';

                    syncTutorialModal.hide();

                    successModalCP.show();
                    document
                        .getElementById('successModalCP')
                        .addEventListener('shown.tw.modal', function (event) {
                            $('#successModalCP .successModalTitleCP').html(
                                'Congratulation!'
                            );
                            $('#successModalCP .successModalDescCP').html(
                                'Tutorial successfully sync with selected theories.'
                            );
                        });

                    setTimeout(function () {
                        successModalCP.hide();
                    }, 2000);
                }
                classPlanTreeListTable.init();
            })
            .catch((error) => {
                document
                    .querySelector('#syncPlanBtn')
                    .removeAttribute('disabled');
                document.querySelector('#syncPlanBtn svg').style.cssText =
                    'display: none;';
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(
                            error.response.data.errors
                        )) {
                            $(`#syncTutorialForm .${key}`).addClass(
                                'border-danger'
                            );
                            $(`#syncTutorialForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
    });
    /* Sync Plan */

    /* Add or Edit Tutorial Plan */
    $('.classPlanTreeResultWrap').on(
        'click',
        '#classPlanTreeListTable .tutorial_btn',
        function (e) {
            e.preventDefault();
            var $theBtn = $(this);
            var theory_id = $theBtn.attr('data-theory');
            var tutorial_id = $theBtn.attr('data-tutorial');

            $('#tutorialDetailsModal .tutorial_modal_title').text(
                tutorial_id > 0 ? 'Edit Tutorial' : 'Add Tutorial'
            );

            axios({
                method: 'post',
                url: route('plans.tree.get.tutorial'),
                data: { theory_id: theory_id, tutorial_id: tutorial_id },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                        'content'
                    ),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        let dataset = response.data;

                        $('#tutorialDetailsModal .termName').html(
                            dataset.plan.term ? dataset.plan.term : ''
                        );
                        $('#tutorialDetailsModal .courseName').html(
                            dataset.plan.course ? dataset.plan.course : ''
                        );
                        $('#tutorialDetailsModal .moduleName').html(
                            dataset.plan.module ? dataset.plan.module : ''
                        );
                        $('#tutorialDetailsModal .groupName').html(
                            dataset.plan.group ? dataset.plan.group : ''
                        );
                        $('#tutorialDetailsModal .venueName').html(
                            dataset.plan.venue ? dataset.plan.venue : ''
                        );

                        if (dataset.plan.rooms_id > 0) {
                            tutorial_rooms_id.addItem(dataset.plan.rooms_id);
                        } else {
                            tutorial_rooms_id.clear(true);
                        }
                        if (dataset.plan.pt_id > 0) {
                            tutorial_personal_tutor_id.addItem(
                                dataset.plan.pt_id
                            );
                        } else if (dataset.plan.personal_tutor_id > 0) {
                            tutorial_personal_tutor_id.addItem(
                                dataset.plan.personal_tutor_id
                            );
                        } else {
                            tutorial_personal_tutor_id.clear(true);
                        }
                        $('#tutorialDetailsModal input[name="start_time"]').val(
                            dataset.plan.start_time
                                ? dataset.plan.start_time
                                : ''
                        );
                        $('#tutorialDetailsModal input[name="end_time"]').val(
                            dataset.plan.end_time ? dataset.plan.end_time : ''
                        );
                        $(
                            '#tutorialDetailsModal textarea[name="virtual_room"]'
                        ).val(
                            dataset.plan.virtual_room
                                ? dataset.plan.virtual_room
                                : ''
                        );
                        $('#tutorialDetailsModal textarea[name="note"]').val(
                            dataset.plan.note ? dataset.plan.note : ''
                        );

                        if (dataset.plan.sat == 1) {
                            $('#tutorialDetailsModal #tutorial_day_sat').prop(
                                'checked',
                                true
                            );
                        } else if (dataset.plan.sun == 1) {
                            $('#tutorialDetailsModal #tutorial_day_sun').prop(
                                'checked',
                                true
                            );
                        } else if (dataset.plan.mon == 1) {
                            $('#tutorialDetailsModal #tutorial_day_mon').prop(
                                'checked',
                                true
                            );
                        } else if (dataset.plan.tue == 1) {
                            $('#tutorialDetailsModal #tutorial_day_tue').prop(
                                'checked',
                                true
                            );
                        } else if (dataset.plan.wed == 1) {
                            $('#tutorialDetailsModal #tutorial_day_wed').prop(
                                'checked',
                                true
                            );
                        } else if (dataset.plan.thu == 1) {
                            $('#tutorialDetailsModal #tutorial_day_thu').prop(
                                'checked',
                                true
                            );
                        } else if (dataset.plan.fri == 1) {
                            $('#tutorialDetailsModal #tutorial_day_fri').prop(
                                'checked',
                                true
                            );
                        }

                        $('#tutorialDetailsModal input[name="theory_id"]').val(
                            theory_id
                        );
                        $(
                            '#tutorialDetailsModal input[name="tutorial_id"]'
                        ).val(tutorial_id);
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        }
    );

    $('#tutorialDetailsForm').on('submit', function (e) {
        e.preventDefault();
        var $form = $(this);
        const form = document.getElementById('tutorialDetailsForm');

        document
            .querySelector('#tutorialPlanSVBtn')
            .setAttribute('disabled', 'disabled');
        document.querySelector('#tutorialPlanSVBtn svg').style.cssText =
            'display: inline-block;';

        let form_data = new FormData(form);
        axios({
            method: 'post',
            url: route('plans.tree.store.tutorial'),
            data: form_data,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
        })
            .then((response) => {
                if (response.status == 200) {
                    document
                        .querySelector('#tutorialPlanSVBtn')
                        .removeAttribute('disabled');
                    document.querySelector(
                        '#tutorialPlanSVBtn svg'
                    ).style.cssText = 'display: none;';

                    tutorialDetailsModal.hide();

                    successModalCP.show();
                    document
                        .getElementById('successModalCP')
                        .addEventListener('shown.tw.modal', function (event) {
                            $('#successModalCP .successModalTitleCP').html(
                                'Congratulation!'
                            );
                            $('#successModalCP .successModalDescCP').html(
                                'Tutorial plan data successfully saved.'
                            );
                        });

                    setTimeout(function () {
                        successModalCP.hide();
                    }, 2000);
                }
                classPlanTreeListTable.init();
            })
            .catch((error) => {
                document
                    .querySelector('#tutorialPlanSVBtn')
                    .removeAttribute('disabled');
                document.querySelector('#tutorialPlanSVBtn svg').style.cssText =
                    'display: none;';
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(
                            error.response.data.errors
                        )) {
                            $(`#tutorialDetailsForm .${key}`).addClass(
                                'border-danger'
                            );
                            $(`#tutorialDetailsForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
    });
    /* Add or Edit Tutorial Plan */
})();
