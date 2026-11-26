/*
 * HR Portal › Reports › Outstanding Holiday, new version (user's outstanding-holiday-report design, 26 Nov 2026).
 *
 * Search loads everyone with a working pattern in the range, with allocation, taken, balance and value
 * worked out by the old report's own methods (App\Services\HR\OutstandingHoliday). The headline, cards,
 * departments and the employee list are drawn here; Download Excel sends the same view to the server.
 *
 * Old version: a Search here fills the old form (holiday year, dates, status) and runs its search while
 * the Old pane is hidden; an old Search fills this form and runs it; an old Reset resets this form.
 */
import { hrDatePickers } from './hr-report-datepicker';

(function () {
    'use strict';

    const root = document.querySelector('[data-eoh]');
    if (!root) return;
    hrDatePickers(root); // Litepicker on the date filters (ISO values stay on data-*-f)

    const app = root.querySelector('[data-eoh-app]');
    const form = root.querySelector('[data-eoh-form]');
    const field = (k) => root.querySelector(`[data-eoh-f="${k}"]`);
    const esc = (s) => String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    const MN = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const PER = 20;
    const KEYS = ['year', 'from', 'to', 'status'];
    const ATT = [
        ['over', 'Taken more than allocated', 'a negative balance', (p) => p.b < 0, true],
        ['left', 'Inactive staff still owed', 'holiday pay due to people who have left', (p) => !p.active && p.b > 0, true],
        ['none', 'Nothing taken yet', 'have an allocation but no holiday taken', (p) => p.a > 0 && p.t === 0, false],
        ['noalloc', 'No allocation', 'nothing allocated for this period', (p) => p.a === 0, false],
    ];

    let D = null; // {filters, today, staff}
    let A = null; // applied form values
    const S = { att: '', dept: null, q: '', sort: 'screen', page: 0 };

    const fmt = (s) => {
        const p = s.split('-');
        return `${+p[2]} ${MN[+p[1] - 1]} ${p[0]}`;
    };
    const dmy = (s) => (s ? s.split('-').reverse().join('-') : '');
    const isoOf = (s) => (/^\d{2}-\d{2}-\d{4}$/.test(s || '') ? s.split('-').reverse().join('-') : '');
    const today = () => {
        const d = new Date();
        return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
    };
    const hm = (m) => {
        const a = Math.abs(m);
        return `${m < 0 ? '−' : ''}${Math.floor(a / 60)}h ${String(a % 60).padStart(2, '0')}m`;
    };
    const hrs = (m) => Math.round(m / 60).toLocaleString('en-GB');
    const gbp = (v, dp) =>
        `${v < 0 ? '−' : ''}£${Math.abs(v).toLocaleString('en-GB', { minimumFractionDigits: dp === 0 ? 0 : 2, maximumFractionDigits: dp === 0 ? 0 : 2 })}`;
    const pc = (a, b) => (b ? `${Math.round((100 * a) / b)}%` : '–');
    const readForm = () => KEYS.reduce((o, k) => ((o[k] = field(k).value), o), {});
    const stale = () => A && KEYS.some((k) => field(k).value !== A[k]);

    /* ---------- the form ---------- */
    function yearBounds() {
        const o = field('year').selectedOptions[0];
        return o ? [o.dataset.start, o.dataset.end] : ['', ''];
    }
    function fitDates(reset) {
        const [s, e] = yearBounds();
        ['from', 'to'].forEach((k) => {
            field(k).min = s;
            field(k).max = e;
        });
        if (reset) {
            const t = today();
            field('from').value = s;
            field('to').value = t < s ? s : t > e ? e : t;
        }
    }
    function defaults() {
        const sel = field('year');
        const def = [...sel.options].find((o) => o.defaultSelected) || sel.options[0];
        if (def) sel.value = def.value;
        field('status').value = '2';
        fitDates(true);
    }
    const note = (msg) => {
        const p = root.querySelector('[data-eoh-formnote]');
        p.textContent = msg || '';
        p.hidden = !msg;
    };
    function valid() {
        const f = readForm();
        if (!f.year) return note('Choose a holiday year.'), false;
        if (!f.from || !f.to) return note('Choose the From and To dates.'), (f.from ? field('to') : field('from')).focus(), false;
        if (f.to < f.from) return note('To must be on or after From.'), field('to').focus(), false;
        note('');
        return true;
    }

    /* ---------- drawing ---------- */
    const base = () => D.staff;

    function render() {
        if (!D) return;
        const list = base();
        const N = list.length;
        const f = D.filters;
        let h = stale() ? '<div class="eoh-stale">The filters have changed. Select Search to update the report.</div>' : '';
        const statusTxt = f.status === '2' ? '' : ` · ${f.status === '1' ? 'Active' : 'Inactive'} only`;
        document.getElementById('eohSub').textContent = `London Churchill College · holiday allocated, taken and still owed · ${fmt(f.from)} to ${fmt(f.to)}`;
        if (!N) {
            app.innerHTML = h + '<div class="eoh-empty">Nobody with a working pattern matches these filters.</div>';
            return;
        }
        let Al = 0;
        let T = 0;
        let V = 0;
        let pos = 0;
        let neg = 0;
        list.forEach((p) => {
            Al += p.a;
            T += p.t;
            V += p.v;
            if (p.v > 0) pos += p.v;
            else neg += p.v;
        });
        h +=
            `<div class="eoh-period"><b>${N} employees</b> · holiday year ${esc(f.yearName)} · ${fmt(f.from)} to ${fmt(f.to)}${statusTxt}</div>` +
            '<div class="eoh-hero">' +
            `<div class="eoh-owed"><span>Value of holiday still owed</span><b>${gbp(V, 0)}</b><small>${hrs(Al - T)} hours not yet taken · ${gbp(pos, 0)} owed to staff, less ${gbp(-neg, 0)} over-taken</small></div>` +
            `<div class="eoh-used"><div class="big"><b>${pc(T, Al)}</b><span>of allocated holiday has been taken</span></div>` +
            `<div class="eoh-track2" role="img" aria-label="${pc(T, Al)} taken"><i style="width:${Al ? Math.min(100, (100 * T) / Al) : 0}%"></i></div>` +
            `<div class="eoh-kv2"><div><b>${hrs(Al)} h</b>allocated</div><div><b>${hrs(T)} h</b>taken</div><div><b>${hrs(Al - T)} h</b>balance</div></div></div></div>`;

        h +=
            '<section class="eoh-sec"><div class="eoh-sechead"><h2>Worth a look</h2><span class="eoh-hint">Select a card to list those employees</span></div><div class="eoh-att">' +
            ATT.map((c) => {
                const m = list.filter(c[3]);
                const val = m.reduce((s, p) => s + p.v, 0);
                return (
                    `<button type="button" class="eoh-ac${c[4] && m.length ? ' is-w' : ''}" data-att="${c[0]}" aria-pressed="${S.att === c[0]}">` +
                    `<b>${m.length}</b><span>${c[1]}</span><small>${c[0] === 'over' || c[0] === 'left' ? `${gbp(val, 0)} · ` : ''}${c[2]}</small></button>`
                );
            }).join('') +
            '</div></section>';

        const dep = {};
        list.forEach((p) => {
            const o = dep[p.dept] || (dep[p.dept] = { c: 0, a: 0, t: 0, v: 0 });
            o.c++;
            o.a += p.a;
            o.t += p.t;
            o.v += p.v;
        });
        h +=
            '<section class="eoh-sec"><div class="eoh-sechead"><h2>By department</h2><span class="eoh-hint">Largest balance first · select one to list its staff</span></div><div>' +
            '<div class="eoh-dh"><span>Department</span><span class="eoh-r">Staff</span><span>Allocation taken</span><span class="eoh-r">Taken</span><span class="eoh-r">Balance hours</span><span class="eoh-r">Balance</span></div>' +
            Object.keys(dep)
                .sort((x, y) => dep[y].v - dep[x].v)
                .map((k) => {
                    const o = dep[k];
                    return (
                        `<button type="button" class="eoh-dr" data-dept="${esc(k)}" aria-pressed="${S.dept === k}"><span>${esc(k || 'No department')}</span><span class="eoh-r">${o.c}</span>` +
                        `<span class="eoh-meter${o.t > o.a ? ' is-over' : ''}"><i style="width:${o.a ? Math.min(100, (100 * o.t) / o.a) : 0}%"></i></span><span class="eoh-r">${pc(o.t, o.a)}</span>` +
                        `<span class="eoh-r">${hrs(o.a - o.t)} h</span><span class="eoh-r${o.v < 0 ? ' eoh-neg' : ''}" style="font-weight:700">${gbp(o.v, 0)}</span></button>`
                    );
                })
                .join('') +
            '</div></section>';

        h +=
            '<section class="eoh-sec"><div class="eoh-sechead"><h2>Employees</h2><span class="eoh-hint" data-eoh-filt></span></div><div class="eoh-tools">' +
            `<label class="eoh-field"><span>Find an employee</span><input class="eoh-ctl" type="search" data-eoh-q placeholder="Employee or department" value="${esc(S.q)}" autocomplete="off"></label>` +
            '<label class="eoh-field" style="flex-basis:220px"><span>Sort by</span><select class="eoh-ctl" data-eoh-sort><option value="screen">Name order</option><option value="vhi">Largest balance first</option><option value="vlo">Most over-taken first</option><option value="used">Least used first</option></select></label>' +
            '</div><div data-eoh-tbl></div></section>';

        const left = list.filter(ATT[1][3]);
        const over = list.filter(ATT[0][3]);
        const noRate = list.filter((p) => !p.rate && p.b !== 0).length;
        const notes = [];
        if (left.length) notes.push(`<li><b>${left.length} inactive employee${left.length === 1 ? '' : 's'} still ${left.length === 1 ? 'has' : 'have'} a positive balance, worth ${gbp(left.reduce((s, p) => s + p.v, 0), 0)}.</b> Check whether that holiday pay has been settled.</li>`);
        if (over.length) notes.push(`<li><b>${over.length} employee${over.length === 1 ? ' has' : 's have'} taken more than ${over.length === 1 ? 'their' : 'their'} allocation.</b> Their balances are negative and reduce the total.</li>`);
        notes.push('<li>Allocation is the holiday earned between From and To on each working pattern, plus or minus any adjustment for the year, not the full year\'s entitlement.</li>');
        notes.push('<li>Taken includes approved holiday booked for later dates and bank holidays booked automatically, as on the old version.</li>');
        if (noRate) notes.push(`<li>${noRate} employee${noRate === 1 ? ' has' : 's have'} a balance but no hourly rate on the working pattern, so the value shows £0.</li>`);
        h += `<ul class="eoh-notes">${notes.join('')}</ul>`;
        app.innerHTML = h;
        app.querySelector('[data-eoh-sort]').value = S.sort;
        draw();
    }

    function filtered() {
        const q = S.q.trim().toLowerCase();
        const att = ATT.find((c) => c[0] === S.att);
        const list = base().filter((p) => (!att || att[3](p)) && (S.dept === null || p.dept === S.dept) && (!q || `${p.name} ${p.dept}`.toLowerCase().includes(q)));
        if (S.sort === 'vhi') list.sort((a, b) => b.v - a.v);
        if (S.sort === 'vlo') list.sort((a, b) => a.v - b.v);
        if (S.sort === 'used') list.sort((a, b) => (a.a ? a.t / a.a : 9) - (b.a ? b.t / b.a : 9) || b.b - a.b);
        return { list, att };
    }

    function draw() {
        const { list, att } = filtered();
        app.querySelector('[data-eoh-filt]').innerHTML =
            (att ? `${att[1]} <button type="button" class="eoh-lk" data-clear="att" aria-label="Clear ${att[1]}">×</button>` : '') +
            (att && S.dept !== null ? ' · ' : '') +
            (S.dept !== null ? `${esc(S.dept || 'No department')} <button type="button" class="eoh-lk" data-clear="dept" aria-label="Clear department">×</button>` : '');
        const pages = Math.max(1, Math.ceil(list.length / PER));
        S.page = Math.min(Math.max(0, S.page), pages - 1);
        const part = list.slice(S.page * PER, S.page * PER + PER);
        let h = '<div class="eoh-eh"><span>Employee</span><span>Allocation used</span><span class="eoh-r">Allocated</span><span class="eoh-r">Taken</span><span class="eoh-r">Balance hours</span><span class="eoh-r">Balance</span></div>';
        if (!part.length) h += '<div class="eoh-empty" style="margin-top:10px">No employees match.</div>';
        h += '<div>';
        part.forEach((p) => {
            const u = p.a ? p.t / p.a : 0;
            const over = p.b < 0;
            h +=
                `<div class="eoh-er"><div class="nm"><b><a href="${esc(root.dataset.profileUrl.replace('__ID__', p.id))}">${esc(p.name)}</a></b>` +
                `<small>${esc(p.dept || 'No department')}${p.active ? '' : ' · <span class="off">Inactive</span>'}</small></div>` +
                '<div class="eoh-use">' +
                (p.a
                    ? `<div class="eoh-meter${over ? ' is-over' : ''}"><i style="width:${Math.min(100, 100 * u)}%"></i></div><span class="${over ? 'eoh-neg' : ''}">${Math.round(100 * u)}%</span>`
                    : '<span style="width:auto;text-align:left">No allocation</span>') +
                '</div>' +
                `<span class="eoh-num eoh-r${p.a ? '' : ' z'}">${p.a ? hm(p.a) : '–'}</span>` +
                `<span class="eoh-num eoh-r${p.t ? '' : ' z'}" title="${p.t ? `Taken ${hm(p.taken)} · booked ${hm(p.booked)} · bank holidays ${hm(p.bank)}` : ''}">${p.t ? hm(p.t) : '–'}</span>` +
                `<span class="eoh-num eoh-r${over ? ' eoh-neg' : p.b ? '' : ' z'}">${p.b ? hm(p.b) : '–'}</span>` +
                `<span class="eoh-num eoh-tot eoh-r${p.v < 0 ? ' eoh-neg' : p.v ? '' : ' z'}">${p.v ? gbp(p.v) : '–'}</span></div>`;
        });
        h +=
            '</div>' +
            `<div class="eoh-foot" style="margin-top:10px"><span>${list.length ? `Showing ${S.page * PER + 1} to ${S.page * PER + part.length} of ${list.length}` : '0 employees'}</span>` +
            (pages > 1
                ? `<span class="eoh-pager"><button type="button" class="eoh-btn" data-page="-1"${S.page ? '' : ' disabled'}>Previous</button><span>Page ${S.page + 1} of ${pages}</span><button type="button" class="eoh-btn" data-page="1"${S.page < pages - 1 ? '' : ' disabled'}>Next</button></span>`
                : '') +
            '</div>';
        app.querySelector('[data-eoh-tbl]').innerHTML = h;
    }

    /* ---------- search ---------- */
    const exportBtn = root.querySelector('[data-eoh-export]');
    function load(values, mirror) {
        A = values;
        Object.assign(S, { att: '', dept: null, q: '', page: 0 });
        app.innerHTML = '<div class="eoh-empty">Working out the balances…</div>';
        exportBtn.disabled = true;
        axios
            .get(root.dataset.dataUrl, { params: values })
            .then(({ data }) => {
                D = data;
                render();
                exportBtn.disabled = !D.staff.length;
            })
            .catch(() => {
                app.innerHTML = '<div class="eoh-empty">The balances could not be loaded. Please try again.</div>';
            });
        if (mirror) toOld(values);
    }
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        if (valid()) load(readForm(), true);
    });
    field('year').addEventListener('change', () => fitDates(true));
    form.addEventListener('change', () => D && render());
    root.querySelector('[data-eoh-reset]').addEventListener('click', () => {
        defaults();
        note('');
        load(readForm(), true);
    });

    /* ---------- report events ---------- */
    app.addEventListener('click', (e) => {
        let b;
        if ((b = e.target.closest('[data-att]'))) {
            const k = b.dataset.att;
            S.att = S.att === k ? '' : k;
            S.page = 0;
            render();
            const x = app.querySelector(`[data-att="${k}"]`);
            if (x) x.focus();
        } else if ((b = e.target.closest('[data-dept]'))) {
            const d = b.dataset.dept;
            S.dept = S.dept === d ? null : d;
            S.page = 0;
            render();
            const y = [...app.querySelectorAll('[data-dept]')].find((z) => z.dataset.dept === d);
            if (y) y.focus();
        } else if ((b = e.target.closest('[data-clear]'))) {
            if (b.dataset.clear === 'att') S.att = '';
            else S.dept = null;
            S.page = 0;
            render();
        } else if ((b = e.target.closest('[data-page]'))) {
            S.page += +b.dataset.page;
            draw();
        }
    });
    app.addEventListener('change', (e) => {
        if (e.target.matches('[data-eoh-sort]')) {
            S.sort = e.target.value;
            S.page = 0;
            draw();
        }
    });
    app.addEventListener('input', (e) => {
        if (e.target.matches('[data-eoh-q]')) {
            S.q = e.target.value;
            S.page = 0;
            draw();
        }
    });

    /* ---------- download ---------- */
    function toast(message) {
        let t = document.querySelector('.eoh-toast');
        if (!t) {
            t = document.createElement('div');
            t.className = 'eoh-toast';
            t.setAttribute('role', 'status');
            document.body.appendChild(t);
        }
        t.textContent = message;
        t.classList.add('show');
        clearTimeout(toast.timer);
        toast.timer = setTimeout(() => t.classList.remove('show'), 2600);
    }
    exportBtn.addEventListener('click', () => {
        if (!A || exportBtn.classList.contains('is-busy')) return;
        exportBtn.classList.add('is-busy');
        exportBtn.disabled = true;
        const params = { ...A, att: S.att, dept: S.dept === null ? '' : S.dept, deptSet: S.dept === null ? '' : '1', q: S.q, sort: S.sort };
        axios({ method: 'get', url: root.dataset.excelUrl, params, responseType: 'blob' })
            .then((response) => {
                const m = /filename="?([^";]+)"?/i.exec(response.headers['content-disposition'] || '');
                const href = window.URL.createObjectURL(new Blob([response.data]));
                const a = document.createElement('a');
                a.href = href;
                a.setAttribute('download', m ? m[1] : 'Outstanding_Holiday.xlsx');
                document.body.appendChild(a);
                a.click();
                a.remove();
                setTimeout(() => window.URL.revokeObjectURL(href), 1000);
            })
            .catch(() => toast('The download failed. Please try again.'))
            .finally(() => {
                exportBtn.classList.remove('is-busy');
                exportBtn.disabled = false;
            });
    });

    /* ---------- New / Old version ---------- */
    root.querySelectorAll('[data-eoh-version]').forEach((button) =>
        button.addEventListener('click', () => {
            const v = button.dataset.eohVersion;
            root.querySelectorAll('[data-eoh-version]').forEach((b) => b.setAttribute('aria-pressed', String(b === button)));
            root.querySelectorAll('[data-eoh-pane]').forEach((pane) => {
                const off = pane.dataset.eohPane !== v;
                pane.classList.toggle('is-off', off);
                pane.setAttribute('aria-hidden', String(off));
            });
            root.querySelectorAll('[data-eoh-new-only]').forEach((el) => (el.hidden = v !== 'new'));
            window.dispatchEvent(new Event('resize')); // the old table redraws on resize
        })
    );

    // The old page: holiday year (Tom Select), From / To (DD-MM-YYYY), Status (2 = All), Search runs its table.
    const oldForm = document.getElementById('outstandingHolidayReportForm');
    let mirroring = false;
    function toOld(v) {
        if (!oldForm || !window.$) return;
        const year = document.getElementById('holiday_year_id');
        if (year && year.tomselect) year.tomselect.setValue(v.year, true);
        else if (year) year.value = v.year;
        document.getElementById('from_date').value = dmy(v.from);
        document.getElementById('to_date').value = dmy(v.to);
        document.getElementById('status').value = v.status;
        oldForm.querySelector('[name="action"]').value = 'search';
        mirroring = true;
        try {
            $(oldForm).trigger('submit');
        } finally {
            mirroring = false;
        }
    }
    if (oldForm && window.$) {
        $(oldForm).on('submit', () => {
            if (mirroring || oldForm.querySelector('[name="action"]').value !== 'search') return;
            const v = {
                year: document.getElementById('holiday_year_id').value,
                from: isoOf(document.getElementById('from_date').value),
                to: isoOf(document.getElementById('to_date').value),
                status: document.getElementById('status').value || '2',
            };
            if (!v.year || !v.from || !v.to) return; // the old form shows its own errors
            field('year').value = v.year;
            fitDates(false);
            field('from').value = v.from;
            field('to').value = v.to;
            field('status').value = v.status;
            note('');
            load(v, false);
        });
        const oldReset = document.getElementById('oshResetBtn');
        if (oldReset)
            oldReset.addEventListener('click', () => {
                defaults();
                note('');
                A = null;
                D = null;
                exportBtn.disabled = true;
                app.innerHTML = '<div class="eoh-empty">Choose a holiday year and dates, then select Search.</div>';
            });
    }

    /* ---------- start ---------- */
    defaults();
    if (field('year').value) load(readForm(), true);
})();
