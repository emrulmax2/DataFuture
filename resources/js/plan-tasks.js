import IMask from 'imask';
import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import { createElement, Plus,Minus } from 'lucide';
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
import Dropzone from "dropzone";
import Toastify from "toastify-js";

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
                        let dropdown = [];
                        let attendanceInformation = cell.getData().attendance_information
                        if(attendanceInformation!=null) {
                            if(attendanceInformation.end_time==null) { 
                            dropdown =`<div data-tw-merge class="transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&amp;:hover:not(:disabled)]:bg-opacity-90 [&amp;:hover:not(:disabled)]:border-opacity-90 [&amp;:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed border-success text-success dark:border-success [&amp;:hover:not(:disabled)]:bg-success/10 mb-2 mr-1  w-24">Class on going...</div>`;
                            } else {
                                dropdown =`<div data-tw-merge class="transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&amp;:hover:not(:disabled)]:bg-opacity-90 [&amp;:hover:not(:disabled)]:border-opacity-90 [&amp;:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed border-primary text-primary dark:border-primary [&amp;:hover:not(:disabled)]:bg-primary/10 mb-2 mr-1  w-24 ">Held</div>`;  
                            }
                        }else {
                            if(cell.getData().upcomming_status=="Upcomming")
                            dropdown =`<div class="transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&amp;:hover:not(:disabled)]:bg-opacity-90 [&amp;:hover:not(:disabled)]:border-opacity-90 [&amp;:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed border-pending text-pending dark:border-pending [&amp;:hover:not(:disabled)]:bg-pending/10 mb-2 mr-1  w-24 ">Upcomming</div>`;
                            else
                            dropdown =`<div class="transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&amp;:hover:not(:disabled)]:bg-opacity-90 [&amp;:hover:not(:disabled)]:border-opacity-90 [&amp;:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed border-danger text-danger dark:border-danger [&amp;:hover:not(:disabled)]:bg-danger/10 mb-2 mr-1  w-24 ">Canceled</div>`;

                        }
                        return dropdown;
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
                        let dropdown = [];
                        
                        let attendanceInformation = cell.getData().attendance_information
                        if(attendanceInformation!=null) {
                            if(attendanceInformation.end_time==null) { 
                                
                                    dropdown =`<a data-attendanceinfo="${
                                        attendanceInformation.id
                                    }" data-id="${
                                        cell.getData().id
                                    }" href="${
                                        cell.getData().tutor_id
                                    }/attendance/${
                                        cell.getData().id
                                    }" class="start-punch transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&amp;:hover:not(:disabled)]:bg-opacity-90 [&amp;:hover:not(:disabled)]:border-opacity-90 [&amp;:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed bg-primary border-primary text-white dark:border-primary mb-2 mr-2 w-32"><i data-lucide="activity" width="24" height="24" class="stroke-1.5 mr-2 h-4 w-4"></i>
                                    Feed Attendance</a>`;
                                
                                dropdown +=`<button data-tw-toggle="modal" data-attendanceinfo="${
                                    attendanceInformation.id
                                }" data-id="${
                                    cell.getData().id
                                }" data-tw-target="#endClassModal" class="start-punch transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&amp;:hover:not(:disabled)]:bg-opacity-90 [&amp;:hover:not(:disabled)]:border-opacity-90 [&amp;:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed bg-danger border-danger text-white dark:border-danger mb-2 mr-2 w-32  "><i data-lucide="clock" width="24" height="24" class="stroke-1.5 mr-2 h-4 w-4"></i>
                                End Class</button>`;
                            } else {
                                dropdown =`<a href="${
                                    cell.getData().tutor_id
                                }/attendance/${
                                    cell.getData().id
                                }"  data-attendanceinfo="${
                                    attendanceInformation.id
                                }" data-id="${
                                    cell.getData().id
                                }" data-tw-target="#viewFeed" class="start-punch transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&amp;:hover:not(:disabled)]:bg-opacity-90 [&amp;:hover:not(:disabled)]:border-opacity-90 [&amp;:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed bg-primary border-primary text-white dark:border-primary mb-2 mr-2 w-32 "><i data-lucide="view" width="24" height="24" class="stroke-1.5 mr-2 h-4 w-4"></i>
                                View Feed</a>`;
                            }
                        }else {
                            if(cell.getData().upcomming_status!="Upcomming") {
                                
                                dropdown =`<div class="flex justify-center items-center mr-3">
                                        N/A
                                </div>`;
                            }
                        }
                        return dropdown;
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
            paginationSize: 10,
            paginationSizeSelector: [true, 5, 10, 20, 30, 40],
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
                    title: "#",
                    field: "sl",
                    
                    headerSort: false,
                    width: "180",
                },
                
                {
                    title: "PHOTO",
                    minWidth: 200,
                    field: "images",
                    hozAlign: "center",
                    headerHozAlign: "center",
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
                        </div>`;
                    },
                },
                
                {
                    title: "REGESTER NO",
                    field: "name",
                    vertAlign: "middle",
                    headerHozAlign: "center",
                    hozAlign:  "center",
                    formatter(cell, formatterParams) {
                        return `<div>
                            <div class="font-medium whitespace-nowrap">${
                                cell.getData().register_no
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
            acceptedFiles: ".jpeg,.jpg,.png,.gif",
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