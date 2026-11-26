/*
 * Litepicker for the date filters on the new HR report pages (26 Nov 2026), set up as elsewhere in the app:
 * DD-MM-YYYY, one month, month and year dropdowns, the app-wide calendar skin.
 *
 * Each page marks its date inputs with data-hr-date. The report scripts read and write their filters as
 * ISO dates (YYYY-MM-DD) through a data-*-f attribute, and also hand those dates to the old version's form,
 * so the attribute moves to a hidden twin that keeps the ISO value:
 * - picking or typing a date sets the twin and fires "change" on it (bubbles, so form listeners see it);
 * - a script setting twin.value (reset, presets, mirroring) updates the visible field and the calendar;
 * - twin.min / twin.max set the calendar's earliest and latest dates (an empty string removes the limit);
 * - twin.disabled reads and sets the visible field's disabled state; twin.focus() focuses the visible field.
 */
import Litepicker from 'litepicker';

const DMY = /^(\d{1,2})[-/.](\d{1,2})[-/.](\d{4})$/;
const ISO = /^\d{4}-\d{2}-\d{2}$/;
const pad = (n) => String(n).padStart(2, '0');
const isoOf = (d) => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
const dmyOf = (iso) => (ISO.test(iso || '') ? iso.split('-').reverse().join('-') : '');
const dateOf = (iso) => {
    const [y, m, d] = iso.split('-').map(Number);
    return new Date(y, m - 1, d);
};
function parseTyped(text) {
    const m = DMY.exec(text.trim());
    if (!m) return '';
    const d = new Date(+m[3], +m[2] - 1, +m[1]);
    return d.getFullYear() === +m[3] && d.getMonth() === +m[2] - 1 && d.getDate() === +m[1] ? isoOf(d) : '';
}

const native = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value');

function enhance(input) {
    const key = [...input.attributes].find((a) => /^data-[a-z]+-f$/.test(a.name));
    if (!key || input.dataset.hrDateReady) return;
    input.dataset.hrDateReady = '1';

    const twin = document.createElement('input');
    twin.type = 'hidden';
    twin.setAttribute(key.name, key.value);
    input.removeAttribute(key.name);
    input.type = 'text';
    input.autocomplete = 'off';
    if (!input.placeholder) input.placeholder = 'DD-MM-YYYY';
    input.insertAdjacentElement('afterend', twin);

    let iso = ISO.test(native.get.call(input)) ? native.get.call(input) : '';
    let min = '';
    let max = '';
    let quiet = false;
    const show = () => native.set.call(input, dmyOf(iso));
    const inRange = (v) => (!min || v >= min) && (!max || v <= max);
    // The year dropdown follows the limits when there are any.
    const dropdowns = () => ({
        minYear: min ? +min.slice(0, 4) : 1990,
        maxYear: max ? +max.slice(0, 4) : new Date().getFullYear() + 10,
        months: true,
        years: true,
    });

    const picker = new Litepicker({
        element: input,
        format: 'DD-MM-YYYY',
        autoApply: true,
        singleMode: true,
        numberOfColumns: 1,
        numberOfMonths: 1,
        showWeekNumbers: false,
        resetButton: !input.required,
        dropdowns: dropdowns(),
        setup(p) {
            p.on('selected', (d) => {
                if (quiet || !d) return;
                const v = isoOf(d.dateInstance);
                if (inRange(v)) commit(v);
                else setTimeout(sync, 0); // Litepicker takes typed dates without checking the limits
            });
            p.on('clear:selection', () => {
                if (!quiet) commit('');
            });
        },
    });

    function commit(v) {
        const changed = v !== iso;
        iso = v;
        show();
        if (changed) twin.dispatchEvent(new Event('change', { bubbles: true }));
    }
    function sync() {
        quiet = true;
        try {
            if (iso) picker.setDate(dateOf(iso));
            else picker.clearSelection();
        } finally {
            quiet = false;
        }
        show();
    }

    // Typing: a full DD-MM-YYYY date inside the limits is taken, an empty field clears, anything else is put back.
    input.addEventListener('change', (e) => {
        e.stopPropagation(); // the twin reports the change
        const text = native.get.call(input).trim();
        if (!text) {
            if (iso) {
                commit('');
                sync();
            }
            return;
        }
        const v = parseTyped(text);
        if (v && inRange(v)) {
            commit(v);
            sync();
        } else {
            show();
        }
    });

    Object.defineProperty(twin, 'value', {
        configurable: true,
        get: () => iso,
        set: (v) => {
            iso = ISO.test(v || '') ? v : '';
            sync();
        },
    });
    const limit = (which) => ({
        configurable: true,
        get: () => (which === 'min' ? min : max),
        set: (v) => {
            const d = ISO.test(v || '') ? v : '';
            if (which === 'min') min = d;
            else max = d;
            picker.setOptions({ minDate: min ? dateOf(min) : null, maxDate: max ? dateOf(max) : null, dropdowns: dropdowns() });
            sync();
        },
    });
    Object.defineProperty(twin, 'min', limit('min'));
    Object.defineProperty(twin, 'max', limit('max'));
    Object.defineProperty(twin, 'disabled', { configurable: true, get: () => input.disabled, set: (v) => (input.disabled = !!v) });
    twin.focus = () => input.focus();
    if (iso) sync();
}

// A calendar icon at the right of the field, as the date inputs had.
function addStyle() {
    if (document.getElementById('hr-date-style')) return;
    const s = document.createElement('style');
    s.id = 'hr-date-style';
    s.textContent =
        'input[data-hr-date]{padding-right:34px!important;cursor:pointer;background-repeat:no-repeat;background-position:right 10px center;background-size:16px;' +
        "background-image:url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2355616b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='3' y='4' width='18' height='18' rx='2'/%3E%3Cpath d='M16 2v4M8 2v4M3 10h18'/%3E%3C/svg%3E\")}";
    document.head.appendChild(s);
}

/** Puts Litepicker on every [data-hr-date] input inside root. Call before the report script reads its filters. */
export function hrDatePickers(root) {
    addStyle();
    (root || document).querySelectorAll('input[data-hr-date]').forEach(enhance);
}
