/*
 * HR Portal › Reports › Employee Starter Information, new version (user's employee-starter-information
 * design, 26 Nov 2026).
 *
 * One GET loads every active and inactive staff member with their start date (App\Services\HR\StarterReport).
 * Go applies the filters (old rules); the figures, the newest starters, the year × month grid and the list
 * (newest first; one year or month picked on the grid) are drawn here, as StarterReport::build does for
 * the PDF and the Excel.
 *
 * The Old version sits beside it: Go here fills the old form and runs its Go (the Old pane is off screen,
 * not display:none, so its table lays out); the old Go or Reset fills this form and applies it.
 */
import { hrDatePickers } from './hr-report-datepicker';

(function () {
    'use strict';

    const root = document.querySelector('[data-est]');
    if (!root) return;
    hrDatePickers(root); // Litepicker on the date filters (ISO values stay on data-*-f)

    const app = root.querySelector('[data-est-app]');
    const form = root.querySelector('[data-est-form]');
    const field = (k) => root.querySelector(`[data-est-f="${k}"]`);
    const esc = (s) => String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    const pc = (a, b) => (b ? `${((100 * a) / b).toFixed(1)}%` : '–');
    const MN = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const FULL = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    const FILTERS = ['type', 'department', 'from', 'to', 'ethnicity', 'nationality', 'gender', 'status'];
    const ETH = JSON.parse(root.dataset.ethnicities || '[]');
    const LIM = 15;

    const blank = () => ({ type: '', department: '', from: '', to: '', ethnicity: '', nationality: '', gender: '', status: '1' });
    let A = blank();
    let SEL = null; // {y} or {y, m}
    let ALL = false;
    let STAFF = null;
    let TODAY = null;
    const ym = (p) => [+p.started.slice(0, 4), +p.started.slice(5, 7) - 1];

    function keep(r) {
        if (A.status !== '2' && String(r.status) !== A.status) return false;
        if (A.type && String(r.type) !== A.type) return false;
        if (A.department && String(r.department) !== A.department) return false;
        if (A.from && !(r.started && r.started <= A.from)) return false;
        if (A.to && !(r.ended && r.ended >= A.to)) return false;
        if (A.ethnicity && String(r.ethnicityId) !== A.ethnicity) return false;
        if (A.nationality && String(r.nationalityId) !== A.nationality) return false;
        if (A.gender && String(r.genderId) !== A.gender) return false;
        return true;
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
    const profile = (p) => root.dataset.profileUrl.replace('__ID__', p.id);

    function render() {
        if (!STAFF) return;
        const rows = STAFF.filter(keep);
        const list = rows.filter((p) => p.started);
        const noStart = rows.filter((p) => !p.started);
        const N = list.length;
        document.getElementById('estSub').textContent = `London Churchill College · ${rows.length} ${A.status === '1' ? 'active ' : A.status === '0' ? 'inactive ' : ''}staff`;
        let h = stale() ? '<div class="est-stale">The filters have changed. Press Go to apply them.</div>' : '';
        if (!N) {
            app.innerHTML = h + '<div class="est-empty">No starters match these filters.</div>';
            return;
        }
        const yr = TODAY.getFullYear();
        const thisYear = list.filter((p) => ym(p)[0] === yr).length;
        const last90 = list.filter((p) => p.days >= 0 && p.days <= 90);
        const noWorks = list.filter((p) => !p.works).length;
        const byM = Array(12).fill(0);
        list.forEach((p) => byM[ym(p)[1]]++);
        const top = byM.indexOf(Math.max(...byM));
        h +=
            '<div class="est-stats">' +
            `<div class="est-stat"><b>${thisYear}</b><span>starters in ${yr} · ${pc(thisYear, N)} of current staff</span></div>` +
            `<div class="est-stat"><b>${last90.length}</b><span>started in the last 90 days</span></div>` +
            `<div class="est-stat"><b>${FULL[top]}</b><span>busiest month to start · ${byM[top]} starters over the years</span></div>` +
            `<div class="est-stat${noWorks ? ' is-warn' : ''}"><b>${noWorks}</b><span>starters with no works number</span></div></div>`;

        /* newest starters */
        const rec = last90.slice().sort((a, b) => a.days - b.days || a.name.localeCompare(b.name));
        h += `<section class="est-sec"><div class="est-sechead"><h2>Newest starters</h2><span class="est-hint">Last 90 days · ${rec.length}</span></div>`;
        h += rec.length
            ? '<div class="est-recent">' +
              rec
                  .slice(0, 12)
                  .map((p) => {
                      const [, m] = ym(p);
                      return (
                          `<div class="est-rc"><div class="est-dateb"><b>${+p.started.slice(8, 10)}</b><span>${MN[m]}</span></div><div style="min-width:0">` +
                          `<div class="est-n"><a href="${esc(profile(p))}">${esc(p.name)}</a></div>` +
                          `<div class="est-w"><b>${esc(p.ago)}</b>${p.works ? ` · No. ${esc(p.works)}` : ' · <span class="est-miss">no works number</span>'}</div></div></div>`
                      );
                  })
                  .join('') +
              '</div>' +
              (rec.length > 12 ? `<div class="est-hint">And ${rec.length - 12} more.</div>` : '')
            : '<div class="est-empty">Nobody has started in the last 90 days.</div>';
        h += '</section>';

        /* year × month grid */
        const years = {};
        list.forEach((p) => {
            const [y, m] = ym(p);
            (years[y] = years[y] || Array(12).fill(0))[m]++;
        });
        const ys = Object.keys(years).map(Number).sort((a, b) => b - a);
        const lvl = (c) => (c >= 7 ? 4 : c >= 4 ? 3 : c >= 2 ? 2 : 1);
        h +=
            '<section class="est-sec"><div class="est-sechead"><h2>When staff started</h2><span class="est-legend"><span style="margin-right:10px">Select a month or year to list those starters</span>1 ' +
            '<i style="background:var(--est-l1)"></i><i style="background:var(--est-l2)"></i><i style="background:var(--est-l3)"></i><i style="background:var(--est-l4)"></i> 7 or more</span></div>' +
            '<div class="est-hm" role="table" aria-label="Starters by year and month"><span></span>' +
            MN.map((m) => `<span class="est-h">${m}</span>`).join('') +
            '<span class="est-h" style="text-align:right;padding-right:4px">Total</span>';
        ys.forEach((y, k) => {
            if (k && ys[k - 1] - y > 1) {
                h += `<span class="est-gapy">No starters still here from ${y + 1}${ys[k - 1] - y > 2 ? ` to ${ys[k - 1] - 1}` : ''}</span>`;
            }
            const sy = SEL && SEL.y === y && SEL.m === undefined;
            h += `<span class="est-y"><button type="button" data-year="${y}" aria-pressed="${!!sy}">${y}</button></span>`;
            h += years[y]
                .map((c, m) => {
                    const fut = new Date(y, m, 1) > TODAY;
                    const on = SEL && SEL.y === y && SEL.m === m;
                    return c
                        ? `<button type="button" class="est-cell l${lvl(c)}" data-cell="${y}-${m}" aria-pressed="${!!on}" aria-label="${FULL[m]} ${y}: ${c} starter${c === 1 ? '' : 's'}">${c}</button>`
                        : `<span class="est-cell${fut ? ' is-fut' : ''}"></span>`;
                })
                .join('');
            h += `<span class="est-t">${years[y].reduce((a, b) => a + b, 0)}</span>`;
        });
        h +=
            '<span class="est-tot" style="border:0"></span>' +
            byM.map((c) => `<span class="est-tot">${c}</span>`).join('') +
            `<span class="est-tot" style="text-align:right;padding-right:4px">${N}</span></div></section>`;

        /* list */
        const shown = list
            .filter((p) => !SEL || (ym(p)[0] === SEL.y && (SEL.m === undefined || ym(p)[1] === SEL.m)))
            .sort((a, b) => b.started.localeCompare(a.started) || a.name.localeCompare(b.name));
        const title = !SEL ? 'All starters' : SEL.m === undefined ? `Started in ${SEL.y}` : `Started in ${FULL[SEL.m]} ${SEL.y}`;
        h +=
            `<section class="est-sec"><div><h2>${title}</h2><span class="est-hint">${shown.length} staff · newest first` +
            (SEL ? ' · <button type="button" class="est-lk" data-clear>Show all</button>' : '') +
            '</span></div><div>';
        let key = null;
        (ALL ? shown : shown.slice(0, LIM)).forEach((p) => {
            const [y, m] = ym(p);
            if (y !== key) {
                key = y;
                const c = shown.filter((x) => ym(x)[0] === y).length;
                h += `<div class="est-yh">${y}<span>${c} starter${c === 1 ? '' : 's'}</span></div>`;
            }
            h +=
                `<div class="est-sr"><span class="est-d"><b>${+p.started.slice(8, 10)}</b><span>${MN[m]}</span></span>` +
                `<span class="est-nm"><a href="${esc(profile(p))}">${esc(p.name)}</a>${p.days >= 0 && p.days <= 90 ? '<span class="est-new">New</span>' : ''}</span>` +
                `<span>${p.works ? `<span class="est-hint">Works no. ${esc(p.works)}</span>` : '<span class="est-miss">No works number</span>'}</span>` +
                `<span class="est-ago">${esc(p.ago)}</span></div>`;
        });
        h +=
            '</div>' +
            (shown.length > LIM
                ? `<div class="est-foot"><span>${ALL ? `Showing all ${shown.length}` : `Showing ${LIM} of ${shown.length}`}</span><button type="button" class="est-btn" data-all>${ALL ? 'Show fewer' : `Show all ${shown.length}`}</button></div>`
                : '') +
            '</section>';

        const notes = ['<li>The grid counts staff by the month they started on their current employment record.</li>', '<li>The old list was in record order; this one is newest first.</li>'];
        if (noStart.length) notes.unshift(`<li><b>${noStart.length} ${noStart.length === 1 ? 'record has' : 'records have'} no start date</b> and ${noStart.length === 1 ? 'is' : 'are'} left out of these figures: ${noStart.map((p) => esc(p.name)).join(', ')}.</li>`);
        h += `<ul class="est-notes">${notes.join('')}</ul>`;
        app.innerHTML = h;
    }

    /* ---------- events ---------- */
    app.addEventListener('click', (e) => {
        let b;
        if ((b = e.target.closest('[data-cell]'))) {
            const [y, m] = b.dataset.cell.split('-').map(Number);
            SEL = SEL && SEL.y === y && SEL.m === m ? null : { y, m };
            ALL = false;
            render();
            const f = app.querySelector(`[data-cell="${y}-${m}"]`);
            if (f) f.focus();
        } else if ((b = e.target.closest('[data-year]'))) {
            const y = +b.dataset.year;
            SEL = SEL && SEL.y === y && SEL.m === undefined ? null : { y };
            ALL = false;
            render();
            const f = app.querySelector(`[data-year="${y}"]`);
            if (f) f.focus();
        } else if (e.target.closest('[data-clear]')) {
            SEL = null;
            ALL = false;
            render();
        } else if (e.target.closest('[data-all]')) {
            ALL = !ALL;
            render();
            const a = app.querySelector('[data-all]');
            if (a) a.focus();
        }
    });
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        A = readForm();
        SEL = null;
        ALL = false;
        render();
        toOld();
    });
    FILTERS.forEach((k) =>
        field(k).addEventListener('change', () => {
            if (k === 'status') fillOptions();
            render();
        })
    );
    root.querySelector('[data-est-reset]').addEventListener('click', () => {
        A = blank();
        SEL = null;
        ALL = false;
        writeForm(A);
        fillOptions();
        render();
        toOld();
    });

    /* ---------- downloads (applied filters and the grid pick) ---------- */
    function toast(message) {
        let t = document.querySelector('.est-toast');
        if (!t) {
            t = document.createElement('div');
            t.className = 'est-toast';
            t.setAttribute('role', 'status');
            document.body.appendChild(t);
        }
        t.textContent = message;
        t.classList.add('show');
        clearTimeout(toast.timer);
        toast.timer = setTimeout(() => t.classList.remove('show'), 2600);
    }
    root.querySelectorAll('[data-est-export]').forEach((btn) =>
        btn.addEventListener('click', () => {
            if (btn.classList.contains('is-busy')) return;
            const pdf = btn.dataset.estExport === 'pdf';
            const params = { ...A, year: SEL ? SEL.y : '', month: SEL && SEL.m !== undefined ? SEL.m : '' };
            btn.classList.add('is-busy');
            btn.disabled = true;
            axios({ method: 'get', url: pdf ? root.dataset.pdfUrl : root.dataset.excelUrl, params, responseType: 'blob' })
                .then((response) => {
                    const match = /filename="?([^";]+)"?/i.exec(response.headers['content-disposition'] || '');
                    const href = window.URL.createObjectURL(new Blob([response.data]));
                    const a = document.createElement('a');
                    a.href = href;
                    a.setAttribute('download', match ? match[1] : pdf ? 'Employee_Starters.pdf' : 'Employee_Starters.xlsx');
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
    root.querySelectorAll('[data-est-version]').forEach((button) =>
        button.addEventListener('click', () => {
            const v = button.dataset.estVersion;
            root.querySelectorAll('[data-est-version]').forEach((b) => b.setAttribute('aria-pressed', String(b === button)));
            root.querySelectorAll('[data-est-pane]').forEach((pane) => {
                const off = pane.dataset.estPane !== v;
                pane.classList.toggle('is-off', off);
                pane.setAttribute('aria-hidden', String(off));
            });
            root.querySelectorAll('[data-est-new-only]').forEach((el) => (el.hidden = v !== 'new'));
            window.dispatchEvent(new Event('resize')); // the old table redraws on resize
        })
    );

    const OLD = {
        type: '#employee_work_type_id-starter', department: '#department_id-starter', from: '#startdate-starter', to: '#enddate-starter',
        ethnicity: '#ethnicity-starter', nationality: '#nationality-starter', gender: '#gender-starter', status: '#status_id-starter',
    };
    const goBtn = document.getElementById('tabulator-html-filter-go-SR');
    const resetBtn = document.getElementById('tabulator-html-filter-reset-SR');
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
        SEL = null;
        ALL = false;
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
            const [y, m, d] = String(data.today).split('-').map(Number);
            TODAY = new Date(y, m - 1, d);
            STAFF = data.staff || [];
            fillOptions();
            render();
        })
        .catch(() => {
            app.innerHTML = '<div class="est-empty">The starter list could not be loaded. Please reload the page.</div>';
        });
})();
