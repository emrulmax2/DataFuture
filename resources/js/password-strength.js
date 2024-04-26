(function () {
    "use strict";
    if($('#password').length >0) {
        $('#password').on('keyup', function(e) {
            let totalText = this.value
            let strenghtTips = checkPasswordStrengthFull(totalText)
            const box1 = document.getElementById('strength-1');
            const box2 = document.getElementById('strength-2');
            const box3 = document.getElementById('strength-3');
            const box4 = document.getElementById('strength-4');

            switch (strenghtTips) {
                case 1:
                        box1.classList.remove('bg-slate-100','dark:bg-darkmode-800')
                        box1.classList.add('bg-danger');
                        break;
                case 2: 
                        box2.classList.remove('bg-slate-100','dark:bg-darkmode-800')
                        box2.classList.add('bg-warning');
                        break;
                case 3: 
                        box3.classList.remove('bg-slate-100','dark:bg-darkmode-800')
                        box3.classList.add('bg-success');
                        break;
                case 4: 
                case 5: 
                case 6: 
                case 7: 
                case 8: 
                case 9: 
                        box4.classList.remove('bg-slate-100','dark:bg-darkmode-800')
                        box4.classList.add('bg-success');
                        break;
                default:
                        box1.classList.remove('bg-danger', 'bg-warning','bg-success','bg-slate-100','dark:bg-darkmode-800');
                        box2.classList.remove('bg-danger', 'bg-warning','bg-success','bg-slate-100','dark:bg-darkmode-800');
                        box3.classList.remove('bg-danger', 'bg-warning','bg-success','bg-slate-100','dark:bg-darkmode-800');
                        box4.classList.remove('bg-danger', 'bg-warning','bg-success','bg-slate-100','dark:bg-darkmode-800');
                        
                        box1.classList.add('bg-slate-100','dark:bg-darkmode-800');
                        box2.classList.add('bg-slate-100','dark:bg-darkmode-800');
                        box3.classList.add('bg-slate-100','dark:bg-darkmode-800');
                        box4.classList.add('bg-slate-100','dark:bg-darkmode-800');
                        break;
            }
        })
    }

})();

function checkPasswordStrengthFull(password) {
    // Initialize variables
    let strength = 0;
    let tips = "";
    //let lowUpperCase = document.querySelector(".low-upper-case i");

    //let number = document.querySelector(".one-number i");
    //let specialChar = document.querySelector(".one-special-char i");
    //let eightChar = document.querySelector(".eight-character i");

    //If password contains both lower and uppercase characters
    if (password.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/)) {
        strength += 1;
        //lowUpperCase.classList.remove('fa-circle');
        //lowUpperCase.classList.add('fa-check');
    } else {
        //lowUpperCase.classList.add('fa-circle');
        //lowUpperCase.classList.remove('fa-check');
    }
    //If it has numbers and characters
    if (password.match(/([0-9])/)) {
        strength += 1;
        //number.classList.remove('fa-circle');
        //number.classList.add('fa-check');
    } else {
        //number.classList.add('fa-circle');
        //number.classList.remove('fa-check');
    }
    //If it has one special character
    if (password.match(/([!,%,&,@,#,$,^,*,?,_,~])/)) {
        strength += 1;
        //specialChar.classList.remove('fa-circle');
        //specialChar.classList.add('fa-check');
    } else {
        //specialChar.classList.add('fa-circle');
        //specialChar.classList.remove('fa-check');
    }
    //If password is greater than 7
    if (password.length > 7) {
        strength += 1;
        //eightChar.classList.remove('fa-circle');
        //eightChar.classList.add('fa-check');
    } else {
        //eightChar.classList.add('fa-circle');
        //eightChar.classList.remove('fa-check');   
    }
   
    // Return results
    if (strength < 2) {
        return strength;
    } else if (strength === 2) {
        return strength;
    } else if (strength === 3) {
        return strength;
    } else {
        return strength;
    }
}
