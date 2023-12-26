import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

("use strict");

var birthdayListSearchTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let birthmonth = $("#dob-BR").val() != "" ? $("#dob-BR").val() : "";
        let worktype = $("#employee_work_type_id-birtdaylist").val() != "" ? $("#employee_work_type_id-birtdaylist").val() : "";
        let department = $("#department_id-birtdaylist").val() != "" ? $("#department_id-birtdaylist").val() : "";
        let status = $("#status_id-birtdaylist").val() != "" ? $("#status_id-birtdaylist").val() : "";
        let tableContent = new Tabulator("#birthdayListSearchTable", {
            ajaxURL: route("hr.portal.reports.birthdaylist.list"),
            ajaxParams: { birthmonth: birthmonth, worktype:worktype, department:department, status:status },
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
            
            columns:[
                {
                    title:"", field:"month",
                    headerSort: false,
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) {  
                        return '<div class="font-bold whitespace-normal text-sm">'+cell.getData().month+'</div>';
                    }
                },
            ],
            rowFormatter:function(row){
                //create and style holder elements
                var holderEl = document.createElement("div");
                var tableEl  = document.createElement("div");
        
                holderEl.appendChild(tableEl);
        
                row.getElement().appendChild(holderEl);

                tableEl.setAttribute("id","tableEl");
        
                var subTable = new Tabulator(tableEl, {
                    layout:"fitColumns",
                    data:row.getData().dataArray,
                    columns:[
                        {title:"Name", field:"name", headerHozAlign: "left"},
                        {title:"Works Number", field:"works_no", headerHozAlign: "left"},
                        {title:"Gender", field:"gender", headerHozAlign: "left"},
                        {title:"Date of Birth", field:"date_of_birth", headerHozAlign: "left"},
                        {title:"Age", field:"age", headerHozAlign: "left"},
                    ],
                    renderComplete() {
                        createIcons({
                            icons,
                            "stroke-width": 1.5,
                            nameAttr: "data-lucide",
                        });
                    },
                })
            },
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
            return confirm( values.length > 1 ? "Are you sure you want to remove this " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };

    $("#tabulator-html-filter-go-BR").on("click", function (event) {
        birthdayListSearchTable.init();         
        document.getElementById("bdayListbySearchExcelBtn").style.display="block";
        document.getElementById("bdayListbySearchPdfBtn").style.display="block";
        $("div .birthdayListAllData").hide();
        document.getElementById("allBdayListExcelBtn").style.display="none";
        document.getElementById("allBdayListPdfBtn").style.display="none";
        // Filter function
        function filterHTMLFormBR() {
            birthdayListSearchTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-BR")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLFormBR();
                }
            }
        );

        // On click go button
        $("#tabulator-html-filter-go-BR").on("click", function (event) {
            filterHTMLFormBR();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-BR").on("click", function (event) {
            $("#employee_work_type_id-birtdaylist").val('');
            $("#department_id-birtdaylist").val('');
            $("#dob-BR").val('');
            $("#status_id-birtdaylist").val('1');
            document.getElementById("birthdayListSearchTable").style.display = "none";
            $("div .birthdayListSearchDiv").hide();
            $("div .birthdayListAllData").show();
            document.getElementById("allBdayListExcelBtn").style.display="block";
            document.getElementById("allBdayListPdfBtn").style.display="block";
            document.getElementById("bdayListbySearchExcelBtn").style.display="none";
            document.getElementById("bdayListbySearchPdfBtn").style.display="none";
        });
    });

    $("#bdayListbySearchExcelBtn").on("click", function (e) {      
        e.preventDefault();
        let birthmonth = $("#dob-BR").val() != "" ? $("#dob-BR").val() : "";
        let worktype = $("#employee_work_type_id-birtdaylist").val() != "" ? $("#employee_work_type_id-birtdaylist").val() : "";
        let department = $("#department_id-birtdaylist").val() != "" ? $("#department_id-birtdaylist").val() : "";
        let status = $("#status_id-birtdaylist").val() != "" ? $("#status_id-birtdaylist").val() : "";
        
        axios({
            method: "get",
            url: route("hr.portal.reports.birthdaylistbysearch.excel"),
            params: {
                birthmonth: birthmonth, worktype:worktype, department:department, status:status
            },
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            responseType: 'blob',
        })
        .then((response) => {
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'Birthday_List.xlsx'); 
                document.body.appendChild(link);
                link.click();
                
        })
        .catch((error) => {
                console.log(error);
        });
    });

    $("#bdayListbySearchPdfBtn").on("click", function (e) {      
        e.preventDefault();
        let birthmonth = $("#dob-BR").val() != "" ? $("#dob-BR").val() : "";
        let worktype = $("#employee_work_type_id-birtdaylist").val() != "" ? $("#employee_work_type_id-birtdaylist").val() : "";
        let department = $("#department_id-birtdaylist").val() != "" ? $("#department_id-birtdaylist").val() : "";
        let status = $("#status_id-birtdaylist").val() != "" ? $("#status_id-birtdaylist").val() : "";
        
        axios({
            method: "get",
            url: route("hr.portal.reports.birthdaylistbysearch.pdf"),
            params: {
                birthmonth: birthmonth, worktype:worktype, department:department, status:status
            },
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            responseType: 'blob',
        })
        .then((response) => {
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'Birthday_List.pdf');
                document.body.appendChild(link);
                link.click();
                
        })
        .catch((error) => {
                console.log(error);
        });
    });
})();