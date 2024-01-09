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
            paginationSize: true,
            paginationSizeSelector: false,
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records found",
            
            columns:[
                {
                    field:"month",
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
                        {title:"Name", field:"name", headerSort: false, headerHozAlign: "left"},
                        {title:"Works Number", field:"works_no", headerSort: false,headerHozAlign: "left"},
                        {title:"Gender", field:"gender", headerSort: false,headerHozAlign: "left"},
                        {title:"Date of Birth", field:"date_of_birth", headerSort: false, headerHozAlign: "left"},
                        {title:"Age", field:"age", headerSort: false, headerHozAlign: "left"},
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
            
            renderStarted:function(){
                $("#birthdayListSearchTable .tabulator-footer").hide();
                $(".tabulator-headers").css('height',0);  
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
    $("#tabulator-html-filter-go-BR").on("click", function (event) {  
        event.preventDefault();
        var dobBR = document.getElementById("dob-BR").value;
        var worktypeBR = document.getElementById("employee_work_type_id-birtdaylist").value;
        var departmentBR = document.getElementById("department_id-birtdaylist").value;
        var statusBR = document.getElementById("status_id-birtdaylist").value;

        if(dobBR !="" || worktypeBR !="" || departmentBR !="" || statusBR!=1) {
            birthdayListSearchTable.init();
            document.getElementById("bdayListbySearchExcelBtn").style.display="block";
            document.getElementById("bdayListbySearchPdfBtn").style.display="block";
        
            document.getElementById("allBdayListExcelBtn").style.display="none";
            document.getElementById("allBdayListPdfBtn").style.display="none";
        } else {
            birthdayListSearchTable.init();
            document.getElementById("bdayListbySearchExcelBtn").style.display="none";
            document.getElementById("bdayListbySearchPdfBtn").style.display="none";
           
            document.getElementById("allBdayListExcelBtn").style.display="block";
            document.getElementById("allBdayListPdfBtn").style.display="block";
        }
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
    });

    $("#tabulator-html-filter-reset-BR").on("click", function (event) {
        $("#dob-BR").val('');
        let employeeWork = document.getElementById('employee_work_type_id-birtdaylist');
        employeeWork.tomselect.setValue("");
        
        let departmentId = document.getElementById('department_id-birtdaylist');
        departmentId.tomselect.setValue("");
        
        let statusIdContact = document.getElementById('status_id-birtdaylist');
        statusIdContact.tomselect.setValue("1");
        
        birthdayListSearchTable.init();
        document.getElementById("allBdayListExcelBtn").style.display="block";
        document.getElementById("allBdayListPdfBtn").style.display="block";
        document.getElementById("bdayListbySearchExcelBtn").style.display="none";
        document.getElementById("bdayListbySearchPdfBtn").style.display="none";
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