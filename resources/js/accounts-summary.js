

(function(){
    if($('.accountsMenu').length > 0){
        $('.accountsMenu li.hasDropdown > a').on('click', function(e){
            e.preventDefault();
            $(this).toggleClass('active');
            $(this).siblings('.accDropDown').slideToggle();
        })
    }
})();