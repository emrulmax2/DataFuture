/*
 * HR Portal › Reports › Employee Service Lengths, new version (user's employee-service-lengths design,
 * 26 Nov 2026).
 *
 * One GET loads every active and inactive staff member with their service already worked out by
 * App\Services\HR\ServiceLengthsReport (calendar lengths, bands, next work anniversary), so the screen,
 * the PDF and the Excel agree. Filters work in the browser with the old report's rules.
 *
 * The Old version sits beside it: a filter change here fills the old form and runs its Go (the Old pane
 * is off screen, not display:none, so its table lays out); the old Go or Reset fills this form.
 */
import { hrDatePickers } from './hr-report-datepicker';

(function () {
    'use strict';

    const root = document.querySelector('[data-esl]');
    if (!root) return;
    hrDatePickers(root); // Litepicker on the date filters (ISO values stay on data-*-f)

    const app = root.querySelector('[data-esl-app]');
    const field = (k) => root.querySelector(`[data-esl-f="${k}"]`);
    const esc = (s) => String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    const pc = (a, b) => (b ? `${((100 * a) / b).toFixed(1)}%` : '–');
    const MN = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const BANDS = ['Under 1 year', '1 to 2 years', '2 to 5 years', '5 to 10 years', '10 years and over'];
    const FILTERS = ['type', 'department', 'from', 'to', 'ethnicity', 'nationality', 'gender', 'status'];
    const ETH = JSON.parse(root.dataset.ethnicities || '[]');
    const fmt = (iso) => {
        const [y, m, d] = iso.split('-').map(Number);
        return `${d} ${MN[m - 1]} ${y}`;
    };
    const LIM = 15;

    const S = { type: '', department: '', from: '', to: '', ethnicity: '', nationality: '', gender: '', status: '1', band: -1, order: 'long', all: false };
    let STAFF = null;
    let TODAY = null;

    function keep(r) {
        if (S.status !== '2' && String(r.status) !== S.status) return false;
        if (S.type && String(r.type) !== S.type) return false;
        if (S.department && String(r.department) !== S.department) return false;
        if (S.from && !(r.started && r.started <= S.from)) return false;
        if (S.to && !(r.ended && r.ended >= S.to)) return false;
        if (S.ethnicity && String(r.ethnicityId) !== S.ethnicity) return false;
        if (S.nationality && String(r.nationalityId) !== S.nationality) return false;
        if (S.gender && String(r.genderId) !== S.gender) return false;
        return true;
    }
    function fillOptions() {
        const pool = STAFF.filter((r) => S.status === '2' || String(r.status) === S.status);
        const count = (k) => pool.reduce((t, r) => ((t[r[k]] = (t[r[k]] || 0) + 1), t), {});
        const ce = count('ethnicityId');
        field('ethnicity').innerHTML =
            '<option value="">All ethnicities</option>' +
            ETH.filter((e) => e[2] || ce[e[0]] || String(e[0]) === S.ethnicity)
                .sort((a, b) => a[1].localeCompare(b[1]))
                .map((e) => `<option value="${e[0]}">${esc(e[1])} (${ce[e[0]] || 0})</option>`)
                .join('');
        const cn = count('nationalityId');
        const names = {};
        STAFF.forEach((r) => r.nationalityId && (names[r.nationalityId] = r.nationality));
        const nat = Object.keys(names).filter((id) => cn[id] || id === S.nationality).sort((a, b) => names[a].localeCompare(names[b]));
        if (S.nationality && !names[S.nationality]) nat.push(S.nationality);
        field('nationality').innerHTML =
            '<option value="">All nationalities</option>' + nat.map((id) => `<option value="${id}">${esc(names[id] || `Country #${id}`)} (${cn[id] || 0})</option>`).join('');
        field('ethnicity').value = S.ethnicity;
        field('nationality').value = S.nationality;
    }
    const syncForm = () => FILTERS.forEach((k) => (field(k).value = S[k]));

    function render() {
        if (!STAFF) return;
        const rows = STAFF.filter(keep);
        const list = rows.filter((p) => p.hasStart);
        const noStart = rows.filter((p) => !p.hasStart);
        const N = list.length;
        document.getElementById('eslSub').textContent = `London Churchill College · ${rows.length} ${S.status === '1' ? 'active ' : S.status === '0' ? 'inactive ' : ''}staff`;
        if (!N) {
            app.innerHTML = '<div class="esl-empty">No staff match these filters.</div>';
            return;
        }
        const ys = list.map((p) => p.years).sort((a, b) => a - b);
        const avg = ys.reduce((a, b) => a + b, 0) / N;
        const med = N % 2 ? ys[(N - 1) / 2] : (ys[N / 2 - 1] + ys[N / 2]) / 2;
        const longest = list.slice().sort((a, b) => b.years - a.years)[0];
        const yr = TODAY.getFullYear();
        const thisYear = list.filter((p) => +p.started.slice(0, 4) === yr).length;
        let h =
            '<div class="esl-stats">' +
            `<div class="esl-stat"><b>${avg.toFixed(1)}<small>years</small></b><span>average service</span></div>` +
            `<div class="esl-stat"><b>${med.toFixed(1)}<small>years</small></b><span>median: half of staff have less</span></div>` +
            `<div class="esl-stat"><b>${longest.y}<small>years</small></b><span>longest service, since ${fmt(longest.started)}</span></div>` +
            `<div class="esl-stat"><b>${thisYear}</b><span>joined in ${yr} · ${pc(thisYear, N)} of staff</span></div></div>`;

        /* joined by year + bands */
        const byY = {};
        let ymax = 0;
        list.forEach((p) => {
            const k = +p.started.slice(0, 4);
            byY[k] = (byY[k] || 0) + 1;
            ymax = Math.max(ymax, byY[k]);
        });
        const y0 = Math.min(...Object.keys(byY).map(Number));
        let bars = '';
        let xs = '';
        for (let y = y0; y <= yr; y++) {
            const c = byY[y] || 0;
            bars += `<div><b>${c || ''}</b><i style="height:${c ? Math.max(2, (100 * c) / ymax) : 0}%"></i></div>`;
            xs += `<span>'${String(y).slice(2)}</span>`;
        }
        const bc = BANDS.map((b, k) => list.filter((p) => p.band === k).length);
        h +=
            '<div class="esl-two"><section class="esl-sec"><div class="esl-sechead"><h2>When staff joined</h2><span class="esl-hint">Staff by year they started</span></div>' +
            `<div><div class="esl-yrs" role="img" aria-label="Staff by start year, ${y0} to ${yr}">${bars}</div><div class="esl-yx">${xs}</div></div></section>` +
            '<section class="esl-sec"><div class="esl-sechead"><h2>Length of service</h2><span class="esl-hint">Select a band to list those staff</span></div><div><div class="esl-split">' +
            bc.map((c, k) => (c ? `<i style="flex:${c};background:var(--esl-b${k});color:${k > 2 ? '#fff' : '#04302c'}">${c / N > 0.06 ? c : ''}</i>` : '')).join('') +
            '</div><div class="esl-bands">' +
            BANDS.map(
                (b, k) =>
                    `<button type="button" class="esl-band" data-band="${k}" aria-pressed="${S.band === k}"><span class="esl-sw" style="background:var(--esl-b${k})"></span><span>${b}</span><b>${bc[k]}</b><span class="esl-pc">${pc(bc[k], N)}</span></button>`
            ).join('') +
            `</div></div><div class="esl-hint">${pc(bc[0] + bc[1], N)} of staff have under 2 years of service.</div></section></div>`;

        /* anniversaries */
        const soon = list.filter((p) => p.ann && p.annDays <= 60 && p.annYears >= 1).sort((a, b) => a.annDays - b.annDays || a.name.localeCompare(b.name));
        h += `<section class="esl-sec"><div class="esl-sechead"><h2>Work anniversaries coming up</h2><span class="esl-hint">Next 60 days · ${soon.length}</span></div>`;
        h += soon.length
            ? '<div class="esl-soon">' +
              soon
                  .slice(0, 12)
                  .map((p) => {
                      const [, m, d] = p.ann.split('-').map(Number);
                      const when = p.annDays === 0 ? 'Today' : p.annDays === 1 ? 'Tomorrow' : `in ${p.annDays} days`;
                      return (
                          `<div class="esl-an"><div class="esl-dateb"><b>${d}</b><span>${MN[m - 1]}</span></div><div style="min-width:0"><div class="esl-n">${esc(p.name)}</div>` +
                          `<div class="esl-w"><b>${when}</b> · reaches ${p.annYears} year${p.annYears === 1 ? '' : 's'}${p.annYears % 5 === 0 ? '<span class="esl-tag">Milestone</span>' : ''}</div></div></div>`
                      );
                  })
                  .join('') +
              '</div>' +
              (soon.length > 12 ? `<div class="esl-hint">And ${soon.length - 12} more.</div>` : '')
            : '<div class="esl-empty">No anniversaries in the next 60 days.</div>';
        h += '</section>';

        /* service bars */
        const shown = list.filter((p) => S.band < 0 || p.band === S.band);
        shown.sort(S.order === 'long' ? (a, b) => b.years - a.years || a.name.localeCompare(b.name) : (a, b) => a.years - b.years || a.name.localeCompare(b.name));
        const top = Math.max(5, Math.ceil(Math.max(...shown.map((p) => p.years)) / 5) * 5);
        const step = top > 10 ? 5 : 1;
        let ticks = '';
        for (let t = 0; t <= top; t += step) ticks += `<span style="left:${(100 * t) / top}%">${t}${t === 0 ? ' years' : ''}</span>`;
        h +=
            `<section class="esl-sec"><div class="esl-tools"><div><h2>${S.band < 0 ? 'All staff' : BANDS[S.band]}</h2><span class="esl-hint">${shown.length} staff` +
            (S.band < 0 ? '' : ` · <button type="button" class="esl-lk" data-band="${S.band}">Clear</button>`) +
            '</span></div><div class="esl-seg" role="group" aria-label="Order">' +
            `<button type="button" data-order="long" aria-pressed="${S.order === 'long'}">Longest first</button><button type="button" data-order="new" aria-pressed="${S.order === 'new'}">Newest first</button></div></div>` +
            `<div><div class="esl-axis"><span>Staff member</span><span>Started</span><div class="esl-ticks">${ticks}</div><span style="text-align:right">Service</span></div>`;
        let key = -1;
        (S.all ? shown : shown.slice(0, LIM)).forEach((p) => {
            if (p.band !== key) {
                key = p.band;
                h += `<div class="esl-bh">${BANDS[key]}<span>${shown.filter((x) => x.band === key).length} staff</span></div>`;
            }
            const profile = root.dataset.profileUrl.replace('__ID__', p.id);
            h +=
                `<div class="esl-sr"><span class="esl-nm"><a href="${esc(profile)}">${esc(p.name || 'Unnamed')}</a></span><span class="esl-st">${fmt(p.started)}</span>` +
                `<div class="esl-track" style="--step:${(100 / top) * step}%"><i class="${p.left ? 'is-ended' : ''}" style="width:${(100 * p.years) / top}%;background:var(--esl-b${p.band})"></i></div>` +
                `<span class="esl-len">${p.words}${p.left ? `<small>Ended ${fmt(p.end)}</small>` : p.future ? `<small>Ends ${fmt(p.end)}</small>` : ''}</span></div>`;
        });
        h +=
            '</div>' +
            (shown.length > LIM
                ? `<div class="esl-foot"><span>${S.all ? `Showing all ${shown.length}` : `Showing ${LIM} of ${shown.length}`}</span><button type="button" class="esl-btn" data-all>${S.all ? 'Show fewer' : `Show all ${shown.length}`}</button></div>`
                : '') +
            '</section>';

        /* notes */
        const left = list.filter((p) => p.left).length;
        const fut = list.filter((p) => p.future).length;
        const notes = [];
        if (left && S.status !== '0') notes.push(`<li><b>${left} of these staff have an end date in the past.</b> Their bars are striped and stop at the end date.</li>`);
        if (fut) notes.push(`<li><b>${fut} ${fut === 1 ? 'has' : 'have'} an end date in the future.</b> Service runs to today; the old report counted up to that future date.</li>`);
        notes.push('<li>Service ends at the earlier of the employment end date and the working pattern end date; the old report used the working pattern only.</li>');
        notes.push('<li>Lengths are worked out from the dates by the calendar; the old report could be one to three days out.</li>');
        if (noStart.length) notes.push(`<li><b>${noStart.length} ${noStart.length === 1 ? 'record has' : 'records have'} no start date</b> and ${noStart.length === 1 ? 'is' : 'are'} left out of the figures: ${noStart.map((p) => esc(p.name)).join(', ')}.</li>`);
        h += `<ul class="esl-notes">${notes.join('')}</ul>`;
        app.innerHTML = h;
    }

    /* ---------- events ---------- */
    app.addEventListener('click', (e) => {
        let b;
        if ((b = e.target.closest('[data-band]'))) {
            const k = +b.dataset.band;
            Object.assign(S, { band: S.band === k ? -1 : k, all: false });
            render();
            const f = app.querySelector(`.esl-band[data-band="${k}"]`);
            if (f) f.focus();
        } else if ((b = e.target.closest('[data-order]'))) {
            S.order = b.dataset.order;
            render();
            app.querySelector(`[data-order="${S.order}"]`).focus();
        } else if (e.target.closest('[data-all]')) {
            S.all = !S.all;
            render();
            const a = app.querySelector('[data-all]');
            if (a) a.focus();
        }
    });
    FILTERS.forEach((k) =>
        field(k).addEventListener('change', () => {
            S[k] = field(k).value;
            Object.assign(S, { band: -1, all: false });
            if (k === 'status') fillOptions();
            render();
            toOld();
        })
    );
    root.querySelector('[data-esl-reset]').addEventListener('click', () => {
        Object.assign(S, { type: '', department: '', from: '', to: '', ethnicity: '', nationality: '', gender: '', status: '1', band: -1, all: false });
        syncForm();
        fillOptions();
        render();
        toOld();
    });

    /* ---------- exports ---------- */
    function toast(message) {
        let t = document.querySelector('.esl-toast');
        if (!t) {
            t = document.createElement('div');
            t.className = 'esl-toast';
            t.setAttribute('role', 'status');
            document.body.appendChild(t);
        }
        t.textContent = message;
        t.classList.add('show');
        clearTimeout(toast.timer);
        toast.timer = setTimeout(() => t.classList.remove('show'), 2600);
    }
    root.querySelectorAll('[data-esl-export]').forEach((btn) =>
        btn.addEventListener('click', () => {
            if (btn.classList.contains('is-busy')) return;
            const kind = btn.dataset.eslExport;
            const params = { band: S.band, order: S.order };
            FILTERS.forEach((k) => (params[k] = S[k]));
            btn.classList.add('is-busy');
            btn.disabled = true;
            axios({ method: 'get', url: kind === 'pdf' ? root.dataset.pdfUrl : root.dataset.excelUrl, params, responseType: 'blob' })
                .then((response) => {
                    const match = /filename="?([^";]+)"?/i.exec(response.headers['content-disposition'] || '');
                    const url = window.URL.createObjectURL(new Blob([response.data]));
                    const a = document.createElement('a');
                    a.href = url;
                    a.setAttribute('download', match ? match[1] : kind === 'pdf' ? 'Employee_Service_Lengths.pdf' : 'Employee_Service_Lengths.xlsx');
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                    setTimeout(() => window.URL.revokeObjectURL(url), 1000);
                })
                .catch(() => toast('The download failed. Please try again.'))
                .finally(() => {
                    btn.classList.remove('is-busy');
                    btn.disabled = false;
                });
        })
    );

    /* ---------- New / Old version ---------- */
    root.querySelectorAll('[data-esl-version]').forEach((button) =>
        button.addEventListener('click', () => {
            const v = button.dataset.eslVersion;
            root.querySelectorAll('[data-esl-version]').forEach((b) => b.setAttribute('aria-pressed', String(b === button)));
            root.querySelectorAll('[data-esl-pane]').forEach((pane) => {
                const off = pane.dataset.eslPane !== v;
                pane.classList.toggle('is-off', off);
                pane.setAttribute('aria-hidden', String(off));
            });
            root.querySelectorAll('[data-esl-new-only]').forEach((el) => (el.hidden = v !== 'new'));
            window.dispatchEvent(new Event('resize')); // the old table redraws on resize
        })
    );

    const OLD = {
        type: '#employee_work_type_id-lengthservice', department: '#department_id-lengthservice', from: '#startdate-lengthservice', to: '#enddate-lengthservice',
        ethnicity: '#ethnicity-lengthservice', nationality: '#nationality-lengthservice', gender: '#gender-lengthservice', status: '#status_id-lengthservice',
    };
    const goBtn = document.getElementById('tabulator-html-filter-go-LS');
    const resetBtn = document.getElementById('tabulator-html-filter-reset-LS');
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
        FILTERS.forEach((k) => setOld(k, S[k]));
        mirroring = true;
        try {
            $(goBtn).trigger('click');
        } finally {
            mirroring = false;
        }
    }
    function fromOld() {
        FILTERS.forEach((k) => (S[k] = oldValue(OLD[k])));
        if (!['0', '1', '2'].includes(S.status)) S.status = '1';
        ['from', 'to'].forEach((k) => (S[k] = /^\d{4}-\d{2}-\d{2}$/.test(S[k]) ? S[k] : ''));
        Object.assign(S, { band: -1, all: false });
        if (STAFF) fillOptions();
        syncForm();
        render();
    }
    if (goBtn) goBtn.addEventListener('click', () => !mirroring && fromOld());
    if (resetBtn) resetBtn.addEventListener('click', () => !mirroring && setTimeout(fromOld, 0));

    /* ---------- load ---------- */
    syncForm();
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
            app.innerHTML = '<div class="esl-empty">The service lengths could not be loaded. Please reload the page.</div>';
        });
})();
