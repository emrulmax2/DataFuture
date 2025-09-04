/* Google Map INIT Code Start */
export default function INTAddressLookUps(){
    let componentForm = {
        locality: "long_name",
        postal_town: "short_name",
        administrative_area_level_1: "short_name",
        country: "long_name",
        postal_code: "short_name",
        street_number: "short_name",
        route: "long_name"
    };
    
    $('.theAddressWrap').each(function(e){
        let $this = $(this);
        let parentid = $this.attr('id');
        let address_field = $('.theAddressLookup', $this); 
        let id = address_field.attr('id');

        let autocomplete = new google.maps.places.Autocomplete(
            document.getElementById(id),
            { types: ["geocode"], componentRestrictions: {country: 'UK'}}
        );
        autocomplete.setFields(["address_component", 'geometry']);
        autocomplete.addListener('place_changed', function () {
            
            var place = autocomplete.getPlace();
            var lat = place.geometry.location.lat(),
                lng = place.geometry.location.lng();
            
            $('#'+parentid+' .address_line_1').val('').removeAttr('disabled');
            $('#'+parentid+' .address_line_2').val('').removeAttr('disabled');
            $('#'+parentid+' .city').val('').removeAttr('disabled');
            $('#'+parentid+' .state').val('').removeAttr('disabled');
            $('#'+parentid+' .country').val('').removeAttr('disabled');
            $('#'+parentid+' .postal_code').val('').removeAttr('disabled');
            $('#'+parentid+' .latitude').val(lat);
            $('#'+parentid+' .longitude').val(lng);

            let street_address = '';
            for (let component of place.address_components) {
                let addressType = component.types[0];

                if (componentForm[addressType]) {
                    if(addressType == 'locality'){
                        $('#'+parentid+' .city').val(component[componentForm[addressType]].toUpperCase());
                    }else if(addressType == 'postal_town' && $('#'+parentid+' .city').val() == ''){
                        $('#'+parentid+' .city').val(component[componentForm[addressType]].toUpperCase());
                    }else if(addressType == 'administrative_area_level_1'){
                        $('#'+parentid+' .state').val(component[componentForm[addressType]].toUpperCase());
                    }else if(addressType == 'country'){
                        $('#'+parentid+' .country').val(component[componentForm[addressType]].toUpperCase());
                    }else if(addressType == 'postal_code'){
                        $('#'+parentid+' .postal_code').val(component[componentForm[addressType]].toUpperCase());
                    }else if(addressType == 'street_number'){
                        //$('#'+parentid+' .address_line_1').val(component[componentForm[addressType]].toUpperCase());
                        street_address += component[componentForm[addressType]]+' ';
                        $('#'+parentid+' .address_line_1').val(street_address);
                    }else if(addressType == 'route'){
                        //$('#'+parentid+' .address_line_1').val(component[componentForm[addressType]].toUpperCase());
                        street_address += component[componentForm[addressType]].toUpperCase();
                        $('#'+parentid+' .address_line_1').val(street_address);
                    }
                }
            }
            // if(street_address != ''){
            //     $('#'+parentid+' .theAddressLookup').val(street_address);
            // }
        });
    });
}
/* Google Map INIT Code End */