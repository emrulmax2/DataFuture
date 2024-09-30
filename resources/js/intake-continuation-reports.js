import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

(function(){
    let dueTomOptions = {
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

    let dueTomOptionsMul = {
        ...dueTomOptions,
        plugins: {
            ...dueTomOptions.plugins,
            remove_button: {
                title: "Remove this item",
            },
        }
    };

    var cr_semester_id = new TomSelect('#cr_semester_id', dueTomOptionsMul);
    $('#cr_semester_id').on('change', function(){
        $('#printPdfContinuationRateBtn').attr('href', 'javascript:void(0);').fadeOut();
        $('#continuationRateWrap').fadeOut().html('');
    });

    $('#continuationRateSearchForm').on('submit', function(e){
        e.preventDefault();
        let $form = $(this);
        const form = document.getElementById('continuationRateSearchForm');
        let cr_semester_id = $form.find('#cr_semester_id').val();
        
        if(cr_semester_id.length > 0){
            $form.find('.error-cr_semester_id').html('')
            document.querySelector('#continuationRateBtn').setAttribute('disabled', 'disabled');
            document.querySelector("#continuationRateBtn svg").style.cssText ="display: inline-block;";
            $('#printPdfContinuationRateBtn').attr('href', 'javascript:void(0);').fadeOut();
            $('#continuationRateWrap').fadeOut().html('');

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('reports.intake.performance.get.continuation.report'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#continuationRateBtn').removeAttribute('disabled');
                document.querySelector("#continuationRateBtn svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    let pdf_url = route('reports.intake.performance.print.continuation.rate', cr_semester_id.join('_'));
                    $('#continuationRateWrap').fadeIn().html(response.data.htm);
                    $('#printPdfContinuationRateBtn').attr('href', pdf_url).fadeIn();

                    setTimeout(() => {
                        createIcons({
                            icons,
                            "stroke-width": 1.5,
                            nameAttr: "data-lucide",
                        });
                    }, 10);
                }
            }).catch(error => {
                document.querySelector('#continuationRateBtn').removeAttribute('disabled');
                document.querySelector("#continuationRateBtn svg").style.cssText = "display: none;";
                $('#printPdfContinuationRateBtn').attr('href', 'javascript:void(0);').fadeOut();
                if (error.response) {
                    console.log('error');
                }
            });
        }else{
            $form.find('.error-cr_semester_id').html('Semesters can not be empty.');
            $('#printPdfContinuationRateBtn').attr('href', 'javascript:void(0);').fadeOut();
            $('#continuationRateWrap').fadeOut().html('');
        }
    });

    
    $('#continuationRateWrap').on('click', '.semisterToggle', function(e){
        e.preventDefault();
        var semesterId = $(this).attr('data-semesterid');
        $('.courseRow_'+semesterId).fadeToggle();
    })



})()