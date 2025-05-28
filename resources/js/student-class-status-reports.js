import xlsx from 'xlsx';
import {
    createElement,
    createIcons,
    icons,
    MinusCircle,
    PlusCircle,
} from 'lucide';
import Tabulator from 'tabulator-tables';
import TomSelect from 'tom-select';
import ClassicEditor from '@ckeditor/ckeditor5-build-decoupled-document';

('use strict');
var statusListTable = (function () {
    var _tableGen = function (attendance_semester) {
        // Setup Tabulator
        const minusIcon = createElement(MinusCircle);
        minusIcon.setAttribute('stroke-width', '1.5');
        minusIcon.setAttribute(
            'class',
            'w-4 h-4 text-emerald-400 py-auto mx-auto'
        );

        const plusIcon = createElement(PlusCircle);
        plusIcon.setAttribute('stroke-width', '1.5');
        plusIcon.setAttribute(
            'class',
            'w-4 h-4 text-emerald-500 py-auto mx-auto'
        );

        let tableContent = new Tabulator('#statusListTable', {
            ajaxURL: route('reports.class-status.list'),
            ajaxParams: { attendance_semester: attendance_semester },
            pagination: false,
            layout: 'fitColumns',
            responsiveLayout: 'collapse',
            placeholder: 'No matching records found',
            dataTree: true,
            dataTreeStartExpanded: false,
            dataTreeElementColumn: 'expand',
            dataTreeCollapseElement: minusIcon, //fontawesome toggle icon

            dataTreeExpandElement: plusIcon, //fontawesome toggle icon
            columns: [
                {
                    title: '',
                    field: 'expand',
                    headerHozAlign: 'left',
                    headerSort: false,
                    width: 60,
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
                        // html +=
                        //     '<div class="font-medium text-sm whitespace-nowrap uppercase">' +
                        //     cell.getData().term_name +
                        //     '</div>';
                        html += '</div>';
                        return html;
                    },
                },
                {
                    title: 'Schedule',
                    field: 'schedule',
                    headerHozAlign: 'center',
                    hozAlign: 'center',
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
                    headerHozAlign: 'center',
                    hozAlign: 'center',
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
                    headerHozAlign: 'center',
                    hozAlign: 'center',
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
                    title: 'Unheld',
                    field: 'unheld',
                    headerHozAlign: 'center',
                    hozAlign: 'center',
                    headerSort: false,
                    formatter(cell, formatterParams) {
                        return (
                            '<div class="text-xs">' +
                            (cell.getData().unheld > 0 ? cell.getData().unheld : '') +
                            '</div>'
                        );
                    },
                },
                {
                    title: 'Cancel',
                    field: 'cancelled',
                    headerHozAlign: 'center',
                    hozAlign: 'center',
                    headerSort: false,
                    formatter(cell, formatterParams) {
                        return (
                            '<div class="text-xs">' +
                            cell.getData().cancelled +
                            '</div>'
                        );
                    },
                },
                {
                    title: 'Unkown',
                    field: 'unknown',
                    headerHozAlign: 'center',
                    hozAlign: 'center',
                    headerSort: false,
                    formatter(cell, formatterParams) {
                        return (
                            '<div class="text-xs">' +
                            cell.getData().unknown +
                            '</div>'
                        );
                    },
                },
                {
                    title: 'Proxy',
                    field: 'proxy',
                    headerHozAlign: 'center',
                    hozAlign: 'center',
                    headerSort: false,
                    width: 200,
                    formatter(cell, formatterParams) {
                        return (
                            '<div class="text-xs">' +
                            cell.getData().proxy +
                            '</div>'
                        );
                    },
                },
            ],
            ajaxResponse: function (url, params, response) {
                // Log the response to debug
                //console.log('AJAX Response:', response);
                $('#classStatusFormBtn svg').removeClass('hidden');
                $('#classStatusFormBtn svg.loadingClass').addClass('hidden');

                return response; // Return the response data to Tabulator
            },
            ajaxError: function (xhr, textStatus, errorThrown) {
                // Log any AJAX errors
                console.error('AJAX Error:', textStatus, errorThrown);
                $('#classStatusFormBtn svg').removeClass('hidden');
                $('#classStatusFormBtn svg.loadingClass').addClass('hidden');
            },
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

                $('#classStatusFormBtn svg').removeClass('hidden');
                $('#classStatusFormBtn svg.loadingClass').addClass('hidden');
            },
            rowClick: function (e, row) {
                // Check if the row has _children
                let childrenData = row.getData();

                if (childrenData) {
                    let $group = childrenData.group_id;
                    let $course = childrenData.course_id;
                    let $term = childrenData.term_id;

                    // Define the new route
                    const newRoute = route('reports.class-status.schedule', [
                        $group,
                        $course,
                        $term,
                    ]);

                    // Navigate to the new route
                    window.open(newRoute, '_blank');
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
        init: function (attendance_semester) {
            _tableGen(attendance_semester);
        },
    };
})();

(function () {
    let tomOptions = {
        plugins: {
            dropdown_input: {},
        },
        placeholder: 'Search Here...',
        //persist: false,
        create: false,
        allowEmptyOption: true,
        maxItems: 1,
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

    let attendance_semester = new TomSelect('#attendance_semester', tomOptions);
    attendance_semester.setValue('');

    $('#classStatusFormBtn').on('click', function (e) {
        let tthis = $(this);
        e.preventDefault();

        let attendSemester = attendance_semester.getValue();

        if (attendSemester > 0) {
            $('svg', tthis).addClass('hidden');
            $('svg.loadingClass', tthis).removeClass('hidden');
            $('#classStatusForm .reportAlert').remove();
            $('.statusReportListTableWrap').fadeIn();
            statusListTable.init(attendSemester);
        } else {
            $('#classStatusForm .reportAlert').remove();
            $('#classStatusForm').append(
                '<div class="reportAlert alert alert-pending-soft show flex items-center mt-5" role="alert"><i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> Please select an attendance term to proceed.</div>'
            );

            createIcons({
                icons,
                'stroke-width': 1.5,
                nameAttr: 'data-lucide',
            });

            setTimeout(function () {
                $('#classStatusForm .reportAlert').fadeOut();
            }, 3000);
        }
    });

    // const successModal = tailwind.Modal.getOrCreateInstance(
    //     document.querySelector('#successModal')
    // );
    // const confirmModal = tailwind.Modal.getOrCreateInstance(
    //     document.querySelector('#confirmModal')
    // );
    // const warningModal = tailwind.Modal.getOrCreateInstance(
    //     document.querySelector('#warningModal')
    // );
})();
