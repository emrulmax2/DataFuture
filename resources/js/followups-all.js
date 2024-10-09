import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");
var allFollowupsListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator

        var term_delclaration = $('#flup_term_declaration_id').val();
        var flup_status = $('#flup_status').val();

        let tableContent = new Tabulator("#allFollowupsListTable", {
            ajaxURL: route("followups.list.all"),
            ajaxParams: { term_delclaration: term_delclaration, status : flup_status},
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
                        var html = '<a href="'+route('student.notes', cell.getData().student_id) +'" class="block">';
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
                    title: "Term",
                    field: "term",
                    headerHozAlign: "left",
                    headerSort: false,
                },
                {
                    title: "Date",
                    field: "opening_date",
                    headerHozAlign: "left",
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
                                if(cell.getData().followed != '' && cell.getData().followed_up_status == 'Pending'){
                                    html += '<div class="whitespace-normal">';
                                        html += cell.getData().followed;
                                    html += '</div>';
                                }
                                if(cell.getData().followed_up_status == 'Completed'){
                                    html += '<div class="whitespace-normal">';
                                        html += (cell.getData().completed_by != '' ? '<div class="font-medium whitespace-nowrap">'+cell.getData().completed_by+'</div>' : '');
                                        html += (cell.getData().completed_at != '' ? '<div class="text-slate-500 text-xs whitespace-nowrap">'+cell.getData().completed_at+'</div>' : '');
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

                        btns += '<a href="'+route('student.notes', cell.getData().student_id) +'" class="view_btn btn btn-twitter text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="eye-off" class="w-4 h-4"></i></a>';
                        //btns += '<button data-id="' + cell.getData().id + '" type="button" class="completedBtn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="check-circle" class="w-4 h-4"></i></a>';
                            
                        
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
    allFollowupsListTable.init();

    function filterHTMLFormCML() {
        allFollowupsListTable.init();
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

    $('#allFollowupsListTable').on('click', '.view_btn', function(e){
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
})()