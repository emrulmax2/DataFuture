/*
 * HR Portal › Reports › Employee Record Card, new version (user's employee-record-card design, 26 Nov 2026).
 *
 * One GET loads every active and inactive staff member's card (App\Services\HR\RecordCardReport). Go
 * applies the filters (old rules); Find a person narrows the cards as you type. Each card opens to its
 * full record and can be downloaded on its own; Download PDF gives one A4 page per card shown.
 *
 * The Old version sits beside it: Go here fills the old form and runs its Go (the Old pane is off screen,
 * not display:none); the old Go or Reset fills this form and applies it.
 */
import { hrDatePickers } from './hr-report-datepicker';

(function () {
    'use strict';

    const root = document.querySelector('[data-erc]');
    if (!root) return;
    hrDatePickers(root); // Litepicker on the date filters (ISO values stay on data-*-f)

    const app = root.querySelector('[data-erc-app]');
    const form = root.querySelector('[data-erc-form]');
    const field = (k) => root.querySelector(`[data-erc-f="${k}"]`);
    const esc = (s) => String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    const MN = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    const date = (iso) => {
        const [y, m, d] = iso.split('-').map(Number);
        return `${d} ${MN[m - 1]} ${y}`;
    };
    const FILTERS = ['type', 'department', 'from', 'to', 'ethnicity', 'nationality', 'gender', 'status'];
    const KEY = ['title', 'forename', 'surname', 'dob', 'gender', 'ethnicity', 'nationality', 'ni', 'works', 'jobTitle', 'department', 'started', 'address', 'mobile', 'email', 'eName', 'eMob', 'disability'];
    const ETH = JSON.parse(root.dataset.ethnicities || '[]');

    const blank = () => ({ type: '', department: '', from: '', to: '', ethnicity: '', nationality: '', gender: '', status: '1' });
    let A = blank(); // applied filters
    let Q = '';
    const OPEN = {};
    let STAFF = null;

    function keep(r) {
        if (A.status !== '2' && String(r.status) !== A.status) return false;
        if (A.type && String(r.typeId) !== A.type) return false;
        if (A.department && String(r.departmentId) !== A.department) return false;
        if (A.from && !(r.started && r.started <= A.from)) return false;
        if (A.to && !(r.ended && r.ended >= A.to)) return false;
        if (A.ethnicity && String(r.ethnicityId) !== A.ethnicity) return false;
        if (A.nationality && String(r.nationalityId) !== A.nationality) return false;
        if (A.gender && String(r.genderId) !== A.gender) return false;
        const q = Q.trim().toLowerCase();
        return !q || `${r.title} ${r.forename} ${r.surname} ${r.works}`.toLowerCase().includes(q);
    }
    function fillOptions() {
        const status = field('status').value;
        const pool = STAFF.filter((r) => status === '2' || String(r.status) === status);
        const count = (k) => pool.reduce((t, r) => ((t[r[k]] = (t[r[k]] || 0) + 1), t), {});
        const ce = count('ethnicityId');
        const eth = field('ethnicity').value;
        field('ethnicity').innerHTML =
            '<option value="">All ethnicities</option>' +
            ETH.filter((e) => e[2] || ce[e[0]] || String(e[0]) === eth)
                .sort((a, b) => a[1].localeCompare(b[1]))
                .map((e) => `<option value="${e[0]}">${esc(e[1])} (${ce[e[0]] || 0})</option>`)
                .join('');
        field('ethnicity').value = eth;
        const cn = count('nationalityId');
        const nat = field('nationality').value;
        const names = {};
        STAFF.forEach((r) => r.nationalityId && (names[r.nationalityId] = r.nationality));
        const ids = Object.keys(names).filter((id) => cn[id] || id === nat).sort((a, b) => names[a].localeCompare(names[b]));
        if (nat && !names[nat]) ids.push(nat);
        field('nationality').innerHTML = '<option value="">All nationalities</option>' + ids.map((id) => `<option value="${id}">${esc(names[id] || `Country #${id}`)} (${cn[id] || 0})</option>`).join('');
        field('nationality').value = nat;
    }
    const readForm = () => FILTERS.reduce((o, k) => ((o[k] = field(k).value), o), {});
    const writeForm = (f) => FILTERS.forEach((k) => (field(k).value = f[k]));
    const stale = () => FILTERS.some((k) => field(k).value !== A[k]);

    /* ---------- drawing ---------- */
    function row(label, v, kind) {
        // kind: 'key' (missing = warning), 'opt' (missing = grey), or the text to show when empty
        if (v) return `<div><dt>${label}</dt><dd>${esc(v)}</dd></div>`;
        const text = kind && kind !== 'key' && kind !== 'opt' ? kind : 'Not recorded';
        return `<div><dt>${label}</dt><dd class="${kind === 'key' ? 'is-miss' : 'is-none'}">${text}</dd></div>`;
    }
    function card(r) {
        const name = [r.title, r.forename, r.surname].filter(Boolean).join(' ') || 'Unnamed';
        const init = ((r.forename || '?')[0] + (r.surname || '')[0] || '').toUpperCase();
        const active = r.status === 1;
        const open = !!OPEN[r.id];
        const done = KEY.length - r.missing.length;
        let h =
            `<article class="erc-sheet${open ? ' is-open' : ''}"><button type="button" class="erc-hero" data-open="${r.id}" aria-expanded="${open}">` +
            (r.photo ? `<div class="erc-av has-photo"><img src="${esc(r.photo)}" alt="Photo of ${esc(name)}" loading="lazy"></div>` : `<div class="erc-av" aria-hidden="true">${esc(init)}</div>`) +
            `<div class="erc-who"><h2><span>${esc(name)}</span><span class="erc-pill${active ? '' : ' is-off'}">${active ? 'Active' : 'Inactive'}</span></h2>` +
            `<p>${r.jobTitle ? esc(r.jobTitle) + (r.department ? ` · ${esc(r.department)}` : '') : 'Job title not recorded'}</p></div>` +
            '<div class="erc-facts">' +
            `<div><span>Works no.</span><b>${r.works ? esc(r.works) : '–'}</b></div>` +
            `<div><span>Service</span><b>${r.service ? esc(r.service) : '–'}</b></div>` +
            `<div><span>Details</span><b class="${r.missing.length ? 'is-gap' : ''}">${r.missing.length ? `${r.missing.length} missing` : 'Complete'}</b></div></div>` +
            '<svg class="erc-chev" aria-hidden="true" width="14" height="14" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2.5 4.5 6 8l3.5-3.5"/></svg></button>';
        if (open) {
            const profile = root.dataset.profileUrl.replace('__ID__', r.id);
            h +=
                '<div class="erc-body">' +
                `<div class="erc-comp"><span><b>${done} of ${KEY.length}</b> key details recorded</span><div class="erc-meter"><i style="width:${(100 * done) / KEY.length}%"></i></div>` +
                `<span class="erc-acts"><a class="erc-lk" href="${esc(profile)}">Open profile</a><button type="button" class="erc-lk" data-card-pdf="${r.id}">Download this card</button></span></div>` +
                '<div class="erc-grid"><section><h3>Personal details</h3><dl>' +
                row('Title', r.title, 'key') + row('Forename', r.forename, 'key') + row('Surname', r.surname, 'key') +
                row('Date of birth', r.dob ? `${date(r.dob)} (age ${r.age})` : '', 'key') + row('Gender', r.gender, 'key') +
                row('Ethnic origin', r.ethnicity, 'key') + row('Nationality', r.nationality, 'key') + row('NI number', r.ni, 'key') +
                '</dl></section><section><h3>Employment details</h3><dl>' +
                row('Company', 'London Churchill College') + row('Works number', r.works, 'key') + row('Job title', r.jobTitle, 'key') +
                row('Department', r.department, 'key') + row('Started on', r.started ? date(r.started) : '', 'key') +
                row('Ended on', r.ended ? date(r.ended) : '', active ? 'Still employed' : 'opt') + row('Current status', active ? 'Active' : 'Inactive') +
                '</dl></section><section><h3>Contact information</h3><dl>' +
                row('Address', r.address, 'key') + row('Telephone', r.telephone, 'opt') + row('Mobile', r.mobile, 'key') + row('Email', r.email, 'key') +
                '</dl></section><section class="is-em"><h3>Emergency contact</h3><dl>' +
                row('Name', r.eName, 'key') + row('Relationship', r.eRel, 'opt') + row('Telephone', r.eTel, 'opt') + row('Mobile', r.eMob, 'key') + row('Email', r.eEmail, 'opt') +
                '</dl></section><section class="is-wide"><h3>Other details</h3><dl>' +
                row('Disabled', r.disability, 'key') + row('Car registration', r.carReg, 'opt') +
                '</dl></section></div></div>';
        }
        return h + '</article>';
    }
    function render() {
        if (!STAFF) return;
        const list = STAFF.filter(keep);
        const statusWord = A.status === '1' ? 'active ' : A.status === '0' ? 'inactive ' : '';
        document.getElementById('ercSub').textContent = `London Churchill College · ${STAFF.filter((r) => A.status === '2' || String(r.status) === A.status).length} ${statusWord}staff`;
        const allOpen = list.length && list.every((r) => OPEN[r.id]);
        let h =
            (stale() ? '<div class="erc-stale">The filters have changed. Press Go to apply them.</div>' : '') +
            `<div class="erc-tools"><div><h2>${list.length} record card${list.length === 1 ? '' : 's'}</h2>` +
            `<span class="erc-hint">${list.filter((r) => !r.missing.length).length} complete · ${list.filter((r) => r.missing.length).length} with key details missing</span></div>` +
            (list.length ? `<button type="button" class="erc-lk" data-toggle-all>${allOpen ? 'Collapse all' : 'Expand all'}</button>` : '') +
            '</div>';
        h += list.length ? `<div class="erc-list">${list.map(card).join('')}</div>` : '<div class="erc-empty">No employees match these filters.</div>';
        app.innerHTML = h;
    }

    /* ---------- downloads ---------- */
    function toast(message) {
        let t = document.querySelector('.erc-toast');
        if (!t) {
            t = document.createElement('div');
            t.className = 'erc-toast';
            t.setAttribute('role', 'status');
            document.body.appendChild(t);
        }
        t.textContent = message;
        t.classList.add('show');
        clearTimeout(toast.timer);
        toast.timer = setTimeout(() => t.classList.remove('show'), 2600);
    }
    function download(btn, url, params, fallback) {
        if (btn.classList.contains('is-busy')) return;
        btn.classList.add('is-busy');
        btn.disabled = true;
        axios({ method: 'get', url, params, responseType: 'blob' })
            .then((response) => {
                const match = /filename="?([^";]+)"?/i.exec(response.headers['content-disposition'] || '');
                const href = window.URL.createObjectURL(new Blob([response.data]));
                const a = document.createElement('a');
                a.href = href;
                a.setAttribute('download', match ? match[1] : fallback);
                document.body.appendChild(a);
                a.click();
                a.remove();
                setTimeout(() => window.URL.revokeObjectURL(href), 1000);
            })
            .catch(() => toast('The download failed. Please try again.'))
            .finally(() => {
                btn.classList.remove('is-busy');
                btn.disabled = false;
            });
    }
    root.querySelectorAll('[data-erc-export]').forEach((btn) =>
        btn.addEventListener('click', () => {
            const pdf = btn.dataset.ercExport === 'pdf';
            download(btn, pdf ? root.dataset.pdfUrl : root.dataset.excelUrl, { ...A, q: Q.trim() }, pdf ? 'Employee_Record_Cards.pdf' : 'Employee_Record_Cards.xlsx');
        })
    );

    /* ---------- events ---------- */
    app.addEventListener('click', (e) => {
        let b;
        if ((b = e.target.closest('[data-open]'))) {
            const id = b.dataset.open;
            OPEN[id] = !OPEN[id];
            render();
            const f = app.querySelector(`[data-open="${id}"]`);
            if (f) f.focus();
        } else if (e.target.closest('[data-toggle-all]')) {
            const list = STAFF.filter(keep);
            const open = !list.every((r) => OPEN[r.id]);
            list.forEach((r) => (OPEN[r.id] = open));
            render();
            const t = app.querySelector('[data-toggle-all]');
            if (t) t.focus();
        } else if ((b = e.target.closest('[data-card-pdf]'))) {
            download(b, root.dataset.pdfUrl, { ids: b.dataset.cardPdf }, 'Record_Card.pdf');
        }
    });
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        A = readForm();
        render();
        toOld();
    });
    FILTERS.forEach((k) =>
        field(k).addEventListener('change', () => {
            if (k === 'status') fillOptions();
            render(); // shows the "press Go" note
        })
    );
    field('q').addEventListener('input', () => {
        Q = field('q').value;
        render();
    });
    root.querySelector('[data-erc-reset]').addEventListener('click', () => {
        A = blank();
        Q = '';
        writeForm(A);
        field('q').value = '';
        fillOptions();
        render();
        toOld();
    });

    /* ---------- New / Old version ---------- */
    root.querySelectorAll('[data-erc-version]').forEach((button) =>
        button.addEventListener('click', () => {
            const v = button.dataset.ercVersion;
            root.querySelectorAll('[data-erc-version]').forEach((b) => b.setAttribute('aria-pressed', String(b === button)));
            root.querySelectorAll('[data-erc-pane]').forEach((pane) => {
                const off = pane.dataset.ercPane !== v;
                pane.classList.toggle('is-off', off);
                pane.setAttribute('aria-hidden', String(off));
            });
            root.querySelectorAll('[data-erc-new-only]').forEach((el) => (el.hidden = v !== 'new'));
            window.dispatchEvent(new Event('resize'));
        })
    );

    const OLD = {
        type: '#employee_work_type_id-recordcard', department: '#department_id-recordcard', from: '#startdate-recordcard', to: '#enddate-recordcard',
        ethnicity: '#ethnicity-recordcard', nationality: '#nationality-recordcard', gender: '#gender-recordcard', status: '#status_id-recordcard',
    };
    const goBtn = document.getElementById('tabulator-html-filter-go-RCD');
    const resetBtn = document.getElementById('tabulator-html-filter-reset-RCD');
    let mirroring = false;
    const oldValue = (sel) => {
        const el = document.querySelector(sel);
        return el ? String(el.tomselect ? el.tomselect.getValue() : el.value) : '';
    };
    function setOld(k, v) {
        const el = document.querySelector(OLD[k]);
        if (!el) return;
        // The old ethnicity list holds active values only; add one it lacks so the same staff are picked.
        if (el.tagName === 'SELECT' && v && !el.querySelector(`option[value="${CSS.escape(v)}"]`)) {
            const src = field(k).querySelector(`option[value="${CSS.escape(v)}"]`);
            el.appendChild(new Option(src ? src.textContent.replace(/ \(\d+\)$/, '') : v, v));
        }
        if (el.tomselect) el.tomselect.setValue(v, true);
        else el.value = v;
    }
    function toOld() {
        if (!goBtn) return;
        FILTERS.forEach((k) => setOld(k, A[k]));
        mirroring = true;
        try {
            $(goBtn).trigger('click');
        } finally {
            mirroring = false;
        }
    }
    function fromOld() {
        const f = {};
        FILTERS.forEach((k) => (f[k] = oldValue(OLD[k])));
        if (!['0', '1', '2'].includes(f.status)) f.status = '1';
        ['from', 'to'].forEach((k) => (f[k] = /^\d{4}-\d{2}-\d{2}$/.test(f[k]) ? f[k] : ''));
        A = f;
        Q = '';
        field('q').value = '';
        writeForm(A);
        if (STAFF) fillOptions();
        render();
    }
    if (goBtn) goBtn.addEventListener('click', () => !mirroring && fromOld());
    if (resetBtn) resetBtn.addEventListener('click', () => !mirroring && setTimeout(fromOld, 0));

    /* ---------- load ---------- */
    writeForm(A);
    axios
        .get(root.dataset.dataUrl)
        .then(({ data }) => {
            STAFF = data.staff || [];
            fillOptions();
            render();
        })
        .catch(() => {
            app.innerHTML = '<div class="erc-empty">The record cards could not be loaded. Please reload the page.</div>';
        });
})();
