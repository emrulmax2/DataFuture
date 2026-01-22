/**
 * Initialize getAddress.io autocomplete for address blocks
 *
 * HTML structure expected:
 * .theAddressWrap
 *  ├─ .theAddressLookup
 *  ├─ .address_line_1
 *  ├─ .address_line_2
 *  ├─ .city
 *  ├─ .state
 *  ├─ .postal_code
 *  ├─ .country
 *  ├─ .latitude
 *  └─ .longitude
 *
 * @param {Object} options
 * @param {String} options.token   getAddress.io DOMAIN TOKEN
 * @param {String} [options.selector='.theAddressWrap']
 * @param {Number} [options.minChars=2]
 * @param {Number} [options.delay=300]
 */
export function initGetAddressAutocomplete({
    token,
    selector = '.theAddressWrap',
    minChars = 2,
    delay = 300
}) {
    if (!token) {
        console.error('getAddress.io token is required');
        return;
    }

    document.querySelectorAll(selector).forEach((wrap, index) => {
        const lookupInput = wrap.querySelector('.theAddressLookup');
        if (!lookupInput) return;

        // getAddress requires a unique ID
        if (!lookupInput.id) {
            lookupInput.id = `getaddress_lookup_${index}_${Date.now()}`;
        }

        const autocomplete = getAddress.autocomplete(
            lookupInput.id,
            token,
            {
                minimum_characters: minChars,
                delay: delay,
                enable_history: false,
                selected: selectaddr
            }
        );

        function selectaddr (addr) {
            // console.log(addr);
            // return false;
            
            lookupInput.value = '';
            setValue(wrap, '.address_line_1', addr.line_1);
            setValue(wrap, '.address_line_2', addr.line_2);
            setValue(wrap, '.city', addr.town_or_city);
            setValue(wrap, '.state', addr.county);
            setValue(wrap, '.postal_code', addr.postcode);
            setValue(wrap, '.country', 'United Kingdom');

            setValue(wrap, '.latitude', addr.latitude);
            setValue(wrap, '.longitude', addr.longitude);
        }
    });
}

/**
 * Safely set input value
 */
function setValue(parent, selector, value) {
    const el = parent.querySelector(selector);
    if (el && value !== undefined && value !== null) {
        el.value = value;
    }
}

