import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
import IMask from 'imask';

import dayjs from "dayjs";
import Litepicker from "litepicker";

var attendanceSyncListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let attendanceDate = $("#queryDate").val() != "" ? $("#queryDate").val() : "";

        let tableContent = new Tabulator("#attendanceSyncListTable", {
            ajaxURL: route("hr.attendance.sync.list"),
            ajaxParams: { attendanceDate: attendanceDate },
            ajaxFiltering: false,
            ajaxSorting: false,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 31,
            paginationSizeSelector: [31],
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records found",
            columns: [
                {
                    title: "Date",
                    field: "theDate",
                    hozAlign: "left",
                    headerHozAlign: "left",
                },
                {
                    title: "Synchronise",
                    field: "synchronise",
                    hozAlign: "left",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) {  
                        if(cell.getData().synchronise == 1){
                            return '<button class="btn btn-sm btn-primary rounded-0 w-auto text-white" type="button"><i data-lucide="check-circle" class="w-4 h-4 mr-2"></i>Synchronised</button>';
                        }else{
                            return '<button type="button"\
                                        data-date="'+cell.getData().date+'"\
                                        class="btn btn-sm btn-success text-white rounded-0 w-auto syncroniseAttendance">\
                                        Synchronise\
                                        <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"\
                                            stroke="white" class="w-4 h-4 ml-2">\
                                            <g fill="none" fill-rule="evenodd">\
                                                <g transform="translate(1 1)" stroke-width="4">\
                                                    <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>\
                                                    <path d="M36 18c0-9.94-8.06-18-18-18">\
                                                        <animateTransform attributeName="transform" type="rotate" from="0 18 18"\
                                                            to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>\
                                                    </path>\
                                                </g>\
                                            </g>\
                                        </svg>\
                                    </button>';
                        }
                    }
                },
                {
                    title: "Issues",
                    field: "issues",
                    hozAlign: "left",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) {
                        var theUrl = cell.getData().synchronise == 1 ? route('hr.attendance.show', cell.getData().dateUnix) : 'javascript:void(0);';
                        if(cell.getData().issues > 0){
                            return '<a href="'+theUrl+'" target="_blank" class="btn btn-sm btn-warning text-white rounded-0">'+cell.getData().issues+' Issues</a>';
                        }else{
                            return '<a href="'+theUrl+'" class="btn btn-sm btn-success text-white rounded-0">0 Issues</a>';
                        }
                    }
                },
                {
                    title: "Absents",
                    field: "absents",
                    hozAlign: "left",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) {
                        var theUrl = cell.getData().synchronise == 1 ? route('hr.attendance.show', cell.getData().dateUnix) : 'javascript:void(0);';
                        if(cell.getData().absents > 0){
                            return '<a href="'+theUrl+'" target="_blank" class="btn btn-sm btn-warning text-white rounded-0">'+cell.getData().absents+' Absents</a>';
                        }else{
                            return '<a href="'+theUrl+'" class="btn btn-sm btn-success text-white rounded-0">0 Absents</a>';
                        }
                    }
                },
                {
                    title: "Overtime",
                    field: "overtime",
                    hozAlign: "left",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) {
                        var theUrl = cell.getData().synchronise == 1 ? route('hr.attendance.show', cell.getData().dateUnix) : 'javascript:void(0);';
                        if(cell.getData().overtime > 0){
                            return '<a href="'+theUrl+'" target="_blank" class="btn btn-sm btn-warning text-white rounded-0">'+cell.getData().overtime+' Overtime</a>';
                        }else{
                            return '<a href="'+theUrl+'" class="btn btn-sm btn-success text-white rounded-0">0 Overtime</a>';
                        }
                    }
                },
                {
                    title: "Pendings",
                    field: "pendings",
                    hozAlign: "left",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) {
                        var theUrl = cell.getData().synchronise == 1 ? route('hr.attendance.show', cell.getData().dateUnix) : 'javascript:void(0);';
                        if(cell.getData().pendings > 0){
                            return '<a href="'+theUrl+'" target="_blank" class="btn btn-sm btn-warning text-white rounded-0">'+cell.getData().pendings+' Pendings</a>';
                        }else{
                            return '<a href="'+theUrl+'" class="btn btn-sm btn-success text-white rounded-0">0 Pendings</a>';
                        }
                    }
                },
                {
                    title: "Actions",
                    field: "allRows",
                    hozAlign: "left",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) {
                        var theUrl = cell.getData().synchronise == 1 ? route('hr.attendance.show', cell.getData().dateUnix) : 'javascript:void(0);';
                        if(cell.getData().allRows > 0){
                            return '<a href="'+theUrl+'" target="_blank" class="btn btn-sm btn-warning text-white rounded-0">'+cell.getData().allRows+' Attendances</a>';
                        }else{
                            return '<a href="'+theUrl+'" class="btn btn-sm btn-success text-white rounded-0">0 Attendances</a>';
                        }
                    }
                }
            ],
            renderComplete() {
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
            },
        });

        // Redraw table onresize
        window.addEventListener("resize", () => {
            tableContent.redraw();
            createIcons({
                icons,
                "stroke-width": 1.5,
                nameAttr: "data-lucide",
            });
        });

        // Export
        $("#tabulator-export-csv").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-xlsx").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Title Details",
            });
        });


        // Print
        $("#tabulator-print").on("click", function (event) {
            tableContent.print();
        });
    };
    return {
        init: function () {
            _tableGen();
        },
    };
})();

(function(){
    if ($("#attendanceSyncListTable").length) {
        attendanceSyncListTable.init();
        
        // Filter function
        function filterTitleHTMLForm() {
            attendanceSyncListTable.init();
        }

        // On click go button
        $("#tabulator-html-filter-go").on("click", function (event) {
            filterTitleHTMLForm();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset").on("click", function (event) {
            $("#queryDate").val($('#queryDate').attr('data-org'));
            filterTitleHTMLForm();
        });

    }


    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));


    let dateOption = {
        autoApply: true,
        singleMode: true,
        numberOfColumns: 1,
        numberOfMonths: 1,
        showWeekNumbers: false,
        format: "MM-YYYY",
        dropdowns: {
            minYear: 1900,
            maxYear: 2050,
            months: true,
            years: true,
        },
    };

    const queryDate = new Litepicker({
        element: document.getElementById('queryDate'),
        ...dateOption
    });


    $('#attendanceSyncListTable').on('click', '.syncroniseAttendance', function(e){
        e.preventDefault();
        var $theBtn = $(this);
        var theDate = $theBtn.attr('data-date');
        var $allBtn = $('#attendanceSyncListTable').find('.syncroniseAttendance');

        $allBtn.attr('disabled', 'disabled');
        $theBtn.find('svg').fadeIn();

        axios({
            method: "post",
            url: route('hr.attendance.sync'),
            data: {theDate : theDate},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            $allBtn.removeAttr('disabled');
            $theBtn.find('svg').fadeOut();
            
            if (response.status == 200) {
                attendanceSyncListTable.init();

                var theDate = response.data.date;
                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html( "Congratulations!" );
                    $("#successModal .successModalDesc").html('Employee attendance for the day '+theDate+' has been successfully syncronised.');
                });   
                
                setTimeout(function(){
                    successModal.hide();
                }, 2000)
            }
        }).catch(error => {
            $allBtn.removeAttr('disabled');
            $theBtn.find('svg').fadeOut();
            if (error.response) {
                if (error.response.status == 422) {
                    console.log('error');
                }
            }
        });

    });
})();