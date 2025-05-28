import IMask from 'imask';
import Tabulator from 'tabulator-tables';

import { createIcons, icons } from 'lucide';
import TomSelect from 'tom-select';

import { Litepicker } from 'litepicker';
import { param } from 'jquery';
import tippy, { roundArrow } from "tippy.js";

('use strict');
var studentNotesListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let studentId =
            $('#studentNotesListTable').attr('data-student') != ''
                ? $('#studentNotesListTable').attr('data-student')
                : '0';
        let queryStr = $('#query-AN').val() != '' ? $('#query-AN').val() : '';
        let status = $('#status-AN').val() != '' ? $('#status-AN').val() : '1';

        let tableContent = new Tabulator('#studentNotesListTable', {
            ajaxURL: route('student.result.previous.list'),
            ajaxParams: {
                studentId: studentId,
                queryStr: queryStr,
                status: status,
            },
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
                    title: 'Module',
                    field: 'course_module_id',
                    headerHozAlign: 'left',
                    formatter(cell, formatterParams) {
                        return cell.getData().course_module;
                    },
                },
                {
                    title: 'Attempt',
                    field: 'attempt',
                    headerHozAlign: 'center',
                    hozAlign: 'center',
                    headerSort: false,
                    formatter(cell, formatterParams) {
                        var btns = '';
                        btns +=
                            '<a data-id="' +
                            cell.getData().id +
                            '" data-coursemodule="' +
                            cell.getData().course_module_id +
                            '" data-student_id="' +
                            cell.getData().student_id +
                            '" data-tw-toggle="modal" data-tw-target="#previous-attempListmodal" class="view_attemptlist text-emerald-700 p-0 w-9 h-9 ml-1">' +
                            cell.getData().attempt +
                            '</a>';

                        return btns;
                    },
                },
                {
                    title: 'Paper ID',
                    field: 'paperID',
                    headerHozAlign: 'left',
                },
                {
                    title: 'Module No',
                    field: 'module_no',
                    headerHozAlign: 'left',
                },
                {
                    title: 'Exam Date',
                    field: 'exam_date',
                    headerHozAlign: 'left',
                },
                {
                    title: 'Grade',
                    field: 'grade',
                    headerHozAlign: 'left',
                },
                {
                    title: 'Status',
                    field: 'status',
                    headerHozAlign: 'left',
                },

                {
                    title: 'Actions',
                    field: 'id',
                    headerSort: false,
                    hozAlign: 'right',
                    headerHozAlign: 'right',
                    width: '120',
                    download: false,
                    formatter(cell, formatterParams) {
                        var btns = '';
                        if (cell.getData().deleted_at == null) {
                            btns +=
                                '<button data-id="' +
                                cell.getData().id +
                                '" data-tw-toggle="modal" data-tw-target="#editNoteModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
                            if (cell.getData().delete_url != null) {
                                btns +=
                                    '<button data-url="' +
                                    cell.getData().delete_url +
                                    '"  data-id="' +
                                    cell.getData().id +
                                    '" class="delete_btn btn btn-danger text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="Trash2" class="w-4 h-4"></i></button>';
                            }
                        } else if (cell.getData().deleted_at != null) {
                            btns +=
                                '<button data-id="' +
                                cell.getData().id +
                                '" class="restore_btn btn btn-linkedin text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="rotate-cw" class="w-4 h-4"></i></button>';
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
                $('.view_attemptlist').on('click', function (e) {
                    let tthis = $(this);
                    let attempt = tthis.text();
                    let studentId = tthis.attr('data-student_id');
                    let courseModule = tthis.attr('data-coursemodule');
                    let attemptPreview = tailwind.Modal.getOrCreateInstance(
                        document.querySelector('#previous-attempListmodal')
                    );
                    $('#studentAttemptPreviousListTable tbody').html(
                        '<tr data-tw-merge class="[&:hover_td]:bg-slate-100 [&:hover_td]:dark:bg-darkmode-300 [&:hover_td]:dark:bg-opacity-50"><td colspan="7" data-tw-merge class="px-3 py-3 border-b dark:border-darkmode-300 border-l border-r border-t relative">Loading...</td></tr>'
                    );
                    axios({
                        method: 'get',
                        url: route('student.result.previous.attemptlist'),
                        params: {
                            student_id: studentId,
                            course_module_id: courseModule,
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                                'content'
                            ),
                        },
                    })
                        .then((response) => {
                            let dataset = response.data.res;
                            let attemptList = '';
                            console.log(dataset);
                            if (dataset.length > 0) {
                                // <td>
                                //     <button data-id="${element.id}" data-tw-toggle="modal" data-tw-target="#editNoteModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>
                                //     <button data-id="${element.id}" data-url="${element.delete_url}" class="delete_btn btn btn-danger text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="Trash2" class="w-4 h-4"></i></button>
                                // </td>
                                dataset.forEach((element, index) => {
                                    attemptList += `<tr data-tw-merge class="[&:hover_td]:bg-slate-100 [&:hover_td]:dark:bg-darkmode-300 [&:hover_td]:dark:bg-opacity-50">
                                    <td data-tw-merge class="px-3 py-3 border-b dark:border-darkmode-300 border-l border-r border-t relative">${
                                        element.semester.name
                                    }</td>
                                    <td data-tw-merge class="px-3 py-3 border-b dark:border-darkmode-300 border-l border-r border-t relative">${
                                        element.module_no
                                    }</td>
                                    <td data-tw-merge class="px-3 py-3 border-b dark:border-darkmode-300 border-l border-r border-t relative">${
                                        element.paperID
                                    }</td>
                                    <td data-tw-merge class="px-3 py-3 border-b dark:border-darkmode-300 border-l border-r border-t relative">${
                                        element.exam_date
                                    }</td>
                                    <td data-tw-merge class="px-3 py-3 border-b dark:border-darkmode-300 border-l border-r border-t relative">${
                                        element.grade
                                    }</td>
                                    <td data-tw-merge class="px-3 py-3 border-b dark:border-darkmode-300 border-l border-r border-t relative">${
                                        element.status
                                    }</td>
                                    <td data-tw-merge class="px-3 py-3 border-b dark:border-darkmode-300 border-l border-r border-t relative">${
                                        element.updated_by
                                            ? element.updated_by
                                            : 'N/A'
                                    }</td>
                                </tr>`;
                                });
                            } else {
                                attemptList = `<tr data-tw-merge class=" text-center [&:hover_td]:bg-slate-100 [&:hover_td]:dark:bg-darkmode-300 [&:hover_td]:dark:bg-opacity-50"><td colspan="7" data-tw-merge class="px-3 py-3 border-b dark:border-darkmode-300 border-l border-r border-t relative">No data found</td></tr>`;
                            }

                            $('#studentAttemptPreviousListTable tbody').html(
                                attemptList
                            );
                            attemptPreview.show();
                        })
                        .catch((error) => {
                            console.log('error');
                        });
                });
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
        $('#tabulator-export-csv-AN').on('click', function (event) {
            tableContent.download('csv', 'data.csv');
        });

        $('#tabulator-export-json-AN').on('click', function (event) {
            tableContent.download('json', 'data.json');
        });

        $('#tabulator-export-xlsx-AN').on('click', function (event) {
            window.XLSX = xlsx;
            tableContent.download('xlsx', 'data.xlsx', {
                sheetName: 'Student Note Details',
            });
        });

        $('#tabulator-export-html-AN').on('click', function (event) {
            tableContent.download('html', 'data.html', {
                style: true,
            });
        });

        // Print
        $('#tabulator-print-AN').on('click', function (event) {
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


    var tomSelectArray = [];
    var tomOptions = {
        plugins: {
            dropdown_input: {},
        },
        placeholder: 'Search Here...',
        persist: false,
        create: false,
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
        tomSelectArray.push(new TomSelect(this, tomOptions));
    });

    if ($('#studentNotesListTable').length) {
        // Init Table
        studentNotesListTable.init();

        // Filter function
        function filterHTMLFormAN() {
            studentNotesListTable.init();
        }

        // On click go button
        $('#tabulator-html-filter-go-AN').on('click', function (event) {
            filterHTMLFormAN();
        });

        // On reset filter form
        $('#tabulator-html-filter-reset-AN').on('click', function (event) {
            $('#query-AN').val('');
            $('#status-AN').val('1');
            termSN.clear(true);
            filterHTMLFormAN();
        });
        $('#studentNotesListTable').on('click', '.edit_btn', function (e) {
            var $btn = $(this);
            var noteId = $btn.attr('data-id');
            axios({
                method: 'post',
                url: route('student.result.previous.edit'),
                data: { id: noteId },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                        'content'
                    ),
                },
            })
                .then((response) => {
                    let dataset = response.data.res;

                    $('#editNoteForm input[name="id"]').val(dataset.id);

                    $('#editNoteForm input[name="paper_id"]').val(
                        dataset.paper_id
                    );
                    $('#editNoteForm input[name="module_no"]').val(
                        dataset.module_no
                    );
                    $('#editNoteForm input[name="exam_date"]').val(
                        dataset.exam_date
                    );
                    $('#editNoteForm input[name="grade"]').val(dataset.grade);
                    $('#editNoteForm input[name="status"]').val(dataset.status);
                    $('#editNoteForm input[name="created_at"]').val(
                        dataset.created_at
                    );
                })
                .catch((error) => {
                    console.log('error');
                });
        });

        $('#editNoteForm').on('submit', function (e) {
            e.preventDefault();
            const form = document.getElementById('editNoteForm');

            document
                .querySelector('#UpdateNote')
                .setAttribute('disabled', 'disabled');
            document.querySelector('#UpdateNote svg').style.cssText =
                'display: inline-block;';

            let form_data = new FormData(form);

            form_data.append('content', editEditor.getData());
            axios({
                method: 'post',
                url: route('student.result.previous.update'),
                data: form_data,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                        'content'
                    ),
                },
            })
                .then((response) => {
                    document
                        .querySelector('#UpdateNote')
                        .removeAttribute('disabled');
                    document.querySelector('#UpdateNote svg').style.cssText =
                        'display: none;';
                    //console.log(response.data.message);
                    //return false;

                    if (response.status == 200) {
                        editNoteModal.hide();

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
                                        'Student Note successfully updated.'
                                    );
                                    $('#successModal .successCloser').attr(
                                        'data-action',
                                        'NONE'
                                    );
                                }
                            );

                        setTimeout(function () {
                            successModal.hide();
                        }, 2000);
                    }
                    studentNotesListTable.init();
                })
                .catch((error) => {
                    document
                        .querySelector('#UpdateNote')
                        .removeAttribute('disabled');
                    document.querySelector('#UpdateNote svg').style.cssText =
                        'display: none;';
                    if (error.response) {
                        if (error.response.status == 422) {
                            for (const [key, val] of Object.entries(
                                error.response.data.errors
                            )) {
                                $(`#editNoteForm .${key}`).addClass(
                                    'border-danger'
                                );
                                $(`#editNoteForm  .error-${key}`).html(val);
                            }
                        } else {
                            console.log('error');
                        }
                    }
                });
        });
    }

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
    });
    if ($('#sortable-table').length > 0) {
        // Initialize an empty object to store the frequency of each grade
        var gradeFrequency = {};

        if (gradeFrequency['P'] == undefined) gradeFrequency['P'] = 0;
        if (gradeFrequency['M'] == undefined) gradeFrequency['M'] = 0;
        if (gradeFrequency['D'] == undefined) gradeFrequency['D'] = 0;
        if (gradeFrequency['C'] == undefined) gradeFrequency['C'] = 0;
        if (gradeFrequency['A'] == undefined) gradeFrequency['A'] = 0;
        if (gradeFrequency['R'] == undefined) gradeFrequency['R'] = 0;
        if (gradeFrequency['U'] == undefined) gradeFrequency['U'] = 0;
        if (gradeFrequency['W'] == undefined) gradeFrequency['W'] = 0;
        // Select all rows from the table body
        $('#sortable-table tbody tr').each(function () {
            // Extract the grade value from the specific column (assuming it's the third column)
            var grade = $(this).find('td').eq(7).text().trim();

            // Increment the count for the extracted grade in the frequency object
            if (gradeFrequency[grade]) {
                gradeFrequency[grade]++;
            } else {
                gradeFrequency[grade] = 1;
            }
        });

        // Display the frequency distribution
        console.log(gradeFrequency);

        // Optionally, you can display the frequency distribution in the HTML
        //completed grabing

        var frequencyHtml = '[ ';
        var completedTotal = 0;
        var outstandingHtml = '[ ';
        var outstandingTotal = 0;
        var totalHtml = '';
        $.each(gradeFrequency, function (grade, count) {
            if(grade=='P' || grade=='M' || grade=='D') {
                if (count != 0) frequencyHtml += grade + ': ' + count + ' ';
                completedTotal += count;
            }
            else {
                if (count != 0) outstandingHtml += grade + ': ' + count + ' ';
                outstandingTotal += count;
            }

        });
        frequencyHtml += ' ]';
        outstandingHtml += ' ]';
        totalHtml +=
            '[ ' + $('#sortable-table tbody tr').length + ' ]';

        // Append the frequency distribution to a specific element
        $('#frequency-distribution').data('content', frequencyHtml);
        $('#frequency-distribution').html("Completed: "+completedTotal);
        $('#outstanding-distribution').data('content', outstandingHtml);
        $('#outstanding-distribution').html('Outstanding: '+outstandingTotal);
        $('#total-distribution').data('content', totalHtml);
        $('#total-distribution').html('Total: '+$('#sortable-table tbody tr').length);
        $(".tabltooltip").each(function () {
            let tipyyoptions = {
                content: $(this).data('content'),
            };
            tippy(this, {
                arrow: roundArrow,
                animation: "shift-away",
                ...tipyyoptions,
            });
        })

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
