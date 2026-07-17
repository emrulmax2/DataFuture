import { createIcons, icons } from "lucide";

(function(){
    createIcons({ icons, "stroke-width": 1.5, nameAttr: "data-lucide" });

    $('#employeeAttendanceDetailsTable .expandRow').on('click', function(){
        const $row = $(this);
        const targetId = $row.attr('data-expandid');
        const $target = $('#employeeAttendanceDetailsTable ' + targetId);

        $target.fadeToggle(140);
        $row.toggleClass('is-open');
    });
})();
