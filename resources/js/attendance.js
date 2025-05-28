import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");
var attendanceListTable = (function () {
    var _tableGen = function (form) {
        // Setup Tabulator
        
        
        let tableContent = new Tabulator("#attendanceListTable", {
            ajaxURL: route("attendance.list"),
            ajaxParams: { "plan_date" : form},
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            headerSort:false,
            paginationSize: 10,
            paginationSizeSelector: [true, 5, 10, 20, 30, 40],
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records found",
            columns: [
                {
                    formatter: "responsiveCollapse",
                    width: 40,
                    minWidth: 30,
                    hozAlign: "center",
                    resizable: false,
                    headerSort: false,
                },
                {
                    title: "COURSE & MODULE",
                    minWidth: 200,
                    responsive: 0,
                    field: "course",
                    vertAlign: "middle",
                    headerHozAlign: "left",
                    print: false,
                    download: false,
                    formatter(cell, formatterParams) {
                        return `<div>
                            <div class="font-medium whitespace-nowrap">${
                                cell.getData().course
                            }</div>
                            <div class="text-slate-500 text-xs whitespace-nowrap">${
                                cell.getData().module
                            }</div>
                        </div>`;
                    },
                },
                {
                    title: "GROUP",
                    field: "group",
                    vertAlign: "middle",
                    headerHozAlign: "center",
                    hozAlign:  "center",
                    width:100,
                    formatter(cell, formatterParams) {
                        return `<div>
                            <div class="font-medium whitespace-nowrap">${
                                cell.getData().group
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
                    title: "ROOM",
                    field: "venue",
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
                    title: "VR",
                    field: "virtual_room",
                    vertAlign: "middle",
                    headerHozAlign: "center",
                    hozAlign:  "center",
                    headerSort:false,
                    width:80,
                    formatter(cell, formatterParams) {       
                        return '<a href="'+cell.getData().virtual_room+'" target="_blank"  class="btn-primary btn text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="video" class="w-4 h-4"></i></a>';
                    },
                },
                
                {
                    title: "TUTOR",
                    field: "tutor",
                    vertAlign: "middle",
                    headerHozAlign: "center",
                    hozAlign:  "center",
                    width:180,
                    formatter(cell, formatterParams) {
                        return `<div>
                            <div class="font-medium whitespace-nowrap">${
                                cell.getData().tutor
                            }</div>
                        </div>`;
                    },
                },
                {
                    title: "LECTURE TYPE",
                    field: "lecture_type",
                    vertAlign: "middle",
                    hozAlign:  "center",
                    headerHozAlign: "center",
                    formatter(cell, formatterParams) {
                        return `<div>
                            <div class="font-medium whitespace-nowrap">${
                                cell.getData().lecture_type
                            }</div>
                        </div>`;
                    },
                },
                
                {
                    title: "CAPTURED BY AND AT",
                    field: "captured_by",
                    vertAlign: "middle",
                    hozAlign:  "center",
                    headerHozAlign: "center",
                    formatter(cell, formatterParams) {
                        return `<div>
                            <div class="font-medium whitespace-nowrap">${
                                cell.getData().captured_by
                            }</div>
                            <div class="text-slate-500 text-xs whitespace-nowrap">${
                                cell.getData().captured_at
                            }</div>
                        </div>`;
                    },
                },
                
                {
                    title: "JOIN",
                    field: "join_request",
                    vertAlign: "middle",
                    hozAlign:  "center",
                    headerHozAlign: "center",
                },
                
                {
                    title: "STATUS",
                    field: "status",
                    vertAlign: "middle",
                    hozAlign:  "center",
                    headerHozAlign: "center",
                },
                {
                    title: "ACTIONS",
                    minWidth: 200,
                    field: "actions",
                    responsive: 1,
                    hozAlign: "center",
                    vertAlign: "middle",
                    headerHozAlign: "center",
                    print: false,
                    download: false,
                    width: "200",
                    formatter(cell, formatterParams) {
                        
                        let dropdown =`<div class="dropdown ml-auto sm:ml-0">
                        <button class="dropdown-toggle transition duration-200 border btn-rounded shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&amp;:hover:not(:disabled)]:bg-opacity-90 [&amp;:hover:not(:disabled)]:border-opacity-90 [&amp;:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed bg-primary border-primary text-white dark:border-primary  mb-2 mr-1" aria-expanded="false" data-tw-toggle="dropdown">
                            <span class="w-5 h-5 flex items-center justify-center">
                                <i class="w-4 h-4" data-lucide="plus"></i>
                            </span>
                        </button>
                        <div class="dropdown-menu w-80">
                            <ul class="dropdown-content">
                                <li>
                                    <a href="" class="dropdown-item">
                                        <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print Attendance Sheet
                                    </a>
                                </li>
                                <li>
                                    <a href="attendance/create/${
                                        cell.getData().id
                                    }" class="dropdown-item">
                                        <i data-lucide="users" class="w-4 h-4 mr-2"></i> Feed Attendance
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>`;
                        // let a =
                        //     $(`<div class="flex lg:justify-center items-center">
                        //     <a class="edit flex items-center mr-3" href="javascript:;">
                        //         <i data-lucide="check-square" class="w-4 h-4 mr-1"></i> Edit
                        //     </a>
                        //     <a class="delete flex items-center text-danger" href="javascript:;">
                        //         <i data-lucide="trash-2" class="w-4 h-4 mr-1"></i> Delete
                        //     </a>
                        // </div>`);
                        // $(a)
                        //     .find(".edit")
                        //     .on("click", function () {
                        //         alert("EDIT");
                        //     });

                        // $(a)
                        //     .find(".delete")
                        //     .on("click", function () {
                        //         alert("DELETE");
                        //     });

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
                const columnLists = this.getColumns();
                if (columnLists.length > 0) {
                    const lastColumn = columnLists[columnLists.length - 1];
                    const currentWidth = lastColumn.getWidth();
                    lastColumn.setWidth(currentWidth - 1);
                }
            },
            rowClick:function(e, row){
                //window.open(row.getData().url, '_blank');
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
    };
    return {
        init: function (form = []) {
            _tableGen(form);
        },
    };
})();

(function(){
    if($('#attendanceListTable').length > 0){
        function resetPlanDateSearch(){
            $('#plan_date').val('');
        }

        //attendanceListTable.init();

        function filterAttendanceListTable(form) {
            attendanceListTable.init(form);
        }

        $("#attendance_search").on("submit", function (event) {
            event.preventDefault()
            //let form_data = new FormData()
            
            let dateSearch  = $("input[name='plan_date']").val()
            //form_data.append( 'plan_date', dateSearch )
            
            let form = {
              "plan_date": dateSearch
            }
            console.log(form)
            filterAttendanceListTable(dateSearch)
        });

        $("#resetPlanDateSearch").on("click", function (event) {
            resetPlanDateSearch();

            //filterAttendanceListTable();
        });
    }
})();
