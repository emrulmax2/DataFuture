import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

("use strict");
var agentComissionListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let semester_id = $("#agentComissionListTable").attr('data-semester') != "" ? $("#agentComissionListTable").attr('data-semester') : "";
        let agent_id = $("#agentComissionListTable").attr('data-agent') != "" ? $("#agentComissionListTable").attr('data-agent') : "";
        let code = $("#agentComissionListTable").attr('data-code') != "" ? $("#agentComissionListTable").attr('data-code') : "";

        let tableContent = new Tabulator("#agentComissionListTable", {
            ajaxURL: route("agent.management.comission.list"),
            ajaxParams: { semester_id: semester_id, agent_id: agent_id, code: code },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: true,
            paginationSizeSelector: [true, 50, 100, 150, 200, 300, 400, 500],
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records found",
            selectable:true,
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
                    title: "#ID",
                    field: "id",
                    width: "80",
                    formatter(cell, formatterParams){
                        var html = cell.getData().id;
                            html += '<input type="hidden" name="ids" class="ids" value="'+cell.getData().id+'"/>';

                        return html;
                    }
                },
                {
                    title: "REG. No",
                    field: "registration_no",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        var html = '';
                        html += '<div>';
                            html += '<div class="font-medium whitespace-nowrap">'+cell.getData().registration_no+'</div>';
                            html += '<div class="text-slate-500 text-xs whitespace-nowrap">'+cell.getData().application_no+'</div>';
                        html += '</div>';

                        return html;
                    }
                },
                {
                    title: "Student",
                    field: "full_name",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        var html = '';
                        html += '<div>';
                            html += '<div class="font-medium whitespace-nowrap">'+cell.getData().full_name+'</div>';
                            html += '<div class="text-slate-500 text-xs whitespace-nowrap">'+cell.getData().date_of_birth+'</div>';
                        html += '</div>';

                        return html;
                    }
                },
                {
                    title: "SSN",
                    field: "ssn_no",
                    headerHozAlign: "left",
                },
                {
                    title: "Course / Status",
                    field: "status",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        var html = '';
                        html += '<div>';
                            html += '<div class="font-medium whitespace-nowrap">'+cell.getData().status+'</div>';
                            html += '<div class="text-slate-500 text-xs whitespace-normal">'+cell.getData().course+'</div>';
                        html += '</div>';

                        return html;
                    }
                },
                {
                    title: "Course Fees",
                    field: "course_fees",
                    headerHozAlign: "left",
                },
                {
                    title: "Claimed",
                    field: "claimed_amount",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        var html = cell.getData().claimed_amount;
                        if(cell.getData().claimed_count > 0){
                            html += ' ('+cell.getData().claimed_count+')';
                        }
                        return html;
                    }
                },
                {
                    title: "Received",
                    field: "receipt_amount",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        var html = cell.getData().receipt_amount;
                        if(cell.getData().receipt_count > 0){
                            html += ' ('+cell.getData().receipt_count+')';
                        }
                        return html;
                    }
                },
            ],
            ajaxResponse: function (url, params, response) {
                var total_rows = response.all_rows && response.all_rows > 0 ? response.all_rows : 0;

                if (total_rows > 0) {
                    $('#noOfStdCount').attr('data-total', total_rows).html(total_rows + ' Students');
                } else {
                    $('#noOfStdCount').attr('data-total', '0').html('0');
                }

                return response;
            },
            renderComplete() {
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
            },
            rowSelectionChanged:function(data, rows){
                if(rows.length > 0){
                    $('#generateComissionBtn').fadeIn();
                }else{
                    $('#generateComissionBtn').fadeOut();
                }
            },
            selectableCheck:function(row){
                return row.getData().id > 0; //allow selection of rows where the age is greater than 18
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
    agentComissionListTable.init();

    const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));
    const comissionGenerateModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#comissionGenerateModal"));

    const comissionGenerateModalEl = document.getElementById('comissionGenerateModal')
    comissionGenerateModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#comissionGenerateModal .acc__input-error').html('');
        $('#comissionGenerateModal #comissionsPaymentTable tbody').html('');
        $('#comissionGenerateModal [name="agent_comission_rule_id"]').val('0');
    });

    $('#generateComissionBtn').on('click', function(e){
        e.preventDefault();
        var $theBtn = $(this);
        var agentcomissionruleid = $theBtn.attr('data-comissionruleid');
        $theBtn.find('svg.theLoader').fadeIn();
        $theBtn.attr('disabled', 'disabled');

        var studentids = [];
        $('#agentComissionListTable').find('.tabulator-row.tabulator-selected').each(function(){
            var $row = $(this);
            studentids.push($row.find('.ids').val());
        });

        if(studentids.length > 0){
            axios({
                method: "post",
                url: route("agent.management.get.payable.comissions"),
                data: { agentcomissionruleid : agentcomissionruleid, studentids : studentids },
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                //console.log(response.data);
                $theBtn.find('svg.theLoader').fadeOut();
                $theBtn.removeAttr('disabled');
                if (response.status == 200) {
                    window.location.href = response.data.url;
                }
            }).catch((error) => {
                $theBtn.find('svg.theLoader').fadeOut();
                $theBtn.removeAttr('disabled');
                if (error.response) {
                    if (error.response.status == 422) {
                        warningModal.show();
                        document.getElementById("warningModal").addEventListener("shown.tw.modal", function (event) {
                            $("#warningModal .warningModalTitle").html("Error!");
                            $("#warningModal .warningModalDesc").html(error.response.data.msg);
                        });

                        setTimeout(() => {
                            warningModal.hide();
                        }, 2000);
                    } else {
                        console.log('error');
                    }
                }
            });
        }else{
            $theBtn.find('svg.theLoader').fadeOut();
            $theBtn.removeAttr('disabled');
        }
    })
})()