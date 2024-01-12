import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");
var taskAssignedStudentTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query").val() != "" ? $("#query").val() : "";
        let task_id = $("#taskAssignedStudentTable").attr('data-taskid');
        let phase = $("#taskAssignedStudentTable").attr('data-phase');
        
        
        let tableContent = new Tabulator("#taskAssignedStudentTable", {
            ajaxURL: route("task.manager.list"),
            ajaxParams: { querystr : querystr, task_id : task_id, phase : phase},
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
            
            selectable: (phase == 'Live' ? true : false),
            columns: [
                {
                    formatter: "rowSelection", 
                    titleFormatter: "rowSelection", 
                    hozAlign: "left", 
                    headerHozAlign: "left",
                    width: "60",
                    headerSort: false, 
                    download: false,
                    visible: (phase == 'Live' ? true : false),
                    cellClick:function(e, cell){
                        cell.getRow().toggleSelect();
                    }
                },
                {
                    title: (phase == 'Applicant' ? 'Ref. No' : 'Reg. No'),
                    field: (phase == 'Applicant' ? "application_no" : 'registration_no'),
                    headerHozAlign: "left",
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
                    title: "DOB",
                    field: "date_of_birth",
                    headerHozAlign: "left",
                },
                {
                    title: "Course",
                    field: "course",
                    headerSort: false,
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) {  
                        var html = '<div class="whitespace-normal">';
                                html += '<span>'+cell.getData().course+'</span><br/>';
                                html += '<span>'+cell.getData().semester+'</span><br/>';
                            html += '</div>';
                        return html;
                    }
                },
                {
                    title: "Status",
                    field: "status_id",
                    headerHozAlign: "left",
                },
                {
                    title: "Task Status",
                    field: "task_status",
                    headerSort: false,
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) {  
                        var html = '<div>';
                                html += '<span class="font-medium">'+cell.getData().task_status+'</span><br/>';
                                html += '<span>'+cell.getData().task_created+'</span><br/>';
                            html += '</div>';
                            html += '<input type="hidden" name="phase" class="phase" value="'+cell.getData().phase+'"/>';
                            html += '<input type="hidden" name="ids" class="ids" value="'+cell.getData().ids+'"/>';
                        return html;
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
            rowClick:function(e, row){
                window.open(row.getData().url, '_blank');
            },
            rowSelectionChanged:function(data, rows){
                var ids = [];
                if(rows.length > 0){
                    if(task_id == 5){
                        $('#exportTaskStudentsBtn').fadeIn();
                        $('#completeEmailTaskStudentsBtn').fadeIn();
                    }
                }else{
                    $('#exportTaskStudentsBtn').fadeOut();
                    $('#completeEmailTaskStudentsBtn').fadeOut();
                }
            },
            selectableCheck:function(row){
                return row.getData().task_id > 0;
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
    if($('#taskAssignedStudentTable').length > 0){
        // Init Table
        taskAssignedStudentTable.init();

        // Filter function
        function filterHTMLFormADM() {
            taskAssignedStudentTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLFormADM();
                }
            }
        );

        // On click go button
        $("#tabulator-html-filter-go").on("click", function (event) {
            filterHTMLFormADM();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset").on("click", function (event) {
            $("#query").val('');

            filterHTMLFormADM();
        });
    }

    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));

    $('#exportTaskStudentsBtn').on('click', function(e){
        e.preventDefault();
        var $btn = $(this);
        var ids = [];

        $btn.attr('disabled', 'disabled');
        $btn.siblings('#completeEmailTaskStudentsBtn').attr('disabled', 'disabled');
        $('#taskAssignedStudentTable').find('.tabulator-row.tabulator-selected').each(function(){
            var $row = $(this);
            ids.push($row.find('.ids').val());
        });

        if(ids.length > 0){
            $.ajax({
                type: 'GET',
                url: route('task.manager.students.email.excel'),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                data: {
                    ids: ids
                },
                xhrFields:{
                    responseType: 'blob'
                },
                beforeSend: function() {},
                success: function(data) {
                    $btn.removeAttr('disabled').fadeOut();
                    $btn.siblings('#completeEmailTaskStudentsBtn').removeAttr('disabled').fadeOut();
                    taskAssignedStudentTable.init();

                    var link = document.createElement('a');
                        link.href = window.URL.createObjectURL(data);
                        link.download = 'New_Student_Email_Id_Create_Task.xlsx';
                        link.click();

                        link.remove();

                    /*var url = window.URL || window.webkitURL;
                    var objectUrl = url.createObjectURL(data);
                    var newWindow = window.open(objectUrl);
                    newWindow.document.title = 'New_Student_Email_Id_Create_Task';*/
                },
                error: function(data) {
                    console.log(data);
                }
            });
        }else{
            $btn.removeAttr('disabled').fadeOut();
            $btn.siblings('#completeEmailTaskStudentsBtn').removeAttr('disabled').fadeOut();
            taskAssignedStudentTable.init();
        }
    });

    $('#completeEmailTaskStudentsBtn').on('click', function(e){
        e.preventDefault();
        var $btn = $(this);
        var ids = [];

        $btn.attr('disabled', 'disabled');
        $btn.siblings('#exportTaskStudentsBtn').attr('disabled', 'disabled')
        $('#taskAssignedStudentTable').find('.tabulator-row.tabulator-selected').each(function(){
            var $row = $(this);
            ids.push($row.find('.ids').val());
        });

        if(ids.length > 0){
            axios({
                method: "post",
                url: route('task.manager.comlete.students.email.id.task'),
                data: {ids : ids},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $btn.removeAttr('disabled').fadeOut();
                    $btn.siblings('#exportTaskStudentsBtn').removeAttr('disabled').fadeOut()
                    taskAssignedStudentTable.init();

                    successModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html('Congratulations!');
                        $("#successModal .successModalDesc").html('Student New Email ID task successfully completed and welcome message has been sent.');
                    });

                    setTimeout(function(){
                        successModal.hide();
                    }, 2000);
                }
            }).catch(error => {
                if(error.response){
                    console.log('error');
                }
            });
        }else{
            $btn.removeAttr('disabled').fadeOut();
            $btn.siblings('#exportTaskStudentsBtn').removeAttr('disabled').fadeOut()
            taskAssignedStudentTable.init();
        }
    });
})();