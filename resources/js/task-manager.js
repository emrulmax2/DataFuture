import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import html2canvas from "html2canvas";
import { saveAs } from 'file-saver';

("use strict");
var taskAssignedStudentTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let status = $("#status").val() != "" ? $("#status").val() : "";
        let task_id = $("#taskAssignedStudentTable").attr('data-taskid');
        let phase = $("#taskAssignedStudentTable").attr('data-phase');
        
        
        let tableContent = new Tabulator("#taskAssignedStudentTable", {
            ajaxURL: route("task.manager.list"),
            ajaxParams: { status : status, task_id : task_id, phase : phase},
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
            
            selectable: true,
            columns: [
                {
                    formatter: "rowSelection", 
                    titleFormatter: "rowSelection", 
                    hozAlign: "left", 
                    headerHozAlign: "left",
                    width: "60",
                    headerSort: false, 
                    download: false,
                    cellClick:function(e, cell){
                        cell.getRow().toggleSelect();
                    }
                },
                {
                    title: (phase == 'Applicant' ? 'Ref. No' : 'Reg. No'),
                    field: (phase == 'Applicant' ? "application_no" : 'registration_no'),
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) {  
                        var html = '<a href="'+cell.getData().url+'" class="whitespace-normal font-medium text-primary">';
                                html += (phase == 'Applicant' ? cell.getData().application_no : cell.getData().registration_no),
                            html += '</a>';
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
                        var html = '<div class="flex justify-start items-center">';
                                html += '<div>';
                                    html += '<span class="font-medium">'+cell.getData().task_status+'</span><br/>';
                                    html += '<span>'+cell.getData().task_created+'</span><br/>';
                                html += '</div>';
                                if(cell.getData().task_id == 2){
                                    html += '<button data-taskid="'+cell.getData().task_id+'" data-studentid="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#downloadIDCard" type="button" class="downloadIDCardBtn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-4"><i data-lucide="download-cloud" class="w-4 h-4"></i></a>';
                                }
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
            rowSelectionChanged:function(data, rows){
                var ids = [];
                if(rows.length > 0){
                    if(task_id == 5){
                        $('#exportTaskStudentsBtn').fadeIn();
                        $('#completeEmailTaskStudentsBtn').fadeIn();
                    }else{
                        $('#commonActionBtns').fadeIn();
                    }
                }else{
                    $('#exportTaskStudentsBtn').fadeOut();
                    $('#completeEmailTaskStudentsBtn').fadeOut();
                    $('#commonActionBtns').fadeOut();
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
    const downloadIDCard = tailwind.Modal.getOrCreateInstance(document.querySelector("#downloadIDCard"));

    const downloadIDCardEl = document.getElementById('downloadIDCard')
    downloadIDCardEl.addEventListener('hide.tw.modal', function(event) {
        $('#downloadIDCard .idContent').html('').fadeOut('fast');
        $('#downloadIDCard .idLoader').fadeIn('fast');
    });

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

    $('#taskAssignedStudentTable').on('click', '.downloadIDCardBtn', function(e){
        e.preventDefault();
        var $btn = $(this);
        var task_id = $btn.attr('data-taskid');
        var student_id = $btn.attr('data-studentid');

        axios({
            method: "post",
            url: route('task.manager.download.id.card'),
            data: {student_id : student_id, task_id : task_id},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200) {
                $('#downloadIDCard .idLoader').fadeOut('fast');
                $('#downloadIDCard .idContent').fadeIn('fast').html(response.data.res);
            }
        }).catch(error => {
            if(error.response){
                console.log('error');
            }
        });
    })

    $('#downloadIDCard').on('click', '.thePrintBtn', function(){
        var $currentBtn = $(this);
        var currentIdAttr = $currentBtn.attr('data-id');
        var currentId = '#theIDCard_'+currentIdAttr;
        var $currentIDCard = $('#theIDCard_'+currentIdAttr);

        html2canvas(document.querySelector(currentId), { useCORS: true, allowTaint : true }).then(canvas => {
            canvas.toBlob(function(blob) {
                window.saveAs(blob, currentIdAttr+'.jpg');

                setTimeout(function(){
                    downloadIDCard.hide();
                }, 2000);
            });
        });
    });

    
    $('.updateSelectedStudentTaskStatusBtn').on('click', function(e){
        e.preventDefault();
        var $btn = $(this);

        if(!$btn.hasClass('disabled')){

            $btn.addClass('disabled');
            $btn.find('svg.theLoaderSvg').fadeIn();
            $btn.closest('.updateSelectedStudentTaskStatusBtn').addClass('disabled');

            var task_id = $btn.attr('data-taskid');
            var status = $btn.attr('data-status');
            var phase = $btn.attr('data-phase');
            var student_ids = [];
            $('#taskAssignedStudentTable').find('.tabulator-row.tabulator-selected').each(function(){
                var $row = $(this);
                student_ids.push($row.find('.ids').val());
            });

            if(student_ids.length > 0){
                axios({
                    method: "post",
                    url: route('task.manager.update.task.status'),
                    data: {student_ids : student_ids, task_id : task_id, status : status, phase : phase},
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $btn.removeClass('disabled');
                        $btn.find('svg.theLoaderSvg').fadeOut();
                        $btn.closest('.updateSelectedStudentTaskStatusBtn').removeClass('disabled');

                        taskAssignedStudentTable.init();

                        successModal.show();
                        document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html('Congratulations!');
                            $("#successModal .successModalDesc").html('Selected students task status successfully updated.');
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
                $btn.removeClass('disabled');
                $btn.find('svg.theLoaderSvg').fadeOut();
                $btn.closest('.updateSelectedStudentTaskStatusBtn').removeClass('disabled');

                taskAssignedStudentTable.init();
            }
        }
    });

})();