import xlsx from 'xlsx';
import { createIcons, icons } from 'lucide';
import Tabulator from 'tabulator-tables';
import TomSelect from 'tom-select';

import dayjs from 'dayjs';
import Litepicker from 'litepicker';
import axios from 'axios';
import { set } from 'lodash';

('use strict');

var hrPayslipListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $('#query-HY').val() != '' ? $('#query-HY').val() : '';
        let status = $('#status-HY').val() != '' ? $('#status-HY').val() : '';

        let tableContent = new Tabulator('#hrPayslipListTable', {
            ajaxURL: route('hr.payslip.sync.list'),
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
                    title: 'Employee List',
                    field: 'employee_id',
                    headerHozAlign: 'left',
                    formatter: function (cell, formatterParams, onRendered) {
                        let empList = cell.getRow().getData().employee_list;
                        let empListHtml = '';
                        empList.forEach((element) => {
                            empListHtml += `<span class="badge badge-info">${element}</span> `;
                        });
                        return empListHtml;
                    },
                },
                {
                    title: 'File Name',
                    field: 'file_name',
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

    
    const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
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

    // $('.lccTom').each(function () {
    //     if ($(this).attr('multiple') !== undefined) {
    //         tomOptions = {
    //             ...tomOptions,
    //             plugins: {
    //                 ...tomOptions.plugins,
    //                 remove_button: {
    //                     title: 'Remove this item',
    //                 },
    //             },
    //         };
    //     }
    //     new TomSelect(this, tomOptions);
    // });

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.lcc-tom-select').forEach(function(selectElement) {
            new TomSelect(selectElement, {
                render: {
                    option: function(data, escape) {
                        console.log(data);
                        return '<div class="flex justify-start items-center">' +
                            '<div class="w-10 h-10 intro-x image-fit mr-5">' +
                                '<img alt="' + escape(data.text) + '" class="rounded-full shadow" src="' + escape(data.photo_url) + '">' +
                            '</div>' +
                            '<div>' +
                                '<div class="font-medium whitespace-nowrap">' + escape(data.text) + '</div>' +
                                '<div class="text-slate-500 text-xs whitespace-nowrap">' + (data.status != 1 ? "InActive" : "Active") + ' - ' + escape(data.id) + '</div>' +
                            '</div>' +
                        '</div>';
                    }
                },
                placeholder: 'Search Here...',
                allowEmptyOption: true,
                onChange: function(value) {
                    // Find the closest table row
                    var row = selectElement.closest('tr');
                    if (value) {
                        // Change the row color to success (green) if a value is selected
                        row.style.backgroundColor = '#d4edda'; // Bootstrap success background color
                        row.style.color = '#155724'; // Bootstrap success text color
                    } else {
                        // Change the row color to danger (red) if no value is selected
                        row.style.backgroundColor = '#f8d7da'; // Bootstrap danger background color
                        row.style.color = '#721c24'; // Bootstrap danger text color
                    }
                }
            });
        });
    });
    $('#hrPaySlipBtn').on('click', function (event) {
        $(".loading").removeClass('hidden');
        //implement form submit
        $('#hrPayslipSyncForm').submit();
    });

    $('#hrPayslipSyncForm').on('submit', function (event) {
        event.preventDefault();
        let tthis = $(this);
        let url = tthis.attr('action');
        const form = document.getElementById('hrPayslipSyncForm');
        let form_data = new FormData(form);
        axios({
            url: url,
            method: "post",
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {

            $(".loading").removeClass('hidden');
            
            if (response.status == 200) {

              
                succModal.show();

                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulations!");
                    $("#successModal .successModalDesc").html('Employee Name successfully syncronized.');
                }); 
                
                setTimeout(() => {
                    succModal.hide();
                    location.reload();
                }, 2000);
            }
        }).catch(error => {
            $(".loading").addClass('hidden');
            
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        // Extract the index from the key (e.g., employee_id.0 -> 0)
                        let indexMatch = key.match(/\d+/);
                        let index = indexMatch ? indexMatch[0] : '';

                        // Remove the index number from the key (e.g., employee_id.0 -> employee_id)
                        let keyWithoutIndex = key.replace(/\.\d+/, '');
                        $(`#hrPayslipSyncForm .${keyWithoutIndex}`).addClass('border-danger')
                        $(`#hrPayslipSyncForm  .error-${keyWithoutIndex}`).eq(index).html(val)
                    }
                } else {
                    console.log('hrPayslipSyncForm error', error.response.data);
                }
            }
        });
    });

    $('#hrPayslipSyncTable').on('click', '.delete_btn', function(){
        let $statusBTN = $(this);
        let rowID = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html(confModalDelTitle);
            $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
            $('#confirmModal .agreeWith').attr('data-id', rowID);
            $('#confirmModal .agreeWith').attr('data-action', 'DELETE');
        1});
    });

    // Confirm Modal Action
    $('#confirmModal .agreeWith').on('click', function(){
        let $agreeBTN = $(this);
        let recordID = $agreeBTN.attr('data-id');
        let action = $agreeBTN.attr('data-action');

        $('#confirmModal button').attr('disabled', 'disabled');
        if(action == 'DELETE'){
            axios({
                method: 'delete',
                url: route('payslip-upload.destroy', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confModal.hide();

                    succModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('Academic year successfully deleted!');
                    });

                    setTimeout(() => {
                        succModal.hide();
                        location.reload();
                    }, 2000);
                }
            }).catch(error =>{
                console.log(error)
            });
        } else if(action == 'RESTORE'){
            axios({
                method: 'post',
                url: route('payslip-upload.restore', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confModal.hide();

                    succModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Success!');
                        $('#successModal .successModalDesc').html('Academic Year Data Successfully Restored!');
                    });
                }
            }).catch(error =>{
                console.log(error)
            });
        }
    })

    
})();