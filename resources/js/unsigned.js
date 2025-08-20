import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

("use strict");
var unsignedStudentList = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let unsignedTerm = $("#unsigned_term").val() != "" ? $("#unsigned_term").val() : "";
        let unsignedStatuses = $("#unsigned_statuses").val() != "" ? $("#unsigned_statuses").val() : "";
        let unsigned_course_id = $("#unsigned_course_id").val() != "" ? $("#unsigned_course_id").val() : "0";

        let tableContent = new Tabulator("#unsignedStudentList", {
            ajaxURL: route("assign.unsignned.list"),
            ajaxParams: { unsignedTerm: unsignedTerm, unsignedStatuses: unsignedStatuses, unsigned_course_id : unsigned_course_id },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 50,
            paginationSizeSelector: [true, 10, 20, 50, 100, 200, 300, 500],
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
                    title: "ID",
                    field: "s_registration_no",
                    width: 180
                },
                {
                    title: "Course",
                    field: "c_name",
                    headerHozAlign: "left",
                },
                {
                    title: "Evening/Weekend",
                    field: "std_ev_wk",
                    headerHozAlign: "left",
                    headerSort: false,
                    formatter(cell, formatterParams){
                        var html = '';
                        if(cell.getData().std_ev_wk == 'Yes'){
                            html += '<span class="text-primary flex justify-start items-center"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="sunset" class="lucide lucide-sunset w-6 h-6"><path d="M12 10V2"></path><path d="m4.93 10.93 1.41 1.41"></path><path d="M2 18h2"></path><path d="M20 18h2"></path><path d="m19.07 10.93-1.41 1.41"></path><path d="M22 22H2"></path><path d="m16 6-4 4-4-4"></path><path d="M16 18a4 4 0 0 0-8 0"></path></svg></span>';
                        }else{
                            html += '<span class="text-amber-600 flex justify-start items-center"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="sun" class="lucide lucide-sun w-6 h-6"><circle cx="12" cy="12" r="4"></circle><path d="M12 2v2"></path><path d="M12 20v2"></path><path d="m4.93 4.93 1.41 1.41"></path><path d="m17.66 17.66 1.41 1.41"></path><path d="M2 12h2"></path><path d="M20 12h2"></path><path d="m6.34 17.66-1.41 1.41"></path><path d="m19.07 4.93-1.41 1.41"></path></svg></span>';
                        }
                        return html;
                    }
                },
                {
                    title: "Group",
                    field: "group",
                    headerHozAlign: "left",
                    headerSort: false,
                },
                {
                    title: "Group Evening/Weekend",
                    field: "group_ev_wk",
                    headerHozAlign: "left",
                    headerSort: false,
                    formatter(cell, formatterParams){
                        var html = '';
                        if(cell.getData().group_ev_wk == 'Yes'){
                            html += '<span class="text-primary flex justify-start items-center"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="sunset" class="lucide lucide-sunset w-6 h-6"><path d="M12 10V2"></path><path d="m4.93 10.93 1.41 1.41"></path><path d="M2 18h2"></path><path d="M20 18h2"></path><path d="m19.07 10.93-1.41 1.41"></path><path d="M22 22H2"></path><path d="m16 6-4 4-4-4"></path><path d="M16 18a4 4 0 0 0-8 0"></path></svg></span>';
                        }else if(cell.getData().group_ev_wk == 'No'){
                            html += '<span class="text-amber-600 flex justify-start items-center"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="sun" class="lucide lucide-sun w-6 h-6"><circle cx="12" cy="12" r="4"></circle><path d="M12 2v2"></path><path d="M12 20v2"></path><path d="m4.93 4.93 1.41 1.41"></path><path d="m17.66 17.66 1.41 1.41"></path><path d="M2 12h2"></path><path d="M20 12h2"></path><path d="m6.34 17.66-1.41 1.41"></path><path d="m19.07 4.93-1.41 1.41"></path></svg></span>';
                        }
                        return html;
                    }
                },
                {
                    title: "Status",
                    field: "sts_name",
                    headerHozAlign: "left",
                    width: 180,
                    formatter(cell, formatterParams){
                        return '<div class="font-medium">'+cell.getData().sts_name+'</div><input type="hidden" class="student_ids" name="student_ids[]" value="'+cell.getData().s_id+'">';
                    }
                },
            ],
            ajaxResponse:function(url, params, response){
                var total_rows = (response.total_rows && response.total_rows > 0 ? response.total_rows : 0);
                if(total_rows > 0){
                    $('#unsignedResultCount').attr('data-total', total_rows).html(total_rows+' Rows');
                }else{
                    $('#unsignedResultCount').attr('data-total', '0').html('');
                }

                return response;
            },
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
            rowSelectionChanged:function(data, rows){
                var ids = [];
                var totalRows = $('#unsignedResultCount').attr('data-total') * 1;
                if(rows.length > 0){
                    $('#moveToProtentialList').fadeIn();
                    $('#unsignedResultCount').html(rows.length+' out of '+totalRows+' Rows');
                }else{
                    $('#moveToProtentialList').fadeOut();
                    $('#unsignedResultCount').html(totalRows+' Rows');
                }
            },
            selectableCheck:function(row){
                return row.getData().s_id > 0; 
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
        init: function () {
            _tableGen();
        },
    };
})();

(function(){
    let tomOptions = {
        plugins: {
            dropdown_input: {}
        },
        placeholder: 'Search Here...',
        persist: false,
        create: true,
        allowEmptyOption: true,
        onDelete: function (values) {
            return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };

    let multiTomOpt = {
        ...tomOptions,
        plugins: {
            ...tomOptions.plugins,
            remove_button: {
                title: "Remove this item",
            },
        }
    };

    var unsignedTerm = new TomSelect('#unsigned_term', tomOptions);
    var unsignedStatuses = new TomSelect('#unsigned_statuses', multiTomOpt);

    $('#unsignnedStudentList-go').on('click', function(){
        if($('#unsigned_term').val() != '' && $('#unsigned_statuses').val() != ''){
            $('.unsignedStudentListWrap').fadeIn('fast', function(){
                unsignedStudentList.init();
            });
        }
    });
    $('#unsignnedStudentList-reset').on('click', function(){
        unsignedTerm.clear(true);
        unsignedStatuses.clear(true);
        unsignedStatuses.addItem(18, true);
        unsignedStatuses.addItem(23, true);
        unsignedStatuses.addItem(24, true);
        unsignedStatuses.addItem(28, true);
        unsignedStatuses.addItem(29, true);
        $('.unsignedStudentListWrap').fadeOut('fast', function(){
            unsignedStudentList.init();
        })
        $('#unsignedResultCount').attr('data-total', '0').html('');
    });

    $('#moveToProtentialList').on('click', function(e){
        e.preventDefault();
        var $theBtn = $(this);
        var ids = [];
        let $assignToCourseId = $('#assignToCourseId');
        let assignToCourseId = $assignToCourseId.val();

        $('#unsignedStudentList').find('.tabulator-row.tabulator-selected').each(function(){
            var $row = $(this);
            ids.push($row.find('.student_ids').val());
        });

        var existingStudents = [];
        if($('.assignStudentsList.existingStudentList li').length > 0){
            $('.assignStudentsList.existingStudentList li').each(function(){
                existingStudents.push($(this).attr('data-studentid'));
            })
        }

        if(ids.length > 0){
            $('.assignStudentsList.potentialStudentList').addClass('loading').html('');
            axios({
                method: "post",
                url: route('assign.generage.potential.list.from.unsigned.list'),
                data: {student_ids : ids, existingStudents : existingStudents, assignToCourseId : assignToCourseId},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                $('html, body').animate({ scrollTop: $("#studentListArea").offset().top - 50 }, 1000);
                $('#moveToProtentialList').fadeOut();
                unsignedStudentList.init();
                $('.assignStudentsList.potentialStudentList').removeClass('loading');
                if (response.status == 200) {
                    $('.assignStudentsList.potentialStudentList').html(response.data.res.htm);
                    if(response.data.res.count > 0){
                        $('.potentialCount').html(' ('+response.data.res.count+')');
                    }else{
                        $('.potentialCount').html('');
                    }

                    createIcons({
                        icons,
                        "stroke-width": 1.5,
                        nameAttr: "data-lucide",
                    });
                }
            }).catch(error => {
                $('html, body').animate({ scrollTop: $("#studentListArea").offset().top - 50 }, 1000);
                $('#moveToProtentialList').fadeOut();
                unsignedStudentList.init();
                $('.assignStudentsList.potentialStudentList').removeClass('loading').html('');
                $('.potentialCount').html('');
                if (error.response) {
                    console.log('error');
                }
            });
        }else{
            $('.assignStudentsList.potentialStudentList').removeClass('loading').html('');
            $('.potentialCount').html('');
            $('#moveToProtentialList').fadeOut();
            unsignedStudentList.init();
        }
    })
})();