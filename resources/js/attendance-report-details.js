
(function(){
    $('#employeeAttendanceDetailsTable tr.expandRow').on('click', function(){
        var $theTr = $(this);
        var theNextTrId = $theTr.attr('data-expandid');
        var $theNextTr = $('#employeeAttendanceDetailsTable '+theNextTrId);

        $theNextTr.fadeToggle();
    })
})()