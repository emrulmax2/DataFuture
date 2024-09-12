import IMask from 'imask';
import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import { createElement, Plus,Minus } from 'lucide';
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
import Dropzone from "dropzone";
import Toastify from "toastify-js";

import ClassicEditor from "@ckeditor/ckeditor5-build-decoupled-document";

("use strict");
var classPlanDateListsTutorTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let planid = $('#classPlanDateListsTutorTable').attr('data-planid');
        let dates = $("#dates-PD").val() != "" ? $("#dates-PD").val() : "";
        let statusu = $("#status-PD").val() != "" ? $("#status-PD").val() : "";
        
        let tableContent = new Tabulator("#classPlanDateListsTutorTable", {
            ajaxURL: route("plan.dates.list"),
            ajaxParams: { planid: planid, dates: dates, status: statusu },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 20,
            paginationSizeSelector: [true, 10, 20, 30, 40],
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records found",
            columns: [
                {
                    title: "#",
                    field: "sl",
                    
                    headerSort: false,
                    width: "180",
                },
                {
                    title: "DATE",
                    field: "date",
                    headerHozAlign: "left",
                },
                {
                    title: "ROOM",
                    field: "room",
                    vertAlign: "middle",
                    headerHozAlign: "center",
                    hozAlign:  "center",
                    width:200,
                    formatter(cell, formatterParams) {
                        return `<div>
                            <div class="font-medium whitespace-nowrap">${
                                cell.getData().venue
                            }</div>
                            <div class="text-slate-500 text-xs whitespace-nowrap">${
                                cell.getData().room
                            }</div>
                        </div>`;
                    },
                },
                {
                    title: "TIME",
                    field: "time",
                    vertAlign: "middle",
                    headerHozAlign: "center",
                    hozAlign:"center",
                    width:150,
                    formatter(cell, formatterParams) {
                        return `<div>
                            <div class="font-medium whitespace-nowrap">${
                                cell.getData().start_time
                            } TO </div>
                            <div class="text-slate-500 text-xs whitespace-nowrap">${
                                cell.getData().end_time
                            }</div>
                        </div>`;
                    },
                },
                {
                    title: "STATUS",
                    field: "status",
                    width: 150,
                    vertAlign: "middle",
                    hozAlign:  "center",
                    headerSort: false,
                    headerHozAlign: "center",
                    formatter(cell, formatterParams) {
                        let labels = '';
                        if(cell.getData().status == 'Scheduled'){
                            labels = '<span class="btn btn-outline-secondary text-info border-info w-24 inline-block">Scheduled</span>';
                        }else if(cell.getData().status == 'Ongoing'){
                            labels = '<span class="btn btn-outline-primary w-24 inline-block">Ongoing</span>';
                        }else if(cell.getData().status == 'Completed'){
                            labels = '<span class="btn btn-outline-success w-24 inline-block">Completed</span>';
                        }else if(cell.getData().status == 'Canceled'){
                            labels = '<span class="btn btn-outline-danger w-24 inline-block">Canceled</span>';
                        }else{
                            labels = '<span class="btn btn-outline-warning w-24 inline-block">Unknown</span>';
                        }
                        return labels;
                    },
                },
                {
                    title: "ACTIONS",
                    minWidth: 200,
                    field: "actions",
                    responsive: 1,
                    hozAlign: "center",
                    vertAlign: "middle",
                    headerHozAlign: "center",
                    headerSort: false,
                    print: false,
                    download: false,
                    formatter(cell, formatterParams) {
                        let btn = '';
                        let attendanceInformation = cell.getData().attendance_information;
                        if(cell.getData().time_passed == 1 && cell.getData().attendance_information == null){
                            btn += '<a href="'+route('attendance.create', cell.getData().id)+'" class="btn btn-primary w-auto ml-2"><i data-lucide="plus-circle" class="stroke-1.5 mr-2 h-4 w-4"></i>Add Feed</a>';
                        }else{
                            if(cell.getData().status == 'Scheduled'){
                                btn = '<div class="flex justify-center items-center font-medium text-info">N/A</div>'
                            }else if(cell.getData().status == 'Canceled'){
                                btn = '<span class="btn btn-danger w-auto"><i data-lucide="x-circle" class="stroke-1.5 mr-2 h-4 w-4"></i>Canceled</span>';
                            }else if(cell.getData().status == 'Unknown'){
                                btn = '<span class="btn btn-pending text-white w-auto">Unknown</span>';
                            }else{
                                if(cell.getData().status == 'Ongoing' && cell.getData().feed_given == 0){
                                    btn += '<a href="'+route('tutor-dashboard.attendance', [cell.getData().tutor_id, cell.getData().id, 2])+'" class="btn btn-primary w-auto"><i data-lucide="activity" class="stroke-1.5 mr-2 h-4 w-4"></i>Feed Attendance</a>';
                                }
                                if(cell.getData().status == 'Ongoing' && cell.getData().feed_given == 1){
                                    btn +='<button data-tw-toggle="modal" data-attendanceinfo="'+attendanceInformation.id+'" data-id="'+cell.getData().id+'" data-tw-target="#endClassModal" class="start-punch btn btn-danger ml-2"><i data-lucide="clock" width="24" height="24" class="stroke-1.5 mr-2 h-4 w-4"></i>End Class</button>';
                                }
                                if(cell.getData().status == 'Completed'){
                                    btn += '<a href="'+route('tutor-dashboard.attendance', [cell.getData().tutor_id, cell.getData().id, 2])+'" class="btn btn-primary w-auto"><i data-lucide="view" class="stroke-1.5 mr-2 h-4 w-4"></i>View Feed</a>';
                                }
                            }
                        }

                        return btn;
                        
                        /*let attendanceInformation = cell.getData().attendance_information;
                        if(attendanceInformation != null) {
                            if(attendanceInformation.end_time == null) { 
                                dropdown = '<a href="'+route('tutor-dashboard.attendance', [cell.getData().tutor_id, cell.getData().id, 0])+'" class="btn btn-primary w-auto"><i data-lucide="activity" class="stroke-1.5 mr-2 h-4 w-4"></i>Feed Attendance</a>';
                                   
                                
                                if(cell.getData().feed_given == 1){
                                    dropdown +='<button data-tw-toggle="modal" data-attendanceinfo="'+attendanceInformation.id+'" data-id="'+cell.getData().id+'" data-tw-target="#endClassModal" class="start-punch btn btn-danger ml-2"><i data-lucide="clock" width="24" height="24" class="stroke-1.5 mr-2 h-4 w-4"></i>End Class</button>';
                                }
                            } else {
                                dropdown = '<a href="'+route('tutor-dashboard.attendance', [cell.getData().tutor_id, cell.getData().id, 0])+'" class="btn btn-primary w-auto"><i data-lucide="view" class="stroke-1.5 mr-2 h-4 w-4"></i>View Feed</a>';
                            }
                        }else {
                            if(cell.getData().upcomming_status!="Upcomming") {
                                dropdown =`<div class="flex justify-center items-center mr-3">N/A</div>`;
                            }
                        }
                        return dropdown;*/
                    },
                },
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

        $("#tabulator-export-json").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Plan Date List Details",
            });
        });

        $("#tabulator-export-html").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
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


var classParticipantsTutorTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let planid = $('#classParticipantsTutorTable').attr('data-planid');
        let statusu = $("#status-PT").val() != "" ? $("#status-PT").val() : "";
        
        let tableContent = new Tabulator("#classParticipantsTutorTable", {
            ajaxURL: route("plan-participant.list"),
            ajaxParams: { planid: planid, status: statusu },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 10,
            paginationSizeSelector: [true, 5, 10, 20, 30, 40],
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records found",
            columns: [
                {
                    title: "#",
                    field: "sl",
                    
                    headerSort: false,
                    width: "180",
                },
                
                {
                    title: "PHOTO",
                    minWidth: 200,
                    field: "images",
                    headerHozAlign: "center",
                    hozAlign: "center",
                    vertAlign: "middle",
                    print: false,
                    download: false,
                    formatter(cell, formatterParams) {
                        return `<div class="flex lg:justify-center">
                            <div class="intro-x w-10 h-10 image-fit">
                                <img  class="rounded-full" src="${
                                    cell.getData().images
                                }">
                            </div>
                        </div>`;
                    },
                },
                {
                    title: "NAME",
                    field: "name",
                    vertAlign: "middle",
                    headerHozAlign: "center",
                    hozAlign:  "center",
                    formatter(cell, formatterParams) {
                        return `<div>
                            <div class="font-medium whitespace-nowrap">${
                                cell.getData().name
                            }</div>
                            <div class="text-slate-500 text-xs whitespace-nowrap">${
                                cell.getData().type
                            }</div>
                        </div>`;
                    },
                },
                
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

        $("#tabulator-export-json").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Plan Date List Details",
            });
        });

        $("#tabulator-export-html").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
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
// classStudentListTutorModuleTable

var classStudentListTutorModuleTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let planid = $('#classStudentListTutorModuleTable').attr('data-planid');
        let statusu = $("#status-CLTML").val() != "" ? $("#status-CLTML").val() : "";
        
        let tableContent = new Tabulator("#classStudentListTutorModuleTable", {
            ajaxURL: route("student-assign.list"),
            ajaxParams: { planid: planid, status: statusu },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 50,
            paginationSizeSelector: [true, 20, 50, 100],
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records found",
            selectable:true,
            columns: [
                {
                    formatter: "rowSelection", 
                    titleFormatter: "rowSelection", 
                    hozAlign: "left", 
                    headerHozAlign: "left",
                    width: "160",
                    headerSort: false, 
                    download: false,
                    cellClick:function(e, cell){
                        cell.getRow().toggleSelect();
                    }
                },
                {
                    title: "Reg. No",
                    field: "registration_no",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) {  
                        var html = '<div class="block">';
                                html += '<div class="w-10 h-10 intro-x image-fit mr-4 inline-block">';
                                    html += '<img alt="'+cell.getData().first_name+'" class="rounded-full shadow" src="'+cell.getData().photo_url+'">';
                                html += '</div>';
                                html += '<div class="inline-block relative" style="top: -13px;">';
                                    html += '<div class="font-medium whitespace-nowrap uppercase">'+cell.getData().registration_no+'</div>';
                                    
                                html += '</div>';
                            html += '</div>';
                            html += '<input type="hidden" class="student_ids" name="student_ids[]" value="'+cell.getData().student_id+'"/>';
                        return html;
                    }
                },
                {
                    title: "",
                    field: "evening_and_weekend",
                    headerHozAlign: "left",
                    headerSort: false,
                    formatter(cell, formatterParams) {  
                        let day=false;
                        if(cell.getData().evening_and_weekend==1) 
                            day = 'text-slate-900' 
                        else  
                            day = 'text-amber-600'
                        var html = '<div class="flex">';
                                html += '<div class="w-8 h-8 '+day+' intro-x inline-flex">';
                                if(cell.getData().evening_and_weekend==1)
                                    html += '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="sunset" class="lucide lucide-sunset w-6 h-6"><path d="M12 10V2"></path><path d="m4.93 10.93 1.41 1.41"></path><path d="M2 18h2"></path><path d="M20 18h2"></path><path d="m19.07 10.93-1.41 1.41"></path><path d="M22 22H2"></path><path d="m16 6-4 4-4-4"></path><path d="M16 18a4 4 0 0 0-8 0"></path></svg>';
                                else
                                    html += '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="sun" class="lucide lucide-sun w-6 h-6"><circle cx="12" cy="12" r="4"></circle><path d="M12 2v2"></path><path d="M12 20v2"></path><path d="m4.93 4.93 1.41 1.41"></path><path d="m17.66 17.66 1.41 1.41"></path><path d="M2 12h2"></path><path d="M20 12h2"></path><path d="m6.34 17.66-1.41 1.41"></path><path d="m19.07 4.93-1.41 1.41"></path></svg>';
                                
                                html += '</div>';
                            if(cell.getData().disability==1)
                                html += '<div class="inline-flex intro-x " style="color:#9b1313"><i data-lucide="accessibility" class="w-6 h-6"></i></div>';
                            
                            html += '</div>';
                            createIcons({icons,"stroke-width": 1.5,nameAttr: "data-lucide"});

                        return html;
                    }
                },
                {
                    title: "First Name",
                    field: "first_name",
                    headerHozAlign: "left",
                },
                {
                    title: "Last Name",
                    field: "last_name",
                    headerHozAlign: "left",
                },
                {
                    title: "Status",
                    field: "status_id",
                    headerHozAlign: "left",
                }
                
            ],
            renderComplete() {
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
            },
            rowSelectionChanged:function(data, rows){
                var ids = [];
                if(rows.length > 0){
                    $('#actionButtonWrap').fadeIn();
                }else{
                    $('#actionButtonWrap').fadeOut();
                }
            },
            selectableCheck:function(row){
                return row.getData().student_id > 0; //allow selection of rows where the age is greater than 18
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
        $("#tabulator-export-csv-CLTML").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-CLTML").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-CLTML").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Student List Details",
            });
        });

        $("#tabulator-export-html-CLTML").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-CLTML").on("click", function (event) {
            tableContent.print();
        });
    };
    return {
        init: function () {
            _tableGen();
        },
    };
})();
var classPlanAssessmentModuleTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let planid = $('#classPlanAssessmentModuleTable').attr('data-planid');
        let statusu = $("#status-CLTML").val() != "" ? $("#status-CLTML").val() : "";
        
        let tableContent = new Tabulator("#classPlanAssessmentModuleTable", {
            ajaxURL: route("assessment.plan.list"),
            ajaxParams: { planid: planid, status: statusu },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 10,
            paginationSizeSelector: [true, 5, 10, 20, 30, 40],
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records found",
            selectable:true,
            columns: [
                {
                    title: "#",
                    field: "sl",
                    
                    headerSort: false,
                    width: "180",
                },
                {
                    title: "Assessment Name",
                    field: "name",
                    vertAlign: "middle",
                    headerHozAlign: "center",
                    hozAlign:  "center",
                    formatter(cell, formatterParams) {
                        return `<div>
                            <div class="font-medium whitespace-nowrap">${
                                cell.getData().name
                            }</div>
                        </div>`;
                    },
                },
                
                {
                    title: "Publish Date",
                    field: "published_at",
                    vertAlign: "middle",
                    headerHozAlign: "center",
                    hozAlign:  "center",
                    formatter(cell, formatterParams) {
                        return `<div>
                            <div class="font-medium whitespace-nowrap">${
                                cell.getData().published_at
                            }</div>
                        </div>`;
                    },
                },
                {
                    title: "Resubmission Date",
                    field: "resubmission_at",
                    vertAlign: "middle",
                    headerHozAlign: "center",
                    hozAlign:  "center",
                    formatter(cell, formatterParams) {
                        return `<div>
                            <div class="font-medium whitespace-nowrap">${
                                cell.getData().resubmission_at
                            }</div>
                        </div>`;
                    },
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    download: false,
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if (cell.getData().deleted_at == null) {
                            if (cell.getData().resultFound == 1) {
                                btns += '<a href="' +route('result.downloadresult-excel',cell.getData().id) +'" data-id="' +cell.getData().id +'" type="button" class="downloadresult_btn  btn btn-warning text-white p-0 w-9 h-9 ml-1"><i data-lucide="download-cloud" class="w-4 h-4"></i> </a>';
                            
                            } else
                            btns += '<a href="' +route('result.download-excel',cell.getData().id) +'" data-id="' +cell.getData().id +'" type="button" class="download_btn  btn btn-primary text-white p-0 w-9 h-9 ml-1"><i data-lucide="file-down" class="w-4 h-4"></i> </a>';
                            
                            btns += '<a href="javascript:void(0);" data-tw-toggle="modal" data-tw-target="#resultImportModal" data-id="' +cell.getData().id +'" type="button" class="uploadresult_btn  btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="upload-cloud" class="w-4 h-4"></i> </a>';
                            btns += '<a href="' +route('result.index',cell.getData().id) +'" data-id="' +cell.getData().id +'"  type="button" class="edit_btn transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&:hover:not(:disabled)]:bg-opacity-90 [&:hover:not(:disabled)]:border-opacity-90 [&:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed bg-dark border-dark text-white dark:bg-darkmode-800 dark:border-transparent dark:text-slate-300 [&:hover:not(:disabled)]:dark:dark:bg-darkmode-800/70  p-0 w-9 h-9 ml-1 "><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
                            btns += '<button data-id="' +cell.getData().id +'"  class="delete_btn btn btn-danger text-white  ml-1 p-0 w-9 h-9"><i data-lucide="Trash2" class="w-4 h-4"></i></button>';
                        }  else if (cell.getData().deleted_at != null) {
                            btns += '<button data-id="' +cell.getData().id +'"  class="restore_btn btn btn-linkedin text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="rotate-cw" class="w-4 h-4"></i></button>';
                        }
                        
                        return btns;
                    },
                },
                
                
            ],
            renderComplete() {
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });

                $(".uploadresult_btn").on('click',function(){
                    let id = $(this).attr('data-id');
                    $("input[name='assessment_plan_id']").val(id);
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
        $("#tabulator-export-csv-CLTML").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-CLTML").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-CLTML").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Student List Details",
            });
        });

        $("#tabulator-export-html-CLTML").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-CLTML").on("click", function (event) {
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
    const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));

    
    let confModalDelTitle = 'Are you sure?';
    if ($("#classParticipantsTutorTable").length) {
        // Init Table
        classParticipantsTutorTable.init();

        // Filter function
        function filterHTMLForm() {
            classParticipantsTutorTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-PT")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLForm();
                }
            }
        );
        // On click go button
        $("#tabulator-html-filter-go-PT").on("click", function (event) {
            filterHTMLForm();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-PT").on("click", function (event) {
            
            $("#status-PT").val("1");
            filterHTMLForm();
        });
    }

    if($("#confirmModalPlanTask").length > 0) {
        const confirmModalPlanTask = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModalPlanTask"));
        let confirmModalPlanTaskTitle = 'Are you sure?';
        let confirmModalPlanTaskDescription = 'Do you really want to re-assign the module related documents.';
        const confirmModalPlanTaskEL = document.getElementById('confirmModalPlanTask');
        confirmModalPlanTaskEL.addEventListener('hidden.tw.modal', function(event){
            $('#confirmModalPlanTask .agreeWithPlanTask').attr('data-id', '0');
            $('#confirmModalPlanTask .agreeWithPlanTask').attr('data-action', 'none');
        });
        document.getElementById('confirmModalPlanTask').addEventListener('shown.tw.modal', function(event){
            $('#confirmModalPlanTask .title').html(confirmModalPlanTaskTitle);
            $('#confirmModalPlanTask .description').html(confirmModalPlanTaskDescription);
            let id = $(".callModalPlanTask").data('planid');
            $('#confirmModalPlanTask .agreeWithPlanTask').attr('data-id', id);
            $('#confirmModalPlanTask .agreeWithPlanTask').attr('data-action', 'update');
        });
        
        $(".agreeWithPlanTask").on('click',function(e){
            let $agreeBTN = $(this);
            let recordID = $agreeBTN.attr('data-id');
            
            $('#confirmModalPlanTask button').attr('disabled', 'disabled');
            
            e.preventDefault();
            let planid = recordID;
            axios({
                method: "post",
                url: route('plan-module-task.auto.sync',planid),
                
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    succModal.show();
                    confirmModalPlanTask.hide()
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Congratulations!');
                        $('#successModal .successModalDesc').html('Modules Assignment data successfully generated.');
                    });

                }
                
                setTimeout(function(){
                    succModal.hide();
                    window.location.reload();
                }, 2000);
            }).catch(error => {
                confirmModalPlanTask.hide();
                console.log('error');
            });
        });
    }

    if ($("#classPlanDateListsTutorTable").length) {
        // Init Table
        classPlanDateListsTutorTable.init();

        // Filter function
        function filterHTMLForm() {
            classPlanDateListsTutorTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-PD")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLForm();
                }
            }
        );

        // On click go button
        $("#tabulator-html-filter-go-PD").on("click", function (event) {
            filterHTMLForm();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-PD").on("click", function (event) {
            $("#dates-PD").val("");
            $("#status-PD").val("1");
            filterHTMLForm();
        });
    }

    if ($("#classStudentListTutorModuleTable").length) {
        // Init Table
        classStudentListTutorModuleTable.init();

        // Filter function
        function filterHTMLFormCLTML() {
            classStudentListTutorModuleTable.init();
        }

        // On click go button
        $("#tabulator-html-filter-go-CLTML").on("click", function (event) {
            filterHTMLFormCLTML();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-CLTML").on("click", function (event) {
            $("#dates-CLTML").val("");
            $("#status-CLTML").val("1");
            filterHTMLFormCLTML();
        });

        const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));
        const sendBulkSmsModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#sendBulkSmsModal"));
        const sendBulkMailModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#sendBulkMailModal"));

        let tomOptions = {
            plugins: {
                dropdown_input: {}
            },
            placeholder: 'Search Here...',
            //persist: false,
            create: false,
            allowEmptyOption: true,
            //maxItems: null,
            onDelete: function (values) {
                return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
            },
        };
        let sms_template_id = new TomSelect('#sms_template_id', tomOptions);
        let email_template_id = new TomSelect('#email_template_id', tomOptions);
    
        let mailEditor;
        if($("#mailEditor").length > 0){
            const el = document.getElementById('mailEditor');
            ClassicEditor.create(el).then((editor) => {
                mailEditor = editor;
                $(el).closest(".editor").find(".document-editor__toolbar").append(editor.ui.view.toolbar.element);
            }).catch((error) => {
                console.error(error);
            });
        }

        const sendBulkSmsModalEl = document.getElementById('sendBulkSmsModal')
        sendBulkSmsModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#sendBulkSmsModal .acc__input-error').html('');
            $('#sendBulkSmsModal .modal-body input, #sendBulkSmsModal .modal-body textarea').val('');
            $('#sendBulkSmsModal .sms_countr').html('160 / 1');
            $('#sendBulkSmsModal input[name="student_ids"]').val('');
            sms_template_id.clear(true);
        });

        const sendBulkMailModalEl = document.getElementById('sendBulkMailModal')
        sendBulkMailModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#sendBulkMailModal .acc__input-error').html('');
            $('#sendBulkMailModal .modal-body input#sendMailsDocument').val('');
            $('#sendBulkMailModal .modal-body input, #sendBulkMailModal .modal-body select').val('');
            $('#sendBulkMailModal .sendMailsDocumentNames').html('').fadeOut();
            $('#sendBulkMailModal input[name="student_ids"]').val('');

            mailEditor.setData('');
            email_template_id.clear(true);
        });


        /* Export Student List Start */
        $('#exportStudentList').on('click', function(e){
            var $theBtn = $(this);
            var plan_id = $theBtn.attr('data-planid');
            var filename = $theBtn.attr('data-filename');

            var ids = [];
            $('#classStudentListTutorModuleTable').find('.tabulator-row.tabulator-selected').each(function(){
                var $row = $(this);
                ids.push($row.find('.student_ids').val());
            });

            $('#actionButtonWrap button').attr('disabled', 'disabled');
            $theBtn.find('.loaders').fadeIn();

            if(ids.length > 0){
                axios({
                    method: "post",
                    url: route('student.assign.export'),
                    data: {plan_id : plan_id, ids : ids},
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                    responseType: 'blob',
                }).then(response => {
                    $('#actionButtonWrap button').removeAttr('disabled');
                    $theBtn.find('.loaders').fadeOut();
    
                    if (response.status == 200) {
                        const url = window.URL.createObjectURL(new Blob([response.data]));
                        const link = document.createElement('a');
                        link.href = url;
                        link.setAttribute('download', filename);
                        document.body.appendChild(link);
                        link.click();
                        link.remove();
                    }
                }).catch(error => {
                    $('#actionButtonWrap button').removeAttr('disabled');
                    $theBtn.find('.loaders').fadeOut();
                    if (error.response) {
                        console.log('error');
                    }
                });
            }else{
                $('#actionButtonWrap button').removeAttr('disabled');
                $theBtn.find('.loaders').fadeOut();

                warningModal.show();
                document.getElementById("warningModal").addEventListener("shown.tw.modal", function (event) {
                    $("#warningModal .warningModalTitle").html("Error Found!");
                    $("#warningModal .warningModalDesc").html('Selected students not foudn. Please select some students first or contact with the site administrator.');
                });
            }
        });
        /* Export Student List End */

        /* Bulk SMS Start */
        $('.sendBulkSmsBtn').on('click', function(e){
            var $btn = $(this);
            var ids = [];
            
            $('#classStudentListTutorModuleTable').find('.tabulator-row.tabulator-selected').each(function(){
                var $row = $(this);
                ids.push($row.find('.student_ids').val());
            });

            if(ids.length > 0){
                sendBulkSmsModal.show();
                document.getElementById("sendBulkSmsModal").addEventListener("shown.tw.modal", function (event) {
                    $('#sendBulkSmsModal [name="student_ids"]').val(ids.join(','));
                });
            }else{
                warningModal.show();
                document.getElementById("warningModal").addEventListener("shown.tw.modal", function (event) {
                    $("#warningModal .warningModalTitle").html("Error Found!");
                    $("#warningModal .warningModalDesc").html('Selected students not foudn. Please select some students first or contact with the site administrator.');
                });
            }
        });

        $('#smsTextArea').on('keyup', function(){
            var maxlength = ($(this).attr('maxlength') > 0 && $(this).attr('maxlength') != '' ? $(this).attr('maxlength') : 0);
            var chars = this.value.length,
                messages = Math.ceil(chars / 160),
                remaining = messages * 160 - (chars % (messages * 160) || messages * 160);
            if(chars > 0){
                if(chars >= maxlength && maxlength > 0){
                    $('#sendBulkSmsModal .modal-content .smsWarning').remove();
                    $('#sendBulkSmsModal .modal-content').prepend('<div class="alert smsWarning alert-danger-soft show flex items-center mb-0" role="alert"><i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i>Opps! Your maximum character limit exceeded. Please make the text short or contact with administrator.</div>').fadeIn();
                }else{
                    $('#sendBulkSmsModal .modal-content .smsWarning').remove();
                }
                $('#sendBulkSmsModal .sms_countr').html(remaining +' / '+messages);
            }else{
                $('#sendBulkSmsModal .sms_countr').html('160 / 1');
                $('#sendBulkSmsModal .modal-content .smsWarning').remove();
            }
        });

        $('#sendBulkSmsForm #sms_template_id').on('change', function(){
            var smsTemplateId = $(this).val();
            if(smsTemplateId != ''){
                axios({
                    method: "post",
                    url: route('bulk.communication.get.sms.template'),
                    data: {smsTemplateId : smsTemplateId},
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#sendBulkSmsForm #smsTextArea').val(response.data.row.description ? response.data.row.description : '').trigger('keyup');
                    }
                }).catch(error => {
                    if (error.response) {
                        console.log('error');
                    }
                })
            }else{
                $('#sendBulkSmsForm #smsTextArea').val('');
                $('#sendBulkSmsForm .sms_countr').html('160 / 1');
            }
        });

        $('#sendBulkSmsForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('sendBulkSmsForm');
        
            document.querySelector('#sendSMSBtn').setAttribute('disabled', 'disabled');
            document.querySelector("#sendSMSBtn svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('bulk.communication.send.sms'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#sendSMSBtn').removeAttribute('disabled');
                document.querySelector("#sendSMSBtn svg").style.cssText = "display: none;";

                if (response.status == 200) {
                    sendBulkSmsModal.hide();

                    successModal.show(); 
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html('Congratulation!');
                        $("#successModal .successModalDesc").html(response.data.message);
                    });  
                    
                    setTimeout(function(){
                        successModal.hide();
                    }, 5000);
                }
            }).catch(error => {
                document.querySelector('#sendSMSBtn').removeAttribute('disabled');
                document.querySelector("#sendSMSBtn svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#sendBulkSmsForm .${key}`).addClass('border-danger');
                            $(`#sendBulkSmsForm  .error-${key}`).html(val);
                        }
                    } else if(error.response.status == 412){
                        warningModal.show(); 
                        document.getElementById("warningModal").addEventListener("shown.tw.modal", function (event) {
                            $("#warningModal .warningModalTitle").html('Oops!');
                            $("#warningModal .warningModalDesc").html(error.response.data.message);
                        });
                    
                        setTimeout(function(){
                            warningModal.hide();
                        }, 5000);
                    }else {
                        console.log('error');
                    }
                }
            });
        });
        /* Bulk SMS End */

        /* Bulk Email Start */
        $('.sendBulkMailBtn').on('click', function(e){
            var $btn = $(this);
            var ids = [];
            
            $('#classStudentListTutorModuleTable').find('.tabulator-row.tabulator-selected').each(function(){
                var $row = $(this);
                ids.push($row.find('.student_ids').val());
            });

            if(ids.length > 0){
                sendBulkMailModal.show();
                document.getElementById("sendBulkMailModal").addEventListener("shown.tw.modal", function (event) {
                    $('#sendBulkMailModal [name="student_ids"]').val(ids.join(','));
                });
            }else{
                warningModal.show();
                document.getElementById("warningModal").addEventListener("shown.tw.modal", function (event) {
                    $("#warningModal .warningModalTitle").html("Error Found!");
                    $("#warningModal .warningModalDesc").html('Selected students not foudn. Please select some students first or contact with the site administrator.');
                });
            }
        });

        $('#sendBulkMailForm #sendMailsDocument').on('change', function(){
            var inputs = document.getElementById('sendMailsDocument');
            var html = '';
            for (var i = 0; i < inputs.files.length; ++i) {
                var name = inputs.files.item(i).name;
                html += '<div class="mb-1 text-primary font-medium flex justify-start items-center"><i data-lucide="disc" class="w-3 h3 mr-2"></i>'+name+'</div>';
            }

            $('#sendBulkMailForm .sendMailsDocumentNames').fadeIn().html(html);
            createIcons({
                icons,
                "stroke-width": 1.5,
                nameAttr: "data-lucide",
            });
        });

        $('#sendBulkMailForm [name="email_template_id"]').on('change', function(){
            var emailTemplateID = $(this).val();
            if(emailTemplateID != ''){
                axios({
                    method: "post",
                    url: route('bulk.communication.get.mail.template'),
                    data: {emailTemplateID : emailTemplateID},
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        if(response.data.row.description){
                            mailEditor.setData(response.data.row.description);
                        }else{
                            mailEditor.setData('');
                        }
                    }
                }).catch(error => {
                    if (error.response) {
                        console.log('error');
                    }
                });
            }else{
                mailEditor.setData('');
            }
        });

        $('#sendBulkMailForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('sendBulkMailForm');
        
            document.querySelector('#sendEmailBtn').setAttribute('disabled', 'disabled');
            document.querySelector("#sendEmailBtn svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            form_data.append('file', $('#sendBulkMailForm input#sendMailsDocument')[0].files[0]); 
            form_data.append("body", mailEditor.getData());
            axios({
                method: "post",
                url: route('bulk.communication.send.email'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#sendEmailBtn').removeAttribute('disabled');
                document.querySelector("#sendEmailBtn svg").style.cssText = "display: none;";

                if (response.status == 200) {
                    sendBulkMailModal.hide();

                    successModal.show(); 
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulation!" );
                        $("#successModal .successModalDesc").html(response.data.message);
                    });  
                    
                    setTimeout(function(){
                        successModal.hide();
                    }, 2000);
                }
            }).catch(error => {
                document.querySelector('#sendEmailBtn').removeAttribute('disabled');
                document.querySelector("#sendEmailBtn svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#sendBulkMailForm .${key}`).addClass('border-danger');
                            $(`#sendBulkMailForm  .error-${key}`).html(val);
                        }
                    } else if(error.response.status == 412){
                        warningModal.show(); 
                        document.getElementById("warningModal").addEventListener("shown.tw.modal", function (event) {
                            $("#warningModal .warningModalTitle").html('Oops!');
                            $("#warningModal .warningModalDesc").html(error.response.data.message);
                        });
                    
                        setTimeout(function(){
                            warningModal.hide();
                        }, 5000);
                    } else {
                        console.log('error');
                    }
                }
            });
        });
        /* Bulk Email End */

    }
    /* End Tabulator */

    if ($("#classPlanAssessmentModuleTable").length) {

        const addModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addAssessmentModal"));
        const resultImportModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#resultImportModal"));
        // Dropzone
        let dzErrors = false;
        Dropzone.autoDiscover = false;
        Dropzone.options.bankholidayImportForm = {
            autoProcessQueue: false,
            maxFiles: 1,
            maxFilesize: 50,
            parallelUploads: 1,
            acceptedFiles: ".xls,.xlsx,.xlsm,.xltx,.xltm",
            addRemoveLinks: true,
            //thumbnailWidth: 100,
            //thumbnailHeight: 100,
        };

        let options = {
            accept: (file, done) => {
                console.log("Uploaded");
                done();
            },
        };


        var drzn12 = new Dropzone('#bankholidayImportForm', options);

        drzn12.on("maxfilesexceeded", (file) => {
            $('#resultImportModal .modal-content .uploadError').remove();
            $('#resultImportModal .modal-content').prepend('<div class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Can not upload more than 10 files at a time.</div>');
            drzn12.removeFile(file);
            setTimeout(function(){
                $('#resultImportModal .modal-content .uploadError').remove();
            }, 2000)
        });

        drzn12.on("error", function(file, response){
            dzErrors = true;
        });

        drzn12.on("success", function(file, response){
            //console.log(response);
            return file.previewElement.classList.add("dz-success");
        });

        drzn12.on("complete", function(file) {
            //drzn1.removeFile(file);
        }); 

        drzn12.on('queuecomplete', function(){
            $('#saveImportResult').removeAttr('disabled');
            document.querySelector("#saveImportResult svg").style.cssText ="display: none;";

            if(!dzErrors){
                drzn12.removeAllFiles();

                $('#resultImportModal .modal-content .uploadError').remove();
                $('#resultImportModal .modal-content').prepend('<div class="alert uploadError alert-success-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> WOW! Student photo successfully uploaded.</div>');
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });

                setTimeout(function(){
                    $('#resultImportModal .modal-content .uploadError').remove();
                    window.location.reload();
                }, 2000);
            }else{
                $('#resultImportModal .modal-content .uploadError').remove();
                $('#resultImportModal .modal-content').prepend('<div class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Something went wrong. Please try later.</div>');
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
                
                setTimeout(function(){
                    $('#resultImportModal .modal-content .uploadError').remove();
                }, 2000);
            }
        })

        $('#saveImportResult').on('click', function(e){
            e.preventDefault();
        
            document.querySelector('#saveImportResult').setAttribute('disabled', 'disabled');
            document.querySelector("#saveImportResult svg").style.cssText ="display: inline-block;";
            
            drzn12.processQueue();
            
        });
        // Init Table
        classPlanAssessmentModuleTable.init();

        // Filter function
        function filterHTMLFormCLTML() {
            classPlanAssessmentModuleTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-CLTML")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLFormCLTML();
                }
            }
        );

        // On click go button
        $("#tabulator-html-filter-go-CLTML").on("click", function (event) {
            filterHTMLFormCLTML();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-CLTML").on("click", function (event) {
            $("#dates-CLTML").val("");
            $("#status-CLTML").val("1");
            filterHTMLFormCLTML();
        });

        const confirmModalEl = document.getElementById('confirmModal')
        confirmModalEl.addEventListener('hidden.tw.modal', function(event){
            $('#confirmModal .agreeWith').attr('data-id', '0');
            $('#confirmModal .agreeWith').attr('data-action', 'none');
        });
        // Delete Course
        $('#classPlanAssessmentModuleTable').on('click', '.delete_btn', function() {
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');
            let url = $statusBTN.attr('data-url');

            confModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes, the please click on agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'DELETE');
            });
        });

        // Restore Course
        $('#classPlanAssessmentModuleTable').on('click', '.restore_btn', function() {
            const confModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
            document.getElementById('confirmModal').addEventListener('hidden.tw.modal', function(event){
                $('#confirmModal .agreeWith').attr('data-id', '0');
                $('#confirmModal .agreeWith').attr('data-action', 'none');
            });
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Want to restore this Academic year from the trash? Please click on agree to continue.');
                $('#confirmModal .agreeWith').attr('data-id', courseID);
                $('#confirmModal .agreeWith').attr('data-action', 'RESTORE');
            });
        });

        

        $('#saveModuleAssesment').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('saveModuleAssesment');
        
            document.querySelector('#save').setAttribute('disabled', 'disabled');
            document.querySelector("#save svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('plan-assessment.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#save').removeAttribute('disabled');
                document.querySelector("#save svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    document.querySelector('#save').removeAttribute('disabled');
                    document.querySelector("#save svg").style.cssText = "display: none;";
                    addModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Academic years data successfully inserted.');
                    });         
                }
                classPlanAssessmentModuleTable.init();
            }).catch(error => {
                document.querySelector('#save').removeAttribute('disabled');
                document.querySelector("#save svg").style.cssText = "display: none;";
                
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addForm .${key}`).addClass('border-danger')
                            $(`#addForm  .error-${key}`).html(val)
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $('#resultImportModal').on('click','#saveImportholiday',function(e) {
            e.preventDefault();
            $('.dropzone').get(0).dropzone.processQueue();
            resultImportModal.hide();

            succModal.show();   
            //setTimeout(function() { succModal.hide(); }, 3000);
            
        });

    }
    
    $(".readd-currentresult").on('click',function(e) {
        //e.preventDefault();
        let tthis = $(this);
        var row = tthis.closest('tr');
        let grade = row.find('select[name="grade_id[]"]').val();
        let student_id = row.find('input[name="student_id[]"]').val();
        let plan_id = row.find('input[name="plan_id[]"]').val();
        let created_by = row.find('input[name="updated_by[]"]').val();
        let assessmentPlan = tthis.data('assessmentplan');
        tthis.attr('disabled', 'disabled');
        $("svg",tthis).eq(1).css('display','inline-block');
        axios({
            method: "post",
            url: route('result.resubmit'),
            data: { grade_id:grade, assessment_plan_id: assessmentPlan, student_id: student_id, plan_id:plan_id, created_by:created_by },
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            let totalAttempt = response.data.data.count;
            let results = response.data.data.results;
            tthis.removeAttr('disabled');
            $("svg",tthis).eq(1).css('display','none');

            if (response.status == 200) {
                row.find('input[name="id[]"]').val(response.data.data.id);
                
                row.find('a.attempt-count').html(totalAttempt);

                Toastify({
                    node: $("#success-notification-content")
                        .clone()
                        .removeClass("hidden")[0],
                    duration: -1,
                    newWindow: true,
                    close: true,
                    gravity: "top",
                    position: "right",
                    stopOnFocus: true,
                }).showToast();     
            }

        }).catch(error => {
            tthis.removeAttr('disabled');
            $("svg",tthis).eq(1).css('display','none');
            if (error.response) {
                if (error.response.status == 422) {
                    Toastify({
                        node: $("#error-notification-content")
                            .clone()
                            .removeClass("hidden")[0],
                        duration: -1,
                        newWindow: true,
                        close: true,
                        gravity: "top",
                        position: "right",
                        stopOnFocus: true,
                    }).showToast(); 
                }
            }
        });
        
    })

    $('.show-attempted').on('click',function(){
        let tthis = $(this);
        
        let assessment_plan_id = tthis.data('assessmentplan');
        let student_id = tthis.data('student_id');
        let totalAttemptHeader = $("h2#totalAttemptHeader");

        $("svg",totalAttemptHeader).css('display','inline-block');
        tthis.siblings("svg").css('display','inline-block');

        axios({
            method: "get",
            url: route("result.show.assessment", [assessment_plan_id,student_id]),
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        }).then((response) => {
            if (response.status == 200) {

                $("svg",totalAttemptHeader).css('display','none');
                tthis.siblings("svg").css('display','none');

                let results = response.data.data.results;
                let html =`<table id="resultListByStudent" data-tw-merge class="w-full text-left">
                    <thead data-tw-merge>
                        <tr data-tw-merge class="[&:hover_td]:bg-slate-100 [&:hover_td]:dark:bg-darkmode-300 [&:hover_td]:dark:bg-opacity-50">
                            <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                #
                            </th>
                            <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                Grade
                            </th>
                            <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                Published at
                            </th>
                            <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                Action
                            </th>
                        </tr>
                    </thead>
                <tbody>`;
                $.each(results,function(i, item){

                    let iCount = i+1;
                    html+=`<tr id="${item.id}" data-tw-merge class="[&:hover_td]:bg-slate-100 [&:hover_td]:dark:bg-darkmode-300 [&:hover_td]:dark:bg-opacity-50">
                            <td data-tw-merge class="px-5 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                                ${ iCount }
                            </td>
                            <td data-tw-merge class="px-5 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                            ${ item.grade.code } - ${item.grade.name}
                            </td>
                            <td data-tw-merge class="px-5 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                            ${ item.published_at  }
                            </td>
                            <td data-tw-merge class="px-5 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                                <a href="javascript:;" data-url="${route('result.destroy', item.id)}" data-id="${item.id}" data-action="DELETE" class="delete-result btn btn-danger text-white"><i class="w-4 h-4" data-lucide="trash"></i></a>
                            </td>
                        </tr>`
                    
                })
                html+=`</tbody>
                </table>`;
                
                $('#attemedModal .modal-body').html(html);
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
                $('.delete-result').on('click',function(){
            
                        let $statusBTN = $(this);
                        let rowID = $statusBTN.attr('data-id');
                        let url = $statusBTN.attr('data-url');
                        let confModalDelTitle ="Do you want to delete all?";
                        confModal.show();
                        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                            $('#confirmModal .confModTitle').html(confModalDelTitle);
                            $('#confirmModal .confModDesc').html('Do you really want to delete this record? If yes, then please click on agree button. This will be permanent.');
                            $('#confirmModal .agreeWith').attr('data-id', rowID);
                            $('#confirmModal .agreeWith').attr('data-action', 'DELETE');
                            $('#confirmModal .agreeWith').attr('data-url', url);
                        });
                
                })
            }
        })
        .catch((error) => {
            console.log(error);
        });
        //let results= {};

    })
    // Delete Course
    $('.delete_all_result').on('click', function(){
        //const confModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle ="Do you want to delete all?";
        
        let assessmentPlan = $(this).data('assessmentplan');
        confModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html(confModalDelTitle);
            $('#confirmModal .confModDesc').html('Want to delete all results. This will be permanent.');
            $('#confirmModal .agreeWith').attr('data-id', "all");
            $('#confirmModal .agreeWith').attr('data-assessmentPlan', assessmentPlan);
            $('#confirmModal .agreeWith').attr('data-action', 'DELETE');
        });
    });

    
    $(".update-currentresult").on('click',function(e) {
        //e.preventDefault();
        let tthis = $(this);
        var row = tthis.closest('tr');
        let grade = row.find('select[name="grade_id[]"]').val();
        let result = tthis.data('id');
        tthis.attr('disabled', 'disabled');
        $("svg",tthis).eq(1).css('display','inline-block');
        axios({
            method: "PATCH",
            url: route('result.update',result),
            data: { grade_id:grade },
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {

            tthis.removeAttr('disabled');
            $("svg",tthis).eq(1).css('display','none');

            if (response.status == 200) {
                Toastify({
                    node: $("#success-notification-content")
                        .clone()
                        .removeClass("hidden")[0],
                    duration: -1,
                    newWindow: true,
                    close: true,
                    gravity: "top",
                    position: "right",
                    stopOnFocus: true,
                }).showToast();     
            }

        }).catch(error => {
            tthis.removeAttr('disabled');
            $("svg",tthis).eq(1).css('display','none');
            if (error.response) {
                if (error.response.status == 422) {
                    Toastify({
                        node: $("#error-notification-content")
                            .clone()
                            .removeClass("hidden")[0],
                        duration: -1,
                        newWindow: true,
                        close: true,
                        gravity: "top",
                        position: "right",
                        stopOnFocus: true,
                    }).showToast(); 
                }
            }
        });
        
    })

    $("input[name='upload_type_select']").on('click',function(e) {
        //e.preventDefault();
        let tthis = $(this);
        let type = tthis.val();
        $('input[name="upload_type"]').val(type);
    })
    //
    // Confirm Modal Action
    $('#confirmModal .agreeWith').on('click', function(){
        let $agreeBTN = $(this);
        let recordID = $agreeBTN.attr('data-id');
        let action = $agreeBTN.attr('data-action');
        let url = $agreeBTN.attr('data-url');
        if(recordID!="all" && (url=="" || url==undefined)) {
            $('#confirmModal button').attr('disabled', 'disabled');
            if(action == 'DELETE'){
                axios({
                    method: 'delete',
                    url: route('plan-assessment.destroy', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModal button').removeAttr('disabled');
                        confModal.hide();

                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Done!');
                            $('#successModal .successModalDesc').html('Assessment successfully deleted!');
                        });
                    }
                    classPlanAssessmentModuleTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTORE'){
                axios({
                    method: 'post',
                    url: route('plan-assessment.restore', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModal button').removeAttr('disabled');
                        confModal.hide();

                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Success!');
                            $('#successModal .successModalDesc').html('Assessment Data Successfully Restored!');
                        });
                    }
                    classPlanAssessmentModuleTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        } else if(recordID=="all") { 
            $('#confirmModal button').attr('disabled', 'disabled');
            if(action == 'DELETE'){
                let assessmentPlan = $agreeBTN.attr('data-assessmentPlan');
                axios({
                    method: 'delete',
                    url: route('result.all.delete', assessmentPlan),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModal button').removeAttr('disabled');
                        confModal.hide();

                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Done!');
                            $('#successModal .successModalDesc').html('Assessment successfully deleted!');
                        });
                    }
                    location.reload();
                }).catch(error =>{
                    console.log(error)
                });
            }
        }else { 
            $('#confirmModal button').attr('disabled', 'disabled');
            if(action == 'DELETE'){

                axios({
                    method: 'delete',
                    url: url,
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModal button').removeAttr('disabled');
                        confModal.hide();

                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Done!');
                            $('#successModal .successModalDesc').html('Assessment successfully deleted!');
                        });
                    }
                    location.reload();
                }).catch(error =>{
                    console.log(error)
                });
            }

        }
    })
    
    $('#resultBulkInsert').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('resultBulkInsert');

        let tthisSubmitButton =$('#insertAllResult');
        tthisSubmitButton.attr('disabled', 'disabled');
        $("svg",tthisSubmitButton).eq(1).css('display','inline-block');
        

        let url = $("#resultBulkInsert input[name=url]").val();

        let form_data = new FormData(form);

        axios({
            method: "post",
            url: url,
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {

            tthisSubmitButton.removeAttr('disabled');
            $("svg",tthisSubmitButton).eq(1).css('display','none');

            if (response.status == 200) {
                

                succModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulations!");
                    $("#successModal .successModalDesc").html('Course creation data successfully inserted.');
                });                
                    
            }
            location.reload()
        }).catch(error => {

            tthisSubmitButton.removeAttr('disabled');
            $("svg",tthisSubmitButton).eq(1).css('display','none');

            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        
                        if(key == "grade_id[]") {
                            //$(`#resultBulkInsert .grade_id`).addClass('border-danger')
                            //$(`#resultBulkInsert  .error-grade_id`).html(val)
                            $('select[name="grade_id[]"]').each(function(){
                                let tthis = $(this);
                                let getValue = tthis.val()
                                if(getValue=="") {
                                    $(`.grade_id`,tthis).addClass('border-danger')
                                    tthis.siblings('div.error-grade_id').html(val)
                                } else {
                                    $(`.grade_id`,tthis).removeClass('border-danger')
                                    
                                    tthis.siblings('div.error-grade_id').html("")
                                }
                            });
                        }
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });
    /* Start Dropzone */
    if($("#addStudentPhotoModal").length > 0){
        let dzErrors = false;
        Dropzone.autoDiscover = false;
        Dropzone.options.addStudentPhotoForm = {
            autoProcessQueue: false,
            maxFiles: 1,
            maxFilesize: 5,
            parallelUploads: 1,
            acceptedFiles: ".jpeg,.jpg,.png,.gif,.pdf",
            addRemoveLinks: true,
            //thumbnailWidth: 100,
            //thumbnailHeight: 100,
        };

        let options = {
            accept: (file, done) => {
                console.log("Uploaded");
                done();
            },
        };


        var drzn1 = new Dropzone('#addStudentPhotoForm', options);

        drzn1.on("maxfilesexceeded", (file) => {
            $('#addStudentPhotoModal .modal-content .uploadError').remove();
            $('#addStudentPhotoModal .modal-content').prepend('<div class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Can not upload more than 10 files at a time.</div>');
            drzn1.removeFile(file);
            setTimeout(function(){
                $('#addStudentPhotoModal .modal-content .uploadError').remove();
            }, 2000)
        });

        drzn1.on("error", function(file, response){
            dzErrors = true;
        });

        drzn1.on("success", function(file, response){
            //console.log(response);
            return file.previewElement.classList.add("dz-success");
        });

        drzn1.on("complete", function(file) {
            //drzn1.removeFile(file);
        }); 

        drzn1.on('queuecomplete', function(){
            $('#uploadStudentPhotoBtn').removeAttr('disabled');
            document.querySelector("#uploadStudentPhotoBtn svg").style.cssText ="display: none;";

            if(!dzErrors){
                drzn1.removeAllFiles();

                $('#addStudentPhotoModal .modal-content .uploadError').remove();
                $('#addStudentPhotoModal .modal-content').prepend('<div class="alert uploadError alert-success-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> WOW! Student photo successfully uploaded.</div>');
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });

                setTimeout(function(){
                    $('#addStudentPhotoModal .modal-content .uploadError').remove();
                    window.location.reload();
                }, 2000);
            }else{
                $('#addStudentPhotoModal .modal-content .uploadError').remove();
                $('#addStudentPhotoModal .modal-content').prepend('<div class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Something went wrong. Please try later.</div>');
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
                
                setTimeout(function(){
                    $('#addStudentPhotoModal .modal-content .uploadError').remove();
                }, 2000);
            }
        })

        $('#uploadStudentPhotoBtn').on('click', function(e){
            e.preventDefault();
        
            document.querySelector('#uploadStudentPhotoBtn').setAttribute('disabled', 'disabled');
            document.querySelector("#uploadStudentPhotoBtn span").style.cssText ="display: inline-block;";
            
            drzn1.processQueue();
            
        });
        $('.task-upload__Button').on('click', function(e){
            let tthis = $(this);
            let planTaskId = tthis.data('plantaskid');
            $("input[name='plan_task_id']").val(planTaskId);

        });
        
    }
    /* End Dropzone */

    /**
     * Accordian Button (+/-) Works
     */
        $('.accordion-button').on('click',function(){
            let tthis = $(this)

            const plusIcon = createElement(Plus); // Returns HTMLElement (svg)
            const minusIcon = createElement(Minus); // Returns HTMLElement (svg)
            //console.log(plusIcon)
            // set custom attributes with browser native functions
            
            plusIcon.classList.add('w-4');
            plusIcon.classList.add('h-4');
            minusIcon.classList.add('w-4');
            minusIcon.classList.add('h-4');
            $("div.accordian-lucide").html("")
            $("div.accordian-lucide").append(plusIcon)
            // Append HTMLElement in webpage
            const myApp = document.getElementById('app');
            if(tthis.hasClass("collapsed")) {
                
                //create minus sign
                tthis.children("div.accordian-lucide").html("");
                tthis.children("div.accordian-lucide").append(minusIcon)
                
                
            } else {
                //create plus sign
                tthis.children("div.accordian-lucide").html("");
                tthis.children("div.accordian-lucide").append(plusIcon)
            }   

            
        })
    /**
     * Accordian Button Finished
     */
    const activityModalCP = tailwind.Modal.getOrCreateInstance(document.querySelector("#addActivityModal"));

    $('.activity-call').on('click', function(e){
        e.preventDefault();
        let tthis = $(this)
        let planDateListId = tthis.data('plandataid');
        let isModuleOnly = tthis.data('mandatory');
        tthis.children('span').css('display', 'inline-block');
        tthis.attr('disabled', 'disabled');
        let data ={
            page: 1,
            size: 100,
            status:1,
        }

        axios({
            method: 'get',
            url: route('elearning.list', data),
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200) {
                tthis.removeAttr('disabled');
                tthis.children('span').css('display', 'none');
                
                const LearningData = response.data.data;
                
                let html = '';
                for (let i=0; i<LearningData.length; i++) {
                    let data =[planDateListId,LearningData[i].id
                    ]
                    if(LearningData[i].active==1 ) {
                      html += `<a href="${
                        route('tutor_module_activity.create',data)
                      }" data-tw-toggle="modal" data-tw-target="#add-item-modal" class="intro-y block col-span-12 sm:col-span-4 2xl:col-span-3">
                                 <div class="box rounded-md p-3 relative zoom-in">
                                     <div class="flex-none relative block before:block before:w-full before:pt-[100%]">
                                         <div class="absolute top-0 left-0 w-full h-full image-fit">
                                             <img alt="London Churchill College" class="rounded-md" src="${
                                                LearningData[i].logo_url
                                             }">
                                         </div>
                                     </div>
                                     <div class="block font-medium text-center truncate mt-3">${
                                        LearningData[i].name
                                     }</div>
                                </div>
                             </a>`
                    }
                }

                $("#activit-contentlist").html(html)

                if(html!="") {
                    activityModalCP.show();
                }
            }
        }).catch(error =>{
            errorModal.show();
                document.getElementById("errorModal").addEventListener("shown.tw.modal", function (event) {
                    $("#errorModal .title").html("Token Mismatch!" );
                    $("#errorModal .descrtiption").html('Please reload');
                }); 
            location.reload();
        });
        
    });

    
    $('.module-call').on('click', function(e){
        e.preventDefault();
        let tthis = $(this)
        let planDateListId = tthis.data('plandataid');
     
        tthis.children('span').css('display', 'inline-block');
        tthis.attr('disabled', 'disabled');
        let data ={
            page: 1,
            size: 100,
            status:1,
        }

        axios({
            method: 'get',
            url: route('elearning.list', data),
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200) {
                tthis.removeAttr('disabled');
                tthis.children('span').css('display', 'none');
                
                const LearningData = response.data.data;
                
                let html = '';
                for (let i=0; i<LearningData.length; i++) {
                    let data =[planDateListId,LearningData[i].id
                    ]
                    if(LearningData[i].active==1 ) {
                      html += `<a href="${
                        route('plan-module-task.create',data)
                      }" data-tw-toggle="modal" data-tw-target="#add-item-modal" class="intro-y block col-span-12 sm:col-span-4 2xl:col-span-3">
                                 <div class="box rounded-md p-3 relative zoom-in">
                                     <div class="flex-none relative block before:block before:w-full before:pt-[100%]">
                                         <div class="absolute top-0 left-0 w-full h-full image-fit">
                                             <img alt="London Churchill College" class="rounded-md" src="${
                                                LearningData[i].logo_url
                                             }">
                                         </div>
                                     </div>
                                     <div class="block font-medium text-center truncate mt-3">${
                                        LearningData[i].name
                                     }</div>
                                </div>
                             </a>`
                    }
                }

                $("#activit-contentlist").html(html)

                if(html!="") {
                    activityModalCP.show();
                }
            }
        }).catch(error =>{
            errorModal.show();
                document.getElementById("errorModal").addEventListener("shown.tw.modal", function (event) {
                    $("#errorModal .title").html("Token Mismatch!" );
                    $("#errorModal .descrtiption").html('Please reload');
                }); 
            location.reload();
        });
        
    });
    /* Profile Menu End */
})();