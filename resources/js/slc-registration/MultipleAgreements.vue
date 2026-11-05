<template>
    <header class="topbar"><TopBar :user="boot.user" :logo="boot.logo" module="dashboard" standalone /></header>
    <main class="wrap">
        <div class="pagehead">
            <div>
                <a class="btn-link small" :href="dashboardUrl">← SLC dashboard</a>
                <h1>Duplicate agreements</h1>
                <p>Students with two or more agreements for the same year (the agreement’s year of study) on the same course. Newest first. The name opens the student’s Accounts tab. <b>Reviewed &amp; Hide</b> takes a student off the list and the Dashboard count once checked; they come back if an agreement is added or removed.</p>
            </div>
        </div>

        <div v-if="flash" :key="flash.k" :class="'notice ' + (flash.tone === 'bad' ? 'bad' : 'ok')" role="status">
            <span style="flex: 1">{{ flash.text }}</span>
            <button type="button" class="btn-link" @click="flash = null">Dismiss</button>
        </div>

        <section class="kpis" aria-label="Figures">
            <div :class="'kpi ' + (rows.length ? 't-bad' : 't-ok')"><span class="k-label">Duplicate agreements</span><span class="k-val">{{ rows.length }}</span><span class="k-note">Still to review · {{ hidden.length }} reviewed &amp; hidden</span></div>
            <div class="kpi"><span class="k-label">Agreements</span><span class="k-val">{{ agreementCount }}</span><span class="k-note">{{ yearSplit }}</span></div>
            <div class="kpi t-warn"><span class="k-label">Without a registration</span><span class="k-val">{{ unlinked }}</span><span class="k-note">Agreements not linked to an SLC registration</span></div>
        </section>

        <section class="card" aria-labelledby="ma-h">
            <div class="card-h">
                <div class="filters" role="group" aria-label="Show">
                    <button type="button" class="chip" :aria-pressed="view === 'open'" @click="view = 'open'">To review <span class="num">{{ rows.length }}</span></button>
                    <button type="button" class="chip" :aria-pressed="view === 'hidden'" @click="view = 'hidden'">Reviewed &amp; hidden <span class="num">{{ hidden.length }}</span></button>
                </div>
                <h2 id="ma-h" class="sr">{{ view === 'hidden' ? 'Reviewed and hidden' : 'Students to review' }}</h2>
                <div class="row">
                    <label class="sr" for="ma-q">Search students</label>
                    <input id="ma-q" v-model="q" class="input dash-search" type="search" placeholder="Name, student ID or course">
                </div>
            </div>
            <div v-if="!list.length" class="empty">{{ view === 'hidden' ? 'Nothing reviewed and hidden yet.' : 'No student left to review: none has more than one agreement for the same year, or all have been reviewed.' }}</div>
            <div v-else-if="!filtered.length" class="empty">No students match “{{ q }}”.</div>
            <div v-else class="tablebox"><table class="tbl" style="min-width: 980px">
                <thead><tr><th scope="col">Student</th><th scope="col">Course</th><th scope="col">Year</th><th scope="col">Agreements</th><th scope="col"></th></tr></thead>
                <tbody><tr v-for="s in shown" :key="s.studentId + '-' + s.year + '-' + s.crelId">
                    <th scope="row"><a class="plain" :href="s.url" target="_blank" rel="noopener">{{ s.name }}</a><div class="muted small" style="font-weight: 400">{{ s.regNo }}</div></th>
                    <td>{{ s.course || '–' }}<div class="muted small">{{ s.intake }}</div></td>
                    <td>Year {{ s.year }}</td>
                    <td>
                        <span class="pill bad">{{ s.count }} agreements</span>
                        <span v-if="s.review && s.review.changed" class="pill warn" style="margin-left: 4px" :title="'Reviewed by ' + s.review.by + ' ' + s.review.at">Agreements changed since the review</span>
                        <ul class="dash-agrs">
                            <li v-for="a in s.agreements" :key="a.id">
                                <span class="mono">#{{ a.id }}</span> · {{ fmtIso(a.date) }} · {{ money(a.total) }}{{ a.instanceYear ? ' · ' + a.instanceYear : '' }}
                                · <span :class="a.linked ? '' : 'dash-warn'">{{ a.linked ? 'registration ' + fmtIso(a.regDate) : 'no registration' }}</span>
                                <span class="muted">· {{ a.by || 'unknown' }} {{ a.at }}</span>
                            </li>
                        </ul>
                    </td>
                    <td><div class="acts">
                        <a class="btn btn-sm" :href="s.url" target="_blank" rel="noopener">Accounts tab</a>
                        <button v-if="view === 'open'" type="button" class="btn btn-sm btn-primary" :disabled="busy" @click="review(s)">Reviewed &amp; Hide</button>
                        <template v-else>
                            <span class="muted small">Reviewed by {{ s.review.by }} · {{ s.review.at }}</span>
                            <button type="button" class="btn-link small" :disabled="busy" @click="unhide(s)">Unhide</button>
                        </template>
                    </div></td>
                </tr></tbody>
            </table></div>
            <div v-if="filtered.length > limit" class="scard-f"><span class="muted small">Showing {{ limit }} of {{ filtered.length }}</span><button type="button" class="btn btn-sm" @click="limit = filtered.length">Show all</button></div>
        </section>
    </main>
</template>

<script>
import { computed, ref, watch } from "vue";
import { fmtIso, money, norm } from "./helpers";
import TopBar from "./TopBar.vue";

/*
 * /slc-registration/multiple-agreements — opened from the Dashboard's "Duplicate agreements" box
 * (user, 2 Nov 2026): students with 2+ agreements (not deleted) for the same year — the agreements
 * table's `year` — on the same course relation. Server: SlcRegistrationController@multipleAgreements →
 * RegistrationState::duplicateAgreements(). "Reviewed & Hide" (user, 5 Nov 2026) hides a group from
 * the list and the count while its agreements stay the same; Unhide brings it back.
 */
export default {
    components: { TopBar },
    props: { boot: { type: Object, required: true } },
    setup(props) {
        const rows = ref(props.boot.multiple.students || []);
        const hidden = ref(props.boot.multiple.hidden || []);
        const view = ref("open");
        const list = computed(() => (view.value === "hidden" ? hidden.value : rows.value));
        const q = ref("");
        const limit = ref(50);
        watch(view, () => { limit.value = 50; });
        const dashboardUrl = route("slc.registration") + "?module=dashboard";
        const filtered = computed(() => {
            const t = norm(q.value);
            return t ? list.value.filter((s) => norm([s.name, s.regNo, s.course, s.intake].join(" ")).includes(t)) : list.value;
        });
        const shown = computed(() => filtered.value.slice(0, limit.value));
        const agreementCount = computed(() => rows.value.reduce((a, s) => a + s.count, 0));
        const unlinked = computed(() => rows.value.reduce((a, s) => a + s.agreements.filter((x) => !x.linked).length, 0));
        const yearSplit = computed(() => {
            const byYear = rows.value.reduce((o, s) => { o[s.year] = (o[s.year] || 0) + 1; return o; }, {});
            return Object.keys(byYear).sort().map((y) => byYear[y] + " in Year " + y).join(" · ") || "–";
        });

        // Reviewed & Hide / Unhide (RegistrationActions::reviewDuplicate / unhideDuplicate): both lists come back fresh
        const busy = ref(false);
        const flash = ref(null);
        async function send(name, body) {
            if (busy.value) return;
            busy.value = true;
            try {
                const res = await fetch(route(name), {
                    method: "POST", credentials: "same-origin",
                    headers: { "Content-Type": "application/json", Accept: "application/json", "X-Requested-With": "XMLHttpRequest", "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "" },
                    body: JSON.stringify(body),
                });
                const data = await res.json().catch(() => null);
                if (data && data.multiple) { rows.value = data.multiple.students; hidden.value = data.multiple.hidden; }
                flash.value = { text: (data && (data.flash ? data.flash.text : data.message)) || (res.status === 419 ? "Your session has expired. Reload the page." : "Something went wrong. Please try again."), tone: data && data.flash ? data.flash.tone : "bad", k: Date.now() };
            } catch (e) {
                flash.value = { text: "The server could not be reached. Please try again.", tone: "bad", k: Date.now() };
            } finally {
                busy.value = false;
            }
        }
        const review = (s) => send("slc.registration.multiple.review", { student_id: s.studentId, crel_id: s.crelId, year: s.year, agreement_ids: s.agreementIds });
        const unhide = (s) => send("slc.registration.multiple.unhide", { student_id: s.studentId, crel_id: s.crelId, year: s.year });

        return { rows, hidden, view, list, q, limit, filtered, shown, agreementCount, unlinked, yearSplit, dashboardUrl, busy, flash, review, unhide, fmtIso, money };
    },
};
</script>
