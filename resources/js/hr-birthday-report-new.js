/*
 * HR Portal › Reports › Birthday List, new version (user's birthday-list-report design, 26 Nov 2026).
 *
 * One GET loads every active and inactive staff member (BirthdayReportNewController::data); the
 * filters, the calendar wall, the side panel and the list all work in the browser. Dates follow the
 * same rules as App\Services\HR\BirthdayList (used by the PDF and the Excel): next birthday from the
 * server's today, 29 February on the 28th in other years, milestone = a birthday ending in 0, or 65.
 *
 * The Old version sits beside it: changing a filter here fills the old form and runs its Go (the
 * Old pane is off screen, not display:none, so its table lays out); the old Go or Reset fills this
 * form and redraws it.
 */
(function () {
    'use strict';

    const root = document.querySelector('[data-bdl]');
    if (!root) return;

    const MN = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    const WD = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    const DAY = 864e5;
    const app = root.querySelector('[data-bdl-app]');
    const field = (k) => root.querySelector(`[data-bdl-f="${k}"]`);
    const esc = (s) => String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    const plural = (n, w) => `${n} ${w}${n === 1 ? '' : 's'}`;

    const S = { month: 0, type: '', department: '', status: '1', q: '', order: 'next', view: 'wall', sel: { t: 'soon' } };
    let TODAY = null;
    let WALL = [];
    let PEOPLE = []; // staff with a date of birth, dates worked out
    let NODOB = []; // staff without one

    /* ---------- dates ---------- */
    const leap = (y) => (y % 4 === 0 && y % 100 !== 0) || y % 400 === 0;
    const bday = (y, m, d) => new Date(y, m - 1, m === 2 && d === 29 && !leap(y) ? 28 : d);
    const daysBetween = (a, b) => Math.round((b - a) / DAY);
    const milestone = (n) => n > 0 && (n % 10 === 0 || n === 65);
    const key = (d) => `${d.getFullYear()}-${d.getMonth() + 1}-${d.getDate()}`;
    const when = (d) => (d === 0 ? 'Today' : d === 1 ? 'Tomorrow' : `in ${d} days`);
    const ago = (o) => (o === 0 ? 'Today' : o === 1 ? 'Tomorrow' : o === -1 ? 'Yesterday' : o > 0 ? `in ${o} days` : `${-o} days ago`);
    const fmt = (d, m, y) => `${d} ${MN[m - 1].slice(0, 3)} ${y}`;

    function enrich(r) {
        const [y, m, d] = r.dob.split('-').map(Number);
        let next = bday(TODAY.getFullYear(), m, d);
        if (next < TODAY) next = bday(TODAY.getFullYear() + 1, m, d);
        const days = daysBetween(TODAY, next);
        const turns = next.getFullYear() - y;
        const wm = WALL.find((w) => w.getMonth() + 1 === m);
        const occ = bday(wm.getFullYear(), m, d);
        return { ...r, m, d, y, next, days, turns, age: days === 0 ? turns : turns - 1, occ, off: daysBetween(TODAY, occ), turnsOcc: occ.getFullYear() - y };
    }

    /* ---------- filters ---------- */
    function keep(p) {
        if (S.status !== '2' && String(p.status) !== S.status) return false;
        if (S.type !== '' && String(p.type) !== S.type) return false;
        if (S.department !== '' && String(p.department) !== S.department) return false;
        const q = S.q.trim().toLowerCase();
        if (q && p.name.toLowerCase().indexOf(q) < 0 && String(p.works).toLowerCase().indexOf(q) < 0) return false;
        return true;
    }
    function syncForm() {
        field('month').value = String(S.month);
        field('type').value = S.type;
        field('department').value = S.department;
        field('status').value = S.status;
        field('q').value = S.q;
    }
    function monthSel() {
        const wm = WALL.find((w) => w.getMonth() + 1 === S.month);
        return wm ? { t: 'month', k: `${wm.getFullYear()}-${wm.getMonth()}` } : { t: 'soon' };
    }

    /* ---------- drawing ---------- */
    const profileUrl = (id) => root.dataset.profileUrl.replace('__ID__', id);
    const nameLink = (p) => `<a class="bdl-name" href="${esc(profileUrl(p.id))}">${esc(p.name || 'Unnamed')}</a>`;
    const tagFor = (days, turns) => (days === 0 ? '<span class="bdl-tag bdl-tag--today">Today</span>' : milestone(turns) ? '<span class="bdl-tag">Milestone</span>' : '');

    function person(p) {
        const past = p.off < 0;
        const extra = [`Born ${fmt(p.d, p.m, p.y)}`];
        if (p.gender) extra.push(esc(p.gender));
        extra.push(p.works ? `Works no. ${esc(p.works)}` : '<span class="bdl-miss">no works number</span>');
        return (
            `<li class="bdl-pc${p.off === 0 ? ' is-today' : past ? ' is-past' : ''}">` +
            `<div class="bdl-dateb"><b>${p.occ.getDate()}</b><span>${MN[p.m - 1].slice(0, 3)}</span></div>` +
            `<div class="bdl-pi"><div class="bdl-n">${nameLink(p)}${p.off === 0 ? '<span class="bdl-tag bdl-tag--today">Today</span>' : milestone(p.turnsOcc) ? '<span class="bdl-tag">Milestone</span>' : ''}</div>` +
            `<div class="bdl-w"><b>${ago(p.off)}</b> · ${WD[p.occ.getDay()]} · ${past ? 'turned' : 'turns'} ${p.turnsOcc}</div>` +
            `<div class="bdl-x">${extra.join(' · ')}</div></div></li>`
        );
    }

    function listHtml(list) {
        const q = S.q.trim();
        let h =
            '<section class="bdl-sec"><div class="bdl-tools"><div>' +
            `<h2>${S.month ? `${MN[S.month - 1]} birthdays` : 'All birthdays'}</h2>` +
            `<span class="bdl-hint">${list.length} staff${q ? ` matching “${esc(q)}”` : ''}</span></div>` +
            '<div class="bdl-seg" role="group" aria-label="Order">' +
            `<button type="button" data-order="next" aria-pressed="${S.order === 'next'}">Upcoming first</button>` +
            `<button type="button" data-order="cal" aria-pressed="${S.order === 'cal'}">January to December</button></div></div>`;
        if (!list.length) return h + '<div class="bdl-empty">No staff match.</div></section>';

        const cal = S.order === 'cal';
        list.sort(cal ? (a, b) => a.m - b.m || a.d - b.d || a.name.localeCompare(b.name) : (a, b) => a.days - b.days || a.name.localeCompare(b.name));
        const bandOf = (p) => (cal ? MN[p.m - 1] : `${MN[p.m - 1]} ${p.next.getFullYear()}`);
        const counts = {};
        list.forEach((p) => (counts[bandOf(p)] = (counts[bandOf(p)] || 0) + 1));

        h += '<div class="bdl-scroll"><table class="bdl-table"><thead><tr><th>Day</th><th>Staff member</th><th>Gender</th><th>Works no.</th><th>Date of birth</th><th class="bdl-r">Age</th><th>Next birthday</th></tr></thead><tbody>';
        let band = null;
        let n = 0;
        list.forEach((p) => {
            const k = bandOf(p);
            if (k !== band) {
                band = k;
                n = 0;
                h += `<tr class="bdl-band"><td colspan="7">${k}<span>${plural(counts[k], 'birthday')}</span></td></tr>`;
            }
            n++;
            h +=
                `<tr class="bdl-row${n % 2 ? '' : ' is-alt'}${p.days ? '' : ' is-today'}">` +
                `<td class="bdl-day"><b>${p.next.getDate()}</b><span>${WD[p.next.getDay()]}</span></td>` +
                `<td class="bdl-nm"><b>${nameLink(p)}</b></td>` +
                `<td>${p.gender ? esc(p.gender) : '<span class="bdl-miss">Missing</span>'}</td>` +
                `<td>${p.works ? esc(p.works) : '<span class="bdl-miss">Missing</span>'}</td>` +
                `<td>${fmt(p.d, p.m, p.y)}</td>` +
                `<td class="bdl-r">${p.age}</td>` +
                `<td class="bdl-when"><b>${when(p.days)}</b><span>turns ${p.turns}</span>${tagFor(p.days, p.turns)}</td></tr>`;
        });
        return h + '</tbody></table></div></section>';
    }

    function notesHtml(base, noDob) {
        const notes = [];
        if (noDob.length) {
            notes.push(
                `<li><b>${plural(noDob.length, 'staff member')} ${noDob.length === 1 ? 'has' : 'have'} no date of birth</b> and ${noDob.length === 1 ? 'is' : 'are'} left off the calendar and the list: ${noDob.map(nameLink).join(', ')}.</li>`
            );
        }
        const nw = base.filter((p) => !p.works).length;
        const ng = base.filter((p) => !p.gender).length;
        if (nw || ng) {
            const parts = [];
            if (nw) parts.push(`${plural(nw, 'staff member')} ${nw === 1 ? 'has' : 'have'} no works number`);
            if (ng) parts.push(`${ng} no gender on file`);
            notes.push(`<li>${parts.join(' and ')}.</li>`);
        }
        if (base.some((p) => p.m === 2 && p.d === 29)) notes.push('<li>Birthdays on 29 February are shown on the 28th in other years.</li>');
        notes.push('<li>Milestone marks a birthday ending in 0, or 65.</li>');
        return `<ul class="bdl-notes">${notes.join('')}</ul>`;
    }

    function render() {
        if (!TODAY) return;
        const base = PEOPLE.filter(keep);
        const noDob = NODOB.filter(keep);
        const list = base.filter((p) => !S.month || p.m === S.month);
        const total = base.length + noDob.length;
        document.getElementById('bdlSub').textContent =
            `London Churchill College · ${total} ${S.status === '1' ? 'active ' : S.status === '0' ? 'inactive ' : ''}staff`;

        const up = base.filter((p) => p.days >= 0).sort((a, b) => a.days - b.days || a.name.localeCompare(b.name));
        const nextDay = up.length ? up.filter((p) => p.days === up[0].days) : [];
        const wk = up.filter((p) => p.days <= 7).length;
        const mth = up.filter((p) => p.days <= 30).length;
        const mile = base.filter((p) => milestone(p.turns)).length;

        let h =
            '<div class="bdl-viewbar"><div class="bdl-seg" role="group" aria-label="View">' +
            `<button type="button" data-view="wall" aria-pressed="${S.view === 'wall'}">Calendar</button>` +
            `<button type="button" data-view="list" aria-pressed="${S.view === 'list'}">List</button></div></div>`;

        if (S.view === 'wall') {
            /* next birthday + figures */
            h += '<section class="bdl-hero"><div class="bdl-nextup">';
            if (nextDay.length) {
                const p0 = nextDay[0];
                h +=
                    `<div class="bdl-big"><span>${p0.days === 0 ? 'Birthday today' : 'Next birthday'}</span><b>${when(p0.days)}</b>` +
                    `<i>${WD[p0.next.getDay()]} ${p0.next.getDate()} ${MN[p0.next.getMonth()]}</i></div><ul class="bdl-who">` +
                    nextDay.slice(0, 4).map((p) => `<li><b>${nameLink(p)}</b><span>turns ${p.turns}</span></li>`).join('') +
                    (nextDay.length > 4 ? `<li><span>and ${nextDay.length - 4} more</span></li>` : '') +
                    '</ul>';
            } else {
                h += '<div class="bdl-big"><b>No birthdays found</b></div>';
            }
            h +=
                '</div><div class="bdl-stats">' +
                `<div><b>${wk}</b><span>in the next 7 days</span></div>` +
                `<div><b>${mth}</b><span>in the next 30 days</span></div>` +
                `<div><b>${mile}</b><span>milestone birthdays in the next 12 months</span></div></div></section>`;

            /* wall + side panel */
            const byDay = {};
            base.forEach((p) => (byDay[key(p.occ)] = byDay[key(p.occ)] || []).push(p));
            const sel = S.sel;
            let people;
            let title;
            let hint;
            if (sel.t === 'day') {
                people = (byDay[sel.k] || []).slice().sort((a, b) => a.name.localeCompare(b.name));
                const sd = people[0] ? people[0].occ : null;
                title = sd ? `${WD[sd.getDay()]} ${sd.getDate()} ${MN[sd.getMonth()]} ${sd.getFullYear()}` : 'No birthdays';
                hint = plural(people.length, 'birthday');
            } else if (sel.t === 'month') {
                people = base.filter((p) => `${p.occ.getFullYear()}-${p.occ.getMonth()}` === sel.k).sort((a, b) => a.occ - b.occ || a.name.localeCompare(b.name));
                const [yy, mm] = sel.k.split('-');
                title = `${MN[+mm]} ${yy}`;
                hint = plural(people.length, 'birthday');
            } else {
                people = up.filter((p) => p.days <= 30);
                title = 'Coming up';
                hint = `Next 30 days · ${people.length}`;
            }

            h +=
                '<div class="bdl-wallwrap"><section class="bdl-sec"><div class="bdl-sechead"><h2>The year ahead</h2>' +
                '<span class="bdl-legend"><span><i class="bdl-dot d1"></i>1</span><span><i class="bdl-dot d2"></i>2</span><span><i class="bdl-dot d3"></i>3 or more birthdays</span><span><i class="bdl-dot is-today"></i>Today</span></span></div><div class="bdl-wall">';
            WALL.forEach((m0) => {
                const y = m0.getFullYear();
                const mi = m0.getMonth();
                const dim = new Date(y, mi + 1, 0).getDate();
                const lead = (m0.getDay() + 6) % 7;
                const mk = `${y}-${mi}`;
                const cnt = base.filter((p) => p.occ.getFullYear() === y && p.occ.getMonth() === mi).length;
                const on = sel.t === 'month' && sel.k === mk;
                let cells = '';
                for (let b = 0; b < lead; b++) cells += '<span></span>';
                for (let d = 1; d <= dim; d++) {
                    const dt = new Date(y, mi, d);
                    const k = key(dt);
                    const ps = byDay[k];
                    const isT = dt.getTime() === TODAY.getTime();
                    const past = dt < TODAY;
                    if (ps) {
                        cells +=
                            `<button type="button" class="bdl-dc d${Math.min(3, ps.length)}${isT ? ' is-today' : ''}${past ? ' is-past' : ''}" data-day="${k}" aria-pressed="${sel.t === 'day' && sel.k === k}" aria-label="${d} ${MN[mi]}, ${plural(ps.length, 'birthday')}">` +
                            `${d}${ps.length > 1 ? `<sup>${ps.length}</sup>` : ''}</button>`;
                    } else {
                        cells += `<span class="bdl-dc${isT ? ' is-today' : ''}${past ? ' is-past' : ''}">${d}</span>`;
                    }
                }
                h +=
                    `<div class="bdl-mcal${on ? ' is-on' : ''}"><button type="button" class="bdl-mh" data-ym="${mk}" aria-pressed="${on}">` +
                    `<b>${MN[mi]}</b><span class="bdl-yr">${y}</span><span class="bdl-cnt">${cnt}</span></button>` +
                    '<div class="bdl-dow"><span>M</span><span>T</span><span>W</span><span>T</span><span>F</span><span>S</span><span>S</span></div>' +
                    `<div class="bdl-days">${cells}</div></div>`;
            });
            h +=
                `</div></section><aside class="bdl-side" aria-live="polite"><div class="bdl-sechead"><h2>${esc(title)}</h2><span class="bdl-hint">${esc(hint)}</span></div>` +
                (sel.t !== 'soon' ? '<button type="button" class="bdl-backlink" data-soon>Show coming up</button>' : '') +
                (people.length ? `<ul class="bdl-plist">${people.map(person).join('')}</ul>` : '<div class="bdl-empty">No birthdays here.</div>') +
                '</aside></div>';
        } else {
            h += listHtml(list);
        }
        h += notesHtml(base, noDob);
        app.innerHTML = h;
    }

    /* ---------- events ---------- */
    app.addEventListener('click', (e) => {
        let b;
        const refocus = (sel) => {
            const f = app.querySelector(sel);
            if (f) f.focus();
        };
        if ((b = e.target.closest('[data-day]'))) {
            const k = b.dataset.day;
            S.sel = S.sel.t === 'day' && S.sel.k === k ? { t: 'soon' } : { t: 'day', k };
            render();
            refocus(`[data-day="${k}"]`);
        } else if ((b = e.target.closest('[data-ym]'))) {
            const k = b.dataset.ym;
            S.sel = S.sel.t === 'month' && S.sel.k === k ? { t: 'soon' } : { t: 'month', k };
            render();
            refocus(`[data-ym="${k}"]`);
        } else if (e.target.closest('[data-soon]')) {
            S.sel = { t: 'soon' };
            render();
        } else if ((b = e.target.closest('[data-view]'))) {
            S.view = b.dataset.view;
            render();
            refocus(`[data-view="${S.view}"]`);
        } else if ((b = e.target.closest('[data-order]'))) {
            S.order = b.dataset.order;
            render();
            refocus(`[data-order="${S.order}"]`);
        }
    });

    // A filter change is this version's search: redraw, and fill the Old version and run it.
    ['month', 'type', 'department', 'status'].forEach((k) =>
        field(k).addEventListener('change', () => {
            S[k] = k === 'month' ? +field(k).value : field(k).value;
            if (k === 'month') S.sel = monthSel();
            render();
            toOld();
        })
    );
    field('q').addEventListener('input', () => {
        S.q = field('q').value;
        render();
    });
    root.querySelector('[data-bdl-reset]').addEventListener('click', () => {
        Object.assign(S, { month: 0, type: '', department: '', status: '1', q: '', sel: { t: 'soon' } });
        syncForm();
        render();
        toOld();
    });

    /* ---------- exports (same filters and list order as on screen) ---------- */
    function toast(message) {
        let t = document.querySelector('.bdl-toast');
        if (!t) {
            t = document.createElement('div');
            t.className = 'bdl-toast';
            t.setAttribute('role', 'status');
            document.body.appendChild(t);
        }
        t.textContent = message;
        t.classList.add('show');
        clearTimeout(toast.timer);
        toast.timer = setTimeout(() => t.classList.remove('show'), 2600);
    }
    root.querySelectorAll('[data-bdl-export]').forEach((btn) =>
        btn.addEventListener('click', () => {
            if (btn.classList.contains('is-busy')) return;
            const kind = btn.dataset.bdlExport;
            const params = { month: S.month, type: S.type, department: S.department, status: S.status, q: S.q.trim(), order: kind === 'pdf' ? 'cal' : S.order };
            btn.classList.add('is-busy');
            btn.disabled = true;
            axios({ method: 'get', url: kind === 'pdf' ? root.dataset.pdfUrl : root.dataset.excelUrl, params, responseType: 'blob' })
                .then((response) => {
                    const disposition = response.headers['content-disposition'] || '';
                    const match = /filename="?([^";]+)"?/i.exec(disposition);
                    const url = window.URL.createObjectURL(new Blob([response.data]));
                    const link = document.createElement('a');
                    link.href = url;
                    link.setAttribute('download', match ? match[1] : kind === 'pdf' ? 'Birthday_List.pdf' : 'Birthday_List.xlsx');
                    document.body.appendChild(link);
                    link.click();
                    link.remove();
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
    root.querySelectorAll('[data-bdl-version]').forEach((button) =>
        button.addEventListener('click', () => {
            const v = button.dataset.bdlVersion;
            root.querySelectorAll('[data-bdl-version]').forEach((b) => b.setAttribute('aria-pressed', String(b === button)));
            root.querySelectorAll('[data-bdl-pane]').forEach((pane) => {
                const off = pane.dataset.bdlPane !== v;
                pane.classList.toggle('is-off', off);
                pane.setAttribute('aria-hidden', String(off));
            });
            root.querySelectorAll('[data-bdl-new-only]').forEach((el) => (el.hidden = v !== 'new'));
            // The old table listens for resize to redraw itself.
            window.dispatchEvent(new Event('resize'));
        })
    );

    const OLD = { month: '#dob-BR', type: '#employee_work_type_id-birtdaylist', department: '#department_id-birtdaylist', status: '#status_id-birtdaylist' };
    const goBtn = document.getElementById('tabulator-html-filter-go-BR');
    const resetBtn = document.getElementById('tabulator-html-filter-reset-BR');
    let mirroring = false;
    const oldValue = (sel) => {
        const el = document.querySelector(sel);
        if (!el) return '';
        return String(el.tomselect ? el.tomselect.getValue() : el.value);
    };
    const setOld = (sel, v) => {
        const el = document.querySelector(sel);
        if (!el) return;
        if (el.tomselect) el.tomselect.setValue(v, true); // silent: no change handlers
        else el.value = v;
    };
    function toOld() {
        if (!goBtn) return;
        const dob = document.querySelector(OLD.month);
        if (dob) dob.value = S.month ? `${(TODAY || new Date()).getFullYear()}-${String(S.month).padStart(2, '0')}` : '';
        setOld(OLD.type, S.type);
        setOld(OLD.department, S.department);
        setOld(OLD.status, S.status);
        mirroring = true;
        try {
            $(goBtn).trigger('click');
        } finally {
            mirroring = false;
        }
    }
    function fromOld() {
        const m = /^\d{4}-(\d{1,2})/.exec(oldValue(OLD.month));
        S.month = m ? Math.min(12, Math.max(0, +m[1])) : 0;
        S.type = oldValue(OLD.type);
        S.department = oldValue(OLD.department);
        const status = oldValue(OLD.status);
        S.status = ['0', '1', '2'].includes(status) ? status : '1';
        S.q = '';
        S.sel = monthSel();
        syncForm();
        render();
    }
    if (goBtn) goBtn.addEventListener('click', () => !mirroring && fromOld());
    // The old Reset sets its fields in its own click handler (bound first); read them after it.
    if (resetBtn) resetBtn.addEventListener('click', () => !mirroring && setTimeout(fromOld, 0));

    /* ---------- load ---------- */
    syncForm();
    axios
        .get(root.dataset.dataUrl)
        .then(({ data }) => {
            const [y, m, d] = String(data.today).split('-').map(Number);
            TODAY = new Date(y, m - 1, d);
            WALL = [];
            for (let i = 0; i < 12; i++) WALL.push(new Date(TODAY.getFullYear(), TODAY.getMonth() + i, 1));
            PEOPLE = [];
            NODOB = [];
            (data.staff || []).forEach((r) => (r.dob ? PEOPLE.push(enrich(r)) : NODOB.push(r)));
            render();
        })
        .catch(() => {
            app.innerHTML = '<div class="bdl-empty">The birthday list could not be loaded. Please reload the page.</div>';
        });
})();
