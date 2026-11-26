/*
 * HR Portal › Reports › Employee Contact Details, new version (user's employee-contact-details design,
 * 26 Nov 2026).
 *
 * One GET loads every active and inactive staff member (App\Services\HR\ContactDetailsReport); filters,
 * the completeness ring, the gap picker, the cards and the pager work in the browser with the old
 * report's filter rules. The PDF and the Excel take the same filters (ContactDetailsReport::base/shown).
 *
 * The Old version sits beside it with more filters (dates, ethnicity, nationality, gender). A change
 * here fills the old form (clearing those) and runs its Go; the old Go fills this form, and any old-only
 * filter shows as a chip here so both versions list the same staff.
 */
(function () {
    'use strict';

    const root = document.querySelector('[data-ecd]');
    if (!root) return;

    const app = root.querySelector('[data-ecd-app]');
    const extraBox = root.querySelector('[data-ecd-extra]');
    const field = (k) => root.querySelector(`[data-ecd-f="${k}"]`);
    const esc = (s) => String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    const NAMES = JSON.parse(root.dataset.names || '{}');
    const GAPS = [['phone', 'No phone number'], ['email', 'No email'], ['address', 'No address'], ['emergency', 'No emergency number']];
    const SHOWN = ['q', 'type', 'department', 'status'];
    const EXTRA = { from: 'Started on or before', to: 'Left on or after', ethnicity: 'Ethnicity', nationality: 'Nationality', gender: 'Gender' };
    const PER = 12;

    const S = { q: '', type: '', department: '', status: '1', from: '', to: '', ethnicity: '', nationality: '', gender: '', gap: '', page: 0 };
    let STAFF = null;

    const IC = {
        mob: '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><rect x="4.5" y="1.5" width="7" height="13" rx="1.5"/><path d="M7 12.2h2"/></svg>',
        tel: '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" aria-hidden="true"><path d="M3 2.5h2.5l1 3-1.5 1a7 7 0 0 0 3.5 3.5l1-1.5 3 1V12a1.5 1.5 0 0 1-1.5 1.5A9.5 9.5 0 0 1 2.5 4 1.5 1.5 0 0 1 3 2.5z"/></svg>',
        email: '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" aria-hidden="true"><rect x="1.5" y="3.5" width="13" height="9" rx="1.5"/><path d="M2 4.5l6 4.5 6-4.5"/></svg>',
        addr: '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" aria-hidden="true"><path d="M8 14.5s4.5-4 4.5-7.5a4.5 4.5 0 0 0-9 0c0 3.5 4.5 7.5 4.5 7.5z"/><circle cx="8" cy="7" r="1.6"/></svg>',
        em: '<svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round" aria-hidden="true"><path d="M8 2l6.5 11.5h-13z"/><path d="M8 6.5v3.2M8 11.6v.2" stroke-linecap="round"/></svg>',
    };
    const link = (kind, v) =>
        kind === 'email' ? `<a href="mailto:${esc(v)}">${esc(v)}</a>` : kind === 'addr' ? `<span>${esc(v)}</span>` : `<a href="tel:${esc(v.replace(/\s/g, ''))}">${esc(v)}</a>`;
    const initials = (n) => (n || '?').split(/\s+/).filter(Boolean).slice(0, 2).map((w) => w[0].toUpperCase()).join('');

    /* ---------- filters (ContactDetailsReport::base and ::shown) ---------- */
    function inBase(r) {
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
    function matches(r) {
        const q = S.q.trim().toLowerCase();
        if (!q) return true;
        const text = [r.name, r.works, r.tel, r.mob, r.email, r.address, r.eName, r.eTel, r.eMob, r.eEmail].join(' ').replace(/\s+/g, ' ').toLowerCase();
        const digits = q.replace(/\s+/g, '');
        return text.includes(q) || (/\d/.test(digits) && (r.tel + ' ' + r.mob + ' ' + r.eTel + ' ' + r.eMob).replace(/\s+/g, '').includes(digits));
    }
    const hasGap = (r) => !S.gap || (S.gap === 'ok' ? !r.gaps.length : r.gaps.includes(S.gap));
    function syncForm() {
        SHOWN.forEach((k) => (field(k).value = S[k]));
        const on = Object.keys(EXTRA).filter((k) => S[k]);
        extraBox.hidden = !on.length;
        extraBox.innerHTML = on.length
            ? '<span>Also filtered by the old version:</span>' +
              on
                  .map((k) => {
                      const v = ['from', 'to'].includes(k) ? new Date(S[k]).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) : (NAMES[k] || {})[S[k]] || S[k];
                      return `<span class="ecd-chip">${EXTRA[k]}: ${esc(v)}<button type="button" data-extra="${k}" aria-label="Remove ${EXTRA[k]}">×</button></span>`;
                  })
                  .join('')
            : '';
    }

    /* ---------- drawing ---------- */
    function render() {
        if (!STAFF) return;
        const base = STAFF.filter(inBase);
        const found = base.filter(matches);
        const list = found.filter(hasGap);
        const N = base.length;
        const ok = base.filter((p) => !p.gaps.length).length;
        document.getElementById('ecdSub').textContent = `London Churchill College · ${N} ${S.status === '1' ? 'active ' : S.status === '0' ? 'inactive ' : ''}staff`;

        const C = 2 * Math.PI * 44;
        let h =
            `<section class="ecd-health"><div class="ecd-ring"><svg width="104" height="104" viewBox="0 0 104 104" role="img" aria-label="${ok} of ${N} records have every key detail">` +
            '<circle cx="52" cy="52" r="44" fill="none" stroke="var(--ecd-track)" stroke-width="10"/>' +
            `<circle cx="52" cy="52" r="44" fill="none" stroke="var(--ecd-teal)" stroke-width="10" stroke-linecap="round" stroke-dasharray="${N ? (C * ok) / N : 0} ${C}"/></svg>` +
            `<div><b>${N ? Math.round((100 * ok) / N) : 0}%</b><span>complete</span></div></div>` +
            `<div><h2>${ok} of ${N} staff have every key detail</h2><div class="ecd-hint">A phone number, an email, an address and an emergency number. Select a gap to list those staff.</div>` +
            '<div class="ecd-gaps" role="group" aria-label="Filter by missing detail">' +
            GAPS.map((g) => `<button type="button" class="ecd-gap" data-gap="${g[0]}" aria-pressed="${S.gap === g[0]}">${g[1]}<b>${base.filter((p) => p.gaps.includes(g[0])).length}</b></button>`).join('') +
            `<button type="button" class="ecd-gap is-ok" data-gap="ok" aria-pressed="${S.gap === 'ok'}">Complete<b>${ok}</b></button></div></div></section>`;

        const pages = Math.max(1, Math.ceil(list.length / PER));
        S.page = Math.min(Math.max(0, S.page), pages - 1);
        const part = list.slice(S.page * PER, S.page * PER + PER);
        const title = S.gap ? (S.gap === 'ok' ? 'Complete records' : GAPS.find((g) => g[0] === S.gap)[1]) : 'All staff';
        h +=
            `<section style="display:flex;flex-direction:column;gap:14px"><div class="ecd-tools"><div><h2>${title}</h2><span class="ecd-hint">${list.length} staff` +
            (S.q.trim() ? ` matching “${esc(S.q.trim())}”` : '') +
            (S.gap ? ` · <button type="button" class="ecd-lk" data-gap="${S.gap}">Clear</button>` : '') +
            '</span></div></div>';
        if (!list.length) {
            h += '<div class="ecd-empty">No staff match.</div>';
        } else {
            const ln = (kind, label, v, miss) =>
                `<li title="${label}">${IC[kind]}<span class="ecd-sr">${label}: </span>${v ? link(kind, v) : `<span class="ecd-nof">${miss}</span>`}</li>`;
            h +=
                '<div class="ecd-tiles">' +
                part
                    .map((p) => {
                        const em = p.eMob || p.eTel;
                        const main = p.mob || p.tel;
                        const who = [p.eName, p.eRel].filter(Boolean).join(' · ');
                        const profile = root.dataset.profileUrl.replace('__ID__', p.id);
                        return (
                            `<article class="ecd-tile"><header><span class="ecd-av" aria-hidden="true">${esc(initials(p.name))}</span>` +
                            `<div class="ecd-tn"><b><a href="${esc(profile)}">${esc(p.name || 'Unnamed')}</a></b>` +
                            `<span class="${p.gaps.length ? 'is-bad' : 'is-good'}">${p.gaps.length ? `${p.gaps.length} detail${p.gaps.length === 1 ? '' : 's'} missing` : 'Complete'}${p.works ? ` · No. ${esc(p.works)}` : ''}</span></div>` +
                            '<div class="ecd-qa">' +
                            (main ? `<a href="tel:${esc(main.replace(/\s/g, ''))}" aria-label="Call ${esc(p.name)}" title="Call">${IC.tel}</a>` : '') +
                            (p.email ? `<a href="mailto:${esc(p.email)}" aria-label="Email ${esc(p.name)}" title="Email">${IC.email}</a>` : '') +
                            '</div></header><ul class="ecd-lines">' +
                            ln('mob', 'Mobile', p.mob, 'No mobile on file') +
                            (p.tel ? ln('tel', 'Telephone', p.tel) : '') +
                            ln('email', 'Email', p.email, 'No email on file') +
                            ln('addr', 'Address', p.address, 'No address on file') +
                            '</ul><footer>' +
                            `<span class="ecd-el">${IC.em}Emergency</span>` +
                            (em || p.eEmail
                                ? '<span class="ecd-ev">' +
                                  (who ? `<span class="ecd-who">${esc(who)}</span>` : '') +
                                  (em ? link('mob', em) : '') +
                                  (p.eMob && p.eTel ? `<small>${link('tel', p.eTel)}</small>` : '') +
                                  (p.eEmail ? `<small>${link('email', p.eEmail)}</small>` : '') +
                                  '</span>'
                                : `<span class="ecd-ev ecd-nof">${who ? `${esc(who)}<br>` : ''}No contact number on file</span>`) +
                            '</footer></article>'
                        );
                    })
                    .join('') +
                `</div><div class="ecd-foot"><span>Showing ${S.page * PER + 1} to ${S.page * PER + part.length} of ${list.length}</span>` +
                (pages > 1
                    ? `<span class="ecd-pager"><button type="button" class="ecd-btn" data-page="-1"${S.page ? '' : ' disabled'}>Previous</button><span>Page ${S.page + 1} of ${pages}</span><button type="button" class="ecd-btn" data-page="1"${S.page < pages - 1 ? '' : ' disabled'}>Next</button></span>`
                    : '') +
                '</div>';
        }
        h += '</section>';

        const notes = [];
        const noEm = base.filter((p) => !p.eTel && !p.eMob && !p.eEmail).length;
        const noPhone = base.filter((p) => !p.tel && !p.mob).length;
        if (noEm || noPhone) {
            notes.push(`<li>${[noEm ? `<b>${noEm} staff have no emergency contact at all</b>` : '', noPhone ? `${noPhone} have no phone number of their own` : ''].filter(Boolean).join(', and ')}.</li>`);
        }
        const tel = base.filter((p) => p.tel).length;
        notes.push(`<li>A telephone line is shown only where one is on file (${tel} staff); most staff give a mobile only.</li>`);
        notes.push('<li>Post code and town come from the address. The old report’s Post Code column was always empty.</li>');
        h += `<ul class="ecd-notes">${notes.join('')}</ul>`;
        app.innerHTML = h;
    }

    /* ---------- events ---------- */
    app.addEventListener('click', (e) => {
        let b;
        if ((b = e.target.closest('[data-gap]'))) {
            const g = b.dataset.gap;
            Object.assign(S, { gap: S.gap === g ? '' : g, page: 0 });
            render();
            const f = app.querySelector(`.ecd-gap[data-gap="${g}"]`);
            if (f) f.focus();
        } else if ((b = e.target.closest('[data-page]'))) {
            S.page += +b.dataset.page;
            render();
            const t = app.querySelector('.ecd-tools');
            if (t) t.scrollIntoView({ block: 'start' });
        }
    });
    extraBox.addEventListener('click', (e) => {
        const b = e.target.closest('[data-extra]');
        if (!b) return;
        S[b.dataset.extra] = '';
        S.page = 0;
        syncForm();
        render();
        toOld();
    });
    ['type', 'department', 'status'].forEach((k) =>
        field(k).addEventListener('change', () => {
            S[k] = field(k).value;
            S.page = 0;
            render();
            toOld();
        })
    );
    field('q').addEventListener('input', () => {
        S.q = field('q').value;
        S.page = 0;
        render();
    });
    root.querySelector('[data-ecd-reset]').addEventListener('click', () => {
        Object.assign(S, { q: '', type: '', department: '', status: '1', from: '', to: '', ethnicity: '', nationality: '', gender: '', gap: '', page: 0 });
        syncForm();
        render();
        toOld();
    });

    /* ---------- exports ---------- */
    function toast(message) {
        let t = document.querySelector('.ecd-toast');
        if (!t) {
            t = document.createElement('div');
            t.className = 'ecd-toast';
            t.setAttribute('role', 'status');
            document.body.appendChild(t);
        }
        t.textContent = message;
        t.classList.add('show');
        clearTimeout(toast.timer);
        toast.timer = setTimeout(() => t.classList.remove('show'), 2600);
    }
    root.querySelectorAll('[data-ecd-export]').forEach((btn) =>
        btn.addEventListener('click', () => {
            if (btn.classList.contains('is-busy')) return;
            const kind = btn.dataset.ecdExport;
            const params = { ...S };
            delete params.page;
            params.q = S.q.trim();
            btn.classList.add('is-busy');
            btn.disabled = true;
            axios({ method: 'get', url: kind === 'pdf' ? root.dataset.pdfUrl : root.dataset.excelUrl, params, responseType: 'blob' })
                .then((response) => {
                    const match = /filename="?([^";]+)"?/i.exec(response.headers['content-disposition'] || '');
                    const url = window.URL.createObjectURL(new Blob([response.data]));
                    const a = document.createElement('a');
                    a.href = url;
                    a.setAttribute('download', match ? match[1] : kind === 'pdf' ? 'Employee_Contact_Details.pdf' : 'Employee_Contact_Details.xlsx');
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
    root.querySelectorAll('[data-ecd-version]').forEach((button) =>
        button.addEventListener('click', () => {
            const v = button.dataset.ecdVersion;
            root.querySelectorAll('[data-ecd-version]').forEach((b) => b.setAttribute('aria-pressed', String(b === button)));
            root.querySelectorAll('[data-ecd-pane]').forEach((pane) => {
                const off = pane.dataset.ecdPane !== v;
                pane.classList.toggle('is-off', off);
                pane.setAttribute('aria-hidden', String(off));
            });
            root.querySelectorAll('[data-ecd-new-only]').forEach((el) => (el.hidden = v !== 'new'));
            window.dispatchEvent(new Event('resize'));
        })
    );

    const OLD = {
        type: '#employee_work_type_id-contact', department: '#department_id-contact', status: '#status-contact', from: '#startdate-contact', to: '#enddate-contact',
        ethnicity: '#ethnicity-contact', nationality: '#nationality-contact', gender: '#gender-contact',
    };
    const goBtn = document.getElementById('tabulator-html-filter-go-ECD');
    const resetBtn = document.getElementById('tabulator-html-filter-reset-ECD');
    let mirroring = false;
    const oldValue = (sel) => {
        const el = document.querySelector(sel);
        return el ? String(el.tomselect ? el.tomselect.getValue() : el.value) : '';
    };
    function setOld(k, v) {
        const el = document.querySelector(OLD[k]);
        if (!el) return;
        if (el.tagName === 'SELECT' && v && !el.querySelector(`option[value="${CSS.escape(v)}"]`)) el.appendChild(new Option((NAMES[k] || {})[v] || v, v));
        if (el.tomselect) el.tomselect.setValue(v, true);
        else el.value = v;
    }
    function toOld() {
        if (!goBtn) return;
        Object.keys(OLD).forEach((k) => setOld(k, S[k]));
        mirroring = true;
        try {
            $(goBtn).trigger('click');
        } finally {
            mirroring = false;
        }
    }
    function fromOld() {
        Object.keys(OLD).forEach((k) => (S[k] = oldValue(OLD[k])));
        if (!['0', '1', '2'].includes(S.status)) S.status = '1';
        ['from', 'to'].forEach((k) => (S[k] = /^\d{4}-\d{2}-\d{2}$/.test(S[k]) ? S[k] : ''));
        Object.assign(S, { q: '', gap: '', page: 0 });
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
            render();
        })
        .catch(() => {
            app.innerHTML = '<div class="ecd-empty">The contact details could not be loaded. Please reload the page.</div>';
        });
})();
