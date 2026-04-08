import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
 
("use strict");
var activeStudentsTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let the_date = $("#date").val() != "" ? $("#date").val() : "";
        let tableContent = new Tabulator("#activeStudentsTable", {
            ajaxURL: route("reports.active.students.by.datee.list"),
            ajaxParams: { the_date: the_date },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 50,
            paginationSizeSelector: [50, 100, 200, 300],
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records found",
            columns: [
                {
                    title: "#ID",
                    field: "id",
                    headerSort: false,
                    width: "120",
                },
                {
                    title: 'Reg. No',
                    field: 'registration_no',
                    headerSort: false,
                    headerHozAlign: 'left',
                    formatter(cell, formatterParams) {
                        var html = '<div class="block">';
                            html += '<div class="font-medium whitespace-nowrap uppercase">' +cell.getData().registration_no +'</div>';
                        html += '</div>';
                        return html;
                    }
                },
                {
                    title: 'Student',
                    field: 'first_name',
                    headerSort: false,
                    headerHozAlign: 'left',
                    formatter(cell, formatterParams) {
                        var html = '<div class="block">';
                            html += '<div class="whitespace-nowrap uppercase">' +cell.getData().first_name +'</div>';
                        html += '</div>';
                        return html;
                    }
                },
                {
                    title: '',
                    field: 'full_time',
                    headerHozAlign: 'left',
                    headerSort: false,
                    formatter(cell, formatterParams) {
                        let day = false;
                        if (cell.getData().full_time == 1){
                            day = 'text-slate-900';
                        } else {
                            day = 'text-amber-600';
                        }
                        var html = '<div class="flex justify-center text-center">';
                            if (cell.getData().flag_html != '') {
                                html += cell.getData().flag_html;
                            }
                            if (cell.getData().multi_agreement_status > 1) {
                                html += '<div class="mr-2 inline-flex  intro-x " style="color:#f59e0b"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="alert-octagon" class="lucide lucide-alert-octagon w-6 h-6"><polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"></polygon><line x1="12" x2="12" y1="8" y2="12"></line><line x1="12" x2="12.01" y1="16" y2="16"></line></svg></div>';
                            }
                            if (cell.getData().due > 1) {
                                html += '<div class="mr-2 ' +(cell.getData().due == 2 ? 'text-success' : cell.getData().due == 3 ? 'text-warning' : 'text-danger') +'"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="badge-pound-sterling" class="lucide lucide-badge-pound-sterling w-6 h-6"><path d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z"></path><path d="M8 12h4"></path><path d="M10 16V9.5a2.5 2.5 0 0 1 5 0"></path><path d="M8 16h7"></path></svg></div>';
                            }
                            html += '<div class="w-8 h-8 '+day +' intro-x inline-flex">';
                                if (cell.getData().full_time == 1){
                                    html += '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="sunset" class="lucide lucide-sunset w-6 h-6"><path d="M12 10V2"></path><path d="m4.93 10.93 1.41 1.41"></path><path d="M2 18h2"></path><path d="M20 18h2"></path><path d="m19.07 10.93-1.41 1.41"></path><path d="M22 22H2"></path><path d="m16 6-4 4-4-4"></path><path d="M16 18a4 4 0 0 0-8 0"></path></svg>';
                                }else{
                                    html += '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="sun" class="lucide lucide-sun w-6 h-6"><circle cx="12" cy="12" r="4"></circle><path d="M12 2v2"></path><path d="M12 20v2"></path><path d="m4.93 4.93 1.41 1.41"></path><path d="m17.66 17.66 1.41 1.41"></path><path d="M2 12h2"></path><path d="M20 12h2"></path><path d="m6.34 17.66-1.41 1.41"></path><path d="m19.07 4.93-1.41 1.41"></path></svg>';
                                }
                            html += '</div>';
                            if (cell.getData().disability == 1){
                                html += '<div class="inline-flex intro-x " style="color:#9b1313"><i data-lucide="accessibility" class="w-6 h-6"></i></div>';
                            }
                        html += '</div>';
                        createIcons({
                            icons,
                            'stroke-width': 1.5,
                            nameAttr: 'data-lucide',
                        });

                        return html;
                    }
                },
                {
                    title: "Semester",
                    field: "semester_name",
                    headerSort: false,
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) {
                        var html = '<div class="block">';
                            html += '<div class="whitespace-nowrap uppercase">' +cell.getData().semester_name +'</div>';
                        html += '</div>';
                        return html;
                    }
                },
                {
                    title: "Course",
                    field: "course_name",
                    headerSort: false,
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) {
                        var html = '<div class="block">';
                            html += '<div class="whitespace-normal text-xs text-slate-500">' +cell.getData().course_name +'</div>';
                        html += '</div>';
                        return html;
                    }
                },
                {
                    title: "Current Status",
                    field: "current_status",
                    headerSort: false,
                    headerHozAlign: "left",
                },
            ],
            ajaxResponse: function (url, params, response) {
                var total_rows = response.total && response.total > 0 ? response.total : 0;
                if (total_rows > 0) {
                    $('#activeStudentReportExcelBtn').removeAttr('disabled');
                    $('#totalCount').attr('data-total', total_rows).html(total_rows);
                } else {
                    $('#activeStudentReportExcelBtn').attr('disabled', 'disabled');
                    $('#totalCount').attr('data-total', '0').html('0');
                }

                return response;
            },
            renderComplete() {
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
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
        window.addEventListener("resize", () => {
            tableContent.redraw();
            createIcons({
                icons,
                "stroke-width": 1.5,
                nameAttr: "data-lucide",
            });
        });

        // Export
        $("#tabulator-export-csv-TITLE").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-TITLE").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-TITLE").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Title Details",
            });
        });

        $("#tabulator-export-html-TITLE").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-TITLE").on("click", function (event) {
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
    if ($("#activeStudentsTable").length) {
        $('#activeStudentsListForm').on('submit', function(e){
            e.preventDefault();
            let $theForm = $(this);
            let $theBtn = $theForm.find('#activeStudentsListFormBtn');
            let the_date = $theForm.find('#date').val();

            $theForm.find('.error-date').addClass('hidden').html('');
            if(the_date != ''){
                $('#activeStudentsTableWrap').fadeIn('fast', function(){
                    activeStudentsTable.init();
                })
            }else{
                $theForm.find('.error-date').removeClass('hidden').html('This field is required.');
                $('#totalCount').html('0');
                $('#activeStudentReportExcelBtn').attr('disabled', 'disabled');
                $('#activeStudentReportExcelBtn svg.loadingCall').addClass('hidden');
                $('#activeStudentsTableWrap').fadeOut('fast', function(){
                    $('#activeStudentsTable').html('').removeClass('tabulator').removeAttr('tabulator-layout').removeAttr('role');
                })
            }
        })

        // Filter function
        function filterHTMLForm() {
            activeStudentsTable.init();
        }


        // On reset filter form
        $("#activeStudentsListFormReset").on("click", function (event) {
            $theForm.find('.error-date').removeClass('hidden').html('This field is required.');
            $('#totalCount').html('0');
            $('#activeStudentReportExcelBtn').attr('disabled', 'disabled');
            $('#activeStudentReportExcelBtn svg.loadingCall').addClass('hidden');
            $('#activeStudentsTableWrap').fadeOut('fast', function(){
                $('#activeStudentsTable').html('').removeClass('tabulator').removeAttr('tabulator-layout').removeAttr('role');
            })
        });
    }

    $('#activeStudentReportExcelBtn').on('click', function(e){
        e.preventDefault();
        let $theForm = $('#activeStudentsListForm');
        let $theSubmitBtn = $theForm.find('#activeStudentsListFormBtn');
        let the_date = $theForm.find('#date').val();

        var $theBtn = $(this);
        var $theLoader = $theBtn.find('.loadingCall');

        if(the_date != ''){
            $theSubmitBtn.attr('disabled', 'disabled');
            $theBtn.attr('disabled', 'disabled');
            $theLoader.fadeIn();

            axios({
                method: "post",
                url: route("reports.active.students.by.datee.export"),
                params:{ the_date : the_date },
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
                responseType: 'blob',
            }).then((response) => {
                $theSubmitBtn.removeAttr('disabled');
                $theBtn.removeAttr('disabled');
                $theLoader.fadeOut();

                // console.log(response.data);
                // return false;

                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'Active_Student_By_Date.xlsx'); 
                document.body.appendChild(link);
                link.click();
            }).catch((error) => {
                $theSubmitBtn.removeAttr('disabled');
                $theBtn.removeAttr('disabled');
                $theLoader.fadeOut();

                console.log(error);
            });
        }
    })
})()