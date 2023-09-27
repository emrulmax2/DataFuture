import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

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