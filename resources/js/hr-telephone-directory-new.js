/*
 * HR Portal › Reports › Employee Telephone Directory, new version (user's employee-telephone-directory
 * design, 26 Nov 2026).
 *
 * One GET loads every active and inactive staff member with their numbers already tidied
 * (App\Services\HR\TelephoneDirectory). Search, the A–Z rail and the quick filters work as you type and
 * click; the eight HR filters under More filters apply with Go (old rules). The PDF and Excel take the
 * same choices (TelephoneDirectory::rows and ::shown).
 *
 * The Old version sits beside it: Go here fills the old form and runs its Go (the Old pane is off screen,
 * not display:none, so its table lays out); the old Go or Reset fills this form and applies it.
 */
import { hrDatePickers } from './hr-report-datepicker';

(function () {
    'use strict';

    const root = document.querySelector('[data-etd]');
    if (!root) return;
    hrDatePickers(root); // Litepicker on the date filters (ISO values stay on data-*-f)

    const app = root.querySelector('[data-etd-app]');
    const rail = root.querySelector('[data-etd-rail]');
    const form = root.querySelector('[data-etd-form]');
    const qInput = root.querySelector('[data-etd-q]');
    const field = (k) => root.querySelector(`[data-etd-f="${k}"]`);
    const esc = (s) => String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    const FILTERS = ['type', 'department', 'from', 'to', 'ethnicity', 'nationality', 'gender', 'status'];
    const CHIPS = [
        ['land', 'Has a landline', (p) => p.land],
        ['nomob', 'No mobile', (p) => !p.mob],
        ['noemail', 'No email', (p) => !p.email],
    ];
    const ETH = JSON.parse(root.dataset.ethnicities || '[]');
    const LETTERS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ#'.split('');

    const blank = () => ({ type: '', department: '', from: '', to: '', ethnicity: '', nationality: '', gender: '', status: '1' });
    let A = blank();
    const S = { q: '', letter: '', chip: '' };
    let STAFF = null;

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
    const digits = (v) => String(v).replace(/\D/g, '');
    function match(p, q) {
        if (!q) return true;
        const d = q.replace(/\s/g, '');
        return p.name.toLowerCase().includes(q) || p.email.toLowerCase().includes(q) || (/\d/.test(d) && digits(p.mob + p.tel).includes(digits(d)));
    }
    function hl(s, q) {
        if (!q) return esc(s);
        const i = s.toLowerCase().indexOf(q);
        return i < 0 ? esc(s) : `${esc(s.slice(0, i))}<mark>${esc(s.slice(i, i + q.length))}</mark>${esc(s.slice(i + q.length))}`;
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
        // Nationality names are not in this report's data: keep the ids staff hold, named from the old list.
        const cn = count('nationalityId');
        const nat = field('nationality').value;
        const oldNat = document.getElementById('nationality-telephonedirectory');
        const name = (id) => {
            const o = oldNat && oldNat.querySelector(`option[value="${CSS.escape(String(id))}"]`);
            return o ? o.textContent.trim() : `Country #${id}`;
        };
        const ids = Object.keys(cn).filter((id) => id && id !== 'null').concat(nat && !cn[nat] ? [nat] : []).sort((a, b) => name(a).localeCompare(name(b)));
        field('nationality').innerHTML = '<option value="">All nationalities</option>' + ids.map((id) => `<option value="${id}">${esc(name(id))} (${cn[id] || 0})</option>`).join('');
        field('nationality').value = nat;
    }
    const readForm = () => FILTERS.reduce((o, k) => ((o[k] = field(k).value), o), {});
    const writeForm = (f) => FILTERS.forEach((k) => (field(k).value = f[k]));
    function syncMore() {
        root.querySelector('[data-etd-stale]').hidden = !FILTERS.some((k) => field(k).value !== A[k]);
        const on = FILTERS.filter((k) => (k === 'status' ? A[k] !== '1' : A[k])).length;
        root.querySelector('[data-etd-more-note]').innerHTML = on ? `<b>· ${on} applied</b>` : '';
    }

    function render() {
        if (!STAFF) return;
        syncMore();
        const q = S.q.trim().toLowerCase();
        const scoped = STAFF.filter(keep);
        const base = scoped.filter((p) => match(p, q));
        const chip = CHIPS.find((c) => c[0] === S.chip);
        const pool = chip ? base.filter(chip[2]) : base;
        const counts = {};
        pool.forEach((p) => (counts[p.letter] = (counts[p.letter] || 0) + 1));
        if (S.letter && !counts[S.letter]) S.letter = '';
        const list = pool.filter((p) => !S.letter || p.letter === S.letter);
        document.getElementById('etdSub').textContent = `London Churchill College · ${scoped.length} ${A.status === '1' ? 'active ' : A.status === '0' ? 'inactive ' : ''}staff`;

        rail.innerHTML =
            `<div class="etd-az" role="group" aria-label="Jump to a letter"><button type="button" class="is-all" data-letter="" aria-pressed="${!S.letter}">All</button>` +
            LETTERS.map((l) =>
                counts[l]
                    ? `<button type="button" data-letter="${l}" aria-pressed="${S.letter === l}" aria-label="${l}, ${counts[l]} people" title="${counts[l]}">${l}</button>`
                    : l === '#' ? '' : `<span aria-hidden="true">${l}</span>`
            ).join('') +
            '</div><div class="etd-chips" role="group" aria-label="Quick filters">' +
            CHIPS.map((c) => `<button type="button" class="etd-chip" data-chip="${c[0]}" aria-pressed="${S.chip === c[0]}">${c[1]}<b>${base.filter(c[2]).length}</b></button>`).join('') +
            '</div>';

        let h =
            `<div class="etd-count">${list.length} of ${scoped.length} staff` +
            (q ? ` matching “${esc(S.q.trim())}”` : '') + (S.letter ? ` · letter ${S.letter}` : '') + (chip ? ` · ${chip[1].toLowerCase()}` : '') + '</div>';
        if (!list.length) {
            h += '<div class="etd-empty">Nobody matches. Check the spelling, or try part of the number.</div>';
        } else {
            const groups = {};
            list.forEach((p) => (groups[p.letter] = groups[p.letter] || []).push(p));
            h += '<div class="etd-book">';
            Object.keys(groups)
                .sort((a, b) => (a === '#') - (b === '#') || a.localeCompare(b))
                .forEach((l) => {
                    const people = groups[l].sort((a, b) => a.name.localeCompare(b.name, undefined, { sensitivity: 'base' }));
                    h +=
                        `<section class="etd-letter" id="etd-${l === '#' ? 'other' : l}"><div class="etd-lt">${l}<small>${people.length}</small></div><div class="etd-entries">` +
                        people
                            .map((p) => {
                                const tel = (v) => `href="tel:${esc(v.replace(/\s/g, ''))}"`;
                                const main = p.mob || p.tel;
                                const profile = root.dataset.profileUrl.replace('__ID__', p.id);
                                return (
                                    `<div class="etd-e"><div class="etd-l1"><span class="etd-n"><a href="${esc(profile)}">${hl(p.name, q)}</a></span><span class="etd-dots" aria-hidden="true"></span>` +
                                    (main ? `<a class="etd-num" ${tel(main)}>${esc(main)}</a>` : '<span class="etd-nof">No number on file</span>') +
                                    `</div><div class="etd-l2"><span>${p.email ? `<a href="mailto:${esc(p.email)}">${hl(p.email, q)}</a>` : '<span class="etd-nof">No email on file</span>'}</span>` +
                                    (p.tel && p.mob ? `<span>${p.land ? 'Landline' : 'Also'} <a ${tel(p.tel)}>${esc(p.tel)}</a></span>` : '') +
                                    '</div></div>'
                                );
                            })
                            .join('') +
                        '</div></section>';
                });
            h += '</div>';
        }
        const dup = scoped.filter((p) => p.dup).length;
        const notes = ['<li>Filed by first name, as before.</li>', '<li>Numbers are shown in one format (07700 900123, 020 7946 0123); +44 numbers are written with a 0.</li>'];
        if (dup) notes.push(`<li>${dup} staff have the same number in Telephone and Mobile; it is shown once.</li>`);
        h += `<ul class="etd-notes">${notes.join('')}</ul>`;
        app.innerHTML = h;
    }

    /* ---------- events ---------- */
    rail.addEventListener('click', (e) => {
        let b;
        if ((b = e.target.closest('[data-letter]'))) {
            S.letter = b.dataset.letter;
            render();
            const f = rail.querySelector(`[data-letter="${S.letter}"]`);
            if (f) f.focus();
        } else if ((b = e.target.closest('[data-chip]'))) {
            const c = b.dataset.chip;
            S.chip = S.chip === c ? '' : c;
            render();
            const g = rail.querySelector(`[data-chip="${c}"]`);
            if (g) g.focus();
        }
    });
    qInput.addEventListener('input', () => {
        S.q = qInput.value;
        render();
    });
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        if (FILTERS.some((k) => field(k).value !== A[k])) {
            A = readForm();
            render();
            toOld();
        }
    });
    FILTERS.forEach((k) =>
        field(k).addEventListener('change', () => {
            if (k === 'status') fillOptions();
            syncMore();
        })
    );
    root.querySelector('[data-etd-reset]').addEventListener('click', () => {
        A = blank();
        Object.assign(S, { q: '', letter: '', chip: '' });
        qInput.value = '';
        writeForm(A);
        fillOptions();
        render();
        toOld();
    });

    /* ---------- downloads ---------- */
    function toast(message) {
        let t = document.querySelector('.etd-toast');
        if (!t) {
            t = document.createElement('div');
            t.className = 'etd-toast';
            t.setAttribute('role', 'status');
            document.body.appendChild(t);
        }
        t.textContent = message;
        t.classList.add('show');
        clearTimeout(toast.timer);
        toast.timer = setTimeout(() => t.classList.remove('show'), 2600);
    }
    root.querySelectorAll('[data-etd-export]').forEach((btn) =>
        btn.addEventListener('click', () => {
            if (btn.classList.contains('is-busy')) return;
            const pdf = btn.dataset.etdExport === 'pdf';
            const params = { ...A, q: S.q.trim(), chip: S.chip, letter: S.letter };
            btn.classList.add('is-busy');
            btn.disabled = true;
            axios({ method: 'get', url: pdf ? root.dataset.pdfUrl : root.dataset.excelUrl, params, responseType: 'blob' })
                .then((response) => {
                    const m = /filename="?([^";]+)"?/i.exec(response.headers['content-disposition'] || '');
                    const href = window.URL.createObjectURL(new Blob([response.data]));
                    const a = document.createElement('a');
                    a.href = href;
                    a.setAttribute('download', m ? m[1] : pdf ? 'Telephone_Directory.pdf' : 'Telephone_Directory.xlsx');
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
    root.querySelectorAll('[data-etd-version]').forEach((button) =>
        button.addEventListener('click', () => {
            const v = button.dataset.etdVersion;
            root.querySelectorAll('[data-etd-version]').forEach((b) => b.setAttribute('aria-pressed', String(b === button)));
            root.querySelectorAll('[data-etd-pane]').forEach((pane) => {
                const off = pane.dataset.etdPane !== v;
                pane.classList.toggle('is-off', off);
                pane.setAttribute('aria-hidden', String(off));
            });
            root.querySelectorAll('[data-etd-new-only]').forEach((el) => (el.hidden = v !== 'new'));
            rail.style.position = v === 'new' ? '' : 'static';
            window.dispatchEvent(new Event('resize')); // the old table redraws on resize
        })
    );

    const OLD = {
        type: '#employee_work_type_id-telephonedirectory', department: '#department_id-telephonedirectory', from: '#startdate-telephonedirectory', to: '#enddate-telephonedirectory',
        ethnicity: '#ethnicity-telephonedirectory', nationality: '#nationality-telephonedirectory', gender: '#gender-telephonedirectory', status: '#status_id-telephonedirectory',
    };
    const goBtn = document.getElementById('tabulator-html-filter-go-TD');
    const resetBtn = document.getElementById('tabulator-html-filter-reset-TD');
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
        Object.assign(S, { letter: '', chip: '' });
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
            app.innerHTML = '<div class="etd-empty">The directory could not be loaded. Please reload the page.</div>';
        });
})();
