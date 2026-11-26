/*
 * HR Portal › Reports › Employee Data Report (user's employee-data-report design, 26 Nov 2026).
 *
 * Step 1: Search asks the server how many employees the filters keep. Step 2: tick fields; the columns
 * follow the order they were ticked. Step 3: the summary, and Generate XLSX posts the filters and the
 * ticked field keys in order (App\Services\HR\EmployeeDataReport builds the sheet).
 */
import { hrDatePickers } from './hr-report-datepicker';

(function () {
    'use strict';

    const root = document.querySelector('[data-edr]');
    if (!root) return;
    hrDatePickers(root); // Litepicker on the date filters (ISO values stay on data-*-f)

    const FIELDS = JSON.parse(root.dataset.fields || '[]');
    const GROUPS = JSON.parse(root.dataset.groups || '[]');
    const BY = {};
    FIELDS.forEach((f) => {
        f.on = f.group === 'personal';
        BY[f.id] = f;
    });
    const PRESETS = {
        contact: ['title_id', 'last_name', 'first_name', 'email', 'telephone', 'mobile', 'employee_job_title_id', 'department_id', 'office_telephone', 'office_mobile', 'office_email'],
        payroll: ['title_id', 'last_name', 'first_name', 'ni_number', 'works_number', 'started_on', 'pay_frequency', 'tax_code', 'payment_method', 'sort_code', 'ac_no', 'contracted_hour', 'salary', 'hourly_rate'],
        rtw: ['last_name', 'first_name', 'nationality_id', 'works_number'].concat(FIELDS.filter((f) => f.group === 'eligibility').map((f) => f.id)),
    };
    const KEYS = ['type', 'department', 'gender', 'from', 'to', 'status'];
    const form = root.querySelector('[data-edr-form]');
    const field = (k) => root.querySelector(`[data-edr-f="${k}"]`);
    const esc = (s) => String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    const n = (v) => Number(v).toLocaleString('en-GB');

    const S = { found: null, applied: null, crit: '', busy: false, q: '', done: '', error: false, order: FIELDS.filter((f) => f.on).map((f) => f.id) };
    const readForm = () => KEYS.reduce((o, k) => ((o[k] = field(k).value), o), {});
    const dirty = () => S.applied !== null && KEYS.some((k) => field(k).value !== S.applied[k]);

    function setField(id, on) {
        const f = BY[id];
        if (!f || f.on === on) return;
        f.on = on;
        S.order = on ? S.order.concat(id) : S.order.filter((x) => x !== id);
        S.done = '';
    }

    function drawGroups() {
        const q = S.q.trim().toLowerCase();
        root.querySelector('[data-edr-groups]').innerHTML = GROUPS.map((g) => {
            const fs = FIELDS.filter((f) => f.group === g.id);
            const on = fs.filter((f) => f.on).length;
            const vis = fs.filter((f) => !q || f.label.toLowerCase().includes(q) || f.col.toLowerCase().includes(q));
            return (
                `<div class="edr-group"><div class="edr-gh"><h3>${esc(g.name)}</h3><span class="edr-gcount">${on} of ${fs.length} selected</span>` +
                `<div class="edr-gbar" aria-hidden="true"><i style="width:${(100 * on) / fs.length}%"></i></div>` +
                `<button type="button" class="edr-lk" data-gall="${g.id}"${on === fs.length ? ' disabled' : ''}>Select all</button>` +
                `<button type="button" class="edr-lk" data-gnone="${g.id}"${on ? '' : ' disabled'}>Clear</button></div><div class="edr-chips">` +
                fs
                    .map(
                        (f) =>
                            `<label class="edr-chip${vis.includes(f) ? '' : ' is-hidden'}"><input type="checkbox" data-f="${f.id}"${f.on ? ' checked' : ''}>` +
                            `<span>${esc(f.label)}${f.sens ? '<em class="edr-lock">Sensitive</em>' : ''}</span></label>`
                    )
                    .join('') +
                (vis.length ? '' : `<span class="edr-nomatch">No field here matches "${esc(S.q)}".</span>`) +
                '</div></div>'
            );
        }).join('');
    }

    function drawFound() {
        const el = root.querySelector('[data-edr-found]');
        const d = dirty();
        el.className = 'edr-found' + (S.found === null || d ? ' is-stale' : '');
        if (S.busy) el.innerHTML = '<span>Counting…</span>';
        else if (S.found === null) el.innerHTML = '<span>Not searched yet</span>';
        else el.innerHTML = `<b>${n(S.found)}</b><span>employee${S.found === 1 ? '' : 's'} found${d ? ' · <span class="st">filters changed, search again</span>' : ''}</span>`;
    }

    function drawSum() {
        const sel = S.order.map((id) => BY[id]);
        const k = sel.length;
        const sens = sel.filter((f) => f.sens);
        const have = S.found !== null;
        const d = dirty();
        let h =
            `<div class="edr-figs"><div class="edr-fig${have ? '' : ' is-wait'}"><b>${have ? n(S.found) : '–'}</b><span>${have ? `employee${S.found === 1 ? '' : 's'} found` : 'employees · search first'}</span></div>` +
            `<div class="edr-fig"><b>${k}</b><span>of ${FIELDS.length} data fields selected</span></div></div>`;
        h +=
            '<div class="edr-eq">' +
            (have ? `<b>${n(S.found)}</b> rows × <b>${k}</b> columns = <b>${n(S.found * k)}</b> cells` : 'Rows × columns appears after you search') +
            (have && S.crit ? `<br>${esc(S.crit)}` : '') +
            '</div>';
        h +=
            '<div><h2 style="margin-bottom:8px">Fields by section</h2><div class="edr-bygroup">' +
            GROUPS.map((g) => {
                const fs = FIELDS.filter((f) => f.group === g.id);
                const on = fs.filter((f) => f.on).length;
                return `<div class="${on ? '' : 'z'}"><span>${esc(g.name)}</span><span class="m"><i style="width:${(100 * on) / fs.length}%"></i></span><b>${on}/${fs.length}</b></div>`;
            }).join('') +
            '</div></div>';
        if (sens.length) h += `<div class="edr-warnbox"><b>${sens.length} sensitive field${sens.length === 1 ? '' : 's'} selected</b>${sens.map((f) => esc(f.col)).join(', ')}. Share this file only with people who need it.</div>`;
        if (k) {
            h +=
                '<div class="edr-cols"><h2>Column order</h2><div class="edr-sheet"><div class="scroll"><table><thead><tr>' +
                sel.slice(0, 6).map((f) => `<th>${esc(f.col)}</th>`).join('') +
                (k > 6 ? `<th>+${k - 6} more</th>` : '') +
                '</tr></thead><tbody>' +
                [0, 1, 2]
                    .map((r) => '<tr>' + sel.slice(0, 6).map((f, c) => `<td><i style="width:${34 + ((r * 17 + c * 23) % 40)}px"></i></td>`).join('') + (k > 6 ? '<td></td>' : '') + '</tr>')
                    .join('') +
                '</tbody></table></div></div><ul class="edr-order" aria-label="Selected fields, in column order">' +
                sel.map((f) => `<li>${esc(f.col)}<button type="button" data-rm="${f.id}" aria-label="Remove ${esc(f.col)}">×</button></li>`).join('') +
                '</ul><div class="edr-hint">Columns follow the order you tick them, after a Serial column.</div></div>';
        } else {
            h += '<div class="edr-hint">No fields selected yet. Tick some in step 2.</div>';
        }
        const ready = have && !d && !S.busy && S.found > 0 && k > 0;
        const why = !have ? 'Search for employees first.' : d ? 'Filters changed. Search again first.' : !S.found ? 'No employees match the filters.' : 'Select at least one field.';
        h +=
            `<button type="button" class="edr-btn edr-btn--primary edr-go${S.gen ? ' is-busy' : ''}" data-edr-gen${ready && !S.gen ? '' : ' disabled'}>${S.gen ? 'Generating…' : 'Generate XLSX'}</button>` +
            (ready || S.gen ? '' : `<div class="edr-hint" style="margin-top:-8px;text-align:center">${why}</div>`) +
            (S.done ? `<div class="edr-done${S.error ? ' is-error' : ''}" role="status">${esc(S.done)}</div>` : '');
        root.querySelector('[data-edr-sum]').innerHTML = h;
    }

    const drawAll = () => {
        drawGroups();
        drawFound();
        drawSum();
    };

    function criteria(f) {
        const name = (k) => {
            const o = field(k).selectedOptions[0];
            return o ? o.textContent.trim() : '';
        };
        const d = (s) => {
            const p = s.split('-');
            return `${+p[2]}/${+p[1]}/${p[0]}`;
        };
        const parts = [`Status: ${name('status')}`];
        if (f.type) parts.push(name('type'));
        if (f.department) parts.push(name('department'));
        if (f.gender) parts.push(name('gender'));
        if (f.from) parts.push(`started from ${d(f.from)}`);
        if (f.to) parts.push(`started to ${d(f.to)}`);
        return parts.join(' · ');
    }

    function search() {
        const f = readForm();
        S.busy = true;
        S.done = '';
        drawFound();
        drawSum();
        axios
            .get(root.dataset.countUrl, { params: f })
            .then(({ data }) => {
                S.found = Number(data.count) || 0;
                S.applied = f;
                S.crit = criteria(f);
            })
            .catch(() => {
                S.done = 'The count failed. Please try again.';
                S.error = true;
            })
            .finally(() => {
                S.busy = false;
                drawFound();
                drawSum();
            });
    }

    function generate() {
        const f = S.applied;
        const fd = new FormData();
        KEYS.forEach((k) => fd.append(k, f[k]));
        S.order.forEach((id) => fd.append('fields[]', id));
        S.gen = true;
        S.done = '';
        S.error = false;
        drawSum();
        axios
            .post(root.dataset.exportUrl, fd, { responseType: 'blob', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') } })
            .then((response) => {
                const m = /filename="?([^";]+)"?/i.exec(response.headers['content-disposition'] || '');
                const href = window.URL.createObjectURL(new Blob([response.data]));
                const a = document.createElement('a');
                a.href = href;
                a.setAttribute('download', m ? m[1] : 'Employee_data_report.xlsx');
                document.body.appendChild(a);
                a.click();
                a.remove();
                setTimeout(() => window.URL.revokeObjectURL(href), 1000);
                S.done = `Downloaded: ${n(S.found)} rows × ${S.order.length} columns.`;
            })
            .catch(() => {
                S.done = 'The spreadsheet could not be made. Please try again.';
                S.error = true;
            })
            .finally(() => {
                S.gen = false;
                drawSum();
            });
    }

    /* ---------- events ---------- */
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        search();
    });
    root.querySelector('[data-edr-reset]').addEventListener('click', () => {
        KEYS.forEach((k) => (field(k).value = k === 'status' ? '1' : ''));
        Object.assign(S, { found: null, applied: null, crit: '', done: '', error: false });
        drawFound();
        drawSum();
    });
    form.addEventListener('change', () => {
        S.done = '';
        drawFound();
        drawSum();
    });
    root.querySelector('[data-edr-find]').addEventListener('input', (e) => {
        S.q = e.target.value;
        drawGroups();
    });
    root.addEventListener('change', (e) => {
        const id = e.target.dataset && e.target.dataset.f;
        if (!id) return;
        setField(id, e.target.checked);
        drawGroups();
        drawSum();
        const c = root.querySelector(`[data-f="${id}"]`);
        if (c) c.focus();
    });
    root.addEventListener('click', (e) => {
        let b;
        if ((b = e.target.closest('[data-gall]'))) {
            const g = b.dataset.gall;
            FIELDS.forEach((f) => f.group === g && setField(f.id, true));
            drawGroups();
            drawSum();
            const x = root.querySelector(`[data-gnone="${g}"]`);
            if (x) x.focus();
        } else if ((b = e.target.closest('[data-gnone]'))) {
            const g = b.dataset.gnone;
            FIELDS.forEach((f) => f.group === g && setField(f.id, false));
            drawGroups();
            drawSum();
            const y = root.querySelector(`[data-gall="${g}"]`);
            if (y) y.focus();
        } else if ((b = e.target.closest('[data-preset]'))) {
            const p = b.dataset.preset;
            FIELDS.forEach((f) => (f.on = false));
            S.order = [];
            S.done = '';
            (p === 'all' ? FIELDS.map((f) => f.id) : p === 'none' ? [] : PRESETS[p]).forEach((id) => setField(id, true));
            drawGroups();
            drawSum();
        } else if ((b = e.target.closest('[data-rm]'))) {
            setField(b.dataset.rm, false);
            drawGroups();
            drawSum();
        } else if (e.target.closest('[data-edr-gen]')) {
            generate();
        }
    });

    drawAll();
})();
