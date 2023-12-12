import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
import Dropzone from "dropzone";

(function(){

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
    if($('.liveStudentMainMenu').length > 0){
        $('.liveStudentMainMenu li.hasChildren > a').on('click', function(e){
            e.preventDefault();
            var $this = $(this);

            if($this.hasClass('active')){
                $this.removeClass('active');
                $this.siblings('.liveStudentSubMenu').removeClass('show');
                $('.liveStudentMainMenu').animate({'padding-bottom' : '0'}, 'fast');
            }else{
                $this.parent('li').siblings('li').children('a').removeClass('active');
                $this.parent('li').siblings('li').children('.liveStudentSubMenu').removeClass('show');

                $this.addClass('active');
                $('.liveStudentMainMenu').animate({'padding-bottom' : '55px'}, 350, function(){
                    $this.siblings('.liveStudentSubMenu').addClass('show');
                });
            }
        })
    }
    /* Profile Menu End */
})();