import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
import Dropzone from "dropzone";

(function(){
    let tomOptionsGlobal = {
        plugins: {
            dropdown_input: {}
        },
        placeholder: 'Search Here...',
        //persist: false,
        maxOptions: null,
        create: false,
        allowEmptyOption: true,
        onDelete: function (values) {
            return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };

    /* Start Dropzone */
    if($("#addStudentPhotoModal").length > 0){
        let dzErrors = false;
        Dropzone.autoDiscover = false;
        Dropzone.options.addStudentPhotoForm = {
            autoProcessQueue: false,
            maxFiles: 1,
            maxFilesize: 5,
            parallelUploads: 1,
            acceptedFiles: ".jpeg,.jpg,.png,.gif",
            addRemoveLinks: true,
            //thumbnailWidth: 100,
            //thumbnailHeight: 100,
        };

        let options = {
            accept: (file, done) => {
                console.log("Uploaded");
                done();
            },
        };


        var drzn1 = new Dropzone('#addStudentPhotoForm', options);

        drzn1.on("maxfilesexceeded", (file) => {
            $('#addStudentPhotoModal .modal-content .uploadError').remove();
            $('#addStudentPhotoModal .modal-content').prepend('<div class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Can not upload more than 10 files at a time.</div>');
            drzn1.removeFile(file);
            setTimeout(function(){
                $('#addStudentPhotoModal .modal-content .uploadError').remove();
            }, 2000)
        });

        drzn1.on("error", function(file, response){
            dzErrors = true;
        });

        drzn1.on("success", function(file, response){
            //console.log(response);
            return file.previewElement.classList.add("dz-success");
        });

        drzn1.on("complete", function(file) {
            //drzn1.removeFile(file);
        }); 

        drzn1.on('queuecomplete', function(){
            $('#uploadStudentPhotoBtn').removeAttr('disabled');
            document.querySelector("#uploadStudentPhotoBtn svg").style.cssText ="display: none;";

            if(!dzErrors){
                drzn1.removeAllFiles();

                $('#addStudentPhotoModal .modal-content .uploadError').remove();
                $('#addStudentPhotoModal .modal-content').prepend('<div class="alert uploadError alert-success-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> WOW! Student photo successfully uploaded.</div>');
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });

                setTimeout(function(){
                    $('#addStudentPhotoModal .modal-content .uploadError').remove();
                    window.location.reload();
                }, 2000);
            }else{
                $('#addStudentPhotoModal .modal-content .uploadError').remove();
                $('#addStudentPhotoModal .modal-content').prepend('<div class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Something went wrong. Please try later.</div>');
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
                
                setTimeout(function(){
                    $('#addStudentPhotoModal .modal-content .uploadError').remove();
                }, 2000);
            }
        })

        $('#uploadStudentPhotoBtn').on('click', function(e){
            e.preventDefault();
            document.querySelector('#uploadStudentPhotoBtn').setAttribute('disabled', 'disabled');
            document.querySelector("#uploadStudentPhotoBtn svg").style.cssText ="display: inline-block;";
            
            drzn1.processQueue();
            
        });
    }
    /* End Dropzone */

    /* Profile Menu Start */
    document.getElementById('menu-toggle').addEventListener('click', function () {
        var menu = document.querySelector('.liveStudentMainMenu');
        menu.classList.toggle('hidden');
        menu.classList.toggle('flex');
    });
    
    // Handle submenu toggle
    if (document.querySelector('.liveStudentMainMenu')) {
        document.querySelectorAll('.liveStudentMainMenu li.hasChildren > a').forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                var $this = this;
                var menu = document.querySelector('.liveStudentMainMenu');
                var paddingValue = window.innerWidth <= 768 ? '38px' : '55px'; // Adjust for screen size
    
                if ($this.classList.contains('active')) {
                    $this.classList.remove('active');
                    $this.nextElementSibling.classList.remove('show');
                    menu.style.paddingBottom = '0';
                } else {
                    $this.parentElement.parentElement.querySelectorAll('li.hasChildren > a').forEach(function (el) {
                        el.classList.remove('active');
                    });
                    $this.parentElement.parentElement.querySelectorAll('.liveStudentSubMenu').forEach(function (el) {
                        el.classList.remove('show');
                    });
    
                    $this.classList.add('active');
                    menu.style.paddingBottom = paddingValue;
                    $this.nextElementSibling.classList.add('show');
                }
            });
        });
    
        // Listen for window resize to adjust padding dynamically
        window.addEventListener('resize', function () {
            var activeLink = document.querySelector('.liveStudentMainMenu li.hasChildren > a.active');
            if (activeLink) {
                var menu = document.querySelector('.liveStudentMainMenu');
                menu.style.paddingBottom = window.innerWidth <= 768 ? '38px' : '55px';
            }
        });
    }
    
    /* Profile Menu End */

    /* Student Status Update */
    if($("#changeStudentModal").length > 0) {
        let change_status_id = new TomSelect('#change_status_id', tomOptionsGlobal);
        const changeStudentModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#changeStudentModal"));
        const successModalInfo = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModalInfo"));

        $('#changeStudentModal #change_status_id, #changeStudentModal #term_declaration_id').on('change', function(){
            var $theStatus = $('#changeStudentModal #change_status_id');
            var $theTerm = $('#changeStudentModal #term_declaration_id');

            var theStatus = $theStatus.val();
            var theTerm = $theTerm.val();
            var student_id = $('#changeStudentModal [name="student_id"]').val();
            $('#changeStudentModal').find('dotLoader').fadeIn();

            if(theStatus > 0 && theTerm > 0){
                axios({
                    method: "post",
                    url: route('student.check.status'),
                    data: {student_id : student_id, theStatus : theStatus, theTerm : theTerm},
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        let res = response.data.res;
                        if(res.indicator == 1){
                            $('#changeStudentModal .attenIndicatorWrap #attendance_indicator').prop('checked', true);
                        }else{
                            $('#changeStudentModal .attenIndicatorWrap #attendance_indicator').prop('checked', false);
                        }
                        if(res.notice == 1){
                            $('#changeStudentModal .attenIndicatorWrap').fadeIn('fast', function(){
                                $('#changeStudentModal .attenIndicatorWrap .indNotice').remove();
                                $('#changeStudentModal .attenIndicatorWrap').append('<div class="indNotice alert alert-warning-soft show flex items-center mt-3" role="alert"><span>'+res.msg+'</span></div>')
                            });
                        }else{
                            $('#changeStudentModal .attenIndicatorWrap').fadeOut('fast', function(){
                                $('#changeStudentModal .attenIndicatorWrap .indNotice').remove();
                            });
                        }

                        $('#changeStudentModal').find('dotLoader').fadeOut();
                        $('#changeStudentForm #updateStatusBtn').removeAttr('disabled');
                    }
                }).catch(error => {
                    if (error.response) {
                        console.log('error');
                    }
                });
            }else{
                $('#changeStudentModal').find('dotLoader').fadeOut();
                $('#changeStudentForm #updateStatusBtn').attr('disabled', 'disabled');
            }
        })

        $('#changeStudentForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('changeStudentForm');
        
            document.querySelector('#updateStatusBtn').setAttribute('disabled', 'disabled');
            document.querySelector("#updateStatusBtn svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('student.update.status'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#updateStatusBtn').removeAttribute('disabled');
                document.querySelector("#updateStatusBtn svg").style.cssText = "display: none;";

                if (response.status == 200) {
                    changeStudentModal.hide();

                    successModalInfo.show(); 
                    document.getElementById("successModalInfo").addEventListener("shown.tw.modal", function (event) {
                        $("#successModalInfo .successModalInfoTitle").html("Congratulation!" );
                        $("#successModalInfo .successModalInfoDesc").html('Student status successfully updated.');
                    });  
                    
                    setTimeout(function(){
                        successModalInfo.hide();
                       window.location.reload();
                    }, 2000);
                }
            }).catch(error => {
                document.querySelector('#updateStatusBtn').removeAttribute('disabled');
                document.querySelector("#updateStatusBtn svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#changeStudentForm .${key}`).addClass('border-danger');
                            $(`#changeStudentForm  .error-${key}`).html(val);
                        }
                    } else if(error.response.status == 304){
                        changeStudentModal.hide();

                        successModalInfo.show(); 
                        document.getElementById("successModalInfo").addEventListener("shown.tw.modal", function (event) {
                            $("#successModalInfo .successModalInfoTitle").html("Oops!" );
                            $("#successModalInfo .successModalInfoDesc").html('Nothing was changed. Please try again later.');
                        });  
                        
                        setTimeout(function(){
                            successModalInfo.hide();
                            window.location.reload();
                        }, 2000);
                    } else {
                        console.log('error');
                    }
                }
            });
        });
    }
    /* Student Status Update */
})();