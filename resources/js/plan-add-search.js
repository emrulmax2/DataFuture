import IMask from 'imask';
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
        create: true,

        allowEmptyOption: true,
        onDelete: function (values) {
            return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };
    var tomSelectList = []
    $('.lccTom').each(function(){
        if ($(this).attr("multiple") !== undefined) {
            tomOptions = {
                ...tomOptions,
                plugins: {
                    ...tomOptions.plugins,
                    remove_button: {
                        title: "Remove this item",
                    },
                }
            };
        }
        tomSelectList.push(new TomSelect(this, tomOptions));
    })
    
    if($('#academic-year').length > 0) {
        // On reset filter form
        $("#academic-year").on("change", function (event) {
            let tthis = $(this)
            let academicYearData = tthis.val()
            tomSelectList[1].clear();

            $('#term-declaration__box').hide();
            $('#course__box').hide();
            $('#group__box').hide();
            $(".theSubmitArea").hide();

            tomSelectList[1].clear(true); 
            tomSelectList[1].clearOptions(); 
            tomSelectList[2].clear(true); 
            tomSelectList[2].clearOptions(); 
            tomSelectList[3].clear(true);
            tomSelectList[3].clearOptions(); 

            if(academicYearData) {
                tomSelectList[0].disable()
                document.querySelector("svg#academic-loading").style.cssText = "display: inline-block;";
                axios({
                    method: "post",
                    url: route('termdeclaration.list.by.academic.year'),
                    data: {academicYear : academicYearData},
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    tomSelectList[0].enable();
                    document.querySelector("svg#academic-loading").style.cssText = "display: none;";
            
                    if(response.status == 200){   
                        $.each(response.data.res, function(index, row) {
                            tomSelectList[1].addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });
                        tomSelectList[1].refreshOptions()
                    }
                }).catch(error => {
                    tomSelectList[0].enable();
                    document.querySelector("svg#academic-loading").style.cssText = "display: none;";
                    if (error.response) {
                        if (error.response.status == 304) {
                            console.log('content not found');
                        } else {
                            console.log('error');
                        }
                    }
                });
                $('#term-declaration__box').show();
            } else {
                $('#term-declaration__box').hide();
                $('#course__box').hide();
                $('#group__box').hide();
            }
        });

        $("#term-declaration__box #termDeclarationId").on("change", function (event) {
            let tthis = $(this);
            let term_declaration_id = tthis.val();
            let academicYearData = $("#academic-year").val();

            $('#course__box').hide();
            $('#group__box').hide();
            $(".theSubmitArea").hide();

            tomSelectList[2].clear(true);
            tomSelectList[2].clearOptions();
            tomSelectList[3].clear(true); 
            tomSelectList[3].clearOptions();

            if(term_declaration_id) {
                tomSelectList[0].disable()
                tomSelectList[1].disable()
                document.querySelector("svg#termDeclarationId-loading").style.cssText = "display: inline-block;";

                axios({
                    method: "post",
                    url: route('course.list.by.academic.term'),
                    data: {academicYear : academicYearData, term_declaration_id: term_declaration_id},
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    tomSelectList[0].enable()
                    tomSelectList[1].enable()
                    document.querySelector("svg#termDeclarationId-loading").style.cssText = "display: none;";

                    if(response.status == 200){
                        tomSelectList[2].clearOptions();    

                        $.each(response.data.res, function(index, row) {
                            tomSelectList[2].addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });
                        tomSelectList[2].refreshOptions()
                    }
                }).catch(error => {
                    tomSelectList[0].enable()
                    tomSelectList[1].enable()
                    document.querySelector("svg#termDeclarationId-loading").style.cssText = "display: none;";
                    if (error.response) {
                        if (error.response.status == 304) {
                            console.log('content not found');
                        } else {
                            console.log('error');
                        }
                    }
                });
                $('#course__box').show();
            } else {
                $('#course__box').hide();
                $('#group__box').hide();
            }

        });

        $("#course__box #course_creation_id").on("change", function (event) {
            let tthis = $(this);
            let course_creation_id = tthis.val();
            let academicYearData = $("#academic-year").val();
            let term_declaration_id = $("#termDeclarationId").val();

            $('#group__box').hide();
            $(".theSubmitArea").hide();
             
            tomSelectList[3].clear(true);
            tomSelectList[3].clearOptions();

            if(term_declaration_id) {
                tomSelectList[0].disable();
                tomSelectList[1].disable();
                tomSelectList[2].disable();
                document.querySelector("svg#course_creation_id-loading").style.cssText = "display: inline-block;";

                axios({
                    method: "post",
                    url: route('group.list.by.academic.term.course'),
                    data: {academicYear : academicYearData, term_declaration_id: term_declaration_id, course_creation_id : course_creation_id},
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    tomSelectList[0].enable()
                    tomSelectList[1].enable()
                    tomSelectList[2].enable();
                    document.querySelector("svg#course_creation_id-loading").style.cssText = "display: none;";

                    if(response.status == 200){
                        tomSelectList[3].clearOptions();    

                        $.each(response.data.res, function(index, row) {
                            tomSelectList[3].addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });
                        tomSelectList[3].refreshOptions()
                    }
                }).catch(error => {
                    tomSelectList[0].enable()
                    tomSelectList[1].enable()
                    tomSelectList[2].enable();
                    document.querySelector("svg#course_creation_id-loading").style.cssText = "display: none;";
                    if (error.response) {
                        if (error.response.status == 304) {
                            console.log('content not found');
                        } else {
                            console.log('error');
                        }
                    }
                });
                $('#group__box').show();
            } else {
                $('#group__box').hide();
            }

        });
        
        $("#group__box #group_id").on("change", function (event) {
            var $group_id = $(this);
            var group_id = $group_id.val();

            if(group_id > 0){
                $(".theSubmitArea").show();
            }else{
                $(".theSubmitArea").hide();
            }
        });
    }

    if($('#classPlanAddForm').length > 0){
        $('#classPlanAddForm').on('submit', function(e){
            e.preventDefault();
            var $form = $(this);
            const form = document.getElementById('classPlanAddForm');
    
            document.querySelector('#submitModulesBtn').setAttribute('disabled', 'disabled');
            document.querySelector('#submitModulesBtn svg').style.cssText = 'display: inline-block;';
    
            var academic = $('#academic-year', $form).val();
            var term = $('#termDeclarationId', $form).val();
            var creation = $('#course_creation_id', $form).val();
            var group = $('#group_id', $form).val();
            
            if(academic > 0 && term > 0 && creation > 0 && group > 0){
                var url = route('class.plan.builder', {'academic' : academic, 'term' : term, 'creation' : creation, 'group' : group});
                window.location.href = url;
            }else{
                $('#classPlanAddForm').find('.formError').remove();
                $('#classPlanAddForm').prepend('<div class="alert formError alert-danger-soft show flex items-center mb-3" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Form validation error found. Please fill out all required fields.</div>');

                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });

                setTimeout(function(){
                    $('#classPlanAddForm').find('.formError').remove();
                }, 5000);
            }
        });
    }
})()