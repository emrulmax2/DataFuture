/*
 * HR Portal › Reports › Employee Sick Leave, new version (user's employee-sick-leave design, 26 Nov 2026).
 *
 * Search loads everyone with approved sick leave in the period (App\Services\HR\SickLeave, the old
 * report's rules). The figures, the day-by-day calendar (month by month for ranges over 100 days), the
 * weekday bars and the people timeline are drawn here; Export XL sends the same view to the server.
 *
 * Old version: a Search here fills the old form (employees, Days, From / To) and runs it while the Old
 * pane is hidden; an old Search fills this form and runs it. The old form lets you pick several
 * employees; that choice shows here as "N selected employees".
 */
import { hrDatePickers } from './hr-report-datepicker';

(function () {
    'use strict';

    const root = document.querySelector('[data-esk]');
    if (!root) return;
    hrDatePickers(root); // Litepicker on the date filters (ISO values stay on data-*-f)

    const app = root.querySelector('[data-esk-app]');
    const form = root.querySelector('[data-esk-form]');
    const field = (k) => root.querySelector(`[data-esk-f="${k}"]`);
    const esc = (s) => String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    const MN = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const WD = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    const DAY = 864e5;
    const DAILY_MAX = 100; // longer ranges are drawn month by month / week by week
    const PER = 25;
    const OLD_DAYS = ['30', '45', '60', '90']; // the old form's Days options

    let D = null; // {filters, from, to, staff}
    let A = null; // applied {emp: [], period, from, to}
    let multi = []; // employees chosen on the old form when there are several
    const S = { day: '', month: '', sort: 'name', page: 0, open: {} };

    const dd = (s) => {
        const p = s.split('-');
        return new Date(+p[0], +p[1] - 1, +p[2]);
    };
    const iso = (d) => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
    const fmt = (s) => {
        const d = typeof s === 'string' ? dd(s) : s;
        return `${d.getDate()} ${MN[d.getMonth()]} ${d.getFullYear()}`;
    };
    const short = (s) => {
        const d = dd(s);
        return `${d.getDate()} ${MN[d.getMonth()]}`;
    };
    const wd = (d) => (d.getDay() + 6) % 7;
    const dmy = (s) => (s ? s.split('-').reverse().join('-') : '');
    const isoOf = (s) => (/^\d{2}-\d{2}-\d{4}$/.test(s || '') ? s.split('-').reverse().join('-') : '');
    const today = () => iso(new Date());
    const daysAgo = (n) => iso(new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate() - n));

    function spells(dates) {
        const out = [];
        dates.forEach((s) => {
            const cur = out[out.length - 1];
            if (cur && (dd(s) - dd(cur[cur.length - 1])) / DAY <= 1.01) cur.push(s);
            else out.push([s]);
        });
        return out;
    }

    /* ---------- the form ---------- */
    function syncPeriod() {
        const v = field('period').value;
        const custom = v === 'custom';
        field('from').disabled = !custom;
        field('to').disabled = !custom;
        if (/^\d+$/.test(v)) {
            field('from').value = daysAgo(+v);
            field('to').value = today();
        } else if (v === 'all') {
            field('from').value = '';
            field('to').value = '';
        }
    }
    function setMulti(ids) {
        multi = ids;
        const sel = field('emp');
        let o = sel.querySelector('option[value="__multi"]');
        if (ids.length > 1) {
            if (!o) {
                o = document.createElement('option');
                o.value = '__multi';
                sel.insertBefore(o, sel.options[1] || null);
            }
            o.textContent = `${ids.length} selected employees`;
            sel.value = '__multi';
        } else {
            if (o) o.remove();
            sel.value = ids.length ? String(ids[0]) : '';
        }
    }
    function readForm() {
        const e = field('emp').value;
        return {
            emp: e === '__multi' ? multi.slice() : e ? [e] : [],
            period: field('period').value,
            from: field('from').value,
            to: field('to').value,
        };
    }
    const same = (a, b) => a.period === b.period && a.emp.join() === b.emp.join() && (a.period !== 'custom' || (a.from === b.from && a.to === b.to));
    const stale = () => A && !same(readForm(), A);
    const note = (msg) => {
        const p = root.querySelector('[data-esk-formnote]');
        p.textContent = msg || '';
        p.hidden = !msg;
    };
    function valid() {
        const f = readForm();
        if (f.period !== 'custom') return note(''), true;
        if (!f.from) return note('Choose a From date.'), field('from').focus(), false;
        if (f.to && f.to < f.from) return note('To must be on or after From.'), field('to').focus(), false;
        note('');
        return true;
    }

    /* ---------- drawing ---------- */
    function render() {
        if (!D) return;
        const P = D.staff;
        const f = D.filters;
        const from = D.from;
        const to = D.to;
        const a = dd(from);
        const b = dd(to);
        const n = Math.round((b - a) / DAY) + 1;
        const daily = n <= DAILY_MAX;
        let h = stale() ? '<div class="esk-stale">The filters have changed. Select Search to update the report.</div>' : '';
        const who = !A.emp.length ? '' : A.emp.length === 1 ? ` · ${esc((P[0] && P[0].name) || field('emp').selectedOptions[0].textContent)}` : ` · ${A.emp.length} selected employees`;
        document.getElementById('eskSub').textContent = `London Churchill College · HR reports · ${f.label}`;
        h += `<div class="esk-period"><b>${esc(f.label)}</b> · ${fmt(from)} to ${fmt(to)}${who}</div>`;
        if (!P.length) {
            app.innerHTML = h + '<div class="esk-empty" style="margin-top:14px">No sick leave recorded in this period.</div>';
            return;
        }
        let days = 0;
        let sp = 0;
        const byDay = {};
        const byWd = [0, 0, 0, 0, 0, 0, 0];
        const byMonth = {};
        P.forEach((p) => {
            sp += spells(p.dates).length;
            p.dates.forEach((s) => {
                days++;
                (byDay[s] = byDay[s] || []).push(p);
                byWd[wd(dd(s))]++;
                const m = s.slice(0, 7);
                const o = byMonth[m] || (byMonth[m] = { days: 0, people: new Set() });
                o.days++;
                o.people.add(p.id);
            });
        });
        const peak = Object.keys(byDay).sort((x, y) => byDay[y].length - byDay[x].length || (x < y ? -1 : 1))[0];
        h +=
            '<div class="esk-stats">' +
            `<div class="esk-stat"><b>${days}<small>day${days === 1 ? '' : 's'}</small></b><span>of sick leave</span></div>` +
            `<div class="esk-stat"><b>${P.length}</b><span>employee${P.length === 1 ? '' : 's'} off sick · ${sp} separate absence${sp === 1 ? '' : 's'}</span></div>` +
            `<div class="esk-stat"><b>${(days / P.length).toFixed(1)}<small>days</small></b><span>average per employee off</span></div>` +
            `<div class="esk-stat"><b>${short(peak)}${daily ? '' : ` <small>${dd(peak).getFullYear()}</small>`}</b><span>busiest day · ${byDay[peak].length} off (${WD[wd(dd(peak))]})</span></div></div>`;

        /* calendar: day by day, or month by month for long ranges */
        let cal;
        if (daily) {
            const start = new Date(a.getTime() - wd(a) * DAY);
            const end = new Date(b.getTime() + (6 - wd(b)) * DAY);
            const t0 = today();
            let cells = '';
            for (let t = new Date(start); t <= end; t = new Date(t.getFullYear(), t.getMonth(), t.getDate() + 1)) {
                const k = iso(t);
                const inR = t >= a && t <= b;
                const ps = byDay[k];
                const we = wd(t) > 4;
                const lab = t.getDate() + (t.getDate() === 1 || +t === +a ? ` ${MN[t.getMonth()]}` : '');
                if (!inR) cells += '<span class="esk-day out"></span>';
                else if (ps)
                    cells +=
                        `<button type="button" class="esk-day l${Math.min(3, ps.length)}${k === t0 ? ' today' : ''}" data-day="${k}" aria-pressed="${S.day === k}" aria-label="${fmt(k)}: ${ps.length} off sick">` +
                        `<span>${lab}${we ? ' <small>weekend</small>' : ''}</span><b>${ps.length}</b></button>`;
                else cells += `<span class="esk-day${we ? ' we' : ''}${k === t0 ? ' today' : ''}"><span>${lab}</span></span>`;
            }
            cal =
                '<section class="esk-sec"><div class="esk-sechead"><h2>Who was off, day by day</h2><span class="esk-hint">Number of employees off sick · select a day to see who</span></div>' +
                `<div class="esk-cal">${WD.map((w) => `<span class="wd">${w}</span>`).join('')}${cells}</div></section>`;
        } else {
            let cells = '';
            for (let m = new Date(a.getFullYear(), a.getMonth(), 1); m <= b; m = new Date(m.getFullYear(), m.getMonth() + 1, 1)) {
                const k = `${m.getFullYear()}-${String(m.getMonth() + 1).padStart(2, '0')}`;
                const o = byMonth[k];
                const lab = `${MN[m.getMonth()]} ${String(m.getFullYear()).slice(2)}`;
                cells += o
                    ? `<button type="button" class="esk-day l${o.people.size >= 5 ? 3 : o.people.size >= 2 ? 2 : 1}" data-month="${k}" aria-pressed="${S.month === k}" aria-label="${MN[m.getMonth()]} ${m.getFullYear()}: ${o.days} sick days, ${o.people.size} employees"><span>${lab} <small>${o.people.size} off</small></span><b>${o.days}</b></button>`
                    : `<span class="esk-day"><span>${lab}</span></span>`;
            }
            cal =
                '<section class="esk-sec"><div class="esk-sechead"><h2>Sick days, month by month</h2><span class="esk-hint">Sick days in each month · select a month to see who</span></div>' +
                `<div class="esk-cal is-months">${cells}</div></section>`;
        }
        const wmax = Math.max(1, ...byWd);
        h +=
            `<div class="esk-two">${cal}` +
            '<section class="esk-sec"><div class="esk-sechead"><h2>By day of the week</h2><span class="esk-hint">Sick days</span></div><div class="esk-wk">' +
            WD.map((w, k) => `<div class="esk-wkr${byWd[k] ? '' : ' z'}"><span>${w}</span><span class="esk-meter"><i style="width:${(100 * byWd[k]) / wmax}%"></i></span><b>${byWd[k]}</b></div>`).join('') +
            `</div><div class="esk-hint">${byWd[0] + byWd[1]} of ${days} sick days fell on a Monday or Tuesday${byWd[5] + byWd[6] ? `; ${byWd[5] + byWd[6]} on a weekend` : ''}.</div></section></div>`;

        h +=
            '<section class="esk-sec"><div class="esk-tools"><div><h2 data-esk-ltitle></h2><span class="esk-hint" data-esk-lhint></span></div>' +
            '<label class="esk-field"><span>Sort by</span><select class="esk-ctl" data-esk-sort><option value="name">Name order</option><option value="most">Most days first</option><option value="recent">Most recent first</option></select></label></div>' +
            `<div class="esk-scroll"><div class="esk-tl" data-esk-tl></div></div><div class="esk-hint">${daily ? 'Each square is one day; Mondays are numbered. Striped squares are weekends.' : 'Each square is one week; the month is shown at its first week.'}</div></section>`;

        const weekend = [];
        P.forEach((p) => p.dates.forEach((s) => wd(dd(s)) > 4 && weekend.push([p, s])));
        const longOld = P.filter((p) => p.dates.length * 12 > 1024).length;
        const notes = [];
        if (weekend.length)
            notes.push(
                `<li><b>${weekend.length === 1 ? 'One sick day is' : `${weekend.length} sick days are`} on a Saturday or Sunday</b>` +
                    (weekend.length <= 3 ? ` (${weekend.map(([p, s]) => `${short(s)}, ${esc(p.name)}, contracted ${esc(p.hours)}`).join('; ')})` : '') +
                    '. Check whether they were due to work those days.</li>'
            );
        notes.push('<li>Consecutive days are joined into one absence, so "2 to 3 Nov (2 days)" replaces two separate dates.</li>');
        if (longOld) notes.push(`<li>The old version cuts long date lists short (a database text limit of 1,024 characters), so ${longOld} employee${longOld === 1 ? '' : 's'} show only part of their dates there. All dates are listed here and in the export.</li>`);
        notes.push('<li>The Employee list holds active staff who clock in, as on the old version; the report itself lists anyone with sick leave in the period.</li>');
        h += `<ul class="esk-notes">${notes.join('')}</ul>`;
        app.innerHTML = h;
        app.querySelector('[data-esk-sort]').value = S.sort;
        draw();
    }

    function draw() {
        const a = dd(D.from);
        const b = dd(D.to);
        const n = Math.round((b - a) / DAY) + 1;
        const daily = n <= DAILY_MAX;
        let list = D.staff.filter((p) => (!S.day || p.dates.includes(S.day)) && (!S.month || p.dates.some((s) => s.startsWith(S.month))));
        if (S.sort === 'most') list = list.slice().sort((x, y) => y.dates.length - x.dates.length || x.name.localeCompare(y.name));
        if (S.sort === 'recent') list = list.slice().sort((x, y) => (y.dates[y.dates.length - 1] > x.dates[x.dates.length - 1] ? 1 : -1));
        const mLabel = S.month ? `${MN[+S.month.slice(5) - 1]} ${S.month.slice(0, 4)}` : '';
        app.querySelector('[data-esk-ltitle]').textContent = S.day ? `Off sick on ${fmt(S.day)}` : S.month ? `Off sick in ${mLabel}` : 'Employees off sick';
        app.querySelector('[data-esk-lhint]').innerHTML =
            `${list.length} employee${list.length === 1 ? '' : 's'}` + (S.day || S.month ? ' · <button type="button" class="esk-lk" data-clearday>Show the whole period</button>' : '');

        // columns: days, or weeks (Monday to Sunday) for long ranges
        const cols = [];
        if (daily) {
            for (let q = 0; q < n; q++) {
                const d = new Date(a.getFullYear(), a.getMonth(), a.getDate() + q);
                cols.push({ from: iso(d), to: iso(d), we: wd(d) > 4, label: wd(d) === 0 || q === 0 ? String(d.getDate()) : '', m: wd(d) === 0 });
            }
        } else {
            let lastM = -1;
            for (let s = new Date(a.getTime() - wd(a) * DAY); s <= b; s = new Date(s.getFullYear(), s.getMonth(), s.getDate() + 7)) {
                const e = new Date(s.getFullYear(), s.getMonth(), s.getDate() + 6);
                const m = s.getMonth();
                cols.push({ from: iso(s), to: iso(e), we: false, label: m !== lastM ? MN[m] : '', m: m !== lastM });
                lastM = m;
            }
        }
        const grid = `grid-template-columns:repeat(${cols.length},minmax(0,1fr))`;
        const pages = Math.max(1, Math.ceil(list.length / PER));
        S.page = Math.min(Math.max(0, S.page), pages - 1);
        const part = list.slice(S.page * PER, S.page * PER + PER);
        let h =
            '<div class="esk-th"><span>Employee</span><span class="esk-r">Contracted</span><span class="esk-r">Days</span>' +
            `<div class="esk-strip" style="${grid}">${cols.map((c) => `<span class="${c.m ? 'm' : ''}">${c.label}</span>`).join('')}</div><span>Dates</span></div>`;
        part.forEach((p) => {
            const ss = spells(p.dates);
            const shownSpells = S.open[p.id] ? ss : ss.slice(0, 4);
            const strip = cols
                .map((c) => {
                    const on = p.dates.some((s) => s >= c.from && s <= c.to);
                    const sel = on && S.day && S.day >= c.from && S.day <= c.to;
                    return `<i class="${c.we ? 'we ' : ''}${on ? 'on ' : ''}${sel ? 'sel' : ''}"></i>`;
                })
                .join('');
            h +=
                `<div class="esk-tr"><span class="nm"><a href="${esc(root.dataset.profileUrl.replace('__ID__', p.id))}">${esc(p.name)}</a>${p.job ? `<small>${esc(p.job)}</small>` : ''}</span>` +
                `<span class="esk-r">${esc(p.hours.replace(':', 'h ') + 'm')}</span><span class="esk-r"><b>${p.dates.length}</b></span>` +
                `<div class="esk-strip" style="${grid}" role="img" aria-label="${p.dates.map(fmt).join(', ')}">${strip}</div>` +
                '<span class="esk-spells">' +
                shownSpells
                    .map(
                        (g) =>
                            `<b>${g.length > 1 ? `${g[0].slice(0, 7) === g[g.length - 1].slice(0, 7) ? dd(g[0]).getDate() : short(g[0])} to ${short(g[g.length - 1])}` : short(g[0])}${daily ? '' : ` ${g[g.length - 1].slice(0, 4)}`}</b>` +
                            (g.length > 1 ? ` (${g.length} days)` : '') +
                            (g.some((s) => wd(dd(s)) > 4) ? '<span class="esk-tag">Weekend</span>' : '')
                    )
                    .join(', ') +
                (ss.length > 4 ? ` <button type="button" class="esk-more" data-more="${p.id}">${S.open[p.id] ? 'Show fewer' : `and ${ss.length - 4} more`}</button>` : '') +
                '</span></div>';
        });
        h +=
            `<div class="esk-foot" style="margin-top:10px"><span>${list.length ? `Showing ${S.page * PER + 1} to ${S.page * PER + part.length} of ${list.length}` : '0 employees'}</span>` +
            (pages > 1
                ? `<span class="esk-pager"><button type="button" class="esk-btn" data-page="-1"${S.page ? '' : ' disabled'}>Previous</button><span>Page ${S.page + 1} of ${pages}</span><button type="button" class="esk-btn" data-page="1"${S.page < pages - 1 ? '' : ' disabled'}>Next</button></span>`
                : '') +
            '</div>';
        app.querySelector('[data-esk-tl]').innerHTML = h;
    }

    /* ---------- search ---------- */
    const exportBtn = root.querySelector('[data-esk-export]');
    function load(values, mirror) {
        A = values;
        Object.assign(S, { day: '', month: '', page: 0, open: {} });
        app.innerHTML = '<div class="esk-empty">Loading sick leave…</div>';
        exportBtn.disabled = true;
        axios
            .get(root.dataset.dataUrl, { params: values })
            .then(({ data }) => {
                D = data;
                render();
                exportBtn.disabled = !D.staff.length;
            })
            .catch(() => {
                app.innerHTML = '<div class="esk-empty">The sick leave could not be loaded. Please try again.</div>';
            });
        if (mirror) toOld(values);
    }
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        if (valid()) load(readForm(), true);
    });
    field('period').addEventListener('change', syncPeriod);
    field('from').addEventListener('change', () => (field('to').min = field('from').value));
    form.addEventListener('change', () => D && render());

    /* ---------- report events ---------- */
    app.addEventListener('click', (e) => {
        let b;
        if ((b = e.target.closest('[data-day]'))) {
            const k = b.dataset.day;
            S.day = S.day === k ? '' : k;
            S.page = 0;
            render();
            const x = app.querySelector(`[data-day="${k}"]`);
            if (x) x.focus();
        } else if ((b = e.target.closest('[data-month]'))) {
            const k = b.dataset.month;
            S.month = S.month === k ? '' : k;
            S.page = 0;
            render();
            const x = app.querySelector(`[data-month="${k}"]`);
            if (x) x.focus();
        } else if (e.target.closest('[data-clearday]')) {
            S.day = '';
            S.month = '';
            S.page = 0;
            render();
        } else if ((b = e.target.closest('[data-more]'))) {
            S.open[b.dataset.more] = !S.open[b.dataset.more];
            draw();
        } else if ((b = e.target.closest('[data-page]'))) {
            S.page += +b.dataset.page;
            draw();
        }
    });
    app.addEventListener('change', (e) => {
        if (e.target.matches('[data-esk-sort]')) {
            S.sort = e.target.value;
            S.page = 0;
            draw();
            app.querySelector('[data-esk-sort]').focus();
        }
    });

    /* ---------- download ---------- */
    function toast(message) {
        let t = document.querySelector('.esk-toast');
        if (!t) {
            t = document.createElement('div');
            t.className = 'esk-toast';
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
        const params = { ...A, day: S.day, month: S.month, sort: S.sort };
        axios({ method: 'get', url: root.dataset.excelUrl, params, responseType: 'blob' })
            .then((response) => {
                const m = /filename="?([^";]+)"?/i.exec(response.headers['content-disposition'] || '');
                const href = window.URL.createObjectURL(new Blob([response.data]));
                const a = document.createElement('a');
                a.href = href;
                a.setAttribute('download', m ? m[1] : 'Sick_Leave.xlsx');
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
    root.querySelectorAll('[data-esk-version]').forEach((button) =>
        button.addEventListener('click', () => {
            const v = button.dataset.eskVersion;
            root.querySelectorAll('[data-esk-version]').forEach((b) => b.setAttribute('aria-pressed', String(b === button)));
            root.querySelectorAll('[data-esk-pane]').forEach((pane) => {
                const off = pane.dataset.eskPane !== v;
                pane.classList.toggle('is-off', off);
                pane.setAttribute('aria-hidden', String(off));
            });
            root.querySelectorAll('[data-esk-new-only]').forEach((el) => (el.hidden = v !== 'new'));
            window.dispatchEvent(new Event('resize')); // the old table redraws on resize
        })
    );

    // The old form: Employee (Tom Select, several), Days (30/45/60/90), From / To (DD-MM-YYYY), Search.
    const oldGo = document.getElementById('searchSickLeave');
    let mirroring = false;
    function toOld(v) {
        if (!oldGo || !window.$) return;
        const emp = document.getElementById('employee_id');
        if (emp && emp.tomselect) emp.tomselect.setValue(v.emp.map(String), true);
        const days = OLD_DAYS.includes(v.period) ? v.period : '';
        document.getElementById('no_of_days').value = days;
        let from = '';
        let to = '';
        if (v.period === '7') [from, to] = [daysAgo(7), today()];
        else if (v.period === 'custom') [from, to] = [v.from, v.to];
        document.getElementById('from_date').value = dmy(from);
        document.getElementById('to_date').value = dmy(to);
        mirroring = true;
        try {
            $(oldGo).trigger('click');
        } finally {
            mirroring = false;
        }
    }
    if (oldGo && window.$) {
        $(oldGo).on('click', () => {
            if (mirroring) return;
            const ids = ($('#employee_id').val() || []).filter(Boolean);
            const days = document.getElementById('no_of_days').value;
            const from = isoOf(document.getElementById('from_date').value);
            const to = isoOf(document.getElementById('to_date').value);
            setMulti(ids);
            field('period').value = days ? days : from ? 'custom' : 'all';
            syncPeriod();
            if (!days && from) {
                field('from').value = from;
                field('to').value = to;
            }
            note('');
            load({ emp: ids, period: field('period').value, from: days ? '' : from, to: days ? '' : to }, false);
        });
    }

    /* ---------- start ---------- */
    ['from', 'to'].forEach((k) => (field(k).max = today()));
    syncPeriod();
    load(readForm(), true);
})();
