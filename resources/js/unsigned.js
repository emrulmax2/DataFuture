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

        let tableContent = new Tabulator("#unsignedStudentList", {
            ajaxURL: route("assign.unsignned.list"),
            ajaxParams: { unsignedTerm: unsignedTerm, unsignedStatuses: unsignedStatuses },
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
                            html += '<span class="text-primary flex justify-start items-center"><i data-lucide="sunset" class="w-6 h-6"></i></span>';
                        }else{
                            html += '<span class="text-amber-600 flex justify-start items-center"><i data-lucide="sun" class="w-6 h-6"></i></span>';
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
                            html += '<span class="text-primary flex justify-start items-center"><i data-lucide="sunset" class="w-6 h-6"></i></span>';
                        }else if(cell.getData().group_ev_wk == 'No'){
                            html += '<span class="text-amber-600 flex justify-start items-center"><i data-lucide="sun" class="w-6 h-6"></i></span>';
                        }
                        return html;
                    }
                },
                {
                    title: "Status",
                    field: "sts_name",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        return '<div class="font-medium">'+cell.getData().sts_name+'</div>';
                    }
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
    });
})();