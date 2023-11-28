import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

("use strict");
var manageHolidayListTable = (function () {
    var _tableGen = function (department) {
        let tableID = '#liveAttendanceListTable_'+department;

        let tableContent = new Tabulator(tableID, {
            ajaxURL: route('hr.portal.live.attedance.list'),
            ajaxParams: { department : department },
            ajaxFiltering: true,
            ajaxSorting: false,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 'true',
            paginationSizeSelector: [true, 5, 10, 20, 30, 40],
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records found",
            columns: [
                {
                    title: "Name",
                    field: "name",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) { 
                        var html = '<a href="'+cell.getData().url+'" class="flex justify-start items-center">';
                                html += '<div class="w-10 h-10 intro-x image-fit mr-5">';
                                    html += '<img alt="'+cell.getData().name+'" class="rounded-full shadow" src="'+cell.getData().photo_url+'">';
                                html += '</div>';
                                html += '<div>';
                                    html += '<div class="font-medium whitespace-nowrap">'+cell.getData().name+'</div>';
                                html += '</div>';
                            html += '</a>';
                        return html;
                    }
                },
                {
                    title: "Schedule",
                    field: "schedule",
                    headerHozAlign: "left",
                },
                {
                    title: "Label",
                    field: "day_label",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) { 
                        var html = '';
                        if(cell.getData().day_label != ''){
                            html += '<span class="btn inline-flex '+cell.getData().day_class+' w-auto py-1 px-2 text-white rounded-0">';
                                html += cell.getData().day_label;
                            html += '</span>';
                        }
                        html += '&nbsp;'+cell.getData().day_suffix;
                        return html;
                    }
                },
                {
                    title: "Where",
                    field: "where",
                    headerHozAlign: "left",
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
        init: function (department) {
            _tableGen(department);
        },
    };
})();

(function(){
    if($('.liveAttendanceListTable').length > 0){
        $('.liveAttendanceListTable').each(function(){
            var $thisTable = $(this);
            var department = $thisTable.attr('data-department');

            manageHolidayListTable.init(department);
        });
    }
})();