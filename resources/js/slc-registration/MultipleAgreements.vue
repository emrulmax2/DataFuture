<template>
    <header class="topbar"><TopBar :user="boot.user" :logo="boot.logo" module="dashboard" standalone /></header>
    <main class="wrap">
        <div class="pagehead">
            <div>
                <a class="btn-link small" :href="dashboardUrl">← SLC dashboard</a>
                <h1>Duplicate agreements</h1>
                <p>Students with two or more agreements for the same year (the agreement’s year of study) on the same course. Newest first. The name opens the student’s Accounts tab.</p>
            </div>
        </div>

        <section class="kpis" aria-label="Figures">
            <div :class="'kpi ' + (rows.length ? 't-bad' : 't-ok')"><span class="k-label">Duplicate agreements</span><span class="k-val">{{ rows.length }}</span><span class="k-note">Students with 2+ agreements for one year</span></div>
            <div class="kpi"><span class="k-label">Agreements</span><span class="k-val">{{ agreementCount }}</span><span class="k-note">{{ yearSplit }}</span></div>
            <div class="kpi t-warn"><span class="k-label">Without a registration</span><span class="k-val">{{ unlinked }}</span><span class="k-note">Agreements not linked to an SLC registration</span></div>
        </section>

        <section class="card" aria-labelledby="ma-h">
            <div class="card-h">
                <h2 id="ma-h">Students</h2>
                <div class="row">
                    <label class="sr" for="ma-q">Search students</label>
                    <input id="ma-q" v-model="q" class="input dash-search" type="search" placeholder="Name, student ID or course">
                </div>
            </div>
            <div v-if="!rows.length" class="empty">No student has more than one agreement for the same year.</div>
            <div v-else-if="!filtered.length" class="empty">No students match “{{ q }}”.</div>
            <div v-else class="tablebox"><table class="tbl" style="min-width: 980px">
                <thead><tr><th scope="col">Student</th><th scope="col">Course</th><th scope="col">Year</th><th scope="col">Agreements</th><th scope="col"></th></tr></thead>
                <tbody><tr v-for="s in shown" :key="s.studentId + '-' + s.year + '-' + s.course">
                    <th scope="row"><a class="plain" :href="s.url" target="_blank" rel="noopener">{{ s.name }}</a><div class="muted small" style="font-weight: 400">{{ s.regNo }}</div></th>
                    <td>{{ s.course || '–' }}<div class="muted small">{{ s.intake }}</div></td>
                    <td>Year {{ s.year }}</td>
                    <td>
                        <span class="pill bad">{{ s.count }} agreements</span>
                        <ul class="dash-agrs">
                            <li v-for="a in s.agreements" :key="a.id">
                                <span class="mono">#{{ a.id }}</span> · {{ fmtIso(a.date) }} · {{ money(a.total) }}{{ a.instanceYear ? ' · ' + a.instanceYear : '' }}
                                · <span :class="a.linked ? '' : 'dash-warn'">{{ a.linked ? 'registration ' + fmtIso(a.regDate) : 'no registration' }}</span>
                                <span class="muted">· {{ a.by || 'unknown' }} {{ a.at }}</span>
                            </li>
                        </ul>
                    </td>
                    <td><a class="btn btn-sm" :href="s.url" target="_blank" rel="noopener">Accounts tab</a></td>
                </tr></tbody>
            </table></div>
            <div v-if="filtered.length > limit" class="scard-f"><span class="muted small">Showing {{ limit }} of {{ filtered.length }}</span><button type="button" class="btn btn-sm" @click="limit = filtered.length">Show all</button></div>
        </section>
    </main>
</template>

<script>
import { computed, ref } from "vue";
import { fmtIso, money, norm } from "./helpers";
import TopBar from "./TopBar.vue";

/*
 * /slc-registration/multiple-agreements — opened from the Dashboard's "Duplicate agreements" box
 * (user, 2 Nov 2026): students with 2+ agreements (not deleted) for the same year — the agreements
 * table's `year` — on the same course relation. Server: SlcRegistrationController@multipleAgreements →
 * RegistrationState::duplicateAgreements().
 */
export default {
    components: { TopBar },
    props: { boot: { type: Object, required: true } },
    setup(props) {
        const rows = props.boot.multiple.students;
        const q = ref("");
        const limit = ref(50);
        const dashboardUrl = route("slc.registration") + "?module=dashboard";
        const filtered = computed(() => {
            const t = norm(q.value);
            return t ? rows.filter((s) => norm([s.name, s.regNo, s.course, s.intake].join(" ")).includes(t)) : rows;
        });
        const shown = computed(() => filtered.value.slice(0, limit.value));
        const agreementCount = rows.reduce((a, s) => a + s.count, 0);
        const unlinked = rows.reduce((a, s) => a + s.agreements.filter((x) => !x.linked).length, 0);
        const byYear = rows.reduce((o, s) => { o[s.year] = (o[s.year] || 0) + 1; return o; }, {});
        const yearSplit = Object.keys(byYear).sort().map((y) => byYear[y] + " in Year " + y).join(" · ") || "–";
        return { rows, q, limit, filtered, shown, agreementCount, unlinked, yearSplit, dashboardUrl, fmtIso, money };
    },
};
</script>
