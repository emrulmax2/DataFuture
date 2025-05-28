import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

("use strict");
var unknownEntryApplicantList = (function () {
    var _tableGen = function (applicant_ids) {
        let tableContent = new Tabulator("#unknownEntryApplicantList", {
            ajaxURL: route("reports.applicant.analysis.unknown.entry.list"),
            ajaxParams: { applicant_ids : applicant_ids },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 50,
            paginationSizeSelector: [true, 50,100,200,500],
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records found",
            columns: [
                {
                    title: "Ref. No",
                    field: "application_no",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) {  
                        var html = '<div class="block">';
                                html += '<div class="w-10 h-10 intro-x image-fit mr-4 inline-block">';
                                    html += '<img alt="'+cell.getData().first_name+'" class="rounded-full shadow" src="'+cell.getData().photo_url+'">';
                                html += '</div>';
                                html += '<div class="inline-block relative" style="top: -5px;">';
                                    html += '<div class="font-medium whitespace-nowrap uppercase">'+cell.getData().application_no+'</div>';
                                    html += '<div class="text-slate-500 text-xs whitespace-nowrap">'+cell.getData().full_name+'</div>';
                                html += '</div>';
                            html += '</div>';
                        return html;
                    }
                },
                {
                    title: "First Name",
                    field: "first_name",
                    headerHozAlign: "left",
                },
                {
                    title: "Last Name",
                    field: "last_name",
                    headerHozAlign: "left",
                },
                {
                    title: "DOB",
                    field: "date_of_birth",
                    headerHozAlign: "left",
                },
                {
                    title: "Gender",
                    field: "gender",
                    headerHozAlign: "left",
                },
                {
                    title: "Semester",
                    field: "semester",
                    headerHozAlign: "left",
                },
                {
                    title: "Course",
                    field: "course",
                    headerHozAlign: "left",
                },
                {
                    title: "Status",
                    field: "status_id",
                    headerHozAlign: "left",
                    width: "150"
                }
            ],
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
            rowClick:function(e, row){
                window.open(row.getData().url, '_blank');
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
        init: function (applicant_ids) {
            _tableGen(applicant_ids);
        },
    };
})();

(function(){
    let apAnlsTomOptions = {
        plugins: {
            dropdown_input: {}
        },
        placeholder: 'Search Here...',
        //persist: false,
        create: false,
        allowEmptyOption: true,
        onDelete: function (values) {
            return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };

    let apAnlsTomOptionsMul = {
        ...apAnlsTomOptions,
        plugins: {
            ...apAnlsTomOptions.plugins,
            remove_button: {
                title: "Remove this item",
            },
        }
    };

    var ap_an_semester_id = new TomSelect('#ap_an_semester_id', apAnlsTomOptions);
    $('#ap_an_semester_id').on('change', function(){
        $('#printPdfAplicntAnalysisBtn').attr('href', 'javascript:void(0);').fadeOut();
        $('#applicantAnalysisReptWrap').fadeOut().html('');
    });

    $('#applicantAnalysisReportForm').on('submit', function(e){
        e.preventDefault();
        let $form = $(this);
        const form = document.getElementById('applicantAnalysisReportForm');
        let ap_an_semester_id = $form.find('#ap_an_semester_id').val();
        
        if(ap_an_semester_id > 0){
            $form.find('.error-ap_an_semester_id').html('')
            document.querySelector('#AplicntAnalysisReptBtn').setAttribute('disabled', 'disabled');
            document.querySelector("#AplicntAnalysisReptBtn svg").style.cssText ="display: inline-block;";
            $('#printPdfAplicntAnalysisBtn').attr('href', 'javascript:void(0);').fadeOut();
            $('#applicantAnalysisReptWrap').fadeOut().html('');

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('reports.applicant.analysis.generate.report'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#AplicntAnalysisReptBtn').removeAttribute('disabled');
                document.querySelector("#AplicntAnalysisReptBtn svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    //console.log(response.data);
                    //return false;
                    let pdf_url = route('reports.applicant.analysis.print.report', ap_an_semester_id);
                    $('#applicantAnalysisReptWrap').fadeIn().html(response.data.htm);
                    $('#printPdfAplicntAnalysisBtn').attr('href', pdf_url).fadeIn();

                    setTimeout(() => {
                        createIcons({
                            icons,
                            "stroke-width": 1.5,
                            nameAttr: "data-lucide",
                        });
                    }, 10);
                }
            }).catch(error => {
                document.querySelector('#AplicntAnalysisReptBtn').removeAttribute('disabled');
                document.querySelector("#AplicntAnalysisReptBtn svg").style.cssText = "display: none;";
                $('#printPdfAplicntAnalysisBtn').attr('href', 'javascript:void(0);').fadeOut();
                if (error.response) {
                    console.log('error');
                }
            });
        }else{
            $form.find('.error-ap_an_semester_id').html('Semesters can not be empty.');
            $('#applicantAnalysisReptWrap').fadeOut().html('');
            $('#printPdfAplicntAnalysisBtn').attr('href', 'javascript:void(0);').fadeOut();
        }
    });


    const viewUnknownEntryModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#viewUnknownEntryModal"));
    const viewUnknownEntryModalEl = document.getElementById('viewUnknownEntryModal')
    viewUnknownEntryModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#unknownEntryApplicantList').html('').removeClass('tabulator').removeAttr('tabulator-layout').removeAttr('role');
    });

    $(document).on('click', '.viewUnknownEntryBtn', function(e){
        e.preventDefault();
        let $theBtn = $(this);
        let applicant_ids = $theBtn.attr('data-ids');
        unknownEntryApplicantList.init(applicant_ids);
    })

})()