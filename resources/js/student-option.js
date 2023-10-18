import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import Dropzone from "dropzone";

Dropzone.autoDiscover = false;
$(".dropzone").each(function () {
    let options = {
        autoProcessQueue: false,
        accept: (file, done) => {
            console.log("Uploaded");
            done();
        },
    };

    if ($(this).data("single")) {
        options.maxFiles = 1;
    }

    if ($(this).data("file-types")) {
        options.accept = (file, done) => {
            if ($(this).data("file-types").split("|").indexOf(file.type) === -1) {
                alert("Error! Files of this type are not accepted");
                done("Error! Files of this type are not accepted");
            } else {
                console.log("Uploaded");
                done();
            }
        };
    }

    var dz = new Dropzone(this, options);

    dz.on("maxfilesexceeded", (file) => {
        alert("No more files please!");
    });
    dz.on("complete", function(file) {
        dz.removeFile(file);
    });        
});

(function(){
    $('.optionBoxTitle').on('click', function(e){
        e.preventDefault();
        var $title = $(this);
        var $box = $title.parents('.optionBox');
        var $boxBody = $title.parent('.optionBoxHeader').siblings('.optionBoxBody');

        $boxBody.slideToggle();
        $box.toggleClass('active');

        createIcons({
            icons,
            "stroke-width": 1.5,
            nameAttr: "data-lucide",
        });
    })
})();