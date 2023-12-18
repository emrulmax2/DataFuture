import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

import dayjs from "dayjs";
import Litepicker from "litepicker";

("use strict");
var manageHolidayListTable = (function () {
    var _tableGen = function (department) {
        let tableID = '#liveAttendanceListTable_'+department;
        let liveAttendanceDate = $('#liveAttendanceDate').val();

        let tableContent = new Tabulator(tableID, {
            ajaxURL: route('hr.portal.live.attedance.list'),
            ajaxParams: { department : department, liveAttendanceDate : liveAttendanceDate},
            ajaxFiltering: true,
            ajaxSorting: false,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 5,
            paginationSizeSelector: [5, 10, 20, 30, 40, true],
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records found",
            columns: [
                {
                    title: "Name",
                    field: "name",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) { 
                        var html = '<a href="'+cell.getData().url+'" class="block">';
                                html += '<div class="w-10 h-10 intro-x image-fit mr-5 inline-block">';
                                    html += '<img alt="'+cell.getData().name+'" class="rounded-full shadow" src="'+cell.getData().photo_url+'">';
                                html += '</div>';
                                html += '<div class="inline-block relative" style="top: -5px;">';
                                    html += '<div class="font-medium whitespace-nowrap">'+cell.getData().name+'</div>';
                                    html += '<div class="text-slate-500 text-xs whitespace-nowrap">'+(cell.getData().designation != '' ? cell.getData().designation : 'Unknown')+'</div>';
                                html += '</div>';
                            html += '</a>';
                        return html;
                    }
                },
                {
                    title: "Schedule",
                    field: "schedule",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) { 
                        var html = '';
                        html += cell.getData().schedule+'<br/>';
                        if(cell.getData().day_label != ''){
                            html += '<span class="btn inline-flex '+cell.getData().day_class+' w-auto py-1 px-2 border-0 text-white rounded-0">';
                                html += cell.getData().day_label;
                            html += '</span>';
                        }
                        html += '&nbsp;'+cell.getData().day_suffix;
                        return html;
                    }
                },
                {
                    title: "Extension",
                    field: "ext",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) { 
                        var html = '';
                        if(cell.getData().ext != ''){
                            html += '<span class="btn inline-flex btn-success w-auto py-1 px-2 border-0 text-white rounded-0">';
                                html += cell.getData().ext;
                            html += '</span>';
                        }
                        return html;
                    }
                },
                {
                    title: "Clock In",
                    field: "day_label",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) { 
                        return '&nbsp;';
                    }
                },
                {
                    title: "Break",
                    field: "day_label",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) { 
                        return '&nbsp;';
                    }
                },
                {
                    title: "Clock Out",
                    field: "day_label",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) { 
                        return '&nbsp;';
                    }
                },
                {
                    title: "Location",
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
    let dateOption = {
        autoApply: true,
        singleMode: true,
        numberOfColumns: 1,
        numberOfMonths: 1,
        showWeekNumbers: false,
        inlineMode: false,
        format: "DD-MM-YYYY",
        dropdowns: {
            minYear: 1900,
            maxYear: 2050,
            months: true,
            years: true,
        },
    };
    const liveAttendanceDate = new Litepicker({
        element: document.getElementById('liveAttendanceDate'),
        ...dateOption
    });

    liveAttendanceDate.on('selected', (date) => {
        if($('.liveAttendanceListTable').length > 0){
            $('.liveAttendanceListTable').each(function(){
                var $thisTable = $(this);
                var department = $thisTable.attr('data-department');
    
                manageHolidayListTable.init(department);
            });
        }
    });


    if($('.liveAttendanceListTable').length > 0){
        $('.liveAttendanceListTable').each(function(){
            var $thisTable = $(this);
            var department = $thisTable.attr('data-department');

            manageHolidayListTable.init(department);
        });
    }
})();