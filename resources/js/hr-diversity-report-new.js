/*
 * HR Portal › Reports › Diversity Information, new version (user's diversity-information-report design,
 * 26 Nov 2026).
 *
 * One GET loads every active and inactive staff member with their ethnic group and detail already
 * worked out (App\Services\HR\DiversityReport); filters, dots, cards and the staff list work in the
 * browser with the old report's rules. The colour-by dimensions match DiversityReport::dimension,
 * which the PDF and the Excel use.
 *
 * The Old version sits beside it: a filter change here fills the old form and runs its Go (the Old
 * pane is off screen, not display:none, so its table lays out); the old Go or Reset fills this form.
 */
import { hrDatePickers } from './hr-report-datepicker';

(function () {
    'use strict';

    const root = document.querySelector('[data-dvr]');
    if (!root) return;
    hrDatePickers(root); // Litepicker on the date filters (ISO values stay on data-*-f)

    const app = root.querySelector('[data-dvr-app]');
    const field = (k) => root.querySelector(`[data-dvr-f="${k}"]`);
    const esc = (s) => String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    const pc = (a, b) => (b ? `${((100 * a) / b).toFixed(1)}%` : '–');
    const C = ['var(--dvr-c0)', 'var(--dvr-c1)', 'var(--dvr-c2)', 'var(--dvr-c3)', 'var(--dvr-c4)', 'var(--dvr-c5)'];
    const GROUPS = ['Asian', 'White', 'Black', 'Mixed', 'Other', 'Not known'];
    const DIMS = { gender: 'Gender', ethnic: 'Ethnic group', nation: 'Nationality', disability: 'Disability' };
    const FILTERS = ['type', 'department', 'from', 'to', 'ethnicity', 'nationality', 'gender', 'status'];
    const ETH = JSON.parse(root.dataset.ethnicities || '[]'); // [id, name, active]
    const ethActive = {};
    ETH.forEach((e) => (ethActive[e[0]] = e[2]));

    const S = { type: '', department: '', from: '', to: '', ethnicity: '', nationality: '', gender: '', status: '1', dim: 'ethnic', pick: null, all: false };
    let STAFF = null;

    /* ---------- filters (DiversityReport::rows) ---------- */
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
    const inStatus = (r) => S.status === '2' || String(r.status) === S.status;

    // Ethnicity and nationality lists with how many staff of the chosen status hold each.
    function fillOptions() {
        const pool = STAFF.filter(inStatus);
        const count = (k) => pool.reduce((t, r) => ((t[r[k]] = (t[r[k]] || 0) + 1), t), {});
        const ce = count('ethnicityId');
        const eth = ETH.filter((e) => e[2] || ce[e[0]] || String(e[0]) === S.ethnicity).sort((a, b) => a[1].localeCompare(b[1]));
        field('ethnicity').innerHTML =
            '<option value="">All ethnicities</option>' + eth.map((e) => `<option value="${e[0]}">${esc(e[1])} (${ce[e[0]] || 0})</option>`).join('');
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
    function syncForm() {
        FILTERS.forEach((k) => (field(k).value = S[k]));
    }

    /* ---------- colour-by dimensions (DiversityReport::dimension) ---------- */
    function dimension(dim, list) {
        let of;
        let order;
        if (dim === 'nation') {
            const t = {};
            list.forEach((p) => p.nationality && (t[p.nationality] = (t[p.nationality] || 0) + 1));
            const top = Object.keys(t).sort((a, b) => t[b] - t[a]).slice(0, 4);
            of = (p) => (!p.nationality ? 'Not recorded' : top.includes(p.nationality) ? p.nationality : 'Other nationalities');
            order = top.concat(['Other nationalities', 'Not recorded']);
        } else if (dim === 'gender') {
            of = (p) => p.gender || 'Not recorded';
            order = ['Male', 'Female', 'Other', 'Information refused', 'Not available', 'Not recorded'];
        } else if (dim === 'disability') {
            of = (p) => (p.disability === 'Yes' ? 'Yes' : p.disability === 'No' ? 'No' : 'Not recorded');
            order = ['No', 'Yes', 'Not recorded'];
        } else {
            of = (p) => p.ethGroup;
            order = GROUPS;
        }
        const counts = {};
        list.forEach((p) => (counts[of(p)] = (counts[of(p)] || 0) + 1));
        const keys = order.filter((k) => counts[k]).concat(Object.keys(counts).filter((k) => !order.includes(k)));
        const col = {};
        let j = 0;
        keys.forEach((k) => {
            if (/^Not |^Other nationalities$/.test(k)) col[k] = C[5];
            else if (dim === 'ethnic') col[k] = C[GROUPS.indexOf(k)] || C[4];
            else col[k] = C[Math.min(j++, 4)];
        });
        return { of, keys, counts, col, label: DIMS[dim] };
    }
    const tally = (list, f) => list.reduce((t, p) => ((t[f(p)] = (t[f(p)] || 0) + 1), t), {});

    function bars(t, total, order, sub) {
        return (
            '<div class="dvr-bars">' +
            order
                .filter((k) => t[k])
                .map(
                    (k) =>
                        `<div class="dvr-bar"><div class="dvr-t"><span>${esc(k)}<span>${pc(t[k], total)}</span></span><b>${t[k]}</b></div>` +
                        `<div class="dvr-meter"><i style="width:${(100 * t[k]) / total}%"></i></div>${sub && sub[k] ? sub[k] : ''}</div>`
                )
                .join('') +
            '</div>'
        );
    }
    const nameLink = (p) => `<a class="dvr-name" href="${esc(root.dataset.profileUrl.replace('__ID__', p.id))}">${esc(p.name || 'Unnamed')}</a>`;
    const missing = '<span class="dvr-miss">Missing</span>';

    /* ---------- drawing ---------- */
    function render() {
        if (!STAFF) return;
        const list = STAFF.filter(keep);
        const N = list.length;
        document.getElementById('dvrSub').textContent = `London Churchill College · ${N} ${S.status === '1' ? 'active ' : S.status === '0' ? 'inactive ' : ''}staff`;
        if (!N) {
            app.innerHTML = '<div class="dvr-empty">No staff match these filters.</div>';
            return;
        }
        const D = dimension(S.dim, list);
        if (S.pick && !D.counts[S.pick]) S.pick = null;
        const sorted = list.slice().sort((a, b) => D.keys.indexOf(D.of(a)) - D.keys.indexOf(D.of(b)));

        /* everyone at a glance */
        let h =
            `<section class="dvr-sec"><div class="dvr-sechead"><div class="dvr-count"><b>${N}</b><span>staff · each dot is one person</span></div>` +
            '<div class="dvr-colourby"><span class="dvr-hint">Colour by</span><div class="dvr-seg" role="group" aria-label="Colour by">' +
            Object.keys(DIMS).map((k) => `<button type="button" data-dim="${k}" aria-pressed="${S.dim === k}">${DIMS[k]}</button>`).join('') +
            '</div></div></div><div class="dvr-glance">' +
            `<div class="dvr-dots${N > 200 ? ' is-many' : ''}" role="img" aria-label="${esc(D.keys.map((k) => `${k} ${D.counts[k]}`).join(', '))}">` +
            sorted.map((p) => `<i style="background:${D.col[D.of(p)]}"${S.pick && S.pick !== D.of(p) ? ' class="is-dim"' : ''}></i>`).join('') +
            `</div><div><div class="dvr-hint" style="margin-bottom:6px">${D.label} · select a group to list those staff</div><ul class="dvr-lg">` +
            D.keys
                .map(
                    (k) =>
                        `<li><button type="button" data-pick="${esc(k)}" aria-pressed="${S.pick === k}"><span class="dvr-sw" style="background:${D.col[k]}"></span>` +
                        `<span>${esc(k)}</span><b>${D.counts[k]}</b><span class="dvr-pc">${pc(D.counts[k], N)}</span></button></li>`
                )
                .join('') +
            '</ul></div></div></section>';

        /* cards */
        const G = dimension('gender', list);
        const tgr = tally(list, (p) => p.ethGroup);
        const yes = list.filter((p) => p.disability === 'Yes').length;
        const sub = {};
        GROUPS.forEach((g) => {
            const td = tally(list.filter((p) => p.ethGroup === g), (p) => p.ethDetail);
            const ks = Object.keys(td).sort((a, b) => td[b] - td[a] || a.localeCompare(b));
            if (ks.length > 1 || (ks.length === 1 && ks[0] !== g && g !== 'Not known')) {
                sub[g] = `<ul class="dvr-subl">${ks.map((k) => `<li><span>${esc(k)}</span><b>${td[k]}</b></li>`).join('')}</ul>`;
            }
        });
        const tn = tally(list, (p) => p.nationality || 'Not recorded');
        const natKeys = Object.keys(tn).sort((a, b) => tn[b] - tn[a] || a.localeCompare(b));
        const natTop = natKeys.filter((k) => tn[k] > 1 && k !== 'Not recorded');
        const natOne = natKeys.filter((k) => tn[k] === 1 && k !== 'Not recorded');
        h +=
            '<div class="dvr-cards"><section class="dvr-card"><h2>Gender</h2><div class="dvr-split">' +
            G.keys.map((k) => `<i style="flex:${G.counts[k]};background:${G.col[k]}">${G.counts[k] / N > 0.08 ? G.counts[k] : ''}</i>`).join('') +
            '</div><div class="dvr-kv">' +
            G.keys.map((k) => `<div><span>${esc(k)}<span>${pc(G.counts[k], N)}</span></span><b>${G.counts[k]}</b></div>`).join('') +
            `</div><h2 style="margin-top:8px">Disability</h2><div><span class="dvr-big2">${yes}</span> <span class="dvr-muted">marked Yes · ${pc(yes, N)}</span></div>` +
            '<div class="dvr-hint">From each person’s disability status, the old report’s “Status” column.</div></section>' +
            `<section class="dvr-card"><h2>Ethnic group</h2>${bars(tgr, N, GROUPS, sub)}</section>` +
            `<section class="dvr-card"><h2>Nationality</h2>${bars(tn, N, natTop)}` +
            (natOne.length ? `<div class="dvr-hint"><b style="color:var(--dvr-ink)">One each (${natOne.length}):</b> ${natOne.map(esc).join(', ')}</div>` : '') +
            (tn['Not recorded'] ? `<div class="dvr-hint">${tn['Not recorded']} not recorded</div>` : '') +
            '</section></div>';

        /* gender within each ethnic group */
        h +=
            '<section class="dvr-sec"><div class="dvr-sechead"><h2>Gender within each ethnic group</h2>' +
            `<span class="dvr-legend"><span><i style="background:${C[0]}"></i>Male</span><span><i style="background:${C[1]}"></i>Female</span><span><i style="background:${C[5]}"></i>Other or not recorded</span></span></div><div class="dvr-x">` +
            GROUPS.filter((g) => tgr[g])
                .map((g) => {
                    const l = list.filter((p) => p.ethGroup === g);
                    const m = l.filter((p) => p.gender === 'Male').length;
                    const f = l.filter((p) => p.gender === 'Female').length;
                    const o = l.length - m - f;
                    return (
                        `<span>${g} <span class="dvr-muted">${l.length}</span></span>` +
                        `<div class="dvr-split" style="width:${Math.max(6, (100 * l.length) / N)}%"><i style="flex:${m};background:${C[0]}"></i><i style="flex:${f};background:${C[1]}"></i>${o ? `<i style="flex:${o};background:${C[5]}"></i>` : ''}</div>` +
                        `<span class="dvr-r">${m} male · ${f} female${o ? ` · ${o} other` : ''}</span>`
                    );
                })
                .join('') +
            '</div></section>';

        /* staff list */
        const shown = S.pick ? list.filter((p) => D.of(p) === S.pick) : list;
        const LIM = 12;
        const rows = S.all ? shown : shown.slice(0, LIM);
        h +=
            `<section class="dvr-sec"><div class="dvr-tools"><div><h2>Staff list</h2><span class="dvr-hint">${shown.length} of ${N} staff</span></div>` +
            (S.pick ? `<span class="dvr-chip">${D.label}: ${esc(S.pick)}<button type="button" data-clear aria-label="Clear group">×</button></span>` : '') +
            '</div><div class="dvr-scroll"><table class="dvr-table"><thead><tr><th>Staff member</th><th>Works no.</th><th>Gender</th><th>Ethnicity</th><th>Nationality</th><th>Disability</th></tr></thead><tbody>' +
            rows
                .map(
                    (p) =>
                        `<tr><td class="dvr-nm">${nameLink(p)}</td><td>${p.works ? esc(p.works) : missing}</td><td>${p.gender ? esc(p.gender) : missing}</td>` +
                        `<td>${p.ethnicity ? esc(p.ethnicity) : missing}</td><td>${p.nationality ? esc(p.nationality) : missing}</td>` +
                        `<td>${p.disability === 'Yes' ? '<span class="dvr-yes">Yes</span>' : p.disability === 'No' ? 'No' : missing}</td></tr>`
                )
                .join('') +
            '</tbody></table></div>' +
            (shown.length > LIM
                ? `<div class="dvr-foot"><span>${S.all ? `Showing all ${shown.length}` : `Showing ${LIM} of ${shown.length}`}</span><button type="button" class="dvr-btn" data-all>${S.all ? 'Show fewer' : `Show all ${shown.length}`}</button></div>`
                : '') +
            '</section>';

        /* notes */
        const notes = [];
        const older = list.filter((p) => p.ethnicityId && ethActive[p.ethnicityId] === 0).length;
        if (older && older < N) {
            notes.push(
                `<li><b>Ethnicity is recorded with two sets of wording.</b> ${older} of these staff use an older, now inactive, wording (for example “Asian or Asian British - Bangladeshi”) and the rest the newer one (“Asian - Bangladeshi or Bangladeshi British”). They are merged in the charts and kept as recorded in the list.</li>`
            );
        }
        const gaps = [
            ['gender', list.filter((p) => !p.gender).length],
            ['ethnicity', list.filter((p) => !p.ethnicity).length],
            ['nationality', list.filter((p) => !p.nationality).length],
        ].filter((g) => g[1]);
        if (gaps.length) notes.push(`<li>Missing on file: ${gaps.map((g) => `${g[0]} for ${g[1]}`).join(', ')}.</li>`);
        const details = tally(list, (p) => p.ethDetail);
        if (Object.values(details).some((c) => c === 1) || natOne.length) notes.push('<li>Several groups hold one person. Take care when sharing these figures outside HR.</li>');
        if (notes.length) h += `<ul class="dvr-notes">${notes.join('')}</ul>`;

        app.innerHTML = h;
    }

    /* ---------- events ---------- */
    app.addEventListener('click', (e) => {
        let b;
        const refocus = (sel) => {
            const f = app.querySelector(sel);
            if (f) f.focus();
        };
        if ((b = e.target.closest('[data-dim]'))) {
            Object.assign(S, { dim: b.dataset.dim, pick: null, all: false });
            render();
            refocus(`[data-dim="${S.dim}"]`);
        } else if ((b = e.target.closest('[data-pick]'))) {
            const k = b.dataset.pick;
            Object.assign(S, { pick: S.pick === k ? null : k, all: false });
            render();
            const f = [...app.querySelectorAll('[data-pick]')].find((x) => x.dataset.pick === k);
            if (f) f.focus();
        } else if (e.target.closest('[data-clear]')) {
            Object.assign(S, { pick: null, all: false });
            render();
        } else if (e.target.closest('[data-all]')) {
            S.all = !S.all;
            render();
            refocus('[data-all]');
        }
    });

    // A filter change is this version's search: redraw, and fill the Old version and run it.
    FILTERS.forEach((k) =>
        field(k).addEventListener('change', () => {
            S[k] = field(k).value;
            Object.assign(S, { pick: null, all: false });
            if (k === 'status') fillOptions();
            render();
            toOld();
        })
    );
    root.querySelector('[data-dvr-reset]').addEventListener('click', () => {
        Object.assign(S, { type: '', department: '', from: '', to: '', ethnicity: '', nationality: '', gender: '', status: '1', pick: null, all: false });
        syncForm();
        fillOptions();
        render();
        toOld();
    });

    /* ---------- exports (same filters, colour-by and picked group as on screen) ---------- */
    function toast(message) {
        let t = document.querySelector('.dvr-toast');
        if (!t) {
            t = document.createElement('div');
            t.className = 'dvr-toast';
            t.setAttribute('role', 'status');
            document.body.appendChild(t);
        }
        t.textContent = message;
        t.classList.add('show');
        clearTimeout(toast.timer);
        toast.timer = setTimeout(() => t.classList.remove('show'), 2600);
    }
    root.querySelectorAll('[data-dvr-export]').forEach((btn) =>
        btn.addEventListener('click', () => {
            if (btn.classList.contains('is-busy')) return;
            const kind = btn.dataset.dvrExport;
            const params = { dim: S.dim, pick: S.pick || '' };
            FILTERS.forEach((k) => (params[k] = S[k]));
            btn.classList.add('is-busy');
            btn.disabled = true;
            axios({ method: 'get', url: kind === 'pdf' ? root.dataset.pdfUrl : root.dataset.excelUrl, params, responseType: 'blob' })
                .then((response) => {
                    const match = /filename="?([^";]+)"?/i.exec(response.headers['content-disposition'] || '');
                    const url = window.URL.createObjectURL(new Blob([response.data]));
                    const link = document.createElement('a');
                    link.href = url;
                    link.setAttribute('download', match ? match[1] : kind === 'pdf' ? 'Diversity_Information.pdf' : 'Diversity_Information.xlsx');
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
    root.querySelectorAll('[data-dvr-version]').forEach((button) =>
        button.addEventListener('click', () => {
            const v = button.dataset.dvrVersion;
            root.querySelectorAll('[data-dvr-version]').forEach((b) => b.setAttribute('aria-pressed', String(b === button)));
            root.querySelectorAll('[data-dvr-pane]').forEach((pane) => {
                const off = pane.dataset.dvrPane !== v;
                pane.classList.toggle('is-off', off);
                pane.setAttribute('aria-hidden', String(off));
            });
            root.querySelectorAll('[data-dvr-new-only]').forEach((el) => (el.hidden = v !== 'new'));
            window.dispatchEvent(new Event('resize')); // the old table redraws on resize
        })
    );

    const OLD = {
        type: '#employee_work_type_id-diversity', department: '#department_id-diversity', from: '#startdate-DR', to: '#enddate-DR',
        ethnicity: '#ethnicity-DR', nationality: '#nationality-DR', gender: '#gender-DR', status: '#status_id-DR',
    };
    const goBtn = document.getElementById('tabulator-html-filter-go-DR');
    const resetBtn = document.getElementById('tabulator-html-filter-reset-DR');
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
        Object.assign(S, { pick: null, all: false });
        if (STAFF) fillOptions();
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
            STAFF = data.staff || [];
            fillOptions();
            render();
        })
        .catch(() => {
            app.innerHTML = '<div class="dvr-empty">The diversity figures could not be loaded. Please reload the page.</div>';
        });
})();
