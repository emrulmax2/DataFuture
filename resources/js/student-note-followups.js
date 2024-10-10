import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import tippy, { roundArrow } from "tippy.js";
 
("use strict");
var pendingFollowupsListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator

        var term_delclaration = $('#flup_term_declaration_id').val();
        var flup_status = $('#flup_status').val();

        let tableContent = new Tabulator("#pendingFollowupsListTable", {
            ajaxURL: route("followups.list"),
            ajaxParams: { term_delclaration: term_delclaration, status : flup_status },
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
                    width: "250",
                    formatter(cell, formatterParams) {  
                        var html = '<a target="_blank" href="'+route('student.notes', cell.getData().student_id) +'" class="block">';
                                html += '<div class="w-10 h-10 intro-x image-fit mr-4 inline-block">';
                                    html += '<img alt="'+cell.getData().first_name+'" class="rounded-full shadow" src="'+cell.getData().student_photo+'">';
                                html += '</div>';
                                html += '<div class="inline-block relative" style="top: -4px;">';
                                    html += '<div class="font-medium whitespace-nowrap uppercase">'+cell.getData().registration_no+'</div>';
                                    html += '<div class="text-slate-500 text-xs whitespace-nowrap">'+(cell.getData().first_name != '' ? cell.getData().first_name : '')+' '+(cell.getData().last_name != '' ? cell.getData().last_name : '')+'</div>';
                                html += '</div>';
                            html += '</a>';
                        return html;
                    }
                },
                {
                    title: "Note",
                    field: "note",
                    headerHozAlign: "left",
                    headerSort: false,
                    formatter(cell, formatterParams){
                        var note = cell.getData().note;
                        var html = '<div class="whitespace-normal break-words">';
                                if(note.length > 250){
                                    html += note.substring(0, 250);
                                    html += '&nbsp;<a data-id="'+cell.getData().id+'" data-tw-toggle="modal" data-tw-target="#viewNoteModal" href="javascript:void(0);" class="view_btn text-primary font-medium underline">[More]</a>';
                                }else{
                                    html += note;
                                }
                                if(cell.getData().note_document_id > 0){
                                    html +='<br/><a data-id="'+cell.getData().note_document_id+'" href="javascript:void(0);" class="downloadDoc btn btn-linkedin text-white px-2 py-0 w-auto h-auto mt-2"><i data-lucide="cloud-lightning" class="w-4 h-4 mr-1"></i> Download Attachment</a>';
                                }
                            html += '</div>';
                        return html;
                    }
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
                    width: "250",
                    formatter(cell, formatterParams){
                        var html = '';
                        html += '<div>';
                            html += '<div class="text-slate-500 text-xs whitespace-nowrap">'+cell.getData().opening_date+'</div>';
                            html += '<div class="font-medium whitespace-nowrap">By: '+cell.getData().created_by+'</div>';
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
                    width: "150",
                    download: false,
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        var countHtml = cell.getData().unread_comment > 0 ? '<span class="bg-danger absolute r-0 t-0" style="    width: 18px; height: 18px; border-radius: 50%; font-size: 11px; line-height: 1; padding: 3px 0 0; margin: -5px -5px 0 0;">'+cell.getData().unread_comment+'</span>' : '';
                        btns += '<button data-id="' + cell.getData().id + '" data-tw-toggle="modal" data-tw-target="#followUpCommentModal"  class="viewCommentBtn relative btn btn-twitter text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="message-square-plus" class="w-4 h-4"></i>'+countHtml+'</button>';
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

    function filterHTMLFormCML() {
        pendingFollowupsListTable.init();
    }

    // On click go button
    $("#tabulator-html-filter-go").on("click", function (event) {
        filterHTMLFormCML();
    });

    // On reset filter form
    $("#tabulator-html-filter-reset").on("click", function (event) {
        $("#flup_term_declaration_id").val("");
        $("#flup_status").val("Pending");

        filterHTMLFormCML();
    });

    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));
    const viewNoteModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#viewNoteModal"));
    const confirmModalN = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModalN"));
    const followUpCommentModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#followUpCommentModal"));

    const followUpCommentModalEl = document.getElementById('followUpCommentModal')
    followUpCommentModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#followUpCommentModal textarea').val('');
        $('#followUpCommentModal [name="student_note_id"]').val('0');
        $('#followUpCommentModal #followUpCommentWrap').html('Loading...');
    });

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

    $('#pendingFollowupsListTable').on('click', '.viewCommentBtn', function(e){
        var $btn = $(this);
        var note_id = $btn.attr('data-id');
        axios({
            method: "POST",
            url: route('followups.comment.list'),
            data: {note_id : note_id},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200) {
                $('#followUpCommentWrap').html(response.data.htm);
                $('#followUpCommentModal [name="student_note_id"]').val(note_id);

                
                setTimeout(() => {
                    $('#followUpCommentModal').find('.tooltip').each(function () {
                        let tippyOption = {
                            content: $(this).attr("alt"),
                        };
                        tippy(this, {
                            arrow: roundArrow,
                            animation: "shift-away",
                            zIndex: '9999999999',
                            ...tippyOption,
                        });
                    });
                }, 10);
            }
        }).catch(error => {
            if (error.response) {
                console.log('error');
            }
        });

        pendingFollowupsListTable.init();
    });

    $('#followUpCommentForm').on('submit', function(e){
        e.preventDefault();
        var $form = $(this);
        const form = document.getElementById('followUpCommentForm');
    
        $('#postCommentBtn').attr('disabled', 'disabled');
        $('#postCommentBtn svg.theIcon').fadeOut();
        $('#postCommentBtn svg.theLoader').fadeIn();

        if($('#the_comment', $form).val() != ''){
            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('followups.comment.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                $('#postCommentBtn').attr('disabled', 'disabled');
                $('#postCommentBtn svg.theIcon').fadeIn();
                $('#postCommentBtn svg.theLoader').fadeOut();

                if (response.status == 200) {
                    $('#followUpCommentWrap').html(response.data.htm);
                    $('#the_comment', $form).val('');

                
                    setTimeout(() => {
                        $('#followUpCommentModal').find('.tooltip').each(function () {
                            let tippyOption = {
                                content: $(this).attr("alt"),
                            };
                            tippy(this, {
                                arrow: roundArrow,
                                animation: "shift-away",
                                zIndex: '9999999999',
                                ...tippyOption,
                            });
                        });
                    }, 10);
                }
            }).catch(error => {
                $('#postCommentBtn').attr('disabled', 'disabled');
                $('#postCommentBtn svg.theIcon').fadeIn();
                $('#postCommentBtn svg.theLoader').fadeOut();
                if (error.response) {
                    console.log('error');
                }
            });
        }else{
            $('#postCommentBtn').attr('disabled', 'disabled');
            $('#postCommentBtn svg.theIcon').fadeIn();
            $('#postCommentBtn svg.theLoader').fadeOut();
        }

        pendingFollowupsListTable.init();
    });

})();