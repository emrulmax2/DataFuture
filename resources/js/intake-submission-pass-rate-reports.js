import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

(function(){
    let subPassTomOptions = {
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

    let subPassTomOptionsMul = {
        ...subPassTomOptions,
        plugins: {
            ...subPassTomOptions.plugins,
            remove_button: {
                title: "Remove this item",
            },
        }
    };

    var sub_pass_semester_id = new TomSelect('#sub_pass_semester_id', subPassTomOptionsMul);
    $('#sub_pass_semester_id').on('change', function(){
        $('#printPdfSubPassRateBtn').attr('href', 'javascript:void(0);').fadeOut();
        $('#submissionPassRateWrap').fadeOut().html('');
    });

    $('#submissionPassRateSearchForm').on('submit', function(e){
        e.preventDefault();
        let $form = $(this);
        const form = document.getElementById('attendanceRateSearchForm');
        let sub_pass_semester_id = $form.find('#sub_pass_semester_id').val();
        
        if(sub_pass_semester_id.length > 0){
            $form.find('.error-sub_pass_semester_id').html('')
            document.querySelector('#SubPassRateBtn').setAttribute('disabled', 'disabled');
            document.querySelector("#SubPassRateBtn svg").style.cssText ="display: inline-block;";
            $('#printPdfSubPassRateBtn').attr('href', 'javascript:void(0);').fadeOut();
            $('#submissionPassRateWrap').fadeOut().html('');

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('reports.intake.performance.get.submission.pass.rate.report'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#SubPassRateBtn').removeAttribute('disabled');
                document.querySelector("#SubPassRateBtn svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    let pdf_url = route('reports.intake.performance.print.attendance.rate', sub_pass_semester_id.join('_'));
                    $('#submissionPassRateWrap').fadeIn().html(response.data.htm);
                    $('#printPdfSubPassRateBtn').attr('href', pdf_url).fadeIn();

                    setTimeout(() => {
                        createIcons({
                            icons,
                            "stroke-width": 1.5,
                            nameAttr: "data-lucide",
                        });
                    }, 10);
                }
            }).catch(error => {
                document.querySelector('#SubPassRateBtn').removeAttribute('disabled');
                document.querySelector("#SubPassRateBtn svg").style.cssText = "display: none;";
                $('#printPdfSubPassRateBtn').attr('href', 'javascript:void(0);').fadeOut();
                if (error.response) {
                    console.log('error');
                }
            });
        }else{
            $form.find('.error-sub_pass_semester_id').html('Semesters can not be empty.');
            $('#submissionPassRateWrap').fadeOut().html('');
            $('#printPdfSubPassRateBtn').attr('href', 'javascript:void(0);').fadeOut();
        }
    });

})();