/*
 * Admission portal — Time line tab (pages/students/admission/timeline).
 *
 * The events are rendered by the server (ApplicantTimeline); this only
 * filters them (category chips, person, text), flips the order, expands
 * "Show all" item lists and prints. The chosen order is remembered per
 * browser (localStorage adp.timeline.order).
 */
(function () {
    'use strict';

    const root = document.querySelector('[data-adp-atl]');
    if (!root) return;

    const daysList = root.querySelector('[data-adp-atl-days]');
    if (!daysList) {
        bindPrint();
        return;
    }

    const days = Array.from(daysList.querySelectorAll('[data-adp-atl-day]'));
    const now = daysList.querySelector('[data-adp-atl-now]');
    const events = Array.from(root.querySelectorAll('[data-adp-atl-ev]'));
    const chips = Array.from(root.querySelectorAll('[data-adp-atl-cat]'));
    const q = root.querySelector('[data-adp-atl-q]');
    const who = root.querySelector('[data-adp-atl-who]');
    const clear = root.querySelector('[data-adp-atl-clear]');
    const count = root.querySelector('[data-adp-atl-count]');
    const empty = root.querySelector('[data-adp-atl-empty]');
    const orderBtns = Array.from(root.querySelectorAll('[data-adp-atl-order]'));
    const total = events.length;
    const STORE = 'adp.timeline.order';

    const store = {
        get() { try { return window.localStorage.getItem(STORE); } catch (e) { return null; } },
        set(v) { try { window.localStorage.setItem(STORE, v); } catch (e) { /* private mode */ } },
    };

    let order = store.get() === 'desc' ? 'desc' : 'asc';
    // The server renders oldest first; keep that order to flip from.
    const dayRows = new Map(days.map((day) => {
        const list = day.querySelector('.adp-atl__list');
        return [day, { list, rows: Array.from(list.children) }];
    }));

    /* ---- filters ---- */
    function apply() {
        const cats = chips.filter((c) => c.getAttribute('aria-pressed') === 'true').map((c) => c.dataset.adpAtlCat);
        const person = who ? who.value : '';
        const text = q ? q.value.trim().toLowerCase() : '';
        const filtered = cats.length > 0 || person !== '' || text !== '';
        let shown = 0;

        events.forEach((ev) => {
            const ok = (cats.length === 0 || cats.includes(ev.dataset.cat))
                && (person === '' || ev.dataset.who === person)
                && (text === '' || (ev.dataset.search || '').includes(text));
            ev.hidden = !ok;
            if (ok) shown++;
        });

        days.forEach((day) => {
            const n = day.querySelectorAll('[data-adp-atl-ev]:not([hidden])').length;
            day.hidden = n === 0;
            const label = day.querySelector('[data-adp-atl-daycount]');
            if (label) label.textContent = n + ' event' + (n === 1 ? '' : 's');
        });

        if (now) now.hidden = filtered;
        if (clear) clear.hidden = !filtered;
        if (count) {
            count.hidden = !filtered;
            count.textContent = 'Showing ' + shown + ' of ' + total + ' event' + (total === 1 ? '' : 's');
        }
        if (empty) empty.hidden = shown > 0;
        daysList.hidden = shown === 0;
    }

    chips.forEach((chip) => chip.addEventListener('click', () => {
        chip.setAttribute('aria-pressed', chip.getAttribute('aria-pressed') === 'true' ? 'false' : 'true');
        chip.classList.toggle('on', chip.getAttribute('aria-pressed') === 'true');
        apply();
    }));
    if (who) who.addEventListener('change', apply);
    if (q) {
        let t = null;
        q.addEventListener('input', () => { clearTimeout(t); t = setTimeout(apply, 120); });
        q.addEventListener('keydown', (e) => { if (e.key === 'Escape') { q.value = ''; apply(); } });
    }
    if (clear) clear.addEventListener('click', () => {
        chips.forEach((c) => { c.setAttribute('aria-pressed', 'false'); c.classList.remove('on'); });
        if (who) who.value = '';
        if (q) q.value = '';
        apply();
    });

    /* ---- order: days and the events inside each day ---- */
    function setOrder(next) {
        order = next;
        orderBtns.forEach((b) => {
            const on = b.dataset.adpAtlOrder === order;
            b.classList.toggle('on', on);
            b.setAttribute('aria-pressed', on ? 'true' : 'false');
        });
        const seq = order === 'desc' ? days.slice().reverse() : days.slice();
        seq.forEach((day) => {
            const { list, rows } = dayRows.get(day);
            (order === 'desc' ? rows.slice().reverse() : rows).forEach((li) => list.appendChild(li));
            daysList.appendChild(day);
        });
        // "Now / End" closes the story oldest-first and opens it newest-first.
        if (now) {
            if (order === 'desc') daysList.insertBefore(now, daysList.firstChild);
            else daysList.appendChild(now);
        }
        daysList.classList.toggle('is-desc', order === 'desc');
        store.set(order);
    }
    orderBtns.forEach((b) => b.addEventListener('click', () => setOrder(b.dataset.adpAtlOrder)));

    /* ---- "Show all n" item lists ---- */
    root.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-adp-atl-more]');
        if (!btn) return;
        const box = btn.closest('[data-adp-atl-items]');
        const open = box.classList.toggle('open');
        btn.textContent = open ? 'Show less' : 'Show all ' + btn.dataset.n;
    });

    /* ---- sticky day headers sit under the portal's sticky top bar ---- */
    function syncTop() {
        const bar = document.querySelector('.adp-topbar');
        root.style.setProperty('--adp-atl-top', (bar ? bar.getBoundingClientRect().height : 0) + 'px');
    }
    syncTop();
    window.addEventListener('resize', syncTop);

    bindPrint();
    if (order === 'desc') setOrder('desc');
    apply();

    function bindPrint() {
        const btn = root.querySelector('[data-adp-atl-print]');
        if (btn) btn.addEventListener('click', () => window.print());
    }
})();
