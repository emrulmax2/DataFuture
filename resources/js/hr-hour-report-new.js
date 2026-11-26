/*
 * HR Portal › Employee Hour Report, new version (user's employee-hour-report design, 26 Nov 2026).
 *
 * Search loads everyone with attendance in the range, with worked and holiday minutes worked out as the
 * old report does (App\Services\HR\HourReport). The figures, the hours histogram, job titles and the
 * employee list are drawn here; Download Excel sends the same view to the server.
 *
 * Old version: its Search posts the page, so the page comes back with its dates; this script then opens the
 * Old version and loads the same dates here. A Search here fetches the old result for the same dates
 * and puts it in the Old pane (with its Download Excel link), so both versions show the same range.
 */
import { hrDatePickers } from './hr-report-datepicker';

(function () {
    'use strict';

    const root = document.querySelector('[data-ehr]');
    if (!root) return;
    hrDatePickers(root); // Litepicker on the date filters (ISO values stay on data-*-f)

    const app = root.querySelector('[data-ehr-app]');
    const form = root.querySelector('[data-ehr-form]');
    const field = (k) => root.querySelector(`[data-ehr-f="${k}"]`);
    const esc = (s) => String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    const MN = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const PER = 20;
    const SMALL_JOB = 4;
    const VIEWS = [
        ['all', 'All', () => true],
        ['hol', 'Took holiday', (p) => p.h > 0],
        ['hi', 'Over 40h a week', (p) => p.wk > 40],
        ['lo', 'Under 5h a week', (p) => p.wk < 5],
    ];

    let D = null; // {from, to, weeks, today, staff}
    let A = null; // applied {from, to}
    const S = { band: -1, job: '', view: 'all', q: '', sort: 'screen', page: 0 };

    const dd = (s) => {
        const p = s.split('-');
        return new Date(+p[0], +p[1] - 1, +p[2]);
    };
    const iso = (d) => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
    const fmt = (s) => {
        const d = dd(s);
        return `${d.getDate()} ${MN[d.getMonth()]} ${d.getFullYear()}`;
    };
    const dmy = (s) => (s ? s.split('-').reverse().join('-') : '');
    const hm = (m) => `${Math.floor(m / 60)}h ${String(m % 60).padStart(2, '0')}m`;
    const n = (v) => Math.round(v).toLocaleString('en-GB');
    const wks = (w) => `${String(w).replace(/\.0$/, '')} week${w === 1 ? '' : 's'}`;
    const pc = (a, b) => (b ? `${((100 * a) / b).toFixed(1)}%` : '–');

    /* Histogram bands: six bands of a round number of hours, sized to the range (100 for 13 weeks). */
    function bandStep(weeks) {
        const want = (weeks * 40) / 4;
        return [5, 10, 20, 25, 50, 100, 200, 250, 500, 1000].reduce((best, s) => (Math.abs(s - want) < Math.abs(best - want) ? s : best), 5);
    }
    function bands(weeks) {
        const s = bandStep(weeks);
        return [0, 1, 2, 3, 4, 5].map((k) => [k * s, k === 5 ? Infinity : (k + 1) * s, k === 0 ? `Under ${s}` : k === 5 ? `${n(5 * s)} or more` : `${n(k * s)} to ${n((k + 1) * s - 1)}`]);
    }

    function termRange() {
        const now = new Date();
        const y = now.getMonth() >= 8 ? now.getFullYear() : now.getFullYear() - 1;
        return [new Date(y, 8, 1), new Date(y, 10, 30)];
    }
    const stale = () => A && (field('from').value !== A.from || field('to').value !== A.to);

    function people() {
        return D.staff.map((p) => ({ ...p, wk: p.w / 60 / D.weeks }));
    }

    function render() {
        if (!D) return;
        const P = people();
        const N = P.length;
        let h = stale() ? '<div class="ehr-stale">The dates have changed. Select Search to update the report.</div>' : '';
        const toLabel = fmt(D.to) + (A.to ? '' : ' (today)');
        document.getElementById('ehrSub').textContent = `London Churchill College · worked and holiday hours · ${fmt(D.from)} to ${toLabel}`;
        if (!N) {
            app.innerHTML = h + `<div class="ehr-empty">Nobody has attendance between ${fmt(D.from)} and ${toLabel}.</div>`;
            return;
        }
        let W = 0;
        let H = 0;
        let hol = 0;
        P.forEach((p) => {
            W += p.w;
            H += p.h;
            if (p.h) hol++;
        });
        h +=
            `<div class="ehr-period"><b>${N} employees</b> · ${fmt(D.from)} to ${toLabel} · ${wks(D.weeks)}</div>` +
            '<div class="ehr-stats">' +
            `<div class="ehr-stat"><b>${n(W / 60)}<small>hours</small></b><span>worked in total</span></div>` +
            `<div class="ehr-stat is-h"><b>${n(H / 60)}<small>hours</small></b><span>holiday taken · ${pc(H, W + H)} of all hours</span></div>` +
            `<div class="ehr-stat"><b>${(W / 60 / N / D.weeks).toFixed(1)}<small>hours a week</small></b><span>average worked per employee</span></div>` +
            `<div class="ehr-stat"><b>${hol}</b><span>of ${N} employees took holiday · ${pc(hol, N)}</span></div></div>`;

        /* histogram + job titles */
        const BANDS = bands(D.weeks);
        const bc = BANDS.map((b) => P.filter((p) => p.t / 60 >= b[0] && p.t / 60 < b[1]).length);
        const bmax = Math.max(1, ...bc);
        const jobs = {};
        P.forEach((p) => {
            const o = jobs[p.job] || (jobs[p.job] = { c: 0, w: 0 });
            o.c++;
            o.w += p.w;
        });
        const jk = Object.keys(jobs)
            .filter((k) => jobs[k].c >= SMALL_JOB)
            .sort((x, y) => jobs[y].c - jobs[x].c || x.localeCompare(y));
        const other = { c: 0, w: 0 };
        Object.keys(jobs).forEach((k) => {
            if (jobs[k].c < SMALL_JOB) {
                other.c += jobs[k].c;
                other.w += jobs[k].w;
            }
        });
        const jrow = (key, label, o) => {
            const wk = o.w / 60 / o.c / D.weeks;
            return (
                `<button type="button" class="ehr-jr" data-job="${esc(key)}" aria-pressed="${S.job === key}"><span>${esc(label)}</span><span class="c">${o.c}</span>` +
                `<span class="ehr-meter"><i style="width:${Math.min(100, (100 * wk) / 40)}%"></i></span><span class="v">${wk.toFixed(1)} h</span></button>`
            );
        };
        h +=
            '<div class="ehr-two"><section class="ehr-sec"><div class="ehr-sechead"><h2>Staff by total hours</h2>' +
            `<span class="ehr-hint">Worked plus holiday, over the ${wks(D.weeks)}</span></div><div><div class="ehr-hist" role="group" aria-label="Staff by total hours">` +
            BANDS.map(
                (b, k) =>
                    `<button type="button" class="ehr-hb" data-band="${k}" aria-pressed="${S.band === k}" aria-label="${b[2]} hours: ${bc[k]} staff"${bc[k] ? '' : ' disabled'}>` +
                    `<b>${bc[k]}</b><i style="height:${(100 * bc[k]) / bmax}%"></i></button>`
            ).join('') +
            '</div><div class="ehr-hx">' +
            BANDS.map((b, k) => `<span class="${S.band === k ? 'on' : ''}">${b[2]}</span>`).join('') +
            '</div></div><div class="ehr-hint">Select a bar to list those staff.</div></section>' +
            '<section class="ehr-sec"><div class="ehr-sechead"><h2>By job title</h2><span class="ehr-hint">Average worked hours a week · bar full at 40</span></div><div>' +
            '<div class="ehr-jh"><span>Job title</span><span style="text-align:right">Staff</span><span></span><span style="text-align:right">A week</span></div>' +
            jk.map((k) => jrow(k || '__none', k || 'No job title', jobs[k])).join('') +
            (other.c ? jrow('__other', `Other job titles (${Object.keys(jobs).length - jk.length})`, other) : '') +
            '</div></section></div>';

        h +=
            '<section class="ehr-sec"><div class="ehr-sechead"><h2>Employees</h2><span class="ehr-legend"><span><i style="background:var(--ehr-teal)"></i>Worked</span><span><i style="background:var(--ehr-orange)"></i>Holiday</span></span></div>' +
            '<div class="ehr-tools">' +
            `<label class="ehr-field"><span>Find an employee</span><input class="ehr-ctl" type="search" data-ehr-q placeholder="Employee or job title" value="${esc(S.q)}" autocomplete="off"></label>` +
            '<div class="ehr-field is-wide"><span id="ehrViewLabel">Show</span><div class="ehr-seg" role="group" aria-labelledby="ehrViewLabel" data-ehr-views></div></div>' +
            '<label class="ehr-field" style="flex-basis:200px"><span>Sort by</span><select class="ehr-ctl" data-ehr-sort><option value="screen">Name order</option><option value="hi">Most hours first</option><option value="lo">Fewest hours first</option><option value="hol">Most holiday first</option></select></label>' +
            '</div><div data-ehr-tbl></div></section>';

        const bh = P.reduce((t, p) => t + (p.bh || 0), 0);
        const notes = [
            `<li><b>Hours a week</b> is worked hours divided by the ${wks(D.weeks)} in the range. Over 40h and Under 5h are flags to look at, not errors.</li>`,
        ];
        if (D.to > D.today) notes.push('<li>The range runs past today, so the last days are not complete yet.</li>');
        notes.push(`<li>${N - hol} of ${N} employees show no holiday hours for the range.</li>`);
        notes.push(
            `<li>Holiday hours include bank holidays for staff whose bank holidays are booked automatically${bh ? ` (${n(bh / 60)} hours in this range)` : ''}. The old version can miss them when the range sits inside a holiday year, or take the hours from another employee's working pattern; worked hours are the same in both versions.</li>`
        );
        notes.push("<li>The old version prints each person's NI number under their name. It is left out here, and stays in the Excel file.</li>");
        h += `<ul class="ehr-notes">${notes.join('')}</ul>`;
        app.innerHTML = h;
        app.querySelector('[data-ehr-sort]').value = S.sort;
        draw();
    }

    function filtered() {
        const P = people();
        const BANDS = bands(D.weeks);
        const q = S.q.trim().toLowerCase();
        const band = S.band >= 0 ? BANDS[S.band] : null;
        const count = {};
        P.forEach((p) => (count[p.job] = (count[p.job] || 0) + 1));
        const pool = P.filter(
            (p) =>
                (!band || (p.t / 60 >= band[0] && p.t / 60 < band[1])) &&
                (!S.job || (S.job === '__other' ? count[p.job] < SMALL_JOB : p.job === (S.job === '__none' ? '' : S.job))) &&
                (!q || `${p.name} ${p.job}`.toLowerCase().includes(q))
        );
        const list = pool.filter(VIEWS.find((v) => v[0] === S.view)[2]);
        if (S.sort === 'hi') list.sort((a, b) => b.t - a.t);
        if (S.sort === 'lo') list.sort((a, b) => a.t - b.t);
        if (S.sort === 'hol') list.sort((a, b) => b.h - a.h || b.t - a.t);
        return { P, pool, list, band };
    }

    function draw() {
        const { P, pool, list, band } = filtered();
        app.querySelector('[data-ehr-views]').innerHTML = VIEWS.map(
            (v) => `<button type="button" data-view="${v[0]}" aria-pressed="${S.view === v[0]}">${v[1]}<small>${pool.filter(v[2]).length}</small></button>`
        ).join('');
        const pages = Math.max(1, Math.ceil(list.length / PER));
        S.page = Math.min(Math.max(0, S.page), pages - 1);
        const part = list.slice(S.page * PER, S.page * PER + PER);
        const max = Math.max(1, ...P.map((p) => p.t));
        const chips =
            (band ? `<button type="button" class="ehr-chipx" data-clear="band">${band[2]} hours ×</button> ` : '') +
            (S.job ? `<button type="button" class="ehr-chipx" data-clear="job">${esc(S.job === '__other' ? 'Other job titles' : S.job === '__none' ? 'No job title' : S.job)} ×</button>` : '');
        let h =
            (chips ? `<div class="ehr-hint" style="margin-bottom:6px">Filtered to: ${chips}</div>` : '') +
            '<div class="ehr-eh"><span>Employee</span><span></span><span class="r">Worked</span><span class="r">Holiday</span><span class="r">Total</span><span class="r">A week</span></div>';
        if (!part.length) h += '<div class="ehr-empty" style="margin-top:10px">No employees match.</div>';
        h += '<div>';
        part.forEach((p) => {
            h +=
                `<div class="ehr-er"><div class="nm"><b><a href="${esc(root.dataset.profileUrl.replace('__ID__', p.id))}">${esc(p.name)}</a>` +
                (p.wk > 40 ? '<span class="ehr-fl">Over 40h</span>' : p.wk < 5 ? '<span class="ehr-fl is-lo">Under 5h</span>' : '') +
                `</b><small title="${esc(p.job)}">${esc(p.job || 'No job title')}</small></div>` +
                `<div class="ehr-bar2" role="img" aria-label="Worked ${hm(p.w)}, holiday ${hm(p.h)}"><i class="w" style="width:${(100 * p.w) / max}%"></i><i class="h" style="width:${(100 * p.h) / max}%"></i></div>` +
                `<span class="num r">${hm(p.w)}</span><span class="num r${p.h ? '' : ' z'}">${p.h ? hm(p.h) : '–'}</span><span class="num tot r">${hm(p.t)}</span><span class="wk r">${p.wk.toFixed(1)} h</span></div>`;
        });
        h +=
            '</div>' +
            `<div class="ehr-foot" style="margin-top:10px"><span>${list.length ? `Showing ${S.page * PER + 1} to ${S.page * PER + part.length} of ${list.length}` : '0 employees'}</span>` +
            (pages > 1
                ? `<span class="ehr-pager"><button type="button" class="ehr-btn" data-page="-1"${S.page ? '' : ' disabled'}>Previous</button><span>Page ${S.page + 1} of ${pages}</span><button type="button" class="ehr-btn" data-page="1"${S.page < pages - 1 ? '' : ' disabled'}>Next</button></span>`
                : '') +
            '</div>';
        app.querySelector('[data-ehr-tbl]').innerHTML = h;
    }

    /* ---------- search ---------- */
    const note = (msg) => {
        const p = root.querySelector('[data-ehr-formnote]');
        p.textContent = msg || '';
        p.hidden = !msg;
    };
    function load(range, mirror) {
        A = range;
        Object.assign(S, { band: -1, job: '', view: 'all', q: '', page: 0 });
        app.innerHTML = '<div class="ehr-empty">Working out the hours…</div>';
        root.querySelector('[data-ehr-export]').disabled = true;
        axios
            .get(root.dataset.dataUrl, { params: range })
            .then(({ data }) => {
                D = data;
                render();
                root.querySelector('[data-ehr-export]').disabled = !D.staff.length;
            })
            .catch(() => {
                app.innerHTML = '<div class="ehr-empty">The hours could not be loaded. Please try again.</div>';
            });
        if (mirror) toOld(range);
    }
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        const from = field('from').value;
        const to = field('to').value;
        if (!from) {
            note('Choose a From date.');
            field('from').focus();
            return;
        }
        if (to && to < from) {
            note('To must be on or after From.');
            field('to').focus();
            return;
        }
        note('');
        load({ from, to }, true);
    });
    form.addEventListener('change', () => D && render());
    root.querySelectorAll('[data-range]').forEach((b) =>
        b.addEventListener('click', () => {
            const now = new Date();
            let a;
            let z;
            if (b.dataset.range === 'month') {
                a = new Date(now.getFullYear(), now.getMonth(), 1);
                z = new Date(now.getFullYear(), now.getMonth() + 1, 0);
            } else if (b.dataset.range === 'last') {
                a = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                z = new Date(now.getFullYear(), now.getMonth(), 0);
            } else {
                [a, z] = termRange();
            }
            field('from').value = iso(a);
            field('to').value = iso(z);
            note('');
            if (D) render();
        })
    );

    /* ---------- report events ---------- */
    app.addEventListener('click', (e) => {
        let b;
        if ((b = e.target.closest('[data-band]'))) {
            const k = +b.dataset.band;
            S.band = S.band === k ? -1 : k;
            S.page = 0;
            render();
            const x = app.querySelector(`[data-band="${k}"]`);
            if (x) x.focus();
        } else if ((b = e.target.closest('[data-job]'))) {
            const j = b.dataset.job;
            S.job = S.job === j ? '' : j;
            S.page = 0;
            render();
            const y = [...app.querySelectorAll('[data-job]')].find((z) => z.dataset.job === j);
            if (y) y.focus();
        } else if ((b = e.target.closest('[data-clear]'))) {
            if (b.dataset.clear === 'band') S.band = -1;
            else S.job = '';
            S.page = 0;
            render();
        } else if ((b = e.target.closest('[data-view]'))) {
            S.view = b.dataset.view;
            S.page = 0;
            draw();
            app.querySelector(`[data-view="${S.view}"]`).focus();
        } else if ((b = e.target.closest('[data-page]'))) {
            S.page += +b.dataset.page;
            draw();
        }
    });
    app.addEventListener('change', (e) => {
        if (e.target.matches('[data-ehr-sort]')) {
            S.sort = e.target.value;
            S.page = 0;
            draw();
        }
    });
    app.addEventListener('input', (e) => {
        if (e.target.matches('[data-ehr-q]')) {
            S.q = e.target.value;
            S.page = 0;
            draw();
        }
    });

    /* ---------- download ---------- */
    function toast(message) {
        let t = document.querySelector('.ehr-toast');
        if (!t) {
            t = document.createElement('div');
            t.className = 'ehr-toast';
            t.setAttribute('role', 'status');
            document.body.appendChild(t);
        }
        t.textContent = message;
        t.classList.add('show');
        clearTimeout(toast.timer);
        toast.timer = setTimeout(() => t.classList.remove('show'), 2600);
    }
    const exportBtn = root.querySelector('[data-ehr-export]');
    exportBtn.addEventListener('click', () => {
        if (!A || exportBtn.classList.contains('is-busy')) return;
        exportBtn.classList.add('is-busy');
        exportBtn.disabled = true;
        const params = { from: A.from, to: A.to, band: S.band >= 0 ? S.band : '', job: S.job, view: S.view, q: S.q, sort: S.sort };
        axios({ method: 'get', url: root.dataset.excelUrl, params, responseType: 'blob' })
            .then((response) => {
                const m = /filename="?([^";]+)"?/i.exec(response.headers['content-disposition'] || '');
                const href = window.URL.createObjectURL(new Blob([response.data]));
                const a = document.createElement('a');
                a.href = href;
                a.setAttribute('download', m ? m[1] : 'Hour_Report.xlsx');
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
    function showVersion(v) {
        root.querySelectorAll('[data-ehr-version]').forEach((b) => b.setAttribute('aria-pressed', String(b.dataset.ehrVersion === v)));
        root.querySelectorAll('[data-ehr-pane]').forEach((pane) => {
            const off = pane.dataset.ehrPane !== v;
            pane.classList.toggle('is-off', off);
            pane.setAttribute('aria-hidden', String(off));
        });
        root.querySelectorAll('[data-ehr-new-only]').forEach((el) => (el.hidden = v !== 'new'));
        window.dispatchEvent(new Event('resize'));
    }
    root.querySelectorAll('[data-ehr-version]').forEach((b) => b.addEventListener('click', () => showVersion(b.dataset.ehrVersion)));

    // The old screen: a form that posts the page. Fill its dates and its result for a New search.
    const oldBox = root.querySelector('[data-ehr-pane="old"] .box');
    function toOld(range) {
        if (!oldBox) return;
        const fromIn = oldBox.querySelector('#from_date');
        const toIn = oldBox.querySelector('#to_date');
        if (fromIn) fromIn.value = dmy(range.from);
        if (toIn) toIn.value = dmy(range.to);
        axios
            .get(root.dataset.oldUrl, { params: range })
            .then(({ data }) => {
                let result = [...oldBox.children].find((el) => el.matches('.overflow-x-auto.mt-5') && !el.classList.contains('scrollbar-hidden'));
                if (!result) {
                    result = document.createElement('div');
                    result.className = 'overflow-x-auto mt-5';
                    oldBox.appendChild(result);
                }
                result.innerHTML = data.html;
                let link = oldBox.querySelector('form a.btn-success');
                if (!link) {
                    const go = oldBox.querySelector('form button[type="submit"]');
                    link = document.createElement('a');
                    link.className = 'btn btn-success text-white w-auto mt-2 sm:mt-0 sm:ml-1';
                    link.textContent = 'Download Excel';
                    go.insertAdjacentElement('afterend', link);
                    go.insertAdjacentText('afterend', '\n');
                }
                link.href = data.export;
            })
            .catch(() => {});
    }

    /* ---------- start ---------- */
    if (root.dataset.searched === '1' && root.dataset.from) {
        // Came back from an Old search: the Old pane already shows these dates.
        field('from').value = root.dataset.from;
        field('to').value = root.dataset.to || '';
        showVersion('old');
        load({ from: root.dataset.from, to: root.dataset.to || '' }, false);
    } else {
        const [a, z] = termRange();
        field('from').value = iso(a);
        field('to').value = iso(z);
        load({ from: iso(a), to: iso(z) }, true);
    }
})();
