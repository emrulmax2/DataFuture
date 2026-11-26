/*
 * HR Portal › Reports › Eligibility Expiry, new version (user's eligibility-expiry-report design, 26 Nov 2026).
 *
 * One GET loads every employee with their latest eligibility record, days left and group already worked
 * out (App\Services\HR\EligibilityExpiry), for both documents: visa / work permit and passport / ID.
 * Go applies the filters; the tiles, the 12-month runway and the list (needs attention, a group or a month)
 * are drawn here, as EligibilityExpiry::build does for the PDF.
 *
 * The Old version has two tables with a name search each and no other filters. Go here puts the name in
 * both old searches and runs them (the Old pane is off screen, not display:none, so its tables lay out);
 * an old search fills Find a person here, shows that table's document and clears the filters the old page
 * does not have, so both versions list the same people.
 */
import { hrDatePickers } from './hr-report-datepicker';

(function () {
    'use strict';

    const root = document.querySelector('[data-eer]');
    if (!root) return;
    hrDatePickers(root); // Litepicker on the date filters (ISO values stay on data-*-f)

    const app = root.querySelector('[data-eer-app]');
    const form = root.querySelector('[data-eer-form]');
    const field = (k) => root.querySelector(`[data-eer-f="${k}"]`);
    const esc = (s) => String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    const MN = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const FILTERS = ['type', 'department', 'from', 'to', 'ethnicity', 'nationality', 'gender', 'status', 'q'];
    const ETH = JSON.parse(root.dataset.ethnicities || '[]');
    const B = [
        ['x', 'Expired', 'date has passed', 'var(--eer-x)'],
        ['s30', 'Within 30 days', 'act now', 'var(--eer-s30)'],
        ['s90', '31 to 90 days', 'start the check', 'var(--eer-s90)'],
        ['s180', '91 to 180 days', 'coming up', 'var(--eer-s180)'],
        ['ok', 'Later', 'more than 180 days', 'var(--eer-ok)'],
        ['nd', 'No date recorded', 'cannot be tracked', 'var(--eer-nd)'],
    ];
    const band = (k) => B.find((b) => b[0] === k);
    const KIND = {
        visa: { label: 'Visa and work permit', sub: 'visa and work permit', unit: 'permit', number: 'Permit number' },
        doc: { label: 'Passport and ID document', sub: 'passport and ID document', unit: 'document', number: 'Document' },
    };
    const LIM = 20;

    const blank = () => ({ type: '', department: '', from: '', to: '', ethnicity: '', nationality: '', gender: '', status: '', q: '' });
    let A = blank();
    const S = { kind: 'visa', band: '', ym: '', all: false };
    let STAFF = null;
    let TODAY = null;

    const fmt = (iso) => {
        const [y, m, d] = iso.split('-').map(Number);
        return `${d} ${MN[m - 1]} ${y}`;
    };
    function span(days) {
        const a = Math.abs(days);
        if (a < 60) return `${a} day${a === 1 ? '' : 's'}`;
        if (a < 730) return `${Math.round(a / 30.44)} months`;
        return `${(a / 365.25).toFixed(1).replace('.0', '')} years`;
    }
    function keep(r) {
        const q = A.q.trim().toLowerCase();
        if (q && !r.first.toLowerCase().includes(q) && !r.last.toLowerCase().includes(q) && !r.name.toLowerCase().includes(q)) return false;
        if (A.status === '1' && r.status !== 1) return false;
        if (A.status === '0' && r.status !== 0) return false;
        if (A.status === 'other' && (r.status === 0 || r.status === 1)) return false;
        if (A.type && String(r.type) !== A.type) return false;
        if (A.department && String(r.department) !== A.department) return false;
        if (A.ethnicity && String(r.ethnicityId) !== A.ethnicity) return false;
        if (A.nationality && String(r.nationalityId) !== A.nationality) return false;
        if (A.gender && String(r.genderId) !== A.gender) return false;
        const d = r[S.kind].date;
        if ((A.from || A.to) && (!d || (A.from && d < A.from) || (A.to && d > A.to))) return false;
        return true;
    }
    function fillOptions() {
        const count = (k) => STAFF.reduce((t, r) => ((t[r[k]] = (t[r[k]] || 0) + 1), t), {});
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
        const ids = Object.keys(names).sort((a, b) => names[a].localeCompare(names[b]));
        field('nationality').innerHTML = '<option value="">All nationalities</option>' + ids.map((id) => `<option value="${id}">${esc(names[id])} (${cn[id] || 0})</option>`).join('');
        field('nationality').value = nat;
    }
    const readForm = () => FILTERS.reduce((o, k) => ((o[k] = field(k).value), o), {});
    const writeForm = (f) => FILTERS.forEach((k) => (field(k).value = f[k]));
    const stale = () => FILTERS.some((k) => field(k).value !== A[k]);
    const profile = (r) => root.dataset.profileUrl.replace('__ID__', r.id);

    function pill(x) {
        if (x.band === 'nd') return '<span class="eer-pillx is-nd">No date</span>';
        const txt = x.days < 0 ? `Expired ${span(x.days)} ago` : x.days === 0 ? 'Expires today' : `In ${span(x.days)}`;
        return `<span class="eer-pillx${x.band === 's90' ? ' is-light' : ''}" style="--c:${band(x.band)[3]}">${txt}</span>`;
    }

    function render() {
        if (!STAFF) return;
        const K = S.kind;
        const list = STAFF.filter(keep);
        document.getElementById('eerSub').textContent = `London Churchill College · ${KIND[K].sub} expiry · ${list.length} people`;
        let h = stale() ? '<div class="eer-stale">The filters have changed. Press Go to apply them.</div>' : '';
        h +=
            '<div class="eer-sechead"><div class="eer-seg" role="group" aria-label="Document">' +
            Object.keys(KIND).map((k) => `<button type="button" data-kind="${k}" aria-pressed="${S.kind === k}">${KIND[k].label}</button>`).join('') +
            `</div><span class="eer-hint">${list.length} people</span></div>`;
        if (!list.length) {
            app.innerHTML = h + '<div class="eer-empty">Nobody matches these filters.</div>';
            return;
        }
        const cnt = {};
        list.forEach((r) => (cnt[r[K].band] = (cnt[r[K].band] || 0) + 1));
        h +=
            `<section class="eer-sec"><div class="eer-sechead"><h2>Where every ${KIND[K].unit} stands</h2><span class="eer-hint">Select a group to list it</span></div><div class="eer-tiles">` +
            B.map((b) => `<button type="button" class="eer-tile" data-band="${b[0]}" aria-pressed="${S.band === b[0]}" style="--c:${b[3]}"><b>${cnt[b[0]] || 0}</b><span>${b[1]}</span><small>${b[2]}</small></button>`).join('') +
            '</div></section>';

        /* the next 12 months */
        const months = [];
        for (let i = 0; i < 12; i++) {
            const m0 = new Date(TODAY.getFullYear(), TODAY.getMonth() + i, 1);
            const ym = `${m0.getFullYear()}-${String(m0.getMonth() + 1).padStart(2, '0')}`;
            months.push({ d: m0, key: `${m0.getFullYear()}-${m0.getMonth()}`, ps: list.filter((r) => r[K].date && r[K].days >= 0 && r[K].date.slice(0, 7) === ym) });
        }
        const inYear = months.reduce((a, m) => a + m.ps.length, 0);
        h +=
            `<section class="eer-sec"><div class="eer-sechead"><h2>The next 12 months</h2><span class="eer-legend"><span>Each dot is one ${KIND[K].unit} expiring that month</span>` +
            B.slice(1, 5).map((b) => `<span><i style="background:${b[3]}"></i>${b[1]}</span>`).join('') +
            '</span></div><div class="eer-run">' +
            months
                .map(
                    (m) =>
                        `<button type="button" class="eer-mo" data-ym="${m.key}" aria-pressed="${S.ym === m.key}" aria-label="${MN[m.d.getMonth()]} ${m.d.getFullYear()}: ${m.ps.length} expiring"${m.ps.length ? '' : ' disabled'}>` +
                        `<div class="eer-stackd">${m.ps.slice().sort((a, b) => a[K].days - b[K].days).map((r) => `<i style="--c:${band(r[K].band)[3]}"></i>`).join('')}</div>` +
                        `<b>${m.ps.length || ''}</b><span>${MN[m.d.getMonth()]} '${String(m.d.getFullYear()).slice(2)}</span></button>`
                )
                .join('') +
            `</div><div class="eer-hint">${inYear} ${KIND[K].unit}${inYear === 1 ? '' : 's'} expire in the next 12 months.</div></section>`;

        /* list */
        let shown;
        let title;
        if (S.ym) {
            const mm = months.find((m) => m.key === S.ym);
            shown = mm ? mm.ps.slice() : [];
            title = mm ? `Expiring in ${MN[mm.d.getMonth()]} ${mm.d.getFullYear()}` : 'Expiring';
        } else if (S.band) {
            shown = list.filter((r) => r[K].band === S.band);
            title = band(S.band)[1];
        } else {
            shown = list.filter((r) => ['s30', 's90', 'x'].includes(r[K].band));
            title = 'Needs attention';
        }
        const order = ['s30', 's90', 's180', 'ok', 'x', 'nd'];
        shown.sort((a, b) => {
            const c = order.indexOf(a[K].band) - order.indexOf(b[K].band);
            if (c) return c;
            if (a[K].band === 'x') return b[K].days - a[K].days || a.name.localeCompare(b.name);
            if (a[K].band === 'nd') return a.name.localeCompare(b.name);
            return a[K].days - b[K].days || a.name.localeCompare(b.name);
        });
        const onlyNd = shown.length && shown.every((r) => r[K].band === 'nd');
        h +=
            `<section class="eer-sec"><div class="eer-tools"><div><h2>${title}</h2><span class="eer-hint">${shown.length} ${KIND[K].unit}${shown.length === 1 ? '' : 's'}` +
            (S.band || S.ym ? ' · <button type="button" class="eer-lk" data-clear>Back to needs attention</button>' : ' · expiring within 90 days, then expired') +
            '</span></div></div>';
        if (!shown.length) {
            h += '<div class="eer-empty">Nothing in this group.</div>';
        } else if (onlyNd) {
            h +=
                (K === 'visa' ? '<p class="eer-hint" style="margin:0">The old report shows these as <b>January 01, 1970 · Expired</b>: an empty date, not an expired one.</p>' : '') +
                `<div class="eer-cols4">${shown.map((r) => `<span>${esc(r.name)}${r[K].num ? '' : ' · no number'}</span>`).join('')}</div>`;
        } else {
            h += `<div><div class="eer-xh"><span>Employee</span><span>${KIND[K].number}</span><span>Expiry date</span><span>Time left</span><span style="text-align:right">Status</span></div>`;
            let key = null;
            (S.all ? shown : shown.slice(0, LIM)).forEach((r) => {
                const x = r[K];
                const b = band(x.band);
                if (x.band !== key) {
                    key = x.band;
                    h += `<div class="eer-gh" style="--c:${b[3]}"><i></i>${b[1]}<span>${shown.filter((y) => y[K].band === key).length}</span></div>`;
                }
                const num = K === 'doc' ? [x.what, x.num].filter(Boolean).join(' · ') : x.num;
                h +=
                    `<div class="eer-xr"><span class="eer-nm"><a href="${esc(profile(r))}">${esc(r.name)}</a></span>` +
                    `<span class="eer-num">${num ? esc(num) : '<span class="eer-miss">Not recorded</span>'}</span>` +
                    `<span class="eer-dt">${x.date ? fmt(x.date) : '–'}</span>` +
                    `<span>${x.days !== null && x.days >= 0 && x.days <= 180 ? `<div class="eer-left" style="--c:${b[3]}"><i style="width:${Math.max(3, (100 * x.days) / 180)}%"></i></div>` : ''}</span>` +
                    `${pill(x)}</div>`;
            });
            h +=
                '</div>' +
                (shown.length > LIM
                    ? `<div class="eer-foot"><span>${S.all ? `Showing all ${shown.length}` : `Showing ${LIM} of ${shown.length}`}</span><button type="button" class="eer-btn" data-all>${S.all ? 'Show fewer' : `Show all ${shown.length}`}</button></div>`
                    : '');
        }
        h += '</section>';

        const nd = list.filter((r) => r[K].band === 'nd').length;
        const far = list.filter((r) => r[K].days !== null && r[K].days > 3650).length;
        const notes = [];
        if (K === 'visa' && nd) notes.push(`<li><b>${nd} people have no ${KIND[K].unit} expiry date.</b> The old report prints an empty date as expired on 1 January 1970; here they sit under No date recorded.</li>`);
        notes.push('<li>The report covers every employee record, as the old one does, including leavers. Use Status to narrow it to active staff.</li>');
        if (far) notes.push(`<li>${far} ${KIND[K].unit}${far === 1 ? '' : 's'} expire more than ten years from now, which may stand for no time limit.</li>`);
        notes.push('<li>The old version has a name search only: the other filters here apply to this version.</li>');
        h += `<ul class="eer-notes">${notes.join('')}</ul>`;
        app.innerHTML = h;
    }

    /* ---------- events ---------- */
    app.addEventListener('click', (e) => {
        let b;
        if ((b = e.target.closest('[data-kind]'))) {
            Object.assign(S, { kind: b.dataset.kind, band: '', ym: '', all: false });
            render();
            const f = app.querySelector(`[data-kind="${S.kind}"]`);
            if (f) f.focus();
        } else if ((b = e.target.closest('[data-band]'))) {
            const k = b.dataset.band;
            Object.assign(S, { band: S.band === k ? '' : k, ym: '', all: false });
            render();
            const f = app.querySelector(`[data-band="${k}"]`);
            if (f) f.focus();
        } else if ((b = e.target.closest('[data-ym]'))) {
            const y = b.dataset.ym;
            Object.assign(S, { ym: S.ym === y ? '' : y, band: '', all: false });
            render();
            const g = app.querySelector(`[data-ym="${y}"]`);
            if (g) g.focus();
        } else if (e.target.closest('[data-clear]')) {
            Object.assign(S, { band: '', ym: '', all: false });
            render();
        } else if (e.target.closest('[data-all]')) {
            S.all = !S.all;
            render();
            const a = app.querySelector('[data-all]');
            if (a) a.focus();
        }
    });
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        A = readForm();
        Object.assign(S, { band: '', ym: '', all: false });
        render();
        toOld();
    });
    FILTERS.forEach((k) => field(k).addEventListener(k === 'q' ? 'input' : 'change', () => render()));
    root.querySelector('[data-eer-reset]').addEventListener('click', () => {
        A = blank();
        Object.assign(S, { band: '', ym: '', all: false });
        writeForm(A);
        render();
        toOld();
    });

    /* ---------- downloads ---------- */
    function toast(message) {
        let t = document.querySelector('.eer-toast');
        if (!t) {
            t = document.createElement('div');
            t.className = 'eer-toast';
            t.setAttribute('role', 'status');
            document.body.appendChild(t);
        }
        t.textContent = message;
        t.classList.add('show');
        clearTimeout(toast.timer);
        toast.timer = setTimeout(() => t.classList.remove('show'), 2600);
    }
    root.querySelectorAll('[data-eer-export]').forEach((btn) =>
        btn.addEventListener('click', () => {
            if (btn.classList.contains('is-busy')) return;
            const pdf = btn.dataset.eerExport === 'pdf';
            const params = { ...A, kind: S.kind, band: S.band, ym: S.ym };
            btn.classList.add('is-busy');
            btn.disabled = true;
            axios({ method: 'get', url: pdf ? root.dataset.pdfUrl : root.dataset.excelUrl, params, responseType: 'blob' })
                .then((response) => {
                    const m = /filename="?([^";]+)"?/i.exec(response.headers['content-disposition'] || '');
                    const href = window.URL.createObjectURL(new Blob([response.data]));
                    const a = document.createElement('a');
                    a.href = href;
                    a.setAttribute('download', m ? m[1] : pdf ? 'Eligibility_Expiry.pdf' : 'Eligibility_Expiry.xlsx');
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
        })
    );

    /* ---------- New / Old version ---------- */
    root.querySelectorAll('[data-eer-version]').forEach((button) =>
        button.addEventListener('click', () => {
            const v = button.dataset.eerVersion;
            root.querySelectorAll('[data-eer-version]').forEach((b) => b.setAttribute('aria-pressed', String(b === button)));
            root.querySelectorAll('[data-eer-pane]').forEach((pane) => {
                const off = pane.dataset.eerPane !== v;
                pane.classList.toggle('is-off', off);
                pane.setAttribute('aria-hidden', String(off));
            });
            root.querySelectorAll('[data-eer-new-only]').forEach((el) => (el.hidden = v !== 'new'));
            window.dispatchEvent(new Event('resize')); // the old tables redraw on resize
        })
    );

    // The old page: a name search on each table (Visa VEXR, Passport PEXR).
    const OLD = { visa: 'VEXR', doc: 'PEXR' };
    let mirroring = false;
    function toOld() {
        mirroring = true;
        try {
            Object.values(OLD).forEach((s) => {
                const input = document.getElementById(`query-${s}`);
                const go = document.getElementById(`tabulator-html-filter-go-${s}`);
                if (!input || !go) return;
                input.value = A.q.trim();
                $(go).trigger('click');
            });
        } finally {
            mirroring = false;
        }
    }
    function fromOld(kind, value) {
        A = { ...blank(), q: value };
        Object.assign(S, { kind, band: '', ym: '', all: false });
        writeForm(A);
        render();
    }
    Object.entries(OLD).forEach(([kind, s]) => {
        const go = document.getElementById(`tabulator-html-filter-go-${s}`);
        const reset = document.getElementById(`tabulator-html-filter-reset-${s}`);
        const input = document.getElementById(`query-${s}`);
        if (go) go.addEventListener('click', () => !mirroring && fromOld(kind, (input && input.value.trim()) || ''));
        if (reset) reset.addEventListener('click', () => !mirroring && setTimeout(() => fromOld(kind, (input && input.value.trim()) || ''), 0));
    });

    /* ---------- load ---------- */
    writeForm(A);
    axios
        .get(root.dataset.dataUrl)
        .then(({ data }) => {
            const [y, m, d] = String(data.today).split('-').map(Number);
            TODAY = new Date(y, m - 1, d);
            STAFF = data.staff || [];
            fillOptions();
            render();
        })
        .catch(() => {
            app.innerHTML = '<div class="eer-empty">The eligibility records could not be loaded. Please reload the page.</div>';
        });
})();
