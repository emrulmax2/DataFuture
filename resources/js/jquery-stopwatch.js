
(function( ){

    let startTime;
    let stopwatchInterval;
    let elapsedPausedTime = 0;

    function startStopwatch(elementid, starts) {
        if (!stopwatchInterval) {
            startTime = starts - elapsedPausedTime;
            stopwatchInterval = setInterval(updateStopwatch, 1000, elementid);
        }
    }

    function updateStopwatch(elementid) {
        var currentTime = new Date().getTime();
        var elapsedTime = currentTime - startTime;
        var seconds = Math.floor(elapsedTime / 1000) % 60;
        var minutes = Math.floor(elapsedTime / 1000 / 60) % 60;
        var hours = Math.floor(elapsedTime / 1000 / 60 / 60);
        var displayTime = hours+" hours "+minutes+' mins';
        document.getElementById(elementid).innerHTML = displayTime;
    }

    if($('#clockedInFrom').length > 0){
        var starts = $('#clockedInFrom').attr('data-starts') * 1;
        startStopwatch('clockedInFrom', starts);
    }

})();