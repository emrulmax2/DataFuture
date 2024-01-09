import IMask from 'imask';

(function(){

    // Turn Off Autocomplete for Datepicker Fields.
    if($('.datepicker').length > 0){
        $('.datepicker').each(function(){
            $(this).attr('autocomplete', 'off');
            var maskOptions = {
                mask: '00-00-0000'
            };
            var mask = IMask(this, maskOptions);
        })
    }

    // Turn off Mouse Wheel for Number Fields.
    if($('input[type="number"').length > 0){
        document.addEventListener("wheel", function(event){
            if(document.activeElement.type === "number"){
                document.activeElement.blur();
            }
        });
    }
})();