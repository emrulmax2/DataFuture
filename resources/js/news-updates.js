import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

("use strict");
var newsUpdatesList = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query").val() != "" ? $("#query").val() : "";
        let status = $("#status").val() != "" ? $("#status").val() : 1;
        let tableContent = new Tabulator("#newsUpdatesList", {
            ajaxURL: route("news.updates.list"),
            ajaxParams: { querystr: querystr, status: status },
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
                    field: "id",
                    headerSort:false,
                    width: "50",
                },
                {
                    title: "Title",
                    field: "title",
                    headerHozAlign: "left",
                    width: "250",
                    formatter(cell, formatterParams){
                        var html = '';
                        html += '<div class="whitespace-normal">';
                            html += cell.getData().title;
                        html += '</div>';

                        return html;
                    }
                },
                {
                    title: "News",
                    field: "content",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        var html = '';
                        html += '<div class="whitespace-normal">';
                            html += cell.getData().content+'...';
                        html += '</div>';

                        return html;
                    }
                },
                {
                    title: "Created By",
                    field: "created_by",
                    headerHozAlign: "left",
                    width: "250",
                    minWidth: 180,
                    formatter(cell, formatterParams){
                        var html = '';
                        html += '<div>';
                            html += '<div class="text-slate-500 text-xs whitespace-nowrap">'+(cell.getData().created_at)+'</div>';
                            html += '<div class="font-medium whitespace-nowrap">'+cell.getData().created_by+'</div>';
                        html += '</div>';

                        return html;
                    }
                },
                {
                    title: "Status",
                    field: "active",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        return '<div class="form-check form-switch"><input data-id="'+cell.getData().id+'" '+(cell.getData().active == 1 ? 'Checked' : '')+' value="'+cell.getData().active+'" type="checkbox" class="status_updater form-check-input"> </div>';
                    }
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: "190",
                    download: false,
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if(cell.getData().fol_all == 0){
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#viewAssignedStudentModal" type="button" class="viewStudents btn btn-instagram text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="users" class="w-4 h-4"></i></button>';
                        }
                        if(cell.getData().documents.length > 0){
                            btns += '<div class="dropdown inline-flex">';
                                btns += '<button class="dropdown-toggle btn btn-facebook text-white btn-rounded ml-1 p-0 w-9 h-9" aria-expanded="false" data-tw-toggle="dropdown"><i data-lucide="download-cloud" class="w-4 h-4"></i></button>';
                                btns += '<div class="dropdown-menu w-64">';
                                    btns += '<ul class="dropdown-content">';
                                        for(const docs of cell.getData().documents){
                                            btns += '<li>';
                                                btns += '<a data-docid="'+docs.id+'" href="javascript:void(0);" class="dropdown-item downloadDoc whitespace-normal text-success break-all" style="align-items: flex-start;">';
                                                    btns += '<i data-lucide="check-circle" class="w-4 h-4 mr-2" style="flex: 0 0 .8rem;"></i>'+docs.name;
                                                btns += '</a>';
                                            btns += '</li>';
                                        }
                                    btns += '</ul>';
                                btns += '</div>';
                            btns += '</div>';
                        }
                        if (cell.getData().deleted_at == null) {
                            btns += '<a href="'+route('news.updates.edit', cell.getData().id)+'"  class="btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
                            btns += '<button data-id="' +cell.getData().id +'"  class="delete_btn btn btn-danger text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="Trash2" class="w-4 h-4"></i></button>';
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
        $("#tabulator-export-csv").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Users Details",
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

var assignedStudentModalListTable = (function () {
    var _tableGen = function (event_id) {
        let tableContent = new Tabulator('#assignedStudentModalListTable', {
            ajaxURL: route('news.updates.assigned.list'),
            ajaxParams: { event_id: event_id },
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
                    title: 'Reg. No',
                    field: 'registration_no',
                    headerHozAlign: 'left',
                    formatter(cell, formatterParams) {
                        var html = '<div class="block">';
                        html +=
                            '<div class="w-10 h-10 intro-x image-fit mr-4 inline-block">';
                        html +=
                            '<img alt="' +
                            cell.getData().first_name +
                            '" class="rounded-full shadow" src="' +
                            cell.getData().photo_url +
                            '">';
                        html += '</div>';
                        html +=
                            '<div class="inline-block relative" style="top: -13px;">';
                        html +=
                            '<div class="font-medium whitespace-nowrap uppercase">' +
                            cell.getData().registration_no +
                            '</div>';

                        html += '</div>';
                        html += '</div>';
                        return html;
                    },
                },
                {
                    title: 'First Name',
                    field: 'first_name',
                    headerHozAlign: 'left',
                },
                {
                    title: 'Last Name',
                    field: 'last_name',
                    headerHozAlign: 'left',
                },
                {
                    title: '',
                    field: 'full_time',
                    headerHozAlign: 'left',
                    headerSort: false,
                    formatter(cell, formatterParams) {
                        let day = false;
                        if (cell.getData().full_time == 1)
                            day = 'text-slate-900';
                        else day = 'text-amber-600';
                        var html = '<div class="flex">';
                        html +=
                            '<div class="w-8 h-8 ' +
                            day +
                            ' intro-x inline-flex">';
                        if (cell.getData().full_time == 1) {
                            html +=
                                '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="sunset" class="lucide lucide-sunset w-6 h-6"><path d="M12 10V2"></path><path d="m4.93 10.93 1.41 1.41"></path><path d="M2 18h2"></path><path d="M20 18h2"></path><path d="m19.07 10.93-1.41 1.41"></path><path d="M22 22H2"></path><path d="m16 6-4 4-4-4"></path><path d="M16 18a4 4 0 0 0-8 0"></path></svg>';
                        } else {
                            html +=
                                '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="sun" class="lucide lucide-sun w-6 h-6"><circle cx="12" cy="12" r="4"></circle><path d="M12 2v2"></path><path d="M12 20v2"></path><path d="m4.93 4.93 1.41 1.41"></path><path d="m17.66 17.66 1.41 1.41"></path><path d="M2 12h2"></path><path d="M20 12h2"></path><path d="m6.34 17.66-1.41 1.41"></path><path d="m19.07 4.93-1.41 1.41"></path></svg>';
                        }
                        html += '</div>';

                        html += '</div>';
                        createIcons({
                            icons,
                            'stroke-width': 1.5,
                            nameAttr: 'data-lucide',
                        });

                        return html;
                    },
                },
                {
                    title: 'Semester',
                    field: 'semester',
                    headerSort: false,
                    headerHozAlign: 'left',
                },
                {
                    title: 'Course',
                    field: 'course',
                    headerSort: false,
                    headerHozAlign: 'left',
                },
                {
                    title: 'Status',
                    field: 'status_id',
                    headerHozAlign: 'left',
                    width: 180,
                },
            ],
            renderComplete() {
                createIcons({
                    icons,
                    'stroke-width': 1.5,
                    nameAttr: 'data-lucide',
                });
                const columnLists = this.getColumns();
                if (columnLists.length > 0) {
                    const lastColumn = columnLists[columnLists.length - 1];
                    const currentWidth = lastColumn.getWidth();
                    lastColumn.setWidth(currentWidth - 1);
                }   
            },
            rowClick: function (e, row) {
                window.open(row.getData().url, '_blank');
            },
        });

        // Redraw table onresize
        window.addEventListener('resize', () => {
            tableContent.redraw();
            createIcons({
                icons,
                'stroke-width': 1.5,
                nameAttr: 'data-lucide',
            });
        });
    };
    return {
        init: function (plan_id) {
            _tableGen(plan_id);
        },
    };
})();

(function(){
    if ($("#newsUpdatesList").length) {
        newsUpdatesList.init();

        // Filter function
        function filterTitleHTMLForm() {
            newsUpdatesList.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm")[0].addEventListener(
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
        $("#tabulator-html-filter-go").on("click", function (event) {
            filterTitleHTMLForm();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset").on("click", function (event) {
            $("#query").val("");
            $("#status").val("1");
            filterTitleHTMLForm();
        });
    }

    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const viewAssignedStudentModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#viewAssignedStudentModal"));
    let confModalDelTitle = 'Are you sure?';

    const viewAssignedStudentModalEl = document.getElementById('viewAssignedStudentModal');
    viewAssignedStudentModalEl.addEventListener('hide.tw.modal', function (event) {
        $('#assignedStudentModalListTable').html('').removeClass('tabulator').removeAttr('tabulator-layout').removeAttr('role');
    });

    $('#newsUpdatesList').on('click', '.viewStudents', function (e) {
        var $theLink = $(this);
        var event_id = $theLink.attr('data-id');

        assignedStudentModalListTable.init(event_id);
    });

    $(document).on('click', '.downloadDoc', function(e){
        e.preventDefault();
        let $theLink = $(this);
        let docId = $theLink.attr('data-docid');

        $theLink.css({'opacity' : '.6', 'cursor' : 'not-allowed'});

        axios({
            method: "post",
            url: route('news.updates.document.download'), 
            data: {row_id : docId},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200){
                let res = response.data.res;
                $theLink.css({'opacity' : '1', 'cursor' : 'pointer'});

                if(res != ''){
                    window.open(res, '_blank');
                }
            } 
        }).catch(error => {
            if(error.response){
                $theLink.css({'opacity' : '1', 'cursor' : 'pointer'});
                console.log('error');
            }
        });
    })
    

    // Confirm Modal Action
    $('#confirmModal .agreeWith').on('click', function(){
        let $agreeBTN = $(this);
        let recordID = $agreeBTN.attr('data-id');
        let action = $agreeBTN.attr('data-action');

        $('#confirmModal button').attr('disabled', 'disabled');
        if(action == 'DELETE'){
            axios({
                method: 'delete',
                url: route('news.updates.destory', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('WOW!');
                        $('#successModal .successModalDesc').html('Record successfully deleted from DB row.');
                    });
                }
                newsUpdatesList.init();
            }).catch(error =>{
                console.log(error)
            });
        } else if(action == 'RESTORE'){
            axios({
                method: 'post',
                url: route('news.updates.restore', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('WOW!');
                        $('#successModal .successModalDesc').html('Record Successfully Restored!');
                    });
                }
                newsUpdatesList.init();
            }).catch(error =>{
                console.log(error)
            });
        } else if(action == 'CHANGESTAT'){
            axios({
                method: 'post',
                url: route('news.updates.update.status', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('WOW!');
                        $('#successModal .successModalDesc').html('Record status successfully updated!');
                    });
                }
                newsUpdatesList.init();
            }).catch(error =>{
                console.log(error)
            });
        }
    })

    // Delete Course
    $('#newsUpdatesList').on('click', '.status_updater', function(){
        let $statusBTN = $(this);
        let rowID = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html(confModalDelTitle);
            $('#confirmModal .confModDesc').html('Do you really want to change status of this record? If yes then please click on the agree btn.');
            $('#confirmModal .agreeWith').attr('data-id', rowID);
            $('#confirmModal .agreeWith').attr('data-action', 'CHANGESTAT');
        });
    });

    // Delete Course
    $('#newsUpdatesList').on('click', '.delete_btn', function(){
        let $statusBTN = $(this);
        let rowID = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html(confModalDelTitle);
            $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
            $('#confirmModal .agreeWith').attr('data-id', rowID);
            $('#confirmModal .agreeWith').attr('data-action', 'DELETE');
        });
    });

    // Restore Course
    $('#newsUpdatesList').on('click', '.restore_btn', function(){
        let $statusBTN = $(this);
        let courseID = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html(confModalDelTitle);
            $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
            $('#confirmModal .agreeWith').attr('data-id', courseID);
            $('#confirmModal .agreeWith').attr('data-action', 'RESTORE');
        });
    });
})();