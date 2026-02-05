import IMask from 'imask';
import Toastify from "toastify-js";
import INTAddressLookUps from './address_lookup';

(function(){
    // INIT Address Lookup
    document.addEventListener('DOMContentLoaded', () => {
        INTAddressLookUps();
    });

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

    // Turn Off Autocomplete for Datepicker Fields.
    if($('.monthYearMask').length > 0){
        $('.monthYearMask').each(function(){
            $(this).attr('autocomplete', 'off');
            var maskOptions = {
                mask: '00-0000'
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

    if($('.letterTags').length > 0){
        $(document).on('click', '.letterTags li.dropdown-item', function(e){
            var theText = $(this).text();
            navigator.clipboard.writeText(theText);

            Toastify({
                node: $("#coppiedNodeEl")
                    .clone()
                    .removeClass("hidden")[0],
                duration: 2000,
                newWindow: true,
                close: true,
                gravity: "top",
                position: "right",
                stopOnFocus: true,
            }).showToast();
        });
    }

    $('.lcc-accordion-button').on('click', function(e){
        e.preventDefault();
        var $theBtn = $(this);
        var $theAccordion = $theBtn.closest('.lcc-accordion');
        var $theHeader = $theBtn.parent('.lcc-accordion-header');
        var $theBody = $theHeader.siblings('.lcc-accordion-collapse');

        if($theBtn.hasClass('lcc-collapsed')){
            console.log('show Now')
            $theAccordion.find('.lcc-accordion-button').addClass('lcc-collapsed');
            $theAccordion.find('.lcc-accordion-collapse').removeClass('lcc-show').slideUp();

            $theBtn.removeClass('lcc-collapsed');
            $theBody.addClass('lcc-show').slideDown();
        }else{
            console.log('hide Now')
            $theBtn.addClass('lcc-collapsed');
            $theBody.removeClass('lcc-show').slideUp();
        }
    })
})();
