import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";


(function(){
    let tomOptions = {
        plugins: {
            dropdown_input: {}
        },
        placeholder: 'Search Here...',
        //persist: false,
        create: false,
        maxOptions: null,
        allowEmptyOption: true,
        onDelete: function (values) {
            return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };

    let semister_id = new TomSelect('#semister_id', tomOptions);

    $('#tabulator-html-filter-reset').on('click', function(e){
        e.preventDefault();
        semister_id.clear(true);
        $('.agentRefListWrap').fadeOut().html('')
    })
    $('#tabulator-html-filter-go').on('click', function(e){
        e.preventDefault();
        var $theBtn = $(this);
        var semester_id = $('#semister_id').val();

        $theBtn.attr('disabled', 'disabled');
        $theBtn.find('svg.theLoader').fadeIn();

        if(semester_id > 0){
            axios({
                method: "post",
                url: route('agent.management.list'),
                data: {semester_id : semester_id},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                $theBtn.removeAttr('disabled');
                $theBtn.find('svg.theLoader').fadeOut();
                
                if (response.status == 200) {
                    $('.agentRefListWrap').fadeIn().html(response.data.html)
                }
            }).catch(error => {
                $theBtn.removeAttr('disabled');
                $theBtn.find('svg.theLoader').fadeOut();
                if (error.response) {
                    console.log('error');
                }
            });
        }else{
            $theBtn.removeAttr('disabled');
            $theBtn.find('svg.theLoader').fadeOut();
            $('.agentRefListWrap').fadeOut().html('')
        }
    });

    $('.agentRefListWrap').on('click', '#referralCountTable tr.result_row', function(e){
        e.preventDefault();
        var $theTr = $(this);
        var semester_id = $theTr.attr('data-semester');

        $('.agentRefListWrap').addClass('loading');
        axios({
            method: "post",
            url: route('agent.management.list.details'),
            data: {semester_id : semester_id},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            $('.agentRefListWrap').removeClass('loading');
            
            if (response.status == 200) {
                $('.agentRefListWrap').html(response.data.html)
            }
        }).catch(error => {
            $('.agentRefListWrap').removeClass('loading');
            if (error.response) {
                console.log('error');
            }
        });
    })
})()