import IMask from 'imask';
import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import { createElement, Plus,Minus } from 'lucide';
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
import Dropzone from "dropzone";
("use strict");
var classPlanDateListsTutorTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let planid = $('#classPlanDateListsTutorTable').attr('data-planid');
        let dates = $("#dates-PD").val() != "" ? $("#dates-PD").val() : "";
        let statusu = $("#status-PD").val() != "" ? $("#status-PD").val() : "";
        
        let tableContent = new Tabulator("#classPlanDateListsTutorTable", {
            ajaxURL: route("students.dashboard.plan.dates.list"),
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


// var classParticipantsTutorTable = (function () {
//     var _tableGen = function () {
//         // Setup Tabulator
//         let planid = $('#classParticipantsTutorTable').attr('data-planid');
//         let statusu = $("#status-PT").val() != "" ? $("#status-PT").val() : "";
        
//         let tableContent = new Tabulator("#classParticipantsTutorTable", {
//             ajaxURL: route("plan-participant.list"),
//             ajaxParams: { planid: planid, status: statusu },
//             ajaxFiltering: true,
//             ajaxSorting: true,
//             printAsHtml: true,
//             printStyled: true,
//             pagination: "remote",
//             paginationSize: 10,
//             paginationSizeSelector: [true, 5, 10, 20, 30, 40],
//             layout: "fitColumns",
//             responsiveLayout: "collapse",
//             placeholder: "No matching records found",
//             columns: [
//                 {
//                     title: "#",
//                     field: "sl",
                    
//                     headerSort: false,
//                     width: "180",
//                 },
                
//                 {
//                     title: "PHOTO",
//                     minWidth: 200,
//                     field: "images",
//                     headerHozAlign: "center",
//                     hozAlign: "center",
//                     vertAlign: "middle",
//                     print: false,
//                     download: false,
//                     formatter(cell, formatterParams) {
//                         return `<div class="flex lg:justify-center">
//                             <div class="intro-x w-10 h-10 image-fit">
//                                 <img  class="rounded-full" src="${
//                                     cell.getData().images
//                                 }">
//                             </div>
//                         </div>`;
//                     },
//                 },
//                 {
//                     title: "NAME",
//                     field: "name",
//                     vertAlign: "middle",
//                     headerHozAlign: "center",
//                     hozAlign:  "center",
//                     formatter(cell, formatterParams) {
//                         return `<div>
//                             <div class="font-medium whitespace-nowrap">${
//                                 cell.getData().name
//                             }</div>
//                             <div class="text-slate-500 text-xs whitespace-nowrap">${
//                                 cell.getData().type
//                             }</div>
//                         </div>`;
//                     },
//                 },
                
//             ],
//             renderComplete() {
//                 createIcons({
//                     icons,
//                     "stroke-width": 1.5,
//                     nameAttr: "data-lucide",
//                 });
//             },
//         });

//         // Redraw table onresize
//         window.addEventListener("resize", () => {
//             tableContent.redraw();
//             createIcons({
//                 icons,
//                 "stroke-width": 1.5,
//                 nameAttr: "data-lucide",
//             });
//         });

//         // Export
//         $("#tabulator-export-csv").on("click", function (event) {
//             tableContent.download("csv", "data.csv");
//         });

//         $("#tabulator-export-json").on("click", function (event) {
//             tableContent.download("json", "data.json");
//         });

//         $("#tabulator-export-xlsx").on("click", function (event) {
//             window.XLSX = xlsx;
//             tableContent.download("xlsx", "data.xlsx", {
//                 sheetName: "Plan Date List Details",
//             });
//         });

//         $("#tabulator-export-html").on("click", function (event) {
//             tableContent.download("html", "data.html", {
//                 style: true,
//             });
//         });

//         // Print
//         $("#tabulator-print").on("click", function (event) {
//             tableContent.print();
//         });
//     };
//     return {
//         init: function () {
//             _tableGen();
//         },
//     };
// })();


// var classStudentListTutorModuleTable = (function () {
//     var _tableGen = function () {
//         // Setup Tabulator
//         let planid = $('#classStudentListTutorModuleTable').attr('data-planid');
//         let statusu = $("#status-CLTML").val() != "" ? $("#status-CLTML").val() : "";
        
//         let tableContent = new Tabulator("#classStudentListTutorModuleTable", {
//             ajaxURL: route("student-assign.list"),
//             ajaxParams: { planid: planid, status: statusu },
//             ajaxFiltering: true,
//             ajaxSorting: true,
//             printAsHtml: true,
//             printStyled: true,
//             pagination: "remote",
//             paginationSize: 10,
//             paginationSizeSelector: [true, 5, 10, 20, 30, 40],
//             layout: "fitColumns",
//             responsiveLayout: "collapse",
//             placeholder: "No matching records found",
//             selectable:true,
//             columns: [
//                 {
//                     formatter: "rowSelection", 
//                     titleFormatter: "rowSelection", 
//                     hozAlign: "left", 
//                     headerHozAlign: "left",
//                     width: "160",
//                     headerSort: false, 
//                     download: false,
//                     cellClick:function(e, cell){
//                         cell.getRow().toggleSelect();
//                     }
//                 },
//                 {
//                     title: "#",
//                     field: "sl",
                    
//                     headerSort: false,
//                     width: "180",
//                 },
                
//                 {
//                     title: "PHOTO",
//                     minWidth: 200,
//                     field: "images",
//                     hozAlign: "center",
//                     headerHozAlign: "center",
//                     vertAlign: "middle",
//                     print: false,
//                     download: false,
//                     formatter(cell, formatterParams) {
//                         return `<div class="flex lg:justify-center">
//                             <div class="intro-x w-10 h-10 image-fit">
//                                 <img  class="rounded-full" src="${
//                                     cell.getData().images
//                                 }">
//                             </div>
//                         </div>`;
//                     },
//                 },
                
//                 {
//                     title: "NAME",
//                     field: "name",
//                     vertAlign: "middle",
//                     headerHozAlign: "center",
//                     hozAlign:  "center",
//                     formatter(cell, formatterParams) {
//                         return `<div>
//                             <div class="font-medium whitespace-nowrap">${
//                                 cell.getData().name
//                             }</div>
//                         </div>`;
//                     },
//                 },
                
//                 {
//                     title: "REGESTER NO",
//                     field: "name",
//                     vertAlign: "middle",
//                     headerHozAlign: "center",
//                     hozAlign:  "center",
//                     formatter(cell, formatterParams) {
//                         return `<div>
//                             <div class="font-medium whitespace-nowrap">${
//                                 cell.getData().register_no
//                             }</div>
//                         </div>`;
//                     },
//                 },
                
//             ],
//             renderComplete() {
//                 createIcons({
//                     icons,
//                     "stroke-width": 1.5,
//                     nameAttr: "data-lucide",
//                 });
//             },
//         });

//         // Redraw table onresize
//         window.addEventListener("resize", () => {
//             tableContent.redraw();
//             createIcons({
//                 icons,
//                 "stroke-width": 1.5,
//                 nameAttr: "data-lucide",
//             });
//         });

//         // Export
//         $("#tabulator-export-csv-CLTML").on("click", function (event) {
//             tableContent.download("csv", "data.csv");
//         });

//         $("#tabulator-export-json-CLTML").on("click", function (event) {
//             tableContent.download("json", "data.json");
//         });

//         $("#tabulator-export-xlsx-CLTML").on("click", function (event) {
//             window.XLSX = xlsx;
//             tableContent.download("xlsx", "data.xlsx", {
//                 sheetName: "Student List Details",
//             });
//         });

//         $("#tabulator-export-html-CLTML").on("click", function (event) {
//             tableContent.download("html", "data.html", {
//                 style: true,
//             });
//         });

//         // Print
//         $("#tabulator-print-CLTML").on("click", function (event) {
//             tableContent.print();
//         });
//     };
//     return {
//         init: function () {
//             _tableGen();
//         },
//     };
// })();

(function(){

    
    // if ($("#classParticipantsTutorTable").length) {
    //     // Init Table
    //     classParticipantsTutorTable.init();

    //     // Filter function
    //     function filterHTMLForm() {
    //         classParticipantsTutorTable.init();
    //     }

    //     // On submit filter form
    //     $("#tabulatorFilterForm-PT")[0].addEventListener(
    //         "keypress",
    //         function (event) {
    //             let keycode = event.keyCode ? event.keyCode : event.which;
    //             if (keycode == "13") {
    //                 event.preventDefault();
    //                 filterHTMLForm();
    //             }
    //         }
    //     );

    //     // On click go button
    //     $("#tabulator-html-filter-go-PT").on("click", function (event) {
    //         filterHTMLForm();
    //     });

    //     // On reset filter form
    //     $("#tabulator-html-filter-reset-PT").on("click", function (event) {
            
    //         $("#status-PT").val("1");
    //         filterHTMLForm();
    //     });
    // }

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

    // if ($("#classStudentListTutorModuleTable").length) {
    //     // Init Table
    //     classStudentListTutorModuleTable.init();

    //     // Filter function
    //     function filterHTMLFormCLTML() {
    //         classStudentListTutorModuleTable.init();
    //     }

    //     // On submit filter form
    //     $("#tabulatorFilterForm-CLTML")[0].addEventListener(
    //         "keypress",
    //         function (event) {
    //             let keycode = event.keyCode ? event.keyCode : event.which;
    //             if (keycode == "13") {
    //                 event.preventDefault();
    //                 filterHTMLFormCLTML();
    //             }
    //         }
    //     );

    //     // On click go button
    //     $("#tabulator-html-filter-go-CLTML").on("click", function (event) {
    //         filterHTMLFormCLTML();
    //     });

    //     // On reset filter form
    //     $("#tabulator-html-filter-reset-CLTML").on("click", function (event) {
    //         $("#dates-CLTML").val("");
    //         $("#status-CLTML").val("1");
    //         filterHTMLFormCLTML();
    //     });
    // }
    /* End Tabulator */


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