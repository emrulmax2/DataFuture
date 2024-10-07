import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
 
("use strict");
var pendingFollowupsListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator

        let tableContent = new Tabulator("#pendingFollowupsListTable", {
            ajaxURL: route("followups.list"),
            ajaxParams: { querystr: '' },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 50,
            paginationSizeSelector: [true, 10, 20, 40, 50, 100],
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records found",
            columns: [
                {
                    title: "Student",
                    field: "registration_no",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) {  
                        var html = '<div class="block">';
                                html += '<div class="w-10 h-10 intro-x image-fit mr-4 inline-block">';
                                    html += '<img alt="'+cell.getData().first_name+'" class="rounded-full shadow" src="'+cell.getData().student_photo+'">';
                                html += '</div>';
                                html += '<div class="inline-block relative" style="top: -4px;">';
                                    html += '<div class="font-medium whitespace-nowrap uppercase">'+cell.getData().registration_no+'</div>';
                                    html += '<div class="text-slate-500 text-xs whitespace-nowrap">'+(cell.getData().first_name != '' ? cell.getData().first_name : '')+' '+(cell.getData().last_name != '' ? cell.getData().last_name : '')+'</div>';
                                html += '</div>';
                            html += '</div>';
                        return html;
                    }
                },
                {
                    title: "Term",
                    field: "term",
                    headerHozAlign: "left",
                    headerSort: false,
                    width: "150",
                },
                {
                    title: "Date",
                    field: "opening_date",
                    headerHozAlign: "left",
                    width: "150",
                },
                {
                    title: "Followed Up",
                    field: "followed_up",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        var html = '';
                        if(cell.getData().followed_up == 'yes'){
                            html += '<div>';
                                if(cell.getData().followed_up_status != ''){
                                    html += '<span class="bg-'+(cell.getData().followed_up_status == 'Pending' ? 'warning' : 'success')+' font-medium text-white px-2 py-1 inline-flex mb-1">'+cell.getData().followed_up_status+'</span>';
                                }
                                if(cell.getData().followed != ''){
                                    html += '<div class="whitespace-normal">';
                                        html += cell.getData().followed;
                                    html += '</div>';
                                }
                            html += '</div>';
                        }
                        return html;
                    }
                },
                {
                    title: "Created By",
                    field: "created_by",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        var html = '';
                        html += '<div>';
                            html += '<div class="font-medium whitespace-nowrap">'+cell.getData().created_by+'</div>';
                            html += '<div class="text-slate-500 text-xs whitespace-nowrap">'+cell.getData().created_at+'</div>';
                        html += '</div>';

                        return html;
                    }
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: "230",
                    download: false,
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if(cell.getData().note_document_id > 0){
                            btns +='<a data-id="'+cell.getData().note_document_id+'" href="javascript:void(0);" class="downloadDoc btn-rounded btn btn-linkedin text-white p-0 w-9 h-9 ml-1"><i data-lucide="cloud-lightning" class="w-4 h-4"></i></a>';
                        }

                        btns += '<button data-id="' + cell.getData().id + '" data-tw-toggle="modal" data-tw-target="#viewNoteModal"  class="view_btn btn btn-twitter text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="eye-off" class="w-4 h-4"></i></button>';
                        btns += '<button data-id="' + cell.getData().id + '" type="button" class="completedBtn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="check-circle" class="w-4 h-4"></i></a>';
                            
                        
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
    };
    return {
        init: function () {
            _tableGen();
        },
    };
})();


(function () {
    pendingFollowupsListTable.init();

    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));
    const viewNoteModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#viewNoteModal"));
    const confirmModalN = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModalN"));

    $('#successModal .successCloser').on('click', function(e){
        e.preventDefault();
        if($(this).attr('data-action') == 'RELOAD'){
            successModal.hide();
            window.location.reload();
        }else{
            successModal.hide();
        }
    })

    $('#pendingFollowupsListTable').on('click', '.view_btn', function(e){
        var $btn = $(this);
        var noteId = $btn.attr('data-id');
        axios({
            method: "post",
            url: route('student.show.note'),
            data: {noteId : noteId},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            $('#viewNoteModal .modal-body').html(response.data.message);
            if(response.data.btns != ''){
                $('#viewNoteModal .modal-footer .footerBtns').html(response.data.btns);
            }
            createIcons({
                icons,
                "stroke-width": 1.5,
                nameAttr: "data-lucide",
            });
        }).catch(error => {
            console.log('error');
        });
    });

    $('#pendingFollowupsListTable').on('click', '.downloadDoc', function(e){
        e.preventDefault();
        var $theLink = $(this);
        var row_id = $theLink.attr('data-id');

        $theLink.css({'opacity' : '.6', 'cursor' : 'not-allowed'});

        axios({
            method: "post",
            url: route('student.note.document.download'), 
            data: {row_id : row_id},
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
    });

    $('#viewNoteModal').on('click', '.downloadDoc', function(e){
        e.preventDefault();
        var $theLink = $(this);
        var row_id = $theLink.attr('data-id');

        $theLink.css({'opacity' : '.6', 'cursor' : 'not-allowed'});

        axios({
            method: "post",
            url: route('student.document.download'), 
            data: {row_id : row_id},
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
    });

    $('#pendingFollowupsListTable').on('click', '.completedBtn', function(e){
        e.preventDefault();
        var $btn = $(this);
        var noteId = $btn.attr('data-id');

        confirmModalN.show();
        document.getElementById("confirmModalN").addEventListener("shown.tw.modal", function (event) {
            $("#confirmModalN .confModTitle").html('Select "Yes" to finish the process.' );
            $("#confirmModalN .agreeWith").attr('data-recordid', noteId);
            $("#confirmModalN .agreeWith").attr('data-status', 'COMPLETEFOLLOWUP');
        });
    });

    $('#confirmModalN .agreeWith').on('click', function(e){
        e.preventDefault();
        let $agreeBTN = $(this);
        let recordid = $agreeBTN.attr('data-recordid');
        let action = $agreeBTN.attr('data-status');

        $('#confirmModalN button').attr('disabled', 'disabled');

        if(action == 'COMPLETEFOLLOWUP'){
            axios({
                method: 'POST',
                url: route('followups.completed'),
                data: {recordid : recordid},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModalN button').removeAttr('disabled');
                    confirmModalN.hide();
                    pendingFollowupsListTable.init();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('The process successfully completed.');
                        $('#successModal .successCloser').attr('data-action', 'NONE');
                    });

                    setTimeout(function(){
                        successModal.hide();
                    }, 2000);
                }
            }).catch(error =>{
                console.log(error)
            });
        }else{
            confirmModal.hide();
        }
    });

})();