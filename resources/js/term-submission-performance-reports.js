import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

(function(){
    let subPerfTomOptions = {
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

    let subPerfTomOptionsMul = {
        ...subPerfTomOptions,
        plugins: {
            ...subPerfTomOptions.plugins,
            remove_button: {
                title: "Remove this item",
            },
        }
    };

    var sub_perf_term_id = new TomSelect('#sub_perf_term_id', subPerfTomOptions);
    $('#sub_perf_term_id').on('change', function(){
        $('#printSubmissionPerformanceReportBtn, #exportSubmissionPerformanceReportBtn').attr('href', 'javascript:void(0);').fadeOut();
        $('#submissionPerformanceReportWrap').fadeOut().html('');
    });

    $('#submissionPerformanceReportForm').on('submit', function(e){
        e.preventDefault();
        let $form = $(this);
        const form = document.getElementById('submissionPerformanceReportForm');
        let sub_perf_term_id = $form.find('#sub_perf_term_id').val();
        
        if(sub_perf_term_id.length > 0){
            $form.find('.error-sub_perf_term_id').html('')
            document.querySelector('#submissionPerformanceReportBtn').setAttribute('disabled', 'disabled');
            document.querySelector("#submissionPerformanceReportBtn svg").style.cssText ="display: inline-block;";
            $('#printSubmissionPerformanceReportBtn, #exportSubmissionPerformanceReportBtn').attr('href', 'javascript:void(0);').fadeOut();
            $('#submissionPerformanceReportWrap').fadeOut().html('');

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('reports.term.performance.submission.generate.report'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#submissionPerformanceReportBtn').removeAttribute('disabled');
                document.querySelector("#submissionPerformanceReportBtn svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    //console.log(response.data);
                    //return false;
                    let pdf_url = route('reports.term.performance.submission.print.report', sub_perf_term_id);
                    let excel_url = route('reportsterm.performance.submission.export.report', sub_perf_term_id);
                    $('#submissionPerformanceReportWrap').fadeIn().html(response.data.htm);
                    $('#printSubmissionPerformanceReportBtn').attr('href', pdf_url).fadeIn();
                    $('#exportSubmissionPerformanceReportBtn').attr('href', excel_url).fadeIn();

                    setTimeout(() => {
                        createIcons({
                            icons,
                            "stroke-width": 1.5,
                            nameAttr: "data-lucide",
                        });
                    }, 10);
                }
            }).catch(error => {
                document.querySelector('#submissionPerformanceReportBtn').removeAttribute('disabled');
                document.querySelector("#submissionPerformanceReportBtn svg").style.cssText = "display: none;";
                $('#printSubmissionPerformanceReportBtn, #exportSubmissionPerformanceReportBtn').attr('href', 'javascript:void(0);').fadeOut();
                if (error.response) {
                    console.log('error');
                }
            });
        }else{
            $form.find('.error-sub_perf_term_id').html('Semesters can not be empty.');
            $('#submissionPerformanceReportWrap').fadeOut().html('');
            $('#printSubmissionPerformanceReportBtn, #exportSubmissionPerformanceReportBtn').attr('href', 'javascript:void(0);').fadeOut();
        }
    });
})();