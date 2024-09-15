import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

("use strict");
var attendanceReportListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let form_data = $('#studentGroupSearchForm').serialize();

        let tableContent = new Tabulator('#attendanceReportListTable', {
            ajaxURL: route('report.attendance.reports.list'),
            ajaxParams: { form_data : form_data},
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 50,
            paginationSizeSelector: [50, 100, 250],
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records found",
            columns: [
                {
                    title: "Student",
                    field: "registration_no",
                    headerHozAlign: "left",
                    headerSort: false,
                    formatter(cell, formatterParams) {  
                        var html = '<div class="inline-block relative">';
                                html += '<div class="font-medium whitespace-nowrap uppercase">'+cell.getData().registration_no+'</div>';
                                html += '<div class="font-medium whitespace-nowrap uppercase">'+cell.getData().name+'</div>';
                            html += '</div>';
                        return html;
                        /*var html = '<div class="block">';
                                html += '<div class="w-10 h-10 intro-x image-fit mr-4 inline-block">';
                                    html += '<img alt="'+cell.getData().name+'" class="rounded-full shadow" src="'+cell.getData().photo_url+'">';
                                html += '</div>';
                                html += '<div class="inline-block relative" style="top: -13px;">';
                                    html += '<div class="font-medium whitespace-nowrap uppercase">'+cell.getData().registration_no+'</div>';
                                    html += '<div class="font-medium whitespace-nowrap uppercase">'+cell.getData().name+'</div>';
                                html += '</div>';
                            html += '</div>';
                        return html;*/
                    }
                },
                {
                    title: "Semester",
                    field: "semester",
                    headerHozAlign: "left",
                    headerSort: false,
                    headerSort: false,
                },
                {
                    title: "Course",
                    field: "course",
                    headerHozAlign: "left",
                    headerSort: false,
                },
                {
                    title: "SSN",
                    field: "ssn",
                    headerHozAlign: "left",
                    headerSort: false,
                },
                {
                    title: "Awarding Body Ref",
                    field: "aw_body_ref",
                    headerHozAlign: "left",
                    headerSort: false,
                },
                {
                    title: "Status",
                    field: "status",
                    headerHozAlign: "left",
                    headerSort: false,
                },
                {
                    title: "Mobile",
                    field: "mobile",
                    headerHozAlign: "left",
                    headerSort: false,
                },
                {
                    title: "Email",
                    field: "institutional_email",
                    headerHozAlign: "left",
                    headerSort: false,
                },
                {
                    title: "Group",
                    field: "group",
                    headerHozAlign: "left",
                    headerSort: false,
                },
                {
                    title: "P",
                    field: "P",
                    headerHozAlign: "left",
                    headerSort: false,
                },
                {
                    title: "O",
                    field: "O",
                    headerHozAlign: "left",
                    headerSort: false,
                },
                {
                    title: "A",
                    field: "A",
                    headerHozAlign: "left",
                    headerSort: false,
                },
                {
                    title: "E",
                    field: "E",
                    headerHozAlign: "left",
                    headerSort: false,
                },
                {
                    title: "M",
                    field: "M",
                    headerHozAlign: "left",
                    headerSort: false,
                },
                {
                    title: "H",
                    field: "H",
                    headerHozAlign: "left",
                    headerSort: false,
                },
                {
                    title: "L",
                    field: "L",
                    headerHozAlign: "left",
                    headerSort: false,
                },
                {
                    title: "L.E",
                    field: "LE",
                    headerHozAlign: "left",
                    headerSort: false,
                },
                {
                    title: "TC",
                    field: "TC",
                    headerHozAlign: "left",
                    headerSort: false,
                },
                {
                    title: "(%)",
                    field: "w_excuse",
                    headerHozAlign: "left",
                    headerSort: false,
                },
                {
                    title: "W/O Excuse",
                    field: "wo_excuse",
                    headerHozAlign: "left",
                    headerSort: false,
                },
            ],
            ajaxResponse:function(url, params, response){
                var total_rows = (response.all_rows && response.all_rows > 0 ? response.all_rows : 0);
                if(total_rows > 0){
                    $('#reportRowCountWrap').find('.reportTotalRowCount').html('No of Students: '+total_rows);
                }else{
                    $('#reportRowCountWrap').find('.reportTotalRowCount').html('No of Students: 0');
                }

                return response;
            },
            renderComplete() {
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
                
                //$(document).find('.autoFillDropdown').html('').fadeOut();
            }
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
        $("#tabulator-export-csv-LSD").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-LSD").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-LSD").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Students Details",
            });
        });

        $("#tabulator-export-html-LSD").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-LSD").on("click", function (event) {
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
    if($('#attendanceReportListTable').length > 0){
        $('#studentGroupSearchBtn').on('click', function(e){
            e.preventDefault();

            $('.attendanceReportListTableWrap').fadeIn();
            attendanceReportListTable.init();
        });

        $('#attendanceReportExcelBtn').on('click', function(e){
            e.preventDefault();
            let $theBtn = $(this);
            let $form = $('#studentGroupSearchForm');
            const form = document.getElementById('addSettingsForm');

            $theBtn.attr('disabled', 'disabled');
            $theBtn.find('svg.loading').fadeIn();

            let form_data = $('#studentGroupSearchForm').serialize();
            axios({
                method: "post",
                url: route('report.attendance.reports.excel'),
                data: { form_data : form_data},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                //responseType: 'blob',
            }).then(response => {
                $theBtn.removeAttr('disabled');
                $theBtn.find('svg.loading').fadeOut();
                
                if(response.status == 200){
                    let url = response.data.url;
                    const link = document.createElement('a');
                    link.href = url;
                    link.setAttribute('download', 'Student_Attendance_Reports.xlsx');
                    document.body.appendChild(link);
                    link.click();
                    link.remove();
                }
            }).catch(error => {
                $theBtn.removeAttr('disabled');
                $theBtn.find('svg.loading').fadeOut();

                if (error.response) {
                    console.log('error');
                }
            });
        })
    }

})()