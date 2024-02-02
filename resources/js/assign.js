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

    let tomOptionsMul = {
        ...tomOptions,
        plugins: {
            ...tomOptions.plugins,
            remove_button: {
                title: "Remove this item",
            },
        }
    };

    let potentialTermDeclaration = new TomSelect('#potentialTermDeclaration', tomOptions);
    let potentialGroups = new TomSelect('#potentialGroups', tomOptions);
    let potentialModules = new TomSelect('#potentialModules', tomOptions);

    const showAllModulesModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#showAllModulesModal"));
    const showAllModulesModalEl = document.getElementById('showAllModulesModal')
    showAllModulesModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#showAllModulesModal .modal-body').html('');
    });

    /* Filter Existing Student List By Module Start*/
    $('.assignToModuleIds').on('change', function(e){
        e.preventDefault();
        var moduleIds = [];
        $('.assignToModuleIds').each(function(){
            if($(this).prop('checked')){
                moduleIds.push($(this).val());
            }
        });

        $('.assignStudentsList.existingStudentList').addClass('loading');
        if(moduleIds.length > 0){
            axios({
                method: "post",
                url: route('assign.get.existing.student.list.by.module'),
                data: {moduleids : moduleIds},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                $('.assignStudentsList.existingStudentList').removeClass('loading');
                if (response.status == 200) {
                    $('.assignStudentsList.existingStudentList').html(response.data.res.htm);
                    $('.existingCount').html(response.data.res.count);

                    createIcons({
                        icons,
                        "stroke-width": 1.5,
                        nameAttr: "data-lucide",
                    });
                }
            }).catch(error => {
                if (error.response) {
                    $('.assignStudentsList.existingStudentList').removeClass('loading');
                    $('.assignStudentsList.existingStudentList').html('');
                    $('.existingCount').html('0');
                    console.log('error');
                }
            });
        }else{
            $('.assignStudentsList.existingStudentList').removeClass('loading').html('');
        }
    });
    /* Filter Existing Student List By Module End*/

    /* Filter Existing Student List Start*/
    $('#filterExistingStudents').on('keyup', function() {
        var value = this.value.toLowerCase().trim();
        $(".assignStudentsList.existingStudentList li").show().filter(function() {
            return $(this).attr('data-reg').toLowerCase().trim().indexOf(value) == -1;
        }).hide();
    });
    /* Filter Existing Student List End*/

    /* Display Students Module List Start*/
    $('.assignStudentsList').on('click', 'li > a.showAllModules', function(){
        var $theLink = $(this);
        var ids = $theLink.attr('data-ids');
        
        axios({
            method: "post",
            url: route('assign.get.module.list.html'),
            data: {ids : ids},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200) {
                $("#showAllModulesModal .modal-body").html(response.data.res);

                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
            }
        }).catch(error => {
            if (error.response) {
                showAllModulesModal.hide();
                console.log('error');
            }
        });
    })
    /* Display Students MOdule List End*/

    /* Select Deselect Existing Students Start*/
    $('.assignStudentsList.existingStudentList').on('click', 'li > label', function(){
        $(this).parent('li').toggleClass('active');

        var activeLength = $('.assignStudentsList.existingStudentList').find('li.active').length;
        if(activeLength > 0){
            $('button.removeStudents').removeAttr('disabled');
        }else{
            $('button.removeStudents').attr('disabled', 'disabled');
        }
    });
    /* Select Deselect Existing Students End*/


    /* Potential Student Search Start*/
    $('#potentialStudentSearch').on('keyup', function(){
        var $theInput = $(this);
        var theValue = $theInput.val();
        var existingStudents = [];
        if($('.assignStudentsList.existingStudentList li').length > 0){
            $('.assignStudentsList.existingStudentList li').each(function(){
                existingStudents.push($(this).attr('data-studentid'));
            })
        }
        resetTermSearch();

        $('.assignStudentsList.potentialStudentList').addClass('loading').html('');
        if(theValue.length > 0){
            axios({
                method: "post",
                url: route('assign.get.potential.student.list.by.search'),
                data: {theValue : theValue, existingStudents : existingStudents},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                $('.assignStudentsList.potentialStudentList').removeClass('loading');
                if (response.status == 200) {
                    $('.assignStudentsList.potentialStudentList').html(response.data.res.htm);
                    if(response.data.res.count > 0){
                        $('.potentialCount').html(' ('+response.data.res.count+')');
                    }else{
                        $('.potentialCount').html('');
                    }

                    createIcons({
                        icons,
                        "stroke-width": 1.5,
                        nameAttr: "data-lucide",
                    });
                }
            }).catch(error => {
                if (error.response) {
                    $('.assignStudentsList.potentialStudentList').removeClass('loading').html('');
                    $('.potentialCount').html('');
                    console.log('error');
                }
            });
        }else{
            $('.assignStudentsList.potentialStudentList').removeClass('loading').html('');
        }
    });

    $('#potentialTermDeclaration').on('change', function(e){
        let $assignToCourseId = $('#assignToCourseId');
        let $potentialTermDeclaration = $('#potentialTermDeclaration');
        let termDeclarationId = $potentialTermDeclaration.val();
        let assignToCourseId = $assignToCourseId.val();

        $('#potentialStudentSearch').val('');
        $('.assignStudentsList.potentialStudentList').removeClass('loading').html('');
        $('button.addStudents').removeAttr('disabled');
        $('.potentialCount').html('');
        $('.termModuleBox').fadeOut('fast', function(){
            $('#termModuleBoxBody', this).html('');
        });
        $('.potentialGroupArea').fadeOut('fast', function(){
            potentialGroups.clear(true);
            potentialGroups.clearOptions();
            potentialGroups.disable();
        })
        $('.potentialModuleArea').fadeOut('fast', function(){
            potentialModules.clear(true);
            potentialModules.clearOptions();
            potentialModules.disable();
        });

        if(termDeclarationId > 0){
            $potentialTermDeclaration.parent('div').siblings('div').children('.theLoading').fadeIn();
            axios({
                method: "post",
                url: route('assign.get.group.list'),
                data: {termDeclarationId : termDeclarationId, assignToCourseId : assignToCourseId},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                $potentialTermDeclaration.parent('div').siblings('div').children('.theLoading').fadeOut();
                if (response.status == 200) {
                    $('.potentialGroupArea').fadeIn('fast', function(){
                        potentialGroups.enable();
                        $.each(response.data.res, function(index, row) {
                            potentialGroups.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });
                        potentialGroups.refreshOptions();
                    });
                }
            }).catch(error => {
                if (error.response) {
                    $('#theSearchForm svg.theLoading').fadeOut();
                    console.log('error');
                }
            });
        }
    });

    $('#potentialGroups').on('change', function(e){
        let $assignToCourseId = $('#assignToCourseId');
        let $potentialTermDeclaration = $('#potentialTermDeclaration');
        let $potentialGroups = $('#potentialGroups');
        let termDeclarationId = $potentialTermDeclaration.val();
        let assignToCourseId = $assignToCourseId.val();
        let assignGroupId = $potentialGroups.val();
        var existingStudents = [];
        if($('.assignStudentsList.existingStudentList li').length > 0){
            $('.assignStudentsList.existingStudentList li').each(function(){
                existingStudents.push($(this).attr('data-studentid'));
            })
        }

        $('#potentialStudentSearch').val('');
        $('.assignStudentsList.potentialStudentList').removeClass('loading').html('');
        $('button.addStudents').removeAttr('disabled');
        $('.potentialCount').html('');
        $('.termModuleBox').fadeOut('fast', function(){
            $('#termModuleBoxBody', this).html('');
        });
        $('.potentialModuleArea').fadeOut('fast', function(){
            potentialModules.clear(true);
            potentialModules.clearOptions();
            potentialModules.disable();
        });

        if(assignGroupId > 0){
            $('.assignStudentsList.potentialStudentList').addClass('loading');
            $potentialGroups.parent('div').siblings('div').children('.theLoading').fadeIn();
            axios({
                method: "post",
                url: route('assign.get.module.student.list'),
                data: {termDeclarationId : termDeclarationId, assignToCourseId : assignToCourseId, assignGroupId : assignGroupId, existingStudents : existingStudents},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                $potentialGroups.parent('div').siblings('div').children('.theLoading').fadeOut();
                if (response.status == 200) {

                    if(response.data.res.modules){
                        $('.potentialModuleArea').fadeIn('fast', function(){
                            potentialModules.enable();
                            potentialModules.addOption({
                                value: '',
                                text: 'Please Select',
                            });
                            $.each(response.data.res.modules, function(index, row) {
                                potentialModules.addOption({
                                    value: row.id,
                                    text: row.name,
                                });
                            });
                            potentialModules.refreshOptions();
                        });
                    }

                    if(response.data.res.students.count > 0){
                        $('.potentialCount').html(' ('+response.data.res.students.count+')');
                    }else{
                        $('.potentialCount').html('');
                    }

                    $('.assignStudentsList.potentialStudentList').removeClass('loading');
                    if(response.data.res.students.htm != ''){
                        $('.assignStudentsList.potentialStudentList').html(response.data.res.students.htm);
                    }else{
                        $('.assignStudentsList.potentialStudentList').html('');
                    }

                    if(response.data.res.module_html != ''){
                        $('.termModuleBox').fadeIn('fast', function(){
                            $('#termModuleBoxBody', this).html(response.data.res.module_html);
                            createIcons({
                                icons,
                                "stroke-width": 1.5,
                                nameAttr: "data-lucide",
                            });
                        });
                    }else{
                        $('.termModuleBox').fadeOut('fast', function(){
                            $('#termModuleBoxBody', this).html('');
                        });
                    }

                    createIcons({
                        icons,
                        "stroke-width": 1.5,
                        nameAttr: "data-lucide",
                    });

                }
            }).catch(error => {
                if (error.response) {
                    $('.assignStudentsList.potentialStudentList').removeClass('loading');
                    $('#theSearchForm svg.theLoading').fadeOut();
                    console.log('error');
                }
            });
        }else{
            $('.assignStudentsList.potentialStudentList').removeClass('loading');
            $('.termModuleBox').fadeOut('fast', function(){
                $('#termModuleBoxBody', this).html('');
            });
        }
    });

    $('#potentialModules').on('change', function(e){
        let $assignToCourseId = $('#assignToCourseId');
        let $potentialTermDeclaration = $('#potentialTermDeclaration');
        let $potentialGroups = $('#potentialGroups');
        let $potentialModules = $('#potentialModules');
        let termDeclarationId = $potentialTermDeclaration.val();
        let assignToCourseId = $assignToCourseId.val();
        let assignGroupId = $potentialGroups.val();
        let assignModuleId = $potentialModules.val();
        var existingStudents = [];
        if($('.assignStudentsList.existingStudentList li').length > 0){
            $('.assignStudentsList.existingStudentList li').each(function(){
                existingStudents.push($(this).attr('data-studentid'));
            })
        }
        
        $('#potentialStudentSearch').val('');
        $('.potentialCount').html('');

        $('.assignStudentsList.potentialStudentList').addClass('loading').html('');
        $('button.addStudents').removeAttr('disabled');
        $potentialModules.parent('div').siblings('div').children('.theLoading').fadeIn();
        axios({
            method: "post",
            url: route('assign.get.student.list.by.module'),
            data: {termDeclarationId : termDeclarationId, assignToCourseId : assignToCourseId, assignGroupId : assignGroupId, assignModuleId : assignModuleId, existingStudents : existingStudents},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            $potentialModules.parent('div').siblings('div').children('.theLoading').fadeOut();
            if (response.status == 200) {
                if(response.data.res.count > 0){
                    $('.potentialCount').html(' ('+response.data.res.count+')');
                }else{
                    $('.potentialCount').html('');
                }

                $('.assignStudentsList.potentialStudentList').removeClass('loading');
                if(response.data.res.htm != ''){
                    $('.assignStudentsList.potentialStudentList').html(response.data.res.htm);
                }else{
                    $('.assignStudentsList.potentialStudentList').html('');
                }

                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });

            }
        }).catch(error => {
            if (error.response) {
                $('.assignStudentsList.potentialStudentList').removeClass('loading').html('');
                $('#theSearchForm svg.theLoading').fadeOut();
                console.log('error');
            }
        });

    });

    function resetTermSearch(){
        $('.assignStudentsList.potentialStudentList').removeClass('loading').html('');
        $('button.addStudents').removeAttr('disabled');
        $('.potentialCount').html('');

        potentialTermDeclaration.clear(true);

        $('.potentialGroupArea').fadeOut('fast', function(){
            potentialGroups.clear(true);
            potentialGroups.clearOptions();
            potentialGroups.disable();
        })
        $('.potentialModuleArea').fadeOut('fast', function(){
            potentialModules.clear(true);
            potentialModules.clearOptions();
            potentialModules.disable();
        });
        $('.termModuleBox').fadeOut('fast', function(){
            $('#termModuleBoxBody', this).html('');
        });
    }
    /* Potential Student Search End*/

    /* Select Deselect Potential Students Start*/
    $('.assignStudentsList.potentialStudentList').on('click', 'li > label', function(){
        if(!$(this).parent('li').hasClass('headingItem') && !$(this).parent('li').hasClass('noticeItem')){
            $(this).parent('li').toggleClass('active');

            var activeLength = $('.assignStudentsList.potentialStudentList').find('li.active').length;
            if(activeLength > 0){
                $('button.addStudents').removeAttr('disabled');
            }else{
                $('button.addStudents').attr('disabled', 'disabled');
            }
        }
    });
    /* Select Deselect Existing Students End*/

    /* Toggle Select All Start */
    $('.selectDeselectAllPotential').on('click', function(e){
        e.preventDefault();
        var itemLength = $('.assignStudentsList.potentialStudentList li:not(.headingItem):not(.noticeItem)').length;
        var activeLenth = $('.assignStudentsList.potentialStudentList li.active').length;
        if(itemLength !== activeLenth){
            $('.assignStudentsList.potentialStudentList li:not(.headingItem):not(.noticeItem)').addClass('active');
            $('button.addStudents').removeAttr('disabled');
        }else{
            $('.assignStudentsList.potentialStudentList li').removeClass('active');
            $('button.addStudents').attr('disabled', 'disabled');
        }
    })
    /* Toggle Select All End */


    /* Assign Students to Class Plan Start */
    $('.addStudents').on('click', function(e){
        e.preventDefault();
        let $theBtn = $(this);
        let academic_year = $('#assignToAcademicYearId').val();
        let term_declaration = $('#assignToTermDeclarationId').val();
        let course_id = $('#assignToCourseId').val();
        let group_id = $('#assignToGroupId').val();
        let plans_id = [];
        let students_id = [];
        $('.assignToModuleIds').each(function(){
            if($(this).prop('checked')){
                plans_id.push($(this).val());
            };
        });
        $('.assignStudentsList.potentialStudentList li.active').each(function(){
            students_id.push($(this).attr('data-studentid'));
        })

        if(plans_id.length > 0 && students_id.length > 0){
            $theBtn.attr('disabled', 'disabled');
            $theBtn.find('svg.theLoader').fadeIn();
            $('.assignStudentsList.potentialStudentList').addClass('loading');

            axios({
                method: "post",
                url: route('assign.students.to.plan'),
                data: {plans_id : plans_id, students_id : students_id},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                $theBtn.find('svg.theLoader').fadeOut();
                $theBtn.attr('disabled', 'disabled');
                $('.assignStudentsList.potentialStudentList').removeClass('loading');
                $('.assignStudentsList.potentialStudentList li').removeClass('active');

                if (response.status == 200) {
                    let successes = response.data.success;
                    let errors = response.data.errors;

                    if(successes.ids.length > 0){
                        $('.resultWrap').fadeIn('fast').html('<div class="alert alert-success-soft show flex items-center mb-2 alert-dismissible" role="alert"><i data-lucide="check-circle" class="w-6 h-6 mr-2"></i> WOW! ('+successes.ids.length+') Students successfully assigned to selected modules.<button type="button" class="btn-close" data-tw-dismiss="alert" aria-label="Close"><i data-lucide="x" class="w-4 h-4"></i></button></div>');
                        $.each(successes.ids, function(index, student) {
                            $('.assignStudentsList.potentialStudentList li[data-studentid="'+student+'"]').hide();
                        });
                        if(successes.htm != ''){
                            $('.assignStudentsList.existingStudentList').prepend(successes.htm);
                        }

                        var newExistLength = $('.assignStudentsList.existingStudentList li').length;
                        $('.existingCount').html(' ('+(newExistLength > 0 ? newExistLength : 0)+')');
                    }
                    if(errors.ids.length > 0){
                        var errorHtml = '';
                        errorHtml += '<ul class="mt-2 assignErrorUl hidden">';
                        $.each(errors.mod_ids, function(module, student) {
                            errorHtml += '<li class="flex items-start mb-2">';
                                errorHtml += '<i data-lucide="x-circle" class="w-4 h-4 mr-3"></i>';
                                errorHtml += '<span>'
                                    errorHtml += '<strong>'+module+'</strong><br/>';
                                    errorHtml += '<span>'+student.join(', ')+'</span>';
                                errorHtml += '</span>';
                            errorHtml += '</li>';
                        });
                        errorHtml += '</ul>';
                        
                        $('.resultWrap').fadeIn('fast').append(
                            '<div class="alert alert-danger-soft show flex items-start mb-0 alert-dismissible" role="alert">\
                                <i data-lucide="alert-octagon" class="w-6 h-6 mr-2 -mt-1"></i>\
                                <span>\
                                    <span>Oops! Previously assigned students found! Click <a href="#" class="font-medium errorToggler"><u>here</u></a> to show details</span>\
                                    '+errorHtml+'\
                                </span>\
                                <button type="button" class="btn-close" data-tw-dismiss="alert" aria-label="Close">\
                                    <i data-lucide="x" class="w-4 h-4"></i>\
                                </button>\
                            </div>'
                        );
                    }

                    createIcons({
                        icons,
                        "stroke-width": 1.5,
                        nameAttr: "data-lucide",
                    });
                }
            }).catch(error => {
                if (error.response) {
                    $theBtn.find('svg.theLoader').fadeOut();
                    $theBtn.removeAttr('disabled');
                    $('.assignStudentsList.potentialStudentList').removeClass('loading');
                    $('.resultWrap').fadeIn('fast').html('<div class="alert alert-danger-soft show flex items-center mb-5" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Something went wrong. Please try later or contact with the administrator.</div>');
                    createIcons({
                        icons,
                        "stroke-width": 1.5,
                        nameAttr: "data-lucide",
                    });

                    setTimeout(function(){
                        $('.resultWrap').fadeOut('fast').html('');
                    }, 5000);
                    console.log('error');
                }
            });
        }else{
            $('.resultWrap').fadeIn('fast').html('<div class="alert alert-danger-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> You have select some Modules and Potential students.</div>');
            createIcons({
                icons,
                "stroke-width": 1.5,
                nameAttr: "data-lucide",
            });

            setTimeout(function(){
                $('.resultWrap').fadeOut('fast').html('');
            }, 5000);
        }

    });

    $(document).on('click', '.errorToggler', function(e){
        e.preventDefault();
        $(this).parent('span').siblings('ul.assignErrorUl').slideToggle();
    })
    /* Assign Students to Class Plan End */

    /* DeAssign Students to Class Plan Start */
    $('.removeStudents').on('click', function(e){
        e.preventDefault();
        let $theBtn = $(this);
        let academic_year = $('#assignToAcademicYearId').val();
        let term_declaration = $('#assignToTermDeclarationId').val();
        let course_id = $('#assignToCourseId').val();
        let group_id = $('#assignToGroupId').val();
        let plans_id = [];
        let students_id = [];
        $('.assignToModuleIds').each(function(){
            if($(this).prop('checked')){
                plans_id.push($(this).val());
            };
        });
        $('.assignStudentsList.existingStudentList li.active').each(function(){
            students_id.push($(this).attr('data-studentid'));
        });

        if(plans_id.length > 0 && students_id.length > 0){
            $theBtn.attr('disabled', 'disabled');
            $theBtn.find('svg.theLoader').fadeIn();
            $('.assignStudentsList.existingStudentList').addClass('loading');

            axios({
                method: "post",
                url: route('assign.remove.students.from.plan'),
                data: {plans_id : plans_id, students_id : students_id},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                $theBtn.find('svg.theLoader').fadeOut();
                $theBtn.attr('disabled', 'disabled');
                $('.assignStudentsList.existingStudentList').removeClass('loading');
                $('.assignStudentsList.existingStudentList li').removeClass('active');

                if (response.status == 200) {
                    var res = response.data.res;
                    if(res != ''){
                        $.each(res, function(status, row) {
                            if($('.assignStudentsList.potentialStudentList li.headingItem[data-status="'+status+'"]').length > 0){
                                $.each(row.htm, function(student_id, htmls){
                                    $('.assignStudentsList.existingStudentList li[data-studentid="'+student_id+'"]').remove();
                                    $('.assignStudentsList.potentialStudentList li[data-studentid="'+student_id+'"]').remove();
                                    $('.assignStudentsList.potentialStudentList li.headingItem[data-status="'+status+'"]').after(htmls);
                                })
                            }else{
                                $('.assignStudentsList.potentialStudentList').append(row.heading);
                                $.each(row.htm, function(student_id, htmls){
                                    $('.assignStudentsList.existingStudentList li[data-studentid="'+student_id+'"]').remove();
                                    $('.assignStudentsList.potentialStudentList').append(htmls);
                                })
                            }
                        });
                    }
                    createIcons({
                        icons,
                        "stroke-width": 1.5,
                        nameAttr: "data-lucide",
                    });

                    console.log(response.data.res);
                }
            }).catch(error => {
                if (error.response) {
                    $theBtn.find('svg.theLoader').fadeOut();
                    $theBtn.removeAttr('disabled');
                    $('.assignStudentsList.existingStudentList').removeClass('loading');
                    $('.resultWrap').fadeIn('fast').html('<div class="alert alert-danger-soft show flex items-center mb-5" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Something went wrong. Please try later or contact with the administrator.</div>');
                    createIcons({
                        icons,
                        "stroke-width": 1.5,
                        nameAttr: "data-lucide",
                    });

                    setTimeout(function(){
                        $('.resultWrap').fadeOut('fast').html('');
                    }, 5000);
                    console.log('error');
                }
            });
        }else{
            $('.resultWrap').fadeIn('fast').html('<div class="alert alert-danger-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> You have select some Modules and Existing students.</div>');
            createIcons({
                icons,
                "stroke-width": 1.5,
                nameAttr: "data-lucide",
            });

            setTimeout(function(){
                $('.resultWrap').fadeOut('fast').html('');
            }, 5000);
        }

    });
    /* DeAssign Students to Class Plan End */

})();